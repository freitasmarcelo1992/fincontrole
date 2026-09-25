<?php
require_once "00.sessao.php";
require_once "09.conexao.php";
require_once "00.crypto.php";
require_once "00.categorias.php";
require_once "00.despesas_status.php";
require_once "00.orcamentos.php";
require_once "00.planos.php";
require_once "00.cache.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: 02.login.php");
    exit();
}

header("Location: 28.financas.php");
exit();

if (!$conexao) {
    http_response_code(503);
    echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>FinControle | Indisponível</title>';
    echo '<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#eef4f8;font-family:Arial,sans-serif;color:#0b2d4d}.box{width:min(420px,calc(100% - 32px));background:#fff;border-radius:16px;padding:24px;box-shadow:0 18px 40px rgba(7,31,51,.16);text-align:center}.box h1{margin:0 0 10px;color:#0077b6;font-size:24px}.box p{line-height:1.45}.box a{display:inline-block;margin-top:12px;background:#0077b6;color:#fff;text-decoration:none;border-radius:10px;padding:11px 14px;font-weight:700}</style>';
    echo '</head><body><main class="box"><h1>Conexão instável</h1><p>Não foi possível carregar seu dashboard agora. Aguarde alguns segundos e tente novamente.</p><a href="03.menu.php">Tentar novamente</a></main></body></html>';
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$nome_usuario = $_SESSION["nome_usuario"] ?? "Usuário";
$tipo_usuario = $_SESSION["tipo_usuario"] ?? "comum";
$dataIni = $_GET["dataIni"] ?? ($_GET["data_inicio"] ?? "");
$dataFim = $_GET["dataFim"] ?? ($_GET["data_fim"] ?? "");
$filtroMes = $_GET["filtroMes"] ?? "";
$filtroAno = $_GET["filtroAno"] ?? "";
$buscaDespesa = trim($_GET["buscaDespesa"] ?? "");
$assinatura_dashboard = function_exists("fincontrol_assinatura_usuario") ? fincontrol_assinatura_usuario($conexao, $id_usuario) : null;
$premium_ativo_dashboard = true;

function avatarDashboardArquivo($idUsuario) {
    $padrao = __DIR__ . "/uploads/avatars/avatar_" . intval($idUsuario) . ".*";
    $arquivos = glob($padrao);
    return $arquivos && isset($arquivos[0]) ? $arquivos[0] : "";
}

function avatarDashboardSrc($idUsuario) {
    $arquivo = avatarDashboardArquivo($idUsuario);
    if ($arquivo === "" || !is_file($arquivo)) {
        return "";
    }

    return "uploads/avatars/" . basename($arquivo) . "?v=" . filemtime($arquivo);
}

function uploadAvatarDashboard($idUsuario) {
    if (empty($_FILES["avatar_usuario"]) || !is_array($_FILES["avatar_usuario"])) {
        return false;
    }

    $arquivo = $_FILES["avatar_usuario"];
    if (($arquivo["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return false;
    }

    if (($arquivo["size"] ?? 0) > 2 * 1024 * 1024) {
        return false;
    }

    $info = @getimagesize($arquivo["tmp_name"]);
    $mime = $info["mime"] ?? "";
    $extensoes = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (!isset($extensoes[$mime])) {
        return false;
    }

    $pasta = __DIR__ . "/uploads/avatars";
    if (!is_dir($pasta)) {
        mkdir($pasta, 0755, true);
    }

    foreach (glob($pasta . "/avatar_" . intval($idUsuario) . ".*") ?: [] as $antigo) {
        if (is_file($antigo)) {
            unlink($antigo);
        }
    }

    $destino = $pasta . "/avatar_" . intval($idUsuario) . "." . $extensoes[$mime];
    return move_uploaded_file($arquivo["tmp_name"], $destino);
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataIni)) {
    $dataIni = "";
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFim)) {
    $dataFim = "";
}

if (!preg_match('/^\d{2}$/', $filtroMes)) {
    $filtroMes = "";
}

if (!preg_match('/^\d{4}$/', $filtroAno)) {
    $filtroAno = "";
}

if ($dataIni !== "" && $dataFim !== "" && $dataIni > $dataFim) {
    $data_temporaria = $dataIni;
    $dataIni = $dataFim;
    $dataFim = $data_temporaria;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["acao_dashboard"] ?? "") === "upload_avatar") {
    if (uploadAvatarDashboard($id_usuario)) {
        fincontrol_cache_invalidar_usuario($id_usuario);
    }
    header("Location: 03.menu.php");
    exit();
}

$avatarDashboardSrc = avatarDashboardSrc($id_usuario);

function parseDataDashboard($data) {
    return DateTime::createFromFormat("Y-m-d", $data) ?: null;
}

function addMesDashboard($data, $meses) {
    $dt = parseDataDashboard($data);

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

function periodoSaldoDashboard($mes, $ano) {
    $mes = preg_match('/^\d{1,2}$/', (string)$mes) ? intval($mes) : intval(date("m"));
    $ano = preg_match('/^\d{4}$/', (string)$ano) ? intval($ano) : intval(date("Y"));

    if ($mes < 1 || $mes > 12) {
        $mes = intval(date("m"));
    }

    return str_pad((string)$mes, 2, "0", STR_PAD_LEFT) . "/" . $ano;
}

function chaveGrupoDashboard($item) {
    return implode("|", [
        $item["descricao"],
        $item["categoria"] ?? "",
        $item["valor"],
        $item["parcelas"],
        $item["recorrente"]
    ]);
}

function normalizarLancamentosDashboard($lista) {
    $grupos = [];

    foreach ($lista as $item) {
        $chave = chaveGrupoDashboard($item);

        if (!isset($grupos[$chave])) {
            $grupos[$chave] = [];
        }

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
            foreach ($grupo as $item) {
                $resultado[] = $item;
            }
            continue;
        }

        if ($recorrente) {
            for ($i = 0; $i < 12; $i++) {
                $item = $primeiro;
                $item["data"] = addMesDashboard($primeiro["data"], $i);
                $item["parcela"] = "Recorrente";
                $resultado[] = $item;
            }
            continue;
        }

        if ($parcelas > 1 && $primeiro["parcela"] === "1/" . $parcelas) {
            for ($i = 0; $i < $parcelas; $i++) {
                $item = $primeiro;
                $item["data"] = addMesDashboard($primeiro["data"], $i);
                $item["parcela"] = ($i + 1) . "/" . $parcelas;
                $resultado[] = $item;
            }
            continue;
        }

        $resultado[] = $primeiro;
    }

    return $resultado;
}

function filtrarDashboard($lista, $dataIni, $dataFim, $filtroMes, $filtroAno, $busca = "") {
    return array_values(array_filter($lista, function ($item) use ($dataIni, $dataFim, $filtroMes, $filtroAno, $busca) {
        if ($dataIni !== "" && $item["data"] < $dataIni) {
            return false;
        }

        if ($dataFim !== "" && $item["data"] > $dataFim) {
            return false;
        }

        if ($filtroMes !== "" && substr($item["data"], 5, 2) !== $filtroMes) {
            return false;
        }

        if ($filtroAno !== "" && substr($item["data"], 0, 4) !== $filtroAno) {
            return false;
        }

        if ($busca !== "" && stripos($item["descricao"], $busca) === false) {
            return false;
        }

        return true;
    }));
}

function somarDashboard($lista) {
    return array_sum(array_map(function ($item) {
        return floatval($item["valor"]);
    }, $lista));
}

function maiorDashboard($lista) {
    if (!count($lista)) {
        return null;
    }

    usort($lista, function ($a, $b) {
        return floatval($b["valor"]) <=> floatval($a["valor"]);
    });

    return $lista[0];
}

function agruparDashboard($lista, $campo) {
    $mapa = [];

    foreach ($lista as $item) {
        $chave = $item[$campo] ?: "Sem categoria";
        $mapa[$chave] = ($mapa[$chave] ?? 0) + floatval($item["valor"]);
    }

    arsort($mapa);

    return array_slice($mapa, 0, 8, true);
}

function moedaDashboard($valor) {
    return "R$ " . number_format(floatval($valor), 2, ",", ".");
}

$dashboardCacheChave = fincontrol_cache_dashboard_chave($id_usuario, [
    "dataIni" => $dataIni,
    "dataFim" => $dataFim,
    "filtroMes" => $filtroMes,
    "filtroAno" => $filtroAno,
    "busca" => $buscaDespesa,
    "premium" => $premium_ativo_dashboard ? 1 : 0,
    "versao" => FINCONTROL_VERSION,
    "avatar" => $avatarDashboardSrc,
    "cache" => "dashboard-safe-20260703",
]);
$dashboardCacheConteudo = fincontrol_cache_ler($dashboardCacheChave);
if ($dashboardCacheConteudo !== null) {
    echo $dashboardCacheConteudo;
    exit;
}
ob_start();

function garantirTabelasCaixinhasDashboard($conexao) {
    if (!$conexao) {
        return false;
    }

    $okPlanos = $conexao->query("CREATE TABLE IF NOT EXISTS caixinhas_economia (
        id_caixinha int NOT NULL AUTO_INCREMENT,
        id_usuario int NOT NULL,
        nome varchar(120) NOT NULL,
        objetivo varchar(160) DEFAULT NULL,
        meta_valor text NOT NULL,
        data_meta date DEFAULT NULL,
        status varchar(20) NOT NULL DEFAULT 'ativa',
        criado_em timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        atualizado_em timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id_caixinha),
        KEY idx_caixinhas_usuario_status (id_usuario, status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $okLancamentos = $conexao->query("CREATE TABLE IF NOT EXISTS caixinhas_lancamentos (
        id_lancamento int NOT NULL AUTO_INCREMENT,
        id_caixinha int NOT NULL,
        id_usuario int NOT NULL,
        descricao varchar(160) NOT NULL,
        valor text NOT NULL,
        data_lancamento date NOT NULL,
        observacao text NULL,
        criado_em timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_lancamento),
        KEY idx_caixinhas_lancamentos_usuario_data (id_usuario, data_lancamento),
        KEY idx_caixinhas_lancamentos_caixinha (id_caixinha)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    return (bool) $okPlanos && (bool) $okLancamentos;
}

function progressoCaixinhaDashboard($meta, $realizado) {
    $meta = floatval($meta);
    if ($meta <= 0) {
        return 0;
    }

    return min(100, max(0, ($realizado / $meta) * 100));
}

function dataCaixinhaDashboard($data) {
    if (!$data || $data === "0000-00-00") {
        return "-";
    }

    return date("d/m/Y", strtotime($data));
}

function primeiroNomeDashboard($nome) {
    $partes = preg_split('/\s+/', trim($nome));
    return $partes && $partes[0] !== "" ? $partes[0] : "Usuário";
}

function saudacaoDashboard() {
    $hora = intval(date("H"));

    if ($hora < 12) return "Bom dia";
    if ($hora < 18) return "Boa tarde";
    return "Boa noite";
}

function dataLongaDashboard($data) {
    $meses = [
        "01" => "Janeiro", "02" => "Fevereiro", "03" => "Marco", "04" => "Abril",
        "05" => "Maio", "06" => "Junho", "07" => "Julho", "08" => "Agosto",
        "09" => "Setembro", "10" => "Outubro", "11" => "Novembro", "12" => "Dezembro"
    ];

    return date("d", strtotime($data)) . " de " . $meses[date("m", strtotime($data))] . " de " . date("Y", strtotime($data));
}

function mesCurtoDashboard($data) {
    $meses = [
        "01" => "JAN", "02" => "FEV", "03" => "MAR", "04" => "ABR",
        "05" => "MAI", "06" => "JUN", "07" => "JUL", "08" => "AGO",
        "09" => "SET", "10" => "OUT", "11" => "NOV", "12" => "DEZ"
    ];

    return $meses[date("m", strtotime($data))] ?? strtoupper(date("M", strtotime($data)));
}

function variacaoPercentualDashboard($atual, $anterior) {
    if (floatval($anterior) <= 0) {
        return floatval($atual) > 0 ? 100 : 0;
    }

    return (($atual - $anterior) / $anterior) * 100;
}

function resumoMesDashboard($lista, $mes) {
    $total = 0;

    foreach ($lista as $item) {
        if (substr($item["data"], 0, 7) === $mes) {
            $total += floatval($item["valor"]);
        }
    }

    return $total;
}

function scoreSaudeDashboard($totalReceitas, $totalSaidas, $saldo, $usoOrcamento) {
    $score = 100;
    $comprometimento = $totalReceitas > 0 ? ($totalSaidas / $totalReceitas) * 100 : 0;

    if ($totalReceitas <= 0 && $totalSaidas > 0) $score -= 35;
    if ($saldo < 0) $score -= 25;
    if ($comprometimento > 90) $score -= 25;
    elseif ($comprometimento > 75) $score -= 15;
    elseif ($comprometimento > 60) $score -= 8;
    if ($usoOrcamento > 100) $score -= 18;
    elseif ($usoOrcamento > 85) $score -= 8;

    return max(0, min(100, round($score)));
}

function statusSaudeDashboard($score) {
    if ($score >= 85) return ["Excelente", "Suas finanças estão muito bem organizadas.", "saude-excelente"];
    if ($score >= 70) return ["Boa", "Seu controle está saudável, com pontos para acompanhar.", "saude-boa"];
    if ($score >= 50) return ["Atenção", "Alguns indicadores pedem revisão neste período.", "saude-atencao"];
    return ["Risco", "As saídas estão pressionando seu planejamento.", "saude-risco"];
}

function topCategoriasDashboard($saidas, $limite = 5) {
    $mapa = agruparDashboard($saidas, "categoria");
    return array_slice($mapa, 0, $limite, true);
}

function proximosVencimentosDashboard($despesasBanco, $limite = 3) {
    $hoje = date("Y-m-d");
    $lista = [];

    foreach ($despesasBanco as $despesa) {
        if (($despesa["status"] ?? "A vencer") === "Paga") {
            continue;
        }

        if ($despesa["data"] < $hoje) {
            continue;
        }

        $lista[] = $despesa;
    }

    usort($lista, function ($a, $b) {
        return strcmp($a["data"], $b["data"]);
    });
    return array_slice($lista, 0, $limite);
}

function conquistasDashboard($score, $saldo, $totalLancamentos, $proximosVencimentos) {
    return [
        [
            "icone" => "fa-shield-halved",
            "titulo" => count($proximosVencimentos) ? "Agenda ativa" : "Sem vencimentos",
            "texto" => count($proximosVencimentos) ? "próximos pagamentos mapeados" : "no período filtrado",
            "classe" => "verde"
        ],
        [
            "icone" => "fa-star",
            "titulo" => $saldo >= 0 ? "Saldo positivo" : "Revisar saldo",
            "texto" => $saldo >= 0 ? "período no azul" : "período no vermelho",
            "classe" => "azul"
        ],
        [
            "icone" => "fa-bullseye",
            "titulo" => $score >= 70 ? "Controle forte" : "Meta de controle",
            "texto" => "score " . $score . "/100",
            "classe" => "laranja"
        ],
        [
            "icone" => "fa-layer-group",
            "titulo" => $totalLancamentos . " lançamentos",
            "texto" => "registrados no filtro",
            "classe" => "roxo"
        ]
    ];
}

$sql = "SELECT despesas.descricao, despesas.categoria, despesas.valor, despesas.parcelas, despesas.parcela_atual, despesas.data_vencimento, despesas.recorrente, despesas.status, orcamentos_categoria.categoria AS categoria_orcamento
        FROM despesas
        LEFT JOIN orcamentos_categoria
          ON orcamentos_categoria.id_orcamento = despesas.id_orcamento
         AND orcamentos_categoria.id_usuario = despesas.id_usuario
        WHERE despesas.id_usuario = ?
        ORDER BY despesas.data_vencimento ASC";
$stmt = $conexao->prepare($sql);
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
        "status" => $row["status"] ?? "A vencer",
        "categoria_orcamento" => $row["categoria_orcamento"] ?? null
    ];
}

$sql = "SELECT descricao, categoria, valor, parcelas, parcela_atual, data_recebimento, recorrente
        FROM receitas
        WHERE id_usuario = ?
        ORDER BY data_recebimento ASC";
$stmt = $conexao->prepare($sql);
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

$sql = "SELECT gastos_diarios.descricao, gastos_diarios.categoria, gastos_diarios.valor, gastos_diarios.data_gasto, orcamentos_categoria.categoria AS categoria_orcamento
        FROM gastos_diarios
        LEFT JOIN orcamentos_categoria
          ON orcamentos_categoria.id_orcamento = gastos_diarios.id_orcamento
         AND orcamentos_categoria.id_usuario = gastos_diarios.id_usuario
        WHERE gastos_diarios.id_usuario = ?
          AND gastos_diarios.id_despesa IS NULL
        ORDER BY gastos_diarios.data_gasto ASC";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$gastosBanco = [];
while ($row = $resultado->fetch_assoc()) {
    $gastosBanco[] = [
        "tipo" => "Gasto Diário",
        "descricao" => $row["descricao"],
        "valor" => descriptografar_valor_financeiro($row["valor"]),
        "parcelas" => 1,
        "parcela" => "-",
        "data" => $row["data_gasto"],
        "recorrente" => "Não",
        "categoria" => $row["categoria"] ?? "Outros",
        "categoria_orcamento" => $row["categoria_orcamento"] ?? null
    ];
}

$despesas = filtrarDashboard(normalizarLancamentosDashboard($despesasBanco), $dataIni, $dataFim, $filtroMes, $filtroAno, $buscaDespesa);
$receitas = filtrarDashboard(normalizarLancamentosDashboard($receitasBanco), $dataIni, $dataFim, $filtroMes, $filtroAno, "");
$gastosDiarios = filtrarDashboard($gastosBanco, $dataIni, $dataFim, $filtroMes, $filtroAno, $buscaDespesa);

$totalReceitas = somarDashboard($receitas);
$totalDespesas = somarDashboard($despesas);
$totalGastosDiarios = somarDashboard($gastosDiarios);

/*
REGRA FINCONTROL:
- Cartão de crédito vira despesa e entra no relatório/dashboard financeiro.
- Débito/PIX/dinheiro permanece apenas em gastos_diarios e impacta somente o limite de gastos.
*/
$saidas = $despesas;
$saidasOrcamento = array_merge($despesas, $gastosDiarios);

$totalSaidas = $totalDespesas;
$saldo = $totalReceitas - $totalSaidas;
$receitaComprometida = $totalReceitas > 0 ? ($totalSaidas / $totalReceitas) * 100 : 0;
$economiaPercentual = $totalReceitas > 0 ? ($saldo / $totalReceitas) * 100 : 0;

$maiorDespesa = maiorDashboard($saidas);
$maiorReceita = maiorDashboard($receitas);

$livro = array_merge($receitas, $despesas);
usort($livro, function ($a, $b) {
    return strcmp($b["data"], $a["data"]);
});

$ultimosArray = array_slice($livro, 0, 6);

$mapaMesDashboard = [];
foreach ($receitas as $item) {
    $mes = substr($item["data"], 0, 7);
    if (!isset($mapaMesDashboard[$mes])) $mapaMesDashboard[$mes] = ["receitas" => 0, "despesas" => 0];
    $mapaMesDashboard[$mes]["receitas"] += floatval($item["valor"]);
}
foreach ($despesas as $item) {
    $mes = substr($item["data"], 0, 7);
    if (!isset($mapaMesDashboard[$mes])) $mapaMesDashboard[$mes] = ["receitas" => 0, "despesas" => 0];
    $mapaMesDashboard[$mes]["despesas"] += floatval($item["valor"]);
}
ksort($mapaMesDashboard);

$variacaoSaidas = 0;
$variacaoReceitas = 0;
if (count($mapaMesDashboard) > 1) {
    $chavesMesDashboard = array_keys($mapaMesDashboard);
    $mesAtualDashboard = $chavesMesDashboard[count($chavesMesDashboard) - 1];
    $mesAnteriorDashboard = $chavesMesDashboard[count($chavesMesDashboard) - 2];
    $variacaoSaidas = variacaoPercentualDashboard(
        $mapaMesDashboard[$mesAtualDashboard]["despesas"] ?? 0,
        $mapaMesDashboard[$mesAnteriorDashboard]["despesas"] ?? 0
    );
    $variacaoReceitas = variacaoPercentualDashboard(
        $mapaMesDashboard[$mesAtualDashboard]["receitas"] ?? 0,
        $mapaMesDashboard[$mesAnteriorDashboard]["receitas"] ?? 0
    );
}

$mapaCategoriaSaidas = [];
foreach ($saidas as $item) {
    $categoria = trim($item["categoria"] ?? "Outros");
    if ($categoria === "") $categoria = "Outros";
    if (!isset($mapaCategoriaSaidas[$categoria])) $mapaCategoriaSaidas[$categoria] = 0;
    $mapaCategoriaSaidas[$categoria] += floatval($item["valor"]);
}
arsort($mapaCategoriaSaidas);

$topCategoriasDashboard = array_slice($mapaCategoriaSaidas, 0, 5, true);
$categoriaPrincipalDashboard = count($topCategoriasDashboard) ? array_key_first($topCategoriasDashboard) : "Sem dados";
$valorCategoriaPrincipalDashboard = count($topCategoriasDashboard) ? reset($topCategoriasDashboard) : 0;
$percentualCategoriaPrincipalDashboard = $totalSaidas > 0 ? ($valorCategoriaPrincipalDashboard / $totalSaidas) * 100 : 0;

$mesesOrcamentoDashboard = meses_orcamento_periodo_fincontrol($dataIni, $dataFim, $filtroMes, $filtroAno, $saidasOrcamento);
$orcamentosDashboard = buscar_orcamentos_fincontrol($conexao, $id_usuario, $mesesOrcamentoDashboard);
$realizadoOrcamentoGeralDashboard = realizado_por_categoria_fincontrol($saidasOrcamento, $mesesOrcamentoDashboard);
$realizadoOrcadoDashboard = realizado_apenas_categorias_orcadas_fincontrol($orcamentosDashboard, $realizadoOrcamentoGeralDashboard);
$totalOrcadoDashboard = array_sum($orcamentosDashboard);
$totalRealizadoOrcadoDashboard = array_sum($realizadoOrcadoDashboard);
$usoOrcamentoDashboard = $totalOrcadoDashboard > 0 ? ($totalRealizadoOrcadoDashboard / $totalOrcadoDashboard) * 100 : 0;

$scoreFinanceiroDashboard = 100;
if ($totalReceitas <= 0 && $totalSaidas > 0) $scoreFinanceiroDashboard -= 35;
if ($receitaComprometida > 90) $scoreFinanceiroDashboard -= 30;
elseif ($receitaComprometida > 75) $scoreFinanceiroDashboard -= 20;
elseif ($receitaComprometida > 60) $scoreFinanceiroDashboard -= 10;
if ($saldo < 0) $scoreFinanceiroDashboard -= 25;
if ($percentualCategoriaPrincipalDashboard > 45) $scoreFinanceiroDashboard -= 10;
if (count($livro) < 5) $scoreFinanceiroDashboard -= 5;
$scoreFinanceiroDashboard = max(0, min(100, round($scoreFinanceiroDashboard)));

if ($scoreFinanceiroDashboard >= 80) {
    $statusSaudeDashboard = "Controle saudável";
    $textoSaudeDashboard = "Seu controle está saudável no período filtrado.";
} elseif ($scoreFinanceiroDashboard >= 60) {
    $statusSaudeDashboard = "Atenção moderada";
    $textoSaudeDashboard = "Alguns indicadores pedem acompanhamento no período filtrado.";
} else {
    $statusSaudeDashboard = "Risco financeiro";
    $textoSaudeDashboard = "As saídas estão pressionando o período filtrado.";
}

$caixinhasDashboard = [];
$totalMetaEconomiaDashboard = 0;
$totalRealizadoEconomiaDashboard = 0;

if (false) {
    $stmt = $conexao->prepare("SELECT id_caixinha, nome, objetivo, meta_valor, data_meta FROM caixinhas_economia WHERE id_usuario = ? AND status = 'ativa' ORDER BY criado_em DESC");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    while ($row = $resultado->fetch_assoc()) {
        $row["meta"] = descriptografar_valor_financeiro($row["meta_valor"]);
        $row["realizado"] = 0;
        $row["qtd_lancamentos"] = 0;
        $caixinhasDashboard[intval($row["id_caixinha"])] = $row;
    }
    $stmt->close();

    if (count($caixinhasDashboard)) {
        $stmt = $conexao->prepare("SELECT id_caixinha, valor FROM caixinhas_lancamentos WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        while ($row = $resultado->fetch_assoc()) {
            $idCaixinhaDashboard = intval($row["id_caixinha"]);
            if (!isset($caixinhasDashboard[$idCaixinhaDashboard])) {
                continue;
            }

            $valorEconomizadoDashboard = descriptografar_valor_financeiro($row["valor"]);
            $caixinhasDashboard[$idCaixinhaDashboard]["realizado"] += $valorEconomizadoDashboard;
            $caixinhasDashboard[$idCaixinhaDashboard]["qtd_lancamentos"]++;
        }
        $stmt->close();

        foreach ($caixinhasDashboard as $caixinhaDashboard) {
            $totalMetaEconomiaDashboard += floatval($caixinhaDashboard["meta"]);
            $totalRealizadoEconomiaDashboard += floatval($caixinhaDashboard["realizado"]);
        }
    }
}

$progressoEconomiaDashboard = $totalMetaEconomiaDashboard > 0 ? min(100, ($totalRealizadoEconomiaDashboard / $totalMetaEconomiaDashboard) * 100) : 0;
$totalLancamentosDashboard = count($livro);
$proximosVencimentos = proximosVencimentosDashboard($despesasBanco, 3);
$conquistasDashboard = conquistasDashboard($scoreFinanceiroDashboard, $saldo, $totalLancamentosDashboard, $proximosVencimentos);

$insightsDashboard = [];
$insightsDashboard[] = [
    "icone" => "fa-arrow-trend-" . ($variacaoSaidas <= 0 ? "down" : "up"),
    "classe" => $variacaoSaidas <= 0 ? "verde" : "vermelho",
    "texto" => count($mapaMesDashboard) > 1
        ? "As saídas ficaram " . number_format(abs($variacaoSaidas), 1, ",", ".") . "% " . ($variacaoSaidas <= 0 ? "menores" : "maiores") . " que no mês anterior do filtro."
        : "Total de saídas no filtro: " . moedaDashboard($totalSaidas) . "."
];
$insightsDashboard[] = [
    "icone" => "fa-cart-shopping",
    "classe" => "azul",
    "texto" => "Sua maior categoria foi " . $categoriaPrincipalDashboard . ", representando " . number_format($percentualCategoriaPrincipalDashboard, 1, ",", ".") . "% das saídas."
];
$insightsDashboard[] = [
    "icone" => "fa-chart-pie",
    "classe" => "laranja",
    "texto" => $totalOrcadoDashboard > 0
        ? "Você já utilizou " . number_format($usoOrcamentoDashboard, 1, ",", ".") . "% do limite de gastos no período filtrado."
        : "Nenhum limite de gastos encontrado para o período filtrado."
];
$insightsDashboard[] = [
    "icone" => "fa-arrow-trend-up",
    "classe" => "roxo",
    "texto" => $saldo >= 0
        ? "O período filtrado fecha com saldo positivo de " . moedaDashboard($saldo) . "."
        : "O período filtrado fecha negativo em " . moedaDashboard(abs($saldo)) . "."
];

$labelsCategoriasDashboard = array_keys($topCategoriasDashboard);
$valoresCategoriasDashboard = array_values($topCategoriasDashboard);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?php require_once "00.pwa.php"; ?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinControle | Dashboard</title>

<link href="img/logo-FinControle.png" rel="icon" type="image/png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="assets/chart.umd.min.js"></script>

<style>
*{box-sizing:border-box;font-family:Poppins,Arial,sans-serif}
body{margin:0;background:#eef4f8;color:#16304f;display:flex;min-height:100vh}
.sidebar{width:248px;background:linear-gradient(180deg,#0077b6,#00a7d8);color:#fff;display:flex;flex-direction:column;padding:24px 18px;box-shadow:4px 0 18px rgba(0,0,0,.08)}
.brand{display:flex;align-items:center;gap:10px;margin-bottom:28px;font-size:23px;font-weight:900}
.brand-menu-button{width:36px;height:36px;border:0;border-radius:10px;background:rgba(255,255,255,.16);color:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
.brand-menu-button:hover{background:rgba(255,255,255,.26)}
.sidebar a{color:#fff;text-decoration:none;padding:12px 14px;border-radius:10px;display:flex;align-items:center;gap:10px;margin-bottom:7px;font-weight:800}
.sidebar a:hover,.sidebar a.active{background:rgba(255,255,255,.18)}
.sidebar a i{width:20px;text-align:center}
.tutorial-link{margin-top:auto}
.support-card{background:rgba(255,255,255,.16);border-radius:12px;padding:14px;display:flex;gap:10px;align-items:center;font-size:12px;font-weight:800}
.support-card i{font-size:22px}
.logout-link{margin-top:10px;background:rgba(255,255,255,.12)}
.main{flex:1;padding:28px 34px;overflow:auto}
.topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:22px}
.title h1{margin:0;color:#17314f;font-size:31px}
.title p{margin:6px 0 0;color:#667085;font-size:16px}
.user-box{background:#fff;border-radius:12px;padding:12px 16px;box-shadow:0 8px 26px rgba(0,0,0,.08);display:flex;align-items:center;gap:10px;font-weight:800;white-space:nowrap}
.user-box i{color:#0077b6}
.filters-panel{background:#fff;border-radius:16px;padding:14px;box-shadow:0 8px 26px rgba(0,0,0,.07);margin-bottom:18px;display:flex;align-items:end;gap:10px;flex-wrap:wrap}
.filters-panel label{display:block;color:#667085;font-size:12px;font-weight:900;margin-bottom:5px}
.filters-panel input,.filters-panel select{padding:9px 10px;border:1px solid #d9e2ec;border-radius:9px;background:#fff;color:#243041;min-height:38px}
.filters-panel button,.filters-panel a{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:38px;padding:9px 13px;border:0;border-radius:9px;text-decoration:none;font-weight:900;cursor:pointer}
.filters-panel button{background:#0077b6;color:#fff}.filters-panel a{background:#eef3f7;color:#425466}
.app-dashboard-filter-toggle{display:none}
.app-dashboard-filter-button{display:none}
.dashboard-notices{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px}
.setup-banner{background:#0077b6;color:#fff;border-radius:16px;padding:20px;margin-bottom:0;display:flex;justify-content:space-between;align-items:center;gap:18px;box-shadow:0 8px 26px rgba(0,0,0,.08)}
.setup-banner h2{margin:0 0 6px;font-size:22px}.setup-banner p{margin:0;color:#dff3ff;font-weight:700}.setup-banner a{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#fff;color:#0077b6;text-decoration:none;border-radius:12px;padding:12px 15px;font-weight:900;white-space:nowrap}
.setup-banner i{font-size:20px}.setup-banner .spark{font-size:38px;opacity:.9}
.premium-callout{background:linear-gradient(135deg,#17314f,#0077b6);color:#fff;border-radius:16px;padding:20px;margin-bottom:0;display:flex;justify-content:space-between;align-items:center;gap:18px;box-shadow:0 8px 26px rgba(0,0,0,.08);position:relative;overflow:hidden}
.premium-callout::after{content:"";position:absolute;right:-28px;top:-34px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.12)}
.premium-callout h2{margin:0 0 6px;font-size:22px}.premium-callout p{margin:0;color:#dff3ff;font-weight:700}.premium-callout strong{color:#9ff0bd}.premium-callout a{display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#fff;color:#0077b6;text-decoration:none;border-radius:12px;padding:12px 15px;font-weight:900;white-space:nowrap;position:relative;z-index:1}
.premium-callout i{font-size:20px}.premium-callout .spark{font-size:38px;opacity:.9;position:relative;z-index:1}
.kpi-grid{display:grid;grid-template-columns:1.35fr repeat(3,1fr);gap:16px;margin-bottom:18px}
.tile{background:#fff;border-radius:14px;padding:20px;box-shadow:0 8px 26px rgba(0,0,0,.08)}
.health{display:flex;align-items:center;gap:24px}
.health-ring{width:136px;height:136px;border-radius:50%;background:conic-gradient(#2fac66 calc(var(--score)*1%),#e8edf3 0);display:flex;align-items:center;justify-content:center;flex:none;position:relative}
.health-ring::before{content:"";width:104px;height:104px;background:#fff;border-radius:50%;position:absolute}
.health-ring i{position:relative;font-size:38px;color:#2fac66}
.health h3,.kpi h3,.panel-title h2{margin:0;color:#0065a8;font-size:18px}
.health strong{display:block;font-size:28px;color:#159447;margin-bottom:4px}.health p{margin:0;color:#52637a;line-height:1.35}
.kpi{position:relative;min-height:150px}.kpi .icon{position:absolute;right:18px;top:18px;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:21px}
.icon.verde{background:#c9f0d8;color:#159447}.icon.vermelho{background:#ffd5d8;color:#c92f37}.icon.azul{background:#cfeeff;color:#0077b6}.icon.laranja{background:#ffddb8;color:#e87500}
.kpi .value{display:block;margin-top:34px;font-size:25px;font-weight:900}.kpi small{display:block;margin-top:9px;color:#52637a;font-weight:700}.trend{margin-top:18px;font-size:13px;font-weight:900}
.trend.verde{color:#159447}.trend.vermelho{color:#c92f37}.trend.azul{color:#0077b6}.trend.laranja{color:#e87500}
.layout{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px;align-items:stretch}
.panel{background:#fff;border-radius:14px;padding:18px;box-shadow:0 8px 26px rgba(0,0,0,.08)}
.layout>.panel,.quick-panels>.panel{height:100%}
.panel-title{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:16px}
.panel-title span,.panel-link{font-size:13px;color:#667085;text-decoration:none;font-weight:800}
.panel-title select{padding:8px 10px;border:1px solid #d9e2ec;border-radius:9px;background:#fff;color:#17314f;font-weight:800}
.insights{display:grid;gap:14px}.insight{display:grid;grid-template-columns:42px 1fr;gap:12px;align-items:center;color:#52637a;font-weight:700}
.bubble{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center}
.bubble.verde{background:#d8f4e2;color:#159447}.bubble.azul{background:#d8ecff;color:#0077b6}.bubble.laranja{background:#ffe4c2;color:#e87500}.bubble.roxo{background:#eadbff;color:#7d47c7}.bubble.vermelho{background:#ffd5d8;color:#c92f37}
.due-list{display:grid;gap:0}.due{display:grid;grid-template-columns:62px 1fr auto;gap:14px;align-items:center;padding:12px 0;border-bottom:1px solid #edf1f5}
.due-date{border-radius:10px;background:#ffe3e6;color:#cf2631;text-align:center;font-weight:900;padding:9px}.due-date span{display:block;font-size:13px}.due h4{margin:0;color:#17314f}.due p{margin:3px 0 0;color:#667085}.due strong{display:block;text-align:right}.due small{display:block;color:#0077b6;text-align:right;font-weight:900}
.quick-panels{display:grid;grid-template-columns:1fr;gap:18px;margin-bottom:18px;align-items:stretch}.quick-panels>.panel:first-child{display:none}.quick-box{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.quick-col{border-top:4px solid;border-radius:12px;padding:12px;background:#f8fbfd}.quick-col.receber{border-color:#2fac66}.quick-col.pagar{border-color:#df3a41}.quick-col.gasto{border-color:#0077b6}
.quick-col h3{margin:0 0 10px;font-size:15px}.quick-item{display:flex;align-items:center;justify-content:space-between;background:#fff;border:1px solid #e2e9f1;border-radius:9px;padding:11px;text-decoration:none;color:#17314f;font-weight:800;margin-bottom:8px}
.goal h3{margin:0}.goal-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin:18px 0}.goal-grid span{color:#667085;font-weight:800}.goal-grid strong{display:block;font-size:23px;margin-top:6px}.progress-text{font-size:27px;color:#159447;font-weight:900}.progress{height:12px;background:#e8edf3;border-radius:99px;overflow:hidden;margin:8px 0 18px}.progress span{display:block;height:100%;background:#2fac66;border-radius:99px}
.goal-summary{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;color:#52637a;font-weight:800}.goal-summary strong{color:#159447;font-size:20px}.goal-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px}.goal-card{border:1px solid #e2e9f1;border-radius:12px;background:#f8fbfd;padding:12px;min-width:0}.goal-card h3{color:#17314f;font-size:15px;margin:0 0 4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.goal-card p{margin:0;color:#667085;font-size:12px;min-height:30px;line-height:1.3}.goal-card-meta{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:10px 0}.goal-card-meta span{display:block;color:#667085;font-size:11px;font-weight:900}.goal-card-meta strong{display:block;color:#0077b6;font-size:14px;margin-top:2px}.goal-card-footer{display:flex;justify-content:space-between;gap:10px;color:#667085;font-size:12px;font-weight:800}.goal-empty{display:flex;align-items:center;justify-content:space-between;gap:14px;border:1px dashed #9fc9e2;background:#f8fcff;border-radius:12px;padding:14px;color:#52637a;min-height:58px}.goal-empty a{display:inline-flex;align-items:center;gap:8px;background:#0077b6;color:#fff;text-decoration:none;border-radius:10px;padding:10px 12px;font-weight:900;white-space:nowrap}
.category-section{grid-template-columns:1fr}.category-section>.panel:nth-child(2){display:none}.category-section .category-wrap{display:grid;grid-template-columns:1fr;gap:18px;align-items:center}.category-wrap{display:grid;grid-template-columns:250px 1fr;gap:18px;align-items:center}.chart-wrap{height:260px}.category-section .chart-wrap{height:300px}.cat-list{display:grid;gap:10px}.cat-row{display:grid;grid-template-columns:12px 1fr auto auto;gap:12px;align-items:center;color:#52637a;font-weight:800}.dot{width:10px;height:10px;border-radius:50%}
.achievements{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;text-align:center}.achievement i{width:68px;height:68px;border-radius:18px;display:inline-flex;align-items:center;justify-content:center;font-size:29px;margin-bottom:10px}.achievement.verde i{background:#c9f0d8;color:#159447}.achievement.azul i{background:#d8ecff;color:#0077b6}.achievement.laranja i{background:#ffddb8;color:#e87500}.achievement.roxo i{background:#eadbff;color:#7d47c7}.achievement strong{display:block}.achievement span{color:#667085;font-size:13px}
.empty{padding:25px;text-align:center;color:#667085}.money-green{color:#159447}.money-red{color:#c92f37}.money-blue{color:#0077b6}.money-orange{color:#e87500}
@media(min-width:1101px){
body{height:100vh;min-height:100vh;overflow:hidden}
.sidebar{height:100vh;padding:18px 16px}
.brand{margin-bottom:18px;font-size:21px}
.sidebar a{padding:9px 12px;margin-bottom:4px;border-radius:8px;font-size:14px}
.support-card{padding:10px;border-radius:10px;font-size:11px}.support-card i{font-size:18px}.logout-link{margin-top:6px}
.main{height:100vh;overflow:auto;padding:14px 18px;display:flex;flex-direction:column;gap:10px}
.topbar{margin-bottom:0;align-items:center}.title h1{font-size:24px}.title p{font-size:13px;margin-top:2px}.user-box{padding:9px 12px;border-radius:10px;font-size:13px}
.filters-panel{margin-bottom:0;padding:9px 12px;border-radius:12px;gap:8px}.filters-panel label{font-size:11px;margin-bottom:3px}.filters-panel input,.filters-panel select{min-height:34px;padding:7px 9px;font-size:12px}.filters-panel button,.filters-panel a{min-height:34px;padding:7px 11px;font-size:12px}
.dashboard-notices{gap:10px;margin-bottom:0}.setup-banner,.premium-callout{padding:10px 14px;border-radius:12px}.setup-banner h2,.premium-callout h2{font-size:16px;margin-bottom:2px}.setup-banner p,.premium-callout p{font-size:12px}.setup-banner a,.premium-callout a{padding:8px 11px;border-radius:9px;font-size:12px}.setup-banner .spark,.premium-callout .spark{font-size:24px}
.kpi-grid{gap:10px;margin-bottom:0}.tile{padding:12px;border-radius:12px}.health{gap:12px}.health-ring{width:86px;height:86px}.health-ring::before{width:66px;height:66px}.health-ring i{font-size:25px}.health h3,.kpi h3,.panel-title h2{font-size:15px}.health strong{font-size:21px;margin-bottom:1px}.health p{font-size:12px;line-height:1.25}
.kpi{min-height:98px}.kpi .icon{width:34px;height:34px;right:12px;top:12px;border-radius:9px;font-size:16px}.kpi .value{margin-top:22px;font-size:20px}.kpi small{margin-top:4px;font-size:12px}.trend{margin-top:8px;font-size:11px}
.layout,.quick-panels{gap:10px;margin-bottom:0;min-height:0}.quick-panels{flex:0 0 auto}.quick-panels>.goal{min-height:118px;overflow:visible}.panel{padding:12px;border-radius:12px;min-height:0;overflow:hidden}.panel-title{margin-bottom:8px}.panel-title span,.panel-link{font-size:11px}
.insights{gap:8px}.insight{grid-template-columns:30px 1fr;gap:9px;font-size:12px}.bubble{width:30px;height:30px}
.due{grid-template-columns:48px 1fr auto;gap:10px;padding:6px 0}.due-date{padding:6px;border-radius:8px}.due-date span,.due p,.due small{font-size:11px}.due h4,.due strong{font-size:13px}
.quick-box{gap:10px}.quick-col{padding:9px;border-radius:10px}.quick-col h3{font-size:12px;margin-bottom:7px}.quick-item{padding:8px;border-radius:8px;margin-bottom:6px;font-size:12px}
.goal-grid{gap:12px;margin:8px 0}.goal-grid span,.goal p{font-size:12px}.goal-grid strong{font-size:18px;margin-top:2px}.progress-text{font-size:21px}.progress{height:9px;margin:5px 0 8px}.goal-summary{margin-bottom:8px;font-size:12px}.goal-summary strong{font-size:17px}.goal-cards{grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;max-height:154px;overflow:auto;padding-right:2px}.goal-card{padding:9px;border-radius:10px}.goal-card h3{font-size:13px}.goal-card p{font-size:11px;min-height:26px}.goal-card-meta{gap:6px;margin:7px 0}.goal-card-meta strong{font-size:12px}.goal-card-footer{font-size:11px}.goal-empty{padding:12px;font-size:12px}.goal-empty a{padding:8px 10px;font-size:12px;border-radius:8px}
.category-section{flex:0 0 auto;min-height:0}.category-section>.panel{height:auto}.category-section .category-wrap{grid-template-columns:minmax(0,1fr) 330px;gap:14px;align-items:center}.category-section .chart-wrap{height:220px}.cat-list{gap:6px}.cat-row{gap:8px;font-size:12px}.achievements{gap:12px}.achievement i{width:46px;height:46px;border-radius:12px;font-size:20px;margin-bottom:6px}.achievement strong{font-size:12px}.achievement span{font-size:11px}.empty{padding:12px;font-size:12px}
}
@media(min-width:1101px) and (max-height:820px){
.main{padding:10px 14px;gap:8px}.setup-banner,.premium-callout{padding:7px 10px}.setup-banner h2,.premium-callout h2{font-size:14px}.setup-banner p,.premium-callout p{font-size:11px}.setup-banner a,.premium-callout a{padding:6px 9px}.setup-banner .spark,.premium-callout .spark{font-size:18px}.kpi-grid{grid-template-columns:1.15fr repeat(3,1fr)}.kpi{min-height:86px}.tile{padding:10px}.health-ring{width:72px;height:72px}.health-ring::before{width:54px;height:54px}.health strong{font-size:18px}.kpi .value{font-size:18px}.layout,.quick-panels{gap:8px}.panel{padding:10px}.chart-wrap{height:120px}.quick-item{padding:6px;margin-bottom:5px}.achievement i{width:38px;height:38px;font-size:17px}
}
@media(max-width:1100px){.dashboard-notices,.kpi-grid{grid-template-columns:1fr 1fr}.health{grid-column:1/3}.layout,.quick-panels{grid-template-columns:1fr}.category-section .category-wrap{grid-template-columns:1fr}}
@media(max-width:760px){body{display:block}.sidebar{width:100%;min-height:auto}.main{padding:18px}.topbar,.setup-banner,.premium-callout,.goal-empty{flex-direction:column;align-items:flex-start}.setup-banner a,.premium-callout a,.goal-empty a{width:100%;justify-content:center}.dashboard-notices,.kpi-grid,.quick-box,.achievements,.goal-cards{grid-template-columns:1fr}.health{grid-column:auto;flex-direction:column;align-items:flex-start}.goal-summary{align-items:flex-start;flex-direction:column}.app-dashboard-filter-button{display:flex!important;width:100%;min-height:48px;align-items:center;justify-content:center;gap:8px;margin:8px 0 12px;border:1px solid #cfe0eb;border-radius:14px;background:#fff;color:#0077b6;font-weight:900;cursor:pointer}.filters-panel{display:none!important}.filters-panel.is-open,.app-dashboard-filter-toggle:checked + .app-dashboard-filter-button + .filters-panel{display:flex!important}}
</style>
<link rel="stylesheet" href="responsive.css?v=menu-cinco-20260917">
<style>
  .app-home-hub{background:rgba(18,47,73,.92);border:1px solid rgba(40,180,235,.24);border-radius:16px;padding:18px;box-shadow:0 16px 36px rgba(0,0,0,.18)}
  .app-hub-heading{display:flex;flex-direction:column;gap:3px;margin-bottom:14px}
  .app-hub-heading span{color:#22c7f2;font-size:.78rem;font-weight:900;text-transform:uppercase}
  .app-hub-heading strong{color:#fff;font-size:1.25rem;line-height:1.15}
  .app-hub-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
  .app-hub-card{min-height:92px;display:flex;align-items:center;gap:14px;padding:16px;color:#fff;text-decoration:none;background:linear-gradient(135deg,rgba(15,43,68,.98),rgba(13,36,59,.98));border:1px solid rgba(92,197,244,.18);border-radius:14px}
  .app-hub-card strong,.app-hub-card small{display:block}
  .app-hub-card strong{font-size:1rem;line-height:1.15}
  .app-hub-card small{margin-top:4px;color:#b7c7d8;font-size:.78rem;line-height:1.2}
  .app-hub-icon{width:42px;height:42px;flex:0 0 42px;display:inline-flex;align-items:center;justify-content:center;border-radius:14px;color:#fff;background:#149dd6}
  .hub-receitas .app-hub-icon{background:#16a34a}.hub-despesas .app-hub-icon{background:#dc2626}.hub-treinos .app-hub-icon{background:#7c3aed}.hub-guias .app-hub-icon{background:#0891b2}.hub-relatorios .app-hub-icon{background:#0284c7}.hub-extrato .app-hub-icon{background:#f59e0b}
  @media(max-width:768px){.app-home-hub{padding:12px;border-radius:14px}.app-hub-heading{margin-bottom:10px}.app-hub-heading strong{font-size:1rem}.app-hub-grid{gap:8px}.app-hub-card{min-height:72px;gap:9px;padding:11px;border-radius:12px}.app-hub-icon{width:34px;height:34px;flex-basis:34px;border-radius:11px}.app-hub-card strong{font-size:.84rem}.app-hub-card small{font-size:.66rem}}
</style>
<?php echo function_exists("fincontrol_version_styles") ? fincontrol_version_styles() : ""; ?>
</head>

<body class="dashboard-app">

<aside class="sidebar" id="menu-completo">
    <div class="brand">
        <button type="button" class="brand-menu-button" data-fc-open-menu aria-label="Menu principal" title="Menu principal">
            <i class="fa-solid fa-bars"></i>
        </button>
        <span>FinControle</span>
    </div>

        <a href="03.menu.php" class="active"><i class="fa fa-gauge"></i> Dashboard</a>
    <a href="05.receitas.php"><i class="fa fa-plus-circle"></i> Receitas</a>
    <a href="04.despesas.php"><i class="fa fa-minus-circle"></i> Despesas</a>
    <a href="07.relatorios.php"><i class="fa fa-chart-line"></i> Relatórios</a>
    
    <a href="30.treinos.php"><i class="fa fa-dumbbell"></i> Treinos</a>
    <a class="mobile-menu-only" href="#" data-fc-open-mobile-menu aria-label="Menu"><i class="fa fa-bars"></i> Menu</a>


    <a class="logout-link" href="15.logout.php"><i class="fa fa-sign-out"></i> Sair</a>
</aside>

<div class="fc-mobile-menu-backdrop" data-fc-mobile-menu hidden>
    <nav class="fc-mobile-menu-panel" aria-label="Menu completo">
        <div class="fc-mobile-menu-head">
            <strong>Menu</strong>
            <button type="button" data-fc-close-mobile-menu aria-label="Fechar menu"><i class="fa fa-xmark"></i></button>
        </div>
        <a href="03.menu.php"><i class="fa fa-house"></i> Início</a>
        <a href="04.despesas.php"><i class="fa fa-minus-circle"></i> Despesas</a>
        <a href="05.receitas.php"><i class="fa fa-plus-circle"></i> Receitas</a>
        
        <a href="07.relatorios.php"><i class="fa fa-chart-line"></i> Relatórios</a>
        <a href="29.minhas_noticias.php"><i class="fa fa-newspaper"></i> Minhas notícias</a>
        <a href="30.treinos.php"><i class="fa fa-dumbbell"></i> Treinos</a>
        <a href="15.logout.php"><i class="fa fa-sign-out"></i> Sair</a>
    </nav>
</div>

<main class="main">

    <div class="topbar app-dashboard-header">
        <div class="app-brand-heading"><i class="fa-solid fa-wallet"></i><span>FinControle</span></div>
        <a class="app-news-shortcut" href="29.minhas_noticias.php" aria-label="Minhas notícias" title="Minhas notícias">
            <i class="fa-solid fa-newspaper"></i>
        </a>
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

    <input class="app-dashboard-filter-toggle" type="checkbox" id="dashboardFiltroToggle">
    <label class="app-filter-button app-dashboard-filter-button" for="dashboardFiltroToggle" data-dashboard-filter-open>
        <i class="fa-solid fa-sliders"></i>
        Filtros
    </label>

    <form class="filters-panel" method="GET" action="03.menu.php">
        <div>
            <label for="dataIni">Data inicial</label>
            <input type="date" id="dataIni" name="dataIni" value="<?php echo htmlspecialchars($dataIni); ?>">
        </div>

        <div>
            <label for="dataFim">Data final</label>
            <input type="date" id="dataFim" name="dataFim" value="<?php echo htmlspecialchars($dataFim); ?>">
        </div>

        <div>
            <label for="filtroMes">Mês</label>
            <select id="filtroMes" name="filtroMes">
                <option value="">Todos</option>
                <?php for ($i = 1; $i <= 12; $i++) { $m = str_pad($i, 2, "0", STR_PAD_LEFT); ?>
                    <option value="<?php echo $m; ?>" <?php echo $filtroMes === $m ? "selected" : ""; ?>><?php echo $i; ?></option>
                <?php } ?>
            </select>
        </div>

        <div>
            <label for="filtroAno">Ano</label>
            <select id="filtroAno" name="filtroAno">
                <option value="">Todos</option>
                <?php for ($a = date("Y") - 2; $a <= date("Y") + 5; $a++) { ?>
                    <option value="<?php echo $a; ?>" <?php echo $filtroAno == $a ? "selected" : ""; ?>><?php echo $a; ?></option>
                <?php } ?>
            </select>
        </div>

        <div>
            <label for="buscaDespesa">Busca</label>
            <input type="text" id="buscaDespesa" name="buscaDespesa" value="<?php echo htmlspecialchars($buscaDespesa); ?>" placeholder="Pesquisar saídas...">
        </div>

        <button type="submit">
            <i class="fa-solid fa-filter"></i>
            Filtrar
        </button>

        <a href="03.menu.php">
            <i class="fa-solid fa-rotate-left"></i>
            Limpar
        </a>
    </form>

    <section class="app-home-hub" aria-label="Menu principal do FinControle">
        <div class="app-hub-heading">
            <span>Menu principal</span>
            <strong>Escolha o que quer organizar agora</strong>
        </div>

        <div class="app-hub-grid">
            <a class="app-hub-card hub-receitas" href="05.receitas.php">
                <span class="app-hub-icon"><i class="fa-solid fa-plus"></i></span>
                <span>
                    <strong>Receitas</strong>
                    <small>Entradas e ganhos</small>
                </span>
            </a>
            <a class="app-hub-card hub-despesas" href="04.despesas.php">
                <span class="app-hub-icon"><i class="fa-solid fa-minus"></i></span>
                <span>
                    <strong>Despesas</strong>
                    <small>Custos fixos e vari&aacute;veis</small>
                </span>
            </a>
            <a class="app-hub-card hub-treinos" href="30.treinos.php">
                <span class="app-hub-icon"><i class="fa-solid fa-dumbbell"></i></span>
                <span>
                    <strong>Treinos</strong>
                    <small>Rotina da academia</small>
                </span>
            </a>
            <a class="app-hub-card hub-guias" href="27.guias.php">
                <span class="app-hub-icon"><i class="fa-regular fa-newspaper"></i></span>
                <span>
                    <strong>Guias</strong>
                    <small>Conte&uacute;dos para decidir melhor</small>
                </span>
            </a>
            <a class="app-hub-card hub-relatorios" href="07.relatorios.php">
                <span class="app-hub-icon"><i class="fa-solid fa-chart-line"></i></span>
                <span>
                    <strong>Relat&oacute;rios</strong>
                    <small>Resumo do seu m&ecirc;s</small>
                </span>
            </a>
            
        </div>
    </section>

    <section class="dashboard-notices">
        <div class="premium-callout">
            <div>
                <?php if ($premium_ativo_dashboard) { ?>
                    <h2>Premium ativo</h2>
                    <p>Seu plano está liberado. Continue usando os recursos avançados do FinControle.</p>
                <?php } else { ?>
                    <h2>Plano Premium</h2>
                    <p>Desbloqueie a assinatura mensal por <strong>R$ 14,90</strong> com pagamento online seguro.</p>
                <?php } ?>
            </div>
            <a href="03.menu.php">
                <i class="fa-solid fa-crown"></i>
                <?php echo $premium_ativo_dashboard ? "Ver plano" : "Assinar agora"; ?>
            </a>
            <i class="fa-solid fa-crown spark"></i>
        </div>
    </section>

    <section class="kpi-grid">
        <?php if ($premium_ativo_dashboard) { ?>
        <div class="tile health">
            <div class="health-ring" style="--score:<?php echo $scoreFinanceiroDashboard; ?>">
                <i class="fa-solid fa-heart"></i>
            </div>
            <div>
                <h3>Saúde Financeira</h3>
                <strong><?php echo htmlspecialchars($statusSaudeDashboard); ?></strong>
                <p>Pontuação: <?php echo $scoreFinanceiroDashboard; ?>/100</p>
                <p><?php echo htmlspecialchars($textoSaudeDashboard); ?></p>
            </div>
        </div>
        <?php } else { ?>
        <div class="tile health">
            <div class="health-ring" style="--score:0">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div>
                <h3>Score Financeiro</h3>
                <strong>Premium</strong>
                <p>Desbloqueie score, saúde financeira, meta de economia, conquistas e insights automáticos.</p>
            </div>
        </div>
        <?php } ?>
        <div class="tile kpi app-receitas">
            <h3>Receitas</h3>
            <div class="icon verde"><i class="fa-solid fa-arrow-trend-up"></i></div>
            <span class="value money-green"><?php echo moedaDashboard($totalReceitas); ?></span>
            <small>Total no filtro</small>
            <div class="trend verde"><i class="fa-solid fa-arrow-up"></i> <?php echo number_format(abs($variacaoReceitas), 1, ",", "."); ?>% vs mês anterior</div>
        </div>

        <div class="tile kpi app-despesas">
            <h3>Despesas</h3>
            <div class="icon vermelho"><i class="fa-solid fa-arrow-trend-down"></i></div>
            <span class="value money-red"><?php echo moedaDashboard($totalSaidas); ?></span>
            <small>Total no filtro</small>
            <div class="trend vermelho"><i class="fa-solid fa-arrow-<?php echo $variacaoSaidas <= 0 ? "down" : "up"; ?>"></i> <?php echo number_format(abs($variacaoSaidas), 1, ",", "."); ?>% vs mês anterior</div>
        </div>

        <div class="tile kpi app-saldo">
            <h3>Saldo</h3>
            <div class="icon azul"><i class="fa-solid fa-piggy-bank"></i></div>
            <span class="value money-blue"><?php echo moedaDashboard($saldo); ?></span>
            <small>Saldo no filtro</small>
            <div class="trend azul"><i class="fa-solid fa-percent"></i> <?php echo number_format($economiaPercentual, 1, ",", "."); ?>% taxa de sobra</div>
        </div>

    </section>

    <section class="layout app-main-panels">
        <div class="panel">
            <div class="panel-title">
                <h2><i class="fa-regular fa-lightbulb"></i> Insights do mês</h2>
                <span><?php echo $premium_ativo_dashboard ? "Leitura automática" : "Premium"; ?></span>
            </div>
            <?php if (!$premium_ativo_dashboard) { ?>
                <div class="empty"><i class="fa-solid fa-lock"></i> Insights automáticos e consultor financeiro IA ficam no Premium.</div>
            <?php } else { ?>
            <div class="insights">
                <?php foreach ($insightsDashboard as $insight) { ?>
                    <div class="insight">
                        <span class="bubble <?php echo $insight["classe"]; ?>"><i class="fa-solid <?php echo $insight["icone"]; ?>"></i></span>
                        <span><?php echo htmlspecialchars($insight["texto"]); ?></span>
                    </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <div class="panel">
            <?php if (!$premium_ativo_dashboard) { ?>
            <div class="panel-title">
                <h2><i class="fa-solid fa-lock"></i> Próximos vencimentos</h2>
                <span>Premium</span>
            </div>
            <div class="empty">Agenda de vencimentos, lembretes e contas a vencer são recursos Premium.</div>
            <?php } else { ?>
            <div class="panel-title">
                <h2><i class="fa-regular fa-calendar-days"></i> Próximos vencimentos</h2>
                <a class="panel-link" href="04.despesas.php">Ver todos</a>
            </div>
            <div class="due-list">
                <?php if (!count($proximosVencimentos)) { ?>
                    <div class="empty">Nenhum vencimento em aberto.</div>
                <?php } ?>
                <?php foreach ($proximosVencimentos as $vencimento) {
                    $dias = max(0, floor((strtotime($vencimento["data"]) - strtotime(date("Y-m-d"))) / 86400));
                ?>
                    <div class="due">
                        <div class="due-date"><?php echo date("d", strtotime($vencimento["data"])); ?><span><?php echo strtoupper(date("M", strtotime($vencimento["data"]))); ?></span></div>
                        <div class="fc-sensitive-description"><h4><?php echo htmlspecialchars($vencimento["descricao"]); ?></h4><p><?php echo htmlspecialchars($vencimento["categoria"] ?? "Despesa"); ?></p></div>
                        <div><strong class="fc-sensitive-value"><?php echo moedaDashboard($vencimento["valor"]); ?></strong><small class="fc-sensitive-meta"><?php echo $dias === 0 ? "Hoje" : "Em " . $dias . " dias"; ?></small></div>
                    </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </section>

    <section class="quick-panels">
        <div class="panel">
            <div class="panel-title">
                <h2>Ações rápidas</h2>
            </div>
            <div class="quick-box">
                <div class="quick-col receber">
                    <h3><i class="fa-solid fa-money-bill-wave"></i> Receber dinheiro</h3>
                    <a class="quick-item" href="05.receitas.php">Cadastrar salário <i class="fa-solid fa-arrow-right"></i></a>
                    <a class="quick-item" href="05.receitas.php">Cadastrar venda <i class="fa-solid fa-arrow-right"></i></a>
                    <a class="quick-item" href="05.receitas.php">Renda extra <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div class="quick-col pagar">
                    <h3><i class="fa-regular fa-file-lines"></i> Registrar despesa</h3>
                    <a class="quick-item" href="04.despesas.php">Conta fixa <i class="fa-solid fa-arrow-right"></i></a>
                    <a class="quick-item" href="04.despesas.php">Cartão <i class="fa-solid fa-arrow-right"></i></a>
                    <a class="quick-item" href="04.despesas.php">Parcela <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                
            </div>
        </div>

        <div class="panel goal">
            <?php if (!$premium_ativo_dashboard) { ?>
            <div class="panel-title">
                <h2><i class="fa-solid fa-lock"></i> Meta de economia</h2>
                <span>Premium</span>
            </div>
            <div class="empty">Metas de viagem, carro, casa e reserva de emergência são recursos Premium.</div>
            <?php } else { ?>
            <div class="panel-title">
                <h2><i class="fa-solid fa-bullseye"></i> Meta de economia</h2>
                <a class="panel-link" href="03.menu.php">Ver metas</a>
            </div>
            <?php if (!count($caixinhasDashboard)) { ?>
                <div class="goal-empty">
                    <span>Nenhuma caixinha criada ainda. Crie uma meta e acompanhe o realizado aqui no dashboard.</span>
                    <a href="03.menu.php"><i class="fa-solid fa-plus-circle"></i> Criar meta</a>
                </div>
            <?php } else { ?>
                <div class="goal-summary">
                    <span><?php echo count($caixinhasDashboard); ?> caixinha(s) ativa(s)</span>
                    <strong><?php echo number_format($progressoEconomiaDashboard, 0, ",", "."); ?>% do plano concluido</strong>
                    <span><?php echo moedaDashboard($totalRealizadoEconomiaDashboard); ?> de <?php echo moedaDashboard($totalMetaEconomiaDashboard); ?></span>
                </div>
                <div class="goal-cards">
                    <?php foreach ($caixinhasDashboard as $caixinhaDashboard) {
                        $progressoCaixinhaDashboard = progressoCaixinhaDashboard($caixinhaDashboard["meta"], $caixinhaDashboard["realizado"]);
                        $faltanteCaixinhaDashboard = max(0, floatval($caixinhaDashboard["meta"]) - floatval($caixinhaDashboard["realizado"]));
                    ?>
                        <div class="goal-card">
                            <h3><?php echo htmlspecialchars($caixinhaDashboard["nome"]); ?></h3>
                            <p><?php echo htmlspecialchars($caixinhaDashboard["objetivo"] ?: "Sem objetivo descrito."); ?></p>
                            <div class="goal-card-meta">
                                <div><span>Meta</span><strong><?php echo moedaDashboard($caixinhaDashboard["meta"]); ?></strong></div>
                                <div><span>Realizado</span><strong class="money-green"><?php echo moedaDashboard($caixinhaDashboard["realizado"]); ?></strong></div>
                            </div>
                            <div class="progress"><span style="width:<?php echo $progressoCaixinhaDashboard; ?>%"></span></div>
                            <div class="goal-card-footer">
                                <span><?php echo number_format($progressoCaixinhaDashboard, 0, ",", "."); ?>%</span>
                                <span>Faltam <?php echo moedaDashboard($faltanteCaixinhaDashboard); ?></span>
                                <span><?php echo dataCaixinhaDashboard($caixinhaDashboard["data_meta"]); ?></span>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php } ?>
        </div>    </section>

    <section class="layout category-section">
        <div class="panel">
            <div class="panel-title">
                <h2>Categorias que mais consomem (Top 5)</h2>
                <a class="panel-link" href="07.relatorios.php">Ver relatório completo</a>
            </div>
            <div class="category-wrap">
                <div class="chart-wrap"><canvas id="grafCategorias"></canvas></div>
                <div class="cat-list">
                    <?php
                    $coresCategorias = ["#063b5c", "#075985", "#087db2", "#0b6fa4", "#0e5f88"];
                    $indiceCor = 0;
                    foreach ($topCategoriasDashboard as $categoria => $valor) {
                        $pct = $totalSaidas > 0 ? ($valor / $totalSaidas) * 100 : 0;
                        $cor = $coresCategorias[$indiceCor % count($coresCategorias)];
                        $indiceCor++;
                    ?>
                        <div class="cat-row">
                            <span class="dot" style="background:<?php echo $cor; ?>"></span>
                            <span><?php echo htmlspecialchars($categoria); ?></span>
                            <span><?php echo number_format($pct, 0, ",", "."); ?>%</span>
                            <span><?php echo moedaDashboard($valor); ?></span>
                        </div>
                    <?php } ?>
                    <?php if (!count($topCategoriasDashboard)) { ?><div class="empty">Sem categorias no período.</div><?php } ?>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-title">
                <h2><i class="fa-solid fa-trophy"></i> Conquistas</h2>
                <span>Indicadores do período</span>
            </div>
            <div class="achievements">
                <?php foreach ($conquistasDashboard as $conquista) { ?>
                    <div class="achievement <?php echo $conquista["classe"]; ?>">
                        <i class="fa-solid <?php echo $conquista["icone"]; ?>"></i>
                        <strong><?php echo htmlspecialchars($conquista["titulo"]); ?></strong>
                        <span><?php echo htmlspecialchars($conquista["texto"]); ?></span>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>

</main>

<script>
if (window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true) {
    const dashboardUrl = new URL(window.location.href);
    const possuiFiltro = ["dataIni", "dataFim", "filtroMes", "filtroAno"].some(function (campo) {
        return dashboardUrl.searchParams.has(campo);
    });

    if (!possuiFiltro) {
        const hoje = new Date();
        dashboardUrl.searchParams.set("filtroMes", String(hoje.getMonth() + 1).padStart(2, "0"));
        dashboardUrl.searchParams.set("filtroAno", String(hoje.getFullYear()));
        window.location.replace(dashboardUrl.toString());
    }
}

const labelsCategoriasDashboard = <?php echo json_encode($labelsCategoriasDashboard, JSON_UNESCAPED_UNICODE); ?>;
const valoresCategoriasDashboard = <?php echo json_encode($valoresCategoriasDashboard, JSON_UNESCAPED_UNICODE); ?>;
const coresCategoriasDashboard = ["#063b5c", "#075985", "#087db2", "#0b6fa4", "#0e5f88"];

new Chart(document.getElementById("grafCategorias"), {
    type: "bar",
    data: {
        labels: labelsCategoriasDashboard.length ? labelsCategoriasDashboard : ["Sem dados"],
        datasets: [{
            label: "Saídas por categoria",
            data: valoresCategoriasDashboard.length ? valoresCategoriasDashboard : [1],
            backgroundColor: coresCategoriasDashboard,
            borderWidth: 0,
            borderRadius: 8,
            maxBarThickness: 72
        }]
    },
    options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            legend: {display: false},
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return "R$ " + Number(context.raw || 0).toLocaleString("pt-BR", {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            }
        },
        scales: {
            x: {grid: {display: false}},
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return "R$ " + Number(value || 0).toLocaleString("pt-BR");
                    }
                }
            }
        }
    }
});

(function () {
    const menu = document.querySelector("[data-fc-mobile-menu]");
    const openButtons = document.querySelectorAll("[data-fc-open-mobile-menu]");
    const closeButtons = document.querySelectorAll("[data-fc-close-mobile-menu]");

    if (!menu || !openButtons.length) {
        return;
    }

    function openMenu(event) {
        event.preventDefault();
        menu.hidden = false;
    }

    function closeMenu() {
        menu.hidden = true;
    }

    openButtons.forEach(function (button) {
        button.addEventListener("click", openMenu);
    });

    closeButtons.forEach(function (button) {
        button.addEventListener("click", closeMenu);
    });

    menu.addEventListener("click", function (event) {
        if (event.target === menu) {
            closeMenu();
        }
    });
})();

(function () {
    const toggle = document.querySelector("[data-dashboard-values-toggle]");
    if (!toggle) {
        return;
    }

    toggle.addEventListener("click", function () {
        const hidden = document.body.classList.toggle("fc-values-hidden");
        toggle.setAttribute("aria-label", hidden ? "Mostrar valores" : "Ocultar valores");
        const icon = toggle.querySelector("i");
        if (icon) {
            icon.className = hidden ? "fa-solid fa-eye" : "fa-solid fa-eye-slash";
        }
    });
})();

</script>

<?php echo function_exists("fincontrol_version_badge") ? fincontrol_version_badge() : ""; ?>
</body>
</html>
<?php
$dashboardConteudoGerado = ob_get_clean();
fincontrol_cache_salvar($dashboardCacheChave, $dashboardConteudoGerado);
echo $dashboardConteudoGerado;
?>




