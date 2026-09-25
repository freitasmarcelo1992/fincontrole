<?php

function fincontrol_treinos_rotina_padrao(): array
{
    return [
        'seg' => [
            'dia' => 'SEG',
            'nome' => 'Peito',
            'resumo' => 'Peito, biceps e cardio.',
            'titulo' => 'Peito + biceps',
            'exercicios' => [
                fincontrol_treino_exercicio('Supino reto', '4 x 10', 'peito', 'supino-reto', [
                    'Deite com pes firmes no chao e escapulas encaixadas no banco.',
                    'Desca a barra ate proximo ao peito e suba mantendo controle.',
                ], [
                    'Abrir demais os cotovelos.',
                    'Tirar o quadril do banco.',
                ]),
                fincontrol_treino_exercicio('Supino inclinado maquina', '3 x 10', 'peito superior', 'supino-inclinado-maquina', [
                    'Ajuste o banco para as pegadas ficarem na linha do peito superior.',
                    'Empurre sem tirar as costas do encosto e volte devagar.',
                ], [
                    'Subir os ombros durante o movimento.',
                    'Soltar o peso na volta.',
                ]),
                fincontrol_treino_exercicio('Crucifixo maquina', '3 x 12', 'peito', 'crucifixo', [
                    'Ajuste o banco para as maos ficarem na linha do peito.',
                    'Feche os bracos mantendo o peito aberto e volte controlando.',
                ], [
                    'Esticar demais os cotovelos.',
                    'Bater os pesos no fim do movimento.',
                ]),
                fincontrol_treino_exercicio('Supino na polia alta', '3 x 12', 'peito e controle', 'supino-polia-alta', [
                    'Posicione as polias acima da linha dos ombros e incline levemente o tronco.',
                    'Empurre as alcas para frente sem perder o controle das escapulas.',
                ], [
                    'Usar impulso do corpo.',
                    'Deixar os ombros subirem perto das orelhas.',
                ]),
                fincontrol_treino_exercicio('Rosca direta barra W', '3 x 10', 'biceps', 'rosca-direta-w', [
                    'Segure a barra W com cotovelos proximos ao corpo.',
                    'Suba a barra sem jogar o tronco para tras e desca controlando.',
                ], [
                    'Balancar o corpo para subir a carga.',
                    'Abrir os cotovelos.',
                ]),
                fincontrol_treino_exercicio('Rosca alternada com halteres', '3 x 10', 'biceps', 'rosca-alternada', [
                    'Mantenha postura firme e suba um halter por vez.',
                    'Gire levemente a palma para cima durante a subida.',
                ], [
                    'Usar impulso do ombro.',
                    'Descer o peso sem controle.',
                ]),
                fincontrol_treino_exercicio('Rosca martelo com halteres', '3 x 12', 'biceps e antebraco', 'rosca-martelo', [
                    'Segure os halteres com pegada neutra, como um martelo.',
                    'Suba mantendo os cotovelos parados ao lado do corpo.',
                ], [
                    'Inclinar o tronco para ajudar.',
                    'Subir os cotovelos junto com o peso.',
                ]),
                fincontrol_treino_exercicio('Esteira', '20 min', 'cardio', 'esteira', [
                    'Comece em ritmo confortavel e aumente aos poucos.',
                    'Mantenha postura alta e passadas regulares.',
                ], [
                    'Comecar muito rapido.',
                    'Segurar forte no apoio o tempo todo.',
                ]),
            ],
        ],
        'ter' => [
            'dia' => 'TER',
            'nome' => 'Costas',
            'resumo' => 'Costas, triceps e cardio.',
            'titulo' => 'Costas + triceps',
            'exercicios' => [
                fincontrol_treino_exercicio('Puxada aberta barra reta', '4 x 10', 'dorsais', 'puxada-aberta', [
                    'Ajuste o apoio das pernas e segure a barra mais aberta que os ombros.',
                    'Puxe a barra em direcao ao peito alto, conduzindo o movimento pelos cotovelos.',
                ], [
                    'Inclinar demais o tronco para tras.',
                    'Puxar a barra atras da nuca.',
                ]),
                fincontrol_treino_exercicio('Remada baixa triangulo', '3 x 12', 'meio das costas', 'remada-baixa-triangulo', [
                    'Mantenha peito aberto, coluna neutra e pes firmes.',
                    'Puxe o triangulo ate o abdomen sem perder postura.',
                ], [
                    'Arredondar as costas.',
                    'Usar impulso do tronco.',
                ]),
                fincontrol_treino_exercicio('Remada maquina (neutro)', '3 x 10', 'dorsais e romboides', 'remada-maquina-neutra', [
                    'Ajuste banco e apoio peitoral, segure as pegadas neutras.',
                    'Puxe ate a linha do tronco com controle.',
                ], [
                    'Afastar o peito do apoio.',
                    'Elevar os ombros.',
                ]),
                fincontrol_treino_exercicio('Pulldown barra reta', '3 x 12', 'dorsais', 'pulldown-barra-reta', [
                    'Fique de frente para a polia alta e incline levemente o tronco.',
                    'Desca a barra ate as coxas com bracos quase estendidos.',
                ], [
                    'Dobrar demais os cotovelos e virar triceps.',
                    'Balancar o corpo.',
                ]),
                fincontrol_treino_exercicio('Triceps na polia barra W', '3 x 12', 'triceps', 'triceps-polia-w', [
                    'Cotovelos colados ao corpo.',
                    'Empurre a barra W para baixo ate estender os bracos sem mexer os ombros.',
                ], [
                    'Abrir os cotovelos.',
                    'Inclinar o corpo sobre a barra.',
                ]),
                fincontrol_treino_exercicio('Triceps frances na polia com corda', '3 x 12', 'cabeca longa do triceps', 'triceps-frances-corda', [
                    'De costas para a polia, leve a corda acima da cabeca.',
                    'Estenda os cotovelos para frente mantendo o tronco firme.',
                ], [
                    'Abrir os cotovelos.',
                    'Arquear demais a lombar.',
                ]),
                fincontrol_treino_exercicio('Triceps coice unilateral na polia baixa', '3 x 12 cada lado', 'triceps', 'triceps-coice-polia', [
                    'Use a polia baixa, incline o tronco e mantenha o cotovelo alinhado.',
                    'Estenda o antebraco para tras sem mexer o ombro.',
                ], [
                    'Balancar o tronco.',
                    'Deixar o cotovelo cair.',
                ]),
                fincontrol_treino_exercicio('Bicicleta', '20 min', 'cardio', 'bicicleta', [
                    'Ajuste o banco para manter leve flexao do joelho no ponto mais baixo.',
                    'Pedale em cadencia constante.',
                ], [
                    'Banco muito baixo.',
                    'Joelhos abrindo para os lados.',
                ]),
            ],
        ],
        'qua' => [
            'dia' => 'QUA',
            'nome' => 'Pernas',
            'resumo' => 'Quadriceps, gluteos e aducao.',
            'titulo' => 'Pernas + aducao',
            'exercicios' => [
                fincontrol_treino_exercicio('Leg press 45', '4 x 10', 'quadriceps e gluteos', 'leg-press', [
                    'Ajuste o banco para apoiar bem a lombar e posicione os pes na plataforma.',
                    'Empurre sem travar os joelhos e desca controlando a carga.',
                ], [
                    'Tirar o quadril do banco.',
                    'Fechar os joelhos para dentro.',
                ]),
                fincontrol_treino_exercicio('Agachamento smith', '4 x 10', 'pernas e gluteos', 'agachamento-smith', [
                    'Posicione a barra no alto das costas e mantenha os pes firmes.',
                    'Desca ate uma amplitude segura e suba empurrando o chao.',
                ], [
                    'Deixar os joelhos cairem para dentro.',
                    'Projetar demais o tronco para frente.',
                ]),
                fincontrol_treino_exercicio('Cadeira extensora', '3 x 12', 'quadriceps', 'extensora', [
                    'Ajuste o encosto e alinhe o eixo da maquina com o joelho.',
                    'Estenda as pernas com controle e segure um instante no topo.',
                ], [
                    'Subir com impulso.',
                    'Soltar a carga na descida.',
                ]),
                fincontrol_treino_exercicio('Afundo smith', '3 x 10 cada perna', 'gluteos e quadriceps', 'afundo-smith', [
                    'Posicione um pe a frente e outro atras, mantendo o tronco firme.',
                    'Desca em linha vertical e suba sem perder o equilibrio.',
                ], [
                    'Dar passo curto demais.',
                    'Deixar o joelho da frente cair para dentro.',
                ]),
                fincontrol_treino_exercicio('Panturrilha hack', '4 x 12', 'panturrilhas', 'panturrilha-hack', [
                    'Apoie a ponta dos pes na plataforma e mantenha joelhos levemente flexionados.',
                    'Suba o calcanhar o maximo possivel e desca controlando.',
                ], [
                    'Fazer movimento curto demais.',
                    'Quicar no fim da descida.',
                ]),
                fincontrol_treino_exercicio('Aducao de quadril maquina', '3 x 12', 'adutores', 'aducao-maquina', [
                    'Ajuste a abertura inicial de forma confortavel.',
                    'Feche as pernas contra a resistencia sem tirar o quadril do banco.',
                ], [
                    'Usar impulso.',
                    'Perder contato com o encosto.',
                ]),
            ],
        ],
        'qui' => [
            'dia' => 'QUI',
            'nome' => 'Ombros',
            'resumo' => 'Ombros, abdomen e cardio.',
            'titulo' => 'Ombros + abdomen',
            'exercicios' => [
                fincontrol_treino_exercicio('Desenvolvimento maquina (pegada pronada)', '4 x 10', 'ombros', 'desenvolvimento-maquina', [
                    'Ajuste o banco para as pegadas ficarem na linha dos ombros.',
                    'Empurre para cima sem travar os cotovelos e volte controlando.',
                ], [
                    'Arquear demais a lombar.',
                    'Descer alem da amplitude segura.',
                ]),
                fincontrol_treino_exercicio('Elevacao lateral com halteres', '3 x 12', 'deltoide lateral', 'elevacao-lateral', [
                    'Segure os halteres ao lado do corpo e mantenha leve flexao nos cotovelos.',
                    'Eleve ate a linha dos ombros sem encolher o pescoco.',
                ], [
                    'Usar impulso do tronco.',
                    'Elevar os ombros junto com os bracos.',
                ]),
                fincontrol_treino_exercicio('Elevacao frontal alternada', '3 x 12', 'deltoide anterior', 'elevacao-frontal', [
                    'Eleve um halter por vez ate a linha dos ombros.',
                    'Mantenha o abdomen firme e controle a descida.',
                ], [
                    'Jogar o corpo para tras.',
                    'Subir acima demais da linha dos ombros.',
                ]),
                fincontrol_treino_exercicio('Crucifixo inverso maquina', '3 x 12', 'posterior de ombro', 'crucifixo-inverso', [
                    'Ajuste o banco e mantenha o peito apoiado.',
                    'Abra os bracos conduzindo pelos cotovelos.',
                ], [
                    'Encolher os ombros.',
                    'Usar carga que reduz a amplitude.',
                ]),
                fincontrol_treino_exercicio('Abdominal crunches maquina', '3 x 15', 'abdomen', 'crunch-maquina', [
                    'Ajuste a carga e mantenha o quadril firme no banco.',
                    'Flexione o tronco aproximando costelas e quadril.',
                ], [
                    'Puxar com os bracos.',
                    'Fazer o movimento muito rapido.',
                ]),
                fincontrol_treino_exercicio('Prancha alta', '3 x 30s', 'core', 'prancha-alta', [
                    'Apoie maos no chao, alinhe ombros, quadril e tornozelos.',
                    'Contraia abdomen e gluteos para manter o corpo estavel.',
                ], [
                    'Deixar o quadril cair.',
                    'Elevar demais o quadril.',
                ]),
                fincontrol_treino_exercicio('Esteira', '20 min', 'cardio', 'esteira', [
                    'Comece em ritmo confortavel e aumente aos poucos.',
                    'Mantenha postura alta e passadas regulares.',
                ], [
                    'Segurar demais nas barras.',
                    'Aumentar velocidade antes de aquecer.',
                ]),
            ],
        ],
        'sex' => [
            'dia' => 'SEX',
            'nome' => 'Posterior',
            'resumo' => 'Posterior, gluteos e cardio.',
            'titulo' => 'Posterior + gluteos',
            'exercicios' => [
                fincontrol_treino_exercicio('Mesa flexora', '4 x 10', 'posterior de coxa', 'flexora', [
                    'Ajuste o rolo acima dos calcanhares e mantenha o quadril apoiado.',
                    'Flexione os joelhos com controle e volte sem soltar a carga.',
                ], [
                    'Levantar o quadril.',
                    'Voltar rapido demais.',
                ]),
                fincontrol_treino_exercicio('Stiff', '3 x 10', 'posterior e gluteos', 'stiff', [
                    'Segure a barra ou halteres com coluna neutra e joelhos levemente flexionados.',
                    'Desca levando o quadril para tras e suba contraindo gluteos.',
                ], [
                    'Arredondar a lombar.',
                    'Dobrar demais os joelhos.',
                ]),
                fincontrol_treino_exercicio('Cadeira flexora', '3 x 12', 'posterior de coxa', 'cadeira-flexora', [
                    'Ajuste o encosto e o apoio para alinhar os joelhos ao eixo da maquina.',
                    'Flexione as pernas ate o fim da amplitude segura.',
                ], [
                    'Usar impulso.',
                    'Tirar o quadril do banco.',
                ]),
                fincontrol_treino_exercicio('Elevacao de quadril com barra', '4 x 10', 'gluteos', 'elevacao-quadril', [
                    'Apoie as costas no banco e posicione a barra sobre o quadril com protecao.',
                    'Suba ate alinhar tronco e coxas, contraindo gluteos no topo.',
                ], [
                    'Hiperestender a lombar.',
                    'Deixar os joelhos abrirem ou fecharem demais.',
                ]),
                fincontrol_treino_exercicio('Abducao de quadril com maquina', '3 x 15', 'gluteo medio', 'abducao-maquina', [
                    'Ajuste a maquina e mantenha o tronco firme no encosto.',
                    'Abra as pernas contra a resistencia e volte controlando.',
                ], [
                    'Usar impulso.',
                    'Inclinar demais o tronco.',
                ]),
                fincontrol_treino_exercicio('Panturrilha sentado', '4 x 12', 'panturrilhas', 'panturrilha-sentado', [
                    'Apoie a ponta dos pes e ajuste a almofada sobre as coxas.',
                    'Eleve os calcanhares e desca ate alongar a panturrilha.',
                ], [
                    'Fazer movimento parcial.',
                    'Bater a carga no suporte.',
                ]),
                fincontrol_treino_exercicio('Esteira', '20 min', 'cardio', 'esteira', [
                    'Comece em ritmo confortavel e aumente aos poucos.',
                    'Mantenha postura alta e passadas regulares.',
                ], [
                    'Segurar demais nas barras.',
                    'Aumentar velocidade antes de aquecer.',
                ]),
            ],
        ],
    ];
}

function fincontrol_treinos_rotina_full_body(): array
{
    $padrao = fincontrol_treinos_rotina_padrao();
    $catalogo = [];

    foreach ($padrao as $rotina) {
        foreach (($rotina['exercicios'] ?? []) as $exercicio) {
            $catalogo[$exercicio['tipo']] = $exercicio;
        }
    }

    $pegar = function (string $tipo, string $series = '', string $foco = '') use ($catalogo): array {
        $exercicio = $catalogo[$tipo] ?? null;
        if (!$exercicio) {
            return [];
        }
        if ($series !== '') {
            $exercicio['series'] = $series;
        }
        if ($foco !== '') {
            $exercicio['foco'] = $foco;
        }
        return $exercicio;
    };

    return [
        'fba' => [
            'dia' => 'A',
            'nome' => 'Quad + peito',
            'resumo' => 'Alta demanda, superiores e acessorios.',
            'titulo' => 'Full body A',
            'exercicios' => array_values(array_filter([
                $pegar('leg-press', '3 x 8-12', 'alta demanda: quadriceps e gluteos'),
                $pegar('supino-inclinado-maquina', '3 x 8-12', 'peito'),
                $pegar('puxada-aberta', '3 x 8-12', 'costas'),
                $pegar('flexora', '3 x 10-12', 'posterior de coxa'),
                $pegar('elevacao-lateral', '2 x 12-15', 'ombros'),
                $pegar('rosca-direta-w', '2 x 10-12', 'biceps'),
                $pegar('triceps-frances-corda', '2 x 10-12', 'triceps'),
                $pegar('crunch-maquina', '2-3 x 12-15', 'abdomen'),
            ])),
        ],
        'fbb' => [
            'dia' => 'B',
            'nome' => 'Costas + posterior',
            'resumo' => 'Costas fortes sem sobrecarregar lombar.',
            'titulo' => 'Full body B',
            'exercicios' => array_values(array_filter([
                $pegar('agachamento-smith', '3 x 8-10', 'alta demanda: pernas e gluteos'),
                $pegar('remada-baixa-triangulo', '3 x 8-12', 'costas'),
                $pegar('supino-inclinado-maquina', '3 x 8-12', 'peito superior'),
                $pegar('cadeira-flexora', '3 x 10-12', 'posterior de coxa'),
                $pegar('desenvolvimento-maquina', '2 x 10-12', 'ombros'),
                $pegar('rosca-martelo', '2 x 10-12', 'biceps e antebraco'),
                $pegar('triceps-polia-w', '2 x 10-12', 'triceps'),
                $pegar('prancha-alta', '2-3 x 30-45s', 'core'),
            ])),
        ],
        'fbc' => [
            'dia' => 'C',
            'nome' => 'Pernas + ombros',
            'resumo' => 'Pernas e ombros com energia distribuida.',
            'titulo' => 'Full body C',
            'exercicios' => array_values(array_filter([
                $pegar('leg-press', '3 x 8-12', 'alta demanda: pernas'),
                $pegar('remada-maquina-neutra', '3 x 8-12', 'costas'),
                $pegar('crucifixo', '3 x 10-12', 'peito'),
                $pegar('cadeira-flexora', '3 x 10-12', 'posterior de coxa'),
                $pegar('elevacao-lateral', '3 x 12-15', 'ombros'),
                $pegar('rosca-alternada', '2 x 10-12', 'biceps'),
                $pegar('triceps-coice-polia', '2 x 10-12', 'triceps'),
                $pegar('crunch-maquina', '2-3 x 12-15', 'abdomen'),
            ])),
        ],
    ];
}

function fincontrol_treino_exercicio(string $nome, string $series, string $foco, string $tipo, array $como, array $erros): array
{
    $idsYoutube = [
        'supino-reto' => '72UUJVBuT7o',
        'supino-inclinado-maquina' => 'acC6hFJuQPU',
        'crucifixo' => 'MENdoLpyj7c',
        'supino-polia-alta' => 'AkY2-AxTGKk',
        'rosca-direta-w' => 'iA4RH6zDin0',
        'rosca-alternada' => 'a28SbBN_14k',
        'rosca-martelo' => '5vPGH1uTtbs',
        'esteira' => 'Nz_vwdgKnrg',
        'puxada-aberta' => '80_bFmlEvJY',
        'remada-baixa-triangulo' => '7lc8Ow4vIwA',
        'remada-maquina-neutra' => 'PQ2753RME90',
        'pulldown-barra-reta' => 'Lgr9JqdRp3M',
        'triceps-polia-w' => 'zsQZ5R5X7x4',
        'triceps-frances-corda' => 'VW6bsCdeITc',
        'triceps-coice-polia' => 'Hb4Wh7621iI',
        'bicicleta' => 'v_h7lHYsV_U',
        'leg-press' => 'waAxlYvtCcI',
        'agachamento-smith' => '-5N0LThl53U',
        'extensora' => 'PzIfB9MiiX8',
        'afundo-smith' => 'DsveOsxNBYE',
        'panturrilha-hack' => 'BMHaJ5U6lWM',
        'aducao-maquina' => 'TB4BwvHaK9o',
        'desenvolvimento-maquina' => '1ojKXaGIPeQ',
        'elevacao-lateral' => 'Qy-zXcgZHWU',
        'elevacao-frontal' => 'EgtUbeC0qbw',
        'crucifixo-inverso' => 'K5T1YBbKEXI',
        'crunch-maquina' => 'JoRVN9R8kQo',
        'prancha-alta' => 'WWk3G7XkupU',
        'flexora' => 'IXg1PQ_5gmw',
        'stiff' => 'ZoUMv0gsgI8',
        'cadeira-flexora' => 'admUZq_tjho',
        'elevacao-quadril' => 'cOvGedlKlD4',
        'abducao-maquina' => 'BnMobvODy1E',
        'panturrilha-sentado' => 'ciTzpPbR2WY',
    ];

    $idYoutube = $idsYoutube[$tipo] ?? '';
    $video = $idYoutube !== '' ? 'https://www.youtube.com/watch?v=' . $idYoutube : '';
    $imagem = $idYoutube !== '' ? 'https://img.youtube.com/vi/' . $idYoutube . '/hqdefault.jpg' : '';

    return [
        'nome' => $nome,
        'series' => $series,
        'foco' => $foco,
        'tipo' => $tipo,
        'como' => $como,
        'erros' => $erros,
        'video' => $video,
        'imagem' => $imagem,
    ];
}
