<?php
// Adicionar proteção por senha
$migration_password = 'SysManager25'; // Use a mesma senha do superadmin

if (!isset($_GET['key']) || $_GET['key'] !== $migration_password) {
    die('Acesso não autorizado');
}

// Definir limite de tempo de execução maior
set_time_limit(300); // 5 minutos
ini_set('memory_limit', '256M');

// Executar a migração
require_once 'includes/migrate_data.php';
?>
