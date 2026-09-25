<?php
require_once "00.sessao.php";
require_once "09.conexao.php";
require_once "00.login_persistente.php";
require_once "00.analytics.php";

$mensagem = "";
fincontrol_validar_csrf();
$lembrarAcessoMarcado = $_SERVER["REQUEST_METHOD"] !== "POST" || isset($_POST["lembrar_acesso"]);

if ($_SERVER["REQUEST_METHOD"] !== "POST" && !empty($_SESSION["id_usuario"])) {
    header("Location: 03.menu.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email_usuario"];
    $senha = $_POST["senha_usuario"];
    $lembrarAcesso = isset($_POST["lembrar_acesso"]);

    if (!$conexao) {
        $mensagem = "Login temporariamente indisponível. Verifique a conexão com o banco de dados.";
    } else {

    /* BUSCAR USUÁRIO */
    $sql = "SELECT * FROM usuario 
            WHERE email_usuario = ?
            LIMIT 1";

    $stmt = $conexao->prepare($sql);

    $stmt->bind_param("s", $email);

    $stmt->execute();

    $resultado = $stmt->get_result();

    /* VERIFICAR SE USUÁRIO EXISTE */
    if ($resultado->num_rows == 1) {

        $usuario = $resultado->fetch_assoc();

        /* VALIDAR SENHA */
        if (password_verify($senha, $usuario["senha_usuario"])) {
            fincontrol_login_preencher_sessao($usuario);
            fincontrol_evento_registrar($conexao, intval($usuario["id_usuario"]), "login");

            if ($lembrarAcesso) {
                fincontrol_login_persistente_criar($conexao, intval($usuario["id_usuario"]));
            } else {
                fincontrol_login_persistente_revogar($conexao);
            }

            /* REDIRECIONAR */
            header("Location: 03.menu.php");

            exit();

        } else {

            $mensagem = "E-mail ou senha incorretos.";

        }

    } else {

        $mensagem = "E-mail ou senha incorretos.";

    }

    $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php require_once "00.pwa.php"; ?>

  <?php require_once "00.google_tag.php"; ?>
  <meta charset="UTF-8" />

  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>FinControle | Login</title>

  <link href="img/logo-FinControle.png" rel="icon" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <style>

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #0077b6, #00b4d8);
    }

    .login-container {
      display: flex;
      width: 950px;
      background: #fff;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .login-info {
      flex: 1;
      background: linear-gradient(180deg, #0077b6, #00b4d8);
      color: #fff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 60px 40px;
      text-align: center;
    }

    .login-info h1 {
      font-size: 34px;
      margin-bottom: 15px;
    }

    .login-info p {
      font-size: 15px;
      opacity: 0.9;
      max-width: 300px;
    }

    .login-info i {
      font-size: 60px;
      margin-bottom: 15px;
    }

    .login-form {
      flex: 1;
      padding: 60px 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background-color: #f4f7fb;
    }

    .app-login-brand {
      display: none;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      gap: 8px;
      margin: 0 auto 18px;
      color: #0077b6;
      font-size: 18px;
      font-weight: 800;
      line-height: 1;
    }

    .app-login-brand img {
      width: 64px;
      height: 64px;
      display: block;
      border-radius: 18px;
      object-fit: cover;
      box-shadow: 0 10px 24px rgba(0,119,182,.22);
    }

    .login-form h2 {
      color: #0077b6;
      margin-bottom: 35px;
      font-size: 26px;
      font-weight: 700;
      text-align: center;
    }

    .input-group {
      margin-bottom: 25px;
      position: relative;
    }

    .input-group i {
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
      color: #0077b6;
      font-size: 16px;
      width: 18px;
      line-height: 1;
      text-align: center;
      pointer-events: none;
    }

    .input-group input {
      width: 100%;
      padding: 12px 16px 12px 46px;
      border: 1px solid #ccd6dd;
      border-radius: 10px;
      background: #fff;
      font-size: 15px;
      min-height: 46px;
      line-height: 20px;
    }

    .input-group input::placeholder {
      line-height: 20px;
    }

    .input-group input:focus {
      outline: none;
      border-color: #00b4d8;
      box-shadow: 0 0 5px rgba(0,180,216,0.3);
    }

    .forgot {
      text-align: right;
      margin-bottom: 25px;
    }

    .remember-access {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin: -8px 0 20px;
      color: #425466;
      font-size: 13px;
      line-height: 1.35;
      cursor: pointer;
    }

    .remember-access input {
      width: 18px;
      height: 18px;
      margin-top: 1px;
      accent-color: #0077b6;
      flex: 0 0 auto;
    }

    .remember-access strong {
      display: block;
      color: #17314f;
      font-size: 14px;
    }

    .forgot a {
      text-decoration: none;
      color: #0077b6;
      font-size: 14px;
    }

    button {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #0077b6, #00b4d8);
      border: none;
      border-radius: 10px;
      color: #fff;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
    }

    button:hover {
      opacity: 0.9;
    }

    .register {
      text-align: center;
      margin-top: 25px;
      font-size: 14px;
      color: #555;
    }

    .register a {
      color: #0077b6;
      font-weight: 600;
      text-decoration: none;
    }

    .home-link {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      color: #0077b6;
      font-weight: 500;
      text-decoration: none;
      font-size: 14px;
      margin-top: 20px;
    }

    .alerta {
      background: #f8d7da;
      color: #842029;
      padding: 12px;
      border-radius: 10px;
      margin-bottom: 20px;
      text-align: center;
      font-size: 14px;
    }

    @media (max-width: 900px) {

      .login-container {
        flex-direction: column;
        width: 90%;
        max-width: 450px;
      }

      .login-info {
        display: none;
      }

      .login-form {
        padding: 50px 30px;
      }

      html.fc-standalone .app-login-brand {
        display: flex;
      }

      html.fc-standalone .login-form h2 {
        margin-bottom: 30px;
      }

      .input-group i {
        left: 14px;
        font-size: 15px;
        width: 18px;
      }

      .input-group input {
        padding-left: 44px;
      }

    }

    :root {
      --fc-bg: #061826;
      --fc-card: #0d2a42;
      --fc-line: rgba(87, 190, 255, .22);
      --fc-blue: #0087c7;
      --fc-cyan: #00b7e8;
      --fc-text: #ffffff;
      --fc-muted: #a9bdd0;
    }

    body {
      min-height: 100vh;
      height: auto;
      padding: 24px;
      background:
        radial-gradient(circle at 82% 2%, rgba(0, 183, 232, .18), transparent 34%),
        linear-gradient(180deg, #020b14 0%, var(--fc-bg) 52%, #020b14 100%);
      color: var(--fc-text);
    }

    .login-container {
      width: min(100%, 980px);
      background: linear-gradient(145deg, rgba(16, 47, 73, .96), rgba(7, 29, 47, .94));
      border: 1px solid var(--fc-line);
      border-radius: 24px;
      box-shadow: 0 24px 60px rgba(0, 0, 0, .34);
    }

    .login-info {
      background:
        radial-gradient(circle at 78% 18%, rgba(0, 183, 232, .20), transparent 30%),
        linear-gradient(160deg, rgba(0, 135, 199, .25), rgba(6, 24, 38, .35));
      padding: 56px 42px;
    }

    .login-info i {
      width: 72px;
      height: 72px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 20px;
      background: linear-gradient(135deg, var(--fc-cyan), var(--fc-blue));
      box-shadow: 0 16px 34px rgba(0, 183, 232, .24);
      font-size: 34px;
    }

    .login-info h1 {
      font-size: 36px;
      font-weight: 900;
    }

    .login-info p {
      color: #d5e1ec;
      line-height: 1.55;
    }

    .login-form {
      background: rgba(3, 18, 31, .38);
      padding: 50px;
    }

    .login-form h2,
    .app-login-brand {
      color: var(--fc-text);
    }

    .login-form h2 {
      font-size: 30px;
      font-weight: 900;
      margin-bottom: 26px;
    }

    .input-group input {
      min-height: 54px;
      border: 1px solid var(--fc-line);
      border-radius: 14px;
      background: rgba(16, 47, 73, .84);
      color: var(--fc-text);
      font-weight: 600;
    }

    .input-group input::placeholder {
      color: var(--fc-muted);
    }

    .input-group i,
    .forgot a,
    .register a,
    .home-link {
      color: var(--fc-cyan);
    }

    .input-group input:focus {
      border-color: var(--fc-cyan);
      box-shadow: 0 0 0 3px rgba(0, 183, 232, .14);
    }

    .remember-access {
      color: #d5e1ec;
      background: rgba(16, 47, 73, .55);
      border: 1px solid var(--fc-line);
      border-radius: 14px;
      padding: 12px;
    }

    .remember-access strong {
      color: var(--fc-text);
    }

    .remember-access input {
      accent-color: var(--fc-cyan);
    }

    button {
      min-height: 54px;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--fc-blue), var(--fc-cyan));
      font-weight: 900;
      box-shadow: 0 16px 34px rgba(0, 183, 232, .20);
    }

    .register {
      color: #d5e1ec;
    }

    .alerta {
      background: rgba(255, 82, 82, .12);
      color: #ffd6d6;
      border: 1px solid rgba(255, 82, 82, .28);
    }

    @media (max-width: 900px) {
      body {
        align-items: flex-start;
        padding: 18px;
      }

      .login-container {
        width: min(100%, 480px);
        border-radius: 22px;
      }

      .login-info {
        display: flex;
        padding: 30px 24px;
      }

      .login-info p {
        display: none;
      }

      .login-form {
        padding: 30px 24px;
      }
    }

    @media (max-width: 640px) {
      html,
      body {
        height: 100svh;
        min-height: 100svh;
        overflow: hidden;
      }

      body {
        align-items: center;
        justify-content: center;
        padding: 8px;
      }

      .login-container {
        width: min(100%, 420px);
        max-height: calc(100svh - 16px);
        border-radius: 20px;
        overflow: hidden;
      }

      .login-info {
        display: none;
      }

      .login-form {
        padding: 14px 18px 16px;
      }

      .app-login-brand {
        display: flex;
        margin-bottom: 8px;
        gap: 6px;
      }

      .app-login-brand img {
        width: 44px;
        height: 44px;
        border-radius: 14px;
      }

      .login-form h2 {
        font-size: 22px;
        margin-bottom: 14px;
      }

      .input-group {
        margin-bottom: 10px;
      }

      .input-group input {
        min-height: 42px;
        padding-top: 8px;
        padding-bottom: 8px;
        border-radius: 12px;
      }

      .remember-access {
        margin: 0 0 10px;
        padding: 8px 10px;
        font-size: 11px;
        line-height: 1.25;
      }

      .remember-access strong {
        font-size: 12px;
      }

      .forgot {
        margin-bottom: 8px;
        line-height: 1.1;
      }

      .forgot a {
        font-size: 12px;
      }

      button {
        min-height: 44px;
        border-radius: 12px;
      }

      .register {
        margin-top: 12px;
        font-size: 12px;
      }

      .register p {
        margin-top: 8px;
      }

      .fc-version-badge,
      .version-badge {
        display: none !important;
      }
    }

  </style>

<link rel="stylesheet" href="responsive.css?v=fit1">
<?php echo function_exists("fincontrol_version_styles") ? fincontrol_version_styles() : ""; ?>
</head>

<body>

  <div class="login-container">

    <!-- ESQUERDA -->
    <div class="login-info">

      <i class="fa-solid fa-wallet"></i>

      <h1>FinControle</h1>

      <p>
        Gerencie suas finanças com facilidade e tenha controle total
        sobre receitas e despesas.
      </p>

    </div>

    <!-- DIREITA -->
    <div class="login-form">

      <div class="app-login-brand" aria-label="FinControle">
        <img src="assets/img/icon-192.png" alt="">
        <span>FinControle</span>
      </div>

      <h2>Bem-vindo de volta</h2>

      <?php if (!empty($mensagem)) { ?>

        <div class="alerta">

          <?php echo $mensagem; ?>

        </div>

      <?php } ?>

      <form method="POST" action="">
        <?php echo fincontrol_csrf_input(); ?>

        <div class="input-group">

          <i class="fa-solid fa-envelope"></i>

          <input type="email"
                 name="email_usuario"
                 placeholder="E-mail"
                 required>

        </div>

        <div class="input-group">

          <i class="fa-solid fa-lock"></i>

          <input type="password"
                 name="senha_usuario"
                 placeholder="Senha"
                 required>

        </div>

        <label class="remember-access">
          <input type="checkbox" name="lembrar_acesso" value="1" <?php echo $lembrarAcessoMarcado ? "checked" : ""; ?>>
          <span><strong>Manter conectado neste aparelho</strong>Entre mais rápido nas próximas vezes, sem precisar digitar seus dados novamente.</span>
        </label>

        <div class="forgot">

          <a href="11.esqueci_senha.php">Esqueceu sua senha?</a>

        </div>

         <div class="forgot">

          <a href="12.alterar_senha.php">Mudar a Senha?</a>

        </div>

        <button type="submit">

          <i class="fa-solid fa-arrow-right-to-bracket"></i>

          Entrar

        </button>

        <div class="register">

          Não tem uma conta?

          <a href="06.cadastrar_usuario.php">

            Cadastre-se

          </a>

          <p>

            <a href="01.home.php" class="home-link">

              <i class="fa-solid fa-house"></i>

              Home

            </a>

          </p>

        </div>

      </form>

    </div>

  </div>

<?php echo function_exists("fincontrol_version_badge") ? fincontrol_version_badge() : ""; ?>
</body>

</html>

