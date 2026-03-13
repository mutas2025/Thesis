<?php
session_start();
require_once '../config.php';
requireRole('cashier');

// Set header to return JSON
header('Content-Type: application/json');

// Get parameters
$studentId = isset($_GET['student_id']) ? mysqli_real_escape_string($conn, $_GET['student_id']) : 0;
$academicYear = isset($_GET['academic_year']) ? mysqli_real_escape_string($conn, $_GET['academic_year']) : '';
$semester = isset($_GET['semester']) ? mysqli_real_escape_string($conn, $_GET['semester']) : '';

// Validate parameters
if (empty($studentId) || empty($academicYear) || empty($semester)) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

// Get student's total units for the specified academic year and semester
$query = "SELECT SUM(s.unit) as total_units 
          FROM student_subjects ss
          JOIN subjects s ON ss.subject_id = s.id
          JOIN enrollments e ON ss.enrollment_id = e.id
          WHERE e.student_id = $studentId 
          AND e.academic_year = '$academicYear'
          AND e.semester = '$semester'";

$result = mysqli_query($conn, $query);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $totalUnits = $row['total_units'] ? $row['total_units'] : 0;
    
    echo json_encode(['total_units' => $totalUnits]);
} else {
    echo json_encode(['error' => 'Database query failed: ' . mysqli_error($conn)]);
}
?>