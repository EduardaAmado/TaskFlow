<?php
session_start();
require_once __DIR__ . '/../models/User.php';

class RegisterController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register() {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate input
        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $this->redirectWithError('register', 'Todos os campos são obrigatórios');
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithError('register', 'Email inválido');
            return;
        }

        // Validate username length
        if (strlen($username) < 3 || strlen($username) > 50) {
            $this->redirectWithError('register', 'Nome de usuário deve ter entre 3 e 50 caracteres');
            return;
        }

        // Validate password length and complexity
        if (strlen($password) < 6) {
            $this->redirectWithError('register', 'Senha deve ter no mínimo 6 caracteres');
            return;
        }

        // Check if passwords match
        if ($password !== $confirmPassword) {
            $this->redirectWithError('register', 'As senhas não coincidem');
            return;
        }

        // Check if username exists
        if ($this->userModel->usernameExists($username)) {
            $this->redirectWithError('register', 'Nome de usuário já está em uso');
            return;
        }

        // Check if email exists
        if ($this->userModel->emailExists($email)) {
            $this->redirectWithError('register', 'Email já está em uso');
            return;
        }

        // Create user
        $userData = [
            'username' => $username,
            'email' => $email,
            'password' => $password
        ];

        if ($this->userModel->create($userData)) {
            // Get the created user
            $user = $this->userModel->findByEmail($email);
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            // Redirect to dashboard
            header('Location: ../../public/dashboard.php?message=welcome');
            exit;
        } else {
            $this->redirectWithError('register', 'Erro ao criar conta. Tente novamente.');
            return;
        }
    }

    private function redirectWithError($page, $error) {
        header("Location: ../../public/$page.php?error=" . urlencode($error));
        exit;
    }
}

// Handle registration request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new RegisterController();
    $controller->register();
} else {
    header('Location: ../../public/register.php');
    exit;
}
