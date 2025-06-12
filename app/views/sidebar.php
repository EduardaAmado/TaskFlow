<?php
if (!isset($_SESSION)) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

// Get current page for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

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
            <a href="../public/dashboard.php" 
               class="flex items-center px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors <?php echo $currentPage === 'dashboard' ? 'bg-white/20 text-white' : ''; ?>">
                <i class="fas fa-home mr-3"></i>
                Dashboard
            </a>
            
            <a href="../public/projects.php" 
               class="flex items-center px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors <?php echo $currentPage === 'projects' ? 'bg-white/20 text-white' : ''; ?>">
                <i class="fas fa-folder mr-3"></i>
                Projetos
            </a>
            
            <a href="../public/gantt.php" 
               class="flex items-center px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors <?php echo $currentPage === 'gantt' ? 'bg-white/20 text-white' : ''; ?>">
                <i class="fas fa-chart-gantt mr-3"></i>
                Cronograma Geral
            </a>
            
            <a href="../public/statistics.php" 
               class="flex items-center px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors <?php echo $currentPage === 'statistics' ? 'bg-white/20 text-white' : ''; ?>">
                <i class="fas fa-chart-bar mr-3"></i>
                Estatísticas
            </a>
            
            <a href="../public/preferences.php" 
               class="flex items-center px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors <?php echo $currentPage === 'preferences' ? 'bg-white/20 text-white' : ''; ?>">
                <i class="fas fa-cog mr-3"></i>
                Configurações
            </a>
            
            <div class="border-t border-white/20 my-4"></div>
            
            <a href="../public/dashboard.php?filter=today" 
               class="flex items-center px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors <?php echo isset($_GET['filter']) && $_GET['filter'] === 'today' ? 'bg-white/20 text-white' : ''; ?>">
                <i class="fas fa-calendar-day mr-3"></i>
                Hoje
            </a>
            
            <a href="../public/dashboard.php?filter=this_week" 
               class="flex items-center px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors <?php echo isset($_GET['filter']) && $_GET['filter'] === 'this_week' ? 'bg-white/20 text-white' : ''; ?>">
                <i class="fas fa-calendar-week mr-3"></i>
                Esta Semana
            </a>
            
            <a href="../public/dashboard.php?filter=important" 
               class="flex items-center px-4 py-3 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-colors <?php echo isset($_GET['filter']) && $_GET['filter'] === 'important' ? 'bg-white/20 text-white' : ''; ?>">
                <i class="fas fa-exclamation-circle mr-3"></i>
                Importantes
            </a>
        </nav>

        <div class="mt-8 pt-8 border-t border-white/20">
            <div class="px-4 py-2">
                <h3 class="text-sm font-medium text-white/70 uppercase tracking-wider mb-2">Estatísticas</h3>
                <?php
                require_once __DIR__ . '/../models/Task.php';
                $taskModel = new Task();
                $tasks = $taskModel->getAllUserTasks($_SESSION['user_id']);
                $completedTasks = array_filter($tasks, function($task) {
                    return $task['completed'];
                });
                $pendingTasks = array_filter($tasks, function($task) {
                    return !$task['completed'];
                });
                ?>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-white/60">Total de Tarefas</span>
                        <span class="text-white"><?php echo count($tasks); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-white/60">Concluídas</span>
                        <span class="text-green-400"><?php echo count($completedTasks); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-white/60">Pendentes</span>
                        <span class="text-yellow-400"><?php echo count($pendingTasks); ?></span>
                    </div>
                </div>
            </div>

            <a href="../public/login.php?logout=1" 
               class="flex items-center px-4 py-3 mt-4 text-white/80 hover:text-white hover:bg-red-500/20 rounded-lg transition-colors">
                <i class="fas fa-sign-out-alt mr-3"></i>
                Sair
            </a>
        </div>
    </div>
</div>
