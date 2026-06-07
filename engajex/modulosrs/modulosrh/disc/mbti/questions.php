<?php
$mbti_questions = [
    // Extroversão (E) vs. Introversão (I)
    [
        'id' => 1,
        'question' => 'Quando participo de um evento social, eu geralmente:',
        'options' => [
            'A' => ['text' => 'Gosto de conversar com diversas pessoas, mesmo as que não conheço.', 'type' => 'E'],
            'B' => ['text' => 'Prefiro falar com poucas pessoas de forma mais profunda.', 'type' => 'I']
        ]
    ],
    [
        'id' => 2,
        'question' => 'Em meu tempo livre, prefiro:',
        'options' => [
            'A' => ['text' => 'Sair e fazer atividades com outras pessoas.', 'type' => 'E'],
            'B' => ['text' => 'Ficar em casa com atividades mais tranquilas.', 'type' => 'I']
        ]
    ],
    // Sensação (S) vs. Intuição (N)
    [
        'id' => 23,
        'question' => 'Ao lidar com novos projetos, costumo:',
        'options' => [
            'A' => ['text' => 'Concentrar-me nos detalhes práticos e passos concretos.', 'type' => 'S'],
            'B' => ['text' => 'Pensar primeiro nas possibilidades gerais e em grandes ideias.', 'type' => 'N']
        ]
    ],
    [
        'id' => 24,
        'question' => 'Quando aprendo algo novo, prefiro:',
        'options' => [
            'A' => ['text' => 'Informações específicas e exemplos práticos.', 'type' => 'S'],
            'B' => ['text' => 'Conceitos abstratos e teorias gerais.', 'type' => 'N']
        ]
    ],
    // Pensamento (T) vs. Sentimento (F)
    [
        'id' => 45,
        'question' => 'Ao lidar com conflitos, costumo:',
        'options' => [
            'A' => ['text' => 'Buscar a solução mais lógica, mesmo que seja direta e objetiva.', 'type' => 'T'],
            'B' => ['text' => 'Levar em conta as emoções envolvidas antes de propor algo.', 'type' => 'F']
        ]
    ],
    [
        'id' => 46,
        'question' => 'Ao tomar decisões importantes, baseio-me mais em:',
        'options' => [
            'A' => ['text' => 'Fatos e análise objetiva da situação.', 'type' => 'T'],
            'B' => ['text' => 'Valores pessoais e impacto nas pessoas.', 'type' => 'F']
        ]
    ],
    // Julgamento (J) vs. Percepção (P)
    [
        'id' => 67,
        'question' => 'Meu estilo de organização é mais:',
        'options' => [
            'A' => ['text' => 'Gosto de ter tudo planejado com antecedência, agendas definidas.', 'type' => 'J'],
            'B' => ['text' => 'Prefiro flexibilidade e adapto-me conforme as coisas acontecem.', 'type' => 'P']
        ]
    ],
    [
        'id' => 68,
        'question' => 'Em relação a prazos e compromissos:',
        'options' => [
            'A' => ['text' => 'Gosto de terminar as tarefas bem antes do prazo.', 'type' => 'J'],
            'B' => ['text' => 'Prefiro manter as opções abertas até o último momento.', 'type' => 'P']
        ]
    ]
];

$mbti_types = [
    'ISTJ' => [
        'name' => 'Inspetor',
        'description' => 'Prático, responsável e organizado. Valoriza tradições e lealdade.',
        'strengths' => [
            'Confiável e dedicado',
            'Excelente atenção aos detalhes',
            'Organizado e metódico',
            'Direto e honesto',
            'Leal e comprometido'
        ],
        'career_matches' => [
            'Contador',
            'Auditor',
            'Gerente de Projetos',
            'Administrador',
            'Analista de Sistemas'
        ]
    ],
    'ISFJ' => [
        'name' => 'Protetor',
        'description' => 'Dedicado, caloroso e protetor. Gosta de proteger e servir os outros.',
        'strengths' => [
            'Confiável e responsável',
            'Observador e atento',
            'Paciente e detalhista',
            'Dedicado e protetor',
            'Excelente memória para detalhes'
        ],
        'career_matches' => [
            'Enfermeiro',
            'Professor',
            'Assistente Social',
            'RH',
            'Bibliotecário'
        ]
    ],
    'INFJ' => [
        'name' => 'Conselheiro',
        'description' => 'Idealista, organizado e insightful. Busca significado e conexão.',
        'strengths' => [
            'Criativo e artístico',
            'Insightful e intuitivo',
            'Dedicado e prestativo',
            'Organizado e decidido',
            'Comprometido com valores'
        ],
        'career_matches' => [
            'Psicólogo',
            'Conselheiro',
            'Professor',
            'Escritor',
            'RH'
        ]
    ],
    'INTJ' => [
        'name' => 'Arquiteto',
        'description' => 'Inovador, independente e estratégico. Motivado por ideias e desafios.',
        'strengths' => [
            'Estratégico e inovador',
            'Independente e determinado',
            'Racional e objetivo',
            'Curioso e perfeccionista',
            'Excelente planejador'
        ],
        'career_matches' => [
            'Cientista',
            'Engenheiro',
            'Arquiteto',
            'Pesquisador',
            'Analista de Sistemas'
        ]
    ],
    'ISTP' => [
        'name' => 'Artesão',
        'description' => 'Prático, realista e adaptável. Gosta de entender como as coisas funcionam.',
        'strengths' => [
            'Lógico e racional',
            'Aprende com facilidade',
            'Bom em resolver problemas',
            'Adaptável e versátil',
            'Bom em situações de crise'
        ],
        'career_matches' => [
            'Mecânico',
            'Engenheiro',
            'Piloto',
            'Programador',
            'Técnico'
        ]
    ],
    // Continue com os outros tipos...
];
