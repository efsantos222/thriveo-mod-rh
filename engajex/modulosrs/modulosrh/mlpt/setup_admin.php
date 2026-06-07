<?php
require_once 'config/database.php';

$email = 'ezequiel.santos@gmail.com';
$password = 'Kyew1802';
$name = 'Proftest Admin';

// Check if admin exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->rowCount() > 0) {
    echo "O Administrador já existe.";
} else {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, 'admin')";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashed_password
        ]);
        echo "Administrador criado com sucesso! <br>";
        echo "Email: " . $email . "<br>";
        echo "Senha: " . $password . "<br>";
        echo "<a href='login.php'>Ir para Login</a>";
    } catch (PDOException $e) {
        echo "Erro ao criar administrador: " . $e->getMessage();
    }
}
?>