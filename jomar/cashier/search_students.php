<?php
session_start();
require_once '../config.php';
requireRole('cashier');

$searchTerm = isset($_GET['term']) ? $_GET['term'] : '';

// Use prepared statements to prevent SQL injection
$query = "SELECT id, id_number, last_name, first_name, middle_name 
          FROM students 
          WHERE id_number LIKE ? 
             OR last_name LIKE ? 
             OR first_name LIKE ?
             OR CONCAT(last_name, ', ', first_name, ' ', middle_name) LIKE ?
          ORDER BY last_name, first_name
          LIMIT 20";

$stmt = mysqli_prepare($conn, $query);
$likeTerm = '%' . $searchTerm . '%';
mysqli_stmt_bind_param($stmt, "ssss", $likeTerm, $likeTerm, $likeTerm, $likeTerm);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$students = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = [
            'id' => $row['id'],
            'idNumber' => $row['id_number'],
            'lastName' => $row['last_name'],
            'firstName' => $row['first_name'],
            'middleName' => $row['middle_name'],
            'fullName' => $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name']
        ];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($students);
?>