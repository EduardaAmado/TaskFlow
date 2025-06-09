<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Combine first and last name to create username
    $username = strtolower(preg_replace('/\s+/', '', $firstName . $lastName));

    // Validate
    $errors = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        // In production, you would redirect back with errors
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo = getDatabaseConnection();

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM tb_users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already registered.']);
            exit;
        }

        // Insert new user
        $insertStmt = $pdo->prepare("
            INSERT INTO tb_users (username, password, email)
            VALUES (:username, :password, :email)
        ");

        $insertStmt->execute([
            'username' => $username,
            'password' => $hashedPassword,
            'email' => $email
        ]);

        echo json_encode(['success' => true, 'message' => 'User registered successfully.']);
        header("Location: ../../public/login.php");
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
