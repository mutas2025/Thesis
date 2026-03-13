<?php
session_start();
require_once '../config.php';
// Check if enrollment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Enrollment ID is required");
}
 $enrollmentId = mysqli_real_escape_string($conn, $_GET['id']);

// Get enrollment data
 $query = "SELECT e.*, c.coursename, c.courselevel 
          FROM enrollments e
          JOIN courses c ON e.course_id = c.id
          WHERE e.id = $enrollmentId";
 $result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    die("Error: Enrollment not found");
}
 $enrollment = mysqli_fetch_assoc($result);

// Get student data
 $studentId = $enrollment['student_id'];
 $query = "SELECT * FROM students WHERE id = $studentId";
 $result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    die("Error: Student not found");
}
 $student = mysqli_fetch_assoc($result);

// Get enrolled subjects for this enrollment
 $query = "SELECT ss.*, s.subject_code, s.subject_description, s.unit
          FROM student_subjects ss
          JOIN subjects s ON ss.subject_id = s.id
          WHERE ss.enrollment_id = {$enrollment['id']}
          ORDER BY s.subject_code";
 $result = mysqli_query($conn, $query);
 $subjects = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $subjects[] = $row;
    }
}

// Calculate total units
 $totalUnits = 0;
foreach ($subjects as $subject) {
    $totalUnits += $subject['unit'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Enrollment Form - <?= $student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name'] ?></title>
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
            justify-content: flex-start; /* Aligns flex items to the left */
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
        
        .subjects-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11pt;
        }
        
        .subjects-table th, .subjects-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        
        .subjects-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .subjects-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .total-row {
            font-weight: bold;
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
    <button class="print-button" onclick="window.print()">Print Form</button>
    
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
    
    <div class="title">ENROLLMENT FORM</div>
    
    <div class="section">
        <div class="section-title">STUDENT INFORMATION</div>
        <div class="student-info">
            <div class="info-column">
                <div class="info-row">
                    <div class="info-label">Student ID:</div>
                    <div class="info-value"><?= $student['id_number'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Last Name:</div>
                    <div class="info-value"><?= $student['last_name'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">First Name:</div>
                    <div class="info-value"><?= $student['first_name'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Middle Name:</div>
                    <div class="info-value"><?= $student['middle_name'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Gender:</div>
                    <div class="info-value"><?= $student['gender'] ?></div>
                </div>
            </div>
            <div class="info-column">
                <div class="info-row">
                    <div class="info-label">Birth Date:</div>
                    <div class="info-value"><?= date('F d, Y', strtotime($student['birth_date'])) ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Age:</div>
                    <div class="info-value"><?= $student['age'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Contact No:</div>
                    <div class="info-value"><?= $student['contact_number'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><?= $student['email'] ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Address:</div>
                    <div class="info-value"><?= $student['home_address'] ?></div>
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
                    <div class="info-label">Status:</div>
                    <div class="info-value"><?= $enrollment['status'] ?></div>
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
                    <div class="info-label">Enrollment Date:</div>
                    <div class="info-value"><?= date('F d, Y', strtotime($enrollment['enrollment_date'])) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">ENROLLED SUBJECTS</div>
        <table class="subjects-table">
            <thead>
                <tr>
                    <th width="15%">Subject Code</th>
                    <th width="55%">Subject Description</th>
                    <th width="15%">Units</th>
                    <th width="15%">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject): ?>
                <tr>
                    <td><?= $subject['subject_code'] ?></td>
                    <td><?= $subject['subject_description'] ?></td>
                    <td><?= $subject['unit'] ?></td>
                    <td><?= $subject['status'] ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;">TOTAL UNITS:</td>
                    <td><?= $totalUnits ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        <div class="signature-row">
            <div class="signature-box">
                <div>Student's Signature</div>
                <div class="signature-line"></div>
                <div><?= $student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name'] ?></div>
            </div>
            <div class="signature-box">
                <div>Registrar's Signature</div>
                <div class="signature-line"></div>
                <div>Registrar</div>
            </div>
        </div>
        
        <div class="signature-row">
            <div class="signature-box">
                <div>Principal</div>
                <div class="signature-line"></div>
                <div>Program Head</div>
            </div>
            <div class="signature-box">
                <div>Guidance Office</div>
                <div class="signature-line"></div>
                <div>Guidance Counselor</div>
            </div>
            <div class="signature-box">
                <div>Treasurer's Office</div>
                <div class="signature-line"></div>
                <div>Treasurer</div>
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