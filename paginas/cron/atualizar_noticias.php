<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require_once __DIR__ . '/../00.noticias_atualizacao.php';
@set_time_limit(0);
for ($i = 0; $i < count(fincontrol_noticias_fontes()); $i++) {
    $resultado = fincontrol_noticias_atualizar_proxima();
    echo '[' . date('c') . '] ' . json_encode($resultado) . PHP_EOL;
    if ($resultado['status'] !== 'processando') break;
}
