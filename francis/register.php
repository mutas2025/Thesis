<?php
require_once 'config.php';
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: admin.php');
    exit();
}

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $position = $conn->real_escape_string($_POST['position']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    $errors = [];
    
    if (empty($lastname) || empty($firstname) || empty($position) || empty($username) || empty($password)) {
        $errors[] = "All fields are required";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    // Check if username already exists
    $check_username = "SELECT id FROM employee WHERE username = '$username'";
    $result = $conn->query($check_username);
    
    if ($result && $result->num_rows > 0) {
        $errors[] = "Username already exists";
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new employee
        $insert_query = "INSERT INTO employee (lastname, firstname, position, username, password_hash) 
                         VALUES ('$lastname', '$firstname', '$position', '$username', '$password_hash')";
        
        if ($conn->query($insert_query)) {
            // Registration successful, redirect to login
            $_SESSION['success'] = "Registration successful! Please login with your new account.";
            header('Location: login.php');
            exit();
        } else {
            $errors[] = "Registration failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Accounting System</title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <style>
        body {
            height: 100vh;
            background-color: #f4f6f9;
        }
        .register-box {
            margin-top: 5%;
        }
        .register-card {
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .register-logo {
            font-weight: 300;
            font-size: 2rem;
        }
        .password-strength {
            height: 5px;
            margin-top: 5px;
            border-radius: 2px;
        }
    </style>
</head>
<body class="hold-transition register-page">
<div class="register-box">
    <div class="register-logo">
        <b>Accounting</b> System
    </div>
    
    <div class="card register-card">
        <div class="card-body register-card-body">
            <p class="login-box-msg">Register a new membership</p>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h5><i class="icon fas fa-ban"></i> Registration Error!</h5>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="register.php" method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Last Name</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="lastname" placeholder="Last Name" required>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-user"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>First Name</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="firstname" placeholder="First Name" required>
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <span class="fas fa-user"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Position</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="position" placeholder="Position" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-briefcase"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="password-strength" id="password-strength"></div>
                    <small class="form-text text-muted">Password must be at least 8 characters long</small>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="confirm_password" placeholder="Retype password" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="agreeTerms" name="terms" value="agree" required>
                            <label for="agreeTerms">
                                I agree to the <a href="#">terms</a>
                            </label>
                        </div>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </div>
                </div>
            </form>
            
            <div class="social-auth-links text-center">
                <p>- OR -</p>
                <a href="#" class="btn btn-block btn-primary">
                    <i class="fab fa-facebook mr-2"></i> Sign up using Facebook
                </a>
                <a href="#" class="btn btn-block btn-danger">
                    <i class="fab fa-google-plus mr-2"></i> Sign up using Google+
                </a>
            </div>
            
            <a href="login.php" class="text-center">I already have a membership</a>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-late@3.2/dist/js/adminlte.min.js"></script>

<script>
    $(document).ready(function() {
        // Password strength indicator
        $('#password').on('input', function() {
            var password = $(this).val();
            var strength = 0;
            
            // If password length is less than 8, return empty
            if (password.length < 8) {
                $('#password-strength').removeClass();
                $('#password-strength').addClass('password-strength bg-danger');
                $('#password-strength').css('width', '25%');
                return;
            }
            
            // Check password strength
            if (password.length > 7) strength += 1;
            if (password.match(/[a-z]+/)) strength += 1;
            if (password.match(/[A-Z]+/)) strength += 1;
            if (password.match(/[0-9]+/)) strength += 1;
            if (password.match(/[$@#&!]+/)) strength += 1;
            
            // Update strength indicator
            var strengthClass = '';
            var width = '';
            
            switch(strength) {
                case 0:
                case 1:
                    strengthClass = 'bg-danger';
                    width = '25%';
                    break;
                case 2:
                    strengthClass = 'bg-warning';
                    width = '50%';
                    break;
                case 3:
                    strengthClass = 'bg-info';
                    width = '75%';
                    break;
                case 4:
                case 5:
                    strengthClass = 'bg-success';
                    width = '100%';
                    break;
            }
            
            $('#password-strength').removeClass();
            $('#password-strength').addClass('password-strength ' + strengthClass);
            $('#password-strength').css('width', width);
        });
        
        // Form validation
        $('form').on('submit', function() {
            var password = $('#password').val();
            var confirmPassword = $('input[name="confirm_password"]').val();
            
            if (password !== confirmPassword) {
                alert('Passwords do not match');
                return false;
            }
            
            if (password.length < 8) {
                alert('Password must be at least 8 characters long');
                return false;
            }
            
            if (!$('#agreeTerms').prop('checked')) {
                alert('You must agree to the terms');
                return false;
            }
            
            return true;
        });
    });
</script>
</body>
</html>