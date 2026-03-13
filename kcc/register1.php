<?php
// register.php
session_start();
require 'config.php';

 $message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure hashing

    // Check if user exists
    $check = $conn->query("SELECT id FROM admin_users WHERE username = '$user'");
    if ($check->num_rows > 0) {
        $message = "Username already exists!";
    } else {
        $sql = "INSERT INTO admin_users (username, password) VALUES ('$user', '$pass')";
        if ($conn->query($sql) === TRUE) {
            // Redirect to login after short delay or provide link
            $message = "Registration successful! <a href='login.php' style='text-decoration:underline; font-weight:bold;'>Login here</a>";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
 $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin - KCC Grading System</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- CSS Variables & Reset --- */
        :root {
            --primary-color: #2c3e50;
            --primary-dark: #1a252f;
            --accent-color: #3498db;
            --accent-hover: #2980b9;
            --bg-light: #f4f6f9;
            --white: #ffffff;
            --text-dark: #2c3e50;
            --text-muted: #7f8c8d;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --radius: 8px;
            --shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* --- Layout Container --- */
        .login-container {
            width: 100%;
            max-width: 1000px;
            height: 600px;
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow);
            display: flex;
            overflow: hidden;
            margin: 20px;
        }

        /* --- Left Side: Visual/Branding --- */
        .login-visual {
            flex: 1;
            background: linear-gradient(135deg, var(--accent-color), var(--accent-hover));
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: var(--white);
            padding: 3rem;
            text-align: center;
            overflow: hidden;
        }

        /* Decorative Circle */
        .login-visual::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            bottom: -50px;
            right: -50px;
        }

        .visual-content {
            position: relative;
            z-index: 2;
        }

        .visual-content i {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }

        .visual-content h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .visual-content p {
            font-size: 1rem;
            opacity: 0.8;
            line-height: 1.6;
            max-width: 300px;
        }

        .back-link {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-link:hover {
            color: var(--white);
        }

        /* --- Right Side: Form --- */
        .login-form-section {
            flex: 1;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--white);
        }

        .form-header {
            margin-bottom: 2.5rem;
        }

        .form-header h3 {
            font-size: 1.75rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.8rem;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            outline: none;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            background-color: var(--white);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn-block {
            width: 100%;
            padding: 0.875rem;
            background-color: var(--accent-color);
            color: var(--white);
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-block:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.2);
        }

        .form-footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.9rem;
        }

        .form-footer a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        /* --- Alert Styling --- */
        .alert {
            padding: 0.75rem 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* --- Responsive Design --- */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                height: auto;
                max-width: 500px;
            }

            .login-visual {
                padding: 2rem;
                min-height: 200px;
            }

            .visual-content i {
                font-size: 3rem;
                margin-bottom: 0.5rem;
            }

            .visual-content h2 {
                font-size: 1.5rem;
            }

            .visual-content p {
                display: none; 
            }

            .login-form-section {
                padding: 2.5rem 2rem;
            }

            .back-link {
                position: relative;
                bottom: auto;
                left: auto;
                transform: none;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        
        <!-- Left Side: Branding -->
        <div class="login-visual">
            <div class="visual-content">
                <i class="fas fa-user-plus"></i>
                <h2>Join the Team</h2>
                <p>Create an administrative account to manage student records and grading systems efficiently.</p>
            </div>
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>

        <!-- Right Side: Registration Form -->
        <div class="login-form-section">
            
            <div class="form-header">
                <h3>Create Account</h3>
                <p>Fill in your details to get started.</p>
            </div>

            <?php if($message): ?>
                <?php 
                    // Simple check to see if it's a success message based on content
                    $isSuccess = strpos($message, 'successful') !== false;
                    $alertClass = $isSuccess ? 'alert-success' : 'alert-warning';
                    $iconClass = $isSuccess ? 'fa-check-circle' : 'fa-exclamation-triangle';
                ?>
                <div class="alert <?php echo $alertClass; ?>">
                    <i class="fas <?php echo $iconClass; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Choose a username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Create a password" required>
                    </div>
                </div>

                <button type="submit" class="btn-block">Register</button>
            </form>

            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>

        </div>
    </div>

</body>
</html>