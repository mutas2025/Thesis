<?php
session_start();
require_once '../config.php';
// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'teacher') {
    header("Location: index.php");
    exit();
}
// Process login form
$login_err = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Validate credentials against teachers table
    $query = "SELECT * FROM teachers WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = 'teacher';
            $_SESSION['name'] = $user['name'];
            
            // Handle remember me functionality
            if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                // Set cookie for 30 days
                $cookie_name = 'teacher_remember';
                $cookie_value = $user['id'] . ':' . hash('sha256', $user['username'] . $user['password']);
                setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
            }
            
            // Redirect to dashboard
            header("Location: index.php");
            exit();
        } else {
            $login_err = "Invalid username or password";
        }
    } else {
        $login_err = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login - School Management System</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header img {
            max-width: 100px;
            margin-bottom: 15px;
        }
        .login-header h2 {
            color: #333;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn-login {
            background-color: #007bff;
            border: none;
            padding: 10px;
            font-weight: 600;
            width: 100%;
        }
        .btn-login:hover {
            background-color: #0069d9;
        }
        .alert {
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
            font-size: 14px;
        }
        .form-options {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 14px;
        }
        
        /* Loading Overlay Styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.95);
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
        
        .loading-logo {
            width: 150px;
            height: 150px;
            margin-bottom: 30px;
            animation: pulse 2s infinite;
        }
        
        .loading-spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        .loading-text {
            font-size: 18px;
            color: #333;
            margin-top: 15px;
        }
        
        .loading-progress {
            width: 300px;
            height: 6px;
            background-color: #e9ecef;
            border-radius: 3px;
            margin-top: 15px;
            overflow: hidden;
        }
        
        .loading-progress-bar {
            height: 100%;
            width: 0;
            background-color: #007bff;
            border-radius: 3px;
            animation: progress 10s linear forwards;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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
            <img src="../uploads/csr.png" alt="School Logo">
            <h2>Teacher Portal</h2>
            <p class="text-muted">Please login to your account</p>
        </div>
        
        <?php if (!empty($login_err)): ?>
            <div class="alert alert-danger"><?php echo $login_err; ?></div>
        <?php endif; ?>
        
        <form id="loginForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                    <input type="text" name="username" class="form-control" placeholder="Username" required autocomplete="new-username">
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                    <input type="password" name="password" class="form-control" placeholder="Password" required autocomplete="new-password">
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-login">Login</button>
            </div>
            
            <div class="form-options">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember Me</label>
                </div>
                <a href="#">Forgot Password?</a>
            </div>
        </form>
        
        <div class="footer">
            <p>&copy; <?php echo date("Y"); ?> School Management System. All rights reserved.</p>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <img src="../uploads/csr.png" alt="School Logo" class="loading-logo">
        <div class="loading-spinner"></div>
        <div class="loading-text">Authenticating...</div>
        <div class="loading-progress">
            <div class="loading-progress-bar"></div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Clear any saved credentials on page load
            $('input[type="text"], input[type="password"]').val('');
            
            // Disable browser's autocomplete
            $('input').attr('autocomplete', 'off');
            
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading overlay
                $('#loadingOverlay').addClass('active');
                
                // Submit form after a short delay to show the loading animation
                setTimeout(function() {
                    $('#loginForm').off('submit').submit();
                }, 2000);
            });
        });
    </script>
</body>
</html>