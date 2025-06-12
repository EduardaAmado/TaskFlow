<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

require_once __DIR__ . '/../models/Statistics.php';
require_once __DIR__ . '/../models/Badge.php';

$statisticsModel = new Statistics();
$badgeModel = new Badge();

$userId = $_SESSION['user_id'];
$weeklyStats = $statisticsModel->getUserWeeklyStats($userId);
$monthlyStats = $statisticsModel->getUserMonthlyStats($userId);
$productivityRanking = $statisticsModel->getUserProductivityRanking();
$badgeStats = $badgeModel->getUserBadgeStats($userId);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Estatísticas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    </style>
</head>
<body>
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="glass-effect shadow-sm">
                <div class="flex items-center justify-between p-6">
                    <div>
                        <h2 class="text-2xl font-bold text-white">Estatísticas e Relatórios</h2>
                        <p class="text-white/70">Acompanhe sua produtividade e conquistas</p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <select id="period-filter" class="px-4 py-2 bg-white/20 border border-white/30 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-white/50">
                            <option value="week">Última Semana</option>
                            <option value="month">Último Mês</option>
                            <option value="year">Último Ano</option>
                        </select>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6">
                <div class="max-w-7xl mx-auto">
                    <!-- Grid de Estatísticas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Card de Tarefas Concluídas -->
                        <div class="glass-effect rounded-xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-white">Tarefas Concluídas</h3>
                                <i class="fas fa-check-circle text-2xl text-white/70"></i>
                            </div>
                            <div class="text-3xl font-bold text-white mb-2" id="completed-tasks-count">0</div>
                            <p class="text-white/70 text-sm">nos últimos 30 dias</p>
                        </div>

                        <!-- Card de Taxa de Conclusão -->
                        <div class="glass-effect rounded-xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-white">Taxa de Conclusão</h3>
                                <i class="fas fa-percentage text-2xl text-white/70"></i>
                            </div>
                            <div class="text-3xl font-bold text-white mb-2" id="completion-rate">0%</div>
                            <p class="text-white/70 text-sm">média mensal</p>
                        </div>

                        <!-- Card de Medalhas -->
                        <div class="glass-effect rounded-xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-white">Medalhas</h3>
                                <i class="fas fa-medal text-2xl text-white/70"></i>
                            </div>
                            <div class="text-3xl font-bold text-white mb-2"><?php echo $badgeStats['earned']; ?>/<?php echo $badgeStats['total']; ?></div>
                            <p class="text-white/70 text-sm">conquistadas</p>
                        </div>

                        <!-- Card de Posição no Ranking -->
                        <div class="glass-effect rounded-xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-white">Ranking</h3>
                                <i class="fas fa-trophy text-2xl text-white/70"></i>
                            </div>
                            <div class="text-3xl font-bold text-white mb-2" id="user-ranking">-</div>
                            <p class="text-white/70 text-sm">sua posição</p>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                        <!-- Gráfico de Produtividade -->
                        <div class="glass-effect rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Produtividade Semanal</h3>
                            <canvas id="productivity-chart" height="300"></canvas>
                        </div>

                        <!-- Gráfico de Distribuição por Prioridade -->
                        <div class="glass-effect rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Distribuição por Prioridade</h3>
                            <canvas id="priority-chart" height="300"></canvas>
                        </div>
                    </div>

                    <!-- Ranking e Medalhas -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Ranking de Usuários -->
                        <div class="glass-effect rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Top Colaboradores</h3>
                            <div class="space-y-4" id="users-ranking">
                                <?php foreach ($productivityRanking as $index => $user): ?>
                                    <div class="flex items-center justify-between p-3 bg-white/10 rounded-lg">
                                        <div class="flex items-center">
                                            <span class="text-2xl font-bold text-white/70 mr-4">#<?php echo $index + 1; ?></span>
                                            <div>
                                                <h4 class="font-medium text-white"><?php echo htmlspecialchars($user['username']); ?></h4>
                                                <p class="text-sm text-white/70"><?php echo $user['total_tasks_completed']; ?> tarefas concluídas</p>
                                            </div>
                                        </div>
                                        <div class="text-white/70">
                                            <i class="fas fa-star"></i>
                                            <?php echo number_format($user['avg_daily_tasks'], 1); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Medalhas Conquistadas -->
                        <div class="glass-effect rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Suas Medalhas</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" id="badges-grid">
                                <?php foreach ($badgeStats['badges'] as $badge): ?>
                                    <div class="flex flex-col items-center p-4 bg-white/10 rounded-lg">
                                        <i class="<?php echo $badge['icon']; ?> text-3xl text-white mb-2"></i>
                                        <h4 class="font-medium text-white text-center"><?php echo htmlspecialchars($badge['name']); ?></h4>
                                        <p class="text-xs text-white/70 text-center mt-1"><?php echo htmlspecialchars($badge['description']); ?></p>
                                        <span class="text-xs text-white/50 mt-2"><?php echo date('d/m/Y', strtotime($badge['earned_at'])); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Função para carregar dados das estatísticas
        async function loadStatistics(period = 'week') {
            try {
                const response = await fetch(`../app/controllers/TaskAdvancedController.php?action=get_statistics&period=${period}`);
                const data = await response.json();
                
                if (data.success) {
                    updateStatisticsDisplay(data.statistics);
                    updateCharts(data.statistics);
                }
            } catch (error) {
                console.error('Erro ao carregar estatísticas:', error);
            }
        }
        
        // Função para atualizar os displays de estatísticas
        function updateStatisticsDisplay(statistics) {
            document.getElementById('completed-tasks-count').textContent = statistics.overall.total_completed;
            
            const completionRate = (statistics.overall.total_completed / statistics.overall.total_tasks * 100) || 0;
            document.getElementById('completion-rate').textContent = `${completionRate.toFixed(1)}%`;
            
            // Encontrar posição do usuário no ranking
            const userRanking = statistics.ranking.findIndex(user => user.id === <?php echo $_SESSION['user_id']; ?>) + 1;
            document.getElementById('user-ranking').textContent = userRanking > 0 ? `#${userRanking}` : '-';
        }
        
        // Função para atualizar os gráficos
        function updateCharts(statistics) {
            // Gráfico de Produtividade
            const productivityCtx = document.getElementById('productivity-chart').getContext('2d');
            new Chart(productivityCtx, {
                type: 'line',
                data: {
                    labels: statistics.weekly.map(stat => stat.week_number),
                    datasets: [{
                        label: 'Tarefas Concluídas',
                        data: statistics.weekly.map(stat => stat.tasks_completed),
                        borderColor: 'rgba(255, 255, 255, 0.8)',
                        backgroundColor: 'rgba(255, 255, 255, 0.2)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: 'rgba(255, 255, 255, 0.8)'
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.8)'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.8)'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    }
                }
            });
            
            // Gráfico de Distribuição por Prioridade
            const priorityCtx = document.getElementById('priority-chart').getContext('2d');
            new Chart(priorityCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Alta', 'Média', 'Baixa'],
                    datasets: [{
                        data: [
                            statistics.priority_distribution.find(p => p.priority === 'high')?.count || 0,
                            statistics.priority_distribution.find(p => p.priority === 'medium')?.count || 0,
                            statistics.priority_distribution.find(p => p.priority === 'low')?.count || 0
                        ],
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(16, 185, 129, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: 'rgba(255, 255, 255, 0.8)'
                            }
                        }
                    }
                }
            });
        }
        
        // Event Listeners
        document.getElementById('period-filter').addEventListener('change', (e) => {
            loadStatistics(e.target.value);
        });
        
        // Carregar estatísticas iniciais
        loadStatistics();
    </script>
</body>
</html>
