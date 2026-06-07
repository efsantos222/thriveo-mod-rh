<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['bigfive_authenticated']) || !isset($_SESSION['bigfive_email'])) {
    header('Location: login_bigfive.php');
    exit;
}

$email = $_SESSION['bigfive_email'];
$candidatos_file = 'resultados/candidatos_bigfive.csv';
$avaliacao_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_avaliacao_bigfive.csv';

// Verificar se a avaliação já foi realizada
if (file_exists($avaliacao_file)) {
    echo "<p>Esta avaliação já foi concluída.</p>";
    exit;
}

// Verificar se o candidato está registrado
$candidato_encontrado = false;
if (file_exists($candidatos_file)) {
    $fp = fopen($candidatos_file, 'r');
    fgetcsv($fp); // Pular cabeçalho
    while (($data = fgetcsv($fp)) !== FALSE) {
        if ($data[4] === $email) { // Email está na quinta coluna
            $candidato_nome = $data[3]; // Nome está na quarta coluna
            $candidato_encontrado = true;
            break;
        }
    }
    fclose($fp);
}

if (!$candidato_encontrado) {
    echo "<p>Candidato não encontrado.</p>";
    exit;
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $respostas = $_POST['respostas'];
    
    // Validar se todas as questões foram respondidas
    if (count($respostas) === 44) {
        $fp = fopen($avaliacao_file, 'w');
        fputcsv($fp, ['Questao', 'Resposta', 'Dimensao', 'Inverso']);
        
        foreach ($respostas as $questao => $resposta) {
            // Determinar a dimensão e se é inverso
            $dimensao = '';
            $inverso = false;
            
            if ($questao <= 8) {
                $dimensao = 'Abertura';
                $inverso = ($questao == 8);
            } elseif ($questao <= 16) {
                $dimensao = 'Conscienciosidade';
                $inverso = ($questao == 13 || $questao == 16);
            } elseif ($questao <= 24) {
                $dimensao = 'Extroversao';
                $inverso = ($questao == 22 || $questao == 24);
            } elseif ($questao <= 32) {
                $dimensao = 'Amabilidade';
                $inverso = ($questao == 31 || $questao == 32);
            } elseif ($questao <= 40) {
                $dimensao = 'Neuroticismo';
                $inverso = ($questao == 38 || $questao == 39);
            } else {
                $dimensao = 'Extra';
            }
            
            fputcsv($fp, [$questao, $resposta, $dimensao, $inverso ? '1' : '0']);
        }
        fclose($fp);
        
        // Salvar resultados
        $resultado_file = 'resultados/' . str_replace(['@', '.'], '_', $email) . '_resultado_bigfive.csv';
        $abertura = 0;
        $conscienciosidade = 0;
        $extroversao = 0;
        $amabilidade = 0;
        $neuroticismo = 0;
        
        $fp = fopen($avaliacao_file, 'r');
        fgetcsv($fp); // Pular cabeçalho
        while (($data = fgetcsv($fp)) !== FALSE) {
            switch ($data[2]) {
                case 'Abertura':
                    $abertura += $data[1];
                    break;
                case 'Conscienciosidade':
                    $conscienciosidade += $data[1];
                    break;
                case 'Extroversao':
                    $extroversao += $data[1];
                    break;
                case 'Amabilidade':
                    $amabilidade += $data[1];
                    break;
                case 'Neuroticismo':
                    $neuroticismo += $data[1];
                    break;
            }
        }
        fclose($fp);
        
        $fp = fopen($resultado_file, 'w');
        fputcsv($fp, ['Abertura', 'Conscienciosidade', 'Extroversao', 'Amabilidade', 'Neuroticismo']);
        fputcsv($fp, [$abertura, $conscienciosidade, $extroversao, $amabilidade, $neuroticismo]);
        fclose($fp);
        
        // Atualizar status do candidato para completed
        $candidatos_file = 'resultados/candidatos_bigfive.csv';
        $linhas = [];
        $atualizado = false;
        
        if (file_exists($candidatos_file)) {
            if (($handle = fopen($candidatos_file, "r")) !== FALSE) {
                // Ler cabeçalho
                $header = fgetcsv($handle);
                $linhas[] = $header;
                
                // Processar linhas
                while (($data = fgetcsv($handle)) !== FALSE) {
                    if ($data[4] === $_SESSION['bigfive_email']) { // Email está na quinta coluna
                        // Garantir que temos a coluna de status
                        while (count($data) < 8) {
                            $data[] = '';
                        }
                        $data[7] = 'completed'; // Status é a oitava coluna
                        $atualizado = true;
                    }
                    $linhas[] = $data;
                }
                fclose($handle);
                
                // Reescrever arquivo com status atualizado
                if ($atualizado) {
                    $fp = fopen($candidatos_file, 'w');
                    foreach ($linhas as $linha) {
                        fputcsv($fp, $linha);
                    }
                    fclose($fp);
                }
            }
        }
        
        header('Location: bigfive_complete.php');
        exit;
    } else {
        $error = "Por favor, responda todas as questões.";
    }
}

// Array com todas as questões
$questoes = [
    // Abertura à Experiência
    "Gosto de explorar ideias novas e diferentes.",
    "Tenho uma imaginação ativa e criativa.",
    "Gosto de aprender sobre tópicos variados.",
    "Prefiro a novidade em vez de rotinas previsíveis.",
    "Sou curioso(a) sobre muitas coisas.",
    "Sou aberto(a) a mudanças e experiências diferentes.",
    "Gosto de arte, música ou literatura que me faz pensar.",
    "Prefiro seguir tradições e evitar mudanças.", // inverso
    
    // Conscienciosidade
    "Planejo minhas tarefas com antecedência.",
    "Costumo cumprir prazos e responsabilidades.",
    "Sou organizado(a) e mantenho as coisas em ordem.",
    "Prefiro concluir as tarefas antes de relaxar.",
    "Costumo agir de forma impulsiva, sem pensar nas consequências.", // inverso
    "Tenho facilidade em cumprir metas e objetivos.",
    "Sou persistente, mesmo quando encontro dificuldades.",
    "Evito me preocupar com detalhes.", // inverso
    
    // Extroversão
    "Sinto-me confortável em situações sociais.",
    "Sou energético(a) e gosto de interagir com os outros.",
    "Prefiro atividades em grupo a atividades solitárias.",
    "Costumo iniciar conversas com facilidade.",
    "Gosto de ser o(a) centro das atenções.",
    "Prefiro ficar sozinho(a) do que em grupos grandes.", // inverso
    "Falo com entusiasmo sobre coisas que me interessam.",
    "Costumo evitar reuniões sociais.", // inverso
    
    // Amabilidade
    "Gosto de ajudar as pessoas sempre que posso.",
    "Sou uma pessoa empática e compreendo os sentimentos dos outros.",
    "Trabalho bem em equipe e valorizo a cooperação.",
    "Costumo confiar nas intenções das pessoas.",
    "Sou gentil e educado(a) com os outros.",
    "Evito conflitos, mesmo quando tenho razão.",
    "Sou competitivo(a) e foco em vencer.", // inverso
    "Sou crítico(a) com os erros dos outros.", // inverso
    
    // Neuroticismo
    "Sinto-me ansioso(a) em situações novas.",
    "Preocupo-me frequentemente com coisas pequenas.",
    "Tenho dificuldade em lidar com situações estressantes.",
    "Fico irritado(a) com facilidade.",
    "Costumo me sentir inseguro(a) em relação às minhas decisões.",
    "Sou calmo(a) e raramente fico chateado(a).", // inverso
    "Consigo me recuperar rapidamente de eventos negativos.", // inverso
    "Minhas emoções frequentemente interferem no meu trabalho ou estudos.",
    
    // Extra
    "Sigo as regras, mesmo quando não concordo com elas.",
    "Sou flexível em situações inesperadas.",
    "Gosto de refletir sobre meus sentimentos e pensamentos.",
    "Busco constantemente melhorar como pessoa."
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliação Big Five</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .question-card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .progress {
            height: 10px;
            margin-bottom: 20px;
        }
        .likert-scale {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .likert-option {
            text-align: center;
            flex: 1;
        }
        .likert-label {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Avaliação Big Five Personality Traits</h4>
                    </div>
                    <div class="card-body">
                        <h5>Olá, <?php echo htmlspecialchars($candidato_nome); ?>!</h5>
                        <p>Esta avaliação contém 44 questões sobre diferentes aspectos da sua personalidade.</p>
                        <p><strong>Instruções:</strong></p>
                        <ul>
                            <li>Leia cada afirmação cuidadosamente</li>
                            <li>Avalie o quanto cada afirmação descreve você</li>
                            <li>Seja honesto(a) em suas respostas</li>
                            <li>Não existe resposta certa ou errada</li>
                        </ul>
                        
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="bigFiveForm">
                    <?php foreach ($questoes as $index => $questao): ?>
                    <div class="card question-card" data-question="<?php echo $index + 1; ?>">
                        <div class="card-body">
                            <h5 class="card-title">Questão <?php echo $index + 1; ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($questao); ?></p>
                            
                            <div class="likert-scale">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="likert-option">
                                    <input type="radio" name="respostas[<?php echo $index + 1; ?>]" value="<?php echo $i; ?>" 
                                           class="btn-check" id="q<?php echo $index + 1; ?>_<?php echo $i; ?>" required>
                                    <label class="btn btn-outline-primary" for="q<?php echo $index + 1; ?>_<?php echo $i; ?>"><?php echo $i; ?></label>
                                    <div class="likert-label">
                                        <?php
                                        echo match($i) {
                                            1 => 'Discordo totalmente',
                                            2 => 'Discordo',
                                            3 => 'Neutro',
                                            4 => 'Concordo',
                                            5 => 'Concordo totalmente',
                                        };
                                        ?>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Enviar Avaliação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('bigFiveForm');
            const progressBar = document.querySelector('.progress-bar');
            const totalQuestions = <?php echo count($questoes); ?>;
            
            // Atualizar barra de progresso
            function updateProgress() {
                const answered = document.querySelectorAll('input[type="radio"]:checked').length;
                const progress = (answered / totalQuestions) * 100;
                progressBar.style.width = progress + '%';
                progressBar.setAttribute('aria-valuenow', progress);
            }
            
            // Adicionar listener para todas as opções de resposta
            document.querySelectorAll('input[type="radio"]').forEach(radio => {
                radio.addEventListener('change', updateProgress);
            });
            
            // Validar formulário antes de enviar
            form.addEventListener('submit', function(e) {
                const answered = document.querySelectorAll('input[type="radio"]:checked').length;
                if (answered < totalQuestions) {
                    e.preventDefault();
                    alert('Por favor, responda todas as questões antes de enviar.');
                }
            });
        });
    </script>
</body>
</html>
