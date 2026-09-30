(() => {
    'use strict';
    const rows = document.querySelector('[data-history-rows]');
    if (!rows) return;
    const content = document.querySelector('[data-history-content]');
    const empty = document.querySelector('[data-history-empty]');
    const emptyAdd = document.querySelector('[data-history-empty-add]');
    const status = document.querySelector('[data-history-status]');
    const opener = document.querySelector('[data-open-medidas]');
    const dialog = document.querySelector('[data-medidas]');
    const form = dialog.querySelector('[data-medidas-form]');
    const count = document.querySelector('[data-history-count]');
    const first = document.querySelector('[data-history-first]');
    const last = document.querySelector('[data-history-last]');
    const fields = [
        ['peso', 'Peso', 'kg'],
        ['gordura_percentual', 'Gordura', '%'],
        ['massa_gordura', 'Massa gordura', 'kg'],
        ['massa_muscular', 'Massa muscular', 'kg'],
        ['abdomen', 'Abdômen', 'cm'],
        ['biceps_direito', 'Bíceps D', 'cm'],
        ['biceps_esquerdo', 'Bíceps E', 'cm']
    ];
    let records = [];
    let loading = false;
    const dateLabel = value => value.split('-').reverse().join('/');
    const measureLabel = (value, unit) => value === null || value === '' ? '-' : `${Number(value).toLocaleString('pt-BR', {maximumFractionDigits: 2})} ${unit}`;
    const message = (text, error = false) => {
        status.textContent = text;
        status.classList.toggle('is-error', error);
    };
    const request = async (path, options = {}) => {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), 15000);
        try {
            const response = await fetch(path, {credentials: 'same-origin', cache: 'no-store', ...options, signal: controller.signal});
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.erro || 'Não foi possível acessar suas medidas.');
            return data;
        } catch (error) {
            if (error.name === 'AbortError' || error instanceof TypeError || error instanceof SyntaxError) {
                throw new Error('Não foi possível confirmar a operação. Confira sua conexão e tente novamente.');
            }
            throw error;
        } finally { clearTimeout(timer); }
    };
    const openEdit = record => {
        opener.click();
        const registerTab = dialog.querySelector('[data-medidas-tab="registro"]');
        if (registerTab) registerTab.click();
        form.elements.data_original.value = record.data_medicao;
        form.elements.data_medicao.value = record.data_medicao;
        fields.forEach(([key]) => {
            form.elements[key].value = record[key] === null ? '' : String(record[key]).replace('.', ',');
            form.elements[key].removeAttribute('aria-invalid');
        });
        dialog.querySelector('[data-medidas-status]').textContent = 'Edite as medidas e salve para atualizar este registro.';
        dialog.scrollTop = 0;
        form.elements.data_medicao.focus();
    };
    const remove = async (record, button) => {
        if (!window.confirm(`Excluir o lançamento de ${dateLabel(record.data_medicao)}?`)) return;
        button.disabled = true;
        message('Excluindo lançamento...');
        try {
            await request('32.medidas.php', {
                method: 'DELETE',
                headers: {'Content-Type': 'application/json', 'X-CSRF-Token': dialog.dataset.csrf},
                body: JSON.stringify({data_medicao: record.data_medicao})
            });
            document.dispatchEvent(new Event('medidas:salvas'));
            await load();
            message('Lançamento excluído.');
        } catch (error) {
            message(error.message, true);
            button.disabled = false;
        }
    };
    const render = () => {
        rows.replaceChildren();
        count.textContent = String(records.length);
        first.textContent = records.length ? dateLabel(records[records.length - 1].data_medicao) : '-';
        last.textContent = records.length ? dateLabel(records[0].data_medicao) : '-';
        content.hidden = records.length === 0;
        empty.hidden = records.length !== 0;
        records.forEach(record => {
            const row = document.createElement('tr');
            const date = document.createElement('td');
            date.dataset.label = 'Data';
            date.textContent = dateLabel(record.data_medicao);
            row.append(date);
            fields.forEach(([key, label, unit]) => {
                const cell = document.createElement('td');
                cell.dataset.label = label;
                cell.textContent = measureLabel(record[key], unit);
                if (record[key] === null || record[key] === '') cell.className = 'empty-value';
                row.append(cell);
            });
            const actionsCell = document.createElement('td');
            actionsCell.className = 'actions-cell';
            actionsCell.dataset.label = 'Ações';
            const actions = document.createElement('div');
            actions.className = 'row-actions';
            const edit = document.createElement('button');
            edit.type = 'button';
            edit.className = 'row-action';
            edit.title = `Editar lançamento de ${date.textContent}`;
            edit.setAttribute('aria-label', edit.title);
            edit.innerHTML = '<i class="fa-solid fa-pen" aria-hidden="true"></i>';
            edit.addEventListener('click', () => openEdit(record));
            const del = document.createElement('button');
            del.type = 'button';
            del.className = 'row-action delete';
            del.title = `Excluir lançamento de ${date.textContent}`;
            del.setAttribute('aria-label', del.title);
            del.innerHTML = '<i class="fa-solid fa-trash" aria-hidden="true"></i>';
            del.addEventListener('click', () => remove(record, del));
            actions.append(edit, del);
            actionsCell.append(actions);
            row.append(actionsCell);
            rows.append(row);
        });
    };
    const load = async () => {
        if (loading) return;
        loading = true;
        message('Carregando medidas...');
        try {
            const all = [];
            let more = true;
            while (more && all.length < 1000) {
                const result = await request(`32.medidas.php?visao=historico&offset=${all.length}`);
                all.push(...result.registros);
                more = result.mais;
            }
            records = all;
            render();
            message(records.length ? '' : '');
        } catch (error) {
            message(error.message, true);
        } finally { loading = false; }
    };
    emptyAdd.addEventListener('click', () => opener.click());
    document.addEventListener('medidas:salvas', load);
    document.querySelectorAll('[data-fc-open-mobile-menu]').forEach(button => button.addEventListener('click', event => {
        event.preventDefault();
        document.querySelector('[data-fc-mobile-menu]').hidden = false;
    }));
    document.querySelectorAll('[data-fc-close-mobile-menu]').forEach(button => button.addEventListener('click', () => {
        document.querySelector('[data-fc-mobile-menu]').hidden = true;
    }));
    const menu = document.querySelector('[data-fc-mobile-menu]');
    menu.addEventListener('click', event => { if (event.target === menu) menu.hidden = true; });
    load();
})();
