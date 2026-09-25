<?php
require_once __DIR__ . "/00.bootstrap.php";
$execucao = fincontrol_cron_iniciar($conexao, "enviar_lembretes");
$data = date("Y-m-d", strtotime("+1 day"));
$stmt = $conexao->prepare("SELECT u.id_usuario, u.nome_usuario, u.email_usuario, COUNT(*) AS quantidade FROM despesas d INNER JOIN usuario u ON u.id_usuario = d.id_usuario WHERE d.data_vencimento = ? AND d.status <> 'Paga' GROUP BY u.id_usuario, u.nome_usuario, u.email_usuario");
$stmt->bind_param("s", $data);
$stmt->execute();
$enviados = 0;

foreach ($stmt->get_result() as $usuario) {
    $idUsuario = intval($usuario["id_usuario"]);
    $tipo = "vencimento_amanha";
    $reserva = $conexao->prepare("INSERT IGNORE INTO notificacoes_envio (id_usuario, tipo, referencia, enviado_em) VALUES (?, ?, ?, NOW())");
    $reserva->bind_param("iss", $idUsuario, $tipo, $data);
    $reserva->execute();
    if ($reserva->affected_rows !== 1) continue;

    $quantidade = intval($usuario["quantidade"]);
    $corpo = "Olá, " . $usuario["nome_usuario"] . ".\n\nVocê possui " . $quantidade . " conta(s) vencendo amanhã. Acesse o FinControle para revisar seus compromissos.";
    try {
        if (fincontrol_email_enviar($usuario["email_usuario"], "Contas vencendo amanhã", $corpo)) {
            $enviados++;
        } else {
            $conexao->query("DELETE FROM notificacoes_envio WHERE id_envio = " . intval($reserva->insert_id));
        }
    } catch (Throwable $erro) {
        $conexao->query("DELETE FROM notificacoes_envio WHERE id_envio = " . intval($reserva->insert_id));
        error_log("FinControle lembrete: " . $erro->getMessage());
    }
}

$detalhes = $enviados . " lembrete(s) enviado(s)";
fincontrol_cron_finalizar($conexao, $execucao, "sucesso", $detalhes);
echo $detalhes . PHP_EOL;

