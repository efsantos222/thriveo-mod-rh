<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/config.php';
require_once '../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Conexão com o banco de dados estabelecida com sucesso!<br>";
    
    // Testar a tabela respondentes
    $stmt = $db->query("SELECT COUNT(*) as total FROM respondentes");
    $result = $stmt->fetch();
    echo "Total de respondentes: " . $result['total'] . "<br>";
    
    // Testar a sessão
    $_SESSION['test'] = 'ok';
    echo "Sessão funcionando corretamente!<br>";
    
    // Testar redirecionamento
    echo "URL base configurada como: " . BASE_URL . "<br>";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "<br>";
}
