<?php
require_once __DIR__ . '/00.noticias.php';

// Uma fonte por chamada: a interface abre usando o cache, sem esperar pela rede.
function fincontrol_noticias_atualizar_proxima(): array
{
    $dir = dirname(fincontrol_noticias_cache_arquivo());
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Diretorio de noticias indisponivel.');
    }
    $lock = fopen($dir . '/noticias_atualizacao.lock', 'c');
    if (!$lock) throw new RuntimeException('Armazenamento de noticias indisponivel.');
    if (!flock($lock, LOCK_EX | LOCK_NB)) {
        fclose($lock);
        return ['status' => 'ocupado', 'novas' => 0];
    }
    try {
        $arquivoEstado = $dir . '/noticias_atualizacao.json';
        $estado = is_file($arquivoEstado) ? json_decode(file_get_contents($arquivoEstado), true) : [];
        $estado = is_array($estado) ? $estado : [];
        $fontePendente = null;
        foreach (fincontrol_noticias_fontes() as $fonte) {
            $id = sha1($fonte['url']);
            if (($estado[$id]['proxima'] ?? 0) <= time()) {
                $fontePendente = $fonte;
                break;
            }
        }
        if ($fontePendente === null) return ['status' => 'concluido', 'novas' => 0, 'revisao' => fincontrol_noticias_revisao()];
        $id = sha1($fontePendente['url']);
        // Evita repeticao imediata mesmo se o servidor encerrar uma chamada lenta.
        $estado[$id] = array_merge($estado[$id] ?? [], ['tentativa' => date('c'), 'proxima' => time() + 600]);
        fincontrol_noticias_salvar_cache($arquivoEstado, $estado);
        $GLOBALS['noticias_deadline'] = microtime(true) + 18;
        $auditoria = [];
        $novas = fincontrol_ler_feed_rss($fontePendente, $auditoria);
        unset($GLOBALS['noticias_deadline']);
        fincontrol_noticias_salvar_auditoria($auditoria);
        if ($novas) {
            fincontrol_noticias_salvar_historico($novas);
            $recentes = fincontrol_noticias_recentes(fincontrol_noticias_historico(PHP_INT_MAX));
            $recentes = fincontrol_noticias_balancear_por_tema(array_values($recentes));
            fincontrol_noticias_salvar_cache(fincontrol_noticias_cache_arquivo(), array_slice($recentes, 0, 80));
            $estado[$id] = ['tentativa' => date('c'), 'sucesso' => date('c'), 'proxima' => (int) (floor(time() / 3600) + 1) * 3600];
            fincontrol_noticias_salvar_cache($arquivoEstado, $estado);
        }
        if (!$novas) {
            $estado[$id]['aviso'] = 'Nenhuma noticia recente validada nesta tentativa.';
            fincontrol_noticias_salvar_cache($arquivoEstado, $estado);
        }
        return ['status' => 'processando', 'novas' => count($novas),
            'fonte' => $fontePendente['nome'], 'revisao' => fincontrol_noticias_revisao()];
    } finally {
        unset($GLOBALS['noticias_deadline']);
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}
