<?php
// Credentials for new admin
$email = 'ezequiel.santos@gmail.com';
$password = 'Kyew1802';
$name = 'Ezequiel Santos';

// Database Credentials (mirrored from config/db.php)
$host = 'localhost';
$dbname = 'efsantos_plano';
$username = 'efsantos_plano';
$db_password = 'Kyew1802';

// Generate Hash
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "<h1>Criação de Administrador</h1>";
echo "<strong>Email:</strong> $email<br>";
echo "<strong>Senha:</strong> $password<br>";
echo "<strong>Hash Gerado:</strong> $hashedPassword<br><br>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Check/Create Company
    $stmt = $pdo->query("SELECT id FROM companies LIMIT 1");
    $companyId = $stmt->fetchColumn();

    if (!$companyId) {
        $pdo->exec("INSERT INTO companies (name) VALUES ('Minha Consultoria')");
        $companyId = $pdo->lastInsertId();
        echo "Empresa padrão criada (ID: $companyId).<br>";
    } else {
        echo "Usando empresa existente (ID: $companyId).<br>";
    }

    // 2. Check User
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "WARNING: Usuário já existe no banco de dados.<br>";
    } else {
        // 3. Insert User
        $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role, status, trial_start_date) VALUES (?, ?, ?, ?, 'admin', 'active', CURDATE())");
        $stmt->execute([$companyId, $name, $email, $hashedPassword]);
        echo "SUCESSO: Administrador inserido no banco de dados automaticamente.<br>";
    }

} catch (PDOException $e) {
    echo "<hr>";
    echo "<h3>Não foi possível conectar ao banco localmente</h3>";
    echo "Isso é normal se você não tiver o banco MySQL rodando na sua máquina ou se as credenciais forem diferentes da produção.<br>";
    echo "<strong>Abaixo está o comando SQL para você rodar no seu banco de dados (phpMyAdmin ou similar):</strong><br><br>";

    echo "<textarea rows='10' cols='80' style='width:100%; font-family:monospace; padding:10px;'>";
    echo "-- 1. Garantir que exite uma empresa\n";
    echo "INSERT INTO companies (name) SELECT 'Minha Consultoria' WHERE NOT EXISTS (SELECT * FROM companies LIMIT 1);\n\n";

    echo "-- 2. Inserir o administrador\n";
    echo "INSERT INTO users (company_id, name, email, password, role, status, trial_start_date)\n";
    echo "VALUES (\n";
    echo "    (SELECT id FROM companies LIMIT 1),\n";
    echo "    '$name',\n";
    echo "    '$email',\n";
    echo "    '$hashedPassword',\n";
    echo "    'admin',\n";
    echo "    'active',\n";
    echo "    CURDATE()\n";
    echo ");";
    echo "</textarea>";

    echo "<br><br>Erro técnico: " . $e->getMessage();
}
?>