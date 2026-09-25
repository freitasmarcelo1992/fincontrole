(() => {
    'use strict';
    const section = document.querySelector('[data-medidas-evolution]');
    if (!section) return;
    const canvas = section.querySelector('[data-evolution-chart]');
    const status = section.querySelector('[data-evolution-status]');
    const summary = section.querySelector('[data-evolution-summary]');
    const period = section.querySelector('[data-evolution-period]');
    const retry = section.querySelector('[data-evolution-retry]');
    const tabs = [...section.querySelectorAll('[data-evolution-metric]')];
    const metrics = {
        peso: {label: 'Peso', unit: 'kg', color: '#22c7f2'},
        abdomen: {label: 'Abdômen', unit: 'cm', color: '#fbbf24'},
        biceps: {label: 'Bíceps (média)', unit: 'cm', color: '#c4b5fd'}
    };
    const number = value => value !== null && value !== '' && Number.isFinite(Number(value)) && Number(value) > 0 ? Number(value) : null;
    const format = value => value.toLocaleString('pt-BR', {maximumFractionDigits: 2});
    const dateLabel = (value, full = false) => new Date(value).toLocaleDateString('pt-BR', {timeZone: 'UTC', day: '2-digit', month: '2-digit', ...(full ? {year: 'numeric'} : {})});
    let metric = 'peso';
    let records = [];
    let chart;
    let controller;
    let ready = false;
    const hideChart = text => {
        if (chart) { chart.destroy(); chart = null; }
        canvas.hidden = true;
        summary.textContent = '';
        status.textContent = text;
        status.hidden = false;
    };
    const render = () => {
        if (!ready) return;
        const config = metrics[metric];
        const points = records.map(record => {
            const right = number(record.biceps_direito);
            const left = number(record.biceps_esquerdo);
            const value = metric === 'biceps' ? (right !== null && left !== null ? (right + left) / 2 : null) : number(record[metric]);
            return {x: Date.parse(record.data_medicao + 'T00:00:00Z'), y: value};
        }).filter(point => point.y !== null && Number.isFinite(point.x)).sort((a, b) => a.x - b.x);
        if (!points.length) {
            hideChart(metric === 'biceps' ? 'Registre os dois braços na mesma data para acompanhar a média neste período.' : `Sem medidas de ${config.label.toLowerCase()} neste período.`);
            return;
        }
        if (typeof Chart !== 'function') { hideChart('Não foi possível carregar o gráfico. Reabra a página.'); return; }
        const first = points[0];
        const last = points[points.length - 1];
        const delta = last.y - first.y;
        summary.textContent = `${format(last.y)} ${config.unit} em ${dateLabel(last.x)} · ` + (points.length === 1 ? 'Primeiro registro' : `${delta > 0 ? '+' : ''}${format(delta)} ${config.unit} no período`);
        status.hidden = true;
        canvas.hidden = false;
        canvas.setAttribute('aria-label', `${config.label} por dia. ${points.map(p => `${dateLabel(p.x, true)}: ${format(p.y)} ${config.unit}`).join('; ')}`);
        if (chart) chart.destroy();
        chart = new Chart(canvas, {
            type: 'line',
            data: {datasets: [{label: config.label, data: points, borderColor: config.color, backgroundColor: config.color, borderWidth: 2, pointRadius: points.length > 60 ? 1 : 3, pointHitRadius: 14, pointHoverRadius: 5, tension: 0, fill: false}]},
            options: {
                responsive: true, maintainAspectRatio: false, animation: false, parsing: false,
                interaction: {mode: 'nearest', axis: 'x', intersect: false},
                plugins: {
                    legend: {display: false},
                    tooltip: {backgroundColor: '#153650', titleColor: '#fff', bodyColor: '#fff', displayColors: false,
                        callbacks: {title: items => dateLabel(items[0].parsed.x, true), label: item => `${format(item.parsed.y)} ${config.unit}`}}
                },
                scales: {
                    x: {type: 'linear', min: first.x - (points.length === 1 ? 86400000 : 0), max: last.x + (points.length === 1 ? 86400000 : 0),
                        afterBuildTicks: scale => {
                            const dates = [...new Set(points.map(point => point.x))];
                            const step = Math.max(1, Math.ceil(dates.length / 4));
                            scale.ticks = dates.filter((_, index) => index % step === 0 || index === dates.length - 1).map(value => ({value}));
                        },
                        ticks: {color: '#9fb3c8', maxRotation: 0, font: {size: 10}, callback: value => dateLabel(value)},
                        grid: {display: false}, border: {display: false}},
                    y: {beginAtZero: false, grace: '10%', ticks: {maxTicksLimit: 4, color: '#9fb3c8', font: {size: 10}, callback: value => `${format(value)} ${config.unit}`},
                        grid: {color: 'rgba(159,179,200,.14)'}, border: {display: false}}
                }
            }
        });
    };
    const load = async () => {
        if (controller) controller.abort();
        const current = new AbortController();
        controller = current;
        const timeout = setTimeout(() => current.abort(), 15000);
        ready = false;
        retry.hidden = true;
        hideChart('Carregando medidas...');
        try {
            const response = await fetch(`32.medidas.php?visao=evolucao&dias=${period.value}`, {credentials: 'same-origin', cache: 'no-store', signal: current.signal});
            const data = await response.json();
            if (!response.ok || !data.ok || !Array.isArray(data.registros)) throw new Error('medidas_evolucao');
            if (controller !== current) return;
            records = data.registros;
            ready = true;
            render();
        } catch (error) {
            if (controller !== current) return;
            hideChart('Não foi possível carregar sua evolução.');
            retry.hidden = false;
        } finally { clearTimeout(timeout); }
    };
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => {
            metric = tab.dataset.evolutionMetric;
            tabs.forEach(item => { item.setAttribute('aria-selected', String(item === tab)); item.tabIndex = item === tab ? 0 : -1; });
            section.querySelector('[role="tabpanel"]').setAttribute('aria-labelledby', tab.id);
            render();
        });
        tab.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const next = tabs[event.key === 'Home' ? 0 : event.key === 'End' ? 2 : (index + (event.key === 'ArrowLeft' ? 2 : 1)) % 3];
            next.focus(); next.click();
        });
    });
    period.addEventListener('change', load);
    retry.addEventListener('click', load);
    document.addEventListener('medidas:salvas', load);
    load();
})();
