<?php
require_once "00.sessao.php";
require_once "09.conexao.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["id_usuario"])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "erro" => "nao_autenticado"]);
    exit;
}

if (!$conexao) {
    http_response_code(503);
    echo json_encode(["ok" => false, "erro" => "conexao_indisponivel"]);
    exit;
}

$idUsuario = intval($_SESSION["id_usuario"]);

function fincontrol_treinos_historico_schema(mysqli $conexao): bool
{
    $sql = "
        CREATE TABLE IF NOT EXISTS treinos_historico (
            id INT AUTO_INCREMENT PRIMARY KEY,
            id_usuario INT NOT NULL,
            plano VARCHAR(30) NOT NULL,
            dia VARCHAR(20) NOT NULL,
            data_treino DATE NOT NULL,
            total_exercicios INT NOT NULL DEFAULT 0,
            exercicios_concluidos TEXT NULL,
            criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_treinos_historico_usuario_data (id_usuario, data_treino),
            INDEX idx_treinos_historico_usuario_plano (id_usuario, plano)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";

    return (bool) $conexao->query($sql);
}

function fincontrol_treinos_historico_listar(mysqli $conexao, int $idUsuario): array
{
    $stmt = $conexao->prepare("
        SELECT id, plano, dia, data_treino, total_exercicios, exercicios_concluidos, criado_em
        FROM treinos_historico
        WHERE id_usuario = ?
        ORDER BY data_treino ASC, id ASC
        LIMIT 500
    ");

    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $historico = [];

    while ($linha = $resultado->fetch_assoc()) {
        $exercicios = json_decode((string) ($linha["exercicios_concluidos"] ?? "[]"), true);
        $historico[] = [
            "id" => intval($linha["id"]),
            "plano" => (string) $linha["plano"],
            "dia" => (string) $linha["dia"],
            "data" => (string) $linha["data_treino"],
            "total_exercicios" => intval($linha["total_exercicios"]),
            "exercicios" => is_array($exercicios) ? $exercicios : [],
            "criado_em" => (string) $linha["criado_em"],
        ];
    }

    $stmt->close();
    return $historico;
}

function fincontrol_treinos_historico_resumo(array $historico): array
{
    $mesAtual = date("Y-m");
    $treinosMes = array_values(array_filter($historico, function ($item) use ($mesAtual) {
        return substr((string) ($item["data"] ?? ""), 0, 7) === $mesAtual;
    }));
    $diasUnicos = [];

    foreach ($treinosMes as $item) {
        $data = (string) ($item["data"] ?? "");
        if ($data !== "") {
            $diasUnicos[$data] = true;
        }
    }

    $ultimo = $historico ? $historico[count($historico) - 1] : null;

    return [
        "treinos_mes" => count($treinosMes),
        "sequencia" => count($diasUnicos),
        "ultimo" => $ultimo ? strtoupper((string) ($ultimo["dia"] ?? "-")) : "-",
    ];
}

if (!fincontrol_treinos_historico_schema($conexao)) {
    http_response_code(500);
    echo json_encode(["ok" => false, "erro" => "schema_indisponivel"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $payload = json_decode((string) file_get_contents("php://input"), true);
    if (!is_array($payload)) {
        $payload = $_POST;
    }

    $plano = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($payload["plano"] ?? "padrao"));
    $dia = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($payload["dia"] ?? "treino"));
    $data = (string) ($payload["data"] ?? date("Y-m-d"));
    $totalExercicios = max(0, intval($payload["total_exercicios"] ?? 0));
    $exercicios = $payload["exercicios"] ?? [];

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        $data = date("Y-m-d");
    }

    if (!is_array($exercicios)) {
        $exercicios = [];
    }

    $exerciciosJson = json_encode(array_values($exercicios), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($exerciciosJson === false) {
        $exerciciosJson = "[]";
    }

    $stmt = $conexao->prepare("
        INSERT INTO treinos_historico
            (id_usuario, plano, dia, data_treino, total_exercicios, exercicios_concluidos)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["ok" => false, "erro" => "prepare_indisponivel"]);
        exit;
    }

    $stmt->bind_param("isssis", $idUsuario, $plano, $dia, $data, $totalExercicios, $exerciciosJson);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        http_response_code(500);
        echo json_encode(["ok" => false, "erro" => "registro_indisponivel"]);
        exit;
    }
}

$historico = fincontrol_treinos_historico_listar($conexao, $idUsuario);
echo json_encode([
    "ok" => true,
    "historico" => $historico,
    "resumo" => fincontrol_treinos_historico_resumo($historico),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
