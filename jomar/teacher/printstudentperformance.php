<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: tlogin.php");
    exit();
}

 $teacher_id = $_SESSION['user_id'];
 $report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'performance';
 $subject_id = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
 $section = isset($_GET['section']) ? $_GET['section'] : '';
 $quarter = isset($_GET['quarter']) ? $_GET['quarter'] : '';

// Get teacher details
 $query = "SELECT name, username, employee_id FROM teachers WHERE id = ?";
 $stmt = $conn->prepare($query);
 $stmt->bind_param("i", $teacher_id);
 $stmt->execute();
 $result = $stmt->get_result();
 $teacher = $result->fetch_assoc();

// Get teacher assignments with sections
 $assignments_query = "SELECT ta.id, ta.academic_year, ta.semester, ta.section, 
                             s.subject_code, s.subject_description, s.id as subject_id,
                             c.coursename, c.courselevel, c.id as course_id
                      FROM teacherassignments ta 
                      JOIN subjects s ON ta.subject_id = s.id 
                      JOIN courses c ON ta.course_id = c.id 
                      WHERE ta.teacher_id = ? 
                      ORDER BY ta.academic_year DESC, ta.semester DESC, c.coursename, s.subject_code";
 $stmt = $conn->prepare($assignments_query);
 $stmt->bind_param("i", $teacher_id);
 $stmt->execute();
 $assignments_result = $stmt->get_result();
 $assignments = [];
 $assignedSubjectIds = [];
 $assignedCourseIds = [];
 $assignedSections = [];
while ($row = $assignments_result->fetch_assoc()) {
    $assignments[] = $row;
    $assignedSubjectIds[] = $row['subject_id'];
    $assignedCourseIds[] = $row['course_id'];
    if (!empty($row['section'])) {
        $assignedSections[] = $row['section'];
    }
}

// Get teacher's personal student list (mystudents) with subject details
 $mystudents_query = "SELECT ms.id as mystudent_id, s.id, s.id_number, s.last_name, s.first_name, s.middle_name, 
                            s.email, s.contact_number, e.status, e.academic_year, e.semester, e.section,
                            c.coursename, c.courselevel, sub.subject_code, sub.subject_description, sub.id as subject_id
                     FROM mystudents ms
                     JOIN students s ON ms.student_id = s.id
                     LEFT JOIN student_subjects ss ON s.id = ss.student_id AND ms.subject_id = ss.subject_id
                     LEFT JOIN enrollments e ON ss.enrollment_id = e.id
                     LEFT JOIN courses c ON e.course_id = c.id
                     LEFT JOIN subjects sub ON ms.subject_id = sub.id
                     WHERE ms.teacher_id = ?";
 $stmt = $conn->prepare($mystudents_query);
 $stmt->bind_param("i", $teacher_id);
 $stmt->execute();
 $mystudents_result = $stmt->get_result();
 $mystudents = [];
while ($row = $mystudents_result->fetch_assoc()) {
    $mystudents[] = $row;
}

// Filter mystudents based on subject and section if provided
 $filtered_mystudents = $mystudents;
if ($subject_id > 0) {
    $filtered_mystudents = array_filter($filtered_mystudents, function($student) use ($subject_id) {
        return $student['subject_id'] == $subject_id;
    });
}

if (!empty($section)) {
    $filtered_mystudents = array_filter($filtered_mystudents, function($student) use ($section) {
        return $student['section'] == $section;
    });
}

// Get data based on report type
if ($report_type === 'scores') {
    // Get activities and scores for students
    $students_query = "SELECT s.id, s.id_number, s.last_name, s.first_name, s.middle_name 
                      FROM students s
                      JOIN mystudents ms ON s.id = ms.student_id
                      LEFT JOIN student_subjects ss ON s.id = ss.student_id AND ms.subject_id = ss.subject_id
                      LEFT JOIN enrollments e ON ss.enrollment_id = e.id
                      WHERE ms.teacher_id = ? AND ms.subject_id = ?";
    
    $params = [$teacher_id, $subject_id];
    $types = "ii";
    
    if (!empty($section)) {
        $students_query .= " AND e.section = ?";
        $params[] = $section;
        $types .= "s";
    }
    
    $students_query .= " ORDER BY s.last_name, s.first_name";
    
    $stmt = $conn->prepare($students_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $students_result = $stmt->get_result();
    
    $students = [];
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
    
    // Get aggregated scores by type and quarter
    $scores = [];
    if (!empty($students)) {
        $student_ids = array_column($students, 'id');
        $student_ids_str = implode(',', $student_ids);
        
        // Modified query to aggregate scores by type and quarter
        $scores_query = "SELECT sas.student_id, a.type, a.quarter, 
                        SUM(sas.score) as total_score, 
                        SUM(a.max_score) as total_max_score
                        FROM student_activity_scores sas
                        JOIN activities a ON sas.activity_id = a.id
                        WHERE a.subject_id = ? AND sas.student_id IN ($student_ids_str)";
        
        $params = [$subject_id];
        $types = "i";
        
        if (!empty($quarter)) {
            $scores_query .= " AND a.quarter = ?";
            $params[] = $quarter;
            $types .= "s";
        }
        
        if (!empty($section)) {
            $scores_query .= " AND a.section = ?";
            $params[] = $section;
            $types .= "s";
        }
        
        $scores_query .= " GROUP BY sas.student_id, a.type, a.quarter
                         ORDER BY sas.student_id, a.quarter, a.type";
        
        $stmt = $conn->prepare($scores_query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $scores_result = $stmt->get_result();
        
        while ($row = $scores_result->fetch_assoc()) {
            $key = $row['student_id'] . '_' . $row['quarter'] . '_' . $row['type'];
            $scores[$key] = [
                'total_score' => $row['total_score'],
                'total_max_score' => $row['total_max_score'],
                'percentage' => $row['total_max_score'] > 0 ? round(($row['total_score'] / $row['total_max_score']) * 100, 2) : 0
            ];
        }
    }
    
    $report_title = "Student Scores Report";
} elseif ($report_type === 'grades') {
    // Get grades for students
    $students_query = "SELECT s.id, s.id_number, s.last_name, s.first_name, s.middle_name 
                      FROM students s
                      JOIN mystudents ms ON s.id = ms.student_id
                      LEFT JOIN student_subjects ss ON s.id = ss.student_id AND ms.subject_id = ss.subject_id
                      LEFT JOIN enrollments e ON ss.enrollment_id = e.id
                      WHERE ms.teacher_id = ? AND ms.subject_id = ?";
    
    $params = [$teacher_id, $subject_id];
    $types = "ii";
    
    if (!empty($section)) {
        $students_query .= " AND e.section = ?";
        $params[] = $section;
        $types .= "s";
    }
    
    $students_query .= " ORDER BY s.last_name, s.first_name";
    
    $stmt = $conn->prepare($students_query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $students_result = $stmt->get_result();
    
    $students = [];
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
    
    // Get grades for all students in this subject
    $grades = [];
    if (!empty($students)) {
        $student_ids = array_column($students, 'id');
        $student_ids_str = implode(',', $student_ids);
        
        $grades_query = "SELECT student_id, quarter1_grade, quarter2_grade, quarter3_grade, quarter4_grade, average_grade, remarks
                        FROM student_grades 
                        WHERE subject_id = ? AND student_id IN ($student_ids_str)";
        $stmt = $conn->prepare($grades_query);
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $grades_result = $stmt->get_result();
        
        while ($row = $grades_result->fetch_assoc()) {
            $grades[$row['student_id']] = $row;
        }
    }
    
    $report_title = "Student Grades Report";
} else {
    // Default performance report
    $report_title = "Student Performance Report";
}

// Get subject details if subject_id is provided
 $subject_details = null;
if ($subject_id > 0) {
    $subject_query = "SELECT subject_code, subject_description FROM subjects WHERE id = ?";
    $stmt = $conn->prepare($subject_query);
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $subject_result = $stmt->get_result();
    $subject_details = $subject_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($report_title) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #004085;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #004085;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            margin: 5px 0;
            color: #555;
        }
        .report-info {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .report-info h3 {
            margin-top: 0;
            color: #004085;
        }
        .table-container {
            width: 100%;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #004085;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #004085;
            color: white;
            border: none;
            padding: 10px 15px;
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
            body {
                padding: 0;
            }
            .header {
                margin-bottom: 20px;
            }
        }
        .quarter-header {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .type-header {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .grade-passed {
            color: #004085;
            font-weight: bold;
        }
        .grade-failed {
            color: #dc3545;
            font-weight: bold;
        }
        .grade-inc {
            color: #ffc107;
            font-weight: bold;
        }
        .summary-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .stat-box {
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            min-width: 120px;
        }
        .stat-box h4 {
            margin: 0 0 10px 0;
            color: #004085;
        }
        .stat-box .value {
            font-size: 24px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">
        <i class="fas fa-print"></i> Print
    </button>
    
    <div class="header">
        <h1><?= htmlspecialchars($report_title) ?></h1>
        <p>Generated on: <?= date('F j, Y') ?></p>
        <p>Teacher: <?= htmlspecialchars($teacher['name']) ?></p>
        <?php if ($subject_details): ?>
            <p>Subject: <?= htmlspecialchars($subject_details['subject_code'] . ' - ' . $subject_details['subject_description']) ?></p>
        <?php endif; ?>
        <?php if (!empty($section)): ?>
            <p>Section: <?= htmlspecialchars($section) ?></p>
        <?php endif; ?>
        <?php if (!empty($quarter)): ?>
            <p>Quarter: <?= htmlspecialchars($quarter) ?></p>
        <?php endif; ?>
    </div>
    
    <?php if ($report_type === 'scores'): ?>
        <div class="report-info">
            <h3>Student Scores by Type and Quarter</h3>
            <p>This report shows the aggregated scores for each student by activity type and quarter.</p>
        </div>
        
        <?php if (!empty($students)): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Student Name</th>
                            <?php 
                            // Determine which quarters to show based on filter
                            $quartersToShow = $quarter ? [$quarter] : ['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'];
                            
                            // Add quarter columns with type sub-columns
                            foreach ($quartersToShow as $q) {
                                echo '<th colspan="3" class="text-center">' . htmlspecialchars($q) . '</th>';
                            }
                            ?>
                            <th class="text-center">Overall Average</th>
                        </tr>
                        <tr>
                            <th></th> <!-- Empty for ID Number -->
                            <th></th> <!-- Empty for Student Name -->
                            <?php 
                            // Add type sub-headers for each quarter
                            foreach ($quartersToShow as $q) {
                                echo '<th class="text-center type-header">Activities</th>';
                                echo '<th class="text-center type-header">Exams</th>';
                                echo '<th class="text-center type-header">Participation</th>';
                            }
                            ?>
                            <th></th> <!-- Empty for Overall Average -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['id_number']) ?></td>
                                <td><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']) ?></td>
                                <?php 
                                $totalScore = 0;
                                $totalMaxScore = 0;
                                $activityCount = 0;
                                
                                // Add scores for each quarter and type
                                foreach ($quartersToShow as $q) {
                                    $activityScore = 0;
                                    $examScore = 0;
                                    $participationScore = 0;
                                    $activityMax = 0;
                                    $examMax = 0;
                                    $participationMax = 0;
                                    
                                    // Get scores for each type in this quarter
                                    $activityKey = $student['id'] . '_' . $q . '_activity';
                                    $examKey = $student['id'] . '_' . $q . '_exam';
                                    $participationKey = $student['id'] . '_' . $q . '_participation';
                                    
                                    if (isset($scores[$activityKey])) {
                                        $activityScore = $scores[$activityKey]['total_score'];
                                        $activityMax = $scores[$activityKey]['total_max_score'];
                                        $totalScore += $activityScore;
                                        $totalMaxScore += $activityMax;
                                        $activityCount++;
                                    }
                                    
                                    if (isset($scores[$examKey])) {
                                        $examScore = $scores[$examKey]['total_score'];
                                        $examMax = $scores[$examKey]['total_max_score'];
                                        $totalScore += $examScore;
                                        $totalMaxScore += $examMax;
                                        $activityCount++;
                                    }
                                    
                                    if (isset($scores[$participationKey])) {
                                        $participationScore = $scores[$participationKey]['total_score'];
                                        $participationMax = $scores[$participationKey]['total_max_score'];
                                        $totalScore += $participationScore;
                                        $totalMaxScore += $participationMax;
                                        $activityCount++;
                                    }
                                    
                                    // Display percentage only for each type
                                    echo '<td class="text-center">' . 
                                        ($activityMax > 0 ? ($scores[$activityKey]['percentage'] . '%') : '-') . '</td>';
                                    echo '<td class="text-center">' . 
                                        ($examMax > 0 ? ($scores[$examKey]['percentage'] . '%') : '-') . '</td>';
                                    echo '<td class="text-center">' . 
                                        ($participationMax > 0 ? ($scores[$participationKey]['percentage'] . '%') : '-') . '</td>';
                                }
                                
                                // Calculate overall average
                                $overallAverage = $totalMaxScore > 0 ? round(($totalScore / $totalMaxScore) * 100, 2) . '%' : '-';
                                echo '<td class="text-center"><strong>' . $overallAverage . '</strong></td>';
                                ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No students found for the selected criteria.</p>
        <?php endif; ?>
        
    <?php elseif ($report_type === 'grades'): ?>
        <div class="report-info">
            <h3>Student Grades Report</h3>
            <p>This report shows the quarterly grades and final remarks for each student.</p>
        </div>
        
        <?php if (!empty($students)): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Student Name</th>
                            <th>Section</th>
                            <th>Quarter 1 | Prelim</th>
                            <th>Quarter 2 | Midterm</th>
                            <th>Quarter 3 | Final</th>
                            <th>Quarter 4 | Summer</th>
                            <th>Average</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $passedCount = 0;
                        $failedCount = 0;
                        $incCount = 0;
                        $totalAverage = 0;
                        $gradeCount = 0;
                        
                        foreach ($students as $student): 
                            $studentGrades = isset($grades[$student['id']]) ? $grades[$student['id']] : null;
                            $remarks = $studentGrades ? $studentGrades['remarks'] : '';
                            $remarksClass = '';
                            
                            if ($remarks === 'Passed') {
                                $remarksClass = 'grade-passed';
                                $passedCount++;
                            } elseif ($remarks === 'Failed') {
                                $remarksClass = 'grade-failed';
                                $failedCount++;
                            } elseif ($remarks === 'INC') {
                                $remarksClass = 'grade-inc';
                                $incCount++;
                            }
                            
                            if ($studentGrades && !empty($studentGrades['average_grade'])) {
                                $totalAverage += $studentGrades['average_grade'];
                                $gradeCount++;
                            }
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($student['id_number']) ?></td>
                                <td><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']) ?></td>
                                <td><?= htmlspecialchars($section) ?></td>
                                <td class="text-center"><?= $studentGrades ? htmlspecialchars($studentGrades['quarter1_grade']) : '' ?></td>
                                <td class="text-center"><?= $studentGrades ? htmlspecialchars($studentGrades['quarter2_grade']) : '' ?></td>
                                <td class="text-center"><?= $studentGrades ? htmlspecialchars($studentGrades['quarter3_grade']) : '' ?></td>
                                <td class="text-center"><?= $studentGrades ? htmlspecialchars($studentGrades['quarter4_grade']) : '' ?></td>
                                <td class="text-center"><?= $studentGrades ? htmlspecialchars($studentGrades['average_grade']) : '' ?></td>
                                <td class="text-center <?= $remarksClass ?>"><?= $remarks ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="summary-stats">
                <div class="stat-box">
                    <h4>Total Students</h4>
                    <div class="value"><?= count($students) ?></div>
                </div>
                <div class="stat-box">
                    <h4>Passed</h4>
                    <div class="value"><?= $passedCount ?></div>
                </div>
                <div class="stat-box">
                    <h4>Failed</h4>
                    <div class="value"><?= $failedCount ?></div>
                </div>
                <div class="stat-box">
                    <h4>INC</h4>
                    <div class="value"><?= $incCount ?></div>
                </div>
                <div class="stat-box">
                    <h4>Class Average</h4>
                    <div class="value"><?= $gradeCount > 0 ? round($totalAverage / $gradeCount, 2) : '0.00' ?></div>
                </div>
            </div>
        <?php else: ?>
            <p>No students found for the selected criteria.</p>
        <?php endif; ?>
        
    <?php else: ?>
        <!-- Default Performance Report -->
        <div class="report-info">
            <h3>Student Performance Summary</h3>
            <p>This report provides an overview of student performance across all subjects.</p>
        </div>
        
        <?php if (!empty($filtered_mystudents)): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Student Name</th>
                            <th>Subject</th>
                            <th>Course</th>
                            <th>Section</th>
                            <th>Status</th>
                            <th>Academic Year</th>
                            <th>Semester</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filtered_mystudents as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['id_number']) ?></td>
                                <td><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']) ?></td>
                                <td><?= htmlspecialchars($student['subject_code'] . ' - ' . $student['subject_description']) ?></td>
                                <td><?= htmlspecialchars($student['coursename'] . ' - ' . $student['courselevel']) ?></td>
                                <td><?= htmlspecialchars($student['section']) ?></td>
                                <td><?= htmlspecialchars($student['status']) ?></td>
                                <td><?= htmlspecialchars($student['academic_year']) ?></td>
                                <td><?= htmlspecialchars($student['semester']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="summary-stats">
                <div class="stat-box">
                    <h4>Total Students</h4>
                    <div class="value"><?= count($filtered_mystudents) ?></div>
                </div>
                <div class="stat-box">
                    <h4>Subjects</h4>
                    <div class="value"><?= count(array_unique(array_column($filtered_mystudents, 'subject_id'))) ?></div>
                </div>
                <div class="stat-box">
                    <h4>Courses</h4>
                    <div class="value"><?= count(array_unique(array_column($filtered_mystudents, 'course_id'))) ?></div>
                </div>
                <div class="stat-box">
                    <h4>Sections</h4>
                    <div class="value"><?= count(array_unique(array_filter(array_column($filtered_mystudents, 'section')))) ?></div>
                </div>
            </div>
        <?php else: ?>
            <p>No students found for the selected criteria.</p>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="footer">
        <p>Report generated on <?= date('F j, Y, g:i a') ?></p>
        <p>&copy; <?= date('Y') ?> Teacher Portal. All rights reserved.</p>
    </div>
</body>
</html>