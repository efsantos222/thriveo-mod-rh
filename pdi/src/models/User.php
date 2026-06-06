<?php
namespace PDI\models;

use PDO;

class User
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function get($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO users (name, email, password_hash, role, company_id, job_title, job_activities, created_at) 
                VALUES (:name, :email, :password_hash, :role, :company_id, :job_title, :job_activities, NOW())";
        $stmt = $this->pdo->prepare($sql);

        $params = [
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role' => $data['role'] ?? 'employee',
            ':company_id' => $data['company_id'] ?? null,
            ':job_title' => $data['job_title'] ?? null,
            ':job_activities' => $data['job_activities'] ?? null
        ];

        return $stmt->execute($params);
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if ($key === 'password') {
                $fields[] = "password_hash = :password_hash";
                $params[':password_hash'] = password_hash($value, PASSWORD_DEFAULT);
            } else {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getByCompany($companyId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE company_id = ? ORDER BY name");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    }

    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT u.*, c.name as company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id ORDER BY u.name");
        return $stmt->fetchAll();
    }
}
