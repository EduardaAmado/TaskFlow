<?php
// Include the database connection file
require_once 'C:/wamp64/www/TaskFlow/app/config/database.php'; // Caminho absoluto

$pdo = getDatabaseConnection();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_title'])) {
    // Extract task details
    $title = $_POST['task_title'];
    $description = $_POST['task_description'];
    $date = $_POST['task_date'];
    $priority = $_POST['task_priority'];

    // Prepare and execute the SQL statement to insert the task into the database
    $stmt = $pdo->prepare("INSERT INTO tb_tasks (title, description, due_date, priority) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$title, $description, $date, $priority])) {
        // Redirect back to the main page or display a success message
        header("Location: dashboard.php"); // Change 'dashboard.php' to your main page
        exit();
    } else {
        echo "Error: Could not add task.";
    }
}
?>
