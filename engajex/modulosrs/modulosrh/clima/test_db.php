<?php
require_once 'config/config.php';
require_once 'config/database.php';

try {
    // Teste de conexão
    $db = Database::getInstance()->getConnection();
    echo "✅ Conexão com o banco de dados estabelecida com sucesso!<br>";
    
    // Verificar tabelas
    $tables = ['administradores', 'respondentes', 'perguntas', 'respostas'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabela '$table' existe<br>";
            
            // Verificar registros
            $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo "  └─ Contém $count registro(s)<br>";
        } else {
            echo "❌ Tabela '$table' não encontrada<br>";
        }
    }
    
    // Verificar índices
    echo "<br>Verificando índices:<br>";
    $indices = [
        'respondentes' => ['idx_respondentes_codigo'],
        'respostas' => ['idx_respostas_data']
    ];
    
    foreach ($indices as $table => $indexList) {
        $stmt = $db->query("SHOW INDEX FROM $table");
        $existingIndices = $stmt->fetchAll(PDO::FETCH_COLUMN, 2);
        
        foreach ($indexList as $index) {
            if (in_array($index, $existingIndices)) {
                echo "✅ Índice '$index' existe na tabela '$table'<br>";
            } else {
                echo "❌ Índice '$index' não encontrado na tabela '$table'<br>";
            }
        }
    }
    
    // Verificar administrador padrão
    $stmt = $db->query("SELECT email FROM administradores WHERE email = 'admin@exemplo.com'");
    if ($stmt->rowCount() > 0) {
        echo "<br>✅ Usuário administrador padrão existe<br>";
    } else {
        echo "<br>❌ Usuário administrador padrão não encontrado<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "<br>";
    echo "Detalhes do erro:<br>";
    echo "- Código: " . $e->getCode() . "<br>";
    echo "- Arquivo: " . $e->getFile() . "<br>";
    echo "- Linha: " . $e->getLine() . "<br>";
}
