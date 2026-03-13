<?php
session_start();
require_once '../config.php';
requireRole('registrar');

// Get filter parameters
 $academicYear = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';
 $semester = isset($_GET['semester']) ? $_GET['semester'] : '';
 $courseId = isset($_GET['course_id']) ? $_GET['course_id'] : '';
 $subjectId = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';

// Get academic years
 $academicYears = [];
 $yearQuery = "SELECT DISTINCT academic_year FROM academic_years ORDER BY academic_year DESC";
 $yearResult = mysqli_query($conn, $yearQuery);
while ($yearRow = mysqli_fetch_assoc($yearResult)) {
    $academicYears[] = $yearRow['academic_year'];
}

// Get courses
 $courses = [];
 $courseQuery = "SELECT *, CONCAT(coursename, ' - ', courselevel) as course_full_name FROM courses ORDER BY coursename, courselevel";
 $courseResult = mysqli_query($conn, $courseQuery);
while ($courseRow = mysqli_fetch_assoc($courseResult)) {
    $courses[] = $courseRow;
}

// Get all subjects for the dropdown
 $allSubjects = [];
 $subjectQuery = "SELECT id, subject_code, subject_description FROM subjects ORDER BY subject_code";
 $subjectResult = mysqli_query($conn, $subjectQuery);
while ($subjectRow = mysqli_fetch_assoc($subjectResult)) {
    $allSubjects[] = $subjectRow;
}

// Initialize variables
 $students = [];
 $subjects = [];
 $grades = [];

// If filters are applied, get the data
if (!empty($academicYear) && !empty($semester)) {
    // Build the WHERE clause
    $whereClause = "WHERE e.academic_year = '" . mysqli_real_escape_string($conn, $academicYear) . "' 
                   AND e.semester = '" . mysqli_real_escape_string($conn, $semester) . "'";
    
    if (!empty($courseId)) {
        $whereClause .= " AND e.course_id = " . mysqli_real_escape_string($conn, $courseId);
    }
    
    // Get students with enrollments
    $studentQuery = "SELECT DISTINCT s.id, s.id_number, s.last_name, s.first_name, s.middle_name, 
                     c.coursename, c.courselevel
                     FROM students s
                     JOIN enrollments e ON s.id = e.student_id
                     JOIN courses c ON e.course_id = c.id
                     $whereClause
                     ORDER BY s.last_name, s.first_name";
    
    $studentResult = mysqli_query($conn, $studentQuery);
    while ($studentRow = mysqli_fetch_assoc($studentResult)) {
        $students[] = $studentRow;
    }
    
    // Get subjects for these enrollments
    $subjectWhereClause = $whereClause;
    if (!empty($subjectId)) {
        $subjectWhereClause .= " AND sub.id = " . mysqli_real_escape_string($conn, $subjectId);
    }
    
    $subjectQuery = "SELECT DISTINCT sub.id, sub.subject_code, sub.subject_description, sub.unit
                     FROM subjects sub
                     JOIN student_subjects ss ON sub.id = ss.subject_id
                     JOIN enrollments e ON ss.enrollment_id = e.id
                     $subjectWhereClause
                     ORDER BY sub.subject_code";
    
    $subjectResult = mysqli_query($conn, $subjectQuery);
    while ($subjectRow = mysqli_fetch_assoc($subjectResult)) {
        $subjects[] = $subjectRow;
    }
    
    // Get grades for these students and subjects
    if (!empty($students) && !empty($subjects)) {
        $studentIds = implode(',', array_column($students, 'id'));
        $subjectIds = implode(',', array_column($subjects, 'id'));
        
        $gradeQuery = "SELECT sg.student_id, sg.subject_id, sg.enrollment_id,
                       sg.quarter1_grade, sg.quarter2_grade, sg.quarter3_grade, sg.quarter4_grade, 
                       sg.average_grade, sg.remarks
                       FROM student_grades sg
                       JOIN enrollments e ON sg.enrollment_id = e.id
                       WHERE sg.student_id IN ($studentIds) 
                       AND sg.subject_id IN ($subjectIds)
                       AND e.academic_year = '" . mysqli_real_escape_string($conn, $academicYear) . "'
                       AND e.semester = '" . mysqli_real_escape_string($conn, $semester) . "'";
        
        $gradeResult = mysqli_query($conn, $gradeQuery);
        while ($gradeRow = mysqli_fetch_assoc($gradeResult)) {
            $grades[] = $gradeRow;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grade Sheet</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-height: 80px;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
        }
        .header p {
            font-size: 14px;
            margin: 2px 0;
        }
        .filters {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .table-container {
            overflow-x: auto;
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
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .student-id {
            text-align: center;
        }
        .student-name {
            min-width: 200px;
        }
        .subject-header {
            min-width: 300px;
            text-align: center;
        }
        .grade-cell {
            text-align: center;
            min-width: 60px;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 45%;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            height: 30px;
        }
        .signature-label {
            font-size: 14px;
        }
        .print-btn {
            margin-bottom: 20px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .container {
                width: 100%;
                padding: 10px;
            }
            .header {
                margin-bottom: 10px;
            }
            .table-container {
                overflow-x: visible;
            }
            th, td {
                padding: 5px;
                font-size: 12px;
            }
            .student-name {
                min-width: 150px;
            }
            .subject-header {
                min-width: 250px;
            }
            .grade-cell {
                min-width: 50px;
            }
            .signature-section {
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="../uploads/csr.png" alt="CSR Logo">
            <!-- Updated to use config constants -->
            <h1><?= SCHOOL_NAME ?></h1>
            <p><?= SCHOOL_ADDRESS ?></p>
            <p>Tel: <?= SCHOOL_CONTACT_NO ?> | Email: <?= SCHOOL_EMAIL ?></p>
            <h2>Student Grade Sheet</h2>
            <?php if (!empty($academicYear) && !empty($semester)): ?>
                <h3><?= $academicYear ?> - <?= $semester ?> Semester</h3>
                <?php if (!empty($courseId)): ?>
                    <?php foreach ($courses as $course): ?>
                        <?php if ($course['id'] == $courseId): ?>
                            <h3><?= $course['course_full_name'] ?></h3>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (!empty($subjectId)): ?>
                    <?php foreach ($allSubjects as $subject): ?>
                        <?php if ($subject['id'] == $subjectId): ?>
                            <h3><?= $subject['subject_code'] ?> - <?= $subject['subject_description'] ?></h3>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="filters no-print">
            <form method="GET" action="printgradesheet.php">
                <div class="form-row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="academic_year">Academic Year</label>
                            <select class="form-control" id="academic_year" name="academic_year" required>
                                <option value="">Select Year</option>
                                <?php foreach ($academicYears as $year): ?>
                                    <option value="<?= $year ?>" <?= ($academicYear == $year) ? 'selected' : '' ?>><?= $year ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="semester">Semester</label>
                            <select class="form-control" id="semester" name="semester" required>
                                <option value="">Select</option>
                                <option value="1st" <?= ($semester == '1st') ? 'selected' : '' ?>>1st</option>
                                <option value="2nd" <?= ($semester == '2nd') ? 'selected' : '' ?>>2nd</option>
                                <option value="Summer" <?= ($semester == 'Summer') ? 'selected' : '' ?>>Summer</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="course_id">Course</label>
                            <select class="form-control" id="course_id" name="course_id">
                                <option value="">All Courses</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>" <?= ($courseId == $course['id']) ? 'selected' : '' ?>><?= $course['course_full_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="subject_id">Subject</label>
                            <select class="form-control" id="subject_id" name="subject_id">
                                <option value="">All Subjects</option>
                                <?php foreach ($allSubjects as $subject): ?>
                                    <option value="<?= $subject['id'] ?>" <?= ($subjectId == $subject['id']) ? 'selected' : '' ?>><?= $subject['subject_code'] ?> - <?= $subject['subject_description'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <button type="button" class="btn btn-success" onclick="window.print()">Print</button>
                                <button type="button" class="btn btn-secondary" onclick="resetFilters()">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if (empty($academicYear) || empty($semester)): ?>
            <div class="no-data">
                Please select academic year and semester to view grades.
            </div>
        <?php elseif (empty($students)): ?>
            <div class="no-data">
                No students found for the selected filters.
            </div>
        <?php elseif (empty($subjects)): ?>
            <div class="no-data">
                No subjects found for the selected filters.
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th rowspan="2" class="student-id">ID Number</th>
                            <th rowspan="2" class="student-name">Student Name</th>
                            <th rowspan="2">Course</th>
                            <?php foreach ($subjects as $subject): ?>
                                <th colspan="5" class="subject-header"><?= $subject['subject_code'] ?></th>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <?php foreach ($subjects as $subject): ?>
                                <th class="grade-cell">Q1 | PRELIM</th>
                                <th class="grade-cell">Q2 | MIDTERM</th>
                                <th class="grade-cell">Q3 | FINAL</th>
                                <th class="grade-cell">Q4 | SUMMER</th>
                                <th class="grade-cell">Average</th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td class="student-id"><?= $student['id_number'] ?></td>
                                <td class="student-name"><?= $student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name'] ?></td>
                                <td><?= $student['coursename'] . ' - ' . $student['courselevel'] ?></td>
                                <?php foreach ($subjects as $subject): ?>
                                    <?php 
                                    // Initialize grade values
                                    $q1 = $q2 = $q3 = $q4 = $avg = '-';
                                    
                                    // Find the grade for this student and subject
                                    foreach ($grades as $grade) {
                                        if ($grade['student_id'] == $student['id'] && $grade['subject_id'] == $subject['id']) {
                                            $q1 = !empty($grade['quarter1_grade']) ? $grade['quarter1_grade'] : '-';
                                            $q2 = !empty($grade['quarter2_grade']) ? $grade['quarter2_grade'] : '-';
                                            $q3 = !empty($grade['quarter3_grade']) ? $grade['quarter3_grade'] : '-';
                                            $q4 = !empty($grade['quarter4_grade']) ? $grade['quarter4_grade'] : '-';
                                            $avg = !empty($grade['average_grade']) ? $grade['average_grade'] : '-';
                                            break;
                                        }
                                    }
                                    ?>
                                    <td class="grade-cell"><?= $q1 ?></td>
                                    <td class="grade-cell"><?= $q2 ?></td>
                                    <td class="grade-cell"><?= $q3 ?></td>
                                    <td class="grade-cell"><?= $q4 ?></td>
                                    <td class="grade-cell"><?= $avg ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Prepared by</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Approved by</div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="footer no-print">
            <p>Generated on: <?= date('F d, Y') ?></p>
        </div>
    </div>

    <script>
        function resetFilters() {
            window.location.href = 'printgradesheet.php';
        }
    </script>
</body>
</html>