<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'u290526623_AccouningSYS26');
define('DB_PASSWORD', 'AccouningSYS26');
define('DB_NAME', 'u290526623_AccouningSYS26');

// Create connection
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>