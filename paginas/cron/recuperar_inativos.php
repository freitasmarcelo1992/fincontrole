<?php
require_once __DIR__ . "/00.bootstrap.php";
$execucao = fincontrol_cron_iniciar($conexao, "recuperar_inativos");
$referencia = date("o-W");
$sql = "SELECT u.id_usuario, u.nome_usuario, u.email_usuario, MAX(e.criado_em) AS ultimo_login FROM usuario u INNER JOIN eventos_usuario e ON e.id_usuario = u.id_usuario AND e.evento = 'login' GROUP BY u.id_usuario, u.nome_usuario, u.email_usuario HAVING ultimo_login < DATE_SUB(NOW(), INTERVAL 7 DAY)";
$usuarios = $conexao->query($sql);
$enviados = 0;

foreach ($usuarios as $usuario) {
    $idUsuario = intval($usuario["id_usuario"]);
    $tipo = "recuperacao_inativo";
    $reserva = $conexao->prepare("INSERT IGNORE INTO notificacoes_envio (id_usuario, tipo, referencia, enviado_em) VALUES (?, ?, ?, NOW())");
    $reserva->bind_param("iss", $idUsuario, $tipo, $referencia);
    $reserva->execute();
    if ($reserva->affected_rows !== 1) continue;

    $corpo = "Olá, " . $usuario["nome_usuario"] . ".\n\nFaz alguns dias que você não acessa o FinControle. Entre para revisar seus gastos, contas e metas financeiras.";
    try {
        if (fincontrol_email_enviar($usuario["email_usuario"], "Como estão suas finanças?", $corpo)) {
            $enviados++;
        } else {
            $conexao->query("DELETE FROM notificacoes_envio WHERE id_envio = " . intval($reserva->insert_id));
        }
    } catch (Throwable $erro) {
        $conexao->query("DELETE FROM notificacoes_envio WHERE id_envio = " . intval($reserva->insert_id));
        error_log("FinControle recuperacao: " . $erro->getMessage());
    }
}

$detalhes = $enviados . " usuario(s) reengajado(s)";
fincontrol_cron_finalizar($conexao, $execucao, "sucesso", $detalhes);
echo $detalhes . PHP_EOL;
