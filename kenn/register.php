<?php
include 'config.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == 'Admin') {
            header('Location: admin.php');
        } else {
            header('Location: dashboard.php');
        }
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

 $success_message = '';
 $error_message = '';

// Process registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $office_hours = trim($_POST['office_hours']);
    
    // Validation
    if (empty($full_name) || empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {
        // Check if username already exists
        $check_username = "SELECT staff_id FROM staff WHERE username = ?";
        $stmt = $conn->prepare($check_username);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "Username already exists. Please choose another one.";
        } else {
            // Check if email already exists (if provided)
            if (!empty($email)) {
                $check_email = "SELECT staff_id FROM staff WHERE email = ?";
                $stmt = $conn->prepare($check_email);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error_message = "Email already exists. Please use another email.";
                }
            }
            
            if (empty($error_message)) {
                // Hash password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $insert_query = "INSERT INTO staff (full_name, role, email, contact_number, username, password_hash, office_hours) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("sssssss", $full_name, $role, $email, $contact_number, $username, $password_hash, $office_hours);
                
                if ($stmt->execute()) {
                    $success_message = "Registration successful! Redirecting to login...";
                    // Clear form fields logic if needed, but we redirect shortly anyway
                } else {
                    $error_message = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Guidance Office System - Register</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="uploads/csr.png" type="image/x-icon">
    
    <style>
        :root {
            --primary-color: #1a3a5f;
            --secondary-color: #0f2238;
            --accent-color: #d32f2f;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --muted-text: #6c757d;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
            height: 100vh;
            margin: 0;
            padding: 0;
            background-image: url('uploads/newbuilding.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(26, 58, 95, 0.7);
            z-index: -1;
        }
        
        .login-container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            min-height: 650px; /* Slightly taller for form fields */
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }
        
        .login-image {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: white;
            text-align: center;
        }
        
        .login-image img {
            max-width: 180px;
            margin-bottom: 20px;
            border-radius: 50%;
            border:4px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .login-image h2 {
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 28px;
        }
        
        .login-image p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 400px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .login-form {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow-y: auto; /* Allow scrolling if height is small */
            max-height: 95vh;
        }
        
        .login-header {
            margin-bottom: 25px;
        }
        
        .login-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: var(--muted-text);
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--dark-text);
            font-size: 14px;
        }
        
        .form-control {
            height: 45px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding-left: 45px;
            font-size: 15px;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.25);
        }

        select.form-control {
            cursor: pointer;
            appearance: none; /* Remove default arrow */
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3e%3cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 8px 10px;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 38px; /* Adjusted for labels */
            color: var(--muted-text);
            font-size: 14px;
        }
        
        /* Adjust icon position for inputs without labels */
        .form-group.no-label .input-icon {
            top: 12px;
        }

        .btn-register {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            height: 50px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 5px;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-register:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border-radius: 5px;
            padding: 10px 20px;
            font-weight: 500;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            color: white;
        }
        
        .alert-danger, .alert-success {
            border-radius: 5px;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: none;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: rgba(211, 47, 47, 0.1);
            color: var(--accent-color);
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .login-footer {
            margin-top: 20px;
            text-align: center;
            color: var(--muted-text);
            font-size: 14px;
        }
        
        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }

        /* Password Strength Meter */
        .password-strength {
            height: 4px;
            margin-top: 8px;
            border-radius: 2px;
            transition: all 0.3s ease;
            width: 0%;
        }
        .strength-weak { background-color: #dc3545; }
        .strength-medium { background-color: #ffc107; }
        .strength-strong { background-color: #28a745; }

        /* Loading Overlay */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            border-radius: 10px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        
        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(26, 58, 95, 0.2);
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 992px) {
            .login-image {
                display: none;
            }
            
            .login-form {
                padding: 30px 20px;
                max-height: none;
            }
            
            .login-container {
                width: 95%;
                min-height: auto;
            }
        }
        
        @media (max-width: 576px) {
            .login-form {
                padding: 20px 15px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .login-container {
                width: 98%;
            }
            
            .input-icon {
                top: 38px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <img src="uploads/csr.png" alt="School Logo">
            <h2>Guidance Office System</h2>
            <p>Join our team of dedicated professionals committed to providing exceptional guidance and support to our students.</p>
        </div>
        
        <div class="login-form">
            <div class="login-header">
                <h1>Create an Account</h1>
                <p>Fill in the details below to register a new staff account</p>
            </div>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i><?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form id="registerForm" action="register.php" method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <div class="input-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <input type="text" class="form-control" id="full_name" name="full_name" placeholder="Full Name" 
                                   value="<?php echo isset($full_name) ? htmlspecialchars($full_name) : ''; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role">Role</label>
                            <div class="input-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <select class="form-control" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="Admin" <?php echo (isset($role) && $role == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="Guidance Counselor" <?php echo (isset($role) && $role == 'Guidance Counselor') ? 'selected' : ''; ?>>Guidance Counselor</option>
                                <option value="Staff" <?php echo (isset($role) && $role == 'Staff') ? 'selected' : ''; ?>>Staff</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" 
                                   value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <div class="input-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" placeholder="Contact Number" 
                                   value="<?php echo isset($contact_number) ? htmlspecialchars($contact_number) : ''; ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-icon">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" 
                           value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <div class="password-strength" id="passwordStrength"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <div class="input-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="office_hours">Office Hours</label>
                    <div class="input-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <input type="text" class="form-control" id="office_hours" name="office_hours" placeholder="Office Hours (e.g., 8:00 AM - 5:00 PM)" 
                           value="<?php echo isset($office_hours) ? htmlspecialchars($office_hours) : ''; ?>">
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="agreeTerms">
                        <label class="custom-control-label" for="agreeTerms">I agree to the <a href="#">terms and conditions</a></label>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-6">
                        <button type="button" onclick="window.location.href='login.php'" class="btn btn-secondary btn-block btn-flat">Cancel</button>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn btn-primary btn-block btn-register" id="registerBtn">Register</button>
                    </div>
                </div>
            </form>
            
            <div class="login-footer">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
                <p>© <?php echo date('Y'); ?> Guidance Office System. All rights reserved.</p>
            </div>
            
            <!-- Loading Overlay -->
            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner"></div>
                <div class="loading-text">Creating Account...</div>
                <div class="loading-subtext">Please wait while we set up your profile</div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.getElementById('registerForm');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const passwordInput = document.getElementById('password');
            const passwordStrength = document.getElementById('passwordStrength');
            const confirmInput = document.getElementById('confirm_password');
            
            // Password strength checker
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                if (password.length >= 6) strength++;
                if (password.match(/[a-z]+/)) strength++;
                if (password.match(/[A-Z]+/)) strength++;
                if (password.match(/[0-9]+/)) strength++;
                if (password.match(/[$@#&!]+/)) strength++;
                
                passwordStrength.className = 'password-strength'; // Reset classes
                
                if (password.length > 0) {
                    passwordStrength.style.width = '33%';
                    if (strength <= 2) {
                        passwordStrength.classList.add('strength-weak');
                    } else if (strength === 3 || strength === 4) {
                        passwordStrength.style.width = '66%';
                        passwordStrength.classList.add('strength-medium');
                    } else {
                        passwordStrength.style.width = '100%';
                        passwordStrength.classList.add('strength-strong');
                    }
                } else {
                    passwordStrength.style.width = '0%';
                }
            });
            
            registerForm.addEventListener('submit', function(e) {
                // Checkbox validation
                if (!document.getElementById('agreeTerms').checked) {
                    e.preventDefault();
                    alert('You must agree to the terms and conditions to register.');
                    return;
                }

                // Password Match Validation
                if (passwordInput.value !== confirmInput.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    return;
                }

                // Password Length Validation (PHP backup, but good for UX)
                if (passwordInput.value.length < 6) {
                    e.preventDefault();
                    alert('Password must be at least 6 characters long!');
                    return;
                }

                // Show loading overlay
                loadingOverlay.classList.add('active');
                
                // Allow form submission naturally after showing overlay
                // The timeout is just to let the animation start visually
                setTimeout(function() {
                    registerForm.submit();
                }, 1000);
            });
        });
    </script>
</body>
</html>