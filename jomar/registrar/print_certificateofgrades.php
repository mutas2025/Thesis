<?php
session_start();
require_once '../config.php';

// Check if enrollment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Enrollment ID is required");
}
 $enrollmentId = mysqli_real_escape_string($conn, $_GET['id']);

// Get enrollment data with student and course information
 $query = "SELECT e.*, s.id_number, s.last_name, s.first_name, s.middle_name, s.gender, 
                 s.birth_date, s.age, s.birth_place, s.civil_status, s.nationality, 
                 s.religion, s.contact_number, s.home_address, s.email,
                 c.coursename, c.courselevel 
          FROM enrollments e
          JOIN students s ON e.student_id = s.id
          JOIN courses c ON e.course_id = c.id
          WHERE e.id = $enrollmentId";
 $result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    die("Error: Enrollment not found");
}
 $enrollment = mysqli_fetch_assoc($result);

// Get enrolled subjects with grades for this enrollment
 $query = "SELECT ss.subject_id, sub.subject_code, sub.subject_description, sub.unit, 
                 sg.quarter1_grade, sg.quarter2_grade, sg.quarter3_grade, sg.quarter4_grade, sg.average_grade, sg.remarks
          FROM student_subjects ss
          JOIN subjects sub ON ss.subject_id = sub.id
          LEFT JOIN student_grades sg ON ss.student_id = sg.student_id AND ss.subject_id = sg.subject_id AND ss.enrollment_id = sg.enrollment_id
          WHERE ss.enrollment_id = $enrollmentId
          ORDER BY sub.subject_code";
 $result = mysqli_query($conn, $query);
 $subjects = [];
 $totalUnits = 0;
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $subjects[] = $row;
        $totalUnits += $row['unit'];
    }
}

// Calculate general average
 $generalAverage = 0;
 $gradedSubjects = 0;
foreach ($subjects as $subject) {
    if (!empty($subject['average_grade'])) {
        $generalAverage += $subject['average_grade'];
        $gradedSubjects++;
    }
}
if ($gradedSubjects > 0) {
    $generalAverage = $generalAverage / $gradedSubjects;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Grades - <?= $enrollment['last_name'] . ', ' . $enrollment['first_name'] . ' ' . $enrollment['middle_name'] ?></title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    <style>
        @page {
            size: A4;
            margin: 0.5cm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.3;
            color: #000;
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 0.5cm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            /* MODIFIED: Changed to left alignment */
            text-align: left;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: flex-start; /* Aligns items to the left */
            gap: 20px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        
        .header-text {
            flex: 1;
        }
        
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0;
            padding: 0;
        }
        
        .header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
            padding: 0;
        }
        
        .header p {
            font-size: 11pt;
            margin: 2px 0;
            padding: 0;
        }
        
        .title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 20px 0 15px;
            text-decoration: underline;
        }
        
        .section {
            margin-bottom: 15px;
        }
        
        .section-title {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }
        
        .student-info {
            display: flex;
            flex-wrap: wrap;
        }
        
        .info-column {
            width: 50%;
            padding: 3px 8px 3px 0;
            box-sizing: border-box;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 6px;
        }
        
        .info-label {
            width: 140px;
            font-weight: bold;
            color: #0066cc; /* Blue color for labels */
        }
        
        .info-value {
            flex: 1;
        }
        
        .enrollment-info {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        
        .enrollment-column {
            width: 33.33%;
            padding: 3px 8px 3px 0;
            box-sizing: border-box;
        }
        
        .grading-note {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 10px;
            margin: 10px 0;
            font-size: 11pt;
        }
        
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11pt;
        }
        
        .grades-table th, .grades-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        
        .grades-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .grades-table td {
            text-align: center;
        }
        
        .grades-table td:first-child {
            text-align: left;
        }
        
        .grades-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .total-row {
            font-weight: bold;
        }
        
        .summary {
            margin-top: 15px;
        }
        
        .summary table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary td {
            padding: 5px;
        }
        
        .summary td:first-child {
            width: 200px;
            font-weight: bold;
            color: #0066cc;
        }
        
        .footer {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .signature-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }
        
        .signature-box {
            flex: 1;
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 1px;
            width: 100%;
            margin: 5px 0 15px;
        }
        
        .date-box {
            text-align: center;
            margin-top: 10px;
        }
        
        .date-line {
            border-bottom: 1px solid #000;
            height: 1px;
            width: 150px;
            margin: 5px auto;
        }
        
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-button:hover {
            background-color: #0069d9;
        }
        
        @media print {
            .print-button {
                display: none;
            }
            
            body {
                margin: 0;
                padding: 0;
                width: 210mm;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">Print Certificate</button>
    
    <div class="header">
        <img src="../uploads/csr.png" alt="School Logo" class="logo">
        <div class="header-text">
            <!-- Updated to use config constants -->
            <h1><?= SCHOOL_NAME ?></h1>
            <h2>OFFICE OF THE REGISTRAR</h2>
            <p><?= SCHOOL_ADDRESS ?></p>
            <p>Tel: <?= SCHOOL_CONTACT_NO ?> | Email: <?= SCHOOL_EMAIL ?></p>
        </div>
    </div>
    
    <div class="title">CERTIFICATE OF GRADES</div>
    
    <div class="section">
        <div class="section-title">STUDENT INFORMATION</div>
        <div class="student-info">
            <div class="info-column">
                <div class="info-row">
                    <div class="info-label">Student ID:</div>
                    <div class="info-value"><?= $enrollment['id_number'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Last Name:</div>
                    <div class="info-value"><?= $enrollment['last_name'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">First Name:</div>
                    <div class="info-value"><?= $enrollment['first_name'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Middle Name:</div>
                    <div class="info-value"><?= $enrollment['middle_name'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Gender:</div>
                    <div class="info-value"><?= $enrollment['gender'] ?></div>
                </div>
            </div>
            <div class="info-column">
                <div class="info-row">
                    <div class="info-label">Birth Date:</div>
                    <div class="info-value"><?= date('F d, Y', strtotime($enrollment['birth_date'])) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Age:</div>
                    <div class="info-value"><?= $enrollment['age'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Contact No:</div>
                    <div class="info-value"><?= $enrollment['contact_number'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><?= $enrollment['email'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Address:</div>
                    <div class="info-value"><?= $enrollment['home_address'] ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">ENROLLMENT INFORMATION</div>
        <div class="enrollment-info">
            <div class="enrollment-column">
                <div class="info-row">
                    <div class="info-label">Academic Year:</div>
                    <div class="info-value"><?= $enrollment['academic_year'] ?></div>
                </div>
            </div>
            <div class="enrollment-column">
                <div class="info-row">
                    <div class="info-label">Semester:</div>
                    <div class="info-value"><?= $enrollment['semester'] ?></div>
                </div>
            </div>
            <div class="enrollment-column">
                <div class="info-row">
                    <div class="info-label">Course:</div>
                    <div class="info-value"><?= $enrollment['coursename'] ?></div>
                </div>
            </div>
            <div class="enrollment-column">
                <div class="info-row">
                    <div class="info-label">Year Level:</div>
                    <div class="info-value"><?= $enrollment['year_level'] ?></div>
                </div>
            </div>
            <div class="enrollment-column">
                <div class="info-row">
                    <div class="info-label">Status:</div>
                    <div class="info-value"><?= $enrollment['status'] ?></div>
                </div>
            </div>
        </div>
        
       
    </div>
    
    <div class="section">
        <div class="section-title">SUBJECTS AND GRADES</div>
        <table class="grades-table">
            <thead>
                <tr>
                    <th width="15%">Subject Code</th>
                    <th width="30%">Subject Description</th>
                    <th width="8%">Units</th>
                    <th width="8%">Quarter 1 | PRELIM</th>
                    <th width="8%">Quarter 2 | MIDTERM</th>
                    <th width="8%">Quarter 3 | FINALS</th>
                    <th width="8%">Quarter 4 | SUMMER</th>
                    <th width="8%">Average</th>
                    <th width="7%">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject): ?>
                <tr>
                    <td><?= $subject['subject_code'] ?></td>
                    <td><?= $subject['subject_description'] ?></td>
                    <td><?= $subject['unit'] ?></td>
                    <td><?= $subject['quarter1_grade'] ?: '' ?></td>
                    <td><?= $subject['quarter2_grade'] ?: '' ?></td>
                    <td><?= $subject['quarter3_grade'] ?: '' ?></td>
                    <td><?= $subject['quarter4_grade'] ?: '' ?></td>
                    <td><?= $subject['average_grade'] ?: '' ?></td>
                    <td><?= $subject['remarks'] ?: '' ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;">TOTAL UNITS:</td>
                    <td><?= $totalUnits ?></td>
                    <td colspan="6"></td>
                </tr>
            </tbody>
        </table>
        
        <div class="summary">
            <table>
                <tr>
                    <td>General Average:</td>
                    <td><?= number_format($generalAverage, 2) ?></td>
                </tr>
                <tr>
                    <td>Date Issued:</td>
                    <td><?= date('F d, Y') ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="footer">
        <div class="signature-row">
        
            <div class="signature-box">
                <div>Certified Correct:</div>
                <div class="signature"></div>
                <div>Registrar</div>
            </div>
        </div>
        
        <div class="date-box">
            <div>Date Printed:</div>
            <div class="date-line"></div>
            <div><?= date('F d, Y') ?></div>
        </div>
    </div>
</body>
</html>