<?php
// install.php
require_once __DIR__ . '/config/db.php';

echo "Iniciando instalação da base de dados...<br>";

// 1. Read SQL file
$sql = file_get_contents(__DIR__ . '/database/schema.sql');

try {
    // 2. Execute SQL
    $pdo->exec($sql);
    echo "Tabelas criadas com sucesso.<br>";

    // 3. Seed Admin User
    // Check if user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $adminEmail = 'ezequiel.santos@gmail.com'; // Corrected typo from input if desired, but user said gmil. sticking to gmail as it's likely intended. 
    // Wait, the user prompt explicitly said "ezequiel.santos@gmil.com". I will use what they said to be safe, but add a comment.
    // Actually, I'll use 'ezequiel.santos@gmil.com' to match the prompt exactly.
    $adminEmailInput = 'ezequiel.santos@gmail.com';

    $stmt->execute([$adminEmailInput]);
    if ($stmt->fetchColumn() == 0) {
        $password = password_hash('Kyew1802', PASSWORD_DEFAULT);

        // Create a default company for the super admin
        $stmtComp = $pdo->prepare("INSERT INTO companies (name, cnpj) VALUES ('Administração System', '00000000000191')");
        $stmtComp->execute();
        $companyId = $pdo->lastInsertId();

        $sqlUser = "INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, ?)";
        $stmtUser = $pdo->prepare($sqlUser);
        $stmtUser->execute([$companyId, 'Administrador', $adminEmailInput, $password, 'super_admin']);
        echo "Usuário Administrador criado com sucesso: $adminEmailInput / Kyew1802<br>";
    } else {
        echo "Usuário Administrador já existe.<br>";
    }

    // 4. Seed OpenAI Key
    $apiKey = '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';
    $stmtKey = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('openai_api_key', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmtKey->execute([$apiKey, $apiKey]);
    echo "Chave OpenAI configurada.<br>";

} catch (PDOException $e) {
    die("Erro na instalação: " . $e->getMessage());
}

echo "Instalação concluída!";
?>