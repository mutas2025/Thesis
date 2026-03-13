<?php
session_start();
require_once '../config.php';

// Check if student is already logged in
if (isset($_SESSION['student_id'])) {
    header("Location: student.php");
    exit();
}

// Initialize variables
$email = $reset_err = $reset_success = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Validate input
    if (empty($email)) {
        $reset_err = "Email is required";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $reset_err = "Invalid email format";
    } else {
        // Check if student exists with the provided email
        $query = "SELECT * FROM students WHERE email = '$email'";
        $result = mysqli_query($conn, $query);
        
        if ($result && mysqli_num_rows($result) == 1) {
            $student = mysqli_fetch_assoc($result);
            
            // Generate a unique token
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));
            
            // Store token in database
            // First, check if password_resets table exists, if not create it
            $tableCheckQuery = "SHOW TABLES LIKE 'password_resets'";
            $tableResult = mysqli_query($conn, $tableCheckQuery);
            
            if (mysqli_num_rows($tableResult) == 0) {
                // Create password_resets table
                $createTableQuery = "CREATE TABLE password_resets (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    email VARCHAR(100) NOT NULL,
                    token VARCHAR(255) NOT NULL,
                    expiry DATETIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                mysqli_query($conn, $createTableQuery);
            }
            
            // Delete any existing tokens for this email
            $deleteQuery = "DELETE FROM password_resets WHERE email = '$email'";
            mysqli_query($conn, $deleteQuery);
            
            // Insert new token
            $insertQuery = "INSERT INTO password_resets (email, token, expiry) VALUES ('$email', '$token', '$expiry')";
            mysqli_query($conn, $insertQuery);
            
            // Send reset email
            $resetLink = "http://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . "/resetpassword.php?token=" . $token;
            
            $to = $email;
            $subject = "Password Reset Request";
            $message = "
            <html>
            <head>
                <title>Password Reset Request</title>
            </head>
            <body>
                <p>Dear " . $student['first_name'] . " " . $student['last_name'] . ",</p>
                <p>We received a request to reset your password for your student account. Click the link below to reset your password:</p>
                <p><a href='" . $resetLink . "'>Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request a password reset, please ignore this email.</p>
                <p>Thank you,<br>
                Student Portal Team</p>
            </body>
            </html>
            ";
            
            // Always set content-type when sending HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: sjc-canlaon.com' . "\r\n";
            
            // Send email
            if (mail($to, $subject, $message, $headers)) {
                $reset_success = "A password reset link has been sent to your email address.";
            } else {
                $reset_err = "Failed to send reset email. Please try again later.";
            }
        } else {
            // Don't reveal if email exists or not for security
            $reset_success = "If your email address exists in our database, you will receive a password reset link shortly.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
        .forgot-container {
            max-width: 400px;
            width: 100%;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .forgot-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .forgot-header img {
            max-width: 100px;
            margin-bottom: 15px;
        }
        .forgot-header h2 {
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
        .back-to-login {
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <img src="../uploads/csr.png" alt="School Logo">
            <h2>Forgot Password</h2>
            <p class="text-muted">Enter your email address to reset your password</p>
        </div>
        
        <?php if (!empty($reset_err)): ?>
            <div class="alert alert-danger"><?php echo $reset_err; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($reset_success)): ?>
            <div class="alert alert-success"><?php echo $reset_success; ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                    <input type="email" name="email" class="form-control" placeholder="Email Address" value="<?php echo $email; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-reset">Send Reset Link</button>
            </div>
        </form>
        
        <div class="back-to-login">
            <a href="slogin.php" class="text-primary"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date("Y"); ?> Student Portal. All rights reserved.</p>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>