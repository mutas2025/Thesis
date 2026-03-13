<?php
include 'config.php';

// Check if student_id is provided
if (!isset($_GET['student_id'])) {
    die("Student ID is required");
}

 $student_id = $_GET['student_id'];

// Securely get student information using Prepared Statements
// Assumption: The 'students' table has columns 'enrollment_status' and 'year_level'
 $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
 $stmt->bind_param("i", $student_id);
 $stmt->execute();
 $student_result = $stmt->get_result();

if ($student_result->num_rows == 0) {
    die("Student not found");
}

 $student = $student_result->fetch_assoc();

// --- LOGIC: Check Graduation Status and Year Level ---

 $status = isset($student['enrollment_status']) ? strtolower($student['enrollment_status']) : '';
 $graduated = false;
 $year_level_text = '';

// Define what counts as graduated (adjust these values to match your database data)
if ($status == 'graduated' || $status == 'alumni' || $status == 'graduate') {
    $graduated = true;
} else {
    $graduated = false;
}

// Get Current Year Level if not graduated
if (!$graduated) {
    // Assuming 'year_level' column exists (e.g., "Grade 11", "1st Year", "4th Year")
    // If the column is named differently, change 'year_level' below.
    $raw_year = isset($student['year_level']) ? $student['year_level'] : '';
    
    // Optional: Capitalize the year level for display consistency
    $year_level_text = ucwords(strtolower($raw_year));
    
    // Fallback if year level is empty but student is not marked graduated
    if (empty($year_level_text)) {
        $year_level_text = 'Currently Enrolled';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Good Moral Character</title>
    <link rel="icon" href="uploads/csr.png" type="image/x-icon">
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            -webkit-print-color-adjust: exact;
        }
        
        .certificate-container {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background-color: white;
            padding: 25mm;
            box-sizing: border-box;
            position: relative;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
        }

        /* Watermark Styling */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            height: 400px;
            opacity: 0.2;
            pointer-events: none;
            z-index: 0;
            mix-blend-mode: multiply;
        }

        /* Content Wrapper */
        .content-wrapper {
            position: relative;
            z-index: 1;
        }
        
        /* Header Section */
        .header-section {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        /* School Header (Left) */
        .school-header {
            display: flex;
            align-items: center;
            width: 100%;
        }
        
        .school-logo {
            width: 90px;
            height: 90px;
            margin-right: 20px;
            object-fit: contain;
        }
        
        .school-details {
            text-align: left;
        }
        
        .school-name {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            color: #1a3a5f;
        }
        
        .school-address {
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .school-contact {
            font-size: 12px;
            font-style: italic;
        }
        
        .separator {
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            width: 100%;
        }

        /* Title Styling */
        .certificate-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin: 10px 0 20px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: underline;
            text-underline-offset: 5px;
        }

        /* Date Styling - Left Aligned */
        .certificate-date {
            text-align: left;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .certificate-content {
            text-align: justify;
            font-size: 14px;
            line-height: 2;
            margin-bottom: 20px;
            text-indent: 50px;
        }
        
        .student-name {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 16px;
        }

        .certificate-footer {
            margin-top: auto;
            padding-top: 50px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            position: relative;
        }
        
        .signature-box {
            text-align: center;
            width: 250px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            margin-bottom: 5px;
        }
        
        .signatory-name {
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .signatory-title {
            font-size: 13px;
            text-transform: uppercase;
        }

        /* Note at the very bottom */
        .validity-note {
            margin-top: 40px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            font-style: italic;
            color: #555;
            width: 100%;
        }
        
        /* Print Controls */
        @media print {
            body {
                background-color: white;
            }
            
            .certificate-container {
                box-shadow: none;
                border: none;
                margin: 0;
                width: 100%;
                height: 100%;
                min-height: 297mm;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <!-- Watermark Background -->
        <img src="uploads/csr.png" alt="Watermark" class="watermark">
        
        <div class="content-wrapper">
            <!-- Header Section -->
            <div class="header-section">
                <div class="school-header">
                    <img src="uploads/csr.png" alt="School Logo" class="school-logo">
                    <div class="school-details">
                        <div class="school-name"><?php echo SCHOOL_NAME; ?></div>
                        <div class="school-address"><?php echo SCHOOL_ADDRESS; ?></div>
                        <div class="school-contact"><?php echo SCHOOL_EMAIL; ?> | <?php echo SCHOOL_CONTACT; ?></div>
                    </div>
                </div>
            </div>

            <!-- Decorative Separator Line -->
            <div class="separator"></div>
            
            <!-- Title -->
            <div class="certificate-title">
                Certificate of Good Moral Character
            </div>

            <!-- Date -->
            <div class="certificate-date">
                <?php echo date("F j, Y"); ?>
            </div>
            
            <div class="certificate-content">
                <p>
                    TO WHOM IT MAY CONCERN:
                </p>
                <p>
                    This is to certify that <span class="student-name"><?php echo $student['full_name']; ?></span>, 
                    <?php if ($graduated): ?>
                        who graduated from this institution with a degree in <strong><?php echo $student['course_or_strand']; ?></strong>,
                    <?php else: ?>
                        a <strong><?php echo $year_level_text; ?></strong> student taking up <strong><?php echo $student['course_or_strand']; ?></strong>,
                    <?php endif; ?>
                    has been known to us as a person of good moral character.
                </p>
                <p>
                    Records show that during his/her stay in this institution, he/she has shown exemplary behavior, deep respect for authority, and has maintained a harmonious relationship with fellow students, faculty members, and the school administration.
                </p>
                <p>
                    This certification is being issued upon the request of the interested party for whatever legal purpose it may serve.
                </p>
            </div>
            
            <div class="certificate-footer">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signatory-name">Sr. Joy A. Dula, AR</div>
                    <div class="signatory-title">Guidance Counselor</div>
                </div>
            </div>

            <!-- Validity Note -->
            <div class="validity-note">
                NOTE: This document is not valid without the Official School Seal.
            </div>
        </div>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 20px; padding-bottom: 20px;">
        <button onclick="window.print()" style="padding: 12px 25px; background-color: #1a3a5f; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
            Print Certificate
        </button>
        <button onclick="window.close()" style="padding: 12px 25px; background-color: #d32f2f; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px; font-size: 16px;">
            Close
        </button>
    </div>
</body>
</html>