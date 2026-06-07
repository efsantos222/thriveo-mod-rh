<?php
// setup_admin.php
require_once 'config.php';

$email = 'ezequiel.santos@gmail.com';
$password = 'Kyew1802';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        // Update existing admin
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin', name = 'Administrador' WHERE email = ?");
        $stmt->execute([$hash, $email]);
        echo "Admin user updated successfully. Password reset to: $password";
    } else {
        // Create new admin
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES ('Administrador', ?, ?, 'admin')");
        $stmt->execute([$email, $hash]);
        echo "Admin user created successfully. Email: $email, Password: $password";
    }

    // Also ensure the "Kyew1802" responsible user exists if needed, or just the admin
    // User requested "efsantos22_engaje" (db user) and credentials for admin.

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>