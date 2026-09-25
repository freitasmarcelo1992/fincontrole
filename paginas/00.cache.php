<?php

const FINCONTROL_CACHE_TTL_DASHBOARD = 300;

function fincontrol_cache_diretorio() {
    $diretorio = dirname(__DIR__) . "/storage/cache";

    if (!is_dir($diretorio)) {
        @mkdir($diretorio, 0755, true);
    }

    return is_dir($diretorio) && is_writable($diretorio) ? $diretorio : null;
}

function fincontrol_cache_dashboard_chave($idUsuario, array $filtros) {
    return "dashboard_" . intval($idUsuario) . "_" . hash("sha256", json_encode($filtros));
}

function fincontrol_cache_arquivo($chave) {
    $diretorio = fincontrol_cache_diretorio();
    return $diretorio ? $diretorio . "/" . preg_replace('/[^a-zA-Z0-9_-]/', '', $chave) . ".cache" : null;
}

function fincontrol_cache_ler($chave, $ttl = FINCONTROL_CACHE_TTL_DASHBOARD) {
    $arquivo = fincontrol_cache_arquivo($chave);

    if (!$arquivo || !is_file($arquivo) || (time() - filemtime($arquivo)) >= $ttl) {
        return null;
    }

    $conteudo = @file_get_contents($arquivo);
    if ($conteudo === false) {
        return null;
    }

    $conteudoTrim = ltrim($conteudo);
    if ($conteudoTrim === "" || stripos($conteudoTrim, "<!DOCTYPE html") !== 0) {
        @unlink($arquivo);
        return null;
    }

    return $conteudo;
}

function fincontrol_cache_salvar($chave, $conteudo) {
    $arquivo = fincontrol_cache_arquivo($chave);
    if (!$arquivo) return false;

    if (ltrim((string)$conteudo) === "" || stripos(ltrim((string)$conteudo), "<!DOCTYPE html") !== 0) {
        return false;
    }

    $temporario = $arquivo . "." . uniqid("tmp_", true);
    if (@file_put_contents($temporario, $conteudo, LOCK_EX) === false) return false;

    return @rename($temporario, $arquivo);
}

function fincontrol_cache_invalidar_usuario($idUsuario) {
    $diretorio = fincontrol_cache_diretorio();
    if (!$diretorio) return;

    foreach (glob($diretorio . "/dashboard_" . intval($idUsuario) . "_*.cache") ?: [] as $arquivo) {
        @unlink($arquivo);
    }
}
