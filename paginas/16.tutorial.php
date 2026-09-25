<?php
require_once "00.sessao.php";
require_once __DIR__ . "/00.version.php";
$usuario_logado = !empty($_SESSION["id_usuario"]);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php require_once "00.pwa.php"; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinControle | Tutorial</title>

    <link href="img/logo-FinControle.png" rel="icon" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: "Poppins", Arial, sans-serif;
            background: #f4f7fb;
            color: #243041;
        }

        a {
            color: inherit;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 10;
            background: rgba(255,255,255,.96);
            border-bottom: 1px solid #e3ebf2;
            box-shadow: 0 8px 22px rgba(31,41,55,.06);
        }

        .nav {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #0077b6;
            font-size: 22px;
            font-weight: 800;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .nav-links a {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 40px;
            padding: 9px 14px;
            border-radius: 8px;
            color: #4a5b6d;
            font-weight: 700;
            text-decoration: none;
            transition: .2s ease;
        }

        .nav-links a:hover {
            color: #0077b6;
            background: #edf7fb;
        }

        .mobile-nav-toggle{display:none}
        .nav-links .primary {
            background: linear-gradient(90deg, #00b4d8, #0077b6);
            color: #fff;
            box-shadow: 0 4px 12px rgba(0,119,182,.22);
        }

        .nav-links .primary:hover {
            color: #fff;
            transform: translateY(-1px);
        }

        .hero {
            background:
                linear-gradient(135deg, rgba(0,119,182,.95), rgba(0,180,216,.78)),
                url("img/1-dash-exemplo.png") center top / cover no-repeat;
            color: #fff;
        }

        .hero-inner {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            min-height: 430px;
            padding: 68px 0 58px;
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(300px, .95fr);
            gap: 34px;
            align-items: end;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 8px;
            background: rgba(255,255,255,.16);
            color: #eefbff;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
        }

        h1 {
            margin: 18px 0 14px;
            max-width: 760px;
            font-size: clamp(2.15rem, 5vw, 4.2rem);
            line-height: 1.07;
            letter-spacing: 0;
        }

        .hero p {
            max-width: 680px;
            margin: 0;
            color: #e7f8ff;
            font-size: 1.08rem;
            line-height: 1.7;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 12px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 800;
        }

        .btn.light {
            background: #fff;
            color: #0077b6;
        }

        .btn.outline {
            border: 2px solid rgba(255,255,255,.82);
            color: #fff;
        }

        .hero-card {
            background: rgba(255,255,255,.96);
            color: #243041;
            border-radius: 8px;
            padding: 22px;
            box-shadow: 0 20px 45px rgba(0,0,0,.18);
        }

        .hero-card h2 {
            margin: 0 0 14px;
            color: #0077b6;
            font-size: 20px;
        }

        .mini-step {
            display: flex;
            gap: 12px;
            padding: 13px 0;
            border-top: 1px solid #e8eef4;
        }

        .mini-step:first-of-type {
            border-top: 0;
        }

        .mini-step strong {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            border-radius: 8px;
            background: #e9f7fc;
            color: #0077b6;
        }

        .mini-step p {
            margin: 0;
            color: #526273;
            font-size: 14px;
            line-height: 1.45;
        }

        .section {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 62px 0;
        }

        .section-head {
            display: flex;
            justify-content: space-between;
            gap: 28px;
            align-items: end;
            margin-bottom: 24px;
        }

        .section-head h2 {
            margin: 0;
            color: #0077b6;
            font-size: clamp(1.6rem, 3vw, 2.3rem);
        }

        .section-head p {
            max-width: 560px;
            margin: 0;
            color: #667085;
            line-height: 1.65;
        }

        .journey {
            display: grid;
            grid-template-columns: repeat(7, minmax(120px, 1fr));
            gap: 10px;
        }

        .journey a {
            min-height: 112px;
            padding: 14px;
            border-radius: 8px;
            background: #fff;
            color: #405164;
            text-decoration: none;
            font-weight: 800;
            box-shadow: 0 3px 14px rgba(0,0,0,.07);
            border: 1px solid #e7edf3;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: .2s ease;
        }

        .journey a:hover {
            transform: translateY(-3px);
            border-color: #9ddfed;
        }

        .journey i {
            color: #0077b6;
            font-size: 21px;
        }

        .steps {
            display: grid;
            gap: 24px;
        }

        .step {
            display: grid;
            grid-template-columns: minmax(280px, .78fr) minmax(0, 1.22fr);
            gap: 24px;
            align-items: stretch;
            background: #fff;
            border: 1px solid #e4ecf3;
            border-radius: 8px;
            padding: 22px;
            box-shadow: 0 4px 18px rgba(31,41,55,.07);
        }

        .step-info {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .step-number {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: linear-gradient(135deg, #0077b6, #00b4d8);
            color: #fff;
            font-weight: 800;
            font-size: 18px;
        }

        .step h3 {
            margin: 0;
            color: #243041;
            font-size: 25px;
        }

        .step p {
            margin: 0;
            color: #5b6b7c;
            line-height: 1.68;
        }

        .checklist {
            display: grid;
            gap: 10px;
            margin: 2px 0 0;
            padding: 0;
            list-style: none;
        }

        .checklist li {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            color: #3f4f60;
            line-height: 1.5;
        }

        .checklist i {
            margin-top: 4px;
            color: #16a34a;
        }

        .screen {
            min-height: 100%;
            border-radius: 8px;
            background: #eef4f8;
            border: 1px solid #dce7ef;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .screen img {
            width: 100%;
            height: auto;
            min-height: 0;
            object-fit: contain;
            object-position: top center;
            display: block;
            background: #eef4f8;
            flex: 0 0 auto;
        }

        .screen.empty {
            padding: 24px;
            justify-content: center;
            background:
                linear-gradient(135deg, rgba(0,119,182,.10), rgba(0,180,216,.08)),
                #fff;
        }

        .mock-form {
            display: grid;
            gap: 12px;
        }

        .mock-line {
            height: 44px;
            border-radius: 8px;
            background: #edf3f8;
            border: 1px solid #d8e3ec;
        }

        .mock-button {
            height: 46px;
            border-radius: 8px;
            background: linear-gradient(90deg, #00b4d8, #0077b6);
        }

        .screen-caption {
            padding: 12px 14px;
            background: #fff;
            border-top: 1px solid #dce7ef;
            color: #667085;
            font-size: 13px;
            font-weight: 700;
        }

        .tip-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .tip {
            background: #fff;
            border: 1px solid #e4ecf3;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 18px rgba(31,41,55,.06);
        }

        .tip i {
            color: #0077b6;
            font-size: 24px;
            margin-bottom: 12px;
        }

        .tip h3 {
            margin: 0 0 8px;
            font-size: 18px;
        }

        .tip p {
            margin: 0;
            color: #667085;
            line-height: 1.6;
        }

        .cta {
            background: linear-gradient(135deg, #0077b6, #00b4d8);
            color: #fff;
            border-radius: 8px;
            padding: 34px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 22px;
        }

        .cta h2 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .cta p {
            margin: 0;
            color: #e8f8ff;
            line-height: 1.6;
        }

        footer {
            padding: 28px 16px;
            color: #667085;
            text-align: center;
            background: #fff;
            border-top: 1px solid #e4ecf3;
        }

        @media (max-width: 980px) {
            .hero-inner,
            .step {
                grid-template-columns: 1fr;
            }

            .journey,
            .tip-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .section-head,
            .cta {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 680px) {
            .nav {
                align-items: flex-start;
                flex-direction: column;
                padding: 14px 0;
            }

            .nav-links {
                width: 100%;
                justify-content: flex-start;
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 4px;
            }

            .nav-links a {
                white-space: nowrap;
            }

            .hero-inner {
                padding: 44px 0;
            }

            .journey,
            .tip-grid {
                grid-template-columns: 1fr;
            }

            .step {
                padding: 16px;
            }

            .screen img {
                min-height: 0;
            }
        }
    <?php if ($usuario_logado) { ?>.requires-guest{display:none!important}<?php } ?></style>

<link rel="stylesheet" href="responsive.css?v=menu-quatro-20260916">
<?php echo function_exists("fincontrol_version_styles") ? fincontrol_version_styles() : ""; ?>
</head>

<body class="<?php echo $usuario_logado ? "fc-logged-public-page" : ""; ?>">
<?php if ($usuario_logado) { ?>
<aside class="sidebar fc-standard-sidebar">
    <h2>FinControle</h2>
    <a href="03.menu.php"><i class="fa fa-gauge"></i> Dashboard</a>
    <a href="05.receitas.php"><i class="fa fa-plus-circle"></i> Receitas</a>
    <a href="04.despesas.php"><i class="fa fa-minus-circle"></i> Despesas</a>
    <a href="07.relatorios.php"><i class="fa fa-chart-line"></i> Relat&oacute;rios</a>
    <a href="16.tutorial.php" class="active"><i class="fa fa-circle-question"></i> Tutorial</a>
    
    <a href="16.tutorial.php"><i class="fa fa-headset"></i> Suporte</a>
    <a class="logout-link" href="15.logout.php"><i class="fa fa-sign-out"></i> Sair</a>
</aside>
<?php } ?>

    <header class="topbar">
        <nav class="nav" aria-label="Navegacao principal">
            <a class="brand" href="01.home.php">
                <i class="fa-solid fa-wallet"></i>
                FinControle
            </a>
            <button class="mobile-nav-toggle" type="button" aria-label="Abrir menu"><i class="fa-solid fa-bars"></i></button>

            <div class="nav-links">
                <?php if (!$usuario_logado) { ?><a href="01.home.php"><i class="fa-solid fa-house"></i> Início</a><?php } ?>
                <?php if (!$usuario_logado) { ?><a href="06.cadastrar_usuario.php"><i class="fa-solid fa-user-plus"></i> Cadastro</a><?php } ?>
                <a class="primary" href="<?php echo $usuario_logado ? "03.menu.php" : "02.login.php"; ?>"><i class="fa-solid fa-right-to-bracket"></i> <?php echo $usuario_logado ? "Dashboard" : "Entrar"; ?></a>
            </div>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-inner">
            <div>
                <span class="eyebrow"><i class="fa-solid fa-circle-play"></i> Tutorial do usuário</span>
                <h1>Aprenda a usar o FinControle do cadastro aos relatórios.</h1>
                <p>
                    Este guia acompanha a jornada completa: criar conta, entrar, entender o dashboard,
                    registrar receitas e despesas, e transformar os dados em relatórios úteis.
                </p>

                <div class="hero-actions">
                    <a class="btn light" href="#jornada"><i class="fa-solid fa-list-check"></i> Ver passo a passo</a>
                    <a class="btn outline requires-guest" href="06.cadastrar_usuario.php"><i class="fa-solid fa-user-plus"></i> Começar agora</a>
                </div>
            </div>

            <div class="hero-card" aria-label="Resumo da jornada">
                <h2>Fluxo recomendado</h2>
                <div class="mini-step"><strong>1</strong><p>Cadastre seus dados e aceite os termos para liberar o acesso.</p></div>
                <div class="mini-step"><strong>2</strong><p>Entre com e-mail e senha para acessar sua area financeira.</p></div>
                <div class="mini-step"><strong>3</strong><p>Alimente receitas e despesas para ver indicadores reais.</p></div>
                <div class="mini-step"><strong>4</strong><p>Use filtros e relatórios para analisar o período e exportar quando precisar.</p></div>
            </div>
        </div>
    </section>

    <main>
        <section class="section" id="jornada">
            <div class="section-head">
                <div>
                    <h2>Jornada do usuário</h2>
                </div>
                <p>
                    Siga a ordem abaixo na primeira utilizacao. Depois que sua conta estiver ativa,
                    você pode voltar direto para as telas de lançamento ou relatórios pelo menu lateral.
                </p>
            </div>

            <div class="journey">
                <?php if (!$usuario_logado) { ?><a href="#cadastro"><i class="fa-solid fa-user-plus"></i><span>Cadastro</span></a><?php } ?>
                <a href="#entrar"><i class="fa-solid fa-right-to-bracket"></i><span>Entrar</span></a>
                <a href="#dashboard"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
                <a href="#receita"><i class="fa-solid fa-circle-plus"></i><span>Receita</span></a>
                <a href="#despesa"><i class="fa-solid fa-circle-minus"></i><span>Despesa</span></a>
                
                <a href="#relatorios"><i class="fa-solid fa-chart-line"></i><span>Relatórios</span></a>
            </div>
        </section>

        <section class="section steps" aria-label="Passo a passo">
            <article class="step" id="cadastro">
                <div class="step-info">
                    <span class="step-number">1</span>
                    <h3>Cadastro</h3>
                    <p>
                        Acesse <strong>Cadastre-se</strong> para criar sua conta. Informe nome completo,
                        data de nascimento, e-mail, senha e confirmacao da senha.
                    </p>
                    <ul class="checklist">
                        <li><i class="fa-solid fa-check"></i><span>Marque o aceite dos Termos de Uso e Politica de Privacidade.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Use um e-mail valido, pois ele sera usado para entrar no sistema.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Apos cadastrar, siga para a tela de login.</span></li>
                    </ul>
                </div>

                <div class="screen empty" aria-label="Modelo visual da tela de cadastro">
                    <div class="mock-form">
                        <div class="mock-line"></div>
                        <div class="mock-line"></div>
                        <div class="mock-line"></div>
                        <div class="mock-line"></div>
                        <div class="mock-button"></div>
                    </div>
                    <div class="screen-caption">Tela de cadastro: dados pessoais, senha e aceite dos termos.</div>
                </div>
            </article>

            <article class="step" id="entrar">
                <div class="step-info">
                    <span class="step-number">2</span>
                    <h3>Entrar</h3>
                    <p>
                        Na tela <strong>Entrar</strong>, digite o e-mail cadastrado e a senha criada.
                        Depois clique no botao de acesso para abrir o dashboard.
                    </p>
                    <ul class="checklist">
                        <li><i class="fa-solid fa-check"></i><span>Se esquecer a senha, use o link de recuperacao disponivel no login.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Ao entrar, o menu lateral passa a mostrar as areas financeiras.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Use <strong>Sair</strong> quando terminar, especialmente em computador compartilhado.</span></li>
                    </ul>
                </div>

                <div class="screen empty" aria-label="Modelo visual da tela de login">
                    <div class="mock-form">
                        <div class="mock-line"></div>
                        <div class="mock-line"></div>
                        <div class="mock-button"></div>
                    </div>
                    <div class="screen-caption">Tela de login: e-mail, senha, recuperacao de senha e acesso.</div>
                </div>
            </article>

            <article class="step" id="dashboard">
                <div class="step-info">
                    <span class="step-number">3</span>
                    <h3>Visualizar dashboard</h3>
                    <p>
                        O dashboard resume sua vida financeira em cartões, gráficos e últimos lançamentos.
                        Ele mostra saldo, total de receitas, total de despesas e gastos do mês ou período filtrado.
                    </p>
                    <ul class="checklist">
                        <li><i class="fa-solid fa-check"></i><span>Use os filtros de data para analisar um intervalo especifico.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Confira os atalhos rápidos para criar nova receita, despesa ou gasto diário.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Observe gráficos de evolução mensal e distribuição por tipo de lançamento.</span></li>
                    </ul>
                </div>

                <figure class="screen">
                    <img src="img/1-dash-exemplo.png" alt="Exemplo do dashboard financeiro do FinControle">
                    <figcaption class="screen-caption">Dashboard: indicadores, filtros, gráficos e atalhos principais.</figcaption>
                </figure>
            </article>

            <article class="step" id="receita">
                <div class="step-info">
                    <span class="step-number">4</span>
                    <h3>Adicionar receita</h3>
                    <p>
                        Entre em <strong>Receitas</strong> para cadastrar entradas de dinheiro, como salario,
                        pagamento recebido, venda, beneficio ou qualquer outro valor positivo.
                    </p>
                    <ul class="checklist">
                        <li><i class="fa-solid fa-check"></i><span>Preencha nome da receita, valor total e data de recebimento.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Use quantidade de parcelas quando o recebimento for dividido.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Marque <strong>Recorrente</strong> para receitas que se repetem todos os meses.</span></li>
                    </ul>
                </div>

                <figure class="screen">
                    <img src="img/2-receita-exemplo.png" alt="Exemplo da tela de cadastro de receitas">
                    <figcaption class="screen-caption">Receitas: cadastro de entradas, parcelas, recorrência e lista de registros.</figcaption>
                </figure>
            </article>

            <article class="step" id="despesa">
                <div class="step-info">
                    <span class="step-number">5</span>
                    <h3>Adicionar despesas</h3>
                    <p>
                        A tela <strong>Despesas</strong> registra contas e compromissos financeiros,
                        como aluguel, mensalidades, compras parceladas ou contas fixas.
                    </p>
                    <ul class="checklist">
                        <li><i class="fa-solid fa-check"></i><span>Informe descrição, valor da parcela, quantidade de parcelas e data de vencimento.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Marque <strong>Recorrente</strong> para contas fixas mensais.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Confira a tabela abaixo do formulário para validar se a despesa entrou corretamente.</span></li>
                    </ul>
                </div>

                <figure class="screen">
                    <img src="img/3-despesa-exemplo.png" alt="Exemplo da tela de cadastro de despesas">
                    <figcaption class="screen-caption">Despesas: vencimento, parcelas, recorrência e exclusão de registros.</figcaption>
                </figure>
            </article>

            

            <article class="step" id="relatorios">
                <div class="step-info">
                    <span class="step-number">7</span>
                    <h3>Visualizar relatórios</h3>
                    <p>
                        Em <strong>Relatórios</strong>, você analisa os dados cadastrados com filtros globais,
                        indicadores, gráfico de fluxo financeiro e tabelas consolidadas.
                    </p>
                    <ul class="checklist">
                        <li><i class="fa-solid fa-check"></i><span>Filtre por data inicial, data final, mes, ano ou busca por despesa.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Compare receitas, despesas e saldo no gráfico de fluxo financeiro.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Use <strong>Exportar XLSX</strong> para baixar os dados em planilha.</span></li>
                    </ul>
                </div>

                <div class="screen empty" aria-label="Resumo visual da tela de relatórios">
                    <div class="mock-form">
                        <div class="mock-line"></div>
                        <div class="mock-line"></div>
                        <div class="mock-line"></div>
                        <div class="mock-button"></div>
                    </div>
                    <div class="screen-caption">Relatórios: filtros, indicadores, gráfico, tabelas e exportação XLSX.</div>
                </div>
            </article>
        </section>

        <section class="section">
            <div class="section-head">
                <div>
                    <h2>Boas praticas</h2>
                </div>
                <p>Pequenos hábitos deixam os relatórios mais confiáveis e tornam o dashboard muito mais útil.</p>
            </div>

            <div class="tip-grid">
                <div class="tip">
                    <i class="fa-solid fa-calendar-check"></i>
                    <h3>Registre na hora</h3>
                    <p>Lance gastos diários assim que acontecerem para evitar esquecimentos no fim do mês.</p>
                </div>
                <div class="tip">
                    <i class="fa-solid fa-repeat"></i>
                    <h3>Use recorrência</h3>
                    <p>Marque entradas e contas fixas como recorrentes para deixar a previsao mensal mais completa.</p>
                </div>
                <div class="tip">
                    <i class="fa-solid fa-filter"></i>
                    <h3>Análise por período</h3>
                    <p>Antes de decidir cortes ou metas, filtre relatórios por datas para comparar ciclos parecidos.</p>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="cta">
                <div>
                    <h2>Pronto para organizar seus dados?</h2>
                    <p>Crie sua conta, registre os primeiros lançamentos e acompanhe o resultado no dashboard.</p>
                </div>
                <a class="btn light requires-guest" href="06.cadastrar_usuario.php"><i class="fa-solid fa-user-plus"></i> Criar conta</a>
            </div>
        </section>
    </main>

    <footer>
        FinControle - Tutorial de uso
    </footer>
<?php echo function_exists("fincontrol_version_badge") ? fincontrol_version_badge() : ""; ?>
<script>document.querySelectorAll(".mobile-nav-toggle").forEach(function(b){b.addEventListener("click",function(){var n=document.querySelector(".nav-links"); if(n)n.classList.toggle("is-open");});});</script></body>

</html>
