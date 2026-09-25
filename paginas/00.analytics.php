<?php

function fincontrol_evento_registrar($conexao, $idUsuario, $evento, array $dados = []) {
    if (!$conexao || !$idUsuario || !preg_match('/^[a-z0-9_]{2,60}$/', $evento)) return false;

    $pagina = substr(basename($_SERVER["PHP_SELF"] ?? "cron"), 0, 100);
    $metadados = $dados ? json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
    $stmt = $conexao->prepare("INSERT INTO eventos_usuario (id_usuario, evento, pagina, metadados) VALUES (?, ?, ?, ?)");
    if (!$stmt) return false;

    $stmt->bind_param("isss", $idUsuario, $evento, $pagina, $metadados);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

