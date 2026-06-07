<?php
// templates/auth/logout.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_destroy();

// Redirect to home (landing page)
header('Location: ?route=home');
exit;
