<?php
// config.php
 $servername = "localhost";
 $username = "u290526623_KCC2026";        // Change to your DB username
 $password = "SystemKcc2026";            // Change to your DB password
 $dbname = "u290526623_KCC2026";      // Change to your DB name

// Create connection
 $conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>