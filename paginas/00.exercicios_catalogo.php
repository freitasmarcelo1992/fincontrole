<?php

function fincontrol_exercicios_catalogo(): array
{
    $itens = [
        ['supino-reto', 'Supino reto com barra', 'Superiores', 'Peito', 'Barra e banco', '4 x 8-12', 'Retraia as escápulas, mantenha os pés firmes e desça a barra até próximo ao peito antes de empurrar.', 'Abrir demais os cotovelos; tirar o quadril do banco; quicar a barra no peito.', 1],
        ['supino-halteres', 'Supino reto com halteres', 'Superiores', 'Peito', 'Halteres e banco', '3 x 8-12', 'Apoie toda a coluna, desça os halteres ao lado do peito e suba mantendo os punhos alinhados.', 'Bater os halteres no topo; descer sem controle; perder o apoio dos pés.', 1],
        ['supino-inclinado-maquina', 'Supino inclinado na máquina', 'Superiores', 'Peito', 'Máquina', '3 x 8-12', 'Ajuste o banco para as pegadas ficarem na linha do peito superior e empurre sem elevar os ombros.', 'Banco mal ajustado; encolher os ombros; soltar a carga na volta.', 1],
        ['supino-declinado', 'Supino declinado', 'Superiores', 'Peito', 'Barra e banco', '3 x 8-12', 'Prenda bem as pernas, firme as escápulas e conduza a barra à parte inferior do peito.', 'Amplitude excessiva; punhos dobrados; retirar as costas do banco.', 1],
        ['crucifixo', 'Crucifixo na máquina', 'Superiores', 'Peito', 'Máquina', '3 x 10-15', 'Mantenha o peito aberto e aproxime os braços sem mudar o ângulo dos cotovelos.', 'Estender demais os cotovelos; bater as placas; projetar a cabeça à frente.', 0],
        ['crucifixo-halteres', 'Crucifixo com halteres', 'Superiores', 'Peito', 'Halteres e banco', '3 x 10-15', 'Faça um arco amplo com leve flexão dos cotovelos e pare quando sentir alongamento confortável.', 'Usar carga excessiva; transformar em supino; descer além da mobilidade.', 0],
        ['crossover', 'Crossover na polia', 'Superiores', 'Peito', 'Polia', '3 x 10-15', 'Incline levemente o tronco e una as mãos à frente mantendo tensão contínua no peitoral.', 'Usar impulso; fechar os ombros; deixar as placas descansarem entre repetições.', 0],
        ['flexao-solo', 'Flexão de braços', 'Superiores', 'Peito', 'Peso corporal', '3 x 8-20', 'Alinhe cabeça, quadril e tornozelos; desça o peito entre as mãos e empurre o chão.', 'Quadril cair; cotovelos totalmente abertos; encurtar a amplitude.', 1],

        ['puxada-aberta', 'Puxada aberta na frente', 'Superiores', 'Costas', 'Polia', '4 x 8-12', 'Fixe as pernas, abra o peito e conduza os cotovelos para baixo trazendo a barra ao peito alto.', 'Puxar atrás da nuca; inclinar demais o tronco; usar impulso.', 1],
        ['puxada-neutra', 'Puxada com pegada neutra', 'Superiores', 'Costas', 'Polia', '3 x 8-12', 'Mantenha o tronco estável e puxe o acessório para a parte superior do peito guiando pelos cotovelos.', 'Arredondar as costas; puxar só com os braços; encurtar a volta.', 1],
        ['barra-fixa', 'Barra fixa', 'Superiores', 'Costas', 'Barra fixa', '3 x 5-12', 'Comece com escápulas ativas e eleve o peito em direção à barra sem balançar o corpo.', 'Usar balanço; relaxar os ombros na base; projetar o queixo.', 1],
        ['remada-baixa-triangulo', 'Remada baixa com triângulo', 'Superiores', 'Costas', 'Polia', '3 x 8-12', 'Mantenha coluna neutra e puxe o triângulo ao abdômen aproximando as escápulas.', 'Arredondar as costas; balançar o tronco; elevar os ombros.', 1],
        ['remada-curvada', 'Remada curvada com barra', 'Superiores', 'Costas', 'Barra', '3 x 8-12', 'Incline o tronco com quadril para trás, estabilize a lombar e puxe a barra em direção ao abdômen.', 'Arredondar a lombar; levantar o tronco a cada repetição; puxar ao peito.', 1],
        ['remada-unilateral', 'Remada unilateral com halter', 'Superiores', 'Costas', 'Halter e banco', '3 x 10-12', 'Apoie mão e joelho, mantenha a coluna estável e leve o cotovelo em direção ao quadril.', 'Girar o tronco; elevar o ombro; puxar o halter para cima em vez de para trás.', 1],
        ['remada-maquina-neutra', 'Remada articulada neutra', 'Superiores', 'Costas', 'Máquina', '3 x 8-12', 'Ajuste o apoio do peito e puxe mantendo os ombros baixos e as escápulas controladas.', 'Afastar o peito do apoio; encolher os ombros; soltar a volta.', 1],
        ['pulldown-barra-reta', 'Pulldown com barra reta', 'Superiores', 'Costas', 'Polia', '3 x 10-15', 'Com braços quase estendidos, leve a barra das clavículas até as coxas usando os dorsais.', 'Dobrar demais os cotovelos; balançar o tronco; perder tensão no topo.', 0],
        ['pullover-halter', 'Pullover com halter', 'Superiores', 'Costas', 'Halter e banco', '3 x 10-15', 'Mantenha costelas controladas e leve o halter atrás da cabeça até amplitude confortável.', 'Arquear a lombar; dobrar e estender os cotovelos; descer em excesso.', 0],
        ['face-pull', 'Face pull', 'Superiores', 'Costas', 'Polia e corda', '3 x 12-15', 'Puxe a corda em direção ao rosto, abrindo as mãos e mantendo cotovelos altos.', 'Inclinar o corpo; encolher os ombros; usar carga que reduz a rotação.', 0],

        ['desenvolvimento-maquina', 'Desenvolvimento na máquina', 'Superiores', 'Ombros', 'Máquina', '4 x 8-12', 'Ajuste o banco, mantenha abdômen firme e empurre sem travar os cotovelos.', 'Arquear a lombar; descer além da mobilidade; elevar os ombros.', 1],
        ['desenvolvimento-halteres', 'Desenvolvimento com halteres', 'Superiores', 'Ombros', 'Halteres e banco', '3 x 8-12', 'Com costas apoiadas, empurre os halteres acima da cabeça mantendo antebraços verticais.', 'Bater os halteres; perder apoio lombar; fechar os cotovelos demais.', 1],
        ['arnold-press', 'Desenvolvimento Arnold', 'Superiores', 'Ombros', 'Halteres', '3 x 10-12', 'Inicie com palmas voltadas ao rosto e gire os braços de forma controlada durante a subida.', 'Girar rápido; arquear a lombar; usar amplitude dolorosa.', 0],
        ['elevacao-lateral', 'Elevação lateral', 'Superiores', 'Ombros', 'Halteres', '3 x 12-15', 'Eleve os braços no plano das escápulas até a linha dos ombros com cotovelos levemente flexionados.', 'Usar balanço; elevar acima da linha dos ombros; encolher o pescoço.', 0],
        ['elevacao-lateral-polia', 'Elevação lateral na polia', 'Superiores', 'Ombros', 'Polia', '3 x 12-15', 'Mantenha tensão desde a base e eleve o braço sem inclinar o corpo.', 'Girar o tronco; puxar com o trapézio; perder controle na descida.', 0],
        ['elevacao-frontal', 'Elevação frontal', 'Superiores', 'Ombros', 'Halteres', '3 x 10-15', 'Eleve o peso até a linha dos ombros mantendo costelas e quadril estáveis.', 'Jogar o corpo para trás; subir alto demais; acelerar a descida.', 0],
        ['crucifixo-inverso', 'Crucifixo inverso', 'Superiores', 'Ombros', 'Máquina', '3 x 12-15', 'Apoie o peito e abra os braços conduzindo pelos cotovelos, sem elevar os ombros.', 'Usar carga excessiva; retrair a cabeça; encurtar a amplitude.', 0],
        ['encolhimento-halteres', 'Encolhimento com halteres', 'Superiores', 'Ombros', 'Halteres', '3 x 10-15', 'Eleve os ombros verticalmente, pause no topo e desça com controle.', 'Girar os ombros; projetar a cabeça; flexionar os cotovelos.', 0],

        ['rosca-direta-w', 'Rosca direta com barra W', 'Superiores', 'Bíceps', 'Barra W', '3 x 8-12', 'Mantenha cotovelos próximos ao corpo e flexione os braços sem mover o tronco.', 'Balançar o corpo; abrir os cotovelos; encurtar a descida.', 0],
        ['rosca-alternada', 'Rosca alternada', 'Superiores', 'Bíceps', 'Halteres', '3 x 10-12', 'Suba um halter por vez e gire a palma para cima mantendo o ombro estável.', 'Levar o cotovelo à frente; usar impulso; soltar a descida.', 0],
        ['rosca-martelo', 'Rosca martelo', 'Superiores', 'Bíceps', 'Halteres', '3 x 10-12', 'Use pegada neutra e mova apenas o antebraço, mantendo punhos firmes.', 'Inclinar o tronco; dobrar os punhos; elevar os cotovelos.', 0],
        ['rosca-scott', 'Rosca Scott', 'Superiores', 'Bíceps', 'Banco Scott', '3 x 10-12', 'Apoie todo o braço e flexione sem perder contato com o banco.', 'Hiperestender o cotovelo; levantar o braço do apoio; usar impulso.', 0],
        ['rosca-inclinada', 'Rosca inclinada com halteres', 'Superiores', 'Bíceps', 'Halteres e banco', '3 x 10-12', 'Apoie as costas no banco inclinado e mantenha os braços atrás da linha do tronco.', 'Mover os ombros; abrir os cotovelos; reduzir a amplitude.', 0],
        ['rosca-polia', 'Rosca na polia baixa', 'Superiores', 'Bíceps', 'Polia', '3 x 10-15', 'Fique estável e flexione os cotovelos mantendo tensão contínua no cabo.', 'Inclinar o corpo; descansar no fim; dobrar os punhos.', 0],

        ['triceps-polia-w', 'Tríceps na polia com barra', 'Superiores', 'Tríceps', 'Polia', '3 x 10-15', 'Fixe os cotovelos ao lado do corpo e estenda completamente sem mover os ombros.', 'Abrir os cotovelos; curvar o tronco; usar o peso do corpo.', 0],
        ['triceps-corda', 'Tríceps com corda', 'Superiores', 'Tríceps', 'Polia e corda', '3 x 10-15', 'Estenda os braços e afaste as pontas da corda no fim do movimento.', 'Mover os cotovelos; encolher os ombros; perder controle na volta.', 0],
        ['triceps-frances-corda', 'Tríceps francês na polia', 'Superiores', 'Tríceps', 'Polia e corda', '3 x 10-15', 'Mantenha cotovelos apontados à frente e estenda sem arquear a lombar.', 'Abrir os cotovelos; mover o tronco; encurtar a flexão.', 0],
        ['triceps-testa', 'Tríceps testa', 'Superiores', 'Tríceps', 'Barra W e banco', '3 x 8-12', 'Com braços verticais, flexione apenas os cotovelos levando a barra próximo à testa.', 'Abrir os cotovelos; mover os ombros; usar carga sem controle.', 0],
        ['triceps-banco', 'Mergulho no banco', 'Superiores', 'Tríceps', 'Banco', '3 x 8-15', 'Mantenha o quadril próximo ao banco e desça até uma amplitude confortável para os ombros.', 'Afastar o quadril; descer demais; abrir os cotovelos.', 1],
        ['paralelas', 'Mergulho nas paralelas', 'Superiores', 'Tríceps', 'Paralelas', '3 x 6-12', 'Estabilize as escápulas, desça com controle e empurre sem balançar.', 'Cair entre os ombros; usar impulso; forçar amplitude dolorosa.', 1],

        ['agachamento-livre', 'Agachamento livre', 'Inferiores', 'Quadríceps', 'Barra', '4 x 6-12', 'Trave o tronco, sente o quadril entre os pés e suba empurrando o chão.', 'Joelhos caírem para dentro; perder a lombar neutra; retirar os calcanhares.', 1],
        ['agachamento-smith', 'Agachamento no Smith', 'Inferiores', 'Quadríceps', 'Smith', '4 x 8-12', 'Posicione os pés para manter equilíbrio e desça até amplitude segura com joelhos alinhados.', 'Pés mal posicionados; joelhos para dentro; relaxar no fundo.', 1],
        ['leg-press', 'Leg press 45°', 'Inferiores', 'Quadríceps', 'Máquina', '4 x 8-15', 'Mantenha quadril e lombar apoiados, desça controlando e empurre sem travar os joelhos.', 'Tirar o quadril do banco; fechar os joelhos; descer além da mobilidade.', 1],
        ['hack-squat', 'Agachamento Hack', 'Inferiores', 'Quadríceps', 'Máquina', '3 x 8-12', 'Apoie toda a coluna, posicione os pés e desça mantendo joelhos na direção das pontas.', 'Levantar o calcanhar; travar joelhos; perder contato com o encosto.', 1],
        ['extensora', 'Cadeira extensora', 'Inferiores', 'Quadríceps', 'Máquina', '3 x 10-15', 'Alinhe o joelho ao eixo da máquina e estenda com controle, sem bater as placas.', 'Banco mal ajustado; usar impulso; soltar a carga na descida.', 0],
        ['afundo-smith', 'Afundo no Smith', 'Inferiores', 'Quadríceps', 'Smith', '3 x 8-12 por lado', 'Use base estável e desça em linha vertical mantendo o joelho da frente alinhado.', 'Passo curto; joelho cair para dentro; empurrar com a perna de trás.', 1],
        ['passada-halteres', 'Passada com halteres', 'Inferiores', 'Quadríceps', 'Halteres', '3 x 10 por lado', 'Dê um passo firme, desça o joelho de trás e avance mantendo o tronco estável.', 'Passos estreitos; perder equilíbrio; bater o joelho no chão.', 1],

        ['stiff', 'Stiff', 'Inferiores', 'Posterior de coxa', 'Barra ou halteres', '3 x 8-12', 'Leve o quadril para trás com coluna neutra e desça até sentir alongamento nos posteriores.', 'Arredondar a lombar; agachar; afastar o peso das pernas.', 1],
        ['levantamento-terra-romeno', 'Levantamento terra romeno', 'Inferiores', 'Posterior de coxa', 'Barra', '4 x 6-10', 'Mantenha a barra próxima às pernas e estenda o quadril sem hiperestender a lombar.', 'Barra afastada; costas arredondadas; terminar inclinando para trás.', 1],
        ['flexora', 'Mesa flexora', 'Inferiores', 'Posterior de coxa', 'Máquina', '3 x 10-15', 'Ajuste o rolo acima dos calcanhares e flexione mantendo o quadril apoiado.', 'Levantar o quadril; bater a carga; encurtar a extensão.', 0],
        ['cadeira-flexora', 'Cadeira flexora', 'Inferiores', 'Posterior de coxa', 'Máquina', '3 x 10-15', 'Alinhe os joelhos ao eixo e flexione até amplitude confortável sem sair do encosto.', 'Deslizar no banco; usar impulso; voltar rápido.', 0],
        ['nordico', 'Flexão nórdica', 'Inferiores', 'Posterior de coxa', 'Peso corporal', '3 x 5-10', 'Mantenha quadril estendido e controle a descida usando os posteriores, apoiando as mãos se necessário.', 'Dobrar o quadril; despencar; tentar amplitude além da força atual.', 1],
        ['good-morning', 'Good morning', 'Inferiores', 'Posterior de coxa', 'Barra', '3 x 8-12', 'Com joelhos suaves, leve o quadril para trás mantendo a coluna neutra e a barra estável.', 'Carga excessiva; arredondar as costas; transformar em agachamento.', 1],

        ['elevacao-quadril', 'Elevação de quadril com barra', 'Inferiores', 'Glúteos', 'Barra e banco', '4 x 8-12', 'Apoie as escápulas, suba o quadril e termine com costelas baixas e glúteos contraídos.', 'Hiperestender a lombar; pés longe demais; perder alinhamento dos joelhos.', 1],
        ['ponte-gluteos', 'Ponte de glúteos', 'Inferiores', 'Glúteos', 'Peso corporal', '3 x 12-20', 'Pressione os pés no chão e eleve o quadril até alinhar joelhos, quadril e ombros.', 'Subir pela lombar; afastar os pés; relaxar no topo.', 0],
        ['agachamento-sumo', 'Agachamento sumô', 'Inferiores', 'Glúteos', 'Halter', '3 x 10-15', 'Use base ampla, joelhos na direção dos pés e desça o peso entre as pernas.', 'Joelhos para dentro; curvar as costas; apoiar o peso no chão.', 1],
        ['afundo-bulgaro', 'Agachamento búlgaro', 'Inferiores', 'Glúteos', 'Banco e halteres', '3 x 8-12 por lado', 'Apoie o pé traseiro e desça sobre a perna da frente mantendo base estável.', 'Base curta; empurrar com a perna de trás; perder alinhamento do joelho.', 1],
        ['coice-polia', 'Coice de glúteo na polia', 'Inferiores', 'Glúteos', 'Polia', '3 x 12-15 por lado', 'Estabilize o tronco e estenda o quadril sem girar a pelve ou arquear a lombar.', 'Balançar o corpo; abrir o quadril; usar amplitude lombar.', 0],
        ['abducao-maquina', 'Abdução de quadril na máquina', 'Inferiores', 'Glúteos', 'Máquina', '3 x 15-20', 'Mantenha a pelve estável, abra as pernas e retorne sem deixar as placas baterem.', 'Usar impulso; perder o contato com o banco; fazer amplitude curta.', 0],
        ['step-up', 'Subida no banco', 'Inferiores', 'Glúteos', 'Banco e halteres', '3 x 10 por lado', 'Apoie todo o pé no banco e suba usando a perna de cima sem impulsionar com a de baixo.', 'Banco alto demais; empurrar com o pé de trás; joelho cair para dentro.', 1],

        ['panturrilha-em-pe', 'Panturrilha em pé', 'Inferiores', 'Panturrilhas', 'Máquina', '4 x 10-15', 'Desça os calcanhares até alongar e suba ao máximo, pausando no topo.', 'Quicar; dobrar os joelhos; reduzir a amplitude.', 0],
        ['panturrilha-sentado', 'Panturrilha sentado', 'Inferiores', 'Panturrilhas', 'Máquina', '4 x 12-20', 'Mantenha a ponta dos pés apoiada e mova apenas os tornozelos em amplitude completa.', 'Bater a carga; mover os joelhos; fazer repetições rápidas.', 0],
        ['panturrilha-legpress', 'Panturrilha no leg press', 'Inferiores', 'Panturrilhas', 'Máquina', '4 x 12-20', 'Apoie a ponta dos pés na plataforma e flexione os tornozelos mantendo joelhos estáveis.', 'Deixar os pés escaparem; travar os joelhos; usar movimento curto.', 0],
        ['panturrilha-unilateral', 'Panturrilha unilateral', 'Inferiores', 'Panturrilhas', 'Degrau', '3 x 12-20 por lado', 'Use apoio leve para equilíbrio e execute toda a amplitude com uma perna.', 'Puxar com os braços; inclinar o corpo; acelerar a descida.', 0],

        ['crunch-maquina', 'Abdominal na máquina', 'Centro do corpo', 'Abdômen', 'Máquina', '3 x 12-20', 'Aproxime costelas e quadril usando o abdômen e retorne sem perder tensão.', 'Puxar com os braços; mover o quadril; usar carga excessiva.', 0],
        ['crunch-solo', 'Abdominal curto no solo', 'Centro do corpo', 'Abdômen', 'Colchonete', '3 x 15-25', 'Eleve as escápulas aproximando as costelas da pelve sem puxar a cabeça.', 'Forçar o pescoço; sentar completamente; prender a respiração.', 0],
        ['prancha-alta', 'Prancha alta', 'Centro do corpo', 'Core', 'Peso corporal', '3 x 30-60 s', 'Alinhe ombros, quadril e tornozelos e mantenha abdômen e glúteos contraídos.', 'Quadril cair; elevar demais o quadril; prender a respiração.', 1],
        ['prancha-lateral', 'Prancha lateral', 'Centro do corpo', 'Core', 'Peso corporal', '3 x 20-45 s por lado', 'Empilhe ombro e cotovelo e mantenha o corpo em linha sem girar o tronco.', 'Quadril cair; ombro colapsar; rodar o peito para o chão.', 1],
        ['dead-bug', 'Dead bug', 'Centro do corpo', 'Core', 'Colchonete', '3 x 8-12 por lado', 'Pressione a lombar no solo e estenda braço e perna opostos sem perder o controle.', 'Arquear a lombar; acelerar; mover braços e pernas do mesmo lado.', 0],
        ['bird-dog', 'Bird dog', 'Centro do corpo', 'Core', 'Colchonete', '3 x 10 por lado', 'Em quatro apoios, estenda braço e perna opostos mantendo quadril e tronco imóveis.', 'Girar a pelve; arquear a lombar; elevar demais a perna.', 0],
        ['elevacao-pernas', 'Elevação de pernas', 'Centro do corpo', 'Abdômen', 'Banco ou solo', '3 x 10-15', 'Mantenha a lombar controlada e eleve as pernas usando o abdômen inferior.', 'Arquear a lombar; usar balanço; descer além do controle.', 0],
        ['pallof-press', 'Pallof press', 'Centro do corpo', 'Core', 'Polia ou elástico', '3 x 10-15 por lado', 'Fique lateral à resistência e estenda os braços sem permitir que o tronco gire.', 'Girar o quadril; inclinar o corpo; usar carga que altera a postura.', 0],

        ['esteira', 'Caminhada ou corrida na esteira', 'Condicionamento', 'Cardio', 'Esteira', '15-30 min', 'Aqueça gradualmente, mantenha postura alta e escolha ritmo compatível com sua condição.', 'Começar rápido demais; segurar nas barras; ignorar dor ou tontura.', 1],
        ['bicicleta', 'Bicicleta ergométrica', 'Condicionamento', 'Cardio', 'Bicicleta', '15-30 min', 'Ajuste o banco para leve flexão do joelho e pedale em cadência estável.', 'Banco muito baixo; joelhos abrirem; resistência incompatível.', 1],
        ['eliptico', 'Elíptico', 'Condicionamento', 'Cardio', 'Elíptico', '15-30 min', 'Mantenha os pés apoiados, tronco ereto e movimento contínuo de braços e pernas.', 'Inclinar sobre os apoios; perder o ritmo; usar resistência excessiva.', 1],
        ['remador', 'Remo ergométrico', 'Condicionamento', 'Cardio', 'Remador', '10-25 min', 'Empurre primeiro com as pernas, depois abra o tronco e finalize puxando com os braços.', 'Puxar primeiro com os braços; arredondar as costas; voltar fora de sequência.', 1],
    ];

    $idsYoutube = [
        'supino-reto' => '72UUJVBuT7o', 'supino-inclinado-maquina' => 'acC6hFJuQPU', 'crucifixo' => 'MENdoLpyj7c',
        'rosca-direta-w' => 'iA4RH6zDin0', 'rosca-alternada' => 'a28SbBN_14k', 'rosca-martelo' => '5vPGH1uTtbs',
        'puxada-aberta' => '80_bFmlEvJY', 'remada-baixa-triangulo' => '7lc8Ow4vIwA', 'remada-maquina-neutra' => 'PQ2753RME90',
        'pulldown-barra-reta' => 'Lgr9JqdRp3M', 'triceps-polia-w' => 'zsQZ5R5X7x4', 'triceps-frances-corda' => 'VW6bsCdeITc',
        'leg-press' => 'waAxlYvtCcI', 'agachamento-smith' => '-5N0LThl53U', 'extensora' => 'PzIfB9MiiX8',
        'afundo-smith' => 'DsveOsxNBYE', 'desenvolvimento-maquina' => '1ojKXaGIPeQ', 'elevacao-lateral' => 'Qy-zXcgZHWU',
        'elevacao-frontal' => 'EgtUbeC0qbw', 'crucifixo-inverso' => 'K5T1YBbKEXI', 'crunch-maquina' => 'JoRVN9R8kQo',
        'prancha-alta' => 'WWk3G7XkupU', 'flexora' => 'IXg1PQ_5gmw', 'stiff' => 'ZoUMv0gsgI8',
        'cadeira-flexora' => 'admUZq_tjho', 'elevacao-quadril' => 'cOvGedlKlD4', 'abducao-maquina' => 'BnMobvODy1E',
        'panturrilha-sentado' => 'ciTzpPbR2WY', 'esteira' => 'Nz_vwdgKnrg', 'bicicleta' => 'v_h7lHYsV_U',
    ];

    return array_map(function (array $item) use ($idsYoutube): array {
        [$slug, $nome, $regiao, $musculo, $equipamento, $series, $tutorial, $erros, $composto] = $item;
        $youtubeId = $idsYoutube[$slug] ?? '';
        $video = $youtubeId !== ''
            ? 'https://www.youtube.com/watch?v=' . $youtubeId
            : 'https://www.youtube.com/results?search_query=' . rawurlencode($nome . ' execução correta');

        return compact('slug', 'nome', 'regiao', 'musculo', 'equipamento', 'series', 'tutorial', 'erros', 'composto', 'video');
    }, $itens);
}
