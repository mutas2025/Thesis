<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

/**
 * Redirect user based on their role
 * @param string $role
 */
function redirectByRole($role) {
    switch ($role) {
        case 'ADMIN':
            redirect('admin.php');
            break;
        case 'CUSTOMER':
            redirect('customer.php');
            break;
        case 'MERCHANT':
            redirect('merchant.php');
            break;
        default:
            // Fallback if role is undefined
            redirect('index.php');
            break;
    }
    exit; // Ensure script stops after redirect
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    redirectByRole($_SESSION['role']);
}

 $error_message = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Retrieve and Trim Inputs
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic Validation
    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        // 2. Prepare SQL Statement (Prevents SQL Injection)
        $sql = "SELECT user_id, full_name, role, status, password_hash FROM users WHERE email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters ("s" means the parameter is a string)
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // 3. Verify Password
                if (password_verify($password, $user['password_hash'])) {
                    
                    // 4. Check Account Status
                    if ($user['status'] === 'SUSPENDED') {
                        $error_message = "Your account has been suspended. Please contact support.";
                    } else {
                        // 5. Regenerate Session ID to prevent Session Fixation attacks
                        session_regenerate_id(true);

                        // Set Session Variables
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['role'] = $user['role'];

                        // 6. Redirect to Dashboard
                        redirectByRole($user['role']);
                    }
                } else {
                    $error_message = "Invalid email or password."; // Generic message for security
                }
            } else {
                $error_message = "Invalid email or password."; // Generic message for security
            }
            $stmt->close();
        } else {
            $error_message = "System error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login - UtilitySYS</title>
    
    <!-- Using Google Fonts for a modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Color Palette */
            --primary-color: #008751; /* Philippine Green/Money color theme */
            --primary-hover: #006b3f;
            --text-dark: #1f2937;
            --text-gray: #6b7280;
            --bg-light: #f3f4f6;
            --white: #ffffff;
            --error-bg: #fee2e2;
            --error-text: #b91c1c;
            --input-border: #d1d5db;
            --input-focus: #008751;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Layout Container */
        .login-wrapper {
            display: flex;
            width: 100%;
            max-width: 1000px;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            margin: 20px;
            min-height: 600px;
        }

        /* Left Side - Visual/Image */
        .login-visual {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color), #34d399);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 40px;
            text-align: center;
            display: none; /* Hidden on mobile by default */
        }

        .login-visual h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 800;
        }
        
        /* Styling for the large logo on the green side */
        .visual-logo-img {
            width: 140px;
            height: auto;
            margin-bottom: 25px;
            background: rgba(255, 255, 255, 0.9); 
            padding: 15px;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .login-visual p {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        /* Decorative circles */
        .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
        }
        .c1 { width: 300px; height: 300px; top: -50px; left: -50px; }
        .c2 { width: 200px; height: 200px; bottom: 50px; right: -20px; }

        /* Right Side - Form */
        .login-form-container {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .header-logo {
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Styling for the small nav icon in the header */
        .header-logo-img {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .header-logo h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        
        .header-logo span.text {
            color: var(--text-dark);
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px 12px 45px; /* Left padding for icon */
            font-size: 1rem;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--input-focus);
            box-shadow: 0 0 0 4px rgba(0, 135, 81, 0.1);
        }

        /* Icons inside input */
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
            pointer-events: none;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
        }
        
        .password-toggle:hover {
            color: var(--text-dark);
        }

        /* Button & Loading Bar Styles */
        .btn-submit-wrapper {
            position: relative;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
            position: relative;
            overflow: hidden;
            z-index: 2;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .btn-submit:disabled {
            background-color: #9ca3af; /* Gray when disabled */
            color: white;
            cursor: not-allowed;
        }

        /* The Loading Bar Line */
        .loading-line {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            background-color: rgba(255,255,255,0.8);
            width: 0%;
            transition: width 0.1s linear;
            z-index: 3;
        }

        /* Active loading animation class */
        .loading-active .loading-line {
            animation: loadProgress 1.5s ease-in-out forwards;
        }

        @keyframes loadProgress {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }

        /* Error Message Styling */
        .alert-danger {
            background-color: var(--error-bg);
            color: var(--error-text);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease-out;
            border-left: 4px solid var(--error-text);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Register Link Styling */
        .register-link {
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-gray);
            margin-top: 10px;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        /* SVG Icons */
        svg {
            width: 20px;
            height: 20px;
        }

        /* Responsive Design */
        @media (min-width: 768px) {
            .login-visual {
                display: flex;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <!-- Visual Side (Hidden on Mobile) -->
        <div class="login-visual">
            <div class="circle c1"></div>
            <div class="circle c2"></div>
            <div>
                <!-- LOGO ADDED HERE -->
                <img src="utilitySYS.png" alt="UtilitySYS Logo" class="visual-logo-img">
                
                <h2>UtilitySYS</h2>
                <p>Secure, fast, and reliable bill payments.<br>Manage your utilities with ease.</p>
            </div>
        </div>

        <!-- Form Side -->
        <div class="login-form-container">
            <div class="header-logo">
                <!-- LOGO ADDED HERE -->
                <img src="utilitySYS.png" alt="UtilitySYS" class="header-logo-img">
                <h1>Utility<span class="text">SYS</span></h1>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert-danger" id="errorBox">
                    <!-- Error Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <form method="post" action="" id="loginForm" autocomplete="off">
                
                <!-- Email Input -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-wrapper">
                        <!-- Email Icon -->
                        <div class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </div>
                        <input type="email" name="email" id="email" class="form-control" placeholder="name@company.com" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrapper">
                        <!-- Lock Icon -->
                        <div class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                        
                        <!-- Eye Toggle -->
                        <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeOffIcon" style="display:none;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <div class="btn-submit-wrapper">
                    <button type="submit" class="btn-submit" id="loginBtn">Sign In</button>
                    <div class="loading-line" id="loadingLine"></div>
                </div>
            </form>

            <!-- NEW: Register Link -->
            <div class="register-link">
                Don't have an account? <a href="register.php">Create one now</a>
            </div>
        </div>
    </div>

    <!-- JS for Password Toggle and Loading Animation -->
    <script>
        // Password Visibility Toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                passwordInput.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        }

        // Handle Loading Animation on Submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            // Check if button is already disabled
            const btn = document.getElementById('loginBtn');
            const wrapper = document.querySelector('.btn-submit-wrapper');

            if (btn.disabled) return;

            e.preventDefault(); // Stop immediate submit to show animation

            // Update UI State
            btn.disabled = true;
            btn.innerText = "Authenticating...";
            wrapper.classList.add('loading-active');

            // Wait 800ms for the visual feedback to be seen by the user
            // then submit the form programmatically
            setTimeout(() => {
                this.submit();
            }, 800);
        });

        // Auto-fade out error messages
        window.addEventListener('load', function() {
            const errorBox = document.getElementById('errorBox');
            if (errorBox) {
                setTimeout(() => {
                    errorBox.style.transition = "opacity 0.5s ease";
                    errorBox.style.opacity = "0";
                    setTimeout(() => {
                        errorBox.style.display = "none";
                    }, 5000);
                }, 5000); // Error disappears after 5 seconds
            }
        });
    </script>
</body>
</html>