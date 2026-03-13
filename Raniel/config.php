<?php
/* config.php */
session_start();

// Database Credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u290526623_Utilitys2026');
define('DB_PASSWORD', 'Utilitys2026');
define('DB_NAME', 'u290526623_Utilitys2026');

// Create connection using MySQLi Object-Oriented (Required for Transactions)
 $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper function to sanitize inputs
function sanitize($data) {
    global $conn;
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}
?>