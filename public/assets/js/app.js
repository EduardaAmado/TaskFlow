// TaskFlow - Main JavaScript File

class TaskManager {
    constructor() {
        this.currentTaskId = null;
        this.isEditMode = false;
        this.bindEvents();
    }

    bindEvents() {
        const addTaskBtn = document.getElementById('add-task-btn');
        const taskModal = document.getElementById('task-modal');
        const closeModal = document.getElementById('close-modal');
        const cancelTask = document.getElementById('cancel-task');
        const taskForm = document.getElementById('task-form');

        if (addTaskBtn) addTaskBtn.addEventListener('click', () => this.openAddTaskModal());
        if (closeModal) closeModal.addEventListener('click', () => this.closeTaskModal());
        if (cancelTask) cancelTask.addEventListener('click', () => this.closeTaskModal());
        if (taskForm) taskForm.addEventListener('submit', (e) => this.handleTaskSubmit(e));

        // Task checkboxes and buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('task-checkbox')) {
                this.toggleTaskComplete(e.target.dataset.taskId);
            }
            
            if (e.target.classList.contains('edit-task-btn') || e.target.parentElement.classList.contains('edit-task-btn')) {
                const taskId = e.target.dataset.taskId || e.target.parentElement.dataset.taskId;
                this.openEditTaskModal(taskId);
            }
            
            if (e.target.classList.contains('delete-task-btn') || e.target.parentElement.classList.contains('delete-task-btn')) {
                const taskId = e.target.dataset.taskId || e.target.parentElement.dataset.taskId;
                this.deleteTask(taskId);
            }
        });

        // Close modal on outside click
        if (taskModal) {
            taskModal.addEventListener('click', (e) => {
                if (e.target === taskModal) {
                    this.closeTaskModal();
                }
            });
        }
    }

    openAddTaskModal() {
        const taskModal = document.getElementById('task-modal');
        this.isEditMode = false;
        this.currentTaskId = null;
        document.getElementById('modal-title').textContent = 'Nova Tarefa';
        document.getElementById('submit-text').textContent = 'Criar Tarefa';
        document.getElementById('task-form').reset();
        document.getElementById('task-id').value = '';
        taskModal.classList.remove('hidden');
    }

    openEditTaskModal(taskId) {
        const taskModal = document.getElementById('task-modal');
        this.isEditMode = true;
        this.currentTaskId = taskId;
        document.getElementById('modal-title').textContent = 'Editar Tarefa';
        document.getElementById('submit-text').textContent = 'Salvar Alterações';
        
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

    closeTaskModal() {
        const taskModal = document.getElementById('task-modal');
        taskModal.classList.add('hidden');
        document.getElementById('task-form').reset();
        this.isEditMode = false;
        this.currentTaskId = null;
    }

    async handleTaskSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const url = this.isEditMode ? '../app/controllers/TaskController.php?action=update' : '../public/add_task.php';
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                taskFlow.showNotification(this.isEditMode ? 'Tarefa atualizada com sucesso!' : 'Tarefa criada com sucesso!', 'success');
                this.closeTaskModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                taskFlow.showNotification('Erro ao salvar tarefa', 'error');
            }
        } catch (error) {
            taskFlow.showNotification('Erro de conexão', 'error');
        }
    }

    async toggleTaskComplete(taskId) {
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
                taskFlow.showNotification('Status da tarefa atualizado!', 'success');
            } else {
                taskFlow.showNotification('Erro ao atualizar tarefa', 'error');
            }
        } catch (error) {
            taskFlow.showNotification('Erro de conexão', 'error');
        }
    }

    async deleteTask(taskId) {
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
                taskFlow.showNotification('Tarefa excluída com sucesso!', 'success');
            } else {
                taskFlow.showNotification('Erro ao excluir tarefa', 'error');
            }
        } catch (error) {
            taskFlow.showNotification('Erro de conexão', 'error');
        }
    }
}

class CommentManager {
    constructor() {
        this.currentTaskId = null;
        this.bindEvents();
    }

    bindEvents() {
        const commentsModal = document.getElementById('comments-modal');
        const closeCommentsModal = document.getElementById('close-comments-modal');
        const commentForm = document.getElementById('comment-form');

        if (closeCommentsModal) {
            closeCommentsModal.addEventListener('click', () => this.closeCommentsModal());
        }
        
        if (commentForm) {
            commentForm.addEventListener('submit', (e) => this.handleCommentSubmit(e));
        }

        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('view-comments-btn')) {
                const taskId = e.target.dataset.taskId;
                this.openCommentsModal(taskId);
            }
        });

        if (commentsModal) {
            commentsModal.addEventListener('click', (e) => {
                if (e.target === commentsModal) {
                    this.closeCommentsModal();
                }
            });
        }
    }

    openCommentsModal(taskId) {
        const commentsModal = document.getElementById('comments-modal');
        this.currentTaskId = taskId;
        document.getElementById('comment-task-id').value = taskId;
        this.loadComments(taskId);
        commentsModal.classList.remove('hidden');
    }

    closeCommentsModal() {
        const commentsModal = document.getElementById('comments-modal');
        commentsModal.classList.add('hidden');
        this.currentTaskId = null;
    }

    async loadComments(taskId) {
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
            taskFlow.showNotification('Erro ao carregar comentários', 'error');
        }
    }

    async handleCommentSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        
        try {
            const response = await fetch('../app/controllers/TaskController.php?action=add_comment', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                document.getElementById('comment-input').value = '';
                this.loadComments(this.currentTaskId);
                
                const taskCard = document.querySelector(`[data-task-id="${this.currentTaskId}"]`);
                const commentCount = taskCard.querySelector('.comment-count');
                commentCount.textContent = parseInt(commentCount.textContent) + 1;
                
                taskFlow.showNotification('Comentário adicionado!', 'success');
            } else {
                taskFlow.showNotification('Erro ao adicionar comentário', 'error');
            }
        } catch (error) {
            taskFlow.showNotification('Erro de conexão', 'error');
        }
    }
}

class SearchManager {
    constructor() {
        this.bindEvents();
    }

    bindEvents() {
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => this.handleSearch(e));
        }
    }

    handleSearch(e) {
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
}

class TaskFlow {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeComponents();
        
        // Initialize feature managers
        this.taskManager = new TaskManager();
        this.commentManager = new CommentManager();
        this.searchManager = new SearchManager();
    }

    bindEvents() {
        // Global event listeners
        document.addEventListener('DOMContentLoaded', () => {
            this.showPageMessages();
            this.initializeModals();
            this.initializeNotifications();
        });

        // Form validation
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('validate-form')) {
                this.validateForm(e);
            }
        });

        // Close modals on outside click
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal')) {
                this.closeModal(e.target);
            }
        });

        // Close notifications
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('notification-close')) {
                this.closeNotification(e.target.closest('.notification'));
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Escape key closes modals
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal.show');
                if (openModal) {
                    this.closeModal(openModal);
                }
            }

            // Ctrl/Cmd + K for search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('#search-input, .search-input');
                if (searchInput) {
                    searchInput.focus();
                }
            }
        });
    }

    initializeComponents() {
        this.initializeTooltips();
        this.initializeDropdowns();
        
        // Set minimum date to today for date inputs
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            input.min = new Date().toISOString().split('T')[0];
        });
    }

    // Modal Management
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal(modal) {
        if (typeof modal === 'string') {
            modal = document.getElementById(modal);
        }
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    initializeModals() {
        document.querySelectorAll('[data-modal-close]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const modal = btn.closest('.modal');
                this.closeModal(modal);
            });
        });

        document.querySelectorAll('[data-modal-open]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const modalId = btn.getAttribute('data-modal-open');
                this.openModal(modalId);
            });
        });
    }

    // Notification System
    showNotification(message, type = 'success', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => notification.classList.add('show'), 100);

        if (duration > 0) {
            setTimeout(() => this.closeNotification(notification), duration);
        }

        return notification;
    }

    closeNotification(notification) {
        if (notification) {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }

    initializeNotifications() {
        document.querySelectorAll('.notification').forEach(notification => {
            setTimeout(() => this.closeNotification(notification), 3000);
        });
    }

    // Form Validation
    validateForm(event) {
        const form = event.target;
        let isValid = true;

        form.querySelectorAll('.error-message').forEach(error => error.remove());
        form.querySelectorAll('.form-control').forEach(field => field.classList.remove('error'));

        // Required fields
        form.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'Este campo é obrigatório');
                isValid = false;
            }
        });

        // Email validation
        form.querySelectorAll('input[type="email"]').forEach(field => {
            if (field.value && !this.isValidEmail(field.value)) {
                this.showFieldError(field, 'Email inválido');
                isValid = false;
            }
        });

        // Password validation
        const password = form.querySelector('input[name="password"]');
        const confirmPassword = form.querySelector('input[name="confirm_password"]');
        
        if (password && confirmPassword && password.value !== confirmPassword.value) {
            this.showFieldError(confirmPassword, 'As senhas não coincidem');
            isValid = false;
        }

        if (password && password.value && password.value.length < 6) {
            this.showFieldError(password, 'A senha deve ter pelo menos 6 caracteres');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
            this.showNotification('Por favor, corrija os erros no formulário', 'error');
        }

        return isValid;
    }

    showFieldError(field, message) {
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Page Messages
    showPageMessages() {
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        const message = urlParams.get('message');
        const success = urlParams.get('success');

        if (error) {
            this.showNotification(decodeURIComponent(error), 'error');
        }

        if (message) {
            let messageText = decodeURIComponent(message);
            let messageType = 'success';

            switch (message) {
                case 'login_success':
                    messageText = 'Login realizado com sucesso!';
                    break;
                case 'logout_success':
                    messageText = 'Logout realizado com sucesso!';
                    break;
                case 'welcome':
                    messageText = 'Bem-vindo ao TaskFlow!';
                    break;
                case 'invalid_credentials':
                    messageText = 'Email ou senha inválidos';
                    messageType = 'error';
                    break;
            }

            this.showNotification(messageText, messageType);
        }

        if (success) {
            this.showNotification('Operação realizada com sucesso!', 'success');
        }

        // Clean URL
        if (error || message || success) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }

    // Utility Functions
    initializeTooltips() {
        document.querySelectorAll('[data-tooltip]').forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = element.getAttribute('data-tooltip');
                document.body.appendChild(tooltip);

                const rect = element.getBoundingClientRect();
                tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
                tooltip.classList.add('show');

                element._tooltip = tooltip;
            });

            element.addEventListener('mouseleave', (e) => {
                if (element._tooltip) {
                    element._tooltip.remove();
                    element._tooltip = null;
                }
            });
        });
    }

    initializeDropdowns() {
        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const dropdown = toggle.nextElementSibling;
                if (dropdown && dropdown.classList.contains('dropdown-menu')) {
                    dropdown.classList.toggle('show');
                }
            });
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    }

    // AJAX Helper
    async makeRequest(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        const config = { ...defaultOptions, ...options };

        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const contentType = response.headers.get('content-type');
            return contentType && contentType.includes('application/json') ? 
                   await response.json() : await response.text();
        } catch (error) {
            console.error('Request failed:', error);
            this.showNotification('Erro de conexão. Tente novamente.', 'error');
            throw error;
        }
    }

    // Loading States
    showLoading(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        if (element) {
            element.classList.add('loading');
            element.disabled = true;
        }
    }

    hideLoading(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        if (element) {
            element.classList.remove('loading');
            element.disabled = false;
        }
    }

    // Date Utilities
    formatDate(date, format = 'dd/mm/yyyy') {
        if (!(date instanceof Date)) {
            date = new Date(date);
        }

        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();

        switch (format) {
            case 'dd/mm/yyyy':
                return `${day}/${month}/${year}`;
            case 'yyyy-mm-dd':
                return `${year}-${month}-${day}`;
            case 'mm/dd/yyyy':
                return `${month}/${day}/${year}`;
            default:
                return date.toLocaleDateString('pt-BR');
        }
    }

    isToday(date) {
        const today = new Date();
        const checkDate = new Date(date);
        return checkDate.toDateString() === today.toDateString();
    }

    isOverdue(date) {
        const today = new Date();
        const checkDate = new Date(date);
        today.setHours(0, 0, 0, 0);
        checkDate.setHours(0, 0, 0, 0);
        return checkDate < today;
    }

    // Local Storage Helpers
    setStorage(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (error) {
            console.error('Error saving to localStorage:', error);
        }
    }

    getStorage(key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (error) {
            console.error('Error reading from localStorage:', error);
            return defaultValue;
        }
    }

    removeStorage(key) {
        try {
            localStorage.removeItem(key);
        } catch (error) {
            console.error('Error removing from localStorage:', error);
        }
    }
}

// Initialize TaskFlow
const taskFlow = new TaskFlow();

// Export for use in other scripts
window.TaskFlow = taskFlow;

// Service Worker Registration (for PWA features)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                console.log('SW registered: ', registration);
            })
            .catch((registrationError) => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}
