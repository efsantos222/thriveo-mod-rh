<?php

class AuthController
{
    public static function login()
    {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            redirect("/{$user['role']}/dashboard");
        } else {
            view('auth/login', ['error' => 'Credenciais inválidas.']);
        }
    }

    public static function logout()
    {
        session_destroy();
        redirect('/login');
    }
}
