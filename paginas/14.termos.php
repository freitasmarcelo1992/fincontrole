<?php
require_once __DIR__ . "/00.version.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php require_once "00.pwa.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>FinControle | Termos de Uso e Política de Privacidade</title>

    <link href="img/logo-FinControle.png" rel="icon" type="image/png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f4f7fb;
            color: #333;
            line-height: 1.6;
        }

        .topo {
            background: linear-gradient(180deg, #0077b6, #00b4d8);
            color: #fff;
            padding: 40px 20px;
            text-align: center;
        }

        .topo i {
            font-size: 44px;
            margin-bottom: 12px;
        }

        .topo h1 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .topo p {
            opacity: 0.92;
            font-size: 15px;
        }

        .container {
            width: min(900px, calc(100% - 32px));
            margin: 30px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.08);
            padding: 34px;
        }

        h2 {
            color: #0077b6;
            font-size: 21px;
            margin-top: 28px;
            margin-bottom: 10px;
        }

        h2:first-child {
            margin-top: 0;
        }

        p {
            margin-bottom: 12px;
        }

        ul {
            margin-left: 22px;
            margin-bottom: 14px;
        }

        li {
            margin-bottom: 8px;
        }

        .aviso {
            background: #eaf6fc;
            border-left: 4px solid #0077b6;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 22px;
        }

        .acoes {
            margin-top: 30px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0077b6;
            color: #fff;
            text-decoration: none;
            border-radius: 10px;
            padding: 12px 18px;
            font-weight: 700;
        }

        .btn.secundario {
            background: #6c757d;
        }

        @media (max-width: 600px) {
            .container {
                padding: 24px;
            }

            .topo h1 {
                font-size: 24px;
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
        }

        body {
            background:
                radial-gradient(circle at top right, rgba(0, 180, 216, 0.18), transparent 34%),
                linear-gradient(180deg, #062033 0%, var(--fc-bg) 100%);
            color: var(--fc-text);
        }

        .topo {
            background: linear-gradient(135deg, #061522, #0b3a58);
            border-bottom: 1px solid var(--fc-line);
            box-shadow: 0 18px 44px rgba(0, 0, 0, 0.22);
        }

        .topo i {
            color: var(--fc-cyan);
        }

        .topo p,
        p,
        li {
            color: var(--fc-muted);
        }

        .container {
            background: linear-gradient(180deg, var(--fc-panel), #0a2032);
            border: 1px solid var(--fc-line);
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.28);
        }

        h2 {
            color: var(--fc-cyan);
        }

        .aviso {
            background: rgba(0, 180, 216, 0.1);
            border-left-color: var(--fc-cyan);
            color: var(--fc-muted);
        }

        .btn {
            background: linear-gradient(135deg, var(--fc-blue), var(--fc-cyan));
            box-shadow: 0 14px 28px rgba(0, 119, 182, 0.28);
        }

        .btn.secundario {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--fc-line);
        }

        .btn:hover {
            transform: translateY(-1px);
        }
    </style>
<link rel="stylesheet" href="responsive.css?v=fit1">
<?php echo function_exists("fincontrol_version_styles") ? fincontrol_version_styles() : ""; ?>
</head>

<body>

    <header class="topo">
        <i class="fa-solid fa-file-shield"></i>
        <h1>Termos de Uso e Política de Privacidade</h1>
        <p>Última atualização: 29/05/2026</p>
    </header>

    <main class="container">

        <div class="aviso">
            Estes termos explicam as regras básicas para uso do FinControle e como os dados informados no sistema são tratados.
        </div>

        <h2>1. Aceite dos Termos</h2>
        <p>
            Ao criar uma conta no FinControle, o usuário declara que leu, compreendeu e aceitou estes Termos de Uso e a Política de Privacidade.
        </p>

        <h2>2. Finalidade do Sistema</h2>
        <p>
            O FinControle é um sistema de controle financeiro pessoal criado para registrar receitas, despesas, gastos diários e relatórios financeiros.
        </p>

        <h2>3. Cadastro do Usuário</h2>
        <p>
            Para utilizar o sistema, o usuário deve informar dados verdadeiros e manter suas informações de acesso em segurança.
        </p>
        <ul>
            <li>O usuário é responsável por proteger seu e-mail e senha.</li>
            <li>O usuário não deve compartilhar sua conta com terceiros.</li>
            <li>O usuário deve comunicar qualquer uso indevido percebido.</li>
        </ul>

        <h2>4. Dados Coletados</h2>
        <p>
            O sistema pode armazenar informações fornecidas no cadastro e durante o uso das funcionalidades financeiras.
        </p>
        <ul>
            <li>Nome, data de nascimento, e-mail e senha criptografada.</li>
            <li>Receitas, despesas, gastos diários, categorias, datas e valores financeiros criptografados.</li>
            <li>Informações necessárias para autenticação e funcionamento do sistema.</li>
        </ul>

        <h2>5. Uso dos Dados</h2>
        <p>
            Os dados são utilizados para permitir o funcionamento do FinControle, exibir relatórios, organizar lançamentos financeiros e autenticar usuários.
        </p>

        <h2>6. Senhas e Segurança</h2>
        <p>
            As senhas são armazenadas em formato criptografado. Os valores financeiros lançados também são gravados criptografados no banco de dados, de modo que o acesso direto ao banco sem a chave da aplicação não revela os números cadastrados.
        </p>

        <h2>7. Responsabilidades do Usuário</h2>
        <p>
            O usuário concorda em utilizar o sistema de forma adequada, sem tentar acessar dados de outras contas, modificar funcionalidades indevidamente ou prejudicar o funcionamento da aplicação.
        </p>

        <h2>8. Limitações</h2>
        <p>
            O FinControle é uma ferramenta de apoio ao controle financeiro. As informações exibidas dependem dos dados cadastrados pelo próprio usuário e não substituem orientação financeira profissional.
        </p>

        <h2>9. Alterações nos Termos</h2>
        <p>
            Estes termos podem ser atualizados para refletir melhorias no sistema, mudanças legais ou ajustes de segurança. A versão mais recente ficará disponível nesta página.
        </p>

        <h2>10. Contato</h2>
        <p>
            Em caso de dúvidas sobre estes termos ou sobre o uso dos dados, entre em contato com o administrador do sistema.
        </p>

        <div class="acoes">
            <a href="06.cadastrar_usuario.php" class="btn">
                <i class="fa-solid fa-arrow-left"></i>
                Voltar ao cadastro
            </a>

            <a href="01.home.php" class="btn secundario">
                <i class="fa-solid fa-house"></i>
                Ir para Home
            </a>
        </div>

    </main>

<?php echo function_exists("fincontrol_version_badge") ? fincontrol_version_badge() : ""; ?>
</body>

</html>
