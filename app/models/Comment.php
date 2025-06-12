<?php
require_once __DIR__ . '/../config/database.php';

class Comment {
    private $conn;
    private $table = 'tb_comments';

    public function __construct() {
        $this->conn = getDatabaseConnection();
    }

    // Create new comment
    public function create($taskId, $userId, $comment) {
        $sql = "INSERT INTO {$this->table} (task_id, user_id, comment) 
                VALUES (:task_id, :user_id, :comment)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':task_id' => $taskId,
            ':user_id' => $userId,
            ':comment' => $comment
        ]);
    }

    // Get comments for a task
    public function getTaskComments($taskId) {
        $sql = "SELECT c.*, u.username 
                FROM {$this->table} c
                JOIN tb_users u ON c.user_id = u.id
                WHERE c.task_id = :task_id 
                ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':task_id' => $taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Delete comment
    public function delete($commentId, $userId) {
        $sql = "DELETE FROM {$this->table} 
                WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id' => $commentId,
            ':user_id' => $userId
        ]);
    }

    // Get comment count for a task
    public function getCommentCount($taskId) {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE task_id = :task_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':task_id' => $taskId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    // Check if user owns comment
    public function isCommentOwner($commentId, $userId) {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE id = :id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':id' => $commentId,
            ':user_id' => $userId
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }
}
