# Instrucoes para manutencao do FinControle

## Fonte oficial

- O repositorio GitHub e a fonte oficial do codigo.
- Antes de editar, sincronize a branch `main` com `git pull origin main`.
- Preserve o layout e os fluxos ja validados pelo usuario.
- Nao altere arquivos fora do escopo solicitado.

## Arquivos de ambiente

Nunca criar, substituir, versionar ou incluir em pacotes de publicacao:

- `paginas/09.conexao.php`
- `paginas/00.email.php`
- `paginas/tmp_sessions/`
- `paginas/storage/cache/`
- `paginas/uploads/`
- arquivos `.sql`, `.log`, `.zip`, backups ou credenciais

Esses arquivos permanecem configurados diretamente no computador e no host.

## Entrega de cada atualizacao

Depois de implementar e validar uma solicitacao:

1. Informar objetivamente quais arquivos foram alterados.
2. Criar commit e enviar a alteracao para a branch `main`.
3. Gerar um pacote ZIP contendo somente os arquivos alterados naquela solicitacao.
4. Preservar dentro do ZIP os caminhos usados no host, como `paginas/`, `cron/` e `assets/`.
5. Nomear o pacote como `publicacao-fincontrole-AAAAMMDD-HHMM.zip`.
6. Entregar o ZIP ao usuario para envio manual pelo Gerenciador de Arquivos do cPanel.

Ao orientar a publicacao, indicar a raiz de destino:

`/home2/mar46460/fincontrole.com.br/`

Ao extrair o ZIP nessa raiz, os arquivos devem cair automaticamente em suas pastas corretas.
