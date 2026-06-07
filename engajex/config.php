<?php
// config.php - Database connection and configuration

$host = 'localhost';
$dbname = 'efsantos_engaj';
$username = 'efsantos_engaj';
$password = 'Kyew@1802';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // In production, log this error instead of showing detailed info
    die("Connection failed: " . $e->getMessage());
}

// Global functions
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isResponsible()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'responsible';
}

function isCompanyAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'company_admin';
}

// Returns true for any role with company-level management access
function hasCompanyAccess()
{
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['responsible', 'company_admin', 'admin']);
}

// Subscription Check
function getCompanySubscription() {
    global $pdo;
    if (!isset($_SESSION['company_id'])) return null;

    $stmt = $pdo->prepare("SELECT trial_ends_at, subscription_status, subscription_expires_at FROM companies WHERE id = ?");
    $stmt->execute([$_SESSION['company_id']]);
    $company = $stmt->fetch();

    if (!$company) return null;

    $is_trial = ($company['subscription_status'] === 'trial');
    $expire_date = $is_trial ? $company['trial_ends_at'] : $company['subscription_expires_at'];
    
    $is_expired = false;
    if ($expire_date && strtotime($expire_date) < time()) {
        $is_expired = true;
    }

    return [
        'status' => $company['subscription_status'],
        'expires_at' => $expire_date,
        'is_expired' => $is_expired,
        'days_left' => $expire_date ? ceil((strtotime($expire_date) - time()) / 86400) : 0
    ];
}

function checkAccess() {
    if (!isLoggedIn()) return;
    
    // Admins have full access
    if (isAdmin()) return;

    $sub = getCompanySubscription();
    if ($sub && $sub['is_expired']) {
        // Determine root-relative path
        $inSubDir = (strpos($_SERVER['PHP_SELF'], '/modules/') !== false || strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/zbb/') !== false || strpos($_SERVER['PHP_SELF'], '/modulosrs/') !== false || strpos($_SERVER['PHP_SELF'], '/modulostd/') !== false);
        $redirectPrefix = $inSubDir ? '../../' : '';
        
        // Final check: if we are in a sub-sub-sub dir, we might need more ../. 
        // But for this system structure, 2 levels up is the max for modules.
        
        $current_page = basename($_SERVER['PHP_SELF']);
        if ($current_page !== 'subscribe.php' && $current_page !== 'logout.php') {
            header("Location: " . $redirectPrefix . "subscribe.php?expired=1");
            exit;
        }
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>