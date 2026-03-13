<?php
// 1. Start the session to access/destroy it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Destroy the session data
session_destroy();

// 3. Prepare redirect URL
 $redirectUrl = 'login.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Logging Out...</title>
    
    <!-- Google Fonts to match the login page -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #008751; /* Philippine Green from login page */
            --bg-light: #f3f4f6;
            --white: #ffffff;
            --text-dark: #1f2937;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .logout-container {
            background: var(--white);
            padding: 40px 60px;
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
        }

        .header-logo {
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .header-logo h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0;
        }

        .header-logo span.peso-symbol {
            font-size: 2.2rem;
            line-height: 1;
        }
        
        .header-logo span.text {
            color: var(--text-dark);
        }

        .status-text {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text-gray);
            margin-bottom: 20px;
        }

        /* Progress Bar Wrapper */
        .progress-container {
            width: 100%;
            height: 6px;
            background-color: #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }

        /* The Moving Bar */
        .progress-bar {
            height: 100%;
            background-color: var(--primary-color);
            width: 0%;
            border-radius: 10px;
        }

        /* Animation Class */
        .loading-active .progress-bar {
            animation: loadProgress 5s linear forwards; /* 5 Seconds duration */
        }

        @keyframes loadProgress {
            0% { width: 0%; }
            100% { width: 100%; }
        }
    </style>
</head>
<body>

    <div class="logout-container">
        <div class="header-logo">
            <span class="peso-symbol">₱</span>
            <h1>Utility<span class="text">SYS</span></h1>
        </div>

        <p class="status-text">Logging out securely...</p>

        <div class="progress-container loading-active">
            <div class="progress-bar"></div>
        </div>
    </div>

    <script>
        // Wait for the DOM to load
        document.addEventListener('DOMContentLoaded', function() {
            // The CSS animation runs for 5 seconds. 
            // We set the JS timeout to match exactly (5000ms).
            setTimeout(function() {
                window.location.href = "<?php echo $redirectUrl; ?>";
            }, 5000);
        });
    </script>

</body>
</html>