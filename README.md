# FinControle

Codigo-fonte do aplicativo FinControle.

## Fluxo de sincronizacao

Antes de iniciar uma alteracao:

```bash
git pull origin main
```

Depois de validar a alteracao:

```bash
git add .
git commit -m "Descreva a alteracao"
git push origin main
```

O GitHub e a referencia da versao atual. O ambiente local deve receber `git pull`
antes de cada trabalho, e a hospedagem deve ser atualizada somente depois que a
versao estiver validada e enviada ao GitHub.

Arquivos de credenciais, sessoes, cache, uploads, bancos e backups nao fazem parte
do repositorio. Eles permanecem configurados separadamente em cada ambiente.
