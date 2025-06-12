<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

// Include models
require_once __DIR__ . '/../models/Task.php';
require_once __DIR__ . '/../models/Comment.php';

$taskModel = new Task();
$commentModel = new Comment();

// Get filter from URL
$filter = $_GET['filter'] ?? 'all';

// Get tasks based on filter
$tasks = $taskModel->getAllUserTasks($_SESSION['user_id'], $filter === 'all' ? null : $filter);

// Add comment counts to tasks
foreach ($tasks as &$task) {
    $task['comment_count'] = $commentModel->getCommentCount($task['id']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Gerenciador de Tarefas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .task-card {
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .priority-high {
            border-left: 4px solid #ef4444;
        }

        .priority-medium {
            border-left: 4px solid #f59e0b;
        }

        .priority-low {
            border-left: 4px solid #10b981;
        }

        .completed {
            opacity: 0.7;
            background: rgba(255, 255, 255, 0.5);
        }

        .completed .task-title {
            text-decoration: line-through;
        }

        .gradient-btn {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        .gradient-btn:hover {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
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
        <div class="w-64 glass-effect shadow-xl">
            <div class="p-6">
                <div class="flex items-center mb-8">
                    <i class="fas fa-tasks text-2xl text-white mr-3"></i>
                    <h1 class="text-xl font-bold text-white">TaskFlow</h1>
                </div>
                
                <div class="mb-6">
                    <div class="text-white/80 text-sm mb-2">Bem-vindo,</div>
                    <div class="text-white font-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                </div>

                <nav class="space-y-2">
                    <a href="?filter=all" class="flex items-center px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors <?php echo $filter === 'all' ? 'bg-white/20 text-white' : ''; ?>">
                        <i class="fas fa-list mr-3"></i>
                        Todas as Tarefas
                    </a>
                    <a href="?filter=today" class="flex items-center px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors <?php echo $filter === 'today' ? 'bg-white/20 text-white' : ''; ?>">
                        <i class="fas fa-calendar-day mr-3"></i>
                        Hoje
                    </a>
                    <a href="?filter=this_week" class="flex items-center px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors <?php echo $filter === 'this_week' ? 'bg-white/20 text-white' : ''; ?>">
                        <i class="fas fa-calendar-week mr-3"></i>
                        Esta Semana
                    </a>
                    <a href="?filter=important" class="flex items-center px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors <?php echo $filter === 'important' ? 'bg-white/20 text-white' : ''; ?>">
                        <i class="fas fa-exclamation-triangle mr-3"></i>
                        Importantes
                    </a>
                </nav>

                <div class="mt-8 pt-8 border-t border-white/20">
                    <a href="../public/login.php?logout=1" class="flex items-center px-4 py-3 text-white/80 hover:text-white hover:bg-red-500/20 rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Sair
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="glass-effect shadow-sm">
                <div class="flex items-center justify-between p-6">
                    <div>
                        <h2 class="text-2xl font-bold text-white">
                            <?php 
                            switch($filter) {
                                case 'today': echo 'Tarefas de Hoje'; break;
                                case 'this_week': echo 'Tarefas desta Semana'; break;
                                case 'important': echo 'Tarefas Importantes'; break;
                                default: echo 'Todas as Tarefas';
                            }
                            ?>
                        </h2>
                        <p class="text-white/70"><?php echo count($tasks); ?> tarefa(s) encontrada(s)</p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- Search -->
                        <div class="relative">
                            <input type="text" id="search-input" placeholder="Buscar tarefas..." 
                                   class="pl-10 pr-4 py-2 bg-white/20 border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50">
                            <i class="fas fa-search absolute left-3 top-3 text-white/70"></i>
                        </div>
                        
                        <!-- Add Task Button -->
                        <button id="add-task-btn" class="gradient-btn text-white px-6 py-2 rounded-lg shadow-lg flex items-center hover:shadow-xl transition-shadow">
                            <i class="fas fa-plus mr-2"></i>
                            Nova Tarefa
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <div class="max-w-6xl mx-auto">
                    <!-- Tasks Grid -->
                    <div id="tasks-container" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <?php if (empty($tasks)): ?>
                            <div class="col-span-full text-center py-12">
                                <i class="fas fa-tasks text-6xl text-white/30 mb-4"></i>
                                <h3 class="text-xl font-semibold text-white mb-2">Nenhuma tarefa encontrada</h3>
                                <p class="text-white/70">Comece criando sua primeira tarefa!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                                <div class="task-card glass-effect rounded-xl p-6 priority-<?php echo $task['priority']; ?> <?php echo $task['completed'] ? 'completed' : ''; ?>" 
                                     data-task-id="<?php echo $task['id']; ?>">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center">
                                            <input type="checkbox" class="task-checkbox mr-3 h-5 w-5 rounded border-white/30 text-indigo-600 focus:ring-indigo-500 bg-white/20" 
                                                   <?php echo $task['completed'] ? 'checked' : ''; ?> 
                                                   data-task-id="<?php echo $task['id']; ?>">
                                            <div>
                                                <h3 class="task-title font-semibold text-white text-lg"><?php echo htmlspecialchars($task['title']); ?></h3>
                                                <span class="priority-badge inline-block px-2 py-1 text-xs rounded-full font-medium
                                                    <?php 
                                                    switch($task['priority']) {
                                                        case 'high': echo 'bg-red-100 text-red-700'; break;
                                                        case 'medium': echo 'bg-yellow-100 text-yellow-700'; break;
                                                        case 'low': echo 'bg-green-100 text-green-700'; break;
                                                    }
                                                    ?>">
                                                    <?php echo ucfirst($task['priority']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            <button class="edit-task-btn text-white/70 hover:text-white p-2" data-task-id="<?php echo $task['id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="delete-task-btn text-white/70 hover:text-red-400 p-2" data-task-id="<?php echo $task['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <p class="text-white/80 mb-4"><?php echo htmlspecialchars($task['description']); ?></p>
                                    
                                    <div class="flex items-center justify-between text-sm text-white/60">
                                        <div class="flex items-center">
                                            <i class="fas fa-calendar mr-2"></i>
                                            <?php echo date('d/m/Y', strtotime($task['due_date'])); ?>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-comments mr-2"></i>
                                            <span class="comment-count"><?php echo $task['comment_count']; ?></span>
                                            <button class="view-comments-btn ml-2 hover:text-white" data-task-id="<?php echo $task['id']; ?>">
                                                Ver comentários
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Task Modal -->
    <div id="task-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="glass-effect rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="p-6 border-b border-white/20">
                <div class="flex items-center justify-between">
                    <h3 id="modal-title" class="text-lg font-semibold text-white">Nova Tarefa</h3>
                    <button id="close-modal" class="text-white/70 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form id="task-form">
                    <input type="hidden" id="task-id" name="task_id">
                    
                    <div class="mb-4">
                        <label for="task-title" class="block text-sm font-medium text-white mb-2">Título</label>
                        <input type="text" id="task-title" name="task_title" required
                               class="w-full px-3 py-2 bg-white/20 border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50"
                               placeholder="Digite o título da tarefa">
                    </div>
                    
                    <div class="mb-4">
                        <label for="task-description" class="block text-sm font-medium text-white mb-2">Descrição</label>
                        <textarea id="task-description" name="task_description" rows="3" required
                                  class="w-full px-3 py-2 bg-white/20 border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50"
                                  placeholder="Digite a descrição da tarefa"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="task-date" class="block text-sm font-medium text-white mb-2">Data de Vencimento</label>
                            <input type="date" id="task-date" name="task_date" required
                                   class="w-full px-3 py-2 bg-white/20 border border-white/30 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50">
                        </div>
                        <div>
                            <label for="task-priority" class="block text-sm font-medium text-white mb-2">Prioridade</label>
                            <select id="task-priority" name="task_priority" required
                                    class="w-full px-3 py-2 bg-white/20 border border-white/30 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50">
                                <option value="low">Baixa</option>
                                <option value="medium" selected>Média</option>
                                <option value="high">Alta</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancel-task" class="px-4 py-2 text-white/70 hover:text-white border border-white/30 rounded-lg transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="px-6 py-2 gradient-btn text-white rounded-lg hover:shadow-lg transition-shadow">
                            <span id="submit-text">Criar Tarefa</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Comments Modal -->
    <div id="comments-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="glass-effect rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[80vh] overflow-hidden">
            <div class="p-6 border-b border-white/20">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">Comentários</h3>
                    <button id="close-comments-modal" class="text-white/70 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 overflow-y-auto max-h-96">
                <div id="comments-list" class="space-y-4 mb-6">
                    <!-- Comments will be loaded here -->
                </div>
                
                <form id="comment-form" class="border-t border-white/20 pt-4">
                    <input type="hidden" id="comment-task-id" name="task_id">
                    <div class="flex space-x-3">
                        <input type="text" id="comment-input" name="comment" placeholder="Adicionar comentário..." required
                               class="flex-1 px-3 py-2 bg-white/20 border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50">
                        <button type="submit" class="px-4 py-2 gradient-btn text-white rounded-lg hover:shadow-lg transition-shadow">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let currentTaskId = null;
        let isEditMode = false;

        // DOM elements
        const addTaskBtn = document.getElementById('add-task-btn');
        const taskModal = document.getElementById('task-modal');
        const closeModal = document.getElementById('close-modal');
        const cancelTask = document.getElementById('cancel-task');
        const taskForm = document.getElementById('task-form');
        const commentsModal = document.getElementById('comments-modal');
        const closeCommentsModal = document.getElementById('close-comments-modal');
        const commentForm = document.getElementById('comment-form');
        const searchInput = document.getElementById('search-input');

        // Event listeners
        addTaskBtn.addEventListener('click', openAddTaskModal);
        closeModal.addEventListener('click', closeTaskModal);
        cancelTask.addEventListener('click', closeTaskModal);
        taskForm.addEventListener('submit', handleTaskSubmit);
        closeCommentsModal.addEventListener('click', closeCommentsModalFunc);
        commentForm.addEventListener('submit', handleCommentSubmit);
        searchInput.addEventListener('input', handleSearch);

        // Task checkboxes
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('task-checkbox')) {
                toggleTaskComplete(e.target.dataset.taskId);
            }
            
            if (e.target.classList.contains('edit-task-btn') || e.target.parentElement.classList.contains('edit-task-btn')) {
                const taskId = e.target.dataset.taskId || e.target.parentElement.dataset.taskId;
                openEditTaskModal(taskId);
            }
            
            if (e.target.classList.contains('delete-task-btn') || e.target.parentElement.classList.contains('delete-task-btn')) {
                const taskId = e.target.dataset.taskId || e.target.parentElement.dataset.taskId;
                deleteTask(taskId);
            }
            
            if (e.target.classList.contains('view-comments-btn')) {
                const taskId = e.target.dataset.taskId;
                openCommentsModal(taskId);
            }
        });

        // Modal functions
        function openAddTaskModal() {
            isEditMode = false;
            currentTaskId = null;
            document.getElementById('modal-title').textContent = 'Nova Tarefa';
            document.getElementById('submit-text').textContent = 'Criar Tarefa';
            taskForm.reset();
            document.getElementById('task-id').value = '';
            taskModal.classList.remove('hidden');
        }

        function openEditTaskModal(taskId) {
            isEditMode = true;
            currentTaskId = taskId;
            document.getElementById('modal-title').textContent = 'Editar Tarefa';
            document.getElementById('submit-text').textContent = 'Salvar Alterações';
            
            // Get task data from the DOM
            const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
            const title = taskCard.querySelector('.task-title').textContent;
            const description = taskCard.querySelector('p').textContent;
            const priority = taskCard.classList.contains('priority-high') ? 'high' : 
                           taskCard.classList.contains('priority-medium') ? 'medium' : 'low';
            
            document.getElementById('task-id').value = taskId;
            document.getElementById('task-title').value = title;
            document.getElementById('task-description').value = description;
            document.getElementById('task-priority').value = priority;
            
            taskModal.classList.remove('hidden');
        }

        function closeTaskModal() {
            taskModal.classList.add('hidden');
            taskForm.reset();
            isEditMode = false;
            currentTaskId = null;
        }

        function openCommentsModal(taskId) {
            currentTaskId = taskId;
            document.getElementById('comment-task-id').value = taskId;
            loadComments(taskId);
            commentsModal.classList.remove('hidden');
        }

        function closeCommentsModalFunc() {
            commentsModal.classList.add('hidden');
            currentTaskId = null;
        }

        // Task operations
        async function handleTaskSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(taskForm);
            const url = isEditMode ? '../app/controllers/TaskController.php?action=update' : '../public/add_task.php';
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    showNotification(isEditMode ? 'Tarefa atualizada com sucesso!' : 'Tarefa criada com sucesso!', 'success');
                    closeTaskModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Erro ao salvar tarefa', 'error');
                }
            } catch (error) {
                showNotification('Erro de conexão', 'error');
            }
        }

        async function toggleTaskComplete(taskId) {
            try {
                const response = await fetch('../app/controllers/TaskController.php?action=toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `task_id=${taskId}`
                });
                
                if (response.ok) {
                    const taskCard = document.querySelector(`[data-task-id="${taskId}"]`);
                    taskCard.classList.toggle('completed');
                    showNotification('Status da tarefa atualizado!', 'success');
                } else {
                    showNotification('Erro ao atualizar tarefa', 'error');
                }
            } catch (error) {
                showNotification('Erro de conexão', 'error');
            }
        }

        async function deleteTask(taskId) {
            if (!confirm('Tem certeza que deseja excluir esta tarefa?')) {
                return;
            }
            
            try {
                const response = await fetch('../app/controllers/TaskController.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `task_id=${taskId}`
                });
                
                if (response.ok) {
                    document.querySelector(`[data-task-id="${taskId}"]`).remove();
                    showNotification('Tarefa excluída com sucesso!', 'success');
                } else {
                    showNotification('Erro ao excluir tarefa', 'error');
                }
            } catch (error) {
                showNotification('Erro de conexão', 'error');
            }
        }

        // Comment operations
        async function loadComments(taskId) {
            try {
                const response = await fetch(`../app/controllers/TaskController.php?action=get_comments&task_id=${taskId}`);
                const data = await response.json();
                
                const commentsList = document.getElementById('comments-list');
                commentsList.innerHTML = '';
                
                if (data.comments && data.comments.length > 0) {
                    data.comments.forEach(comment => {
                        const commentDiv = document.createElement('div');
                        commentDiv.className = 'bg-white/10 rounded-lg p-3';
                        commentDiv.innerHTML = `
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-medium text-white">${comment.username}</span>
                                <span class="text-xs text-white/60">${new Date(comment.created_at).toLocaleString('pt-BR')}</span>
                            </div>
                            <p class="text-white/80">${comment.comment}</p>
                        `;
                        commentsList.appendChild(commentDiv);
                    });
                } else {
                    commentsList.innerHTML = '<p class="text-white/60 text-center">Nenhum comentário ainda.</p>';
                }
            } catch (error) {
                showNotification('Erro ao carregar comentários', 'error');
            }
        }

        async function handleCommentSubmit(e) {
            e.preventDefault();
            
            const formData = new FormData(commentForm);
            
            try {
                const response = await fetch('../app/controllers/TaskController.php?action=add_comment', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    document.getElementById('comment-input').value = '';
                    loadComments(currentTaskId);
                    
                    // Update comment count in task card
                    const taskCard = document.querySelector(`[data-task-id="${currentTaskId}"]`);
                    const commentCount = taskCard.querySelector('.comment-count');
                    commentCount.textContent = parseInt(commentCount.textContent) + 1;
                    
                    showNotification('Comentário adicionado!', 'success');
                } else {
                    showNotification('Erro ao adicionar comentário', 'error');
                }
            } catch (error) {
                showNotification('Erro de conexão', 'error');
            }
        }

        // Search functionality
        function handleSearch(e) {
            const searchTerm = e.target.value.toLowerCase();
            const taskCards = document.querySelectorAll('.task-card');
            
            taskCards.forEach(card => {
                const title = card.querySelector('.task-title').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Notification system
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notification-text');
            
            notificationText.textContent = message;
            
            // Change color based on type
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
        taskModal.addEventListener('click', (e) => {
            if (e.target === taskModal) {
                closeTaskModal();
            }
        });

        commentsModal.addEventListener('click', (e) => {
            if (e.target === commentsModal) {
                closeCommentsModalFunc();
            }
        });

        // Set minimum date to today
        document.getElementById('task-date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
