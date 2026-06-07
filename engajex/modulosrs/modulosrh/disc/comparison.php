<?php
session_start();

// Verificar se está logado como admin ou superadmin
if (!isset($_SESSION['admin_authenticated']) && !isset($_SESSION['superadmin_authenticated'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DISC vs. MBTI vs. Big Five - Comparação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .content-section {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .table-comparison th {
            background-color: #f8f9fa;
        }
        .comparison-card {
            border-left: 4px solid;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            border-radius: 0 10px 10px 0;
        }
        .disc-card { border-left-color: #0d6efd; }
        .mbti-card { border-left-color: #198754; }
        .bigfive-card { border-left-color: #0dcaf0; }
        .list-custom {
            list-style: none;
            padding-left: 0;
        }
        .list-custom li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .list-custom li:last-child {
            border-bottom: none;
        }
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-5">
        <?php include 'header.php'; ?>

        <div class="content-section">
            <h1 class="section-title">DISC, MBTI e Big Five: Comparando os Principais Testes de Personalidade</h1>
            <p class="lead">Os testes de personalidade são ferramentas poderosas para compreender comportamentos, preferências e traços individuais. Entre os modelos mais conhecidos, estão o DISC, o MBTI (Myers-Briggs Type Indicator) e o Big Five (Cinco Grandes Traços de Personalidade). Cada um possui uma abordagem única, sendo mais adequado para diferentes contextos. Vamos explorar as diferenças entre eles e suas melhores aplicações.</p>

            <h2 class="section-title mt-5">O que é o DISC?</h2>
            <div class="comparison-card disc-card">
                <p>O DISC avalia comportamentos observáveis, categorizando-os em quatro perfis principais:</p>
                <ul class="list-custom">
                    <li><strong>Dominância (D):</strong> Foco em resultados, assertividade.</li>
                    <li><strong>Influência (I):</strong> Comunicação, sociabilidade.</li>
                    <li><strong>Estabilidade (S):</strong> Calma, paciência, busca por harmonia.</li>
                    <li><strong>Conformidade (C):</strong> Analítico, orientado a regras e detalhes.</li>
                </ul>
                <p class="mt-3"><strong>Objetivo:</strong> Identificar como uma pessoa age em diferentes situações.</p>
                <p><strong>Aplicações principais:</strong></p>
                <ul>
                    <li>Gestão de equipes e liderança.</li>
                    <li>Recrutamento e seleção.</li>
                    <li>Comunicação interpessoal.</li>
                </ul>
            </div>

            <h2 class="section-title mt-5">O que é o MBTI?</h2>
            <div class="comparison-card mbti-card">
                <p>Baseado na teoria dos tipos psicológicos de Carl Jung, o MBTI classifica a personalidade em 16 tipos, combinando quatro dicotomias:</p>
                <ul class="list-custom">
                    <li><strong>Extroversão (E) vs. Introversão (I):</strong> Onde a pessoa recarrega energia.</li>
                    <li><strong>Sensação (S) vs. Intuição (N):</strong> Como percebe informações.</li>
                    <li><strong>Pensamento (T) vs. Sentimento (F):</strong> Como toma decisões.</li>
                    <li><strong>Julgamento (J) vs. Percepção (P):</strong> Como organiza a vida.</li>
                </ul>
                <p class="mt-3"><strong>Objetivo:</strong> Explorar por que a pessoa age de determinada forma.</p>
                <p><strong>Aplicações principais:</strong></p>
                <ul>
                    <li>Autoconhecimento e desenvolvimento pessoal.</li>
                    <li>Coaching e orientação vocacional.</li>
                    <li>Construção de relacionamentos.</li>
                </ul>
            </div>

            <h2 class="section-title mt-5">O que é o Big Five?</h2>
            <div class="comparison-card bigfive-card">
                <p>O Big Five mede cinco traços universais da personalidade, baseados em pesquisa científica:</p>
                <ul class="list-custom">
                    <li><strong>Abertura à Experiência:</strong> Criatividade e interesse por novas ideias.</li>
                    <li><strong>Conscienciosidade:</strong> Organização, disciplina e responsabilidade.</li>
                    <li><strong>Extroversão:</strong> Energia e sociabilidade.</li>
                    <li><strong>Agradabilidade:</strong> Empatia e cooperação.</li>
                    <li><strong>Neuroticismo:</strong> Tendência à instabilidade emocional.</li>
                </ul>
                <p class="mt-3"><strong>Objetivo:</strong> Fornecer um modelo amplo e científico da personalidade.</p>
                <p><strong>Aplicações principais:</strong></p>
                <ul>
                    <li>Pesquisa acadêmica e psicológica.</li>
                    <li>Planejamento de carreira.</li>
                    <li>Previsão de desempenho profissional e comportamental.</li>
                </ul>
            </div>

            <h2 class="section-title mt-5">Diferenças Principais</h2>
            <div class="table-responsive">
                <table class="table table-hover table-comparison">
                    <thead>
                        <tr>
                            <th>Aspecto</th>
                            <th>DISC</th>
                            <th>MBTI</th>
                            <th>Big Five</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Base Teórica</strong></td>
                            <td>Psicologia comportamental</td>
                            <td>Teoria dos tipos psicológicos de Jung</td>
                            <td>Pesquisas científicas em psicologia</td>
                        </tr>
                        <tr>
                            <td><strong>Foco</strong></td>
                            <td>Comportamento observável</td>
                            <td>Preferências psicológicas</td>
                            <td>Traços universais da personalidade</td>
                        </tr>
                        <tr>
                            <td><strong>Classificação</strong></td>
                            <td>4 perfis principais</td>
                            <td>16 tipos de personalidade</td>
                            <td>5 dimensões amplas</td>
                        </tr>
                        <tr>
                            <td><strong>Objetivo</strong></td>
                            <td>Identificar como a pessoa age</td>
                            <td>Explicar por que a pessoa age</td>
                            <td>Traçar um panorama detalhado da personalidade</td>
                        </tr>
                        <tr>
                            <td><strong>Aplicação Prática</strong></td>
                            <td>Gestão e dinâmicas de equipe</td>
                            <td>Autoconhecimento e relacionamentos</td>
                            <td>Pesquisa e planejamento de carreira</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h2 class="section-title mt-5">Qual Ferramenta Escolher?</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Escolha o DISC se o foco for:</h5>
                            <p class="card-text">Contextos profissionais como gestão, liderança e recrutamento.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Escolha o MBTI se o foco for:</h5>
                            <p class="card-text">Desenvolvimento pessoal, autoconhecimento e relacionamentos interpessoais.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Escolha o Big Five se o foco for:</h5>
                            <p class="card-text">Uma análise científica e abrangente da personalidade, especialmente em contextos acadêmicos ou de planejamento de longo prazo.</p>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="section-title mt-5">Conclusão</h2>
            <p class="mb-0">Enquanto o DISC é prático e voltado para comportamentos em contextos específicos, o MBTI mergulha nas preferências psicológicas, e o Big Five oferece uma visão científica e detalhada da personalidade. A escolha depende do objetivo e do contexto, mas utilizar as ferramentas de forma complementar pode proporcionar uma visão rica e completa sobre o indivíduo.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
