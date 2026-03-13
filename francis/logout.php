<?php
session_start();

// Check if the action parameter is set to actually perform the logout
if (isset($_GET['action']) && $_GET['action'] == 'confirm') {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to login page
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <link rel="icon" href="uploads/diocese.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0f172a;
            --accent-color: #10b981; /* Emerald Green */
        }

        body {
            /* Deep Navy Blue - Matching Login Theme */
            background-color: #0f172a; 
            /* Subtle Money Pattern Background */
            background-image: 
                radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.03) 0%, transparent 60%),
                repeating-linear-gradient(45deg, rgba(16, 185, 129, 0.03) 0px, rgba(16, 185, 129, 0.03) 1px, transparent 1px, transparent 20px),
                repeating-linear-gradient(-45deg, rgba(16, 185, 129, 0.03) 0px, rgba(16, 185, 129, 0.03) 1px, transparent 1px, transparent 20px);
            
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
        }

        /* Abstract Shapes for consistency */
        body::before {
            content: '';
            position: absolute;
            top: -100px;
            left: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(21, 128, 61, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            z-index: 0;
        }

        /* Glassmorphism Card */
        .logout-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        
        /* Logo Styles */
        .loading-logo {
            width: 120px;
            height: 120px;
            margin-bottom: 30px; /* Space between logo and spinner */
            animation: pulse 2s infinite;
        }
        
        /* Spinner Styles - OUTSIDE Logo */
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #10b981; /* Emerald Top */
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px auto; /* Centered and spaced */
        }
        
        .loading-text {
            font-size: 18px;
            color: #334155;
            font-weight: 500;
            letter-spacing: 0.5px;
            margin-bottom: 25px;
        }

        /* Progress Bar */
        .loading-progress {
            width: 100%;
            height: 6px;
            background-color: #e2e8f0;
            border-radius: 3px;
            margin-top: 15px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .loading-progress-bar {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #10b981, #059669);
            border-radius: 3px;
            animation: progress 4s linear forwards; /* 4 seconds matches the JS timeout */
        }

        /* --- Developer Section Styles --- */
        .developer-section {
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: center;
        }

        .dev-card {
            display: flex;
            align-items: center;
            gap: 12px;
            background-color: #f0fdf4;
            padding: 8px 18px 8px 8px;
            border-radius: 50px;
            border: 1px solid #dcfce7;
            transition: all 0.3s ease;
            cursor: default;
        }

        .dev-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
            background-color: #fff;
            border-color: #10b981;
        }

        .dev-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #10b981;
            transition: transform 0.4s ease;
        }

        .dev-card:hover .dev-avatar {
            transform: rotate(360deg);
        }

        .dev-info {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .dev-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0f172a;
        }

        .dev-role {
            font-size: 0.7rem;
            color: #059669;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        /* ------------------------------- */
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes progress {
            0% { width: 0; }
            100% { width: 100%; }
        }
    </style>
</head>
<body>

    <div class="logout-container">
        <!-- 1. Logo -->
        <img src="uploads/diocese.png" alt="System Logo" class="loading-logo">
        
        <!-- 2. Spinner (Outside the logo) -->
        <div class="loading-spinner"></div>
        
        <!-- 3. Text -->
        <div class="loading-text">Securely Logging Out...</div>
        
        <!-- 4. Progress Bar -->
        <div class="loading-progress">
            <div class="loading-progress-bar"></div>
        </div>

        <!-- Developer Section -->
        <div class="developer-section">
            <div class="dev-card">
                <img src="uploads/francis.jpg" alt="Francis P. Malabo" class="dev-avatar">
                <div class="dev-info">
                    <div class="dev-name">Francis P. Malabo</div>
                    <div class="dev-role"><i class="fas fa-code"></i> Code Warriors Member</div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Increased timeout to 4 seconds to allow viewing the developer section
            setTimeout(function() {
                window.location.href = "logout.php?action=confirm";
            }, 4000);
        });
    </script>
</body>
</html>