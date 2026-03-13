<?php
// Start session
session_start();
// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin.php");
    } elseif ($_SESSION['role'] == 'registrar') {
        header("Location: registrar/registrar.php");
    } elseif ($_SESSION['role'] == 'cashier') {
        header("Location: cashier/cashier.php");
    } elseif ($_SESSION['role'] == 'treasurer') {
        header("Location: treasurer/treasurer.php");
    } elseif ($_SESSION['role'] == 'counselor') {
        header("Location: counselor/counselor.php");
    }
    exit();
}
// Include config file
require_once 'config.php';
// Process login when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        $login_err = "Please enter both username and password.";
    } else {
        // Prepare SQL statement to prevent SQL injection
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                // Check if username exists
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $username, $hashed_password, $role);
                    if ($stmt->fetch()) {
                        // Verify password
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, start session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;
                            
                            // Redirect user based on role
                            if ($role == 'admin') {
                                header("Location: admin.php");
                            } elseif ($role == 'registrar') {
                                header("Location: registrar/registrar.php");
                            } elseif ($role == 'cashier') {
                                header("Location: cashier/cashier.php");
                            } elseif ($role == 'treasurer') {
                                header("Location: treasurer/treasurer.php");
                            } elseif ($role == 'counselor') {
                                header("Location: counselor/counselor.php");
                            }
                            exit();
                        } else {
                            // Password is not valid
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // Username doesn't exist
                    $login_err = "Invalid username or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            $stmt->close();
        }
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMART School System- Login</title>
    <link rel="icon" href="uploads/csr.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-image: url('uploads/csrschool.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        /* Add a semi-transparent overlay to improve readability */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }
        
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            z-index: 1;
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
        
        /* Updated Spinner Container */
        .loading-spinner {
            width: 120px;
            height: 120px;
            position: relative;
            margin: 0 auto 20px auto;
            /* Removed border and animation from parent container */
        }
        
        /* The rotating circle ring created with pseudo-element */
        .loading-spinner::before {
            content: "";
            position: absolute;
            /* Slightly larger than container to create a ring around the logo */
            top: -10px; 
            left: -10px; 
            right: -10px; 
            bottom: -10px;
            
            border-radius: 50%;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #007bff;
            
            animation: spin 1s linear infinite;
        }
        
        /* The Logo Styling inside the spinner */
        .loading-spinner img {
            width: 80px;
            height: auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
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
        
        @keyframes progress {
            0% { width: 0; }
            100% { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="uploads/csr.png" alt="School Logo">
            <h2>SMART School System</h2>
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
        </form>
        
        <div class="footer">
            <p>&copy; <?php echo date("Y"); ?> School Management System. All rights reserved.</p>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <!-- Logo moved inside the rotating circle div -->
            <img src="uploads/csr.png" alt="School Logo">
        </div>
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