<?php
require_once __DIR__ . '/../config/database.php';

class Project {
    private $conn;
    private $table = 'tb_projects';

    public function __construct() {
        $this->conn = getDatabaseConnection();
    }

    // Create new project
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (user_id, name, description, start_date, end_date, status, color) 
                VALUES (:user_id, :name, :description, :start_date, :end_date, :status, :color)";
        
        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':status' => $data['status'] ?? 'planning',
            ':color' => $data['color'] ?? '#6366f1'
        ]);
    }

    // Get all projects for a user
    public function getAllUserProjects($userId) {
        $sql = "SELECT p.*, 
                COUNT(t.id) as total_tasks,
                SUM(CASE WHEN t.completed = 1 THEN 1 ELSE 0 END) as completed_tasks,
                SUM(t.estimated_hours) as total_estimated_hours,
                SUM(t.actual_hours) as total_actual_hours
                FROM {$this->table} p
                LEFT JOIN tb_tasks t ON p.id = t.project_id
                WHERE p.user_id = :user_id
                GROUP BY p.id
                ORDER BY p.start_date ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single project with tasks
    public function getProject($projectId, $userId) {
        // Get project details
        $sql = "SELECT * FROM {$this->table} 
                WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':id' => $projectId,
            ':user_id' => $userId
        ]);
        
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$project) {
            return null;
        }

        // Get project tasks
        $sql = "SELECT * FROM tb_tasks 
                WHERE project_id = :project_id 
                ORDER BY start_date ASC, due_date ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':project_id' => $projectId]);
        
        $project['tasks'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $project;
    }

    // Update project
    public function update($projectId, $userId, $data) {
        $updateFields = [];
        $params = [':id' => $projectId, ':user_id' => $userId];

        foreach ($data as $key => $value) {
            if ($value !== null && in_array($key, ['name', 'description', 'start_date', 'end_date', 'status', 'color'])) {
                $updateFields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . 
               " WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    // Delete project
    public function delete($projectId, $userId) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $projectId,
            ':user_id' => $userId
        ]);
    }

    // Get project progress
    public function getProgress($projectId, $userId) {
        $sql = "SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_tasks,
                SUM(estimated_hours) as total_estimated_hours,
                SUM(actual_hours) as total_actual_hours
                FROM tb_tasks 
                WHERE project_id = :project_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':project_id' => $projectId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total_tasks'] > 0) {
            $result['completion_percentage'] = round(($result['completed_tasks'] / $result['total_tasks']) * 100);
        } else {
            $result['completion_percentage'] = 0;
        }
        
        return $result;
    }

    // Get project timeline data for Gantt chart
    public function getTimelineData($projectId, $userId) {
        // Get project details
        $project = $this->getProject($projectId, $userId);
        
        if (!$project) {
            return null;
        }

        $timeline = [
            'project' => [
                'id' => $project['id'],
                'name' => $project['name'],
                'start' => $project['start_date'],
                'end' => $project['end_date'],
                'color' => $project['color'],
                'progress' => 0
            ],
            'tasks' => []
        ];

        // Calculate project progress
        $progress = $this->getProgress($projectId, $userId);
        $timeline['project']['progress'] = $progress['completion_percentage'];

        // Format tasks for timeline
        foreach ($project['tasks'] as $task) {
            $timeline['tasks'][] = [
                'id' => $task['id'],
                'name' => $task['title'],
                'start' => $task['start_date'],
                'end' => $task['due_date'],
                'progress' => $task['completed'] ? 100 : 0,
                'dependencies' => '', // Could be added in future
                'estimated_hours' => $task['estimated_hours'],
                'actual_hours' => $task['actual_hours']
            ];
        }

        return $timeline;
    }
}
