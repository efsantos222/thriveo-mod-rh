<?php
session_start();

// Se já estiver autenticado, redireciona para as questões
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
    header('Location: question.php');
    exit;
}

require_once 'includes/menu.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Avaliação DISC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { padding-top: 50px; }
        .container { max-width: 800px; } /* Aumentado para acomodar as instruções */
        .form-container { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .home-button {
            position: absolute;
            top: 10px;
            left: 10px;
        }
        .alert-info h5 {
            margin-top: 1.5rem;
            color: #0c5460;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .alert-info h5:first-of-type {
            margin-top: 1rem;
        }
        .alert-info p {
            margin-left: 1.7rem;
            margin-bottom: 0.5rem;
        }
        .alert-info hr {
            margin: 1rem 0;
            border-color: #bee5eb;
        }
        .bi {
            font-size: 1.2rem;
        }
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            cursor: pointer;
            color: #6c757d;
        }
        .password-toggle:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <?php renderMenu(); ?>
    
    <a href="https://proftest.com.br/proftest" class="btn btn-secondary home-button">
        <i class="bi bi-house-fill"></i> Home
    </a>
    
    <div class="container">
        <div class="form-container">
            <h2 class="mb-4 text-center">Login do Candidato</h2>
            
            <!-- Instruções do Teste -->
            <div class="alert alert-info mb-4">
                <h4 class="alert-heading mb-3">Instruções para o Teste</h4>
                <p>Seja bem-vindo(a) ao seu processo de avaliação. Antes de iniciar, leia com atenção as orientações abaixo para garantir que todo o procedimento seja realizado corretamente:</p>
                
                <hr>
                <h5><i class="bi bi-check-circle"></i> Não há respostas certas ou erradas</h5>
                <p>O teste avalia tendências comportamentais. Portanto, responda de forma autêntica e sincera, sem se preocupar em "acertar" ou "errar".</p>
                
                <h5><i class="bi bi-exclamation-circle"></i> É obrigatório responder cada questão</h5>
                <p>Todas as perguntas devem ser respondidas para que o teste seja finalizado com êxito. Caso não responda a alguma questão, não conseguirá avançar para a próxima.</p>
                
                <h5><i class="bi bi-arrow-right-circle"></i> Não é possível voltar para revisar uma questão</h5>
                <p>Assim que responder a uma pergunta e avançar, não será possível retornar a itens anteriores. Portanto, reflita bem antes de confirmar cada resposta.</p>
                
                <h5><i class="bi bi-list-ol"></i> Será disponibilizada uma questão por vez</h5>
                <p>O teste mostrará uma única pergunta de cada vez para que você possa se concentrar totalmente naquela questão.</p>
                
                <h5><i class="bi bi-browser-chrome"></i> Não feche o navegador de internet antes do final do teste</h5>
                <p>Caso o navegador seja fechado ou a conexão seja interrompida, você pode perder o andamento do teste e não será possível recuperá-lo.</p>
                
                <h5><i class="bi bi-arrow-repeat"></i> Você não poderá reiniciar o teste uma vez concluído</h5>
                <p>Após finalizar suas respostas, não será possível recomeçar o teste do zero. Certifique-se de estar pronto(a) antes de iniciar.</p>
                
                <h5><i class="bi bi-chat-dots"></i> O selecionador te dará os feedback adicionais necessários</h5>
                <p>Ao término do teste, aguarde as orientações da pessoa responsável pela seleção. Ela dará o devido retorno sobre a avaliação e os próximos passos do processo.</p>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php
                    switch ($_GET['error']) {
                        case 'test_completed':
                            echo 'Você já completou o teste. Não é possível realizar o teste novamente.';
                            break;
                        default:
                            echo 'Credenciais inválidas. Tente novamente.';
                    }
                    ?>
                </div>
            <?php endif; ?>
            <form action="verify_login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="senha" class="form-label">Senha</label>
                    <div class="password-container">
                        <input type="password" class="form-control" id="senha" name="senha" required>
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('senha')">
                            <i class="bi bi-eye" data-password-toggle="senha"></i>
                        </button>
                    </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/form-utils.js"></script>
    <script>
        // Limpar o formulário após o envio
        document.querySelector('form').addEventListener('submit', function(e) {
            // Aguarda um pequeno delay para garantir que o formulário foi enviado
            setTimeout(() => {
                this.reset();
            }, 100);
        });
    </script>
</body>
</html>
