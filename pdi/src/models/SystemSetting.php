<?php
namespace PDI\models;

use PDO;

class SystemSetting
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get($key)
    {
        $stmt = $this->pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : null;
    }

    public function set($key, $value)
    {
        $sql = "INSERT INTO system_settings (setting_key, setting_value) VALUES (:key, :value) 
                ON DUPLICATE KEY UPDATE setting_value = :value";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':key' => $key, ':value' => $value]);
    }
}
