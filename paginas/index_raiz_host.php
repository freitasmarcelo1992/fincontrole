<?php
header("Location: /paginas/01.home.php", true, 302);
exit;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php require_once "00.pwa.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="0; url=/paginas/01.home.php">
    <title>FinControle</title>
</head>
<body>
    <p>Redirecionando para o FinControle...</p>
    <p><a href="/paginas/01.home.php">Acessar o FinControle</a></p>
</body>
</html>
