<?php
require_once "00.sessao.php";
require_once "09.conexao.php";

$home = "01.home.php";
$login = "02.login.php?origem=pwa";
$dashboard = "03.menu.php";

if (isset($_GET["logout"]) || isset($_GET["sair"])) {
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
}

$destinoFallback = !empty($_SESSION["id_usuario"]) ? $dashboard : $home;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php require_once "00.pwa.php"; ?>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="2; url=<?php echo $destinoFallback; ?>">
    <title>FinControle</title>
<?php echo function_exists("fincontrol_version_styles") ? fincontrol_version_styles() : ""; ?>
</head>
<body>
    <script>
    (function () {
        var usuarioLogado = <?php echo !empty($_SESSION["id_usuario"]) ? "true" : "false"; ?>;
        var modoApp = window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true;
        var destino = usuarioLogado
            ? "<?php echo $dashboard; ?>"
            : (modoApp ? "<?php echo $login; ?>" : "<?php echo $home; ?>");

        window.location.replace(destino);
    })();
    </script>
    <p>Redirecionando para o FinControle...</p>
    <p><a href="<?php echo $destinoFallback; ?>">Continuar</a></p>
<?php echo function_exists("fincontrol_version_badge") ? fincontrol_version_badge() : ""; ?>
</body>
</html>
<?php exit(); ?>
