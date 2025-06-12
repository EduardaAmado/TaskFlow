// TaskFlow - Main JavaScript File

class TaskFlow {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeComponents();
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
    }

    initializeComponents() {
        // Initialize tooltips, dropdowns, etc.
        this.initializeTooltips();
        this.initializeDropdowns();
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
        // Close modal buttons
        document.querySelectorAll('[data-modal-close]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const modal = btn.closest('.modal');
                this.closeModal(modal);
            });
        });

        // Open modal buttons
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

        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        // Auto hide
        if (duration > 0) {
            setTimeout(() => {
                this.closeNotification(notification);
            }, duration);
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
        // Auto-close existing notifications
        document.querySelectorAll('.notification').forEach(notification => {
            setTimeout(() => {
                this.closeNotification(notification);
            }, 3000);
        });
    }

    // Form Validation
    validateForm(event) {
        const form = event.target;
        let isValid = true;
        const errors = [];

        // Clear previous errors
        form.querySelectorAll('.error-message').forEach(error => {
            error.remove();
        });

        form.querySelectorAll('.form-control').forEach(field => {
            field.classList.remove('error');
        });

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

        // Password confirmation
        const password = form.querySelector('input[name="password"]');
        const confirmPassword = form.querySelector('input[name="confirm_password"]');
        
        if (password && confirmPassword && password.value !== confirmPassword.value) {
            this.showFieldError(confirmPassword, 'As senhas não coincidem');
            isValid = false;
        }

        // Password strength
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
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
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

            // Handle specific message types
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
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
        }
    }

    // Utility Functions
    initializeTooltips() {
        // Simple tooltip implementation
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

        // Close dropdowns when clicking outside
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
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            } else {
                return await response.text();
            }
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

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    // Escape key closes modals
    if (e.key === 'Escape') {
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            taskFlow.closeModal(openModal);
        }
    }

    // Ctrl/Cmd + K for search (if search exists)
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('#search-input, .search-input');
        if (searchInput) {
            searchInput.focus();
        }
    }
});

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
