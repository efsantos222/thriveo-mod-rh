<?php
require_once 'config.php';

echo "<pre>";
echo "Testing Login Logic...\n";

$email = 'ezequiel.santos@gmail.com';
$password = 'Kyew1802';

echo "Checking user: $email\n";

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        echo "User found! ID: " . $user['id'] . "\n";
        echo "Role: " . $user['role'] . "\n";
        echo "Hash: " . mb_substr($user['password_hash'], 0, 10) . "...\n";

        if (password_verify($password, $user['password_hash'])) {
            echo "Password Verify: SUCCESS\n";
            $_SESSION['test_sess_var'] = 'working';
            echo "Session ID: " . session_id() . "\n";
            echo "Session Name: " . session_name() . "\n";
        } else {
            echo "Password Verify: FAILED\n";
            echo "New Hash for '$password': " . password_hash($password, PASSWORD_DEFAULT) . "\n";
        }
    } else {
        echo "User NOT found in database.\n";
    }

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>