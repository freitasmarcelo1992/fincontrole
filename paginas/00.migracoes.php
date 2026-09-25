<?php

function fincontrol_migracao_coluna_existe($conexao, $tabela, $coluna) {
    $stmt = $conexao->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1");
    $stmt->bind_param("ss", $tabela, $coluna);
    $stmt->execute();
    $existe = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();
    return $existe;
}

function fincontrol_migracao_indice_existe($conexao, $tabela, $indice) {
    $stmt = $conexao->prepare("SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1");
    $stmt->bind_param("ss", $tabela, $indice);
    $stmt->execute();
    $existe = (bool) $stmt->get_result()->fetch_row();
    $stmt->close();
    return $existe;
}

function fincontrol_migracao_executar_sql($conexao, $sql, &$resultado, $descricao) {
    if ($conexao->query($sql)) {
        $resultado[] = ["ok" => true, "descricao" => $descricao];
        return true;
    }

    $resultado[] = ["ok" => false, "descricao" => $descricao . ": " . $conexao->error];
    return false;
}

function fincontrol_executar_migracoes($conexao) {
    $resultado = [];
    if (!$conexao) return [["ok" => false, "descricao" => "Conexao indisponivel"]];

    $tabelas = [
        "schema_migrations" => "CREATE TABLE IF NOT EXISTS schema_migrations (versao varchar(40) NOT NULL, executado_em timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (versao)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "cartoes_credito" => "CREATE TABLE IF NOT EXISTS cartoes_credito (id_cartao int NOT NULL AUTO_INCREMENT, id_usuario int NOT NULL, nome_cartao varchar(100) NOT NULL, dia_vencimento tinyint NOT NULL, dia_fechamento tinyint NOT NULL, criado_em timestamp NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id_cartao), KEY idx_cartoes_usuario (id_usuario)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "orcamentos_categoria" => "CREATE TABLE IF NOT EXISTS orcamentos_categoria (id_orcamento int NOT NULL AUTO_INCREMENT, id_usuario int NOT NULL, categoria varchar(100) NOT NULL, mes_referencia date NOT NULL, valor text NOT NULL, criado_em timestamp NULL DEFAULT CURRENT_TIMESTAMP, atualizado_em timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (id_orcamento), UNIQUE KEY uk_orcamento_usuario_categoria_mes (id_usuario, categoria, mes_referencia), KEY idx_orcamento_usuario_mes (id_usuario, mes_referencia)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "caixinhas_economia" => "CREATE TABLE IF NOT EXISTS caixinhas_economia (id_caixinha int NOT NULL AUTO_INCREMENT, id_usuario int NOT NULL, nome varchar(120) NOT NULL, objetivo varchar(160) DEFAULT NULL, meta_valor text NOT NULL, data_meta date DEFAULT NULL, status varchar(20) NOT NULL DEFAULT 'ativa', criado_em timestamp NULL DEFAULT CURRENT_TIMESTAMP, atualizado_em timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (id_caixinha), KEY idx_caixinhas_usuario_status (id_usuario, status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "caixinhas_lancamentos" => "CREATE TABLE IF NOT EXISTS caixinhas_lancamentos (id_lancamento int NOT NULL AUTO_INCREMENT, id_caixinha int NOT NULL, id_usuario int NOT NULL, descricao varchar(160) NOT NULL, valor text NOT NULL, data_lancamento date NOT NULL, observacao text NULL, criado_em timestamp NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id_lancamento), KEY idx_caixinhas_lancamentos_usuario_data (id_usuario, data_lancamento), KEY idx_caixinhas_lancamentos_caixinha (id_caixinha)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "login_persistente" => "CREATE TABLE IF NOT EXISTS login_persistente (id_token bigint unsigned NOT NULL AUTO_INCREMENT, id_usuario int NOT NULL, seletor char(32) NOT NULL, token_hash char(64) NOT NULL, expira_em datetime NOT NULL, ultimo_uso datetime DEFAULT NULL, criado_em timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id_token), UNIQUE KEY uk_login_persistente_seletor (seletor), KEY idx_login_persistente_usuario (id_usuario), KEY idx_login_persistente_expira (expira_em)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "assinaturas" => "CREATE TABLE IF NOT EXISTS assinaturas (id_assinatura int NOT NULL AUTO_INCREMENT, id_usuario int NOT NULL, plano varchar(30) NOT NULL DEFAULT 'premium', status varchar(30) NOT NULL DEFAULT 'pendente', asaas_customer_id varchar(80) DEFAULT NULL, asaas_subscription_id varchar(80) DEFAULT NULL, asaas_payment_id varchar(80) DEFAULT NULL, billing_type varchar(30) NOT NULL DEFAULT 'PIX', valor decimal(10,2) NOT NULL DEFAULT 0.00, ciclo varchar(30) NOT NULL DEFAULT 'MONTHLY', invoice_url text NULL, data_vencimento date DEFAULT NULL, data_ativacao datetime DEFAULT NULL, criado_em timestamp NULL DEFAULT CURRENT_TIMESTAMP, atualizado_em timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (id_assinatura), KEY idx_assinaturas_usuario_status (id_usuario, status), KEY idx_assinaturas_asaas_subscription (asaas_subscription_id), KEY idx_assinaturas_asaas_customer (asaas_customer_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "eventos_usuario" => "CREATE TABLE IF NOT EXISTS eventos_usuario (id_evento bigint unsigned NOT NULL AUTO_INCREMENT, id_usuario int NOT NULL, evento varchar(60) NOT NULL, pagina varchar(100) DEFAULT NULL, metadados text NULL, criado_em timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (id_evento), KEY idx_eventos_usuario_data (id_usuario, criado_em), KEY idx_eventos_tipo_data (evento, criado_em)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "usuario_metricas" => "CREATE TABLE IF NOT EXISTS usuario_metricas (id_usuario int NOT NULL, score_financeiro smallint NOT NULL DEFAULT 0, total_receitas decimal(14,2) NOT NULL DEFAULT 0, total_despesas decimal(14,2) NOT NULL DEFAULT 0, saldo decimal(14,2) NOT NULL DEFAULT 0, categoria_critica varchar(100) DEFAULT NULL, atualizado_em datetime NOT NULL, PRIMARY KEY (id_usuario)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "cron_execucoes" => "CREATE TABLE IF NOT EXISTS cron_execucoes (id_execucao bigint unsigned NOT NULL AUTO_INCREMENT, tarefa varchar(80) NOT NULL, status varchar(20) NOT NULL, detalhes text NULL, iniciado_em datetime NOT NULL, finalizado_em datetime DEFAULT NULL, PRIMARY KEY (id_execucao), KEY idx_cron_tarefa_data (tarefa, iniciado_em)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ,"notificacoes_envio" => "CREATE TABLE IF NOT EXISTS notificacoes_envio (id_envio bigint unsigned NOT NULL AUTO_INCREMENT, id_usuario int NOT NULL, tipo varchar(60) NOT NULL, referencia varchar(80) NOT NULL, enviado_em datetime NOT NULL, PRIMARY KEY (id_envio), UNIQUE KEY uk_notificacao_usuario_tipo_ref (id_usuario, tipo, referencia), KEY idx_notificacoes_data (enviado_em)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    foreach ($tabelas as $nome => $sql) fincontrol_migracao_executar_sql($conexao, $sql, $resultado, "Tabela " . $nome);

    $colunas = [
        ["receitas", "categoria", "ALTER TABLE receitas ADD categoria varchar(100) NOT NULL DEFAULT 'Outros' AFTER descricao"],
        ["despesas", "categoria", "ALTER TABLE despesas ADD categoria varchar(100) NOT NULL DEFAULT 'Outros' AFTER descricao"],
        ["despesas", "status", "ALTER TABLE despesas ADD status varchar(20) NOT NULL DEFAULT 'A vencer' AFTER recorrente"],
        ["despesas", "id_orcamento", "ALTER TABLE despesas ADD id_orcamento int DEFAULT NULL AFTER id_usuario"],
        ["despesas", "id_cartao", "ALTER TABLE despesas ADD id_cartao int DEFAULT NULL AFTER id_orcamento"],
        ["gastos_diarios", "id_orcamento", "ALTER TABLE gastos_diarios ADD id_orcamento int DEFAULT NULL AFTER id_usuario"],
        ["gastos_diarios", "id_cartao", "ALTER TABLE gastos_diarios ADD id_cartao int DEFAULT NULL AFTER id_orcamento"],
        ["gastos_diarios", "id_despesa", "ALTER TABLE gastos_diarios ADD id_despesa int DEFAULT NULL AFTER id_cartao"],
        ["usuario", "perfil_usuario", "ALTER TABLE usuario ADD perfil_usuario varchar(30) NOT NULL DEFAULT 'CLT' AFTER nome_usuario"]
    ];

    foreach ($colunas as $item) {
        if (!fincontrol_migracao_coluna_existe($conexao, $item[0], $item[1])) {
            fincontrol_migracao_executar_sql($conexao, $item[2], $resultado, "Coluna {$item[0]}.{$item[1]}");
        }
    }

    $versaoValeRefeicao = "2026.06-vale-refeicao";
    $stmtVersao = $conexao->prepare("SELECT 1 FROM schema_migrations WHERE versao = ? LIMIT 1");
    $stmtVersao->bind_param("s", $versaoValeRefeicao);
    $stmtVersao->execute();
    $valeRefeicaoMigrado = (bool) $stmtVersao->get_result()->fetch_row();
    $stmtVersao->close();

    if (!$valeRefeicaoMigrado) {
        if (fincontrol_migracao_executar_sql(
            $conexao,
            "ALTER TABLE gastos_diarios MODIFY forma_pagamento varchar(40) NOT NULL",
            $resultado,
            "Forma de pagamento Vale-refeicao"
        )) {
            $stmtRegistrar = $conexao->prepare("INSERT INTO schema_migrations (versao) VALUES (?)");
            $stmtRegistrar->bind_param("s", $versaoValeRefeicao);
            $stmtRegistrar->execute();
            $stmtRegistrar->close();
        }
    }

    if (!fincontrol_migracao_coluna_existe($conexao, "orcamentos_categoria", "valor")) {
        fincontrol_migracao_executar_sql($conexao, "ALTER TABLE orcamentos_categoria ADD valor text NULL AFTER mes_referencia", $resultado, "Coluna orcamentos_categoria.valor");
        if (fincontrol_migracao_coluna_existe($conexao, "orcamentos_categoria", "valor_planejado")) {
            $conexao->query("UPDATE orcamentos_categoria SET valor = CAST(valor_planejado AS CHAR) WHERE valor IS NULL OR valor = ''");
        }
        $conexao->query("UPDATE orcamentos_categoria SET valor = '0' WHERE valor IS NULL");
        $conexao->query("ALTER TABLE orcamentos_categoria MODIFY valor text NOT NULL");
    }

    $indices = [
        ["despesas", "idx_despesas_usuario_data", "CREATE INDEX idx_despesas_usuario_data ON despesas (id_usuario, data_vencimento)"],
        ["receitas", "idx_receitas_usuario_data", "CREATE INDEX idx_receitas_usuario_data ON receitas (id_usuario, data_recebimento)"],
        ["gastos_diarios", "idx_gastos_usuario_data", "CREATE INDEX idx_gastos_usuario_data ON gastos_diarios (id_usuario, data_gasto)"],
        ["despesas", "idx_despesas_orcamento", "CREATE INDEX idx_despesas_orcamento ON despesas (id_orcamento)"],
        ["despesas", "idx_despesas_cartao", "CREATE INDEX idx_despesas_cartao ON despesas (id_cartao)"]
        ,["orcamentos_categoria", "uk_orcamento_usuario_categoria_mes", "CREATE UNIQUE INDEX uk_orcamento_usuario_categoria_mes ON orcamentos_categoria (id_usuario, categoria, mes_referencia)"]
    ];

    foreach ($indices as $item) {
        if (!fincontrol_migracao_indice_existe($conexao, $item[0], $item[1])) {
            fincontrol_migracao_executar_sql($conexao, $item[2], $resultado, "Indice {$item[1]}");
        }
    }

    $conexao->query("INSERT IGNORE INTO schema_migrations (versao) VALUES ('2026.06-saas-performance')");
    return $resultado;
}
