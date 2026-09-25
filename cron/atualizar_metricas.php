<?php
require_once __DIR__ . "/00.bootstrap.php";
$execucao = fincontrol_cron_iniciar($conexao, "atualizar_metricas");
$inicio = date("Y-m-01");
$fim = date("Y-m-t");
$usuarios = $conexao->query("SELECT id_usuario FROM usuario");
$atualizados = 0;

while ($usuario = $usuarios->fetch_assoc()) {
    $idUsuario = intval($usuario["id_usuario"]);
    $receitas = 0.0;
    $despesas = 0.0;
    $categorias = [];

    $stmt = $conexao->prepare("SELECT valor FROM receitas WHERE id_usuario = ? AND data_recebimento BETWEEN ? AND ?");
    $stmt->bind_param("iss", $idUsuario, $inicio, $fim);
    $stmt->execute();
    foreach ($stmt->get_result() as $row) $receitas += descriptografar_valor_financeiro($row["valor"]);

    $stmt = $conexao->prepare("SELECT valor, categoria FROM despesas WHERE id_usuario = ? AND data_vencimento BETWEEN ? AND ?");
    $stmt->bind_param("iss", $idUsuario, $inicio, $fim);
    $stmt->execute();
    foreach ($stmt->get_result() as $row) {
        $valor = descriptografar_valor_financeiro($row["valor"]);
        $despesas += $valor;
        $categoria = $row["categoria"] ?: "Outros";
        $categorias[$categoria] = ($categorias[$categoria] ?? 0) + $valor;
    }

    arsort($categorias);
    $categoriaCritica = $categorias ? array_key_first($categorias) : null;
    $saldo = $receitas - $despesas;
    $comprometimento = $receitas > 0 ? ($despesas / $receitas) * 100 : ($despesas > 0 ? 100 : 0);
    $score = 100;
    if ($saldo < 0) $score -= 35;
    if ($comprometimento > 90) $score -= 30; elseif ($comprometimento > 75) $score -= 20; elseif ($comprometimento > 60) $score -= 10;
    if ($receitas <= 0 && $despesas > 0) $score -= 20;
    $score = max(0, min(100, $score));

    $stmt = $conexao->prepare("INSERT INTO usuario_metricas (id_usuario, score_financeiro, total_receitas, total_despesas, saldo, categoria_critica, atualizado_em) VALUES (?, ?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE score_financeiro=VALUES(score_financeiro), total_receitas=VALUES(total_receitas), total_despesas=VALUES(total_despesas), saldo=VALUES(saldo), categoria_critica=VALUES(categoria_critica), atualizado_em=NOW()");
    $stmt->bind_param("iiddds", $idUsuario, $score, $receitas, $despesas, $saldo, $categoriaCritica);
    if ($stmt->execute()) $atualizados++;
}

$detalhes = $atualizados . " usuario(s) atualizado(s)";
fincontrol_cron_finalizar($conexao, $execucao, "sucesso", $detalhes);
echo $detalhes . PHP_EOL;

