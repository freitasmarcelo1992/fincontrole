<?php
if (empty($_SESSION['id_usuario'])) { return; }
$medidas_hoje = (new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo')))->format('Y-m-d');
?>
<dialog class="medidas-dialog" data-medidas aria-labelledby="medidas-title" data-csrf="<?= h($_SESSION['medidas_csrf']); ?>">
    <header class="medidas-head">
        <h2 id="medidas-title">Minhas medidas</h2>
        <button type="button" data-close-medidas aria-label="Fechar" title="Fechar"><i class="fa-solid fa-xmark"></i></button>
    </header>
    <div class="medidas-tabs" role="tablist" aria-label="Medidas">
        <button type="button" role="tab" id="medidas-tab-registro" aria-controls="medidas-registro" aria-selected="true" data-medidas-tab="registro">Registrar</button>
        <button type="button" role="tab" id="medidas-tab-historico" aria-controls="medidas-historico" aria-selected="false" tabindex="-1" data-medidas-tab="historico">Histórico</button>
    </div>
    <p class="medidas-status" role="status" aria-live="polite" data-medidas-status></p>
    <section id="medidas-registro" role="tabpanel" aria-labelledby="medidas-tab-registro">
        <form data-medidas-form>
            <div class="medidas-grid">
                <label>Data<input name="data_medicao" type="date" required max="<?= $medidas_hoje; ?>" value="<?= $medidas_hoje; ?>"></label>
                <label>Peso (kg)<input name="peso" inputmode="decimal" type="text" placeholder="Ex.: 75,5"></label>
            </div>
            <fieldset><legend>Bioimpedância <button type="button" class="medidas-help" data-medidas-help="bio" aria-label="Sobre a bioimpedância" title="Sobre a bioimpedância"><i class="fa-regular fa-circle-question"></i></button></legend>
                <div class="medidas-grid">
                    <label>Gordura corporal (%)<input name="gordura_percentual" inputmode="decimal" type="text" placeholder="Ex.: 22,5"></label>
                    <label>Massa de gordura (kg)<input name="massa_gordura" inputmode="decimal" type="text" placeholder="Ex.: 17"></label>
                    <label class="medidas-wide">Massa muscular esquelética (kg)<input name="massa_muscular" inputmode="decimal" type="text" placeholder="Ex.: 30,2"></label>
                </div>
            </fieldset>
            <fieldset><legend>Circunferências <button type="button" class="medidas-help" data-medidas-help="fita" aria-label="Como medir abdômen e braços" title="Como medir abdômen e braços"><i class="fa-regular fa-circle-question"></i></button></legend>
                <label>Abdômen / cintura (cm)<input name="abdomen" inputmode="decimal" type="text" placeholder="Ex.: 88"></label>
                <div class="medidas-grid">
                    <label>Bíceps direito (cm)<input name="biceps_direito" inputmode="decimal" type="text" placeholder="Ex.: 32"></label>
                    <label>Bíceps esquerdo (cm)<input name="biceps_esquerdo" inputmode="decimal" type="text" placeholder="Ex.: 31,5"></label>
                </div>
            </fieldset>
            <p class="medidas-note">Na mesma data, o novo registro substitui o anterior.</p>
            <button class="primary-btn" type="submit" data-medidas-save><i class="fa-solid fa-check"></i> Salvar medidas</button>
        </form>
    </section>
    <section id="medidas-historico" role="tabpanel" aria-labelledby="medidas-tab-historico" hidden>
        <div data-medidas-history></div>
        <button type="button" class="medidas-more" data-medidas-more hidden>Carregar mais</button>
    </section>
</dialog>
<dialog class="medidas-help-dialog" data-medidas-help-dialog aria-labelledby="medidas-help-title">
    <header class="medidas-head">
        <h2 id="medidas-help-title">Orientações</h2>
        <button type="button" data-close-medidas-help aria-label="Voltar às medidas" title="Voltar às medidas"><i class="fa-solid fa-xmark"></i></button>
    </header>
    <section data-medidas-help-content="bio" hidden>
        <h3>Bioimpedância</h3>
        <p>Preencha os valores do seu laudo. Deixe em branco o que não tiver.</p>
        <p>Massa magra e massa muscular esquelética são medidas diferentes. Use o campo correspondente no laudo.</p>
    </section>
    <section data-medidas-help-content="fita" hidden>
        <h3>Abdômen / cintura</h3>
        <p>Em pé, passe a fita na horizontal logo acima dos ossos do quadril, sobre a pele. Relaxe a barriga e leia após soltar o ar normalmente. A fita deve encostar sem apertar. Use sempre esse mesmo ponto.</p>
        <h3>Bíceps direito e esquerdo</h3>
        <p>Marque o meio entre a ponta do ombro e o cotovelo. Com o braço relaxado ao lado do corpo, passe a fita nesse ponto, sem comprimir a pele. Meça cada lado separadamente, sem contrair o bíceps. Repita sempre na mesma posição.</p>
    </section>
</dialog>
