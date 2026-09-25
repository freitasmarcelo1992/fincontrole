<?php
if (!isset($id_usuario, $saldo)) { http_response_code(404); exit; }
$reportEscape = static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$reportNome = explode(' ', trim($nome_usuario))[0] ?: 'Usuário';
$reportAvatars = glob(__DIR__ . '/uploads/avatars/avatar_' . (int) $id_usuario . '.*') ?: [];
$reportAvatar = $reportAvatars ? 'uploads/avatars/' . rawurlencode(basename($reportAvatars[0])) : '';
$reportLinks = [
    ['03.menu.php', 'fa-house', 'Início'], ['04.despesas.php', 'fa-minus-circle', 'Despesas'],
    ['05.receitas.php', 'fa-plus-circle', 'Receitas'],
    ['07.relatorios.php', 'fa-chart-line', 'Relatórios'], ['29.minhas_noticias.php', 'fa-newspaper', 'Minhas notícias'],
    ['30.treinos.php', 'fa-dumbbell', 'Treinos'], ['15.logout.php', 'fa-sign-out', 'Sair']
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
    <title>FinControle | Relatórios</title>
    <?php require_once __DIR__ . '/00.pwa.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/relatorios.css?v=3">
    <script src="assets/chart.umd.min.js" defer></script>
    <script src="assets/xlsx.full.min.js" defer></script>
    <script src="assets/relatorios.js?v=1" defer></script>
</head>
<body class="reports-app">
    <aside class="reports-sidebar">
        <div class="reports-brand"><i class="fa-solid fa-wallet"></i> FINCONTROLE</div>
        <nav aria-label="Navegação principal">
            <?php foreach ($reportLinks as [$href, $icon, $label]) { ?>
                <a href="<?= $href ?>" <?= $href === '07.relatorios.php' ? 'aria-current="page"' : '' ?>><i class="fa-solid <?= $icon ?>"></i><?= $label ?></a>
            <?php } ?>
        </nav>
    </aside>
    <main class="reports-main">
        <header class="reports-header">
            <div class="reports-brand"><i class="fa-solid fa-wallet"></i> FINCONTROLE</div>
            <div class="reports-heading">
                <h1>Relatórios</h1>
                <div class="reports-user"><span><?= $reportEscape($reportNome) ?></span><span class="reports-avatar">
                    <?php if ($reportAvatar) { ?><img src="<?= $reportEscape($reportAvatar) ?>" alt="Foto de perfil"><?php } else { echo $reportEscape(strtoupper(substr($reportNome, 0, 1))); } ?>
                </span></div>
            </div>
        </header>
        <div class="reports-toolbar">
            <p><i class="fa-regular fa-calendar"></i> <?= $reportEscape($periodoTexto) ?></p>
            <button type="button" data-report-filter title="Filtros" aria-label="Filtros"><i class="fa-solid fa-sliders"></i></button>
            <button type="button" data-report-export title="Exportar Excel" aria-label="Exportar Excel"><i class="fa-solid fa-file-excel"></i></button>
        </div>
        <section class="reports-metrics" aria-label="Indicadores financeiros">
            <div><span>Saldo</span><strong class="<?= $saldo < 0 ? 'negative' : 'positive' ?>"><?= moeda($saldo) ?></strong></div>
            <div><span>Comprometido</span><strong><?= number_format($receitaComprometida, 1, ',', '.') ?>%</strong></div>
            <div><span>Média por despesa</span><strong><?= moeda($ticketMedioSaida) ?></strong></div>
            <div><span>Lançamentos</span><strong><?= (int) $quantidadeLancamentos ?></strong></div>
        </section>
        <div class="reports-tabs" role="tablist" aria-label="Análises financeiras">
            <?php foreach (['resumo' => 'Resumo', 'categorias' => 'Categorias', 'evolucao' => 'Evolução'] as $key => $label) { ?>
                <button type="button" role="tab" id="tab-<?= $key ?>" aria-controls="panel-<?= $key ?>" aria-selected="<?= $key === 'resumo' ? 'true' : 'false' ?>" tabindex="<?= $key === 'resumo' ? '0' : '-1' ?>" data-report-tab="<?= $key ?>"><?= $label ?></button>
            <?php } ?>
        </div>
        <section class="reports-panel reports-summary" id="panel-resumo" role="tabpanel" aria-labelledby="tab-resumo">
            <section class="reports-section">
                <h2>Receitas × despesas</h2>
                <div class="reports-chart"><canvas id="report-flow" role="img" aria-label="Comparação mensal de receitas e despesas"></canvas></div>
            </section>
            <section class="reports-section reports-ranking">
                <h2>Principais despesas <small>Top 5</small></h2>
                <?php if (!$topCategorias) { ?><p class="reports-empty">Sem despesas no período.</p><?php } ?>
                <?php foreach ($topCategorias as $cat => $valor) { $pct = $totalSaidas > 0 ? $valor / $totalSaidas * 100 : 0; ?>
                    <div class="reports-rank-row">
                        <span title="<?= $reportEscape($cat) ?>"><?= $reportEscape($cat) ?></span>
                        <strong><?= moeda($valor) ?> <small>· <?= number_format($pct, 1, ',', '.') ?>%</small></strong>
                        <div class="reports-bar"><span style="width:<?= min(100, $pct) ?>%"></span></div>
                    </div>
                <?php } ?>
            </section>
        </section>
        <section class="reports-panel reports-categories" id="panel-categorias" role="tabpanel" aria-labelledby="tab-categorias" hidden>
            <section class="reports-section"><h2>Despesas por categoria <small>Top 5</small></h2><div class="reports-chart"><canvas id="report-expenses" role="img" aria-label="Cinco maiores categorias de despesas"></canvas></div></section>
            <section class="reports-section"><h2>Receitas por categoria <small>Top 5</small></h2><div class="reports-chart"><canvas id="report-income" role="img" aria-label="Cinco maiores categorias de receitas"></canvas></div></section>
        </section>
        <section class="reports-panel reports-evolution" id="panel-evolucao" role="tabpanel" aria-labelledby="tab-evolucao" hidden>
            <div class="reports-stories">
                <div><span>Melhor mês</span><strong><?= $reportEscape($historiaMelhorMes['mes']) ?></strong><small><?= moeda($historiaMelhorMes['valor']) ?></small></div>
                <div><span>Ponto de atenção</span><strong><?= $reportEscape($historiaPiorMes['mes']) ?></strong><small><?= moeda($historiaPiorMes['valor']) ?></small></div>
            </div>
            <section class="reports-section"><h2>Variação do saldo</h2><div class="reports-chart"><canvas id="report-balance" role="img" aria-label="Evolução mensal do saldo"></canvas></div></section>
            <p class="reports-trend"><strong><?= $reportEscape($historiaTendencia) ?></strong> · <?= $reportEscape($historiaDeltaTexto) ?></p>
        </section>
        <p class="reports-status" data-report-status role="status" hidden></p>
    </main>
    <nav class="reports-bottom" aria-label="Menu inferior">
        <?php foreach (array_slice($reportLinks, 0, 4) as [$href, $icon, $label]) { ?>
            <a href="<?= $href ?>"<?= $href === "07.relatorios.php" ? ' class="active" aria-current="page"' : "" ?>><i class="fa-solid <?= $icon ?>"></i><span><?= $label ?></span></a>
        <?php } ?>
        <button type="button" data-report-menu><i class="fa-solid fa-bars"></i><span>Menu</span></button>
    </nav>
    <dialog class="reports-dialog reports-menu" data-report-menu-dialog aria-labelledby="reports-menu-title">
        <header><h2 id="reports-menu-title">Menu</h2><button type="button" data-close-dialog aria-label="Fechar menu"><i class="fa-solid fa-xmark"></i></button></header>
        <nav aria-label="Menu completo">
            <?php foreach ($reportLinks as [$href, $icon, $label]) { ?>
                <a href="<?= $href ?>" <?= $href === '07.relatorios.php' ? 'aria-current="page"' : '' ?>><i class="fa-solid <?= $icon ?>"></i><?= $label ?></a>
            <?php } ?>
        </nav>
    </dialog>
    <dialog class="reports-dialog" data-report-filter-dialog aria-labelledby="reports-filter-title">
        <header><h2 id="reports-filter-title">Filtros</h2><button type="button" data-close-dialog aria-label="Fechar filtros"><i class="fa-solid fa-xmark"></i></button></header>
        <form method="get" action="07.relatorios.php">
            <div class="reports-filter-grid">
                <label>Data inicial<input type="date" name="dataIni" value="<?= $reportEscape($dataIni) ?>"></label>
                <label>Data final<input type="date" name="dataFim" value="<?= $reportEscape($dataFim) ?>"></label>
                <label>Mês<select name="filtroMes"><option value="">Todos</option><?php for ($i = 1; $i <= 12; $i++) { $m = str_pad($i, 2, '0', STR_PAD_LEFT); ?><option value="<?= $m ?>" <?= $filtroMes === $m ? 'selected' : '' ?>><?= $m ?></option><?php } ?></select></label>
                <label>Ano<select name="filtroAno"><option value="">Todos</option><?php for ($a = date('Y') - 2; $a <= date('Y') + 5; $a++) { ?><option value="<?= $a ?>" <?= $filtroAno == $a ? 'selected' : '' ?>><?= $a ?></option><?php } ?></select></label>
                <label class="reports-wide">Busca<input type="search" name="buscaDespesa" value="<?= $reportEscape($buscaDespesa) ?>" placeholder="Pesquisar despesas"></label>
            </div>
            <div class="reports-filter-actions"><a href="07.relatorios.php?filtroMes=&amp;filtroAno=">Limpar</a><button type="submit"><i class="fa-solid fa-check"></i> Aplicar</button></div>
        </form>
    </dialog>
    <script type="application/json" id="reports-data"><?= json_encode([
        'labels' => $labelsGrafico, 'receitas' => $receitasGrafico, 'despesas' => $despesasGrafico, 'saldo' => $saldoGrafico,
        'categorias' => array_slice($labelsCategorias, 0, 5), 'valoresCategorias' => array_slice($valoresCategorias, 0, 5),
        'categoriasReceitas' => $labelsReceitasCategorias, 'valoresReceitas' => $valoresReceitasCategorias, 'exportacao' => $livro
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
</body>
</html>
