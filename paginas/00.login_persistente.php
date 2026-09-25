<?php

require_once __DIR__ . "/00.sessao.php";

const FINCONTROL_REMEMBER_COOKIE = "FINCONTROLREMEMBER";
const FINCONTROL_REMEMBER_DAYS = 3650;

function fincontrol_login_persistente_tabela($conexao) {
    if (!$conexao) {
        return false;
    }

    $sql = "CREATE TABLE IF NOT EXISTS login_persistente (
        id_token bigint unsigned NOT NULL AUTO_INCREMENT,
        id_usuario int NOT NULL,
        seletor char(32) NOT NULL,
        token_hash char(64) NOT NULL,
        expira_em datetime NOT NULL,
        ultimo_uso datetime DEFAULT NULL,
        criado_em timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_token),
        UNIQUE KEY uk_login_persistente_seletor (seletor),
        KEY idx_login_persistente_usuario (id_usuario),
        KEY idx_login_persistente_expira (expira_em)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    return (bool) $conexao->query($sql);
}

function fincontrol_login_cookie_config($expira) {
    return [
        "expires" => $expira,
        "path" => "/",
        "domain" => "",
        "secure" => fincontrol_https_ativo(),
        "httponly" => true,
        "samesite" => "Lax",
    ];
}

function fincontrol_login_cookie_definir($valor, $expira) {
    if (PHP_VERSION_ID >= 70300) {
        return setcookie(FINCONTROL_REMEMBER_COOKIE, $valor, fincontrol_login_cookie_config($expira));
    }

    return setcookie(
        FINCONTROL_REMEMBER_COOKIE,
        $valor,
        $expira,
        "/",
        "",
        fincontrol_https_ativo(),
        true
    );
}

function fincontrol_login_cookie_limpar() {
    fincontrol_login_cookie_definir("", time() - 3600);
    unset($_COOKIE[FINCONTROL_REMEMBER_COOKIE]);
}

function fincontrol_login_preencher_sessao($usuario) {
    fincontrol_regenerar_sessao();
    $_SESSION["id_usuario"] = intval($usuario["id_usuario"]);
    $_SESSION["nome_usuario"] = $usuario["nome_usuario"];
    $_SESSION["email_usuario"] = $usuario["email_usuario"];
    $_SESSION["tipo_usuario"] = fincontrol_email_acesso_completo($usuario["email_usuario"] ?? "")
        ? "admin"
        : ($usuario["tipo_usuario"] ?? "comum");
}

function fincontrol_login_persistente_criar($conexao, $idUsuario) {
    if (!$conexao) {
        return false;
    }

    if (!fincontrol_login_persistente_tabela($conexao)) {
        return false;
    }

    $seletor = bin2hex(random_bytes(16));
    $validador = bin2hex(random_bytes(32));
    $hash = hash("sha256", $validador);
    $expiraTimestamp = time() + (FINCONTROL_REMEMBER_DAYS * 86400);
    $expiraBanco = date("Y-m-d H:i:s", $expiraTimestamp);

    $stmt = $conexao->prepare("INSERT INTO login_persistente (id_usuario, seletor, token_hash, expira_em) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("isss", $idUsuario, $seletor, $hash, $expiraBanco);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        return false;
    }

    fincontrol_login_cookie_definir($seletor . ":" . $validador, $expiraTimestamp);
    $_COOKIE[FINCONTROL_REMEMBER_COOKIE] = $seletor . ":" . $validador;

    return true;
}

function fincontrol_login_persistente_revogar($conexao) {
    $cookie = $_COOKIE[FINCONTROL_REMEMBER_COOKIE] ?? "";
    $partes = explode(":", $cookie, 2);

    if ($conexao && count($partes) === 2 && preg_match('/^[a-f0-9]{32}$/', $partes[0])) {
        $stmt = $conexao->prepare("DELETE FROM login_persistente WHERE seletor = ?");
        if ($stmt) {
            $stmt->bind_param("s", $partes[0]);
            $stmt->execute();
            $stmt->close();
        }
    }

    fincontrol_login_cookie_limpar();
}

function fincontrol_login_persistente_autenticar($conexao) {
    if (!empty($_SESSION["id_usuario"])) {
        return true;
    }

    $cookie = $_COOKIE[FINCONTROL_REMEMBER_COOKIE] ?? "";
    if ($cookie === "" || !$conexao) {
        return false;
    }

    if (!fincontrol_login_persistente_tabela($conexao)) {
        return false;
    }

    $partes = explode(":", $cookie, 2);
    if (
        count($partes) !== 2
        || !preg_match('/^[a-f0-9]{32}$/', $partes[0])
        || !preg_match('/^[a-f0-9]{64}$/', $partes[1])
    ) {
        fincontrol_login_cookie_limpar();
        return false;
    }

    $stmt = $conexao->prepare("SELECT lp.id_token, lp.id_usuario, lp.token_hash, lp.expira_em,
                                     u.nome_usuario, u.email_usuario, u.tipo_usuario
                                FROM login_persistente lp
                                INNER JOIN usuario u ON u.id_usuario = lp.id_usuario
                               WHERE lp.seletor = ?
                               LIMIT 1");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $partes[0]);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $registro = $resultado ? $resultado->fetch_assoc() : null;
    $stmt->close();

    $hashRecebido = hash("sha256", $partes[1]);
    $valido = $registro
        && strtotime($registro["expira_em"]) > time()
        && hash_equals($registro["token_hash"], $hashRecebido);

    if (!$valido) {
        if ($registro) {
            $stmt = $conexao->prepare("DELETE FROM login_persistente WHERE id_token = ?");
            if ($stmt) {
                $stmt->bind_param("i", $registro["id_token"]);
                $stmt->execute();
                $stmt->close();
            }
        }
        fincontrol_login_cookie_limpar();
        return false;
    }

    fincontrol_login_preencher_sessao($registro);

    $stmt = $conexao->prepare("UPDATE login_persistente SET ultimo_uso = NOW() WHERE id_token = ?");
    if ($stmt) {
        $stmt->bind_param("i", $registro["id_token"]);
        $stmt->execute();
        $stmt->close();
    }

    return true;
}

?>
