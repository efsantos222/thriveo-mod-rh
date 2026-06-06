<?php
// modulostd/unia/dashboard.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check main system login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

require_once 'config/db.php'; // This connects to $pdo_unia and loads main config
checkAccess(); // This is the main checkAccess now smart enough to find root

$email = $_SESSION['email'] ?? '';
$name = $_SESSION['name'] ?? 'Usuário';
$main_role = $_SESSION['role'] ?? 'employee';

// 1. Find or Create user in UniA Database
$stmt = $pdo_unia->prepare("SELECT id, role FROM users WHERE email = ?");
$stmt->execute([$email]);
$unia_user = $stmt->fetch();

if (!$unia_user) {
    // Determine UniA role based on Engaja role
    $target_role = 'student';
    if ($main_role === 'admin') $target_role = 'admin';
    elseif (in_array($main_role, ['responsible', 'company_admin'])) $target_role = 'coordinator';
    elseif ($main_role === 'manager') $target_role = 'instructor';
    
    // Create the user in UniA (Password doesn't matter much as we use SSO from main login)
    $stmt = $pdo_unia->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $dummy_pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $stmt->execute([$name, $email, $dummy_pass, $target_role]);
    
    $unia_user_id = $pdo_unia->lastInsertId();
    $unia_role = $target_role;
} else {
    $unia_user_id = $unia_user['id'];
    $unia_role = $unia_user['role'];
}

// 2. Map UniA specific session variables
$_SESSION['unia_user_id'] = $unia_user_id;
$_SESSION['unia_role'] = $unia_role;

// Note: To avoid breaking Engaja's sidebar, we keep $_SESSION['role'] as is.
// But some UniA pages might check $_SESSION['role']. 
// We will set a temporary role variable for the redirection.
$_SESSION['role_backup'] = $_SESSION['role']; 
$_SESSION['role'] = $unia_role; // Temporarily swap for UniA internal checks

// 3. Routing
switch ($unia_role) {
    case 'admin':
        header("Location: admin/index.php");
        break;
    case 'coordinator':
        header("Location: coordinator/index.php");
        break;
    case 'instructor':
        header("Location: instructor/index.php");
        break;
    case 'student':
    default:
        header("Location: student/index.php");
        break;
}
exit;
?>