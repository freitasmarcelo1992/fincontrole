<?php
require_once __DIR__ . '/00.sessao.php';
require_once __DIR__ . '/09.conexao.php';
require_once __DIR__ . '/00.exercicios_catalogo.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

function treinos_catalogo_resposta(int $status, array $dados): void
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (empty($_SESSION['id_usuario'])) {
    treinos_catalogo_resposta(401, ['ok' => false, 'erro' => 'Sua sessão expirou. Entre novamente.']);
}

$idUsuario = (int) $_SESSION['id_usuario'];
$csrf = (string) ($_SESSION['treinos_csrf'] ?? '');
session_write_close();

if (!$conexao) {
    treinos_catalogo_resposta(503, ['ok' => false, 'erro' => 'Não foi possível conectar ao banco de dados.']);
}

function treinos_catalogo_schema(mysqli $conexao): bool
{
    $comandos = [
        "CREATE TABLE IF NOT EXISTS treinos_exercicios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(100) NOT NULL,
            nome VARCHAR(140) NOT NULL,
            regiao VARCHAR(60) NOT NULL,
            musculo VARCHAR(80) NOT NULL,
            equipamento VARCHAR(100) NOT NULL,
            series_sugeridas VARCHAR(40) NOT NULL,
            tutorial TEXT NOT NULL,
            erros TEXT NOT NULL,
            video_url VARCHAR(500) NOT NULL,
            composto TINYINT(1) NOT NULL DEFAULT 0,
            ativo TINYINT(1) NOT NULL DEFAULT 1,
            atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_treinos_exercicios_slug (slug),
            INDEX idx_treinos_exercicios_filtro (ativo, regiao, musculo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS treinos_personalizados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NOT NULL,
            nome VARCHAR(100) NOT NULL,
            objetivo VARCHAR(30) NOT NULL,
            duracao_minutos SMALLINT NOT NULL,
            criterio_tipo VARCHAR(20) NOT NULL,
            criterio_valor VARCHAR(80) NOT NULL,
            criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_treinos_personalizados_usuario (id_usuario, atualizado_em)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS treinos_personalizados_itens (
            id_treino INT NOT NULL,
            id_exercicio INT NOT NULL,
            ordem SMALLINT NOT NULL,
            PRIMARY KEY (id_treino, id_exercicio),
            INDEX idx_treinos_personalizados_itens_ordem (id_treino, ordem),
            CONSTRAINT fk_treino_item_treino FOREIGN KEY (id_treino) REFERENCES treinos_personalizados(id) ON DELETE CASCADE,
            CONSTRAINT fk_treino_item_exercicio FOREIGN KEY (id_exercicio) REFERENCES treinos_exercicios(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];

    foreach ($comandos as $sql) {
        if (!$conexao->query($sql)) {
            return false;
        }
    }
    return true;
}

function treinos_catalogo_abastecer(mysqli $conexao): bool
{
    $catalogo = fincontrol_exercicios_catalogo();
    $resultado = $conexao->query('SELECT COUNT(*) AS total FROM treinos_exercicios');
    $total = $resultado ? (int) ($resultado->fetch_assoc()['total'] ?? 0) : 0;
    if ($total >= count($catalogo)) {
        return true;
    }

    $stmt = $conexao->prepare("INSERT INTO treinos_exercicios
        (slug, nome, regiao, musculo, equipamento, series_sugeridas, tutorial, erros, video_url, composto, ativo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ON DUPLICATE KEY UPDATE nome=VALUES(nome), regiao=VALUES(regiao), musculo=VALUES(musculo),
            equipamento=VALUES(equipamento), series_sugeridas=VALUES(series_sugeridas), tutorial=VALUES(tutorial),
            erros=VALUES(erros), video_url=VALUES(video_url), composto=VALUES(composto), ativo=1");
    if (!$stmt) {
        return false;
    }

    foreach ($catalogo as $item) {
        $stmt->bind_param(
            'sssssssssi',
            $item['slug'], $item['nome'], $item['regiao'], $item['musculo'], $item['equipamento'],
            $item['series'], $item['tutorial'], $item['erros'], $item['video'], $item['composto']
        );
        if (!$stmt->execute()) {
            $stmt->close();
            return false;
        }
    }
    $stmt->close();
    return true;
}

function treinos_catalogo_listar(mysqli $conexao): array
{
    $resultado = $conexao->query("SELECT id, slug, nome, regiao, musculo, equipamento,
        series_sugeridas, tutorial, erros, video_url, composto
        FROM treinos_exercicios WHERE ativo = 1 ORDER BY regiao, musculo, composto DESC, nome");
    $itens = [];
    while ($resultado && $linha = $resultado->fetch_assoc()) {
        $itens[] = [
            'id' => (int) $linha['id'],
            'slug' => (string) $linha['slug'],
            'nome' => (string) $linha['nome'],
            'regiao' => (string) $linha['regiao'],
            'musculo' => (string) $linha['musculo'],
            'equipamento' => (string) $linha['equipamento'],
            'series' => (string) $linha['series_sugeridas'],
            'tutorial' => (string) $linha['tutorial'],
            'erros' => array_values(array_filter(array_map('trim', explode(';', (string) $linha['erros'])))),
            'video' => (string) $linha['video_url'],
            'composto' => (bool) $linha['composto'],
        ];
    }
    return $itens;
}

function treinos_personalizados_listar(mysqli $conexao, int $idUsuario): array
{
    $stmt = $conexao->prepare("SELECT t.id, t.nome, t.objetivo, t.duracao_minutos, t.criterio_tipo, t.criterio_valor,
        GROUP_CONCAT(i.id_exercicio ORDER BY i.ordem SEPARATOR ',') AS exercicios
        FROM treinos_personalizados t
        LEFT JOIN treinos_personalizados_itens i ON i.id_treino = t.id
        WHERE t.id_usuario = ? GROUP BY t.id ORDER BY t.atualizado_em DESC LIMIT 30");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $treinos = [];
    while ($linha = $resultado->fetch_assoc()) {
        $treinos[] = [
            'id' => (int) $linha['id'],
            'nome' => (string) $linha['nome'],
            'objetivo' => (string) $linha['objetivo'],
            'duracao' => (int) $linha['duracao_minutos'],
            'criterio_tipo' => (string) $linha['criterio_tipo'],
            'criterio_valor' => (string) $linha['criterio_valor'],
            'exercicios' => $linha['exercicios'] ? array_map('intval', explode(',', $linha['exercicios'])) : [],
        ];
    }
    $stmt->close();
    return $treinos;
}

function treinos_catalogo_recomendacao(mysqli $conexao, int $idUsuario): array
{
    $alvos = ['Peito', 'Costas', 'Quadríceps', 'Posterior de coxa', 'Ombros'];
    $contagem = array_fill_keys($alvos, 0);
    $mapa = [
        'seg' => ['Peito'], 'ter' => ['Costas'], 'qua' => ['Quadríceps'],
        'qui' => ['Ombros'], 'sex' => ['Posterior de coxa'],
        'fba' => ['Peito', 'Quadríceps'], 'fbb' => ['Costas', 'Posterior de coxa'],
        'fbc' => ['Ombros', 'Quadríceps'],
    ];
    $idsPersonalizados = [];
    $stmt = $conexao->prepare("SELECT dia, exercicios_concluidos FROM treinos_historico WHERE id_usuario = ? AND data_treino >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) ORDER BY data_treino DESC LIMIT 30");
    if ($stmt) {
        $stmt->bind_param('i', $idUsuario);
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();
            while ($linha = $resultado->fetch_assoc()) {
                foreach ($mapa[strtolower((string) $linha['dia'])] ?? [] as $alvo) {
                    $contagem[$alvo]++;
                }
                $concluidos = json_decode((string) ($linha['exercicios_concluidos'] ?? '[]'), true);
                foreach (is_array($concluidos) ? $concluidos : [] as $referencia) {
                    if (preg_match('/^personalizado-\d+-(\d+)$/', (string) $referencia, $match)) {
                        $idsPersonalizados[] = (int) $match[1];
                    }
                }
            }
        }
        $stmt->close();
    }
    $idsPersonalizados = array_values(array_unique(array_filter($idsPersonalizados)));
    if ($idsPersonalizados) {
        $ids = implode(',', $idsPersonalizados);
        $resultado = $conexao->query("SELECT musculo FROM treinos_exercicios WHERE id IN ($ids)");
        while ($resultado && $linha = $resultado->fetch_assoc()) {
            $musculo = (string) $linha['musculo'];
            if (array_key_exists($musculo, $contagem)) {
                $contagem[$musculo]++;
            }
        }
    }
    asort($contagem);
    $musculo = (string) array_key_first($contagem);
    $total = array_sum($contagem);
    return [
        'musculo' => $musculo,
        'motivo' => $total > 0
            ? 'É o grupo com menor frequência nos últimos 14 dias.'
            : 'É um bom ponto de partida enquanto seu histórico é formado.',
    ];
}

if (!treinos_catalogo_schema($conexao) || !treinos_catalogo_abastecer($conexao)) {
    treinos_catalogo_resposta(500, ['ok' => false, 'erro' => 'Não foi possível preparar o catálogo de exercícios.']);
}

$metodo = $_SERVER['REQUEST_METHOD'];
if ($metodo === 'POST' || $metodo === 'DELETE') {
    if ($csrf === '' || !hash_equals($csrf, (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''))) {
        treinos_catalogo_resposta(403, ['ok' => false, 'erro' => 'Reabra a página de treinos e tente novamente.']);
    }
    $dados = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($dados)) {
        $dados = [];
    }

    if ($metodo === 'DELETE') {
        $idTreino = (int) ($dados['id'] ?? 0);
        $stmt = $conexao->prepare('DELETE FROM treinos_personalizados WHERE id = ? AND id_usuario = ? LIMIT 1');
        if (!$stmt) treinos_catalogo_resposta(500, ['ok' => false, 'erro' => 'Não foi possível excluir o treino.']);
        $stmt->bind_param('ii', $idTreino, $idUsuario);
        $ok = $stmt->execute();
        $stmt->close();
        treinos_catalogo_resposta($ok ? 200 : 500, ['ok' => $ok]);
    }

    $nome = trim((string) ($dados['nome'] ?? ''));
    $objetivo = (string) ($dados['objetivo'] ?? 'hipertrofia');
    $duracao = (int) ($dados['duracao'] ?? 45);
    $criterioTipo = (string) ($dados['criterio_tipo'] ?? 'musculo');
    $criterioValor = trim((string) ($dados['criterio_valor'] ?? ''));
    $exercicios = array_values(array_unique(array_map('intval', is_array($dados['exercicios'] ?? null) ? $dados['exercicios'] : [])));

    if ($nome === '' || mb_strlen($nome) > 100 || !in_array($objetivo, ['hipertrofia', 'forca', 'condicionamento'], true)
        || !in_array($duracao, [30, 45, 60], true) || !in_array($criterioTipo, ['regiao', 'musculo'], true)
        || $criterioValor === '' || count($exercicios) < 3 || count($exercicios) > 12) {
        treinos_catalogo_resposta(422, ['ok' => false, 'erro' => 'Revise o nome e selecione entre 3 e 12 exercícios.']);
    }

    $ids = implode(',', $exercicios);
    $validos = $conexao->query("SELECT id FROM treinos_exercicios WHERE ativo = 1 AND id IN ($ids)");
    if (!$validos || $validos->num_rows !== count($exercicios)) {
        treinos_catalogo_resposta(422, ['ok' => false, 'erro' => 'A seleção contém exercícios inválidos.']);
    }

    $conexao->begin_transaction();
    try {
        $stmt = $conexao->prepare('INSERT INTO treinos_personalizados (id_usuario, nome, objetivo, duracao_minutos, criterio_tipo, criterio_valor) VALUES (?, ?, ?, ?, ?, ?)');
        if (!$stmt) throw new RuntimeException('prepare_treino');
        $stmt->bind_param('ississ', $idUsuario, $nome, $objetivo, $duracao, $criterioTipo, $criterioValor);
        if (!$stmt->execute()) throw new RuntimeException('insert_treino');
        $idTreino = (int) $stmt->insert_id;
        $stmt->close();

        $itemStmt = $conexao->prepare('INSERT INTO treinos_personalizados_itens (id_treino, id_exercicio, ordem) VALUES (?, ?, ?)');
        if (!$itemStmt) throw new RuntimeException('prepare_itens');
        foreach ($exercicios as $ordem => $idExercicio) {
            $posicao = $ordem + 1;
            $itemStmt->bind_param('iii', $idTreino, $idExercicio, $posicao);
            if (!$itemStmt->execute()) throw new RuntimeException('insert_item');
        }
        $itemStmt->close();
        $conexao->commit();
        treinos_catalogo_resposta(201, ['ok' => true, 'id' => $idTreino]);
    } catch (Throwable $erro) {
        $conexao->rollback();
        treinos_catalogo_resposta(500, ['ok' => false, 'erro' => 'Não foi possível salvar o treino.']);
    }
}

treinos_catalogo_resposta(200, [
    'ok' => true,
    'catalogo' => treinos_catalogo_listar($conexao),
    'treinos' => treinos_personalizados_listar($conexao, $idUsuario),
    'recomendacao' => treinos_catalogo_recomendacao($conexao, $idUsuario),
]);
