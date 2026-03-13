<?php
require_once '../config.php';

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'Student ID is required']);
    exit();
}

$studentId = mysqli_real_escape_string($conn, $_GET['id']);

// Query to get student details
$query = "SELECT * FROM students WHERE id = $studentId";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
    exit();
}

// Check if student exists
if (mysqli_num_rows($result) === 0) {
    echo json_encode(['error' => 'Student not found']);
    exit();
}

// Get student data
$student = mysqli_fetch_assoc($result);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($student);
?>