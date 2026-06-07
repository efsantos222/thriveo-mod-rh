<?php
namespace PDI\models;

use PDO;

class Company
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($data)
    {
        $sql = "INSERT INTO companies (name, cnpj, identity_purpose, identity_mission, identity_vision, identity_principles, identity_values) 
                VALUES (:name, :cnpj, :purpose, :mission, :vision, :principles, :values)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':cnpj' => $data['cnpj'] ?? null,
            ':purpose' => $data['identity_purpose'] ?? null,
            ':mission' => $data['identity_mission'] ?? null,
            ':vision' => $data['identity_vision'] ?? null,
            ':principles' => $data['identity_principles'] ?? null,
            ':values' => $data['identity_values'] ?? null
        ]);
    }

    public function update($id, $data)
    {
        $fields = [];
        $params = [':id' => $id];

        $allowed = ['name', 'cnpj', 'identity_purpose', 'identity_mission', 'identity_vision', 'identity_principles', 'identity_values'];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields))
            return false;

        $sql = "UPDATE companies SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id)
    {
        // Warning: this should check for users first or cascade. 
        // For now, let's assume it's allowed but users might become orphaned if no FK cascade action.
        $stmt = $this->pdo->prepare("DELETE FROM companies WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function get($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAll()
    {
        $stmt = $this->pdo->query("SELECT * FROM companies ORDER BY name");
        return $stmt->fetchAll();
    }
}
