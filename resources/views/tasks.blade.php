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
    <!-- custom page css -->
    <link rel="stylesheet" href="{{ asset('css/tasks.css') }}">
</head>
<body>
    <!-- Loading Spinner -->
    <div class="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

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

                    <!-- Task filters -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2">
                                <select class="form-select form-select-sm" id="filter-status">
                                    <option value="all">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="in-progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                                
                            </div>
                            
                        </div>
                    </div>

                    <!-- Task list -->
                    <div id="task-list">
                        <!-- Tasks will be dynamically added here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" >
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-task-form">
                        <input type="hidden" id="edit-task-id">
                        <div class="mb-3">
                            <label for="edit-task-name" class="form-label">Task Name</label>
                            <input type="text" class="form-control" id="edit-task-name" required minlength="3" maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label for="edit-task-status" class="form-label">Status</label>
                            <select class="form-select" id="edit-task-status">
                                <option value="pending">Pending</option>
                                <option value="in-progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        
                       
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-edit-task">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
<!-- custom page js -->
<script src="{{ asset('js/tasks.js') }}"></script>
