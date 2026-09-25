<?php

if (file_exists(__DIR__ . "/00.assinaturas.php")) {
    require_once __DIR__ . "/00.assinaturas.php";
}


function fincontrol_usuario_acesso_completo($conexao, $id_usuario) {
    if (function_exists("fincontrol_sessao_acesso_completo") && fincontrol_sessao_acesso_completo()) {
        return true;
    }

    if (!$conexao || !$id_usuario) {
        return false;
    }

    $stmt = $conexao->prepare("SELECT email_usuario FROM usuario WHERE id_usuario = ? LIMIT 1");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (function_exists("fincontrol_email_acesso_completo")) {
        return fincontrol_email_acesso_completo($usuario["email_usuario"] ?? "");
    }

    return strtolower(trim((string) ($usuario["email_usuario"] ?? ""))) === "teste@teste.com";
}
function fincontrol_plano_premium_ativo($conexao, $id_usuario) {
    return true;
}

function fincontrol_limite_orcamentos_gratuito() {
    return 3;
}

function fincontrol_limite_cartoes_gratuito() {
    return 1;
}

function fincontrol_total_orcamentos_mes($conexao, $id_usuario, $mes_referencia) {
    if (!$conexao) {
        return 0;
    }

    $stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM orcamentos_categoria WHERE id_usuario = ? AND mes_referencia = ?");
    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param("is", $id_usuario, $mes_referencia);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return intval($row["total"] ?? 0);
}

function fincontrol_total_cartoes_usuario($conexao, $id_usuario) {
    if (!$conexao) {
        return 0;
    }

    $stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM cartoes_credito WHERE id_usuario = ?");
    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return intval($row["total"] ?? 0);
}


function fincontrol_limite_despesas_gratuito() {
    return 100;
}

function fincontrol_limite_receitas_gratuito() {
    return 50;
}

function fincontrol_total_lancamentos_usuario($conexao, $id_usuario, $tabela) {
    if (!$conexao) {
        return 0;
    }

    $tabelasPermitidas = ["despesas", "receitas"];
    if (!in_array($tabela, $tabelasPermitidas, true)) {
        return 0;
    }

    $stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM `$tabela` WHERE id_usuario = ?");
    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return intval($row["total"] ?? 0);
}
function fincontrol_premium_cta_url() {
    return "03.menu.php";
}

?>



