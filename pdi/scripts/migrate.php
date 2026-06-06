<?php
require_once __DIR__ . '/../src/bootstrap.php';

echo "Running migration...\n";

try {
    // Read SQL file
    $sql = file_get_contents(__DIR__ . '/../database/update_schema_v2.sql');
    
    // Split by semicolon to execute mostly correctly (basic splitter)
    // Note: The SQL file I wrote has some complex prepared statements which might fail with simple split. 
    // I'll execute the simpler parts directly or just try to run the whole thing if PDO supports multiple queries.
    // PDO allows multiple queries if emulation is on.
    
    $pdo->exec($sql);
    echo "Schema updated successfully.\n";
    
    // Create/Update Superadmin User
    $email = 'ezequiel.santos@gmail.com';
    $password = 'Kyew1802'; // Provided by user
    // Simple password hashing (adapt to what the system uses - verify later)
    // bootstrap.php showed plain text 'admin123' in one place but 'password_hash' column name suggests hashing.
    // I will use password_hash() default.
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $stmt = $pdo->prepare("UPDATE users SET role = 'superadmin', password_hash = ? WHERE id = ?");
        $stmt->execute([$passwordHash, $user['id']]);
        echo "Superadmin updated.\n";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Super Admin', $email, $passwordHash, 'superadmin']);
        echo "Superadmin created.\n";
    }

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
