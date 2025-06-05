
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Gestão de Tarefas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9ff;
        }
        .task-card {
            transition: all 0.3s ease;
        }
        .task-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
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
            opacity: 0.6;
        }
        .completed h3 {
            text-decoration: line-through;
        }
        .gradient-btn {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }
        .gradient-btn:hover {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg hidden md:block">
            <div class="p-6">
                <h1 class="text-2xl font-bold text-indigo-600 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    TaskFlow
                </h1>
            </div>
            <nav class="mt-6">
                <div class="px-4">
                    <button class="w-full flex items-center px-4 py-3 bg-indigo-50 text-indigo-700 rounded-lg mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </button>
                    <button class="w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-indigo-600 rounded-lg mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Projetos
                    </button>
                    <button class="w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-indigo-600 rounded-lg mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendário
                    </button>
                    <button class="w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-indigo-600 rounded-lg mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Equipe
                    </button>
                    <button class="w-full flex items-center px-4 py-3 text-gray-600 hover:bg-gray-50 hover:text-indigo-600 rounded-lg mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Configurações
                    </button>
                </div>
            </nav>
            <div class="px-6 mt-8">
                <div class="bg-indigo-100 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-indigo-800">Precisa de ajuda?</h3>
                    <p class="text-xs text-indigo-600 mt-1">Acesse nossa central de suporte para tirar suas dúvidas.</p>
                    <button class="mt-3 text-xs font-medium text-white bg-indigo-600 py-2 px-3 rounded-md hover:bg-indigo-700">
                        Suporte
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center md:hidden">
                        <button id="menu-button" class="text-gray-500 hover:text-gray-600 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-xl font-bold text-indigo-600 ml-3 md:hidden">TaskFlow</h1>
                    </div>
                    <div class="relative w-64 hidden md:block">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="text" class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Pesquisar tarefas...">
                    </div>
                    <div class="flex items-center">
                        <button class="text-gray-500 hover:text-gray-600 focus:outline-none mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </button>
                        <div class="relative">
                            <button class="flex items-center focus:outline-none">
                                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-medium">
                                    JP
                                </div>
                                <span class="ml-2 text-sm font-medium text-gray-700 hidden md:block">João Paulo</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 ml-1 hidden md:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-gradient-to-br from-indigo-50 to-blue-50 p-4 md:p-6">
                <div class="max-w-7xl mx-auto">
                    <!-- Dashboard Header -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
                            <p class="text-gray-600 mt-1">Bem-vindo de volta, João Paulo!</p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <button id="add-task-btn" class="gradient-btn text-white px-4 py-2 rounded-lg shadow-md flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Nova Tarefa
                            </button>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="bg-indigo-100 p-3 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-500">Total de Tarefas</h3>
                                    <p class="text-2xl font-semibold text-gray-800">12</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="bg-green-100 p-3 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-500">Concluídas</h3>
                                    <p class="text-2xl font-semibold text-gray-800">5</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="bg-yellow-100 p-3 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-500">Em Progresso</h3>
                                    <p class="text-2xl font-semibold text-gray-800">4</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <div class="flex items-center">
                                <div class="bg-red-100 p-3 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-medium text-gray-500">Atrasadas</h3>
                                    <p class="text-2xl font-semibold text-gray-800">3</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Task Filters -->
                    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                        <div class="flex flex-wrap items-center justify-between">
                            <div class="flex space-x-2 mb-2 md:mb-0">
                                <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium">Todas</button>
                                <button class="px-4 py-2 bg-white text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium">Hoje</button>
                                <button class="px-4 py-2 bg-white text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium">Esta Semana</button>
                                <button class="px-4 py-2 bg-white text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium">Importantes</button>
                            </div>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-500 mr-2">Ordenar por:</span>
                                <select class="bg-gray-50 border border-gray-200 text-gray-700 py-2 px-3 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option>Data</option>
                                    <option>Prioridade</option>
                                    <option>Nome</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks List -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800">Minhas Tarefas</h3>
                        </div>
                        <div class="divide-y divide-gray-100" id="tasks-container">
                            <!-- Task 1 -->
                            <div class="p-4 hover:bg-gray-50 task-card priority-high">
                                <div class="flex items-start">
                                    <input type="checkbox" class="task-checkbox mt-1 h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-base font-medium text-gray-800">Finalizar apresentação do projeto</h3>
                                            <span class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded-full font-medium">Alta</span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-600">Preparar slides e documentação para a reunião com o cliente.</p>
                                        <div class="mt-2 flex items-center text-xs text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>Hoje, 14:00</span>
                                            <span class="mx-2">•</span>
                                            <span class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                </svg>
                                                3 comentários
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Task 2 -->
                            <div class="p-4 hover:bg-gray-50 task-card priority-medium">
                                <div class="flex items-start">
                                    <input type="checkbox" class="task-checkbox mt-1 h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-base font-medium text-gray-800">Revisar relatório mensal</h3>
                                            <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full font-medium">Média</span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-600">Verificar os números e métricas do relatório antes de enviar.</p>
                                        <div class="mt-2 flex items-center text-xs text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>Amanhã, 10:00</span>
                                            <span class="mx-2">•</span>
                                            <span class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                </svg>
                                                1 comentário
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Task 3 -->
                            <div class="p-4 hover:bg-gray-50 task-card priority-low">
                                <div class="flex items-start">
                                    <input type="checkbox" class="task-checkbox mt-1 h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-base font-medium text-gray-800">Agendar reunião com equipe</h3>
                                            <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">Baixa</span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-600">Organizar pauta e enviar convites para a reunião semanal.</p>
                                        <div class="mt-2 flex items-center text-xs text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>Quinta, 09:00</span>
                                            <span class="mx-2">•</span>
                                            <span class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                                </svg>
                                                0 comentários
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Task 4 (Completed) -->
                            <div class="p-4 hover:bg-gray-50 task-card completed">
                                <div class="flex items-start">
                                    <input type="checkbox" checked class="task-checkbox mt-1 h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-base font-medium text-gray-800">Responder e-mails pendentes</h3>
                                            <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full font-medium">Concluída</span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-600">Verificar caixa de entrada e responder mensagens importantes.</p>
                                        <div class="mt-2 flex items-center text-xs text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>Ontem, 16:30</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal for adding new task -->
    <div id="task-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Nova Tarefa</h3>
                    <button id="close-modal" class="text-gray-400 hover:text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form id="task-form">
                    <div class="mb-4">
                        <label for="task-title" class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                        <input type="text" id="task-title" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Digite o título da tarefa">
                    </div>
                    <div class="mb-4">
                        <label for="task-description" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea id="task-description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Digite uma descrição"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="task-date" class="block text-sm font-medium text-gray-700 mb-1">Data</label>
                            <input type="date" id="task-date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label for="task-priority" class="block text-sm font-medium text-gray-700 mb-1">Prioridade</label>
                            <select id="task-priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="low">Baixa</option>
                                <option value="medium">Média</option>
                                <option value="high">Alta</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" id="cancel-task" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg mr-2">Cancelar</button>
                        <button type="submit" class="px-4 py-2 text-white gradient-btn rounded-lg">Adicionar Tarefa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const menuButton = document.getElementById('menu-button');
        const sidebar = document.querySelector('.w-64');
        
        menuButton.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('absolute');
            sidebar.classList.toggle('z-20');
            sidebar.classList.toggle('h-screen');
        });

        // Task modal functionality
        const addTaskBtn = document.getElementById('add-task-btn');
        const taskModal = document.getElementById('task-modal');
        const closeModal = document.getElementById('close-modal');
        const cancelTask = document.getElementById('cancel-task');
        const taskForm = document.getElementById('task-form');
        const tasksContainer = document.getElementById('tasks-container');

        addTaskBtn.addEventListener('click', () => {
            taskModal.classList.remove('hidden');
        });

        closeModal.addEventListener('click', () => {
            taskModal.classList.add('hidden');
        });

        cancelTask.addEventListener('click', () => {
            taskModal.classList.add('hidden');
        });

        // Close modal when clicking outside
        taskModal.addEventListener('click', (e) => {
            if (e.target === taskModal) {
                taskModal.classList.add('hidden');
            }
        });

        // Handle task form submission
        taskForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const title = document.getElementById('task-title').value;
            const description = document.getElementById('task-description').value;
            const date = document.getElementById('task-date').value;
            const priority = document.getElementById('task-priority').value;
            
            if (!title) return;
            
            // Create new task element
            const taskElement = document.createElement('div');
            taskElement.className = `p-4 hover:bg-gray-50 task-card priority-${priority}`;
            
            let priorityLabel = 'Baixa';
            let priorityClass = 'bg-green-100 text-green-700';
            
            if (priority === 'medium') {
                priorityLabel = 'Média';
                priorityClass = 'bg-yellow-100 text-yellow-700';
            } else if (priority === 'high') {
                priorityLabel = 'Alta';
                priorityClass = 'bg-red-100 text-red-700';
            }
            
            const dateObj = new Date(date);
            const formattedDate = dateObj.toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric' });
            
            taskElement.innerHTML = `
                <div class="flex items-start">
                    <input type="checkbox" class="task-checkbox mt-1 h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <div class="ml-3 flex-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-medium text-gray-800">${title}</h3>
                            <span class="${priorityClass} text-xs px-2 py-1 rounded-full font-medium">${priorityLabel}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600">${description}</p>
                        <div class="mt-2 flex items-center text-xs text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>${formattedDate}</span>
                            <span class="mx-2">•</span>
                            <span class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                                0 comentários
                            </span>
                        </div>
                    </div>
                </div>
            `;
            
            // Add the new task to the top of the list
            tasksContainer.insertBefore(taskElement, tasksContainer.firstChild);
            
            // Reset form and close modal
            taskForm.reset();
            taskModal.classList.add('hidden');
            
            // Add event listener to the new checkbox
            const newCheckbox = taskElement.querySelector('.task-checkbox');
            newCheckbox.addEventListener('change', handleTaskCompletion);
        });

        // Handle task completion
        const checkboxes = document.querySelectorAll('.task-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', handleTaskCompletion);
        });

        function handleTaskCompletion(e) {
            const taskCard = e.target.closest('.task-card');
            if (e.target.checked) {
                taskCard.classList.add('completed');
                const priorityBadge = taskCard.querySelector('.rounded-full');
                priorityBadge.className = 'bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded-full font-medium';
                priorityBadge.textContent = 'Concluída';
            } else {
                taskCard.classList.remove('completed');
                // Restore original priority badge
                const priorityClass = taskCard.classList.contains('priority-high') ? 'bg-red-100 text-red-700' :
                                    taskCard.classList.contains('priority-medium') ? 'bg-yellow-100 text-yellow-700' :
                                    'bg-green-100 text-green-700';
                const priorityLabel = taskCard.classList.contains('priority-high') ? 'Alta' :
                                    taskCard.classList.contains('priority-medium') ? 'Média' : 'Baixa';
                const priorityBadge = taskCard.querySelector('.rounded-full');
                priorityBadge.className = `${priorityClass} text-xs px-2 py-1 rounded-full font-medium`;
                priorityBadge.textContent = priorityLabel;
            }
        }
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9495bf27c7fa368c',t:'MTc0ODg1NDY4My4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
