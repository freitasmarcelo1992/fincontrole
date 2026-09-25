<?php

const FINCONTROL_VERSION = "1.9.1";
const FINCONTROL_VERSION_NAME = "Marca no Dashboard PWA";
const FINCONTROL_VERSION_DATE = "2026-06-18";

function fincontrol_version_label() {
    return "FinControle v" . FINCONTROL_VERSION;
}

function fincontrol_version_full_label() {
    return fincontrol_version_label() . " - " . FINCONTROL_VERSION_NAME;
}

function fincontrol_version_styles() {
    static $rendered = false;

    if ($rendered) {
        return "";
    }

    $rendered = true;

    return '<style>
.fincontrol-version-badge{position:fixed;right:16px;bottom:14px;z-index:9999;display:inline-flex;align-items:center;gap:7px;padding:7px 10px;border:1px solid rgba(0,119,182,.16);border-radius:999px;background:rgba(255,255,255,.92);box-shadow:0 8px 24px rgba(16,24,40,.12);backdrop-filter:blur(8px);color:#425466;font:700 11px/1.2 Poppins,Arial,sans-serif;letter-spacing:.2px}
.fincontrol-version-badge i{color:#0077b6;font-size:12px}
.fincontrol-version-badge strong{color:#17314f;font-size:11px}
.fincontrol-sidebar-session{margin-top:auto;display:grid;gap:8px;width:100%}
.fincontrol-version-badge.is-sidebar-version{position:static;right:auto;bottom:auto;z-index:auto;width:100%;margin-top:0;padding:12px;border:0;border-radius:8px;background:rgba(255,255,255,.15);box-shadow:none;backdrop-filter:none;color:#fff;font:700 14px/1.2 Poppins,Arial,sans-serif;letter-spacing:0;justify-content:flex-start}
.fincontrol-version-badge.is-sidebar-version i,.fincontrol-version-badge.is-sidebar-version strong{color:#fff;font-size:14px}
@media(max-width:760px){.fincontrol-version-badge{right:10px;bottom:10px;padding:6px 9px;font-size:10px}.fincontrol-version-badge strong{font-size:10px}.fincontrol-sidebar-session{margin-top:auto;display:grid;gap:8px;width:100%}
.fincontrol-version-badge.is-sidebar-version{position:static;padding:12px;font-size:14px}.fincontrol-version-badge.is-sidebar-version strong{font-size:14px}}
@media print{.fincontrol-version-badge{display:none}}
</style>';
}

function fincontrol_version_badge() {
    $label = htmlspecialchars(fincontrol_version_full_label(), ENT_QUOTES, "UTF-8");
    $version = htmlspecialchars(FINCONTROL_VERSION, ENT_QUOTES, "UTF-8");

    return '<div id="fincontrolVersionBadge" class="fincontrol-version-badge" title="' . $label . '" aria-label="' . $label . '"><i class="fa-solid fa-code-branch" aria-hidden="true"></i><span>Vers&atilde;o</span><strong>' . $version . '</strong></div><script>
(function(){
    var badge = document.getElementById("fincontrolVersionBadge");
    if (!badge) return;
    var logoutLinks = document.querySelectorAll("a[href*=\"15.logout.php\"]");
    if (!logoutLinks.length) return;
    var logout = logoutLinks[logoutLinks.length - 1];
    var sidebar = logout.closest(".sidebar, aside");
    var parent = logout.parentElement;
    badge.classList.add("is-sidebar-version");

    if (sidebar && parent === sidebar) {
        var stack = document.createElement("div");
        stack.className = "fincontrol-sidebar-session";
        sidebar.insertBefore(stack, logout);
        stack.appendChild(logout);
        stack.appendChild(badge);
        return;
    }

    if (parent) {
        parent.classList.add("fincontrol-sidebar-session");
    }

    logout.insertAdjacentElement("afterend", badge);
})();
</script>';
}

?>
