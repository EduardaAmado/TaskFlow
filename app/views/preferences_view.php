<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

require_once __DIR__ . '/../models/UserPreferences.php';

$preferencesModel = new UserPreferences();
$userId = $_SESSION['user_id'];
$preferences = $preferencesModel->getUserPreferences($userId);
$availableThemes = $preferencesModel->getAvailableThemes();
$availableLanguages = $preferencesModel->getAvailableLanguages();
$availableTimezones = $preferencesModel->getCommonTimezones();
?>

<!DOCTYPE html>
<html lang="pt-BR" data-theme="<?php echo $preferences['theme']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Configurações</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --bg-secondary: rgba(255, 255, 255, 0.25);
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
        }
        
        [data-theme="dark"] {
            --bg-primary: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            --bg-secondary: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.6);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            min-height: 100vh;
            color: var(--text-primary);
        }
        
        .glass-effect {
            background: var(--bg-secondary);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.3);
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #4f46e5;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .theme-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .theme-card:hover {
            transform: translateY(-2px);
        }
        
        .theme-card.active {
            border: 2px solid #4f46e5;
            box-shadow: 0 0 20px rgba(79, 70, 229, 0.3);
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="glass-effect shadow-sm">
                <div class="flex items-center justify-between p-6">
                    <div>
                        <h2 class="text-2xl font-bold text-white">Configurações</h2>
                        <p class="text-white/70">Personalize sua experiência no TaskFlow</p>
                    </div>
                    
                    <button id="save-preferences" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Salvar Alterações
                    </button>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <div class="max-w-4xl mx-auto space-y-8">
                    
                    <!-- Aparência -->
                    <div class="glass-effect rounded-xl p-6">
                        <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                            <i class="fas fa-palette mr-3"></i>
                            Aparência
                        </h3>
                        
                        <!-- Seleção de Tema -->
                        <div class="mb-6">
                            <label class="block text-white font-medium mb-4">Tema</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach ($availableThemes as $themeKey => $theme): ?>
                                    <div class="theme-card glass-effect rounded-lg p-4 <?php echo $preferences['theme'] === $themeKey ? 'active' : ''; ?>" 
                                         data-theme="<?php echo $themeKey; ?>">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="font-medium text-white"><?php echo $theme['name']; ?></h4>
                                            <input type="radio" name="theme" value="<?php echo $themeKey; ?>" 
                                                   <?php echo $preferences['theme'] === $themeKey ? 'checked' : ''; ?> class="hidden">
                                        </div>
                                        <p class="text-white/70 text-sm"><?php echo $theme['description']; ?></p>
                                        <div class="mt-3 h-8 rounded flex">
                                            <?php if ($themeKey === 'light'): ?>
                                                <div class="flex-1 bg-white rounded-l"></div>
                                                <div class="flex-1 bg-gray-200"></div>
                                                <div class="flex-1 bg-gray-300 rounded-r"></div>
                                            <?php else: ?>
                                                <div class="flex-1 bg-gray-800 rounded-l"></div>
                                                <div class="flex-1 bg-gray-700"></div>
                                                <div class="flex-1 bg-gray-600 rounded-r"></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Toggle Rápido de Tema -->
                        <div class="flex items-center justify-between p-4 bg-white/10 rounded-lg">
                            <div>
                                <h4 class="font-medium text-white">Alternar Tema Rapidamente</h4>
                                <p class="text-white/70 text-sm">Use o botão para alternar entre claro e escuro</p>
                            </div>
                            <button id="quick-theme-toggle" class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg transition-colors">
                                <i class="fas fa-adjust mr-2"></i>
                                Alternar
                            </button>
                        </div>
                    </div>

                    <!-- Notificações -->
                    <div class="glass-effect rounded-xl p-6">
                        <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                            <i class="fas fa-bell mr-3"></i>
                            Notificações
                        </h3>
                        
                        <div class="space-y-4">
                            <!-- Notificações do App -->
                            <div class="flex items-center justify-between p-4 bg-white/10 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-white">Notificações do Aplicativo</h4>
                                    <p class="text-white/70 text-sm">Receber notificações sobre tarefas e prazos</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="notifications-enabled" 
                                           <?php echo $preferences['notifications_enabled'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <!-- Notificações por Email -->
                            <div class="flex items-center justify-between p-4 bg-white/10 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-white">Notificações por Email</h4>
                                    <p class="text-white/70 text-sm">Receber emails sobre atividades importantes</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="email-notifications" 
                                           <?php echo $preferences['email_notifications'] ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Idioma e Região -->
                    <div class="glass-effect rounded-xl p-6">
                        <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                            <i class="fas fa-globe mr-3"></i>
                            Idioma e Região
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Seleção de Idioma -->
                            <div>
                                <label class="block text-white font-medium mb-3">Idioma</label>
                                <select id="language-select" class="w-full px-4 py-3 bg-white/20 border border-white/30 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <?php foreach ($availableLanguages as $langKey => $language): ?>
                                        <option value="<?php echo $langKey; ?>" 
                                                <?php echo $preferences['language'] === $langKey ? 'selected' : ''; ?>>
                                            <?php echo $language['flag']; ?> <?php echo $language['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Seleção de Fuso Horário -->
                            <div>
                                <label class="block text-white font-medium mb-3">Fuso Horário</label>
                                <select id="timezone-select" class="w-full px-4 py-3 bg-white/20 border border-white/30 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <?php foreach ($availableTimezones as $timezoneKey => $timezoneName): ?>
                                        <option value="<?php echo $timezoneKey; ?>" 
                                                <?php echo $preferences['timezone'] === $timezoneKey ? 'selected' : ''; ?>>
                                            <?php echo $timezoneName; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Dados e Privacidade -->
                    <div class="glass-effect rounded-xl p-6">
                        <h3 class="text-xl font-semibold text-white mb-6 flex items-center">
                            <i class="fas fa-shield-alt mr-3"></i>
                            Dados e Privacidade
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="p-4 bg-white/10 rounded-lg">
                                <h4 class="font-medium text-white mb-2">Exportar Dados</h4>
                                <p class="text-white/70 text-sm mb-3">Baixe uma cópia de todos os seus dados</p>
                                <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                    <i class="fas fa-download mr-2"></i>
                                    Exportar
                                </button>
                            </div>
                            
                            <div class="p-4 bg-white/10 rounded-lg">
                                <h4 class="font-medium text-white mb-2">Limpar Cache</h4>
                                <p class="text-white/70 text-sm mb-3">Limpar dados temporários armazenados</p>
                                <button id="clear-cache" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors">
                                    <i class="fas fa-broom mr-2"></i>
                                    Limpar Cache
                                </button>
                            </div>
                            
                            <div class="p-4 bg-red-500/20 border border-red-500/30 rounded-lg">
                                <h4 class="font-medium text-white mb-2">Excluir Conta</h4>
                                <p class="text-white/70 text-sm mb-3">Esta ação não pode ser desfeita</p>
                                <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                                    <i class="fas fa-trash mr-2"></i>
                                    Excluir Conta
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 z-50">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span id="toast-message">Configurações salvas com sucesso!</span>
        </div>
    </div>

    <script>
        // Elementos do DOM
        const themeCards = document.querySelectorAll('.theme-card');
        const quickThemeToggle = document.getElementById('quick-theme-toggle');
        const saveButton = document.getElementById('save-preferences');
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toast-message');
        
        // Seleção de tema
        themeCards.forEach(card => {
            card.addEventListener('click', () => {
                // Remover active de todos os cards
                themeCards.forEach(c => c.classList.remove('active'));
                
                // Adicionar active ao card clicado
                card.classList.add('active');
                
                // Marcar o radio button
                const radio = card.querySelector('input[type="radio"]');
                radio.checked = true;
                
                // Aplicar tema imediatamente
                const theme = card.dataset.theme;
                document.documentElement.setAttribute('data-theme', theme);
            });
        });
        
        // Toggle rápido de tema
        quickThemeToggle.addEventListener('click', async () => {
            try {
                const response = await fetch('../app/controllers/PreferencesController.php?action=toggle_theme', {
                    method: 'POST'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.documentElement.setAttribute('data-theme', data.new_theme);
                    
                    // Atualizar seleção visual
                    themeCards.forEach(card => {
                        card.classList.remove('active');
                        const radio = card.querySelector('input[type="radio"]');
                        radio.checked = false;
                        
                        if (card.dataset.theme === data.new_theme) {
                            card.classList.add('active');
                            radio.checked = true;
                        }
                    });
                    
                    showToast('Tema alterado com sucesso!');
                }
            } catch (error) {
                console.error('Erro ao alterar tema:', error);
                showToast('Erro ao alterar tema', 'error');
            }
        });
        
        // Salvar preferências
        saveButton.addEventListener('click', async () => {
            try {
                const formData = new FormData();
                
                // Tema
                const selectedTheme = document.querySelector('input[name="theme"]:checked');
                if (selectedTheme) {
                    formData.append('theme', selectedTheme.value);
                }
                
                // Notificações
                formData.append('notifications_enabled', document.getElementById('notifications-enabled').checked);
                formData.append('email_notifications', document.getElementById('email-notifications').checked);
                
                // Idioma e fuso horário
                formData.append('language', document.getElementById('language-select').value);
                formData.append('timezone', document.getElementById('timezone-select').value);
                
                const response = await fetch('../app/controllers/PreferencesController.php?action=update', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Configurações salvas com sucesso!');
                } else {
                    throw new Error(data.error || 'Erro ao salvar configurações');
                }
            } catch (error) {
                console.error('Erro ao salvar configurações:', error);
                showToast('Erro ao salvar configurações', 'error');
            }
        });
        
        // Limpar cache
        document.getElementById('clear-cache').addEventListener('click', () => {
            if (confirm('Tem certeza que deseja limpar o cache?')) {
                localStorage.clear();
                sessionStorage.clear();
                showToast('Cache limpo com sucesso!');
            }
        });
        
        // Função para mostrar toast
        function showToast(message, type = 'success') {
            toastMessage.textContent = message;
            
            if (type === 'error') {
                toast.className = toast.className.replace('bg-green-600', 'bg-red-600');
            } else {
                toast.className = toast.className.replace('bg-red-600', 'bg-green-600');
            }
            
            toast.classList.remove('translate-x-full');
            
            setTimeout(() => {
                toast.classList.add('translate-x-full');
            }, 3000);
        }
        
        // Auto-save em mudanças de toggle
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                // Salvar automaticamente após 1 segundo
                setTimeout(() => {
                    saveButton.click();
                }, 1000);
            });
        });
    </script>
</body>
</html>
