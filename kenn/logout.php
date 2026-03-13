<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Logging Out - Guidance Office System</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="uploads/csr.png" type="image/x-icon">
    
    <style>
        :root {
            --primary-color: #1a3a5f;
            --secondary-color: #0f2238;
            --spinner-color: #d32f2f; /* Red accent for the spinner */
            --light-bg: #f8f9fa;
            --dark-text: #212529;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
            height: 100vh;
            margin: 0;
            padding: 0;
            background-image: url('uploads/newbuilding.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(26, 58, 95, 0.7);
            z-index: -1;
        }
        
        .logout-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 50px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            position: relative;
            z-index: 1;
        }
        
        /* --- NEW LOGO SPINNER STYLES --- */
        .logo-spinner-wrapper {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto 30px auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            z-index: 2;
            position: relative;
            background: #fff; /* Background to ensure contrast */
            padding: 2px;
        }

        .spinner-ring {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 4px solid rgba(211, 47, 47, 0.2); /* Light red background track */
            border-top: 4px solid var(--spinner-color); /* Solid red spinning part */
            animation: spin 1.5s linear infinite;
            z-index: 1;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* --- END NEW STYLES --- */
        
        .logout-title {
            font-size: 28px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .logout-message {
            font-size: 16px;
            color: var(--dark-text);
            margin-bottom: 30px;
            line-height: 1.5;
        }
        
        .btn-continue {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 12px 30px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .btn-continue:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .countdown {
            margin-top: 20px;
            font-size: 14px;
            color: var(--dark-text);
        }
        
        .countdown span {
            font-weight: bold;
            color: var(--primary-color);
        }
        
        @media (max-width: 576px) {
            .logout-container {
                padding: 30px 20px;
            }
            .logout-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <!-- Loading State with Logo Spinner -->
        <div id="loading-state">
            <!-- New UI: Logo with Circle Loader Outside -->
            <div class="logo-spinner-wrapper">
                <div class="spinner-ring"></div>
                <img src="uploads/csr.png" alt="School Logo" class="logo-image">
            </div>

            <h1 class="logout-title">Logging Out</h1>
            <p class="logout-message">Please wait while we securely log you out of the system...</p>
            <div class="countdown">Logging out in <span id="logout-countdown">5</span> seconds.</div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
    
    <script>
        // Countdown timer for logout process
        let logoutSeconds = 5;
        const logoutCountdownElement = document.getElementById('logout-countdown');
        
        function updateLogoutCountdown() {
            logoutCountdownElement.textContent = logoutSeconds;
            logoutSeconds--;
            
            if (logoutSeconds < 0) {
                // Perform logout via AJAX to avoid page reload
                performLogout();
            } else {
                setTimeout(updateLogoutCountdown, 1000);
            }
        }
        
        function performLogout() {
            // Use fetch to call a PHP script that handles the logout
            fetch('logout_process.php')
                .then(response => response.json())
                .then(data => {
                    // Redirect directly to login page
                    window.location.href = 'login.php';
                })
                .catch(error => {
                    console.error('Error during logout:', error);
                    // Still redirect to login page even if there's an error
                    window.location.href = 'login.php';
                });
        }
        
        // Start logout countdown after page loads
        setTimeout(updateLogoutCountdown, 1000);
    </script>
</body>
</html>