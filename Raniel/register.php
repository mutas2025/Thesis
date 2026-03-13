<?php
require_once 'config.php';

// Assuming you have a sanitize function, otherwise we use standard trimming.
// For security, it is better to use Prepared Statements (shown below).
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(trim($data));
    }
}

 $errors = [];
 $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = trim($_POST['role']);
    $phone = trim($_POST['phone'] ?? ''); // Made phone optional in form, but keeping var
    $business_name = trim($_POST['business_name'] ?? '');
    // $service_type removed

    // 1. Basic Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $errors[] = "All required fields must be filled.";
    }
    
    // 2. Check if email exists (Using Prepared Statement for security)
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Email is already registered.";
    }
    $check->close();

    // 3. Merchant Specific Validation
    if ($role == 'MERCHANT') {
        // Removed service_type check, only business_name is required
        if (empty($business_name)) {
            $errors[] = "Business Name is required for Merchants.";
        }
    }

    if (empty($errors)) {
        // 4. Insert User (Prepared Statement)
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $conn->prepare("INSERT INTO users (role, full_name, email, password_hash, phone, status) VALUES (?, ?, ?, ?, ?, 'ACTIVE')");
        $stmt->bind_param("sssss", $role, $full_name, $email, $password_hash, $phone);
        
        if ($stmt->execute()) {
            // 5. Insert Merchant Details if applicable
            if ($role == 'MERCHANT') {
                $user_id = $conn->insert_id;
                // Removed service_type from database insertion
                $mStmt = $conn->prepare("INSERT INTO merchants (user_id, business_name, status) VALUES (?, ?, 'PENDING')");
                $mStmt->bind_param("is", $user_id, $business_name);
                $mStmt->execute();
                $mStmt->close();
            }
            $success = "Registration successful! <a href='login.php' style='text-decoration:underline; font-weight:600;'>Login here</a>";
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Utilities Pay</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #008751; /* Philippine Green */
            --primary-hover: #006b3f;
            --text-dark: #1f2937;
            --text-gray: #6b7280;
            --bg-light: #f3f4f6;
            --white: #ffffff;
            --input-border: #d1d5db;
            --input-focus: #008751;
            --error-bg: #fee2e2;
            --error-text: #b91c1c;
            --success-bg: #d1fae5;
            --success-text: #065f46;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: var(--white);
            width: 100%;
            max-width: 900px;
            border-radius: 20px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            display: flex;
            overflow: hidden;
            flex-direction: row;
        }

        /* Left Side - Visual */
        .register-visual {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color), #34d399);
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            display: none; /* Hidden on mobile */
        }

        /* --- NEW: Visual Logo Styling --- */
        .visual-logo-img {
            width: 140px;
            height: auto;
            margin-bottom: 25px;
            /* White background card for the logo to pop against the green gradient */
            background: rgba(255, 255, 255, 0.9); 
            padding: 15px;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .register-visual h2 { font-size: 2rem; margin-bottom: 10px; font-weight: 700; }
        .register-visual p { font-size: 1rem; opacity: 0.9; line-height: 1.5; max-width: 80%; }

        /* Right Side - Form */
        .register-form-section {
            flex: 1.2;
            padding: 50px;
            overflow-y: auto;
            max-height: 90vh;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        /* --- NEW: Header Logo Styling --- */
        .header-logo-img {
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .header-logo h1 {
            color: var(--primary-color);
            font-size: 1.6rem;
            font-weight: 800;
            margin: 0;
        }
        
        .header-logo span.text { color: var(--text-dark); }

        .form-group { margin-bottom: 20px; position: relative; }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--input-focus);
            box-shadow: 0 0 0 3px rgba(0, 135, 81, 0.1);
        }

        select.form-control { background-color: white; cursor: pointer; }

        /* Role Toggle Styling */
        .role-toggle {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .role-option {
            flex: 1;
            position: relative;
        }

        .role-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .role-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px;
            border: 2px solid var(--input-border);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--text-gray);
            font-weight: 600;
        }

        .role-card i { font-size: 1.5rem; margin-bottom: 5px; }

        .role-option input[type="radio"]:checked + .role-card {
            border-color: var(--primary-color);
            background-color: rgba(0, 135, 81, 0.05);
            color: var(--primary-color);
        }

        /* Merchant Fields Animation */
        #merchantFields {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: all 0.4s ease-in-out;
            transform: translateY(-10px);
        }

        #merchantFields.visible {
            max-height: 200px; /* Adjusted height since one field removed */
            opacity: 1;
            transform: translateY(0);
            margin-bottom: 20px;
            padding: 20px;
            background-color: #f9fafb;
            border-radius: 12px;
            border: 1px dashed var(--primary-color);
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }

        .btn-register:hover { background-color: var(--primary-hover); }

        .login-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: var(--text-gray);
        }
        .login-link a { color: var(--primary-color); text-decoration: none; font-weight: 600; }
        .login-link a:hover { text-decoration: underline; }

        /* Alerts */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            animation: slideDown 0.3s ease;
        }
        .alert-danger { background-color: var(--error-bg); color: var(--error-text); border-left: 4px solid var(--error-text); }
        .alert-success { background-color: var(--success-bg); color: var(--success-text); border-left: 4px solid var(--success-text); }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (min-width: 768px) {
            .register-visual { display: flex; }
        }
    </style>
</head>
<body>

    <div class="register-container">
        <!-- Left Side: Branding -->
        <div class="register-visual">
            <!-- LOGO ADDED HERE -->
            <img src="utilitySYS.png" alt="UtilitySYS Logo" class="visual-logo-img">
            
            <h2>Join Utility SYS</h2>
            <p>Create an account to manage your bills or start accepting payments for your business.</p>
        </div>

        <!-- Right Side: Form -->
        <div class="register-form-section">
            <div class="header-logo">
                <!-- LOGO ADDED HERE -->
                <img src="utilitySYS.png" alt="UtilitySYS Logo" class="header-logo-img">
                
                <h1>Utility<span class="text">Sys</span></h1>
            </div>

            <?php if(!empty($errors)): ?>
                <?php foreach($errors as $e): ?>
                    <div class="alert alert-danger"><?php echo $e; ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php else: ?>

                <form method="post" id="registerForm">
                    <!-- Role Selection -->
                    <div class="form-label">I want to register as:</div>
                    <div class="role-toggle">
                        <label class="role-option">
                            <input type="radio" name="role" value="CUSTOMER" checked>
                            <div class="role-card">
                                <i>C</i>
                                <span>Customer</span>
                            </div>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="MERCHANT" id="merchantRadio">
                            <div class="role-card">
                                <i>M</i>
                                <span>Merchant</span>
                            </div>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" placeholder="Juan Dela Cruz" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="juan@example.com" required>
                    </div>

                    <!-- Merchant Specific Fields (Animated) -->
                    <div id="merchantFields">
                        <h4 style="margin-bottom:15px; font-size:0.9rem; color:var(--primary-color);">Business Details</h4>
                        <div class="form-group">
                            <input type="text" name="business_name" class="form-control" placeholder="Business Name (e.g. Maynilad)">
                        </div>
                        <!-- Service Type Input Removed -->
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Create a strong password" required>
                    </div>

                    <button type="submit" class="btn-register">Create Account</button>
                </form>

                <a href="login.php" class="login-link">Already have an account? Login</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Toggle Merchant Fields Animation
        const roleRadios = document.querySelectorAll('input[name="role"]');
        const merchantFields = document.getElementById('merchantFields');
        
        // Initial check in case page reloads with Merchant selected
        document.addEventListener('DOMContentLoaded', () => {
            const selectedRole = document.querySelector('input[name="role"]:checked').value;
            if (selectedRole === 'MERCHANT') {
                merchantFields.classList.add('visible');
            }
        });

        roleRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'MERCHANT') {
                    merchantFields.classList.add('visible');
                } else {
                    merchantFields.classList.remove('visible');
                }
            });
        });
    </script>
</body>
</html>