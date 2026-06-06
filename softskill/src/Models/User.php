<?php
class User
{
    private $conn;
    private $table = 'users';

    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $recruiter_id;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function login($email, $password)
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE email = :email LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }

    // Create User (Admin creating Recruiter, or Recruiter creating Candidate)
    public function create()
    {
        $query = 'INSERT INTO ' . $this->table . ' 
                  SET name = :name, 
                      email = :email, 
                      password = :password, 
                      role = :role, 
                      recruiter_id = :recruiter_id';

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));

        // Hash password
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':recruiter_id', $this->recruiter_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getRecruiters()
    {
        $query = "SELECT * FROM " . $this->table . " WHERE role = 'recruiter'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getCandidatesByRecruiter($recruiter_id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE role = 'candidate' AND recruiter_id = :recruiter_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':recruiter_id', $recruiter_id);
        $stmt->execute();
        return $stmt;
    }
}
