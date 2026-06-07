<?php
require_once 'db.php';

class Auth {
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = getDbConnection();
    }

    public function login($email, $password, $role = null) {
        $db = getDbConnection();
        
        error_log("=== INÍCIO DO PROCESSO DE LOGIN ===");
        error_log("Email: $email");
        error_log("Role solicitado: $role");
        
        // Se for superadmin, trata especialmente
        if ($role === 'superadmin' && $email === 'recursoshumanos@sysmanager.com.br' && $password === 'SysManager25') {
            $_SESSION['user_id'] = 1;
            $_SESSION['user_name'] = 'Recursos Humanos';
            $_SESSION['user_role'] = 'superadmin';
            $_SESSION['user_email'] = $email;
            error_log("Login superadmin bem sucedido");
            return true;
        }

        $user = null;
        
        // Para candidatos, procura APENAS na tabela candidates
        if ($role === 'candidate') {
            error_log("Procurando usuário na tabela candidates");
            $stmt = $db->prepare("SELECT * FROM candidates WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                error_log("Candidato encontrado - ID: {$user['id']}, Nome: {$user['name']}");
                
                // Verifica a senha diretamente
                if ($password === $user['password']) {
                    error_log("Senha correta para o candidato");
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = 'candidate';
                    return true;
                } else {
                    error_log("Senha incorreta para o candidato");
                }
            } else {
                error_log("Candidato não encontrado com este email");
            }
        }
        // Para seletores, procura APENAS na tabela users
        else if ($role === 'selector') {
            error_log("Procurando usuário na tabela users (selector)");
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'selector'");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                error_log("Seletor encontrado - ID: {$user['id']}, Nome: {$user['name']}");
                
                // Verifica a senha diretamente
                if ($password === $user['password']) {
                    error_log("Senha correta para o seletor");
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = 'selector';
                    return true;
                } else {
                    error_log("Senha incorreta para o seletor");
                }
            } else {
                error_log("Seletor não encontrado com este email");
            }
        }
        
        error_log("=== FIM DO PROCESSO DE LOGIN - FALHOU ===");
        return false;
    }

    public function logout() {
        error_log("Logout do usuário: " . ($_SESSION['user_id'] ?? 'Não logado'));
        session_unset();
        session_destroy();
        session_write_close();
        setcookie(session_name(),'',0,'/');
    }

    public function isLoggedIn() {
        $loggedIn = isset($_SESSION['user_id']);
        error_log("Verificando login. Está logado? " . ($loggedIn ? 'Sim' : 'Não'));
        error_log("Session ID: " . session_id());
        error_log("Sessão atual: " . json_encode($_SESSION));
        return $loggedIn;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }

    public function requireRole($role) {
        if (!$this->isLoggedIn() || $this->getCurrentUserRole() !== $role) {
            error_log("Acesso negado. Role requerida: $role, Role atual: " . $this->getCurrentUserRole());
            header('Location: login.php?error=access_denied');
            exit;
        }
    }

    public function getCurrentUserId() {
        if (!$this->isLoggedIn()) {
            error_log("Tentativa de pegar ID sem estar logado");
            return null;
        }
        return $_SESSION['user_id'];
    }

    public function getCurrentUserRole() {
        return $_SESSION['user_role'] ?? null;
    }

    public function getCurrentUserName() {
        return $_SESSION['user_name'] ?? null;
    }

    public function getRedirectPage() {
        $role = $this->getCurrentUserRole();
        switch ($role) {
            case 'admin':
                return 'superadmin_panel.php';
            case 'selector':
                return 'selector_panel.php';
            case 'candidate':
                return 'candidate_panel.php';
            case 'superadmin':
                return 'superadmin_panel.php';
            default:
                return 'login.php';
        }
    }
}
?>
