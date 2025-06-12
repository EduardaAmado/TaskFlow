<?php
// Include the database connection file using the base path
require_once __DIR__ . '/../config/database.php';

// Now you can use the getDatabaseConnection() function
$pdo = getDatabaseConnection();

// Fetch tasks or perform other operations
// ...
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Task Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
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
        <?php include '../app/views/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between p-4">
                    <h1 class="text-xl font-bold text-indigo-600">TaskFlow</h1>
                    <button id="add-task-btn"
                        class="gradient-btn text-white px-4 py-2 rounded-lg shadow-md flex items-center">
                        New Task
                    </button>
                </div>
            </header>
            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-gradient-to-br from-indigo-50 to-blue-50 p-4 md:p-6">
                <div class="max-w-7xl mx-auto">
                    <!-- Task Filters -->
                    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                        <div class="flex flex-wrap items-center justify-between">
                            <div class="flex space-x-2 mb-2 md:mb-0">
                                <form id="task-form" action="add_task.php" method="POST">
                                    <button type="submit" name="filter" value="all"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium">All</button>
                                    <button type="submit" name="filter" value="today"
                                        class="px-4 py-2 bg-white text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium">Today</button>
                                    <button type="submit" name="filter" value="this_week"
                                        class="px-4 py-2 bg-white text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium">This
                                        Week</button>
                                    <button type="submit" name="filter" value="important"
                                        class="px-4 py-2 bg-white text-gray-600 hover:bg-gray-50 rounded-lg text-sm font-medium">Important</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks List -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-4 border-b border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800">My Tasks</h3>
                        </div>
                        <div class="divide-y divide-gray-100" id="tasks-container">
                            <?php
                            // Include the database connection file
                            // Fetch tasks from the database
                            $stmt = $pdo->query("SELECT * FROM tb_tasks ORDER BY due_date");
                            while ($task = $stmt->fetch()) {
                                $priorityClass = "priority-" . $task['priority'];
                                $priorityLabel = ucfirst($task['priority']);
                                $priorityColor = $task['priority'] === 'high' ? 'red' : ($task['priority'] === 'medium' ? 'yellow' : 'green');
                                echo "
                                <div class='p-4 hover:bg-gray-50 task-card $priorityClass'>
                                    <div class='flex items-start'>
                                        <input type='checkbox' class='task-checkbox mt-1 h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500'>
                                        <div class='ml-3 flex-1'>
                                            <div class='flex items-center justify-between'>
                                                <h3 class='text-base font-medium text-gray-800'>{$task['title']}</h3>
                                                <span class='bg-{$priorityColor}-100 text-{$priorityColor}-700 text-xs px-2 py-1 rounded-full font-medium'>{$priorityLabel}</span>
                                            </div>
                                            <p class='mt-1 text-sm text-gray-600'>{$task['description']}</p>
                                            <div class='mt-2 flex items-center text-xs text-gray-500'>
                                                <span>{$task['due_date']}</span>
                                                <span class='mx-2'>•</span>
                                                <span class='flex items-center'>
                                                    <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4 mr-1' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                                        <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z' />
                                                    </svg>
                                                    0 comments
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                ";
                            }
                            ?>
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
                <h3 class="text-lg font-semibold text-gray-800">New Task</h3>
                <button id="close-modal" class="text-gray-400 hover:text-gray-500">X</button>
            </div>
            <div class="p-6">
                <form id="new-task-form" action="add_task.php" method="POST">
                    <div class="mb-4">
                        <label for="task-title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text" id="task-title" name="task_title"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter task title" required>
                    </div>
                    <div class="mb-4">
                        <label for="task-description"
                            class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="task-description" name="task_description" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            placeholder="Enter a description" required></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="task-date" class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                            <input type="date" id="task-date" name="task_date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required>
                        </div>
                        <div>
                            <label for="task-priority"
                                class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <select id="task-priority" name="task_priority"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="button" id="cancel-task"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg mr-2">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-white gradient-btn rounded-lg">Add Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>

   <script>
    // Task modal functionality
    const addTaskBtn = document.getElementById('add-task-btn');
    const taskModal = document.getElementById('task-modal');
    const closeModal = document.getElementById('close-modal');
    const cancelTask = document.getElementById('cancel-task');

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
</script>

</body>

</html>