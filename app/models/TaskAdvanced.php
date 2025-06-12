<?php
require_once __DIR__ . '/../config/database.php';

class TaskAdvanced {
    private $conn;
    
    public function __construct() {
        $this->conn = getDatabaseConnection();
    }
    
    public function createTask($userId, $title, $description, $dueDate, $priority, $projectId = null, $isRecurring = false, $recurrenceType = null, $recurrenceInterval = 1, $recurrenceEndDate = null) {
        $sql = "INSERT INTO tb_tasks (user_id, title, description, due_date, priority, project_id, is_recurring, recurrence_type, recurrence_interval, recurrence_end_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$userId, $title, $description, $dueDate, $priority, $projectId, $isRecurring, $recurrenceType, $recurrenceInterval, $recurrenceEndDate]);
        
        if ($result && $isRecurring) {
            $taskId = $this->conn->lastInsertId();
            $this->generateRecurringTasks($taskId, $userId, $title, $description, $dueDate, $priority, $projectId, $recurrenceType, $recurrenceInterval, $recurrenceEndDate);
        }
        
        return $result;
    }
    
    public function getAllUserTasks($userId, $filter = null) {
        $sql = "SELECT t.*, p.name as project_name FROM tb_tasks t 
                LEFT JOIN tb_projects p ON t.project_id = p.id 
                WHERE t.user_id = ?";
        $params = [$userId];
        
        if ($filter) {
            switch ($filter) {
                case 'today':
                    $sql .= " AND DATE(t.due_date) = CURDATE()";
                    break;
                case 'this_week':
                    $sql .= " AND WEEK(t.due_date) = WEEK(CURDATE()) AND YEAR(t.due_date) = YEAR(CURDATE())";
                    break;
                case 'important':
                    $sql .= " AND t.priority = 'high'";
                    break;
                case 'recurring':
                    $sql .= " AND t.is_recurring = 1";
                    break;
                case 'overdue':
                    $sql .= " AND t.due_date < CURDATE() AND t.completed = 0";
                    break;
            }
        }
        
        $sql .= " ORDER BY t.due_date ASC, t.priority DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTaskById($taskId) {
        $sql = "SELECT t.*, p.name as project_name FROM tb_tasks t 
                LEFT JOIN tb_projects p ON t.project_id = p.id 
                WHERE t.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$taskId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateTask($taskId, $title, $description, $dueDate, $priority, $projectId = null) {
        $sql = "UPDATE tb_tasks SET title = ?, description = ?, due_date = ?, priority = ?, project_id = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$title, $description, $dueDate, $priority, $projectId, $taskId]);
    }
    
    public function deleteTask($taskId) {
        // Deletar também tarefas recorrentes filhas
        $sql = "DELETE FROM tb_tasks WHERE id = ? OR parent_recurring_task_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$taskId, $taskId]);
    }
    
    public function toggleTaskComplete($taskId) {
        $sql = "UPDATE tb_tasks SET completed = NOT completed, completed_at = CASE WHEN completed = 0 THEN NOW() ELSE NULL END WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$taskId]);
        
        if ($result) {
            // Verificar se a tarefa foi marcada como completa e se é recorrente
            $task = $this->getTaskById($taskId);
            if ($task['completed'] && $task['is_recurring']) {
                $this->createNextRecurringTask($taskId);
            }
            
            // Verificar dependências
            $this->checkAndUnlockDependentTasks($taskId);
        }
        
        return $result;
    }
    
    public function bulkCompleteTask($taskIds, $userId) {
        if (empty($taskIds)) {
            return false;
        }
        
        $placeholders = str_repeat('?,', count($taskIds) - 1) . '?';
        $sql = "UPDATE tb_tasks SET completed = 1, completed_at = NOW() 
                WHERE id IN ($placeholders) AND user_id = ?";
        
        $params = array_merge($taskIds, [$userId]);
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute($params);
        
        if ($result) {
            // Verificar dependências para cada tarefa
            foreach ($taskIds as $taskId) {
                $this->checkAndUnlockDependentTasks($taskId);
            }
        }
        
        return $result;
    }
    
    public function getProjectTasks($projectId) {
        $sql = "SELECT * FROM tb_tasks WHERE project_id = ? ORDER BY due_date ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Métodos para tarefas recorrentes
    private function generateRecurringTasks($parentTaskId, $userId, $title, $description, $startDate, $priority, $projectId, $recurrenceType, $recurrenceInterval, $endDate) {
        $currentDate = new DateTime($startDate);
        $endDateTime = $endDate ? new DateTime($endDate) : new DateTime('+1 year');
        
        while ($currentDate <= $endDateTime) {
            $this->addRecurrenceInterval($currentDate, $recurrenceType, $recurrenceInterval);
            
            if ($currentDate <= $endDateTime) {
                $sql = "INSERT INTO tb_tasks (user_id, title, description, due_date, priority, project_id, parent_recurring_task_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    $userId, 
                    $title, 
                    $description, 
                    $currentDate->format('Y-m-d'), 
                    $priority, 
                    $projectId, 
                    $parentTaskId
                ]);
            }
        }
    }
    
    private function createNextRecurringTask($taskId) {
        $task = $this->getTaskById($taskId);
        
        if ($task['is_recurring'] || $task['parent_recurring_task_id']) {
            $parentId = $task['parent_recurring_task_id'] ?: $taskId;
            $parentTask = $this->getTaskById($parentId);
            
            if ($parentTask['recurrence_end_date'] && date('Y-m-d') >= $parentTask['recurrence_end_date']) {
                return; // Recorrência expirou
            }
            
            $nextDate = new DateTime($task['due_date']);
            $this->addRecurrenceInterval($nextDate, $parentTask['recurrence_type'], $parentTask['recurrence_interval']);
            
            $sql = "INSERT INTO tb_tasks (user_id, title, description, due_date, priority, project_id, parent_recurring_task_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $task['user_id'],
                $task['title'],
                $task['description'],
                $nextDate->format('Y-m-d'),
                $task['priority'],
                $task['project_id'],
                $parentId
            ]);
        }
    }
    
    private function addRecurrenceInterval($date, $type, $interval) {
        switch ($type) {
            case 'daily':
                $date->add(new DateInterval("P{$interval}D"));
                break;
            case 'weekly':
                $date->add(new DateInterval("P" . ($interval * 7) . "D"));
                break;
            case 'monthly':
                $date->add(new DateInterval("P{$interval}M"));
                break;
            case 'yearly':
                $date->add(new DateInterval("P{$interval}Y"));
                break;
        }
    }
    
    // Métodos para dependências
    public function addTaskDependency($taskId, $dependsOnTaskId) {
        $sql = "INSERT INTO tb_task_dependencies (task_id, depends_on_task_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$taskId, $dependsOnTaskId]);
    }
    
    public function removeTaskDependency($taskId, $dependsOnTaskId) {
        $sql = "DELETE FROM tb_task_dependencies WHERE task_id = ? AND depends_on_task_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$taskId, $dependsOnTaskId]);
    }
    
    public function getTaskDependencies($taskId) {
        $sql = "SELECT t.*, td.created_at as dependency_created 
                FROM tb_task_dependencies td
                JOIN tb_tasks t ON td.depends_on_task_id = t.id
                WHERE td.task_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getDependentTasks($taskId) {
        $sql = "SELECT t.*, td.created_at as dependency_created 
                FROM tb_task_dependencies td
                JOIN tb_tasks t ON td.task_id = t.id
                WHERE td.depends_on_task_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function canCompleteTask($taskId) {
        $sql = "SELECT COUNT(*) FROM tb_task_dependencies td
                JOIN tb_tasks t ON td.depends_on_task_id = t.id
                WHERE td.task_id = ? AND t.completed = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$taskId]);
        return $stmt->fetchColumn() == 0;
    }
    
    private function checkAndUnlockDependentTasks($taskId) {
        $dependentTasks = $this->getDependentTasks($taskId);
        
        foreach ($dependentTasks as $dependentTask) {
            if ($this->canCompleteTask($dependentTask['id'])) {
                // Tarefa pode ser desbloqueada - você pode adicionar lógica adicional aqui
                // Por exemplo, enviar notificação ao usuário
            }
        }
    }
    
    public function getTasksWithDependencies($userId) {
        $sql = "SELECT t.*, 
                       GROUP_CONCAT(dt.title) as dependency_titles,
                       COUNT(td.depends_on_task_id) as dependency_count,
                       SUM(CASE WHEN dt.completed = 0 THEN 1 ELSE 0 END) as pending_dependencies
                FROM tb_tasks t
                LEFT JOIN tb_task_dependencies td ON t.id = td.task_id
                LEFT JOIN tb_tasks dt ON td.depends_on_task_id = dt.id
                WHERE t.user_id = ?
                GROUP BY t.id
                ORDER BY t.due_date ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
