<?php
require_once "00.sessao.php";
require_once "09.conexao.php";
require_once "00.crypto.php";
require_once "00.categorias.php";
require_once "00.despesas_status.php";
foreach (["00.orcamentos.php", "00.planos.php", "00.cache.php", "00.analytics.php"] as $arquivoOpcional) {
    if (is_file(__DIR__ . "/" . $arquivoOpcional)) {
        require_once $arquivoOpcional;
    }
}
if (!function_exists("fincontrol_plano_premium_ativo")) {
    function fincontrol_plano_premium_ativo($conexao, $id_usuario) { return true; }
}
if (!function_exists("fincontrol_limite_despesas_gratuito")) {
    function fincontrol_limite_despesas_gratuito() { return PHP_INT_MAX; }
}
if (!function_exists("fincontrol_cache_invalidar_usuario")) {
    function fincontrol_cache_invalidar_usuario($id_usuario) { return null; }
}
if (!function_exists("fincontrol_evento_registrar")) {
    function fincontrol_evento_registrar($conexao, $id_usuario, $evento, $dados = []) { return null; }
}

if (!isset($_SESSION["id_usuario"])) {
    header("Location: 02.login.php");
    exit();
}

$id_usuario = $_SESSION["id_usuario"];
$nome_usuario = $_SESSION["nome_usuario"] ?? "Usuário";
$mensagem = "";
$premium_ativo_despesas = fincontrol_plano_premium_ativo($conexao, $id_usuario);
$limite_despesas_gratuito = fincontrol_limite_despesas_gratuito();
$categorias_despesas = categorias_gastos_fincontrol();
fincontrol_validar_csrf();
if ($_SERVER["REQUEST_METHOD"] === "POST") fincontrol_cache_invalidar_usuario($id_usuario);
$dataIni = $_GET["dataIni"] ?? "";
$dataFim = $_GET["dataFim"] ?? "";
$filtroMes = $_GET["filtroMes"] ?? "";
$filtroAno = $_GET["filtroAno"] ?? "";
$buscaDespesa = trim($_GET["buscaDespesa"] ?? "");
$filtroOrigem = $_GET["filtroOrigem"] ?? "";
$periodoMes = $_GET["periodoMes"] ?? "";
$periodoAno = $_GET["periodoAno"] ?? "";

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataIni)) $dataIni = "";
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFim)) $dataFim = "";
if (!preg_match('/^\d{2}$/', $filtroMes)) $filtroMes = "";
if (!preg_match('/^\d{4}$/', $filtroAno)) $filtroAno = "";
if (!preg_match('/^\d{2}$/', $periodoMes)) $periodoMes = "";
if (!preg_match('/^\d{4}$/', $periodoAno)) $periodoAno = "";

$filtroAvancadoAtivo = $filtroOrigem === "avancado"
    || $dataIni !== ""
    || $dataFim !== ""
    || $buscaDespesa !== ""
    || array_key_exists("filtroMes", $_GET)
    || array_key_exists("filtroAno", $_GET);

if (!$filtroAvancadoAtivo) {
    if ($periodoMes === "") $periodoMes = date("m");
    if ($periodoAno === "") $periodoAno = date("Y");
    $filtroMes = $periodoMes;
    $filtroAno = $periodoAno;
}

if ($dataIni !== "" && $dataFim !== "" && $dataIni > $dataFim) {
    $tmp = $dataIni;
    $dataIni = $dataFim;
    $dataFim = $tmp;
}

function adicionarMes($data, $meses) {
    return date("Y-m-d", strtotime("+$meses month", strtotime($data)));
}

function garantir_tabela_cartoes_despesas_fincontrol($conexao) {
    $sql = "CREATE TABLE IF NOT EXISTS cartoes_credito (
        id_cartao int NOT NULL AUTO_INCREMENT,
        id_usuario int NOT NULL,
        nome_cartao varchar(100) NOT NULL,
        dia_vencimento tinyint NOT NULL,
        dia_fechamento tinyint NOT NULL,
        criado_em timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_cartao),
        KEY idx_cartoes_usuario (id_usuario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    return (bool) $conexao->query($sql);
}

function garantir_coluna_cartao_despesas_fincontrol($conexao) {
    $resultado = $conexao->query("SHOW COLUMNS FROM despesas LIKE 'id_cartao'");

    if ($resultado && $resultado->num_rows > 0) {
        return true;
    }

    return (bool) $conexao->query("ALTER TABLE despesas ADD id_cartao int DEFAULT NULL AFTER id_orcamento");
}

function dataComDiaFincontrol($ano, $mes, $dia) {
    $ultimoDia = cal_days_in_month(CAL_GREGORIAN, intval($mes), intval($ano));
    $diaSeguro = min(max(1, intval($dia)), $ultimoDia);
    return sprintf("%04d-%02d-%02d", intval($ano), intval($mes), $diaSeguro);
}

function dataVencimentoCartaoFincontrol($dataCompra, $diaVencimento, $diaFechamento) {
    $compra = new DateTime($dataCompra);
    $ano = intval($compra->format("Y"));
    $mes = intval($compra->format("m"));
    $fechamento = new DateTime(dataComDiaFincontrol($ano, $mes, $diaFechamento));
    if ($compra > $fechamento) {
        $fechamento->modify("first day of next month");
        $fechamento = new DateTime(dataComDiaFincontrol(intval($fechamento->format("Y")), intval($fechamento->format("m")), $diaFechamento));
    }
    $vencimento = new DateTime(dataComDiaFincontrol(intval($fechamento->format("Y")), intval($fechamento->format("m")), $diaVencimento));
    if (intval($diaVencimento) <= intval($diaFechamento)) $vencimento->modify("+1 month");
    return $vencimento->format("Y-m-d");
}

function bindParametrosDespesas($stmt, $tipos, &$params) {
    $refs = [];
    foreach ($params as $chave => &$valor) {
        $refs[$chave] = &$valor;
    }
    array_unshift($refs, $tipos);
    return call_user_func_array([$stmt, "bind_param"], $refs);
}

$cartoes_credito = [];
$stmtCartoes = $conexao->prepare("SELECT id_cartao, nome_cartao, dia_vencimento, dia_fechamento FROM cartoes_credito WHERE id_usuario = ? ORDER BY nome_cartao ASC");
if ($stmtCartoes) {
    $stmtCartoes->bind_param("i", $id_usuario);
    $stmtCartoes->execute();
    $resultadoCartoes = $stmtCartoes->get_result();
    while ($cartao = $resultadoCartoes->fetch_assoc()) {
        $cartoes_credito[] = $cartao;
    }
}

$limites_gastos = [];
$stmtLimites = $conexao->prepare("SELECT id_orcamento, categoria, mes_referencia FROM orcamentos_categoria WHERE id_usuario = ? ORDER BY mes_referencia DESC, categoria ASC");
if ($stmtLimites) {
    $stmtLimites->bind_param("i", $id_usuario);
    $stmtLimites->execute();
    $resultadoLimites = $stmtLimites->get_result();
    while ($limite = $resultadoLimites->fetch_assoc()) {
        $limites_gastos[] = $limite;
    }
}
/* ACOES EM MASSA E CATEGORIA */
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "") !== "cadastrar") {
    $acao = $_POST["acao"] ?? "";

    if ($acao === "excluir_linha") {
        $id_despesa = intval($_POST["id_despesa"] ?? 0);

        if ($id_despesa > 0) {
            $sql = "DELETE FROM despesas WHERE id_despesa = ? AND id_usuario = ?";
            $stmt = $conexao->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ii", $id_despesa, $id_usuario);
                $ok = $stmt->execute();
                $mensagem = $ok && $stmt->affected_rows === 1
                    ? "Despesa excluída com sucesso."
                    : "A despesa não foi encontrada ou já havia sido excluída.";
            } else {
                $mensagem = "Não foi possível excluir a despesa.";
            }
        } else {
            $mensagem = "Despesa inválida para exclusão.";
        }
    }

    if ($acao === "alterar_status_linha") {
        $id_despesa = intval($_POST["id_despesa"] ?? 0);
        $status = status_despesa_valido_fincontrol($_POST["status"] ?? "");

        if ($id_despesa > 0) {
            $sql = "UPDATE despesas SET status = ? WHERE id_despesa = ? AND id_usuario = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("sii", $status, $id_despesa, $id_usuario);
            $stmt->execute();
            $mensagem = "Status da despesa atualizado.";
        }
    }

    if ($acao === "alterar_categoria_linha") {
        $id_despesa = intval($_POST["id_despesa"] ?? 0);
        $categorias_linha = $_POST["categoria_linha"] ?? [];
        $categoria = categoria_fincontrol_valida($categorias_linha[$id_despesa] ?? "", $categorias_despesas);

        if ($id_despesa > 0 && $categoria !== "") {
            $sql = "UPDATE despesas SET categoria = ? WHERE id_despesa = ? AND id_usuario = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param("sii", $categoria, $id_despesa, $id_usuario);
            $stmt->execute();

            $mensagem = "Categoria da despesa atualizada.";
        }
    }

    if ($acao === "massa") {
        $ids = array_values(array_filter(array_map("intval", $_POST["selecionados"] ?? [])));
        $acao_massa = $_POST["acao_massa"] ?? "";

        if (!count($ids)) {
            $mensagem = "Selecione ao menos uma despesa.";
        } elseif ($acao_massa === "excluir") {
            $sql = "DELETE FROM despesas WHERE id_usuario = ? AND id_despesa = ?";
            $stmt = $conexao->prepare($sql);

            foreach ($ids as $id_despesa) {
                $stmt->bind_param("ii", $id_usuario, $id_despesa);
                $stmt->execute();
            }

            $mensagem = count($ids) . " despesa(s) excluída(s).";
        } elseif ($acao_massa === "status") {
            $status = status_despesa_valido_fincontrol($_POST["status_massa"] ?? "");
            $sql = "UPDATE despesas SET status = ? WHERE id_despesa = ? AND id_usuario = ?";
            $stmt = $conexao->prepare($sql);

            foreach ($ids as $id_despesa) {
                $stmt->bind_param("sii", $status, $id_despesa, $id_usuario);
                $stmt->execute();
            }

            $mensagem = "Status atualizado em " . count($ids) . " despesa(s).";
        } elseif ($acao_massa === "categoria") {
            $categoria = categoria_fincontrol_valida($_POST["categoria_massa"] ?? "", $categorias_despesas);

            if ($categoria === "") {
                $mensagem = "Selecione uma categoria válida.";
            } else {
                $sql = "UPDATE despesas SET categoria = ? WHERE id_despesa = ? AND id_usuario = ?";
                $stmt = $conexao->prepare($sql);

                foreach ($ids as $id_despesa) {
                    $stmt->bind_param("sii", $categoria, $id_despesa, $id_usuario);
                    $stmt->execute();
                }

                $mensagem = "Categoria atualizada em " . count($ids) . " despesa(s).";
            }
        } else {
            $mensagem = "Selecione uma ação em massa.";
        }
    }
}

/* CADASTRAR */
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["acao"] ?? "cadastrar") === "cadastrar") {
    $tipos_despesa_validos = ["conta_fixa", "cartao_credito", "compra_avulsa"];
    $tipo_despesa = $_POST["tipo_despesa"] ?? "";
    $descricao = trim($_POST["descricao"] ?? "");
    $categoria = categoria_fincontrol_valida($_POST["categoria"] ?? "", $categorias_despesas);
    $valor_parcela = floatval($_POST["valor"] ?? 0);
    $parcelas = in_array($tipo_despesa, ["cartao_credito", "compra_avulsa"], true) ? max(1, intval($_POST["parcelas"] ?? 1)) : 1;
    $data_vencimento = $_POST["data_vencimento"] ?? "";
    $data_objeto = DateTime::createFromFormat("Y-m-d", $data_vencimento);
    $data_valida = $data_objeto && $data_objeto->format("Y-m-d") === $data_vencimento;
    $id_cartao = null;
    $cartaoSelecionado = null;
    $id_orcamento_post = intval($_POST["id_orcamento"] ?? 0);
    $orcamentoSelecionado = null;
    $recorrente = in_array($tipo_despesa, ["conta_fixa", "cartao_credito"], true) && isset($_POST["recorrente"]) ? "Sim" : "Não";
    if ($recorrente === "Sim") $parcelas = 1;

    if ($id_orcamento_post > 0) {
        foreach ($limites_gastos as $limite) {
            if (intval($limite["id_orcamento"]) === $id_orcamento_post) {
                $orcamentoSelecionado = $limite;
                break;
            }
        }
    }

    if ($tipo_despesa === "cartao_credito") {
        $id_cartao_post = intval($_POST["id_cartao"] ?? 0);

        if ($id_cartao_post > 0) {
            foreach ($cartoes_credito as $cartao) {
                if (intval($cartao["id_cartao"]) === $id_cartao_post) {
                    $cartaoSelecionado = $cartao;
                    $id_cartao = $id_cartao_post;
                    break;
                }
            }
        }

        if ($cartaoSelecionado && $data_valida) {
            $data_vencimento = dataVencimentoCartaoFincontrol($data_vencimento, intval($cartaoSelecionado["dia_vencimento"]), intval($cartaoSelecionado["dia_fechamento"]));
        }
    }

    if (!in_array($tipo_despesa, $tipos_despesa_validos, true)) {
        $mensagem = "Selecione o tipo de despesa.";
    } elseif ($descricao === "" || $categoria === "" || $valor_parcela <= 0 || !$data_valida) {
        $mensagem = "Erro: preencha todos os campos corretamente.";
    } elseif ($id_orcamento_post > 0 && !$orcamentoSelecionado) {
        $mensagem = "Selecione um orçamento válido.";
    } else {

        $sucesso = true;
        $erro_banco = "";

        $qtd_lancamentos = ($recorrente == "Sim") ? 12 : $parcelas;
        $totalDespesasUsuario = fincontrol_total_lancamentos_usuario($conexao, $id_usuario, "despesas");
        if (!$premium_ativo_despesas && ($totalDespesasUsuario + $qtd_lancamentos) > $limite_despesas_gratuito) {
        $mensagem = "No plano gratuito você pode ter até " . $limite_despesas_gratuito . " lançamentos de despesas. Este cadastro criaria " . $qtd_lancamentos . " lançamento(s).";
        } else {

        $conexao->begin_transaction();

        for ($i = 1; $i <= $qtd_lancamentos; $i++) {

            $data_parcela = adicionarMes($data_vencimento, $i - 1);

            if ($recorrente == "Sim") {
                $parcela_atual = "Recorrente";
                $parcelas_banco = 1;
            } else {
                $parcela_atual = $i . "/" . $parcelas;
                $parcelas_banco = $parcelas;
            }

            $id_orcamento_insert = null;
            if ($orcamentoSelecionado) {
                foreach ($limites_gastos as $limiteMes) {
                    if ($limiteMes["categoria"] === $orcamentoSelecionado["categoria"] && substr($limiteMes["mes_referencia"], 0, 7) === substr($data_parcela, 0, 7)) {
                        $id_orcamento_insert = intval($limiteMes["id_orcamento"]);
                        break;
                    }
                }
            }

            $sql = "INSERT INTO despesas 
                    (id_usuario, id_orcamento, id_cartao, descricao, categoria, valor, parcelas, parcela_atual, data_vencimento, recorrente)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql);

            if (!$stmt) {
                $sucesso = false;
                $erro_banco = $conexao->error;
                break;
            }

            $valor_criptografado = criptografar_valor_financeiro($valor_parcela);

            $stmt->bind_param(
                "iiisssisss",
                $id_usuario,
                $id_orcamento_insert,
                $id_cartao,
                $descricao,
                $categoria,
                $valor_criptografado,
                $parcelas_banco,
                $parcela_atual,
                $data_parcela,
                $recorrente
            );

            if (!$stmt->execute()) {
                $sucesso = false;
                $erro_banco = $stmt->error;
                break;
            }

        }

        if ($sucesso) $conexao->commit();
        else $conexao->rollback();
        }

        if ($mensagem === "") {
            $mensagem = $sucesso
                ? "Despesa cadastrada com sucesso!"
                : "Erro ao cadastrar despesa: " . $erro_banco;
            if ($sucesso) fincontrol_evento_registrar($conexao, $id_usuario, "cadastro_despesa", ["quantidade" => $qtd_lancamentos, "tipo" => $tipo_despesa]);
        }
    }
}

/* LISTAR */
$whereListagem = ["despesas.id_usuario = ?"];
$tiposListagem = "i";
$paramsListagem = [$id_usuario];

if ($dataIni !== "") {
    $whereListagem[] = "despesas.data_vencimento >= ?";
    $tiposListagem .= "s";
    $paramsListagem[] = $dataIni;
}

if ($dataFim !== "") {
    $whereListagem[] = "despesas.data_vencimento <= ?";
    $tiposListagem .= "s";
    $paramsListagem[] = $dataFim;
}

if ($filtroMes !== "") {
    $whereListagem[] = "DATE_FORMAT(despesas.data_vencimento, '%m') = ?";
    $tiposListagem .= "s";
    $paramsListagem[] = $filtroMes;
}

if ($filtroAno !== "") {
    $whereListagem[] = "DATE_FORMAT(despesas.data_vencimento, '%Y') = ?";
    $tiposListagem .= "s";
    $paramsListagem[] = $filtroAno;
}

if ($buscaDespesa !== "") {
    $whereListagem[] = "(despesas.descricao LIKE ? OR despesas.categoria LIKE ? OR despesas.status LIKE ? OR despesas.recorrente LIKE ? OR cartoes_credito.nome_cartao LIKE ? OR orcamentos_categoria.categoria LIKE ?)";
    $tiposListagem .= "ssssss";
    $buscaLike = "%" . $buscaDespesa . "%";
    $paramsListagem[] = $buscaLike;
    $paramsListagem[] = $buscaLike;
    $paramsListagem[] = $buscaLike;
    $paramsListagem[] = $buscaLike;
    $paramsListagem[] = $buscaLike;
    $paramsListagem[] = $buscaLike;
}

$sql = "SELECT despesas.*, cartoes_credito.nome_cartao,
               orcamentos_categoria.categoria AS categoria_limite,
               orcamentos_categoria.mes_referencia AS mes_limite
        FROM despesas
        LEFT JOIN cartoes_credito
          ON cartoes_credito.id_cartao = despesas.id_cartao
         AND cartoes_credito.id_usuario = despesas.id_usuario
        LEFT JOIN orcamentos_categoria
          ON orcamentos_categoria.id_orcamento = despesas.id_orcamento
         AND orcamentos_categoria.id_usuario = despesas.id_usuario
        WHERE " . implode(" AND ", $whereListagem) . " ORDER BY data_vencimento ASC";
$stmt = $conexao->prepare($sql);
if (!$stmt) {
    $whereListagemFallback = array_values(array_filter($whereListagem, function ($condicao) {
        return strpos($condicao, "cartoes_credito.") === false
            && strpos($condicao, "orcamentos_categoria.") === false;
    }));
    $tiposListagemFallback = "";
    $paramsListagemFallback = [];
    foreach ($whereListagemFallback as $condicao) {
        if (strpos($condicao, "id_usuario") !== false) {
            $tiposListagemFallback .= "i";
            $paramsListagemFallback[] = $id_usuario;
        } elseif (strpos($condicao, "data_vencimento >=") !== false) {
            $tiposListagemFallback .= "s";
            $paramsListagemFallback[] = $dataIni;
        } elseif (strpos($condicao, "data_vencimento <=") !== false) {
            $tiposListagemFallback .= "s";
            $paramsListagemFallback[] = $dataFim;
        } elseif (strpos($condicao, "DATE_FORMAT(despesas.data_vencimento, '%m')") !== false) {
            $tiposListagemFallback .= "s";
            $paramsListagemFallback[] = str_pad($filtroMes, 2, "0", STR_PAD_LEFT);
        } elseif (strpos($condicao, "DATE_FORMAT(despesas.data_vencimento, '%Y')") !== false) {
            $tiposListagemFallback .= "s";
            $paramsListagemFallback[] = $filtroAno;
        } elseif (strpos($condicao, "despesas.descricao LIKE") !== false) {
            $tiposListagemFallback .= "ssss";
            $paramsListagemFallback[] = $buscaLike;
            $paramsListagemFallback[] = $buscaLike;
            $paramsListagemFallback[] = $buscaLike;
            $paramsListagemFallback[] = $buscaLike;
        }
    }
    $sqlFallback = "SELECT despesas.*, NULL AS nome_cartao, NULL AS categoria_limite, NULL AS mes_limite
            FROM despesas
            WHERE " . implode(" AND ", $whereListagemFallback) . " ORDER BY data_vencimento ASC";
    $stmt = $conexao->prepare($sqlFallback);
    $tiposListagem = $tiposListagemFallback;
    $paramsListagem = $paramsListagemFallback;
}
bindParametrosDespesas($stmt, $tiposListagem, $paramsListagem);
$stmt->execute();
$resultado = $stmt->get_result();
$totalDespesasMesMobile = 0;
while ($despesaTotalMobile = $resultado->fetch_assoc()) {
    $totalDespesasMesMobile += descriptografar_valor_financeiro($despesaTotalMobile["valor"]);
}
$resultado->data_seek(0);

$despesas_listagem_cache = [];
while ($despesaCache = $resultado->fetch_assoc()) {
    $despesaCache["_valor_num"] = descriptografar_valor_financeiro($despesaCache["valor"]);
    $despesas_listagem_cache[] = $despesaCache;
}
$resultado->data_seek(0);

function rotulo_grupo_despesa_fincontrol($data) {
    $hoje = new DateTime(date("Y-m-d"));
    $dataObj = new DateTime($data);
    $ontem = (clone $hoje)->modify("-1 day");

    if ($dataObj->format("Y-m-d") === $hoje->format("Y-m-d")) return "Hoje";
    if ($dataObj->format("Y-m-d") === $ontem->format("Y-m-d")) return "Ontem";

    $dias = ["Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado"];
    return $dias[(int) $dataObj->format("w")] . ", " . $dataObj->format("d/m");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php require_once "00.pwa.php"; ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinControle | Despesas</title>
<link href="img/logo-FinControle.png" rel="icon" type="image/png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Poppins,Arial,sans-serif}
body.despesas-app{background:#eef4f8;display:flex;height:100vh;overflow:hidden;color:#102a43}
.sidebar{width:250px;background:#087fb0;color:#fff;padding:28px 18px;display:flex;flex-direction:column;justify-content:space-between}
.brand{font-size:24px;font-weight:900;display:flex;align-items:center;gap:9px;margin-bottom:34px}
.menu{display:grid;gap:8px}
.menu a,.sidebar>a{color:#fff;text-decoration:none;padding:12px;border-radius:8px;display:flex;align-items:center;gap:10px;font-weight:800}
.menu a:hover,.menu a.active{background:rgba(255,255,255,.15)}
.main{flex:1;padding:22px;display:flex;flex-direction:column;overflow:auto;min-height:0}
.despesas-shell{display:grid;gap:14px}
.despesas-header{display:grid;grid-template-columns:44px 1fr 44px;align-items:center;gap:12px}
.despesas-header h1{text-align:center;color:#c0392b;font-size:30px;line-height:1}
.icon-btn{width:44px;height:44px;border:0;border-radius:14px;display:grid;place-items:center;background:#fff;color:#c0392b;box-shadow:0 8px 22px rgba(10,44,72,.08);cursor:pointer;text-decoration:none}
.period-pill{justify-self:center;display:inline-flex;align-items:center;justify-content:center;gap:6px;background:#fff;color:#425466;border:1px solid #d8e5ed;border-radius:999px;padding:0;font-weight:900;font-size:13px;position:relative;overflow:visible;margin-top:0;min-width:112px;height:32px}
.period-pill select{appearance:none!important;-webkit-appearance:none!important;display:block!important;background:transparent!important;border:0!important;color:inherit!important;font-weight:900!important;padding:0 30px 0 14px!important;width:112px!important;min-width:112px!important;height:30px!important;min-height:30px!important;line-height:30px!important;cursor:pointer}
.period-pill i{position:absolute;right:12px;pointer-events:none;font-size:11px}
.despesas-total-card{background:#fff;border:1px solid #dce8ef;border-radius:16px;padding:24px;box-shadow:0 10px 28px rgba(10,44,72,.08);text-align:center;position:relative}
.despesas-add-btn{position:absolute;right:22px;top:50%;transform:translateY(-50%);width:50px;height:50px;border-radius:50%;display:grid;place-items:center;background:#c0392b;color:#fff;font-size:22px;box-shadow:0 10px 24px rgba(192,57,43,.26);padding:0}
.despesas-total-card strong{display:block;color:#c0392b;font-size:42px;line-height:1.1;margin-top:4px}
.despesas-total-card span,.despesas-total-card small{color:#667085;font-weight:800}
.despesas-progress{height:9px;background:#e7edf2;border-radius:999px;overflow:hidden;margin:16px auto 0;max-width:420px}
.despesas-progress span{display:block;height:100%;background:#2fb46e;border-radius:999px}
.quick-create{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.quick-create button{min-height:88px;border:0;border-radius:16px;background:#fff;color:#c0392b;box-shadow:0 8px 22px rgba(10,44,72,.08);font-weight:900;font-size:16px;cursor:pointer}
.quick-create i{display:grid;place-items:center;width:42px;height:42px;margin:0 auto 8px;border-radius:50%;background:#c0392b;color:#fff;font-size:20px}
.despesas-list{background:#fff;border:1px solid #dce8ef;border-radius:16px;padding:16px;box-shadow:0 10px 28px rgba(10,44,72,.08)}
.list-title{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:14px}
.list-title h2{font-size:22px;color:#102a43}.list-title small{font-weight:900;color:#667085}
.date-group{margin-top:14px}.date-group:first-of-type{margin-top:0}
.date-label{font-size:13px;font-weight:900;color:#667085;margin:8px 0}
.expense-row{display:grid;grid-template-columns:44px minmax(0,1fr) auto 32px;align-items:center;gap:12px;background:#f8fbfd;border:1px solid #e4edf3;border-radius:12px;padding:10px 12px;margin-bottom:8px}
.row-icon{width:36px;height:36px;border-radius:50%;display:grid;place-items:center;background:#ffd9d9;color:#c0392b}
.row-main strong{display:block;color:#102a43;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.row-main small{display:block;color:#667085;font-size:12px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.row-money{text-align:right;font-weight:900;color:#102a43;white-space:nowrap}
.row-money small{display:block;color:#667085;font-size:11px}
.acao-linha{width:30px;min-width:30px;height:30px;border:0;background:transparent;color:#667085;font-size:0;cursor:pointer}
.acao-linha option{font-size:14px;color:#102a43}
.empty-state{padding:26px;text-align:center;color:#667085;font-weight:700}
label{font-weight:800;margin-bottom:5px;display:block;color:#243041}
input,select{width:100%;padding:10px;border:1px solid #ccd8e0;border-radius:8px;background:#fff}
button{background:#c0392b;color:#fff;border:none;padding:12px;border-radius:8px;cursor:pointer;font-weight:800}
.msg{background:#d1e7dd;color:#0f5132;padding:12px;border-radius:10px;margin-bottom:4px}
.form-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:15px}
.modal-backdrop{position:fixed;inset:0;z-index:9998;background:rgba(4,19,32,.66);display:none;align-items:center;justify-content:center;padding:18px;overflow:hidden}.modal-backdrop.is-open{display:flex}.modal-card{width:min(760px,100%);max-height:calc(100vh - 48px);overflow:hidden;background:#fff;border-radius:18px;box-shadow:0 22px 70px rgba(16,42,84,.28);padding:20px}.modal-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px}.modal-head h2{margin:0;color:#c0392b;font-size:22px}.modal-head p{margin:4px 0 0;color:#667085;font-weight:700}.modal-close{width:38px;height:38px;border-radius:10px;background:#eef3f7;color:#425466;padding:0;flex:none}.modal-actions{display:flex;gap:10px;align-items:center}.modal-actions button,.modal-actions .btn{width:auto;min-height:42px}.modal-actions .btn{background:#eef3f7;color:#425466;text-decoration:none}.body-modal-open{overflow:hidden}
.modal-escolha{width:min(520px,100%)}.opcoes-lancamento{display:grid;gap:10px}.opcoes-lancamento button{min-height:70px;display:grid;grid-template-columns:40px 1fr;align-items:center;text-align:left;gap:12px;padding:12px 16px;font-size:15px;background:#f8fbfd;color:#102a43;border:1px solid #dce8ef}.opcoes-lancamento button strong{display:block;color:#c0392b}.opcoes-lancamento button small{display:block;color:#667085;font-size:12px;font-weight:700;margin-top:2px}.opcoes-lancamento button i{width:40px;height:40px;border-radius:13px;display:grid;place-items:center;background:#ffe9e6;color:#c0392b;font-size:18px}
.expense-flow{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px}.expense-flow span{background:#f6f9fb;border:1px solid #dce8ef;border-radius:12px;padding:10px;font-size:12px;font-weight:900;color:#667085}.expense-flow b{display:block;color:#c0392b;font-size:13px}.expense-helper{background:#fff7f5;border:1px solid #ffd6cf;color:#8f2c20;border-radius:12px;padding:10px 12px;margin-bottom:14px;font-weight:800;font-size:13px}.app-filter-modal{position:fixed;inset:0;z-index:9997;background:rgba(4,19,32,.66);display:none;align-items:center;justify-content:center;padding:18px;overflow:hidden}.app-filter-modal.is-open{display:flex}.app-filter-dialog{width:min(760px,100%);max-height:calc(100vh - 48px);overflow:hidden;background:#fff;border-radius:18px;padding:20px;box-shadow:0 22px 70px rgba(16,42,84,.28)}.app-filter-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}.app-filter-head h2{color:#c0392b}.app-filter-head p{color:#667085;font-weight:700;margin-top:3px}.app-filter-head button{width:38px;height:38px;background:#eef3f7;color:#425466;padding:0}.command{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}.command .busca{grid-column:1/-1}.command button,.command .btn{width:100%;display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none}.command .btn{background:#eef3f7;color:#425466}
.modal-head h2,.app-filter-head h2,.opcoes-lancamento button strong,.expense-flow b{color:#0077b6}.opcoes-lancamento button i{background:#d8ecff;color:#0077b6}.opcoes-lancamento button{border-color:#d8ecff}.expense-helper{background:#f3fbff;border-color:#cfeeff;color:#075985}.modal-actions button:not(.btn),.app-filter-dialog button[type="submit"]{background:#0077b6;color:#fff}.modal-card,.app-filter-dialog{border:1px solid #cfe0eb}.modal-close,.app-filter-head button{background:#eef6fb;color:#17314f}
.modal-backdrop,.app-filter-modal{background:rgba(1,12,24,.78)}.modal-card,.app-filter-dialog{background:linear-gradient(180deg,#102a43,#071827);border:1px solid rgba(31,163,224,.28);color:#f8fbfd;box-shadow:0 24px 70px rgba(0,0,0,.42)}.modal-head h2,.app-filter-head h2{color:#f8fbfd}.modal-head p,.app-filter-head p{color:#9fb1c3}.modal-close,.app-filter-head button{background:#132f49;color:#d8ecff}.opcoes-lancamento button{background:#0d253a;color:#f8fbfd;border-color:#244761}.opcoes-lancamento button strong{color:#f8fbfd}.opcoes-lancamento button small{color:#9fb1c3}.opcoes-lancamento button i{background:#0b7fa9;color:#fff}.expense-flow span{background:#0d253a;border-color:#244761;color:#9fb1c3}.expense-flow b{color:#1fa3e0}.expense-helper{background:#0d253a;border-color:#244761;color:#b8d8f0}.modal-card label,.app-filter-dialog label{color:#d8ecff}.modal-card input,.modal-card select,.app-filter-dialog input,.app-filter-dialog select{background:#071827;border-color:#244761;color:#f8fbfd}.modal-card input::placeholder,.app-filter-dialog input::placeholder{color:#7d91a8}.modal-card option,.app-filter-dialog option{background:#071827;color:#f8fbfd}.modal-actions .btn,.command .btn{background:#132f49;color:#d8ecff}.modal-actions button:not(.btn),.app-filter-dialog button[type="submit"]{background:#0b7fa9;color:#fff}.check{display:flex!important;align-items:center;gap:10px;min-height:42px;padding:10px 0}.check input[type="checkbox"]{width:18px!important;min-width:18px;height:18px;margin:0;accent-color:#0b7fa9}.check span{color:#d8ecff;font-weight:800}
.hidden-mass-form{display:none}
.fc-mobile-menu-backdrop[hidden]{display:none!important}
.fc-mobile-menu-backdrop{position:fixed;inset:0;z-index:10001;background:rgba(4,19,32,.74);display:flex;align-items:flex-end;padding:12px}
.fc-mobile-menu-panel{width:100%;background:#fff;border-radius:18px 18px 10px 10px;padding:18px;display:grid;gap:10px}
.fc-mobile-menu-head{display:flex;justify-content:space-between;align-items:center}.fc-mobile-menu-head h2{color:#087fb0}
.fc-mobile-menu-head button{width:42px;height:42px;background:#eef3f7;color:#102a43;padding:0}
.fc-mobile-menu-panel a{display:flex;align-items:center;gap:12px;padding:13px;border-radius:12px;background:#f3f7fa;color:#102a43;text-decoration:none;font-weight:900}
@media(max-width:768px){
  body.despesas-app{display:block;height:100svh;background:#111318;overflow:hidden;padding:0 0 64px;color:#fff}
  body.despesas-app .main{height:calc(100svh - 64px);padding:12px 12px 10px;overflow:auto;background:linear-gradient(180deg,#111318 0%,#111318 100%)}
  .despesas-shell{gap:10px;min-height:100%;padding-bottom:4px}
  .despesas-header{grid-template-columns:36px 1fr 36px}
  .despesas-header h1{font-size:18px;color:#fff}.icon-btn{background:transparent;color:#fff;box-shadow:none}
  .period-pill{background:#22262d;color:#fff;border-color:#303741;margin-top:-2px;margin-bottom:0;min-width:108px;height:30px}
  .period-pill select{width:108px!important;min-width:108px!important;height:28px!important;min-height:28px!important;line-height:28px!important;padding:0 28px 0 12px!important;font-size:12px;text-align:center}
  .despesas-total-card{background:transparent;border:0;box-shadow:none;color:#fff;padding:4px 62px 6px 6px}
  .despesas-add-btn{right:10px;width:48px;height:48px;background:#1fa3e0;box-shadow:0 10px 24px rgba(31,163,224,.32)}
  .despesas-total-card strong{color:#fff;font-size:34px}.despesas-total-card span,.despesas-total-card small{color:#d8dee8}
  .despesas-progress{height:8px;background:#2b3038;margin-top:10px}.despesas-progress span{background:#35c66f}
  .quick-create{display:none}
  .despesas-list{background:transparent;border:0;box-shadow:none;padding:0}
  .list-title h2{font-size:15px;color:#fff}.list-title small,.date-label{color:#c9d0da}
  .expense-row{grid-template-columns:42px minmax(0,1fr) auto 24px;background:linear-gradient(180deg,#24282f,#1d2127);border:1px solid #303741;border-radius:10px;padding:10px;margin-bottom:8px}
  .row-main strong,.row-money{color:#fff}.row-main small,.row-money small{color:#c9d0da}
  .row-icon{background:#2f68ff;color:#fff}.acao-linha{color:#fff}
  .sidebar{position:fixed;left:0;right:0;bottom:0;z-index:999;width:100%;height:64px;min-height:0;padding:5px 8px;display:grid;grid-template-columns:repeat(5,1fr);gap:4px;background:#081727;border-top:1px solid rgba(255,255,255,.08);box-shadow:0 -10px 30px rgba(0,0,0,.26)}
  .sidebar .brand,.sidebar>a:not(.mobile-menu-only){display:none!important}
  .sidebar>div{display:contents!important}
  .menu{display:contents}
  .menu a,.mobile-menu-only{display:flex!important;flex-direction:column;align-items:center;justify-content:center;gap:4px;min-width:0;margin:0;padding:5px 2px;border-radius:14px;color:#9dacbd!important;background:transparent!important;font-size:0;line-height:1;font-weight:900;text-decoration:none}
  .menu a::after,.mobile-menu-only::after{display:block;font-size:10px;line-height:1;font-weight:900}
  .menu a[href="03.menu.php"]{order:1}.menu a[href="04.despesas.php"]{order:2}.menu a[href="05.receitas.php"]{order:3}.menu a[href="07.relatorios.php"]{order:4}.mobile-menu-only{order:5}
  .menu a[href="03.menu.php"]::after{content:"Inicio"}.menu a[href="04.despesas.php"]::after{content:"Despesas"}.menu a[href="05.receitas.php"]::after{content:"Receitas"}.menu a[href="07.relatorios.php"]::after{content:"Relatórios"}.mobile-menu-only::after{content:"Menu"}
  .menu a i,.mobile-menu-only i{font-size:18px}.menu a.active{background:#0b7fa9!important;color:#fff!important}
  .modal-backdrop,.app-filter-modal{align-items:center;justify-content:center;padding:10px;overflow:hidden}.modal-card,.app-filter-dialog{width:calc(100vw - 20px);max-height:calc(100svh - 84px);overflow:hidden;border-radius:18px;padding:14px}.modal-head,.app-filter-head{margin-bottom:8px}.modal-head h2,.app-filter-head h2{font-size:19px}.modal-head p{font-size:12px;line-height:1.25}.expense-flow,.expense-helper{display:none}.form-grid,.command{grid-template-columns:1fr 1fr;gap:8px}.command .busca,.form-grid .modal-actions{grid-column:1/-1}.modal-card label,.app-filter-dialog label{font-size:12px}.modal-card input,.modal-card select,.app-filter-dialog input,.app-filter-dialog select{min-height:40px;padding:8px}.modal-actions{display:grid;grid-template-columns:1fr 1fr}.modal-actions button,.modal-actions .btn,.command button,.command .btn{width:100%;min-height:40px;padding:9px}
}
</style>
<link rel="stylesheet" href="responsive.css?v=menu-cinco-20260917">
<?php echo function_exists("fincontrol_version_styles") ? fincontrol_version_styles() : ""; ?>
</head>

<body class="despesas-app">

<div class="sidebar">
    <div>
        <div class="brand"><i class="fa-solid fa-wallet"></i> FinControle</div>
                <div class="menu">
            <a href="03.menu.php"><i class="fa-solid fa-house"></i> Início</a>
            <a href="05.receitas.php"><i class="fa-solid fa-plus-circle"></i> Receitas</a>
            <a href="04.despesas.php" class="active"><i class="fa-solid fa-minus-circle"></i> Despesas</a>
            
            <a href="07.relatorios.php"><i class="fa-solid fa-chart-line"></i> Relatórios</a><a href="#" class="mobile-menu-only" data-fc-open-mobile-menu><i class="fa-solid fa-bars"></i> Menu</a>
        </div>
    </div>


    <a class="logout-link" href="15.logout.php">
        <i class="fa-solid fa-right-from-bracket"></i> Sair
    </a>
</div>

<div class="main">

<?php
$periodoDespesaAtual = ($periodoMes !== "" ? $periodoMes : ($filtroMes !== "" ? $filtroMes : date("m"))) . "/" . ($periodoAno !== "" ? $periodoAno : ($filtroAno !== "" ? $filtroAno : date("Y")));
$percentualDespesaVisual = $totalDespesasMesMobile > 0 ? 82 : 8;
$opcoesPeriodoDespesa = [];
$basePeriodoDespesa = new DateTime("first day of this month");
for ($i = -12; $i <= 12; $i++) {
    $periodo = clone $basePeriodoDespesa;
    $periodo->modify(($i >= 0 ? "+" : "") . "{$i} month");
    $opcoesPeriodoDespesa[] = $periodo->format("m/Y");
}
?>
<div class="despesas-shell">
    <header class="despesas-header">
        <a class="icon-btn" href="03.menu.php" aria-label="Voltar para o início"><i class="fa-solid fa-arrow-left"></i></a>
        <h1>Despesas</h1>
        <button class="icon-btn" type="button" data-filter-open="modalFiltrosDespesas" aria-label="Abrir filtros"><i class="fa-solid fa-filter"></i></button>
    </header>

    <form class="period-pill" method="GET" action="04.despesas.php">
        <input type="hidden" name="filtroOrigem" value="rapido">
        <input type="hidden" name="periodoMes" value="<?php echo htmlspecialchars($periodoMes !== "" ? $periodoMes : ($filtroMes !== "" ? $filtroMes : date("m"))); ?>">
        <input type="hidden" name="periodoAno" value="<?php echo htmlspecialchars($periodoAno !== "" ? $periodoAno : ($filtroAno !== "" ? $filtroAno : date("Y"))); ?>">
        <select aria-label="Selecionar mês e ano" onchange="const p=this.value.split('/');this.form.periodoMes.value=p[0];this.form.periodoAno.value=p[1];this.form.submit();">
            <?php foreach ($opcoesPeriodoDespesa as $opcaoPeriodo) { ?>
                <option value="<?php echo htmlspecialchars($opcaoPeriodo); ?>" <?php echo $periodoDespesaAtual === $opcaoPeriodo ? "selected" : ""; ?>><?php echo htmlspecialchars($opcaoPeriodo); ?></option>
            <?php } ?>
        </select>
        <i class="fa-solid fa-chevron-down"></i>
    </form>

    <?php if (!empty($mensagem)) { ?>
        <div class="msg"><?php echo $mensagem; ?></div>
    <?php } ?>

    <section class="despesas-total-card">
        <strong>R$ <?php echo number_format($totalDespesasMesMobile, 2, ",", "."); ?></strong>
        <button class="despesas-add-btn" type="button" data-modal-open="modalTipoDespesa" aria-label="Adicionar despesa"><i class="fa-solid fa-plus"></i></button>
        <div class="despesas-progress"><span style="width: <?php echo $percentualDespesaVisual; ?>%;"></span></div>
    </section>

    <section class="quick-create" aria-label="Ações rápidas de despesa">
        <button type="button" data-tipo-despesa="conta_fixa"><i class="fa-solid fa-calendar-check"></i>Conta fixa</button>
        <button type="button" data-tipo-despesa="cartao_credito"><i class="fa-solid fa-credit-card"></i>Cartão</button>
        <button type="button" data-tipo-despesa="compra_avulsa"><i class="fa-solid fa-plus"></i>Avulsa</button>
    </section>

    <section class="despesas-list">
        <div class="list-title">
            <h2>Lançamentos</h2>
            <small><?php echo count($despesas_listagem_cache); ?> item(ns)</small>
        </div>

        <?php if (!count($despesas_listagem_cache)) { ?>
            <div class="empty-state">Nenhuma despesa encontrada para este filtro.</div>
        <?php } ?>

        <?php $grupoDespesaAtual = ""; ?>
        <?php foreach ($despesas_listagem_cache as $despesa) { ?>
            <?php
                $grupoDespesa = rotulo_grupo_despesa_fincontrol($despesa["data_vencimento"]);
                $statusExibicao = status_despesa_exibicao_fincontrol($despesa["status"] ?? "A vencer", $despesa["data_vencimento"]);
                if ($grupoDespesa !== $grupoDespesaAtual) {
                    if ($grupoDespesaAtual !== "") echo '</div>';
                    $grupoDespesaAtual = $grupoDespesa;
                    echo '<div class="date-group"><div class="date-label">' . htmlspecialchars($grupoDespesa) . '</div>';
                }
            ?>
            <article class="expense-row">
                <div class="row-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                <div class="row-main">
                    <strong><?php echo htmlspecialchars($despesa["descricao"]); ?></strong>
                    <small><?php echo htmlspecialchars($despesa["categoria"] ?? "Outros"); ?> • <?php echo htmlspecialchars($statusExibicao); ?></small>
                </div>
                <div class="row-money">
                    R$ <?php echo number_format($despesa["_valor_num"], 2, ",", "."); ?>
                    <small><?php echo date("d/m/Y", strtotime($despesa["data_vencimento"])); ?></small>
                </div>
                <select class="acao-linha" aria-label="Ações da despesa" onchange="executarAcaoDespesa(this, <?php echo (int) $despesa["id_despesa"]; ?>)">
                    <option value="">›</option>
                    <option value="Paga" <?php echo $statusExibicao === "Paga" ? "disabled" : ""; ?>>Pagar</option>
                    <option value="A vencer" <?php echo $statusExibicao === "A vencer" ? "disabled" : ""; ?>>A vencer</option>
                    <option value="Atrasada" <?php echo $statusExibicao === "Atrasada" ? "disabled" : ""; ?>>Atrasada</option>
                    <option value="Excluir">Excluir</option>
                </select>
            </article>
        <?php } ?>
        <?php if ($grupoDespesaAtual !== "") echo '</div>'; ?>
    </section>
</div>

<div class="modal-backdrop" id="modalTipoDespesa" aria-hidden="true">
<div class="modal-card modal-escolha" role="dialog" aria-modal="true" aria-labelledby="tituloTipoDespesa">
<div class="modal-head">
    <div><h2 id="tituloTipoDespesa">Adicionar despesa</h2><p>Escolha o tipo para mostrar somente os campos necessários.</p></div>
    <button class="modal-close" type="button" data-modal-close aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
</div>
<div class="opcoes-lancamento">
    <button type="button" data-tipo-despesa="conta_fixa"><i class="fa-solid fa-calendar-check"></i><span><strong>Conta fixa</strong><small>Aluguel, internet, escola ou qualquer custo que repete.</small></span></button>
    <button type="button" data-tipo-despesa="cartao_credito"><i class="fa-solid fa-credit-card"></i><span><strong>Cartão de crédito</strong><small>Compra parcelada ou recorrente lançada na fatura.</small></span></button>
    <button type="button" data-tipo-despesa="compra_avulsa"><i class="fa-solid fa-bag-shopping"></i><span><strong>Compra avulsa</strong><small>Despesa pontual, à vista ou parcelada fora do cartão.</small></span></button>
</div>
</div>
</div>

<div class="modal-backdrop" id="modalNovaDespesa" aria-hidden="true">
<div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="tituloNovaDespesa">
<div class="modal-head">
    <div>
        <h2 id="tituloNovaDespesa">Nova despesa</h2>
        <p>Fluxo enxuto para cadastrar em poucos segundos.</p>
    </div>
    <button class="modal-close" type="button" data-modal-close aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
</div>
<form method="POST">
    <?php echo fincontrol_csrf_input(); ?>
    <input type="hidden" name="acao" value="cadastrar">
    <input type="hidden" name="tipo_despesa" id="tipoDespesaSelecionado" value="">
    <div class="expense-flow" aria-label="Etapas do lançamento">
        <span><b>1. Identifique</b>tipo e categoria</span>
        <span><b>2. Informe</b>valor e data</span>
        <span><b>3. Salve</b>gera parcelas se houver</span>
    </div>
    <div class="expense-helper">Parcelas aparecem para cartão e compra avulsa. Conta fixa pode repetir automaticamente todo mês.</div>
    <div class="form-grid">
        <div>
            <label>Descrição</label>
            <input type="text" name="descricao" required>
        </div>
        <div>
            <label>Categoria</label>
            <select name="categoria" id="categoriaDespesa" required>
                <option value="" disabled selected>Selecione uma categoria</option>
                <?php foreach ($categorias_despesas as $categoria_opcao) { ?>
                    <option value="<?php echo htmlspecialchars($categoria_opcao); ?>"><?php echo htmlspecialchars($categoria_opcao); ?></option>
                <?php } ?>
            </select>
        </div>
        <div>
            <label id="labelValorDespesa">Valor (R$)</label>
            <input type="number" step="0.01" name="valor" required>
        </div>
        <div id="campoParcelasDespesa" style="display:none;">
            <label>Quantidade de parcelas</label>
            <input type="number" name="parcelas" min="1" value="1" required>
        </div>
        <div>
            <label id="labelDataDespesa">Data de vencimento</label>
            <input type="date" name="data_vencimento" required>
        </div>
        <div class="check" id="campoRecorrenciaDespesa" style="display:none;">
            <input type="checkbox" name="recorrente" id="recorrenteDespesa">
            <span id="textoRecorrenciaDespesa">Repetir automaticamente todo mês</span>
        </div>
        <div class="modal-actions" style="display:flex;align-items:end;">
            <button type="submit"><i class="fa-solid fa-plus"></i> Adicionar</button>
            <button class="btn" type="button" data-modal-close>Cancelar</button>
        </div>
    </div>
</form>
</div>
</div>

<div class="app-filter-modal" id="modalFiltrosDespesas" aria-hidden="true">
<div class="app-filter-dialog" role="dialog" aria-modal="true" aria-labelledby="tituloFiltrosDespesas">
<div class="app-filter-head"><h2 id="tituloFiltrosDespesas">Filtrar despesas</h2><button type="button" data-filter-close aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button></div>
<form class="command" method="GET" action="04.despesas.php">
    <input type="hidden" name="filtroOrigem" value="avancado">
    <div class="field"><label>Data inicial</label><input type="date" name="dataIni" value="<?php echo htmlspecialchars($dataIni); ?>"></div>
    <div class="field"><label>Data final</label><input type="date" name="dataFim" value="<?php echo htmlspecialchars($dataFim); ?>"></div>
    <div class="field"><label>Mês</label><select name="filtroMes"><option value="">Todos</option><?php for ($i = 1; $i <= 12; $i++) { $m = str_pad($i, 2, "0", STR_PAD_LEFT); ?><option value="<?php echo $m; ?>" <?php echo $filtroMes === $m ? "selected" : ""; ?>><?php echo $i; ?></option><?php } ?></select></div>
    <div class="field"><label>Ano</label><select name="filtroAno"><option value="">Todos</option><?php for ($a = date("Y") - 2; $a <= date("Y") + 5; $a++) { ?><option value="<?php echo $a; ?>" <?php echo $filtroAno == $a ? "selected" : ""; ?>><?php echo $a; ?></option><?php } ?></select></div>
    <div class="field busca"><label>Busca na lista</label><input type="text" name="buscaDespesa" value="<?php echo htmlspecialchars($buscaDespesa); ?>" placeholder="Pesquisar despesas já lançadas..."></div>
    <button type="submit"><i class="fa-solid fa-filter"></i> Filtrar lista</button>
    <a class="btn" href="04.despesas.php"><i class="fa-solid fa-rotate-left"></i> Limpar filtro</a>
</form>
</div>
</div>

<form method="POST" id="formDespesasMassa" class="hidden-mass-form">
    <?php echo fincontrol_csrf_input(); ?>
    <input type="hidden" name="acao" value="massa">
    <input type="hidden" name="status" value="">
    <input type="hidden" name="id_despesa" value="">
</form>

<div class="fc-mobile-menu-backdrop" hidden>
    <div class="fc-mobile-menu-panel" role="dialog" aria-modal="true" aria-labelledby="tituloMenuCompletoDespesas">
        <div class="fc-mobile-menu-head"><h2 id="tituloMenuCompletoDespesas">Menu completo</h2><button type="button" data-fc-close-mobile-menu aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button></div>
        <a href="03.menu.php"><i class="fa-solid fa-house"></i> Início</a>
        <a href="04.despesas.php"><i class="fa-solid fa-minus-circle"></i> Despesas</a>
        <a href="05.receitas.php"><i class="fa-solid fa-plus-circle"></i> Receitas</a>
        
        <a href="07.relatorios.php"><i class="fa fa-chart-line"></i> Relatórios</a>
        <a href="30.treinos.php"><i class="fa fa-dumbbell"></i> Treinos</a>
        <a href="15.logout.php"><i class="fa-solid fa-right-from-bracket"></i> Sair</a>
    </div>
</div>

</div>
<script>
function abrirFiltrosFincontrol(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("body-modal-open");
}

function fecharFiltrosFincontrol(modal) {
    if (!modal) return;
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("body-modal-open");
}

document.querySelectorAll("[data-filter-open]").forEach(function (botao) {
    botao.addEventListener("click", function () { abrirFiltrosFincontrol(botao.dataset.filterOpen); });
});
document.querySelectorAll("[data-filter-close]").forEach(function (botao) {
    botao.addEventListener("click", function () { fecharFiltrosFincontrol(botao.closest(".app-filter-modal")); });
});
document.querySelectorAll(".app-filter-modal").forEach(function (modal) {
    modal.addEventListener("click", function (evento) { if (evento.target === modal) fecharFiltrosFincontrol(modal); });
});

const menuMobileDespesas = document.querySelector(".fc-mobile-menu-backdrop");
document.querySelectorAll("[data-fc-open-mobile-menu]").forEach(function (botao) {
    botao.addEventListener("click", function (evento) {
        evento.preventDefault();
        if (menuMobileDespesas) menuMobileDespesas.hidden = false;
    });
});
document.querySelectorAll("[data-fc-close-mobile-menu]").forEach(function (botao) {
    botao.addEventListener("click", function () {
        if (menuMobileDespesas) menuMobileDespesas.hidden = true;
    });
});
if (menuMobileDespesas) {
    menuMobileDespesas.addEventListener("click", function (evento) {
        if (evento.target === menuMobileDespesas) menuMobileDespesas.hidden = true;
    });
}

if (window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true) {
    const despesasUrl = new URL(window.location.href);
    const possuiFiltroDespesas = ["dataIni", "dataFim", "filtroMes", "filtroAno", "periodoMes", "periodoAno", "buscaDespesa", "filtroOrigem"].some(function (campo) {
        return despesasUrl.searchParams.has(campo);
    });
    if (!possuiFiltroDespesas) {
        const hojeDespesas = new Date();
        despesasUrl.searchParams.set("filtroOrigem", "rapido");
        despesasUrl.searchParams.set("periodoMes", String(hojeDespesas.getMonth() + 1).padStart(2, "0"));
        despesasUrl.searchParams.set("periodoAno", String(hojeDespesas.getFullYear()));
        window.location.replace(despesasUrl.toString());
    }
}

function abrirModalFincontrol(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("body-modal-open");
    const foco = modal.querySelector("form input:not([type='hidden']), form select, form textarea");
    if (foco) foco.focus();
}

function fecharModalFincontrol(modal) {
    if (!modal) return;
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("body-modal-open");
}

document.querySelectorAll("[data-modal-open]").forEach(botao => {
    botao.addEventListener("click", () => abrirModalFincontrol(botao.getAttribute("data-modal-open")));
});

document.querySelectorAll("[data-modal-close]").forEach(botao => {
    botao.addEventListener("click", () => fecharModalFincontrol(botao.closest(".modal-backdrop")));
});

document.querySelectorAll(".modal-backdrop").forEach(modal => {
    modal.addEventListener("click", evento => {
        if (evento.target === modal) fecharModalFincontrol(modal);
    });
});

document.addEventListener("keydown", evento => {
    if (evento.key === "Escape") {
        document.querySelectorAll(".modal-backdrop.is-open").forEach(fecharModalFincontrol);
    }
});

const selecionarTodasDespesas = document.getElementById("selecionarTodasDespesas");
if (selecionarTodasDespesas) {
    selecionarTodasDespesas.addEventListener("change", () => {
        document.querySelectorAll('input[name="selecionados[]"]').forEach(item => item.checked = selecionarTodasDespesas.checked);
    });
}

const campoCartaoCredito = document.getElementById("campoCartaoCredito");
const idCartaoDespesa = document.getElementById("idCartaoDespesa");
const campoParcelasDespesa = document.getElementById("campoParcelasDespesa");
const parcelasDespesa = document.querySelector('[name="parcelas"]');
const campoRecorrenciaDespesa = document.getElementById("campoRecorrenciaDespesa");
const recorrenteDespesa = document.getElementById("recorrenteDespesa");
const textoRecorrenciaDespesa = document.getElementById("textoRecorrenciaDespesa");
const labelValorDespesa = document.getElementById("labelValorDespesa");
const labelDataDespesa = document.getElementById("labelDataDespesa");

function atualizarTipoDespesa() {
    const selecionado = document.getElementById("tipoDespesaSelecionado");
    const tipo = selecionado ? selecionado.value : "";
    const ehCartao = tipo === "cartao_credito";
    const ehContaFixa = tipo === "conta_fixa";
    const permiteParcelas = ehCartao || tipo === "compra_avulsa";
    const titulo = document.getElementById("tituloNovaDespesa");
    if (titulo) titulo.textContent = ehCartao ? "Compra no cartão" : (ehContaFixa ? "Nova conta fixa" : "Nova compra avulsa");

    if (campoCartaoCredito) {
        campoCartaoCredito.style.display = ehCartao ? "block" : "none";
    }

    if (idCartaoDespesa) {
        idCartaoDespesa.required = ehCartao;
        if (!ehCartao) {
            idCartaoDespesa.value = "";
        }
    }

    if (campoParcelasDespesa) campoParcelasDespesa.style.display = permiteParcelas ? "block" : "none";
    if (parcelasDespesa && !permiteParcelas) parcelasDespesa.value = "1";

    if (campoRecorrenciaDespesa) campoRecorrenciaDespesa.style.display = (ehCartao || ehContaFixa) ? "flex" : "none";
    if (recorrenteDespesa) {
        if (ehContaFixa) recorrenteDespesa.checked = true;
        if (!ehCartao && !ehContaFixa) recorrenteDespesa.checked = false;
    }
    if (textoRecorrenciaDespesa) textoRecorrenciaDespesa.textContent = ehCartao ? "Compra recorrente" : "Repetir automaticamente todo mês";
    if (labelValorDespesa) labelValorDespesa.textContent = permiteParcelas ? "Valor da parcela (R$)" : (ehContaFixa ? "Valor mensal (R$)" : "Valor (R$)");

    if (labelDataDespesa) {
        labelDataDespesa.textContent = ehCartao ? "Data da compra" : "Data de vencimento";
    }
    atualizarParcelamentoRecorrente();
}

function atualizarParcelamentoRecorrente() {
    if (!parcelasDespesa || !recorrenteDespesa) return;
    parcelasDespesa.disabled = recorrenteDespesa.checked;
    if (recorrenteDespesa.checked) parcelasDespesa.value = "1";
}

document.querySelectorAll("[data-tipo-despesa]").forEach(botao => {
    botao.addEventListener("click", () => {
        const campoTipo = document.getElementById("tipoDespesaSelecionado");
        if (campoTipo) campoTipo.value = botao.dataset.tipoDespesa;
        atualizarTipoDespesa();
        fecharModalFincontrol(botao.closest(".modal-backdrop"));
        abrirModalFincontrol("modalNovaDespesa");
    });
});
if (recorrenteDespesa) recorrenteDespesa.addEventListener("change", atualizarParcelamentoRecorrente);

function confirmarAcaoMassaDespesas() {
    const selecionados = document.querySelectorAll('input[name="selecionados[]"]:checked').length;
    const acao = document.querySelector('#formDespesasMassa select[name="acao_massa"]').value;

    if (!selecionados) {
        alert("Selecione ao menos uma despesa.");
        return false;
    }

    if (!acao) {
        alert("Selecione uma ação em massa.");
        return false;
    }

    if (acao === "excluir") {
        return confirm("Deseja excluir as despesas selecionadas?");
    }

    return true;
}

function prepararAcaoLinhaDespesa(acao, id, status = "") {
    const form = document.getElementById("formDespesasMassa");
    form.acao.value = acao;
    form.id_despesa.value = id;
    form.status.value = status;
    return form;
}

function alterarCategoriaDespesa(id) {
    const form = prepararAcaoLinhaDespesa("alterar_categoria_linha", id);
    form.submit();
}

function executarAcaoDespesa(select, id) {
    const valor = select.value;

    if (!valor) {
        return false;
    }

    if (valor === "Excluir") {
        if (!confirm("Deseja excluir esta despesa?")) {
            select.value = "";
            return false;
        }

        select.disabled = true;
        const form = prepararAcaoLinhaDespesa("excluir_linha", id);
        form.submit();
        return true;
    }

    select.disabled = true;
    const form = prepararAcaoLinhaDespesa("alterar_status_linha", id, valor);
    form.submit();
    return true;
}
</script>
<?php echo function_exists("fincontrol_version_badge") ? fincontrol_version_badge() : ""; ?>
</body>
</html>


