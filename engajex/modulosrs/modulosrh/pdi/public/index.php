<?php
// Garantir que nenhum output seja enviado antes dos headers
ob_start();

// Definir constantes de caminho
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');

// Carregar configurações e inicializar conexões
require_once SRC_PATH . '/bootstrap.php';

// Rotas protegidas que requerem autenticação
$protected_routes = ['dashboard', 'pdi', 'admin', 'profile', 'pdi/novo', 'courses', 'courses/manage'];

// Obter a rota atual
$route = $_GET['route'] ?? 'home';

// Verificar autenticação para rotas protegidas
if (in_array($route, $protected_routes) && !isset($_SESSION['user'])) {
    header('Location: ?route=login');
    exit;
}

// Mapear rotas para arquivos
$route_map = [
    'home' => 'landing.php',
    'dashboard' => 'dashboard.php',
    'login' => 'auth/login.php',
    'logout' => 'auth/logout.php',
    'register' => 'auth/register.php',
    'forgot-password' => 'auth/forgot-password.php',
    'pdi' => 'pdi/index.php',
    'pdi/novo' => 'pdi/novo.php',
    'pdi/save' => 'pdi/save.php',
    'admin' => 'admin/index.php',
    'courses' => 'courses/catalog.php',
    'courses/catalog' => 'courses/catalog.php',
    'courses/manage' => 'courses/manage.php',
    'courses/view' => 'courses/view.php',
    'courses/module' => 'courses/module.php',
    // Admin Routes
    'admin/companies' => 'admin/companies.php',
    'admin/settings' => 'admin/settings.php',
    // Company Routes
    'company/users' => 'company/users.php',
    'company/identity' => 'company/identity.php',
    'company/reports' => 'company/reports.php',
    'pdi/generate' => 'pdi/generate.php'
];

// Rotas que usam controladores
$controller_routes = [
    'courses/create' => ['file' => 'controllers/courses.php', 'action' => 'create'],
    'courses/update' => ['file' => 'controllers/courses.php', 'action' => 'update'],
    'courses/delete' => ['file' => 'controllers/courses.php', 'action' => 'delete'],
    'courses/get' => ['file' => 'controllers/courses.php', 'action' => 'get'],
    'courses/modules' => ['file' => 'controllers/courses.php', 'action' => 'modules'],
    'courses/getModule' => ['file' => 'controllers/courses.php', 'action' => 'getModule'],
    'courses/createModule' => ['file' => 'controllers/courses.php', 'action' => 'createModule'],
    'courses/updateModule' => ['file' => 'controllers/courses.php', 'action' => 'updateModule'],
    'courses/deleteModule' => ['file' => 'controllers/courses.php', 'action' => 'deleteModule']
];

// Verificar se é uma rota de controlador
if (isset($controller_routes[$route])) {
    $_GET['action'] = $controller_routes[$route]['action'];
    require_once $controller_routes[$route]['file'];
    handleCourseAction();
    exit;
}

// Tentar carregar o template
$template_path = null;

// 1. Verificar no mapa de rotas
if (isset($route_map[$route])) {
    $template_file = TEMPLATES_PATH . '/' . $route_map[$route];
    if (file_exists($template_file)) {
        $template_path = $template_file;
    }
}

// 2. Se não encontrou no mapa, tentar diretamente
if (!$template_path) {
    $template_file = TEMPLATES_PATH . '/' . str_replace(['..', '\\'], '', $route) . '.php';
    if (file_exists($template_file)) {
        $template_path = $template_file;
    }
}

// 3. Se ainda não encontrou, tentar como subdiretório
if (!$template_path) {
    $parts = explode('/', $route);
    if (count($parts) > 1) {
        $subdir_path = TEMPLATES_PATH . '/' . implode('/', $parts) . '.php';
        if (file_exists($subdir_path)) {
            $template_path = $subdir_path;
        }
    }
}

// Carregar o template ou mostrar 404
if ($template_path) {
    error_log("Loading template: " . $template_path);
    require_once $template_path;
} else {
    error_log("Template not found for route: " . $route);
    error_log("Tried paths:");
    if (isset($route_map[$route])) {
        error_log("- " . TEMPLATES_PATH . '/' . $route_map[$route]);
    }
    error_log("- " . TEMPLATES_PATH . '/' . str_replace(['..', '\\'], '', $route) . '.php');
    if (isset($subdir_path)) {
        error_log("- " . $subdir_path);
    }

    http_response_code(404);
    require_once TEMPLATES_PATH . '/404.php';
}

ob_end_flush();
