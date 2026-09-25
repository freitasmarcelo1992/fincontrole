<?php
require_once "00.sessao.php";
require_once "09.conexao.php";
require_once "00.email.php";

$mensagem = "";
$tipoMensagem = "";
fincontrol_validar_csrf();

function gerarSenhaTemporariaFincontrol($tamanho = 10) {
    $caracteres = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789";
    $senha = "";
    $max = strlen($caracteres) - 1;

    for ($i = 0; $i < $tamanho; $i++) {
        $senha .= $caracteres[random_int(0, $max)];
    }

    return $senha;
}

function montarEmailSenhaTemporariaFincontrol($nome, $senhaTemporaria) {
    $assunto = "Senha temporaria FinControl";
    $nomeSeguro = $nome ?: "usuario";

    $corpo = "Olá, " . $nomeSeguro . ".\n\n";
    $corpo .= "Recebemos uma solicitação de recuperação de senha para sua conta FinControl.\n\n";
    $corpo .= "Sua senha temporária é: " . $senhaTemporaria . "\n\n";
    $corpo .= "Faça login com essa senha e altere-a imediatamente na tela Alterar Senha.\n\n";
    $corpo .= "Se você não solicitou essa recuperação, acesse sua conta e altere a senha assim que possível.\n";

    return [$assunto, $corpo];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email_usuario"]);

    $sql = "SELECT id_usuario, nome_usuario, email_usuario, senha_usuario
            FROM usuario
            WHERE email_usuario = ?
            LIMIT 1";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {

        $usuario = $resultado->fetch_assoc();

        $novaSenha = gerarSenhaTemporariaFincontrol();

        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $senhaAnterior = $usuario["senha_usuario"];

        $sqlUpdate = "UPDATE usuario
                      SET senha_usuario = ?
                      WHERE id_usuario = ?";

        $stmtUpdate = $conexao->prepare($sqlUpdate);
        $stmtUpdate->bind_param(
            "si",
            $senhaHash,
            $usuario["id_usuario"]
        );

        if ($stmtUpdate->execute()) {

            [$assuntoEmail, $corpoEmail] = montarEmailSenhaTemporariaFincontrol(
                $usuario["nome_usuario"],
                $novaSenha
            );

            try {
                $emailEnviado = fincontrol_email_enviar(
                    $usuario["email_usuario"],
                    $assuntoEmail,
                    $corpoEmail
                );
            } catch (Throwable $erroEmail) {
                error_log("Erro ao enviar senha temporaria FinControl: " . $erroEmail->getMessage());
                $emailEnviado = false;
            }

            if ($emailEnviado) {

                $mensagem = "Senha temporária enviada para o e-mail cadastrado. Verifique sua caixa de entrada.";
                $tipoMensagem = "sucesso";

            } else {

                $sqlRestaurar = "UPDATE usuario
                                 SET senha_usuario = ?
                                 WHERE id_usuario = ?";

                $stmtRestaurar = $conexao->prepare($sqlRestaurar);

                if ($stmtRestaurar) {
                    $stmtRestaurar->bind_param(
                        "si",
                        $senhaAnterior,
                        $usuario["id_usuario"]
                    );
                    $stmtRestaurar->execute();
                    $stmtRestaurar->close();
                }

                $mensagem = "Não foi possível enviar o e-mail. A senha não foi alterada.";
                $tipoMensagem = "erro";

            }

        } else {

            $mensagem = "Erro ao redefinir a senha.";
            $tipoMensagem = "erro";

        }

        $stmtUpdate->close();

    } else {

        $mensagem = "E-mail não encontrado.";
        $tipoMensagem = "erro";

    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php require_once "00.pwa.php"; ?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>FinControle | Recuperar Senha</title>

<link href="img/logo-FinControle.png" rel="icon" type="image/png">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800;900&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Poppins",sans-serif;
}

body{
    height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:linear-gradient(135deg,#0077b6,#00b4d8);
}

.container{
    display:flex;
    width:950px;
    background:#fff;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,.15);
}

.left{
    flex:1;
    background:linear-gradient(180deg,#0077b6,#00b4d8);
    color:#fff;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    padding:60px 40px;
    text-align:center;
}

.left i{
    font-size:60px;
    margin-bottom:15px;
}

.left h1{
    font-size:34px;
    margin-bottom:15px;
}

.left p{
    max-width:300px;
}

.right{
    flex:1;
    padding:60px 50px;
    background:#f4f7fb;
}

.right h2{
    color:#0077b6;
    text-align:center;
    margin-bottom:30px;
}

.input-group{
    position:relative;
    margin-bottom:25px;
}

.input-group i{
    position:absolute;
    left:15px;
    top:50%;
    transform:translateY(-50%);
    color:#0077b6;
}

.input-group input{
    width:100%;
    padding:12px 45px;
    border:1px solid #ccd6dd;
    border-radius:10px;
}

button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:10px;
    color:#fff;
    font-weight:600;
    cursor:pointer;
    background:linear-gradient(135deg,#0077b6,#00b4d8);
}

button:hover{
    opacity:.9;
}

.alerta{
    padding:12px;
    border-radius:10px;
    margin-bottom:20px;
    text-align:center;
}

.sucesso{
    background:#d1e7dd;
    color:#0f5132;
}

.erro{
    background:#f8d7da;
    color:#842029;
}

.voltar{
    display:block;
    text-align:center;
    margin-top:20px;
    text-decoration:none;
    color:#0077b6;
    font-weight:600;
}

@media(max-width:900px){

    .container{
        flex-direction:column;
        width:90%;
    }

    .left{
        display:none;
    }

}

:root {
    --fc-bg: #061522;
    --fc-panel: #0d2a40;
    --fc-panel-2: #102f49;
    --fc-line: rgba(125, 211, 252, 0.18);
    --fc-blue: #0077b6;
    --fc-cyan: #00b4d8;
    --fc-text: #f8fafc;
    --fc-muted: #c7d2df;
    --fc-success: #22c55e;
    --fc-danger: #ef4444;
}

body {
    min-height: 100vh;
    height: auto;
    padding: 24px;
    background:
        radial-gradient(circle at top right, rgba(0, 180, 216, 0.2), transparent 34%),
        linear-gradient(180deg, #062033 0%, var(--fc-bg) 100%);
    color: var(--fc-text);
}

.container {
    max-width: 980px;
    background: linear-gradient(180deg, var(--fc-panel), #0a2032);
    border: 1px solid var(--fc-line);
    box-shadow: 0 28px 80px rgba(0, 0, 0, 0.32);
}

.left {
    background: linear-gradient(160deg, #061522, #0b3a58);
    border-right: 1px solid var(--fc-line);
}

.left i {
    color: var(--fc-cyan);
}

.left p,
.right p {
    color: var(--fc-muted);
}

.right {
    background: rgba(255, 255, 255, 0.03);
}

.right h2 {
    color: var(--fc-text);
    font-weight: 900;
}

.input-group i {
    color: var(--fc-cyan);
}

.input-group input {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid var(--fc-line);
    color: var(--fc-text);
}

.input-group input::placeholder {
    color: rgba(199, 210, 223, 0.72);
}

.input-group input:focus {
    outline: none;
    border-color: var(--fc-cyan);
    box-shadow: 0 0 0 4px rgba(0, 180, 216, 0.12);
}

button {
    background: linear-gradient(135deg, var(--fc-blue), var(--fc-cyan));
    box-shadow: 0 14px 28px rgba(0, 119, 182, 0.28);
}

.voltar {
    color: var(--fc-cyan);
}

.alerta {
    border: 1px solid var(--fc-line);
}

.sucesso {
    background: rgba(34, 197, 94, 0.12);
    color: #bbf7d0;
}

.erro {
    background: rgba(239, 68, 68, 0.12);
    color: #fecaca;
}

@media(max-width:900px) {
    body {
        padding: 18px;
        align-items: flex-start;
    }

    .container {
        width: min(100%, 520px);
        margin: auto;
    }

    .right {
        padding: 36px 24px;
    }
}

</style>

<link rel="stylesheet" href="responsive.css?v=fit1">
<?php echo function_exists("fincontrol_version_styles") ? fincontrol_version_styles() : ""; ?>
</head>

<body>

<div class="container">

    <div class="left">

        <i class="fa-solid fa-key"></i>

        <h1>Recuperação</h1>

        <p>
            Informe seu e-mail para receber uma nova senha temporária.
        </p>

    </div>

    <div class="right">

        <h2>Esqueci minha senha</h2>

        <?php if(!empty($mensagem)){ ?>

            <div class="alerta <?php echo $tipoMensagem; ?>">

                <?php echo $mensagem; ?>

            </div>

        <?php } ?>

        <form method="POST">
            <?php echo fincontrol_csrf_input(); ?>

            <div class="input-group">

                <i class="fa-solid fa-envelope"></i>

                <input
                    type="email"
                    name="email_usuario"
                    placeholder="Digite seu e-mail"
                    required>

            </div>

            <button type="submit">

                <i class="fa-solid fa-rotate"></i>

                Redefinir Senha

            </button>

        </form>

        <a href="02.login.php" class="voltar">

            <i class="fa-solid fa-arrow-left"></i>

            Voltar para o Login

        </a>

    </div>

</div>

<?php echo function_exists("fincontrol_version_badge") ? fincontrol_version_badge() : ""; ?>
</body>
</html>
