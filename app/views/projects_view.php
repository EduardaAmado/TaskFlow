<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

// Include models
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/Task.php';

$projectModel = new Project();
$taskModel = new Task();

// Get all projects for the user
$projects = $projectModel->getAllUserProjects($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Projetos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Include Frappe Gantt -->
    <script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .project-card {
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .project-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .gantt-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 1rem;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .notification.show {
            transform: translateX(0);
        }
    </style>
</head>
<body>
    <!-- Notification -->
    <div id="notification" class="notification">
        <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
            <span id="notification-text"></span>
        </div>
    </div>

    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="glass-effect shadow-sm">
                <div class="flex items-center justify-between p-6">
                    <div>
                        <h2 class="text-2xl font-bold text-white">Projetos</h2>
                        <p class="text-white/70"><?php echo count($projects); ?> projeto(s)</p>
                    </div>
                    
                    <button id="add-project-btn" class="gradient-btn text-white px-6 py-2 rounded-lg shadow-lg flex items-center hover:shadow-xl transition-shadow">
                        <i class="fas fa-plus mr-2"></i>
                        Novo Projeto
                    </button>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <div class="max-w-7xl mx-auto">
                    <!-- Projects Grid -->
                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-8">
                        <?php foreach ($projects as $project): ?>
                            <div class="project-card glass-effect rounded-xl p-6" data-project-id="<?php echo $project['id']; ?>">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-xl font-semibold text-white"><?php echo htmlspecialchars($project['name']); ?></h3>
                                    <div class="flex items-center space-x-2">
                                        <button class="edit-project-btn text-white/70 hover:text-white" data-project-id="<?php echo $project['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="delete-project-btn text-white/70 hover:text-red-400" data-project-id="<?php echo $project['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <p class="text-white/80 mb-4"><?php echo htmlspecialchars($project['description']); ?></p>
                                
                                <div class="flex items-center justify-between text-sm text-white/60 mb-4">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar mr-2"></i>
                                        <?php echo date('d/m/Y', strtotime($project['start_date'])); ?> - 
                                        <?php echo date('d/m/Y', strtotime($project['end_date'])); ?>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-tasks mr-2"></i>
                                        <?php echo $project['completed_tasks']; ?>/<?php echo $project['total_tasks']; ?> tarefas
                                    </div>
                                </div>

                                <!-- Progress Bar -->
                                <?php 
                                $progress = $project['total_tasks'] > 0 
                                    ? ($project['completed_tasks'] / $project['total_tasks']) * 100 
                                    : 0;
                                ?>
                                <div class="w-full bg-white/20 rounded-full h-2 mb-4">
                                    <div class="bg-white h-2 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="px-3 py-1 text-sm rounded-full 
                                        <?php 
                                        switch($project['status']) {
                                            case 'planning': echo 'bg-blue-100 text-blue-700'; break;
                                            case 'active': echo 'bg-green-100 text-green-700'; break;
                                            case 'completed': echo 'bg-gray-100 text-gray-700'; break;
                                            case 'on_hold': echo 'bg-yellow-100 text-yellow-700'; break;
                                        }
                                        ?>">
                                        <?php echo ucfirst($project['status']); ?>
                                    </span>
                                    <button class="view-gantt-btn text-white/80 hover:text-white text-sm" 
                                            data-project-id="<?php echo $project['id']; ?>">
                                        <i class="fas fa-chart-bar mr-1"></i>
                                        Ver Gantt
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Gantt Chart Container -->
                    <div id="gantt-container" class="gantt-container hidden">
                        <div class="flex justify-between items-center mb-4">
                            <h3 id="gantt-title" class="text-lg font-semibold text-gray-800">Cronograma do Projeto</h3>
                            <button id="close-gantt" class="text-gray-600 hover:text-gray-800">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="gantt"></div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Project Modal -->
    <div id="project-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="glass-effect rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="p-6 border-b border-white/20">
                <div class="flex items-center justify-between">
                    <h3 id="modal-title" class="text-lg font-semibold text-white">Novo Projeto</h3>
                    <button id="close-modal" class="text-white/70 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form id="project-form">
                    <input type="hidden" id="project-id" name="project_id">
                    
                    <div class="mb-4">
                        <label for="project-name" class="block text-sm font-medium text-white mb-2">Nome do Projeto</label>
                        <input type="text" id="project-name" name="name" required
                               class="w-full px-3 py-2 bg-white/20 border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50">
                    </div>
                    
                    <div class="mb-4">
                        <label for="project-description" class="block text-sm font-medium text-white mb-2">Descrição</label>
                        <textarea id="project-description" name="description" rows="3"
                                  class="w-full px-3 py-2 bg-white/20 border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="start-date" class="block text-sm font-medium text-white mb-2">Data de Início</label>
                            <input type="date" id="start-date" name="start_date" required
                                   class="w-full px-3 py-2 bg-white/20 border border-white/30 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50">
                        </div>
                        <div>
                            <label for="end-date" class="block text-sm font-medium text-white mb-2">Data de Término</label>
                            <input type="date" id="end-date" name="end_date" required
                                   class="w-full px-3 py-2 bg-white/20 border border-white/30 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="project-status" class="block text-sm font-medium text-white mb-2">Status</label>
                            <select id="project-status" name="status" required
                                    class="w-full px-3 py-2 bg-white/20 border border-white/30 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50">
                                <option value="planning">Planejamento</option>
                                <option value="active">Ativo</option>
                                <option value="completed">Concluído</option>
                                <option value="on_hold">Em Espera</option>
                            </select>
                        </div>
                        <div>
                            <label for="project-color" class="block text-sm font-medium text-white mb-2">Cor</label>
                            <input type="color" id="project-color" name="color" value="#6366f1"
                                   class="w-full h-10 rounded-lg cursor-pointer">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-project" class="px-4 py-2 text-white/70 hover:text-white border border-white/30 rounded-lg transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="px-6 py-2 gradient-btn text-white rounded-lg hover:shadow-lg transition-shadow">
                            <span id="submit-text">Criar Projeto</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentProjectId = null;
        let isEditMode = false;
        let ganttChart = null;

        // DOM elements
        const addProjectBtn = document.getElementById('add-project-btn');
        const projectModal = document.getElementById('project-modal');
        const closeModal = document.getElementById('close-modal');
        const cancelProject = document.getElementById('cancel-project');
        const projectForm = document.getElementById('project-form');
        const ganttContainer = document.getElementById('gantt-container');
        const closeGantt = document.getElementById('close-gantt');

        // Event listeners
        addProjectBtn.addEventListener('click', openAddProjectModal);
        closeModal.addEventListener('click', closeProjectModal);
        cancelProject.addEventListener('click', closeProjectModal);
        projectForm.addEventListener('submit', handleProjectSubmit);
        closeGantt.addEventListener('click', () => ganttContainer.classList.add('hidden'));

        // Project buttons
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('edit-project-btn') || e.target.parentElement.classList.contains('edit-project-btn')) {
                const projectId = e.target.dataset.projectId || e.target.parentElement.dataset.projectId;
                openEditProjectModal(projectId);
            }
            
            if (e.target.classList.contains('delete-project-btn') || e.target.parentElement.classList.contains('delete-project-btn')) {
                const projectId = e.target.dataset.projectId || e.target.parentElement.dataset.projectId;
                deleteProject(projectId);
            }
            
            if (e.target.classList.contains('view-gantt-btn') || e.target.parentElement.classList.contains('view-gantt-btn')) {
                const projectId = e.target.dataset.projectId || e.target.parentElement.dataset.projectId;
                loadGanttChart(projectId);
            }
        });

        // Modal functions
        function openAddProjectModal() {
            isEditMode = false;
            currentProjectId = null;
            document.getElementById('modal-title').textContent = 'Novo Projeto';
            document.getElementById('submit-text').textContent = 'Criar Projeto';
            projectForm.reset();
            document.getElementById('project-id').value = '';
            projectModal.classList.remove('hidden');
        }

        function openEditProjectModal(projectId) {
            isEditMode = true;
            currentProjectId = projectId;
            document.getElementById('modal-title').textContent = 'Editar Projeto';
            document.getElementById('submit-text').textContent = 'Salvar Alterações';
            
            // Get project data from the DOM
            const projectCard = document.querySelector(`[data-project-id="${projectId}"]`);
            const name = projectCard.querySelector('h3').textContent;
            const description = projectCard.querySelector('p').textContent;
            const dates = projectCard.querySelector('.fa-calendar').parentElement.textContent.trim().split(' - ');
            const status = projectCard.querySelector('span').textContent.toLowerCase();
            
            document.getElementById('project-id').value = projectId;
            document.getElementById('project-name').value = name;
            document.getElementById('project-description').value = description;
            document.getElementById('start-date').value = formatDateForInput(dates[0]);
            document.getElementById('end-date').value = formatDateForInput(dates[1]);
            document.getElementById('project-status').value = status;
            
            projectModal.classList.remove('hidden');
        }

        function closeProjectModal() {
            projectModal.classList.add('hidden');
            projectForm.reset();
            isEditMode = false;
            currentProjectId = null;
        }

        // Project operations
        async function handleProjectSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(projectForm);
            const url = isEditMode ? '../app/controllers/ProjectController.php?action=update' : '../app/controllers/ProjectController.php?action=create';
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    showNotification(isEditMode ? 'Projeto atualizado com sucesso!' : 'Projeto criado com sucesso!', 'success');
                    closeProjectModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Erro ao salvar projeto', 'error');
                }
            } catch (error) {
                showNotification('Erro de conexão', 'error');
            }
        }

        async function deleteProject(projectId) {
            if (!confirm('Tem certeza que deseja excluir este projeto? Todas as tarefas associadas serão excluídas.')) {
                return;
            }
            
            try {
                const response = await fetch('../app/controllers/ProjectController.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `project_id=${projectId}`
                });
                
                if (response.ok) {
                    document.querySelector(`[data-project-id="${projectId}"]`).remove();
                    showNotification('Projeto excluído com sucesso!', 'success');
                } else {
                    showNotification('Erro ao excluir projeto', 'error');
                }
            } catch (error) {
                showNotification('Erro de conexão', 'error');
            }
        }

        // Gantt chart functions
        async function loadGanttChart(projectId) {
            try {
                const response = await fetch(`../app/controllers/ProjectController.php?action=timeline&project_id=${projectId}`);
                const data = await response.json();
                
                if (data.timeline) {
                    renderGanttChart(data.timeline);
                    ganttContainer.classList.remove('hidden');
                } else {
                    showNotification('Erro ao carregar cronograma', 'error');
                }
            } catch (error) {
                showNotification('Erro de conexão', 'error');
            }
        }

        function renderGanttChart(timeline) {
            const tasks = [
                {
                    id: 'project',
                    name: timeline.project.name,
                    start: timeline.project.start,
                    end: timeline.project.end,
                    progress: timeline.project.progress,
                    custom_class: 'project-bar'
                },
                ...timeline.tasks.map(task => ({
                    id: `task-${task.id}`,
                    name: task.name,
                    start: task.start,
                    end: task.end,
                    progress: task.progress,
                    dependencies: task.dependencies
                }))
            ];

            if (ganttChart) {
                ganttChart.refresh(tasks);
            } else {
                ganttChart = new Gantt("#gantt", tasks, {
                    header_height: 50,
                    column_width: 30,
                    step: 24,
                    view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
                    bar_height: 20,
                    bar_corner_radius: 3,
                    arrow_curve: 5,
                    padding: 18,
                    view_mode: 'Week',
                    date_format: 'YYYY-MM-DD',
                    custom_popup_html: null
                });
            }
        }

        // Helper functions
        function formatDateForInput(dateStr) {
            const [day, month, year] = dateStr.split('/');
            return `${year}-${month}-${day}`;
        }

        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notification-text');
            
            notificationText.textContent = message;
            
            const notificationDiv = notification.querySelector('div');
            notificationDiv.className = type === 'success' ? 
                'bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg' : 
                'bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg';
            
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Close modals when clicking outside
        projectModal.addEventListener('click', (e) => {
            if (e.target === projectModal) {
                closeProjectModal();
            }
        });

        // Set minimum date to today for new projects
        document.getElementById('start-date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
