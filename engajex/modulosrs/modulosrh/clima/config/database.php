<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'efsantos_clima');  // Correct database name
define('DB_USER', 'efsantos_clima');  // Correct username
define('DB_PASS', 'Kyew1802');        // Correct password

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            );
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            error_log("Erro de conexão com o banco de dados: " . $e->getMessage());
            die("Erro ao conectar ao banco de dados. Por favor, tente novamente mais tarde.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}
