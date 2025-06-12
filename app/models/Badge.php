<?php
require_once __DIR__ . '/../config/database.php';

class Badge {
    private $conn;
    
    public function __construct() {
        $this->conn = getDatabaseConnection();
    }
    
    public function getAllBadges() {
        $sql = "SELECT * FROM tb_badges ORDER BY criteria_value ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserBadges($userId) {
        $sql = "SELECT b.*, ub.earned_at 
                FROM tb_badges b 
                JOIN tb_user_badges ub ON b.id = ub.badge_id 
                WHERE ub.user_id = ? 
                ORDER BY ub.earned_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function checkAndAwardBadges($userId) {
        $newBadges = [];
        
        // Verificar badges de tarefas completadas
        $tasksCompleted = $this->getCompletedTasksCount($userId);
        $taskBadges = $this->getBadgesByCriteria('tasks_completed');
        
        foreach ($taskBadges as $badge) {
            if ($tasksCompleted >= $badge['criteria_value'] && !$this->userHasBadge($userId, $badge['id'])) {
                $this->awardBadge($userId, $badge['id']);
                $newBadges[] = $badge;
            }
        }
        
        // Verificar badges de sequência de dias
        $streakDays = $this->getConsecutiveDaysStreak($userId);
        $streakBadges = $this->getBadgesByCriteria('streak_days');
        
        foreach ($streakBadges as $badge) {
            if ($streakDays >= $badge['criteria_value'] && !$this->userHasBadge($userId, $badge['id'])) {
                $this->awardBadge($userId, $badge['id']);
                $newBadges[] = $badge;
            }
        }
        
        // Verificar badges de projetos completados
        $projectsCompleted = $this->getCompletedProjectsCount($userId);
        $projectBadges = $this->getBadgesByCriteria('projects_completed');
        
        foreach ($projectBadges as $badge) {
            if ($projectsCompleted >= $badge['criteria_value'] && !$this->userHasBadge($userId, $badge['id'])) {
                $this->awardBadge($userId, $badge['id']);
                $newBadges[] = $badge;
            }
        }
        
        // Verificar badges de comentários
        $commentsCount = $this->getCommentsCount($userId);
        $commentBadges = $this->getBadgesByCriteria('comments_made');
        
        foreach ($commentBadges as $badge) {
            if ($commentsCount >= $badge['criteria_value'] && !$this->userHasBadge($userId, $badge['id'])) {
                $this->awardBadge($userId, $badge['id']);
                $newBadges[] = $badge;
            }
        }
        
        return $newBadges;
    }
    
    private function getBadgesByCriteria($criteriaType) {
        $sql = "SELECT * FROM tb_badges WHERE criteria_type = ? ORDER BY criteria_value ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$criteriaType]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function userHasBadge($userId, $badgeId) {
        $sql = "SELECT COUNT(*) FROM tb_user_badges WHERE user_id = ? AND badge_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $badgeId]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function awardBadge($userId, $badgeId) {
        $sql = "INSERT INTO tb_user_badges (user_id, badge_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$userId, $badgeId]);
    }
    
    private function getCompletedTasksCount($userId) {
        $sql = "SELECT COUNT(*) FROM tb_tasks WHERE user_id = ? AND completed = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
    
    private function getConsecutiveDaysStreak($userId) {
        $sql = "SELECT DATE(completed_at) as completion_date 
                FROM tb_tasks 
                WHERE user_id = ? AND completed = 1 AND completed_at IS NOT NULL
                GROUP BY DATE(completed_at) 
                ORDER BY completion_date DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($dates)) {
            return 0;
        }
        
        $streak = 1;
        $currentDate = new DateTime($dates[0]);
        
        for ($i = 1; $i < count($dates); $i++) {
            $previousDate = new DateTime($dates[$i]);
            $diff = $currentDate->diff($previousDate)->days;
            
            if ($diff == 1) {
                $streak++;
                $currentDate = $previousDate;
            } else {
                break;
            }
        }
        
        return $streak;
    }
    
    private function getCompletedProjectsCount($userId) {
        $sql = "SELECT COUNT(DISTINCT p.id) 
                FROM tb_projects p 
                JOIN tb_tasks t ON p.id = t.project_id 
                WHERE p.user_id = ? 
                GROUP BY p.id 
                HAVING COUNT(t.id) = SUM(CASE WHEN t.completed = 1 THEN 1 ELSE 0 END)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->rowCount();
    }
    
    private function getCommentsCount($userId) {
        $sql = "SELECT COUNT(*) FROM tb_comments WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
    
    public function getUserBadgeStats($userId) {
        $badges = $this->getUserBadges($userId);
        $totalBadges = count($this->getAllBadges());
        $earnedBadges = count($badges);
        
        return [
            'earned' => $earnedBadges,
            'total' => $totalBadges,
            'percentage' => $totalBadges > 0 ? round(($earnedBadges / $totalBadges) * 100, 1) : 0,
            'badges' => $badges
        ];
    }
}
?>
