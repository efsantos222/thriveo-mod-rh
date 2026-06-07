<?php
/**
 * TESTE DE AMBIENTE - VIRTUAL CONNECTIONS
 * Execute este arquivo para verificar se seu servidor está configurado corretamente
 * Acesse: www.seusite.com/caminho/test.php
 * 
 * IMPORTANTE: Remova este arquivo após a instalação por segurança!
 */

$tests = [];
$allPassed = true;

// Teste 1: Versão do PHP
$phpVersion = phpversion();
$tests['PHP Version'] = [
    'status' => version_compare($phpVersion, '7.0.0', '>='),
    'message' => "PHP $phpVersion " . (version_compare($phpVersion, '7.0.0', '>=') ? '✓' : '✗ (necessário 7.0+)'),
    'required' => true
];

// Teste 2: Sessões
session_start();
$_SESSION['test'] = 'ok';
$tests['Sessões PHP'] = [
    'status' => isset($_SESSION['test']) && $_SESSION['test'] === 'ok',
    'message' => isset($_SESSION['test']) ? 'Funcionando ✓' : 'Não funcionando ✗',
    'required' => true
];

// Teste 3: JSON
$tests['JSON'] = [
    'status' => function_exists('json_encode') && function_exists('json_decode'),
    'message' => function_exists('json_encode') ? 'Habilitado ✓' : 'Não disponível ✗',
    'required' => true
];

// Teste 4: Diretório storage existe
$storageExists = is_dir(__DIR__ . '/storage');
$tests['Diretório storage'] = [
    'status' => $storageExists,
    'message' => $storageExists ? 'Existe ✓' : 'Não encontrado ✗',
    'required' => true
];

// Teste 5: Permissões de escrita
$storageWritable = is_writable(__DIR__ . '/storage');
$tests['Permissões de escrita'] = [
    'status' => $storageWritable,
    'message' => $storageWritable ? 'OK ✓' : 'Sem permissão de escrita ✗',
    'required' => true
];

// Teste 6: Criar arquivo de teste
$testFile = __DIR__ . '/storage/test_write.txt';
$canWrite = false;
if ($storageWritable) {
    $canWrite = @file_put_contents($testFile, 'test') !== false;
    if ($canWrite && file_exists($testFile)) {
        @unlink($testFile);
    }
}
$tests['Teste de escrita real'] = [
    'status' => $canWrite,
    'message' => $canWrite ? 'Consegue criar arquivos ✓' : 'Não consegue criar arquivos ✗',
    'required' => true
];

// Teste 7: Funções necessárias
$requiredFunctions = ['file_get_contents', 'file_put_contents', 'uniqid', 'date', 'array_filter', 'array_map'];
$missingFunctions = [];
foreach ($requiredFunctions as $func) {
    if (!function_exists($func)) {
        $missingFunctions[] = $func;
    }
}
$tests['Funções PHP'] = [
    'status' => empty($missingFunctions),
    'message' => empty($missingFunctions) ? 'Todas disponíveis ✓' : 'Faltando: ' . implode(', ', $missingFunctions) . ' ✗',
    'required' => true
];

// Teste 8: Timezone
$tests['Timezone'] = [
    'status' => true,
    'message' => 'Configurado: ' . date_default_timezone_get() . ' ✓',
    'required' => false
];

// Verificar se todos os testes obrigatórios passaram
foreach ($tests as $test) {
    if ($test['required'] && !$test['status']) {
        $allPassed = false;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Ambiente - Virtual Connections</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        h1 {
            color: #1f2937;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .result {
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-size: 18px;
            font-weight: 600;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            border: 2px solid #10b981;
        }
        .error {
            background: #fee2e2;
            color: #991b1b;
            border: 2px solid #ef4444;
        }
        .test-item {
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .test-item.pass {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
        }
        .test-item.fail {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
        }
        .test-name {
            font-weight: 600;
            color: #1f2937;
        }
        .test-message {
            color: #6b7280;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s;
            margin-right: 10px;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        }
        .warning {
            background: #fef3c7;
            color: #92400e;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #f59e0b;
        }
        .info {
            background: #dbeafe;
            color: #1e40af;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Teste de Ambiente</h1>
        <p class="subtitle">Virtual Connections - Verificação de Requisitos</p>

        <div class="result <?= $allPassed ? 'success' : 'error' ?>">
            <?php if ($allPassed): ?>
                ✓ Ambiente Configurado Corretamente!
                <div style="font-size: 14px; font-weight: normal; margin-top: 8px;">
                    Todos os requisitos foram atendidos. Você pode começar a usar o Virtual Connections.
                </div>
            <?php else: ?>
                ✗ Problemas Detectados
                <div style="font-size: 14px; font-weight: normal; margin-top: 8px;">
                    Corrija os problemas abaixo antes de usar a aplicação.
                </div>
            <?php endif; ?>
        </div>

        <h2 style="margin-bottom: 20px; color: #1f2937;">Resultados dos Testes:</h2>

        <?php foreach ($tests as $name => $test): ?>
            <div class="test-item <?= $test['status'] ? 'pass' : 'fail' ?>">
                <div>
                    <div class="test-name"><?= $name ?></div>
                    <div class="test-message"><?= $test['message'] ?></div>
                </div>
                <div style="font-size: 24px;">
                    <?= $test['status'] ? '✓' : '✗' ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (!$allPassed): ?>
            <div class="warning">
                <strong>⚠️ Ação Necessária:</strong><br>
                Corrija os problemas acima antes de usar a aplicação. Consulte o arquivo INSTALACAO.txt para mais detalhes.
            </div>
        <?php endif; ?>

        <div class="info">
            <strong>🛡️ Segurança:</strong><br>
            Remova este arquivo (test.php) após verificar que tudo está funcionando corretamente!
        </div>

        <div style="margin-top: 30px;">
            <?php if ($allPassed): ?>
                <a href="index.php" class="btn">Ir para a Aplicação →</a>
            <?php endif; ?>
            <a href="INSTALACAO.txt" class="btn btn-secondary">Ver Instruções</a>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e5e7eb; color: #6b7280; font-size: 14px;">
            <strong>Informações do Servidor:</strong><br>
            Servidor: <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido' ?><br>
            PHP: <?= $phpVersion ?><br>
            Sistema: <?= PHP_OS ?><br>
            Caminho: <?= __DIR__ ?>
        </div>
    </div>
</body>
</html>
