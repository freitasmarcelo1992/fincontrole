(() => {
    'use strict';
    const data = JSON.parse(document.getElementById('reports-data').textContent);
    const tabs = [...document.querySelectorAll('[data-report-tab]')];
    const charts = new Map();
    const status = document.querySelector('[data-report-status]');
    const money = value => Number(value || 0).toLocaleString('pt-BR', {style:'currency', currency:'BRL'});
    const compactMoney = value => Math.abs(value) >= 1000 ? `R$ ${(value / 1000).toLocaleString('pt-BR', {maximumFractionDigits:1})} mil` : `R$ ${Number(value).toLocaleString('pt-BR')}`;
    const notify = text => { status.hidden = !text; status.textContent = text; };
    const options = () => ({
        responsive:true, maintainAspectRatio:false, animation:false,
        interaction:{mode:'index', intersect:false},
        plugins:{legend:{position:'bottom',labels:{color:'#c9d6e5',boxWidth:10,font:{size:10},padding:8}},
            tooltip:{callbacks:{label:item => `${item.dataset.label}: ${money(item.parsed.y)}`}}},
        scales:{x:{grid:{display:false},ticks:{color:'#9fb3c8',maxRotation:0,maxTicksLimit:5,font:{size:10}}},
            y:{beginAtZero:true,grid:{color:'rgba(159,179,200,.14)'},ticks:{color:'#9fb3c8',maxTicksLimit:4,font:{size:10},callback:compactMoney}}}
    });
    const build = (id, config) => {
        if (charts.has(id)) { charts.get(id).resize(); return; }
        if (typeof Chart !== 'function') { notify('Não foi possível carregar os gráficos. Reabra a página.'); return; }
        charts.set(id, new Chart(document.getElementById(id), config));
    };
    const line = (id, datasets) => build(id, {type:'line',data:{labels:data.labels.length ? data.labels : ['Sem dados'],datasets:datasets.map(([label, values, color]) => ({label,data:values.length ? values : [0],borderColor:color,backgroundColor:color,borderWidth:2,pointRadius:2,tension:0,fill:false}))},options:options()});
    const bar = (id, label, labels, values, colors) => {
        const config = options();
        config.indexAxis = 'y';
        config.plugins.legend.display = false;
        config.plugins.tooltip.callbacks.label = item => money(item.parsed.x);
        config.scales = {
            x:{beginAtZero:true,grid:{color:'rgba(159,179,200,.14)'},ticks:{color:'#9fb3c8',maxTicksLimit:3,font:{size:10},callback:compactMoney}},
            y:{grid:{display:false},ticks:{color:'#c9d6e5',autoSkip:false,font:{size:10},callback:function(value) { const text = this.getLabelForValue(value); return text.length > 20 ? text.slice(0, 19) + '…' : text; }}}
        };
        build(id,{type:'bar',data:{labels:labels.length ? labels : ['Sem dados'],datasets:[{label,data:values.length ? values : [0],backgroundColor:colors,borderRadius:3,maxBarThickness:22}]},options:config});
    };
    const render = view => {
        if (view === 'resumo') line('report-flow', [['Receitas', data.receitas, '#42d6a4'], ['Despesas', data.despesas, '#fb8a9d']]);
        if (view === 'categorias') {
            bar('report-expenses','Despesas',data.categorias,data.valoresCategorias,['#22c7f2','#fbbf24','#a78bfa','#fb8a9d','#42d6a4']);
            bar('report-income','Receitas',data.categoriasReceitas,data.valoresReceitas,['#42d6a4','#22c7f2','#fbbf24','#a78bfa','#fb8a9d']);
        }
        if (view === 'evolucao') {
            line('report-balance', [['Saldo', data.saldo, '#22c7f2']]);
        }
    };
    tabs.forEach((tab,index) => {
        tab.addEventListener('click', () => {
            tabs.forEach(item => {item.setAttribute('aria-selected',String(item === tab)); item.tabIndex = item === tab ? 0 : -1; document.getElementById(item.getAttribute('aria-controls')).hidden = item !== tab;});
            requestAnimationFrame(() => render(tab.dataset.reportTab));
        });
        tab.addEventListener('keydown', event => {
            if (!['ArrowLeft','ArrowRight','Home','End'].includes(event.key)) return;
            event.preventDefault();
            const next = tabs[event.key === 'Home' ? 0 : event.key === 'End' ? 2 : (index + (event.key === 'ArrowLeft' ? 2 : 1)) % 3];
            next.focus(); next.click();
        });
    });
    ['filter','menu'].forEach(name => {
        const dialog = document.querySelector(`[data-report-${name}-dialog]`);
        document.querySelector(`[data-report-${name}]`).addEventListener('click', () => dialog.showModal());
        dialog.querySelector('[data-close-dialog]').addEventListener('click', () => dialog.close());
    });
    document.querySelector('[data-report-export]').addEventListener('click', () => {
        if (!data.exportacao.length) { notify('Nenhum lançamento no período para exportar.'); return; }
        if (typeof XLSX === 'undefined') { notify('Exportação indisponível. Reabra a página e tente novamente.'); return; }
        const rows = data.exportacao.map(item => ({Tipo:item.tipo,Data:item.data,Categoria:item.categoria || 'Outros',Descricao:item.descricao,Parcela:item.parcela,Valor:Number(item.valor)}));
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, XLSX.utils.json_to_sheet(rows), 'Relatorio');
        XLSX.writeFile(workbook,'Relatorio_Financeiro.xlsx');
        notify('');
    });
    render('resumo');
})();
