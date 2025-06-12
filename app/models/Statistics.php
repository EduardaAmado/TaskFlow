<?php
require_once __DIR__ . '/../config/database.php';

class Statistics {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function updateUserStatistics($userId, $date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        try {
            // Verificar se já existe registro para o dia
            $sql = "SELECT id FROM user_statistics WHERE user_id = ? AND date = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId, $date]);
            $existing = $stmt->fetch();
            
            // Calcular estatísticas do dia
            $tasksCompleted = $this->getTasksCompletedOnDate($userId, $date);
            $tasksCreated = $this->getTasksCreatedOnDate($userId, $date);
            $commentsMade = $this->getCommentsOnDate($userId, $date);
            
            if ($existing) {
                // Atualizar registro existente
                $sql = "UPDATE user_statistics 
                        SET tasks_completed = ?, tasks_created = ?, comments_made = ? 
                        WHERE user_id = ? AND date = ?";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([$tasksCompleted, $tasksCreated, $commentsMade, $userId, $date]);
            } else {
                // Criar novo registro
                $sql = "INSERT INTO user_statistics (user_id, date, tasks_completed, tasks_created, comments_made) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([$userId, $date, $tasksCompleted, $tasksCreated, $commentsMade]);
            }
        } catch (Exception $e) {
            error_log("Erro ao atualizar estatísticas: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserWeeklyStats($userId, $weeks = 4) {
        $sql = "SELECT 
                    DATE(date) as date,
                    WEEK(date) as week_number,
                    YEAR(date) as year,
                    SUM(tasks_completed) as tasks_completed,
                    SUM(tasks_created) as tasks_created,
                    SUM(comments_made) as comments_made
                FROM user_statistics 
                WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL ? WEEK)
                GROUP BY YEAR(date), WEEK(date)
                ORDER BY year DESC, week_number DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $weeks]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserMonthlyStats($userId, $months = 6) {
        $sql = "SELECT 
                    YEAR(date) as year,
                    MONTH(date) as month,
                    MONTHNAME(date) as month_name,
                    SUM(tasks_completed) as tasks_completed,
                    SUM(tasks_created) as tasks_created,
                    SUM(comments_made) as comments_made
                FROM user_statistics 
                WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                GROUP BY YEAR(date), MONTH(date)
                ORDER BY year DESC, month DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $months]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserProductivityRanking($limit = 10) {
        $sql = "SELECT 
                    u.id,
                    u.username,
                    u.email,
                    SUM(us.tasks_completed) as total_tasks_completed,
                    SUM(us.tasks_created) as total_tasks_created,
                    SUM(us.comments_made) as total_comments_made,
                    AVG(us.tasks_completed) as avg_daily_tasks
                FROM users u
                LEFT JOIN user_statistics us ON u.id = us.user_id
                WHERE us.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY u.id, u.username, u.email
                ORDER BY total_tasks_completed DESC, avg_daily_tasks DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTaskCompletionTrends($userId, $days = 30) {
        $sql = "SELECT 
                    DATE(date) as date,
                    tasks_completed,
                    tasks_created,
                    (tasks_completed / NULLIF(tasks_created, 0)) * 100 as completion_rate
                FROM user_statistics 
                WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                ORDER BY date ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPriorityDistribution($userId) {
        $sql = "SELECT 
                    priority,
                    COUNT(*) as count,
                    SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_count
                FROM tasks 
                WHERE user_id = ?
                GROUP BY priority";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProjectProgress($userId) {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.description,
                    COUNT(t.id) as total_tasks,
                    SUM(CASE WHEN t.completed = 1 THEN 1 ELSE 0 END) as completed_tasks,
                    (SUM(CASE WHEN t.completed = 1 THEN 1 ELSE 0 END) / COUNT(t.id)) * 100 as progress_percentage
                FROM projects p
                LEFT JOIN tasks t ON p.id = t.project_id
                WHERE p.user_id = ?
                GROUP BY p.id, p.name, p.description
                ORDER BY progress_percentage DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getOverallUserStats($userId) {
        $sql = "SELECT 
                    COUNT(CASE WHEN completed = 1 THEN 1 END) as total_completed,
                    COUNT(CASE WHEN completed = 0 THEN 1 END) as total_pending,
                    COUNT(*) as total_tasks,
                    COUNT(CASE WHEN priority = 'high' THEN 1 END) as high_priority_tasks,
                    COUNT(CASE WHEN due_date < CURDATE() AND completed = 0 THEN 1 END) as overdue_tasks
                FROM tasks 
                WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        $taskStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sql = "SELECT COUNT(*) as total_projects FROM projects WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        $projectStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sql = "SELECT COUNT(*) as total_comments FROM comments WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        $commentStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return array_merge($taskStats, $projectStats, $commentStats);
    }
    
    private function getTasksCompletedOnDate($userId, $date) {
        $sql = "SELECT COUNT(*) FROM tasks 
                WHERE user_id = ? AND completed = 1 AND DATE(completed_at) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $date]);
        return $stmt->fetchColumn();
    }
    
    private function getTasksCreatedOnDate($userId, $date) {
        $sql = "SELECT COUNT(*) FROM tasks 
                WHERE user_id = ? AND DATE(created_at) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $date]);
        return $stmt->fetchColumn();
    }
    
    private function getCommentsOnDate($userId, $date) {
        $sql = "SELECT COUNT(*) FROM comments 
                WHERE user_id = ? AND DATE(created_at) = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $date]);
        return $stmt->fetchColumn();
    }
    
    public function getActivityFeed($userId, $limit = 20) {
        $sql = "SELECT * FROM activity_log 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function logActivity($userId, $actionType, $entityType, $entityId, $description = null) {
        $sql = "INSERT INTO activity_log (user_id, action_type, entity_type, entity_id, description) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$userId, $actionType, $entityType, $entityId, $description]);
    }
}
?>
