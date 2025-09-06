<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

// Fetch universities for the select dropdown
$stmt = $pdo->query("SELECT id, name FROM universities ORDER BY name");
$universities = $stmt->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $university_id = (int) ($_POST['university_id'] ?? 0);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validate form fields
    if (!$fullname || !$email || !$university_id || !$password || !$confirm) {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'The email is not valid.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    } else {
        $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $check->execute(['email' => $email]);
        if ($check->fetchColumn() > 0) {
            $errors[] = 'This email is already registered.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ins = $pdo->prepare("
            INSERT INTO users (fullname, email, password_hash, university_id, created_at)
            VALUES (:fullname, :email, :hash, :uid, NOW())
        ");
        $ins->execute([
            'fullname' => $fullname,
            'email' => $email,
            'hash' => $hash,
            'uid' => $university_id
        ]);
        header('Location: Toplogin.php?registered=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BiblioBros – Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/style.css" />
    
    <style>
        .password-field {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 38px;
            background: white;
            border: 1px solid #dee2e6;
            border-left: none;
            color: #6c757d;
            padding: 0.375rem 0.75rem;
            cursor: pointer;
            z-index: 10;
            border-radius: 0 0.375rem 0.375rem 0;
            transition: all 0.3s ease;
            height: 38px;
        }
        
        .password-toggle:hover {
            background: #ffc107;
            color: white;
            border-color: #ffc107;
        }
        
        .password-field input {
            padding-right: 60px;
        }
        
        .password-strength {
            margin-top: 0.5rem;
        }
        
        .strength-bar {
            height: 5px;
            border-radius: 3px;
            background: #e9ecef;
            overflow: hidden;
            margin-top: 0.25rem;
        }
        
        .strength-bar-fill {
            height: 100%;
            transition: all 0.3s ease;
            width: 0%;
        }
        
        .strength-bar-fill.weak {
            width: 33%;
            background: #dc3545;
        }
        
        .strength-bar-fill.medium {
            width: 66%;
            background: #ffc107;
        }
        
        .strength-bar-fill.strong {
            width: 100%;
            background: #28a745;
        }
        
        .strength-text {
            font-size: 0.875rem;
            margin-top: 0.25rem;
            font-weight: 500;
        }
        
        .strength-text.weak {
            color: #dc3545;
        }
        
        .strength-text.medium {
            color: #ffc107;
        }
        
        .strength-text.strong {
            color: #28a745;
        }
        
        .strength-requirements {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 0.25rem;
        }
        
        .requirement {
            margin: 0.25rem 0;
        }
        
        .requirement.met {
            color: #28a745;
        }
        
        .requirement i {
            width: 15px;
            margin-right: 0.25rem;
        }
        
        .form-control:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }
        
        /* Ajustes específicos para el campo de confirmación */
        .confirm-password-container {
            position: relative;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <div id="navbar-placeholder"></div>

    <main class="container d-flex flex-column align-items-center justify-content-center flex-fill py-5">
        <div class="form-card">
            <h2 class="section-title text-center mb-4">Create Account</h2>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="post" id="registerForm" novalidate>
                <div class="mb-3">
                    <label for="fullname" class="form-label">
                        <i class="fas fa-user me-1"></i>Full Name
                    </label>
                    <input type="text" id="fullname" name="fullname" class="form-control"
                        value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" required />
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-1"></i>Email Address
                    </label>
                    <input type="email" id="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                
                <div class="mb-3">
                    <label for="university_id" class="form-label">
                        <i class="fas fa-university me-1"></i>University
                    </label>
                    <select id="university_id" name="university_id" class="form-select" required>
                        <option value="">-- Select a university --</option>
                        <?php foreach ($universities as $uni): ?>
                            <option value="<?= $uni['id'] ?>" 
                                <?= (isset($_POST['university_id']) && (int)$_POST['university_id'] === (int)$uni['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($uni['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3 password-field">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-1"></i>Password
                    </label>
                    <input type="password" id="password" name="password" class="form-control" 
                           minlength="6" required />
                    <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password">
                        <i class="fas fa-eye"></i>
                    </button>
                    
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-bar-fill" id="strengthBarFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText"></div>
                        <div class="strength-requirements" id="requirements" style="display: none;">
                            <div class="requirement" id="length">
                                <i class="fas fa-times text-danger"></i> At least 6 characters
                            </div>
                            <div class="requirement" id="lowercase">
                                <i class="fas fa-times text-danger"></i> One lowercase letter
                            </div>
                            <div class="requirement" id="uppercase">
                                <i class="fas fa-times text-danger"></i> One uppercase letter
                            </div>
                            <div class="requirement" id="number">
                                <i class="fas fa-times text-danger"></i> One number
                            </div>
                            <div class="requirement" id="special">
                                <i class="fas fa-times text-danger"></i> One special character (optional but recommended)
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3 password-field">
                    <label for="confirm_password" class="form-label">
                        <i class="fas fa-lock me-1"></i>Confirm Password
                    </label>
                    <div class="confirm-password-container">
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="form-control" minlength="6" required />
                        <button type="button" class="password-toggle" id="toggleConfirmPassword" aria-label="Toggle password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">Passwords do not match.</div>
                </div>
                
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-secondary btn-xl">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </button>
                </div>
                
                <p class="text-center">
                    Already have an account? <a href="Toplogin.php" class="text-decoration-none">Log in</a>.
                </p>
            </form>
        </div>
    </main>
    
    <div id="modal-container"></div>
    <div id="footer-placeholder"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <script>
        // Password visibility toggles
        function setupPasswordToggle(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            
            toggle.addEventListener('click', function() {
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }
        
        setupPasswordToggle('togglePassword', 'password');
        setupPasswordToggle('toggleConfirmPassword', 'confirm_password');
        
        // Password strength checker
        const password = document.getElementById('password');
        const strengthBarFill = document.getElementById('strengthBarFill');
        const strengthText = document.getElementById('strengthText');
        const requirements = document.getElementById('requirements');
        
        password.addEventListener('focus', function() {
            requirements.style.display = 'block';
        });
        
        password.addEventListener('blur', function() {
            if (!this.value) {
                requirements.style.display = 'none';
            }
        });
        
        password.addEventListener('input', function() {
            const value = this.value;
            let strength = 0;
            const checks = {
                length: value.length >= 6,
                lowercase: /[a-z]/.test(value),
                uppercase: /[A-Z]/.test(value),
                number: /[0-9]/.test(value),
                special: /[^a-zA-Z0-9]/.test(value)
            };
            
            // Update requirements
            for (let check in checks) {
                const element = document.getElementById(check);
                if (element) {
                    const icon = element.querySelector('i');
                    if (checks[check]) {
                        icon.classList.remove('fa-times', 'text-danger');
                        icon.classList.add('fa-check', 'text-success');
                        element.classList.add('met');
                        if (check !== 'special') strength++;
                        else strength += 0.5; // Special chars are bonus
                    } else {
                        icon.classList.remove('fa-check', 'text-success');
                        icon.classList.add('fa-times', 'text-danger');
                        element.classList.remove('met');
                    }
                }
            }
            
            // Calculate strength level
            strengthBarFill.className = 'strength-bar-fill';
            strengthText.className = 'strength-text';
            
            if (value.length === 0) {
                strengthText.textContent = '';
            } else if (strength < 2) {
                strengthBarFill.classList.add('weak');
                strengthText.classList.add('weak');
                strengthText.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Weak password';
            } else if (strength < 4) {
                strengthBarFill.classList.add('medium');
                strengthText.classList.add('medium');
                strengthText.innerHTML = '<i class="fas fa-shield-alt me-1"></i>Medium strength';
            } else {
                strengthBarFill.classList.add('strong');
                strengthText.classList.add('strong');
                strengthText.innerHTML = '<i class="fas fa-check-shield me-1"></i>Strong password!';
            }
        });
        
        // Email validation
        document.getElementById('email').addEventListener('blur', function() {
            const emailValue = this.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (emailValue && !emailRegex.test(emailValue)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
        
        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            if (this.value !== password.value && this.value) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>