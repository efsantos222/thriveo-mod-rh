<?php
// Production Error Handling
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL); // Log errors, don't display

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/helpers.php';

// Simple Router
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// 1. Tentar pegar rota via parâmetro GET 'r' (Bala de prata para Hostgator)
if (isset($_GET['r'])) {
    $uri = $_GET['r'];
}
// 2. Tentar via PATH_INFO
elseif (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) {
    $uri = $_SERVER['PATH_INFO'];
}
// 3. Fallback
elseif (strpos($uri, $scriptName) === 0 && $scriptName !== '/') {
    $uri = substr($uri, strlen($scriptName));
}

// Remove trailing slash
if ($uri !== '/' && substr($uri, -1) === '/') {
    $uri = substr($uri, 0, -1);
}

// Ensure URI starts with /
if (substr($uri, 0, 1) !== '/') {
    $uri = '/' . $uri;
}

// Routes
if ($uri === '/' || $uri === '/login') {
    if (is_authenticated()) {
        $role = current_user()['role'];
        redirect("/{$role}/dashboard");
    }

    if ($method === 'GET') {
        view('auth/login');
    } elseif ($method === 'POST') {
        require __DIR__ . '/src/Controllers/AuthController.php';
        AuthController::login();
    }
} elseif ($uri === '/logout') {
    require __DIR__ . '/src/Controllers/AuthController.php';
    AuthController::logout();
}
// Admin Routes
elseif (strpos($uri, '/admin') === 0) {
    require_role('admin');
    require __DIR__ . '/src/Controllers/AdminController.php';

    if ($uri === '/admin/dashboard')
        AdminController::dashboard();
    elseif ($uri === '/admin/recruiters')
        AdminController::manageRecruiters();
    elseif ($uri === '/admin/recruiters/create')
        AdminController::createRecruiter();
    elseif ($uri === '/admin/settings')
        AdminController::settings();
}
// Recruiter Routes
elseif (strpos($uri, '/recruiter') === 0) {
    require_role('recruiter');
    require __DIR__ . '/src/Controllers/RecruiterController.php';

    if ($uri === '/recruiter/dashboard')
        RecruiterController::dashboard();
    elseif ($uri === '/recruiter/tests')
        RecruiterController::tests();
    elseif ($uri === '/recruiter/tests/create')
        RecruiterController::createTest();
    elseif ($uri === '/recruiter/tests/generate')
        RecruiterController::generateTestAI(); // AJAX/POST
    elseif ($uri === '/recruiter/candidates')
        RecruiterController::manageCandidates();
    elseif ($uri === '/recruiter/candidates/create')
        RecruiterController::createCandidate();
    elseif ($uri === '/recruiter/assignments')
        RecruiterController::listAssignments();
    elseif ($uri === '/recruiter/assign')
        RecruiterController::assignTest();
    elseif (preg_match('/\/recruiter\/assignment\/(\d+)\/review/', $uri, $matches))
        RecruiterController::reviewAssignment($matches[1]);
    elseif (preg_match('/\/recruiter\/assignment\/(\d+)\/autocorrect/', $uri, $matches))
        RecruiterController::autoCorrectTest($matches[1]);
    elseif (preg_match('/\/recruiter\/test\/(\d+)\/view/', $uri, $matches))
        RecruiterController::viewTest($matches[1]);

}
// Candidate Routes
elseif (strpos($uri, '/candidate') === 0) {
    require_role('candidate');
    require __DIR__ . '/src/Controllers/CandidateController.php';

    if ($uri === '/candidate/dashboard')
        CandidateController::dashboard();
    elseif (preg_match('/\/candidate\/test\/(\d+)\/take/', $uri, $matches))
        CandidateController::takeTest($matches[1]);
    elseif (preg_match('/\/candidate\/test\/(\d+)\/submit/', $uri, $matches))
        CandidateController::submitTest($matches[1]);

} else {
    http_response_code(404);
    echo "404 Not Found";
}
