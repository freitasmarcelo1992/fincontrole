<?php

function fincontrol_crypto_key() {
    $config_key = "";
    $arquivo_config = __DIR__ . "/00.crypto.config.php";

    if (file_exists($arquivo_config)) {
        $config = require $arquivo_config;

        if (is_array($config)) {
            $config_key = trim($config["key"] ?? "");
        }
    }

    $env_key = getenv("FINCONTROL_CRYPTO_KEY");
    $base_key = $config_key ?: $env_key;

    if ((getenv("FINCONTROL_ENV") ?: "") === "production" && !$base_key) {
        throw new RuntimeException("Configure a chave de criptografia em 00.crypto.config.php ou FINCONTROL_CRYPTO_KEY.");
    }

    if (!$base_key) {
        $base_key = "fincontrol-chave-local-trocar-em-producao-2026";
    }

    return hash("sha256", $base_key, true);
}

function fincontrol_valor_criptografado($valor) {
    return is_string($valor) && substr($valor, 0, 7) === "enc:v1:";
}

function criptografar_valor_financeiro($valor) {
    $texto = number_format((float)$valor, 2, ".", "");
    $iv = random_bytes(16);
    $cifrado = openssl_encrypt(
        $texto,
        "AES-256-CBC",
        fincontrol_crypto_key(),
        OPENSSL_RAW_DATA,
        $iv
    );

    if ($cifrado === false) {
        throw new RuntimeException("Nao foi possivel criptografar o valor financeiro.");
    }

    return "enc:v1:" . base64_encode($iv . $cifrado);
}

function descriptografar_valor_financeiro($valor) {
    if ($valor === null || $valor === "") {
        return 0.0;
    }

    if (!fincontrol_valor_criptografado($valor)) {
        return (float)$valor;
    }

    $dados = base64_decode(substr($valor, 7), true);

    if ($dados === false || strlen($dados) <= 16) {
        return 0.0;
    }

    $iv = substr($dados, 0, 16);
    $cifrado = substr($dados, 16);
    $texto = openssl_decrypt(
        $cifrado,
        "AES-256-CBC",
        fincontrol_crypto_key(),
        OPENSSL_RAW_DATA,
        $iv
    );

    if ($texto === false) {
        return 0.0;
    }

    return (float)$texto;
}
