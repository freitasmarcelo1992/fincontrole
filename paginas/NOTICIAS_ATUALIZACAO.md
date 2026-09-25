# Noticias recentes e atualizacao horaria
Extraia o pacote na raiz fincontrole.com.br preservando paginas e cron.
Nao apague storage/cache: ali permanece o historico. O PHP precisa poder gravar.

A tela padrao exibe somente noticias publicadas nas ultimas 72 horas.
Noticias antigas continuam disponiveis na pesquisa CONTEM, de qualquer categoria.
Datas ausentes ou futuras nao sao apresentadas como noticias novas.
As novas categorias sao Carreira e estudos, Treinos e bem-estar e Saude e nutricao.

## Agendamento no cPanel
O codigo local nao cadastra a tarefa na hospedagem. Configure em Trabalhos Cron:
Minuto: */5; Hora, Dia, Mes, Dia da semana: *.
Comando (confirme o executavel PHP com o provedor):
php /home2/mar46460/fincontrole.com.br/cron/atualizar_noticias.php

O cron roda a cada cinco minutos apenas para conferir o vencimento.
Cada fonte com sucesso e consultada novamente a partir da proxima hora.
Fontes com falha ou sem noticias recentes sao tentadas novamente apos dez minutos.
A trava impede coletas simultaneas. A data original das noticias e preservada.
Uma fonte pode nao publicar a cada hora: atualizar a consulta nao garante
novas publicacoes nem uma quantidade minima por categoria.

Sem cron, a consulta ocorre somente quando alguem abre uma pagina de noticias.
A interface se atualiza apos a coleta quando nao houver pesquisa ou filtro ativo.
O log do cron lista a fonte e a quantidade validada.
paginas/storage/cache/noticias_atualizacao.json registra tentativas, sucesso e avisos.
O script cron nao pode ser aberto pelo navegador (403 esperado).
