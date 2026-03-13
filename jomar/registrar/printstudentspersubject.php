<?php
require_once '../config.php';
requireRole('registrar');

// Get filter parameters
 $courseId = isset($_GET['course']) ? intval($_GET['course']) : 0;
 $yearLevel = isset($_GET['year_level']) ? mysqli_real_escape_string($conn, $_GET['year_level']) : '';
 $section = isset($_GET['section']) ? mysqli_real_escape_string($conn, $_GET['section']) : '';
 $subjectId = isset($_GET['subject']) ? intval($_GET['subject']) : 0;

// Get courses for filter dropdown
 $courses = [];
 $query = "SELECT *, CONCAT(coursename, ' - ', courselevel) as course_full_name FROM courses ORDER BY coursename, courselevel";
 $result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}

// Get year levels for filter dropdown
 $yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

// Get sections for filter dropdown
 $sections = ['A', 'B', 'C', 'D', 'E'];

// Get subjects for filter dropdown
 $subjectsForFilter = [];
 $query = "SELECT sub.id, sub.subject_code, sub.subject_description, c.coursename, c.courselevel
          FROM subjects sub
          JOIN courses c ON sub.course_id = c.id
          WHERE 1=1";

if ($courseId > 0) {
    $query .= " AND sub.course_id = $courseId";
}

if (!empty($yearLevel)) {
    $query .= " AND sub.year_level = '$yearLevel'";
}

 $query .= " ORDER BY c.coursename, c.courselevel, sub.subject_code";
 $result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $subjectsForFilter[] = $row;
    }
}

// Get subjects based on filters
 $subjects = [];
 $filterInfo = "All Courses, All Year Levels, All Sections, All Subjects";

// Build the base query
 $query = "SELECT DISTINCT sub.id, sub.subject_code, sub.subject_description, c.coursename, c.courselevel
          FROM subjects sub
          JOIN courses c ON sub.course_id = c.id
          WHERE 1=1";

if ($courseId > 0) {
    $query .= " AND sub.course_id = $courseId";
}

if (!empty($yearLevel)) {
    $query .= " AND sub.year_level = '$yearLevel'";
}

if ($subjectId > 0) {
    $query .= " AND sub.id = $subjectId";
}

 $query .= " ORDER BY c.coursename, c.courselevel, sub.subject_code";
 $result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $subjects[] = $row;
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

 $selectedYearLevel = !empty($yearLevel) ? $yearLevel : 'All Year Levels';
 $selectedSection = !empty($section) ? $section : 'All Sections';
 $selectedSubject = 'All Subjects';
if ($subjectId > 0) {
    foreach ($subjectsForFilter as $subject) {
        if ($subject['id'] == $subjectId) {
            $selectedSubject = $subject['subject_code'] . ' - ' . $subject['subject_description'];
            break;
        }
    }
}

// Build filter info string
 $filterInfo = "$selectedCourse, $selectedYearLevel, $selectedSection, $selectedSubject";

// For each subject, get the students enrolled
 $subjectsWithStudents = [];
foreach ($subjects as $subject) {
    $studentQuery = "SELECT s.id_number, CONCAT(s.last_name, ', ', s.first_name, ' ', s.middle_name) as student_name, s.gender
                     FROM students s
                     JOIN student_subjects ss ON s.id = ss.student_id
                     JOIN enrollments e ON ss.enrollment_id = e.id
                     WHERE ss.subject_id = {$subject['id']} AND e.status IN ('Registered', 'Enrolled')";
    
    // Apply section filter if provided
    if (!empty($section)) {
        $studentQuery .= " AND e.section = '$section'";
    }
    
    $studentQuery .= " ORDER BY s.gender DESC, s.last_name, s.first_name";
    
    $studentResult = mysqli_query($conn, $studentQuery);
    
    if (mysqli_num_rows($studentResult) > 0) {
        $students = [];
        while ($studentRow = mysqli_fetch_assoc($studentResult)) {
            $students[] = $studentRow;
        }
        
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
        
        $subjectsWithStudents[] = [
            'subject' => $subject,
            'maleStudents' => $maleStudents,
            'femaleStudents' => $femaleStudents,
            'totalStudents' => count($students)
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Per Subject</title>
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
        
        .subject-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .subject-header {
            background-color: #f0f0f0;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #000;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .gender-section {
            margin-bottom: 20px;
        }
        
        .gender-header {
            background-color: #e9ecef;
            font-weight: bold;
            padding: 6px;
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
        
        .name-col {
            width: 95%;
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
            
            .subject-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print">
            <button class="filter-btn" onclick="openModal()">Select Filters</button>
            <button type="button" onclick="window.print()" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;" <?= empty($subjectsWithStudents) ? 'disabled' : '' ?>>Print</button>
        </div>
        
        <!-- Filter Modal -->
        <div id="filterModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Subject Filters</h2>
                <form method="GET" action="printstudentspersubject.php">
                    <div class="filter-row">
                        <div class="filter-col">
                            <div class="form-group">
                                <label for="course">Course:</label>
                                <select name="course" id="course" onchange="updateSubjects()">
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
                                <label for="year_level">Year Level:</label>
                                <select name="year_level" id="year_level" onchange="updateSubjects()">
                                    <option value="" <?= (empty($yearLevel)) ? 'selected' : '' ?>>All Year Levels</option>
                                    <?php foreach ($yearLevels as $level): ?>
                                    <option value="<?= $level ?>" <?= ($yearLevel == $level) ? 'selected' : '' ?>>
                                        <?= $level ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="filter-row">
                        <div class="filter-col">
                            <div class="form-group">
                                <label for="section">Section:</label>
                                <select name="section" id="section">
                                    <option value="" <?= (empty($section)) ? 'selected' : '' ?>>All Sections</option>
                                    <?php foreach ($sections as $sec): ?>
                                    <option value="<?= $sec ?>" <?= ($section == $sec) ? 'selected' : '' ?>>
                                        Section <?= $sec ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="filter-col">
                            <div class="form-group">
                                <label for="subject">Subject:</label>
                                <select name="subject" id="subject">
                                    <option value="0" <?= ($subjectId == 0) ? 'selected' : '' ?>>All Subjects</option>
                                    <?php foreach ($subjectsForFilter as $subject): ?>
                                    <option value="<?= $subject['id'] ?>" <?= ($subject['id'] == $subjectId) ? 'selected' : '' ?>>
                                        <?= $subject['subject_code'] ?> - <?= $subject['subject_description'] ?>
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
            <h1>STUDENTS PER SUBJECT</h1>
            <h2><?= $filterInfo ?></h2>
        </div>
        
        <div class="filter-info">
            Filter: <?= $filterInfo ?> | Total Subjects: <?= count($subjectsWithStudents) ?>
        </div>
        
        <?php if (!empty($subjectsWithStudents)): ?>
            <?php foreach ($subjectsWithStudents as $subjectData): ?>
                <div class="subject-section">
                    <div class="subject-header">
                        <?= $subjectData['subject']['subject_code'] ?> - <?= $subjectData['subject']['subject_description'] ?>
                        <br>
                        <?= $subjectData['subject']['coursename'] ?> - <?= $subjectData['subject']['courselevel'] ?>
                        <br>
                        Total Students: <?= $subjectData['totalStudents'] ?>
                    </div>
                    
                    <div class="gender-stats">
                        <div>Male Students: <?= count($subjectData['maleStudents']) ?></div>
                        <div>Female Students: <?= count($subjectData['femaleStudents']) ?></div>
                    </div>
                    
                    <?php if (!empty($subjectData['maleStudents'])): ?>
                    <div class="gender-section">
                        <div class="gender-header">MALE STUDENTS</div>
                        <table class="student-table">
                            <thead>
                                <tr>
                                    <th class="number-col">#</th>
                                    <th class="name-col">Student Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjectData['maleStudents'] as $index => $student): ?>
                                <tr>
                                    <td style="text-align: center;"><?= $index + 1 ?></td>
                                    <td><?= $student['student_name'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($subjectData['femaleStudents'])): ?>
                    <div class="gender-section">
                        <div class="gender-header">FEMALE STUDENTS</div>
                        <table class="student-table">
                            <thead>
                                <tr>
                                    <th class="number-col">#</th>
                                    <th class="name-col">Student Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subjectData['femaleStudents'] as $index => $student): ?>
                                <tr>
                                    <td style="text-align: center;"><?= $index + 1 ?></td>
                                    <td><?= $student['student_name'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; font-style: italic; margin-top: 30px;">No subjects found for selected filters.</p>
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
        
        // Function to update subjects dropdown based on course and year level selection
        function updateSubjects() {
            const courseId = document.getElementById('course').value;
            const yearLevel = document.getElementById('year_level').value;
            
            // Create an AJAX request to get filtered subjects
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_subjects_for_filter.php?course=' + courseId + '&year_level=' + yearLevel, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Parse the JSON response
                    const subjects = JSON.parse(xhr.responseText);
                    
                    // Update the subjects dropdown
                    const subjectSelect = document.getElementById('subject');
                    subjectSelect.innerHTML = '<option value="0">All Subjects</option>';
                    
                    subjects.forEach(function(subject) {
                        const option = document.createElement('option');
                        option.value = subject.id;
                        option.textContent = subject.subject_code + ' - ' + subject.subject_description;
                        subjectSelect.appendChild(option);
                    });
                }
            };
            xhr.send();
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