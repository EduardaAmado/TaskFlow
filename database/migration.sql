-- Criação da tabela de usuários
CREATE TABLE IF NOT EXISTS tb_users (
    id SERIAL PRIMARY KEY,
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
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES tb_users(id) ON DELETE CASCADE,
    description TEXT,
    due_date DATE,
    priority ENUM('low', 'medium', 'high') NOT NULL, -- Use 'priority_enum' se for PostgreSQL
    completed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Criação da tabela de comentários
CREATE TABLE IF NOT EXISTS tb_comments (
    id SERIAL PRIMARY KEY,
    task_id INTEGER REFERENCES tb_tasks(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES tb_users(id) ON DELETE CASCADE,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Criação da tabela de tokens
CREATE TABLE IF NOT EXISTS tb_tokens (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES tb_users(id) ON DELETE CASCADE,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL
);
