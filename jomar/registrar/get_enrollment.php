<?php
require_once '../config.php';

if (isset($_GET['id'])) {
    $enrollmentId = mysqli_real_escape_string($conn, $_GET['id']);
    
    $query = "SELECT e.*, s.last_name, s.first_name, s.middle_name, 
                     c.coursename, c.courselevel
              FROM enrollments e
              JOIN students s ON e.student_id = s.id
              JOIN courses c ON e.course_id = c.id
              WHERE e.id = $enrollmentId";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $enrollment = mysqli_fetch_assoc($result);
        $enrollment['student_name'] = $enrollment['last_name'] . ', ' . $enrollment['first_name'] . ' ' . $enrollment['middle_name'];
        $enrollment['course_full_name'] = $enrollment['coursename'] . ' - ' . $enrollment['courselevel'];
        echo json_encode($enrollment);
    } else {
        echo json_encode(['error' => 'Enrollment not found']);
    }
} else {
    echo json_encode(['error' => 'No enrollment ID provided']);
}
?>