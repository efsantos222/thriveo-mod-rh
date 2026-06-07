<?php

session_start();

function redirect($path)
{
    header("Location: " . APP_URL . $path);
    exit;
}

function view($name, $data = [])
{
    extract($data);
    $viewPath = __DIR__ . '/../templates/' . $name . '.php';
    if (file_exists($viewPath)) {
        require $viewPath;
    } else {
        echo "View '{$name}' not found!";
    }
}

function is_authenticated()
{
    return isset($_SESSION['user_id']);
}

function current_user()
{
    return $_SESSION['user'] ?? null;
}

function require_login()
{
    if (!is_authenticated()) {
        redirect('/login');
    }
}

function require_role($role)
{
    require_login();
    $user = current_user();
    if ($user['role'] !== $role) {
        // Se tentar acessar área de outro role, manda pro dashboard correto
        redirect('/' . $user['role'] . '/dashboard');
    }
}

function json_response($data, $status = 200)
{
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function get_db()
{
    global $pdo;
    return $pdo;
}
