<?php
require_once __DIR__ . '/00.sessao.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['erro' => 'Metodo invalido.']);
    exit;
}
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($token) || empty($_SESSION['noticias_csrf']) || !hash_equals($_SESSION['noticias_csrf'], $token)) {
    http_response_code(403);
    echo json_encode(['erro' => 'Sessao invalida.']);
    exit;
}
session_write_close();
require_once __DIR__ . '/00.noticias_atualizacao.php';
@set_time_limit(35);
try {
    echo json_encode(fincontrol_noticias_atualizar_proxima());
} catch (Throwable $erro) {
    error_log('Atualizacao noticias: ' . $erro->getMessage());
    http_response_code(503);
    echo json_encode(['erro' => 'Atualizacao temporariamente indisponivel.']);
}
