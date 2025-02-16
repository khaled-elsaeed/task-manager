// State management
let tasks = [];
let sortDirection = 'desc';
let currentFilters = { status: 'all' };
let currentSort = 'created';

// Initialize edit modal and loading spinner
const editTaskModal = new bootstrap.Modal(document.getElementById('editTaskModal'));
const loadingSpinner = document.querySelector('.loading-spinner');

/**
 * Displays a notification using SweetAlert2.
 * @param {Object} options - Notification configuration options.
 */
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
        showClass: { popup: 'animate__animated animate__fadeIn' },
        hideClass: { popup: 'animate__animated animate__fadeOut' }
    });
}

/**
 * Shows the loading spinner.
 */
function showLoading() {
    loadingSpinner.style.display = 'block';
}

/**
 * Hides the loading spinner.
 */
function hideLoading() {
    loadingSpinner.style.display = 'none';
}

/**
 * Makes an API request with authorization and CSRF token.
 * @param {string} url - The API endpoint.
 * @param {Object} options - Fetch API options.
 * @returns {Promise<Object>} - The JSON response from the API.
 */
async function apiRequest(url, options = {}) {
    showLoading();
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

        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return await response.json();
    } catch (error) {
        showNotification({ icon: 'error', title: 'Error', text: error.message, position: 'center' });
        throw error;
    } finally {
        hideLoading();
    }
}

/**
 * Fetches the CSRF token from the server.
 * @returns {Promise<string>} - The CSRF token.
 */
async function fetchCsrfToken() {
    try {
        const response = await fetch('/csrf-token');
        const data = await response.json();
        return data.csrfToken;
    } catch (error) {
        showNotification({ icon: 'error', title: 'Error', text: 'Failed to fetch CSRF token', position: 'center' });
        throw error;
    }
}

/**
 * Returns the CSS class for a task status badge.
 * @param {string} status - The task status.
 * @returns {string} - The CSS class for the status badge.
 */
function getStatusBadgeClass(status) {
    const statusClasses = {
        'pending': 'status-pending',
        'in-progress': 'status-in-progress',
        'completed': 'status-completed'
    };
    return statusClasses[status] || 'status-pending';
}

/**
 * Filters tasks based on current filters.
 * @param {Array} tasks - The list of tasks to filter.
 * @returns {Array} - The filtered tasks.
 */
function filterTasks(tasks) {
    return tasks.filter(task => currentFilters.status === 'all' || task.status === currentFilters.status);
}

/**
 * Sorts tasks based on the current sort criteria and direction.
 * @param {Array} tasks - The list of tasks to sort.
 * @returns {Array} - The sorted tasks.
 */
function sortTasks(tasks) {
    return [...tasks].sort((a, b) => {
        let comparison = 0;
        if (currentSort === 'status') {
            const statusOrder = { pending: 1, 'in-progress': 2, completed: 3 };
            comparison = statusOrder[a.status] - statusOrder[b.status];
        }
        return sortDirection === 'desc' ? comparison : -comparison;
    });
}

/**
 * Formats a date string into a readable format.
 * @param {string} dateString - The date string to format.
 * @returns {string} - The formatted date string.
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

/**
 * Updates the task list view in the DOM.
 */
function updateTaskList() {
    const taskList = document.getElementById('task-list');
    const filteredTasks = filterTasks(tasks);
    const sortedTasks = sortTasks(filteredTasks);

    if (sortedTasks.length === 0) {
        taskList.innerHTML = `
            <div class="text-center text-muted my-5">
                <i class="fa fa-tasks fa-3x mb-3"></i>
                <p>No tasks found. Create your first task above!</p>
            </div>
        `;
        return;
    }

    taskList.innerHTML = '';
    sortedTasks.forEach(task => {
        const listItem = document.createElement('div');
        listItem.className = 'task-item p-4 mb-3 bg-white rounded-3 shadow-sm';
        listItem.innerHTML = `
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-2">
                <div class="d-flex flex-column flex-md-row align-items-start">
                    <span class="task-title h5 mb-0">${task.title}</span>
                    <div class="d-flex gap-2 ms-md-2 mt-2 mt-md-0">
                        <span class="status-badge ${getStatusBadgeClass(task.status)}">${task.status}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center mt-2 mt-md-0">
                    <button class="btn btn-warning btn-sm btn-action edit-task me-2" data-task-id="${task.id}">
                        <i class="fa fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm btn-action delete-task" data-task-id="${task.id}">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        taskList.appendChild(listItem);
    });

    // Add event listeners for edit and delete buttons
    document.querySelectorAll('.edit-task').forEach(button => button.addEventListener('click', editTask));
    document.querySelectorAll('.delete-task').forEach(button => button.addEventListener('click', deleteTask));
}

/**
 * Fetches tasks from the server and updates the task list.
 */
async function fetchTasks() {
    try {
        tasks = await apiRequest('/api/tasks');
        updateTaskList();
    } catch (error) {
        console.error('Error fetching tasks:', error);
    }
}

/**
 * Handles the edit task action.
 * @param {Event} event - The click event.
 */
async function editTask(event) {
    const taskId = event.target.closest('.edit-task').getAttribute('data-task-id');
    const task = tasks.find(t => t.id == taskId);
    if (!task) return;

    // Set values in modal
    document.getElementById('edit-task-id').value = task.id;
    document.getElementById('edit-task-name').value = task.title;
    document.getElementById('edit-task-status').value = task.status;

    // Show modal
    editTaskModal.show();
}

/**
 * Handles the delete task action.
 * @param {Event} event - The click event.
 */
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
            await apiRequest(`/api/tasks/${taskId}`, { method: 'DELETE' });
            tasks = tasks.filter(task => task.id !== taskId);
            fetchTasks();
            showNotification({ title: 'Deleted!', text: 'Task has been deleted.', toast: true });
        } catch (error) {
            console.error('Error deleting task:', error);
        }
    }
}

// Event listeners for save changes, create task, filters, and sorting
document.getElementById('save-edit-task').addEventListener('click', async function() {
    const taskId = document.getElementById('edit-task-id').value;
    const taskData = {
        title: document.getElementById('edit-task-name').value.trim(),
        status: document.getElementById('edit-task-status').value,
    };

    if (taskData.title.length < 3) {
        showNotification({ icon: 'warning', title: 'Invalid Input', text: 'Task name must be at least 3 characters long', position: 'center' });
        return;
    }

    try {
        const updatedTask = await apiRequest(`/api/tasks/${taskId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(taskData),
        });

        tasks = tasks.map(task => task.id === taskId ? updatedTask : task);
        fetchTasks();
        editTaskModal.hide();
        showNotification({ title: 'Success', text: 'Task updated successfully!', toast: true });
    } catch (error) {
        console.error('Error updating task:', error);
    }
});

document.getElementById('create-task-form').addEventListener('submit', async function(event) {
    event.preventDefault();
    const taskData = { title: document.getElementById('task-name').value.trim(), status: 'pending' };

    if (taskData.title.length < 3) {
        showNotification({ icon: 'warning', title: 'Invalid Input', text: 'Task name must be at least 3 characters long', position: 'center' });
        return;
    }

    try {
        const newTask = await apiRequest('/api/tasks', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(taskData),
        });

        tasks.unshift(newTask);
        fetchTasks();
        document.getElementById('task-name').value = '';
        showNotification({ title: 'Success', text: 'Task created successfully!', toast: true });
    } catch (error) {
        console.error('Error creating task:', error);
    }
});

document.getElementById('filter-status').addEventListener('change', function(event) {
    currentFilters.status = event.target.value;
    updateTaskList();
});


// Initialize the application
fetchTasks();