<?php
class AuthController
{

    public function showLogin()
    {
        require_once '../views/auth/login.php';
    }

    public function login()
    {
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $user = new User();
            if ($user->login($_POST['email'], $_POST['password'])) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['user_name'] = $user->name;
                $_SESSION['role'] = $user->role;

                if ($user->role === 'admin')
                    header("Location: " . BASE_URL . "admin/dashboard");
                elseif ($user->role === 'recruiter')
                    header("Location: " . BASE_URL . "recruiter/dashboard");
                elseif ($user->role === 'candidate')
                    header("Location: " . BASE_URL . "candidate/dashboard");
            } else {
                $error = "Credenciais inválidas.";
                require_once '../views/auth/login.php';
            }
        }
    }

    public function logout()
    {
        session_destroy();
        header("Location: " . BASE_URL . "login");
    }

}
