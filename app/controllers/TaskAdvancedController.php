<?php
require_once __DIR__ . '/../models/TaskAdvanced.php';
require_once __DIR__ . '/../models/Attachment.php';
require_once __DIR__ . '/../models/Statistics.php';
require_once __DIR__ . '/../models/Badge.php';

class TaskAdvancedController {
    private $taskModel;
    private $attachmentModel;
    private $statisticsModel;
    private $badgeModel;
    
    public function __construct() {
        $this->taskModel = new TaskAdvanced();
        $this->attachmentModel = new Attachment();
        $this->statisticsModel = new Statistics();
        $this->badgeModel = new Badge();
    }
    
    public function createTask() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $data = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'due_date' => $_POST['due_date'],
                'priority' => $_POST['priority'],
                'project_id' => $_POST['project_id'] ?? null,
                'is_recurring' => isset($_POST['is_recurring']) ? (bool)$_POST['is_recurring'] : false,
                'recurrence_type' => $_POST['recurrence_type'] ?? null,
                'recurrence_interval' => $_POST['recurrence_interval'] ?? 1,
                'recurrence_end_date' => $_POST['recurrence_end_date'] ?? null
            ];
            
            // Criar tarefa
            $result = $this->taskModel->createTask(
                $userId,
                $data['title'],
                $data['description'],
                $data['due_date'],
                $data['priority'],
                $data['project_id'],
                $data['is_recurring'],
                $data['recurrence_type'],
                $data['recurrence_interval'],
                $data['recurrence_end_date']
            );
            
            if ($result) {
                $taskId = $this->taskModel->lastInsertId();
                
                // Upload de arquivos
                if (!empty($_FILES['attachments'])) {
                    foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                        $file = [
                            'name' => $_FILES['attachments']['name'][$key],
                            'type' => $_FILES['attachments']['type'][$key],
                            'tmp_name' => $tmp_name,
                            'error' => $_FILES['attachments']['error'][$key],
                            'size' => $_FILES['attachments']['size'][$key]
                        ];
                        
                        $this->attachmentModel->uploadFile($taskId, $file, $userId);
                    }
                }
                
                // Adicionar dependências
                if (!empty($_POST['dependencies'])) {
                    $dependencies = json_decode($_POST['dependencies'], true);
                    foreach ($dependencies as $dependencyId) {
                        $this->taskModel->addTaskDependency($taskId, $dependencyId);
                    }
                }
                
                // Atualizar estatísticas
                $this->statisticsModel->updateUserStatistics($userId);
                
                // Verificar e conceder badges
                $newBadges = $this->badgeModel->checkAndAwardBadges($userId);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Tarefa criada com sucesso',
                    'task_id' => $taskId,
                    'new_badges' => $newBadges
                ]);
            } else {
                throw new Exception('Erro ao criar tarefa');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function bulkCompleteTask() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $taskIds = json_decode($_POST['task_ids'], true);
            
            if (empty($taskIds)) {
                throw new Exception('Nenhuma tarefa selecionada');
            }
            
            // Verificar se todas as tarefas podem ser completadas (dependências)
            foreach ($taskIds as $taskId) {
                if (!$this->taskModel->canCompleteTask($taskId)) {
                    throw new Exception('Algumas tarefas têm dependências pendentes');
                }
            }
            
            $result = $this->taskModel->bulkCompleteTask($taskIds, $userId);
            
            if ($result) {
                // Atualizar estatísticas
                $this->statisticsModel->updateUserStatistics($userId);
                
                // Verificar e conceder badges
                $newBadges = $this->badgeModel->checkAndAwardBadges($userId);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Tarefas concluídas com sucesso',
                    'new_badges' => $newBadges
                ]);
            } else {
                throw new Exception('Erro ao concluir tarefas');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getTaskAttachments($taskId) {
        try {
            $attachments = $this->attachmentModel->getTaskAttachments($taskId);
            echo json_encode(['success' => true, 'attachments' => $attachments]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function deleteAttachment() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $attachmentId = $_POST['attachment_id'];
            
            $result = $this->attachmentModel->deleteAttachment($attachmentId, $userId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Anexo excluído com sucesso'
                ]);
            } else {
                throw new Exception('Erro ao excluir anexo');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getTaskDependencies($taskId) {
        try {
            $dependencies = $this->taskModel->getTaskDependencies($taskId);
            echo json_encode(['success' => true, 'dependencies' => $dependencies]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getUserStatistics() {
        try {
            $userId = $_SESSION['user_id'];
            
            $weeklyStats = $this->statisticsModel->getUserWeeklyStats($userId);
            $monthlyStats = $this->statisticsModel->getUserMonthlyStats($userId);
            $productivityRanking = $this->statisticsModel->getUserProductivityRanking();
            $taskCompletionTrends = $this->statisticsModel->getTaskCompletionTrends($userId);
            $priorityDistribution = $this->statisticsModel->getPriorityDistribution($userId);
            $projectProgress = $this->statisticsModel->getProjectProgress($userId);
            $overallStats = $this->statisticsModel->getOverallUserStats($userId);
            $activityFeed = $this->statisticsModel->getActivityFeed($userId);
            
            echo json_encode([
                'success' => true,
                'statistics' => [
                    'weekly' => $weeklyStats,
                    'monthly' => $monthlyStats,
                    'ranking' => $productivityRanking,
                    'trends' => $taskCompletionTrends,
                    'priority_distribution' => $priorityDistribution,
                    'project_progress' => $projectProgress,
                    'overall' => $overallStats,
                    'activity' => $activityFeed
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getUserBadges() {
        try {
            $userId = $_SESSION['user_id'];
            $badgeStats = $this->badgeModel->getUserBadgeStats($userId);
            
            echo json_encode([
                'success' => true,
                'badges' => $badgeStats
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

// Inicializar controller e rotear ações
$controller = new TaskAdvancedController();

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'create':
            $controller->createTask();
            break;
        case 'bulk_complete':
            $controller->bulkCompleteTask();
            break;
        case 'get_attachments':
            $controller->getTaskAttachments($_GET['task_id']);
            break;
        case 'delete_attachment':
            $controller->deleteAttachment();
            break;
        case 'get_dependencies':
            $controller->getTaskDependencies($_GET['task_id']);
            break;
        case 'get_statistics':
            $controller->getUserStatistics();
            break;
        case 'get_badges':
            $controller->getUserBadges();
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
    }
}
?>
