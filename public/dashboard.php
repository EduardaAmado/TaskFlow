<?php
// Include the database configuration
require_once '../app/config/db.php';

// Function to get tasks for a user
function getTasks($userId) {
    global $pdo; // Assuming PDO is used for database interaction
    $stmt = $pdo->prepare("SELECT * FROM tb_tasks WHERE user_id = :user_id ORDER BY due_date");
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to add a new task
function addTask($userId, $description, $dueDate, $priority) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO tb_tasks (user_id, description, due_date, priority) VALUES (:user_id, :description, :due_date, :priority)");
    $stmt->execute([
        'user_id' => $userId,
        'description' => $description,
        'due_date' => $dueDate,
        'priority' => $priority
    ]);
}

// Function to mark a task as completed
function completeTask($taskId) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE tb_tasks SET completed = TRUE WHERE id = :id");
    $stmt->execute(['id' => $taskId]);
}

// Check if a task is marked as completed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_task_id'])) {
    completeTask($_POST['complete_task_id']);
}

// Check if a new task is added
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_description'])) {
    addTask($_POST['user_id'], $_POST['task_description'], $_POST['task_due_date'], $_POST['task_priority']);
}

// Assuming $userId is set when the user logs in
$userId = 1; // Example userId
$tasks = getTasks($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Task Management</title>
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
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
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto bg-gradient-to-br from-indigo-50 to-blue-50 p-4 md:p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Dashboard Header -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
                        <p class="text-gray-600 mt-1">Welcome back, John Paul!</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <button id="add-task-btn" class="gradient-btn text-white px-4 py-2 rounded-lg shadow-md flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            New Task
                        </button>
                    </div>
                </div>

                <!-- Tasks List -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">My Tasks</h3>
                    </div>
                    <div class="divide-y divide-gray-100" id="tasks-container">
                        <?php foreach ($tasks as $task): ?>
                            <div class="p-4 hover:bg-gray-50 task-card <?= 'priority-' . $task['priority'] . ($task['completed'] ? ' completed' : '') ?>">
                                <form method="POST" class="flex items-start">
                                    <!-- Checkbox to mark task as completed -->
                                    <input type="checkbox" name="complete_task_id" value="<?= $task['id'] ?>" <?= $task['completed'] ? 'checked' : '' ?> class="task-checkbox mt-1 h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center justify-between">
                                            <!-- Task description -->
                                            <h3 class="text-base font-medium text-gray-800"><?= $task['description'] ?></h3>
                                            <!-- Priority badge -->
                                            <span class="<?= $task['completed'] ? 'bg-gray-100 text-gray-700' : 'bg-' . (($task['priority'] === 'high') ? 'red' : (($task['priority'] === 'medium') ? 'yellow' : 'green')) . '-100 text-' . (($task['priority'] === 'high') ? 'red' : (($task['priority'] === 'medium') ? 'yellow' : 'green')) . '-700 text-xs px-2 py-1 rounded-full font-medium' ?>">
                                                <?= $task['completed'] ? 'Completed' : ucfirst($task['priority']) ?>
                                            </span>
                                        </div>
                                        <!-- Task due date -->
                                        <p class="mt-1 text-sm text-gray-600">Due date: <?= date('m/d/Y', strtotime($task['due_date'])) ?></p>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>

        <!-- Modal for adding new task -->
        <div id="task-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">New Task</h3>
                        <button id="close-modal" class="text-gray-400 hover:text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <form id="task-form" method="POST">
                        <!-- Hidden input to store user ID -->
                        <input type="hidden" name="user_id" value="<?= $userId ?>">
                        
                        <!-- Task description input -->
                        <div class="mb-4">
                            <label for="task-description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="task_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter task description"></textarea>
                        </div>
                        <!-- Task due date and priority inputs -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="task-date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" name="task_due_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label for="task-priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                <select name="task_priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <!-- Submit and cancel buttons -->
                        <div class="flex justify-end">
                            <button type="button" id="cancel-task" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg mr-2">Cancel</button>
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
        
        // Show the modal when 'New Task' button is clicked
        addTaskBtn.addEventListener('click', () => {
            taskModal.classList.remove('hidden');
        });

        // Hide the modal when the close button is clicked
        closeModal.addEventListener('click', () => {
            taskModal.classList.add('hidden');
        });

        // Hide the modal when cancel button is clicked
        cancelTask.addEventListener('click', () => {
            taskModal.classList.add('hidden');
        });

        // Close modal when clicking outside of it
        taskModal.addEventListener('click', (e) => {
            if (e.target === taskModal) {
                taskModal.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
