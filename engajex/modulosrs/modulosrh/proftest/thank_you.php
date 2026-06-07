<?php
session_start();

// Determinar qual arquivo de candidatos usar baseado no tipo de usuário
$user_type = $_SESSION['user_type'] ?? '';
$candidatos_file = $user_type === 'mbti' ? 'resultados/candidatos_mbti.csv' : 'resultados/candidatos.csv';
$temp_file = $user_type === 'mbti' ? 'resultados/candidatos_mbti_temp.csv' : 'resultados/candidatos_temp.csv';
$user_email = $_SESSION['user_email'] ?? '';

if (file_exists($candidatos_file) && $user_email) {
    $fp_read = fopen($candidatos_file, 'r');
    $fp_write = fopen($temp_file, 'w');
    
    if ($fp_read !== false && $fp_write !== false) {
        // Copiar cabeçalho
        $header = fgetcsv($fp_read);
        
        // Encontrar o índice da coluna de status
        $status_index = 8; // Índice padrão
        foreach ($header as $index => $column) {
            if (strtolower(trim($column)) === 'status') {
                $status_index = $index;
                break;
            }
        }
        
        // Se não houver coluna de status, adicionar ao final
        if (!in_array('Status', $header)) {
            $header[] = 'Status';
            $status_index = count($header) - 1;
        }
        
        error_log("Índice da coluna de status: " . $status_index);
        fputcsv($fp_write, $header);
        
        // Atualizar dados do candidato
        while (($data = fgetcsv($fp_read)) !== FALSE) {
            // Garantir que o array tem elementos suficientes
            while (count($data) <= $status_index) {
                $data[] = '';
            }
            
            if ($data[4] === $user_email) {
                $data[$status_index] = 'completed';
                error_log("Atualizando status para completed - Dados: " . implode(", ", $data));
            }
            fputcsv($fp_write, $data);
        }
        
        fclose($fp_read);
        fclose($fp_write);
        
        // Substituir arquivo original
        if (rename($temp_file, $candidatos_file)) {
            error_log("Arquivo atualizado com sucesso");
        } else {
            error_log("Erro ao atualizar o arquivo");
        }
    }
}

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Se houver um cookie de sessão, destruí-lo
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Fazer logout
session_destroy();

// URL do sistema
$home_url = "https://proftest.com.br/disc";
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    $home_url = "index.php";
}

// Adicionar um pequeno atraso antes do redirecionamento
header("refresh:5;url=" . $home_url);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obrigado - Sistema de Avaliação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 50px; }
        .container { max-width: 600px; }
        .thank-you-container { 
            background: #f8f9fa; 
            padding: 30px; 
            border-radius: 10px;
            text-align: center;
        }
        .countdown {
            font-size: 1.2em;
            color: #6c757d;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="thank-you-container">
            <h1 class="mb-4">Obrigado por completar o teste!</h1>
            <p class="lead mb-4">Suas respostas foram registradas com sucesso.</p>
            <p>Você será redirecionado em <span id="countdown">5</span> segundos...</p>
            <div class="mt-4">
                <a href="<?php echo $home_url; ?>" class="btn btn-primary">Voltar para a página inicial</a>
            </div>
        </div>
    </div>

    <script>
        // Contador regressivo
        let seconds = 5;
        const countdownDisplay = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            seconds--;
            countdownDisplay.textContent = seconds;
            if (seconds <= 0) clearInterval(timer);
        }, 1000);
    </script>
</body>
</html>
