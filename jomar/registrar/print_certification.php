<?php
session_start();
require_once '../config.php';

// Check if enrollment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Enrollment ID is required");
}

 $enrollmentId = mysqli_real_escape_string($conn, $_GET['id']);

// Get enrollment data with student and course information
 $query = "SELECT e.*, c.coursename, c.courselevel, s.* 
          FROM enrollments e
          JOIN students s ON e.student_id = s.id
          JOIN courses c ON e.course_id = c.id
          WHERE e.id = $enrollmentId";
 $result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Error: Enrollment not found");
}

 $enrollment = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">
    <title>Certificate of Enrollment - <?= $enrollment['last_name'] . ', ' . $enrollment['first_name'] . ' ' . $enrollment['middle_name'] ?></title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            background: #fff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 1cm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            /* MODIFIED: Changed to left alignment */
            text-align: left;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: flex-start; /* Aligns items to the left */
            gap: 20px;
        }
        
        .logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }
        
        .header-text {
            flex: 1;
        }
        
        .header h1 {
            font-size: 18pt;
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
            font-size: 20pt;
            font-weight: bold;
            margin: 30px 0 20px;
            text-decoration: underline;
        }
        
        .subtitle {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 30px;
        }
        
        .content {
            text-align: justify;
            margin-bottom: 40px;
            font-size: 12pt;
        }
        
        .content p {
            margin-bottom: 15px;
            text-indent: 50px;
        }
        
        .student-info {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .info-column {
            width: 50%;
            padding: 5px 10px;
            box-sizing: border-box;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 10px;
        }
        
        .info-label {
            width: 150px;
            font-weight: bold;
        }
        
        .info-value {
            flex: 1;
        }
        
        .enrollment-info {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .enrollment-column {
            width: 50%;
            padding: 5px 10px;
            box-sizing: border-box;
        }
        
        .footer {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .signature-row {
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 50%;
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 1px;
            width: 200px;
            margin: 5px auto 15px;
        }
        
        .date-box {
            text-align: right;
            margin-top: 20px;
        }
        
        .date-line {
            border-bottom: 1px solid #000;
            height: 1px;
            width: 150px;
            margin: 5px 0 5px auto;
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
    <button class="print-button" onclick="window.print()">Print Certification</button>
    
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
    
    <div class="title">CERTIFICATION</div>
    <div class="subtitle">TO WHOM IT MAY CONCERN:</div>
    
    <div class="content">
        <p>This is to certify that <strong><?= $enrollment['first_name'] . ' ' . $enrollment['middle_name'] . ' ' . $enrollment['last_name'] ?></strong> is officially enrolled as a student of <strong><?= SCHOOL_NAME ?></strong> for the <?= $enrollment['academic_year'] ?> Academic Year, <?= $enrollment['semester'] ?> Semester.</p>
        
        <p>The student is in good standing and is currently pursuing <?= $enrollment['coursename'] ?> (<?= $enrollment['courselevel'] ?>) in this institution.</p>
        
        <p>This certification is being issued upon the request of the student for whatever legal purpose it may serve.</p>
    </div>
    
    <div class="student-info">
        <div class="info-column">
            <div class="info-row">
                <div class="info-label">Student ID:</div>
                <div class="info-value"><?= $enrollment['id_number'] ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Full Name:</div>
                <div class="info-value"><?= $enrollment['last_name'] . ', ' . $enrollment['first_name'] . ' ' . $enrollment['middle_name'] ?></div>
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
                <div class="info-label">Contact No:</div>
                <div class="info-value"><?= $enrollment['contact_number'] ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Address:</div>
                <div class="info-value"><?= $enrollment['home_address'] ?></div>
            </div>
        </div>
    </div>
    
    <div class="enrollment-info">
        <div class="enrollment-column">
            <div class="info-row">
                <div class="info-label">Academic Year:</div>
                <div class="info-value"><?= $enrollment['academic_year'] ?></div>
            </div>
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
            <div class="info-row">
                <div class="info-label">Year Level:</div>
                <div class="info-value"><?= $enrollment['courselevel'] ?></div>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <div class="signature-row">
            <div class="signature-box">
                <div>Certified by:</div>
                <div class="signature-line"></div>
                <div><strong>REGISTRAR</strong></div>
            </div>
        </div>
        
        <div class="date-box">
            <div>Issued on:</div>
            <div class="date-line"></div>
            <div><?= date('F d, Y') ?></div>
        </div>
    </div>
</body>
</html>