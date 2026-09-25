<?php
require_once "00.sessao.php";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php require_once "00.pwa.php"; ?>
    <?php if (file_exists("00.google_tag.php")) { require_once "00.google_tag.php"; } ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ferramentas simples do FinControle para planejar parcelas, gastos do mes e metas de economia.">
    <title>FinControle - Planejar</title>
    <link href="img/logo-FinControle.png" rel="icon" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg: #061826;
            --card: #0d2a42;
            --card-2: #102f49;
            --line: rgba(87, 190, 255, .22);
            --blue: #0087c7;
            --cyan: #00b7e8;
            --green: #22d36b;
            --text: #ffffff;
            --muted: #a9bdd0;
        }

        * {
            box-sizing: border-box;
            letter-spacing: 0;
        }

        html,
        body {
            width: 100%;
            overflow-x: hidden;
        }

        body {
            margin: 0;
            font-family: Poppins, Arial, sans-serif;
            background:
                radial-gradient(circle at 82% 2%, rgba(0, 183, 232, .18), transparent 34%),
                linear-gradient(180deg, #020b14 0%, var(--bg) 52%, #020b14 100%);
            color: var(--text);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .fc-page {
            width: min(100%, 480px);
            min-height: 100vh;
            margin: 0 auto;
            padding: 24px 18px 92px;
        }

        .fc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .fc-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 900;
            font-size: 1.35rem;
            line-height: 1;
            min-width: 0;
        }

        .fc-brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--cyan), var(--blue));
            color: #fff;
            flex: 0 0 auto;
        }

        .fc-brand span span,
        .accent {
            color: var(--cyan);
        }

        .fc-login {
            border: 1px solid var(--cyan);
            border-radius: 14px;
            padding: 10px 16px;
            font-weight: 800;
            background: rgba(0, 183, 232, .05);
            flex: 0 0 auto;
        }

        .hero,
        .tool-card,
        .cta-card {
            border: 1px solid var(--line);
            border-radius: 22px;
            background: linear-gradient(145deg, rgba(16, 47, 73, .96), rgba(7, 29, 47, .94));
            box-shadow: 0 18px 36px rgba(0, 0, 0, .18);
        }

        .hero {
            padding: 22px;
            margin-bottom: 16px;
        }

        .hero small,
        .tool-card small {
            color: var(--cyan);
            font-weight: 900;
            text-transform: uppercase;
        }

        h1 {
            margin: 10px 0 8px;
            font-size: 1.75rem;
            line-height: 1.12;
        }

        .hero p,
        .tool-card p,
        .tool-card li,
        .cta-card p {
            color: #d5e1ec;
            line-height: 1.45;
            font-size: .94rem;
        }

        .tool-list {
            display: grid;
            gap: 12px;
        }

        .tool-card {
            padding: 18px;
        }

        .tool-head {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr);
            gap: 12px;
            align-items: center;
            margin-bottom: 10px;
        }

        .tool-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 183, 232, .12);
            color: var(--cyan);
            border: 1px solid rgba(0, 183, 232, .28);
        }

        .tool-card h2 {
            margin: 2px 0 0;
            font-size: 1.08rem;
            line-height: 1.2;
        }

        .tool-card ul {
            margin: 12px 0 14px;
            padding-left: 18px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 10px 16px;
            border-radius: 13px;
            background: linear-gradient(135deg, var(--cyan), var(--blue));
            color: #fff;
            font-weight: 900;
            width: 100%;
        }

        .cta-card {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) auto;
            gap: 12px;
            align-items: center;
            padding: 16px;
            margin-top: 14px;
        }

        .cta-card h3 {
            margin: 0;
            font-size: 1rem;
        }

        .cta-card p {
            margin: 2px 0 0;
            font-size: .78rem;
        }

        .cta-card .btn {
            width: auto;
            min-height: 38px;
            white-space: nowrap;
            background: rgba(0, 183, 232, .04);
            border: 1px solid var(--cyan);
        }

        .bottom-nav {
            position: fixed;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: min(100%, 480px);
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px;
            padding: 10px 14px 12px;
            background: rgba(3, 18, 31, .96);
            border-top: 1px solid rgba(255, 255, 255, .08);
            z-index: 20;
        }

        .bottom-nav a {
            display: grid;
            justify-items: center;
            gap: 4px;
            color: var(--muted);
            font-weight: 800;
            font-size: .74rem;
        }

        .bottom-nav a.active,
        .bottom-nav a:hover {
            color: var(--cyan);
        }

        @media (max-width: 420px) {
            .fc-page {
                padding: 18px 12px 84px;
                max-width: 420px;
                overflow-x: hidden;
            }

            .fc-header {
                margin-bottom: 14px;
                gap: 10px;
            }

            .fc-brand {
                font-size: 1.08rem;
            }

            .fc-brand-mark {
                width: 40px;
                height: 40px;
            }

            .fc-login {
                padding: 10px 14px;
                font-size: .88rem;
            }

            h1 {
                font-size: 1.36rem;
            }

            .hero,
            .tool-card,
            .cta-card {
                border-radius: 18px;
            }

            .hero,
            .tool-card {
                padding: 16px;
            }

            .tool-card p,
            .tool-card li {
                font-size: .84rem;
            }

            .cta-card {
                grid-template-columns: 42px minmax(0, 1fr) auto;
                padding: 14px;
            }

            .cta-card .btn {
                padding: 8px 10px;
                font-size: .72rem;
            }
        }
    </style>
</head>
<body>
    <main class="fc-page">
        <header class="fc-header">
            <a class="fc-brand" href="01.home.php" aria-label="FinControle">
                <span class="fc-brand-mark"><i class="fa-solid fa-wallet"></i></span>
                <span>Fin<span>Controle</span></span>
            </a>
            <a class="fc-login" href="02.login.php">Entrar</a>
        </header>

        <section class="hero">
            <small>Planejar</small>
            <h1>Decida antes de gastar</h1>
            <p>Use perguntas simples para entender parcelas, limite mensal e metas antes de comprometer seu dinheiro.</p>
        </section>

        <section class="tool-list" aria-label="Ferramentas de planejamento">
            <article class="tool-card" id="parcelamento">
                <div class="tool-head">
                    <span class="tool-icon"><i class="fa-solid fa-calculator"></i></span>
                    <div>
                        <small>Calculadora</small>
                        <h2>Quanto custa parcelar?</h2>
                    </div>
                </div>
                <p>Antes de comprar, compare o valor da parcela com o que ja esta comprometido no mes.</p>
                <ul>
                    <li>Some parcelas novas com parcelas existentes.</li>
                    <li>Verifique se a compra cabe no saldo previsto.</li>
                    <li>Evite transformar compra pequena em compromisso longo.</li>
                </ul>
                <a class="btn" href="06.cadastrar_usuario.php">Criar conta e simular <i class="fa-solid fa-arrow-right"></i></a>
            </article>

            <article class="tool-card" id="limite">
                <div class="tool-head">
                    <span class="tool-icon"><i class="fa-regular fa-calendar-check"></i></span>
                    <div>
                        <small>Planejamento</small>
                        <h2>Quanto posso gastar?</h2>
                    </div>
                </div>
                <p>Descubra um limite mensal para compras do dia a dia sem perder de vista as contas importantes.</p>
                <ul>
                    <li>Veja quanto entrou e quanto precisa sair.</li>
                    <li>Separe despesas obrigatorias de compras nao obrigatorias.</li>
                    <li>Acompanhe o saldo disponivel ao longo do mes.</li>
                </ul>
                <a class="btn" href="06.cadastrar_usuario.php">Organizar meu mes gratis <i class="fa-solid fa-arrow-right"></i></a>
            </article>

            <article class="tool-card" id="meta">
                <div class="tool-head">
                    <span class="tool-icon"><i class="fa-solid fa-piggy-bank"></i></span>
                    <div>
                        <small>Economia</small>
                        <h2>Quanto preciso guardar?</h2>
                    </div>
                </div>
                <p>Transforme uma vontade em meta: viagem, reserva, compra importante ou qualquer objetivo financeiro.</p>
                <ul>
                    <li>Defina o valor que deseja juntar.</li>
                    <li>Escolha uma data alvo realista.</li>
                    <li>Acompanhe se o ritmo mensal esta suficiente.</li>
                </ul>
                <a class="btn" href="06.cadastrar_usuario.php">Criar minha meta <i class="fa-solid fa-arrow-right"></i></a>
            </article>
        </section>

        <section class="cta-card" aria-label="Criar conta no FinControle">
            <span class="fc-brand-mark"><i class="fa-solid fa-wallet"></i></span>
            <div>
                <h3>FinControle</h3>
                <p>Seu dinheiro mais simples de entender.</p>
            </div>
            <a class="btn" href="06.cadastrar_usuario.php">Criar conta &rarr;</a>
        </section>
    </main>

    <nav class="bottom-nav" aria-label="Navega&ccedil;&atilde;o principal">
        <a href="01.home.php"><i class="fa-regular fa-newspaper"></i><span>Not&iacute;cias</span></a>
        <a class="active" href="28.planejar.php"><i class="fa-regular fa-calendar-check"></i><span>Planejar</span></a>
        <a href="27.guias.php"><i class="fa-regular fa-book-open"></i><span>Guias</span></a>
        <a href="02.login.php"><i class="fa-regular fa-user"></i><span>Entrar</span></a>
    </nav>

    <?php echo function_exists("fincontrol_version_badge") ? fincontrol_version_badge() : ""; ?>
</body>
</html>
