<?php
// templates/dashboard.php

if (!isset($_SESSION['user_id'])) {
    header('Location: ?route=login');
    exit;
}

$role = $_SESSION['user_role'] ?? 'employee';

// Route based on role
switch ($role) {
    case 'superadmin':
        // Pass control to admin template
        require_once TEMPLATES_PATH . '/admin/index.php';
        break;
    case 'company_admin':
        require_once TEMPLATES_PATH . '/company/dashboard.php';
        break;
    default:
        require_once TEMPLATES_PATH . '/employee/dashboard.php';
        break;
}
