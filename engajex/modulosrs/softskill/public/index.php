<?php
// Force session to be shared across subdirectories
session_set_cookie_params([
    'path' => '/',
    'samesite' => 'Lax'
]);
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define Base URL dynamically
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = str_replace('/public', '', $scriptDir);
$baseUrl = rtrim($baseUrl, '/') . '/';
define('BASE_URL', $baseUrl);

require_once '../config/database.php';

// Simple Autoloader
spl_autoload_register(function ($class_name) {
    if (file_exists('../src/Controllers/' . $class_name . '.php')) {
        require_once '../src/Controllers/' . $class_name . '.php';
    } elseif (file_exists('../src/Models/' . $class_name . '.php')) {
        require_once '../src/Models/' . $class_name . '.php';
    } elseif (file_exists('../src/Services/' . $class_name . '.php')) {
        require_once '../src/Services/' . $class_name . '.php';
    }
});

$route = isset($_GET['route']) ? $_GET['route'] : '';
$route = rtrim($route, '/');

// --- SSO BRIDGE START ---
// Detect main system login and AUTO-REDIRECT
// Skip SSO bridge if the user logged in directly via ss_users (flag set by login.php)
if (isset($_SESSION['user_id']) && !isset($_SESSION['ss_direct_user'])) {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Always fetch main user data to ensure sync
    $stmt_main = $conn->prepare("SELECT name, email, company_id FROM users WHERE id = ?");
    $stmt_main->execute([$_SESSION['user_id']]);
    $main_user = $stmt_main->fetch(PDO::FETCH_ASSOC);

    if ($main_user) {
        // Sync main session email/name if missing
        if (!isset($_SESSION['email'])) $_SESSION['email'] = $main_user['email'];
        if (!isset($_SESSION['name'])) $_SESSION['name'] = $main_user['name'];
        if (!isset($_SESSION['company_id'])) $_SESSION['company_id'] = $main_user['company_id'];

        $email = strtolower($main_user['email']);
        $main_role = $_SESSION['role'] ?? 'employee';

        // Map Roles
        $role_map = ['admin' => 'admin', 'responsible' => 'recruiter', 'company_admin' => 'recruiter', 'manager' => 'recruiter'];
        $target_ss_role = $role_map[$main_role] ?? 'candidate';
        
        // Special case for specific email to be admin
        if ($email === 'ezequiel.santos@gmail.com') {
            $target_ss_role = 'admin';
        }

        // Provision or update ss_users table
        $stmt_ss_user = $conn->prepare("SELECT id, role FROM ss_users WHERE email = ?");
        $stmt_ss_user->execute([$email]);
        $ss_user = $stmt_ss_user->fetch(PDO::FETCH_ASSOC);

        if (!$ss_user) {
            $stmt_insert = $conn->prepare("INSERT INTO ss_users (name, email, password, role, company_id) VALUES (?, ?, ?, ?, ?)");
            $dummy_pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            $stmt_insert->execute([$main_user['name'], $email, $dummy_pass, $target_ss_role, $main_user['company_id']]);
            $ss_id = $conn->lastInsertId();
            $ss_role = $target_ss_role;
        } else {
            $ss_id = $ss_user['id'];
            $ss_role = $ss_user['role']; // Keep existing role unless it needs to be updated
            
            // Update role and company_id to keep in sync with main system
            $stmt_update = $conn->prepare("UPDATE ss_users SET role = ?, company_id = ? WHERE id = ?");
            $stmt_update->execute([$target_ss_role, $main_user['company_id'], $ss_id]);
            $ss_role = $target_ss_role;
        }

        // Set INTERNAL sessions for SoftSkill module
        $_SESSION['ss_id'] = $ss_id;
        $_SESSION['ss_role'] = $ss_role;
        $_SESSION['ss_user_name'] = $main_user['name'];
        $_SESSION['ss_company_id'] = $main_user['company_id'];

        // IF WE ARE ON LOGIN PAGE AND ALREADY LOGGED VIA BRIDGE -> GO TO DASHBOARD
        if ($route === 'login' || $route === '') {
            $redir = ($ss_role === 'admin') ? "admin/dashboard" : (($ss_role === 'recruiter') ? "recruiter/dashboard" : "candidate/dashboard");
            header("Location: " . BASE_URL . $redir);
            exit;
        }
    }
}
// --- SSO BRIDGE END ---

// Middleware for access control
function checkAuth($role = null)
{
    if (($_SESSION['email'] ?? '') === 'ezequiel.santos@gmail.com' || ($_SESSION['role'] ?? '') === 'admin') {
        return;
    }
    if (!isset($_SESSION['ss_id'])) {
        header("Location: ../../../login.php");
        exit;
    }
    $userRole = $_SESSION['ss_role'] ?? 'candidate';
    if ($role && $userRole !== $role) {
        if ($userRole === 'admin') return;
        die("Acesso negado. Perfil atual: " . $userRole . " | Necessário: " . $role);
    }
}

// Routing
switch ($route) {
    case '':
    case 'login':
        // If ss_id is already set (by bridge or manual login), redirect to dashboard
        if (isset($_SESSION['ss_id'])) {
            $role = $_SESSION['ss_role'] ?? 'candidate';
            $redir = ($role === 'admin') ? "admin/dashboard" : (($role === 'recruiter') ? "recruiter/dashboard" : "candidate/dashboard");
            header("Location: " . BASE_URL . $redir);
            exit;
        }
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') $controller->login();
        else $controller->showLogin();
        break;

    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;

    default:
        if (strpos($route, 'admin/') === 0) {
            checkAuth('admin');
            $controller = new AdminController();
            $action = substr($route, 6);
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->dashboard();
        } elseif (strpos($route, 'recruiter/') === 0) {
            checkAuth('recruiter');
            $controller = new RecruiterController();
            $action = substr($route, 10);
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->dashboard();
        } elseif (strpos($route, 'candidate/') === 0) {
            checkAuth('candidate');
            $controller = new CandidateController();
            $action = substr($route, 10);
            if (method_exists($controller, $action)) $controller->$action();
            else $controller->dashboard();
        } else {
            echo "404 - Página não encontrada";
        }
        break;
}
