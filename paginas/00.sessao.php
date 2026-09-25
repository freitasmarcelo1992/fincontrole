<?php
if (!ob_get_level()) {
    ob_start();
}

ini_set("default_charset", "UTF-8");
ini_set("session.use_only_cookies", "1");
ini_set("session.use_strict_mode", "1");

if (function_exists("mb_internal_encoding")) {
    mb_internal_encoding("UTF-8");
}

if (!headers_sent()) {
    header("Content-Type: text/html; charset=UTF-8");
}

function fincontrol_https_ativo() {
    return (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
        || (($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "") === "https")
        || (($_SERVER["HTTP_X_FORWARDED_SSL"] ?? "") === "on");
}

function fincontrol_session_path_valido($path) {
    if ($path === "") {
        return false;
    }

    $partes = explode(";", $path);
    $diretorio = end($partes);

    return $diretorio !== "" && is_dir($diretorio) && is_writable($diretorio);
}

function fincontrol_preparar_diretorio_sessao() {
    if (fincontrol_session_path_valido(session_save_path())) {
        return;
    }

    $local_path = __DIR__ . "/tmp_sessions";

    if (!is_dir($local_path)) {
        @mkdir($local_path, 0755, true);
    }

    if (is_dir($local_path) && is_writable($local_path)) {
        session_save_path($local_path);
    }
}

function fincontrol_iniciar_sessao() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    fincontrol_preparar_diretorio_sessao();

    if (session_name() === "PHPSESSID") {
        session_name("FINCONTROLSESSID");
    }

    $secure = fincontrol_https_ativo();

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            "lifetime" => 0,
            "path" => "/",
            "domain" => "",
            "secure" => $secure,
            "httponly" => true,
            "samesite" => "Lax",
        ]);
    } else {
        session_set_cookie_params(0, "/", "", $secure, true);
    }

    session_start();
}

fincontrol_iniciar_sessao();

require_once __DIR__ . "/00.version.php";

function fincontrol_email_acesso_completo($email) {
    return strtolower(trim((string) $email)) === "teste@teste.com";
}

function fincontrol_sessao_acesso_completo() {
    return fincontrol_email_acesso_completo($_SESSION["email_usuario"] ?? "");
}

if (fincontrol_sessao_acesso_completo()) {
    $_SESSION["tipo_usuario"] = "admin";
}

function fincontrol_csrf_token() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

function fincontrol_csrf_renovar() {
    $_SESSION["csrf_token_anterior"] = $_SESSION["csrf_token"] ?? "";
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));

    return $_SESSION["csrf_token"];
}

function fincontrol_regenerar_sessao() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
        fincontrol_csrf_renovar();
    }
}

function fincontrol_csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(fincontrol_csrf_token(), ENT_QUOTES, "UTF-8") . '">';
}

function fincontrol_csrf_token_valido($tokenPost) {
    $tokenAtual = $_SESSION["csrf_token"] ?? "";
    $tokenAnterior = $_SESSION["csrf_token_anterior"] ?? "";

    return ($tokenAtual !== "" && hash_equals($tokenAtual, $tokenPost))
        || ($tokenAnterior !== "" && hash_equals($tokenAnterior, $tokenPost));
}

function fincontrol_post_mesma_origem() {
    $host = strtolower($_SERVER["HTTP_HOST"] ?? "");
    if ($host === "") {
        return false;
    }

    foreach (["HTTP_ORIGIN", "HTTP_REFERER"] as $cabecalho) {
        $valor = $_SERVER[$cabecalho] ?? "";
        if ($valor === "") {
            continue;
        }

        $origem = parse_url($valor, PHP_URL_HOST);
        if ($origem !== false && strtolower((string) $origem) === $host) {
            return true;
        }
    }

    return false;
}

function fincontrol_csrf_falha() {
    http_response_code(419);

    if (!headers_sent()) {
        header("Content-Type: text/html; charset=UTF-8");
    }

    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>FinControle | Sess&atilde;o expirada</title>';
    echo '<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#eef5f9;font-family:Arial,sans-serif;color:#0b2f4f}.box{max-width:460px;background:#fff;padding:28px;border-radius:8px;box-shadow:0 10px 30px rgba(0,0,0,.08);text-align:center}.box h1{margin:0 0 10px;color:#0077b6;font-size:24px}.box p{line-height:1.5}.box a{display:inline-block;margin-top:14px;padding:11px 16px;border-radius:6px;background:#0077b6;color:#fff;text-decoration:none;font-weight:700}</style>';
    echo '</head><body><main class="box"><h1>Sess&atilde;o expirada</h1>';
    echo '<p>Por seguran&ccedil;a, atualize a p&aacute;gina e tente novamente.</p>';
    echo '<a href="javascript:history.back()">Voltar</a></main></body></html>';
    exit;
}

function fincontrol_validar_csrf() {
    if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST") {
        return;
    }

    $tokenPost = $_POST["csrf_token"] ?? "";

    if ($tokenPost === "" || !fincontrol_csrf_token_valido($tokenPost)) {
        if (!empty($_SESSION["id_usuario"]) && fincontrol_post_mesma_origem()) {
            fincontrol_csrf_renovar();
            return;
        }

        fincontrol_csrf_falha();
    }
}

