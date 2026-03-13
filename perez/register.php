<?php
session_start();
require 'config.php';

 $error = '';
 $success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']); 
    $password = trim($_POST['password']);
    $role = trim($_POST['role']); 

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($phone_number)) {
        $error = "All fields are required.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email is already registered.";
        } else {
            // Storing plain text for demo compatibility
            $hash = $password; 

            // Updated SQL to include phone_number
            $sql = "INSERT INTO Users (first_name, last_name, email, phone_number, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?, 'active')";
            $stmt = $pdo->prepare($sql);
            
            try {
                // Updated execute array to include phone_number
                $stmt->execute([$first_name, $last_name, $email, $phone_number, $hash, $role]);
                $success = "Registration successful! Please <a href='login.php' style='text-decoration: underline; font-weight: 700; color: var(--primary-dark);'>login here</a>.";
            } catch (PDOException $e) {
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | DormFinder Portal</title>
    
    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=stylesheet" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            /* BLUE & LIGHT BLUE THEME (Matching Login) */
            --primary-blue: #2563eb;        /* Vivid Blue */
            --primary-dark: #1e40af;        /* Darker Blue for hover */
            --accent-light: #60a5fa;        /* Light Sky Blue */
            --bg-theme: #e0f2fe;            /* Very Light Blue Background */
            --card-bg: #ffffff;             /* White Card */
            
            --text-main: #0c4a6e;           /* Dark Blue Text */
            --text-muted: #64748b;          
            
            --border-color: #bae6fd;        /* Light Blue Border */
            --input-bg: #f0f9ff;            /* Pale Blue Input BG */
            
            --radius-lg: 20px;
            --radius-md: 10px;
            
            --shadow-soft: 0 10px 40px -10px rgba(37, 99, 235, 0.2);
            --shadow-hover: 0 20px 25px -5px rgba(37, 99, 235, 0.3), 0 8px 10px -6px rgba(37, 99, 235, 0.1);
            
            /* Alert Colors */
            --error-bg: #fef2f2;
            --error-text: #991b1b;
            --error-border: #fecaca;
            --success-bg: #eff6ff; 
            --success-text: #1e40af;
            --success-border: #bfdbfe;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
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
        .register-wrapper {
            display: flex;
            background: var(--card-bg);
            width: 100%;
            max-width: 1100px;
            min-height: 700px; /* Increased height for description content */
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-soft);
            overflow: hidden;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s ease;
        }

        .register-wrapper:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        /* Left Side: Branding & Description */
        .brand-section {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-blue) 0%, #1e3a8a 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        /* Abstract Background Blobs */
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
        }
        
        .brand-section::after {
            content: '';
            position: absolute;
            bottom: 100px;
            left: -50px;
            width: 250px;
            height: 250px;
            background: rgba(96, 165, 250, 0.2);
            border-radius: 50%;
            filter: blur(30px);
        }

        .brand-logo {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
        }

        .brand-logo i {
            color: #bfdbfe;
            font-size: 1.8rem;
            transition: transform 0.3s ease;
        }
        
        .brand-logo:hover i {
            transform: scale(1.1) rotate(-5deg);
            color: white;
        }

        /* System Description Container */
        .system-description {
            position: relative;
            z-index: 2;
            margin-top: 1rem;
        }

        .system-description h2 {
            font-size: 2rem;
            line-height: 1.2;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        
        .system-description p.intro {
            color: #dbeafe;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            max-width: 90%;
        }

        /* Feature List */
        .features-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .feature-item:hover {
            transform: translateX(5px);
            background: rgba(255, 255, 255, 0.1);
        }

        .feature-icon {
            background: rgba(96, 165, 250, 0.2);
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .feature-text h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #fff;
        }

        .feature-text p {
            font-size: 0.85rem;
            color: #bfdbfe;
            line-height: 1.4;
        }

        /* Right Side: Form */
        .form-section {
            flex: 1.2;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            background-color: var(--card-bg);
            overflow-y: auto;
        }

        .form-header {
            margin-bottom: 2rem;
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

        /* Form Elements (Simplified for brevity, copied from previous styles) */
        .form-row { display: flex; gap: 1rem; }
        .form-col { flex: 1; }
        .form-group { margin-bottom: 1.25rem; position: relative; }
        
        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-blue);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .form-control, select.form-control {
            width: 100%;
            padding: 0.9rem 1rem;
            font-family: inherit;
            font-size: 0.95rem;
            background-color: var(--input-bg);
            border: 2px solid transparent;
            border-radius: var(--radius-md);
            color: var(--text-main);
            transition: all 0.3s ease;
            appearance: none;
        }
        
        select.form-control {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%232563eb' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.2em 1.2em;
        }

        .form-control:hover, select.form-control:hover {
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.05);
        }

        .form-control:focus, select.form-control:focus {
            outline: none;
            background-color: #fff;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        }

        /* Alerts */
        .alert {
            padding: 0.85rem 1rem;
            border-radius: var(--radius-md);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease-out;
        }

        .alert.error {
            background-color: var(--error-bg);
            color: var(--error-text);
            border: 1px solid var(--error-border);
        }
        
        .alert.success {
            background-color: var(--success-bg);
            color: var(--success-text);
            border: 1px solid var(--success-border);
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Button */
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
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
        }

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
        
        .card-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .link {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s ease;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .register-wrapper {
                flex-direction: column;
                min-height: auto;
                max-width: 500px;
            }
            .brand-section {
                padding: 2rem;
                min-height: auto;
                text-align: left;
            }
            .feature-item {
                flex-direction: row;
                align-items: center;
            }
            .form-section {
                padding: 2.5rem 2rem;
            }
            .form-row { flex-direction: column; gap: 0; }
        }
    </style>
</head>
<body>

<div class="register-wrapper">
    
    <!-- LEFT SIDE: BRANDING & SYSTEM DESCRIPTION -->
    <div class="brand-section">
        <div class="brand-logo">
            <i class="fas fa-building-user"></i>
            <span>DormFinder</span>
        </div>
        
        <div class="system-description">
            <h2>Boarding House <br>Management System</h2>
            <p class="intro">
                A comprehensive digital solution designed to streamline the boarding experience. 
                We connect students with safe housing and empower landlords with efficient management tools.
            </p>

            <ul class="features-list">
                <li class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="feature-text">
                        <h4>Safe & Verified</h4>
                        <p>All listings are verified to ensure student safety and compliance.</p>
                    </div>
                </li>

                <li class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-laptop-house"></i>
                    </div>
                    <div class="feature-text">
                        <h4>Digital Convenience</h4>
                        <p>Manage payments, maintenance requests, and bills online seamlessly.</p>
                    </div>
                </li>

                <li class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="feature-text">
                        <h4>Efficient Management</h4>
                        <p>Landlords can track occupancy, finances, and tenant status in real-time.</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!-- RIGHT SIDE: REGISTRATION FORM -->
    <div class="form-section">
        <div class="form-header">
            <h3>Create Account</h3>
            <p>Join our community today.</p>
        </div>

        <?php if($error): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $success; ?></span>
            </div>
        <?php endif; ?>
        
        <?php if(!$success): ?>
        <form action="" method="POST" id="registerForm">
            <div class="form-row">
                <div class="form-col form-group">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" name="first_name" id="first_name" class="form-control" placeholder="Drunreb" required>
                </div>
                <div class="form-col form-group">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Perez" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="perez@admin.com" required>
            </div>

            <div class="form-group">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="tel" name="phone_number" id="phone_number" class="form-control" placeholder="e.g. 09123456789" required>
            </div>

            <div class="form-group">
                <label for="role" class="form-label">I am a...</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="boarder" selected>Boarder</option>
                    <option value="landlord">Landlord</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Create a password" required>
            </div>

            <button type="submit" class="btn-submit" id="registerBtn">
                <span>Create Account</span>
                <i class="fas fa-user-plus"></i>
            </button>
        </form>
        <?php endif; ?>

        <div class="card-footer">
            Already have an account? <a href="login.php" class="link">Sign In</a>
        </div>
    </div>
</div>

<script>
    document.getElementById('registerForm')?.addEventListener('submit', function(e) {
        var btn = document.getElementById('registerBtn');
        e.preventDefault();
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Creating Account...';
        btn.disabled = true;
        btn.style.opacity = "0.8";
        btn.style.cursor = "wait";
        btn.style.transform = "none";
        btn.style.background = "#94a3b8";
        setTimeout(function() {
            document.getElementById('registerForm').submit();
        }, 800); 
    });
</script>

</body>
</html>