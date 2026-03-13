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

 $error = "";

// Process login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Query to check user
    $query = "SELECT * FROM staff WHERE username = '$username'";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            
            // Set session variables
            $_SESSION['user_id'] = $user['staff_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] == 'Admin') {
                header('Location: admin.php');
            } else {
                header('Location: dashboard.php');
            }
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Guidance Office System - Login</title>
    
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
            min-height: 600px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease;
        }

        /* Hover effect for the container itself */
        .login-container:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
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
            border: 4px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.4s ease, border-color 0.3s ease;
        }

        /* Logo Hover */
        .login-image img:hover {
            transform: scale(1.05) rotate(5deg);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .login-image h2 {
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 28px;
            transition: color 0.3s ease;
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
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }
        
        .login-header {
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
            transition: transform 0.3s ease;
        }

        .login-header h1:hover {
            transform: translateX(5px);
        }
        
        .login-header p {
            color: var(--muted-text);
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--dark-text);
            transition: color 0.3s ease;
        }

        .form-group:hover label {
            color: var(--primary-color);
        }
        
        .form-control {
            height: 50px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            padding-left: 45px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #fff;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 58, 95, 0.25);
            background-color: #fcfcfc;
        }

        /* Input Hover Effect */
        .form-control:hover {
            border-color: #8daac9;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 42px;
            color: var(--muted-text);
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .form-group:focus-within .input-icon {
            color: var(--primary-color);
            transform: scale(1.1);
        }
        
        .btn-login {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            height: 50px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 5px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(26, 58, 95, 0.3);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .alert-danger {
            border-radius: 5px;
            padding: 12px 15px;
            margin-bottom: 25px;
            border: none;
            background-color: rgba(211, 47, 47, 0.1);
            color: var(--accent-color);
        }
        
        .login-footer {
            margin-top: 30px;
            text-align: center;
            color: var(--muted-text);
            font-size: 14px;
        }
        
        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            position: relative;
            transition: color 0.3s ease;
        }
        
        .login-footer a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--primary-color);
            transition: width 0.3s ease;
        }
        
        .login-footer a:hover {
            color: var(--secondary-color);
            text-decoration: none;
        }

        .login-footer a:hover::after {
            width: 100%;
        }

        /* Alumni Section Styles */
        .alumni-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
        }

        .alumni-text {
            font-size: 15px;
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .btn-tracer {
            background-color: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-tracer:hover {
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            box-shadow: 0 5px 15px rgba(26, 58, 95, 0.2);
            transform: scale(1.02);
        }
        
        /* --- NEW: Developer Section Styles --- */
        .developer-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0.9;
            transition: opacity 0.3s ease;
        }

        .developer-section:hover {
            opacity: 1;
        }

        .dev-label {
            font-size: 0.8rem;
            color: #adb5bd;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .dev-card {
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: #f1f3f5;
            padding: 8px 20px 8px 8px;
            border-radius: 50px;
            cursor: default;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .dev-card:hover {
            background-color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .dev-img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-color);
            padding: 2px;
            background-clip: content-box;
            transition: transform 0.4s ease;
        }

        .dev-card:hover .dev-img {
            transform: rotate(360deg);
        }

        .dev-details {
            display: flex;
            flex-direction: column;
        }

        .dev-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
            line-height: 1.2;
        }

        .dev-team {
            font-size: 0.75rem;
            color: var(--muted-text);
            font-weight: 500;
            margin: 0;
        }

        .dev-team i {
            margin-right: 4px;
        }
        /* ----------------------------------- */
        
        /* Loading Overlay Styles */
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
        
        .loading-text {
            font-size: 18px;
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .loading-subtext {
            font-size: 14px;
            color: var(--muted-text);
            text-align: center;
            max-width: 300px;
        }
        
        .progress-container {
            width: 200px;
            height: 6px;
            background-color: #e9ecef;
            border-radius: 3px;
            margin-top: 20px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background-color: var(--primary-color);
            width: 0%;
            transition: width 5s linear;
            border-radius: 3px;
        }
        
        @media (max-width: 992px) {
            .login-image {
                display: none;
            }
            
            .login-form {
                padding: 40px 30px;
            }
            
            .login-container {
                width: 95%;
                min-height: 500px;
            }
        }
        
        @media (max-width: 576px) {
            .login-form {
                padding: 30px 20px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .login-container {
                width: 98%;
                min-height: 450px;
            }
            
            .loading-text {
                font-size: 16px;
            }
            
            .loading-subtext {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <img src="uploads/csr.png" alt="School Logo">
            <h2>Guidance Office System</h2>
            <p>A comprehensive student guidance and counseling management platform designed to streamline services and enhance student support.</p>
        </div>
        <div class="login-form">
            <div class="login-header">
                <h1>Welcome Back</h1>
                <p>Please sign in to your account to access the system</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form id="loginForm" action="login.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="rememberMe">
                        <label class="custom-control-label" for="rememberMe">Remember me</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-login">Sign In</button>
            </form>
            
            <div class="login-footer">
                <p>© <?php echo date('Y'); ?> Guidance Office System. All rights reserved.</p>
              
            </div>

            <!-- Alumni Section -->
            <div class="alumni-section">
                <div class="alumni-text">We want to hear from our alumni.</div>
                <a href="alumnitracer.php" class="btn-tracer">
                    <i class="fas fa-graduation-cap mr-1"></i> Graduate Tracer
                </a>
            </div>

            <!-- Developer Section -->
            <div class="developer-section">
                <div class="dev-label">System Development</div>
                <div class="dev-card">
                    <img src="uploads/kenn.png" alt="Kenn Jay A. Eslais" class="dev-img">
                    <div class="dev-details">
                        <span class="dev-name">Kenn Jay A. Eslais</span>
                        <span class="dev-team"><i class="fas fa-code"></i> Member of Code Warriors</span>
                    </div>
                </div>
            </div>
            
            <!-- Loading Overlay -->
            <div class="loading-overlay" id="loadingOverlay">
                <div class="spinner"></div>
                <div class="loading-text">Authenticating...</div>
                <div class="loading-subtext">Please wait while we verify your credentials and prepare your dashboard</div>
                <div class="progress-container">
                    <div class="progress-bar" id="progressBar"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const progressBar = document.getElementById('progressBar');
            
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading overlay
                loadingOverlay.classList.add('active');
                
                // Start progress bar animation
                setTimeout(function() {
                    progressBar.style.width = '100%';
                }, 100);
                
                // Submit form after 5 seconds
                setTimeout(function() {
                    loginForm.submit();
                }, 5000);
            });
        });
    </script>
</body>
</html>