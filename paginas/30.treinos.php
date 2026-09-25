<?php
require_once "00.sessao.php";
require_once "09.conexao.php";
require_once "00.version.php";
require_once "00.treinos.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: 02.login.php");
    exit();
}

$_SESSION['medidas_csrf'] = $_SESSION['medidas_csrf'] ?? bin2hex(random_bytes(32));
require_once __DIR__ . '/00.header_perfil.php';
require_once __DIR__ . '/00.cache.php';
$id_usuario = (int) $_SESSION['id_usuario'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao_dashboard'] ?? '') === 'upload_avatar') {
    if (uploadAvatarDashboard($id_usuario)) {
        fincontrol_cache_invalidar_usuario($id_usuario);
    }
    header('Location: 30.treinos.php', true, 303);
    exit;
}
$avatarDashboardSrc = avatarDashboardSrc($id_usuario);
$nome_usuario = $_SESSION["nome_usuario"] ?? "Usuario";
$primeiro_nome = trim(explode(" ", $nome_usuario)[0] ?? $nome_usuario);
$rotinas_padrao = fincontrol_treinos_rotina_padrao();
$rotinas_padrao += [
    'sab' => [
        'dia' => 'SAB',
        'nome' => 'Leve',
        'resumo' => 'Cardio leve, caminhada ou mobilidade.',
        'titulo' => 'Recuperacao ativa',
        'exercicios' => [],
    ],
    'dom' => [
        'dia' => 'DOM',
        'nome' => 'Pausa',
        'resumo' => 'Descanso para recuperar energia.',
        'titulo' => 'Descanso',
        'exercicios' => [],
    ],
];
$rotinas_full_body = fincontrol_treinos_rotina_full_body();
$tipos_treino = [
    'padrao' => [
        'nome' => 'Treino padrao',
        'resumo' => 'Cronograma atual validado por dia da semana.',
        'rotinas' => $rotinas_padrao,
    ],
    'fullbody' => [
        'nome' => 'Treino Full body',
        'resumo' => 'ABC com demanda alternada e exercicios do treino padrao.',
        'rotinas' => $rotinas_full_body,
    ],
];
$rotinas = $rotinas_padrao;
$mapa_dia = [1 => 'seg', 2 => 'ter', 3 => 'qua', 4 => 'qui', 5 => 'sex'];
$dia_padrao = $mapa_dia[(int)date('N')] ?? 'seg';
$dia_atual = $_GET['dia'] ?? $dia_padrao;
if (!isset($rotinas[$dia_atual])) {
    $dia_atual = 'seg';
}
$treino_atual = $rotinas[$dia_atual];
$versao = defined('FINCONTROL_VERSION') ? FINCONTROL_VERSION : '1.9.1';

function h($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function treino_imagem_data_uri(array $exercicio): string
{
    $video = (string)($exercicio['video'] ?? '');
    if (preg_match('~(?:youtube\.com/shorts/|youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{8,})~', $video, $match)) {
        return h('https://img.youtube.com/vi/' . $match[1] . '/hqdefault.jpg');
    }

    $imagem = trim((string)($exercicio['imagem'] ?? ''));
    if ($imagem !== '') {
        return h($imagem);
    }

    return h('https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?auto=format&fit=crop&w=640&q=80');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>FinControle | Treinos</title>
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg: #061a2a;
            --panel: #112b43;
            --panel-2: #153650;
            --line: rgba(95, 174, 219, .28);
            --text: #f8fbff;
            --muted: #9fb3c8;
            --brand: #12aeea;
            --green: #22c55e;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; background: var(--bg); color: var(--text); font-family: Arial, sans-serif; }
        body { display: flex; }
        a { color: inherit; text-decoration: none; }
        .sidebar {
            width: 250px; min-height: 100vh; padding: 22px 20px; background: #082236; border-right: 1px solid var(--line);
            position: sticky; top: 0;
        }
        .brand { display: flex; align-items: center; gap: 12px; font-size: 24px; font-weight: 800; margin-bottom: 28px; }
        .brand i { color: var(--brand); }
        .sidebar a { display: flex; align-items: center; gap: 12px; padding: 14px 12px; border-radius: 10px; color: #c9d6e5; font-weight: 700; margin-bottom: 8px; }
        .sidebar a.active, .sidebar a:hover { background: #118db8; color: #fff; }
        .main { width: 100%; max-width: 1180px; margin: 0 auto; padding: 24px 28px 92px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 18px; }
        .topbar h1 { margin: 0; font-size: 34px; line-height: 1; }
        .topbar p { margin: 6px 0 0; color: var(--muted); }
        .user-pill { width: 42px; height: 42px; border-radius: 999px; background: var(--panel); display: grid; place-items: center; color: var(--brand); }
        .hero, .card, .exercise-card {
            background: linear-gradient(145deg, rgba(20, 58, 85, .96), rgba(8, 31, 51, .96));
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 18px 42px rgba(0,0,0,.22);
        }
        .hero { padding: 22px; display: grid; grid-template-columns: 1fr auto; gap: 16px; align-items: center; margin-bottom: 16px; }
        .hero h2 { margin: 0; font-size: 26px; }
        .hero p { margin: 8px 0 0; color: var(--muted); max-width: 620px; }
        .primary-btn {
            border: 0; border-radius: 14px; padding: 14px 18px; min-height: 48px; color: #fff; font-weight: 800;
            background: linear-gradient(135deg, #12c8ef, #148de2); cursor: pointer;
        }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
        .card { padding: 16px; }
        .card strong { display: block; font-size: 26px; margin-top: 7px; }
        .card span { color: var(--muted); font-size: 13px; font-weight: 700; }
        .days { display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; margin: 14px 0; }
        .day {
            border-radius: 14px; padding: 11px 6px; background: var(--panel); border: 1px solid var(--line); text-align: center;
            color: var(--text); font-family: inherit; cursor: pointer;
        }
        .day.active { background: linear-gradient(135deg, #12c8ef, #148de2); }
        .day b { display: block; font-size: 14px; }
        .day small { color: #c8d8e8; }
        .workout-types { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin: 12px 0; }
        .workout-type {
            border: 1px solid var(--line); border-radius: 14px; padding: 12px; text-align: left; background: rgba(255,255,255,.04);
            color: var(--text); font: inherit; cursor: pointer;
        }
        .workout-type strong, .workout-type small { display: block; }
        .workout-type strong { font-weight: 900; }
        .workout-type small { color: var(--muted); margin-top: 4px; line-height: 1.25; }
        .workout-type.active { border-color: rgba(18, 200, 239, .75); background: rgba(18, 174, 234, .22); }
        .workout-days[hidden] { display: none; }
        .section-title { display: flex; justify-content: space-between; align-items: center; margin: 18px 0 12px; }
        .section-title h2 { margin: 0; font-size: 25px; }
        .exercise-list { display: grid; gap: 12px; }
        .exercise-card { overflow: hidden; }
        .exercise-main { display: grid; grid-template-columns: 150px 1fr auto; gap: 16px; align-items: center; padding: 14px; }
        .exercise-main img { width: 150px; height: 94px; object-fit: cover; border-radius: 14px; border: 1px solid var(--line); }
        .exercise-card h3 { margin: 0 0 6px; font-size: 21px; }
        .exercise-card p { margin: 0; color: var(--muted); font-weight: 700; }
        .badge { display: inline-flex; align-items: center; justify-content: center; min-width: 42px; height: 42px; border-radius: 999px; background: var(--brand); font-weight: 900; }
        .exercise-actions { display: grid; gap: 8px; justify-items: center; }
        .exercise-check {
            width: 42px; height: 42px; border: 1px solid rgba(125, 211, 252, .45); border-radius: 999px;
            background: rgba(15, 143, 197, .14); color: #7dd3fc; display: inline-flex; align-items: center; justify-content: center;
            font: inherit; cursor: pointer; transition: .18s ease;
        }
        .exercise-check.is-done { background: #22c55e; border-color: #22c55e; color: #fff; }
        .exercise-card.exercise-done { border-color: rgba(34, 197, 94, .7); }
        details { border-top: 1px solid var(--line); padding: 0 14px 14px; }
        summary { cursor: pointer; padding: 14px 0 6px; font-weight: 800; color: #7dd3fc; }
        .tips { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .tips h4 { margin: 0 0 8px; }
        .tips ul { margin: 0; padding-left: 18px; color: #d7e3ef; }
        .video-link { display: inline-flex; gap: 8px; align-items: center; margin-top: 12px; color: #fff; background: #0f8fc5; border-radius: 12px; padding: 10px 12px; font-weight: 800; }
        .bottom-nav { display: none; }
        .mobile-menu-backdrop { position: fixed; inset: 0; background: rgba(2,10,17,.72); z-index: 30; display: grid; place-items: end center; padding: 20px; }
        .mobile-menu-backdrop[hidden] { display: none; }
        .mobile-menu-panel { width: min(430px, 100%); background: linear-gradient(180deg, #133653, #0b263d); border: 1px solid rgba(40,180,235,.22); border-radius: 22px; padding: 18px; color: #eaf6ff; box-shadow: 0 30px 80px rgba(0,0,0,.38); }
        .mobile-menu-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .mobile-menu-head button { border: 0; width: 42px; height: 42px; border-radius: 12px; background: rgba(34,199,242,.18); color: #fff; font-size: 18px; }
        .mobile-menu-panel a { display: flex; gap: 12px; align-items: center; padding: 14px; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.06); border-radius: 12px; margin-top: 10px; font-weight: 800; color: #eaf6ff; }
        .mobile-menu-panel a i { color: #22c7f2; }
        body.treinos-app .mobile-menu-panel a[href="30.treinos.php"] { background: rgba(34,199,242,.16); color: #22c7f2; border-color: rgba(34,199,242,.2); }
        @media (max-width: 760px) {
            body { display: block; background: radial-gradient(circle at top right, #06435f 0, #061a2a 32%, #041522 100%); }
            .sidebar { display: none; }
            body.no-scroll { overflow: hidden; }
            .main { max-width: 430px; min-height: 100svh; padding: 14px 12px 84px; display: flex; flex-direction: column; gap: 12px; overflow: hidden; }
            .topbar {
                display: block;
                margin: 0;
                padding: 0;
            }
            .brand-mini {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 13px;
                font-weight: 900;
                letter-spacing: .02em;
                text-transform: uppercase;
                margin-bottom: 10px;
            }
            .brand-mini i { color: var(--brand); }
            .greeting-row {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .avatar-badge {
                width: 42px;
                height: 42px;
                border-radius: 999px;
                background: #2f4357;
                display: grid;
                place-items: center;
                font-weight: 900;
                color: #fff;
                overflow: hidden;
            }
            .avatar-badge img { width: 100%; height: 100%; object-fit: cover; }
            .greeting-row span { display: block; color: var(--muted); font-weight: 700; font-size: 13px; }
            .topbar h1 { font-size: 21px; margin: 1px 0 0; }
            .topbar p { display: none; }
            .user-pill { display: none; }
            .hero { grid-template-columns: 1fr; padding: 16px; }
            .hero h2 { font-size: 22px; }
            .hero p { font-size: 13px; line-height: 1.35; }
            .primary-btn { width: 100%; }
            .stats { grid-template-columns: repeat(3, 1fr); gap: 8px; }
            .card { padding: 12px 10px; min-height: 86px; }
            .card strong { font-size: 19px; }
            .days {
                grid-template-columns: repeat(7, minmax(0, 1fr));
                gap: 5px;
                overflow: visible;
                margin: 8px 0 10px;
            }
            .day { min-width: 0; padding: 8px 2px; border-radius: 12px; }
            .day b { font-size: 12px; }
            .day small { display: block; font-size: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .workout-types { gap: 7px; margin: 8px 0; }
            .workout-type { padding: 9px; border-radius: 12px; }
            .workout-type strong { font-size: 12px; }
            .workout-type small { font-size: 10px; }
            .treino-home-only { display: none; }
            .exercise-main { grid-template-columns: 82px 1fr auto; gap: 10px; padding: 8px; }
            .exercise-main img { width: 82px; height: 58px; border-radius: 12px; }
            .exercise-card h3 { font-size: 14px; }
            .exercise-card p { font-size: 11px; }
            .badge { min-width: 34px; height: 34px; }
            .exercise-actions { gap: 5px; }
            .exercise-check { width: 34px; height: 34px; }
            .tips { grid-template-columns: 1fr; }
            details { padding: 0 10px 8px; }
            summary { padding: 8px 0 3px; font-size: 12px; }
            .workout-panel { overflow: hidden; padding: 16px; }
            .workout-panel > p { margin-bottom: 6px; font-size: 13px; }
            .workout-head { margin-bottom: 6px; }
            .workout-head h2 { font-size: 23px; }
            .section-title { margin: 8px 0; }
            .section-title h2 { font-size: 21px; }
            .section-title p { font-size: 12px; }
            .section-title .primary-btn { min-height: 40px; padding: 10px 12px; font-size: 12px; }
            .exercise-list { gap: 8px; }
            .bottom-nav {
                display: grid; grid-template-columns:repeat(5,1fr); gap: 4px; position: fixed; left: 0; right: 0; bottom: 0; z-index: 20;
                padding: 10px 8px calc(10px + env(safe-area-inset-bottom)); background: rgba(5, 24, 39, .98); border-top: 1px solid var(--line);
            }
            .bottom-nav a { display: grid; place-items: center; gap: 3px; color: #a9bbcc; font-size: 11px; font-weight: 800; }
            .bottom-nav a.active { color: #18bdf2; }
            .bottom-nav i { font-size: 18px; }
        }
        .workout-backdrop {
            position: fixed;
            inset: 0;
            z-index: 35;
            background: rgba(0, 0, 0, .72);
            display: grid;
            place-items: center;
            padding: 14px;
        }
        .workout-backdrop[hidden] { display: none; }
        .workout-panel {
            width: min(460px, calc(100vw - 24px));
            max-height: calc(100svh - 28px);
            overflow: auto;
            background: #102c44;
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 18px;
            box-shadow: 0 26px 70px rgba(0,0,0,.45);
        }
        .workout-step[hidden] { display: none; }
        .workout-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }
        .workout-head.is-detail { align-items: flex-start; }
        .workout-head-copy { flex: 1; min-width: 0; }
        .workout-head-copy p { margin: 4px 0 0; color: var(--muted); font-size: 13px; font-weight: 700; }
        .workout-head h2 { margin: 0; font-size: 24px; }
        .workout-head button {
            border: 0;
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: #183a57;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
        }
        .workout-panel > p { margin: 0 0 12px; color: var(--muted); font-weight: 700; }
        .workout-day-panel[hidden] { display: none; }
    </style>
    <link rel="stylesheet" href="assets/medidas.css?v=3">
    <link rel="stylesheet" href="assets/header-perfil.css?v=1">
</head>
<body class="treinos-app">
    <aside class="sidebar">
        <div class="brand"><i class="fa fa-wallet"></i> FinControle</div>
        <a href="03.menu.php"><i class="fa fa-house"></i> Dashboard</a>
        <a href="04.despesas.php"><i class="fa fa-minus-circle"></i> Despesas</a>
        <a href="05.receitas.php"><i class="fa fa-plus-circle"></i> Receitas</a>
        
        <a href="07.relatorios.php"><i class="fa fa-chart-line"></i> Relatórios</a>
        <a class="active" href="30.treinos.php"><i class="fa fa-dumbbell"></i> Treinos</a>
        <a href="15.logout.php"><i class="fa fa-sign-out"></i> Sair</a>
    </aside>

    <main class="main">
        <?php require __DIR__ . '/00.header_perfil_view.php'; ?>

        <section class="hero">
            <div>
                <h2>Meus treinos</h2>
                <p>Sua rotina de treino e evolução.</p>
            </div>
            <div class="treino-actions">
                <button class="primary-btn" type="button" data-open-workout><i class="fa fa-play"></i> Iniciar treino</button>
                <button class="primary-btn medidas-open" type="button" data-open-medidas><i class="fa-solid fa-ruler"></i> Medidas</button>
            </div>
        </section>

        <section class="stats" aria-label="Indicadores de treino">
            <div class="card"><span>Treinos no mês</span><strong data-treinos-mes>0</strong></div>
            <div class="card"><span>Sequência</span><strong data-sequencia>0</strong></div>
            <div class="card"><span>Último treino</span><strong data-ultimo>-</strong></div>
        </section>
        <section class="medidas-evolution" aria-labelledby="evolution-title" data-medidas-evolution>
            <header class="evolution-head">
                <h2 id="evolution-title">Minha evolução</h2>
                <select aria-label="Período da evolução" data-evolution-period>
                    <option value="30">30 dias</option>
                    <option value="90" selected>90 dias</option>
                    <option value="365">1 ano</option>
                </select>
            </header>
            <div class="evolution-tabs" role="tablist" aria-label="Medida do gráfico">
                <button type="button" role="tab" id="evolution-peso" aria-controls="evolution-panel" aria-selected="true" data-evolution-metric="peso">Peso</button>
                <button type="button" role="tab" id="evolution-abdomen" aria-controls="evolution-panel" aria-selected="false" tabindex="-1" data-evolution-metric="abdomen">Abdômen</button>
                <button type="button" role="tab" id="evolution-biceps" aria-controls="evolution-panel" aria-selected="false" tabindex="-1" data-evolution-metric="biceps">Bíceps (média)</button>
            </div>
            <div id="evolution-panel" role="tabpanel" aria-labelledby="evolution-peso">
                <p class="evolution-summary" data-evolution-summary aria-live="polite"></p>
                <div class="evolution-chart">
                    <canvas data-evolution-chart role="img" aria-label="Evolução do peso por data" hidden></canvas>
                    <p data-evolution-status role="status">Carregando medidas...</p>
                    <button type="button" class="evolution-retry" data-evolution-retry hidden>Tentar novamente</button>
                </div>
            </div>
        </section>
    </main>

    <div class="workout-backdrop" data-workout-modal hidden>
        <section class="workout-panel workout-step workout-picker" data-workout-step="picker" role="dialog" aria-modal="true" aria-label="Escolher dia do treino">
            <div class="workout-head">
                <h2>Meus treinos</h2>
                <button type="button" data-close-workout aria-label="Fechar">×</button>
            </div>
            <p>Escolha uma rotina e veja os exercicios.</p>

            <div class="workout-types" aria-label="Meus treinos">
                <?php foreach ($tipos_treino as $tipo_codigo => $tipo_treino): ?>
                    <button class="workout-type <?= $tipo_codigo === 'padrao' ? 'active' : ''; ?>" type="button" data-plan-option="<?= h($tipo_codigo); ?>">
                        <strong><?= h($tipo_treino['nome']); ?></strong>
                        <small><?= h($tipo_treino['resumo']); ?></small>
                    </button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($tipos_treino as $tipo_codigo => $tipo_treino): ?>
                <nav class="days workout-days" aria-label="<?= h($tipo_treino['nome']); ?>" data-plan-days="<?= h($tipo_codigo); ?>" <?= $tipo_codigo === 'padrao' ? '' : 'hidden'; ?>>
                    <?php foreach ($tipo_treino['rotinas'] as $codigo => $rotina): ?>
                        <button class="day <?= $tipo_codigo === 'padrao' && $codigo === $dia_atual ? 'active' : ''; ?>" type="button" data-plan-target="<?= h($tipo_codigo); ?>" data-day-target="<?= h($codigo); ?>">
                            <b><?= h($rotina['dia']); ?></b>
                            <small><?= h($rotina['nome']); ?></small>
                        </button>
                    <?php endforeach; ?>
                </nav>
            <?php endforeach; ?>
        </section>

        <section class="workout-panel workout-step workout-detail" data-workout-step="detail" role="dialog" aria-modal="true" aria-label="Exercícios do treino" hidden>
            <div class="workout-head is-detail">
                <button type="button" data-back-workout-days aria-label="Escolher outro dia"><i class="fa fa-arrow-left"></i></button>
                <div class="workout-head-copy">
                    <h2>Treino escolhido</h2>
                    <p>Execute a lista com atenção à técnica.</p>
                </div>
                <button type="button" data-close-workout aria-label="Fechar">×</button>
            </div>

            <?php foreach ($tipos_treino as $tipo_codigo => $tipo_treino): ?>
            <?php foreach ($tipo_treino['rotinas'] as $codigo => $rotina): ?>
                <div class="workout-day-panel" data-plan-panel="<?= h($tipo_codigo); ?>" data-day-panel="<?= h($codigo); ?>" <?= $tipo_codigo === 'padrao' && $codigo === $dia_atual ? '' : 'hidden'; ?>>
                    <div class="section-title" id="treino-do-dia-<?= h($codigo); ?>">
                        <div>
                            <h2><?= h($rotina['titulo']); ?></h2>
                            <p style="margin:4px 0 0;color:var(--muted);"><?= h($rotina['resumo']); ?></p>
                        </div>
                        <button class="primary-btn" style="width:auto;" type="button" data-concluir-treino data-plano="<?= h($tipo_codigo); ?>" data-dia="<?= h($codigo); ?>">
                            <i class="fa fa-check"></i> Concluir
                        </button>
                    </div>

                    <section class="exercise-list">
                        <?php if (empty($rotina['exercicios'])): ?>
                            <article class="exercise-card">
                                <div class="exercise-main">
                                    <img src="<?= treino_imagem_data_uri(['tipo' => 'alongamento', 'nome' => 'Mobilidade leve']); ?>" alt="Dia de recuperacao ativa">
                                    <div>
                                        <h3>Dia sem treino pesado</h3>
                                        <p>Descanso, caminhada ou mobilidade leve.</p>
                                    </div>
                                    <span class="badge"><i class="fa fa-heart"></i></span>
                                </div>
                            </article>
                        <?php endif; ?>
                        <?php foreach ($rotina['exercicios'] as $index => $exercicio): ?>
                            <article class="exercise-card">
                                <div class="exercise-main">
                                    <img src="<?= treino_imagem_data_uri($exercicio); ?>" alt="Imagem para identificar <?= h($exercicio['nome']); ?>">
                                    <div>
                                        <h3><?= h($exercicio['nome']); ?></h3>
                                        <p><?= h($exercicio['series']); ?> • <?= h($exercicio['foco']); ?></p>
                                    </div>
                                    <div class="exercise-actions">
                                        <button class="exercise-check" type="button" data-check-exercicio="<?= h($tipo_codigo . '-' . $codigo . '-' . $index); ?>" aria-label="Marcar <?= h($exercicio['nome']); ?> como concluído" aria-pressed="false">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <span class="badge"><?= $index + 1; ?></span>
                                    </div>
                                </div>
                                <details>
                                    <summary>Execução correta e erros comuns</summary>
                                    <div class="tips">
                                        <div>
                                            <h4>Como executar</h4>
                                            <ul>
                                                <?php foreach ($exercicio['como'] as $item): ?><li><?= h($item); ?></li><?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <div>
                                            <h4>Erros comuns</h4>
                                            <ul>
                                                <?php foreach ($exercicio['erros'] as $item): ?><li><?= h($item); ?></li><?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <a class="video-link" href="<?= h($exercicio['video']); ?>" target="_blank" rel="noopener">
                                        <i class="fa-brands fa-youtube"></i> Assistir tutorial
                                    </a>
                                </details>
                            </article>
                        <?php endforeach; ?>
                    </section>
                </div>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </section>
    </div>

    <nav class="bottom-nav" aria-label="Menu inferior">
        <a href="03.menu.php"><i class="fa fa-house"></i><span>Início</span></a>
        <a href="04.despesas.php"><i class="fa fa-minus-circle"></i><span>Despesas</span></a>
        <a href="05.receitas.php"><i class="fa fa-plus-circle"></i><span>Receitas</span></a>
        
        <a href="07.relatorios.php"><i class="fa fa-chart-line"></i><span>Relatórios</span></a>
    <a class="active" href="#" data-fc-open-mobile-menu><i class="fa fa-bars"></i><span>Menu</span></a>
    </nav>

    <div class="mobile-menu-backdrop" data-fc-mobile-menu hidden>
        <div class="mobile-menu-panel">
            <div class="mobile-menu-head">
                <h2>Menu completo</h2>
                <button type="button" data-fc-close-mobile-menu aria-label="Fechar">×</button>
            </div>
            <a href="03.menu.php"><i class="fa fa-house"></i> Início</a>
            <a href="04.despesas.php"><i class="fa fa-minus-circle"></i> Despesas</a>
            <a href="05.receitas.php"><i class="fa fa-plus-circle"></i> Receitas</a>
            
            <a href="07.relatorios.php"><i class="fa fa-chart-line"></i> Relatórios</a>
            <a href="29.minhas_noticias.php"><i class="fa fa-newspaper"></i> Minhas notícias</a>
            <a href="30.treinos.php"><i class="fa fa-dumbbell"></i> Treinos</a>
            <a href="15.logout.php"><i class="fa fa-sign-out"></i> Sair</a>
        </div>
    </div>

    <script>
    (function () {
        const menu = document.querySelector('[data-fc-mobile-menu]');
        document.querySelectorAll('[data-fc-open-mobile-menu]').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                if (menu) menu.hidden = false;
            });
        });
        document.querySelectorAll('[data-fc-close-mobile-menu]').forEach((btn) => {
            btn.addEventListener('click', () => { if (menu) menu.hidden = true; });
        });
        if (menu) {
            menu.addEventListener('click', (event) => {
                if (event.target === menu) menu.hidden = true;
            });
        }

        const workoutModal = document.querySelector('[data-workout-modal]');
        const workoutPicker = document.querySelector('[data-workout-step="picker"]');
        const workoutDetail = document.querySelector('[data-workout-step="detail"]');
        let selectedPlan = 'padrao';
        const showWorkoutPicker = () => {
            if (workoutPicker) workoutPicker.hidden = false;
            if (workoutDetail) workoutDetail.hidden = true;
        };
        const showWorkoutDetail = () => {
            if (workoutPicker) workoutPicker.hidden = true;
            if (workoutDetail) workoutDetail.hidden = false;
        };
        const openWorkout = () => {
            if (!workoutModal) return;
            showWorkoutPicker();
            workoutModal.hidden = false;
            document.body.classList.add('no-scroll');
        };
        const closeWorkout = () => {
            if (!workoutModal) return;
            workoutModal.hidden = true;
            document.body.classList.remove('no-scroll');
        };
        document.querySelector('[data-open-workout]')?.addEventListener('click', openWorkout);
        document.querySelectorAll('[data-close-workout]').forEach((btn) => btn.addEventListener('click', closeWorkout));
        document.querySelector('[data-back-workout-days]')?.addEventListener('click', showWorkoutPicker);
        workoutModal?.addEventListener('click', (event) => {
            if (event.target === workoutModal) closeWorkout();
        });
        document.querySelectorAll('[data-plan-option]').forEach((btn) => {
            btn.addEventListener('click', () => {
                selectedPlan = btn.dataset.planOption || 'padrao';
                document.querySelectorAll('[data-plan-option]').forEach((item) => item.classList.toggle('active', item === btn));
                document.querySelectorAll('[data-plan-days]').forEach((nav) => {
                    nav.hidden = nav.dataset.planDays !== selectedPlan;
                });
                document.querySelectorAll('[data-plan-target]').forEach((item) => {
                    item.classList.toggle('active', item.dataset.planTarget === selectedPlan && item.parentElement && !item.parentElement.hidden && item === item.parentElement.querySelector('[data-plan-target]'));
                });
            });
        });
        document.querySelectorAll('[data-day-target]').forEach((btn) => {
            btn.addEventListener('click', () => {
                selectedPlan = btn.dataset.planTarget || selectedPlan;
                const day = btn.dataset.dayTarget;
                document.querySelectorAll('[data-day-target]').forEach((item) => {
                    item.classList.toggle('active', item.dataset.planTarget === selectedPlan && item === btn);
                });
                document.querySelectorAll('[data-day-panel]').forEach((panel) => {
                    panel.hidden = panel.dataset.planPanel !== selectedPlan || panel.dataset.dayPanel !== day;
                });
                showWorkoutDetail();
            });
        });

        const key = 'fincontrole_treinos_execucoes_v1';
        const exerciseKey = 'fincontrole_treinos_exercicios_v1';
        const historyEndpoint = '31.treinos_historico.php';
        const getHistorico = () => {
            try { return JSON.parse(localStorage.getItem(key) || '[]'); } catch (e) { return []; }
        };
        const setHistorico = (historico) => localStorage.setItem(key, JSON.stringify(historico));
        const getExerciciosConcluidos = () => {
            try { return JSON.parse(localStorage.getItem(exerciseKey) || '{}'); } catch (e) { return {}; }
        };
        const setExerciciosConcluidos = (dados) => localStorage.setItem(exerciseKey, JSON.stringify(dados));
        const atualizarChecksExercicios = () => {
            const concluidos = getExerciciosConcluidos();
            document.querySelectorAll('[data-check-exercicio]').forEach((botao) => {
                const feito = !!concluidos[botao.dataset.checkExercicio];
                botao.classList.toggle('is-done', feito);
                botao.setAttribute('aria-pressed', feito ? 'true' : 'false');
                const card = botao.closest('.exercise-card');
                if (card) card.classList.toggle('exercise-done', feito);
            });
        };
        const hojeISO = () => new Date().toISOString().slice(0, 10);
        const aplicarHistoricoServidor = (dados) => {
            if (!dados || !dados.ok || !Array.isArray(dados.historico)) return;
            const historicoServidor = dados.historico.map((item) => ({
                id: item.id || null,
                data: item.data || hojeISO(),
                plano: item.plano || 'padrao',
                dia: item.dia || 'treino',
                total_exercicios: item.total_exercicios || 0,
                exercicios: Array.isArray(item.exercicios) ? item.exercicios : []
            }));
            const mesclado = [];
            const vistos = new Set();
            [...getHistorico(), ...historicoServidor].forEach((item) => {
                const chave = item.id ? `srv-${item.id}` : `${item.data}-${item.plano}-${item.dia}-${item.total_exercicios || 0}`;
                if (vistos.has(chave)) return;
                vistos.add(chave);
                mesclado.push(item);
            });
            mesclado.sort((a, b) => String(a.data || '').localeCompare(String(b.data || '')));
            setHistorico(mesclado);
            atualizaIndicadores();
        };
        const carregarHistoricoServidor = async () => {
            try {
                const resposta = await fetch(historyEndpoint, { credentials: 'same-origin', cache: 'no-store' });
                if (!resposta.ok) return;
                aplicarHistoricoServidor(await resposta.json());
            } catch (e) {}
        };
        const exerciciosMarcadosDoPainel = (painel) => {
            if (!painel) return [];
            return Array.from(painel.querySelectorAll('[data-check-exercicio]'))
                .filter((botao) => botao.classList.contains('is-done'))
                .map((botao) => botao.dataset.checkExercicio);
        };
        const limparChecksDoPainel = (painel) => {
            if (!painel) return;
            const concluidos = getExerciciosConcluidos();
            painel.querySelectorAll('[data-check-exercicio]').forEach((botao) => {
                delete concluidos[botao.dataset.checkExercicio];
            });
            setExerciciosConcluidos(concluidos);
            atualizarChecksExercicios();
        };
        const voltarParaPrincipalTreinos = () => {
            closeWorkout();
            showWorkoutPicker();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };
        const salvarHistoricoServidor = async (registro, painel) => {
            const payload = {
                data: registro.data,
                plano: registro.plano,
                dia: registro.dia,
                total_exercicios: painel ? painel.querySelectorAll('[data-check-exercicio]').length : 0,
                exercicios: exerciciosMarcadosDoPainel(painel)
            };
            try {
                const resposta = await fetch(historyEndpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    cache: 'no-store',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                if (!resposta.ok) return;
                aplicarHistoricoServidor(await resposta.json());
            } catch (e) {}
        };
        const atualizaIndicadores = () => {
            const historico = getHistorico();
            const agora = new Date();
            const mesAtual = String(agora.getMonth() + 1).padStart(2, '0');
            const anoAtual = String(agora.getFullYear());
            const treinosMes = historico.filter((item) => item.data.slice(0, 7) === `${anoAtual}-${mesAtual}`);
            const diasUnicos = new Set(treinosMes.map((item) => item.data)).size;
            const ultimo = historico.length ? historico[historico.length - 1] : null;
            document.querySelector('[data-treinos-mes]').textContent = treinosMes.length;
            document.querySelector('[data-sequencia]').textContent = diasUnicos;
            document.querySelector('[data-ultimo]').textContent = ultimo ? ultimo.dia.toUpperCase() : '-';
            document.querySelectorAll('[data-hist-ultimo]').forEach((el) => { el.textContent = ultimo ? ultimo.dia.toUpperCase() : '-'; });
            document.querySelectorAll('[data-hist-mes]').forEach((el) => { el.textContent = `${treinosMes.length} treino(s)`; });
            document.querySelectorAll('[data-hist-sequencia]').forEach((el) => { el.textContent = `${diasUnicos} dia(s)`; });
        };
        document.querySelectorAll('[data-concluir-treino]').forEach((botao) => {
            botao.addEventListener('click', async () => {
                const painel = botao.closest('[data-day-panel]');
                const confirmar = window.confirm('Concluir treino?');
                if (!confirmar) return;

                botao.disabled = true;
                const historico = getHistorico();
                const registro = { data: hojeISO(), plano: botao.dataset.plano || selectedPlan, dia: botao.dataset.dia || 'treino' };
                historico.push(registro);
                setHistorico(historico);
                atualizaIndicadores();
                await salvarHistoricoServidor(registro, painel);
                limparChecksDoPainel(painel);
                botao.disabled = false;
                voltarParaPrincipalTreinos();
            });
        });
        document.querySelectorAll('[data-check-exercicio]').forEach((botao) => {
            botao.addEventListener('click', () => {
                const concluidos = getExerciciosConcluidos();
                const id = botao.dataset.checkExercicio;
                if (concluidos[id]) {
                    delete concluidos[id];
                } else {
                    concluidos[id] = hojeISO();
                }
                setExerciciosConcluidos(concluidos);
                atualizarChecksExercicios();
            });
        });
        atualizaIndicadores();
        atualizarChecksExercicios();
        carregarHistoricoServidor();
    })();
    </script>
    <?php require __DIR__ . '/32.medidas_popup.php'; ?>
    <script src="assets/chart.umd.min.js" defer></script>
    <script src="assets/medidas-evolucao.js?v=1" defer></script>
    <script src="assets/medidas.js?v=3" defer></script>
</body>
</html>
