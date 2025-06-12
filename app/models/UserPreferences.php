<?php
require_once __DIR__ . '/../config/database.php';

class UserPreferences {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getUserPreferences($userId) {
        // Verificar se já existem preferências
        $sql = "SELECT * FROM user_preferences WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$preferences) {
            // Criar preferências padrão se não existirem
            $this->createDefaultPreferences($userId);
            return $this->getUserPreferences($userId);
        }
        
        return $preferences;
    }
    
    public function createDefaultPreferences($userId) {
        $sql = "INSERT INTO user_preferences (
                    user_id, 
                    theme, 
                    notifications_enabled, 
                    email_notifications, 
                    language, 
                    timezone
                ) VALUES (?, 'light', 1, 1, 'pt-BR', 'America/Sao_Paulo')";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    public function updatePreferences($userId, $preferences) {
        $validThemes = ['light', 'dark'];
        $validLanguages = ['pt-BR', 'en-US', 'es-ES'];
        
        // Validar tema
        if (isset($preferences['theme']) && !in_array($preferences['theme'], $validThemes)) {
            throw new Exception('Tema inválido');
        }
        
        // Validar idioma
        if (isset($preferences['language']) && !in_array($preferences['language'], $validLanguages)) {
            throw new Exception('Idioma inválido');
        }
        
        // Validar timezone
        if (isset($preferences['timezone'])) {
            try {
                new DateTimeZone($preferences['timezone']);
            } catch (Exception $e) {
                throw new Exception('Fuso horário inválido');
            }
        }
        
        try {
            $currentPrefs = $this->getUserPreferences($userId);
            
            $theme = $preferences['theme'] ?? $currentPrefs['theme'];
            $notificationsEnabled = isset($preferences['notifications_enabled']) ? 
                                  (bool)$preferences['notifications_enabled'] : 
                                  $currentPrefs['notifications_enabled'];
            $emailNotifications = isset($preferences['email_notifications']) ? 
                                (bool)$preferences['email_notifications'] : 
                                $currentPrefs['email_notifications'];
            $language = $preferences['language'] ?? $currentPrefs['language'];
            $timezone = $preferences['timezone'] ?? $currentPrefs['timezone'];
            
            $sql = "UPDATE user_preferences 
                    SET theme = ?,
                        notifications_enabled = ?,
                        email_notifications = ?,
                        language = ?,
                        timezone = ?,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE user_id = ?";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $theme,
                $notificationsEnabled,
                $emailNotifications,
                $language,
                $timezone,
                $userId
            ]);
        } catch (Exception $e) {
            error_log("Erro ao atualizar preferências: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function toggleTheme($userId) {
        $currentPrefs = $this->getUserPreferences($userId);
        $newTheme = $currentPrefs['theme'] === 'light' ? 'dark' : 'light';
        
        return $this->updatePreferences($userId, ['theme' => $newTheme]);
    }
    
    public function toggleNotifications($userId, $type = 'all') {
        $currentPrefs = $this->getUserPreferences($userId);
        $updates = [];
        
        switch ($type) {
            case 'app':
                $updates['notifications_enabled'] = !$currentPrefs['notifications_enabled'];
                break;
            case 'email':
                $updates['email_notifications'] = !$currentPrefs['email_notifications'];
                break;
            case 'all':
                $updates['notifications_enabled'] = !$currentPrefs['notifications_enabled'];
                $updates['email_notifications'] = !$currentPrefs['email_notifications'];
                break;
            default:
                throw new Exception('Tipo de notificação inválido');
        }
        
        return $this->updatePreferences($userId, $updates);
    }
    
    public function setLanguage($userId, $language) {
        return $this->updatePreferences($userId, ['language' => $language]);
    }
    
    public function setTimezone($userId, $timezone) {
        return $this->updatePreferences($userId, ['timezone' => $timezone]);
    }
    
    public function getAvailableThemes() {
        return [
            'light' => [
                'name' => 'Claro',
                'description' => 'Tema claro padrão',
                'preview' => 'assets/images/themes/light.png'
            ],
            'dark' => [
                'name' => 'Escuro',
                'description' => 'Tema escuro para uso noturno',
                'preview' => 'assets/images/themes/dark.png'
            ]
        ];
    }
    
    public function getAvailableLanguages() {
        return [
            'pt-BR' => [
                'name' => 'Português (Brasil)',
                'native_name' => 'Português',
                'flag' => '🇧🇷'
            ],
            'en-US' => [
                'name' => 'English (US)',
                'native_name' => 'English',
                'flag' => '🇺🇸'
            ],
            'es-ES' => [
                'name' => 'Español',
                'native_name' => 'Español',
                'flag' => '🇪🇸'
            ]
        ];
    }
    
    public function getCommonTimezones() {
        return [
            'America/Sao_Paulo' => 'São Paulo',
            'America/New_York' => 'Nova York',
            'Europe/London' => 'Londres',
            'Europe/Paris' => 'Paris',
            'Asia/Tokyo' => 'Tóquio'
        ];
    }
}
?>
