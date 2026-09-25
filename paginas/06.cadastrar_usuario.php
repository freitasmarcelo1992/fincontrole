<?php
require_once "00.sessao.php";
require_once "09.conexao.php";

$mensagem = "";
$tipo_mensagem = "";
fincontrol_validar_csrf();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = trim($_POST["nome_usuario"]);
    $data = $_POST["dt_nasc_usuario"];
    $email = trim($_POST["email_usuario"]);
    $senha = $_POST["senha_usuario"];
    $confirmar = $_POST["confirmar_senha"];
    $aceite_termos = isset($_POST["aceite_termos"]);

    if (!$aceite_termos) {

        $mensagem = "Você precisa aceitar os Termos de Uso e a Política de Privacidade para criar sua conta.";
        $tipo_mensagem = "erro";

    } elseif ($senha != $confirmar) {

        $mensagem = "As senhas não coincidem.";
        $tipo_mensagem = "erro";

    } elseif (strlen($senha) < 6) {

        $mensagem = "A senha deve possuir no mínimo 6 caracteres.";
        $tipo_mensagem = "erro";

    } else {

        $sql_verifica = "SELECT id_usuario
                         FROM usuario
                         WHERE email_usuario = ?";

        $stmt_verifica = $conexao->prepare($sql_verifica);
        $stmt_verifica->bind_param("s", $email);
        $stmt_verifica->execute();

        $resultado_verifica = $stmt_verifica->get_result();

        if ($resultado_verifica->num_rows > 0) {

            $mensagem = "Este e-mail já está cadastrado.";
            $tipo_mensagem = "erro";

        } else {

            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuario
            (
                nome_usuario,
                dt_nasc_usuario,
                email_usuario,
                senha_usuario,
                tipo_usuario
            )
            VALUES
            (
                ?, ?, ?, ?, 'comum'
            )";

            $stmt = $conexao->prepare($sql);

            $stmt->bind_param(
                "ssss",
                $nome,
                $data,
                $email,
                $senha_hash
            );

            if ($stmt->execute()) {

                $mensagem = "Cadastro realizado com sucesso!";
                $tipo_mensagem = "sucesso";

            } else {

                $mensagem = "Erro ao cadastrar usuário.";
                $tipo_mensagem = "erro";

            }

            $stmt->close();
        }

        $stmt_verifica->close();
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

  <title>FinControle | Cadastro</title>

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
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: linear-gradient(180deg, #0077b6, #00b4d8);
      padding: 20px;
    }

    .container {
      display: flex;
      width: 950px;
      max-width: 100%;
      background: #fff;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    .info-side {
      flex: 1;
      background: linear-gradient(180deg, #0077b6, #00b4d8);
      color: #fff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 60px 40px;
    }

    .info-side i {
      font-size: 60px;
      margin-bottom: 15px;
    }

    .info-side h1 {
      font-size: 32px;
      margin-bottom: 10px;
    }

    .info-side p {
      font-size: 15px;
      opacity: 0.9;
      max-width: 300px;
      line-height: 1.5;
    }

    .form-side {
      flex: 1;
      background: #f4f7fb;
      padding: 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .form-side h2 {
      color: #0077b6;
      font-size: 26px;
      font-weight: 700;
      margin-bottom: 30px;
      text-align: center;
    }

    .input-group {
      position: relative;
      margin-bottom: 18px;
    }

    .input-group i {
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
      color: #008ADD;
      font-size: 16px;
    }

    .input-group input {
      width: 100%;
      padding: 12px 45px;
      border: 1px solid #ccd6dd;
      border-radius: 10px;
      background: #fff;
      font-size: 15px;
    }

    .input-group input:focus {
      outline: none;
      border-color: #008ADD;
      box-shadow: 0 0 5px rgba(0,138,221,0.3);
    }

    .input-group.date-field::after {
      content: attr(data-placeholder);
      position: absolute;
      top: 50%;
      left: 45px;
      transform: translateY(-50%);
      color: #777;
      font-size: 15px;
      pointer-events: none;
      background: #fff;
      padding-right: 6px;
    }

    .input-group.date-field:focus-within::after,
    .input-group.date-field:has(input:valid)::after {
      display: none;
    }

    .input-group.date-field input:required:invalid {
      color: transparent;
    }

    .input-group.date-field input:focus,
    .input-group.date-field input:valid {
      color: #333;
    }

    .terms-box {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      background: #fff;
      border: 1px solid #ccd6dd;
      border-radius: 10px;
      padding: 12px;
      margin-bottom: 18px;
      font-size: 13px;
      line-height: 1.4;
      color: #444;
    }

    .terms-box input {
      width: 18px;
      height: 18px;
      margin-top: 1px;
      accent-color: #0077b6;
      flex: 0 0 auto;
    }

    .terms-box a {
      color: #0077b6;
      font-weight: 700;
      text-decoration: none;
    }

    .terms-box a:hover {
      text-decoration: underline;
    }

    button {
      width: 100%;
      padding: 14px;
      background: linear-gradient(180deg, #0077b6, #00b4d8);
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

    button:disabled {
      opacity: 0.55;
      cursor: not-allowed;
    }

    .login-link {
      text-align: center;
      margin-top: 25px;
      font-size: 14px;
      color: #555;
    }

    .login-link a {
      color: #008ADD;
      font-weight: 600;
      text-decoration: none;
    }

    .home-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #0077b6;
      font-weight: 500;
      text-decoration: none;
      font-size: 14px;
      margin-top: 20px;
    }

    .mensagem {
      padding: 12px;
      border-radius: 10px;
      margin-bottom: 20px;
      text-align: center;
      font-size: 14px;
    }

    .sucesso {
      background: #d1e7dd;
      color: #0f5132;
    }

    .erro {
      background: #f8d7da;
      color: #842029;
    }

    @media (max-width: 900px) {

      .container {
        flex-direction: column;
        width: 90%;
        max-width: 450px;
      }

      .info-side {
        display: none;
      }

      .form-side {
        padding: 40px 28px;
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
      padding: 24px;
      background:
        radial-gradient(circle at 82% 2%, rgba(0, 183, 232, .18), transparent 34%),
        linear-gradient(180deg, #020b14 0%, var(--fc-bg) 52%, #020b14 100%);
      color: var(--fc-text);
    }

    .container {
      width: min(100%, 980px);
      background: linear-gradient(145deg, rgba(16, 47, 73, .96), rgba(7, 29, 47, .94));
      border: 1px solid var(--fc-line);
      border-radius: 24px;
      box-shadow: 0 24px 60px rgba(0, 0, 0, .34);
    }

    .info-side {
      background:
        radial-gradient(circle at 78% 18%, rgba(0, 183, 232, .20), transparent 30%),
        linear-gradient(160deg, rgba(0, 135, 199, .25), rgba(6, 24, 38, .35));
      padding: 56px 42px;
    }

    .info-side i {
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

    .info-side h1 {
      font-size: 36px;
      font-weight: 900;
    }

    .info-side p {
      color: #d5e1ec;
      line-height: 1.55;
    }

    .form-side {
      background: rgba(3, 18, 31, .38);
      padding: 48px;
    }

    .form-side h2 {
      color: var(--fc-text);
      font-size: 30px;
      font-weight: 900;
      margin-bottom: 24px;
    }

    .input-group input {
      min-height: 52px;
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
    .terms-box a,
    .login-link a,
    .home-link {
      color: var(--fc-cyan);
    }

    .input-group input:focus {
      border-color: var(--fc-cyan);
      box-shadow: 0 0 0 3px rgba(0, 183, 232, .14);
    }

    .input-group.date-field::after {
      background: transparent;
      color: var(--fc-muted);
    }

    .input-group.date-field input:focus,
    .input-group.date-field input:valid {
      color: var(--fc-text);
    }

    .terms-box {
      background: rgba(16, 47, 73, .55);
      border: 1px solid var(--fc-line);
      color: #d5e1ec;
      border-radius: 14px;
    }

    .terms-box input {
      accent-color: var(--fc-cyan);
    }

    button {
      min-height: 54px;
      border-radius: 14px;
      background: linear-gradient(135deg, var(--fc-blue), var(--fc-cyan));
      font-weight: 900;
      box-shadow: 0 16px 34px rgba(0, 183, 232, .20);
    }

    .login-link {
      color: #d5e1ec;
    }

    .mensagem.erro,
    .erro {
      background: rgba(255, 82, 82, .12);
      color: #ffd6d6;
      border: 1px solid rgba(255, 82, 82, .28);
    }

    .mensagem.sucesso,
    .sucesso {
      background: rgba(36, 229, 107, .12);
      color: #d8ffe5;
      border: 1px solid rgba(36, 229, 107, .28);
    }

    @media (max-width: 900px) {
      body {
        align-items: flex-start;
        padding: 18px;
      }

      .container {
        width: min(100%, 480px);
        border-radius: 22px;
      }

      .info-side {
        display: flex;
        padding: 30px 24px;
      }

      .info-side p {
        display: none;
      }

      .form-side {
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
        justify-content: center;
        padding: 8px;
      }

      .container {
        width: min(100%, 420px);
        max-height: calc(100svh - 16px);
        border-radius: 20px;
        overflow: hidden;
      }

      .info-side {
        display: none;
      }

      .form-side {
        padding: 12px 18px 14px;
      }

      .form-side h2 {
        font-size: 21px;
        margin-bottom: 12px;
      }

      .input-group {
        margin-bottom: 8px;
      }

      .input-group input {
        min-height: 38px;
        padding-top: 7px;
        padding-bottom: 7px;
        border-radius: 11px;
      }

      .input-group.date-field::after {
        font-size: 13px;
      }

      .terms-box {
        margin-bottom: 8px;
        padding: 8px 10px;
        font-size: 10.5px;
        line-height: 1.25;
        border-radius: 12px;
      }

      button {
        min-height: 42px;
        border-radius: 12px;
      }

      .login-link {
        margin-top: 9px;
        font-size: 12px;
        line-height: 1.25;
      }

      .login-link div {
        margin-top: 6px;
      }

      .mensagem {
        padding: 8px;
        margin-bottom: 8px;
        font-size: 11px;
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

  <div class="container">

    <div class="info-side">

      <i class="fa-solid fa-wallet"></i>

      <h1>FinControle</h1>

      <p>
        Gerencie suas finanças com facilidade e tenha controle total
        sobre suas receitas e despesas.
      </p>

    </div>

    <div class="form-side">

      <h2>Crie sua conta</h2>

      <?php if (!empty($mensagem)) { ?>

        <div class="mensagem <?php echo htmlspecialchars($tipo_mensagem); ?>">

          <?php echo htmlspecialchars($mensagem); ?>

        </div>

      <?php } ?>

      <form method="POST" action="" id="formCadastro">
        <?php echo fincontrol_csrf_input(); ?>

        <div class="input-group">

          <i class="fa-solid fa-user"></i>

          <input type="text"
                 name="nome_usuario"
                 placeholder="Nome completo"
                 required>

        </div>

        <div class="input-group date-field" data-placeholder="data de nascimento">

          <i class="fa-solid fa-calendar"></i>

          <input type="date"
                 name="dt_nasc_usuario"
                 placeholder="data de nascimento"
                 aria-label="data de nascimento"
                 required>

        </div>

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
                 minlength="6"
                 required>

        </div>

        <div class="input-group">

          <i class="fa-solid fa-lock"></i>

          <input type="password"
                 name="confirmar_senha"
                 placeholder="Confirmar senha"
                 minlength="6"
                 required>

        </div>

        <label class="terms-box">
          <input type="checkbox"
                 name="aceite_termos"
                 id="aceiteTermos"
                 required>

          <span>
            Li e aceito os
            <a href="14.termos.php" target="_blank" rel="noopener noreferrer">Termos de Uso e a Política de Privacidade</a>
            do FinControle.
          </span>
        </label>

        <button type="submit" id="btnCadastrar" disabled>

          <i class="fa-solid fa-user-plus"></i>

          Cadastrar

        </button>

        <div class="login-link">

          Já tem uma conta?

          <a href="02.login.php">

            Entrar

          </a>

          <div>

            <a href="01.home.php" class="home-link">

              <i class="fa-solid fa-house"></i>

              Home

            </a>

          </div>

        </div>

      </form>

    </div>

  </div>

  <script>
    const aceiteTermos = document.getElementById("aceiteTermos");
    const btnCadastrar = document.getElementById("btnCadastrar");

    function atualizarBotaoCadastro() {
      btnCadastrar.disabled = !aceiteTermos.checked;
    }

    aceiteTermos.addEventListener("change", atualizarBotaoCadastro);
    atualizarBotaoCadastro();
  </script>

<?php echo function_exists("fincontrol_version_badge") ? fincontrol_version_badge() : ""; ?>
</body>

</html>
