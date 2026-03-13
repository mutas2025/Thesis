<?php
// config.php
 $host = 'localhost';
 $db   = 'u290526623_BHMS2026';
 $user = 'u290526623_BHMS2026';      // Default XAMPP user
 $pass = 'Bhms2026';          // Default XAMPP password (empty)
 $charset = 'utf8mb4';

 $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
 $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper function to check login
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// Helper to check role
function checkRole($allowedRoles) {
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        // If not allowed, redirect to index or a specific error page
        header("Location: index.php");
        exit;
    }
}
?>