<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is a program head
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'program_head') {
    header("Location: login.php");
    exit();
}

// Get program head details
 $program_head_id = $_SESSION['user_id'];
 $query = "SELECT ph.*, d.dept_name FROM program_heads ph 
         JOIN departments d ON ph.department_id = d.id 
         WHERE ph.id = ?";
 $stmt = $conn->prepare($query);
 $stmt->bind_param("i", $program_head_id);
 $stmt->execute();
 $result = $stmt->get_result();
 $program_head = $result->fetch_assoc();
 $department_id = $program_head['department_id'];

// Get filters
 $academic_year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';
 $semester = isset($_GET['semester']) ? $_GET['semester'] : '';
 $year_level = isset($_GET['year_level']) ? $_GET['year_level'] : '';
 $section = isset($_GET['section']) ? $_GET['section'] : '';

// Get course name if year level is selected
 $course_name = '';
if (!empty($year_level)) {
    $course_query = "SELECT DISTINCT c.coursename 
                     FROM courses c 
                     JOIN enrollments e ON e.course_id = c.id 
                     WHERE c.department_id = ? AND e.year_level = ? 
                     LIMIT 1";
    $stmt = $conn->prepare($course_query);
    $stmt->bind_param("is", $department_id, $year_level);
    $stmt->execute();
    $course_result = $stmt->get_result();
    if ($course_row = $course_result->fetch_assoc()) {
        $course_name = $course_row['coursename'];
    }
}

// Build query
 $students_query = "SELECT 
                    s.id, 
                    s.id_number, 
                    s.gender,
                    CONCAT(s.last_name, ', ', s.first_name, ' ', IFNULL(s.middle_name, '')) as student_name,
                    e.year_level, 
                    e.academic_year, 
                    e.semester, 
                    e.status,
                    e.section,
                    c.coursename
                  FROM enrollments e
                  JOIN students s ON e.student_id = s.id
                  JOIN courses c ON e.course_id = c.id
                  WHERE c.department_id = ? AND e.status IN ('Enrolled', 'Registered')";

 $params = [$department_id];
 $types = 'i';

if (!empty($academic_year)) {
    $students_query .= " AND e.academic_year = ?";
    $params[] = $academic_year;
    $types .= 's';
}

if (!empty($semester)) {
    $students_query .= " AND e.semester = ?";
    $params[] = $semester;
    $types .= 's';
}

if (!empty($year_level)) {
    $students_query .= " AND e.year_level = ?";
    $params[] = $year_level;
    $types .= 's';
}

if (!empty($section)) {
    $students_query .= " AND e.section = ?";
    $params[] = $section;
    $types .= 's';
}

 $students_query .= " ORDER BY s.last_name, s.first_name";

 $stmt = $conn->prepare($students_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
 $stmt->execute();
 $students_result = $stmt->get_result();
 $all_students = [];
while ($student = $students_result->fetch_assoc()) {
    $all_students[] = $student;
}

// Separate students by gender
 $male_students = [];
 $female_students = [];
foreach ($all_students as $student) {
    if (strtolower($student['gender']) === 'male') {
        $male_students[] = $student;
    } elseif (strtolower($student['gender']) === 'female') {
        $female_students[] = $student;
    }
}

// Get distinct academic years, semesters, year levels, and sections for dropdowns
 $academic_years_query = "SELECT DISTINCT academic_year FROM enrollments 
                         JOIN courses ON enrollments.course_id = courses.id
                         WHERE courses.department_id = ? 
                         ORDER BY academic_year DESC";
 $stmt = $conn->prepare($academic_years_query);
 $stmt->bind_param("i", $department_id);
 $stmt->execute();
 $academic_years_result = $stmt->get_result();
 $academic_years = [];
while ($row = $academic_years_result->fetch_assoc()) {
    $academic_years[] = $row['academic_year'];
}

 $semesters_query = "SELECT DISTINCT semester FROM enrollments 
                    JOIN courses ON enrollments.course_id = courses.id
                    WHERE courses.department_id = ? 
                    ORDER BY semester";
 $stmt = $conn->prepare($semesters_query);
 $stmt->bind_param("i", $department_id);
 $stmt->execute();
 $semesters_result = $stmt->get_result();
 $semesters = [];
while ($row = $semesters_result->fetch_assoc()) {
    $semesters[] = $row['semester'];
}

 $year_levels_query = "SELECT DISTINCT year_level FROM enrollments 
                      JOIN courses ON enrollments.course_id = courses.id
                      WHERE courses.department_id = ? 
                      ORDER BY year_level";
 $stmt = $conn->prepare($year_levels_query);
 $stmt->bind_param("i", $department_id);
 $stmt->execute();
 $year_levels_result = $stmt->get_result();
 $year_levels = [];
while ($row = $year_levels_result->fetch_assoc()) {
    $year_levels[] = $row['year_level'];
}

// Get distinct sections
 $sections_query = "SELECT DISTINCT section FROM enrollments 
                   JOIN courses ON enrollments.course_id = courses.id
                   WHERE courses.department_id = ? AND section IS NOT NULL AND section != ''
                   ORDER BY section";
 $stmt = $conn->prepare($sections_query);
 $stmt->bind_param("i", $department_id);
 $stmt->execute();
 $sections_result = $stmt->get_result();
 $sections = [];
while ($row = $sections_result->fetch_assoc()) {
    $sections[] = $row['section'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Masterlist</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #fff;
            color: #000;
        }
        
        .document {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 20mm;
            background: #fff;
            position: relative;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .logo {
            max-height: 50px;
            margin-bottom: 10px;
        }
        
        .school-name {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        
        .department {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .document-title {
            font-size: 16pt;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }
        
        .document-info {
            margin: 20px 0;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .info-row {
            display: flex;
            margin: 5px 0;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        
        .info-value {
            flex: 1;
        }
        
        .no-print {
            display: none;
        }
        
        @media screen {
            body {
                background-color: #f5f5f5;
                padding: 20px;
            }
            
            .document {
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                margin-bottom: 20px;
            }
            
            .no-print {
                display: block;
                margin-bottom: 20px;
                padding: 20px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 5px;
            }
            
            .filter-form {
                background: #f9f9f9;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 15px;
            }
            
            .filter-row {
                display: flex;
                gap: 15px;
                margin-bottom: 10px;
            }
            
            .filter-col {
                flex: 1;
            }
            
            .form-group {
                margin-bottom: 10px;
            }
            
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
                font-size: 11pt;
            }
            
            select, button {
                width: 100%;
                padding: 8px;
                border: 1px solid #ccc;
                border-radius: 3px;
                font-size: 11pt;
            }
            
            button {
                background-color: #007bff;
                color: white;
                border: none;
                cursor: pointer;
                font-weight: bold;
            }
            
            button:hover {
                background-color: #0056b3;
            }
            
            .print-button {
                background-color: #28a745;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 3px;
                cursor: pointer;
                font-weight: bold;
                font-size: 12pt;
                display: inline-block;
                text-decoration: none;
            }
            
            .print-button:hover {
                background-color: #1e7e34;
            }
        }
        
        .gender-section {
            margin: 25px 0;
            page-break-inside: avoid;
        }
        
        .gender-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 15px 0 10px 0;
            text-align: center;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        
        .gender-count {
            text-align: right;
            font-style: italic;
            margin-bottom: 10px;
            font-size: 11pt;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 11pt;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 10pt;
        }
        
        td {
            font-size: 10pt;
        }
        
        .no {
            text-align: center;
            width: 30px;
        }
        
        .id-number {
            text-align: center;
            width: 80px;
        }
        
        .name {
            width: 200px;
        }
        
        .section {
            text-align: center;
            width: 60px;
        }
        
        .academic-year {
            text-align: center;
            width: 80px;
        }
        
        .semester {
            text-align: center;
            width: 60px;
        }
        
        .status {
            text-align: center;
            width: 80px;
        }
        
        .no-data {
            text-align: center;
            font-style: italic;
            padding: 20px;
        }
        
        .footer {
            margin-top: 40px;
            border-top: 1px solid #000;
            padding-top: 20px;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            margin: 40px 0 5px 0;
            height: 1px;
        }
        
        .signature-label {
            font-size: 10pt;
        }
        
        .total-count {
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
            font-size: 11pt;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- Screen-only controls -->
    <div class="no-print">
        <div class="filter-form">
            <form method="get" action="">
                <div class="filter-row">
                    <div class="filter-col">
                        <div class="form-group">
                            <label for="academic_year">Academic Year</label>
                            <select name="academic_year" id="academic_year">
                                <option value="">All Years</option>
                                <?php foreach ($academic_years as $year): ?>
                                    <option value="<?= $year ?>" <?= ($academic_year == $year) ? 'selected' : '' ?>><?= $year ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="filter-col">
                        <div class="form-group">
                            <label for="semester">Semester</label>
                            <select name="semester" id="semester">
                                <option value="">All Semesters</option>
                                <?php foreach ($semesters as $sem): ?>
                                    <option value="<?= $sem ?>" <?= ($semester == $sem) ? 'selected' : '' ?>><?= $sem ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="filter-col">
                        <div class="form-group">
                            <label for="year_level">Year Level</label>
                            <select name="year_level" id="year_level">
                                <option value="">All Year Levels</option>
                                <?php foreach ($year_levels as $yl): ?>
                                    <option value="<?= $yl ?>" <?= ($year_level == $yl) ? 'selected' : '' ?>><?= $yl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="filter-col">
                        <div class="form-group">
                            <label for="section">Section</label>
                            <select name="section" id="section">
                                <option value="">All Sections</option>
                                <?php foreach ($sections as $sec): ?>
                                    <option value="<?= $sec ?>" <?= ($section == $sec) ? 'selected' : '' ?>><?= $sec ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="filter-col">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit">Apply Filters</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <a href="#" class="print-button" onclick="window.print(); return false;">
            <i class="fas fa-print"></i> Print Masterlist
        </a>
    </div>
    
    <!-- Document content -->
    <div class="document">
        <div class="header">
            <img src="../uploads/csr.png" alt="School Logo" class="logo">
            <div class="school-name">Colegio De Santa Rita De San Carlos, Inc.</div>
            <div class="department"><?= $program_head['dept_name'] ?> Department</div>
            <div class="document-title">Student Masterlist</div>
        </div>
        
        <div class="document-info">
            <div class="info-row">
                <div class="info-label">Academic Year:</div>
                <div class="info-value"><?= $academic_year ?: 'All Years' ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Semester:</div>
                <div class="info-value"><?= $semester ?: 'All Semesters' ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Year Level:</div>
                <div class="info-value"><?= $year_level ?: 'All Year Levels' ?></div>
            </div>
            <?php if (!empty($course_name)): ?>
                <div class="info-row">
                    <div class="info-label">Course:</div>
                    <div class="info-value"><?= $course_name ?></div>
                </div>
            <?php endif; ?>
            <div class="info-row">
                <div class="info-label">Section:</div>
                <div class="info-value"><?= $section ?: 'All Sections' ?></div>
            </div>
        </div>
        
        <!-- Male Students Section -->
        <div class="gender-section">
            <h2 class="gender-title">Male Students</h2>
            <p class="gender-count">Total: <?= count($male_students) ?> students</p>
            <table>
                <thead>
                    <tr>
                        <th class="no">No.</th>
                        <th class="id-number">ID Number</th>
                        <th class="name">Student Name</th>
                        <th class="status">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($male_students)): ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($male_students as $student): ?>
                            <tr>
                                <td class="no"><?= $counter++ ?></td>
                                <td class="id-number"><?= $student['id_number'] ?></td>
                                <td class="name"><?= $student['student_name'] ?></td>
                                <td class="status"><?= $student['status'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data">No male students found with the selected filters.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Female Students Section -->
        <div class="gender-section">
            <h2 class="gender-title">Female Students</h2>
            <p class="gender-count">Total: <?= count($female_students) ?> students</p>
            <table>
                <thead>
                    <tr>
                        <th class="no">No.</th>
                        <th class="id-number">ID Number</th>
                        <th class="name">Student Name</th>
                        <th class="status">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($female_students)): ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($female_students as $student): ?>
                            <tr>
                                <td class="no"><?= $counter++ ?></td>
                                <td class="id-number"><?= $student['id_number'] ?></td>
                                <td class="name"><?= $student['student_name'] ?></td>
                                <td class="status"><?= $student['status'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data">No female students found with the selected filters.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="total-count">
            Total Students: <?= count($all_students) ?> (Male: <?= count($male_students) ?>, Female: <?= count($female_students) ?>)
        </div>
        
        <div class="footer">
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Prepared by:</div>
                    <div class="signature-label"><?= $program_head['name'] ?></div>
                    <div class="signature-label">Program Head</div>
                    <div class="signature-label"><?= $program_head['dept_name'] ?></div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Certified Correct:</div>
                    <div class="signature-label">&nbsp;</div>
                    <div class="signature-label">&nbsp;</div>
                </div>
                <div class="signature-box">
                    <div class="signature-label">Date Prepared:</div>
                    <div class="signature-label"><?= date('F d, Y') ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>