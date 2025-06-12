<?php
session_start();
require_once '../app/controllers/TaskController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskController = new TaskController();
    
    // If it's an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        $taskController->createTask();
    } else {
        // Regular form submission
        try {
            $taskController->createTask();
        } catch (Exception $e) {
            header('Location: dashboard.php?error=' . urlencode($e->getMessage()));
            exit;
        }
        header('Location: dashboard.php?success=1');
        exit;
    }
}

// If not a POST request, redirect to dashboard
header('Location: dashboard.php');
exit;
