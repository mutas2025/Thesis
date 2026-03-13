<?php
session_start();
require_once '../config.php';
// Require login and cashier role
requireRole('cashier');

// Get student ID from request
$studentId = isset($_GET['student_id']) ? mysqli_real_escape_string($conn, $_GET['student_id']) : '';

if (empty($studentId)) {
    echo json_encode([]);
    exit;
}

// Get student payments
$studentPayments = [];
$query = "SELECT id, or_number, amount, payment_date FROM payments WHERE student_id = $studentId ORDER BY payment_date DESC";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $studentPayments[] = $row;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($studentPayments);