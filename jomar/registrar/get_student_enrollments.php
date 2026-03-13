<?php
require_once '../config.php';

// Check if student ID is provided
if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    echo json_encode(['error' => 'Student ID is required']);
    exit();
}

$studentId = mysqli_real_escape_string($conn, $_GET['student_id']);

// Query to get student's enrollment history
$query = "SELECT e.id, e.academic_year, e.semester, e.enrollment_date, e.status, e.year_level,
                c.coursename, c.courselevel
          FROM enrollments e
          JOIN courses c ON e.course_id = c.id
          WHERE e.student_id = $studentId
          ORDER BY e.academic_year DESC, e.semester DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
    exit();
}

$enrollments = [];
while ($row = mysqli_fetch_assoc($result)) {
    $row['course_full_name'] = $row['coursename'] . ' - ' . $row['courselevel'];
    $enrollments[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($enrollments);
?>