<?php
require_once "00.sessao.php";
require_once "09.conexao.php";
require_once "00.crypto.php";
require_once "00.categorias.php";
require_once "00.orcamentos.php";
require_once "00.planos.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION["id_usuario"])) {
    header("Location: 02.login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$nome_usuario = $_SESSION["nome_usuario"] ?? "";
$premium_ativo_relatorios = true;
$indicadores_premium_relatorios = true;

if ($nome_usuario === "") {
    $stmt_usuario = $conexao->prepare("SELECT nome_usuario FROM usuario WHERE id_usuario = ? LIMIT 1");
    $stmt_usuario->bind_param("i", $id_usuario);
    $stmt_usuario->execute();
    $usuario_logado = $stmt_usuario->get_result()->fetch_assoc();
    $nome_usuario = $usuario_logado["nome_usuario"] ?? "Usuario";
}

$dataIni = $_GET["dataIni"] ?? "";
$dataFim = $_GET["dataFim"] ?? "";
$filtroMes = $_GET["filtroMes"] ?? date("m");
$filtroAno = $_GET["filtroAno"] ?? date("Y");
$buscaDespesa = trim($_GET["buscaDespesa"] ?? "");

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataIni)) $dataIni = "";
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFim)) $dataFim = "";
if (!preg_match('/^\d{2}$/', $filtroMes)) $filtroMes = "";
if (!preg_match('/^\d{4}$/', $filtroAno)) $filtroAno = "";

if ($dataIni !== "" && $dataFim !== "" && $dataIni > $dataFim) {
    $tmp = $dataIni;
    $dataIni = $dataFim;
    $dataFim = $tmp;
}

function addMesRelatorio($data, $meses) {
    $dt = DateTime::createFromFormat("Y-m-d", $data);
    if (!$dt) return $data;

    $diaOriginal = intval($dt->format("d"));
    $dt->modify("+$meses month");

    if (intval($dt->format("d")) !== $diaOriginal) {
        $dt->modify("last day of previous month");
    }

    return $dt->format("Y-m-d");
}

function chaveGrupoRelatorio($item) {
    return implode("|", [
        $item["descricao"],
        $item["categoria"] ?? "",
        $item["valor"],
        $item["parcelas"],
        $item["recorrente"]
    ]);
}

function normalizarRelatorio($lista) {
    $grupos = [];

    foreach ($lista as $item) {
        $chave = chaveGrupoRelatorio($item);
        if (!isset($grupos[$chave])) $grupos[$chave] = [];
        $grupos[$chave][] = $item;
    }

    $resultado = [];

    foreach ($grupos as $grupo) {
        usort($grupo, function ($a, $b) {
            return strcmp($a["data"], $b["data"]);
        });

        $primeiro = $grupo[0];
        $recorrente = $primeiro["recorrente"] === "Sim";
        $parcelas = intval($primeiro["parcelas"] ?: 1);

        if (count($grupo) > 1) {
            foreach ($grupo as $item) $resultado[] = $item;
            continue;
        }

        if ($recorrente) {
            for ($i = 0; $i < 12; $i++) {
                $item = $primeiro;
                $item["data"] = addMesRelatorio($primeiro["data"], $i);
                $item["parcela"] = "Recorrente";
                $resultado[] = $item;
            }
            continue;
        }

        if ($parcelas > 1 && $primeiro["parcela"] === "1/" . $parcelas) {
            for ($i = 0; $i < $parcelas; $i++) {
                $item = $primeiro;
                $item["data"] = addMesRelatorio($primeiro["data"], $i);
                $item["parcela"] = ($i + 1) . "/" . $parcelas;
                $resultado[] = $item;
            }
            continue;
        }

        $resultado[] = $primeiro;
    }

    return $resultado;
}

function filtrarRelatorio($lista, $dataIni, $dataFim, $filtroMes, $filtroAno, $busca = "") {
    return array_values(array_filter($lista, function ($item) use ($dataIni, $dataFim, $filtroMes, $filtroAno, $busca) {
        if ($dataIni !== "" && $item["data"] < $dataIni) return false;
        if ($dataFim !== "" && $item["data"] > $dataFim) return false;
        if ($filtroMes !== "" && substr($item["data"], 5, 2) !== $filtroMes) return false;
        if ($filtroAno !== "" && substr($item["data"], 0, 4) !== $filtroAno) return false;
        if ($busca !== "" && stripos($item["descricao"], $busca) === false) return false;
        return true;
    }));
}

function totalRelatorio($lista) {
    $total = 0;
    foreach ($lista as $item) {
        $total += floatval($item["valor"]);
    }

    return $total;
}

function moeda($valor) {
    return "R$ " . number_format(floatval($valor), 2, ",", ".");
}

function dataBr($data) {
    return date("d/m/Y", strtotime($data));
}

$stmt = $conexao->prepare("SELECT despesas.descricao, despesas.categoria, despesas.valor, despesas.parcelas, despesas.parcela_atual, despesas.data_vencimento, despesas.recorrente, orcamentos_categoria.categoria AS categoria_orcamento
    FROM despesas
    LEFT JOIN orcamentos_categoria
      ON orcamentos_categoria.id_orcamento = despesas.id_orcamento
     AND orcamentos_categoria.id_usuario = despesas.id_usuario
    WHERE despesas.id_usuario = ?
    ORDER BY despesas.data_vencimento ASC");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$despesasBanco = [];
while ($row = $resultado->fetch_assoc()) {
    $despesasBanco[] = [
        "tipo" => "Despesa",
        "descricao" => $row["descricao"],
        "valor" => descriptografar_valor_financeiro($row["valor"]),
        "parcelas" => intval($row["parcelas"]),
        "parcela" => $row["parcela_atual"],
        "data" => $row["data_vencimento"],
        "recorrente" => $row["recorrente"],
        "categoria" => $row["categoria"] ?? "Outros",
        "categoria_orcamento" => $row["categoria_orcamento"] ?? null
    ];
}

$stmt = $conexao->prepare("SELECT descricao, categoria, valor, parcelas, parcela_atual, data_recebimento, recorrente FROM receitas WHERE id_usuario = ? ORDER BY data_recebimento ASC");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$receitasBanco = [];
while ($row = $resultado->fetch_assoc()) {
    $receitasBanco[] = [
        "tipo" => "Receita",
        "descricao" => $row["descricao"],
        "valor" => descriptografar_valor_financeiro($row["valor"]),
        "parcelas" => intval($row["parcelas"]),
        "parcela" => $row["parcela_atual"],
        "data" => $row["data_recebimento"],
        "recorrente" => $row["recorrente"],
        "categoria" => $row["categoria"] ?? "Outros"
    ];
}

$stmt = $conexao->prepare("SELECT gastos_diarios.descricao, gastos_diarios.categoria, gastos_diarios.valor, gastos_diarios.data_gasto, orcamentos_categoria.categoria AS categoria_orcamento
    FROM gastos_diarios
    LEFT JOIN orcamentos_categoria
      ON orcamentos_categoria.id_orcamento = gastos_diarios.id_orcamento
     AND orcamentos_categoria.id_usuario = gastos_diarios.id_usuario
    WHERE gastos_diarios.id_usuario = ?
      AND gastos_diarios.id_despesa IS NULL
    ORDER BY gastos_diarios.data_gasto ASC");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$gastosBanco = [];
while ($row = $resultado->fetch_assoc()) {
    $gastosBanco[] = [
        "tipo" => "Gasto Diario",
        "descricao" => $row["descricao"],
        "valor" => descriptografar_valor_financeiro($row["valor"]),
        "parcelas" => 1,
        "parcela" => "-",
        "data" => $row["data_gasto"],
        "recorrente" => "Nao",
        "categoria" => $row["categoria"] ?? "Outros",
        "categoria_orcamento" => $row["categoria_orcamento"] ?? null
    ];
}

$despesasHistorico = normalizarRelatorio($despesasBanco);
$receitasHistorico = normalizarRelatorio($receitasBanco);

$despesas = filtrarRelatorio($despesasHistorico, $dataIni, $dataFim, $filtroMes, $filtroAno, $buscaDespesa);
$receitas = filtrarRelatorio($receitasHistorico, $dataIni, $dataFim, $filtroMes, $filtroAno, "");
$gastosDiarios = filtrarRelatorio($gastosBanco, $dataIni, $dataFim, $filtroMes, $filtroAno, $buscaDespesa);

$totalDespesas = totalRelatorio($despesas);
$totalGastosDiarios = totalRelatorio($gastosDiarios);

/*
REGRA FINCONTROL:
- Cartao de credito vira despesa e entra no relatorio financeiro.
- Debito/PIX/dinheiro permanece apenas em gastos_diarios e impacta somente o orcamento.
*/
$saidas = $despesas;
$saidasOrcamento = array_merge($despesas, $gastosDiarios);

$totalSaidas = $totalDespesas;
$totalReceitas = totalRelatorio($receitas);
$saldo = $totalReceitas - $totalSaidas;
$receitaComprometida = $totalReceitas > 0 ? ($totalSaidas / $totalReceitas) * 100 : 0;

$livro = array_merge($receitas, $despesas);
usort($livro, function ($a, $b) {
    return strcmp($b["data"], $a["data"]);
});

$mapaMes = [];
foreach ($receitas as $item) {
    $mes = substr($item["data"], 0, 7);
    if (!isset($mapaMes[$mes])) $mapaMes[$mes] = ["receitas" => 0, "despesas" => 0];
    $mapaMes[$mes]["receitas"] += $item["valor"];
}
foreach ($despesas as $item) {
    $mes = substr($item["data"], 0, 7);
    if (!isset($mapaMes[$mes])) $mapaMes[$mes] = ["receitas" => 0, "despesas" => 0];
    $mapaMes[$mes]["despesas"] += $item["valor"];
}
ksort($mapaMes);

$mapaMesSaldo = [];
foreach ($receitasHistorico as $item) {
    $mes = substr($item["data"], 0, 7);
    if (!isset($mapaMesSaldo[$mes])) $mapaMesSaldo[$mes] = ["receitas" => 0, "despesas" => 0];
    $mapaMesSaldo[$mes]["receitas"] += $item["valor"];
}
foreach ($despesasHistorico as $item) {
    $mes = substr($item["data"], 0, 7);
    if (!isset($mapaMesSaldo[$mes])) $mapaMesSaldo[$mes] = ["receitas" => 0, "despesas" => 0];
    $mapaMesSaldo[$mes]["despesas"] += $item["valor"];
}
ksort($mapaMesSaldo);

$labelsGrafico = [];
$receitasGrafico = [];
$despesasGrafico = [];
$saldoGrafico = [];
foreach ($mapaMesSaldo as $mes => $valores) {
    $labelsGrafico[] = date("m/Y", strtotime($mes . "-01"));
    $receitasGrafico[] = $valores["receitas"];
    $despesasGrafico[] = $valores["despesas"];
    $saldoGrafico[] = $valores["receitas"] - $valores["despesas"];
}

$historiaMelhorMes = ["mes" => "Sem dados", "valor" => 0];
$historiaPiorMes = ["mes" => "Sem dados", "valor" => 0];
$historiaTendencia = "Sem historico";
$historiaTendenciaClasse = "neutral";
$historiaDeltaSaldo = 0;
$historiaDeltaTexto = "Aguardando mais meses";
if (count($saldoGrafico) > 0) {
    $indiceMelhorSaldo = 0;
    $indicePiorSaldo = 0;
    foreach ($saldoGrafico as $indiceSaldo => $valorSaldo) {
        if ($valorSaldo > $saldoGrafico[$indiceMelhorSaldo]) $indiceMelhorSaldo = $indiceSaldo;
        if ($valorSaldo < $saldoGrafico[$indicePiorSaldo]) $indicePiorSaldo = $indiceSaldo;
    }
    $historiaMelhorMes = ["mes" => $labelsGrafico[$indiceMelhorSaldo] ?? "Sem dados", "valor" => $saldoGrafico[$indiceMelhorSaldo]];
    $historiaPiorMes = ["mes" => $labelsGrafico[$indicePiorSaldo] ?? "Sem dados", "valor" => $saldoGrafico[$indicePiorSaldo]];

    if (count($saldoGrafico) > 1) {
        $ultimoSaldo = floatval($saldoGrafico[count($saldoGrafico) - 1]);
        $saldoAnterior = floatval($saldoGrafico[count($saldoGrafico) - 2]);
        $historiaDeltaSaldo = $ultimoSaldo - $saldoAnterior;
        if ($historiaDeltaSaldo > 0) {
            $historiaTendencia = "Saldo melhorou";
            $historiaTendenciaClasse = "good";
        } elseif ($historiaDeltaSaldo < 0) {
            $historiaTendencia = "Saldo piorou";
            $historiaTendenciaClasse = "bad";
        } else {
            $historiaTendencia = "Saldo estavel";
        }
        $historiaDeltaTexto = moeda(abs($historiaDeltaSaldo)) . " vs mes anterior";
    }
}

$maiorDespesa = null;
foreach ($saidas as $saida) {
    if (!$maiorDespesa || $saida["valor"] > $maiorDespesa["valor"]) {
        $maiorDespesa = $saida;
    }
}

$maiorReceita = null;
foreach ($receitas as $receita) {
    if (!$maiorReceita || $receita["valor"] > $maiorReceita["valor"]) {
        $maiorReceita = $receita;
    }
}

$mesesOrcamento = meses_orcamento_periodo_fincontrol($dataIni, $dataFim, $filtroMes, $filtroAno, $saidasOrcamento);
$orcamentosRelatorio = buscar_orcamentos_fincontrol($conexao, $id_usuario, $mesesOrcamento);
$realizadoOrcamentoGeral = realizado_por_categoria_fincontrol($saidasOrcamento, $mesesOrcamento);
$realizadoOrcamento = realizado_apenas_categorias_orcadas_fincontrol($orcamentosRelatorio, $realizadoOrcamentoGeral);
$comparativoOrcamento = comparar_orcamento_realizado_fincontrol($orcamentosRelatorio, $realizadoOrcamento, false);
$totalOrcadoRelatorio = array_sum($orcamentosRelatorio);
$totalRealizadoOrcamento = array_sum($realizadoOrcamento);
$saldoOrcamentoRelatorio = $totalOrcadoRelatorio - $totalRealizadoOrcamento;

$economiaPercentual = $totalReceitas > 0 ? ($saldo / $totalReceitas) * 100 : 0;
$ticketMedioSaida = count($saidas) > 0 ? $totalSaidas / count($saidas) : 0;
$ticketMedioReceita = count($receitas) > 0 ? $totalReceitas / count($receitas) : 0;

$mapaCategoriaReceitas = [];
foreach ($receitas as $item) {
    $categoria = trim($item["categoria"] ?? "Outros");
    if ($categoria === "") $categoria = "Outros";
    if (!isset($mapaCategoriaReceitas[$categoria])) $mapaCategoriaReceitas[$categoria] = 0;
    $mapaCategoriaReceitas[$categoria] += floatval($item["valor"]);
}
arsort($mapaCategoriaReceitas);
$labelsReceitasCategorias = array_keys(array_slice($mapaCategoriaReceitas, 0, 5, true));
$valoresReceitasCategorias = array_values(array_slice($mapaCategoriaReceitas, 0, 5, true));

$mapaCategoriaSaidas = [];
foreach ($saidas as $item) {
    $categoria = trim($item["categoria"] ?? "Outros");
    if ($categoria === "") $categoria = "Outros";
    if (!isset($mapaCategoriaSaidas[$categoria])) $mapaCategoriaSaidas[$categoria] = 0;
    $mapaCategoriaSaidas[$categoria] += floatval($item["valor"]);
}
arsort($mapaCategoriaSaidas);

$labelsCategorias = array_keys($mapaCategoriaSaidas);
$valoresCategorias = array_values($mapaCategoriaSaidas);
$topCategorias = array_slice($mapaCategoriaSaidas, 0, 5, true);
$chavesTopCategorias = array_keys($topCategorias);
$categoriaPrincipal = count($chavesTopCategorias) ? $chavesTopCategorias[0] : "Sem dados";
$valorCategoriaPrincipal = count($topCategorias) ? reset($topCategorias) : 0;
$percentualCategoriaPrincipal = $totalSaidas > 0 ? ($valorCategoriaPrincipal / $totalSaidas) * 100 : 0;

$chavesMapaMes = array_keys($mapaMes);
$mesAtualChave = count($chavesMapaMes) ? $chavesMapaMes[count($chavesMapaMes) - 1] : "";
$mesAnteriorChave = "";
$variacaoSaidas = 0;
$variacaoReceitas = 0;
$textoComparacao = "Sem historico suficiente para comparacao mensal.";
if ($mesAtualChave !== "") {
    $chavesMes = array_keys($mapaMes);
    $idxAtual = array_search($mesAtualChave, $chavesMes);
    if ($idxAtual !== false && $idxAtual > 0) {
        $mesAnteriorChave = $chavesMes[$idxAtual - 1];
        $saidasAtual = $mapaMes[$mesAtualChave]["despesas"] ?? 0;
        $saidasAnterior = $mapaMes[$mesAnteriorChave]["despesas"] ?? 0;
        $receitasAtual = $mapaMes[$mesAtualChave]["receitas"] ?? 0;
        $receitasAnterior = $mapaMes[$mesAnteriorChave]["receitas"] ?? 0;
        $variacaoSaidas = $saidasAnterior > 0 ? (($saidasAtual - $saidasAnterior) / $saidasAnterior) * 100 : 0;
        $variacaoReceitas = $receitasAnterior > 0 ? (($receitasAtual - $receitasAnterior) / $receitasAnterior) * 100 : 0;
        $textoComparacao = "Comparacao de " . date("m/Y", strtotime($mesAnteriorChave . "-01")) . " para " . date("m/Y", strtotime($mesAtualChave . "-01")) . ".";
    }
}

$scoreFinanceiro = 100;
if ($totalReceitas <= 0 && $totalSaidas > 0) $scoreFinanceiro -= 35;
if ($receitaComprometida > 90) $scoreFinanceiro -= 30;
elseif ($receitaComprometida > 75) $scoreFinanceiro -= 20;
elseif ($receitaComprometida > 60) $scoreFinanceiro -= 10;
if ($saldo < 0) $scoreFinanceiro -= 25;
if ($percentualCategoriaPrincipal > 45) $scoreFinanceiro -= 10;
if (count($livro) < 5) $scoreFinanceiro -= 5;
$scoreFinanceiro = max(0, min(100, round($scoreFinanceiro)));

if ($scoreFinanceiro >= 80) $statusScore = "Controle saudavel";
elseif ($scoreFinanceiro >= 60) $statusScore = "Atencao moderada";
else $statusScore = "Risco financeiro";

$saldoProjetado = $saldo;
$economiaSugerida = $valorCategoriaPrincipal * 0.15;
$saldoComAcao = $saldo + $economiaSugerida;

$recomendacoes = [];
if ($saldo < 0) {
    $recomendacoes[] = "Seu saldo esta negativo no periodo. Priorize reduzir gastos variaveis antes de assumir novas parcelas.";
}
if ($receitaComprometida > 80) {
    $recomendacoes[] = "Mais de 80% da receita esta comprometida. O ideal e revisar despesas recorrentes e parceladas.";
}
if ($percentualCategoriaPrincipal > 35 && $categoriaPrincipal !== "Sem dados") {
    $recomendacoes[] = "A categoria " . $categoriaPrincipal . " concentra " . number_format($percentualCategoriaPrincipal, 1, ",", ".") . "% das saidas. Uma reducao de 15% economizaria cerca de " . moeda($economiaSugerida) . ".";
}
if ($variacaoSaidas > 15) {
    $recomendacoes[] = "As saidas cresceram " . number_format($variacaoSaidas, 1, ",", ".") . "% em relacao ao mes anterior. Vale investigar o motivo.";
}
if (!count($recomendacoes)) {
    $recomendacoes[] = "Seu controle esta equilibrado no periodo. Continue acompanhando as categorias para manter previsibilidade.";
}

$ticketMaximoSaida = 0;
$ticketMinimoSaida = 0;
foreach ($saidas as $saida) {
    $valorSaida = floatval($saida["valor"]);
    if ($ticketMaximoSaida === 0 || $valorSaida > $ticketMaximoSaida) {
        $ticketMaximoSaida = $valorSaida;
    }
    if ($ticketMinimoSaida === 0 || $valorSaida < $ticketMinimoSaida) {
        $ticketMinimoSaida = $valorSaida;
    }
}
$quantidadeLancamentos = count($receitas) + count($saidas);
$percentualComprometidoGauge = max(0, min(100, $receitaComprometida));

$periodoTexto = "Todos os lancamentos";
if ($dataIni !== "" && $dataFim !== "") $periodoTexto = dataBr($dataIni) . " ate " . dataBr($dataFim);
elseif ($dataIni !== "") $periodoTexto = "A partir de " . dataBr($dataIni);
elseif ($dataFim !== "") $periodoTexto = "Ate " . dataBr($dataFim);
elseif ($filtroMes !== "" || $filtroAno !== "") $periodoTexto = trim(($filtroMes ? "Mes " . $filtroMes : "") . " " . ($filtroAno ? "Ano " . $filtroAno : ""));
require __DIR__ . '/07.relatorios_view.php';






