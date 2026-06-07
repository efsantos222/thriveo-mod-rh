<?php
// modulosrh/entrev/dashboard.php
require_once 'config.php';

// Check main system login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

// Enforce trial/subscription
if (function_exists('checkAccess')) {
    checkAccess();
}

$email = $_SESSION['email'] ?? '';
$name = $_SESSION['name'] ?? 'Usuário';
$main_role = $_SESSION['role'] ?? 'employee';

// 1. Find or Create user in Entrevista Database
$stmt = $pdo_entrev->prepare("SELECT id, role FROM users WHERE email = ?");
$stmt->execute([$email]);
$entrev_user = $stmt->fetch();

if (!$entrev_user) {
    // Map roles: admin -> superadmin, responsible/manager -> responsible, employee -> (maybe viewing?)
    // For this module, let's allow admin and responsible/manager.
    $target_role = 'responsible';
    if ($main_role === 'admin') $target_role = 'superadmin';
    elseif ($main_role === 'company_admin') $target_role = 'responsible';
    
    $stmt = $pdo_entrev->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $dummy_pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $stmt->execute([$name, $email, $dummy_pass, $target_role]);
    
    $entrev_id = $pdo_entrev->lastInsertId();
    $entrev_role = $target_role;
} else {
    $entrev_id = $entrev_user['id'];
    $entrev_role = $entrev_user['role'];

    // Sync admin role
    if ($main_role === 'admin' && $entrev_role !== 'superadmin') {
        $pdo_entrev->prepare("UPDATE users SET role = 'superadmin' WHERE id = ?")->execute([$entrev_id]);
        $entrev_role = 'superadmin';
    }
}

// 2. Map Module specific session variables
$_SESSION['entrev_user_id'] = $entrev_id;
$_SESSION['user_role'] = $entrev_role; // The module uses user_role

// Backup main role if not already done
if (!isset($_SESSION['role_backup'])) {
    $_SESSION['role_backup'] = $_SESSION['role'];
}
$_SESSION['role'] = $entrev_role;

// 3. Routing
if ($entrev_role === 'superadmin') {
    header('Location: admin/dashboard.php');
} elseif ($entrev_role === 'responsible') {
    header('Location: responsible/dashboard.php');
} else {
    header('Location: ../../dashboard.php?error=no_access_entrev');
}
exit;
?>