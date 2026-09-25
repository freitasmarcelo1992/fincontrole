(() => {
    'use strict';
    const dialog = document.querySelector('[data-medidas]');
    if (!dialog) return;
    const opener = document.querySelector('[data-open-medidas]');
    const form = dialog.querySelector('[data-medidas-form]');
    const status = dialog.querySelector('[data-medidas-status]');
    const save = dialog.querySelector('[data-medidas-save]');
    const history = dialog.querySelector('[data-medidas-history]');
    const more = dialog.querySelector('[data-medidas-more]');
    const tabs = [...dialog.querySelectorAll('[data-medidas-tab]')];
    const helpDialog = document.querySelector('[data-medidas-help-dialog]');
    if (helpDialog) {
        dialog.querySelectorAll('[data-medidas-help]').forEach(button => {
            button.addEventListener('click', () => {
                helpDialog.querySelectorAll('[data-medidas-help-content]').forEach(content => {
                    content.hidden = content.dataset.medidasHelpContent !== button.dataset.medidasHelp;
                });
                helpDialog.showModal();
            });
        });
        helpDialog.querySelector('[data-close-medidas-help]').addEventListener('click', () => helpDialog.close());
    }
    const fields = {
        peso: ['Peso', 'kg', 600],
        gordura_percentual: ['Gordura corporal', '%', 100],
        massa_gordura: ['Massa de gordura', 'kg', 600],
        massa_muscular: ['Massa muscular esquelética', 'kg', 600],
        abdomen: ['Abdômen / cintura', 'cm', 400],
        biceps_direito: ['Bíceps direito', 'cm', 150],
        biceps_esquerdo: ['Bíceps esquerdo', 'cm', 150]
    };
    let records = [];
    let loading = false;
    let previousOverflow;
    const message = (text, error = false) => {
        status.textContent = text;
        status.classList.toggle('is-error', error);
    };
    const selectTab = name => {
        tabs.forEach(tab => {
            const selected = tab.dataset.medidasTab === name;
            tab.setAttribute('aria-selected', String(selected));
            tab.tabIndex = selected ? 0 : -1;
            document.getElementById(tab.getAttribute('aria-controls')).hidden = !selected;
        });
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
    const render = () => {
        history.replaceChildren();
        if (!records.length) {
            const empty = document.createElement('p');
            empty.textContent = 'Você ainda não registrou medidas.';
            history.append(empty);
        }
        records.forEach(record => {
            const article = document.createElement('article');
            article.className = 'medidas-entry';
            const header = document.createElement('header');
            const date = document.createElement('strong');
            date.textContent = record.data_medicao.split('-').reverse().join('/');
            const edit = document.createElement('button');
            edit.type = 'button';
            edit.title = `Editar medidas de ${date.textContent}`;
            edit.setAttribute('aria-label', edit.title);
            edit.innerHTML = '<i class="fa-solid fa-pen" aria-hidden="true"></i>';
            edit.addEventListener('click', () => {
                form.elements.data_medicao.value = record.data_medicao;
                Object.keys(fields).forEach(key => {
                    form.elements[key].value = record[key] === null ? '' : String(record[key]).replace('.', ',');
                    form.elements[key].removeAttribute('aria-invalid');
                });
                selectTab('registro');
                message('Edite as medidas e salve para atualizar este registro.');
                dialog.scrollTop = 0;
                form.elements.data_medicao.focus();
            });
            header.append(date, edit);
            const list = document.createElement('dl');
            Object.entries(fields).forEach(([key, [label, unit]]) => {
                if (record[key] === null || record[key] === undefined) return;
                const term = document.createElement('dt');
                const value = document.createElement('dd');
                term.textContent = label;
                value.textContent = `${Number(record[key]).toLocaleString('pt-BR', {maximumFractionDigits: 2})} ${unit}`;
                list.append(term, value);
            });
            article.append(header, list);
            history.append(article);
        });
    };
    const loadHistory = async (append = false, saved = false) => {
        if (loading) return;
        loading = true;
        more.disabled = true;
        message(saved ? 'Medidas salvas. Carregando histórico...' : 'Carregando histórico...');
        try {
            const result = await request(`32.medidas.php?offset=${append ? records.length : 0}`);
            records = append ? records.concat(result.registros) : result.registros;
            more.hidden = !result.mais;
            render();
            message(saved ? 'Medidas salvas.' : '');
        } catch (error) {
            message((saved ? 'Medidas salvas. ' : '') + error.message, true);
        } finally { loading = false; more.disabled = false; }
    };
    opener.addEventListener('click', () => {
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        dialog.showModal();
    });
    dialog.querySelector('[data-close-medidas]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => {
        document.body.style.overflow = previousOverflow;
        opener.focus();
    });
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => {
            if (save.disabled) return;
            selectTab(tab.dataset.medidasTab);
            message('');
            if (tab.dataset.medidasTab === 'historico') loadHistory();
        });
        tab.addEventListener('keydown', event => {
            if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
            event.preventDefault();
            const next = tabs[event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : (index + 1) % tabs.length];
            next.focus();
            next.click();
        });
    });
    more.addEventListener('click', () => loadHistory(true));
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (save.disabled || loading) return;
        const payload = {data_medicao: form.elements.data_medicao.value};
        let count = 0;
        for (const [key, [label, , max]] of Object.entries(fields)) {
            const input = form.elements[key];
            input.removeAttribute('aria-invalid');
            const value = input.value.trim().replace(',', '.');
            if (!value) { payload[key] = null; continue; }
            if (!/^\d+(?:\.\d{1,2})?$/.test(value) || Number(value) <= 0 || Number(value) > max) {
                input.setAttribute('aria-invalid', 'true');
                input.focus();
                message(`Confira ${label.toLowerCase()}: informe um número maior que zero, até ${max}, com até duas casas decimais.`, true);
                return;
            }
            payload[key] = Number(value);
            count++;
        }
        if (!count) { message('Preencha pelo menos uma medida.', true); return; }
        save.disabled = true;
        message('Salvando...');
        try {
            await request('32.medidas.php', {
                method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-Token': dialog.dataset.csrf},
                body: JSON.stringify(payload)
            });
            document.dispatchEvent(new Event('medidas:salvas'));
            form.reset();
            selectTab('historico');
            await loadHistory(false, true);
            dialog.scrollTop = 0;
        } catch (error) { message(error.message, true); }
        finally { save.disabled = false; }
    });
})();
