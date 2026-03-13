<?php
// index.php

// 1. Start the session so we can check if the user is logged in
session_start();

// 2. Check if the user is NOT logged in
// (Assuming you set $_SESSION['user_id'] or $_SESSION['logged_in'] during your login process)
if (!isset($_SESSION['user_id'])) {
    
    // 3. Redirect to the login page
    header("Location: login.php");
    exit(); // Ensure no further code is executed after the redirect
}

// OPTIONAL: If they ARE logged in, you might want to send them 
// to a dashboard instead of showing a blank page.
// header("Location: dashboard.php");
// exit();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<body>
    <!-- This content only shows if the user IS logged in -->
    <h1>Welcome!</h1>
    <p>You are successfully logged in.</p>
    <a href="logout.php">Logout</a>
</body>
</html>