<?php
session_start();
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Comment.php';

class TaskController {
    private $taskModel;
    private $commentModel;

    public function __construct() {
        $this->taskModel = new Task();
        $this->commentModel = new Comment();
    }

    // Create new task
    public function createTask() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = [
            'user_id' => $_SESSION['user_id'],
            'title' => $_POST['task_title'] ?? '',
            'description' => $_POST['task_description'] ?? '',
            'due_date' => $_POST['task_date'] ?? null,
            'start_date' => $_POST['start_date'] ?? date('Y-m-d'),
            'priority' => $_POST['task_priority'] ?? 'medium'
        ];

        // Validate input
        if (empty($data['title']) || empty($data['due_date'])) {
            $this->jsonResponse(['error' => 'Missing required fields'], 400);
            return;
        }

        if ($this->taskModel->create($data)) {
            $this->jsonResponse(['message' => 'Task created successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to create task'], 500);
        }
    }

    // Update task
    public function updateTask() {
        if (!isset($_SESSION['user_id']) || !isset($_POST['task_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized or missing task ID'], 401);
            return;
        }

        $taskId = $_POST['task_id'];
        $userId = $_SESSION['user_id'];

        // Verify task ownership
        if (!$this->taskModel->getTask($taskId, $userId)) {
            $this->jsonResponse(['error' => 'Task not found or unauthorized'], 404);
            return;
        }

        $data = [
            'description' => $_POST['description'] ?? null,
            'due_date' => $_POST['due_date'] ?? null,
            'priority' => $_POST['priority'] ?? null,
            'completed' => isset($_POST['completed']) ? (bool)$_POST['completed'] : null
        ];

        if ($this->taskModel->update($taskId, $userId, $data)) {
            $this->jsonResponse(['message' => 'Task updated successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to update task'], 500);
        }
    }

    // Delete task
    public function deleteTask() {
        if (!isset($_SESSION['user_id']) || !isset($_POST['task_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized or missing task ID'], 401);
            return;
        }

        $taskId = $_POST['task_id'];
        $userId = $_SESSION['user_id'];

        if ($this->taskModel->delete($taskId, $userId)) {
            $this->jsonResponse(['message' => 'Task deleted successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to delete task'], 500);
        }
    }

    // Toggle task completion
    public function toggleTaskComplete() {
        if (!isset($_SESSION['user_id']) || !isset($_POST['task_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized or missing task ID'], 401);
            return;
        }

        $taskId = $_POST['task_id'];
        $userId = $_SESSION['user_id'];

        if ($this->taskModel->toggleComplete($taskId, $userId)) {
            $this->jsonResponse(['message' => 'Task status updated successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to update task status'], 500);
        }
    }

    // Get tasks with filter
    public function getTasks() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }

        $userId = $_SESSION['user_id'];
        $filter = $_GET['filter'] ?? null;
        $tasks = $this->taskModel->getAllUserTasks($userId, $filter);

        // Add comment counts to each task
        foreach ($tasks as &$task) {
            $task['comment_count'] = $this->commentModel->getCommentCount($task['id']);
        }

        $this->jsonResponse(['tasks' => $tasks]);
    }

    // Add comment to task
    public function addComment() {
        if (!isset($_SESSION['user_id']) || !isset($_POST['task_id']) || !isset($_POST['comment'])) {
            $this->jsonResponse(['error' => 'Missing required fields'], 400);
            return;
        }

        $taskId = $_POST['task_id'];
        $userId = $_SESSION['user_id'];
        $comment = trim($_POST['comment']);

        if (empty($comment)) {
            $this->jsonResponse(['error' => 'Comment cannot be empty'], 400);
            return;
        }

        // Verify task exists and belongs to user
        if (!$this->taskModel->getTask($taskId, $userId)) {
            $this->jsonResponse(['error' => 'Task not found or unauthorized'], 404);
            return;
        }

        if ($this->commentModel->create($taskId, $userId, $comment)) {
            $this->jsonResponse(['message' => 'Comment added successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to add comment'], 500);
        }
    }

    // Get task comments
    public function getComments() {
        if (!isset($_SESSION['user_id']) || !isset($_GET['task_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized or missing task ID'], 401);
            return;
        }

        $taskId = $_GET['task_id'];
        $userId = $_SESSION['user_id'];

        // Verify task exists and belongs to user
        if (!$this->taskModel->getTask($taskId, $userId)) {
            $this->jsonResponse(['error' => 'Task not found or unauthorized'], 404);
            return;
        }

        $comments = $this->commentModel->getTaskComments($taskId);
        $this->jsonResponse(['comments' => $comments]);
    }

    // Delete comment
    public function deleteComment() {
        if (!isset($_SESSION['user_id']) || !isset($_POST['comment_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized or missing comment ID'], 401);
            return;
        }

        $commentId = $_POST['comment_id'];
        $userId = $_SESSION['user_id'];

        // Verify comment ownership
        if (!$this->commentModel->isCommentOwner($commentId, $userId)) {
            $this->jsonResponse(['error' => 'Comment not found or unauthorized'], 404);
            return;
        }

        if ($this->commentModel->delete($commentId, $userId)) {
            $this->jsonResponse(['message' => 'Comment deleted successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to delete comment'], 500);
        }
    }

    // Search tasks
    public function searchTasks() {
        if (!isset($_SESSION['user_id']) || !isset($_GET['search'])) {
            $this->jsonResponse(['error' => 'Unauthorized or missing search term'], 401);
            return;
        }

        $userId = $_SESSION['user_id'];
        $searchTerm = trim($_GET['search']);

        if (empty($searchTerm)) {
            $this->jsonResponse(['error' => 'Search term cannot be empty'], 400);
            return;
        }

        $tasks = $this->taskModel->searchTasks($userId, $searchTerm);
        $this->jsonResponse(['tasks' => $tasks]);
    }

    // Helper method for JSON responses
    public function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

// Handle incoming requests only if accessed directly
if (basename($_SERVER['PHP_SELF']) === 'TaskController.php') {
    $controller = new TaskController();

    // Route the request to the appropriate method
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            $controller->createTask();
            break;
        case 'update':
            $controller->updateTask();
            break;
        case 'delete':
            $controller->deleteTask();
            break;
        case 'toggle':
            $controller->toggleTaskComplete();
            break;
        case 'get':
            $controller->getTasks();
            break;
        case 'search':
            $controller->searchTasks();
            break;
        case 'add_comment':
            $controller->addComment();
            break;
        case 'get_comments':
            $controller->getComments();
            break;
        case 'delete_comment':
            $controller->deleteComment();
            break;
        default:
            $controller->jsonResponse(['error' => 'Invalid action'], 400);
    }
}
