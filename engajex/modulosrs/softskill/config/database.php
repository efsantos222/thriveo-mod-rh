<?php
// modulosrs/softskill/config/database.php
// Bridge to Thriveo Engajex Main Database

$mainRoot = dirname(dirname(dirname(__DIR__)));
require_once $mainRoot . '/config.php';

class Database {
    public $conn;

    public function getConnection() {
        global $pdo;
        $this->conn = $pdo; 
        return $this->conn;
    }
}
?>
