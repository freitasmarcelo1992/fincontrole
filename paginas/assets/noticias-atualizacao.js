(() => {
    const script = document.currentScript;
    const token = script?.dataset.newsToken;
    const initialRevision = script?.dataset.newsRevision || '';
    if (!token) return;
    let running = false;
    function notice(message, reload = false) {
        let status = document.querySelector('[data-news-update-status]');
        if (!status) {
            status = document.createElement('p');
            status.setAttribute('data-news-update-status', '');
            status.setAttribute('role', 'status');
            document.querySelector('main')?.append(status);
        }
        status.replaceChildren();
        const element = document.createElement(reload ? 'a' : 'span');
        if (reload) element.href = location.href;
        element.textContent = message;
        status.append(element);
    }
    async function update() {
        if (running || document.hidden || !navigator.onLine) return;
        running = true;
        let revision = initialRevision;
        try {
            for (let source = 0; source < 32 && !document.hidden; source++) {
                const response = await fetch('33.noticias_atualizar.php', {
                    method: 'POST', credentials: 'same-origin', cache: 'no-store',
                    headers: {'X-CSRF-Token': token}
                });
                if (!response.ok) throw new Error('update unavailable');
                const data = await response.json();
                revision = data.revisao || revision;
                if (data.status !== 'processando') break;
            }
            if (revision && revision !== initialRevision) {
                const search = document.querySelector('[data-news-query]');
                const filtered = document.querySelector('[data-news-theme].active')?.dataset.newsTheme;
                const menu = document.querySelector('[data-fc-mobile-menu]:not([hidden])');
                if (!document.hidden && !search?.value && (!filtered || filtered === 'todos') && !menu) {
                    location.reload();
                } else {
                    notice('Notícias atualizadas. Ver novidades', true);
                }
            }
        } catch (_) {
            notice('Atualização indisponível no momento. Tentaremos novamente.');
        } finally { running = false; }
    }
    setTimeout(update, 1000);
    setInterval(update, 5 * 60 * 1000);
    document.addEventListener('visibilitychange', update);
    window.addEventListener('online', update);
})();
