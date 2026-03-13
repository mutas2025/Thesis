<?php

date_default_timezone_set('Asia/Manila');

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u290526623_SmartSchool26');
define('DB_PASSWORD', 'SmartSchool26');
define('DB_NAME', 'u290526623_SmartSchool26');

// School Information Configuration
define('SCHOOL_NAME', 'Colegio de Santa Rita de San Carlos, Inc..'); // Replace with actual school name
define('SCHOOL_ADDRESS', 'Big Tibuco Brgy. 1 San Carlos City, Negros Island Region'); // Replace with actual address
define('SCHOOL_EMAIL', 'admin@csr-scc.edu.pp'); // Replace with actual email
define('SCHOOL_CONTACT_NO', '+63 910-188-2719'); // Replace with actual contact number

// Create connection
 $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Function to check if user has a specific role
function hasRole($requiredRole) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $requiredRole;
}

// Function to require login and redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: login.php");
        exit();
    }
}

// Function to require specific role
function requireRole($requiredRole) {
    requireLogin(); // First check if user is logged in
    
    if (!hasRole($requiredRole)) {
        // User is logged in but doesn't have the required role
        header("Location: unauthorized.php");
        exit();
    }
}

// Function to check if user is admin
function isAdmin() {
    return hasRole('admin');
}

// Function to check if user is registrar
function isRegistrar() {
    return hasRole('registrar');
}

// Function to check if user is cashier
function isCashier() {
    return hasRole('cashier');
}

// Function to check if user is cashier
function isTreasurer() {
    return hasRole('treasurer');
}
// Function to check if user is cashier
function isCounselor() {
    return hasRole('counselor');
}

// Function to get current user info
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}
?>