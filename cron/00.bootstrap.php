<?php
if (PHP_SAPI !== "cli") {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . "/09.conexao.php";
require_once dirname(__DIR__) . "/00.crypto.php";
require_once dirname(__DIR__) . "/00.email.php";

if (!$conexao) {
    fwrite(STDERR, "Conexao indisponivel." . PHP_EOL);
    exit(1);
}

function fincontrol_cron_iniciar($conexao, $tarefa) {
    $stmt = $conexao->prepare("INSERT INTO cron_execucoes (tarefa, status, iniciado_em) VALUES (?, 'executando', NOW())");
    if (!$stmt) return 0;
    $stmt->bind_param("s", $tarefa);
    $stmt->execute();
    return intval($conexao->insert_id);
}

function fincontrol_cron_finalizar($conexao, $idExecucao, $status, $detalhes) {
    if (!$idExecucao) return;
    $stmt = $conexao->prepare("UPDATE cron_execucoes SET status = ?, detalhes = ?, finalizado_em = NOW() WHERE id_execucao = ?");
    $stmt->bind_param("ssi", $status, $detalhes, $idExecucao);
    $stmt->execute();
}

