<?php
class Database {
    private static $host = 'localhost';
    private static $db_name = 'efsantos_softskill';
    private static $username = 'efsantos_softskill';
    private static $password = 'Kyew1802';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . self::$host . ";dbname=" . self::$db_name, self::$username, self::$password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // In production, log this instead of showing full error
            // For now, simple error handling
            die("Database Error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
