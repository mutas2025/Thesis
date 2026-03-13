<?php
// Set Timezone to Manila
date_default_timezone_set('Asia/Manila');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'u290526623_GuidanceSYS26');
define('DB_PASS', 'GuidanceSYS26');
define('DB_NAME', 'u290526623_GuidanceSYS26');

// Create connection
 $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();

// Define base URL
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/guidance_office_system/');

// School Configuration
define('SCHOOL_NAME', 'Colegio de Santa Rita de San Carlos, Inc.');
define('SCHOOL_ADDRESS', 'San Carlos City, NIR');
define('SCHOOL_EMAIL', 'csr-scc.edu.ph');
define('SCHOOL_CONTACT', '+1 234 567 8900');
?>