<?php
require_once "00.sessao.php";
require_once "09.conexao.php";

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
    header('Location: 34.medidas_historico.php', true, 303);
    exit;
}

$avatarDashboardSrc = avatarDashboardSrc($id_usuario);
$nome_usuario = $_SESSION['nome_usuario'] ?? 'Usuario';

function h($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>FinControle | Histórico de medidas</title>
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/header-perfil.css?v=1">
    <link rel="stylesheet" href="assets/medidas.css?v=5">
    <link rel="stylesheet" href="assets/medidas-historico.css?v=1">
</head>
<body class="treinos-app medidas-history-app">
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

        <section class="history-heading">
            <div>
                <a class="back-link" href="30.treinos.php"><i class="fa-solid fa-arrow-left"></i> Meus treinos</a>
                <h2>Histórico de medidas</h2>
                <p>Consulte, corrija ou exclua seus lançamentos.</p>
            </div>
            <button class="primary-btn" type="button" data-open-medidas><i class="fa-solid fa-plus"></i> Novo registro</button>
        </section>

        <section class="history-summary" aria-label="Resumo do histórico">
            <div><span>Registros</span><strong data-history-count>-</strong></div>
            <div><span>Primeira medição</span><strong data-history-first>-</strong></div>
            <div><span>Última medição</span><strong data-history-last>-</strong></div>
        </section>

        <p class="history-status" data-history-status role="status" aria-live="polite">Carregando medidas...</p>

        <section class="history-table-wrap" data-history-content hidden aria-label="Lançamentos de medidas">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Peso</th>
                        <th>Gordura</th>
                        <th>Massa gordura</th>
                        <th>Massa muscular</th>
                        <th>Abdômen</th>
                        <th>Bíceps D</th>
                        <th>Bíceps E</th>
                        <th class="actions-column">Ações</th>
                    </tr>
                </thead>
                <tbody data-history-rows></tbody>
            </table>
        </section>

        <section class="history-empty" data-history-empty hidden>
            <i class="fa-solid fa-ruler-combined"></i>
            <h2>Nenhuma medida registrada</h2>
            <p>Registre sua primeira medição para acompanhar a evolução.</p>
            <button class="primary-btn" type="button" data-history-empty-add>Registrar medida</button>
        </section>
    </main>

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
            <a class="active" href="30.treinos.php"><i class="fa fa-dumbbell"></i> Treinos</a>
            <a href="15.logout.php"><i class="fa fa-sign-out"></i> Sair</a>
        </div>
    </div>

    <?php require __DIR__ . '/32.medidas_popup.php'; ?>
    <script src="assets/medidas.js?v=4" defer></script>
    <script src="assets/medidas-historico.js?v=1" defer></script>
</body>
</html>
