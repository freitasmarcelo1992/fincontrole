<?php
require_once "00.sessao.php";
require_once "09.conexao.php";

if (function_exists("fincontrol_login_persistente_revogar")) {
    fincontrol_login_persistente_revogar($conexao);
}

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

header("Location: 01.home.php");
exit();
?>
