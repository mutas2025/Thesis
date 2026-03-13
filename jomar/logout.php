<?php
// Start the session
session_start();

// Unset all session variables
 $_SESSION = array();

// Destroy the session
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out - School Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .logout-container {
            max-width: 500px;
            width: 100%;
            padding: 40px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .logo-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 25px;
        }
        .logout-logo {
            width: 120px;
            height: 120px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: fadeOut 3s forwards;
            z-index: 2;
        }
        .logout-spinner {
            position: absolute;
            width: 150px;
            height: 150px;
            border: 5px solid rgba(0, 123, 255, 0.2);
            border-top: 5px solid #007bff;
            border-radius: 50%;
            animation: spin 1.5s linear infinite, fadeOut 3s forwards;
            top: 0;
            left: 0;
            z-index: 1;
        }
        .logout-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            animation: fadeOut 3s forwards;
        }
        .logout-message {
            font-size: 18px;
            color: #6c757d;
            margin-bottom: 30px;
            animation: fadeOut 3s forwards;
        }
        .logout-progress {
            width: 100%;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .logout-progress-bar {
            height: 100%;
            width: 0;
            background-color: #007bff;
            border-radius: 4px;
            animation: progress 3s ease-out forwards;
        }
        .logout-footer {
            font-size: 14px;
            color: #6c757d;
            animation: fadeOut 3s forwards;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes progress {
            0% { width: 0; }
            100% { width: 100%; }
        }
        
        @keyframes fadeOut {
            0% { opacity: 1; }
            70% { opacity: 1; }
            100% { opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logo-container">
            <img src="uploads/csr.png" alt="School Logo" class="logout-logo">
            <div class="logout-spinner"></div>
        </div>
        
        <h1 class="logout-title">Logging Out</h1>
        <p class="logout-message">Thank you for using the School Management System</p>
        
        <div class="logout-progress">
            <div class="logout-progress-bar"></div>
        </div>
        
        <p class="logout-footer">Redirecting to login page...</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Redirect to login page after 3 seconds
            setTimeout(function() {
                window.location.href = "../index.php";
            }, 3000);
        });
    </script>
</body>
</html>