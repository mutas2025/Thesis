<?php
require_once '../config.php';
requireRole('registrar');

// Get filter parameters
 $courseId = isset($_GET['course']) ? intval($_GET['course']) : 0;
 $academicYear = isset($_GET['academic_year']) ? mysqli_real_escape_string($conn, $_GET['academic_year']) : '';
 $semester = isset($_GET['semester']) ? mysqli_real_escape_string($conn, $_GET['semester']) : '';

// Get courses for filter dropdown
 $courses = [];
 $query = "SELECT *, CONCAT(coursename, ' - ', courselevel) as course_full_name FROM courses ORDER BY coursename, courselevel";
 $result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}

// Get academic years for filter dropdown
 $academicYears = [];
 $query = "SELECT DISTINCT academic_year FROM academic_years ORDER BY academic_year DESC";
 $result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $academicYears[] = $row['academic_year'];
    }
}

// Get semester options
 $semesterOptions = ['1st', '2nd', 'Summer'];

// Get students based on filters
 $students = [];
 $filterInfo = "All Courses, All Academic Years, All Semesters";

// Build the query based on filters
 $query = "SELECT s.id_number, CONCAT(s.last_name, ', ', s.first_name, ' ', s.middle_name) as student_name, s.gender, 
                 c.coursename, c.courselevel, e.academic_year, e.semester, e.status
          FROM students s
          JOIN enrollments e ON s.id = e.student_id
          JOIN courses c ON e.course_id = c.id
          WHERE e.status IN ('Registered', 'Enrolled')";

if ($courseId > 0) {
    $query .= " AND e.course_id = $courseId";
}

if (!empty($academicYear)) {
    $query .= " AND e.academic_year = '$academicYear'";
}

if (!empty($semester)) {
    $query .= " AND e.semester = '$semester'";
}

 $query .= " ORDER BY s.gender DESC, s.last_name, s.first_name";
 $result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }
}

// Get selected filter names
 $selectedCourse = 'All Courses';
if ($courseId > 0) {
    foreach ($courses as $course) {
        if ($course['id'] == $courseId) {
            $selectedCourse = $course['coursename'] . ' - ' . $course['courselevel'];
            break;
        }
    }
}

 $selectedYear = !empty($academicYear) ? $academicYear : 'All Academic Years';
 $selectedSemester = !empty($semester) ? $semester : 'All Semesters';

// Build filter info string
 $filterInfo = "$selectedCourse, $selectedYear, $selectedSemester";

// Separate students by gender
 $maleStudents = [];
 $femaleStudents = [];
foreach ($students as $student) {
    if ($student['gender'] === 'Male') {
        $maleStudents[] = $student;
    } else {
        $femaleStudents[] = $student;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Masterlist</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 210mm;
            margin: 0 auto;
            padding: 10mm;
            box-sizing: border-box;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header .logo {
            margin-bottom: 10px;
        }
        
        .header .logo img {
            height: 50px;
        }
        
        .header h1 {
            font-size: 18pt;
            margin: 5px 0;
            padding: 0;
        }
        
        .header h2 {
            font-size: 14pt;
            margin: 5px 0;
            font-weight: normal;
        }
        
        .header .school-name {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .header p {
            font-size: 10pt;
            margin: 2px 0;
        }
        
        .filter-info {
            margin-bottom: 15px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        
        .gender-section {
            margin-bottom: 20px;
        }
        
        .gender-header {
            background-color: #f0f0f0;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #000;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .student-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .student-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        
        .student-table td {
            border: 1px solid #000;
            padding: 8px;
        }
        
        .no-print {
            display: block;
            margin-bottom: 20px;
        }
        
        .filter-btn {
            padding: 8px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 500px;
            border-radius: 5px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: black;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .btn-primary {
            padding: 8px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-secondary {
            padding: 8px 15px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        
        .number-col {
            width: 5%;
            text-align: center;
        }
        
        .id-col {
            width: 20%;
        }
        
        .name-col {
            width: 75%;
        }
        
        .gender-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .filter-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .filter-col {
            flex: 1;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            body {
                background: none;
            }
            
            .container {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print">
            <button class="filter-btn" onclick="openModal()">Select Filters</button>
            <button type="button" onclick="window.print()" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;" <?= empty($students) ? 'disabled' : '' ?>>Print</button>
        </div>
        
        <!-- Filter Modal -->
        <div id="filterModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Enrollment Filters</h2>
                <form method="GET" action="printenrollmentmasterlist.php">
                    <div class="filter-row">
                        <div class="filter-col">
                            <div class="form-group">
                                <label for="course">Course:</label>
                                <select name="course" id="course">
                                    <option value="0" <?= ($courseId == 0) ? 'selected' : '' ?>>All Courses</option>
                                    <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>" <?= ($course['id'] == $courseId) ? 'selected' : '' ?>>
                                        <?= $course['course_full_name'] ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="filter-col">
                            <div class="form-group">
                                <label for="academic_year">Academic Year:</label>
                                <select name="academic_year" id="academic_year">
                                    <option value="" <?= (empty($academicYear)) ? 'selected' : '' ?>>All Years</option>
                                    <?php foreach ($academicYears as $year): ?>
                                    <option value="<?= $year ?>" <?= ($academicYear == $year) ? 'selected' : '' ?>>
                                        <?= $year ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="filter-row">
                        <div class="filter-col">
                            <div class="form-group">
                                <label for="semester">Semester:</label>
                                <select name="semester" id="semester">
                                    <option value="" <?= (empty($semester)) ? 'selected' : '' ?>>All Semesters</option>
                                    <?php foreach ($semesterOptions as $option): ?>
                                    <option value="<?= $option ?>" <?= ($semester == $option) ? 'selected' : '' ?>>
                                        <?= $option ?> Semester
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Apply Filters</button>
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                </form>
            </div>
        </div>
        
        <div class="header">
            <div class="logo">
                <img src="../uploads/csr.png" alt="School Logo">
            </div>
            <!-- Updated to use config constants -->
            <div class="school-name"><?= SCHOOL_NAME ?></div>
            <p><?= SCHOOL_ADDRESS ?></p>
            <p>Tel: <?= SCHOOL_CONTACT_NO ?> | Email: <?= SCHOOL_EMAIL ?></p>
            <h1>ENROLLMENT MASTERLIST</h1>
            <h2><?= $filterInfo ?></h2>
        </div>
        
        <div class="filter-info">
            Filter: <?= $filterInfo ?> | Total Students: <?= count($students) ?>
        </div>
        
        <div class="gender-stats">
            <div>Male Students: <?= count($maleStudents) ?></div>
            <div>Female Students: <?= count($femaleStudents) ?></div>
        </div>
        
        <?php if (!empty($maleStudents)): ?>
        <div class="gender-section">
            <div class="gender-header">MALE STUDENTS</div>
            <table class="student-table">
                <thead>
                    <tr>
                        <th class="number-col">#</th>
                        <th class="id-col">ID Number</th>
                        <th class="name-col">Student Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($maleStudents as $index => $student): ?>
                    <tr>
                        <td style="text-align: center;"><?= $index + 1 ?></td>
                        <td><?= $student['id_number'] ?></td>
                        <td><?= $student['student_name'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($femaleStudents)): ?>
        <div class="gender-section">
            <div class="gender-header">FEMALE STUDENTS</div>
            <table class="student-table">
                <thead>
                    <tr>
                        <th class="number-col">#</th>
                        <th class="id-col">ID Number</th>
                        <th class="name-col">Student Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($femaleStudents as $index => $student): ?>
                    <tr>
                        <td style="text-align: center;"><?= $index + 1 ?></td>
                        <td><?= $student['id_number'] ?></td>
                        <td><?= $student['student_name'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if (empty($maleStudents) && empty($femaleStudents)): ?>
        <p style="text-align: center; font-style: italic; margin-top: 30px;">No students found for the selected filters.</p>
        <?php endif; ?>
        
        <div style="margin-top: 20px; text-align: right;">
            <p>Generated on: <?= date('F d, Y') ?></p>
            <p>Generated by: <?= $_SESSION['username'] ?></p>
        </div>
    </div>
    
    <script>
        function openModal() {
            document.getElementById('filterModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('filterModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('filterModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>