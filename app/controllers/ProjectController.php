<?php
session_start();
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/Task.php';

class ProjectController {
    private $projectModel;
    private $taskModel;

    public function __construct() {
        $this->projectModel = new Project();
        $this->taskModel = new Task();
    }

    // Create new project
    public function createProject() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }

        $data = [
            'user_id' => $_SESSION['user_id'],
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
            'status' => $_POST['status'] ?? 'planning',
            'color' => $_POST['color'] ?? '#6366f1'
        ];

        // Validate input
        if (empty($data['name']) || empty($data['start_date']) || empty($data['end_date'])) {
            $this->jsonResponse(['error' => 'Missing required fields'], 400);
            return;
        }

        // Validate dates
        if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
            $this->jsonResponse(['error' => 'End date must be after start date'], 400);
            return;
        }

        if ($this->projectModel->create($data)) {
            $this->jsonResponse(['message' => 'Project created successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to create project'], 500);
        }
    }

    // Update project
    public function updateProject() {
        if (!isset($_SESSION['user_id']) || !isset($_POST['project_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized or missing project ID'], 401);
            return;
        }

        $projectId = $_POST['project_id'];
        $userId = $_SESSION['user_id'];

        // Verify project ownership
        if (!$this->projectModel->getProject($projectId, $userId)) {
            $this->jsonResponse(['error' => 'Project not found or unauthorized'], 404);
            return;
        }

        $data = [
            'name' => $_POST['name'] ?? null,
            'description' => $_POST['description'] ?? null,
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
            'status' => $_POST['status'] ?? null,
            'color' => $_POST['color'] ?? null
        ];

        // Validate dates if both are provided
        if ($data['start_date'] && $data['end_date']) {
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                $this->jsonResponse(['error' => 'End date must be after start date'], 400);
                return;
            }
        }

        if ($this->projectModel->update($projectId, $userId, $data)) {
            $this->jsonResponse(['message' => 'Project updated successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to update project'], 500);
        }
    }

    // Delete project
    public function deleteProject() {
        if (!isset($_SESSION['user_id']) || !isset($_POST['project_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized or missing project ID'], 401);
            return;
        }

        $projectId = $_POST['project_id'];
        $userId = $_SESSION['user_id'];

        if ($this->projectModel->delete($projectId, $userId)) {
            $this->jsonResponse(['message' => 'Project deleted successfully']);
        } else {
            $this->jsonResponse(['error' => 'Failed to delete project'], 500);
        }
    }

    // Get all projects
    public function getProjects() {
        if (!isset($_SESSION['user_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }

        $projects = $this->projectModel->getAllUserProjects($_SESSION['user_id']);
        $this->jsonResponse(['projects' => $projects]);
    }

    // Get single project with tasks
    public function getProject() {
        if (!isset($_SESSION['user_id']) || !isset($_GET['project_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized or missing project ID'], 401);
            return;
        }

        $projectId = $_GET['project_id'];
        $userId = $_SESSION['user_id'];

        $project = $this->projectModel->getProject($projectId, $userId);
        
        if (!$project) {
            $this->jsonResponse(['error' => 'Project not found'], 404);
            return;
        }

        $this->jsonResponse(['project' => $project]);
    }

    // Get project timeline data for Gantt chart
    public function getTimelineData() {
        if (!isset($_SESSION['user_id']) || !isset($_GET['project_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized or missing project ID'], 401);
            return;
        }

        $projectId = $_GET['project_id'];
        $userId = $_SESSION['user_id'];

        $timelineData = $this->projectModel->getTimelineData($projectId, $userId);
        
        if (!$timelineData) {
            $this->jsonResponse(['error' => 'Project not found'], 404);
            return;
        }

        $this->jsonResponse(['timeline' => $timelineData]);
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
if (basename($_SERVER['PHP_SELF']) === 'ProjectController.php') {
    $controller = new ProjectController();

    // Route the request to the appropriate method
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'create':
            $controller->createProject();
            break;
        case 'update':
            $controller->updateProject();
            break;
        case 'delete':
            $controller->deleteProject();
            break;
        case 'get':
            $controller->getProject();
            break;
        case 'list':
            $controller->getProjects();
            break;
        case 'timeline':
            $controller->getTimelineData();
            break;
        default:
            $controller->jsonResponse(['error' => 'Invalid action'], 400);
    }
}
