<?php
// Carregar variáveis de ambiente
$config = require_once __DIR__ . '/../config/database.php';

// Configurar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Criar diretório de logs se não existir
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

// Autoloader para classes
spl_autoload_register(function ($class) {
    // Converter namespace para caminho de arquivo
    $prefix = 'PDI\\';
    $base_dir = __DIR__ . '/';

    // Verificar se a classe usa o namespace PDI
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Pegar o caminho relativo da classe
    $relative_class = substr($class, $len);

    // Converter namespace para caminho de arquivo
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Se o arquivo existir, carregá-lo
    if (file_exists($file)) {
        require $file;
    }
});

// Conectar ao banco de dados
try {
    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=%s",
        $config['host'],
        $config['dbname'],
        $config['charset']
    );

    $pdo = new PDO(
        $dsn,
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    error_log("Database connection successful");

    // Verificar se a tabela users existe
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    if (empty($tables)) {
        error_log("Creating users table");
        
        // Criar tabela users
        $pdo->exec("
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('admin', 'manager', 'employee', 'coach') NOT NULL DEFAULT 'employee',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Inserir usuário admin
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, role)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            'Administrador',
            'admin@proftest.com.br',
            'admin123', // Senha direta para teste
            'admin'
        ]);
        
        error_log("Admin user created with direct password");
    } else {
        error_log("Users table already exists");
        
        // Verificar se admin existe
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute(['admin@proftest.com.br']);
        $admin = $stmt->fetch();
        
        if (!$admin) {
            error_log("Creating missing admin user");
            
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password_hash, role)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                'Administrador',
                'admin@proftest.com.br',
                'admin123', // Senha direta para teste
                'admin'
            ]);
            
            error_log("Admin user created with direct password");
        }
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("Erro de conexão: " . $e->getMessage());
}

// Iniciar ou resumir sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Utility functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
}

function requireRole($roles) {
    requireLogin();
    $userRole = getUserRole();
    if (!in_array($userRole, (array)$roles)) {
        header('Location: /unauthorized');
        exit;
    }
}
