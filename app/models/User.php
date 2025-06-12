<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = 'tb_users';

    public function __construct() {
        $this->conn = getDatabaseConnection();
    }

    // Find user by email
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Find user by username
    public function findByUsername($username) {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create new user
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
    }

    // Get user by ID
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update user profile
    public function update($id, $data) {
        $updateFields = [];
        $params = [':id' => $id];

        // Build dynamic update query
        foreach ($data as $key => $value) {
            if ($value !== null && in_array($key, ['username', 'email', 'password'])) {
                if ($key === 'password') {
                    $value = password_hash($value, PASSWORD_DEFAULT);
                }
                $updateFields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    // Check if email exists
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];
        
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Check if username exists
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = :username";
        $params = [':username' => $username];
        
        if ($excludeId) {
            $sql .= " AND id != :id";
            $params[':id'] = $excludeId;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Verify password
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
