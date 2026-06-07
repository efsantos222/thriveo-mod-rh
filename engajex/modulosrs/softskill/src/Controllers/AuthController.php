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
                $_SESSION['ss_id'] = $user->id;
                $_SESSION['ss_user_name'] = $user->name;
                $_SESSION['ss_role'] = $user->role;

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
        $_SESSION = array();
        if (session_id()) {
            session_destroy();
        }
        // Redirect to main logout (3 levels up from BASE_URL: /modulosrs/softskill/)
        header("Location: " . BASE_URL . "../../logout.php");
        exit;
    }

}
