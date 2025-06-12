<?php
require_once __DIR__ . '/../config/database.php';

class Attachment {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function uploadFile($taskId, $file, $uploadedBy) {
        try {
            // Criar diretório de uploads se não existir
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Gerar nome único para o arquivo
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;
            
            // Verificar tipo de arquivo permitido
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip', 'rar'];
            if (!in_array(strtolower($fileExtension), $allowedTypes)) {
                throw new Exception('Tipo de arquivo não permitido');
            }
            
            // Verificar tamanho do arquivo (máximo 10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                throw new Exception('Arquivo muito grande. Máximo 10MB');
            }
            
            // Mover arquivo para diretório de uploads
            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Salvar informações no banco de dados
                $sql = "INSERT INTO task_attachments (task_id, filename, original_filename, file_path, file_size, mime_type, uploaded_by) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $this->conn->prepare($sql);
                
                return $stmt->execute([
                    $taskId,
                    $fileName,
                    $file['name'],
                    'uploads/' . $fileName,
                    $file['size'],
                    $file['type'],
                    $uploadedBy
                ]);
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro no upload: " . $e->getMessage());
            return false;
        }
    }
    
    public function getTaskAttachments($taskId) {
        $sql = "SELECT ta.*, u.username as uploaded_by_name 
                FROM task_attachments ta 
                JOIN users u ON ta.uploaded_by = u.id 
                WHERE ta.task_id = ? 
                ORDER BY ta.uploaded_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$taskId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function deleteAttachment($attachmentId, $userId) {
        try {
            // Buscar informações do arquivo
            $sql = "SELECT * FROM task_attachments WHERE id = ? AND uploaded_by = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$attachmentId, $userId]);
            $attachment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($attachment) {
                // Deletar arquivo físico
                $filePath = __DIR__ . '/../../public/' . $attachment['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                // Deletar registro do banco
                $sql = "DELETE FROM task_attachments WHERE id = ?";
                $stmt = $this->conn->prepare($sql);
                return $stmt->execute([$attachmentId]);
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Erro ao deletar anexo: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAttachmentById($attachmentId) {
        $sql = "SELECT * FROM task_attachments WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$attachmentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
