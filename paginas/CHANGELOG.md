# Changelog

Todas as mudanças relevantes do FinControle serão documentadas neste arquivo.

O projeto passa a usar Versionamento Semântico a partir da versão `1.0.0`.

Formato: `MAJOR.MINOR.PATCH`

- `MAJOR`: mudanças incompatíveis, migrações estruturais grandes ou quebra de comportamento existente.
- `MINOR`: novas funcionalidades compatíveis com a versão atual.
- `PATCH`: correções, ajustes visuais, segurança pontual e melhorias pequenas sem mudar contratos.

## [1.9.1] - 2026-06-20

### Ajustado
- Cabecalho do Dashboard PWA passa a usar icone de carteira e marca `FinControle` em azul e negrito.
- Cache atualizado para `fincontrole-pwa-v18` e CSS para `responsive.css?v=pwa18`.

## [1.9.0] - 2026-06-20

### Alterado
- Dashboard PWA redesenhado com saldo em destaque e resumo de receita, despesa e gastos diarios.
- Gastos diarios usam a identidade laranja `#e67e22` da pagina correspondente.
- Atalhos de entrada passam a exibir apenas `Receita`, `Despesa` e `Gastos diarios`, sem o termo `Lancar`.
- Atalho superior de relatorio completo removido; acesso permanece no menu inferior.
- Proximos vencimentos passam a ocupar toda a largura do conteudo principal.
- Menu inferior preservado sem alteracoes.
- Cache PWA atualizado para `fincontrole-pwa-v17` e CSS para `responsive.css?v=pwa17`.

## [1.8.2] - 2026-06-19

### Corrigido
- Login passa a carregar explicitamente `00.login_persistente.php` antes de preencher a sessao.
- Corrigido erro fatal `Call to undefined function fincontrol_login_preencher_sessao()` em producao.

## [1.8.1] - 2026-06-19

### Corrigido
- Exclusao individual de despesas e receitas agora valida a linha realmente removida no banco.
- Mensagem de sucesso deixa de ser exibida quando nenhum registro foi excluido.
- Materializacao de despesas recorrentes retirada da abertura da pagina para impedir que itens excluidos sejam recriados.
- Cache do Dashboard continua sendo invalidado antes da exclusao.

## [1.8.0] - 2026-06-18

### Performance
- Verificacoes estruturais removidas das requisicoes de Dashboard, despesas, receitas, gastos, limites, caixinhas e relatorios.
- Migracoes centralizadas e idempotentes em `00.migracoes.php`, executadas pela pagina administrativa `26.migracoes.php`.
- Indices compostos adicionados para filtros por usuario e data.
- Cache HTML do Dashboard por usuario e filtro, com TTL de 5 minutos, escrita atomica e invalidacao nas alteracoes financeiras.
- Chart.js e XLSX passam a usar os arquivos locais existentes.

### Automacoes
- Cron para metricas financeiras materializadas em `usuario_metricas`.
- Cron para lembretes de vencimento e recuperacao de usuarios inativos, sem envios duplicados.
- Backup semanal com retencao dos 7 arquivos mais recentes e fallback PHP quando `mysqldump` estiver indisponivel.
- Registro de eventos de login, receitas, despesas, gastos, caixinhas e assinatura Premium.

### PWA e seguranca
- Tela offline propria adicionada.
- Paginas financeiras autenticadas deixam de ser persistidas no Cache Storage do navegador.
- Cache do service worker atualizado para `fincontrole-pwa-v16`.
- Scripts de Cron protegidos contra acesso web.

### Documentacao
- Passo a passo de publicacao e configuracao do cPanel em `README_PRODUCAO_HOSTGATOR.md`.
- Materializacao automatica de recorrencias adiada ate existir uma chave de origem que impeça duplicidades.
- Backup criado em `_backup_geral/backup-20260618-234237-v1.8.0-infra-saas`.

## [1.7.1] - 2026-06-18

### Ajustado
- Removido o menu sanduiche do topo direito somente no Dashboard PWA.
- Acesso ao menu completo permanece no ultimo item da navegacao inferior.
- Cabecalho, indicadores, atalhos e paineis principais recalibrados conforme as proporcoes do wireframe validado.
- Cache atualizado para `fincontrole-pwa-v15` e CSS para `responsive.css?v=pwa15`.
- Backup criado em `_backup_geral/backup-20260618-224847-v1.7.1-proporcoes-dashboard-pwa`.

## [1.7.0] - 2026-06-18

### Alterado
- Dashboard do PWA reorganizado com base no wireframe mobile.
- Saldo, receitas e despesas passam a formar a primeira linha de indicadores.
- Comece Aqui e Premium ficam lado a lado em atalhos compactos.
- Insights e proximos vencimentos ocupam o espaco principal ate o menu inferior fixo.
- Filtros e paineis secundarios deixam de ocupar a primeira tela do app, sem alteracao na versao web.
- Cache atualizado para `fincontrole-pwa-v13` e CSS do Dashboard para `responsive.css?v=pwa13`.

### Validado
- Layout conferido em 390 x 844, sem rolagem horizontal ou sobreposicao com o menu inferior.
- Backup criado em `_backup_geral/backup-20260618-224120-v1.7.0-dashboard-pwa`.

## [1.6.3] - 2026-06-18

### Adicionado
- Marca FinControle no login da versao app mobile instalada.
- Icone oficial do PWA com 64 px, sombra discreta e nome da marca acima da saudacao.

### Alterado
- Marca fica oculta no navegador comum e aparece apenas em modo standalone.
- Cache do service worker atualizado para `fincontrole-pwa-v12`.
- Backup final criado em `_backup_geral/backup-20260618-221938-v1.6.3-marca-login-app`.
## [1.6.2] - 2026-06-18

### Corrigido
- Icones de e-mail e senha deixam de sobrepor os placeholders no login mobile.
- Recuo esquerdo dos inputs preservado mesmo com a camada responsiva global.
- Icones de Entrar e Home centralizados com dimensoes estaveis.
- CSS atualizado para `responsive.css?v=pwa11` e cache para `fincontrole-pwa-v11`.
- Backup final criado em `_backup_geral/backup-20260618-220917-v1.6.2-icones-login-mobile`.
## [1.6.1] - 2026-06-18

### Corrigido
- Instalacoes antigas do PWA que ainda iniciavam por `index.php` agora sao direcionadas ao login em modo app.
- Navegador comum continua direcionado para a Home publica.
- Usuario com token persistente valido continua sendo direcionado ao Dashboard.
- Cache do service worker atualizado para `fincontrole-pwa-v10`.
- Backup final criado em `_backup_geral/backup-20260618-213744-v1.6.1-correcao-start-login-pwa`.
## [1.6.0] - 2026-06-18

### Adicionado
- Login persistente seguro por token aleatorio e cookie HttpOnly.
- Opcao `Memorizar acesso neste aparelho` na tela de login.
- Tabela `login_persistente` no script incremental do banco.

### Alterado
- PWA passa a abrir em `02.login.php?origem=pwa`.
- Token valido restaura a sessao e redireciona automaticamente ao Dashboard.
- Logout passa a revogar o token persistente do aparelho.
- Cache do service worker atualizado para `fincontrole-pwa-v9`.

### Seguranca
- A senha nunca e gravada no cookie ou no banco alem do hash de senha existente.
- O cookie persistente armazena um seletor e um segredo aleatorio; apenas o hash do segredo fica no banco.

### Validacao
- Fluxo persistente validado ponta a ponta no Apache local.
- Sintaxe PHP, manifest e service worker validados.
- Backup final criado em `_backup_geral/backup-20260618-212934-v1.6.0-login-persistente-app`.
## [1.5.0] - 2026-06-17

### Adicionado
- PWA instalavel com `manifest.json`, `service-worker.js` e icones de aplicativo.
- Popup global para baixar/instalar o aplicativo.
- Menu inferior fixo para a versao app mobile instalada.
- Menu completo acionado pelo botao `Menu` no app.

### Alterado
- Popup de instalacao passa a aparecer de forma confiavel no navegador, sem depender apenas do evento nativo `beforeinstallprompt`.
- Cache do service worker atualizado para `fincontrole-pwa-v8`.
- CSS responsivo versionado para reduzir cache antigo no celular.
- Home e Dashboard ajustados para melhor fluxo mobile/app.

### Operacao
- Sintaxe PHP e service worker validados.
- Popup de instalacao validado visualmente em navegador local.
- Backup final da versao criado em `_backup_geral/backup-20260616-230439-v1.5.0-pwa-mobile-app`.
## [1.4.0] - 2026-06-14

### Adicionado
- Home de lancamento reforcando que existe plano gratuito.
- Nova pagina publica `24.porque_fincontrole.php` comparando o FinControle com planilha Excel.
- CTA na Home para o comparativo `Por que escolher o FinControle?`.

### Alterado
- Comunicacao da Home reposicionada para aquisicao: comecar gratis, plano gratuito e comparativo com Excel.
- Menu principal da Home enxugado para itens de decisao e conversao.
- Bloco Premium passa a deixar claro que o usuario pode iniciar pelo plano gratuito e evoluir quando fizer sentido.
- Pagina `07.relatorios.php` normalizada para remover caracteres corrompidos em textos visiveis.

### Operacao
- Backup criado antes da alteracao em `_backup_geral/backup-20260614-155314-pre-lancamento-home-comparativo`.
- Backup final da versao criado em `_backup_geral/backup-20260614-155849-v1.4.0-lancamento-home-comparativo-final`.
- Backup dos ajustes de lancamento criado em `_backup_geral/backup-20260614-160937-v1.4.0-ajustes-home-caixinhas-relatorio`.

## [1.3.0] - 2026-06-14

### Adicionado
- Nova pagina `23.caixinhas.php` para criar caixinhas de economia.
- Cadastro de plano de economia com nome, objetivo, meta e data alvo.
- Lancamento de entradas economizadas vinculadas a uma caixinha.
- Analise simples de meta x realizado, progresso geral, falta para meta e prioridade de aporte.
- Link `Caixinhas` nos menus laterais principais.

### Banco De Dados
- Criadas as tabelas `caixinhas_economia` e `caixinhas_lancamentos` na migracao incremental `fincontrol_atualizacao_banco.sql`.
- Valores financeiros das caixinhas seguem o padrao criptografado do FinControle.

### Operacao
- Backup criado antes da alteracao em `_backup_geral/backup-20260614-151137-caixinhas-economia`.
- Backup final da versao criado em `_backup_geral/backup-20260614-151732-v1.3.0-caixinhas-economia-final`.

## [1.2.8] - 2026-06-13

### Alterado
- Dashboard removendo visualmente `Ações rápidas` e `Conquistas`.
- `Meta de economia` passa a ocupar toda a largura da seção principal.
- Gráfico `Categorias que mais consomem` alterado de rosca para colunas.

### Operação
- Backup criado antes da alteração em `_backup_geral/backup-20260613-120714-limpeza-dashboard-grafico-colunas`.

## [1.2.7] - 2026-06-12

### Corrigido
- Página Premium passa a consultar a cobrança diretamente na API quando a assinatura estiver pendente.
- Adicionado botão `Atualizar status` para identificar pagamento já realizado mesmo quando o webhook não chegar.
- Pagamentos com status `RECEIVED`, `CONFIRMED` ou `RECEIVED_IN_CASH` ativam automaticamente o Premium.

### Operação
- Backup criado antes da alteração em `_backup_geral/backup-20260612-224529-sync-status-pagamento-premium`.

## [1.2.6] - 2026-06-12

### Alterado
- Formulário de dados de cobrança do Premium passa a abrir em popup somente após o clique em `Assinar Premium`.
- Card lateral da assinatura volta a ficar limpo, exibindo apenas status, plano, valor, ciclo e CTA.

### UX
- Popup informa que os dados são usados somente para gerar a cobrança e confirmar a assinatura.
- Texto deixa claro que os dados de cobrança não ficam armazenados no banco de dados do FinControle.

### Operação
- Backup criado antes da alteração em `_backup_geral/backup-20260612-223319-popup-formulario-premium`.

## [1.2.5] - 2026-06-12

### Adicionado
- Formulário de dados de cobrança na página Premium antes da criação da assinatura.
- Coleta de nome/razão social, CPF/CNPJ, e-mail, celular, CEP, endereço, número, bairro, complemento e forma de pagamento.

### Corrigido
- Cadastro do cliente de pagamento passa a enviar `cpfCnpj`, evitando erro de cobrança sem CPF/CNPJ.

### Operação
- Backup criado antes da alteração em `_backup_geral/backup-20260612-222558-formulario-dados-cobranca-premium`.

## [1.2.4] - 2026-06-12

### Corrigido
- Requisições de pagamento passam a enviar o cabeçalho `User-Agent`, obrigatório para criação de assinatura.

### Operação
- Backup criado antes da alteração em `_backup_geral/backup-20260612-222240-user-agent-pagamentos`.

## [1.2.3] - 2026-06-12

### Corrigido
- Leitura da configuração de pagamento Premium ficou mais tolerante a variações comuns de nome de arquivo e campo da chave.
- Página Premium passa a exibir dica técnica apenas para administradores quando o pagamento online não estiver configurado.

### Operação
- Arquivo preferencial continua sendo `00.asaas.config.php` dentro da pasta `/paginas`.
- Backup criado antes da alteração em `_backup_geral/backup-20260612-221610-ajuste-config-pagamento-premium`.

## [1.2.2] - 2026-06-12

### Corrigido
- Dashboard deixa de quebrar quando o arquivo auxiliar de assinaturas ainda não foi enviado ao host.
- Textos públicos das páginas removem o nome do provedor de pagamentos, mantendo foco em `Plano Premium`, `assinatura` e `pagamento online`.
- Criação de assinatura passa a buscar a primeira cobrança gerada para salvar e exibir o link correto de pagamento.
- Webhook passa a aceitar validação pelo header `asaas-access-token`, mantendo compatibilidade com token por URL.

### Operação
- Backup criado antes da alteração em `_backup_geral/backup-20260612-214610-integracao-pagamentos-premium`.

## [1.2.1] - 2026-06-12

### Adicionado
- Seção de apelo comercial para o Plano Premium na página home.
- Link `Premium` no menu superior da home.
- Chamada contextual para o Plano Premium no dashboard, com variação para usuários com Premium ativo.

### Operação
- Backup criado antes da alteração em `_backup_geral/backup-20260612-212442-apelo-premium-home-dashboard`.

## [1.2.0] - 2026-06-12

### Adicionado
- Integração inicial com Asaas para assinatura Premium.
- Nova página `20.assinar_premium.php` com oferta Premium, escolha de forma de pagamento e criação de assinatura recorrente.
- Novo endpoint `21.asaas_webhook.php` para receber eventos do Asaas e ativar, atrasar ou cancelar assinatura conforme o pagamento.
- Módulos `00.asaas.php`, `00.asaas.config.example.php` e `00.assinaturas.php` para configuração, API e persistência.
- Tabela `assinaturas` incluída na migração incremental `fincontrol_atualizacao_banco.sql`.
- Link `Premium` nos menus laterais das páginas principais.
- Documentação operacional criada em `README_ASAAS_PREMIUM.md`.

### Operação
- Para produção, criar `00.asaas.config.php` com chave da API, ambiente e token do webhook.
- Webhook sugerido: `https://fincontrole.com.br/paginas/21.asaas_webhook.php?token=SEU_TOKEN`.
- Backup criado antes da alteração em `_backup_geral/backup-20260612-205953-asaas-premium`.

## [1.1.8] - 2026-06-12

### Removido
- Cancelada a integração/verificação do Google AdSense.
- Removida a metatag `google-adsense-account` do cabeçalho central.
- Removido o arquivo `ads.txt` local.

### Operação
- Backup criado antes da alteração em `_backup_geral/backup-20260612-205410-remover-adsense`.

### Validado
- Sintaxe PHP de `00.version.php` validada sem erros.
## [1.1.7] - 2026-06-12

### Alterado
- Método de verificação do AdSense alterado para `Metatag`.
- Removido o script `adsbygoogle.js` do cabeçalho de verificação, mantendo apenas `<meta name="google-adsense-account" content="ca-pub-6244151290549727">`.

### Operação
- Backup criado antes da alteração em `_backup_geral/backup-20260612-205202-adsense-metatag`.

### Validado
- Sintaxe PHP de `00.version.php` validada sem erros.
## [1.1.6] - 2026-06-12

### Adicionado
- Metatag `google-adsense-account` para reforçar a verificação do AdSense.
- Arquivo `ads.txt` com o publisher `pub-6244151290549727`.

### Operação
- Backup criado antes da alteração em `_backup_geral/backup-20260612-204702-adsense-verificacao`.
- Cópia local de `ads.txt` criada também na raiz do projeto para facilitar o upload em `/ads.txt`.

### Validado
- Sintaxe PHP de `00.version.php` validada sem erros.
## [1.1.5] - 2026-06-12

### Adicionado
- Script de verificação do Google AdSense no `<head>` das páginas, usando o client `ca-pub-6244151290549727`.

### Operação
- Backup criado antes da alteração em `_backup_geral/backup-20260612-204323-adsense`.

### Validado
- Sintaxe PHP de `00.version.php` validada sem erros.
## [1.1.4] - 2026-06-12

### Alterado
- Dashboard (`03.menu.php`) ajustado para caber em uma única tela em desktop, reduzindo espaçamentos, alturas de cards, ícones e gráficos.
- Layout responsivo preservado para mobile/tablet, onde a rolagem continua adequada para usabilidade.

### Validado
- Sintaxe PHP de `03.menu.php` e `00.version.php` validada sem erros.
## [1.1.3] - 2026-06-12

### Corrigido
- Página de Gastos Diários agora exibe `Sair` no rodapé lateral e recebe a versão logo abaixo, seguindo o padrão das demais páginas.

### Validado
- Sintaxe PHP de `13.gastos_diarios.php` e `00.version.php` validada sem erros.
## [1.1.2] - 2026-06-12

### Alterado
- Padronizado o posicionamento de `Sair` e `Versão` em todas as páginas com menu lateral.
- O componente agora agrupa `Sair` e `Versão` no rodapé da sidebar quando necessário, evitando desalinhamento em layouts com `space-between`.

### Validado
- Sintaxe PHP de `00.version.php` e `00.sessao.php` validada sem erros.
## [1.1.1] - 2026-06-12

### Alterado
- Badge de versão reposicionado para baixo do botão `Sair` quando a página possui menu lateral.
- Visual do badge ajustado para seguir o mesmo estilo dos itens do menu lateral.

### UX
- A versão fica próxima da área de sessão do usuário, facilitando suporte sem competir com o conteúdo principal.

### Validado
- Sintaxe PHP de `00.version.php` validada sem erros.
## [1.1.0] - 2026-06-12

### Adicionado
- Badge visual de versão em todas as páginas com interface do FinControle.
- Componente centralizado em `00.version.php` para exibir a versão atual com estilo consistente e acessível.

### UX
- A versão aparece como um selo discreto e fixo no canto inferior, com baixo impacto visual e fácil conferência em suporte, homologação e produção.

### Validado
- Sintaxe PHP das páginas com interface validada sem erros.
## [1.0.4] - 2026-06-12

### Adicionado
- Soma total no rodapé das tabelas de Receitas e Despesas, respeitando os filtros aplicados.

### Validado
- Sintaxe PHP de `04.despesas.php` e `05.receitas.php` validada sem erros.
## [1.0.3] - 2026-06-12

### Adicionado
- Soma total no rodapé da tabela de Gastos Diários, respeitando os filtros aplicados.

### Validado
- Sintaxe PHP de `13.gastos_diarios.php` validada sem erros.
## [1.0.2] - 2026-06-12

### Corrigido
- Reforçada a entrega de páginas em UTF-8 pelo arquivo comum de sessão.
- Conexão MySQL configurada para `utf8mb4` e `utf8mb4_unicode_ci`.
- `.htaccess` configurado com charset padrão UTF-8.
- Textos visíveis sem acentuação revisados em dashboard, gastos diários, tutorial, primeiro passo e administração do produto.
- Identificadores internos preservados em ASCII para evitar regressões no PHP.

### Validado
- Sintaxe PHP revisada nas páginas alteradas.
## [1.0.1] - 2026-06-12

### Alterado
- Página de Receitas alinhada ao padrão visual e de hierarquia da página de Despesas.
- Adicionadas seções claras para `Lançar nova receita`, `Filtrar receitas lançadas` e `Receitas lançadas`.
- Filtros de receitas passam a ficar junto da lista filtrada, reduzindo confusão entre cadastro e filtro.
- Ações por linha em receitas ajustadas para o mesmo comportamento da pagina de despesas.

### Validado
- Sintaxe PHP de `05.receitas.php` validada sem erros.

## [1.0.0] - 2026-06-11

### Adicionado
- Jornada principal de usuario: cadastro, login, dashboard, receitas, despesas, gastos diarios, orcamento por categoria e relatorios.
- Tela de tutorial para guiar a jornada do usuario.
- Pagina Primeiro Passo para configuracao inicial de renda e cartoes.
- Dashboard com indicadores, insights, proximos vencimentos, atalhos, categorias e conquistas.
- Relatorios com filtros globais, fluxo financeiro, leitura rapida, plano de acao, tabelas e exportacao.
- Orcamento por categoria com comparativo entre planejado e realizado.
- Status de despesas: `A vencer`, `Atrasada` e `Paga`.
- Acoes em massa em receitas e despesas.
- Categorias padronizadas para despesas, gastos diarios e receitas.
- Recuperacao de senha por e-mail configuravel.
- Checklist de lancamento em `README_LANCAMENTO.md`.

### Alterado
- Reorganizada a experiencia em telas de lancamento para separar claramente cadastro, filtro e lista.
- Filtros de despesas e gastos diarios agora ficam junto das listas que filtram.
- Dashboard e relatorios consideram receitas, despesas e gastos diarios.
- Despesas recorrentes passam a gerar linhas mensais para permitir controle de status por mes.
- Layout responsivo revisado para as principais paginas.

### Segurança
- Formularios sensiveis protegidos com CSRF.
- Acoes destrutivas migradas de `GET` para `POST`.
- Configuracao de chave criptografica obrigatoria em producao via `FINCONTROL_CRYPTO_KEY`.
- `.htaccess` criado para bloquear acesso direto a arquivos sensiveis e dumps.

### Banco De Dados
- Criada/atualizada estrutura de orcamentos por categoria.
- Adicionados campos de categoria, status e vinculo com orcamento nas despesas.
- Migração incremental consolidada em `fincontrol_atualizacao_banco.sql`.

### Observacoes
- Esta versão passa a ser a baseline oficial do produto.
- Para atualizar o host com dados reais, usar somente `fincontrol_atualizacao_banco.sql`, nunca importar dump local completo por cima.
