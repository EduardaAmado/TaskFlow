# TaskFlow - Sistema de Gerenciamento de Tarefas

TaskFlow é um sistema completo de gerenciamento de tarefas desenvolvido em PHP com uma interface moderna e responsiva. O sistema permite aos usuários criar, organizar e acompanhar suas tarefas de forma eficiente.

## 🚀 Características

### Funcionalidades Principais
- **Autenticação de Usuários**: Sistema completo de login e registro
- **Gerenciamento de Tarefas**: Criar, editar, excluir e marcar tarefas como concluídas
- **Sistema de Prioridades**: Classificar tarefas por prioridade (Alta, Média, Baixa)
- **Filtros Inteligentes**: Visualizar tarefas por período (Hoje, Esta Semana, Importantes)
- **Sistema de Comentários**: Adicionar comentários às tarefas para melhor acompanhamento
- **Busca Avançada**: Pesquisar tarefas por título, descrição ou prioridade
- **Interface Responsiva**: Design moderno com efeito glass e gradientes

### Tecnologias Utilizadas
- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Frameworks CSS**: Tailwind CSS
- **Ícones**: Font Awesome
- **Fontes**: Google Fonts (Poppins)

## 📋 Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior / MariaDB 10.2+
- Servidor web (Apache/Nginx)
- Extensões PHP necessárias:
  - PDO
  - PDO_MySQL
  - mbstring
  - json

## 🛠️ Instalação

### 1. Clone o Repositório
```bash
git clone https://github.com/seu-usuario/taskflow.git
cd taskflow
```

### 2. Configuração do Banco de Dados

1. Crie um banco de dados MySQL:
```sql
CREATE DATABASE taskflow_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Configure as credenciais do banco em `app/config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'taskflow_db');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

3. Execute as migrações do banco de dados:
```bash
mysql -u seu_usuario -p taskflow_db < database/migrations.sql
```

### 3. Configuração do Servidor Web

#### Apache
Certifique-se de que o mod_rewrite está habilitado e configure o DocumentRoot para a pasta `public/`.

#### Nginx
Configure o servidor para servir arquivos da pasta `public/` e redirecionar requisições para `index.php`.

### 4. Permissões
```bash
chmod -R 755 .
chmod -R 777 storage/ # se houver pasta de storage
```

## 📁 Estrutura do Projeto

```
TaskFlow/
├── app/
│   ├── config/
│   │   └── database.php          # Configurações do banco de dados
│   ├── controllers/
│   │   ├── LoginController.php   # Controlador de autenticação
│   │   ├── RegisterController.php # Controlador de registro
│   │   └── TaskController.php    # Controlador de tarefas
│   ├── models/
│   │   ├── User.php             # Model de usuários
│   │   ├── Task.php             # Model de tarefas
│   │   └── Comment.php          # Model de comentários
│   └── views/
│       ├── dashboard_view.php   # Interface principal
│       ├── login_view.php       # Tela de login
│       ├── register_view.php    # Tela de registro
│       └── sidebar.php          # Barra lateral
├── database/
│   └── migrations.sql           # Scripts de criação do banco
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css        # Estilos customizados
│   │   └── js/
│   │       └── app.js           # JavaScript principal
│   ├── dashboard.php            # Página principal
│   ├── login.php               # Página de login
│   ├── register.php            # Página de registro
│   ├── add_task.php            # Endpoint para criar tarefas
│   └── index.php               # Página inicial
└── README.md
```

## 🎯 Como Usar

### 1. Registro de Usuário
1. Acesse a página de registro
2. Preencha os dados solicitados
3. Clique em "Criar Conta"

### 2. Login
1. Acesse a página de login
2. Digite seu email e senha
3. Clique em "Entrar"

### 3. Gerenciamento de Tarefas

#### Criar Nova Tarefa
1. No dashboard, clique em "Nova Tarefa"
2. Preencha o título, descrição, data de vencimento e prioridade
3. Clique em "Criar Tarefa"

#### Editar Tarefa
1. Clique no ícone de edição na tarefa desejada
2. Modifique os campos necessários
3. Clique em "Salvar Alterações"

#### Marcar como Concluída
1. Clique na checkbox ao lado do título da tarefa
2. A tarefa será marcada como concluída automaticamente

#### Adicionar Comentários
1. Clique em "Ver comentários" na tarefa
2. Digite seu comentário
3. Clique no botão de enviar

### 4. Filtros e Busca

#### Filtros Disponíveis
- **Todas as Tarefas**: Exibe todas as tarefas
- **Hoje**: Tarefas com vencimento hoje
- **Esta Semana**: Tarefas da semana atual
- **Importantes**: Tarefas com prioridade alta

#### Busca
Use a barra de pesquisa no topo para encontrar tarefas por título, descrição ou prioridade.

## 🔧 API Endpoints

### Tarefas
- `GET /app/controllers/TaskController.php?action=get` - Listar tarefas
- `POST /app/controllers/TaskController.php?action=create` - Criar tarefa
- `POST /app/controllers/TaskController.php?action=update` - Atualizar tarefa
- `POST /app/controllers/TaskController.php?action=delete` - Excluir tarefa
- `POST /app/controllers/TaskController.php?action=toggle` - Alternar status da tarefa

### Comentários
- `GET /app/controllers/TaskController.php?action=get_comments` - Listar comentários
- `POST /app/controllers/TaskController.php?action=add_comment` - Adicionar comentário
- `POST /app/controllers/TaskController.php?action=delete_comment` - Excluir comentário

### Busca
- `GET /app/controllers/TaskController.php?action=search` - Buscar tarefas

## 🎨 Personalização

### Cores e Temas
As cores principais podem ser modificadas no arquivo `public/assets/css/style.css`:

```css
:root {
    --primary-color: #6366f1;
    --secondary-color: #8b5cf6;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
}
```

### Layout
O layout utiliza CSS Grid e Flexbox para responsividade. Modifique as classes no arquivo CSS para ajustar o design.

## 🔒 Segurança

### Medidas Implementadas
- **Sanitização de Dados**: Todos os inputs são sanitizados
- **Prepared Statements**: Proteção contra SQL Injection
- **Hash de Senhas**: Senhas são criptografadas com password_hash()
- **Validação de Sessão**: Verificação de autenticação em todas as páginas protegidas
- **CSRF Protection**: Tokens CSRF em formulários críticos
- **XSS Protection**: Escape de dados de saída

### Recomendações Adicionais
- Use HTTPS em produção
- Configure headers de segurança
- Implemente rate limiting
- Mantenha o PHP e dependências atualizados

## 🐛 Solução de Problemas

### Problemas Comuns

#### Erro de Conexão com Banco
1. Verifique as credenciais em `app/config/database.php`
2. Certifique-se de que o MySQL está rodando
3. Verifique se o banco de dados existe

#### Páginas em Branco
1. Ative a exibição de erros no PHP
2. Verifique os logs do servidor web
3. Verifique permissões de arquivos

#### Problemas de CSS/JS
1. Verifique se os arquivos estão sendo servidos corretamente
2. Limpe o cache do navegador
3. Verifique o console do navegador para erros

## 📈 Melhorias Futuras

- [ ] Sistema de notificações
- [ ] Integração com calendário
- [ ] Anexos em tarefas
- [ ] Colaboração entre usuários
- [ ] API REST completa
- [ ] Aplicativo mobile
- [ ] Relatórios e estatísticas
- [ ] Integração com serviços externos

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 👥 Autores

- **Seu Nome** - *Desenvolvimento inicial* - [SeuGitHub](https://github.com/seu-usuario)

## 🙏 Agradecimentos

- Tailwind CSS pela framework CSS
- Font Awesome pelos ícones
- Google Fonts pelas fontes
- Comunidade PHP pelo suporte

---

**TaskFlow** - Organize suas tarefas, organize sua vida! 🚀
