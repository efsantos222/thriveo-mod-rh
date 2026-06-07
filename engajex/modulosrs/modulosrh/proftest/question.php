<?php
session_start();

if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header('Location: login.php');
    exit;
}

$questions = [
    [
        'id' => 1,
        'question' => 'Quando estou diante de um novo desafio...',
        'options' => [
            'A' => ['text' => 'Gosto de agir rapidamente e assumir a liderança.', 'type' => 'D'],
            'B' => ['text' => 'Prefiro envolver e motivar as pessoas ao redor.', 'type' => 'I'],
            'C' => ['text' => 'Busco manter o ambiente estável e colaborar com todos.', 'type' => 'S'],
            'D' => ['text' => 'Analiso os detalhes e planejo cuidadosamente cada passo.', 'type' => 'C']
        ]
    ],
    [
        'id' => 2,
        'question' => 'Em uma reunião de equipe, eu normalmente...',
        'options' => [
            'A' => ['text' => 'Vou direto ao ponto e foco nos resultados.', 'type' => 'D'],
            'B' => ['text' => 'Trago energia, ideias criativas e tento contagiar o grupo.', 'type' => 'I'],
            'C' => ['text' => 'Escuto os colegas e ofereço ajuda para facilitar a harmonia.', 'type' => 'S'],
            'D' => ['text' => 'Estruturo tópicos e organizo as informações de forma lógica.', 'type' => 'C']
        ]
    ],
    [
        'id' => 3,
        'question' => 'Quando preciso tomar uma decisão importante...',
        'options' => [
            'A' => ['text' => 'Arrisco e tomo a decisão sem demora.', 'type' => 'D'],
            'B' => ['text' => 'Converso com as pessoas para ouvir opiniões e criar consenso.', 'type' => 'I'],
            'C' => ['text' => 'Evito pressa, prefiro ter segurança e estabilidade na escolha.', 'type' => 'S'],
            'D' => ['text' => 'Estudo todos os prós e contras antes de decidir.', 'type' => 'C']
        ]
    ],
    [
        'id' => 4,
        'question' => 'No trabalho, me sinto mais confortável quando...',
        'options' => [
            'A' => ['text' => 'Posso liderar projetos e tomar decisões importantes.', 'type' => 'D'],
            'B' => ['text' => 'Tenho oportunidades de interagir e influenciar pessoas.', 'type' => 'I'],
            'C' => ['text' => 'O ambiente é previsível e posso ajudar meus colegas.', 'type' => 'S'],
            'D' => ['text' => 'Posso seguir processos bem definidos e precisos.', 'type' => 'C']
        ]
    ],
    [
        'id' => 5,
        'question' => 'Quando surge um problema inesperado...',
        'options' => [
            'A' => ['text' => 'Tomo a iniciativa e busco resolver rapidamente.', 'type' => 'D'],
            'B' => ['text' => 'Mobilizo a equipe e mantenho o otimismo.', 'type' => 'I'],
            'C' => ['text' => 'Mantenho a calma e sigo os procedimentos estabelecidos.', 'type' => 'S'],
            'D' => ['text' => 'Analiso todas as variáveis antes de propor uma solução.', 'type' => 'C']
        ]
    ],
    [
        'id' => 6,
        'question' => 'Em um projeto em equipe, prefiro...',
        'options' => [
            'A' => ['text' => 'Estabelecer metas claras e cobrar resultados.', 'type' => 'D'],
            'B' => ['text' => 'Motivar o grupo e manter o entusiasmo.', 'type' => 'I'],
            'C' => ['text' => 'Dar suporte aos colegas e manter a harmonia.', 'type' => 'S'],
            'D' => ['text' => 'Cuidar da documentação e garantir a qualidade.', 'type' => 'C']
        ]
    ],
    [
        'id' => 7,
        'question' => 'Quando recebo uma nova tarefa...',
        'options' => [
            'A' => ['text' => 'Começo imediatamente e busco resultados rápidos.', 'type' => 'D'],
            'B' => ['text' => 'Penso em formas criativas de envolver outros.', 'type' => 'I'],
            'C' => ['text' => 'Verifico se tenho todo o suporte necessário.', 'type' => 'S'],
            'D' => ['text' => 'Organizo um passo a passo detalhado.', 'type' => 'C']
        ]
    ],
    [
        'id' => 8,
        'question' => 'Em situações de mudança, eu...',
        'options' => [
            'A' => ['text' => 'Vejo como uma oportunidade de assumir novos desafios.', 'type' => 'D'],
            'B' => ['text' => 'Ajudo a criar um clima positivo e animador.', 'type' => 'I'],
            'C' => ['text' => 'Procuro manter a estabilidade durante a transição.', 'type' => 'S'],
            'D' => ['text' => 'Foco em entender todos os detalhes do processo.', 'type' => 'C']
        ]
    ],
    [
        'id' => 9,
        'question' => 'Minha maior contribuição para a equipe é...',
        'options' => [
            'A' => ['text' => 'Capacidade de tomar decisões e alcançar resultados.', 'type' => 'D'],
            'B' => ['text' => 'Habilidade de inspirar e engajar pessoas.', 'type' => 'I'],
            'C' => ['text' => 'Disposição para cooperar e manter a harmonia.', 'type' => 'S'],
            'D' => ['text' => 'Atenção aos detalhes e organização.', 'type' => 'C']
        ]
    ],
    [
        'id' => 10,
        'question' => 'No dia a dia, me preocupo mais em...',
        'options' => [
            'A' => ['text' => 'Atingir objetivos e superar desafios.', 'type' => 'D'],
            'B' => ['text' => 'Construir relacionamentos e influenciar positivamente.', 'type' => 'I'],
            'C' => ['text' => 'Manter um ambiente estável e dar suporte aos outros.', 'type' => 'S'],
            'D' => ['text' => 'Garantir precisão e qualidade em tudo que faço.', 'type' => 'C']
        ]
    ]
];

$disc_types = [
    'D' => [
        'name' => 'Dominância',
        'description' => 'Pessoas com alto D são diretas, decisivas e orientadas a resultados. Gostam de desafios, assumem riscos e tomam iniciativa.',
        'strengths' => [
            'Liderança natural',
            'Foco em resultados',
            'Tomada rápida de decisão',
            'Capacidade de assumir riscos',
            'Determinação'
        ],
        'career_matches' => [
            'Executivo',
            'Empreendedor',
            'Gerente de Projetos',
            'Diretor Comercial',
            'Consultor Estratégico'
        ]
    ],
    'I' => [
        'name' => 'Influência',
        'description' => 'Pessoas com alto I são sociáveis, entusiastas e persuasivas. Gostam de interagir com outros e são naturalmente otimistas.',
        'strengths' => [
            'Comunicação efetiva',
            'Habilidades sociais',
            'Criatividade',
            'Entusiasmo contagiante',
            'Capacidade de motivar'
        ],
        'career_matches' => [
            'Vendedor',
            'Relações Públicas',
            'Marketing',
            'Treinador',
            'Apresentador'
        ]
    ],
    'S' => [
        'name' => 'Estabilidade',
        'description' => 'Pessoas com alto S são pacientes, leais e colaborativas. Valorizam a estabilidade e são excelentes ouvintes.',
        'strengths' => [
            'Trabalho em equipe',
            'Paciência',
            'Confiabilidade',
            'Capacidade de ouvir',
            'Lealdade'
        ],
        'career_matches' => [
            'RH',
            'Professor',
            'Assistente Social',
            'Enfermeiro',
            'Administrador'
        ]
    ],
    'C' => [
        'name' => 'Conformidade',
        'description' => 'Pessoas com alto C são analíticas, precisas e sistemáticas. Valorizam a qualidade e são atentas aos detalhes.',
        'strengths' => [
            'Atenção aos detalhes',
            'Organização',
            'Pensamento analítico',
            'Precisão',
            'Planejamento'
        ],
        'career_matches' => [
            'Analista',
            'Contador',
            'Engenheiro',
            'Pesquisador',
            'Programador'
        ]
    ]
];

if (!isset($_SESSION['questao_atual']) || $_SESSION['questao_atual'] >= count($questions)) {
    header('Location: process.php');
    exit;
}

$questao_atual = $questions[$_SESSION['questao_atual']];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questão <?php echo $questao_atual['id']; ?> - Sistema de Avaliação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 50px; }
        .container { max-width: 800px; }
        .question-container { background: #f8f9fa; padding: 20px; border-radius: 10px; }
        .options-container { margin-top: 20px; }
        .option-item { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'includes/candidate_header.php'; ?>
        
        <div class="question-container">
            <div class="progress mb-4">
                <div class="progress-bar" role="progressbar" 
                     style="width: <?php echo ($_SESSION['questao_atual'] / count($questions)) * 100; ?>%">
                </div>
            </div>
            
            <h3 class="mb-4">Questão <?php echo $questao_atual['id']; ?></h3>
            <p class="lead mb-4"><?php echo $questao_atual['question']; ?></p>
            
            <form action="process.php" method="POST" id="questionForm">
                <div class="options-container">
                    <?php foreach ($questao_atual['options'] as $key => $opcao): ?>
                        <div class="option-item">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" 
                                       name="resposta" value="<?php echo $key; ?>" 
                                       id="opcao_<?php echo $key; ?>" required>
                                <label class="form-check-label" for="opcao_<?php echo $key; ?>">
                                    <?php echo $opcao['text']; ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">Próxima Questão</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prevenir o usuário de voltar
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };

        // Limpar o formulário após o envio
        document.getElementById('questionForm').addEventListener('submit', function(e) {
            // Aguarda um pequeno delay para garantir que o formulário foi enviado
            setTimeout(() => {
                this.reset();
                // Remover seleção de todos os radio buttons
                document.querySelectorAll('input[type="radio"]').forEach(radio => {
                    radio.checked = false;
                });
            }, 100);
        });
    </script>
</body>
</html>
