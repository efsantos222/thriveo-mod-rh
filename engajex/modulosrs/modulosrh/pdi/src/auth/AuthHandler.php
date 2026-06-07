<?php
namespace PDI\Auth;

use PDO;

class AuthHandler
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function login($email, $password)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Set primary session vars
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['company_id'] = $user['company_id'] ?? null;

            // Legacy support array
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'is_admin' => $user['role'] === 'superadmin',
                'role' => $user['role'],
                'company_id' => $user['company_id'] ?? null
            ];

            return true;
        }
        return false;
    }

    public function logout()
    {
        session_destroy();
        session_start();
    }

    // Kept for compatibility but should use User model
    public function createAdmin($email, $password)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES ('Admin', ?, ?, 'superadmin')");
            $stmt->execute([$email, $hash]);
        }
    }
}
