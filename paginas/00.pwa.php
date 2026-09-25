<?php
if (!defined("FINCONTROL_PWA_RENDERED")) {
    define("FINCONTROL_PWA_RENDERED", true);

    $documentRoot = realpath($_SERVER["DOCUMENT_ROOT"] ?? "");
    $pwaDir = realpath(__DIR__);

    if ($documentRoot && $pwaDir && strpos($pwaDir, $documentRoot) === 0) {
        $fincontrolPwaBase = "/" . trim(str_replace("\\", "/", substr($pwaDir, strlen($documentRoot))), "/");
    } else {
        $fincontrolPwaBase = rtrim(str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? "/paginas/index.php")), "/");
    }

    $fincontrolPwaBase = $fincontrolPwaBase === "/" ? "" : $fincontrolPwaBase;
    ?>
    <link rel="manifest" href="<?php echo htmlspecialchars($fincontrolPwaBase . "/manifest.json"); ?>">
    <meta name="theme-color" content="#061826">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="FinControle">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo htmlspecialchars($fincontrolPwaBase . "/assets/img/icon-192.png"); ?>">
    <link rel="apple-touch-icon" href="<?php echo htmlspecialchars($fincontrolPwaBase . "/assets/img/icon-192.png"); ?>">
    <script>
      window.fincontrolPwaBase = <?php echo json_encode($fincontrolPwaBase, JSON_UNESCAPED_SLASHES); ?>;
      window.fincontrolDeferredInstallPrompt = window.fincontrolDeferredInstallPrompt || null;
      window.fincontrolIsStandalone = function () {
        return window.matchMedia("(display-mode: standalone)").matches ||
          window.navigator.standalone === true ||
          document.referrer.indexOf("android-app://") === 0;
      };
      window.addEventListener("beforeinstallprompt", function (event) {
        event.preventDefault();
        window.fincontrolDeferredInstallPrompt = event;
        window.dispatchEvent(new CustomEvent("fincontrol:pwa-ready"));
      });
      window.addEventListener("appinstalled", function () {
        window.fincontrolDeferredInstallPrompt = null;
        window.dispatchEvent(new CustomEvent("fincontrol:pwa-installed"));
      });
      window.fincontrolInstallApp = async function () {
        if (window.fincontrolIsStandalone()) {
          return { status: "installed" };
        }

        if (!window.fincontrolDeferredInstallPrompt) {
          await new Promise(function (resolve) {
            var resolved = false;
            var finish = function () {
              if (resolved) return;
              resolved = true;
              window.removeEventListener("fincontrol:pwa-ready", finish);
              resolve();
            };
            window.addEventListener("fincontrol:pwa-ready", finish, { once: true });
            setTimeout(finish, 2500);
          });
        }

        if (!window.fincontrolDeferredInstallPrompt) {
          return { status: "unavailable" };
        }

        var promptEvent = window.fincontrolDeferredInstallPrompt;
        window.fincontrolDeferredInstallPrompt = null;
        promptEvent.prompt();
        var choice = await promptEvent.userChoice.catch(function () {
          return { outcome: "dismissed" };
        });
        return { status: choice.outcome || "dismissed" };
      };
      if ("serviceWorker" in navigator) {
        window.addEventListener("load", function () {
          navigator.serviceWorker.register(window.fincontrolPwaBase + "/service-worker.js?v=20260904-rotas-filtros", {
            scope: window.fincontrolPwaBase + "/"
          }).then(function (registration) {
            registration.update();
          }).catch(function () {});
        });
      }
    </script>
    <?php
}
?>
