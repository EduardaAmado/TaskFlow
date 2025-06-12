-- Criação da tabela de projetos
CREATE TABLE IF NOT EXISTS tb_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('planning', 'active', 'completed', 'on_hold') NOT NULL DEFAULT 'planning',
    color VARCHAR(7) DEFAULT '#6366f1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tb_users(id) ON DELETE CASCADE
);

-- Adicionar colunas à tabela de tarefas existente
ALTER TABLE tb_tasks ADD COLUMN IF NOT EXISTS project_id INT NULL;
ALTER TABLE tb_tasks ADD COLUMN IF NOT EXISTS estimated_hours INT DEFAULT 0;
ALTER TABLE tb_tasks ADD COLUMN IF NOT EXISTS actual_hours INT DEFAULT 0;

-- Adicionar foreign key para project_id (se não existir)
ALTER TABLE tb_tasks ADD CONSTRAINT fk_tasks_project_id 
FOREIGN KEY (project_id) REFERENCES tb_projects(id) ON DELETE CASCADE;

-- Adicionar índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_projects_user_id ON tb_projects(user_id);
CREATE INDEX IF NOT EXISTS idx_projects_status ON tb_projects(status);
CREATE INDEX IF NOT EXISTS idx_projects_dates ON tb_projects(start_date, end_date);
CREATE INDEX IF NOT EXISTS idx_tasks_project_id ON tb_tasks(project_id);
