<?php

function fincontrol_noticias_categorias(): array
{
    return ['financas' => 'Finanças', 'tecnologia' => 'Tecnologia', 'gestao' => 'Gestão',
        'engenharia' => 'Engenharia', 'carreira' => 'Carreira e estudos',
        'treinos' => 'Treinos e bem-estar', 'saude' => 'Saúde e nutrição'];
}

function fincontrol_noticias_recentes(array $noticias): array
{
    $agora = time();
    $noticias = array_values(array_filter($noticias, static function ($item) use ($agora) {
        $data = (int) ($item['timestamp'] ?? 0);
        return $data >= $agora - 72 * 3600 && $data <= $agora + 300
            && !empty($item['validado_em']) && fincontrol_noticias_link_destino_valido($item);
    }));
    usort($noticias, static function ($a, $b) { return $b['timestamp'] <=> $a['timestamp']; });
    return $noticias;
}

function fincontrol_noticias_revisao(): string
{
    $arquivo = fincontrol_noticias_cache_arquivo();
    return is_file($arquivo) ? (string) hash_file('sha256', $arquivo) : '';
}

function fincontrol_noticias_fontes(): array
{
    return [
        ['tema' => 'financas', 'nome' => 'InfoMoney', 'url' => 'https://www.infomoney.com.br/feed/', 'peso' => 4, 'imagem' => 'img/news-selic.jpg', 'hosts' => ['infomoney.com.br', 'www.infomoney.com.br'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'financas', 'nome' => 'G1 Economia', 'url' => 'https://g1.globo.com/rss/g1/economia/', 'peso' => 4, 'imagem' => 'img/news-dollar.jpg', 'hosts' => ['g1.globo.com'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'financas', 'nome' => 'Investing Brasil', 'url' => 'https://br.investing.com/rss/news_25.rss', 'peso' => 3, 'imagem' => 'img/news-planning.jpg', 'hosts' => ['br.investing.com'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'financas', 'nome' => 'Valor Econômico', 'url' => 'https://pox.globo.com/rss/valor', 'peso' => 4, 'imagem' => 'img/news-card.jpg', 'hosts' => ['valor.globo.com'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'tecnologia', 'nome' => 'G1 Tecnologia', 'url' => 'https://g1.globo.com/rss/g1/tecnologia/', 'peso' => 4, 'imagem' => 'img/news-card.jpg', 'hosts' => ['g1.globo.com'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'tecnologia', 'nome' => 'Olhar Digital', 'url' => 'https://olhardigital.com.br/feed/', 'peso' => 3, 'imagem' => 'img/news-planning.jpg', 'hosts' => ['olhardigital.com.br', 'www.olhardigital.com.br'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'tecnologia', 'nome' => 'Canaltech', 'url' => 'https://www.canaltech.com.br/rss/', 'peso' => 3, 'imagem' => 'img/news-selic.jpg', 'hosts' => ['canaltech.com.br', 'www.canaltech.com.br'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'gestao', 'nome' => 'EUAX', 'url' => 'https://www.euax.com.br/feed/', 'peso' => 3, 'imagem' => 'img/news-planning.jpg', 'hosts' => ['euax.com.br', 'www.euax.com.br'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'gestao', 'nome' => 'Project Design Management', 'url' => 'https://projectdesignmanagement.com.br/feed/', 'peso' => 3, 'imagem' => 'img/news-card.jpg', 'hosts' => ['projectdesignmanagement.com.br', 'www.projectdesignmanagement.com.br'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'gestao', 'nome' => 'PM3', 'url' => 'https://pm3.com.br/blog/feed/', 'peso' => 3, 'imagem' => 'img/news-planning.jpg', 'hosts' => ['pm3.com.br'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'gestao', 'nome' => 'Pipefy', 'url' => 'https://www.pipefy.com/pt-br/blog/feed/', 'peso' => 3, 'imagem' => 'img/news-card.jpg', 'hosts' => ['pipefy.com', 'www.pipefy.com'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'gestao', 'nome' => 'Ploomes', 'url' => 'https://blog.ploomes.com/feed/', 'peso' => 2, 'imagem' => 'img/news-dollar.jpg', 'hosts' => ['blog.ploomes.com', 'ploomes.com', 'www.ploomes.com'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'engenharia', 'nome' => 'Engenharia e', 'url' => 'https://engenhariae.com.br/feed/', 'peso' => 3, 'imagem' => 'img/news-selic.jpg', 'hosts' => ['engenhariae.com.br', 'www.engenhariae.com.br'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'engenharia', 'nome' => 'Instituto de Engenharia', 'url' => 'https://www.institutodeengenharia.org.br/site/feed/', 'peso' => 3, 'imagem' => 'img/news-planning.jpg', 'hosts' => ['institutodeengenharia.org.br', 'www.institutodeengenharia.org.br'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'engenharia', 'nome' => 'CBIC', 'url' => 'https://www.cbic.org.br/feed/', 'peso' => 2, 'imagem' => 'img/news-card.jpg', 'hosts' => ['cbic.org.br', 'www.cbic.org.br'], 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'carreira', 'nome' => 'Agência Brasil Educação', 'url' => 'https://agenciabrasil.ebc.com.br/rss/educacao/feed.xml', 'hosts' => ['agenciabrasil.ebc.com.br'], 'peso' => 3, 'imagem' => 'img/news-planning.jpg', 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'treinos', 'nome' => 'Boa Forma', 'url' => 'https://boaforma.abril.com.br/feed/', 'hosts' => ['boaforma.abril.com.br'], 'peso' => 3, 'imagem' => 'img/news-planning.jpg', 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'saude', 'nome' => 'Agência Brasil Saúde', 'url' => 'https://agenciabrasil.ebc.com.br/rss/saude/feed.xml', 'hosts' => ['agenciabrasil.ebc.com.br'], 'peso' => 3, 'imagem' => 'img/news-planning.jpg', 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'saude', 'nome' => 'VEJA Saúde', 'url' => 'https://saude.abril.com.br/feed/', 'hosts' => ['saude.abril.com.br'], 'peso' => 3, 'imagem' => 'img/news-planning.jpg', 'max_idade_horas' => 72, 'limite_itens' => 10],
        ['tema' => 'financas', 'nome' => 'Agência Brasil Economia', 'url' => 'https://agenciabrasil.ebc.com.br/rss/economia/feed.xml', 'hosts' => ['agenciabrasil.ebc.com.br'], 'peso' => 3, 'imagem' => 'img/news-planning.jpg', 'max_idade_horas' => 72, 'limite_itens' => 10],
    ];
}
function fincontrol_noticias_home(int $limite = 4, bool $atualizarSeExpirado = false): array
{
    $cache = fincontrol_noticias_cache_arquivo();
    if ($atualizarSeExpirado && (!is_file($cache) || time() - filemtime($cache) >= 3600)) {
        fincontrol_noticias_atualizar_cache();
    }
    $dados = is_file($cache) ? json_decode((string) file_get_contents($cache), true) : [];
    $dados = is_array($dados) ? $dados : [];
    $recentes = fincontrol_noticias_recentes(fincontrol_noticias_unicas(array_merge(
        $dados, fincontrol_noticias_historico(PHP_INT_MAX)
    )));
    return array_slice(fincontrol_noticias_balancear_por_tema($recentes), 0, $limite);
}

function fincontrol_noticias_cache_arquivo(): string
{
    return __DIR__ . '/storage/cache/noticias_home.json';
}

function fincontrol_noticias_cache_validado(array $dados): bool
{
    foreach ($dados as $noticia) {
        if (
            empty($noticia['validado_em']) ||
            empty($noticia['link']) ||
            empty($noticia['fonte']) ||
            empty($noticia['tema']) ||
            !fincontrol_noticias_link_destino_valido($noticia)
        ) {
            return false;
        }
    }

    return true;
}

function fincontrol_noticias_url_imagem(string $url): bool
{
    $path = strtolower((string) parse_url($url, PHP_URL_PATH));
    return (bool) preg_match('/\.(?:jpe?g|png|gif|webp|avif|svg)$/i', $path);
}

function fincontrol_noticias_link_destino_valido(array $noticia): bool
{
    $link = trim((string) ($noticia['link'] ?? ''));
    $imagem = trim((string) ($noticia['imagem'] ?? ''));
    $scheme = strtolower((string) parse_url($link, PHP_URL_SCHEME));

    return $link !== '' && in_array($scheme, ['http', 'https'], true) && $link !== $imagem && !fincontrol_noticias_url_imagem($link);
}

function fincontrol_noticias_auditoria_arquivo(): string
{
    return __DIR__ . '/storage/cache/noticias_auditoria.json';
}

function fincontrol_noticias_historico_arquivo(): string
{
    return __DIR__ . '/storage/cache/noticias_historico.json';
}

function fincontrol_noticias_historico(int $limite): array
{
    $arquivo = fincontrol_noticias_historico_arquivo();
    if (!is_file($arquivo)) {
        return [];
    }

    $dados = json_decode((string) file_get_contents($arquivo), true);
    if (!is_array($dados)) {
        return [];
    }

    $noticias = [];
    foreach ($dados as $noticia) {
        if (!is_array($noticia) || !fincontrol_noticias_link_destino_valido($noticia)) {
            continue;
        }

        $noticia['tempo'] = isset($noticia['timestamp']) ? fincontrol_noticias_tempo((int) $noticia['timestamp']) : ($noticia['tempo'] ?? 'Hoje');
        $noticias[] = $noticia;

        if (count($noticias) >= $limite) {
            break;
        }
    }

    return $noticias;
}

function fincontrol_noticias_imagens_padrao(): array
{
    return [
        'img/news-selic.jpg',
        'img/news-dollar.jpg',
        'img/news-card.jpg',
        'img/news-planning.jpg',
    ];
}

function fincontrol_noticias_eh_imagem_padrao(string $imagem): bool
{
    return in_array($imagem, fincontrol_noticias_imagens_padrao(), true);
}

function fincontrol_noticias_distribuir_imagens_padrao(array $noticias): array
{
    $imagens = fincontrol_noticias_imagens_padrao();
    $indice = 0;

    foreach ($noticias as &$noticia) {
        $imagem = (string) ($noticia['imagem'] ?? '');
        if ($imagem === '' || fincontrol_noticias_eh_imagem_padrao($imagem)) {
            $noticia['imagem'] = $imagens[$indice % count($imagens)];
            $indice++;
        }
    }
    unset($noticia);

    return $noticias;
}

function fincontrol_noticias_atualizar_cache(int $limiteCache = 80): array
{
    $auditoria = [];
    $noticias = fincontrol_noticias_coletar($auditoria);
    fincontrol_noticias_salvar_auditoria($auditoria);

    if (!$noticias) {
        return [];
    }

    $noticias = array_slice($noticias, 0, $limiteCache);
    $noticias = fincontrol_noticias_distribuir_imagens_padrao($noticias);
    fincontrol_noticias_salvar_cache(fincontrol_noticias_cache_arquivo(), $noticias);
    fincontrol_noticias_salvar_historico($noticias);

    return $noticias;
}

function fincontrol_noticias_coletar(array &$auditoria = []): array
{
    $noticias = [];
    foreach (fincontrol_noticias_fontes() as $fonte) {
        $noticias = array_merge($noticias, fincontrol_ler_feed_rss($fonte, $auditoria));
    }

    if (!$noticias) {
        return [];
    }

    usort($noticias, function ($a, $b) {
        if ($a['score'] === $b['score']) {
            return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
        }
        return $b['score'] <=> $a['score'];
    });

    $noticias = fincontrol_noticias_unicas($noticias);
    return fincontrol_noticias_balancear_por_tema($noticias);
}

function fincontrol_ler_feed_rss(array $fonte, array &$auditoria = []): array
{
    if (!function_exists('simplexml_load_string')) {
        return [];
    }

    $xmlTexto = fincontrol_noticias_http_get($fonte['url']);
    if (!$xmlTexto) {
        return [];
    }

    $anterior = libxml_use_internal_errors(true);
    $xml = simplexml_load_string($xmlTexto, 'SimpleXMLElement', LIBXML_NOCDATA);
    libxml_clear_errors();
    libxml_use_internal_errors($anterior);

    if (!$xml) {
        return [];
    }

    $items = [];
    if (isset($xml->channel->item)) {
        $items = $xml->channel->item;
    } elseif (isset($xml->entry)) {
        $items = $xml->entry;
    }

    $noticias = [];
    $lidos = 0;
    $limiteItens = (int) ($fonte['limite_itens'] ?? 12);
    foreach ($items as $item) {
        if (isset($GLOBALS['noticias_deadline']) && microtime(true) >= $GLOBALS['noticias_deadline']) {
            break;
        }
        $lidos++;
        if ($lidos > $limiteItens) {
            break;
        }

        $titulo = fincontrol_limpar_texto((string) ($item->title ?? ''));
        if ($titulo === '') {
            continue;
        }

        $descricaoHtml = (string) ($item->description ?? $item->summary ?? '');
        $descricao = fincontrol_resumir_texto(fincontrol_limpar_texto(strip_tags($descricaoHtml)), 118);
        $link = fincontrol_noticias_link($item);
        $timestamp = fincontrol_noticias_data($item);
        $imagem = fincontrol_noticias_imagem($item, $descricaoHtml) ?: $fonte['imagem'];
        $validacao = fincontrol_noticias_validar_origem($titulo, $link, $timestamp, $fonte);

        $auditoria[] = [
            'status' => $validacao['ok'] ? 'validada' : 'rejeitada',
            'motivo' => $validacao['motivo'],
            'fonte' => $fonte['nome'],
            'titulo' => $titulo,
            'link_rss' => $link,
            'link_validado' => $validacao['link_final'] ?? '',
            'host_validado' => $validacao['host'] ?? '',
            'publicada_em' => date('c', $timestamp),
            'capturada_em' => date('c'),
        ];

        if (!$validacao['ok']) {
            continue;
        }

        $base = [
            'tema' => $fonte['tema'] ?? 'financas',
            'titulo' => $titulo,
            'resumo' => $descricao ?: 'Veja os principais pontos desta notícia.',
            'fonte' => $fonte['nome'],
            'link' => ($validacao['link_final'] ?? '') ?: $link,
            'imagem' => $imagem,
            'timestamp' => $timestamp,
            'tempo' => fincontrol_noticias_tempo($timestamp),
            'validado_em' => date('c'),
        ];

        $base['score'] = fincontrol_noticias_score($base, (int) $fonte['peso']);
        $noticias[] = $base;
    }

    return $noticias;
}

function fincontrol_noticias_http_get(string $url): string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'FinControle/1.0 (+https://fincontrole.com.br)',
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($status >= 200 && $status < 300 && is_string($body)) {
            return $body;
        }
        return '';
    }

    $contexto = stream_context_create([
        'http' => [
            'timeout' => 8,
            'header' => "User-Agent: FinControle/1.0 (+https://fincontrole.com.br)\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $body = @file_get_contents($url, false, $contexto);
    if (is_string($body) && $body !== '') {
        return $body;
    }

    if (function_exists('shell_exec')) {
        $bin = stripos(PHP_OS_FAMILY, 'Windows') === 0 ? 'curl.exe' : 'curl';
        $cmd = $bin . ' -L -s --max-time 8 -A ' . escapeshellarg('FinControle/1.0 (+https://fincontrole.com.br)') . ' ' . escapeshellarg($url);
        $saida = @shell_exec($cmd);
        if (is_string($saida) && trim($saida) !== '') {
            return $saida;
        }
    }

    return '';
}

function fincontrol_noticias_validar_origem(string $titulo, string $link, int $timestamp, array $fonte): array
{
    $link = trim($link);
    if ($link === '' || !filter_var($link, FILTER_VALIDATE_URL)) {
        return ['ok' => false, 'motivo' => 'link_invalido'];
    }

    if ($timestamp <= 0 || $timestamp > time() + 300) {
        return ['ok' => false, 'motivo' => 'data_invalida'];
    }
    $idadeHoras = max(0, (time() - $timestamp) / 3600);
    $maxIdade = (int) ($fonte['max_idade_horas'] ?? 72);
    if ($idadeHoras > $maxIdade) {
        return ['ok' => false, 'motivo' => 'noticia_fora_da_janela', 'link_final' => $link];
    }

    $host = fincontrol_noticias_host($link);
    if (!fincontrol_noticias_host_permitido($host, $fonte['hosts'] ?? [])) {
        return ['ok' => false, 'motivo' => 'dominio_nao_oficial', 'host' => $host, 'link_final' => $link];
    }

    $pagina = fincontrol_noticias_http_get_validado($link);
    if (!$pagina['ok']) {
        return [
            'ok' => false,
            'motivo' => 'pagina_indisponivel',
            'host' => $host,
            'link_final' => $pagina['url_final'] ?? $link,
        ];
    }

    $linkFinal = $pagina['url_final'] ?: $link;
    $hostFinal = fincontrol_noticias_host($linkFinal);
    if (!fincontrol_noticias_host_permitido($hostFinal, $fonte['hosts'] ?? [])) {
        return [
            'ok' => false,
            'motivo' => 'redirecionou_para_dominio_nao_oficial',
            'host' => $hostFinal,
            'link_final' => $linkFinal,
        ];
    }

    if (!fincontrol_noticias_titulo_confere($titulo, $pagina['body'])) {
        return [
            'ok' => false,
            'motivo' => 'titulo_nao_confirmado_na_pagina',
            'host' => $hostFinal,
            'link_final' => $linkFinal,
        ];
    }

    return ['ok' => true, 'motivo' => 'ok', 'host' => $hostFinal, 'link_final' => $linkFinal];
}

function fincontrol_noticias_http_get_validado(string $url): array
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 6,
            CURLOPT_ENCODING => '',
            CURLOPT_USERAGENT => 'FinControle/1.0 (+https://fincontrole.com.br)',
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $urlFinal = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        return [
            'ok' => $status >= 200 && $status < 300 && is_string($body) && trim($body) !== '',
            'body' => is_string($body) ? $body : '',
            'url_final' => $urlFinal ?: $url,
            'status' => $status,
        ];
    }

    $body = fincontrol_noticias_http_get($url);
    return [
        'ok' => trim($body) !== '',
        'body' => $body,
        'url_final' => $url,
        'status' => trim($body) !== '' ? 200 : 0,
    ];
}

function fincontrol_noticias_host(string $url): string
{
    return strtolower((string) parse_url($url, PHP_URL_HOST));
}

function fincontrol_noticias_host_permitido(string $host, array $permitidos): bool
{
    foreach ($permitidos as $permitido) {
        $permitido = strtolower((string) $permitido);
        if ($host === $permitido || str_ends_with($host, '.' . $permitido)) {
            return true;
        }
    }

    return false;
}

function fincontrol_noticias_titulo_confere(string $titulo, string $html): bool
{
    $tituloBase = fincontrol_noticias_normalizar_comparacao($titulo);
    if ($tituloBase === '') {
        return false;
    }

    $candidatos = [];
    if (preg_match_all('/<meta[^>]+(?:property|name)=["\'](?:og:title|twitter:title)["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)) {
        $candidatos = array_merge($candidatos, $m[1]);
    }
    if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
        $candidatos[] = $m[1];
    }
    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $html, $m)) {
        $candidatos[] = $m[1];
    }

    foreach ($candidatos as $candidato) {
        $comparar = fincontrol_noticias_normalizar_comparacao($candidato);
        if ($comparar === '') {
            continue;
        }
        if (str_contains($comparar, $tituloBase) || str_contains($tituloBase, $comparar)) {
            return true;
        }

        similar_text($tituloBase, $comparar, $percentual);
        if ($percentual >= 62) {
            return true;
        }
    }

    $trecho = substr($tituloBase, 0, 80);
    return $trecho !== '' && str_contains(fincontrol_noticias_normalizar_comparacao($html), $trecho);
}

function fincontrol_noticias_normalizar_comparacao(string $texto): string
{
    $texto = html_entity_decode(strip_tags($texto), ENT_QUOTES, 'UTF-8');
    if (function_exists('iconv')) {
        $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        if (is_string($convertido)) {
            $texto = $convertido;
        }
    }
    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9]+/', ' ', $texto);
    return trim((string) preg_replace('/\s+/', ' ', (string) $texto));
}

function fincontrol_noticias_salvar_auditoria(array $auditoria): void
{
    $arquivo = fincontrol_noticias_auditoria_arquivo();
    $dir = dirname($arquivo);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $dados = [
        'gerado_em' => date('c'),
        'total' => count($auditoria),
        'itens' => $auditoria,
    ];
    $json = json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json !== false) {
        $temporario = tempnam($dir, 'noticias-');
        if ($temporario === false || file_put_contents($temporario, $json, LOCK_EX) === false) {
            throw new RuntimeException('Nao foi possivel gravar as noticias.');
        }
        if (!rename($temporario, $arquivo)) {
            @unlink($temporario);
            throw new RuntimeException('Nao foi possivel publicar as noticias.');
        }
    }
}

function fincontrol_noticias_link(SimpleXMLElement $item): string
{
    if (isset($item->link)) {
        $candidatos = [];

        foreach ($item->link as $linkNode) {
            $attrs = $linkNode->attributes();
            $href = trim((string) ($attrs['href'] ?? ''));
            $rel = strtolower(trim((string) ($attrs['rel'] ?? '')));
            $type = strtolower(trim((string) ($attrs['type'] ?? '')));
            $texto = trim((string) $linkNode);

            if ($href !== '') {
                $prioridade = ($rel === 'alternate' || $rel === '') && !str_contains($type, 'image') ? 0 : 1;
                $candidatos[] = ['url' => $href, 'prioridade' => $prioridade];
            }

            if ($texto !== '') {
                $candidatos[] = ['url' => $texto, 'prioridade' => 1];
            }
        }

        usort($candidatos, fn($a, $b) => $a['prioridade'] <=> $b['prioridade']);

        foreach ($candidatos as $candidato) {
            $url = trim((string) $candidato['url']);
            if ($url !== '' && !fincontrol_noticias_url_imagem($url)) {
                return $url;
            }
        }
    }

    if (isset($item->guid)) {
        $guid = trim((string) $item->guid);
        if (filter_var($guid, FILTER_VALIDATE_URL) && !fincontrol_noticias_url_imagem($guid)) {
            return $guid;
        }
    }

    return '';
}

function fincontrol_noticias_data(SimpleXMLElement $item): int
{
    $candidatos = [
        (string) ($item->pubDate ?? ''),
        (string) ($item->published ?? ''),
        (string) ($item->updated ?? ''),
    ];

    $dc = $item->children('http://purl.org/dc/elements/1.1/');
    if (isset($dc->date)) {
        $candidatos[] = (string) $dc->date;
    }

    foreach ($candidatos as $data) {
        $ts = strtotime($data);
        if ($ts) {
            return $ts;
        }
    }

    return 0;
}

function fincontrol_noticias_imagem(SimpleXMLElement $item, string $html): string
{
    if (isset($item->enclosure)) {
        foreach ($item->enclosure as $enclosure) {
            $attrs = $enclosure->attributes();
            $type = strtolower((string) ($attrs['type'] ?? ''));
            if (isset($attrs['url']) && (!$type || strpos($type, 'image') !== false)) {
                return (string) $attrs['url'];
            }
        }
    }

    $media = $item->children('http://search.yahoo.com/mrss/');
    foreach (['content', 'thumbnail'] as $tag) {
        if (isset($media->{$tag})) {
            foreach ($media->{$tag} as $node) {
                $attrs = $node->attributes();
                if (isset($attrs['url'])) {
                    return (string) $attrs['url'];
                }
            }
        }
    }

    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
        return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }

    return '';
}

function fincontrol_noticias_score(array $noticia, int $pesoFonte): int
{
    $texto = fincontrol_texto_minusculo(($noticia['titulo'] ?? '') . ' ' . ($noticia['resumo'] ?? ''));
    $score = max(0, $pesoFonte) * 6;

    $utilidadePublica = [
        'juros' => 18, 'selic' => 18, 'inflação' => 18, 'inflacao' => 18,
        'ipca' => 17, 'endividamento' => 17, 'inadimplência' => 17,
        'inadimplencia' => 17, 'cartão' => 16, 'cartao' => 16,
        'crédito' => 16, 'credito' => 16, 'orçamento' => 16,
        'orcamento' => 16, 'salário' => 15, 'salario' => 15,
        'benefício' => 15, 'beneficio' => 15, 'fgts' => 15, 'inss' => 15,
        'imposto' => 14, 'receita federal' => 14, 'pix' => 14,
        'golpe' => 14, 'fraude' => 14, 'conta de luz' => 13,
        'energia' => 13, 'alimentação' => 13, 'alimentacao' => 13,
        'famílias' => 13, 'familias' => 13, 'dívida' => 13,
        'divida' => 13, 'renda' => 13,
    ];

    $impactoNoBolso = [
        'planejamento' => 11, 'educação financeira' => 11, 'educacao financeira' => 11,
        'poupar' => 11, 'economizar' => 11, 'consumo' => 10, 'empréstimo' => 10,
        'emprestimo' => 10, 'parcelas' => 10, 'financiamento' => 10,
        'renegociação' => 10, 'renegociacao' => 10, 'desconto' => 9,
        'preço' => 9, 'preco' => 9, 'tarifa' => 9, 'taxa' => 9,
    ];

    $mercadoUtil = [
        'dólar' => 6, 'dolar' => 6, 'câmbio' => 6, 'cambio' => 6,
        'banco' => 6, 'boleto' => 6, 'fintech' => 5,
    ];

    $distante = [
        'bolsa', 'ações', 'acoes', 'bitcoin', 'cripto', 'balanço',
        'balanco', 'wall street', 'ibovespa', 'fed', 'techs', 'payroll',
        'lucro', 'prejuízo', 'prejuizo', 'trimestre', '2t', '3t', '4t',
        'ebitda', 'dividendos', 'recomenda', 'preço-alvo', 'preco-alvo',
        'quina', 'mega-sena', 'megasena', 'loteria', 'loterias',
        'resultado da quina', 'resultado da mega',
    ];

    foreach ([$utilidadePublica, $impactoNoBolso, $mercadoUtil] as $grupo) {
        foreach ($grupo as $palavra => $peso) {
            if (fincontrol_texto_contem($texto, $palavra)) {
                $score += $peso;
            }
        }
    }

    foreach ($distante as $palavra) {
        if (fincontrol_texto_contem($texto, $palavra)) {
            $score -= 8;
        }
    }

    $idadeHoras = max(0, (time() - (int) ($noticia['timestamp'] ?? time())) / 3600);
    if ($idadeHoras <= 6) {
        $score += 12;
    } elseif ($idadeHoras <= 24) {
        $score += 8;
    } elseif ($idadeHoras <= 72) {
        $score += 4;
    } else {
        $score -= 4;
    }

    if (preg_match('/\b(como|entenda|veja|saiba|guia|dicas|aprenda)\b/iu', $texto)) {
        $score += 6;
    }

    if (preg_match('/\b(hoje|amanhã|amanha|prazo|calendário|calendario|liberado|mudança|mudanca|nova regra)\b/iu', $texto)) {
        $score += 6;
    }

    return max(0, min(100, $score));
}

function fincontrol_noticias_tem_utilidade_publica(array $noticia): bool
{
    $texto = fincontrol_texto_minusculo(($noticia['titulo'] ?? '') . ' ' . ($noticia['resumo'] ?? ''));
    $palavras = [
        'juros', 'selic', 'inflação', 'inflacao', 'ipca', 'endividamento',
        'inadimplência', 'inadimplencia', 'cartão', 'cartao', 'crédito',
        'credito', 'orçamento', 'orcamento', 'salário', 'salario', 'renda',
        'conta', 'energia', 'alimentação', 'alimentacao', 'famílias',
        'familias', 'dívida', 'divida', 'planejamento', 'educação financeira',
        'educacao financeira', 'poupar', 'economizar', 'consumo', 'empréstimo',
        'emprestimo', 'parcelas', 'financiamento', 'pix', 'boleto', 'banco',
        'dólar', 'dolar', 'câmbio', 'cambio',
    ];

    foreach ($palavras as $palavra) {
        if (fincontrol_texto_contem($texto, $palavra)) {
            return true;
        }
    }

    return false;
}

function fincontrol_noticias_keywords_por_tema(string $tema): array
{
    $keywords = [
        'financas' => [
            'juros', 'selic', 'inflação', 'inflacao', 'ipca', 'endividamento',
            'inadimplência', 'inadimplencia', 'cartão', 'cartao', 'crédito',
            'credito', 'orçamento', 'orcamento', 'salário', 'salario', 'renda',
            'dívida', 'divida', 'poupar', 'economizar', 'financiamento',
            'pix', 'boleto', 'banco', 'dólar', 'dolar',
        ],
        'tecnologia' => [
            'tecnologia', 'internet', 'inteligência artificial', 'inteligencia artificial',
            'ia', 'dados', 'segurança', 'seguranca', 'golpe', 'celular', 'aplicativo',
            'software', 'digital', 'inovação', 'inovacao',
        ],
        'gestao' => [
            'gestão', 'gestao', 'projeto', 'projetos', 'produtividade', 'processo',
            'processos', 'empresa', 'negócio', 'negocio', 'cliente', 'clientes',
            'vendas', 'liderança', 'lideranca', 'estratégia', 'estrategia',
            'crm', 'software', 'time', 'equipes', 'indicador', 'indicadores',
            'kpi', 'dashboard', 'produto', 'produtos', 'ia',
        ],
        'engenharia' => [
            'engenharia', 'obra', 'construção', 'construcao', 'infraestrutura',
            'energia', 'indústria', 'industria', 'tecnologia', 'inovação',
            'inovacao', 'sustentabilidade',
        ],
    ];

    return $keywords[$tema] ?? [];
}

function fincontrol_noticias_relevante_para_tema(array $noticia): bool
{
    $tema = (string) ($noticia['tema'] ?? 'financas');
    if (in_array($tema, ['carreira', 'treinos', 'saude'], true)) return true;
    if ($tema === 'financas') {
        return fincontrol_noticias_tem_utilidade_publica($noticia);
    }

    $texto = fincontrol_texto_minusculo(($noticia['titulo'] ?? '') . ' ' . ($noticia['resumo'] ?? ''));
    foreach (fincontrol_noticias_keywords_por_tema($tema) as $palavra) {
        if (fincontrol_texto_contem($texto, $palavra)) {
            return true;
        }
    }

    return false;
}

function fincontrol_noticias_balancear_por_tema(array $noticias): array
{
    $porTema = array_fill_keys(array_keys(fincontrol_noticias_categorias()), []);

    foreach ($noticias as $noticia) {
        $tema = (string) ($noticia['tema'] ?? 'financas');
        if (!isset($porTema[$tema])) {
            $porTema[$tema] = [];
        }
        if (fincontrol_noticias_relevante_para_tema($noticia)) {
            $porTema[$tema][] = $noticia;
        }
    }

    $ordenar = function (array &$lista): void {
        usort($lista, function ($a, $b) {
            return (($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0))
                ?: (($b['score'] ?? 0) <=> ($a['score'] ?? 0));
        });
    };

    foreach ($porTema as &$listaTema) {
        $ordenar($listaTema);
    }
    unset($listaTema);

    $balanceadas = [];
    $linksSelecionados = [];
    foreach (array_keys(fincontrol_noticias_categorias()) as $tema) {
        $fontesUsadas = [];
        $selecionadasTema = [];

        foreach ($porTema[$tema] ?? [] as $noticia) {
            $fonte = (string) ($noticia['fonte'] ?? '');
            if ($fonte === '' || isset($fontesUsadas[$fonte])) {
                continue;
            }

            $selecionadasTema[] = $noticia;
            $fontesUsadas[$fonte] = true;
            $linksSelecionados[(string) ($noticia['link'] ?? md5((string) ($noticia['titulo'] ?? '')))] = true;

            if (count($selecionadasTema) >= 3) {
                break;
            }
        }

        if (count($selecionadasTema) < 3) {
            foreach ($porTema[$tema] ?? [] as $noticia) {
                $chave = (string) ($noticia['link'] ?? md5((string) ($noticia['titulo'] ?? '')));
                if (isset($linksSelecionados[$chave])) {
                    continue;
                }

                $selecionadasTema[] = $noticia;
                $linksSelecionados[$chave] = true;

                if (count($selecionadasTema) >= 3) {
                    break;
                }
            }
        }

        $balanceadas = array_merge($balanceadas, $selecionadasTema);
    }

    foreach ($porTema as $listaTema) {
        foreach ($listaTema as $noticia) {
            $chave = (string) ($noticia['link'] ?? md5((string) ($noticia['titulo'] ?? '')));
            if (isset($linksSelecionados[$chave])) {
                continue;
            }

            $balanceadas[] = $noticia;
            $linksSelecionados[$chave] = true;
        }
    }

    return fincontrol_noticias_unicas($balanceadas);
}

function fincontrol_noticias_unicas(array $noticias): array
{
    $vistos = [];
    $unicas = [];

    foreach ($noticias as $noticia) {
        $chave = md5(fincontrol_texto_minusculo(($noticia['titulo'] ?? '') . '|' . ($noticia['link'] ?? '')));
        if (isset($vistos[$chave])) {
            continue;
        }
        $vistos[$chave] = true;
        $unicas[] = $noticia;
    }

    return $unicas;
}

function fincontrol_noticias_salvar_cache(string $arquivo, array $noticias): void
{
    $dir = dirname($arquivo);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $json = json_encode($noticias, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json !== false) {
        @file_put_contents($arquivo, $json);
    }
}

function fincontrol_noticias_salvar_historico(array $noticias): void
{
    $arquivo = fincontrol_noticias_historico_arquivo();
    $existentes = [];

    if (is_file($arquivo)) {
        $dados = json_decode((string) file_get_contents($arquivo), true);
        if (is_array($dados)) {
            $existentes = $dados;
        }
    }

    $porLink = [];
    foreach (array_merge($existentes, $noticias) as $noticia) {
        $link = trim((string) ($noticia['link'] ?? ''));
        if ($link === '') {
            continue;
        }
        $porLink[$link] = $noticia;
    }

    $historico = array_values($porLink);
    usort($historico, function ($a, $b) {
        return ($b['timestamp'] ?? 0) <=> ($a['timestamp'] ?? 0);
    });

    fincontrol_noticias_salvar_cache($arquivo, $historico);
}

function fincontrol_noticias_tempo(int $timestamp): string
{
    $diff = max(0, time() - $timestamp);
    if ($diff < 3600) {
        return 'Agora';
    }
    if ($diff < 86400) {
        $horas = max(1, (int) floor($diff / 3600));
        return 'Há ' . $horas . ' hora' . ($horas > 1 ? 's' : '');
    }
    $dias = max(1, (int) floor($diff / 86400));
    return $dias === 1 ? 'Ontem' : 'Há ' . $dias . ' dias';
}

function fincontrol_limpar_texto(string $texto): string
{
    $texto = html_entity_decode($texto, ENT_QUOTES, 'UTF-8');
    $texto = preg_replace('/\s+/u', ' ', $texto);
    return trim((string) $texto);
}

function fincontrol_resumir_texto(string $texto, int $limite): string
{
    if (fincontrol_texto_tamanho($texto) <= $limite) {
        return $texto;
    }

    return rtrim(fincontrol_texto_cortar($texto, 0, $limite - 1)) . '…';
}

function fincontrol_texto_minusculo(string $texto): string
{
    return function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);
}

function fincontrol_texto_contem(string $texto, string $busca): bool
{
    if (function_exists('mb_strpos')) {
        return mb_strpos($texto, $busca, 0, 'UTF-8') !== false;
    }

    return strpos($texto, $busca) !== false;
}

function fincontrol_texto_tamanho(string $texto): int
{
    return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
}

function fincontrol_texto_cortar(string $texto, int $inicio, int $tamanho): string
{
    return function_exists('mb_substr') ? mb_substr($texto, $inicio, $tamanho, 'UTF-8') : substr($texto, $inicio, $tamanho);
}

function fincontrol_noticias_fallback(): array
{
    return [
        [
            'titulo' => 'Selic segue em atenção e crédito fica mais caro',
            'resumo' => 'Entenda como isso afeta cartão, parcelas e organização do mês.',
            'fonte' => 'FinControle',
            'link' => '01.home.php#noticias',
            'imagem' => 'img/news-selic.jpg',
            'timestamp' => time(),
            'tempo' => 'Hoje',
            'score' => 100,
        ],
        [
            'titulo' => 'Dólar fecha em queda e alivia compras internacionais',
            'resumo' => 'Movimento ajuda quem compra fora ou acompanha preços importados.',
            'fonte' => 'FinControle',
            'link' => '01.home.php#noticias',
            'imagem' => 'img/news-dollar.jpg',
            'timestamp' => time() - 7200,
            'tempo' => 'Há 2 horas',
            'score' => 80,
        ],
        [
            'titulo' => 'Educação financeira: como organizar parcelas do cartão',
            'resumo' => 'Veja uma forma simples de controlar compras parceladas.',
            'fonte' => 'FinControle',
            'link' => '01.home.php#noticias',
            'imagem' => 'img/news-card.jpg',
            'timestamp' => time() - 14400,
            'tempo' => 'Há 4 horas',
            'score' => 75,
        ],
        [
            'titulo' => 'Planejamento do mês ajuda a reduzir gastos invisíveis',
            'resumo' => 'Pequenos ajustes na rotina podem melhorar o saldo do mês.',
            'fonte' => 'FinControle',
            'link' => '01.home.php#noticias',
            'imagem' => 'img/news-planning.jpg',
            'timestamp' => time() - 21600,
            'tempo' => 'Hoje',
            'score' => 70,
        ],
    ];
}

function fincontrol_noticias_busca_texto(string $texto): string
{
    $texto = html_entity_decode(strip_tags($texto), ENT_QUOTES, 'UTF-8');
    $texto = strtr($texto, ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a','Á'=>'A','À'=>'A','Â'=>'A','Ã'=>'A','Ä'=>'A',
        'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E','í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
        'Í'=>'I','Ì'=>'I','Î'=>'I','Ï'=>'I','ó'=>'o','ò'=>'o','ô'=>'o','õ'=>'o','ö'=>'o','Ó'=>'O','Ò'=>'O','Ô'=>'O','Õ'=>'O','Ö'=>'O',
        'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u','Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U','ç'=>'c','Ç'=>'C']);
    if (function_exists('iconv')) {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        if ($ascii !== false) $texto = $ascii;
    }
    return function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);
}

function fincontrol_noticias_pesquisar(array $noticias, string $termo): array
{
    $termo = fincontrol_noticias_busca_texto(trim($termo));
    if ($termo === '') return $noticias;
    return array_values(array_filter($noticias, static function ($noticia) use ($termo) {
        $texto = implode(' ', array_map(static function ($campo) use ($noticia) {
            return (string) ($noticia[$campo] ?? '');
        }, ['titulo', 'resumo', 'fonte', 'tema', 'tema_rotulo']));
        return strpos(fincontrol_noticias_busca_texto($texto), $termo) !== false;
    }));
}

