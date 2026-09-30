(() => {
    'use strict';

    const endpoint = '38.treinos_catalogo.php';
    const historyEndpoint = '31.treinos_historico.php';
    const csrf = document.querySelector('meta[name="treinos-csrf"]')?.content || '';
    const state = { catalogo: [], treinos: [], recomendacao: null, modo: 'musculo', selecionados: new Set(), treinoAtivo: null };
    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));
    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[char]));

    const toast = (message, error = false) => {
        const el = $('[data-app-toast]');
        if (!el) return;
        el.textContent = message;
        el.style.background = error ? '#ffe4e6' : '#ecfeff';
        el.style.color = error ? '#881337' : '#083344';
        el.hidden = false;
        window.clearTimeout(toast.timer);
        toast.timer = window.setTimeout(() => { el.hidden = true; }, 3200);
    };

    const switchTab = (tab) => {
        $$('[data-master-tab]').forEach((button) => {
            const active = button.dataset.masterTab === tab;
            button.classList.toggle('active', active);
            button.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        $$('[data-master-panel]').forEach((panel) => { panel.hidden = panel.dataset.masterPanel !== tab; });
        if (tab === 'montar') renderCatalog();
    };

    const catalogById = () => new Map(state.catalogo.map((item) => [Number(item.id), item]));
    const suggestedSeries = (item, objective = $('[data-builder-objective]')?.value || 'hipertrofia') => {
        if (item.musculo === 'Cardio') return objective === 'condicionamento' ? '20-35 min' : item.series;
        if (objective === 'forca') return item.composto ? '4 x 4-6' : '3 x 8-10';
        if (objective === 'condicionamento') return '3 x 15-20';
        return item.series;
    };

    const renderTargets = (preferred = '') => {
        const key = state.modo === 'musculo' ? 'musculo' : 'regiao';
        const values = [...new Set(state.catalogo.map((item) => item[key]))].sort((a, b) => a.localeCompare(b, 'pt-BR'));
        const select = $('[data-builder-target]');
        if (!select) return;
        select.innerHTML = values.map((value) => `<option value="${escapeHtml(value)}">${escapeHtml(value)}</option>`).join('');
        if (preferred && values.includes(preferred)) select.value = preferred;
        $('[data-target-label]').textContent = state.modo === 'musculo' ? 'Músculo' : 'Região';
        updateBuilderHeading();
        renderCatalog();
    };

    const updateBuilderHeading = () => {
        const target = $('[data-builder-target]')?.value || '';
        const title = $('[data-builder-title]');
        const copy = $('[data-builder-copy]');
        if (title) title.textContent = target ? `Treino de ${target}` : 'Escolha um foco';
        if (copy) copy.textContent = target
            ? `Selecione manualmente ou gere uma composição para ${target.toLowerCase()}.`
            : 'Selecione o foco para receber uma composição equilibrada.';
    };

    const filteredCatalog = () => {
        const target = $('[data-builder-target]')?.value || '';
        const search = ($('[data-exercise-search]')?.value || '').trim().toLocaleLowerCase('pt-BR');
        const key = state.modo === 'musculo' ? 'musculo' : 'regiao';
        return state.catalogo.filter((item) => {
            const matchesTarget = !target || item[key] === target;
            const haystack = `${item.nome} ${item.musculo} ${item.equipamento}`.toLocaleLowerCase('pt-BR');
            return matchesTarget && (!search || haystack.includes(search));
        });
    };

    const renderCatalog = () => {
        const root = $('[data-exercise-catalog]');
        if (!root || $('[data-master-panel="montar"]')?.hidden) return;
        const items = filteredCatalog();
        root.innerHTML = items.length ? items.map((item) => {
            const selected = state.selecionados.has(Number(item.id));
            const errors = (item.erros || []).map((error) => `<li>${escapeHtml(error)}</li>`).join('');
            return `<article class="exercise-item${selected ? ' selected' : ''}" data-exercise-id="${item.id}">
                <div class="exercise-main">
                    <button class="exercise-select" type="button" data-toggle-exercise="${item.id}" aria-pressed="${selected}" aria-label="${selected ? 'Remover' : 'Adicionar'} ${escapeHtml(item.nome)}"><i class="fa-solid fa-${selected ? 'check' : 'plus'}"></i></button>
                    <div class="exercise-copy"><strong>${escapeHtml(item.nome)}</strong><small>${escapeHtml(item.musculo)} · ${escapeHtml(item.equipamento)} · ${escapeHtml(suggestedSeries(item))}</small></div>
                    ${item.composto ? '<span title="Exercício composto"><i class="fa-solid fa-layer-group"></i></span>' : ''}
                </div>
                <details><summary>Como fazer e erros comuns</summary><div class="exercise-guide"><h4>Mini tutorial</h4><p>${escapeHtml(item.tutorial)}</p><h4>Evite</h4><ul>${errors}</ul><a class="exercise-video" href="${escapeHtml(item.video)}" target="_blank" rel="noopener"><i class="fa-brands fa-youtube"></i> Ver demonstração</a></div></details>
            </article>`;
        }).join('') : '<div class="empty-state"><strong>Nenhum exercício encontrado</strong><p>Ajuste o foco ou a busca.</p></div>';
        updateSelectedCount();
    };

    const updateSelectedCount = () => {
        const count = state.selecionados.size;
        $('[data-selected-count]').textContent = String(count);
        const save = $('[data-save-workout]');
        if (save) save.disabled = count < 3 || count > 12;
    };

    const balancedSelection = (items, limit) => {
        const sorted = [...items].sort((a, b) => Number(b.composto) - Number(a.composto) || a.nome.localeCompare(b.nome, 'pt-BR'));
        if (state.modo === 'musculo') return sorted.slice(0, limit);
        const groups = new Map();
        sorted.forEach((item) => {
            if (!groups.has(item.musculo)) groups.set(item.musculo, []);
            groups.get(item.musculo).push(item);
        });
        const result = [];
        while (result.length < limit && [...groups.values()].some((group) => group.length)) {
            groups.forEach((group) => { if (group.length && result.length < limit) result.push(group.shift()); });
        }
        return result;
    };

    const generateWorkout = () => {
        const target = $('[data-builder-target]')?.value || '';
        if (!target) return;
        const duration = Number($('[data-builder-duration]')?.value || 45);
        const objective = $('[data-builder-objective]')?.value || 'hipertrofia';
        const limits = objective === 'forca' ? { 30: 3, 45: 5, 60: 6 } : objective === 'condicionamento' ? { 30: 5, 45: 7, 60: 9 } : { 30: 4, 45: 6, 60: 8 };
        const limit = limits[duration] || 6;
        const key = state.modo === 'musculo' ? 'musculo' : 'regiao';
        const candidates = state.catalogo.filter((item) => item[key] === target);
        const chosen = balancedSelection(candidates, Math.min(limit, candidates.length));
        state.selecionados = new Set(chosen.map((item) => Number(item.id)));
        const name = $('[data-workout-name]');
        if (name && !name.value.trim()) name.value = `${target} ${duration} min`;
        renderCatalog();
        toast(`${chosen.length} exercícios selecionados para ${target}.`);
    };

    const renderSaved = () => {
        const root = $('[data-saved-list]');
        const empty = $('[data-saved-empty]');
        const status = $('[data-master-status]');
        if (status) status.textContent = '';
        if (!root || !empty) return;
        empty.hidden = state.treinos.length > 0;
        root.innerHTML = state.treinos.map((treino) => `<article class="saved-row">
            <span class="ready-icon"><i class="fa-solid fa-clipboard-check"></i></span>
            <span><strong>${escapeHtml(treino.nome)}</strong><small>${escapeHtml(treino.criterio_valor)} · ${escapeHtml(treino.objetivo)} · ${treino.duracao} min · ${treino.exercicios.length} exercícios</small></span>
            <div class="saved-actions"><button class="start" type="button" data-start-custom="${treino.id}"><i class="fa-solid fa-play"></i> Iniciar</button><button class="delete" type="button" data-delete-custom="${treino.id}" aria-label="Excluir ${escapeHtml(treino.nome)}"><i class="fa-solid fa-trash"></i></button></div>
        </article>`).join('');
    };

    const renderSuggestion = () => {
        if (!state.recomendacao) return;
        $('[data-smart-muscle]').textContent = state.recomendacao.musculo;
        $('[data-smart-reason]').textContent = state.recomendacao.motivo;
        $('[data-smart-suggestion]').hidden = false;
    };

    const load = async () => {
        const status = $('[data-master-status]');
        try {
            const response = await fetch(endpoint, { credentials: 'same-origin', cache: 'no-store' });
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.erro || 'Não foi possível carregar a área de treinos.');
            state.catalogo = Array.isArray(data.catalogo) ? data.catalogo : [];
            state.treinos = Array.isArray(data.treinos) ? data.treinos : [];
            state.recomendacao = data.recomendacao || null;
            $('[data-catalog-count]').innerHTML = `<i class="fa-solid fa-dumbbell"></i> ${state.catalogo.length} exercícios`;
            renderTargets();
            renderSaved();
            renderSuggestion();
        } catch (error) {
            if (status) { status.textContent = error.message; status.classList.add('is-error'); }
            $('[data-catalog-count]').innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Catálogo indisponível';
        }
    };

    const saveWorkout = async () => {
        const name = $('[data-workout-name]')?.value.trim() || '';
        if (!name) { toast('Informe um nome para o treino.', true); $('[data-workout-name]')?.focus(); return; }
        const button = $('[data-save-workout]');
        button.disabled = true;
        try {
            const response = await fetch(endpoint, {
                method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body: JSON.stringify({
                    nome: name, objetivo: $('[data-builder-objective]').value, duracao: Number($('[data-builder-duration]').value),
                    criterio_tipo: state.modo, criterio_valor: $('[data-builder-target]').value, exercicios: [...state.selecionados]
                })
            });
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.erro || 'Não foi possível salvar o treino.');
            toast('Treino salvo.');
            state.selecionados.clear();
            $('[data-workout-name]').value = '';
            await load();
            switchTab('selecionar');
        } catch (error) {
            toast(error.message, true);
        } finally {
            updateSelectedCount();
        }
    };

    const deleteWorkout = async (id) => {
        const treino = state.treinos.find((item) => Number(item.id) === Number(id));
        if (!treino || !window.confirm(`Excluir o treino “${treino.nome}”?`)) return;
        try {
            const response = await fetch(endpoint, { method: 'DELETE', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf }, body: JSON.stringify({ id }) });
            const data = await response.json();
            if (!response.ok || !data.ok) throw new Error(data.erro || 'Não foi possível excluir.');
            state.treinos = state.treinos.filter((item) => Number(item.id) !== Number(id));
            renderSaved();
            toast('Treino excluído.');
        } catch (error) { toast(error.message, true); }
    };

    const openRunner = (id) => {
        const treino = state.treinos.find((item) => Number(item.id) === Number(id));
        if (!treino) return;
        const catalog = catalogById();
        const exercises = treino.exercicios.map((exerciseId) => catalog.get(Number(exerciseId))).filter(Boolean);
        state.treinoAtivo = { ...treino, detalhes: exercises };
        $('[data-runner-title]').textContent = treino.nome;
        $('[data-runner-list]').innerHTML = exercises.map((item) => `<label class="runner-item"><input type="checkbox" value="${item.id}"><span><strong>${escapeHtml(item.nome)}</strong><small>${escapeHtml(suggestedSeries(item, treino.objetivo))} · ${escapeHtml(item.musculo)}</small></span></label>`).join('');
        $('[data-runner]').hidden = false;
        document.body.classList.add('no-scroll');
    };

    const closeRunner = () => {
        $('[data-runner]').hidden = true;
        document.body.classList.remove('no-scroll');
        state.treinoAtivo = null;
    };

    const finishCustom = async () => {
        if (!state.treinoAtivo || !window.confirm('Concluir treino?')) return;
        const checked = $$('[data-runner-list] input:checked').map((input) => `personalizado-${state.treinoAtivo.id}-${input.value}`);
        const button = $('[data-finish-custom]');
        button.disabled = true;
        try {
            const response = await fetch(historyEndpoint, {
                method: 'POST', credentials: 'same-origin', cache: 'no-store', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ data: new Date().toISOString().slice(0, 10), plano: `personalizado-${state.treinoAtivo.id}`, dia: 'personalizado', total_exercicios: state.treinoAtivo.detalhes.length, exercicios: checked })
            });
            if (!response.ok) throw new Error('Não foi possível registrar o treino.');
            window.location.href = '30.treinos.php?treino_concluido=1';
        } catch (error) { toast(error.message, true); button.disabled = false; }
    };

    $$('[data-master-tab]').forEach((button) => button.addEventListener('click', () => switchTab(button.dataset.masterTab)));
    $$('[data-go-builder]').forEach((button) => button.addEventListener('click', () => switchTab('montar')));
    $$('[data-builder-mode]').forEach((button) => button.addEventListener('click', () => {
        state.modo = button.dataset.builderMode;
        $$('[data-builder-mode]').forEach((item) => item.classList.toggle('active', item === button));
        state.selecionados.clear();
        renderTargets();
    }));
    $('[data-builder-target]')?.addEventListener('change', () => { state.selecionados.clear(); updateBuilderHeading(); renderCatalog(); });
    $('[data-builder-duration]')?.addEventListener('change', updateBuilderHeading);
    $('[data-builder-objective]')?.addEventListener('change', renderCatalog);
    $('[data-exercise-search]')?.addEventListener('input', renderCatalog);
    $('[data-generate-workout]')?.addEventListener('click', generateWorkout);
    $('[data-save-workout]')?.addEventListener('click', saveWorkout);
    $('[data-use-suggestion]')?.addEventListener('click', () => { switchTab('montar'); state.modo = 'musculo'; $$('[data-builder-mode]').forEach((item) => item.classList.toggle('active', item.dataset.builderMode === 'musculo')); renderTargets(state.recomendacao?.musculo || ''); generateWorkout(); });
    $('[data-close-runner]')?.addEventListener('click', closeRunner);
    $('[data-finish-custom]')?.addEventListener('click', finishCustom);
    $('[data-runner]')?.addEventListener('click', (event) => { if (event.target === $('[data-runner]')) closeRunner(); });
    document.addEventListener('click', (event) => {
        const toggle = event.target.closest('[data-toggle-exercise]');
        if (toggle) {
            const id = Number(toggle.dataset.toggleExercise);
            state.selecionados.has(id) ? state.selecionados.delete(id) : state.selecionados.add(id);
            renderCatalog();
        }
        const start = event.target.closest('[data-start-custom]');
        if (start) openRunner(start.dataset.startCustom);
        const remove = event.target.closest('[data-delete-custom]');
        if (remove) deleteWorkout(remove.dataset.deleteCustom);
    });

    const menu = $('[data-fc-mobile-menu]');
    $$('[data-fc-open-mobile-menu]').forEach((button) => button.addEventListener('click', (event) => { event.preventDefault(); if (menu) menu.hidden = false; }));
    $$('[data-fc-close-mobile-menu]').forEach((button) => button.addEventListener('click', () => { if (menu) menu.hidden = true; }));
    menu?.addEventListener('click', (event) => { if (event.target === menu) menu.hidden = true; });

    load();
})();
