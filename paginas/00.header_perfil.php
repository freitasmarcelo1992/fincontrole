<?php
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


