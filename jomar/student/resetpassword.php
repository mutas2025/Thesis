<?php
session_start();
require_once '../config.php';

// Check if student is already logged in
if (isset($_SESSION['student_id'])) {
    header("Location: student.php");
    exit();
}

// Initialize variables
 $token = $_GET['token'] ?? '';
 $email = $password = $confirm_password = $reset_err = $reset_success = "";

// Check if token is provided
if (empty($token)) {
    $reset_err = "Invalid reset link.";
} else {
    // Check if token exists and is valid
    $query = "SELECT * FROM password_resets WHERE token = '$token' AND expiry > NOW()";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) == 1) {
        $reset_data = mysqli_fetch_assoc($result);
        $email = $reset_data['email'];
        
        // Process form submission
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validate input
            if (empty($password) || empty($confirm_password)) {
                $reset_err = "Please enter both password and confirm password.";
            } else if (strlen($password) < 8) {
                $reset_err = "Password must be at least 8 characters long.";
            } else if ($password !== $confirm_password) {
                $reset_err = "Passwords do not match.";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Update password in students table
                $updateQuery = "UPDATE students SET password = '$hashed_password' WHERE email = '$email'";
                
                if (mysqli_query($conn, $updateQuery)) {
                    // Delete the token
                    $deleteQuery = "DELETE FROM password_resets WHERE token = '$token'";
                    mysqli_query($conn, $deleteQuery);
                    
                    $reset_success = "Your password has been reset successfully. You can now login with your new password.";
                    
                    // Redirect to login page after 3 seconds
                    header("refresh:3;url=login.php");
                } else {
                    $reset_err = "Oops! Something went wrong. Please try again later.";
                }
            }
        }
    } else {
        $reset_err = "Invalid or expired reset link. Please request a new password reset.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
        .reset-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .reset-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .reset-header img {
            max-width: 100px;
            margin-bottom: 15px;
        }
        .reset-header h2 {
            color: #333;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn-reset {
            background-color: #007bff;
            border: none;
            padding: 10px;
            font-weight: 600;
            width: 100%;
        }
        .btn-reset:hover {
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
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 3px;
        }
        .strength-weak {
            background-color: #dc3545;
            width: 33%;
        }
        .strength-medium {
            background-color: #ffc107;
            width: 66%;
        }
        .strength-strong {
            background-color: #28a745;
            width: 100%;
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
    <div class="reset-container">
        <div class="reset-header">
            <img src="../uploads/csr.png" alt="School Logo">
            <h2>Reset Password</h2>
            <p class="text-muted">Enter your new password</p>
        </div>
        
        <?php if (!empty($reset_err)): ?>
            <div class="alert alert-danger"><?php echo $reset_err; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($reset_success)): ?>
            <div class="alert alert-success"><?php echo $reset_success; ?></div>
        <?php endif; ?>
        
        <?php if (empty($reset_success)): ?>
            <form id="resetForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?token=" . $token); ?>" method="post">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" name="password" id="password" class="form-control" placeholder="New Password" required>
                    </div>
                    <div id="passwordStrength" class="password-strength"></div>
                    <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                </div>
                
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm New Password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-reset">Reset Password</button>
                </div>
            </form>
        <?php endif; ?>
        
        <div class="footer">
            <p>&copy; <?php echo date("Y"); ?> Student Portal. All rights reserved.</p>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <img src="../uploads/csr.png" alt="School Logo" class="loading-logo">
        <div class="loading-spinner"></div>
        <div class="loading-text">Updating password...</div>
        <div class="loading-progress">
            <div class="loading-progress-bar"></div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#password').on('input', function() {
                var password = $(this).val();
                var strength = 0;
                
                // Check password strength
                if (password.length >= 8) strength += 1;
                if (password.match(/[a-z]+/)) strength += 1;
                if (password.match(/[A-Z]+/)) strength += 1;
                if (password.match(/[0-9]+/)) strength += 1;
                if (password.match(/[$@#&!]+/)) strength += 1;
                
                // Update strength indicator
                var strengthElement = $('#passwordStrength');
                strengthElement.removeClass();
                
                if (password.length > 0) {
                    if (strength <= 2) {
                        strengthElement.addClass('password-strength strength-weak');
                    } else if (strength <= 4) {
                        strengthElement.addClass('password-strength strength-medium');
                    } else {
                        strengthElement.addClass('password-strength strength-strong');
                    }
                }
            });
            
            $('#resetForm').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading overlay
                $('#loadingOverlay').addClass('active');
                
                // Submit form after 5 seconds
                setTimeout(function() {
                    $('#resetForm').off('submit').submit();
                }, 5000);
            });
        });
    </script>
</body>
</html>