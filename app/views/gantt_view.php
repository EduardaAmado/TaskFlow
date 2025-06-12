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
    <title>TaskFlow - Cronograma Geral</title>
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

        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .gantt-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 1rem;
            min-height: 400px;
        }

        .project-selector {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .project-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .project-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .project-card.selected {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
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

        /* Custom Gantt styles */
        .gantt .bar {
            fill: #6366f1;
        }

        .gantt .bar-progress {
            fill: #10b981;
        }

        .gantt .bar-label {
            fill: #374151;
            font-weight: 500;
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
                        <h2 class="text-2xl font-bold text-white">Cronograma Geral</h2>
                        <p class="text-white/70">Visualização em linha do tempo dos projetos e tarefas</p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <select id="view-mode" class="project-selector text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-white/50">
                            <option value="Week">Semana</option>
                            <option value="Month">Mês</option>
                            <option value="Quarter Day">Quarto de Dia</option>
                            <option value="Half Day">Meio Dia</option>
                            <option value="Day">Dia</option>
                        </select>
                        
                        <button id="show-all-projects" class="gradient-btn text-white px-6 py-2 rounded-lg shadow-lg hover:shadow-xl transition-shadow">
                            <i class="fas fa-eye mr-2"></i>
                            Mostrar Todos
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <div class="max-w-7xl mx-auto">
                    <!-- Project Selection -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Selecionar Projetos</h3>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            <?php foreach ($projects as $project): ?>
                                <div class="project-card glass-effect rounded-lg p-4" 
                                     data-project-id="<?php echo $project['id']; ?>"
                                     data-project-name="<?php echo htmlspecialchars($project['name']); ?>"
                                     data-project-color="<?php echo $project['color']; ?>">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-medium text-white truncate"><?php echo htmlspecialchars($project['name']); ?></h4>
                                        <input type="checkbox" class="project-checkbox" 
                                               data-project-id="<?php echo $project['id']; ?>"
                                               style="accent-color: <?php echo $project['color']; ?>">
                                    </div>
                                    <div class="text-sm text-white/70 mb-2">
                                        <?php echo date('d/m/Y', strtotime($project['start_date'])); ?> - 
                                        <?php echo date('d/m/Y', strtotime($project['end_date'])); ?>
                                    </div>
                                    <div class="text-sm text-white/60">
                                        <?php echo $project['completed_tasks']; ?>/<?php echo $project['total_tasks']; ?> tarefas
                                    </div>
                                    <!-- Progress Bar -->
                                    <?php 
                                    $progress = $project['total_tasks'] > 0 
                                        ? ($project['completed_tasks'] / $project['total_tasks']) * 100 
                                        : 0;
                                    ?>
                                    <div class="w-full bg-white/20 rounded-full h-1 mt-2">
                                        <div class="h-1 rounded-full" 
                                             style="width: <?php echo $progress; ?>%; background-color: <?php echo $project['color']; ?>"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Gantt Chart Container -->
                    <div class="gantt-container">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-semibold text-gray-800">Cronograma dos Projetos</h3>
                            <div class="flex items-center space-x-4">
                                <span id="selected-count" class="text-sm text-gray-600">0 projetos selecionados</span>
                                <button id="refresh-gantt" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                                    <i class="fas fa-sync-alt mr-2"></i>
                                    Atualizar
                                </button>
                            </div>
                        </div>
                        
                        <div id="gantt-chart">
                            <div class="text-center py-12 text-gray-500">
                                <i class="fas fa-chart-gantt text-4xl mb-4"></i>
                                <p class="text-lg">Selecione um ou mais projetos para visualizar o cronograma</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Global variables
        let ganttChart = null;
        let selectedProjects = new Set();
        let allProjectsData = {};

        // DOM elements
        const projectCards = document.querySelectorAll('.project-card');
        const projectCheckboxes = document.querySelectorAll('.project-checkbox');
        const viewModeSelect = document.getElementById('view-mode');
        const showAllBtn = document.getElementById('show-all-projects');
        const refreshBtn = document.getElementById('refresh-gantt');
        const selectedCountSpan = document.getElementById('selected-count');

        // Event listeners
        projectCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', handleProjectSelection);
        });

        projectCards.forEach(card => {
            card.addEventListener('click', (e) => {
                if (e.target.type !== 'checkbox') {
                    const checkbox = card.querySelector('.project-checkbox');
                    checkbox.checked = !checkbox.checked;
                    handleProjectSelection({ target: checkbox });
                }
            });
        });

        viewModeSelect.addEventListener('change', updateGanttViewMode);
        showAllBtn.addEventListener('click', selectAllProjects);
        refreshBtn.addEventListener('click', refreshGanttChart);

        // Project selection handling
        function handleProjectSelection(e) {
            const projectId = e.target.dataset.projectId;
            const card = document.querySelector(`[data-project-id="${projectId}"]`);
            
            if (e.target.checked) {
                selectedProjects.add(projectId);
                card.classList.add('selected');
                loadProjectData(projectId);
            } else {
                selectedProjects.delete(projectId);
                card.classList.remove('selected');
                delete allProjectsData[projectId];
                updateGanttChart();
            }
            
            updateSelectedCount();
        }

        function selectAllProjects() {
            projectCheckboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    checkbox.checked = true;
                    handleProjectSelection({ target: checkbox });
                }
            });
        }

        function updateSelectedCount() {
            selectedCountSpan.textContent = `${selectedProjects.size} projeto(s) selecionado(s)`;
        }

        // Load project data
        async function loadProjectData(projectId) {
            try {
                const response = await fetch(`../app/controllers/ProjectController.php?action=timeline&project_id=${projectId}`);
                const data = await response.json();
                
                if (data.timeline) {
                    allProjectsData[projectId] = data.timeline;
                    updateGanttChart();
                } else {
                    showNotification('Erro ao carregar dados do projeto', 'error');
                }
            } catch (error) {
                showNotification('Erro de conexão', 'error');
            }
        }

        // Update Gantt chart
        function updateGanttChart() {
            if (selectedProjects.size === 0) {
                document.getElementById('gantt-chart').innerHTML = `
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-chart-gantt text-4xl mb-4"></i>
                        <p class="text-lg">Selecione um ou mais projetos para visualizar o cronograma</p>
                    </div>
                `;
                return;
            }

            const allTasks = [];
            
            // Combine all selected projects data
            Object.values(allProjectsData).forEach((projectData, index) => {
                // Add project as a task
                allTasks.push({
                    id: `project-${projectData.project.id}`,
                    name: `📁 ${projectData.project.name}`,
                    start: projectData.project.start,
                    end: projectData.project.end,
                    progress: projectData.project.progress,
                    custom_class: 'project-bar'
                });

                // Add project tasks
                projectData.tasks.forEach(task => {
                    allTasks.push({
                        id: `task-${task.id}`,
                        name: `  └ ${task.name}`,
                        start: task.start,
                        end: task.end,
                        progress: task.progress,
                        dependencies: task.dependencies
                    });
                });
            });

            if (allTasks.length === 0) {
                document.getElementById('gantt-chart').innerHTML = `
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
                        <p class="text-lg">Nenhuma tarefa encontrada nos projetos selecionados</p>
                    </div>
                `;
                return;
            }

            // Clear the container
            document.getElementById('gantt-chart').innerHTML = '';

            // Create or update Gantt chart
            try {
                ganttChart = new Gantt("#gantt-chart", allTasks, {
                    header_height: 50,
                    column_width: 30,
                    step: 24,
                    view_modes: ['Quarter Day', 'Half Day', 'Day', 'Week', 'Month'],
                    bar_height: 20,
                    bar_corner_radius: 3,
                    arrow_curve: 5,
                    padding: 18,
                    view_mode: viewModeSelect.value,
                    date_format: 'YYYY-MM-DD',
                    custom_popup_html: function(task) {
                        const isProject = task.id.startsWith('project-');
                        return `
                            <div class="details-container">
                                <h5>${task.name}</h5>
                                <p>Início: ${task._start.toLocaleDateString('pt-BR')}</p>
                                <p>Fim: ${task._end.toLocaleDateString('pt-BR')}</p>
                                <p>Progresso: ${task.progress}%</p>
                                ${isProject ? '<p>📁 Projeto</p>' : '<p>📋 Tarefa</p>'}
                            </div>
                        `;
                    }
                });
            } catch (error) {
                console.error('Erro ao criar gráfico Gantt:', error);
                document.getElementById('gantt-chart').innerHTML = `
                    <div class="text-center py-12 text-red-500">
                        <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
                        <p class="text-lg">Erro ao carregar o cronograma</p>
                    </div>
                `;
            }
        }

        function updateGanttViewMode() {
            if (ganttChart) {
                ganttChart.change_view_mode(viewModeSelect.value);
            }
        }

        function refreshGanttChart() {
            // Reload all selected projects data
            const promises = Array.from(selectedProjects).map(projectId => loadProjectData(projectId));
            
            Promise.all(promises).then(() => {
                showNotification('Cronograma atualizado!', 'success');
            }).catch(() => {
                showNotification('Erro ao atualizar cronograma', 'error');
            });
        }

        // Notification system
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

        // Initialize
        updateSelectedCount();
    </script>
</body>
</html>
