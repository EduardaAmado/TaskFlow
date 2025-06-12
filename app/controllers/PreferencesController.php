<?php
require_once __DIR__ . '/../models/UserPreferences.php';

class PreferencesController {
    private $preferencesModel;
    
    public function __construct() {
        $this->preferencesModel = new UserPreferences();
    }
    
    public function getPreferences() {
        try {
            $userId = $_SESSION['user_id'];
            $preferences = $this->preferencesModel->getUserPreferences($userId);
            
            echo json_encode([
                'success' => true,
                'preferences' => $preferences
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function updatePreferences() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $preferences = [];
            
            if (isset($_POST['theme'])) {
                $preferences['theme'] = $_POST['theme'];
            }
            
            if (isset($_POST['notifications_enabled'])) {
                $preferences['notifications_enabled'] = (bool)$_POST['notifications_enabled'];
            }
            
            if (isset($_POST['email_notifications'])) {
                $preferences['email_notifications'] = (bool)$_POST['email_notifications'];
            }
            
            if (isset($_POST['language'])) {
                $preferences['language'] = $_POST['language'];
            }
            
            if (isset($_POST['timezone'])) {
                $preferences['timezone'] = $_POST['timezone'];
            }
            
            $result = $this->preferencesModel->updatePreferences($userId, $preferences);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Preferências atualizadas com sucesso'
                ]);
            } else {
                throw new Exception('Erro ao atualizar preferências');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function toggleTheme() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $result = $this->preferencesModel->toggleTheme($userId);
            
            if ($result) {
                $preferences = $this->preferencesModel->getUserPreferences($userId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Tema alterado com sucesso',
                    'new_theme' => $preferences['theme']
                ]);
            } else {
                throw new Exception('Erro ao alterar tema');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function toggleNotifications() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $type = $_POST['type'] ?? 'all';
            
            $result = $this->preferencesModel->toggleNotifications($userId, $type);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Configurações de notificação atualizadas'
                ]);
            } else {
                throw new Exception('Erro ao atualizar notificações');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getAvailableOptions() {
        try {
            $themes = $this->preferencesModel->getAvailableThemes();
            $languages = $this->preferencesModel->getAvailableLanguages();
            $timezones = $this->preferencesModel->getCommonTimezones();
            
            echo json_encode([
                'success' => true,
                'options' => [
                    'themes' => $themes,
                    'languages' => $languages,
                    'timezones' => $timezones
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

// Inicializar controller e rotear ações
$controller = new PreferencesController();

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'get':
            $controller->getPreferences();
            break;
        case 'update':
            $controller->updatePreferences();
            break;
        case 'toggle_theme':
            $controller->toggleTheme();
            break;
        case 'toggle_notifications':
            $controller->toggleNotifications();
            break;
        case 'get_options':
            $controller->getAvailableOptions();
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Ação não encontrada']);
    }
}
?>
