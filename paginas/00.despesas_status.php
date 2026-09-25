<?php

function garantir_coluna_status_despesas_fincontrol($conexao) {
    if (!$conexao) {
        return false;
    }

    $resultado = $conexao->query("SHOW COLUMNS FROM `despesas` LIKE 'status'");

    if ($resultado && $resultado->num_rows > 0) {
        return true;
    }

    return (bool) $conexao->query("ALTER TABLE `despesas` ADD `status` varchar(20) NOT NULL DEFAULT 'A vencer' AFTER `recorrente`");
}

function status_despesa_valido_fincontrol($status) {
    $permitidos = ["A vencer", "Atrasada", "Paga"];
    return in_array($status, $permitidos, true) ? $status : "A vencer";
}

function status_despesa_exibicao_fincontrol($statusBanco, $dataVencimento) {
    $status = status_despesa_valido_fincontrol($statusBanco ?: "A vencer");

    if ($status === "Paga") {
        return "Paga";
    }

    if ($dataVencimento < date("Y-m-d")) {
        return "Atrasada";
    }

    return "A vencer";
}

function classe_status_despesa_fincontrol($status) {
    if ($status === "Paga") return "status-paga";
    if ($status === "Atrasada") return "status-atrasada";
    return "status-vencer";
}

function adicionar_mes_despesa_recorrente_fincontrol($data, $meses) {
    $dt = DateTime::createFromFormat("Y-m-d", $data);

    if (!$dt) {
        return $data;
    }

    $diaOriginal = intval($dt->format("d"));
    $dt->modify("+$meses month");

    if (intval($dt->format("d")) !== $diaOriginal) {
        $dt->modify("last day of previous month");
    }

    return $dt->format("Y-m-d");
}

function materializar_despesas_recorrentes_fincontrol($conexao, $id_usuario, $meses = 12) {
    if (!$conexao || !$id_usuario) {
        return 0;
    }

    $sql = "SELECT *
            FROM despesas
            WHERE id_usuario = ?
            AND recorrente = 'Sim'
            ORDER BY data_vencimento ASC, id_despesa ASC";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $grupos = [];

    while ($row = $resultado->fetch_assoc()) {
        $valorNumerico = number_format(descriptografar_valor_financeiro($row["valor"]), 2, ".", "");
        $chave = mb_strtolower(trim($row["descricao"]), "UTF-8") . "|" . ($row["categoria"] ?? "Outros") . "|" . $valorNumerico;

        if (!isset($grupos[$chave])) {
            $grupos[$chave] = [];
        }

        $grupos[$chave][] = $row;
    }

    $criadas = 0;

    foreach ($grupos as $grupo) {
        usort($grupo, function ($a, $b) {
            return strcmp($a["data_vencimento"], $b["data_vencimento"]);
        });

        $seed = $grupo[0];
        $datasExistentes = [];

        foreach ($grupo as $item) {
            $datasExistentes[$item["data_vencimento"]] = true;
        }

        for ($i = 0; $i < $meses; $i++) {
            $dataParcela = adicionar_mes_despesa_recorrente_fincontrol($seed["data_vencimento"], $i);

            if (isset($datasExistentes[$dataParcela])) {
                continue;
            }

            $status = "A vencer";
            $parcelaAtual = "Recorrente";
            $parcelas = 1;
            $categoria = $seed["categoria"] ?? "Outros";

            $sqlInsert = "INSERT INTO despesas
                (id_usuario, descricao, categoria, valor, parcelas, parcela_atual, data_vencimento, recorrente, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Sim', ?)";

            $stmtInsert = $conexao->prepare($sqlInsert);

            if (!$stmtInsert) {
                continue;
            }

            $stmtInsert->bind_param(
                "isssisss",
                $id_usuario,
                $seed["descricao"],
                $categoria,
                $seed["valor"],
                $parcelas,
                $parcelaAtual,
                $dataParcela,
                $status
            );

            if ($stmtInsert->execute()) {
                $criadas++;
                $datasExistentes[$dataParcela] = true;

                if (function_exists("vincular_despesa_orcamento_fincontrol")) {
                    vincular_despesa_orcamento_fincontrol(
                        $conexao,
                        $id_usuario,
                        $stmtInsert->insert_id,
                        $categoria,
                        $dataParcela
                    );
                }
            }

            $stmtInsert->close();
        }
    }

    return $criadas;
}
