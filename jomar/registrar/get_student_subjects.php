<?php
require_once '../config.php';

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get parameters
$enrollmentId = isset($_GET['enrollment_id']) ? mysqli_real_escape_string($conn, $_GET['enrollment_id']) : '';
$studentId = isset($_GET['student_id']) ? mysqli_real_escape_string($conn, $_GET['student_id']) : '';

// Build query
$query = "SELECT ss.*, s.subject_code, s.subject_description, s.unit, s.year_level, s.academic_year 
          FROM student_subjects ss
          JOIN subjects s ON ss.subject_id = s.id
          WHERE 1=1";

if (!empty($enrollmentId)) {
    $query .= " AND ss.enrollment_id = '$enrollmentId'";
}

if (!empty($studentId)) {
    $query .= " AND ss.student_id = '$studentId'";
}

$query .= " ORDER BY s.year_level, s.subject_code ASC";

$result = mysqli_query($conn, $query);
$subjects = [];

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $subjects[] = $row;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($subjects);

mysqli_close($conn);
?>