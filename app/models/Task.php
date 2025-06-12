<?php
require_once __DIR__ . '/../config/database.php';

class Task {
    private $conn;
    private $table = 'tb_tasks';

    public function __construct() {
        $this->conn = getDatabaseConnection();
    }

    // Create new task
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (user_id, project_id, title, description, due_date, start_date, priority, estimated_hours) 
                VALUES (:user_id, :project_id, :title, :description, :due_date, :start_date, :priority, :estimated_hours)";
        
        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':project_id' => $data['project_id'] ?? null,
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':due_date' => $data['due_date'],
            ':start_date' => $data['start_date'] ?? date('Y-m-d'),
            ':priority' => $data['priority'],
            ':estimated_hours' => $data['estimated_hours'] ?? 0
        ]);
    }

    // Get all tasks for a user
    public function getAllUserTasks($userId, $filter = null) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id";
        
        // Apply filters
        if ($filter) {
            switch($filter) {
                case 'today':
                    $sql .= " AND DATE(due_date) = CURDATE()";
                    break;
                case 'this_week':
                    $sql .= " AND YEARWEEK(due_date) = YEARWEEK(CURDATE())";
                    break;
                case 'important':
                    $sql .= " AND priority = 'high'";
                    break;
            }
        }
        
        $sql .= " ORDER BY due_date ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get single task
    public function getTask($taskId, $userId) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $taskId, ':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update task
    public function update($taskId, $userId, $data) {
        $updateFields = [];
        $params = [':id' => $taskId, ':user_id' => $userId];

        // Build dynamic update query
        foreach ($data as $key => $value) {
            if ($value !== null && in_array($key, ['title', 'description', 'due_date', 'start_date', 'priority', 'completed'])) {
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

    // Delete task
    public function delete($taskId, $userId) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $taskId, ':user_id' => $userId]);
    }

    // Toggle task completion
    public function toggleComplete($taskId, $userId) {
        $sql = "UPDATE {$this->table} SET completed = NOT completed 
                WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $taskId, ':user_id' => $userId]);
    }

    // Search tasks
    public function searchTasks($userId, $searchTerm) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                AND (title LIKE :search 
                OR description LIKE :search 
                OR priority LIKE :search)
                ORDER BY due_date ASC";
        
        $stmt = $this->conn->prepare($sql);
        $searchTerm = "%$searchTerm%";
        $stmt->execute([
            ':user_id' => $userId,
            ':search' => $searchTerm
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get tasks by priority
    public function getTasksByPriority($userId, $priority) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id AND priority = :priority 
                ORDER BY due_date ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':priority' => $priority
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get overdue tasks
    public function getOverdueTasks($userId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                AND due_date < CURDATE() 
                AND completed = false 
                ORDER BY due_date ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
