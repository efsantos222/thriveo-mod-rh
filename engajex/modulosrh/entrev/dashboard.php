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

// SELF-HEALING: Create tables if they don't exist
try {
    $pdo_entrev->query("SELECT 1 FROM entrev_users LIMIT 1");
} catch (Exception $e) {
    $pdo_entrev->exec("CREATE TABLE IF NOT EXISTS entrev_companies (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo_entrev->exec("CREATE TABLE IF NOT EXISTS entrev_users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), email VARCHAR(255) UNIQUE, password VARCHAR(255), role VARCHAR(50), company_id INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo_entrev->exec("CREATE TABLE IF NOT EXISTS entrev_jobs (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT, title VARCHAR(255), description TEXT, specifications TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo_entrev->exec("CREATE TABLE IF NOT EXISTS entrev_candidates (id INT AUTO_INCREMENT PRIMARY KEY, company_id INT, name VARCHAR(255), email VARCHAR(255), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo_entrev->exec("CREATE TABLE IF NOT EXISTS entrev_interview_scripts (id INT AUTO_INCREMENT PRIMARY KEY, job_id INT, content TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo_entrev->exec("CREATE TABLE IF NOT EXISTS entrev_settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(50) UNIQUE, setting_value TEXT)");
}

$email = $_SESSION['email'] ?? '';
$name = $_SESSION['name'] ?? 'Usuário';
$main_role = $_SESSION['role'] ?? 'employee';

// 1. Find or Create user in Entrevista Database
$stmt = $pdo_entrev->prepare("SELECT id, role FROM entrev_users WHERE email = ?");
$stmt->execute([$email]);
$entrev_user = $stmt->fetch();

if (!$entrev_user) {
    $target_role = 'responsible';
    if ($main_role === 'admin') $target_role = 'superadmin';
    elseif ($main_role === 'company_admin') $target_role = 'responsible';
    
    $stmt = $pdo_entrev->prepare("INSERT INTO entrev_users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $dummy_pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $stmt->execute([$name, $email, $dummy_pass, $target_role]);
    
    $entrev_id = $pdo_entrev->lastInsertId();
    $entrev_role = $target_role;
} else {
    $entrev_id = $entrev_user['id'];
    $entrev_role = $entrev_user['role'];

    // Sync admin role and fix column issues
    if ($main_role === 'admin' && $entrev_role !== 'superadmin') {
        $pdo_entrev->prepare("UPDATE entrev_users SET role = 'superadmin' WHERE id = ?")->execute([$entrev_id]);
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