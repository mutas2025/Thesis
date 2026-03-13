<?php
session_start();
require_once '../config.php'; // Ensure this path is correct for your setup
requireRole('counselor'); 

// Handle AJAX request to store active tab
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['active_tab'])) {
    $_SESSION['active_tab_counselor'] = $_POST['active_tab'];
    echo json_encode(['success' => true]);
    exit();
}

// Helper functions
function getCurrentTab() {
    return isset($_SESSION['active_tab_counselor']) ? $_SESSION['active_tab_counselor'] : 'dashboard';
}

function redirectToTab($tab) {
    $_SESSION['active_tab_counselor'] = $tab;
    header("Location: counselor.php#$tab");
    exit();
}

// Get data for dropdowns
 $students_list = [];
 $students_res = mysqli_query($conn, "SELECT id, CONCAT(last_name, ', ', first_name, ' ', middle_name) as full_name, id_number FROM students ORDER BY last_name, first_name");
while($s = mysqli_fetch_assoc($students_res)) {
    $students_list[] = $s;
}

// Dashboard Statistics
 $statsPendingAppts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM appointments WHERE status = 'Pending'"))['count'];
 $statsOngoingSessions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM counseling_sessions WHERE session_status = 'Ongoing'"))['count'];
 $statsPendingIncidents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM incidents WHERE resolution_status = 'Pending'"))['count'];
 $statsTotalTracer = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM graduate_tracer"))['count'];
 $statsTotalExams = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM exam_results"))['count'];
 $statsTotalAssessments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM assessments"))['count'];

// Chart Data
 $counselingTypeStats = mysqli_query($conn, "SELECT counseling_type, COUNT(*) as count FROM counseling_sessions GROUP BY counseling_type");
 $incidentTypeStats = mysqli_query($conn, "SELECT incident_type, COUNT(*) as count FROM incidents GROUP BY incident_type");

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentTab = $_POST['current_tab'] ?? getCurrentTab();

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            
            // --- DASHBOARD (No Save, just visual) ---

            // --- APPOINTMENTS (Save/Update) ---
            case 'save_appointment':
                $aptId = isset($_POST['appointment_id']) && !empty($_POST['appointment_id']) ? mysqli_real_escape_string($conn, $_POST['appointment_id']) : null;
                $studentId = mysqli_real_escape_string($conn, $_POST['student_id']);
                $datetime = mysqli_real_escape_string($conn, $_POST['appointment_datetime']);
                $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
                $status = mysqli_real_escape_string($conn, $_POST['status']);

                if ($aptId) {
                    $query = "UPDATE appointments SET student_id = '$studentId', appointment_datetime = '$datetime', purpose = '$purpose', status = '$status' WHERE appointment_id = '$aptId'";
                    $msg = "Appointment updated successfully";
                } else {
                    $query = "INSERT INTO appointments (student_id, appointment_datetime, purpose, status) VALUES ('$studentId', '$datetime', '$purpose', '$status')";
                    $msg = "Appointment scheduled successfully";
                }
                
                if (mysqli_query($conn, $query)) {
                    $_SESSION['message'] = $msg;
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Error: " . mysqli_error($conn);
                    $_SESSION['message_type'] = "error";
                }
                redirectToTab('appointments');
                break;

            // --- SESSIONS (Save/Update) ---
            case 'save_session':
                $sessionId = isset($_POST['session_id']) && !empty($_POST['session_id']) ? mysqli_real_escape_string($conn, $_POST['session_id']) : null;
                $studentId = mysqli_real_escape_string($conn, $_POST['student_id']);
                $counselorId = $_SESSION['user_id'] ?? 1; 
                $sessionDate = mysqli_real_escape_string($conn, $_POST['session_date']);
                $counselingType = mysqli_real_escape_string($conn, $_POST['counseling_type']);
                
                // Handle Reason (Select or Other)
                $reason = mysqli_real_escape_string($conn, $_POST['reason']);
                if ($reason == "Please specify:") {
                    $reason = mysqli_real_escape_string($conn, $_POST['reason_other']);
                }

                $referredBy = mysqli_real_escape_string($conn, $_POST['referred_by']);
                $sessionNotes = mysqli_real_escape_string($conn, $_POST['session_notes']);
                $recommendations = mysqli_real_escape_string($conn, $_POST['recommendations']);
                $followUpDate = !empty($_POST['follow_up_date']) ? "'" . mysqli_real_escape_string($conn, $_POST['follow_up_date']) . "'" : "NULL";
                $sessionStatus = mysqli_real_escape_string($conn, $_POST['session_status']);

                if ($sessionId) {
                    $query = "UPDATE counseling_sessions SET 
                                student_id = '$studentId',
                                counselor_id = '$counselorId',
                                session_date = '$sessionDate',
                                counseling_type = '$counselingType',
                                reason = '$reason',
                                referred_by = '$referredBy',
                                session_notes = '$sessionNotes',
                                recommendations = '$recommendations',
                                follow_up_date = $followUpDate,
                                session_status = '$sessionStatus'
                              WHERE session_id = '$sessionId'";
                    $msg = "Session updated successfully";
                } else {
                    $query = "INSERT INTO counseling_sessions (student_id, counselor_id, session_date, counseling_type, reason, referred_by, session_notes, recommendations, follow_up_date, session_status) 
                              VALUES ('$studentId', '$counselorId', '$sessionDate', '$counselingType', '$reason', '$referredBy', '$sessionNotes', '$recommendations', $followUpDate, '$sessionStatus')";
                    $msg = "Session logged successfully";
                }

                if (mysqli_query($conn, $query)) {
                    $_SESSION['message'] = $msg;
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Error: " . mysqli_error($conn);
                    $_SESSION['message_type'] = "error";
                }
                redirectToTab('sessions');
                break;

            // --- INCIDENTS (Save/Update) ---
            case 'save_incident':
                $incidentId = isset($_POST['incident_id']) && !empty($_POST['incident_id']) ? mysqli_real_escape_string($conn, $_POST['incident_id']) : null;
                $studentId = mysqli_real_escape_string($conn, $_POST['student_id']);
                $incidentDate = mysqli_real_escape_string($conn, $_POST['incident_date']);
                $incidentType = mysqli_real_escape_string($conn, $_POST['incident_type']);
                $description = mysqli_real_escape_string($conn, $_POST['description']);
                $actionTaken = mysqli_real_escape_string($conn, $_POST['action_taken']);
                $resolutionStatus = mysqli_real_escape_string($conn, $_POST['resolution_status']);
                $counselorRemarks = mysqli_real_escape_string($conn, $_POST['counselor_remarks']);

                if ($incidentId) {
                    $query = "UPDATE incidents SET 
                                student_id = '$studentId',
                                incident_date = '$incidentDate',
                                incident_type = '$incidentType',
                                description = '$description',
                                action_taken = '$actionTaken',
                                resolution_status = '$resolutionStatus',
                                counselor_remarks = '$counselorRemarks'
                              WHERE incident_id = '$incidentId'";
                    $msg = "Incident updated successfully";
                } else {
                    $query = "INSERT INTO incidents (student_id, incident_date, incident_type, description, action_taken, resolution_status, counselor_remarks) 
                              VALUES ('$studentId', '$incidentDate', '$incidentType', '$description', '$actionTaken', '$resolutionStatus', '$counselorRemarks')";
                    $msg = "Incident reported successfully";
                }

                if (mysqli_query($conn, $query)) {
                    $_SESSION['message'] = $msg;
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Error: " . mysqli_error($conn);
                    $_SESSION['message_type'] = "error";
                }
                redirectToTab('incidents');
                break;

            // --- EXAM RESULTS (Save/Update) ---
            case 'save_exam':
                $examId = isset($_POST['exam_id']) && !empty($_POST['exam_id']) ? mysqli_real_escape_string($conn, $_POST['exam_id']) : null;
                $deptName = mysqli_real_escape_string($conn, $_POST['department_name']);
                $studentName = mysqli_real_escape_string($conn, $_POST['student_name']);
                $stdRaw = mysqli_real_escape_string($conn, $_POST['std_raw_score']);
                $stdPerc = mysqli_real_escape_string($conn, $_POST['std_percentile_rank']);
                $stdVerbal = mysqli_real_escape_string($conn, $_POST['std_verbal_desc']);
                $tmtRaw = mysqli_real_escape_string($conn, $_POST['tmt_raw_score']);
                $tmtInterp = mysqli_real_escape_string($conn, $_POST['tmt_interpretation']);

                if ($examId) {
                    $query = "UPDATE exam_results SET 
                                department_name = '$deptName',
                                student_name = '$studentName',
                                std_raw_score = '$stdRaw',
                                std_percentile_rank = '$stdPerc',
                                std_verbal_desc = '$stdVerbal',
                                tmt_raw_score = '$tmtRaw',
                                tmt_interpretation = '$tmtInterp'
                              WHERE exam_id = '$examId'";
                    $msg = "Exam result updated successfully";
                } else {
                    $query = "INSERT INTO exam_results (department_name, student_name, std_raw_score, std_percentile_rank, std_verbal_desc, tmt_raw_score, tmt_interpretation) 
                              VALUES ('$deptName', '$studentName', '$stdRaw', '$stdPerc', '$stdVerbal', '$tmtRaw', '$tmtInterp')";
                    $msg = "Exam result added successfully";
                }
                
                if (mysqli_query($conn, $query)) {
                    $_SESSION['message'] = $msg;
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Error: " . mysqli_error($conn);
                    $_SESSION['message_type'] = "error";
                }
                redirectToTab('exams');
                break;

            // --- ASSESSMENTS (Save/Update) ---
            case 'save_assessment':
                $assessId = isset($_POST['assessment_id']) && !empty($_POST['assessment_id']) ? mysqli_real_escape_string($conn, $_POST['assessment_id']) : null;
                $studentId = mysqli_real_escape_string($conn, $_POST['student_id']);
                $type = mysqli_real_escape_string($conn, $_POST['assessment_type']);
                $date = mysqli_real_escape_string($conn, $_POST['assessment_date']);
                $result = mysqli_real_escape_string($conn, $_POST['result']);
                $interp = mysqli_real_escape_string($conn, $_POST['interpretation']);
                $rec = mysqli_real_escape_string($conn, $_POST['recommendations']);

                if ($assessId) {
                    $query = "UPDATE assessments SET 
                                student_id = '$studentId',
                                assessment_type = '$type',
                                assessment_date = '$date',
                                result = '$result',
                                interpretation = '$interp',
                                recommendations = '$rec'
                              WHERE assessment_id = '$assessId'";
                    $msg = "Assessment updated successfully";
                } else {
                    $query = "INSERT INTO assessments (student_id, assessment_type, assessment_date, result, interpretation, recommendations) 
                              VALUES ('$studentId', '$type', '$date', '$result', '$interp', '$rec')";
                    $msg = "Assessment added successfully";
                }

                if (mysqli_query($conn, $query)) {
                    $_SESSION['message'] = $msg;
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Error: " . mysqli_error($conn);
                    $_SESSION['message_type'] = "error";
                }
                redirectToTab('assessments');
                break;

            // --- GRADUATE TRACER (Full Save/Update) ---
            case 'save_tracer':
                $tracerId = isset($_POST['tracer_id']) && !empty($_POST['tracer_id']) ? mysqli_real_escape_string($conn, $_POST['tracer_id']) : null;

                // Capture all fields
                $email = mysqli_real_escape_string($conn, $_POST['email']);
                $familyName = mysqli_real_escape_string($conn, $_POST['family_name']);
                $firstName = mysqli_real_escape_string($conn, $_POST['first_name']);
                $middleName = mysqli_real_escape_string($conn, $_POST['middle_name']);
                $yearGraduated = mysqli_real_escape_string($conn, $_POST['year_graduated']);
                $gender = mysqli_real_escape_string($conn, $_POST['gender']);
                $birthday = !empty($_POST['birthday']) ? "'" . mysqli_real_escape_string($conn, $_POST['birthday']) . "'" : "NULL";
                $civilStatus = mysqli_real_escape_string($conn, $_POST['civil_status']);
                $spouseName = mysqli_real_escape_string($conn, $_POST['spouse_name']);
                $childrenCount = mysqli_real_escape_string($conn, $_POST['children_count']);
                $address = mysqli_real_escape_string($conn, $_POST['address']);
                $contact = mysqli_real_escape_string($conn, $_POST['contact']);
                $programs = mysqli_real_escape_string($conn, $_POST['programs']);
                $postGrad = mysqli_real_escape_string($conn, $_POST['post_grad']);
                $honors = mysqli_real_escape_string($conn, $_POST['honors']);
                $boardExam = mysqli_real_escape_string($conn, $_POST['board_exam']);
                $otherSchools = mysqli_real_escape_string($conn, $_POST['other_schools']);
                $occupation = mysqli_real_escape_string($conn, $_POST['occupation']);
                $company = mysqli_real_escape_string($conn, $_POST['company']);
                $position = mysqli_real_escape_string($conn, $_POST['position']);
                $companyAddress = mysqli_real_escape_string($conn, $_POST['company_address']);
                $employmentDate = !empty($_POST['employment_date']) ? "'" . mysqli_real_escape_string($conn, $_POST['employment_date']) . "'" : "NULL";
                $salary = mysqli_real_escape_string($conn, $_POST['salary']);
                $prevCompany = mysqli_real_escape_string($conn, $_POST['prev_company']);
                $prevPosition = mysqli_real_escape_string($conn, $_POST['prev_position']);
                $prevAddress = mysqli_real_escape_string($conn, $_POST['prev_address']);
                $employmentTime = mysqli_real_escape_string($conn, $_POST['employment_time']);
                $successStory = mysqli_real_escape_string($conn, $_POST['success_story']);
                $consent = mysqli_real_escape_string($conn, $_POST['consent']);

                if ($tracerId) {
                    // UPDATE: Update all fields
                    $query = "UPDATE graduate_tracer SET 
                                email = '$email',
                                family_name = '$familyName',
                                first_name = '$firstName',
                                middle_name = '$middleName',
                                year_graduated = '$yearGraduated',
                                gender = '$gender',
                                birthday = $birthday,
                                civil_status = '$civilStatus',
                                spouse_name = '$spouseName',
                                children_count = '$childrenCount',
                                address = '$address',
                                contact = '$contact',
                                programs = '$programs',
                                post_grad = '$postGrad',
                                honors = '$honors',
                                board_exam = '$boardExam',
                                other_schools = '$otherSchools',
                                occupation = '$occupation',
                                company = '$company',
                                position = '$position',
                                company_address = '$companyAddress',
                                employment_date = $employmentDate,
                                salary = '$salary',
                                prev_company = '$prevCompany',
                                prev_position = '$prevPosition',
                                prev_address = '$prevAddress',
                                employment_time = '$employmentTime',
                                success_story = '$successStory',
                                consent = '$consent'
                              WHERE id = '$tracerId'";
                    $msg = "Tracer record updated successfully";
                } else {
                    // INSERT: Insert all fields (submitted_at handled by DB default ideally, or here)
                    $submittedAt = date('Y-m-d H:i:s');
                    $query = "INSERT INTO graduate_tracer (
                                email, family_name, first_name, middle_name, year_graduated, gender, birthday, civil_status, spouse_name, children_count, address, contact, programs, post_grad, honors, board_exam, other_schools, occupation, company, position, company_address, employment_date, salary, prev_company, prev_position, prev_address, employment_time, success_story, consent, submitted_at
                              ) VALUES (
                                '$email', '$familyName', '$firstName', '$middleName', '$yearGraduated', '$gender', $birthday, '$civilStatus', '$spouseName', '$childrenCount', '$address', '$contact', '$programs', '$postGrad', '$honors', '$boardExam', '$otherSchools', '$occupation', '$company', '$position', '$companyAddress', $employmentDate, '$salary', '$prevCompany', '$prevPosition', '$prevAddress', '$employmentTime', '$successStory', '$consent', '$submittedAt'
                              )";
                    $msg = "Tracer record added successfully";
                }

                if (mysqli_query($conn, $query)) {
                    $_SESSION['message'] = $msg;
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Error: " . mysqli_error($conn);
                    $_SESSION['message_type'] = "error";
                }
                redirectToTab('tracer');
                break;

            case 'delete_item':
                $type = $_POST['type'];
                $id = mysqli_real_escape_string($conn, $_POST['id']);
                
                switch($type) {
                    case 'appointment': $query = "DELETE FROM appointments WHERE appointment_id = '$id'"; $tab = 'appointments'; break;
                    case 'session': $query = "DELETE FROM counseling_sessions WHERE session_id = '$id'"; $tab = 'sessions'; break;
                    case 'incident': $query = "DELETE FROM incidents WHERE incident_id = '$id'"; $tab = 'incidents'; break;
                    case 'tracer': $query = "DELETE FROM graduate_tracer WHERE id = '$id'"; $tab = 'tracer'; break;
                    case 'exam': $query = "DELETE FROM exam_results WHERE exam_id = '$id'"; $tab = 'exams'; break;
                    case 'assessment': $query = "DELETE FROM assessments WHERE assessment_id = '$id'"; $tab = 'assessments'; break;
                    default: $query = ""; $tab = 'dashboard';
                }

                if ($query && mysqli_query($conn, $query)) {
                    $_SESSION['message'] = "Record deleted successfully";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Error deleting record: " . mysqli_error($conn);
                    $_SESSION['message_type'] = "error";
                }
                redirectToTab($tab);
                break;
        }
    }
}

// Handle AJAX Requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_action'])) {
    switch ($_POST['ajax_action']) {
        case 'load_sessions':
            $conditions = [];
            if (!empty($_POST['status'])) $conditions[] = "cs.session_status = '" . mysqli_real_escape_string($conn, $_POST['status']) . "'";
            if (!empty($_POST['search'])) {
                $search = mysqli_real_escape_string($conn, $_POST['search']);
                $conditions[] = "(s.last_name LIKE '%$search%' OR s.first_name LIKE '%$search%' OR s.id_number LIKE '%$search%')";
            }
            $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

            $query = "SELECT cs.*, s.id_number, CONCAT(s.last_name, ', ', s.first_name) as student_name
                      FROM counseling_sessions cs
                      JOIN students s ON cs.student_id = s.id
                      $whereClause
                      ORDER BY cs.session_date DESC";
            
            $result = mysqli_query($conn, $query);
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
            exit();

        case 'load_appointments':
            $query = "SELECT a.*, s.id_number, CONCAT(s.last_name, ', ', s.first_name) as student_name
                      FROM appointments a
                      JOIN students s ON a.student_id = s.id
                      ORDER BY a.appointment_datetime DESC";
            $result = mysqli_query($conn, $query);
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
            exit();

        case 'load_incidents':
             $conditions = [];
            if (!empty($_POST['status'])) $conditions[] = "i.resolution_status = '" . mysqli_real_escape_string($conn, $_POST['status']) . "'";
             $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

            $query = "SELECT i.*, s.id_number, CONCAT(s.last_name, ', ', s.first_name) as student_name
                      FROM incidents i
                      JOIN students s ON i.student_id = s.id
                      $whereClause
                      ORDER BY i.incident_date DESC";
            $result = mysqli_query($conn, $query);
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
            exit();

        case 'load_tracer':
             // SELECT ALL fields for the full edit functionality
             $query = "SELECT * FROM graduate_tracer ORDER BY year_graduated DESC";
            $result = mysqli_query($conn, $query);
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
            exit();

        case 'load_exams':
            $query = "SELECT * FROM exam_results ORDER BY exam_id DESC";
            $result = mysqli_query($conn, $query);
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
            exit();

        case 'load_assessments':
            $query = "SELECT a.*, s.id_number, CONCAT(s.last_name, ', ', s.first_name) as student_name
                      FROM assessments a
                      JOIN students s ON a.student_id = s.id
                      ORDER BY a.assessment_date DESC";
            $result = mysqli_query($conn, $query);
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
            echo json_encode($data);
            exit();
    }
}

// Get current user info
 $current_user_id = $_SESSION['user_id'] ?? 0;
 $current_user = $conn->query("SELECT username, fullname, role FROM users WHERE id = $current_user_id")->fetch_assoc();
 $display_name = $current_user['fullname'] ?? $current_user['username'] ?? 'Counselor';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guidance Counselor Portal</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        /* Base & Layout */
        .sidebar-menu li.active { background-color: rgba(255,255,255,0.1); }
        .sidebar-menu li.active > a { color: #fff; }
        .action-buttons { display: flex; gap: 5px; flex-wrap: wrap; }
        .modal-body { max-height: 75vh; overflow-y: auto; }
        .nav-tabs { margin-bottom: 15px; }
        .small-box { border-radius: 0.25rem; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); margin-bottom: 1rem; display: block; margin-bottom: 20px; }
        .small-box > .inner { padding: 10px; }
        .small-box > .small-box-footer { background: rgba(0,0,0,0.1); color: rgba(255,255,255,0.8); display: block; padding: 3px 0; text-align: center; text-decoration: none; z-index: 10; }
        .small-box > .icon { color: rgba(0,0,0,0.15); z-index: 0; }
        .small-box h3 { font-size: 2.5rem; font-weight: 700; margin: 0 0 10px 0; white-space: nowrap; padding: 0; }
        .card-header { display: flex; justify-content: space-between; align-items: center; }
        .main-sidebar { background-color: #004085 !important; }
        .brand-link { background-color: #002752 !important; border-bottom: 1px solid #004085; display: flex; align-items: center; justify-content: center; }
        .brand-link img { max-height: 35px; margin-right: 10px; }
        .btn-primary { background-color: #004085; border-color: #004085; }
        .btn-primary:hover { background-color: #003366; border-color: #002244; }
        .nav-tabs .nav-link.active { color: #004085; border-color: #004085 #004085 #fff; }
        .logout-btn { margin-top: auto; padding: 10px 15px; border-top: 1px solid rgba(255,255,255,0.1); }
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        .toast { background-color: #fff; border-radius: 0.25rem; box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1); margin-bottom: 0.75rem; opacity: 0; transition: opacity 0.15s linear; }
        .toast.show { opacity: 1; }
        .toast-success .toast-header { color: #fff; background-color: #004085; }
        .toast-error .toast-header { color: #fff; background-color: #dc3545; }
        
        /* Split Screen Layout */
        .split-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .split-left {
            flex: 0 0 400px; /* Increased width for full Tracer form */
            max-width: 400px;
        }
        .split-right {
            flex: 1; /* Takes remaining space */
            min-width: 300px;
        }
        .sticky-form {
            position: sticky;
            top: 20px;
        }
        
        /* Readonly Fields Styling */
        .readonly-field {
            background-color: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
            border-color: #ced4da;
        }
        
        /* Tracer Form Styling */
        .tracer-section-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #004085;
            border-bottom: 2px solid #e9ecef;
            margin-top: 15px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            text-transform: uppercase;
        }
        .form-row { display: flex; gap: 10px; }
        .form-row .form-group { flex: 1; }
        
        /* Responsive adjustments */
        @media (max-width: 991px) {
            .split-left { flex: 0 0 100%; max-width: 100%; }
            .sticky-form { position: static; }
        }

        /* Dashboard Layout specific adjustments */
        #dashboard .split-left { flex: 0 0 100%; max-width: 100%; position: static; }
        #dashboard .card { margin-bottom: 20px; }
        #dashboard .split-container { flex-direction: column; }
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
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-success elevation-4">
        <a href="#" class="brand-link">
            <img src="../uploads/csr.png" alt="CSR Logo">
            <span class="brand-text font-weight-light">Guidance Office</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="../uploads/registrar.jpg" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?= htmlspecialchars($display_name) ?></a>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="#dashboard" class="nav-link <?= getCurrentTab() == 'dashboard' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#appointments" class="nav-link <?= getCurrentTab() == 'appointments' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-calendar-check"></i>
                            <p>Appointments</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#sessions" class="nav-link <?= getCurrentTab() == 'sessions' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-comments"></i>
                            <p>Counseling Sessions</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#incidents" class="nav-link <?= getCurrentTab() == 'incidents' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-exclamation-triangle"></i>
                            <p>Incidents</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#exams" class="nav-link <?= getCurrentTab() == 'exams' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-file-signature"></i>
                            <p>Entrance Exams</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#assessments" class="nav-link <?= getCurrentTab() == 'assessments' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-clipboard-check"></i>
                            <p>Assessments</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#tracer" class="nav-link <?= getCurrentTab() == 'tracer' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fa-solid fa-graduation-cap"></i>
                            <p>Graduate Tracer</p>
                        </a>
                    </li>
                    <li class="nav-item logout-btn">
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
                <h1 class="m-0">Guidance Counselor Portal</h1>
            </div>
        </div>

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <!-- Toast Container -->
                <div class="toast-container"></div>
                
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade <?= getCurrentTab() == 'dashboard' ? 'show active' : '' ?>" id="dashboard">
                        <div class="split-container">
                            <div class="split-left">
                                <div class="row">
                                    <div class="col-lg-6 col-6">
                                        <div class="small-box bg-info">
                                            <div class="inner"><h3><?= $statsPendingAppts ?></h3><p>Pending Appts</p></div>
                                            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-6">
                                        <div class="small-box bg-success">
                                            <div class="inner"><h3><?= $statsOngoingSessions ?></h3><p>Ongoing Sessions</p></div>
                                            <div class="icon"><i class="fas fa-user-clock"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-6">
                                        <div class="small-box bg-warning">
                                            <div class="inner"><h3><?= $statsPendingIncidents ?></h3><p>Pending Incidents</p></div>
                                            <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-6">
                                        <div class="small-box bg-danger">
                                            <div class="inner"><h3><?= $statsTotalTracer ?></h3><p>Grad Tracers</p></div>
                                            <div class="icon"><i class="fas fa-user-graduate"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="split-right">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header"><h3 class="card-title">Counseling by Type</h3></div>
                                            <div class="card-body">
                                                <canvas id="counselingChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header"><h3 class="card-title">Incident Breakdown</h3></div>
                                            <div class="card-body">
                                                <canvas id="incidentChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Appointments Tab (Split) -->
                    <div class="tab-pane fade <?= getCurrentTab() == 'appointments' ? 'show active' : '' ?>" id="appointments">
                        <div class="split-container">
                            <div class="split-left">
                                <div class="card sticky-form">
                                    <div class="card-header bg-info">
                                        <h5 class="card-title m-0">Manage Appointment</h5>
                                    </div>
                                    <div class="card-body p-2">
                                        <form method="POST" action="counselor.php" id="aptForm">
                                            <input type="hidden" name="action" value="save_appointment">
                                            <input type="hidden" name="current_tab" value="appointments">
                                            <input type="hidden" name="appointment_id" id="apt_id">
                                            
                                            <div class="form-group">
                                                <label>Student</label>
                                                <select class="form-control" name="student_id" id="apt_student_id" required>
                                                    <option value="">Select Student</option>
                                                    <?php foreach($students_list as $s): ?>
                                                        <option value="<?= $s['id'] ?>"><?= $s['full_name'] ?> (<?= $s['id_number'] ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Date & Time</label>
                                                <input type="datetime-local" class="form-control" name="appointment_datetime" id="apt_datetime" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Purpose</label>
                                                <textarea class="form-control" name="purpose" id="apt_purpose" rows="2"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select class="form-control" name="status" id="apt_status">
                                                    <option value="Pending">Pending</option>
                                                    <option value="Approved">Approved</option>
                                                    <option value="Cancelled">Cancelled</option>
                                                    <option value="Completed">Completed</option>
                                                </select>
                                            </div>

                                            <div class="d-flex justify-content-between mt-3">
                                                <button type="button" class="btn btn-secondary btn-sm" id="resetAptBtn">Reset</button>
                                                <button type="submit" class="btn btn-info btn-sm">Save Appointment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="split-right">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title m-0">Appointment List</h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <table id="appointmentsTable" class="table table-bordered table-striped table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Counseling Sessions Tab (Split) -->
                    <div class="tab-pane fade <?= getCurrentTab() == 'sessions' ? 'show active' : '' ?>" id="sessions">
                        <div class="split-container">
                            <div class="split-left">
                                <div class="card sticky-form">
                                    <div class="card-header bg-primary">
                                        <h5 class="card-title m-0">Counseling Session</h5>
                                    </div>
                                    <div class="card-body p-2">
                                        <form method="POST" action="counselor.php" id="sessionForm">
                                            <input type="hidden" name="action" value="save_session">
                                            <input type="hidden" name="current_tab" value="sessions">
                                            <input type="hidden" name="session_id" id="sess_id">

                                            <div class="form-group">
                                                <label>Student</label>
                                                <select class="form-control" name="student_id" id="sess_student_id" required>
                                                    <option value="">Select Student</option>
                                                    <?php foreach($students_list as $s): ?>
                                                        <option value="<?= $s['id'] ?>"><?= $s['full_name'] ?> (<?= $s['id_number'] ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Date</label>
                                                <input type="date" class="form-control" name="session_date" id="sess_date" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Type</label>
                                                <select class="form-control" name="counseling_type" id="sess_type" required>
                                                    <option value="Academic">Academic</option>
                                                    <option value="Personal">Personal</option>
                                                    <option value="Career">Career</option>
                                                    <option value="Behavioral">Behavioral</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Referred By</label>
                                                <input type="text" class="form-control" name="referred_by" id="sess_referred" placeholder="Who referred the student?">
                                            </div>
                                            <div class="form-group">
                                                <label>Reason</label>
                                                <select class="form-control form-control-sm" id="counseling_reason" name="reason" onchange="toggleOtherReason('counseling_reason', 'counseling_reason_other')">
                                                    <option value="Frequent absences">Frequent absences</option>
                                                    <option value="Low academic performances">Low academic performances</option>
                                                    <option value="Timidity, Shyness, Withdrawal">Timidity, Shyness, Withdrawal</option>
                                                    <option value="Over Agrresiveness towards Teachers and Classmates">Over Agrresiveness</option>
                                                    <option value="Tardiness in Class">Tardiness in Class</option>
                                                    <option value="Not Wearing complete/proper uniform">Not Wearing Uniform</option>
                                                    <option value="Not wearing school ID/proper haircut">Not wearing ID/Haircut</option>
                                                    <option value="Indifference towards school work">Indifference towards school work</option>
                                                    <option value="Lack of interest in studying">Lack of interest</option>
                                                    <option value="Mental Health Problem">Mental Health Problem</option>
                                                    <option value="Family Problem">Family Problem</option>
                                                    <option value="Cutting Classes">Cutting Classes</option>
                                                    <option value="Missbehavior">Missbehavior</option>
                                                    <option value="Please specify:">Please specify:</option>
                                                </select>
                                                <input type="text" class="form-control form-control-sm mt-2" id="counseling_reason_other" name="reason_other" placeholder="Specify reason..." style="display:none;">
                                            </div>
                                            <div class="form-group">
                                                <label>Notes</label>
                                                <textarea class="form-control" name="session_notes" id="sess_notes" rows="2"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>Recommendations</label>
                                                <textarea class="form-control" name="recommendations" id="sess_recommendations" rows="2"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select class="form-control" name="session_status" id="sess_status">
                                                    <option value="Ongoing">Ongoing</option>
                                                    <option value="Completed">Completed</option>
                                                    <option value="Referred">Referred</option>
                                                </select>
                                            </div>
                                             <div class="form-group">
                                                <label>Follow Up Date</label>
                                                <input type="date" class="form-control" name="follow_up_date" id="sess_followup">
                                            </div>

                                            <div class="d-flex justify-content-between mt-3">
                                                <button type="button" class="btn btn-secondary btn-sm" id="resetSessBtn">Reset</button>
                                                <button type="submit" class="btn btn-primary btn-sm">Save Session</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="split-right">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title m-0">Session History</h5>
                                        <div class="card-tools">
                                            <select class="form-control form-control-sm" id="sessionStatusFilter" style="width: 120px;">
                                                <option value="">All</option>
                                                <option value="Ongoing">Ongoing</option>
                                                <option value="Completed">Completed</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <table id="sessionsTable" class="table table-bordered table-striped table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Incidents Tab (Split) -->
                    <div class="tab-pane fade <?= getCurrentTab() == 'incidents' ? 'show active' : '' ?>" id="incidents">
                        <div class="split-container">
                            <div class="split-left">
                                <div class="card sticky-form">
                                    <div class="card-header bg-danger">
                                        <h5 class="card-title m-0">Report Incident</h5>
                                    </div>
                                    <div class="card-body p-2">
                                        <form method="POST" action="counselor.php" id="incidentForm">
                                            <input type="hidden" name="action" value="save_incident">
                                            <input type="hidden" name="current_tab" value="incidents">
                                            <input type="hidden" name="incident_id" id="inc_id">

                                            <div class="form-group">
                                                <label>Student</label>
                                                <select class="form-control" name="student_id" id="inc_student_id" required>
                                                    <option value="">Select Student</option>
                                                    <?php foreach($students_list as $s): ?>
                                                        <option value="<?= $s['id'] ?>"><?= $s['full_name'] ?> (<?= $s['id_number'] ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Date</label>
                                                <input type="date" class="form-control" name="incident_date" id="inc_date" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Type</label>
                                                <select class="form-control" name="incident_type" id="inc_type">
                                                    <option value="Minor Offense">Minor Offense</option>
                                                    <option value="Major Offense">Major Offense</option>
                                                    <option value="Academic Dishonesty">Academic Dishonesty</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select class="form-control" name="resolution_status" id="inc_status">
                                                    <option value="Pending">Pending</option>
                                                    <option value="Resolved">Resolved</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Description</label>
                                                <textarea class="form-control" name="description" id="inc_desc" rows="3" required></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>Action Taken</label>
                                                <textarea class="form-control" name="action_taken" id="inc_action" rows="2"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>Counselor Remarks</label>
                                                <textarea class="form-control" name="counselor_remarks" id="inc_remarks" rows="2"></textarea>
                                            </div>

                                            <div class="d-flex justify-content-between mt-3">
                                                <button type="button" class="btn btn-secondary btn-sm" id="resetIncBtn">Reset</button>
                                                <button type="submit" class="btn btn-danger btn-sm">Save Incident</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="split-right">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title m-0">Incident Log</h5>
                                         <div class="card-tools">
                                            <select class="form-control form-control-sm" id="incidentStatusFilter" style="width: 120px;">
                                                <option value="">All</option>
                                                <option value="Pending">Pending</option>
                                                <option value="Resolved">Resolved</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <table id="incidentsTable" class="table table-bordered table-striped table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Entrance Exams Tab (Split Screen) -->
                    <div class="tab-pane fade <?= getCurrentTab() == 'exams' ? 'show active' : '' ?>" id="exams">
                        <div class="split-container">
                            <div class="split-left">
                                <div class="card sticky-form">
                                    <div class="card-header bg-primary">
                                        <h5 class="card-title m-0">Entrance Exam Result</h5>
                                    </div>
                                    <div class="card-body p-2">
                                        <form method="POST" action="counselor.php" id="examForm">
                                            <input type="hidden" name="action" value="save_exam">
                                            <input type="hidden" name="current_tab" value="exams">
                                            <input type="hidden" name="exam_id" id="exam_id">
                                            
                                            <div class="form-group"><label>Department</label><input type="text" class="form-control" name="department_name" id="exam_dept" required></div>
                                            <div class="form-group"><label>Student Name</label><input type="text" class="form-control" name="student_name" id="exam_student" required></div>
                                            <div class="form-group"><label>Std Raw Score</label><input type="number" step="0.01" class="form-control" name="std_raw_score" id="exam_std_raw"></div>
                                            <div class="form-group"><label>Std Percentile Rank</label><input type="number" step="0.01" class="form-control" name="std_percentile_rank" id="exam_std_perc"></div>
                                            <div class="form-group"><label>Std Verbal Desc</label><input type="text" class="form-control" name="std_verbal_desc" id="exam_std_desc"></div>
                                            <div class="form-group"><label>TMT Raw Score</label><input type="number" step="0.01" class="form-control" name="tmt_raw_score" id="exam_tmt_raw"></div>
                                            <div class="form-group"><label>TMT Interpretation</label><input type="text" class="form-control" name="tmt_interpretation" id="exam_tmt_interp"></div>

                                            <div class="d-flex justify-content-between mt-3">
                                                <button type="button" class="btn btn-secondary btn-sm" id="resetExamBtn">Reset</button>
                                                <button type="submit" class="btn btn-primary btn-sm">Save Record</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="split-right">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title m-0">Exam Records</h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <table id="examsTable" class="table table-bordered table-striped table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Dept</th>
                                                    <th>Std Score</th>
                                                    <th>% Rank</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assessments Tab (Split Screen) -->
                    <div class="tab-pane fade <?= getCurrentTab() == 'assessments' ? 'show active' : '' ?>" id="assessments">
                        <div class="split-container">
                            <div class="split-left">
                                <div class="card sticky-form">
                                    <div class="card-header bg-warning">
                                        <h5 class="card-title m-0">Student Assessment</h5>
                                    </div>
                                    <div class="card-body p-2">
                                        <form method="POST" action="counselor.php" id="assessmentForm">
                                            <input type="hidden" name="action" value="save_assessment">
                                            <input type="hidden" name="current_tab" value="assessments">
                                            <input type="hidden" name="assessment_id" id="assessment_id">

                                            <div class="form-group">
                                                <label>Student</label>
                                                <select class="form-control" name="student_id" id="assess_student_id" required>
                                                    <option value="">Select Student</option>
                                                    <?php foreach($students_list as $s): ?>
                                                        <option value="<?= $s['id'] ?>"><?= $s['full_name'] ?> (<?= $s['id_number'] ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Assessment Type</label>
                                                <select class="form-control" name="assessment_type" id="assess_type">
                                                    <option value="Psychological">Psychological</option>
                                                    <option value="Career">Career</option>
                                                    <option value="Personality">Personality</option>
                                                    <option value="Aptitude">Aptitude</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Date</label>
                                                <input type="date" class="form-control" name="assessment_date" id="assess_date" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Result</label>
                                                <textarea class="form-control" name="result" id="assess_result" rows="2"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>Interpretation</label>
                                                <textarea class="form-control" name="interpretation" id="assess_interp" rows="2"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>Recommendations</label>
                                                <textarea class="form-control" name="recommendations" id="assess_rec" rows="2"></textarea>
                                            </div>

                                            <div class="d-flex justify-content-between mt-3">
                                                <button type="button" class="btn btn-secondary btn-sm" id="resetAssessBtn">Reset</button>
                                                <button type="submit" class="btn btn-warning btn-sm text-white">Save Assessment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="split-right">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title m-0">Assessment Records</h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <table id="assessmentsTable" class="table table-bordered table-striped table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Student</th>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Result Preview</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Graduate Tracer Tab (Split Screen - FULL FIELDS) -->
                    <div class="tab-pane fade <?= getCurrentTab() == 'tracer' ? 'show active' : '' ?>" id="tracer">
                        <div class="split-container">
                            <div class="split-left">
                                <div class="card sticky-form">
                                    <div class="card-header bg-secondary">
                                        <h5 class="card-title m-0">Graduate Tracer Details</h5>
                                    </div>
                                    <div class="card-body p-2" style="max-height: 80vh; overflow-y: auto;">
                                        <form method="POST" action="counselor.php" id="tracerForm">
                                            <input type="hidden" name="action" value="save_tracer">
                                            <input type="hidden" name="current_tab" value="tracer">
                                            <input type="hidden" name="tracer_id" id="tracer_id">

                                            <!-- SECTION 1: PERSONAL INFO -->
                                            <div class="tracer-section-title">Personal Information</div>
                                            
                                            <div class="form-row">
                                                <div class="form-group"><label>Email</label><input type="email" class="form-control form-control-sm" name="email" id="tracer_email"></div>
                                                <div class="form-group"><label>Gender</label>
                                                    <select class="form-control form-control-sm" name="gender" id="tracer_gender">
                                                        <option value="Male">Male</option>
                                                        <option value="Female">Female</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group"><label>Last Name</label><input type="text" class="form-control form-control-sm" name="family_name" id="tracer_lname"></div>
                                                <div class="form-group"><label>First Name</label><input type="text" class="form-control form-control-sm" name="first_name" id="tracer_fname"></div>
                                            </div>
                                            <div class="form-group"><label>Middle Name</label><input type="text" class="form-control form-control-sm" name="middle_name" id="tracer_mname"></div>
                                            
                                            <div class="form-row">
                                                <div class="form-group"><label>Birthday</label><input type="date" class="form-control form-control-sm" name="birthday" id="tracer_birthday"></div>
                                                <div class="form-group"><label>Civil Status</label>
                                                    <select class="form-control form-control-sm" name="civil_status" id="tracer_civil">
                                                        <option value="Single">Single</option>
                                                        <option value="Married">Married</option>
                                                        <option value="Widowed">Widowed</option>
                                                        <option value="Separated">Separated</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group"><label>Address</label><textarea class="form-control form-control-sm" name="address" id="tracer_address" rows="2"></textarea></div>
                                            <div class="form-row">
                                                <div class="form-group"><label>Contact No.</label><input type="text" class="form-control form-control-sm" name="contact" id="tracer_contact"></div>
                                                <div class="form-group"><label>Year Graduated</label><input type="number" class="form-control form-control-sm" name="year_graduated" id="tracer_year"></div>
                                            </div>

                                            <div class="form-group"><label>Spouse Name (if applicable)</label><input type="text" class="form-control form-control-sm" name="spouse_name" id="tracer_spouse"></div>
                                            <div class="form-group"><label>No. of Children</label><input type="number" class="form-control form-control-sm" name="children_count" id="tracer_children"></div>

                                            <!-- SECTION 2: ACADEMIC BACKGROUND -->
                                            <div class="tracer-section-title">Academic Background</div>
                                            
                                            <div class="form-group"><label>Program / Course</label><input type="text" class="form-control form-control-sm" name="programs" id="tracer_programs"></div>
                                            <div class="form-group"><label>Post Graduate Studies</label><input type="text" class="form-control form-control-sm" name="post_grad" id="tracer_postgrad"></div>
                                            <div class="form-group"><label>Honors / Awards</label><input type="text" class="form-control form-control-sm" name="honors" id="tracer_honors"></div>
                                            <div class="form-group"><label>Board Exam Taken</label><input type="text" class="form-control form-control-sm" name="board_exam" id="tracer_board"></div>
                                            <div class="form-group"><label>Other Schools Attended</label><input type="text" class="form-control form-control-sm" name="other_schools" id="tracer_otherschools"></div>

                                            <!-- SECTION 3: EMPLOYMENT DETAILS -->
                                            <div class="tracer-section-title">Employment Details</div>
                                            
                                            <div class="form-row">
                                                <div class="form-group"><label>Present Occupation</label><input type="text" class="form-control form-control-sm" name="occupation" id="tracer_occupation"></div>
                                                <div class="form-group"><label>Company Name</label><input type="text" class="form-control form-control-sm" name="company" id="tracer_company"></div>
                                            </div>
                                            
                                            <div class="form-group"><label>Position / Job Title</label><input type="text" class="form-control form-control-sm" name="position" id="tracer_position"></div>
                                            <div class="form-group"><label>Company Address</label><input type="text" class="form-control form-control-sm" name="company_address" id="tracer_company_addr"></div>
                                            
                                            <div class="form-row">
                                                <div class="form-group"><label>Employment Date</label><input type="date" class="form-control form-control-sm" name="employment_date" id="tracer_emp_date"></div>
                                                <div class="form-group"><label>Monthly Salary</label><input type="number" class="form-control form-control-sm" name="salary" id="tracer_salary"></div>
                                            </div>
                                            
                                            <div class="form-group"><label>Employment Status / Time</label><input type="text" class="form-control form-control-sm" name="employment_time" id="tracer_emp_time" placeholder="e.g. Full Time, Regular"></div>

                                            <!-- SECTION 4: PREVIOUS EMPLOYMENT -->
                                            <div class="tracer-section-title">Previous Employment</div>
                                            
                                            <div class="form-row">
                                                <div class="form-group"><label>Previous Company</label><input type="text" class="form-control form-control-sm" name="prev_company" id="tracer_prev_comp"></div>
                                                <div class="form-group"><label>Previous Position</label><input type="text" class="form-control form-control-sm" name="prev_position" id="tracer_prev_pos"></div>
                                            </div>
                                            <div class="form-group"><label>Previous Company Address</label><input type="text" class="form-control form-control-sm" name="prev_address" id="tracer_prev_addr"></div>

                                            <!-- SECTION 5: ADDITIONAL INFO -->
                                            <div class="tracer-section-title">Additional Info</div>
                                            
                                            <div class="form-group"><label>Success Story</label><textarea class="form-control form-control-sm" name="success_story" id="tracer_story" rows="2"></textarea></div>
                                            
                                            <div class="form-group">
                                                <label>Data Consent</label>
                                                <select class="form-control form-control-sm" name="consent" id="tracer_consent">
                                                    <option value="Yes">Yes, I agree</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>

                                            <div class="d-flex justify-content-between mt-3">
                                                <button type="button" class="btn btn-secondary btn-sm" id="resetTracerBtn">Clear</button>
                                                <button type="submit" class="btn btn-secondary btn-sm">Update Record</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="split-right">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title m-0">Graduate Records</h5>
                                    </div>
                                    <div class="card-body p-0">
                                        <table id="tracerTable" class="table table-bordered table-striped table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Year</th>
                                                    <th>Program</th>
                                                    <th>Company</th>
                                                    <th>Position</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form method="POST" action="counselor.php" id="deleteForm">
                    <input type="hidden" name="action" value="delete_item">
                    <input type="hidden" name="type" id="deleteType">
                    <input type="hidden" name="id" id="deleteId">
                    <div class="modal-body">
                        <p>Are you sure you want to delete this record? This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
 $(function() {
    // Toggle Other Reason Input
    window.toggleOtherReason = function(selectId, inputId) {
        var select = document.getElementById(selectId);
        var input = document.getElementById(inputId);
        if (select.value === "Please specify:") {
            input.style.display = 'block';
            input.required = true;
        } else {
            input.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    };

    // Toast Notification Logic
    function showToast(message, type = 'success') {
        const toastId = 'toast-' + Date.now();
        const toastClass = type === 'success' ? 'toast-success' : 'toast-error';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const toastHtml = `
            <div id="${toastId}" class="toast ${toastClass}" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                <div class="toast-header">
                    <i class="fas ${icon} mr-2"></i>
                    <strong class="mr-auto">System</strong>
                    <small>Just now</small>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="toast-body">${message}</div>
            </div>
        `;
        $('.toast-container').append(toastHtml);
        $(`#${toastId}`).toast('show').on('hidden.bs.toast', function () { $(this).remove(); });
    }

    <?php if(isset($_SESSION['message'])): ?>
        showToast('<?=$_SESSION['message']?>', '<?=$_SESSION['message_type']?>');
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>

    // Store Active Tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var tabId = $(e.target).attr("href").substring(1);
        $.ajax({ type: 'POST', url: 'counselor.php', data: {active_tab: tabId} });
    });

    // Global DataTable Variables
    var aptTable, sessTable, incTable, tracerTable, examsTable, assessTable;

    // --- LOAD FUNCTIONS ---
    
    function loadAppointments() {
        if (!aptTable) {
            aptTable = $('#appointmentsTable').DataTable({
                "responsive": true, "pageLength": 5, "lengthChange": false,
                "ajax": { url: "counselor.php", type: "POST", data: { "ajax_action": "load_appointments" }, dataSrc: "" },
                "columns": [
                    { "data": "student_name" },
                    { "data": "appointment_datetime" },
                    {
                        "data": "status",
                        "render": function(data) {
                            let cls = 'bg-secondary';
                            if(data == 'Approved') cls = 'bg-success';
                            if(data == 'Pending') cls = 'bg-warning';
                            if(data == 'Cancelled') cls = 'bg-danger';
                            return `<span class="badge ${cls}">${data}</span>`;
                        }
                    },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            return `
                                <div class="action-buttons">
                                    <a href="printform.php?id=${row.appointment_id}&type=appointment" target="_blank" class="btn btn-sm btn-secondary" title="Print"><i class="fas fa-print"></i></a>
                                    <button class="btn btn-sm btn-info edit-apt-btn" data-row='${JSON.stringify(row)}'><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-type="appointment" data-id="${row.appointment_id}"><i class="fas fa-trash"></i></button>
                                </div>
                            `;
                        }
                    }
                ]
            });
        }
    }

    function loadSessions(statusFilter = '') {
        if(sessTable) sessTable.destroy();
        sessTable = $('#sessionsTable').DataTable({
            "responsive": true, "pageLength": 5, "lengthChange": false,
            "ajax": { 
                url: "counselor.php", 
                type: "POST", 
                data: function(d) { d.ajax_action = "load_sessions"; d.status = statusFilter; }, 
                dataSrc: "" 
            },
            "columns": [
                { "data": "student_name" },
                { "data": "session_date" },
                { "data": "counseling_type" },
                {
                    "data": "session_status",
                    "render": function(data) {
                        let cls = 'bg-secondary';
                        if(data == 'Ongoing') cls = 'bg-primary';
                        if(data == 'Completed') cls = 'bg-success';
                        return `<span class="badge ${cls}">${data}</span>`;
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return `
                            <div class="action-buttons">
                                <a href="printform.php?id=${row.session_id}&type=session" target="_blank" class="btn btn-sm btn-secondary" title="Print"><i class="fas fa-print"></i></a>
                                <button class="btn btn-sm btn-primary edit-sess-btn" data-row='${JSON.stringify(row)}'><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn" data-type="session" data-id="${row.session_id}"><i class="fas fa-trash"></i></button>
                            </div>
                        `;
                    }
                }
            ]
        });
    }

    function loadIncidents(statusFilter = '') {
        if(incTable) incTable.destroy();
        incTable = $('#incidentsTable').DataTable({
            "responsive": true, "pageLength": 5, "lengthChange": false,
            "ajax": { 
                url: "counselor.php", 
                type: "POST", 
                data: function(d) { d.ajax_action = "load_incidents"; d.status = statusFilter; }, 
                dataSrc: "" 
            },
            "columns": [
                { "data": "student_name" },
                { "data": "incident_date" },
                { "data": "incident_type" },
                {
                    "data": "resolution_status",
                    "render": function(data) {
                        let cls = data == 'Resolved' ? 'bg-success' : 'bg-danger';
                        return `<span class="badge ${cls}">${data}</span>`;
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return `
                            <div class="action-buttons">
                                <a href="printform.php?id=${row.incident_id}&type=incident" target="_blank" class="btn btn-sm btn-secondary" title="Print"><i class="fas fa-print"></i></a>
                                <button class="btn btn-sm btn-danger edit-inc-btn" data-row='${JSON.stringify(row)}'><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-dark delete-btn" data-type="incident" data-id="${row.incident_id}"><i class="fas fa-trash"></i></button>
                            </div>
                        `;
                    }
                }
            ]
        });
    }

    function loadExams() {
        if(examsTable) examsTable.destroy();
        examsTable = $('#examsTable').DataTable({
            "responsive": true, "pageLength": 5, "lengthChange": false,
            "ajax": { url: "counselor.php", type: "POST", data: { "ajax_action": "load_exams" }, dataSrc: "" },
            "columns": [
                { "data": "student_name" },
                { "data": "department_name" },
                { "data": "std_raw_score" },
                { "data": "std_percentile_rank" },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return `
                            <div class="action-buttons">
                                <a href="printform.php?id=${row.exam_id}&type=exam" target="_blank" class="btn btn-sm btn-secondary" title="Print"><i class="fas fa-print"></i></a>
                                <button class="btn btn-sm btn-primary edit-exam-btn" data-row='${JSON.stringify(row)}'><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn" data-type="exam" data-id="${row.exam_id}"><i class="fas fa-trash"></i></button>
                            </div>
                        `;
                    }
                }
            ]
        });
    }

    function loadAssessments() {
        if(assessTable) assessTable.destroy();
        assessTable = $('#assessmentsTable').DataTable({
            "responsive": true, "pageLength": 5, "lengthChange": false,
            "ajax": { url: "counselor.php", type: "POST", data: { "ajax_action": "load_assessments" }, dataSrc: "" },
            "columns": [
                { "data": "student_name" },
                { "data": "assessment_date" },
                { "data": "assessment_type" },
                { 
                    "data": "result", 
                    "render": function(data) {
                        return data.length > 40 ? data.substr(0, 40) + '...' : data;
                    }
                },
                {
                    "data": null,
                    "render": function(data, type, row) {
                        return `
                            <div class="action-buttons">
                                <a href="printform.php?id=${row.assessment_id}&type=assessment" target="_blank" class="btn btn-sm btn-secondary" title="Print"><i class="fas fa-print"></i></a>
                                <button class="btn btn-sm btn-warning edit-assess-btn" data-row='${JSON.stringify(row)}'><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn" data-type="assessment" data-id="${row.assessment_id}"><i class="fas fa-trash"></i></button>
                            </div>
                        `;
                    }
                }
            ]
        });
    }

    function loadTracer() {
        if (!tracerTable) {
            tracerTable = $('#tracerTable').DataTable({
                "responsive": true, "pageLength": 5, "lengthChange": false,
                "ajax": { url: "counselor.php", type: "POST", data: { "ajax_action": "load_tracer" }, dataSrc: "" },
                "columns": [
                    { 
                        "data": null,
                        "render": function(data, type, row) {
                            return (row.last_name || row.family_name) + ', ' + row.first_name;
                        }
                    },
                    { "data": "year_graduated" },
                    { "data": "programs" },
                    { "data": "company" },
                    { "data": "position" },
                    {
                        "data": null,
                        "render": function(data, type, row) {
                            return `
                                <div class="action-buttons">
                                    <a href="printform.php?id=${row.id}&type=tracer" target="_blank" class="btn btn-sm btn-secondary" title="Print"><i class="fas fa-print"></i></a>
                                    <button class="btn btn-sm btn-secondary edit-tracer-btn" data-row='${JSON.stringify(row)}'><i class="fas fa-edit"></i> Edit</button>
                                    <button class="btn btn-sm btn-danger delete-btn" data-type="tracer" data-id="${row.id}"><i class="fas fa-trash"></i></button>
                                </div>
                            `;
                        }
                    }
                ]
            });
        }
    }

    // Tab Switching Logic
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if (target === '#appointments') loadAppointments();
        if (target === '#sessions') loadSessions();
        if (target === '#incidents') loadIncidents();
        if (target === '#exams') loadExams();
        if (target === '#assessments') loadAssessments();
        if (target === '#tracer') loadTracer();
    });

    // Load initial tab
    if ($('#appointments').hasClass('show active')) loadAppointments();
    else if ($('#sessions').hasClass('show active')) loadSessions();
    else if ($('#incidents').hasClass('show active')) loadIncidents();
    else if ($('#exams').hasClass('show active')) loadExams();
    else if ($('#assessments').hasClass('show active')) loadAssessments();
    else if ($('#tracer').hasClass('show active')) loadTracer();

    // Filter Buttons
    $('#sessionStatusFilter').on('change', function() { loadSessions($(this).val()); });
    $('#incidentStatusFilter').on('change', function() { loadIncidents($(this).val()); });

    // --- INLINE EDIT LOGIC ---

    // Appointments
    $(document).on('click', '.edit-apt-btn', function() {
        var row = $(this).data('row');
        $('#apt_id').val(row.appointment_id);
        $('#apt_student_id').val(row.student_id);
        $('#apt_datetime').val(row.appointment_datetime);
        $('#apt_purpose').val(row.purpose);
        $('#apt_status').val(row.status);
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    });
    $('#resetAptBtn').click(function() { $('#apt_id').val(''); $('#aptForm')[0].reset(); });

    // Sessions
    $(document).on('click', '.edit-sess-btn', function() {
        var row = $(this).data('row');
        $('#sess_id').val(row.session_id);
        $('#sess_student_id').val(row.student_id);
        $('#sess_date').val(row.session_date);
        $('#sess_type').val(row.counseling_type);
        $('#sess_status').val(row.session_status);
        $('#sess_referred').val(row.referred_by || '');
        $('#sess_notes').val(row.session_notes || '');
        $('#sess_recommendations').val(row.recommendations || '');
        $('#sess_followup').val(row.follow_up_date || '');

        // Handle Reason Logic
        var reasonSelect = document.getElementById('counseling_reason');
        var reasonOtherInput = document.getElementById('counseling_reason_other');
        
        // Check if current reason exists in options
        var optionExists = false;
        $("#counseling_reason option").each(function(){
            if (this.value === row.reason) {
                optionExists = true;
            }
        });

        if (optionExists) {
            $(reasonSelect).val(row.reason);
            $(reasonOtherInput).hide();
            $(reasonOtherInput).prop('required', false);
            $(reasonOtherInput).val('');
        } else {
            $(reasonSelect).val("Please specify:");
            $(reasonOtherInput).show();
            $(reasonOtherInput).prop('required', true);
            $(reasonOtherInput).val(row.reason);
        }

        $('html, body').animate({ scrollTop: 0 }, 'slow');
    });
    $('#resetSessBtn').click(function() { 
        $('#sess_id').val(''); 
        $('#sessionForm')[0].reset(); 
        $('#counseling_reason_other').hide();
    });

    // Incidents
    $(document).on('click', '.edit-inc-btn', function() {
        var row = $(this).data('row');
        $('#inc_id').val(row.incident_id);
        $('#inc_student_id').val(row.student_id);
        $('#inc_date').val(row.incident_date);
        $('#inc_type').val(row.incident_type);
        $('#inc_status').val(row.resolution_status);
        $('#inc_desc').val(row.description);
        $('#inc_action').val(row.action_taken || '');
        $('#inc_remarks').val(row.counselor_remarks || '');
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    });
    $('#resetIncBtn').click(function() { $('#inc_id').val(''); $('#incidentForm')[0].reset(); });

    // Exams
    $(document).on('click', '.edit-exam-btn', function() {
        var row = $(this).data('row');
        $('#exam_id').val(row.exam_id);
        $('#exam_dept').val(row.department_name);
        $('#exam_student').val(row.student_name);
        $('#exam_std_raw').val(row.std_raw_score);
        $('#exam_std_perc').val(row.std_percentile_rank);
        $('#exam_std_desc').val(row.std_verbal_desc);
        $('#exam_tmt_raw').val(row.tmt_raw_score);
        $('#exam_tmt_interp').val(row.tmt_interpretation);
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    });
    $('#resetExamBtn').click(function() { $('#exam_id').val(''); $('#examForm')[0].reset(); });

    // Assessments
    $(document).on('click', '.edit-assess-btn', function() {
        var row = $(this).data('row');
        $('#assessment_id').val(row.assessment_id);
        $('#assess_student_id').val(row.student_id);
        $('#assess_type').val(row.assessment_type);
        $('#assess_date').val(row.assessment_date);
        $('#assess_result').val(row.result);
        $('#assess_interp').val(row.interpretation);
        $('#assess_rec').val(row.recommendations);
        $('html, body').animate({ scrollTop: 0 }, 'slow');
    });
    $('#resetAssessBtn').click(function() { $('#assessment_id').val(''); $('#assessmentForm')[0].reset(); });

    // Tracer (FULL FIELDS EDIT)
    $(document).on('click', '.edit-tracer-btn', function() {
        var row = $(this).data('row');
        $('#tracer_id').val(row.id);
        
        // Personal
        $('#tracer_email').val(row.email);
        $('#tracer_gender').val(row.gender);
        $('#tracer_lname').val(row.last_name || row.family_name);
        $('#tracer_fname').val(row.first_name);
        $('#tracer_mname').val(row.middle_name);
        $('#tracer_birthday').val(row.birthday);
        $('#tracer_civil').val(row.civil_status);
        $('#tracer_address').val(row.address);
        $('#tracer_contact').val(row.contact);
        $('#tracer_year').val(row.year_graduated);
        $('#tracer_spouse').val(row.spouse_name);
        $('#tracer_children').val(row.children_count);

        // Academic
        $('#tracer_programs').val(row.programs);
        $('#tracer_postgrad').val(row.post_grad);
        $('#tracer_honors').val(row.honors);
        $('#tracer_board').val(row.board_exam);
        $('#tracer_otherschools').val(row.other_schools);

        // Employment
        $('#tracer_occupation').val(row.occupation);
        $('#tracer_company').val(row.company);
        $('#tracer_position').val(row.position);
        $('#tracer_company_addr').val(row.company_address);
        $('#tracer_emp_date').val(row.employment_date);
        $('#tracer_salary').val(row.salary);
        $('#tracer_emp_time').val(row.employment_time);

        // Previous
        $('#tracer_prev_comp').val(row.prev_company);
        $('#tracer_prev_pos').val(row.prev_position);
        $('#tracer_prev_addr').val(row.prev_address);

        // Additional
        $('#tracer_story').val(row.success_story);
        $('#tracer_consent').val(row.consent);

        $('html, body').animate({ scrollTop: 0 }, 'slow');
    });
    $('#resetTracerBtn').click(function() { $('#tracer_id').val(''); $('#tracerForm')[0].reset(); });

    // Delete Interaction
    $(document).on('click', '.delete-btn', function() {
        var type = $(this).data('type');
        var id = $(this).data('id');
        $('#deleteType').val(type);
        $('#deleteId').val(id);
        $('#deleteModal').modal('show');
    });

    // Charts Logic (Dashboard)
    function loadCharts() {
        // Counseling Chart
        <?php 
        $cData = []; $cLabels = [];
        while($row = mysqli_fetch_assoc($counselingTypeStats)) { $cLabels[] = $row['counseling_type']; $cData[] = $row['count']; }
        ?>
        const ctx1 = document.getElementById('counselingChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($cLabels) ?>,
                datasets: [{ data: <?= json_encode($cData) ?>, backgroundColor: ['#004085', '#28a745', '#ffc107', '#17a2b8', '#dc3545'] }]
            }
        });

        // Incident Chart
        <?php 
        $iData = []; $iLabels = [];
        while($row = mysqli_fetch_assoc($incidentTypeStats)) { $iLabels[] = $row['incident_type']; $iData[] = $row['count']; }
        ?>
        const ctx2 = document.getElementById('incidentChart').getContext('2d');
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: <?= json_encode($iLabels) ?>,
                datasets: [{ data: <?= json_encode($iData) ?>, backgroundColor: ['#dc3545', '#fd7e14', '#6c757d'] }]
            }
        });
    }

    if ($('#dashboard').hasClass('show active')) loadCharts();
});
</script>
</body>
</html>