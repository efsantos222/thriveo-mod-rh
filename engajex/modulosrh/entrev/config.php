<?php
// modulosrh/entrev/config.php
// Centralized Bridge to Main Database

$mainRoot = dirname(dirname(__DIR__));
require_once $mainRoot . '/config.php';

// The main system provides $pdo which is already connected to 'efsantos_engaj'
$pdo_entrev = $pdo; 

// If the system expects mysqli for some legacy parts, you can uncomment this:
/*
$host = 'localhost';
$dbname = 'efsantos_engaj';
$username = 'efsantos_engaj';
$password = 'Kyew@1802';
$conn = mysqli_connect($host, $username, $password, $dbname);
*/
?>
