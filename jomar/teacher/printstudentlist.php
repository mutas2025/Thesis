<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: tlogin.php");
    exit();
}

// Get filter parameters
 $subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
 $course = isset($_GET['course']) ? $_GET['course'] : '';
 $section = isset($_GET['section']) ? $_GET['section'] : '';

// Get teacher ID
 $teacher_id = $_SESSION['user_id'];

// Build query to get students from teacher's personal list
 $students_query = "SELECT s.id, s.id_number, s.last_name, s.first_name, s.middle_name, s.gender,
                   c.coursename, c.courselevel, sub.subject_code, sub.subject_description, sub.id as subject_id,
                   e.section
                   FROM mystudents ms
                   JOIN students s ON ms.student_id = s.id
                   LEFT JOIN student_subjects ss ON s.id = ss.student_id AND ms.subject_id = ss.subject_id
                   LEFT JOIN enrollments e ON ss.enrollment_id = e.id
                   LEFT JOIN courses c ON e.course_id = c.id
                   LEFT JOIN subjects sub ON ms.subject_id = sub.id
                   WHERE ms.teacher_id = ?";

 $params = [$teacher_id];
 $types = "i";

// Add subject filter if provided
if (!empty($subject_id)) {
    $students_query .= " AND ms.subject_id = ?";
    $params[] = $subject_id;
    $types .= "i";
}

// Add course filter if provided
if (!empty($course)) {
    $students_query .= " AND CONCAT(c.coursename, ' - ', c.courselevel) = ?";
    $params[] = $course;
    $types .= "s";
}

// Add section filter if provided
if (!empty($section)) {
    $students_query .= " AND e.section = ?";
    $params[] = $section;
    $types .= "s";
}

// Order by gender first, then by name
 $students_query .= " ORDER BY s.gender, s.last_name, s.first_name";

 $stmt = $conn->prepare($students_query);
 $stmt->bind_param($types, ...$params);
 $stmt->execute();
 $students_result = $stmt->get_result();

 $students = [];
while ($row = $students_result->fetch_assoc()) {
    $students[] = $row;
}

// Get teacher details
 $teacher_query = "SELECT name, username, employee_id FROM teachers WHERE id = ?";
 $stmt = $conn->prepare($teacher_query);
 $stmt->bind_param("i", $teacher_id);
 $stmt->execute();
 $result = $stmt->get_result();
 $teacher = $result->fetch_assoc();

// Get current date for the report
 $current_date = date('F d, Y');

// Get subject name if subject_id is provided
 $subject_name = '';
if (!empty($subject_id)) {
    $subject_query = "SELECT subject_code, subject_description FROM subjects WHERE id = ?";
    $stmt = $conn->prepare($subject_query);
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $subject_result = $stmt->get_result();
    if ($subject_row = $subject_result->fetch_assoc()) {
        $subject_name = $subject_row['subject_code'] . ' - ' . $subject_row['subject_description'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .filters {
            margin-bottom: 20px;
            font-size: 14px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .student-list {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #333;
            margin-bottom: 20px;
        }
        .student-list th, .student-list td {
            padding: 8px;
            text-align: left;
            border: 1px solid #333;
        }
        .student-list th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .student-list td:nth-child(1) {
            width: 80%;
        }
        .student-list td:nth-child(2) {
            width: 20%;
            text-align: center;
        }
        .gender-header {
            background-color: #004085;
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 16px;
        }
        .gender-header td {
            border: 1px solid #333;
        }
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 8px 15px;
            background-color: #004085;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-button:hover {
            background-color: #003366;
        }
        @media print {
            .print-button {
                display: none;
            }
            .header {
                border-bottom: 2px solid #333;
            }
            body {
                padding: 10px;
            }
            .student-list {
                border: 1px solid #000;
            }
            .student-list th, .student-list td {
                border: 1px solid #000;
            }
            .gender-header {
                background-color: #f0f0f0 !important;
                color: #000 !important;
            }
            .gender-header td {
                border: 1px solid #000;
            }
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">Print</button>
    
    <div class="header">
        <h1>Student List</h1>
        <p>Teacher: <?= htmlspecialchars($teacher['name']) ?> (<?= htmlspecialchars($teacher['employee_id']) ?>)</p>
        <p>Date: <?= $current_date ?></p>
    </div>
    
    <div class="filters">
        <strong>Filters Applied:</strong>
        <?php if (!empty($subject_name)): ?>
            Subject: <?= htmlspecialchars($subject_name) ?>
        <?php endif; ?>
        <?php if (!empty($course)): ?>
            | Course: <?= htmlspecialchars($course) ?>
        <?php endif; ?>
        <?php if (!empty($section)): ?>
            | Section: <?= htmlspecialchars($section) ?>
        <?php endif; ?>
        <?php if (empty($subject_id) && empty($course) && empty($section)): ?>
            All students in your list
        <?php endif; ?>
    </div>
    
    <?php if (!empty($students)): ?>
        <table class="student-list">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $currentGender = '';
                foreach ($students as $student): 
                    // Add a row with gender header when gender changes
                    if ($currentGender !== $student['gender']) {
                        $currentGender = $student['gender'];
                        echo '<tr><td colspan="2" class="gender-header">' . htmlspecialchars($currentGender) . '</td></tr>';
                    }
                ?>
                    <tr>
                        <td><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']) ?></td>
                        <td></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="footer">
            <p>Total Students: <?= count($students) ?></p>
            <p>Generated on <?= date('Y-m-d H:i:s') ?></p>
        </div>
    <?php else: ?>
        <p>No students found matching the selected filters.</p>
    <?php endif; ?>
</body>
</html>