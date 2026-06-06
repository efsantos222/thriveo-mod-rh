<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define Base URL dynamically
// If script is at /softskill/public/index.php, root is /softskill/
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = str_replace('/public', '', $scriptDir);
// Ensure trailing slash
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

// Middleware for access control
function checkAuth($role = null)
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "login");
        exit;
    }
    $userRole = $_SESSION['role'] ?? null;
    if ($role && $userRole !== $role) {
        echo "Acesso negado.";
        exit;
    }
}


// Routing
switch ($route) {
    case '':
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login");
        } else {
            $role = $_SESSION['role'] ?? null;
            if ($role === 'admin')
                header("Location: " . BASE_URL . "admin/dashboard");
            elseif ($role === 'recruiter')
                header("Location: " . BASE_URL . "recruiter/dashboard");
            elseif ($role === 'candidate')
                header("Location: " . BASE_URL . "candidate/dashboard");
            else {
                // Invalid state, logout
                header("Location: " . BASE_URL . "logout");
            }
        }
        break;


    case 'login':
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
            $controller->login();
        else
            $controller->showLogin();
        break;

    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;

    default:
        // Admin Routes
        if (strpos($route, 'admin/') === 0) {
            checkAuth('admin');
            $controller = new AdminController();
            $action = substr($route, 6);
            // Handle dashes to camelCase if needed, but simple matching is fine for now
            if (method_exists($controller, $action))
                $controller->$action();
            else
                $controller->dashboard(); // default
        }
        // Recruiter Routes
        elseif (strpos($route, 'recruiter/') === 0) {
            checkAuth('recruiter');
            $controller = new RecruiterController();
            $action = substr($route, 10);
            if (method_exists($controller, $action))
                $controller->$action();
            else
                $controller->dashboard();
        }
        // Candidate Routes
        elseif (strpos($route, 'candidate/') === 0) {
            checkAuth('candidate');
            $controller = new CandidateController();
            $action = substr($route, 10);
            if (method_exists($controller, $action))
                $controller->$action();
            else
                $controller->dashboard();
        }
        // Fallback
        else {
            echo "404 - Página não encontrada";
        }
        break;
}
