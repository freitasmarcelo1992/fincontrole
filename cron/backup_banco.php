<?php
require_once __DIR__ . "/00.bootstrap.php";
$execucao = fincontrol_cron_iniciar($conexao, "backup_banco");
$diretorio = dirname(dirname(__DIR__)) . "/storage/backups";
if (!is_dir($diretorio)) @mkdir($diretorio, 0750, true);
$arquivo = $diretorio . "/backup_fincontrol_" . date("Ymd_His") . ".sql";

function fincontrol_backup_php($conexao, $arquivo) {
    $handle = @fopen($arquivo, "wb");
    if (!$handle) return false;

    fwrite($handle, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
    $tabelas = $conexao->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    if (!$tabelas) { fclose($handle); return false; }

    while ($item = $tabelas->fetch_row()) {
        $tabela = $item[0];
        $nomeSeguro = "`" . str_replace("`", "``", $tabela) . "`";
        $estrutura = $conexao->query("SHOW CREATE TABLE " . $nomeSeguro)->fetch_row();
        fwrite($handle, "DROP TABLE IF EXISTS " . $nomeSeguro . ";\n" . $estrutura[1] . ";\n\n");

        $dados = $conexao->query("SELECT * FROM " . $nomeSeguro, MYSQLI_USE_RESULT);
        if (!$dados) continue;
        while ($row = $dados->fetch_assoc()) {
            $colunas = array_map(function ($coluna) { return "`" . str_replace("`", "``", $coluna) . "`"; }, array_keys($row));
            $valores = array_map(function ($valor) use ($conexao) {
                return $valor === null ? "NULL" : "'" . $conexao->real_escape_string($valor) . "'";
            }, array_values($row));
            fwrite($handle, "INSERT INTO " . $nomeSeguro . " (" . implode(",", $colunas) . ") VALUES (" . implode(",", $valores) . ");\n");
        }
        $dados->free();
        fwrite($handle, "\n");
    }

    fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($handle);
    return is_file($arquivo) && filesize($arquivo) > 0;
}

putenv("MYSQL_PWD=" . $senha);
$mysqldump = getenv("FINCONTROL_MYSQLDUMP") ?: "mysqldump";
if (PHP_OS_FAMILY === "Windows" && is_file("C:/xampp2/mysql/bin/mysqldump.exe")) {
    $mysqldump = "C:/xampp2/mysql/bin/mysqldump.exe";
}
$comando = escapeshellarg($mysqldump) . " --single-transaction --quick --host=" . escapeshellarg($host)
    . " --user=" . escapeshellarg($usuario)
    . " " . escapeshellarg($banco)
    . " --result-file=" . escapeshellarg($arquivo) . " 2>&1";
$saida = [];
$codigo = 1;
exec($comando, $saida, $codigo);

if ($codigo !== 0 || !is_file($arquivo) || filesize($arquivo) === 0) {
    @unlink($arquivo);
    if (fincontrol_backup_php($conexao, $arquivo)) {
        $codigo = 0;
        $saida[] = "Fallback PHP utilizado";
    }
}

if ($codigo === 0 && is_file($arquivo) && filesize($arquivo) > 0) {
    $arquivos = glob($diretorio . "/backup_fincontrol_*.sql") ?: [];
    rsort($arquivos);
    foreach (array_slice($arquivos, 7) as $antigo) @unlink($antigo);
    $detalhes = "Backup criado: " . basename($arquivo);
    fincontrol_cron_finalizar($conexao, $execucao, "sucesso", $detalhes);
    echo $detalhes . PHP_EOL;
    exit;
}

@unlink($arquivo);
$detalhes = "Falha no mysqldump: " . implode(" ", $saida);
fincontrol_cron_finalizar($conexao, $execucao, "erro", $detalhes);
fwrite(STDERR, $detalhes . PHP_EOL);
exit(1);
