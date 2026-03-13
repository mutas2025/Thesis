<?php
require_once '../config.php';

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Student ID not provided.");
}

 $studentId = mysqli_real_escape_string($conn, $_GET['id']);

// Get student data
 $query = "SELECT * FROM students WHERE id = $studentId";
 $result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Error: Student not found.");
}

 $student = mysqli_fetch_assoc($result);

// Get enrollment data if exists
 $enrollmentQuery = "SELECT e.*, c.coursename, c.courselevel 
                   FROM enrollments e 
                   JOIN courses c ON e.course_id = c.id 
                   WHERE e.student_id = $studentId 
                   ORDER BY e.enrollment_date DESC LIMIT 1";
 $enrollmentResult = mysqli_query($conn, $enrollmentQuery);
 $enrollment = $enrollmentResult ? mysqli_fetch_assoc($enrollmentResult) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information Form</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    <style>
        @page {
            size: A4;
            margin: 0.5cm;
        }
        
        * {
            box-sizing: border-box;
            font-family: 'Times New Roman', Times, serif;
            font-size: 8pt;
            line-height: 1.2;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
        }
        
        .container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm;
            background: #fff;
            position: relative;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #333;
            padding-bottom: 8px;
        }
        
        .logo {
            width: 50px;
            height: 50px;
            margin-bottom: 5px;
        }
        
        .header h1 {
            font-size: 12pt; /* Increased slightly for School Name */
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header h2 {
            font-size: 9pt;
            margin: 3px 0 0;
            font-weight: normal;
        }
        
        .header p {
            font-size: 8pt;
            margin: 2px 0;
            padding: 0;
        }

        .form-title-section {
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid #ccc;
        }

        .form-title-section h2 {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
        }
        
        .student-info {
            margin-bottom: 15px;
        }
        
        .student-details {
            width: 100%;
        }
        
        .student-details h3 {
            font-size: 9pt;
            margin: 0 0 5px;
            font-weight: bold;
        }
        
        .student-id {
            font-size: 8pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            margin: 0 0 8px;
            padding-bottom: 3px;
            border-bottom: 1px solid #333;
            text-transform: uppercase;
        }
        
        .form-row {
            display: flex;
            margin-bottom: 5px;
        }
        
        .form-group {
            flex: 1;
            margin-right: 10px;
        }
        
        .form-group:last-child {
            margin-right: 0;
        }
        
        .form-group label {
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        
        .form-group .value {
            border-bottom: 1px dotted #333;
            padding-bottom: 1px;
            min-height: 12px;
        }
        
        .education-card {
            border: 1px solid #ccc;
            padding: 6px;
            margin-bottom: 8px;
            border-radius: 3px;
        }
        
        .education-card h4 {
            font-size: 8pt;
            margin: 0 0 5px;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .requirements {
            display: flex;
            flex-wrap: wrap;
        }
        
        .requirement-item {
            width: 50%;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .requirement-item .checkbox {
            width: 12px;
            height: 12px;
            border: 1px solid #333;
            margin-right: 6px;
            position: relative;
        }
        
        .requirement-item .checkbox.checked:after {
            content: '✓';
            position: absolute;
            top: -2px;
            left: 1px;
            font-weight: bold;
        }
        
        .text-requirement {
            width: 100%;
            margin-bottom: 8px;
        }
        
        .text-requirement label {
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        
        .text-requirement .value {
            border-bottom: 1px dotted #333;
            padding-bottom: 1px;
            min-height: 12px;
        }
        
        .footer {
            position: absolute;
            bottom: 10mm;
            left: 0;
            right: 0;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
        
        .footer .date {
            margin-bottom: 3px;
        }
        
        .footer .signature {
            display: flex;
            justify-content: space-around;
        }
        
        .footer .signature-box {
            width: 100px;
            height: 30px;
            border-bottom: 1px solid #333;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="../uploads/csr.png" alt="School Logo" class="logo">
            <!-- Updated to use config constants -->
            <h1><?= SCHOOL_NAME ?></h1>
            <p><?= SCHOOL_ADDRESS ?></p>
            <p>Tel: <?= SCHOOL_CONTACT_NO ?> | Email: <?= SCHOOL_EMAIL ?></p>
            
            <div class="form-title-section">
                <h2>Student Information Form</h2>
                <h2>Academic Year <?= date('Y') ?></h2>
            </div>
        </div>
        
        <div class="student-info">
            <div class="student-details">
                <h3><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']) ?></h3>
                <div class="student-id">ID Number: <?= htmlspecialchars($student['id_number']) ?></div>
                <div>Course: <?= $enrollment ? htmlspecialchars($enrollment['coursename'] . ' - ' . $enrollment['courselevel']) : 'Not Enrolled' ?></div>
                <div>Year Level: <?= $enrollment ? htmlspecialchars($enrollment['year_level']) : 'N/A' ?></div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Primary Details</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Last Name</label>
                    <div class="value"><?= htmlspecialchars($student['last_name']) ?></div>
                </div>
                <div class="form-group">
                    <label>First Name</label>
                    <div class="value"><?= htmlspecialchars($student['first_name']) ?></div>
                </div>
                <div class="form-group">
                    <label>Middle Name</label>
                    <div class="value"><?= htmlspecialchars($student['middle_name']) ?></div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Gender</label>
                    <div class="value"><?= htmlspecialchars($student['gender']) ?></div>
                </div>
                <div class="form-group">
                    <label>Birth Date</label>
                    <div class="value"><?= date('F d, Y', strtotime($student['birth_date'])) ?></div>
                </div>
                <div class="form-group">
                    <label>Age</label>
                    <div class="value"><?= htmlspecialchars($student['age']) ?></div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Birth Place</label>
                    <div class="value"><?= htmlspecialchars($student['birth_place']) ?></div>
                </div>
                <div class="form-group">
                    <label>Civil Status</label>
                    <div class="value"><?= htmlspecialchars($student['civil_status']) ?></div>
                </div>
                <div class="form-group">
                    <label>Nationality</label>
                    <div class="value"><?= htmlspecialchars($student['nationality']) ?></div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Religion</label>
                    <div class="value"><?= htmlspecialchars($student['religion']) ?></div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <div class="value"><?= htmlspecialchars($student['email']) ?></div>
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <div class="value"><?= htmlspecialchars($student['contact_number']) ?></div>
                </div>
            </div>
            <!-- Added new fields: LRN No and Contact Person -->
            <div class="form-row">
                <div class="form-group">
                    <label>LRN No</label>
                    <div class="value"><?= htmlspecialchars($student['lrn_no'] ?: 'N/A') ?></div>
                </div>
                <div class="form-group">
                    <label>Contact Person</label>
                    <div class="value"><?= htmlspecialchars($student['contact_person'] ?: 'N/A') ?></div>
                </div>
                <div class="form-group">
                    <label>Home Address</label>
                    <div class="value"><?= htmlspecialchars($student['home_address']) ?></div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Family Information</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Father's Name</label>
                    <div class="value"><?= htmlspecialchars($student['father_name'] ?: 'N/A') ?></div>
                </div>
                <div class="form-group">
                    <label>Father's Occupation</label>
                    <div class="value"><?= htmlspecialchars($student['father_occupation'] ?: 'N/A') ?></div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Mother's Name</label>
                    <div class="value"><?= htmlspecialchars($student['mother_name'] ?: 'N/A') ?></div>
                </div>
                <div class="form-group">
                    <label>Mother's Occupation</label>
                    <div class="value"><?= htmlspecialchars($student['mother_occupation'] ?: 'N/A') ?></div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Guardian's Name</label>
                    <div class="value"><?= htmlspecialchars($student['guardian_name'] ?: 'N/A') ?></div>
                </div>
                <div class="form-group">
                    <label>Guardian's Address</label>
                    <div class="value"><?= htmlspecialchars($student['guardian_address'] ?: 'N/A') ?></div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Other Person Supporting</label>
                    <div class="value"><?= htmlspecialchars($student['other_support'] ?: 'N/A') ?></div>
                </div>
                <div class="form-group">
                    <label>Living Arrangement</label>
                    <div class="value">
                        <?php
                        $arrangements = [];
                        if ($student['is_boarding']) $arrangements[] = 'Boarding';
                        if ($student['with_family']) $arrangements[] = 'With Family';
                        echo !empty($arrangements) ? implode(', ', $arrangements) : 'N/A';
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group" style="width: 100%;">
                    <label>Family Address</label>
                    <div class="value"><?= htmlspecialchars($student['family_address'] ?: 'N/A') ?></div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Educational Background</div>
            
            <?php if (!empty($student['elem_address']) || !empty($student['elem_year'])): ?>
            <div class="education-card">
                <h4>Elementary</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>School Address</label>
                        <div class="value"><?= htmlspecialchars($student['elem_address']) ?></div>
                    </div>
                    <div class="form-group">
                        <label>Academic Year</label>
                        <div class="value"><?= htmlspecialchars($student['elem_year']) ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($student['sec_address']) || !empty($student['sec_year'])): ?>
            <div class="education-card">
                <h4>Secondary</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>School Address</label>
                        <div class="value"><?= htmlspecialchars($student['sec_address']) ?></div>
                    </div>
                    <div class="form-group">
                        <label>Academic Year</label>
                        <div class="value"><?= htmlspecialchars($student['sec_year']) ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($student['college_address']) || !empty($student['college_year'])): ?>
            <div class="education-card">
                <h4>College</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>School Address</label>
                        <div class="value"><?= htmlspecialchars($student['college_address']) ?></div>
                    </div>
                    <div class="form-group">
                        <label>Academic Year</label>
                        <div class="value"><?= htmlspecialchars($student['college_year']) ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($student['voc_address']) || !empty($student['voc_year'])): ?>
            <div class="education-card">
                <h4>Vocational</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>School Address</label>
                        <div class="value"><?= htmlspecialchars($student['voc_address']) ?></div>
                    </div>
                    <div class="form-group">
                        <label>Academic Year</label>
                        <div class="value"><?= htmlspecialchars($student['voc_year']) ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($student['others_address']) || !empty($student['others_year'])): ?>
            <div class="education-card">
                <h4>Others</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label>School Address</label>
                        <div class="value"><?= htmlspecialchars($student['others_address']) ?></div>
                    </div>
                    <div class="form-group">
                        <label>Academic Year</label>
                        <div class="value"><?= htmlspecialchars($student['others_year']) ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <div class="section-title">Requirements</div>
            <div class="requirements">
                <div class="requirement-item">
                    <div class="checkbox <?= $student['form138'] ? 'checked' : '' ?>"></div>
                    <div>Form 138/SF9</div>
                </div>
                <div class="requirement-item">
                    <div class="checkbox <?= $student['moral_cert'] ? 'checked' : '' ?>"></div>
                    <div>Moral Certificate</div>
                </div>
                <div class="requirement-item">
                    <div class="checkbox <?= $student['birth_cert'] ? 'checked' : '' ?>"></div>
                    <div>Birth Certificate</div>
                </div>
                <div class="requirement-item">
                    <div class="checkbox <?= $student['good_moral'] ? 'checked' : '' ?>"></div>
                    <div>Good Moral Certificate</div>
                </div>
            </div>
            
            <!-- Changed Others 1, 2, 3 from checkboxes to text fields -->
            <div class="text-requirement">
                <label>Others 1</label>
                <div class="value"><?= htmlspecialchars($student['others1'] ?: 'N/A') ?></div>
            </div>
            
            <div class="text-requirement">
                <label>Others 2</label>
                <div class="value"><?= htmlspecialchars($student['others2'] ?: 'N/A') ?></div>
            </div>
            
            <div class="text-requirement">
                <label>Others 3</label>
                <div class="value"><?= htmlspecialchars($student['others3'] ?: 'N/A') ?></div>
            </div>
            
            <?php if (!empty($student['notes'])): ?>
            <div class="form-row" style="margin-top: 10px;">
                <div class="form-group" style="width: 100%;">
                    <label>Notes</label>
                    <div class="value"><?= htmlspecialchars($student['notes']) ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <div class="date">Date Printed: <?= date('F d, Y') ?></div>
            <div class="signature">
                <div>
                    <div>Student's Signature</div>
                    <div class="signature-box"></div>
                </div>
                <div>
                    <div>Registrar's Signature</div>
                    <div class="signature-box"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>