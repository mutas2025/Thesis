<?php
session_start();
require_once '../config.php';

// Check if ID and Type are provided
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['type']) || empty($_GET['type'])) {
    die("Error: Record ID and Type are required.");
}

 $id = mysqli_real_escape_string($conn, $_GET['id']);
 $type = mysqli_real_escape_string($conn, $_GET['type']);

 $data = null;
 $title = "";
 $content = "";
 $customCss = ""; 

// Switch based on type to fetch data and prepare HTML content
switch ($type) {
    case 'appointment':
        $title = "APPOINTMENT SLIP";
        $query = "SELECT a.*, s.id_number, CONCAT(s.last_name, ', ', s.first_name, ' ', s.middle_name) as student_name, s.gender, s.contact_number, s.home_address
                  FROM appointments a
                  JOIN students s ON a.student_id = s.id
                  WHERE a.appointment_id = '$id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $datetime = date('F d, Y - h:i A', strtotime($row['appointment_datetime']));
            
            $content = "
                <div class='student-info'>
                    <div class='info-column'>
                        <div class='info-row'><div class='info-label'>Student ID:</div><div class='info-value'>{$row['id_number']}</div></div>
                        <div class='info-row'><div class='info-label'>Name:</div><div class='info-value'>{$row['student_name']}</div></div>
                    </div>
                    <div class='info-column'>
                        <div class='info-row'><div class='info-label'>Contact:</div><div class='info-value'>{$row['contact_number']}</div></div>
                        <div class='info-row'><div class='info-label'>Gender:</div><div class='info-value'>{$row['gender']}</div></div>
                    </div>
                </div>
                <div class='content'>
                    <p><strong>Appointment Schedule:</strong> $datetime</p>
                    <p><strong>Purpose:</strong> {$row['purpose']}</p>
                    <p><strong>Status:</strong> {$row['status']}</p>
                    <p>Please bring this slip on the scheduled date. If you need to reschedule, please contact the Guidance Office immediately.</p>
                </div>
            ";
        }
        break;

    case 'session':
        $title = "COUNSELING SESSION RECORD";
        // FIXED FORMAT: Exact same structure as Incidents
        $query = "SELECT cs.*, s.id_number, CONCAT(s.last_name, ', ', s.first_name, ' ', s.middle_name) as student_name
                  FROM counseling_sessions cs
                  JOIN students s ON cs.student_id = s.id
                  WHERE cs.session_id = '$id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $date = date('F d, Y', strtotime($row['session_date']));
            
            $content = "
                <div class='student-info'>
                    <div class='info-column'>
                        <div class='info-row'><div class='info-label'>Student ID:</div><div class='info-value'>{$row['id_number']}</div></div>
                        <div class='info-row'><div class='info-label'>Name:</div><div class='info-value'>{$row['student_name']}</div></div>
                    </div>
                    <div class='info-column'>
                        <div class='info-row'><div class='info-label'>Session Date:</div><div class='info-value'>$date</div></div>
                        <div class='info-row'><div class='info-label'>Counseling Type:</div><div class='info-value'>{$row['counseling_type']}</div></div>
                    </div>
                </div>
                <div class='content'>
                    <p><strong>Status:</strong> {$row['session_status']}</p>
                    <p><strong>Reason:</strong> {$row['reason']}</p>
                    <p><strong>Referred By:</strong> {$row['referred_by']}</p>
                    <p><strong>Notes:</strong> {$row['session_notes']}</p>
                    <p><strong>Recommendations:</strong> {$row['recommendations']}</p>
                    <p><strong>Follow Up:</strong> " . ($row['follow_up_date'] ? date('F d, Y', strtotime($row['follow_up_date'])) : 'None') . "</p>
                </div>
            ";
        }
        break;

    case 'incident':
        $title = "INCIDENT REPORT";
        $query = "SELECT i.*, s.id_number, CONCAT(s.last_name, ', ', s.first_name, ' ', s.middle_name) as student_name
                  FROM incidents i
                  JOIN students s ON i.student_id = s.id
                  WHERE i.incident_id = '$id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $date = date('F d, Y', strtotime($row['incident_date']));
            
            $content = "
                <div class='student-info'>
                    <div class='info-column'>
                        <div class='info-row'><div class='info-label'>Student ID:</div><div class='info-value'>{$row['id_number']}</div></div>
                        <div class='info-row'><div class='info-label'>Name:</div><div class='info-value'>{$row['student_name']}</div></div>
                    </div>
                    <div class='info-column'>
                        <div class='info-row'><div class='info-label'>Incident Date:</div><div class='info-value'>$date</div></div>
                        <div class='info-row'><div class='info-label'>Incident Type:</div><div class='info-value'>{$row['incident_type']}</div></div>
                    </div>
                </div>
                <div class='content'>
                    <p><strong>Description:</strong> {$row['description']}</p>
                    <p><strong>Action Taken:</strong> {$row['action_taken']}</p>
                    <p><strong>Resolution Status:</strong> {$row['resolution_status']}</p>
                    <p><strong>Counselor Remarks:</strong> {$row['counselor_remarks']}</p>
                </div>
            ";
        }
        break;

    case 'exam':
        $title = "ENTRANCE EXAMINATION RESULT";
        $query = "SELECT * FROM exam_results WHERE exam_id = '$id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            $content = "
                <div class='student-info'>
                    <div class='info-column'>
                        <div class='info-row'><div class='info-label'>Student Name:</div><div class='info-value'>{$row['student_name']}</div></div>
                        <div class='info-row'><div class='info-label'>Department:</div><div class='info-value'>{$row['department_name']}</div></div>
                    </div>
                </div>
                <div class='content'>
                    <h3 style='text-align:center; text-decoration:underline; margin-bottom:20px;'>Test Scores</h3>
                    <p><strong>Standardized Test Raw Score:</strong> {$row['std_raw_score']}</p>
                    <p><strong>Standardized Test Percentile Rank:</strong> {$row['std_percentile_rank']}</p>
                    <p><strong>Standardized Test Verbal Desc:</strong> {$row['std_verbal_desc']}</p>
                    <hr style='margin: 15px 0;'>
                    <p><strong>TMT (Trail Making Test) Raw Score:</strong> {$row['tmt_raw_score']}</p>
                    <p><strong>TMT Interpretation:</strong> {$row['tmt_interpretation']}</p>
                </div>
            ";
        }
        break;

    case 'assessment':
        $title = "STUDENT ASSESSMENT REPORT";
        $query = "SELECT a.*, s.id_number, CONCAT(s.last_name, ', ', s.first_name, ' ', s.middle_name) as student_name
                  FROM assessments a
                  JOIN students s ON a.student_id = s.id
                  WHERE a.assessment_id = '$id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $date = date('F d, Y', strtotime($row['assessment_date']));
            
            $content = "
                <div class='student-info'>
                    <div class='info-column'>
                        <div class='info-row'><div class='info-label'>Student ID:</div><div class='info-value'>{$row['id_number']}</div></div>
                        <div class='info-row'><div class='info-label'>Name:</div><div class='info-value'>{$row['student_name']}</div></div>
                    </div>
                    <div class='info-column'>
                        <div class='info-row'><div class='info-label'>Date:</div><div class='info-value'>$date</div></div>
                        <div class='info-row'><div class='info-label'>Type:</div><div class='info-value'>{$row['assessment_type']}</div></div>
                    </div>
                </div>
                <div class='content'>
                    <p><strong>Result:</strong> {$row['result']}</p>
                    <p><strong>Interpretation:</strong> {$row['interpretation']}</p>
                    <p><strong>Recommendations:</strong> {$row['recommendations']}</p>
                </div>
            ";
        }
        break;

    case 'tracer':
        $title = "GRADUATE TRACER STUDY PROFILE";
        $query = "SELECT * FROM graduate_tracer WHERE id = '$id'";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            $fullname = $row['family_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
            $bday = $row['birthday'] ? date('F d, Y', strtotime($row['birthday'])) : 'N/A';
            $empDate = $row['employment_date'] ? date('F d, Y', strtotime($row['employment_date'])) : 'N/A';
            
            // FULL DETAILS DISPLAY
            $content = "
                <div class='tracer-section'>
                    <h4>1. Personal Information</h4>
                    <div class='student-info'>
                        <div class='info-column'>
                            <div class='info-row'><div class='info-label'>Full Name:</div><div class='info-value'>$fullname</div></div>
                            <div class='info-row'><div class='info-label'>Email:</div><div class='info-value'>{$row['email']}</div></div>
                            <div class='info-row'><div class='info-label'>Gender:</div><div class='info-value'>{$row['gender']}</div></div>
                            <div class='info-row'><div class='info-label'>Birthday:</div><div class='info-value'>$bday</div></div>
                        </div>
                        <div class='info-column'>
                            <div class='info-row'><div class='info-label'>Civil Status:</div><div class='info-value'>{$row['civil_status']}</div></div>
                            <div class='info-row'><div class='info-label'>Address:</div><div class='info-value'>{$row['address']}</div></div>
                            <div class='info-row'><div class='info-label'>Contact:</div><div class='info-value'>{$row['contact']}</div></div>
                            <div class='info-row'><div class='info-label'>Spouse:</div><div class='info-value'>{$row['spouse_name']}</div></div>
                        </div>
                    </div>
                    <div class='student-info'>
                         <div class='info-column'>
                            <div class='info-row'><div class='info-label'>No. of Children:</div><div class='info-value'>{$row['children_count']}</div></div>
                        </div>
                    </div>
                </div>
                
                <div class='tracer-section'>
                    <h4>2. Academic Background</h4>
                    <div class='student-info'>
                         <div class='info-column'>
                            <div class='info-row'><div class='info-label'>Program:</div><div class='info-value'>{$row['programs']}</div></div>
                            <div class='info-row'><div class='info-label'>Year Graduated:</div><div class='info-value'>{$row['year_graduated']}</div></div>
                        </div>
                        <div class='info-column'>
                            <div class='info-row'><div class='info-label'>Post Grad:</div><div class='info-value'>{$row['post_grad']}</div></div>
                            <div class='info-row'><div class='info-label'>Honors/Awards:</div><div class='info-value'>{$row['honors']}</div></div>
                        </div>
                    </div>
                    <div class='student-info'>
                        <div class='info-column'>
                             <div class='info-row'><div class='info-label'>Board Exam Taken:</div><div class='info-value'>{$row['board_exam']}</div></div>
                        </div>
                        <div class='info-column'>
                             <div class='info-row'><div class='info-label'>Other Schools:</div><div class='info-value'>{$row['other_schools']}</div></div>
                        </div>
                    </div>
                </div>

                <div class='tracer-section'>
                    <h4>3. Present Employment</h4>
                    <div class='student-info'>
                         <div class='info-column'>
                            <div class='info-row'><div class='info-label'>Occupation:</div><div class='info-value'>{$row['occupation']}</div></div>
                            <div class='info-row'><div class='info-label'>Company:</div><div class='info-value'>{$row['company']}</div></div>
                            <div class='info-row'><div class='info-label'>Position:</div><div class='info-value'>{$row['position']}</div></div>
                        </div>
                        <div class='info-column'>
                            <div class='info-row'><div class='info-label'>Company Address:</div><div class='info-value'>{$row['company_address']}</div></div>
                            <div class='info-row'><div class='info-label'>Employment Date:</div><div class='info-value'>$empDate</div></div>
                            <div class='info-row'><div class='info-label'>Monthly Salary:</div><div class='info-value'>{$row['salary']}</div></div>
                        </div>
                    </div>
                    <div class='student-info'>
                        <div class='info-column'>
                             <div class='info-row'><div class='info-label'>Status/Time:</div><div class='info-value'>{$row['employment_time']}</div></div>
                        </div>
                    </div>
                </div>

                <div class='tracer-section'>
                    <h4>4. Previous Employment</h4>
                    <div class='student-info'>
                         <div class='info-column'>
                            <div class='info-row'><div class='info-label'>Company:</div><div class='info-value'>{$row['prev_company']}</div></div>
                            <div class='info-row'><div class='info-label'>Position:</div><div class='info-value'>{$row['prev_position']}</div></div>
                        </div>
                        <div class='info-column'>
                             <div class='info-row'><div class='info-label'>Address:</div><div class='info-value'>{$row['prev_address']}</div></div>
                        </div>
                    </div>
                </div>

                <div class='tracer-section' style='border:none;'>
                    <h4>5. Additional Information</h4>
                    <div class='student-info'>
                        <div class='info-column'>
                             <div class='info-row'><div class='info-label'>Success Story:</div><div class='info-value'>{$row['success_story']}</div></div>
                        </div>
                        <div class='info-column'>
                             <div class='info-row'><div class='info-label'>Data Consent:</div><div class='info-value'>{$row['consent']}</div></div>
                        </div>
                    </div>
                </div>
            ";
            
            // Add CSS for tracer sections
            $customCss = "<style>.tracer-section { margin-bottom: 20px; border-bottom: 1px dashed #ccc; padding-bottom: 10px; } .tracer-section h4 { margin-bottom: 10px; color: #444; background-color: #f9f9f9; padding: 5px; border-bottom: 1px solid #ddd; }</style>";
        }
        break;

    default:
        die("Error: Invalid Type specified.");
}

if (!$content) {
    die("Error: Record not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $title ?> - Print Form</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    <?php if(isset($customCss)) echo $customCss; ?>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.4;
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
            /* Align header to the left */
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 20px;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        
        .header-text {
            flex: 1;
            /* Align text within header to left */
            text-align: left;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin: 0;
            padding: 0;
            text-transform: uppercase;
        }
        
        .header h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 5px 0;
            padding: 0;
            text-transform: uppercase;
        }
        
        .header p {
            font-size: 10pt;
            margin: 2px 0;
            padding: 0;
        }
        
        .doc-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 20px 0 10px;
            text-transform: uppercase;
            text-decoration: underline;
        }
        
        .student-info {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
        }
        
        .info-column {
            width: 100%; /* Full width for better readability */
            padding: 2px 5px;
            box-sizing: border-box;
        }
        
        @media(min-width: 600px) {
            .info-column { width: 50%; }
        }
        
        .info-row {
            display: flex;
            margin-bottom: 5px;
            align-items: baseline;
        }
        
        .info-label {
            width: 140px;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .info-value {
            flex: 1;
            font-weight: normal;
        }
        
        .content {
            margin-bottom: 20px;
            font-size: 12pt;
            text-align: justify;
        }
        
        .content p {
            margin-bottom: 8px;
        }

        .content strong {
            font-weight: bold;
        }

        .footer {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            /* Align footer items to the left */
            align-items: flex-start; 
            gap: 30px;
            padding-top: 20px;
        }
        
        .signature-box {
            width: 45%;
            text-align: left; /* Align signature text left */
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            height: 1px;
            width: 100%;
            margin: 5px auto 5px;
        }
        
        .date-box {
            /* Align date box to the left */
            text-align: left;
            margin-top: 10px;
        }
        
        .date-line {
            border-bottom: 1px solid #000;
            height: 1px;
            width: 150px;
            margin: 5px 0 5px 0; /* Removed auto margin to keep left aligned */
        }
        
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            background-color: #004085;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background-color: #003366;
        }
        
        @media print {
            .print-button {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
                width: 100%;
            }
            .header {
                border-bottom: 2px solid #000;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">Print Document</button>
    
    <div class="header">
        <img src="../uploads/csr.png" alt="School Logo" class="logo">
        <div class="header-text">
            <h1><?= SCHOOL_NAME ?></h1>
            <h2>GUIDANCE SERVICES OFFICE</h2>
            <p><?= SCHOOL_ADDRESS ?></p>
            <p>Tel: <?= SCHOOL_CONTACT_NO ?> | Email: <?= SCHOOL_EMAIL ?></p>
        </div>
    </div>
    
    <div class="doc-title"><?= $title ?></div>
    
    <?= $content ?>
    
    <div class="footer">
        <div class="signature-box">
            <div>Prepared by:</div>
            <div class="signature-line"></div>
            <div><strong>SR. JOY A. DULA,AR</strong></div>
            <div><strong>GUIDANCE COUNSELOR</strong></div>
        </div>
        
        <div class="date-box">
            <div>Date Printed:</div>
            <div class="date-line"></div>
            <div><?= date('F d, Y') ?></div>
        </div>
    </div>
</body>
</html>