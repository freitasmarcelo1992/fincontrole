<?php

require_once "00.sessao.php";
require_once "09.conexao.php";

$mensagem = "";
$tipo = "";
fincontrol_validar_csrf();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email_usuario"]);
    $senhaAtual = trim($_POST["senha_atual"]);
    $novaSenha = trim($_POST["nova_senha"]);
    $confirmarSenha = trim($_POST["confirmar_senha"]);

    if (
        empty($email) ||
        empty($senhaAtual) ||
        empty($novaSenha) ||
        empty($confirmarSenha)
    ) {

        $mensagem = "Preencha todos os campos.";
        $tipo = "erro";

    } elseif ($novaSenha != $confirmarSenha) {

        $mensagem = "A confirmação da nova senha não confere.";
        $tipo = "erro";

    } elseif (strlen($novaSenha) < 6) {

        $mensagem = "A nova senha deve possuir no mínimo 6 caracteres.";
        $tipo = "erro";

    } else {

        $sql = "SELECT id_usuario,
                       senha_usuario
                FROM usuario
                WHERE email_usuario = ?
                LIMIT 1";

        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 0) {

            $mensagem = "E-mail não encontrado.";
            $tipo = "erro";

        } else {

            $usuario = $resultado->fetch_assoc();

            if (!password_verify(
                    $senhaAtual,
                    $usuario["senha_usuario"]
                )) {

                $mensagem = "Senha atual incorreta.";
                $tipo = "erro";

            } else {

                $novaSenhaHash = password_hash(
                    $novaSenha,
                    PASSWORD_DEFAULT
                );

                $sqlUpdate = "UPDATE usuario
                              SET senha_usuario = ?
                              WHERE id_usuario = ?";

                $stmtUpdate = $conexao->prepare($sqlUpdate);

                $stmtUpdate->bind_param(
                    "si",
                    $novaSenhaHash,
                    $usuario["id_usuario"]
                );

                if ($stmtUpdate->execute()) {

                    $mensagem = "Senha alterada com sucesso!";
                    $tipo = "sucesso";

                } else {

                    $mensagem = "Erro ao atualizar a senha.";
                    $tipo = "erro";

                }

                $stmtUpdate->close();
            }
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php require_once "00.pwa.php"; ?>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>FinControle | Alterar Senha</title>

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
margin-bottom:20px;
}

.left h1{
font-size:34px;
margin-bottom:15px;
}

.left p{
max-width:320px;
}

.right{
flex:1;
padding:60px 50px;
background:#f4f7fb;
}

.right h2{
text-align:center;
margin-bottom:30px;
color:#0077b6;
}

.input-group{
position:relative;
margin-bottom:18px;
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
font-size:15px;
}

.input-group input:focus{
outline:none;
border-color:#00b4d8;
box-shadow:0 0 5px rgba(0,180,216,.3);
}

button{
width:100%;
padding:14px;
border:none;
border-radius:10px;
background:linear-gradient(135deg,#0077b6,#00b4d8);
color:#fff;
font-size:16px;
font-weight:600;
cursor:pointer;
}

button:hover{
opacity:.9;
}

.alerta{
padding:12px;
border-radius:10px;
margin-bottom:20px;
text-align:center;
font-size:14px;
}

.erro{
background:#f8d7da;
color:#842029;
}

.sucesso{
background:#d1e7dd;
color:#0f5132;
}

.voltar{
display:block;
text-align:center;
margin-top:20px;
text-decoration:none;
font-weight:600;
color:#0077b6;
}

@media(max-width:900px){

.container{
width:90%;
flex-direction:column;
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

body{
min-height:100vh;
height:auto;
padding:24px;
background:
radial-gradient(circle at top right, rgba(0, 180, 216, 0.2), transparent 34%),
linear-gradient(180deg, #062033 0%, var(--fc-bg) 100%);
color:var(--fc-text);
}

.container{
max-width:980px;
background:linear-gradient(180deg, var(--fc-panel), #0a2032);
border:1px solid var(--fc-line);
box-shadow:0 28px 80px rgba(0,0,0,.32);
}

.left{
background:linear-gradient(160deg, #061522, #0b3a58);
border-right:1px solid var(--fc-line);
}

.left i{
color:var(--fc-cyan);
}

.left p,
.right p{
color:var(--fc-muted);
}

.right{
background:rgba(255,255,255,.03);
}

.right h2{
color:var(--fc-text);
font-weight:900;
}

.input-group i{
color:var(--fc-cyan);
}

.input-group input{
background:rgba(255,255,255,.06);
border:1px solid var(--fc-line);
color:var(--fc-text);
}

.input-group input::placeholder{
color:rgba(199,210,223,.72);
}

.input-group input:focus{
outline:none;
border-color:var(--fc-cyan);
box-shadow:0 0 0 4px rgba(0,180,216,.12);
}

button{
background:linear-gradient(135deg, var(--fc-blue), var(--fc-cyan));
box-shadow:0 14px 28px rgba(0,119,182,.28);
}

.voltar{
color:var(--fc-cyan);
}

.alerta{
border:1px solid var(--fc-line);
}

.sucesso{
background:rgba(34,197,94,.12);
color:#bbf7d0;
}

.erro{
background:rgba(239,68,68,.12);
color:#fecaca;
}

@media(max-width:900px){
body{
padding:18px;
align-items:flex-start;
}

.container{
width:min(100%, 520px);
margin:auto;
}

.right{
padding:32px 24px;
}
}

</style>

<link rel="stylesheet" href="responsive.css?v=fit1">
<?php echo function_exists("fincontrol_version_styles") ? fincontrol_version_styles() : ""; ?>
</head>

<body>

<div class="container">

<div class="left">

<i class="fa-solid fa-lock"></i>

<h1>Segurança</h1>

<p>
Altere sua senha informando seu e-mail e sua senha atual.
</p>

</div>

<div class="right">

<h2>Alterar Senha</h2>

<?php if(!empty($mensagem)){ ?>

<div class="alerta <?php echo $tipo; ?>">

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
placeholder="E-mail"
required>

</div>

<div class="input-group">

<i class="fa-solid fa-key"></i>

<input
type="password"
name="senha_atual"
placeholder="Senha Atual"
required>

</div>

<div class="input-group">

<i class="fa-solid fa-lock"></i>

<input
type="password"
name="nova_senha"
placeholder="Nova Senha"
required>

</div>

<div class="input-group">

<i class="fa-solid fa-lock"></i>

<input
type="password"
name="confirmar_senha"
placeholder="Confirmar Nova Senha"
required>

</div>

<button type="submit">

<i class="fa-solid fa-floppy-disk"></i>

Salvar Nova Senha

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
