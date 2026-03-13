<?php
session_start();
require_once '../config.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: slogin.php");
    exit();
}

// Get student ID from session
 $studentId = $_SESSION['student_id'];

// Get student details
 $query = "SELECT * FROM students WHERE id = $studentId";
 $result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    session_destroy();
    header("Location: slogin.php");
    exit();
}
 $student = mysqli_fetch_assoc($result);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_email'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $updateQuery = "UPDATE students SET email = '$email' WHERE id = $studentId";
        if (mysqli_query($conn, $updateQuery)) {
            $_SESSION['message'] = "Email updated successfully!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['error'] = "Failed to update email.";
        }
    }
    
    if (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        $query = "SELECT password FROM students WHERE id = $studentId";
        $result = mysqli_query($conn, $query);
        $student_data = mysqli_fetch_assoc($result);
        
        if (password_verify($current_password, $student_data['password'])) {
            if ($new_password == $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $updateQuery = "UPDATE students SET password = '$hashed_password' WHERE id = $studentId";
                if (mysqli_query($conn, $updateQuery)) {
                    $_SESSION['message'] = "Password updated successfully!";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $_SESSION['error'] = "Failed to update password.";
                }
            } else {
                $_SESSION['error'] = "New passwords do not match.";
            }
        } else {
            $_SESSION['error'] = "Current password is incorrect.";
        }
    }
    
    if (isset($_POST['upload_photo'])) {
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_photo'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];
            $fileType = $file['type'];
            
            $fileExt = explode('.', $fileName);
            $fileActualExt = strtolower(end($fileExt));
            $allowed = array('jpg', 'jpeg', 'png');
            
            if (in_array($fileActualExt, $allowed)) {
                if ($fileError === 0) {
                    if ($fileSize < 5000000) {
                        $fileNameNew = "profile_" . $studentId . "_" . uniqid('', true) . "." . $fileActualExt;
                        $fileDestination = '../uploads/profile_photos/' . $fileNameNew;
                        
                        if (!is_dir('../uploads/profile_photos/')) {
                            mkdir('../uploads/profile_photos/', 0777, true);
                        }
                        
                        if (move_uploaded_file($fileTmpName, $fileDestination)) {
                            $photoPath = 'uploads/profile_photos/' . $fileNameNew;
                            $updateQuery = "UPDATE students SET profile_photo = '$photoPath' WHERE id = $studentId";
                            
                            if (mysqli_query($conn, $updateQuery)) {
                                $student['profile_photo'] = $photoPath;
                                $_SESSION['message'] = "Profile photo updated successfully!";
                                header("Location: " . $_SERVER['PHP_SELF']);
                                exit();
                            } else {
                                $_SESSION['error'] = "Failed to update profile photo in database.";
                            }
                        } else {
                            $_SESSION['error'] = "Failed to upload profile photo.";
                        }
                    } else {
                        $_SESSION['error'] = "File size is too large. Maximum size is 5MB.";
                    }
                } else {
                    $_SESSION['error'] = "There was an error uploading your file.";
                }
            } else {
                $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, and PNG files are allowed.";
            }
        } else {
            $_SESSION['error'] = "Please select a file to upload.";
        }
    }
}

// Get enrollment history
 $enrollments = [];
 $query = "SELECT e.id, e.academic_year, e.semester, e.enrollment_date, e.status, e.section,
                 c.coursename, c.courselevel, c.id as course_id
          FROM enrollments e
          JOIN courses c ON e.course_id = c.id
          WHERE e.student_id = $studentId
          ORDER BY e.academic_year DESC, e.semester DESC";
 $result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $enrollments[] = $row;
    }
}

// Get subjects and grades for each enrollment
 $enrollmentDetails = [];
 $completedSubjects = []; 
 $totalSubjects = 0;
 $totalUnits = 0;

foreach ($enrollments as $enrollment) {
    $enrollmentId = $enrollment['id'];
    
    $subjects = [];
    $query = "SELECT ss.subject_id, ss.status, sub.subject_code, sub.subject_description, sub.unit
              FROM student_subjects ss
              JOIN subjects sub ON ss.subject_id = sub.id
              WHERE ss.enrollment_id = $enrollmentId
              ORDER BY sub.subject_code";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $subjects[] = $row;
        }
    }
    
    foreach ($subjects as $key => $subject) {
        $subjectId = $subject['subject_id'];
        
        $teacherQuery = "SELECT t.name as teacher_name, t.employee_id as teacher_id
                        FROM teacherassignments ta
                        JOIN teachers t ON ta.teacher_id = t.id
                        WHERE ta.subject_id = $subjectId 
                        AND ta.academic_year = '{$enrollment['academic_year']}'
                        AND ta.semester = '{$enrollment['semester']}'
                        LIMIT 1";
        $teacherResult = mysqli_query($conn, $teacherQuery);
        if ($teacherResult && mysqli_num_rows($teacherResult) > 0) {
            $teacherData = mysqli_fetch_assoc($teacherResult);
            $subjects[$key]['teacher_name'] = $teacherData['teacher_name'];
            $subjects[$key]['teacher_id'] = $teacherData['teacher_id'];
        } else {
            $subjects[$key]['teacher_name'] = 'Not Assigned';
            $subjects[$key]['teacher_id'] = '';
        }
        
        $query = "SELECT quarter1_grade, quarter2_grade, quarter3_grade, quarter4_grade, average_grade, remarks
                  FROM student_grades
                  WHERE student_id = $studentId 
                  AND subject_id = $subjectId 
                  AND enrollment_id = $enrollmentId";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $gradeData = mysqli_fetch_assoc($result);
            $subjects[$key]['grades'] = $gradeData;
            
            if (!empty($gradeData['average_grade'])) {
                $completedSubjects[] = [
                    'subject_code' => $subject['subject_code'],
                    'subject_description' => $subject['subject_description'],
                    'unit' => $subject['unit'],
                    'academic_year' => $enrollment['academic_year'],
                    'semester' => $enrollment['semester'],
                    'grades' => $gradeData,
                    'teacher_name' => $subjects[$key]['teacher_name'],
                    'section' => $enrollment['section']
                ];
                $totalSubjects++;
                $totalUnits += (int)$subject['unit'];
            }
        } else {
            $subjects[$key]['grades'] = [
                'quarter1_grade' => '',
                'quarter2_grade' => '',
                'quarter3_grade' => '',
                'quarter4_grade' => '',
                'average_grade' => '',
                'remarks' => ''
            ];
        }
        
        $query = "SELECT a.id, a.title, a.description, a.type, a.max_score, a.quarter, 
                  sas.score, sas.created_at as score_date
                  FROM activities a
                  LEFT JOIN student_activity_scores sas ON a.id = sas.activity_id AND sas.student_id = $studentId
                  WHERE a.subject_id = $subjectId";
        
        if (!empty($enrollment['section'])) {
            $query .= " AND (a.section = '" . $enrollment['section'] . "' OR a.section IS NULL OR a.section = '')";
        }
        
        $query .= " ORDER BY a.quarter, a.type, a.created_at";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $activities = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $activities[] = $row;
            }
            $subjects[$key]['activities'] = $activities;
        } else {
            $subjects[$key]['activities'] = [];
        }
    }
    unset($subject);
    
    $enrollmentDetails[] = [
        'enrollment' => $enrollment,
        'subjects' => $subjects
    ];
}

// --- FIXED GUIDANCE RECORDS SECTION ---

// 1. Counseling Sessions
 $counselingRecords = [];
 $query = "SELECT * FROM counseling_sessions WHERE student_id = $studentId ORDER BY session_date DESC";
 $result = mysqli_query($conn, $query);
// Check if query succeeded before fetching
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $counselingRecords[] = $row;
    }
}

// 2. Assessments
 $assessmentRecords = [];
 $query = "SELECT * FROM assessments WHERE student_id = $studentId ORDER BY assessment_date DESC";
 $result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $assessmentRecords[] = $row;
    }
}

// 3. Incidents
 $incidentRecords = [];
 $query = "SELECT * FROM incidents WHERE student_id = $studentId ORDER BY incident_date DESC";
 $result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $incidentRecords[] = $row;
    }
}

// 4. Appointments
 $appointmentRecords = [];
 $query = "SELECT * FROM appointments WHERE student_id = $studentId ORDER BY appointment_datetime DESC";
 $result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $appointmentRecords[] = $row;
    }
}

// --- END GUIDANCE RECORDS ---

// Get student fees
 $studentFees = [];
 $query = "SELECT sf.id, sf.amount, sf.academic_year, sf.semester, f.fee_name, f.fee_type
          FROM student_fees sf
          JOIN fees f ON sf.fee_id = f.id
          WHERE sf.student_id = $studentId
          ORDER BY sf.academic_year DESC, sf.semester DESC";
 $result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $studentFees[] = $row;
    }
}

// Get student payments
 $studentPayments = [];
 $query = "SELECT * FROM payments WHERE student_id = $studentId ORDER BY payment_date DESC";
 $result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $studentPayments[] = $row;
    }
}

// Calculate totals
 $totalFees = 0;
foreach ($studentFees as $fee) {
    $totalFees += $fee['amount'];
}

 $totalPayments = 0;
foreach ($studentPayments as $payment) {
    $totalPayments += ($payment['amount'] - $payment['discount']);
}

 $balance = $totalFees - $totalPayments;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    
    <style>
        /* General Styles */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }
        .content-wrapper { background-color: #f8f9fa; min-height: calc(100vh - 57px); }
        .tab-content { display: none; padding: 20px; width: 100%; clear: both; }
        .tab-content.active { display: block; }
        .card { border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); margin-bottom: 25px; border: none; }
        .card-header { border-radius: 10px 10px 0 0 !important; padding: 15px 20px; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .card-body { padding: 20px; }
        
        /* Theme Overrides */
        .main-header.navbar { background-color: #003d82; border-bottom: 1px solid #003d82; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .main-sidebar { background-color: #003d82; box-shadow: 2px 0 5px rgba(0,0,0,0.1); }
        .main-sidebar .nav-sidebar > .nav-item > .nav-link.active { background-color: #0056b3; color: #fff; }
        .main-sidebar .nav-sidebar > .nav-item > .nav-link { color: rgba(255,255,255,0.8); }
        .brand-link { background-color: #002d62; border-bottom: 1px solid #001f42; }
        .user-panel .info a { color: #fff; }
        .btn-primary { background-color: #003d82; border-color: #003d82; }
        .btn-info { background-color: #0056b3; border-color: #0056b3; }
        
        /* Dashboard & Profile */
        .info-box { border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); margin-bottom: 20px; overflow: hidden; }
        .info-box-icon { border-radius: 0; }
        .small-box { border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); margin-bottom: 20px; overflow: hidden; }
        .profile-header { background: linear-gradient(135deg, #003d82 0%, #0056b3 100%); color: white; padding: 30px; border-radius: 10px; margin-bottom: 25px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .profile-picture { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.2); }
        .profile-stats { display: flex; margin-top: 20px; }
        .profile-stat { flex: 1; text-align: center; padding: 15px; background-color: rgba(255,255,255,0.15); border-radius: 8px; margin-right: 15px; backdrop-filter: blur(5px); }
        .profile-stat:last-child { margin-right: 0; }
        .profile-stat-value { font-size: 24px; font-weight: 600; }
        
        /* Photo Upload */
        .photo-upload-container { position: relative; display: inline-block; }
        .photo-upload-overlay { position: absolute; top: 0; left: 0; width: 120px; height: 120px; background-color: rgba(0,0,0,0.5); border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; cursor: pointer; }
        .photo-upload-container:hover .photo-upload-overlay { opacity: 1; }
        .photo-upload-icon { color: white; font-size: 24px; }
        
        /* Detail Cards */
        .detail-row { display: flex; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: 600; width: 200px; color: #495057; }
        .detail-value { flex: 1; color: #212529; }
        
        /* Table Styles */
        .table-responsive { margin: 0; border-radius: 0 0 10px 10px; }
        .table th { border-top: none; font-weight: 600; color: #495057; background-color: #f8f9fa; }
        
        /* Badges */
        .grade-badge { font-size: 1em; padding: 5px 10px; border-radius: 5px; }
        .grade-pass { background-color: #003d82; color: white; }
        .grade-fail { background-color: #dc3545; color: white; }
        .section-badge { display: inline-block; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 700; line-height: 1; text-align: center; white-space: nowrap; vertical-align: baseline; border-radius: 0.25rem; background-color: #003d82; color: #fff; }
        
        /* Balance */
        .balance-positive { color: #dc3545; font-weight: bold; }
        .balance-negative { color: #003d82; font-weight: bold; }
        .balance-card { border-left: 5px solid #003d82; }
        .fee-card { border-left: 5px solid #0056b3; }
        .payment-card { border-left: 5px solid #003d82; }
        
        /* Activity Styles */
        .activity-card { border-left: 4px solid #003d82; margin-bottom: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); border-radius: 0 10px 10px 0; }
        .activity-card.exam { border-left-color: #dc3545; }
        .activity-card.participation { border-left-color: #ffc107; }
        .score-badge { font-weight: bold; padding: 5px 10px; border-radius: 4px; }
        .score-high { background-color: #003d82; color: white; }
        .score-medium { background-color: #ffc107; color: #212529; }
        .score-low { background-color: #dc3545; color: white; }
        .score-none { background-color: #6c757d; color: white; }
        
        /* Quarter Tabs */
        .quarter-tabs { display: flex; margin-bottom: 15px; background-color: #f8f9fa; border-radius: 8px; overflow: hidden; }
        .quarter-tab { padding: 10px 15px; background-color: #f8f9fa; border: none; cursor: pointer; margin-right: 0; flex: 1; text-align: center; transition: all 0.3s; }
        .quarter-tab.active { background-color: #003d82; color: white; }
        .quarter-content { display: none; }
        .quarter-content.active { display: block; }
        
        /* Toast */
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        .custom-toast { min-width: 300px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); margin-bottom: 10px; }
        .custom-toast-success { background-color: #003d82; color: white; }
        .custom-toast-error { background-color: #dc3545; color: white; }
        
        /* Guidance Styles */
        .guidance-summary-cards { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .g-card { flex: 1; min-width: 200px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-align: center; border-top: 4px solid #003d82; }
        .g-card h3 { font-size: 2rem; margin: 0; color: #003d82; }
        .g-card p { margin: 5px 0 0; color: #666; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; }
        
        .accordion .card { box-shadow: none; border-radius: 0; border-bottom: 1px solid rgba(0,0,0,.125); margin-bottom: 0; }
        .accordion .card-header { background-color: #fff; padding: 10px 15px; cursor: pointer; }
        .guidance-table th { background-color: #f1f4f6; color: #333; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .profile-stats { flex-direction: column; }
            .profile-stat { margin-right: 0; margin-bottom: 10px; }
            .detail-row { flex-direction: column; }
            .detail-label { width: 100%; margin-bottom: 5px; }
            .guidance-summary-cards { flex-direction: column; }
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link">Student Portal</a>
            </li>
        </ul>
    </nav>
    
    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-success elevation-4">
        <a href="#" class="brand-link">
            <img src="../uploads/csr.png" alt="CSR Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">Student Portal</span>
        </a>
        
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <?php 
                    if (!empty($student['profile_photo'])) {
                        echo '<img src="../' . $student['profile_photo'] . '" class="img-circle elevation-2" alt="User Image">';
                    } else {
                        echo '<img src="https://ui-avatars.com/api/?name=' . urlencode($student['first_name'] . ' ' . $student['last_name']) . '&background=28a745&color=fff&size=40" class="img-circle elevation-2" alt="User Image">';
                    }
                    ?>
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?php echo $student['first_name'] . ' ' . $student['last_name']; ?></a>
                </div>
            </div>
            
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="#" class="nav-link active" data-tab="dashboard">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-tab="profile">
                            <i class="nav-icon fas fa-user"></i>
                            <p>Student Details</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-tab="enrollments">
                            <i class="nav-icon fas fa-history"></i>
                            <p>Enrollment History</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-tab="grades">
                            <i class="nav-icon fas fa-graduation-cap"></i>
                            <p>Grades</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-tab="activities">
                            <i class="nav-icon fas fa-tasks"></i>
                            <p>Activities & Exams</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-tab="guidance">
                            <i class="nav-icon fas fa-hands-helping"></i>
                            <p>Guidance Records</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" data-tab="balance">
                            <i class="nav-icon fas fa-money-bill-wave"></i>
                            <p>Balance and Payments</p>
                        </a>
                    </li>
                    <li class="nav-header">ACCOUNT</li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
    
    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Student Portal</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" id="breadcrumb-active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="toast-container"></div>
                
                <!-- Dashboard Tab -->
                <div id="dashboard" class="tab-content active">
                    <div class="row">
                        <div class="col-lg-4 col-md-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?php echo $totalSubjects; ?></h3>
                                    <p>Subjects Taken</p>
                                </div>
                                <div class="icon"><i class="fas fa-chart-line"></i></div>
                                <a href="#" class="small-box-footer"><?php echo $totalUnits; ?> Units <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?php echo !empty($enrollments) ? $enrollments[0]['academic_year'] : 'None'; ?></h3>
                                    <p>Current Enrollment</p>
                                </div>
                                <div class="icon"><i class="fas fa-history"></i></div>
                                <a href="#" class="small-box-footer">
                                    <?php echo !empty($enrollments) ? $enrollments[0]['semester'] : 'No Enrollment'; ?> <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?php echo !empty($enrollments) ? $enrollments[0]['status'] : 'N/A'; ?></h3>
                                    <p>Academic Status</p>
                                </div>
                                <div class="icon"><i class="fas fa-graduation-cap"></i></div>
                                <a href="#" class="small-box-footer">
                                    <?php echo !empty($enrollments) ? date('M d, Y', strtotime($enrollments[0]['enrollment_date'])) : 'Not Enrolled'; ?> <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header border-transparent">
                                    <h3 class="card-title">Recent Activity</h3>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table m-0">
                                            <thead><tr><th>Activity</th><th>Date</th></tr></thead>
                                            <tbody>
                                                <?php if (!empty($enrollments)): ?>
                                                    <tr><td>Enrolled in <?php echo $enrollments[0]['coursename']; ?></td><td><?php echo date('M d, Y', strtotime($enrollments[0]['enrollment_date'])); ?></td></tr>
                                                <?php endif; ?>
                                                <?php if (count($completedSubjects) > 0): ?>
                                                    <tr><td>Completed <?php echo count($completedSubjects); ?> subjects</td><td>Recently</td></tr>
                                                <?php endif; ?>
                                                <tr><td>Last login</td><td>Today</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header border-transparent">
                                    <h3 class="card-title">Quick Stats</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6 text-center">
                                            <div class="description-block border-right">
                                                <span class="description-percentage text-success"><?php echo count($enrollments); ?></span>
                                                <h5 class="description-header">Enrollments</h5>
                                                <span class="description-text">Total</span>
                                            </div>
                                        </div>
                                        <div class="col-6 text-center">
                                            <div class="description-block">
                                                <span class="description-percentage text-warning"><?php echo count($completedSubjects); ?></span>
                                                <h5 class="description-header">Subjects</h5>
                                                <span class="description-text">Completed</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Tab -->
                <div id="profile" class="tab-content">
                    <div class="profile-header">
                        <div class="row">
                            <div class="col-md-2 text-center">
                                <div class="photo-upload-container">
                                    <?php 
                                    if (!empty($student['profile_photo'])) {
                                        echo '<img src="../' . $student['profile_photo'] . '" alt="Profile Picture" class="profile-picture">';
                                    } else {
                                        echo '<img src="https://ui-avatars.com/api/?name=' . urlencode($student['first_name'] . ' ' . $student['last_name']) . '&background=28a745&color=fff&size=120" alt="Profile Picture" class="profile-picture">';
                                    }
                                    ?>
                                    <div class="photo-upload-overlay" data-toggle="modal" data-target="#uploadPhotoModal">
                                        <i class="fas fa-camera photo-upload-icon"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-10">
                                <h2><?php echo $student['first_name'] . ' ' . $student['middle_name'] . ' ' . $student['last_name']; ?></h2>
                                <p><strong>ID Number:</strong> <?php echo $student['id_number']; ?></p>
                                <p><strong>Course:</strong> 
                                    <?php echo !empty($enrollments) ? $enrollments[0]['coursename'] . ' - ' . $enrollments[0]['courselevel'] : 'Not enrolled'; ?>
                                </p>
                                <?php if (!empty($enrollments) && !empty($enrollments[0]['section'])): ?>
                                <p><strong>Section:</strong> <span class="section-badge"><?php echo $enrollments[0]['section']; ?></span></p>
                                <?php endif; ?>
                                <div class="profile-stats">
                                    <div class="profile-stat">
                                        <div class="profile-stat-value"><?php echo $totalSubjects; ?></div>
                                        <div class="profile-stat-label">Subjects Taken</div>
                                    </div>
                                    <div class="profile-stat">
                                        <div class="profile-stat-value"><?php echo $totalUnits; ?></div>
                                        <div class="profile-stat-label">Units Earned</div>
                                    </div>
                                    <div class="profile-stat">
                                        <div class="profile-stat-value"><?php echo $student['age']; ?></div>
                                        <div class="profile-stat-label">Age</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Personal Info -->
                    <div class="detail-card">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-user mr-2"></i>Personal Information</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="detail-row"><div class="detail-label">Full Name:</div><div class="detail-value"><?php echo $student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']; ?></div></div>
                                    <div class="detail-row"><div class="detail-label">ID Number:</div><div class="detail-value"><?php echo $student['id_number']; ?></div></div>
                                    <div class="detail-row"><div class="detail-label">Gender:</div><div class="detail-value"><?php echo $student['gender']; ?></div></div>
                                    <div class="detail-row"><div class="detail-label">Birth Date:</div><div class="detail-value"><?php echo date('F d, Y', strtotime($student['birth_date'])); ?></div></div>
                                    <div class="detail-row"><div class="detail-label">Age:</div><div class="detail-value"><?php echo $student['age']; ?></div></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-row"><div class="detail-label">Birth Place:</div><div class="detail-value"><?php echo $student['birth_place']; ?></div></div>
                                    <div class="detail-row"><div class="detail-label">Civil Status:</div><div class="detail-value"><?php echo $student['civil_status']; ?></div></div>
                                    <div class="detail-row"><div class="detail-label">Nationality:</div><div class="detail-value"><?php echo $student['nationality']; ?></div></div>
                                    <div class="detail-row"><div class="detail-label">Religion:</div><div class="detail-value"><?php echo $student['religion']; ?></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="detail-card">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-address-book mr-2"></i>Contact Information</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="detail-row">
                                        <div class="detail-label">Email:</div>
                                        <div class="detail-value">
                                            <form method="post" action="" class="form-inline">
                                                <div class="input-group">
                                                    <input type="email" name="email" class="form-control" value="<?php echo $student['email']; ?>" required>
                                                    <div class="input-group-append"><button type="submit" name="update_email" class="btn btn-info">Update</button></div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="detail-row"><div class="detail-label">Contact Number:</div><div class="detail-value"><?php echo $student['contact_number']; ?></div></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-row"><div class="detail-label">Home Address:</div><div class="detail-value"><?php echo $student['home_address']; ?></div></div>
                                    <div class="detail-row">
                                        <div class="detail-label">Password:</div>
                                        <div class="detail-value">
                                            <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#changePasswordModal"><i class="fas fa-key mr-1"></i> Change Password</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Family Info -->
                    <div class="detail-card">
                        <div class="card-header"><h3 class="card-title"><i class="fas fa-users mr-2"></i>Family Information</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="detail-row"><div class="detail-label">Father's Name:</div><div class="detail-value"><?php echo $student['father_name']; ?></div></div>
                                    <div class="detail-row"><div class="detail-label">Father's Occupation:</div><div class="detail-value"><?php echo $student['father_occupation']; ?></div></div>
                                    <div class="detail-row"><div class="detail-label">Mother's Name:</div><div class="detail-value"><?php echo $student['mother_name']; ?></div></div>
                                    <div class="detail-row"><div class="detail-label">Mother's Occupation:</div><div class="detail-value"><?php echo $student['mother_occupation']; ?></div></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-row"><div class="detail-label">Guardian's Name:</div><div class="detail-value"><?php echo $student['guardian_name']; ?></div></div>
                                    <div class="detail-row"><div class="detail-label">Guardian's Address:</div><div class="detail-value"><?php echo $student['guardian_address']; ?></div></div>
                                    <div class="detail-row"><div class="detail-label">Other Support:</div><div class="detail-value"><?php echo $student['other_support']; ?></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Enrollment History Tab -->
                <div id="enrollments" class="tab-content">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h3 class="card-title"><i class="fas fa-history mr-2"></i>Enrollment History</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($enrollments)): ?>
                                <div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i> No enrollment records found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped" id="enrollmentsTable">
                                        <thead>
                                            <tr>
                                                <th>Academic Year</th>
                                                <th>Semester</th>
                                                <th>Course</th>
                                                <th>Year Level</th>
                                                <th>Section</th>
                                                <th>Enrollment Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($enrollments as $enrollment): ?>
                                            <tr>
                                                <td><?php echo $enrollment['academic_year']; ?></td>
                                                <td><?php echo $enrollment['semester']; ?></td>
                                                <td><?php echo $enrollment['coursename']; ?></td>
                                                <td><?php echo $enrollment['courselevel']; ?></td>
                                                <td><?php echo !empty($enrollment['section']) ? '<span class="section-badge">'.$enrollment['section'].'</span>' : '<span class="text-muted">Not Assigned</span>'; ?></td>
                                                <td><?php echo date('F d, Y', strtotime($enrollment['enrollment_date'])); ?></td>
                                                <td>
                                                    <?php
                                                    $statusClass = 'bg-secondary';
                                                    switch($enrollment['status']) {
                                                        case 'Pending': $statusClass = 'bg-warning'; break;
                                                        case 'Enrolled': $statusClass = 'bg-success'; break;
                                                        case 'Dropped': $statusClass = 'bg-danger'; break;
                                                        case 'Completed': $statusClass = 'bg-info'; break;
                                                        case 'Registered': $statusClass = 'bg-primary'; break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $enrollment['status']; ?></span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Grades Tab -->
                <div id="grades" class="tab-content">
                    <?php if (empty($enrollmentDetails)): ?>
                        <div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i> No enrollment records found. Please enroll first to view grades.</div>
                    <?php else: ?>
                        <?php foreach ($enrollmentDetails as $enrollmentDetail): ?>
                        <div class="card">
                            <div class="card-header bg-warning">
                                <h3 class="card-title"><i class="fas fa-graduation-cap mr-2"></i><?php echo $enrollmentDetail['enrollment']['academic_year'] . ' - ' . $enrollmentDetail['enrollment']['semester']; ?></h3>
                                <div class="card-tools">
                                    <span class="badge badge-light"><?php echo $enrollmentDetail['enrollment']['coursename'] . ' - ' . $enrollmentDetail['enrollment']['courselevel']; ?></span>
                                    <?php if (!empty($enrollmentDetail['enrollment']['section'])): ?>
                                        <span class="section-badge ml-2"><?php echo $enrollmentDetail['enrollment']['section']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (empty($enrollmentDetail['subjects'])): ?>
                                    <div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i> No subjects found for this enrollment.</div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Subject Code</th>
                                                    <th>Subject Description</th>
                                                    <th>Units</th>
                                                    <th>Q1 | PRELIM</th>
                                                    <th>Q2 | MIDTERM</th>
                                                    <th>Q3 | FINALS</th>
                                                    <th>Q4 | SUMMER</th>
                                                    <th>Average</th>
                                                    <th>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($enrollmentDetail['subjects'] as $subject): ?>
                                                <tr>
                                                    <td><?php echo $subject['subject_code']; ?></td>
                                                    <td><?php echo $subject['subject_description']; ?></td>
                                                    <td><?php echo $subject['unit']; ?></td>
                                                    <td><?php echo $subject['grades']['quarter1_grade'] ?: '-'; ?></td>
                                                    <td><?php echo $subject['grades']['quarter2_grade'] ?: '-'; ?></td>
                                                    <td><?php echo $subject['grades']['quarter3_grade'] ?: '-'; ?></td>
                                                    <td><?php echo $subject['grades']['quarter4_grade'] ?: '-'; ?></td>
                                                    <td>
                                                        <?php 
                                                        if ($subject['grades']['average_grade']) {
                                                            $avg = floatval($subject['grades']['average_grade']);
                                                            $badgeClass = ($avg >= 75) ? 'grade-pass' : 'grade-fail';
                                                            echo '<span class="badge ' . $badgeClass . '">' . $avg . '</span>';
                                                        } else { echo '-'; }
                                                        ?>
                                                    </td>
                                                    <td><?php echo $subject['grades']['remarks'] ?: '-'; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Activities & Exams Tab -->
                <div id="activities" class="tab-content">
                    <?php if (empty($enrollmentDetails)): ?>
                        <div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i> No enrollment records found. Please enroll first to view activities.</div>
                    <?php else: ?>
                        <?php foreach ($enrollmentDetails as $enrollmentDetail): ?>
                        <div class="card">
                            <div class="card-header bg-info">
                                <h3 class="card-title"><i class="fas fa-tasks mr-2"></i><?php echo $enrollmentDetail['enrollment']['academic_year'] . ' - ' . $enrollmentDetail['enrollment']['semester']; ?></h3>
                                <div class="card-tools">
                                    <span class="badge badge-light"><?php echo $enrollmentDetail['enrollment']['coursename'] . ' - ' . $enrollmentDetail['enrollment']['courselevel']; ?></span>
                                    <?php if (!empty($enrollmentDetail['enrollment']['section'])): ?>
                                        <span class="section-badge ml-2"><?php echo $enrollmentDetail['enrollment']['section']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (empty($enrollmentDetail['subjects'])): ?>
                                    <div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i> No subjects found for this enrollment.</div>
                                <?php else: ?>
                                    <?php foreach ($enrollmentDetail['subjects'] as $subject): ?>
                                    <div class="card activity-card">
                                        <div class="card-header">
                                            <div class="activity-header">
                                                <h5 class="card-title activity-title"><?php echo $subject['subject_code'] . ' - ' . $subject['subject_description']; ?></h5>
                                                <div class="badge badge-info activity-type-badge"><?php echo $subject['unit']; ?> Units</div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="teacher-info"><i class="fas fa-user-tie mr-1"></i> Instructor: <?php echo $subject['teacher_name']; ?></div>
                                            
                                            <?php if (empty($subject['activities'])): ?>
                                                <div class="alert alert-info mt-3"><i class="fas fa-info-circle mr-2"></i> No activities or exams found for this subject.</div>
                                            <?php else: ?>
                                                <div class="quarter-tabs">
                                                    <div class="quarter-tab active" data-quarter="all">All</div>
                                                    <div class="quarter-tab" data-quarter="1st Quarter">Q1 | PRELIM</div>
                                                    <div class="quarter-tab" data-quarter="2nd Quarter">Q2 | MIDTERM</div>
                                                    <div class="quarter-tab" data-quarter="3rd Quarter">Q3 | FINALS</div>
                                                    <div class="quarter-tab" data-quarter="4th Quarter">Q4 | SUMMER</div>
                                                </div>
                                                
                                                <div class="activity-list">
                                                    <?php foreach ($subject['activities'] as $activity): ?>
                                                    <div class="activity-item quarter-content active" data-quarter="<?php echo $activity['quarter']; ?>">
                                                        <div class="activity-details">
                                                            <div class="activity-info">
                                                                <div class="activity-title">
                                                                    <?php 
                                                                    $typeClass = 'text-success'; $typeIcon = '<i class="fas fa-tasks"></i> ';
                                                                    if ($activity['type'] == 'exam') { $typeClass = 'text-danger'; $typeIcon = '<i class="fas fa-file-alt"></i> '; } 
                                                                    elseif ($activity['type'] == 'participation') { $typeClass = 'text-warning'; $typeIcon = '<i class="fas fa-users"></i> '; }
                                                                    ?>
                                                                    <span class="<?php echo $typeClass; ?>"><?php echo $typeIcon . ucfirst($activity['type']); ?></span>
                                                                    : <?php echo htmlspecialchars($activity['title']); ?>
                                                                </div>
                                                                <div class="activity-meta">
                                                                    <span><i class="fas fa-star"></i> Max Score: <?php echo $activity['max_score']; ?></span>
                                                                    <span><i class="fas fa-calendar"></i> <?php echo $activity['quarter']; ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="activity-score">
                                                                <?php 
                                                                if ($activity['score'] !== null) {
                                                                    $score = floatval($activity['score']); $maxScore = floatval($activity['max_score']); $percentage = ($score / $maxScore) * 100;
                                                                    $scoreClass = ($percentage >= 75) ? 'score-high' : (($percentage >= 50) ? 'score-medium' : 'score-low');
                                                                    echo '<div class="score-badge ' . $scoreClass . '">' . $score . '/' . $maxScore . ' (' . round($percentage, 1) . '%)' . '</div>';
                                                                } else { echo '<div class="score-badge score-none">Not Scored</div>'; }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- GUIDANCE RECORDS TAB -->
                <div id="guidance" class="tab-content">
                    
                    <!-- Summary Cards -->
                    <div class="guidance-summary-cards">
                        <div class="g-card">
                            <h3><?php echo count($counselingRecords); ?></h3>
                            <p>Counseling Sessions</p>
                        </div>
                        <div class="g-card">
                            <h3><?php echo count($assessmentRecords); ?></h3>
                            <p>Assessments</p>
                        </div>
                        <div class="g-card">
                            <h3><?php echo count($incidentRecords); ?></h3>
                            <p>Incidents</p>
                        </div>
                        <div class="g-card">
                            <h3><?php echo count($appointmentRecords); ?></h3>
                            <p>Appointments</p>
                        </div>
                    </div>

                    <div class="accordion" id="guidanceAccordion">
                        
                        <!-- 1. Counseling Sessions -->
                        <div class="card">
                            <div class="card-header" id="headingCounseling" data-toggle="collapse" data-target="#collapseCounseling" aria-expanded="true" aria-controls="collapseCounseling">
                                <h5 class="mb-0">
                                    <i class="fas fa-comments mr-2 text-primary"></i> Counseling History
                                    <span class="badge badge-primary float-right"><?php echo count($counselingRecords); ?></span>
                                </h5>
                            </div>
                            <div id="collapseCounseling" class="collapse show" aria-labelledby="headingCounseling" data-parent="#guidanceAccordion">
                                <div class="card-body">
                                    <?php if(empty($counselingRecords)): ?>
                                        <div class="alert alert-light">No counseling records found.</div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered guidance-table">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Type</th>
                                                        <th>Reason</th>
                                                        <th>Status</th>
                                                        <th>Referred By</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($counselingRecords as $rec): ?>
                                                    <tr>
                                                        <td><?php echo isset($rec['session_date']) ? date('M d, Y', strtotime($rec['session_date'])) : '-'; ?></td>
                                                        <td><?php echo htmlspecialchars($rec['counseling_type'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($rec['reason'] ?? '-'); ?></td>
                                                        <td><span class="badge badge-info"><?php echo htmlspecialchars($rec['session_status'] ?? '-'); ?></span></td>
                                                        <td><?php echo htmlspecialchars($rec['referred_by'] ?? '-'); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Assessments -->
                        <div class="card">
                            <div class="card-header collapsed" id="headingAssessments" data-toggle="collapse" data-target="#collapseAssessments" aria-expanded="false" aria-controls="collapseAssessments">
                                <h5 class="mb-0">
                                    <i class="fas fa-clipboard-list mr-2 text-warning"></i> Assessments
                                    <span class="badge badge-warning float-right"><?php echo count($assessmentRecords); ?></span>
                                </h5>
                            </div>
                            <div id="collapseAssessments" class="collapse" aria-labelledby="headingAssessments" data-parent="#guidanceAccordion">
                                <div class="card-body">
                                    <?php if(empty($assessmentRecords)): ?>
                                        <div class="alert alert-light">No assessment records found.</div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered guidance-table">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Type</th>
                                                        <th>Result</th>
                                                        <th>Interpretation</th>
                                                        <th>Recommendations</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($assessmentRecords as $rec): ?>
                                                    <tr>
                                                        <td><?php echo isset($rec['assessment_date']) ? date('M d, Y', strtotime($rec['assessment_date'])) : '-'; ?></td>
                                                        <td><?php echo htmlspecialchars($rec['assessment_type'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($rec['result'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($rec['interpretation'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($rec['recommendations'] ?? '-'); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Incidents -->
                        <div class="card">
                            <div class="card-header collapsed" id="headingIncidents" data-toggle="collapse" data-target="#collapseIncidents" aria-expanded="false" aria-controls="collapseIncidents">
                                <h5 class="mb-0">
                                    <i class="fas fa-exclamation-triangle mr-2 text-danger"></i> Incidents
                                    <span class="badge badge-danger float-right"><?php echo count($incidentRecords); ?></span>
                                </h5>
                            </div>
                            <div id="collapseIncidents" class="collapse" aria-labelledby="headingIncidents" data-parent="#guidanceAccordion">
                                <div class="card-body">
                                    <?php if(empty($incidentRecords)): ?>
                                        <div class="alert alert-success">No incident records found. Good job!</div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered guidance-table">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Type</th>
                                                        <th>Description</th>
                                                        <th>Action Taken</th>
                                                        <th>Resolution</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($incidentRecords as $rec): ?>
                                                    <tr>
                                                        <td><?php echo isset($rec['incident_date']) ? date('M d, Y', strtotime($rec['incident_date'])) : '-'; ?></td>
                                                        <td><?php echo htmlspecialchars($rec['incident_type'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($rec['description'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($rec['action_taken'] ?? '-'); ?></td>
                                                        <td><?php echo htmlspecialchars($rec['resolution_status'] ?? '-'); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Appointments -->
                        <div class="card">
                            <div class="card-header collapsed" id="headingAppointments" data-toggle="collapse" data-target="#collapseAppointments" aria-expanded="false" aria-controls="collapseAppointments">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-alt mr-2 text-success"></i> Appointments
                                    <span class="badge badge-success float-right"><?php echo count($appointmentRecords); ?></span>
                                </h5>
                            </div>
                            <div id="collapseAppointments" class="collapse" aria-labelledby="headingAppointments" data-parent="#guidanceAccordion">
                                <div class="card-body">
                                    <?php if(empty($appointmentRecords)): ?>
                                        <div class="alert alert-light">No appointment records found.</div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered guidance-table">
                                                <thead>
                                                    <tr>
                                                        <th>Date & Time</th>
                                                        <th>Purpose</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($appointmentRecords as $rec): ?>
                                                    <tr>
                                                        <td><?php echo isset($rec['appointment_datetime']) ? date('M d, Y h:i A', strtotime($rec['appointment_datetime'])) : '-'; ?></td>
                                                        <td><?php echo htmlspecialchars($rec['purpose'] ?? '-'); ?></td>
                                                        <td>
                                                            <?php 
                                                                $status = strtolower($rec['status'] ?? '');
                                                                $badge = 'bg-secondary';
                                                                if($status == 'pending') $badge = 'bg-warning';
                                                                if($status == 'approved' || $status == 'confirmed') $badge = 'bg-success';
                                                                if($status == 'cancelled' || $status == 'rejected') $badge = 'bg-danger';
                                                            ?>
                                                            <span class="badge <?php echo $badge; ?>"><?php echo htmlspecialchars($rec['status']); ?></span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- END GUIDANCE RECORDS TAB -->

                <!-- Balance Tab -->
                <div id="balance" class="tab-content">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Fees</span>
                                    <span class="info-box-number">₱<?php echo number_format($totalFees, 2); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-money-check-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Payments</span>
                                    <span class="info-box-number">₱<?php echo number_format($totalPayments, 2); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box <?php echo $balance > 0 ? 'bg-warning' : ($balance < 0 ? 'bg-danger' : 'bg-info'); ?>">
                                <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Balance</span>
                                    <span class="info-box-number <?php echo $balance > 0 ? 'balance-positive' : ($balance < 0 ? 'balance-negative' : 'text-muted'); ?>">
                                        ₱<?php echo number_format(abs($balance), 2); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card fee-card">
                                <div class="card-header bg-info"><h3 class="card-title"><i class="fas fa-file-invoice-dollar mr-2"></i>Fees</h3></div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead><tr><th>Fee Name</th><th>Academic Year</th><th>Semester</th><th>Amount</th></tr></thead>
                                            <tbody>
                                                <?php if (empty($studentFees)): ?>
                                                    <tr><td colspan="4" class="text-center">No fees found</td></tr>
                                                <?php else: ?>
                                                    <?php foreach ($studentFees as $fee): ?>
                                                    <tr>
                                                        <td><?php echo $fee['fee_name']; ?></td>
                                                        <td><?php echo $fee['academic_year']; ?></td>
                                                        <td><?php echo $fee['semester']; ?></td>
                                                        <td>₱<?php echo number_format($fee['amount'], 2); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card payment-card">
                                <div class="card-header bg-success"><h3 class="card-title"><i class="fas fa-receipt mr-2"></i>Payments</h3></div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead><tr><th>OR Number</th><th>Payment Date</th><th>Amount</th><th>Discount</th><th>Net Amount</th></tr></thead>
                                            <tbody>
                                                <?php if (empty($studentPayments)): ?>
                                                    <tr><td colspan="5" class="text-center">No payments found</td></tr>
                                                <?php else: ?>
                                                    <?php foreach ($studentPayments as $payment): ?>
                                                    <tr>
                                                        <td><?php echo $payment['or_number']; ?></td>
                                                        <td><?php echo date('F d, Y', strtotime($payment['payment_date'])); ?></td>
                                                        <td>₱<?php echo number_format($payment['amount'], 2); ?></td>
                                                        <td>₱<?php echo number_format($payment['discount'], 2); ?></td>
                                                        <td>₱<?php echo number_format($payment['amount'] - $payment['discount'], 2); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Change Password</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <form method="post">
                <div class="modal-body">
                    <div class="form-group"><label>Current Password</label><input type="password" name="current_password" class="form-control" required></div>
                    <div class="form-group"><label>New Password</label><input type="password" name="new_password" class="form-control" required></div>
                    <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" name="update_password" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadPhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Upload Profile Photo</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Photo</label>
                        <div class="input-group"><div class="custom-file">
                            <input type="file" name="profile_photo" class="custom-file-input" accept="image/*" required>
                            <label class="custom-file-label">Choose file</label>
                        </div></div>
                    </div>
                    <div class="form-group"><label>Preview</label><div id="photoPreview" class="text-center"><img src="#" style="display:none; max-width:100%; max-height:300px;" class="photo-preview"><p id="previewText" class="text-muted">No file selected</p></div></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" name="upload_photo" class="btn btn-primary">Upload</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {
        // Toast
        <?php if(isset($_SESSION['message'])): ?>
            showToast('success', '<?php echo $_SESSION['message']; ?>'); <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            showToast('error', '<?php echo $_SESSION['error']; ?>'); <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        // Nav
        $('.nav-link').on('click', function(e) {
            if ($(this).attr('href') === 'logout.php') return true;
            e.preventDefault();
            $('.nav-link').removeClass('active');
            $(this).addClass('active');
            $('.tab-content').removeClass('active').hide();
            var tabId = $(this).data('tab');
            $('#' + tabId).addClass('active').show();
            $('#breadcrumb-active').text($(this).find('p').text());
            if (tabId === 'enrollments' && !$.fn.DataTable.isDataTable('#enrollmentsTable')) {
                $('#enrollmentsTable').DataTable({ responsive: true, pageLength: 10 });
            }
        });
        
        // Quarter Tabs
        $(document).on('click', '.quarter-tab', function() {
            var quarter = $(this).data('quarter');
            $('.quarter-tab').removeClass('active');
            $(this).addClass('active');
            if (quarter === 'all') { $('.activity-item').addClass('active'); } 
            else { $('.activity-item').removeClass('active'); $('.activity-item[data-quarter="' + quarter + '"]').addClass('active'); }
        });
        
        // Photo Preview
        $('#profile_photo').on('change', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) { $('#photoPreview img').attr('src', e.target.result).show(); $('#previewText').hide(); }
                reader.readAsDataURL(file);
            } else { $('#photoPreview img').hide(); $('#previewText').show(); }
        });
        $('.custom-file-input').on('change', function() { $(this).next('.custom-file-label').html($(this).val().split('\\').pop()); });
        
        function showToast(type, message) {
            var cls = type === 'success' ? 'custom-toast-success' : 'custom-toast-error';
            var icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            var html = `<div class="toast custom-toast ${cls}" role="alert" data-delay="5000"><div class="custom-toast-header"><i class="fas ${icon} mr-2"></i><strong class="mr-auto">${type}</strong><button type="button" class="close custom-toast-close"><span>&times;</span></button></div><div class="custom-toast-body">${message}</div></div>`;
            $('.toast-container').append(html);
            $('.toast').toast('show');
            $('.toast').on('hidden.bs.toast', function() { $(this).remove(); });
        }
    });
</script>
</body>
</html>