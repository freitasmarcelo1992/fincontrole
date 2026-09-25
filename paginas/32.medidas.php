<?php
require_once __DIR__ . '/00.sessao.php';
require_once __DIR__ . '/09.conexao.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

function medidas_resposta(int $status, array $dados): void {
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}
if (empty($_SESSION['id_usuario'])) medidas_resposta(401, ['erro' => 'Sua sessão expirou. Entre novamente.']);
$usuario = (int) $_SESSION['id_usuario'];
$csrf = (string) ($_SESSION['medidas_csrf'] ?? '');
session_write_close();
$metodo = $_SERVER['REQUEST_METHOD'];
if (!in_array($metodo, ['GET', 'POST'], true)) medidas_resposta(405, ['erro' => 'Método não permitido.']);
if ($metodo === 'POST' && ($csrf === '' || !hash_equals($csrf, (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')))) {
    medidas_resposta(403, ['erro' => 'Reabra a página de Treinos e tente novamente.']);
}
if (!$conexao) medidas_resposta(503, ['erro' => 'Não foi possível conectar. Tente novamente.']);

try {
    // Existing installations create the table once; normal reads do not run DDL.
    if (!$conexao->query('SELECT 1 FROM treinos_medidas LIMIT 0')) {
        if (!$conexao->query(file_get_contents(__DIR__ . '/32.medidas.sql'))) {
            throw new RuntimeException('medidas_schema');
        }
    }
    $campos = ['peso' => 600, 'gordura_percentual' => 100, 'massa_gordura' => 600,
        'massa_muscular' => 600, 'abdomen' => 400, 'biceps_direito' => 150, 'biceps_esquerdo' => 150];
    if ($metodo === 'POST') {
        $dados = json_decode(file_get_contents('php://input'), true);
        if (!is_array($dados)) medidas_resposta(422, ['erro' => 'Registro inválido.']);
        $data = $dados['data_medicao'] ?? '';
        $dia = is_string($data) ? DateTimeImmutable::createFromFormat('!Y-m-d', $data) : false;
        $hoje = (new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d');
        if (!$dia || $dia->format('Y-m-d') !== $data || $data > $hoje || $data < '1900-01-01') {
            medidas_resposta(422, ['erro' => 'Informe uma data válida, até hoje.']);
        }
        $valores = [];
        foreach ($campos as $campo => $maximo) {
            $valor = $dados[$campo] ?? null;
            if ($valor === null || $valor === '') { $valores[$campo] = null; continue; }
            if (!is_scalar($valor) || is_bool($valor)) medidas_resposta(422, ['erro' => 'Informe apenas números nas medidas.']);
            $valor = str_replace(',', '.', trim((string) $valor));
            if (!preg_match('/^\d+(?:\.\d{1,2})?$/D', $valor) || (float) $valor <= 0 || (float) $valor > $maximo) {
                medidas_resposta(422, ['erro' => 'Confira os valores e use até duas casas decimais.']);
            }
            $valores[$campo] = (float) $valor;
        }
        if (!count(array_filter($valores, function ($v) { return $v !== null; }))) {
            medidas_resposta(422, ['erro' => 'Preencha pelo menos uma medida.']);
        }
        if ($valores['peso'] !== null && (($valores['massa_gordura'] ?? 0) > $valores['peso'] || ($valores['massa_muscular'] ?? 0) > $valores['peso'] || ($valores['massa_gordura'] ?? 0) + ($valores['massa_muscular'] ?? 0) > $valores['peso'])) {
            medidas_resposta(422, ['erro' => 'Confira as massas: juntas, gordura e músculo não podem superar o peso.']);
        }
        $stmt = $conexao->prepare('INSERT INTO treinos_medidas
            (id_usuario, data_medicao, peso, gordura_percentual, massa_gordura, massa_muscular, abdomen, biceps_direito, biceps_esquerdo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE peso=VALUES(peso), gordura_percentual=VALUES(gordura_percentual),
            massa_gordura=VALUES(massa_gordura), massa_muscular=VALUES(massa_muscular), abdomen=VALUES(abdomen),
            biceps_direito=VALUES(biceps_direito), biceps_esquerdo=VALUES(biceps_esquerdo)');
        if (!$stmt) throw new RuntimeException('medidas_prepare');
        $stmt->bind_param('isddddddd', $usuario, $data, $valores['peso'], $valores['gordura_percentual'], $valores['massa_gordura'], $valores['massa_muscular'], $valores['abdomen'], $valores['biceps_direito'], $valores['biceps_esquerdo']);
        if (!$stmt->execute()) throw new RuntimeException('medidas_save');
        $stmt->close();
        medidas_resposta(200, ['ok' => true]);
    }
    if (($_GET['visao'] ?? '') === 'evolucao') {
        $dias = (int) ($_GET['dias'] ?? 90);
        if (!in_array($dias, [30, 90, 365], true)) medidas_resposta(422, ['erro' => 'Período inválido.']);
        $hoje = new DateTimeImmutable('today', new DateTimeZone('America/Sao_Paulo'));
        $inicio = $hoje->modify('-' . ($dias - 1) . ' days')->format('Y-m-d');
        $fim = $hoje->format('Y-m-d');
        $stmt = $conexao->prepare('SELECT data_medicao, peso, abdomen, biceps_direito, biceps_esquerdo FROM treinos_medidas WHERE id_usuario = ? AND data_medicao BETWEEN ? AND ? ORDER BY data_medicao ASC');
        if (!$stmt) throw new RuntimeException('medidas_evolucao');
        $stmt->bind_param('iss', $usuario, $inicio, $fim);
        if (!$stmt->execute()) throw new RuntimeException('medidas_evolucao_read');
        medidas_resposta(200, ['ok' => true, 'registros' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
    }
    $offset = max(0, (int) ($_GET['offset'] ?? 0));
    $stmt = $conexao->prepare('SELECT data_medicao, peso, gordura_percentual, massa_gordura, massa_muscular, abdomen, biceps_direito, biceps_esquerdo FROM treinos_medidas WHERE id_usuario = ? ORDER BY data_medicao DESC LIMIT 21 OFFSET ?');
    if (!$stmt) throw new RuntimeException('medidas_list');
    $stmt->bind_param('ii', $usuario, $offset);
    if (!$stmt->execute()) throw new RuntimeException('medidas_read');
    $registros = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    medidas_resposta(200, ['ok' => true, 'mais' => count($registros) > 20, 'registros' => array_slice($registros, 0, 20)]);
} catch (Throwable $e) {
    error_log('FinControle medidas: ' . $e->getMessage());
    medidas_resposta(500, ['erro' => 'Não foi possível acessar suas medidas. Tente novamente.']);
}
