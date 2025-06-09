<?php
// Start the session
session_start();

// Include database configuration
require_once '../config/database.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Initialize an array to hold error messages
    $errors = [];

    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    // If there are no errors, proceed to register the user
    if (empty($errors)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare SQL statement
        $sql = "INSERT INTO tb_users (username, password, email) VALUES (:username, :password, :email)";
        // Create a prepared statement
        if ($stmt = $pdo->prepare($sql)) {
            // Bind parameters
            $stmt->bindParam(':username', $firstName); // Use o nome de usuário correto
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':email', $email);
            // Execute the statement
            if ($stmt->execute()) {
                // Redirect to a success page or show a success message
                $_SESSION['success'] = "Account created successfully!";
                header("Location: ../public/success.php");
                exit();
            } else {
                $errors[] = "Something went wrong. Please try again.";
            }
        } else {
            $errors[] = "Failed to prepare the SQL statement.";
        }
    }

    // If there are errors, store them in the session and redirect back to the form
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../public/register.php");
        exit();
    }
}
?>