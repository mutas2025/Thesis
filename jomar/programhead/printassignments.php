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
 $course_id = isset($_GET['course_id']) ? $_GET['course_id'] : '';
 $section = isset($_GET['section']) ? $_GET['section'] : '';

// Get course name if course_id is selected
 $course_name = '';
if (!empty($course_id)) {
    $course_query = "SELECT coursename FROM courses WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($course_query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $course_result = $stmt->get_result();
    if ($course_row = $course_result->fetch_assoc()) {
        $course_name = $course_row['coursename'];
    }
}

// Build query
 $assignments_query = "SELECT 
                        ta.id,
                        ta.teacher_id,
                        ta.subject_id,
                        ta.course_id,
                        ta.academic_year,
                        ta.semester,
                        ta.section,
                        ta.hours,
                        ta.notes,
                        IFNULL(t.name, 'Unassigned') as teacher_name,
                        s.subject_code,
                        s.subject_description,
                        c.coursename,
                        c.courselevel
                      FROM teacherassignments ta 
                      LEFT JOIN teachers t ON ta.teacher_id = t.id 
                      JOIN subjects s ON ta.subject_id = s.id 
                      JOIN courses c ON ta.course_id = c.id 
                      WHERE c.department_id = ?";

 $params = [$department_id];
 $types = 'i';

if (!empty($academic_year)) {
    $assignments_query .= " AND ta.academic_year = ?";
    $params[] = $academic_year;
    $types .= 's';
}

if (!empty($semester)) {
    $assignments_query .= " AND ta.semester = ?";
    $params[] = $semester;
    $types .= 's';
}

if (!empty($course_id)) {
    $assignments_query .= " AND ta.course_id = ?";
    $params[] = $course_id;
    $types .= 'i';
}

if (!empty($section)) {
    $assignments_query .= " AND ta.section = ?";
    $params[] = $section;
    $types .= 's';
}

 $assignments_query .= " ORDER BY ta.academic_year DESC, ta.semester DESC, c.coursename, s.subject_code";

 $stmt = $conn->prepare($assignments_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
 $stmt->execute();
 $assignments_result = $stmt->get_result();
 $all_assignments = [];
while ($assignment = $assignments_result->fetch_assoc()) {
    $all_assignments[] = $assignment;
}

// Get distinct academic years, semesters, courses, and sections for dropdowns
 $academic_years_query = "SELECT DISTINCT academic_year FROM teacherassignments 
                         JOIN courses ON teacherassignments.course_id = courses.id
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

 $semesters_query = "SELECT DISTINCT semester FROM teacherassignments 
                    JOIN courses ON teacherassignments.course_id = courses.id
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

 $courses_query = "SELECT id, coursename, courselevel FROM courses 
                 WHERE department_id = ? 
                 ORDER BY coursename";
 $stmt = $conn->prepare($courses_query);
 $stmt->bind_param("i", $department_id);
 $stmt->execute();
 $courses_result = $stmt->get_result();
 $courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
}

 $sections_query = "SELECT DISTINCT section FROM teacherassignments 
                  JOIN courses ON teacherassignments.course_id = courses.id
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
    <title>Teacher Assignments</title>
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
        
        .assignment-section {
            margin: 25px 0;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            margin: 15px 0 10px 0;
            text-align: center;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        
        .section-count {
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
        
        .teacher {
            width: 150px;
        }
        
        .subject {
            width: 200px;
        }
        
        .course {
            width: 150px;
        }
        
        .section {
            text-align: center;
            width: 60px;
        }
        
        .hours {
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
        
        .notes {
            width: 150px;
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
                            <label for="course_id">Course</label>
                            <select name="course_id" id="course_id">
                                <option value="">All Courses</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?= $course['id'] ?>" <?= ($course_id == $course['id']) ? 'selected' : '' ?>><?= $course['coursename'] ?> (<?= $course['courselevel'] ?>)</option>
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
            <i class="fas fa-print"></i> Print Assignments
        </a>
    </div>
    
    <!-- Document content -->
    <div class="document">
        <div class="header">
            <img src="../uploads/csr.png" alt="School Logo" class="logo">
            <div class="school-name">Colegio De Santa Rita De San Carlos, Inc.</div>
            <div class="department"><?= $program_head['dept_name'] ?> Department</div>
            <div class="document-title">Teacher Assignments</div>
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
                <div class="info-label">Course:</div>
                <div class="info-value"><?= $course_name ?: 'All Courses' ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Section:</div>
                <div class="info-value"><?= $section ?: 'All Sections' ?></div>
            </div>
        </div>
        
        <div class="assignment-section">
            <h2 class="section-title">Teacher Assignments</h2>
            <p class="section-count">Total: <?= count($all_assignments) ?> assignments</p>
            <table>
                <thead>
                    <tr>
                        <th class="no">No.</th>
                        <th class="teacher">Teacher</th>
                        <th class="subject">Subject</th>
                        <th class="course">Course</th>
                        <th class="section">Section</th>
                        <th class="hours">Hours</th>
                        <th class="academic-year">Academic Year</th>
                        <th class="semester">Semester</th>
                        <th class="notes">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($all_assignments)): ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($all_assignments as $assignment): ?>
                            <tr>
                                <td class="no"><?= $counter++ ?></td>
                                <td class="teacher"><?= $assignment['teacher_name'] ?></td>
                                <td class="subject"><?= $assignment['subject_code'] ?> - <?= $assignment['subject_description'] ?></td>
                                <td class="course"><?= $assignment['coursename'] ?> (<?= $assignment['courselevel'] ?>)</td>
                                <td class="section"><?= $assignment['section'] ?: '-' ?></td>
                                <td class="hours"><?= $assignment['hours'] ?: '-' ?></td>
                                <td class="academic-year"><?= $assignment['academic_year'] ?></td>
                                <td class="semester"><?= $assignment['semester'] ?></td>
                                <td class="notes"><?= $assignment['notes'] ?: '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="no-data">No assignments found with the selected filters.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="total-count">
            Total Assignments: <?= count($all_assignments) ?>
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