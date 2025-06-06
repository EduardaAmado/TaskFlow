<?php
require_once '../config/db_connect.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function register($firstName, $lastName, $email, $password) {
        // Hash da senha
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepara a consulta
        $stmt = $this->db->prepare("INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $firstName, $lastName, $email, $hashedPassword);

        // Executa a consulta e verifica se foi bem-sucedida
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
?>
