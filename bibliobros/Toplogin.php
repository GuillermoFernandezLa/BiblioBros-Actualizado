<?php

require_once __DIR__ . '/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: Topdashboard.php');
    exit;
}

$error = '';
$emailError = '';
$passwordError = '';
$showSuccessMessage = false;

// Check if user just registered
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $showSuccessMessage = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    
    $hasErrors = false;
    
    // Validate email
    if (!$email) {
        $emailError = 'Please enter your email address.';
        $hasErrors = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = 'Please enter a valid email address.';
        $hasErrors = true;
    }
    
    // Validate password
    if (!$password) {
        $passwordError = 'Please enter your password.';
        $hasErrors = true;
    }
    
    if (!$hasErrors) {
        $stmt = $pdo->prepare("
            SELECT id, fullname, password_hash
            FROM users
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            $emailError = 'This email is not registered. Please check your email or register for a new account.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            $passwordError = 'Incorrect password. Please try again.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            header('Location: Topdashboard.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>BiblioBros – Login</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/style.css" />
    
    <style>
        .password-input-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 1px;
            top: 1px;
            bottom: 1px;
            background: white;
            border: 1px solid #dee2e6;
            border-left: 1px solid #dee2e6;
            color: #6c757d;
            padding: 0 0.75rem;
            cursor: pointer;
            z-index: 10;
            border-radius: 0 0.375rem 0.375rem 0;
            transition: all 0.3s ease;
            font-size: 0.875rem;
            font-weight: 500;
            min-width: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .password-toggle:hover {
            background: #ffc107;
            color: white;
            border-color: #ffc107;
        }
        
        .password-input-wrapper input {
            padding-right: 70px;
        }
        
        .form-control.is-invalid {
            background-image: none;
            border-color: #dc3545;
        }
        
        .form-control.is-valid {
            background-image: none;
            border-color: #28a745;
        }
        
        .form-control:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }
        
        .invalid-feedback {
            display: block;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .valid-feedback {
            display: block;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            color: #28a745;
        }
        
        .alert-success {
            border-left: 4px solid #28a745;
            background: #d4edda;
        }
        
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 450px;
            width: 100%;
        }
        
        .section-title {
            color: #333;
            font-weight: 600;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 3px;
            background: #ffc107;
        }
        
        .btn-primary {
            background: #ffc107;
            border: none;
            color: #333;
            font-weight: 500;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #ffdb4d;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
        }
        
        .btn-primary:disabled {
            background: #ffc107;
            opacity: 0.7;
        }
        
        a {
            color: #ffc107;
        }
        
        a:hover {
            color: #ffdb4d;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">

    <!-- Dynamic Navbar -->
    <div id="navbar-placeholder"></div>

    <!-- Login Form -->
    <main class="container d-flex flex-column align-items-center justify-content-center flex-fill py-5">
        <div class="form-card">
            <h2 class="section-title text-center mb-4">Login to Your Account</h2>

            <?php if ($showSuccessMessage): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    Registration successful! Please login with your credentials.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="Toplogin.php" method="post" id="loginForm" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-1"></i>Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control <?= $emailError ? 'is-invalid' : '' ?>" 
                        placeholder="you@example.com"
                        value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" 
                        required 
                        autocomplete="email"
                    />
                    <?php if ($emailError): ?>
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            <?= htmlspecialchars($emailError) ?>
                        </div>
                    <?php endif; ?>
                    <div class="invalid-feedback" id="emailError" style="display: none;">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        <span></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-1"></i>Password
                    </label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control <?= $passwordError ? 'is-invalid' : '' ?>" 
                            placeholder="Enter your password"
                            required 
                            autocomplete="current-password"
                        />
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                            Show
                        </button>
                    </div>
                    <?php if ($passwordError): ?>
                        <div class="invalid-feedback">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            <?= htmlspecialchars($passwordError) ?>
                        </div>
                    <?php endif; ?>
                    <div class="invalid-feedback" id="passwordError" style="display: none;">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        <span></span>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-xl" id="loginBtn">
                        <span id="btnText">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </span>
                        <span id="btnSpinner" style="display: none;">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Logging in...
                        </span>
                    </button>
                </div>

                <p class="text-center">
                    Don't have an account?
                    <a href="Topregister.php" class="text-decoration-none">Register here</a>
                </p>
            </form>
        </div>
    </main>

    <!-- Dynamic Modals -->
    <div id="modal-container"></div>

    <!-- Dynamic Footer -->
    <div id="footer-placeholder"></div>

    <!-- Bootstrap JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Shared logic: navbar, footer, modals -->
    <script src="assets/js/main.js"></script>
    
    <script>
        // Password visibility toggle with text
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.textContent = 'Hide';
                this.setAttribute('aria-label', 'Hide password');
            } else {
                passwordInput.type = 'password';
                this.textContent = 'Show';
                this.setAttribute('aria-label', 'Show password');
            }
        });
        
        // Form validation
        const loginForm = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const loginBtn = document.getElementById('loginBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        
        // Clear validation on input
        emailInput.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                const errorDiv = document.getElementById('emailError');
                if (errorDiv) errorDiv.style.display = 'none';
            }
        });
        
        passwordInput.addEventListener('input', function() {
            if (this.value) {
                this.classList.remove('is-invalid');
                const errorDiv = document.getElementById('passwordError');
                if (errorDiv) errorDiv.style.display = 'none';
            }
        });
        
        // Client-side validation on submit
        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Reset previous validation states
            emailInput.classList.remove('is-invalid');
            passwordInput.classList.remove('is-invalid');
            
            // Email validation
            const emailValue = emailInput.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const emailErrorDiv = document.getElementById('emailError');
            
            if (!emailValue) {
                emailInput.classList.add('is-invalid');
                if (emailErrorDiv) {
                    emailErrorDiv.querySelector('span').textContent = 'Please enter your email address.';
                    emailErrorDiv.style.display = 'block';
                }
                isValid = false;
            } else if (!emailRegex.test(emailValue)) {
                emailInput.classList.add('is-invalid');
                if (emailErrorDiv) {
                    emailErrorDiv.querySelector('span').textContent = 'Please enter a valid email address.';
                    emailErrorDiv.style.display = 'block';
                }
                isValid = false;
            }
            
            // Password validation
            const passwordErrorDiv = document.getElementById('passwordError');
            if (!passwordInput.value) {
                passwordInput.classList.add('is-invalid');
                if (passwordErrorDiv) {
                    passwordErrorDiv.querySelector('span').textContent = 'Please enter your password.';
                    passwordErrorDiv.style.display = 'block';
                }
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                // Focus on first error field
                if (emailInput.classList.contains('is-invalid')) {
                    emailInput.focus();
                } else if (passwordInput.classList.contains('is-invalid')) {
                    passwordInput.focus();
                }
            } else {
                // Show loading state
                btnText.style.display = 'none';
                btnSpinner.style.display = 'inline-block';
                loginBtn.disabled = true;
            }
        });
        
        // Real-time email validation on blur
        emailInput.addEventListener('blur', function() {
            const emailValue = this.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const emailErrorDiv = document.getElementById('emailError');
            
            if (emailValue && !emailRegex.test(emailValue)) {
                this.classList.add('is-invalid');
                if (emailErrorDiv) {
                    emailErrorDiv.querySelector('span').textContent = 'Please enter a valid email address.';
                    emailErrorDiv.style.display = 'block';
                }
            }
        });
    </script>
</body>

</html>