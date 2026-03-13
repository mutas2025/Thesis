<?php
require_once '../config.php';

// Get filter parameters
$courseId = isset($_GET['course_id']) ? mysqli_real_escape_string($conn, $_GET['course_id']) : '';
$academicYear = isset($_GET['academic_year']) ? mysqli_real_escape_string($conn, $_GET['academic_year']) : '';
$semester = isset($_GET['semester']) ? mysqli_real_escape_string($conn, $_GET['semester']) : '';

// Build WHERE clause
$whereClause = "WHERE 1=1";
if (!empty($courseId)) {
    $whereClause .= " AND course_id = '$courseId'";
}
if (!empty($academicYear)) {
    $whereClause .= " AND academic_year = '$academicYear'";
}
if (!empty($semester)) {
    $whereClause .= " AND semester = '$semester'";
}

// Get subjects
$query = "SELECT * FROM subjects $whereClause ORDER BY subject_code";
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
?>