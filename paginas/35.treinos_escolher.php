<?php
require_once __DIR__ . '/00.sessao.php';
require_once __DIR__ . '/09.conexao.php';

if (empty($_SESSION['id_usuario'])) {
    header('Location: 02.login.php');
    exit;
}

$_SESSION['treinos_csrf'] = $_SESSION['treinos_csrf'] ?? bin2hex(random_bytes(32));
require_once __DIR__ . '/00.header_perfil.php';
require_once __DIR__ . '/00.cache.php';

$id_usuario = (int) $_SESSION['id_usuario'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao_dashboard'] ?? '') === 'upload_avatar') {
    if (uploadAvatarDashboard($id_usuario)) {
        fincontrol_cache_invalidar_usuario($id_usuario);
    }
    header('Location: 35.treinos_escolher.php', true, 303);
    exit;
}

$avatarDashboardSrc = avatarDashboardSrc($id_usuario);
$nome_usuario = $_SESSION['nome_usuario'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="treinos-csrf" content="<?= htmlspecialchars($_SESSION['treinos_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
    <title>FinControle | Área de treinos</title>
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/header-perfil.css?v=1">
    <link rel="stylesheet" href="assets/treinos-master.css?v=2">
    <link rel="stylesheet" href="assets/treinos-exercise-images.css?v=1">
</head>
<body class="treinos-app treinos-master-app">
    <aside class="sidebar">
        <div class="brand"><i class="fa fa-wallet"></i> FinControle</div>
        <a href="03.menu.php"><i class="fa fa-house"></i> Início</a>
        <a href="04.despesas.php"><i class="fa fa-minus-circle"></i> Despesas</a>
        <a href="05.receitas.php"><i class="fa fa-plus-circle"></i> Receitas</a>
        <a href="07.relatorios.php"><i class="fa fa-chart-line"></i> Relatórios</a>
        <a class="active" href="30.treinos.php"><i class="fa fa-dumbbell"></i> Treinos</a>
        <a href="15.logout.php"><i class="fa fa-sign-out"></i> Sair</a>
    </aside>

    <main class="main">
        <?php require __DIR__ . '/00.header_perfil_view.php'; ?>

        <header class="master-heading">
            <div>
                <a class="back-link" href="30.treinos.php"><i class="fa-solid fa-arrow-left"></i> Meus treinos</a>
                <h2>Área de treinos</h2>
                <p>Escolha uma rotina pronta ou monte um treino para o seu objetivo.</p>
            </div>
            <span class="catalog-count" data-catalog-count><i class="fa-solid fa-dumbbell"></i> Carregando catálogo</span>
        </header>

        <nav class="master-tabs" aria-label="Opções da área de treinos">
            <button class="active" type="button" data-master-tab="selecionar" aria-selected="true">
                <i class="fa-solid fa-list-check"></i><span><strong>Selecionar treino</strong><small>Treinos prontos e salvos</small></span>
            </button>
            <button type="button" data-master-tab="montar" aria-selected="false">
                <i class="fa-solid fa-sliders"></i><span><strong>Montar treino</strong><small>Por músculo ou região</small></span>
            </button>
        </nav>

        <section data-master-panel="selecionar">
            <article class="smart-suggestion" data-smart-suggestion hidden>
                <div class="smart-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
                <div><span>Sugestão inteligente para hoje</span><strong data-smart-muscle>-</strong><p data-smart-reason></p></div>
                <button type="button" data-use-suggestion>Montar sugestão</button>
            </article>

            <div class="section-heading"><div><h3>Treinos prontos</h3><p>Rotinas estruturadas para começar imediatamente.</p></div></div>
            <div class="ready-list">
                <a class="ready-row" href="37.treinos_prontos.php?plano=padrao"><span class="ready-icon"><i class="fa-solid fa-calendar-week"></i></span><span><strong>Treino padrão semanal</strong><small>Peito, costas, pernas, ombros e posterior organizados por dia.</small></span><i class="fa-solid fa-chevron-right"></i></a>
                <a class="ready-row" href="37.treinos_prontos.php?plano=fullbody"><span class="ready-icon"><i class="fa-solid fa-arrows-rotate"></i></span><span><strong>Full body ABC</strong><small>Três sessões equilibradas para trabalhar o corpo todo.</small></span><i class="fa-solid fa-chevron-right"></i></a>
            </div>

            <div class="section-heading saved-heading"><div><h3>Meus treinos montados</h3><p>Treinos personalizados salvos por você.</p></div><button type="button" class="text-action" data-go-builder><i class="fa-solid fa-plus"></i> Novo</button></div>
            <p class="master-status" data-master-status role="status">Carregando seus treinos...</p>
            <div class="saved-list" data-saved-list></div>
            <div class="empty-state" data-saved-empty hidden><i class="fa-solid fa-clipboard-list"></i><strong>Nenhum treino personalizado</strong><p>Monte sua primeira rotina por músculo ou região do corpo.</p><button type="button" data-go-builder>Montar treino</button></div>
        </section>

        <section data-master-panel="montar" hidden>
            <div class="builder-toolbar">
                <div class="field-group mode-field"><span>Quero escolher por</span><div class="segmented" role="group" aria-label="Forma de seleção"><button class="active" type="button" data-builder-mode="musculo">Músculo</button><button type="button" data-builder-mode="regiao">Região</button></div></div>
                <label class="field-group"><span data-target-label>Músculo</span><select data-builder-target></select></label>
                <label class="field-group"><span>Objetivo</span><select data-builder-objective><option value="hipertrofia">Hipertrofia</option><option value="forca">Força</option><option value="condicionamento">Condicionamento</option></select></label>
                <label class="field-group"><span>Duração</span><select data-builder-duration><option value="30">30 min</option><option value="45" selected>45 min</option><option value="60">60 min</option></select></label>
            </div>

            <div class="builder-summary"><div><strong data-builder-title>Escolha um músculo</strong><p data-builder-copy>Selecione o foco para receber uma composição equilibrada.</p></div><button type="button" data-generate-workout><i class="fa-solid fa-wand-magic-sparkles"></i> Gerar seleção</button></div>
            <div class="exercise-toolbar"><span><strong data-selected-count>0</strong> selecionados</span><label>Buscar exercício <input type="search" data-exercise-search placeholder="Nome ou equipamento"></label></div>
            <div class="exercise-catalog" data-exercise-catalog></div>
            <footer class="builder-save"><label><span>Nome do treino</span><input type="text" maxlength="100" data-workout-name placeholder="Ex.: Peito e tríceps A"></label><button type="button" data-save-workout disabled><i class="fa-solid fa-floppy-disk"></i> Salvar treino</button></footer>
        </section>
    </main>

    <div class="runner-backdrop" data-runner hidden>
        <section class="runner-panel" role="dialog" aria-modal="true" aria-labelledby="runner-title">
            <header><div><span>Treino personalizado</span><h2 id="runner-title" data-runner-title></h2></div><button type="button" data-close-runner aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button></header>
            <p>Marque cada exercício conforme concluir.</p>
            <div class="runner-list" data-runner-list></div>
            <button class="runner-finish" type="button" data-finish-custom><i class="fa-solid fa-check"></i> Concluir treino</button>
        </section>
    </div>

    <nav class="bottom-nav" aria-label="Menu inferior"><a href="03.menu.php"><i class="fa fa-house"></i><span>Início</span></a><a href="04.despesas.php"><i class="fa fa-minus-circle"></i><span>Despesas</span></a><a href="05.receitas.php"><i class="fa fa-plus-circle"></i><span>Receitas</span></a><a href="07.relatorios.php"><i class="fa fa-chart-line"></i><span>Relatórios</span></a><a class="active" href="#" data-fc-open-mobile-menu><i class="fa fa-bars"></i><span>Menu</span></a></nav>
    <div class="mobile-menu-backdrop" data-fc-mobile-menu hidden><div class="mobile-menu-panel"><div class="mobile-menu-head"><h2>Menu completo</h2><button type="button" data-fc-close-mobile-menu aria-label="Fechar">×</button></div><a href="03.menu.php"><i class="fa fa-house"></i> Início</a><a href="04.despesas.php"><i class="fa fa-minus-circle"></i> Despesas</a><a href="05.receitas.php"><i class="fa fa-plus-circle"></i> Receitas</a><a href="07.relatorios.php"><i class="fa fa-chart-line"></i> Relatórios</a><a href="29.minhas_noticias.php"><i class="fa fa-newspaper"></i> Minhas notícias</a><a class="active" href="30.treinos.php"><i class="fa fa-dumbbell"></i> Treinos</a><a href="15.logout.php"><i class="fa fa-sign-out"></i> Sair</a></div></div>
    <div class="app-toast" data-app-toast hidden role="status"></div>
    <script src="assets/treinos-master.js?v=2" defer></script>
</body>
</html>
