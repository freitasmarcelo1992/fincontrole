    <div class="topbar app-dashboard-header">
        <div class="app-brand-heading"><i class="fa-solid fa-wallet"></i><span>FinControle</span></div>
        <div class="title">
            <form class="app-avatar-form" method="post" enctype="multipart/form-data" aria-label="Foto de perfil">
                <input type="hidden" name="acao_dashboard" value="upload_avatar">
                <label class="app-avatar-button" title="Alterar foto de perfil">
                    <?php if ($avatarDashboardSrc !== "") { ?>
                        <img src="<?php echo htmlspecialchars($avatarDashboardSrc); ?>" alt="Foto de perfil">
                    <?php } else { ?>
                        <span><?php echo htmlspecialchars(strtoupper(substr(primeiroNomeDashboard($nome_usuario), 0, 1))); ?></span>
                    <?php } ?>
                    <input type="file" name="avatar_usuario" accept="image/jpeg,image/png,image/webp" onchange="this.form.submit()">
                </label>
            </form>
            <h1><?php echo saudacaoDashboard(); ?>, <?php echo htmlspecialchars(primeiroNomeDashboard($nome_usuario)); ?>!</h1>
            <p>Hoje é <?php echo dataLongaDashboard(date("Y-m-d")); ?></p>
        </div>

        <div class="user-box">
            <i class="fa-solid fa-user"></i>
            <?php echo htmlspecialchars($nome_usuario); ?>
        </div>
    </div>

