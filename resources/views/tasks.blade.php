<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; font-src cdnjs.cloudflare.com">
    <title>Task Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .task-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .status-badge {
            padding: 0.35em 0.65em;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-in-progress {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }
        .task-item {
            transition: all 0.3s ease;
            border: 1px solid #dee2e6;
            margin-bottom: 0.5rem;
            border-radius: 8px;
        }
        .task-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn-action {
            transition: all 0.2s ease;
        }
        .btn-action:hover {
            transform: scale(1.05);
        }
        .app-header {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .status-select {
            min-width: 140px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="app-header">
        <div class="container">
            <h1 class="text-center mb-0">Task Management System</h1>
            <p class="text-center mb-0 mt-2 text-light">Organize and track your tasks efficiently</p>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="task-card">
                    <!-- Create a new task form -->
                    <form id="create-task-form" class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" id="task-name" placeholder="Enter a new task" required minlength="3" maxlength="100">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fa fa-plus me-2"></i>Add Task
                            </button>
                        </div>
                    </form>

                    <!-- Task list -->
                    <div id="task-list">
                        <!-- Tasks will be dynamically added here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script>
    // Utility function for showing notifications
    function showNotification(options) {
        return Swal.fire({
            icon: options.icon || 'success',
            title: options.title,
            text: options.text,
            toast: options.toast || false,
            position: options.position || 'top-end',
            showConfirmButton: options.showConfirmButton || false,
            timer: options.timer || 3000,
            timerProgressBar: true,
            showClass: {
                popup: 'animate__animated animate__fadeIn'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOut'
            }
        });
    }

    // API request utility
    async function apiRequest(url, options = {}) {
        try {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                window.location.href = '/login';
                return;
            }

            const csrfToken = await fetchCsrfToken();
            const response = await fetch(url, {
                ...options,
                headers: {
                    ...options.headers,
                    'Authorization': `Bearer ${token}`,
                    'X-CSRF-TOKEN': csrfToken,
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            showNotification({
                icon: 'error',
                title: 'Error',
                text: error.message,
                position: 'center'
            });
            throw error;
        }
    }

    // Fetch CSRF token
    async function fetchCsrfToken() {
        try {
            const response = await fetch('/csrf-token');
            const data = await response.json();
            return data.csrfToken;
        } catch (error) {
            showNotification({
                icon: 'error',
                title: 'Error',
                text: 'Failed to fetch CSRF token',
                position: 'center'
            });
            throw error;
        }
    }

    // Get status badge class
    function getStatusBadgeClass(status) {
        const statusClasses = {
            'pending': 'status-pending',
            'in-progress': 'status-in-progress',
            'completed': 'status-completed'
        };
        return statusClasses[status] || 'status-pending';
    }

    // Fetch and display tasks
    async function fetchTasks() {
        try {
            const tasks = await apiRequest('/api/tasks');
            const taskList = document.getElementById('task-list');
            taskList.innerHTML = '';

            if (tasks.length === 0) {
                taskList.innerHTML = `
                    <div class="text-center text-muted my-5">
                        <i class="fa fa-tasks fa-3x mb-3"></i>
                        <p>No tasks yet. Create your first task above!</p>
                    </div>
                `;
                return;
            }

            tasks.forEach(task => {
                const listItem = document.createElement('div');
                listItem.className = 'task-item p-3 mb-2';
                
                // Sanitize task title
                const sanitizedTitle = document.createElement('div');
                sanitizedTitle.textContent = task.title;

                listItem.innerHTML = `
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3">
        <!-- Task Name and Status -->
        <div class="d-flex flex-column flex-md-row align-items-start">
            <span class="task-title h5 mb-0">${sanitizedTitle.innerHTML}</span>
            <span class="status-badge ms-2 ${getStatusBadgeClass(task.status)}">${task.status}</span>
        </div>
        
        <!-- Task Actions: Dropdown and Delete Button -->
        <div class="d-flex align-items-center mt-2 mt-md-0">
            <select class="form-select form-select-sm status-select me-2" data-task-id="${task.id}">
                <option value="pending" ${task.status === 'pending' ? 'selected' : ''}>Pending</option>
                <option value="in-progress" ${task.status === 'in-progress' ? 'selected' : ''}>In Progress</option>
                <option value="completed" ${task.status === 'completed' ? 'selected' : ''}>Completed</option>
            </select>
            <button class="btn btn-danger btn-sm btn-action delete-task" data-task-id="${task.id}">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </div>
`;

                taskList.appendChild(listItem);
            });

            // Add event listeners
            document.querySelectorAll('.status-select').forEach(select => {
                select.addEventListener('change', updateTaskStatus);
            });

            document.querySelectorAll('.delete-task').forEach(button => {
                button.addEventListener('click', deleteTask);
            });
        } catch (error) {
            console.error('Error fetching tasks:', error);
        }
    }

    // Update task status
    async function updateTaskStatus(event) {
        const taskId = event.target.getAttribute('data-task-id');
        const newStatus = event.target.value;

        try {
            await apiRequest(`/api/tasks/${taskId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ status: newStatus }),
            });

            showNotification({
                title: 'Success',
                text: 'Task status updated successfully!',
                toast: true
            });
            await fetchTasks();
        } catch (error) {
            console.error('Error updating task status:', error);
        }
    }

    // Delete a task
    async function deleteTask(event) {
        const taskId = event.target.closest('.delete-task').getAttribute('data-task-id');
        
        const result = await Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        });

        if (result.isConfirmed) {
            try {
                await apiRequest(`/api/tasks/${taskId}`, {
                    method: 'DELETE',
                });

                showNotification({
                    title: 'Deleted!',
                    text: 'Task has been deleted.',
                    toast: true
                });
                await fetchTasks();
            } catch (error) {
                console.error('Error deleting task:', error);
            }
        }
    }

    // Handle task creation
    document.getElementById('create-task-form').addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const taskNameInput = document.getElementById('task-name');
        const taskName = taskNameInput.value.trim();

        if (taskName.length < 3) {
            showNotification({
                icon: 'warning',
                title: 'Invalid Input',
                text: 'Task name must be at least 3 characters long',
                position: 'center'
            });
            return;
        }

        try {
            await apiRequest('/api/tasks', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ title: taskName, status: 'pending' }),
            });

            taskNameInput.value = '';
            showNotification({
                title: 'Success',
                text: 'Task created successfully!',
                toast: true
            });
            await fetchTasks();
        } catch (error) {
            console.error('Error creating task:', error);
        }
    });

    // Initial fetch of tasks
    fetchTasks();
    </script>
</body>
</html>