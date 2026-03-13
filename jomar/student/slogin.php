<?php
session_start();
require_once '../config.php';
// Check if student is already logged in
if (isset($_SESSION['student_id'])) {
    header("Location: student.php");
    exit();
}
// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $login_err = "Email and password are required";
    } else {
        // Check if student exists with the provided email
        $query = "SELECT * FROM students WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) == 1) {
            $student = mysqli_fetch_assoc($result);
            
            // Verify password
            if (password_verify($password, $student['password'])) {
                // Set session variables
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
                $_SESSION['student_email'] = $student['email'];
                $_SESSION['student_role'] = 'student';
                
                // Redirect to student dashboard
                header("Location: student.php");
                exit();
            } else {
                $login_err = "Invalid email or password";
            }
        } else {
            $login_err = "Invalid email or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
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
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        .forgot-password a {
            color: #007bff;
            text-decoration: none;
        }
        .forgot-password a:hover {
            text-decoration: underline;
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
            <h2>Student Portal</h2>
            <p class="text-muted">Please login to your account</p>
        </div>
        
        <?php if (!empty($login_err)): ?>
            <div class="alert alert-danger"><?php echo $login_err; ?></div>
        <?php endif; ?>
        
        <form id="loginForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                    <input type="email" name="email" class="form-control" placeholder="Email Address" required autocomplete="new-email">
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
        </form>
        
        <!-- Added Forgot Password Link -->
        <div class="forgot-password">
            <a href="forgotpassword.php">Forgot Password?</a>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date("Y"); ?> Student Portal. All rights reserved.</p>
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
            $('input[type="email"], input[type="password"]').val('');
            
            // Disable browser's autocomplete
            $('input').attr('autocomplete', 'off');
            
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading overlay
                $('#loadingOverlay').addClass('active');
                
                // Submit form after 5 seconds
                setTimeout(function() {
                    $('#loginForm').off('submit').submit();
                }, 5000);
            });
        });
    </script>
</body>
</html>