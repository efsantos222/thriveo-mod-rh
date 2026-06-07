<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Teste de Configuração<br>-----------------<br>";

// 1. Testar inclusão de arquivos
echo "1. Testando include de arquivos...<br>";
$configFile = '../config/config.php';
$dbFile = '../config/database.php';

if (file_exists($configFile)) {
    echo "✓ config.php encontrado<br>";
    require_once $configFile;
    echo "✓ config.php incluído com sucesso<br>";
} else {
    echo "✗ config.php não encontrado<br>";
}

if (file_exists($dbFile)) {
    echo "✓ database.php encontrado<br>";
    require_once $dbFile;
    echo "✓ database.php incluído com sucesso<br>";
} else {
    echo "✗ database.php não encontrado<br>";
}

// 2. Testar constantes
echo "<br>2. Testando constantes...<br>";
echo "BASE_URL = " . (defined('BASE_URL') ? BASE_URL : 'não definida') . "<br>";
echo "DB_HOST = " . (defined('DB_HOST') ? DB_HOST : 'não definida') . "<br>";
echo "DB_NAME = " . (defined('DB_NAME') ? DB_NAME : 'não definida') . "<br>";

// 3. Testar sessão
echo "<br>3. Testando sessão...<br>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✓ Sessão está ativa<br>";
} else {
    echo "✗ Sessão não está ativa<br>";
    session_start();
}
$_SESSION['test'] = 'ok';
echo "Valor da sessão: " . ($_SESSION['test'] ?? 'não definido') . "<br>";

// 4. Testar conexão com banco usando a classe Database
echo "<br>4. Testando conexão com o banco usando a classe Database...<br>";
try {
    $db = Database::getInstance()->getConnection();
    echo "✓ Conexão estabelecida com sucesso<br>";
    
    // Testar consulta
    $stmt = $db->query("SELECT COUNT(*) as total FROM respondentes");
    $result = $stmt->fetch();
    echo "Total de respondentes: " . $result['total'] . "<br>";
    
    // Testar estrutura da tabela
    $stmt = $db->query("DESCRIBE respondentes");
    echo "<br>Estrutura da tabela respondentes:<br>";
    while ($row = $stmt->fetch()) {
        echo "- {$row['Field']}: {$row['Type']}<br>";
    }
    
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "<br>";
}

// 5. Testar função de redirecionamento
echo "<br>5. Função de redirecionamento...<br>";
echo "Se você chamar redirect('/'), irá para: " . rtrim(BASE_URL, '/') . "/<br>";
