<?php
session_start();
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: ../../public/login.php?error=empty_fields");
        exit;
    }

    $userModel = new User();
    $user = $userModel->findByEmail($email);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];

        header("Location: ../../public/dashboard.php?message=login_success");
        exit;
    } else {
        header("Location: ../../public/login.php?message=invalid_credentials");
        exit;
    }
} else {
    header("Location: ../../public/login.php?message=empty_fields");
    exit;
}
