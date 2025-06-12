<?php
session_start();
require_once __DIR__ . '/../models/User.php';

class LoginController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->redirectWithError('login', 'Email e senha são obrigatórios');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            header("Location: ../../public/dashboard.php?message=login_success");
            exit;
        } else {
            $this->redirectWithError('login', 'Email ou senha inválidos');
            return;
        }
    }

    public function logout() {
        // Destroy all session data
        $_SESSION = array();
        
        // Delete the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
        
        header("Location: ../../public/login.php?message=logout_success");
        exit;
    }

    private function redirectWithError($page, $error) {
        header("Location: ../../public/$page.php?error=" . urlencode($error));
        exit;
    }
}

// Handle requests
if (isset($_GET['logout'])) {
    $controller = new LoginController();
    $controller->logout();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new LoginController();
    $controller->login();
} else {
    header('Location: ../../public/login.php');
    exit;
}
