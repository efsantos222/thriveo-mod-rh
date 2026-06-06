<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico e Correção de Banco de Dados</h1>";

require_once 'config/database.php';
$config = require 'config/database.php';

try {
    echo "1. Tentando conectar ao banco de dados...<br>";
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    echo "<span style='color:green'>Conexão bem sucedida.</span><br><br>";

    // 2. Verificar/Criar tabela Empresas
    echo "2. Verificando tabela 'empresas'...<br>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS empresas (
        id_empresa INT PRIMARY KEY AUTO_INCREMENT,
        nome_empresa VARCHAR(150) NOT NULL,
        cnpj VARCHAR(20),
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ultima_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "<span style='color:green'>Tabela 'empresas' ok.</span><br><br>";

    // 3. Verificar coluna 'id_empresa' em 'usuarios'
    echo "3. Verificando coluna 'id_empresa' na tabela 'usuarios'...<br>";
    $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'id_empresa'");
    if ($stmt->rowCount() == 0) {
        echo "Coluna não encontrada. Adicionando coluna 'id_empresa'...<br>";
        try {
            $pdo->exec("ALTER TABLE usuarios ADD COLUMN id_empresa INT NULL AFTER perfil");
            $pdo->exec("ALTER TABLE usuarios ADD FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa)");
            echo "<span style='color:green'>Coluna 'id_empresa' adicionada com sucesso.</span><br>";
        } catch (Exception $e) {
            echo "<span style='color:red'>Erro ao adicionar coluna: " . $e->getMessage() . "</span><br>";
        }
    } else {
        echo "<span style='color:blue'>Coluna 'id_empresa' já existe.</span><br>";
    }
    echo "<br>";

    // 4. Atualizar ENUM de perfil
    echo "4. Atualizando opções de 'perfil' (ENUM)...<br>";
    try {
        $pdo->exec("ALTER TABLE usuarios MODIFY COLUMN perfil ENUM('administrador', 'master_coach', 'coach', 'coachee', 'colaborador', 'gestor') NOT NULL DEFAULT 'coach'");
        echo "<span style='color:green'>Coluna 'perfil' atualizada.</span><br>";
    } catch (Exception $e) {
        echo "<span style='color:red'>Erro ao atualizar perfil (pode ser ignorado se já estiver atualizado): " . $e->getMessage() . "</span><br>";
    }

    echo "<br><h2>PROCESSO CONCLUÍDO</h2>";
    echo "Tente fazer login novamente.";

} catch (PDOException $e) {
    echo "<h2 style='color:red'>ERRO CRÍTICO DE CONEXÃO:</h2>";
    echo $e->getMessage();
    echo "<br>Verifique as credenciais no arquivo config/database.php no servidor.";
}
?>