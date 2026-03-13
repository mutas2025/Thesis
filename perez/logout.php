<?php
// 1. Start the session and destroy it immediately
session_start();
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <style>
        /* CSS Reset and basic styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        /* Container for the loader */
        .logout-container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        /* The Spinner Design */
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db; /* Blue color */
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px auto;
        }

        /* Text styling */
        h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 24px;
        }

        p {
            color: #666;
            font-size: 16px;
        }

        /* Spinner Animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <div class="logout-container">
        <div class="loader"></div>
        <h2>Logging Out</h2>
        <p>Please wait while we secure your account...</p>
    </div>

    <script>
        // 2. Set a timer for 2 seconds (5000 milliseconds)
        setTimeout(function() {
            // 3. Redirect to login page after the delay
            window.location.href = 'index.php';
        }, 3000); // Change 2000 to however many milliseconds you want (e.g., 3000 = 3 seconds)
    </script>

    <!-- Fallback for users with JavaScript disabled -->
    <noscript>
        <meta http-equiv="refresh" content="2;url=login.php">
    </noscript>

</body>
</html>