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
    <meta name="description" content="Dicas financeiras simples do FinControle para organizar o dinheiro, criar reserva e evitar juros.">
    <title>FinControle - Dicas financeiras</title>
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
            --text: #ffffff;
            --muted: #a9bdd0;
        }

        * {
            box-sizing: border-box;
            letter-spacing: 0;
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
            margin-bottom: 22px;
        }

        .fc-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 900;
            font-size: 1.45rem;
            line-height: 1;
        }

        .fc-brand-mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--cyan), var(--blue));
            box-shadow: 0 14px 28px rgba(0, 183, 232, .22);
        }

        .fc-brand span span,
        .accent {
            color: var(--cyan);
        }

        .fc-header-actions {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            flex: 0 0 auto;
        }

        .fc-login,
        .fc-install {
            border: 1px solid var(--cyan);
            border-radius: 14px;
            padding: 12px 22px;
            font-weight: 800;
            background: rgba(0, 183, 232, .05);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .03);
        }

        .fc-install {
            color: #fff;
            font-family: inherit;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .fc-install[hidden] {
            display: none;
        }

        .hero {
            border: 1px solid var(--line);
            border-radius: 22px;
            background: linear-gradient(145deg, rgba(16, 47, 73, .96), rgba(7, 29, 47, .94));
            padding: 22px;
            margin-bottom: 16px;
        }

        .hero small {
            color: var(--cyan);
            font-weight: 900;
            text-transform: uppercase;
        }

        h1 {
            margin: 10px 0 8px;
            font-size: 1.75rem;
            line-height: 1.15;
        }

        .hero p,
        article p,
        article li {
            color: #d5e1ec;
            line-height: 1.55;
            font-size: .96rem;
        }

        .article-nav {
            display: grid;
            gap: 10px;
            margin: 16px 0 20px;
        }

        .article-nav a,
        article {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(16, 47, 73, .78);
        }

        .article-nav a {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 14px;
            font-weight: 800;
        }

        article {
            padding: 20px;
            margin-bottom: 16px;
        }

        article h2 {
            margin: 0 0 10px;
            font-size: 1.25rem;
            color: var(--text);
        }

        article ul {
            padding-left: 18px;
            margin: 12px 0;
        }

        .callout {
            border-left: 4px solid var(--cyan);
            padding: 12px 14px;
            background: rgba(0, 183, 232, .08);
            border-radius: 12px;
            color: #e7f6ff;
            font-weight: 700;
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
            padding: 10px 14px calc(10px + env(safe-area-inset-bottom));
            background: rgba(3, 17, 29, .96);
            border-top: 1px solid rgba(87, 190, 255, .18);
            backdrop-filter: blur(14px);
            z-index: 20;
        }

        .bottom-nav a {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: #9fb3c4;
            font-weight: 700;
            font-size: .76rem;
        }

        .bottom-nav a.active,
        .bottom-nav a:hover {
            color: var(--cyan);
        }

        .bottom-nav i {
            font-size: 1.18rem;
        }

        @media (max-width: 420px) {
            .fc-page {
                padding-top: 20px;
            }

            .fc-brand {
                font-size: 1.32rem;
            }

            .fc-header-actions {
                gap: 8px;
            }

            .fc-install {
                width: 42px;
                height: 42px;
                padding: 0;
            }

            .fc-install span {
                display: none;
            }

            .fc-login {
                padding: 11px 17px;
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
            <div class="fc-header-actions">
                <button type="button" class="fc-install" id="installAppButtonPwa" aria-label="Instalar aplicativo FinControle">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                    <span>Instalar app</span>
                </button>
                <a class="fc-login" href="02.login.php">Entrar</a>
            </div>
        </header>

        <section class="hero">
            <small>Dicas financeiras</small>
            <h1>Aprenda a cuidar melhor do seu dinheiro</h1>
            <p>Conte&uacute;dos curtos, pr&aacute;ticos e permanentes para transformar informa&ccedil;&atilde;o em decis&atilde;o.</p>
        </section>

        <nav class="article-nav" aria-label="Lista de dicas financeiras">
            <a href="#orcamento">Como montar um or&ccedil;amento mensal <i class="fa-solid fa-chevron-right"></i></a>
            <a href="#reserva">Como criar uma reserva de emerg&ecirc;ncia <i class="fa-solid fa-chevron-right"></i></a>
            <a href="#cartao">Cart&atilde;o de cr&eacute;dito: como evitar juros <i class="fa-solid fa-chevron-right"></i></a>
        </nav>

        <article id="orcamento">
            <h2>Como montar um or&ccedil;amento mensal</h2>
            <p>Comece pelas despesas essenciais. Elas mostram quanto dinheiro j&aacute; tem destino antes de qualquer compra do dia a dia.</p>
            <ul>
                <li>Liste receitas previstas para o m&ecirc;s.</li>
                <li>Some despesas fixas e vari&aacute;veis obrigat&oacute;rias.</li>
                <li>Compare o que entrou com o que precisa sair.</li>
                <li>Defina um limite para compras n&atilde;o obrigat&oacute;rias.</li>
            </ul>
            <p class="callout">No FinControle, use Receitas, Despesas e Relatórios para enxergar o saldo real do m&ecirc;s.</p>
        </article>

        <article id="reserva">
            <h2>Como criar uma reserva de emerg&ecirc;ncia</h2>
            <p>Reserva n&atilde;o nasce do dinheiro que sobra por acaso. Ela precisa virar um compromisso simples e recorrente.</p>
            <ul>
                <li>Escolha um valor pequeno para come&ccedil;ar.</li>
                <li>Guarde assim que a receita entrar.</li>
                <li>Evite misturar reserva com dinheiro de uso di&aacute;rio.</li>
                <li>Acompanhe o avan&ccedil;o todos os meses.</li>
            </ul>
            <p class="callout">O objetivo inicial &eacute; criar ritmo. Depois voc&ecirc; aumenta a meta.</p>
        </article>

        <article id="cartao">
            <h2>Cart&atilde;o de cr&eacute;dito: como evitar juros</h2>
            <p>O cart&atilde;o ajuda quando voc&ecirc; controla a fatura antes dela fechar. O problema come&ccedil;a quando a parcela vira surpresa.</p>
            <ul>
                <li>Registre compras parceladas no momento da compra.</li>
                <li>Veja quanto j&aacute; est&aacute; comprometido nos pr&oacute;ximos meses.</li>
                <li>Evite pagar apenas o m&iacute;nimo da fatura.</li>
                <li>Antes de parcelar, simule se a parcela cabe no or&ccedil;amento.</li>
            </ul>
            <p class="callout">Se a compra n&atilde;o cabe no m&ecirc;s, ela precisa caber no planejamento.</p>
        </article>
    </main>

    <nav class="bottom-nav" aria-label="Navega&ccedil;&atilde;o principal">
        <a href="01.home.php"><i class="fa-regular fa-newspaper"></i><span>Not&iacute;cias</span></a>
        <a href="28.planejar.php"><i class="fa-regular fa-calendar-check"></i><span>Planejar</span></a>
        <a class="active" href="27.guias.php"><i class="fa-regular fa-book-open"></i><span>Dicas</span></a>
        <a href="02.login.php"><i class="fa-regular fa-user"></i><span>Entrar</span></a>
    </nav>

    <script>
        (() => {
            const installButton = document.getElementById('installAppButtonPwa');
            if (!installButton) return;

            let deferredPrompt = null;
            const isStandalone = () =>
                window.matchMedia('(display-mode: standalone)').matches ||
                window.navigator.standalone === true ||
                document.referrer.startsWith('android-app://');

            const hideIfInstalled = () => {
                if (isStandalone()) installButton.hidden = true;
            };

            window.addEventListener('beforeinstallprompt', (event) => {
                event.preventDefault();
                deferredPrompt = event;
                installButton.hidden = false;
            });

            window.addEventListener('appinstalled', () => {
                deferredPrompt = null;
                installButton.hidden = true;
            });

            installButton.addEventListener('click', async () => {
                if (isStandalone()) {
                    installButton.hidden = true;
                    return;
                }

                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    await deferredPrompt.userChoice.catch(() => undefined);
                    deferredPrompt = null;
                    return;
                }

                alert('Para instalar o FinControle, toque no menu do navegador e escolha "Instalar app" ou "Adicionar à tela inicial". Se essa opção não aparecer, atualize a página e tente novamente.');
            });

            hideIfInstalled();
        })();
    </script>

    <?php echo function_exists("fincontrol_version_badge") ? fincontrol_version_badge() : ""; ?>
</body>
</html>
