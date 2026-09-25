<?php
require_once "00.sessao.php";
require_once "00.noticias.php";

if (!isset($_SESSION["id_usuario"])) {
    header("Location: 02.login.php");
    exit();
}

$nomeUsuario = $_SESSION["nome_usuario"] ?? "Usuário";
$primeiroNome = trim(explode(" ", $nomeUsuario)[0] ?? "Usuário");
$idUsuarioNoticias = intval($_SESSION["id_usuario"] ?? 0);
$consultaNoticias = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
$paginaNoticias = max(1, (int) ($_GET['pagina'] ?? 1));
$arquivoNoticias = fincontrol_noticias_unicas(array_merge(
    fincontrol_noticias_home(80), fincontrol_noticias_historico(PHP_INT_MAX)
));
$resultadosNoticias = $consultaNoticias === ''
    ? fincontrol_noticias_balancear_por_tema(fincontrol_noticias_recentes($arquivoNoticias))
    : fincontrol_noticias_pesquisar($arquivoNoticias, $consultaNoticias);
$totalNoticias = count($resultadosNoticias);
$totalPaginasNoticias = max(1, (int) ceil($totalNoticias / 60));
$paginaNoticias = min($paginaNoticias, $totalPaginasNoticias);
$noticias = array_slice($resultadosNoticias, ($paginaNoticias - 1) * 60, 60);
$_SESSION['noticias_csrf'] = $_SESSION['noticias_csrf'] ?? bin2hex(random_bytes(24));

function minhas_noticias_avatar_src(int $idUsuario): string
{
    if ($idUsuario <= 0) {
        return "";
    }

    $arquivos = glob(__DIR__ . "/uploads/avatars/avatar_" . $idUsuario . ".*");
    if (!$arquivos || !isset($arquivos[0]) || !is_file($arquivos[0])) {
        return "";
    }

    return "uploads/avatars/" . basename($arquivos[0]) . "?v=" . filemtime($arquivos[0]);
}

function minhas_noticias_h($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

function minhas_noticias_texto_minusculo(string $texto): string
{
    return function_exists("mb_strtolower") ? mb_strtolower($texto, "UTF-8") : strtolower($texto);
}

function minhas_noticias_link_seguro(array $noticia): string
{
    if (function_exists("fincontrol_noticias_link_destino_valido") && fincontrol_noticias_link_destino_valido($noticia)) {
        return (string) $noticia["link"];
    }

    return "27.guias.php";
}

function minhas_noticias_link_target(array $noticia): string
{
    return (function_exists("fincontrol_noticias_link_destino_valido") && fincontrol_noticias_link_destino_valido($noticia)) ? ' target="_blank" rel="noopener noreferrer"' : "";
}

function minhas_noticias_icone(array $noticia): array
{
    $texto = minhas_noticias_texto_minusculo(
        ($noticia["titulo"] ?? "") . " " .
        ($noticia["resumo"] ?? "") . " " .
        ($noticia["fonte"] ?? "") . " " .
        ($noticia["tema"] ?? "")
    );

    $temPalavra = static function (string $texto, string $chave): bool {
        $chave = minhas_noticias_texto_minusculo($chave);
        if (strlen($chave) <= 3) {
            return (bool) preg_match('/(^|[^a-z0-9])' . preg_quote($chave, '/') . '([^a-z0-9]|$)/u', $texto);
        }

        return str_contains($texto, $chave);
    };

    $regras = [
        ["chaves" => ["mega-sena", "megasena", "loteria", "ganhador", "prêmio", "premio"], "icone" => "fa-dice", "classe" => "news-icon-loteria", "rotulo" => "LOTERIA"],
        ["chaves" => ["anatel", "telefonia", "telefone", "operadora", "plano móvel", "plano movel"], "icone" => "fa-tower-cell", "classe" => "news-icon-telecom", "rotulo" => "ANATEL"],
        ["chaves" => ["vazamento", "documentos", "afetado", "direito digital", "dados pessoais"], "icone" => "fa-file-shield", "classe" => "news-icon-dados", "rotulo" => "DADOS"],
        ["chaves" => ["apple", "iphone", "patente", "violação", "violacao"], "icone" => "fa-mobile-screen-button", "classe" => "news-icon-apple", "rotulo" => "APPLE"],
        ["chaves" => ["olhar digital news", "news na íntegra", "news na integra", "vídeo", "video"], "icone" => "fa-circle-play", "classe" => "news-icon-video", "rotulo" => "NEWS"],
        ["chaves" => ["openai", "chatgpt", "inteligência artificial", "inteligencia artificial", "agentes de ia", " ia ", "automação", "automacao"], "icone" => "fa-brain", "classe" => "news-icon-ia", "rotulo" => "IA"],
        ["chaves" => ["crm", "cliente", "clientes", "vendas", "comercial", "lead"], "icone" => "fa-address-card", "classe" => "news-icon-crm", "rotulo" => "CRM"],
        ["chaves" => ["produto", "produtividade", "prioridade", "processo", "projeto", "gestão", "gestao"], "icone" => "fa-list-check", "classe" => "news-icon-projeto", "rotulo" => "GESTÃO"],
        ["chaves" => ["concurso", "inscrição", "inscricao", "candidato", "transpetro"], "icone" => "fa-id-card", "classe" => "news-icon-servico", "rotulo" => "SERVIÇO"],
        ["chaves" => ["crédito", "credito", "cartão", "cartao", "banco", "juros", "parcelas"], "icone" => "fa-credit-card", "classe" => "news-icon-credito", "rotulo" => "CRÉDITO"],
        ["chaves" => ["dólar", "dolar", "câmbio", "cambio", "mercado", "selic", "bolsa"], "icone" => "fa-chart-line", "classe" => "news-icon-mercado", "rotulo" => "MERCADO"],
        ["chaves" => ["lucro", "receita líquida", "receita liquida", "resultado", "vibra"], "icone" => "fa-sack-dollar", "classe" => "news-icon-resultado", "rotulo" => "RESULT."],
        ["chaves" => ["orçamento", "orcamento", "gasto", "despesa", "receita", "dinheiro"], "icone" => "fa-wallet", "classe" => "news-icon-financas", "rotulo" => "BOLSO"],
        ["chaves" => ["segurança", "seguranca", "senha", "internet", "digital"], "icone" => "fa-shield-halved", "classe" => "news-icon-dados", "rotulo" => "SEGUR."],
        ["chaves" => ["software", "tecnologia", "app", "sistema"], "icone" => "fa-microchip", "classe" => "news-icon-tech", "rotulo" => "TECH"],
        ["chaves" => ["engenharia", "obra", "infraestrutura", "construção", "construcao"], "icone" => "fa-helmet-safety", "classe" => "news-icon-engenharia", "rotulo" => "OBRA"],
        ["chaves" => ["energia", "indústria", "industria"], "icone" => "fa-bolt", "classe" => "news-icon-energia", "rotulo" => "ENERGIA"],
    ];

    foreach ($regras as $regra) {
        foreach ($regra["chaves"] as $chave) {
            if ($temPalavra($texto, $chave)) {
                return $regra;
            }
        }
    }

    $tema = (string) ($noticia["tema"] ?? "financas");
    return match ($tema) {
        "carreira" => ["icone" => "fa-graduation-cap", "classe" => "news-icon-projeto", "rotulo" => "CARREIRA"],
        "treinos" => ["icone" => "fa-dumbbell", "classe" => "news-icon-tech", "rotulo" => "TREINOS"],
        "saude" => ["icone" => "fa-heart-pulse", "classe" => "news-icon-financas", "rotulo" => "SAÚDE"],
        "tecnologia" => ["icone" => "fa-microchip", "classe" => "news-icon-tech", "rotulo" => "TECH"],
        "gestao" => ["icone" => "fa-list-check", "classe" => "news-icon-projeto", "rotulo" => "GESTÃO"],
        "engenharia" => ["icone" => "fa-helmet-safety", "classe" => "news-icon-engenharia", "rotulo" => "OBRA"],
        default => ["icone" => "fa-wallet", "classe" => "news-icon-financas", "rotulo" => "BOLSO"],
    };
}

function minhas_noticias_tema(array $noticia): string
{
    $texto = minhas_noticias_texto_minusculo(($noticia["titulo"] ?? "") . " " . ($noticia["resumo"] ?? ""));
    $temas = [
        "tecnologia" => ["tecnologia", "ia", "inteligência artificial", "internet", "software", "dados", "digital", "app"],
        "gestao" => ["gestão", "gestao", "projeto", "produtividade", "processo", "negócio", "negocio", "empresa"],
        "engenharia" => ["engenharia", "infraestrutura", "energia", "construção", "construcao", "obra", "indústria", "industria"],
    ];

    foreach ($temas as $tema => $palavras) {
        foreach ($palavras as $palavra) {
            if (str_contains($texto, $palavra)) {
                return $tema;
            }
        }
    }

    return "financas";
}

function minhas_noticias_rotulo_tema(string $tema): string
{
    return fincontrol_noticias_categorias()[$tema] ?? 'Notícias';
}

function minhas_noticias_exemplos_por_tema(): array
{
    $porTema = [];
    foreach (minhas_noticias_exemplos_lista() as $noticia) {
        $tema = $noticia["tema"] ?? "financas";
        if (!isset($porTema[$tema])) {
            $porTema[$tema] = $noticia;
        }
    }

    return $porTema;
}

function minhas_noticias_exemplos_lista(): array
{
    return [
        [
            "tema" => "financas",
            "titulo" => "Planejamento do mês ajuda a reduzir gastos invisíveis",
            "resumo" => "Veja como organizar entradas, contas e pequenas compras para entender quanto ainda pode gastar.",
            "fonte" => "FinControle",
            "tempo" => "Hoje",
            "imagem" => "img/news-planning.jpg",
            "link" => "27.guias.php"
        ],
        [
            "tema" => "financas",
            "titulo" => "Cartão de crédito: pequenas parcelas também pesam no orçamento",
            "resumo" => "Organizar vencimentos e compras parceladas ajuda a evitar surpresa no fechamento da fatura.",
            "fonte" => "FinControle",
            "tempo" => "Hoje",
            "imagem" => "img/news-card.jpg",
            "link" => "27.guias.php#cartao"
        ],
        [
            "tema" => "financas",
            "titulo" => "Reserva de emergência começa com uma meta simples",
            "resumo" => "Separar um valor possível todo mês torna o controle financeiro mais sustentável.",
            "fonte" => "FinControle",
            "tempo" => "Hoje",
            "imagem" => "img/news-dollar.jpg",
            "link" => "27.guias.php#reserva"
        ],
        [
            "tema" => "tecnologia",
            "titulo" => "Tecnologia no bolso: apps ajudam a acompanhar dinheiro em tempo real",
            "resumo" => "Alertas, extratos digitais e relatórios simples tornam a rotina financeira mais fácil de acompanhar.",
            "fonte" => "FinControle",
            "tempo" => "Hoje",
            "imagem" => "img/news-card.jpg",
            "link" => "27.guias.php"
        ],
        [
            "tema" => "tecnologia",
            "titulo" => "Automação reduz retrabalho no controle financeiro pessoal",
            "resumo" => "Rotinas digitais ajudam a revisar lançamentos, categorias e extratos com menos esforço.",
            "fonte" => "FinControle",
            "tempo" => "Hoje",
            "imagem" => "img/news-planning.jpg",
            "link" => "27.guias.php"
        ],
        [
            "tema" => "tecnologia",
            "titulo" => "Segurança digital também faz parte da vida financeira",
            "resumo" => "Senhas, autenticação e cuidado com links protegem dados bancários e contas online.",
            "fonte" => "FinControle",
            "tempo" => "Hoje",
            "imagem" => "img/news-selic.jpg",
            "link" => "27.guias.php"
        ],
        [
            "tema" => "gestao",
            "titulo" => "Como a IA está mudando o trabalho diário de quem faz produto",
            "resumo" => "Mudanças práticas na rotina de produto, decisão e priorização com apoio de inteligência artificial.",
            "fonte" => "PM3",
            "tempo" => "Hoje",
            "imagem" => "img/news-planning.jpg",
            "link" => "https://pm3.com.br/blog/product-manager-ia/"
        ],
        [
            "tema" => "gestao",
            "titulo" => "Software de CRM: os recursos essenciais para escalar vendas com IA",
            "resumo" => "Recursos de gestão comercial, automação e dados para organizar processos de vendas.",
            "fonte" => "Pipefy",
            "tempo" => "Hoje",
            "imagem" => "img/news-card.jpg",
            "link" => "https://www.pipefy.com/pt-br/blog/software-de-crm/"
        ],
        [
            "tema" => "gestao",
            "titulo" => "Gestão de clientes: como estruturar o processo com CRM e Agentes de IA",
            "resumo" => "Como estruturar processos, acompanhar clientes e organizar decisões com CRM.",
            "fonte" => "Pipefy",
            "tempo" => "Hoje",
            "imagem" => "img/news-card.jpg",
            "link" => "https://www.pipefy.com/pt-br/blog/gestao-de-clientes/"
        ],
        [
            "tema" => "engenharia",
            "titulo" => "Engenharia e energia: custos de infraestrutura pedem planejamento",
            "resumo" => "Contas recorrentes, manutenção e consumo de energia podem pesar no mês quando não entram no controle.",
            "fonte" => "FinControle",
            "tempo" => "Hoje",
            "imagem" => "img/news-selic.jpg",
            "link" => "27.guias.php"
        ],
        [
            "tema" => "engenharia",
            "titulo" => "Obras e manutenção exigem reserva para imprevistos",
            "resumo" => "Custos técnicos variam e precisam aparecer no planejamento antes de virar dívida.",
            "fonte" => "FinControle",
            "tempo" => "Hoje",
            "imagem" => "img/news-planning.jpg",
            "link" => "27.guias.php"
        ],
        [
            "tema" => "engenharia",
            "titulo" => "Eficiência energética pode aliviar despesas fixas",
            "resumo" => "Medir consumo e comparar meses ajuda a identificar desperdícios no orçamento.",
            "fonte" => "FinControle",
            "tempo" => "Hoje",
            "imagem" => "img/news-card.jpg",
            "link" => "27.guias.php"
        ],
    ];
}

$noticias = array_map(function ($noticia) {
    $noticia["tema"] = $noticia["tema"] ?? minhas_noticias_tema($noticia);
    $noticia["tema_rotulo"] = minhas_noticias_rotulo_tema($noticia["tema"]);
    return $noticia;
}, $noticias);

$avatarNoticiasSrc = minhas_noticias_avatar_src($idUsuarioNoticias);
$noticiasCarrossel = [];
$titulosCarrossel = [];
foreach (array_keys(fincontrol_noticias_categorias()) as $temaCarrossel) {
    foreach ($noticias as $noticia) {
        if (($noticia["tema"] ?? "") === $temaCarrossel) {
            $tituloCarrossel = (string)($noticia["titulo"] ?? "");
            if (!isset($titulosCarrossel[$tituloCarrossel])) {
                $noticiasCarrossel[] = $noticia;
                $titulosCarrossel[$tituloCarrossel] = true;
            }
            break;
        }
    }
}
foreach ($noticias as $noticia) {
    if (count($noticiasCarrossel) >= 6) {
        break;
    }
    $tituloCarrossel = (string)($noticia["titulo"] ?? "");
    if (!isset($titulosCarrossel[$tituloCarrossel])) {
        $noticiasCarrossel[] = $noticia;
        $titulosCarrossel[$tituloCarrossel] = true;
    }
}
$noticiasLista = $noticias;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>FinControle | Minhas notícias</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#071927">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .news-pagination { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:16px 0; }
        :root {
            --bg: #071927;
            --panel: #102b44;
            --panel-2: #143651;
            --line: rgba(130, 190, 225, .22);
            --text: #f4fbff;
            --muted: #a8bad0;
            --brand: #10b7e8;
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; background: var(--bg); color: var(--text); font-family: Arial, Helvetica, sans-serif; }
        body { overflow-x: hidden; }
        a { color: inherit; text-decoration: none; }
        button, input { font: inherit; }

        .news-page {
            min-height: 100dvh;
            padding: 18px 18px 96px;
            background:
                radial-gradient(circle at 90% 4%, rgba(16, 183, 232, .18), transparent 28%),
                linear-gradient(180deg, #08283d 0%, #071927 34%, #061521 100%);
        }

        .news-shell { width: min(1120px, 100%); margin: 0 auto; overflow: hidden; }
        .news-header { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .news-title-block { display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1; }
        .news-avatar { width: 46px; height: 46px; border-radius: 50%; flex: 0 0 46px; display: grid; place-items: center; background: rgba(255,255,255,.12); color: #fff; font-weight: 900; overflow: hidden; }
        .news-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .news-copy { min-width: 0; }
        .news-header h1 { margin: 0; font-size: clamp(24px, 6vw, 34px); letter-spacing: -.02em; }
        .news-header p { margin: 3px 0 0; color: var(--muted); font-size: 14px; }

        .news-filter-card,
        .news-carousel,
        .news-list-card {
            border: 1px solid var(--line);
            background: linear-gradient(145deg, rgba(20, 54, 81, .94), rgba(10, 35, 55, .96));
            box-shadow: 0 20px 50px rgba(0, 0, 0, .22);
            border-radius: 18px;
        }

        .news-filter-card { padding: 14px; margin-bottom: 12px; }
        .news-search { display: grid; grid-template-columns: minmax(0, 1fr) 52px; gap: 10px; margin-bottom: 12px; }
        .news-search input { min-width: 0; border: 1px solid var(--line); border-radius: 14px; color: var(--text); background: rgba(4, 19, 31, .62); padding: 12px 14px; outline: none; }
        .news-search button { border: 0; border-radius: 14px; background: var(--brand); color: #fff; padding: 0 16px; font-weight: 800; cursor: pointer; }
        .news-chips { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 7px; }
        .news-chip { min-width: 0; border: 1px solid var(--line); border-radius: 999px; background: rgba(255,255,255,.07); color: var(--muted); padding: 9px 5px; font-size: 12px; font-weight: 800; line-height: 1.2; text-align: center; cursor: pointer; }
        .news-chip.active { background: rgba(16, 183, 232, .18); color: #fff; border-color: rgba(16, 183, 232, .72); }

        .section-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 16px 2px 10px; }
        .section-heading h2 { margin: 0; font-size: 20px; }
        .section-heading span { color: var(--brand); font-size: 12px; font-weight: 900; text-transform: uppercase; }

        .news-carousel { overflow: hidden; }
        .news-carousel-track { display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; }
        .news-carousel-track::-webkit-scrollbar { display: none; }
        .news-feature { flex: 0 0 100%; width: 100%; min-height: 230px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; gap: 18px; scroll-snap-align: start; background-size: cover; background-position: center; position: relative; isolation: isolate; overflow: hidden; }
        .news-feature::before { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(6, 21, 33, .94), rgba(6, 21, 33, .62)); z-index: -1; }
        .news-source { width: fit-content; border-radius: 999px; background: rgba(7, 25, 39, .8); border: 1px solid var(--line); color: #d9efff; padding: 8px 11px; font-size: 12px; font-weight: 900; }
        .news-feature h3 { margin: 0; max-width: 680px; font-size: clamp(23px, 7.2vw, 42px); line-height: 1; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
        .news-feature p { margin: 10px 0 0; max-width: 640px; color: #d5e4ef; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .news-feature small { color: var(--brand); font-weight: 900; }
        .news-feature-icon { width: 58px; height: 54px; border-radius: 16px; display: inline-flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; margin-bottom: 12px; color: #fff; border: 1px solid rgba(255,255,255,.18); box-shadow: 0 14px 28px rgba(0,0,0,.24); }
        .news-feature-icon i { font-size: 19px; }
        .news-icon-label { display: block; font-size: 8px; line-height: 1; font-weight: 900; letter-spacing: 0; text-transform: uppercase; opacity: .96; max-width: 52px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .news-list-card { padding: 12px; }
        .news-grid { display: grid; gap: 10px; }
        .news-item { display: grid; grid-template-columns: 86px 1fr auto; gap: 12px; align-items: center; border: 1px solid var(--line); background: rgba(255,255,255,.04); border-radius: 16px; padding: 10px; }
        .news-thumb { width: 86px; height: 72px; border-radius: 13px; object-fit: cover; background: var(--panel-2); }
        .news-topic-icon { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px; color: #fff; border: 1px solid rgba(255,255,255,.12); box-shadow: inset 0 1px 0 rgba(255,255,255,.08), 0 12px 24px rgba(0,0,0,.18); }
        .news-topic-icon i { font-size: 24px; }
        .news-icon-financas { background: linear-gradient(135deg, #22c7f2, #087fb2); }
        .news-icon-tech { background: linear-gradient(135deg, #8b5cf6, #2563eb); }
        .news-icon-gestao { background: linear-gradient(135deg, #14b8a6, #0f766e); }
        .news-icon-engenharia { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .news-icon-video { background: linear-gradient(135deg, #ef4444, #7f1d1d); }
        .news-icon-telecom { background: linear-gradient(135deg, #06b6d4, #155e75); }
        .news-icon-dados { background: linear-gradient(135deg, #64748b, #1e293b); }
        .news-icon-apple { background: linear-gradient(135deg, #94a3b8, #334155); }
        .news-icon-loteria { background: linear-gradient(135deg, #22c55e, #166534); }
        .news-icon-ia { background: linear-gradient(135deg, #a855f7, #4c1d95); }
        .news-icon-crm { background: linear-gradient(135deg, #14b8a6, #0f766e); }
        .news-icon-projeto { background: linear-gradient(135deg, #0ea5e9, #075985); }
        .news-icon-servico { background: linear-gradient(135deg, #f97316, #9a3412); }
        .news-icon-credito { background: linear-gradient(135deg, #38bdf8, #0369a1); }
        .news-icon-mercado { background: linear-gradient(135deg, #10b981, #065f46); }
        .news-icon-resultado { background: linear-gradient(135deg, #eab308, #854d0e); }
        .news-icon-energia { background: linear-gradient(135deg, #f59e0b, #b45309); }
        .news-item h3 { margin: 0 0 4px; font-size: 15px; line-height: 1.15; }
        .news-meta { display: flex; flex-wrap: wrap; gap: 8px; color: var(--muted); font-size: 12px; }
        .news-meta strong { color: var(--brand); }
        .news-arrow { color: #fff; opacity: .8; }
        .news-empty { display: none; margin: 12px 0 0; color: var(--muted); text-align: center; padding: 18px; border: 1px dashed var(--line); border-radius: 16px; }

        .news-bottom-nav { position: fixed; left: 0; right: 0; bottom: 0; z-index: 30; display: grid; grid-template-columns:repeat(5,1fr); gap: 4px; padding: 10px 12px calc(10px + env(safe-area-inset-bottom)); background: rgba(6, 21, 33, .96); border-top: 1px solid var(--line); backdrop-filter: blur(16px); }
        .news-bottom-nav a { display: grid; gap: 5px; place-items: center; color: var(--muted); font-size: 11px; font-weight: 900; }
        .news-bottom-nav i { font-size: 18px; }
        .news-bottom-nav a.active { color: var(--brand); }

        .fc-mobile-menu-backdrop[hidden] { display: none; }
        .fc-mobile-menu-backdrop { position: fixed; inset: 0; z-index: 80; display: grid; place-items: center; padding: 20px; background: rgba(2, 10, 17, .72); }
        .fc-mobile-menu-panel { width: min(380px, 100%); border-radius: 22px; background: linear-gradient(180deg, #133653, #0b263d); color: #eaf6ff; padding: 18px; border: 1px solid rgba(40,180,235,.22); box-shadow: 0 30px 80px rgba(0,0,0,.38); }
        .fc-mobile-menu-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .fc-mobile-menu-head strong { color: #fff; font-size: 24px; }
        .fc-mobile-menu-head button { border: 0; border-radius: 12px; width: 42px; height: 42px; color: #fff; background: rgba(34,199,242,.18); }
        .fc-mobile-menu-panel a { display: flex; align-items: center; gap: 12px; padding: 13px 14px; border-radius: 13px; background: rgba(255,255,255,.07); color: #eaf6ff; font-weight: 900; margin-top: 9px; border: 1px solid rgba(255,255,255,.06); }
        .fc-mobile-menu-panel a i { color: var(--brand); }
        .fc-mobile-menu-panel a.active { background: rgba(34,199,242,.16); color: #22c7f2; }

        @media (min-width: 780px) {
            .news-page { padding-left: 280px; }
            .news-shell { max-width: 1040px; }
            .news-filter-card { display: grid; grid-template-columns: 1fr minmax(460px, auto); gap: 12px; align-items: center; }
            .news-search { margin: 0; min-width: 360px; }
            .news-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .news-bottom-nav { display: none; }
        }
    </style>
</head>
<body class="minhas-noticias-app">
<main class="news-page">
    <div class="news-shell">
        <header class="news-header">
            <div class="news-title-block">
                <div class="news-avatar">
                    <?php if ($avatarNoticiasSrc !== "") { ?>
                        <img src="<?php echo minhas_noticias_h($avatarNoticiasSrc); ?>" alt="Foto de perfil">
                    <?php } else { ?>
                        <?php echo minhas_noticias_h(strtoupper(substr($primeiroNome, 0, 1))); ?>
                    <?php } ?>
                </div>
                <div class="news-copy">
                    <h1>Minhas notícias</h1>
                    <p>Conteúdo útil para o seu dinheiro, seu trabalho e suas decisões.</p>
                </div>
            </div>
        </header>

        <section class="news-filter-card" aria-label="Filtros de notícias">
            <form class="news-search" method="get" action="29.minhas_noticias.php" data-news-search>
                <input type="search" name="q" value="<?php echo minhas_noticias_h($consultaNoticias); ?>" placeholder="Notícias sobre qual assunto?" aria-label="Pesquisar notícia" data-news-query>
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
            <div class="news-chips" aria-label="Temas">
                <button class="news-chip active" type="button" data-news-theme="todos">Todos</button>
                <?php foreach (fincontrol_noticias_categorias() as $tema => $rotulo) { ?>
                <button class="news-chip" type="button" data-news-theme="<?php echo minhas_noticias_h($tema); ?>"><?php echo minhas_noticias_h($rotulo); ?></button>
                <?php } ?>
            </div>
        </section>

        <section aria-label="Notícias em destaque">
            <div class="section-heading">
                <h2>Últimas por tema</h2>
                <span><?php echo $consultaNoticias !== '' ? 'Histórico' : 'Últimas 72h'; ?></span>
            </div>
            <div class="news-carousel">
                <div class="news-carousel-track">
                    <?php foreach ($noticiasCarrossel as $noticia) {
                        $iconeNoticia = minhas_noticias_icone($noticia);
                    ?>
                        <a class="news-feature" href="<?php echo minhas_noticias_h(minhas_noticias_link_seguro($noticia)); ?>"<?php echo minhas_noticias_link_target($noticia); ?> data-news-card data-theme="<?php echo minhas_noticias_h($noticia["tema"]); ?>" data-text="<?php echo minhas_noticias_h(minhas_noticias_texto_minusculo(($noticia["titulo"] ?? "") . " " . ($noticia["resumo"] ?? "") . " " . ($noticia["fonte"] ?? ""))); ?>" style="background-image:url('<?php echo minhas_noticias_h($noticia["imagem"] ?? "img/news-planning.jpg"); ?>')">
                            <div>
                                <span class="news-feature-icon <?php echo minhas_noticias_h($iconeNoticia["classe"]); ?>">
                                    <i class="fa-solid <?php echo minhas_noticias_h($iconeNoticia["icone"]); ?>"></i>
                                    <small class="news-icon-label"><?php echo minhas_noticias_h($iconeNoticia["rotulo"]); ?></small>
                                </span>
                                <div class="news-source"><i class="fa fa-newspaper"></i> Fonte: <?php echo minhas_noticias_h($noticia["fonte"] ?? "FinControle"); ?></div>
                                <h3><?php echo minhas_noticias_h($noticia["titulo"] ?? "Notícia"); ?></h3>
                                <p><?php echo minhas_noticias_h($noticia["resumo"] ?? "Leia a notícia completa."); ?></p>
                            </div>
                            <small><?php echo minhas_noticias_h($noticia["tema_rotulo"]); ?> · <?php echo minhas_noticias_h($noticia["tempo"] ?? "Hoje"); ?></small>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </section>

        <section aria-label="Lista de notícias">
            <div class="section-heading">
                <h2><?php echo $consultaNoticias !== '' ? 'Resultados da pesquisa' : 'Mais recentes'; ?></h2>
                <span><?php echo $totalNoticias; ?> notícias</span>
            </div>
            <div class="news-list-card">
                <div class="news-grid">
                    <?php foreach ($noticiasLista as $noticia) {
                        $iconeNoticia = minhas_noticias_icone($noticia);
                    ?>
                        <a class="news-item" href="<?php echo minhas_noticias_h(minhas_noticias_link_seguro($noticia)); ?>"<?php echo minhas_noticias_link_target($noticia); ?> data-news-card data-theme="<?php echo minhas_noticias_h($noticia["tema"]); ?>" data-text="<?php echo minhas_noticias_h(minhas_noticias_texto_minusculo(($noticia["titulo"] ?? "") . " " . ($noticia["resumo"] ?? "") . " " . ($noticia["fonte"] ?? ""))); ?>">
                            <span class="news-thumb news-topic-icon <?php echo minhas_noticias_h($iconeNoticia["classe"]); ?>">
                                <i class="fa-solid <?php echo minhas_noticias_h($iconeNoticia["icone"]); ?>"></i>
                                <small class="news-icon-label"><?php echo minhas_noticias_h($iconeNoticia["rotulo"]); ?></small>
                            </span>
                            <div>
                                <h3><?php echo minhas_noticias_h($noticia["titulo"] ?? "Notícia"); ?></h3>
                                <div class="news-meta">
                                    <strong><?php echo minhas_noticias_h($noticia["fonte"] ?? "FinControle"); ?></strong>
                                    <span><?php echo minhas_noticias_h($noticia["tema_rotulo"]); ?></span>
                                    <span><i class="fa fa-clock"></i> <?php echo minhas_noticias_h($noticia["tempo"] ?? "Hoje"); ?></span>
                                </div>
                            </div>
                            <i class="news-arrow fa fa-chevron-right"></i>
                        </a>
                    <?php } ?>
                </div>
                <div class="news-empty" style="display:<?php echo $totalNoticias ? 'none' : 'block'; ?>" data-news-empty><?php echo $consultaNoticias === '' ? 'Nenhuma notícia das últimas 72 horas neste filtro. O histórico continua disponível na pesquisa.' : 'Nenhuma notícia encontrada para esta pesquisa.'; ?></div>
            </div>
        </section>
        <?php if ($totalPaginasNoticias > 1) { ?>
        <nav class="news-pagination" aria-label="Páginas de notícias">
            <?php if ($paginaNoticias > 1) { ?><a href="?<?php echo minhas_noticias_h(http_build_query(['q' => $consultaNoticias, 'pagina' => $paginaNoticias - 1])); ?>">Anterior</a><?php } ?>
            <span><?php echo $paginaNoticias . ' / ' . $totalPaginasNoticias; ?></span>
            <?php if ($paginaNoticias < $totalPaginasNoticias) { ?><a href="?<?php echo minhas_noticias_h(http_build_query(['q' => $consultaNoticias, 'pagina' => $paginaNoticias + 1])); ?>">Próxima</a><?php } ?>
        </nav>
        <?php } ?>
        <p data-news-update-status role="status"></p>
    </div>
</main>

<div class="fc-mobile-menu-backdrop" data-fc-mobile-menu hidden>
    <nav class="fc-mobile-menu-panel" aria-label="Menu completo">
        <div class="fc-mobile-menu-head">
            <strong>Menu completo</strong>
            <button type="button" data-fc-close-mobile-menu aria-label="Fechar menu"><i class="fa fa-xmark"></i></button>
        </div>
        <a href="03.menu.php"><i class="fa fa-house"></i> Início</a>
        <a href="04.despesas.php"><i class="fa fa-minus-circle"></i> Despesas</a>
        <a href="05.receitas.php"><i class="fa fa-plus-circle"></i> Receitas</a>
        
        <a href="07.relatorios.php"><i class="fa fa-chart-line"></i> Relatórios</a>
        <a class="active" href="29.minhas_noticias.php"><i class="fa fa-newspaper"></i> Minhas notícias</a>
        <a href="30.treinos.php"><i class="fa fa-dumbbell"></i> Treinos</a>
        <a href="15.logout.php"><i class="fa fa-sign-out"></i> Sair</a>
    </nav>
</div>

<nav class="news-bottom-nav" aria-label="Menu inferior">
    <a href="03.menu.php"><i class="fa fa-house"></i><span>Início</span></a>
    <a href="04.despesas.php"><i class="fa fa-minus-circle"></i><span>Despesas</span></a>
    <a href="05.receitas.php"><i class="fa fa-plus-circle"></i><span>Receitas</span></a>
    
    <a href="07.relatorios.php"><i class="fa fa-chart-line"></i><span>Relatórios</span></a>
    <a class="active" href="#" data-fc-open-mobile-menu><i class="fa fa-bars"></i><span>Menu</span></a>
</nav>

<script>
    (function () {
        const menu = document.querySelector("[data-fc-mobile-menu]");
        const openButtons = document.querySelectorAll("[data-fc-open-mobile-menu]");
        const closeButtons = document.querySelectorAll("[data-fc-close-mobile-menu]");

        openButtons.forEach((button) => {
            button.addEventListener("click", (event) => {
                event.preventDefault();
                menu.hidden = false;
            });
        });

        closeButtons.forEach((button) => {
            button.addEventListener("click", () => {
                menu.hidden = true;
            });
        });

        menu.addEventListener("click", (event) => {
            if (event.target === menu) {
                menu.hidden = true;
            }
        });

        const cards = Array.from(document.querySelectorAll("[data-news-card]"));
        const chips = Array.from(document.querySelectorAll("[data-news-theme]"));
        const form = document.querySelector("[data-news-search]");
        const queryInput = document.querySelector("[data-news-query]");
        const empty = document.querySelector("[data-news-empty]");
        let currentTheme = "todos";
        let currentQuery = <?php echo json_encode($consultaNoticias, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        function applyFilters() {
            let visible = 0;
            cards.forEach((card) => {
                const matchesTheme = currentQuery !== "" || currentTheme === "todos" || card.dataset.theme === currentTheme;
                const matchesQuery = true; // A busca global ja foi aplicada ao historico no servidor.
                const show = matchesTheme && matchesQuery;
                card.style.display = show ? "" : "none";
                if (show) visible++;
            });
            if (empty) empty.style.display = visible ? "none" : "block";
        }

        chips.forEach((chip) => {
            chip.addEventListener("click", () => {
                chips.forEach((item) => item.classList.remove("active"));
                chip.classList.add("active");
                currentTheme = chip.dataset.newsTheme || "todos";
                applyFilters();
            });
        });

        form.addEventListener("submit", () => {
            chips.forEach((chip) => chip.classList.toggle("active", chip.dataset.newsTheme === "todos"));
        });
    })();
</script>
<script src="assets/noticias-atualizacao.js?v=2" data-news-revision="<?php echo minhas_noticias_h(fincontrol_noticias_revisao()); ?>" data-news-token="<?php echo minhas_noticias_h($_SESSION['noticias_csrf']); ?>" defer></script>
</body>
</html>
