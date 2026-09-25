<?php

function garantir_tabela_orcamentos_fincontrol($conexao) {
    if (!$conexao) {
        return false;
    }

    $sql = "CREATE TABLE IF NOT EXISTS orcamentos_categoria (
        id_orcamento int NOT NULL AUTO_INCREMENT,
        id_usuario int NOT NULL,
        categoria varchar(100) NOT NULL,
        mes_referencia date NOT NULL,
        valor text NOT NULL,
        criado_em timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        atualizado_em timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id_orcamento),
        UNIQUE KEY uk_orcamento_usuario_categoria_mes (id_usuario, categoria, mes_referencia),
        KEY idx_orcamento_usuario_mes (id_usuario, mes_referencia)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $ok = (bool) $conexao->query($sql);

    if ($ok) {
        garantir_coluna_valor_orcamentos_fincontrol($conexao);
    }

    return $ok;
}

function garantir_coluna_valor_orcamentos_fincontrol($conexao) {
    if (!$conexao) {
        return false;
    }

    $temValor = $conexao->query("SHOW COLUMNS FROM `orcamentos_categoria` LIKE 'valor'");

    if ($temValor && $temValor->num_rows > 0) {
        return true;
    }

    $temValorPlanejado = $conexao->query("SHOW COLUMNS FROM `orcamentos_categoria` LIKE 'valor_planejado'");

    if ($temValorPlanejado && $temValorPlanejado->num_rows > 0) {
        $ok = $conexao->query("ALTER TABLE `orcamentos_categoria` ADD `valor` text NULL AFTER `mes_referencia`");

        if ($ok) {
            $conexao->query("UPDATE `orcamentos_categoria` SET `valor` = CAST(`valor_planejado` AS CHAR) WHERE `valor` IS NULL OR `valor` = ''");
            $conexao->query("ALTER TABLE `orcamentos_categoria` MODIFY `valor` text NOT NULL");
        }

        return (bool) $ok;
    }

    return (bool) $conexao->query("ALTER TABLE `orcamentos_categoria` ADD `valor` text NOT NULL AFTER `mes_referencia`");
}

function garantir_coluna_orcamento_despesas_fincontrol($conexao) {
    if (!$conexao) {
        return false;
    }

    $resultado = $conexao->query("SHOW COLUMNS FROM `despesas` LIKE 'id_orcamento'");

    if ($resultado && $resultado->num_rows > 0) {
        return true;
    }

    $ok = $conexao->query("ALTER TABLE `despesas` ADD `id_orcamento` int DEFAULT NULL AFTER `id_usuario`");

    if ($ok) {
        $conexao->query("ALTER TABLE `despesas` ADD KEY `idx_despesas_orcamento` (`id_orcamento`)");
    }

    return (bool) $ok;
}

function garantir_colunas_orcamento_gastos_fincontrol($conexao) {
    if (!$conexao) {
        return false;
    }

    $colunas = [
        "id_orcamento" => "ALTER TABLE `gastos_diarios` ADD `id_orcamento` int DEFAULT NULL AFTER `id_usuario`",
        "id_cartao" => "ALTER TABLE `gastos_diarios` ADD `id_cartao` int DEFAULT NULL AFTER `id_orcamento`",
        "id_despesa" => "ALTER TABLE `gastos_diarios` ADD `id_despesa` int DEFAULT NULL AFTER `id_cartao`"
    ];

    foreach ($colunas as $coluna => $sql) {
        $resultado = $conexao->query("SHOW COLUMNS FROM `gastos_diarios` LIKE '$coluna'");
        if (!$resultado || $resultado->num_rows === 0) {
            $conexao->query($sql);
        }
    }

    return true;
}

function mes_referencia_fincontrol($valor) {
    if (preg_match('/^\d{4}-\d{2}$/', $valor)) {
        return $valor . "-01";
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
        return substr($valor, 0, 7) . "-01";
    }

    return date("Y-m-01");
}

function mes_input_fincontrol($data) {
    return substr(mes_referencia_fincontrol($data), 0, 7);
}

function meses_orcamento_periodo_fincontrol($dataIni, $dataFim, $filtroMes, $filtroAno, $saidas = []) {
    $meses = [];

    if ($filtroAno !== "" && $filtroMes !== "") {
        return [$filtroAno . "-" . $filtroMes . "-01"];
    }

    if ($filtroAno !== "" && $filtroMes === "") {
        for ($i = 1; $i <= 12; $i++) {
            $meses[] = $filtroAno . "-" . str_pad((string) $i, 2, "0", STR_PAD_LEFT) . "-01";
        }
        return $meses;
    }

    if ($dataIni !== "" || $dataFim !== "") {
        $inicio = $dataIni !== "" ? substr($dataIni, 0, 7) . "-01" : substr($dataFim, 0, 7) . "-01";
        $fim = $dataFim !== "" ? substr($dataFim, 0, 7) . "-01" : substr($dataIni, 0, 7) . "-01";
        $dtInicio = DateTime::createFromFormat("Y-m-d", $inicio);
        $dtFim = DateTime::createFromFormat("Y-m-d", $fim);

        if ($dtInicio && $dtFim) {
            while ($dtInicio <= $dtFim) {
                $meses[] = $dtInicio->format("Y-m-01");
                $dtInicio->modify("+1 month");
            }
            return $meses;
        }
    }

    if (count($saidas)) {
        foreach ($saidas as $saida) {
            if (!empty($saida["data"])) {
                $meses[substr($saida["data"], 0, 7) . "-01"] = true;
            }
        }

        if (count($meses)) {
            return array_keys($meses);
        }
    }

    return [date("Y-m-01")];
}

function buscar_orcamentos_fincontrol($conexao, $id_usuario, $meses) {
    $resultado = [];

    if (!$conexao || !count($meses)) {
        return $resultado;
    }

    $placeholders = implode(",", array_fill(0, count($meses), "?"));
    $tipos = "i" . str_repeat("s", count($meses));
    $params = array_merge([$id_usuario], $meses);

    $sql = "SELECT categoria, mes_referencia, valor
            FROM orcamentos_categoria
            WHERE id_usuario = ?
            AND mes_referencia IN ($placeholders)";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return $resultado;
    }

    $refs = [];
    foreach ($params as $indice => $valor) {
        $refs[$indice] = &$params[$indice];
    }

    $stmt->bind_param($tipos, ...$refs);
    $stmt->execute();
    $dados = $stmt->get_result();

    while ($row = $dados->fetch_assoc()) {
        $categoria = $row["categoria"] ?: "Sem nome";

        if (!isset($resultado[$categoria])) {
            $resultado[$categoria] = 0;
        }

        $resultado[$categoria] += descriptografar_valor_financeiro($row["valor"]);
    }

    $stmt->close();
    return $resultado;
}

function realizado_por_categoria_fincontrol($saidas, $meses) {
    $resultado = [];
    $mesesNormalizados = [];
    foreach ($meses as $mes) {
        $mesesNormalizados[] = substr($mes, 0, 7);
    }
    $mapaMeses = array_fill_keys($mesesNormalizados, true);

    foreach ($saidas as $saida) {
        $mes = substr($saida["data"] ?? "", 0, 7);

        if (!isset($mapaMeses[$mes])) {
            continue;
        }

        $categoria = trim($saida["categoria_orcamento"] ?? "");
        if ($categoria === "") {
            continue;
        }

        if (!isset($resultado[$categoria])) {
            $resultado[$categoria] = 0;
        }

        $resultado[$categoria] += floatval($saida["valor"]);
    }

    return $resultado;
}

function realizado_apenas_categorias_orcadas_fincontrol($orcamentos, $realizado) {
    $resultado = [];

    foreach ($orcamentos as $categoria => $valorOrcado) {
        $resultado[$categoria] = floatval($realizado[$categoria] ?? 0);
    }

    return $resultado;
}

function comparar_orcamento_realizado_fincontrol($orcamentos, $realizado, $incluirSemOrcamento = true) {
    $categorias = $incluirSemOrcamento
        ? array_unique(array_merge(array_keys($orcamentos), array_keys($realizado)))
        : array_keys($orcamentos);

    sort($categorias);
    $linhas = [];

    foreach ($categorias as $categoria) {
        $orcado = floatval($orcamentos[$categoria] ?? 0);
        $gasto = floatval($realizado[$categoria] ?? 0);
        $saldo = $orcado - $gasto;
        $uso = $orcado > 0 ? ($gasto / $orcado) * 100 : 0;

        if ($orcado <= 0 && $gasto > 0) {
            $status = "Sem limite";
        } elseif ($saldo < 0) {
            $status = "Estourado";
        } elseif ($uso >= 80) {
            $status = "Atenção";
        } else {
            $status = "Dentro";
        }

        $linhas[] = [
            "categoria" => $categoria,
            "orcado" => $orcado,
            "realizado" => $gasto,
            "saldo" => $saldo,
            "uso" => $uso,
            "status" => $status
        ];
    }

    usort($linhas, function ($a, $b) {
        if ($a["realizado"] == $b["realizado"]) {
            return 0;
        }

        return ($a["realizado"] < $b["realizado"]) ? 1 : -1;
    });
    return $linhas;
}

function vincular_orcamento_despesas_fincontrol($conexao, $id_usuario, $id_orcamento, $categoria, $mesReferencia) {
    return 0;
}

function vincular_despesa_orcamento_fincontrol($conexao, $id_usuario, $id_despesa, $categoria, $dataVencimento) {
    return false;
}
