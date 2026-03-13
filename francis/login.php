<?php
require_once 'config.php';
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: admin.php');
    exit();
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        $login_error = "Please enter both username and password";
    } else {
        // Check user credentials
        $query = "SELECT id, firstname, lastname, position, password_hash, is_active FROM employee WHERE username = '$username'";
        $result = $conn->query($query);
        
        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password and check if account is active
            if (password_verify($password, $user['password_hash']) && $user['is_active']) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['firstname'] = $user['firstname'];
                $_SESSION['lastname'] = $user['lastname'];
                $_SESSION['position'] = $user['position'];
                
                // Redirect to admin dashboard
                header('Location: admin.php');
                exit();
            } else {
                $login_error = "Invalid username or password";
            }
        } else {
            $login_error = "Invalid username or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Accounting System</title>
    <link rel="icon" href="uploads/diocese.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- ACCOUNTING THEME BACKGROUND --- */
        body {
            /* Deep Navy Blue - Professional Financial Color */
            background-color: #0f172a; 
            /* Subtle Money Pattern Background */
            background-image: 
                radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.03) 0%, transparent 60%),
                repeating-linear-gradient(45deg, rgba(16, 185, 129, 0.03) 0px, rgba(16, 185, 129, 0.03) 1px, transparent 1px, transparent 20px),
                repeating-linear-gradient(-45deg, rgba(16, 185, 129, 0.03) 0px, rgba(16, 185, 129, 0.03) 1px, transparent 1px, transparent 20px);
            
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
            position: relative;
        }

        /* Abstract Finance Shapes */
        body::before {
            content: '';
            position: absolute;
            top: -100px;
            left: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(21, 128, 61, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -150px;
            right: -150px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
        }

        .login-container {
            max-width: 420px;
            width: 90%;
            padding: 40px;
            position: relative;
            z-index: 1;
            
            /* Glassmorphism Effect */
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            
            border-radius: 15px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header img {
            max-width: 90px;
            margin-bottom: 20px;
            /* Logo Shadow for depth */
            filter: drop-shadow(0 5px 10px rgba(0,0,0,0.1));
        }

        .login-header h2 {
            color: #1e293b; /* Slate 800 */
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 5px;
        }

        .login-header p {
            color: #64748b; /* Slate 500 */
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .input-group-text {
            background-color: #f1f5f9;
            border-right: none;
            color: #475569;
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
            transition: background-color 0.3s ease;
        }
        
        .input-group:hover .input-group-text {
            background-color: #e2e8f0;
        }

        .form-control {
            border-left: none;
            padding: 12px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            box-shadow: none;
            border-color: #10b981; /* Emerald Green Focus */
            background-color: #fff;
        }

        /* Fix border radius issue with input group */
        .input-group .form-control:first-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        
        /* Button Styling - Call to Action */
        .btn-login {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%); /* Emerald Gradient */
            border: none;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            border-radius: 8px;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(16, 185, 129, 0.3);
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
        }

        .alert {
            margin-bottom: 20px;
            border-radius: 8px;
            border: none;
            font-size: 0.9rem;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .footer {
            text-align: center;
            margin-top: 25px;
            color: #94a3b8;
            font-size: 12px;
        }
        
        /* --- NEW: Developer Section Styles --- */
        .developer-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: center;
        }

        .dev-card {
            display: flex;
            align-items: center;
            gap: 12px;
            background-color: #f0fdf4; /* Very light emerald tint */
            padding: 8px 18px 8px 8px;
            border-radius: 50px;
            border: 1px solid #dcfce7;
            transition: all 0.3s ease;
            cursor: default;
        }

        .dev-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
            background-color: #fff;
            border-color: #10b981;
        }

        .dev-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #10b981;
            transition: transform 0.4s ease;
        }

        .dev-card:hover .dev-avatar {
            transform: rotate(360deg);
        }

        .dev-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .dev-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0f172a;
        }

        .dev-role {
            font-size: 0.7rem;
            color: #059669; /* Emerald 600 */
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        /* -------------------------------------- */
        
        /* --- Loading Overlay Styles --- */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /* White background with slight opacity */
            background-color: rgba(255, 255, 255, 0.98);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        .loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        /* Logo Styles */
        .loading-logo {
            width: 120px;
            height: 120px;
            margin-bottom: 30px; /* Space between logo and spinner */
            animation: pulse 2s infinite;
        }
        
        /* Custom Green Spinner - Positioned OUTSIDE the logo */
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #10b981; /* Emerald Top */
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px; /* Space between spinner and text */
        }
        
        .loading-text {
            font-size: 18px;
            color: #334155;
            margin-bottom: 15px; /* Adjusted margin */
            font-weight: 500;
        }
        
        .loading-progress {
            width: 300px;
            height: 6px;
            background-color: #e2e8f0;
            border-radius: 3px;
            margin-top: 5px; /* Space above progress bar */
            overflow: hidden;
        }
        
        .loading-progress-bar {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #10b981, #059669);
            border-radius: 3px;
            animation: progress 10s linear forwards;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        @keyframes progress {
            0% { width: 0; }
            100% { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="uploads/diocese.png" alt="System Logo">
            <h2>Admin Portal</h2>
            <p>Secure Accounting Access</p>
        </div>
        
        <?php if (isset($login_error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $login_error; ?>
            </div>
        <?php endif; ?>
        
        <form id="loginForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                    </div>
                    <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                    </div>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-login">
                    Access Dashboard <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </form>

        <!-- Developer Section -->
        <div class="developer-section">
            <div class="dev-card">
                <img src="uploads/francis.jpg" alt="Francis P. Malabo" class="dev-avatar">
                <div class="dev-info">
                    <div class="dev-name">Francis P. Malabo</div>
                    <div class="dev-role"><i class="fas fa-code"></i> Code Warriors Member</div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date("Y"); ?> Accounting System. All rights reserved.</p>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <!-- 1. Logo -->
        <img src="uploads/diocese.png" alt="System Logo" class="loading-logo">
        
        <!-- 2. Spinner (Outside the logo) -->
        <div class="loading-spinner"></div>
        
        <!-- 3. Text -->
        <div class="loading-text">Authenticating Credentials...</div>
        
        <!-- 4. Progress Bar -->
        <div class="loading-progress">
            <div class="loading-progress-bar"></div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading overlay
                $('#loadingOverlay').addClass('active');
                
                // Submit form after 5 seconds (simulating delay)
                setTimeout(function() {
                    $('#loginForm').off('submit').submit();
                }, 5000);
            });
        });
    </script>
</body>
</html>