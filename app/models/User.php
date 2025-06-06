<?php
require_once '../config/database.php';

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

    public function login($email, $password) {
        // Prepara a consulta
        $stmt = $this->db->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();

            // Verifica a senha
            if (password_verify($password, $hashedPassword)) {
                return true; // Login bem-sucedido
            }
        }
        return false; // Login falhou
    }
}
?>
