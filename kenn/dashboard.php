<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user info
 $user_id = $_SESSION['user_id'];
 $user_role = $_SESSION['role'];
 $query = "SELECT * FROM staff WHERE staff_id = $user_id";
 $result = $conn->query($query);
 $user = $result->fetch_assoc();

// Function to send email to student
function sendStudentEmail($conn, $student_id, $subject, $message, $current_user_id) {
    // We only use this if linked to student ID. 
    // Since Exam Results are standalone (per requirements), this function is used by other modules.
    $student_query = "SELECT * FROM students WHERE student_id = $student_id";
    $student_result = $conn->query($student_query);
    
    if ($student_result && $student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
        $student_email = $student['email'];
        $student_name = $student['full_name'];
        
        if (!empty($student_email) && filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: no-reply@schoolguidance.com" . "\r\n";
            
            $personalized_message = "
            <html>
            <head><title>Student Guidance Notification</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
                    .header { background-color: #1a3a5f; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
                    .content { padding: 20px; }
                    .footer { background-color: #f5f5f5; padding: 10px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 5px 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'><h2>Student Guidance Notification</h2></div>
                    <div class='content'>
                        <p>Dear $student_name,</p>
                        <p>$message</p>
                        <p>This is an automated notification from the Student Guidance System. Please do not reply to this email.</p>
                    </div>
                    <div class='footer'><p>&copy; " . date('Y') . " Student Guidance System. All rights reserved.</p></div>
                </div>
            </body>
            </html>";
            
            $mail_sent = @mail($student_email, $subject, $personalized_message, $headers);
            $error_message = $mail_sent ? '' : (error_get_last()['message'] ?? 'Unknown error');
            
            $log_action = $mail_sent ? "Email sent to $student_name ($student_email)" : "Failed to send email to $student_name ($student_email): $error_message";
            
            $stmt = $conn->prepare("INSERT INTO system_logs (staff_id, action) VALUES (?, ?)");
            $stmt->bind_param("is", $current_user_id, $log_action);
            $stmt->execute();
            
            return $mail_sent;
        }
    }
    return false;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['form_type'])) {
        switch ($_POST['form_type']) {
            case 'add_student':
            case 'update_student':
                $is_update = $_POST['form_type'] == 'update_student';
                $student_id = $is_update ? $_POST['student_id'] : null;
                
                $student_number = $_POST['student_number'];
                $full_name = $_POST['full_name'];
                $gender = $_POST['gender'];
                $date_of_birth = $_POST['date_of_birth'];
                $address = $_POST['address'];
                $contact_number = $_POST['contact_number'];
                $email = $_POST['email'];
                $year_level = $_POST['year_level'];
                $course_or_strand = $_POST['course_or_strand'];
                $section = $_POST['section'];
                $enrollment_status = $_POST['enrollment_status'];
                $guardian_name = $_POST['guardian_name'];
                $guardian_contact = $_POST['guardian_contact'];
                
                if ($is_update) {
                    $check_status = $conn->query("SELECT enrollment_status FROM students WHERE student_id = $student_id")->fetch_assoc()['enrollment_status'];
                    $query = "UPDATE students SET 
                              student_number = '$student_number', full_name = '$full_name', gender = '$gender', 
                              date_of_birth = '$date_of_birth', address = '$address', contact_number = '$contact_number', 
                              email = '$email', year_level = '$year_level', course_or_strand = '$course_or_strand', 
                              section = '$section', enrollment_status = '$enrollment_status', 
                              guardian_name = '$guardian_name', guardian_contact = '$guardian_contact' 
                              WHERE student_id = $student_id";
                    $conn->query($query);
                    
                    if ($check_status != 'Graduated' && $enrollment_status == 'Graduated') {
                        $student_data = $conn->query("SELECT * FROM students WHERE student_id = $student_id")->fetch_assoc();
                        $conn->query("INSERT INTO alumni (student_number, full_name, gender, date_of_birth, address, contact_number, email, graduation_date, year_graduated, employment_status, degree_earned, social_media_account, salary) 
                                      VALUES ('{$student_data['student_number']}', '{$student_data['full_name']}', '{$student_data['gender']}', '{$student_data['date_of_birth']}', '{$student_data['address']}', '{$student_data['contact_number']}', '{$student_data['email']}', NOW(), '', '', '', '', '')");
                    }
                    $action = "Updated student: $full_name ($student_number)";
                } else {
                    $query = "INSERT INTO students (student_number, full_name, gender, date_of_birth, address, contact_number, email, year_level, course_or_strand, section, enrollment_status, guardian_name, guardian_contact) 
                              VALUES ('$student_number', '$full_name', '$gender', '$date_of_birth', '$address', '$contact_number', '$email', '$year_level', '$course_or_strand', '$section', '$enrollment_status', '$guardian_name', '$guardian_contact')";
                    $conn->query($query);
                    $action = "Added new student: $full_name ($student_number)";
                }
                
                $stmt = $conn->prepare("INSERT INTO system_logs (staff_id, action) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $action);
                $stmt->execute();
                
                header('Location: dashboard.php?tab=students&success=' . ($is_update ? 'student_updated' : 'student_added'));
                exit();
                break;

            // --- COUNSELING SESSIONS ---
            case 'add_counseling':
            case 'update_counseling':
                $is_update = $_POST['form_type'] == 'update_counseling';
                $session_id = $is_update ? $_POST['session_id'] : null;
                
                $student_id = $_POST['student_id'];
                $counselor_id = $_POST['counselor_id'];
                $session_date = $_POST['session_date'];
                $counseling_type = $_POST['counseling_type'];
                $reason = $_POST['reason'];
                $reason_other = isset($_POST['reason_other']) ? $_POST['reason_other'] : '';
                $referred_by = $_POST['referred_by'];
                $session_notes = $_POST['session_notes'];
                $recommendations = $_POST['recommendations'];
                $follow_up_date = $_POST['follow_up_date'];
                $session_status = $_POST['session_status'];
                
                if ($reason == 'Please specify:' && !empty($reason_other)) {
                    $reason = $reason_other;
                }
                
                if ($is_update) {
                    $query = "UPDATE counseling_sessions SET student_id = '$student_id', counselor_id = '$counselor_id', session_date = '$session_date', counseling_type = '$counseling_type', reason = '$reason', referred_by = '$referred_by', session_notes = '$session_notes', recommendations = '$recommendations', follow_up_date = '$follow_up_date', session_status = '$session_status' WHERE session_id = $session_id";
                    $action = "Updated counseling session";
                } else {
                    $query = "INSERT INTO counseling_sessions (student_id, counselor_id, session_date, counseling_type, reason, referred_by, session_notes, recommendations, follow_up_date, session_status) VALUES ('$student_id', '$counselor_id', '$session_date', '$counseling_type', '$reason', '$referred_by', '$session_notes', '$recommendations', '$follow_up_date', '$session_status')";
                    $action = "Added counseling session";
                }
                $conn->query($query);
                
                $counselor_name = $conn->query("SELECT full_name FROM staff WHERE staff_id = $counselor_id")->fetch_assoc()['full_name'];
                $email_subject = $is_update ? "Your Counseling Session Has Been Updated" : "New Counseling Session Scheduled";
                $email_message = ($is_update ? "Your counseling session has been updated." : "A new counseling session has been scheduled for you.") . " Date: $session_date, Counselor: $counselor_name, Type: $counseling_type.";
                sendStudentEmail($conn, $student_id, $email_subject, $email_message, $user_id);
                
                $stmt = $conn->prepare("INSERT INTO system_logs (staff_id, action) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $action);
                $stmt->execute();
                
                header('Location: dashboard.php?tab=counseling&success=' . ($is_update ? 'counseling_updated' : 'counseling_added'));
                exit();
                break;
                
            // --- APPOINTMENTS ---
            case 'add_appointment':
            case 'update_appointment':
                $is_update = $_POST['form_type'] == 'update_appointment';
                $appointment_id = $is_update ? $_POST['appointment_id'] : null;
                
                $student_id = $_POST['student_id'];
                $counselor_id = $_POST['counselor_id'];
                $appointment_datetime = $_POST['appointment_datetime'];
                $purpose = $_POST['purpose'];
                $status = $_POST['status'];
                
                if ($is_update) {
                    $query = "UPDATE appointments SET student_id = '$student_id', counselor_id = '$counselor_id', appointment_datetime = '$appointment_datetime', purpose = '$purpose', status = '$status' WHERE appointment_id = $appointment_id";
                    $action = "Updated appointment";
                } else {
                    $query = "INSERT INTO appointments (student_id, counselor_id, appointment_datetime, purpose, status) VALUES ('$student_id', '$counselor_id', '$appointment_datetime', '$purpose', '$status')";
                    $action = "Added appointment";
                }
                $conn->query($query);
                
                $counselor_name = $conn->query("SELECT full_name FROM staff WHERE staff_id = $counselor_id")->fetch_assoc()['full_name'];
                $email_subject = ($is_update ? "Your Appointment Has Been Updated" : "New Appointment Scheduled");
                $email_message = ($is_update ? "Your appointment has been updated." : "A new appointment has been scheduled for you.") . " Time: $appointment_datetime, Counselor: $counselor_name.";
                sendStudentEmail($conn, $student_id, $email_subject, $email_message, $user_id);
                
                $stmt = $conn->prepare("INSERT INTO system_logs (staff_id, action) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $action);
                $stmt->execute();
                
                header('Location: dashboard.php?tab=appointments&success=' . ($is_update ? 'appointment_updated' : 'appointment_added'));
                exit();
                break;
                
            // --- INCIDENTS ---
            case 'add_incident':
            case 'update_incident':
                $is_update = $_POST['form_type'] == 'update_incident';
                $incident_id = $is_update ? $_POST['incident_id'] : null;
                
                $student_id = $_POST['student_id'];
                $incident_date = $_POST['incident_date'];
                $incident_type = $_POST['incident_type'];
                $description = $_POST['description'];
                $action_taken = $_POST['action_taken'];
                $counselor_remarks = $_POST['counselor_remarks'];
                $resolution_status = $_POST['resolution_status'];
                
                if ($is_update) {
                    $query = "UPDATE incidents SET student_id = '$student_id', incident_date = '$incident_date', incident_type = '$incident_type', description = '$description', action_taken = '$action_taken', counselor_remarks = '$counselor_remarks', resolution_status = '$resolution_status' WHERE incident_id = $incident_id";
                    $action = "Updated incident report";
                } else {
                    $query = "INSERT INTO incidents (student_id, incident_date, incident_type, description, action_taken, counselor_remarks, resolution_status) VALUES ('$student_id', '$incident_date', '$incident_type', '$description', '$action_taken', '$counselor_remarks', '$resolution_status')";
                    $action = "Added incident report";
                }
                $conn->query($query);
                
                $email_subject = ($is_update ? "Your Incident Report Has Been Updated" : "New Incident Report Filed");
                $email_message = ($is_update ? "The incident report regarding the incident on" : "An incident report has been filed regarding an incident that occurred on") . " $incident_date. Type: $incident_type.";
                sendStudentEmail($conn, $student_id, $email_subject, $email_message, $user_id);
                
                $stmt = $conn->prepare("INSERT INTO system_logs (staff_id, action) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $action);
                $stmt->execute();
                
                header('Location: dashboard.php?tab=incidents&success=' . ($is_update ? 'incident_updated' : 'incident_added'));
                exit();
                break;
                
            // --- ASSESSMENTS ---
            case 'add_assessment':
            case 'update_assessment':
                $is_update = $_POST['form_type'] == 'update_assessment';
                $assessment_id = $is_update ? $_POST['assessment_id'] : null;
                
                $student_id = $_POST['student_id'];
                $assessment_type = $_POST['assessment_type'];
                $assessment_date = $_POST['assessment_date'];
                $result = $_POST['result'];
                $interpretation = $_POST['interpretation'];
                $recommendations = $_POST['recommendations'];
                
                if ($is_update) {
                    $query = "UPDATE assessments SET student_id = '$student_id', assessment_type = '$assessment_type', assessment_date = '$assessment_date', result = '$result', interpretation = '$interpretation', recommendations = '$recommendations' WHERE assessment_id = $assessment_id";
                    $action = "Updated assessment";
                } else {
                    $query = "INSERT INTO assessments (student_id, assessment_type, assessment_date, result, interpretation, recommendations) VALUES ('$student_id', '$assessment_type', '$assessment_date', '$result', '$interpretation', '$recommendations')";
                    $action = "Added assessment";
                }
                $conn->query($query);
                
                $stmt = $conn->prepare("INSERT INTO system_logs (staff_id, action) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $action);
                $stmt->execute();
                
                header('Location: dashboard.php?tab=assessments&success=' . ($is_update ? 'assessment_updated' : 'assessment_added'));
                exit();
                break;

            case 'add_alumni':
            case 'update_alumni':
                $is_update = $_POST['form_type'] == 'update_alumni';
                $alumni_id = $is_update ? $_POST['alumni_id'] : null;
                
                $student_number = $_POST['student_number'];
                $full_name = $_POST['full_name'];
                $gender = $_POST['gender'];
                $date_of_birth = $_POST['date_of_birth'];
                $address = $_POST['address'];
                $contact_number = $_POST['contact_number'];
                $email = $_POST['email'];
                $graduation_date = $_POST['graduation_date'];
                $year_graduated = $_POST['year_graduated'];
                $employment_status = $_POST['employment_status'];
                $degree_earned = $_POST['degree_earned'];
                $social_media_account = $_POST['social_media_account'];
                $salary = $_POST['salary'];
                
                if ($is_update) {
                    $query = "UPDATE alumni SET student_number = '$student_number', full_name = '$full_name', gender = '$gender', date_of_birth = '$date_of_birth', address = '$address', contact_number = '$contact_number', email = '$email', graduation_date = '$graduation_date', year_graduated = '$year_graduated', employment_status = '$employment_status', degree_earned = '$degree_earned', social_media_account = '$social_media_account', salary = '$salary' WHERE alumni_id = $alumni_id";
                    $action = "Updated alumni";
                } else {
                    $query = "INSERT INTO alumni (student_number, full_name, gender, date_of_birth, address, contact_number, email, graduation_date, year_graduated, employment_status, degree_earned, social_media_account, salary) VALUES ('$student_number', '$full_name', '$gender', '$date_of_birth', '$address', '$contact_number', '$email', '$graduation_date', '$year_graduated', '$employment_status', '$degree_earned', '$social_media_account', '$salary')";
                    $action = "Added new alumni";
                }
                $conn->query($query);
                
                $stmt = $conn->prepare("INSERT INTO system_logs (staff_id, action) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $action);
                $stmt->execute();
                
                header('Location: dashboard.php?tab=alumni&success=' . ($is_update ? 'alumni_updated' : 'alumni_added'));
                exit();
                break;

            // --- ENTRANCE EXAM RESULTS (NEW) ---
            case 'add_exam':
            case 'update_exam':
                $is_update = $_POST['form_type'] == 'update_exam';
                $exam_id = $is_update ? $_POST['exam_id'] : null;

                $department_name = $_POST['department_name'];
                $student_name = $_POST['student_name'];
                
                // Standardized Test
                $std_raw_score = $_POST['std_raw_score'];
                $std_percentile_rank = $_POST['std_percentile_rank'];
                $std_verbal_desc = $_POST['std_verbal_desc'];
                
                // Teacher Made Test
                $tmt_raw_score = $_POST['tmt_raw_score'];
                $tmt_interpretation = $_POST['tmt_interpretation'];

                if ($is_update) {
                    $query = "UPDATE exam_results SET 
                              department_name = '$department_name', 
                              student_name = '$student_name',
                              std_raw_score = '$std_raw_score',
                              std_percentile_rank = '$std_percentile_rank',
                              std_verbal_desc = '$std_verbal_desc',
                              tmt_raw_score = '$tmt_raw_score',
                              tmt_interpretation = '$tmt_interpretation'
                              WHERE exam_id = $exam_id";
                    $action = "Updated Entrance Exam Result for $student_name";
                } else {
                    $query = "INSERT INTO exam_results (department_name, student_name, std_raw_score, std_percentile_rank, std_verbal_desc, tmt_raw_score, tmt_interpretation) 
                              VALUES ('$department_name', '$student_name', '$std_raw_score', '$std_percentile_rank', '$std_verbal_desc', '$tmt_raw_score', '$tmt_interpretation')";
                    $action = "Added Entrance Exam Result for $student_name";
                }
                $conn->query($query);

                $stmt = $conn->prepare("INSERT INTO system_logs (staff_id, action) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $action);
                $stmt->execute();

                header('Location: dashboard.php?tab=entrance_exams&success=' . ($is_update ? 'exam_updated' : 'exam_added'));
                exit();
                break;

            // --- GRADUATE TRACER (FIXED) ---
            case 'update_tracer':
                $id = $_POST['id'];
                $email = $_POST['email'];
                $family_name = $_POST['family_name'];
                $first_name = $_POST['first_name'];
                $middle_name = $_POST['middle_name'];
                $year_graduated = $_POST['year_graduated'];
                $gender = $_POST['gender'];
                $birthday = $_POST['birthday'];
                $civil_status = $_POST['civil_status'];
                $spouse_name = $_POST['spouse_name'];
                $children_count = $_POST['children_count'];
                $address = $_POST['address'];
                $contact = $_POST['contact'];
                $programs = $_POST['programs'];
                $post_grad = $_POST['post_grad'];
                $honors = $_POST['honors'];
                $board_exam = $_POST['board_exam'];
                $other_schools = $_POST['other_schools'];
                $occupation = $_POST['occupation'];
                $company = $_POST['company'];
                $position = $_POST['position'];
                $company_address = $_POST['company_address'];
                $employment_date = $_POST['employment_date'];
                $salary = $_POST['salary'];
                $prev_company = $_POST['prev_company'];
                $prev_position = $_POST['prev_position'];
                $prev_address = $_POST['prev_address'];
                $employment_time = $_POST['employment_time'];
                $success_story = $_POST['success_story'];
                $consent = $_POST['consent'];

                $query = "UPDATE graduate_tracer SET 
                          email = '$email', family_name = '$family_name', first_name = '$first_name', middle_name = '$middle_name',
                          year_graduated = '$year_graduated', gender = '$gender', birthday = '$birthday', civil_status = '$civil_status',
                          spouse_name = '$spouse_name', children_count = '$children_count', address = '$address', contact = '$contact',
                          programs = '$programs', post_grad = '$post_grad', honors = '$honors', board_exam = '$board_exam',
                          other_schools = '$other_schools', occupation = '$occupation', company = '$company', position = '$position',
                          company_address = '$company_address', employment_date = '$employment_date', salary = '$salary',
                          prev_company = '$prev_company', prev_position = '$prev_position', prev_address = '$prev_address',
                          employment_time = '$employment_time', success_story = '$success_story', consent = '$consent'
                          WHERE id = $id";
                $conn->query($query);

                // FIX: Assign to variable before binding
                $log_msg = "Updated graduate tracer record for: $first_name $family_name";
                $stmt = $conn->prepare("INSERT INTO system_logs (staff_id, action) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $log_msg);
                $stmt->execute();

                header('Location: dashboard.php?tab=graduate_tracer&success=tracer_updated');
                exit();
                break;
        }
    }
}

// Data Fetching for Dashboard
 $students_count = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
 $counseling_count = $conn->query("SELECT COUNT(*) as count FROM counseling_sessions")->fetch_assoc()['count'];
 $appointments_count = $conn->query("SELECT COUNT(*) as count FROM appointments")->fetch_assoc()['count'];
 $incidents_count = $conn->query("SELECT COUNT(*) as count FROM incidents")->fetch_assoc()['count'];
 $alumni_count = $conn->query("SELECT COUNT(*) as count FROM alumni")->fetch_assoc()['count'];
 $tracer_count = $conn->query("SELECT COUNT(*) as count FROM graduate_tracer")->fetch_assoc()['count'];
 $exams_count = $conn->query("SELECT COUNT(*) as count FROM exam_results")->fetch_assoc()['count'];

 $recent_students = $conn->query("SELECT * FROM students ORDER BY created_at DESC LIMIT 5");
 $recent_counseling = $conn->query("SELECT cs.*, s.full_name as student_name, st.full_name as counselor_name FROM counseling_sessions cs JOIN students s ON cs.student_id = s.student_id JOIN staff st ON cs.counselor_id = st.staff_id ORDER BY cs.created_at DESC LIMIT 5");

 $all_students = $conn->query("SELECT student_id, full_name, student_number, course_or_strand FROM students ORDER BY full_name");
 $all_counselors = $conn->query("SELECT staff_id, full_name FROM staff WHERE role = 'Guidance Counselor' ORDER BY full_name");
 $all_tracers = $conn->query("SELECT * FROM graduate_tracer ORDER BY submitted_at DESC");
 $all_exams = $conn->query("SELECT * FROM exam_results ORDER BY exam_id DESC");

 $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

 $success_message = '';
if (isset($_GET['success'])) {
    $map = [
        'student_added' => 'Student added successfully!', 'student_updated' => 'Student updated successfully!',
        'counseling_added' => 'Counseling session added successfully!', 'counseling_updated' => 'Counseling session updated successfully!',
        'appointment_added' => 'Appointment added successfully!', 'appointment_updated' => 'Appointment updated successfully!',
        'incident_added' => 'Incident report added successfully!', 'incident_updated' => 'Incident report updated successfully!',
        'assessment_added' => 'Assessment added successfully!', 'assessment_updated' => 'Assessment updated successfully!',
        'alumni_added' => 'Alumni added successfully!', 'alumni_updated' => 'Alumni updated successfully!',
        'tracer_updated' => 'Graduate Tracer updated successfully!',
        'exam_added' => 'Exam Result added successfully!', 'exam_updated' => 'Exam Result updated successfully!'
    ];
    $success_message = $map[$_GET['success']] ?? '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="uploads/csr.png" type="image/x-icon">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        .main-header { background-color: #1a3a5f; border-bottom: 1px solid #0f2238; }
        .main-header .navbar-brand { color: #fff; }
        .main-sidebar { background-color: #1a3a5f; }
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active { background-color: #d32f2f; color: #fff; }
        .content-wrapper { background-color: #f4f6f9; }
        .card-header { background-color: #1a3a5f; color: #fff; }
        .btn-primary { background-color: #1a3a5f; border-color: #1a3a5f; }
        .btn-primary:hover { background-color: #0f2238; border-color: #0f2238; }
        .btn-danger { background-color: #d32f2f; border-color: #d32f2f; }
        .main-footer { background-color: #1a3a5f; color: #fff; border-top: 1px solid #0f2238; }
        
        /* Split Layout Form Styling */
        .split-form-container {
            display: none; /* Hidden by default until JS activates it */
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            height: fit-content;
        }
        
        .split-form-container.active { display: block; }
        .split-form-container h4 { margin-top: 0; color: #1a3a5f; border-bottom: 2px solid #1a3a5f; padding-bottom: 10px; margin-bottom: 15px; font-weight: bold; }
        
        .form-group { margin-bottom: 12px; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: #555; }
        
        /* Exam Section Specifics */
        .exam-section-title {
            font-size: 0.9rem;
            font-weight: bold;
            color: #1a3a5f;
            margin-top: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        
        /* Toast */
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        .toast { min-width: 300px; margin-bottom: 10px; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); padding: 15px; display: flex; align-items: center; opacity: 0; transform: translateX(100%); transition: opacity 0.3s, transform 0.3s; }
        .toast.show { opacity: 1; transform: translateX(0); }
        .toast-success { background-color: #00a65a; color: white; }
        .toast-error { background-color: #d32f2f; color: white; }
        
        /* Modal custom scrollbar */
        .modal-body { max-height: 75vh; overflow-y: auto; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-dark">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
            <li class="nav-item d-none d-sm-inline-block"><a href="dashboard.php" class="nav-link">Home</a></li>
        </ul>
        <ul class="navbar-nav ml-auto"></ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="dashboard.php" class="brand-link">
            <img src="uploads/csr.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">Dashboard</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="<?php echo !empty($user['photo']) ? $user['photo'] : 'dist/img/user2-160x160.jpg'; ?>" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block text-white"><?php echo $user['full_name']; ?></a>
                    <a href="#" class="d-block text-white"><?php echo $user['role']; ?></a>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item"><a href="#dashboard" class="nav-link <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>" data-bs-toggle="tab"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
                    <li class="nav-item"><a href="#students" class="nav-link <?php echo $active_tab == 'students' ? 'active' : ''; ?>" data-bs-toggle="tab"><i class="nav-icon fas fa-user-graduate"></i><p>Students</p></a></li>
                    <li class="nav-item"><a href="#entrance_exams" class="nav-link <?php echo $active_tab == 'entrance_exams' ? 'active' : ''; ?>" data-bs-toggle="tab"><i class="nav-icon fas fa-pen-alt"></i><p>Entrance Exams</p></a></li>
                    <li class="nav-item"><a href="#alumni" class="nav-link <?php echo $active_tab == 'alumni' ? 'active' : ''; ?>" data-bs-toggle="tab"><i class="nav-icon fas fa-users"></i><p>Alumni</p></a></li>
                    <li class="nav-item"><a href="#graduate_tracer" class="nav-link <?php echo $active_tab == 'graduate_tracer' ? 'active' : ''; ?>" data-bs-toggle="tab"><i class="nav-icon fas fa-clipboard-list"></i><p>Graduate Tracer</p></a></li>
                    <li class="nav-item"><a href="#counseling" class="nav-link <?php echo $active_tab == 'counseling' ? 'active' : ''; ?>" data-bs-toggle="tab"><i class="nav-icon fas fa-comments"></i><p>Counseling Sessions</p></a></li>
                    <li class="nav-item"><a href="#appointments" class="nav-link <?php echo $active_tab == 'appointments' ? 'active' : ''; ?>" data-bs-toggle="tab"><i class="nav-icon fas fa-calendar-alt"></i><p>Appointments</p></a></li>
                    <li class="nav-item"><a href="#incidents" class="nav-link <?php echo $active_tab == 'incidents' ? 'active' : ''; ?>" data-bs-toggle="tab"><i class="nav-icon fas fa-exclamation-triangle"></i><p>Incidents</p></a></li>
                    <li class="nav-item"><a href="#assessments" class="nav-link <?php echo $active_tab == 'assessments' ? 'active' : ''; ?>" data-bs-toggle="tab"><i class="nav-icon fas fa-clipboard-check"></i><p>Assessments</p></a></li>
                    <li class="nav-item"><a href="#reports" class="nav-link <?php echo $active_tab == 'reports' ? 'active' : ''; ?>" data-bs-toggle="tab"><i class="nav-icon fas fa-print"></i><p>Reports</p></a></li>
                    <li class="nav-item"><form action="logout.php" method="post" id="logoutForm"><button type="button" class="nav-link btn btn-link text-danger" onclick="confirmLogout()"><i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p></button></form></li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="toast-container" id="toastContainer"></div>
        <div class="tab-content">
            <!-- Dashboard -->
            <div id="dashboard" class="tab-pane <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>">
                <div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0">Dashboard</h1></div></div></div></div>
                <section class="content"><div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3><?php echo $students_count; ?></h3><p>Total Students</p></div><div class="icon"><i class="fas fa-user-graduate"></i></div></div></div>
                        <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3><?php echo $counseling_count; ?></h3><p>Counseling Sessions</p></div><div class="icon"><i class="fas fa-comments"></i></div></div></div>
                        <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3><?php echo $appointments_count; ?></h3><p>Appointments</p></div><div class="icon"><i class="fas fa-calendar-alt"></i></div></div></div>
                        <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3><?php echo $alumni_count; ?></h3><p>Alumni</p></div><div class="icon"><i class="fas fa-users"></i></div></div></div>
                    </div>
                </div></section>
            </div>
            
            <!-- Students -->
            <div id="students" class="tab-pane <?php echo $active_tab == 'students' ? 'active' : ''; ?>">
                <div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0">Students Management</h1></div></div></div></div>
                <section class="content"><div class="container-fluid">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Students List</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- LEFT: FORM -->
                                <div class="col-md-4 col-lg-4">
                                    <div id="studentFormContainer" class="split-form-container">
                                        <h4 id="studentFormTitle">Add New Student</h4>
                                        <form action="dashboard.php" method="post">
                                            <input type="hidden" name="form_type" id="student_form_type" value="add_student">
                                            <input type="hidden" name="student_id" id="student_id">
                                            
                                            <div class="form-group"><label>Student Number</label><input type="text" class="form-control form-control-sm" id="student_number" name="student_number" required></div>
                                            <div class="form-group"><label>Full Name</label><input type="text" class="form-control form-control-sm" id="full_name" name="full_name" required></div>
                                            <div class="form-group"><label>Gender</label><select class="form-control form-control-sm" id="gender" name="gender"><option value="Male">Male</option><option value="Female">Female</option></select></div>
                                            <div class="form-group"><label>Date of Birth</label><input type="date" class="form-control form-control-sm" id="date_of_birth" name="date_of_birth"></div>
                                            
                                            <div class="row">
                                                <div class="col-6"><div class="form-group"><label>Year Level</label><input type="text" class="form-control form-control-sm" id="year_level" name="year_level"></div></div>
                                                <div class="col-6"><div class="form-group"><label>Section</label><input type="text" class="form-control form-control-sm" id="section" name="section"></div></div>
                                            </div>
                                            <div class="form-group"><label>Course/Strand</label><input type="text" class="form-control form-control-sm" id="course_or_strand" name="course_or_strand"></div>
                                            
                                            <div class="form-group"><label>Address</label><textarea class="form-control form-control-sm" id="address" name="address" rows="2"></textarea></div>
                                            <div class="form-group"><label>Contact Number</label><input type="text" class="form-control form-control-sm" id="contact_number" name="contact_number"></div>
                                            <div class="form-group"><label>Email</label><input type="email" class="form-control form-control-sm" id="email" name="email"></div>
                                            
                                            <div class="form-group"><label>Guardian Name</label><input type="text" class="form-control form-control-sm" id="guardian_name" name="guardian_name"></div>
                                            <div class="form-group"><label>Guardian Contact</label><input type="text" class="form-control form-control-sm" id="guardian_contact" name="guardian_contact"></div>
                                            
                                            <div class="form-group"><label>Status</label><select class="form-control form-control-sm" id="enrollment_status" name="enrollment_status"><option value="Active">Active</option><option value="Inactive">Inactive</option><option value="Graduated">Graduated</option></select></div>
                                            
                                            <div class="d-flex justify-content-between mt-3">
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="closeAndResetForm('studentFormContainer', 'student')">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm" id="studentFormBtn">Save Student</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- RIGHT: TABLE -->
                                <div class="col-md-8 col-lg-8">
                                    <div class="table-responsive"><table id="studentsTable" class="table table-bordered table-striped table-sm"><thead><tr><th>Student Number</th><th>Full Name</th><th>Gender</th><th>Year Level</th><th>Course/Strand</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php $students = $conn->query("SELECT * FROM students ORDER BY full_name"); while ($row = $students->fetch_assoc()): $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?><tr><td><?php echo $row['student_number']; ?></td><td><?php echo $row['full_name']; ?></td><td><?php echo $row['gender']; ?></td><td><?php echo $row['year_level']; ?></td><td><?php echo $row['course_or_strand']; ?></td><td><span class="badge badge-<?php echo $row['enrollment_status'] == 'Active' ? 'success' : 'warning'; ?>"><?php echo $row['enrollment_status']; ?></span></td><td><button class="btn btn-info btn-sm" onclick="viewStudentDetails(<?php echo $json; ?>)" title="View Details"><i class="fas fa-eye"></i></button> <a href="printgoodmoral.php?student_id=<?php echo $row['student_id']; ?>" target="_blank" class="btn btn-success btn-sm" title="Print Good Moral"><i class="fas fa-print"></i></a> <button class="btn btn-warning btn-sm" onclick="editStudent(<?php echo $json; ?>)"><i class="fas fa-edit"></i></button></td></tr><?php endwhile; ?></tbody></table></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div></section>
            </div>

            <!-- Entrance Exam Results -->
            <div id="entrance_exams" class="tab-pane <?php echo $active_tab == 'entrance_exams' ? 'active' : ''; ?>">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0">Entrance Exam Results</h1>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="content">
                    <div class="container-fluid">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Exam Results List</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- LEFT: FORM -->
                                    <div class="col-md-4 col-lg-4">
                                        <div id="examFormContainer" class="split-form-container">
                                            <h4 id="examFormTitle">Add Exam Result</h4>
                                            <form action="dashboard.php" method="post">
                                                <input type="hidden" name="form_type" id="exam_form_type" value="add_exam">
                                                <input type="hidden" name="exam_id" id="exam_id">
                                                
                                                <div class="form-group">
                                                    <label>Department Name</label>
                                                    <input type="text" class="form-control form-control-sm" id="exam_department" name="department_name" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Student Name</label>
                                                    <input type="text" class="form-control form-control-sm" id="exam_student_name" name="student_name" required>
                                                </div>

                                                <div class="exam-section-title">Standardized Test</div>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group"><label>Raw Score</label><input type="text" class="form-control form-control-sm" id="exam_std_raw" name="std_raw_score"></div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group"><label>Percentile Rank</label><input type="text" class="form-control form-control-sm" id="exam_std_percentile" name="std_percentile_rank"></div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Verbal Description</label>
                                                    <input type="text" class="form-control form-control-sm" id="exam_std_verbal" name="std_verbal_desc">
                                                </div>

                                                <div class="exam-section-title">Teacher Made Test</div>
                                                <div class="form-group">
                                                    <label>Raw Score</label>
                                                    <input type="text" class="form-control form-control-sm" id="exam_tmt_raw" name="tmt_raw_score">
                                                </div>
                                                <div class="form-group">
                                                    <label>Interpretation</label>
                                                    <input type="text" class="form-control form-control-sm" id="exam_tmt_interpretation" name="tmt_interpretation">
                                                </div>
                                                
                                                <div class="d-flex justify-content-between mt-3">
                                                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeAndResetForm('examFormContainer', 'exam')">Cancel</button>
                                                    <button type="submit" class="btn btn-primary btn-sm" id="examFormBtn">Save Result</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <!-- RIGHT: TABLE -->
                                    <div class="col-md-8 col-lg-8">
                                        <div class="table-responsive">
                                            <table id="examResultsTable" class="table table-bordered table-striped table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Department</th>
                                                        <th>Student Name</th>
                                                        <th>Std. Test (Raw/PR)</th>
                                                        <th>Verbal Desc.</th>
                                                        <th>TMT (Raw)</th>
                                                        <th>Interpretation</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    while ($row = $all_exams->fetch_assoc()): 
                                                        $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $row['department_name']; ?></td>
                                                        <td><?php echo $row['student_name']; ?></td>
                                                        <td><?php echo $row['std_raw_score'] . ' / ' . $row['std_percentile_rank']; ?></td>
                                                        <td><?php echo $row['std_verbal_desc']; ?></td>
                                                        <td><?php echo $row['tmt_raw_score']; ?></td>
                                                        <td><?php echo $row['tmt_interpretation']; ?></td>
                                                        <td>
                                                            <button class="btn btn-warning btn-sm" onclick="editExam(<?php echo $json; ?>)"><i class="fas fa-edit"></i></button>
                                                        </td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            
            <!-- Alumni -->
            <div id="alumni" class="tab-pane <?php echo $active_tab == 'alumni' ? 'active' : ''; ?>">
                <div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0">Alumni Management</h1></div></div></div></div>
                <section class="content"><div class="container-fluid">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Alumni List</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <!-- LEFT: FORM -->
                                <div class="col-md-4 col-lg-4">
                                    <div id="alumniFormContainer" class="split-form-container">
                                        <h4 id="alumniFormTitle">Add New Alumni</h4>
                                        <form action="dashboard.php" method="post">
                                            <input type="hidden" name="form_type" id="alumni_form_type" value="add_alumni">
                                            <input type="hidden" name="alumni_id" id="alumni_id">
                                            <div class="form-group"><label>Student Number</label><input type="text" class="form-control form-control-sm" id="alumni_student_number" name="student_number" required></div>
                                            <div class="form-group"><label>Full Name</label><input type="text" class="form-control form-control-sm" id="alumni_full_name" name="full_name" required></div>
                                            <div class="form-group"><label>Gender</label><select class="form-control form-control-sm" id="alumni_gender" name="gender"><option value="Male">Male</option><option value="Female">Female</option></select></div>
                                            <div class="form-group"><label>Year Graduated</label><input type="text" class="form-control form-control-sm" id="alumni_year_graduated" name="year_graduated"></div>
                                            <div class="form-group"><label>Degree Earned</label><input type="text" class="form-control form-control-sm" id="alumni_degree_earned" name="degree_earned"></div>
                                            <div class="form-group"><label>Employment Status</label><select class="form-control form-control-sm" id="alumni_employment_status" name="employment_status"><option value="Employed">Employed</option><option value="Unemployed">Unemployed</option><option value="Self-Employed">Self-Employed</option><option value="Further Studies">Further Studies</option></select></div>
                                            <div class="form-group"><label>Salary</label><input type="text" class="form-control form-control-sm" id="alumni_salary" name="salary"></div>
                                            <input type="hidden" id="alumni_date_of_birth" name="date_of_birth" value=""><input type="hidden" id="alumni_address" name="address" value=""><input type="hidden" id="alumni_contact_number" name="contact_number" value=""><input type="hidden" id="alumni_email" name="email" value=""><input type="hidden" id="alumni_graduation_date" name="graduation_date" value="">
                                            <div class="d-flex justify-content-between mt-3">
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="closeAndResetForm('alumniFormContainer', 'alumni')">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm" id="alumniFormBtn">Save Alumni</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- RIGHT: TABLE -->
                                <div class="col-md-8 col-lg-8">
                                    <div class="table-responsive"><table id="alumniTable" class="table table-bordered table-striped table-sm"><thead><tr><th>Student Number</th><th>Full Name</th><th>Gender</th><th>Year Graduated</th><th>Employment Status</th><th>Degree Earned</th><th>Actions</th></tr></thead><tbody><?php $alumni = $conn->query("SELECT * FROM alumni ORDER BY full_name"); while ($row = $alumni->fetch_assoc()): $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?><tr><td><?php echo $row['student_number']; ?></td><td><?php echo $row['full_name']; ?></td><td><?php echo $row['gender']; ?></td><td><?php echo $row['year_graduated']; ?></td><td><span class="badge badge-<?php echo $row['employment_status'] == 'Employed' ? 'success' : 'warning'; ?>"><?php echo $row['employment_status']; ?></span></td><td><?php echo $row['degree_earned']; ?></td><td><button class="btn btn-warning btn-sm" onclick="editAlumni(<?php echo $json; ?>)"><i class="fas fa-edit"></i></button></td></tr><?php endwhile; ?></tbody></table></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div></section>
            </div>

            <!-- Graduate Tracer -->
            <div id="graduate_tracer" class="tab-pane <?php echo $active_tab == 'graduate_tracer' ? 'active' : ''; ?>">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0">Graduate Tracer</h1>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="content">
                    <div class="container-fluid">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Alumni Tracer Submissions</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="tracerTable" class="table table-bordered table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Year Graduated</th>
                                                <th>Program</th>
                                                <th>Current Occupation</th>
                                                <th>Company</th>
                                                <th>Submitted At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($row = $all_tracers->fetch_assoc()): 
                                                $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                                                $fullName = $row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['family_name'];
                                            ?>
                                            <tr>
                                                <td><?php echo $fullName; ?></td>
                                                <td><?php echo $row['year_graduated']; ?></td>
                                                <td><?php echo $row['programs']; ?></td>
                                                <td><?php echo $row['occupation']; ?></td>
                                                <td><?php echo $row['company']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($row['submitted_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm" onclick="editTracer(<?php echo $json; ?>)"><i class="fas fa-edit"></i></button>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            
            <!-- Counseling Sessions -->
            <div id="counseling" class="tab-pane <?php echo $active_tab == 'counseling' ? 'active' : ''; ?>">
                <div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0">Counseling Sessions</h1></div></div></div></div>
                <section class="content"><div class="container-fluid">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Counseling Sessions List</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <!-- LEFT: FORM -->
                                <div class="col-md-4 col-lg-4">
                                    <div id="counselingFormContainer" class="split-form-container">
                                        <h4 id="counselingFormTitle">Add New Session</h4>
                                        <form action="dashboard.php" method="post">
                                            <input type="hidden" name="form_type" id="counseling_form_type" value="add_counseling">
                                            <input type="hidden" name="session_id" id="counseling_session_id">
                                            <div class="form-group"><label>Student</label><select class="form-control form-control-sm" id="counseling_student_id" name="student_id" required><option value="">Select Student</option><?php $all_students->data_seek(0); while ($s = $all_students->fetch_assoc()): ?><option value="<?php echo $s['student_id']; ?>"><?php echo $s['full_name']; ?></option><?php endwhile; ?></select></div>
                                            <div class="form-group"><label>Counselor</label><select class="form-control form-control-sm" id="counseling_counselor_id" name="counselor_id" required><option value="">Select Counselor</option><?php $all_counselors->data_seek(0); while ($c = $all_counselors->fetch_assoc()): ?><option value="<?php echo $c['staff_id']; ?>"><?php echo $c['full_name']; ?></option><?php endwhile; ?></select></div>
                                            <div class="form-group"><label>Session Date</label><input type="date" class="form-control form-control-sm" id="counseling_session_date" name="session_date" required></div>
                                            <div class="form-group"><label>Type</label><select class="form-control form-control-sm" id="counseling_counseling_type" name="counseling_type"><option value="Academic">Academic</option><option value="Personal">Personal</option><option value="Career">Career</option><option value="Behavioral">Behavioral</option></select></div>
                                            <div class="form-group"><label>Reason</label>
                                                <select class="form-control form-control-sm" id="counseling_reason" name="reason" onchange="toggleOtherReason('counseling_reason', 'counseling_reason_other')">
                                                    <option value="Frequent absences">Frequent absences</option><option value="Low academic performances">Low academic performances</option><option value="Timidity, Shyness, Withdrawal">Timidity, Shyness, Withdrawal</option><option value="Over Agrresiveness towards Teachers and Classmates">Over Agrresiveness</option><option value="Tardiness in Class">Tardiness in Class</option><option value="Not Wearing complete/proper uniform">Not Wearing Uniform</option><option value="Not wearing school ID/proper haircut">Not wearing ID/Haircut</option><option value="Indifference towards school work">Indifference towards school work</option><option value="Lack of interest in studying">Lack of interest</option><option value="Mental Health Problem">Mental Health Problem</option><option value="Family Problem">Family Problem</option><option value="Cutting Classes">Cutting Classes</option><option value="Missbehavior">Missbehavior</option><option value="Please specify:">Please specify:</option>
                                                </select>
                                                <input type="text" class="form-control form-control-sm mt-2" id="counseling_reason_other" name="reason_other" style="display:none;" placeholder="Please specify reason">
                                            </div>
                                            <div class="form-group"><label>Referred By</label><input type="text" class="form-control form-control-sm" id="counseling_referred_by" name="referred_by" placeholder="Who referred?"></div>
                                            <div class="form-group"><label>Follow-up Date</label><input type="date" class="form-control form-control-sm" id="counseling_follow_up_date" name="follow_up_date"></div>
                                            <div class="form-group"><label>Status</label><select class="form-control form-control-sm" id="counseling_session_status" name="session_status"><option value="Ongoing">Ongoing</option><option value="Completed">Completed</option><option value="Referred">Referred</option></select></div>
                                            <div class="form-group"><label>Session Notes</label><textarea class="form-control form-control-sm" id="counseling_session_notes" name="session_notes" rows="2"></textarea></div>
                                            <div class="form-group"><label>Recommendations</label><textarea class="form-control form-control-sm" id="counseling_recommendations" name="recommendations" rows="2"></textarea></div>
                                            <div class="d-flex justify-content-between mt-3">
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="closeAndResetForm('counselingFormContainer', 'counseling')">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm" id="counselingFormBtn">Save Session</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- RIGHT: TABLE -->
                                <div class="col-md-8 col-lg-8">
                                    <div class="table-responsive"><table id="counselingTable" class="table table-bordered table-striped table-sm"><thead><tr><th>Student</th><th>Counselor</th><th>Date</th><th>Type</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php $counseling = $conn->query("SELECT cs.*, s.full_name as student_name, st.full_name as counselor_name FROM counseling_sessions cs JOIN students s ON cs.student_id = s.student_id JOIN staff st ON cs.counselor_id = st.staff_id ORDER BY cs.session_date DESC"); while ($row = $counseling->fetch_assoc()): $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?><tr><td><?php echo $row['student_name']; ?></td><td><?php echo $row['counselor_name']; ?></td><td><?php echo $row['session_date']; ?></td><td><?php echo $row['counseling_type']; ?></td><td><?php echo $row['reason']; ?></td><td><span class="badge badge-<?php echo $row['session_status'] == 'Completed' ? 'success' : 'warning'; ?>"><?php echo $row['session_status']; ?></span></td><td><button class="btn btn-warning btn-sm" onclick="editCounseling(<?php echo $json; ?>)"><i class="fas fa-edit"></i></button></td></tr><?php endwhile; ?></tbody></table></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div></section>
            </div>
            
            <!-- Appointments -->
            <div id="appointments" class="tab-pane <?php echo $active_tab == 'appointments' ? 'active' : ''; ?>">
                <div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0">Appointments</h1></div></div></div></div>
                <section class="content"><div class="container-fluid">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Appointments List</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <!-- LEFT: FORM -->
                                <div class="col-md-4 col-lg-4">
                                    <div id="appointmentFormContainer" class="split-form-container">
                                        <h4 id="appointmentFormTitle">Add New Appointment</h4>
                                        <form action="dashboard.php" method="post">
                                            <input type="hidden" name="form_type" id="appointment_form_type" value="add_appointment">
                                            <input type="hidden" name="appointment_id" id="appointment_appointment_id">
                                            <div class="form-group"><label>Student</label><select class="form-control form-control-sm" id="appointment_student_id" name="student_id" required><option value="">Select Student</option><?php $all_students->data_seek(0); while ($s = $all_students->fetch_assoc()): ?><option value="<?php echo $s['student_id']; ?>"><?php echo $s['full_name']; ?></option><?php endwhile; ?></select></div>
                                            <div class="form-group"><label>Counselor</label><select class="form-control form-control-sm" id="appointment_counselor_id" name="counselor_id" required><option value="">Select Counselor</option><?php $all_counselors->data_seek(0); while ($c = $all_counselors->fetch_assoc()): ?><option value="<?php echo $c['staff_id']; ?>"><?php echo $c['full_name']; ?></option><?php endwhile; ?></select></div>
                                            <div class="form-group"><label>Date & Time</label><input type="datetime-local" class="form-control form-control-sm" id="appointment_appointment_datetime" name="appointment_datetime" required></div>
                                            <div class="form-group"><label>Status</label><select class="form-control form-control-sm" id="appointment_status" name="status"><option value="Pending">Pending</option><option value="Approved">Approved</option><option value="Cancelled">Cancelled</option><option value="Completed">Completed</option></select></div>
                                            <div class="form-group"><label>Purpose</label><textarea class="form-control form-control-sm" id="appointment_purpose" name="purpose" rows="2"></textarea></div>
                                            <div class="d-flex justify-content-between mt-3">
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="closeAndResetForm('appointmentFormContainer', 'appointment')">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm" id="appointmentFormBtn">Save Appointment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- RIGHT: TABLE -->
                                <div class="col-md-8 col-lg-8">
                                    <div class="table-responsive"><table id="appointmentsTable" class="table table-bordered table-striped table-sm"><thead><tr><th>Student</th><th>Counselor</th><th>Date & Time</th><th>Purpose</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php $appointments = $conn->query("SELECT a.*, s.full_name as student_name, st.full_name as counselor_name FROM appointments a JOIN students s ON a.student_id = s.student_id JOIN staff st ON a.counselor_id = st.staff_id ORDER BY a.appointment_datetime DESC"); while ($row = $appointments->fetch_assoc()): $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?><tr><td><?php echo $row['student_name']; ?></td><td><?php echo $row['counselor_name']; ?></td><td><?php echo $row['appointment_datetime']; ?></td><td><?php echo $row['purpose']; ?></td><td><span class="badge badge-<?php echo $row['status'] == 'Completed' ? 'success' : 'warning'; ?>"><?php echo $row['status']; ?></span></td><td><button class="btn btn-warning btn-sm" onclick="editAppointment(<?php echo $json; ?>)"><i class="fas fa-edit"></i></button></td></tr><?php endwhile; ?></tbody></table></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div></section>
            </div>
            
            <!-- Incidents -->
            <div id="incidents" class="tab-pane <?php echo $active_tab == 'incidents' ? 'active' : ''; ?>">
                <div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0">Incidents</h1></div></div></div></div>
                <section class="content"><div class="container-fluid">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Incidents List</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <!-- LEFT: FORM -->
                                <div class="col-md-4 col-lg-4">
                                    <div id="incidentFormContainer" class="split-form-container">
                                        <h4 id="incidentFormTitle">Add New Incident</h4>
                                        <form action="dashboard.php" method="post">
                                            <input type="hidden" name="form_type" id="incident_form_type" value="add_incident">
                                            <input type="hidden" name="incident_id" id="incident_incident_id">
                                            <div class="form-group"><label>Student</label><select class="form-control form-control-sm" id="incident_student_id" name="student_id" required><option value="">Select Student</option><?php $all_students->data_seek(0); while ($s = $all_students->fetch_assoc()): ?><option value="<?php echo $s['student_id']; ?>"><?php echo $s['full_name']; ?></option><?php endwhile; ?></select></div>
                                            <div class="form-group"><label>Incident Date</label><input type="date" class="form-control form-control-sm" id="incident_incident_date" name="incident_date" required></div>
                                            <div class="form-group"><label>Type</label><input type="text" class="form-control form-control-sm" id="incident_incident_type" name="incident_type"></div>
                                            <div class="form-group"><label>Resolution Status</label><select class="form-control form-control-sm" id="incident_resolution_status" name="resolution_status"><option value="Pending">Pending</option><option value="Resolved">Resolved</option></select></div>
                                            <div class="form-group"><label>Description</label><textarea class="form-control form-control-sm" id="incident_description" name="description" rows="2"></textarea></div>
                                            <div class="form-group"><label>Action Taken</label><textarea class="form-control form-control-sm" id="incident_action_taken" name="action_taken" rows="2"></textarea></div>
                                            <div class="form-group"><label>Counselor Remarks</label><textarea class="form-control form-control-sm" id="incident_counselor_remarks" name="counselor_remarks" rows="2"></textarea></div>
                                            <div class="d-flex justify-content-between mt-3">
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="closeAndResetForm('incidentFormContainer', 'incident')">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm" id="incidentFormBtn">Save Incident</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- RIGHT: TABLE -->
                                <div class="col-md-8 col-lg-8">
                                    <div class="table-responsive"><table id="incidentsTable" class="table table-bordered table-striped table-sm"><thead><tr><th>Student</th><th>Date</th><th>Type</th><th>Description</th><th>Resolution Status</th><th>Actions</th></tr></thead><tbody><?php $incidents = $conn->query("SELECT i.*, s.full_name as student_name FROM incidents i JOIN students s ON i.student_id = s.student_id ORDER BY i.incident_date DESC"); while ($row = $incidents->fetch_assoc()): $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?><tr><td><?php echo $row['student_name']; ?></td><td><?php echo $row['incident_date']; ?></td><td><?php echo $row['incident_type']; ?></td><td><?php echo substr($row['description'], 0, 50) . '...'; ?></td><td><span class="badge badge-<?php echo $row['resolution_status'] == 'Resolved' ? 'success' : 'danger'; ?>"><?php echo $row['resolution_status']; ?></span></td><td><button class="btn btn-warning btn-sm" onclick="editIncident(<?php echo $json; ?>)"><i class="fas fa-edit"></i></button></td></tr><?php endwhile; ?></tbody></table></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div></section>
            </div>
            
            <!-- Assessments -->
            <div id="assessments" class="tab-pane <?php echo $active_tab == 'assessments' ? 'active' : ''; ?>">
                <div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0">Assessments</h1></div></div></div></div>
                <section class="content"><div class="container-fluid">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Assessments List</h3></div>
                        <div class="card-body">
                            <div class="row">
                                <!-- LEFT: FORM -->
                                <div class="col-md-4 col-lg-4">
                                    <div id="assessmentFormContainer" class="split-form-container">
                                        <h4 id="assessmentFormTitle">Add New Assessment</h4>
                                        <form action="dashboard.php" method="post">
                                            <input type="hidden" name="form_type" id="assessment_form_type" value="add_assessment">
                                            <input type="hidden" name="assessment_id" id="assessment_assessment_id">
                                            <div class="form-group"><label>Student</label><select class="form-control form-control-sm" id="assessment_student_id" name="student_id" required><option value="">Select Student</option><?php $all_students->data_seek(0); while ($s = $all_students->fetch_assoc()): ?><option value="<?php echo $s['student_id']; ?>"><?php echo $s['full_name']; ?></option><?php endwhile; ?></select></div>
                                            <div class="form-group"><label>Type</label><input type="text" class="form-control form-control-sm" id="assessment_assessment_type" name="assessment_type" placeholder="e.g. Personality Test"></div>
                                            <div class="form-group"><label>Date</label><input type="date" class="form-control form-control-sm" id="assessment_assessment_date" name="assessment_date"></div>
                                            <div class="form-group"><label>Result</label><textarea class="form-control form-control-sm" id="assessment_result" name="result" rows="2" placeholder="Summary of results"></textarea></div>
                                            <div class="form-group"><label>Interpretation</label><textarea class="form-control form-control-sm" id="assessment_interpretation" name="interpretation" rows="2" placeholder="Analysis of the results"></textarea></div>
                                            <div class="form-group"><label>Recommendations</label><textarea class="form-control form-control-sm" id="assessment_recommendations" name="recommendations" rows="2" placeholder="Steps to take based on results"></textarea></div>
                                            <div class="d-flex justify-content-between mt-3">
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="closeAndResetForm('assessmentFormContainer', 'assessment')">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm" id="assessmentFormBtn">Save Assessment</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- RIGHT: TABLE -->
                                <div class="col-md-8 col-lg-8">
                                    <div class="table-responsive"><table id="assessmentsTable" class="table table-bordered table-striped table-sm"><thead><tr><th>Student</th><th>Type</th><th>Date</th><th>Result</th><th>Actions</th></tr></thead><tbody><?php $assessments = $conn->query("SELECT a.*, s.full_name as student_name FROM assessments a JOIN students s ON a.student_id = s.student_id ORDER BY a.assessment_date DESC"); while ($row = $assessments->fetch_assoc()): $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?><tr><td><?php echo $row['student_name']; ?></td><td><?php echo $row['assessment_type']; ?></td><td><?php echo $row['assessment_date']; ?></td><td><?php echo substr($row['result'], 0, 50) . '...'; ?></td><td><button class="btn btn-warning btn-sm" onclick="editAssessment(<?php echo $json; ?>)"><i class="fas fa-edit"></i></button></td></tr><?php endwhile; ?></tbody></table></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div></section>
            </div>
            
            <!-- Reports -->
            <div id="reports" class="tab-pane <?php echo $active_tab == 'reports' ? 'active' : ''; ?>">
                <div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0">Reports</h1></div></div></div></div>
                <section class="content"><div class="container-fluid">
                    <div class="row">
                        <div class="col-md-4"><div class="card"><div class="card-header"><h3 class="card-title">Entrance Exam Results</h3></div><div class="card-body"><p>Generate and print entrance exam reports.</p><a href="printexamresults.php" target="_blank" class="btn btn-primary btn-block"><i class="fas fa-print"></i> Print Entrance Exam Results</a></div></div></div>
                        <div class="col-md-4"><div class="card"><div class="card-header"><h3 class="card-title">Student Data</h3></div><div class="card-body"><p>Generate and print student data reports.</p><a href="printstudentdata.php" target="_blank" class="btn btn-primary btn-block"><i class="fas fa-print"></i> Print Student Data</a></div></div></div>
                        <div class="col-md-4"><div class="card"><div class="card-header"><h3 class="card-title">General Reports</h3></div><div class="card-body"><p>Generate comprehensive reports on services.</p><a href="printreports.php" target="_blank" class="btn btn-primary btn-block"><i class="fas fa-print"></i> Print Reports</a></div></div></div>
                        <div class="col-md-4"><div class="card"><div class="card-header"><h3 class="card-title">Alumni Tracer</h3></div><div class="card-body"><p>View Alumni Tracer Responses.</p><a href="graduatetracerreport.php" target="_blank" class="btn btn-primary btn-block"><i class="fas fa-print"></i> Alumni Tracer Reports</a></div></div></div>
                    </div>
                </div></section>
            </div>
        </div>
    </div>
    <footer class="main-footer"><strong>Copyright &copy; <?php echo date('Y'); ?> Dashboard.</strong> All rights reserved.</footer>
</div>

<!-- Student Details Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">Student Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-sm">
                    <tr><th>Student Number</th><td id="view_student_number"></td></tr>
                    <tr><th>Full Name</th><td id="view_full_name"></td></tr>
                    <tr><th>Gender</th><td id="view_gender"></td></tr>
                    <tr><th>Date of Birth</th><td id="view_date_of_birth"></td></tr>
                    <tr><th>Address</th><td id="view_address"></td></tr>
                    <tr><th>Contact Number</th><td id="view_contact_number"></td></tr>
                    <tr><th>Email</th><td id="view_email"></td></tr>
                    <tr><th>Year Level</th><td id="view_year_level"></td></tr>
                    <tr><th>Course/Strand</th><td id="view_course_or_strand"></td></tr>
                    <tr><th>Section</th><td id="view_section"></td></tr>
                    <tr><th>Guardian Name</th><td id="view_guardian_name"></td></tr>
                    <tr><th>Guardian Contact</th><td id="view_guardian_contact"></td></tr>
                    <tr><th>Enrollment Status</th><td id="view_enrollment_status"></td></tr>
                    <tr><th>Created At</th><td id="view_created_at"></td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Graduate Tracer Edit Modal -->
<div class="modal fade" id="tracerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">Edit Graduate Tracer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="dashboard.php" method="post" id="tracerForm">
                    <input type="hidden" name="form_type" value="update_tracer">
                    <input type="hidden" name="id" id="tracer_id">
                    
                    <h5 class="text-primary border-bottom pb-1 mb-3">Personal Information</h5>
                    <div class="row">
                        <div class="col-md-4 mb-2"><label>Email</label><input type="email" class="form-control form-control-sm" name="email" id="tracer_email"></div>
                        <div class="col-md-4 mb-2"><label>Family Name</label><input type="text" class="form-control form-control-sm" name="family_name" id="tracer_family_name"></div>
                        <div class="col-md-4 mb-2"><label>First Name</label><input type="text" class="form-control form-control-sm" name="first_name" id="tracer_first_name"></div>
                        <div class="col-md-4 mb-2"><label>Middle Name</label><input type="text" class="form-control form-control-sm" name="middle_name" id="tracer_middle_name"></div>
                        <div class="col-md-4 mb-2"><label>Year Graduated</label><input type="text" class="form-control form-control-sm" name="year_graduated" id="tracer_year_graduated"></div>
                        <div class="col-md-4 mb-2"><label>Gender</label><input type="text" class="form-control form-control-sm" name="gender" id="tracer_gender"></div>
                        <div class="col-md-4 mb-2"><label>Birthday</label><input type="date" class="form-control form-control-sm" name="birthday" id="tracer_birthday"></div>
                        <div class="col-md-4 mb-2"><label>Civil Status</label><input type="text" class="form-control form-control-sm" name="civil_status" id="tracer_civil_status"></div>
                        <div class="col-md-4 mb-2"><label>Spouse Name</label><input type="text" class="form-control form-control-sm" name="spouse_name" id="tracer_spouse_name"></div>
                        <div class="col-md-4 mb-2"><label>Children Count</label><input type="number" class="form-control form-control-sm" name="children_count" id="tracer_children_count"></div>
                        <div class="col-md-4 mb-2"><label>Contact Number</label><input type="text" class="form-control form-control-sm" name="contact" id="tracer_contact"></div>
                        <div class="col-md-8 mb-2"><label>Address</label><textarea class="form-control form-control-sm" name="address" id="tracer_address" rows="1"></textarea></div>
                    </div>

                    <h5 class="text-primary border-bottom pb-1 mb-3 mt-4">Educational Background</h5>
                    <div class="row">
                        <div class="col-md-6 mb-2"><label>Programs</label><input type="text" class="form-control form-control-sm" name="programs" id="tracer_programs"></div>
                        <div class="col-md-6 mb-2"><label>Post Graduate</label><input type="text" class="form-control form-control-sm" name="post_grad" id="tracer_post_grad"></div>
                        <div class="col-md-6 mb-2"><label>Honors</label><input type="text" class="form-control form-control-sm" name="honors" id="tracer_honors"></div>
                        <div class="col-md-6 mb-2"><label>Board Exam</label><input type="text" class="form-control form-control-sm" name="board_exam" id="tracer_board_exam"></div>
                        <div class="col-md-12 mb-2"><label>Other Schools</label><textarea class="form-control form-control-sm" name="other_schools" id="tracer_other_schools" rows="1"></textarea></div>
                    </div>

                    <h5 class="text-primary border-bottom pb-1 mb-3 mt-4">Employment Details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-2"><label>Occupation</label><input type="text" class="form-control form-control-sm" name="occupation" id="tracer_occupation"></div>
                        <div class="col-md-6 mb-2"><label>Company</label><input type="text" class="form-control form-control-sm" name="company" id="tracer_company"></div>
                        <div class="col-md-6 mb-2"><label>Position</label><input type="text" class="form-control form-control-sm" name="position" id="tracer_position"></div>
                        <div class="col-md-6 mb-2"><label>Employment Date</label><input type="date" class="form-control form-control-sm" name="employment_date" id="tracer_employment_date"></div>
                        <div class="col-md-6 mb-2"><label>Salary</label><input type="text" class="form-control form-control-sm" name="salary" id="tracer_salary"></div>
                        <div class="col-md-12 mb-2"><label>Company Address</label><textarea class="form-control form-control-sm" name="company_address" id="tracer_company_address" rows="1"></textarea></div>
                    </div>

                    <h5 class="text-primary border-bottom pb-1 mb-3 mt-4">Previous Employment</h5>
                    <div class="row">
                        <div class="col-md-4 mb-2"><label>Previous Company</label><input type="text" class="form-control form-control-sm" name="prev_company" id="tracer_prev_company"></div>
                        <div class="col-md-4 mb-2"><label>Previous Position</label><input type="text" class="form-control form-control-sm" name="prev_position" id="tracer_prev_position"></div>
                        <div class="col-md-4 mb-2"><label>Employment Time</label><input type="text" class="form-control form-control-sm" name="employment_time" id="tracer_employment_time"></div>
                        <div class="col-md-12 mb-2"><label>Previous Address</label><textarea class="form-control form-control-sm" name="prev_address" id="tracer_prev_address" rows="1"></textarea></div>
                    </div>

                    <h5 class="text-primary border-bottom pb-1 mb-3 mt-4">Other Information</h5>
                    <div class="row">
                        <div class="col-md-12 mb-2"><label>Success Story</label><textarea class="form-control form-control-sm" name="success_story" id="tracer_success_story" rows="3"></textarea></div>
                        <div class="col-md-6 mb-2"><label>Consent</label><input type="text" class="form-control form-control-sm" name="consent" id="tracer_consent"></div>
                        <div class="col-md-6 mb-2"><label>Submitted At</label><input type="text" class="form-control form-control-sm" name="submitted_at_display" id="tracer_submitted_at" disabled></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('tracerForm').submit()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(function () {
        // DataTables Init
        ['studentsTable', 'alumniTable', 'counselingTable', 'appointmentsTable', 'incidentsTable', 'assessmentsTable', 'tracerTable', 'examResultsTable'].forEach(id => {
            if($('#'+id).length) $('#'+id).DataTable({pageLength: 10, lengthChange: false});
        });

        // Toast
        if('<?php echo $success_message; ?>') {
            showToast('<?php echo $success_message; ?>', 'success');
            var url = new URL(window.location); url.searchParams.delete('success'); window.history.replaceState({}, '', url);
        }
        function showToast(msg, type) {
            var toast = $(`<div class="toast toast-${type}"><div class="toast-message">${msg}</div></div>`);
            $('#toastContainer').append(toast);
            setTimeout(()=>toast.addClass('show'), 100);
            setTimeout(()=>{toast.removeClass('show'); setTimeout(()=>toast.remove(), 300);}, 3000);
        }
        
        window.toggleOtherReason = function(selectId, inputId) {
            $('#'+inputId).toggle($('#'+selectId).val() === 'Please specify:');
        };

        // --- Functional Cancel Button Logic ---
        window.closeAndResetForm = function(containerId, type) {
            if(type === 'student') window.openStudentForm();
            else if(type === 'alumni') window.openAlumniForm();
            else if(type === 'counseling') window.openCounselingForm();
            else if(type === 'appointment') window.openAppointmentForm();
            else if(type === 'incident') window.openIncidentForm();
            else if(type === 'assessment') window.openAssessmentForm();
            else if(type === 'exam') window.openExamForm();
        };

        // --- STUDENT ---
        window.openStudentForm = function() {
            $('#studentFormTitle').text('Add New Student');
            $('#studentFormBtn').text('Save Student');
            $('#student_form_type').val('add_student');
            $('#student_id').val('');
            $('#student_number, #full_name, #date_of_birth, #address, #contact_number, #email, #year_level, #course_or_strand, #section, #guardian_name, #guardian_contact').val('');
            $('#gender').val('Male');
            $('#enrollment_status').val('Active');
            $('#studentFormContainer').addClass('active');
        };

        window.viewStudentDetails = function(row) {
            $('#view_student_number').text(row.student_number || '');
            $('#view_full_name').text(row.full_name || '');
            $('#view_gender').text(row.gender || '');
            $('#view_date_of_birth').text(row.date_of_birth || '');
            $('#view_address').text(row.address || '');
            $('#view_contact_number').text(row.contact_number || '');
            $('#view_email').text(row.email || '');
            $('#view_year_level').text(row.year_level || '');
            $('#view_course_or_strand').text(row.course_or_strand || '');
            $('#view_section').text(row.section || '');
            $('#view_guardian_name').text(row.guardian_name || '');
            $('#view_guardian_contact').text(row.guardian_contact || '');
            $('#view_enrollment_status').text(row.enrollment_status || '');
            $('#view_created_at').text(row.created_at || '');

            new bootstrap.Modal(document.getElementById('studentDetailsModal')).show();
        };

        window.editStudent = function(row) {
            $('#studentFormTitle').text('Edit Student');
            $('#studentFormBtn').text('Update Student');
            $('#student_form_type').val('update_student');
            $('#student_id').val(row.student_id);
            $('#student_number').val(row.student_number);
            $('#full_name').val(row.full_name);
            $('#gender').val(row.gender);
            $('#date_of_birth').val(row.date_of_birth || '');
            $('#year_level').val(row.year_level);
            $('#course_or_strand').val(row.course_or_strand);
            $('#section').val(row.section);
            $('#enrollment_status').val(row.enrollment_status);
            $('#address').val(row.address || '');
            $('#contact_number').val(row.contact_number || '');
            $('#email').val(row.email || '');
            $('#guardian_name').val(row.guardian_name || '');
            $('#guardian_contact').val(row.guardian_contact || '');
            $('#studentFormContainer').addClass('active');
        };

        // --- ENTRANCE EXAM RESULTS (NEW) ---
        window.openExamForm = function() {
            $('#examFormTitle').text('Add Exam Result');
            $('#examFormBtn').text('Save Result');
            $('#exam_form_type').val('add_exam');
            $('#exam_id').val('');
            $('#exam_department, #exam_student_name, #exam_std_raw, #exam_std_percentile, #exam_std_verbal, #exam_tmt_raw, #exam_tmt_interpretation').val('');
            $('#examFormContainer').addClass('active');
        };
        window.editExam = function(row) {
            $('#examFormTitle').text('Edit Exam Result');
            $('#examFormBtn').text('Update Result');
            $('#exam_form_type').val('update_exam');
            $('#exam_id').val(row.exam_id);
            $('#exam_department').val(row.department_name);
            $('#exam_student_name').val(row.student_name);
            $('#exam_std_raw').val(row.std_raw_score);
            $('#exam_std_percentile').val(row.std_percentile_rank);
            $('#exam_std_verbal').val(row.std_verbal_desc);
            $('#exam_tmt_raw').val(row.tmt_raw_score);
            $('#exam_tmt_interpretation').val(row.tmt_interpretation);
            $('#examFormContainer').addClass('active');
        };

        // --- ALUMNI ---
        window.openAlumniForm = function() {
            $('#alumniFormTitle').text('Add New Alumni');
            $('#alumniFormBtn').text('Save Alumni');
            $('#alumni_form_type').val('add_alumni');
            $('#alumni_id').val('');
            $('#alumni_student_number, #alumni_full_name, #alumni_year_graduated, #alumni_degree_earned, #alumni_salary').val('');
            $('#alumni_gender').val('Male');
            $('#alumni_employment_status').val('Employed');
            $('#alumniFormContainer').addClass('active');
        };
        window.editAlumni = function(row) {
            $('#alumniFormTitle').text('Edit Alumni');
            $('#alumniFormBtn').text('Update Alumni');
            $('#alumni_form_type').val('update_alumni');
            $('#alumni_id').val(row.alumni_id);
            $('#alumni_student_number').val(row.student_number);
            $('#alumni_full_name').val(row.full_name);
            $('#alumni_gender').val(row.gender);
            $('#alumni_year_graduated').val(row.year_graduated);
            $('#alumni_degree_earned').val(row.degree_earned);
            $('#alumni_employment_status').val(row.employment_status);
            $('#alumni_salary').val(row.salary);
            $('#alumni_date_of_birth').val(row.date_of_birth || '');
            $('#alumni_address').val(row.address || '');
            $('#alumni_contact_number').val(row.contact_number || '');
            $('#alumni_email').val(row.email || '');
            $('#alumni_graduation_date').val(row.graduation_date || '');
            $('#alumniFormContainer').addClass('active');
        };

        // --- TRACER EDIT ---
        window.editTracer = function(row) {
            $('#tracer_id').val(row.id);
            $('#tracer_email').val(row.email);
            $('#tracer_family_name').val(row.family_name);
            $('#tracer_first_name').val(row.first_name);
            $('#tracer_middle_name').val(row.middle_name);
            $('#tracer_year_graduated').val(row.year_graduated);
            $('#tracer_gender').val(row.gender);
            $('#tracer_birthday').val(row.birthday);
            $('#tracer_civil_status').val(row.civil_status);
            $('#tracer_spouse_name').val(row.spouse_name);
            $('#tracer_children_count').val(row.children_count);
            $('#tracer_address').val(row.address);
            $('#tracer_contact').val(row.contact);
            $('#tracer_programs').val(row.programs);
            $('#tracer_post_grad').val(row.post_grad);
            $('#tracer_honors').val(row.honors);
            $('#tracer_board_exam').val(row.board_exam);
            $('#tracer_other_schools').val(row.other_schools);
            $('#tracer_occupation').val(row.occupation);
            $('#tracer_company').val(row.company);
            $('#tracer_position').val(row.position);
            $('#tracer_company_address').val(row.company_address);
            $('#tracer_employment_date').val(row.employment_date);
            $('#tracer_salary').val(row.salary);
            $('#tracer_prev_company').val(row.prev_company);
            $('#tracer_prev_position').val(row.prev_position);
            $('#tracer_prev_address').val(row.prev_address);
            $('#tracer_employment_time').val(row.employment_time);
            $('#tracer_success_story').val(row.success_story);
            $('#tracer_consent').val(row.consent);
            $('#tracer_submitted_at').val(row.submitted_at);

            new bootstrap.Modal(document.getElementById('tracerModal')).show();
        };

        // --- COUNSELING ---
        window.openCounselingForm = function() {
            $('#counselingFormTitle').text('Add New Session');
            $('#counselingFormBtn').text('Save Session');
            $('#counseling_form_type').val('add_counseling');
            $('#counseling_session_id').val('');
            $('#counseling_student_id, #counseling_counselor_id, #counseling_session_date, #counseling_counseling_type, #counseling_reason, #counseling_referred_by, #counseling_follow_up_date').val('');
            $('#counseling_session_status').val('Ongoing');
            $('#counseling_session_notes, #counseling_recommendations').val('');
            $('#counseling_reason_other').hide().val('');
            $('#counselingFormContainer').addClass('active');
        };
        window.editCounseling = function(row) {
            $('#counselingFormTitle').text('Edit Counseling Session');
            $('#counselingFormBtn').text('Update Session');
            $('#counseling_form_type').val('update_counseling');
            $('#counseling_session_id').val(row.session_id);
            $('#counseling_student_id').val(row.student_id);
            $('#counseling_counselor_id').val(row.counselor_id);
            $('#counseling_session_date').val(row.session_date);
            $('#counseling_counseling_type').val(row.counseling_type);
            $('#counseling_session_status').val(row.session_status);
            $('#counseling_referred_by').val(row.referred_by || '');
            $('#counseling_follow_up_date').val(row.follow_up_date || '');
            $('#counseling_session_notes').val(row.session_notes || '');
            $('#counseling_recommendations').val(row.recommendations || '');
            
            var reason = row.reason;
            var select = $('#counseling_reason');
            if(select.find("option[value='"+reason+"']").length) {
                select.val(reason);
                $('#counseling_reason_other').hide();
            } else {
                select.val('Please specify:');
                $('#counseling_reason_other').show().val(reason);
            }
            $('#counselingFormContainer').addClass('active');
        };

        // --- APPOINTMENTS ---
        window.openAppointmentForm = function() {
            $('#appointmentFormTitle').text('Add New Appointment');
            $('#appointmentFormBtn').text('Save Appointment');
            $('#appointment_form_type').val('add_appointment');
            $('#appointment_appointment_id').val('');
            $('#appointment_student_id, #appointment_counselor_id, #appointment_appointment_datetime').val('');
            $('#appointment_status').val('Pending');
            $('#appointment_purpose').val('');
            $('#appointmentFormContainer').addClass('active');
        };
        window.editAppointment = function(row) {
            $('#appointmentFormTitle').text('Edit Appointment');
            $('#appointmentFormBtn').text('Update Appointment');
            $('#appointment_form_type').val('update_appointment');
            $('#appointment_appointment_id').val(row.appointment_id);
            $('#appointment_student_id').val(row.student_id);
            $('#appointment_counselor_id').val(row.counselor_id);
            $('#appointment_appointment_datetime').val(row.appointment_datetime);
            $('#appointment_status').val(row.status);
            $('#appointment_purpose').val(row.purpose);
            $('#appointmentFormContainer').addClass('active');
        };

        // --- INCIDENTS ---
        window.openIncidentForm = function() {
            $('#incidentFormTitle').text('Add New Incident');
            $('#incidentFormBtn').text('Save Incident');
            $('#incident_form_type').val('add_incident');
            $('#incident_incident_id').val('');
            $('#incident_student_id, #incident_incident_date, #incident_incident_type').val('');
            $('#incident_resolution_status').val('Pending');
            $('#incident_description, #incident_action_taken, #incident_counselor_remarks').val('');
            $('#incidentFormContainer').addClass('active');
        };
        window.editIncident = function(row) {
            $('#incidentFormTitle').text('Edit Incident');
            $('#incidentFormBtn').text('Update Incident');
            $('#incident_form_type').val('update_incident');
            $('#incident_incident_id').val(row.incident_id);
            $('#incident_student_id').val(row.student_id);
            $('#incident_incident_date').val(row.incident_date);
            $('#incident_incident_type').val(row.incident_type);
            $('#incident_resolution_status').val(row.resolution_status);
            $('#incident_description').val(row.description);
            $('#incident_action_taken').val(row.action_taken || '');
            $('#incident_counselor_remarks').val(row.counselor_remarks || '');
            $('#incidentFormContainer').addClass('active');
        };

        // --- ASSESSMENTS ---
        window.openAssessmentForm = function() {
            $('#assessmentFormTitle').text('Add New Assessment');
            $('#assessmentFormBtn').text('Save Assessment');
            $('#assessment_form_type').val('add_assessment');
            $('#assessment_assessment_id').val('');
            $('#assessment_student_id, #assessment_assessment_type, #assessment_assessment_date').val('');
            $('#assessment_result').val('');
            $('#assessment_interpretation').val('');
            $('#assessment_recommendations').val('');
            $('#assessmentFormContainer').addClass('active');
        };
        window.editAssessment = function(row) {
            $('#assessmentFormTitle').text('Edit Assessment');
            $('#assessmentFormBtn').text('Update Assessment');
            $('#assessment_form_type').val('update_assessment');
            $('#assessment_assessment_id').val(row.assessment_id);
            $('#assessment_student_id').val(row.student_id);
            $('#assessment_assessment_type').val(row.assessment_type);
            $('#assessment_assessment_date').val(row.assessment_date);
            $('#assessment_result').val(row.result);
            $('#assessment_interpretation').val(row.interpretation || '');
            $('#assessment_recommendations').val(row.recommendations || '');
            $('#assessmentFormContainer').addClass('active');
        };

        // Tab Handling
        $('.nav-sidebar a').on('click', function(e) {
            e.preventDefault();
            var tab = $(this).attr('href').substring(1);
            var url = new URL(window.location); url.searchParams.set('tab', tab); window.history.replaceState({}, '', url);
            $('.tab-pane').removeClass('active'); $('#'+tab).addClass('active');
            $('.nav-sidebar .nav-item').removeClass('active'); $(this).closest('.nav-item').addClass('active');
            
            if(tab === 'students') openStudentForm();
            else if(tab === 'alumni') openAlumniForm();
            else if(tab === 'entrance_exams') openExamForm();
            else if(tab === 'counseling') openCounselingForm();
            else if(tab === 'appointments') openAppointmentForm();
            else if(tab === 'incidents') openIncidentForm();
            else if(tab === 'assessments') openAssessmentForm();
        });
        
        // Activate initial tab
        var urlParams = new URLSearchParams(window.location.search);
        var tab = urlParams.get('tab') || 'dashboard';
        $('.tab-pane').removeClass('active'); $('#'+tab).addClass('active');
        $('.nav-sidebar a[href="#'+tab+'"]').closest('.nav-item').addClass('active');
        
        // Initial Auto-Open
        if(tab === 'students') openStudentForm();
        else if(tab === 'alumni') openAlumniForm();
        else if(tab === 'entrance_exams') openExamForm();
        else if(tab === 'counseling') openCounselingForm();
        else if(tab === 'appointments') openAppointmentForm();
        else if(tab === 'incidents') openIncidentForm();
        else if(tab === 'assessments') openAssessmentForm();

        window.confirmLogout = function() { if(confirm("Are you sure you want to logout?")) document.getElementById('logoutForm').submit(); };
    });
</script>
</body>
</html>