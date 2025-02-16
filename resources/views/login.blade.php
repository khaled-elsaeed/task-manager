<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .password-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-4">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4 p-sm-5">
                        <!-- Logo -->
                        <div class="text-center mb-4">
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-user fa-2x text-white"></i>
                            </div>
                        </div>

                        <h2 class="text-center mb-4">Welcome Back</h2>

                        <form id="login-form" method="POST">
                            <!-- Email Input -->
                            <div class="position-relative mb-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" 
                                           class="form-control border-start-0" 
                                           id="email" 
                                           placeholder="Email address" 
                                           required>
                                </div>
                            </div>

                            <!-- Password Input -->
                            <div class="position-relative mb-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control border-start-0 border-end-0" 
                                           id="password" 
                                           placeholder="Password" 
                                           required>
                                    <span class="input-group-text bg-light border-start-0">
                                        <i class="fas fa-eye password-toggle text-muted" id="password-toggle"></i>
                                    </span>
                                </div>
                            </div>

                            

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary w-100 py-2 mb-4 position-relative">
                                <span>Sign In</span>
                                <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                            </button>

                           
                        </form>

                        <!-- Error Message -->
                        <div id="error-message" class="alert alert-danger mt-3 d-none">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('login-form');
        const passwordToggle = document.getElementById('password-toggle');
        const passwordInput = document.getElementById('password');
        const emailInput = document.getElementById('email');
        const errorDiv = document.getElementById('error-message');
        const submitButton = loginForm.querySelector('button[type="submit"]');
        const spinner = submitButton.querySelector('.spinner-border');

        // API Configuration
        const API_URL = '/api/login'; 
        
        // CSRF Token Fetch
        async function fetchCsrfToken() {
            const response = await fetch('/csrf-token');
            const data = await response.json();
            return data.csrfToken;
        }

        // Password visibility toggle
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Form validation
        function validateForm() {
            let isValid = true;
            const email = emailInput.value.trim();
            const password = passwordInput.value.trim();

            if (!email || !email.includes('@')) {
                showError('Please enter a valid email address');
                isValid = false;
            }

            if (!password || password.length < 6) {
                showError('Password must be at least 6 characters long');
                isValid = false;
            }

            return isValid;
        }

        // Show error message
        function showError(message) {
            errorDiv.querySelector('span').textContent = message;
            errorDiv.classList.remove('d-none');
        }

        // Hide error message
        function hideError() {
            errorDiv.classList.add('d-none');
        }

        // Set loading state
        function setLoading(isLoading) {
            submitButton.disabled = isLoading;
            if (isLoading) {
                spinner.classList.remove('d-none');
            } else {
                spinner.classList.add('d-none');
            }
        }

        // Handle API response
        async function handleApiResponse(response) {
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Login failed. Please try again.');
                }
                
                return data;
            }
            throw new Error('Invalid server response');
        }

        // Form submission
        loginForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            hideError();

            if (!validateForm()) {
                return;
            }

            const formData = {
                email: emailInput.value.trim(),
                password: passwordInput.value.trim()
            };

            setLoading(true);

            try {
                const csrfToken = await fetchCsrfToken(); // Fetch CSRF Token

                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken // Include CSRF Token in headers
                    },
                    body: JSON.stringify(formData),
                    credentials: 'include' // Include cookies if needed
                });

                const data = await handleApiResponse(response);

                if (data.token) {
                    // Store token securely
                    localStorage.setItem('auth_token', data.token);
                    // Redirect to dashboard or home page
                    window.location.href = data.redirect || '/tasks';
                } else {
                    throw new Error('Authentication failed. No token received.');
                }

            } catch (error) {
                showError(error.message || 'An error occurred during login. Please try again.');
            } finally {
                setLoading(false);
            }
        });

        // Prevent form submission when password is visible
        document.addEventListener('keypress', function(event) {
            if (event.key === 'Enter' && passwordInput.type === 'text') {
                event.preventDefault();
            }
        });

        // Clear error when user starts typing
        emailInput.addEventListener('input', hideError);
        passwordInput.addEventListener('input', hideError);
    });
</script>

</body>
</html>