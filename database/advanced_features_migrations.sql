-- Advanced Features Migrations for TaskFlow

-- 1. Tarefas Recorrentes
ALTER TABLE tasks ADD COLUMN is_recurring BOOLEAN DEFAULT FALSE;
ALTER TABLE tasks ADD COLUMN recurrence_type ENUM('daily', 'weekly', 'monthly', 'yearly') NULL;
ALTER TABLE tasks ADD COLUMN recurrence_interval INT DEFAULT 1;
ALTER TABLE tasks ADD COLUMN recurrence_end_date DATE NULL;
ALTER TABLE tasks ADD COLUMN parent_recurring_task_id INT NULL;
ALTER TABLE tasks ADD FOREIGN KEY (parent_recurring_task_id) REFERENCES tasks(id) ON DELETE CASCADE;

-- 2. Upload de Arquivos
CREATE TABLE task_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. Dependências de Tarefas
CREATE TABLE task_dependencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    depends_on_task_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (depends_on_task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    UNIQUE KEY unique_dependency (task_id, depends_on_task_id)
);

-- 4. Sistema de Recompensas/Medalhas
CREATE TABLE badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(100) NOT NULL,
    criteria_type ENUM('tasks_completed', 'streak_days', 'projects_completed', 'comments_made') NOT NULL,
    criteria_value INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_badge (user_id, badge_id)
);

-- 5. Estatísticas de Produtividade
CREATE TABLE user_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    tasks_completed INT DEFAULT 0,
    tasks_created INT DEFAULT 0,
    comments_made INT DEFAULT 0,
    time_spent_minutes INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, date)
);

-- 6. Preferências do Usuário
CREATE TABLE user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    theme ENUM('light', 'dark') DEFAULT 'light',
    notifications_enabled BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    language VARCHAR(10) DEFAULT 'pt-BR',
    timezone VARCHAR(50) DEFAULT 'America/Sao_Paulo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_preferences (user_id)
);

-- 7. Log de Atividades
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type ENUM('task_created', 'task_completed', 'task_updated', 'task_deleted', 'comment_added', 'file_uploaded') NOT NULL,
    entity_type ENUM('task', 'project', 'comment', 'file') NOT NULL,
    entity_id INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Inserir badges padrão
INSERT INTO badges (name, description, icon, criteria_type, criteria_value) VALUES
('Primeiro Passo', 'Complete sua primeira tarefa', 'fas fa-star', 'tasks_completed', 1),
('Produtivo', 'Complete 10 tarefas', 'fas fa-trophy', 'tasks_completed', 10),
('Dedicado', 'Complete 50 tarefas', 'fas fa-medal', 'tasks_completed', 50),
('Mestre das Tarefas', 'Complete 100 tarefas', 'fas fa-crown', 'tasks_completed', 100),
('Sequência de 7 dias', 'Complete tarefas por 7 dias consecutivos', 'fas fa-fire', 'streak_days', 7),
('Sequência de 30 dias', 'Complete tarefas por 30 dias consecutivos', 'fas fa-calendar-check', 'streak_days', 30),
('Gerente de Projetos', 'Complete 5 projetos', 'fas fa-briefcase', 'projects_completed', 5),
('Comunicador', 'Faça 25 comentários', 'fas fa-comments', 'comments_made', 25);

-- Adicionar colunas para seleção múltipla
ALTER TABLE tasks ADD COLUMN selected_for_bulk BOOLEAN DEFAULT FALSE;

-- Índices para performance
CREATE INDEX idx_tasks_recurring ON tasks(is_recurring);
CREATE INDEX idx_tasks_due_date ON tasks(due_date);
CREATE INDEX idx_user_statistics_date ON user_statistics(date);
CREATE INDEX idx_activity_log_user_date ON activity_log(user_id, created_at);
