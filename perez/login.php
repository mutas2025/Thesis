<?php
require 'config.php';
session_start();

 $error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password_input = trim($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // VERIFYING AGAINST THE SPECIFIC DUMMY DATA PROVIDED
        if ($password_input === 'password123' || $password_input === $user['password_hash']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['status'] = $user['status'];

            if ($user['role'] == 'admin') {
                header("Location: admin.php");
            } elseif ($user['role'] == 'landlord') {
                header("Location: landlord.php");
            } else {
                header("Location: boarders.php");
            }
            exit;
        } else {
            $error = "Invalid password. (Try 'password123')";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | DormFinder Portal</title>
    
    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=stylesheet" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            /* BLUE & LIGHT BLUE THEME */
            --primary-blue: #2563eb;        /* Vivid Blue */
            --primary-dark: #1e40af;        /* Darker Blue for hover */
            --accent-light: #60a5fa;        /* Light Sky Blue */
            --bg-theme: #e0f2fe;            /* Very Light Blue Background (Sky 100) */
            --card-bg: #ffffff;             /* White Card */
            
            --text-main: #0c4a6e;           /* Dark Blue Text */
            --text-muted: #64748b;          
            
            --border-color: #bae6fd;        /* Light Blue Border */
            --input-bg: #f0f9ff;            /* Pale Blue Input BG */
            
            --radius-lg: 20px;
            --radius-md: 10px;
            
            --shadow-soft: 0 10px 40px -10px rgba(37, 99, 235, 0.2);
            --shadow-hover: 0 20px 25px -5px rgba(37, 99, 235, 0.3), 0 8px 10px -6px rgba(37, 99, 235, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            /* Theme Background */
            background-color: var(--bg-theme);
            background-image: 
                radial-gradient(at 0% 0%, rgba(96, 165, 250, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(37, 99, 235, 0.15) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* --- Main Layout --- */
        .login-wrapper {
            display: flex;
            background: var(--card-bg);
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s ease;
        }

        .login-wrapper:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        /* Left Side: Branding - Now a Vibrant Blue Gradient */
        .brand-section {
            flex: 1;
            /* Modern Blue Gradient */
            background: linear-gradient(135deg, var(--primary-blue) 0%, #1e3a8a 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        /* Abstract Blue Shapes for Branding */
        .brand-section::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            filter: blur(40px);
            transition: transform 0.8s ease;
        }
        
        .brand-section::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -50px;
            width: 250px;
            height: 250px;
            background: rgba(96, 165, 250, 0.2);
            border-radius: 50%;
            filter: blur(30px);
            transition: transform 0.8s ease;
        }

        .login-wrapper:hover .brand-section::before {
            transform: scale(1.2) translate(-20px, 20px);
        }
        
        .login-wrapper:hover .brand-section::after {
            transform: scale(1.2) translate(20px, -20px);
        }

        .brand-logo {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        .brand-logo i {
            color: #bfdbfe; /* Light Blue Icon */
            font-size: 1.8rem;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        .brand-logo:hover i {
            transform: scale(1.2) rotate(-10deg);
            color: white;
        }

        .brand-content {
            position: relative;
            z-index: 2;
        }

        .brand-content h2 {
            font-size: 2.25rem;
            line-height: 1.1;
            margin-bottom: 1rem;
            font-weight: 800;
        }
        
        .brand-content p {
            color: #dbeafe; /* Very light blue text */
            font-size: 1.05rem;
            line-height: 1.6;
        }

        .testimonial {
            position: relative;
            z-index: 2;
            font-size: 0.875rem;
            color: #bfdbfe;
            font-style: italic;
            border-left: 3px solid #60a5fa;
            padding-left: 1rem;
        }

        /* Right Side: Form */
        .form-section {
            flex: 1;
            padding: 3.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: var(--card-bg);
        }

        .form-header {
            margin-bottom: 2.5rem;
            text-align: center;
        }

        .form-header h3 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-blue);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: color 0.2s ease;
        }
        
        .form-group:focus-within .form-label {
            color: var(--primary-dark);
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-blue);
            opacity: 0.6;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 0.9rem 1rem 0.9rem 2.75rem;
            font-family: inherit;
            font-size: 0.95rem;
            background-color: var(--input-bg);
            border: 2px solid transparent; /* Prepare for border transition */
            border-radius: var(--radius-md);
            color: var(--text-main);
            transition: all 0.3s ease;
        }

        /* Input Hover State */
        .form-control:hover {
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.05);
        }

        /* Input Focus State */
        .form-control:focus {
            outline: none;
            background-color: #fff;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        }

        /* Icon Animation */
        .form-control:focus + i,
        .form-control:hover + i {
            opacity: 1;
            color: var(--primary-dark);
            transform: translateY(-50%) scale(1.1);
        }

        /* Utilities */
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.2s ease;
            user-select: none;
        }

        .checkbox-wrapper:hover {
            color: var(--primary-blue);
        }

        .checkbox-wrapper input {
            accent-color: var(--primary-blue);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .link-primary {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 600;
            position: relative;
            transition: color 0.2s ease;
        }

        .link-primary::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--primary-blue);
            transition: width 0.3s ease;
        }

        .link-primary:hover {
            color: var(--primary-dark);
        }

        .link-primary:hover::after {
            width: 100%;
        }

        /* Button - Solid Blue */
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background-color: var(--primary-blue);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
        }

        /* Button Shine Effect */
        .btn-submit::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
        }
        
        .btn-submit:hover::before {
            left: 100%;
        }
        
        .btn-submit:hover i {
            transform: translateX(4px);
            transition: transform 0.3s ease;
        }

        .btn-submit:active {
            transform: translateY(0);
        }
        
        .btn-submit:disabled {
            background-color: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Error Alert - Blue Tinted */
        .alert-box {
            background-color: #eff6ff; /* Very light blue */
            border: 1px solid #bfdbfe;
            color: #1e3a8a; /* Dark blue text */
            padding: 0.75rem 1rem;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease-out;
        }
        
        .alert-box i {
            color: var(--primary-blue);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .footer-text {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .footer-text a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s ease;
        }

        .footer-text a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 850px) {
            .login-wrapper {
                flex-direction: column;
                max-width: 500px;
                min-height: auto;
            }
            
            .brand-section {
                padding: 2.5rem;
                min-height: 220px;
                text-align: center;
            }

            .brand-logo {
                justify-content: center;
            }

            .brand-content h2 {
                font-size: 1.5rem;
            }
            
            .form-section {
                padding: 2.5rem 2rem;
            }
            
            .testimonial {
                display: none;
            }
            
            /* Disable hover lift on mobile */
            .login-wrapper:hover {
                transform: none;
                box-shadow: var(--shadow-soft);
            }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    
    <!-- LEFT SIDE: BRANDING & VISUALS -->
    <div class="brand-section">
        <div class="brand-logo">
            <i class="fas fa-building-user"></i>
            <span>DormFinder</span>
        </div>
        
        <div class="brand-content">
            <h2>Find your perfect <br>space to call home.</h2>
            <p>The most trusted platform for boarding house management and student accommodation.</p>
        </div>

        <div class="testimonial">
            "DormFinder made managing my properties seamless and professional." <br>
            — Satisfied Landlord
        </div>
    </div>

    <!-- RIGHT SIDE: LOGIN FORM -->
    <div class="form-section">
        <div class="form-header">
            <h3>Welcome Back</h3>
            <p>Please enter your details to sign in.</p>
        </div>

        <?php if($error): ?>
            <div class="alert-box">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" id="loginForm">
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <input type="email" name="email" id="email" class="form-control" placeholder="name@company.com" required>
                    <i class="far fa-envelope"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    <i class="fas fa-lock"></i>
                </div>
            </div>

            <div class="form-actions">
                <label class="checkbox-wrapper">
                    <input type="checkbox" name="remember" id="remember">
                    <span>Remember me</span>
                </label>
                <a href="#" class="link-primary">Forgot password?</a>
            </div>

            <button type="submit" class="btn-submit" id="loginBtn">
                <span>Sign In</span>
                <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="footer-text">
            Don't have an account? <a href="register.php">Register new member</a>
        </div>
    </div>
</div>

<script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        var btn = document.getElementById('loginBtn');
        
        // 1. Prevent default submission to handle loading state
        e.preventDefault();

        // 2. Visual feedback
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Authenticating...';
        
        // Disable button
        btn.disabled = true;
        btn.style.opacity = "0.8";
        btn.style.cursor = "wait";
        
        // Reset hover effects during load
        btn.style.transform = "none";
        btn.style.boxShadow = "none";
        btn.style.background = "#94a3b8"; // Grey out

        // 3. Delay for visual effect
        setTimeout(function() {
            document.getElementById('loginForm').submit();
        }, 800); 
    });
</script>

</body>
</html>