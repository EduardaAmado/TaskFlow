-- Criação da tabela de usuários
CREATE TABLE IF NOT EXISTS tb_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Criação do tipo ENUM (caso esteja usando PostgreSQL)
-- CREATE TYPE priority_enum AS ENUM ('low', 'medium', 'high');

-- Criação da tabela de tarefas
CREATE TABLE IF NOT EXISTS tb_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date DATE,
    start_date DATE,
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tb_users(id) ON DELETE CASCADE
);

-- Criação da tabela de comentários
CREATE TABLE IF NOT EXISTS tb_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tb_tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES tb_users(id) ON DELETE CASCADE
);

-- Criação da tabela de tokens
CREATE TABLE IF NOT EXISTS tb_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES tb_users(id) ON DELETE CASCADE
);

-- Adicionar índices para melhor performance
CREATE INDEX idx_tasks_user_id ON tb_tasks(user_id);
CREATE INDEX idx_tasks_due_date ON tb_tasks(due_date);
CREATE INDEX idx_tasks_priority ON tb_tasks(priority);
CREATE INDEX idx_comments_task_id ON tb_comments(task_id);
CREATE INDEX idx_tokens_user_id ON tb_tokens(user_id);
