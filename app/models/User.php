<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = 'tb_users';

    public function __construct() {
        $this->conn = getDatabaseConnection(); // ✅ Correto
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
