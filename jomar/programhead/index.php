<?php
session_start();
require_once '../config.php';

// Handle storing active tab via AJAX
if (isset($_GET['store_active_tab']) && isset($_GET['active_tab'])) {
    $_SESSION['active_tab'] = $_GET['active_tab'];
    exit();
}

// Get active tab from session and unset it
 $activeTab = 'dashboard';
if (isset($_SESSION['active_tab'])) {
    $activeTab = $_SESSION['active_tab'];
    unset($_SESSION['active_tab']);
}

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

// Get department ID
 $department_id = $program_head['department_id'];

// Get student counts per year level
 $year_level_counts_query = "SELECT e.year_level, COUNT(*) as count 
                          FROM enrollments e
                          JOIN courses c ON e.course_id = c.id
                          WHERE c.department_id = ? AND e.status IN ('Enrolled', 'Registered')
                          GROUP BY e.year_level
                          ORDER BY e.year_level";
 $stmt = $conn->prepare($year_level_counts_query);
 $stmt->bind_param("i", $department_id);
 $stmt->execute();
 $year_level_counts_result = $stmt->get_result();
 $year_level_counts = [];
while ($row = $year_level_counts_result->fetch_assoc()) {
    $year_level_counts[$row['year_level']] = $row['count'];
}

// Get total students
 $total_students_query = "SELECT COUNT(*) as count 
                       FROM enrollments e
                       JOIN courses c ON e.course_id = c.id
                       WHERE c.department_id = ? AND e.status IN ('Enrolled', 'Registered')";
 $stmt = $conn->prepare($total_students_query);
 $stmt->bind_param("i", $department_id);
 $stmt->execute();
 $total_students_result = $stmt->get_result();
 $total_students = $total_students_result->fetch_assoc()['count'];

// Get total courses
 $total_courses_query = "SELECT COUNT(*) as count FROM courses WHERE department_id = ?";
 $stmt = $conn->prepare($total_courses_query);
 $stmt->bind_param("i", $department_id);
 $stmt->execute();
 $total_courses_result = $stmt->get_result();
 $total_courses = $total_courses_result->fetch_assoc()['count'];

// Get total subjects
 $total_subjects_query = "SELECT COUNT(*) as count 
                       FROM subjects s
                       JOIN courses c ON s.course_id = c.id
                       WHERE c.department_id = ?";
 $stmt = $conn->prepare($total_subjects_query);
 $stmt->bind_param("i", $department_id);
 $stmt->execute();
 $total_subjects_result = $stmt->get_result();
 $total_subjects = $total_subjects_result->fetch_assoc()['count'];

// Get unique year levels for filters
 $year_levels_query = "SELECT DISTINCT e.year_level 
                     FROM enrollments e
                     JOIN courses c ON e.course_id = c.id
                     WHERE c.department_id = ? AND e.status IN ('Enrolled', 'Registered')
                     ORDER BY e.year_level";
 $stmt = $conn->prepare($year_levels_query);
 $stmt->bind_param("i", $department_id);
 $stmt->execute();
 $year_levels_result = $stmt->get_result();
 $year_levels = [];
while ($row = $year_levels_result->fetch_assoc()) {
    $year_levels[] = $row['year_level'];
}

// Get unique sections for filters
 $sections_query = "SELECT DISTINCT e.section 
                  FROM enrollments e
                  JOIN courses c ON e.course_id = c.id
                  WHERE c.department_id = ? AND e.status IN ('Enrolled', 'Registered') AND e.section IS NOT NULL AND e.section != ''
                  ORDER BY e.section";
 $stmt = $conn->prepare($sections_query);
 $stmt->bind_param("i", $department_id);
 $stmt->execute();
 $sections_result = $stmt->get_result();
 $sections = [];
while ($row = $sections_result->fetch_assoc()) {
    $sections[] = $row['section'];
}

// --- NEW: Get enrolled students ---
 $students_query = "SELECT 
                    e.id as enrollment_id,
                    s.id, 
                    s.id_number, 
                    s.gender,
                    CONCAT(s.last_name, ', ', s.first_name, ' ', IFNULL(s.middle_name, '')) as student_name,
                    e.year_level, 
                    e.academic_year, 
                    e.semester, 
                    e.status,
                    e.section,
                    c.coursename
                  FROM enrollments e
                  JOIN students s ON e.student_id = s.id
                  JOIN courses c ON e.course_id = c.id
                  WHERE c.department_id = ? AND e.status IN ('Enrolled', 'Registered')
                  ORDER BY s.last_name, s.first_name";
 $stmt = $conn->prepare($students_query);
 $stmt->bind_param("i", $department_id);
 $stmt->execute();
 $students_result = $stmt->get_result();
 $enrolled_students = $students_result->fetch_all(MYSQLI_ASSOC);
// --- End of new code ---

// Handle AJAX requests
if (isset($_GET['action'])) {
    // Set content type header for all AJAX responses
    header('Content-Type: application/json');
    
    $action = $_GET['action'];
    switch ($action) {
        // Subject Assignment actions
        case 'get_subject_assignments':
            $query = "SELECT ta.*, IFNULL(t.name, 'Unassigned') as teacher_name, s.subject_code, s.subject_description, c.coursename, c.courselevel 
                      FROM teacherassignments ta 
                      LEFT JOIN teachers t ON ta.teacher_id = t.id 
                      JOIN subjects s ON ta.subject_id = s.id 
                      JOIN courses c ON ta.course_id = c.id 
                      WHERE c.department_id = ? 
                      ORDER BY ta.academic_year DESC, ta.semester DESC, c.coursename, s.subject_code";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $department_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $assignments = [];
            while ($row = $result->fetch_assoc()) {
                $assignments[] = $row;
            }
            echo json_encode($assignments);
            exit();
            
        case 'add_assignment':
            $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : null;
            $subject_id = $_POST['subject_id'];
            $course_id = $_POST['course_id'];
            $academic_year = $_POST['academic_year'];
            $semester = $_POST['semester'];
            $section = $_POST['section'];
            $hours = $_POST['hours'];
            $notes = $_POST['notes'];
            
            $query = "INSERT INTO teacherassignments (teacher_id, subject_id, course_id, academic_year, semester, section, hours, notes) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iiisssis", $teacher_id, $subject_id, $course_id, $academic_year, $semester, $section, $hours, $notes);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Assignment added successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error adding assignment: ' . $conn->error]);
            }
            exit();
            
        case 'update_assignment':
            $id = $_POST['assignment_id'];
            $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : null;
            $subject_id = $_POST['subject_id'];
            $course_id = $_POST['course_id'];
            $academic_year = $_POST['academic_year'];
            $semester = $_POST['semester'];
            $section = $_POST['section'];
            $hours = $_POST['hours'];
            $notes = $_POST['notes'];
            
            $query = "UPDATE teacherassignments SET teacher_id = ?, subject_id = ?, course_id = ?, academic_year = ?, semester = ?, section = ?, hours = ?, notes = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iiisssisi", $teacher_id, $subject_id, $course_id, $academic_year, $semester, $section, $hours, $notes, $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Assignment updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating assignment: ' . $conn->error]);
            }
            exit();
            
        case 'delete_assignment':
            $id = $_POST['assignment_id'];
            
            $query = "DELETE FROM teacherassignments WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Assignment deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error deleting assignment: ' . $conn->error]);
            }
            exit();
            
        case 'get_teachers':
            $query = "SELECT id, name FROM teachers ORDER BY name";
            $result = $conn->query($query);
            $teachers = [];
            while ($row = $result->fetch_assoc()) {
                $teachers[] = $row;
            }
            echo json_encode($teachers);
            exit();
            
        case 'get_subjects_by_course':
            $course_id = $_POST['course_id'];
            $query = "SELECT id, subject_code, subject_description FROM subjects WHERE course_id = ? ORDER BY subject_code";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $course_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $subjects = [];
            while ($row = $result->fetch_assoc()) {
                $subjects[] = $row;
            }
            echo json_encode($subjects);
            exit();
            
        case 'get_courses_by_department':
            $query = "SELECT id, coursename, courselevel FROM courses WHERE department_id = ? ORDER BY coursename";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $department_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $courses = [];
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
            echo json_encode($courses);
            exit();
            
        case 'get_academic_years':
            $query = "SELECT academic_year FROM academic_years ORDER BY academic_year DESC";
            $result = $conn->query($query);
            $years = [];
            while ($row = $result->fetch_assoc()) {
                $years[] = $row['academic_year'];
            }
            echo json_encode($years);
            exit();
            
        // NEW: Update student section
        case 'update_student_section':
            $enrollment_id = $_POST['enrollment_id'];
            $section = $_POST['section'];
            
            $query = "UPDATE enrollments SET section = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $section, $enrollment_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Section updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error updating section: ' . $conn->error]);
            }
            exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Program Head Portal</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <style>
        .brand-link {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }
        .brand-link img {
            max-height: 35px;
            margin-right: 10px;
        }
        .info-box {
            min-height: 100px;
        }
        .info-box .info-box-icon {
            border-radius: 0.375rem;
            font-size: 1.875rem;
        }
        .info-box .info-box-text {
            font-size: 1.25rem;
            font-weight: bold;
        }
        .small-box {
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .toast {
            opacity: 0 !important;
        }
        .toast.show {
            opacity: 0.9 !important;
        }
        .toast-success {
            background-color: #007bff;
            color: white;
        }
        .toast-error {
            background-color: #dc3545;
            color: white;
        }
        .content-section {
            display: none;
        }
        .content-section.active {
            display: block;
        }
        .nav-pills .nav-link.active {
            background-color: #007bff;
        }
        .card-header {
            font-weight: bold;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .no-data-message {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #6c757d;
        }
        /* AdminLTE Customizations */
        .nav-sidebar .nav-link>p {
            display: inline-block;
            margin: 0;
        }
        .nav-sidebar .nav-treeview .nav-link>p {
            font-size: 0.9rem;
        }
        .user-panel .info {
            display: block;
            padding: 5px 5px 5px 15px;
            font-size: 14px;
            position: static;
        }
        .user-panel .info>a {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }
        .user-panel .info>a:hover, .user-panel .info>a:active, .user-panel .info>a:focus {
            text-decoration: none;
        }
        .content-header h1 {
            font-size: 1.8rem;
            margin: 0;
        }
        .card {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            margin-bottom: 1rem;
        }
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0,0,0,.125);
            padding: .75rem 1.25rem;
            position: relative;
            border-top-left-radius: .25rem;
            border-top-right-radius: .25rem;
        }
        .card-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 400;
        }
        .btn-tool {
            background: transparent;
            color: #adb5bd;
            font-size: 1rem;
            margin: -.75rem 0;
            padding: .25rem .5rem;
        }
        .info-box {
            border-radius: .25rem;
            background: #fff;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            display: flex;
            margin-bottom: 1rem;
            min-height: 80px;
            padding: .5rem;
            position: relative;
            width: 100%;
        }
        .info-box .info-box-icon {
            border-radius: .25rem;
            display: block;
            font-size: 1.875rem;
            height: 60px;
            line-height: 60px;
            text-align: center;
            width: 60px;
        }
        .info-box .info-box-content {
            flex: 1;
            padding: 5px 10px;
        }
        .info-box .info-box-number {
            display: block;
            font-weight: 700;
            font-size: 1.25rem;
        }
        .small-box {
            border-radius: .25rem;
            display: block;
            margin-bottom: 20px;
            position: relative;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }
        .small-box>.inner {
            padding: 15px;
        }
        .small-box>.inner h3 {
            font-size: 2.1rem;
            font-weight: bold;
            margin: 0 0 10px 0;
            white-space: nowrap;
            padding: 0;
        }
        .small-box>.inner p {
            font-size: 1rem;
            margin: 0;
        }
        .small-box .icon {
            color: rgba(0,0,0,.15);
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 0;
            font-size: 70px;
        }
        .small-box>.small-box-footer {
            background: rgba(0,0,0,.1);
            color: rgba(255,255,255,.8);
            display: block;
            padding: 3px 0;
            position: relative;
            text-align: center;
            text-decoration: none;
            z-index: 10;
        }
        .small-box>.small-box-footer:hover {
            background: rgba(0,0,0,.15);
            color: #fff;
        }
        .table-responsive {
            border-radius: .25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }
        .table {
            margin-bottom: 0;
        }
        .table thead th {
            border-bottom: 2px solid #dee2e6;
        }
        .badge {
            font-size: 85%;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 .2rem rgba(0,123,255,.25);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
            color: #212529;
        }
        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        /* Enhanced table styling */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.075);
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        /* Modal styles */
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        .modal-title {
            font-weight: bold;
        }
        .modal-footer {
            background-color: #f8f9fa;
        }
        .btn-close {
            background: transparent;
            border: none;
            color: white;
            font-size: 1.5rem;
        }
        .btn-close:hover {
            color: #f8f9fa;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        /* Assignment styles */
        .assignment-actions {
            display: flex;
            gap: 5px;
        }
        .assignment-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .no-assignments {
            text-align: center;
            padding: 30px;
            color: #6c757d;
            font-style: italic;
        }
        /* Dashboard styles */
        .dashboard-stats {
            margin-bottom: 20px;
        }
        .year-level-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            padding: 15px;
        }
        .year-level-stat {
            flex: 1;
            min-width: 120px;
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .year-level-stat h4 {
            margin: 0 0 10px 0;
            font-size: 1rem;
            color: #6c757d;
        }
        .year-level-stat .count {
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
        }
        /* Search box styling */
        .search-container {
            margin-bottom: 15px;
            position: relative;
        }
        .search-container .form-control {
            border-radius: 20px;
            padding-left: 40px;
        }
        .search-container .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 10;
        }
        .search-container .clear-search {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            cursor: pointer;
            z-index: 10;
            display: none;
        }
        .search-container.search-active .clear-search {
            display: block;
        }
        .dataTables_wrapper .dataTables_filter {
            display: none;
        }
        .dataTables_wrapper .dataTables_length {
            margin-bottom: 10px;
        }
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 10px;
        }
        /* Empty state styling */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .empty-state p {
            margin-bottom: 20px;
        }
        /* Loading state */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* Custom blue theme */
        .sidebar-dark-primary {
            background-color: #003366 !important;
        }
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: #007bff;
            color: #fff;
        }
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .main-header {
            border-bottom: 1px solid #007bff;
        }
        .main-header .navbar-nav .nav-link {
            color: #007bff;
        }
        .main-header .navbar-nav .nav-link:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }
        .brand-link {
            background-color: #003366 !important;
        }
        /* Notes field styling */
        .notes-display {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .notes-display:hover {
            white-space: normal;
            word-wrap: break-word;
        }
        /* Student section styling */
        .section-cell {
            position: relative;
        }
        .section-input {
            width: 100px;
            padding: 3px 5px;
            font-size: 0.875rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        .section-actions {
            display: flex;
            gap: 5px;
        }
        .section-actions .btn {
            padding: 0.125rem 0.25rem;
            font-size: 0.75rem;
        }
        .section-display {
            cursor: pointer;
            padding: 3px 5px;
            border-radius: 0.25rem;
            transition: background-color 0.2s;
        }
        .section-display:hover {
            background-color: #f8f9fa;
        }
        .section-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            background-color: #007bff;
            color: #fff;
        }
        /* Red accents for important elements */
        .bg-red {
            background-color: #dc3545 !important;
        }
        .text-red {
            color: #dc3545 !important;
        }
        .btn-red {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        .btn-red:hover {
            background-color: #c82333;
            border-color: #bd2130;
            color: white;
        }
        .badge-red {
            background-color: #dc3545;
            color: white;
        }
        /* Filter styling */
        .filter-container {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        .filter-container .form-group {
            margin-bottom: 0;
            flex: 1;
            min-width: 150px;
        }
        .filter-container .btn {
            height: 38px;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }
        /* DataTables customization */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.5rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #e9ecef;
            border: 1px solid #dee2e6;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background-color: #0069d9;
            color: white;
            border: 1px solid #0069d9;
        }
        .dataTables_wrapper .dataTables_info {
            margin-top: 10px;
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
                <a href="#" class="nav-link">Home</a>
            </li>
        </ul>
    </nav>
    
    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="#" class="brand-link">
            <img src="../uploads/csr.png" alt="CSR Logo">
            <span class="brand-text font-weight-light">Program Head</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="../uploads/programhead.png" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?= htmlspecialchars($program_head['name']) ?></a>
                    <a href="#" class="d-block"><?= htmlspecialchars($program_head['dept_name']) ?></a>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="#" class="nav-link <?= $activeTab == 'dashboard' ? 'active' : '' ?>" data-section="dashboard">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link <?= $activeTab == 'subject-assignment' ? 'active' : '' ?>" data-section="subject-assignment">
                            <i class="nav-icon fas fa-chalkboard-teacher"></i>
                            <p>Subject Assignment</p>
                        </a>
                    </li>
                    <!-- NEW: Students Tab -->
                    <li class="nav-item">
                        <a href="#" class="nav-link <?= $activeTab == 'students' ? 'active' : '' ?>" data-section="students">
                            <i class="nav-icon fas fa-user-graduate"></i>
                            <p>Students</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link <?= $activeTab == 'reports' ? 'active' : '' ?>" data-section="reports">
                            <i class="nav-icon fas fa-print"></i>
                            <p>Reports</p>
                        </a>
                    </li>
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
                        <h1 class="m-0">Program Head Portal</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active"><?= ucfirst(str_replace('-', ' ', $activeTab)) ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <!-- Toast Container -->
                <div class="toast-container"></div>
                
                <!-- Dashboard Section -->
                <div id="dashboard" class="content-section <?= $activeTab == 'dashboard' ? 'active' : '' ?>">
                    <!-- Dashboard Stats -->
                    <div class="row dashboard-stats">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3><?= $total_students ?></h3>
                                    <p>Total Students</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <a href="#" class="small-box-footer" data-section="students">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3><?= $total_courses ?></h3>
                                    <p>Total Courses</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <a href="#" class="small-box-footer" data-section="subject-assignment">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3><?= $total_subjects ?></h3>
                                    <p>Total Subjects</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <a href="#" class="small-box-footer" data-section="subject-assignment">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-red">
                                <div class="inner">
                                    <h3><?= count($year_level_counts) ?></h3>
                                    <p>Year Levels</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-layer-group"></i>
                                </div>
                                <a href="#" class="small-box-footer" data-section="students">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>

                    
                    <!-- Students per Year Level -->
                    <div class="card">
                        <div class="card-header border-transparent">
                            <h3 class="card-title">Students per Year Level</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (!empty($year_level_counts)): ?>
                                <div class="year-level-stats">
                                    <?php foreach ($year_level_counts as $year_level => $count): ?>
                                        <div class="year-level-stat">
                                            <h4><?= htmlspecialchars($year_level) ?> Year</h4>
                                            <div class="count"><?= $count ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <h3>No Student Data</h3>
                                    <p>There are currently no enrolled students in your department.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Subject Assignment Section -->
                <div id="subject-assignment" class="content-section <?= $activeTab == 'subject-assignment' ? 'active' : '' ?>">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="card-title">Subject Assignment</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#assignmentModal">
                                    <i class="fas fa-plus"></i> New Assignment
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Search Box -->
                            <div class="search-container" id="searchContainer">
                                <span class="search-icon"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="assignmentSearch" placeholder="Search assignments...">
                                <span class="clear-search" id="clearSearch"><i class="fas fa-times"></i></span>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="assignmentsTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Teacher</th>
                                            <th>Subject</th>
                                            <th>Course</th>
                                            <th>Section</th>
                                            <th>Hours</th>
                                            <th>Academic Year</th>
                                            <th>Semester</th>
                                            <th>Notes</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Assignments will be loaded here -->
                                        <tr>
                                            <td colspan="9" class="text-center no-assignments">
                                                <i class="fas fa-spinner fa-spin"></i> Loading assignments...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NEW: Students Section -->
                <div id="students" class="content-section <?= $activeTab == 'students' ? 'active' : '' ?>">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h3 class="card-title">Enrolled Students</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filters for Students -->
                            <div class="filter-container">
                                <div class="form-group">
                                    <label for="yearLevelFilter" class="form-label">Year Level</label>
                                    <select class="form-control" id="yearLevelFilter">
                                        <option value="">All Year Levels</option>
                                        <?php foreach ($year_levels as $year_level): ?>
                                            <option value="<?= htmlspecialchars($year_level) ?>"><?= htmlspecialchars($year_level) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="studentsSearch" class="form-label">Search</label>
                                    <input type="text" class="form-control" id="studentsSearch" placeholder="Search students...">
                                </div>
                                <button type="button" class="btn btn-primary" id="applyFilters">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                                <button type="button" class="btn btn-secondary" id="resetFilters">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                            
                            <?php if (!empty($enrolled_students)): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover" id="studentsTable">
                                        <thead>
                                            <tr>
                                                <th>ID Number</th>
                                                <th>Name</th>
                                                <th>Gender</th>
                                                <th>Year Level</th>
                                                <th>Semester</th>
                                                <th>Section</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($enrolled_students as $student): ?>
                                                <tr data-enrollment-id="<?= $student['enrollment_id'] ?>"
                                                    data-year-level="<?= htmlspecialchars($student['year_level']) ?>"
                                                    data-section="<?= htmlspecialchars($student['section']) ?>">
                                                    <td><?= htmlspecialchars($student['id_number']) ?></td>
                                                    <td><?= htmlspecialchars($student['student_name']) ?></td>
                                                    <td><?= htmlspecialchars($student['gender']) ?></td>
                                                    <td><?= htmlspecialchars($student['year_level']) ?></td>
                                                    <td><?= htmlspecialchars($student['semester']) ?></td>
                                                    <td class="section-cell">
                                                        <div class="section-display">
                                                            <?php if (!empty($student['section'])): ?>
                                                                <span class="section-badge"><?= htmlspecialchars($student['section']) ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">Not Assigned</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="section-edit" style="display: none;">
                                                            <div class="input-group input-group-sm">
                                                                <input type="text" class="form-control section-input" value="<?= htmlspecialchars($student['section']) ?>" placeholder="e.g., A, B, C">
                                                                <div class="input-group-append">
                                                                    <button class="btn btn-primary save-section" type="button"><i class="fas fa-check"></i></button>
                                                                    <button class="btn btn-secondary cancel-section" type="button"><i class="fas fa-times"></i></button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><span class="badge badge-primary"><?= htmlspecialchars($student['status']) ?></span></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-user-graduate"></i>
                                    <h3>No Enrolled Students</h3>
                                    <p>There are currently no enrolled students in your department.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
    <!-- Reports Section -->
<div id="reports" class="content-section <?= $activeTab == 'reports' ? 'active' : '' ?>">
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">Reports</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if ($total_students > 0): ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-primary">
                            <span class="info-box-icon"><i class="fas fa-list-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Student Masterlist</span>
                                <span class="info-box-number">Print by Year Level</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 70%"></div>
                                </div>
                                <span class="progress-description">
                                    Generate and print student masterlist filtered by year level
                                </span>
                                <div class="mt-3">
                                    <a href="masterlist.php" target="_blank" class="btn btn-primary">
                                        <i class="fas fa-print"></i> Print Masterlist
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Teacher Assignments</span>
                                <span class="info-box-number">Print by Filters</span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 70%"></div>
                                </div>
                                <span class="progress-description">
                                    Generate and print teacher assignments filtered by various criteria
                                </span>
                                <div class="mt-3">
                                    <a href="printassignments.php" target="_blank" class="btn btn-red">
                                        <i class="fas fa-print"></i> Print Assignments
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-file-alt"></i>
                    <h3>No Reports Available</h3>
                    <p>There are currently no students enrolled in your department, so no reports are available at this time.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
    
    
</div>
<!-- Assignment Modal -->
<div class="modal fade" id="assignmentModal" tabindex="-1" aria-labelledby="assignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="assignmentModalLabel">Create New Assignment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="assignmentForm">
                    <input type="hidden" name="assignment_id" id="assignment_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="teacher_id" class="form-label">Teacher (Optional)</label>
                                <select class="form-control" id="teacher_id" name="teacher_id">
                                    <option value="">Unassigned</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="course_id" class="form-label">Course</label>
                                <select class="form-control" id="course_id" name="course_id" required>
                                    <option value="">Select Course</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subject_id" class="form-label">Subject</label>
                                <select class="form-control" id="subject_id" name="subject_id" required>
                                    <option value="">Select Subject</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="section" class="form-label">Section</label>
                                <input type="text" class="form-control" id="section" name="section" placeholder="e.g., A, B, C">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="academic_year" class="form-label">Academic Year</label>
                                <select class="form-control" id="academic_year" name="academic_year" required>
                                    <option value="">Select Year</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="semester" class="form-label">Semester</label>
                                <select class="form-control" id="semester" name="semester" required>
                                    <option value="">Select Semester</option>
                                    <option value="1st">1st Semester</option>
                                    <option value="2nd">2nd Semester</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="hours" class="form-label">Hours</label>
                                <input type="number" class="form-control" id="hours" name="hours" min="1" max="99" step="0.5" placeholder="e.g., 3">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="1" placeholder="Additional notes..."></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelAssignmentBtn">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitAssignmentBtn">Save Assignment</button>
            </div>
        </div>
    </div>
</div>
<!-- Required Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script>
 $(function() {
    // Check for active tab in session and set it
    var activeTab = "<?= $activeTab ?>";
    
    // Navigation between sections
    $('.nav-link[data-section]').on('click', function(e) {
        e.preventDefault();
        
        var section = $(this).data('section');
        
        // Update active nav link
        $('.nav-link[data-section]').removeClass('active');
        $(this).addClass('active');
        
        // Show selected section
        $('.content-section').removeClass('active');
        $('#' + section).addClass('active');
        
        // Update breadcrumb
        var sectionName = $(this).find('p').text();
        $('.breadcrumb li:last-child').text(sectionName);
        
        // Store active tab in session via AJAX
        $.ajax({
            url: 'index.php',
            type: 'GET',
            data: {
                store_active_tab: 1,
                active_tab: section
            }
        });
        
        // Load assignments if switching to assignment tab
        if (section === 'subject-assignment') {
            loadAssignments();
        }
        
        // Initialize students table if switching to students tab
        if (section === 'students' && !studentsTable) {
            initStudentsTable();
        }
    });
    
    // Handle small-box footer links
    $('.small-box-footer[data-section]').on('click', function(e) {
        e.preventDefault();
        var section = $(this).data('section');
        
        // Update active nav link
        $('.nav-link[data-section]').removeClass('active');
        $('.nav-link[data-section="' + section + '"]').addClass('active');
        
        // Show selected section
        $('.content-section').removeClass('active');
        $('#' + section).addClass('active');
        
        // Update breadcrumb
        var sectionName = $('.nav-link[data-section="' + section + '"] p').text();
        $('.breadcrumb li:last-child').text(sectionName);
        
        // Store active tab in session via AJAX
        $.ajax({
            url: 'index.php',
            type: 'GET',
            data: {
                store_active_tab: 1,
                active_tab: section
            }
        });
        
        // Load assignments if switching to assignment tab
        if (section === 'subject-assignment') {
            loadAssignments();
        }
        
        // Initialize students table if switching to students tab
        if (section === 'students' && !studentsTable) {
            initStudentsTable();
        }
    });
    
    // Show toast notification if exists
    <?php if(isset($_SESSION['message'])): ?>
        showToast("<?= $_SESSION['message'] ?>", "<?= $_SESSION['message_type'] ?? 'success' ?>");
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>
    
    // Toast notification function
    function showToast(message, type = 'success') {
        var toastContainer = $('.toast-container');
        var toastId = 'toast-' + Date.now();
        
        var toastHtml = `
            <div id="${toastId}" class="toast toast-${type}" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                <div class="toast-body">
                    ${message}
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
        `;
        
        toastContainer.append(toastHtml);
        var toastElement = $('#' + toastId);
        toastElement.toast('show');
        
        toastElement.on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }
    
    // Subject Assignment functionality
    // Load dropdown data when modal is shown
    $('#assignmentModal').on('show.bs.modal', function() {
        loadTeachers();
        loadCourses();
        loadAcademicYears();
    });
    
    // Load subjects when course is selected
    $('#course_id').on('change', function() {
        var courseId = $(this).val();
        if (courseId) {
            loadSubjectsByCourse(courseId);
        } else {
            $('#subject_id').html('<option value="">Select Subject</option>');
        }
    });
    
    // Reset modal when hidden
    $('#assignmentModal').on('hidden.bs.modal', function () {
        resetAssignmentForm();
    });
    
    // Cancel button click handler
    $('#cancelAssignmentBtn').on('click', function() {
        $('#assignmentModal').modal('hide');
    });
    
    // Reset assignment form
    function resetAssignmentForm() {
        $('#assignmentForm')[0].reset();
        $('#assignment_id').val('');
        $('#submitAssignmentBtn').text('Save Assignment');
        $('#assignmentModalLabel').text('Create New Assignment');
    }
    
    // Assignment form submission
    $('#submitAssignmentBtn').on('click', function() {
        var form = $('#assignmentForm')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        var formData = new FormData(form);
        var assignmentId = $('#assignment_id').val();
        var action = assignmentId ? 'update_assignment' : 'add_assignment';
        
        $.ajax({
            url: 'index.php?action=' + action,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#assignmentModal').modal('hide');
                    loadAssignments();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                try {
                    var response = JSON.parse(xhr.responseText);
                    showToast(response.message || 'Error processing request', 'error');
                } catch (e) {
                    showToast('Error processing request: ' + error, 'error');
                }
            }
        });
    });
    
    // Edit assignment
    $(document).on('click', '.edit-assignment', function(e) {
        e.preventDefault();
        var assignmentId = $(this).data('id');
        
        // Show loading state
        $('#assignmentModalLabel').text('Loading Assignment...');
        $('#assignmentModal').modal('show');
        
        // First, load all dropdown data
        $.when(
            loadTeachers(),
            loadCourses(),
            loadAcademicYears()
        ).then(function() {
            // After dropdowns are loaded, get the assignment data
            $.ajax({
                url: 'index.php?action=get_subject_assignments',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var assignment = data.find(a => a.id == assignmentId);
                    if (assignment) {
                        // Set form values
                        $('#assignment_id').val(assignment.id);
                        $('#teacher_id').val(assignment.teacher_id || '');
                        $('#course_id').val(assignment.course_id);
                        $('#academic_year').val(assignment.academic_year);
                        $('#semester').val(assignment.semester);
                        $('#section').val(assignment.section || '');
                        $('#hours').val(assignment.hours || '');
                        $('#notes').val(assignment.notes || '');
                        
                        // Load subjects for the selected course
                        loadSubjectsByCourse(assignment.course_id, function() {
                            $('#subject_id').val(assignment.subject_id);
                            
                            // Update modal title and button
                            $('#submitAssignmentBtn').text('Update Assignment');
                            $('#assignmentModalLabel').text('Edit Assignment');
                        });
                    } else {
                        showToast('Assignment not found', 'error');
                        $('#assignmentModal').modal('hide');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    showToast('Error loading assignment details', 'error');
                    $('#assignmentModal').modal('hide');
                }
            });
        }).fail(function() {
            showToast('Error loading dropdown data', 'error');
            $('#assignmentModal').modal('hide');
        });
    });
    
    // Delete assignment
    $(document).on('click', '.delete-assignment', function() {
        var assignmentId = $(this).data('id');
        
        if (confirm('Are you sure you want to delete this assignment?')) {
            $.ajax({
                url: 'index.php?action=delete_assignment',
                type: 'POST',
                data: { assignment_id: assignmentId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        loadAssignments();
                    } else {
                        showToast(response.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr.responseText);
                    try {
                        var response = JSON.parse(xhr.responseText);
                        showToast(response.message || 'Error deleting assignment', 'error');
                    } catch (e) {
                        showToast('Error deleting assignment: ' + error, 'error');
                    }
                }
            });
        }
    });
    
    // Student section editing functionality
    $(document).on('click', '.section-display', function() {
        var $cell = $(this).closest('.section-cell');
        var $display = $cell.find('.section-display');
        var $edit = $cell.find('.section-edit');
        var $input = $edit.find('.section-input');
        
        // Store original value
        $input.data('original-value', $input.val());
        
        // Show edit form
        $display.hide();
        $edit.show();
        $input.focus();
    });
    
    $(document).on('click', '.save-section', function() {
        var $cell = $(this).closest('.section-cell');
        var $row = $cell.closest('tr');
        var enrollmentId = $row.data('enrollment-id');
        var $display = $cell.find('.section-display');
        var $edit = $cell.find('.section-edit');
        var $input = $edit.find('.section-input');
        var section = $input.val().trim();
        
        // Save section via AJAX
        $.ajax({
            url: 'index.php?action=update_student_section',
            type: 'POST',
            data: { 
                enrollment_id: enrollmentId,
                section: section
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update display
                    if (section) {
                        $display.html('<span class="section-badge">' + htmlspecialchars(section) + '</span>');
                    } else {
                        $display.html('<span class="text-muted">Not Assigned</span>');
                    }
                    
                    // Hide edit form
                    $display.show();
                    $edit.hide();
                    
                    showToast(response.message, 'success');
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                try {
                    var response = JSON.parse(xhr.responseText);
                    showToast(response.message || 'Error updating section', 'error');
                } catch (e) {
                    showToast('Error updating section: ' + error, 'error');
                }
            }
        });
    });
    
    $(document).on('click', '.cancel-section', function() {
        var $cell = $(this).closest('.section-cell');
        var $display = $cell.find('.section-display');
        var $edit = $cell.find('.section-edit');
        var $input = $edit.find('.section-input');
        
        // Restore original value
        $input.val($input.data('original-value'));
        
        // Hide edit form
        $display.show();
        $edit.hide();
    });
    
    // Handle Enter key in section input
    $(document).on('keypress', '.section-input', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            $(this).closest('.section-edit').find('.save-section').click();
        }
    });
    
    // Search functionality for assignments
    var assignmentsTable;
    
    // Initialize assignment search functionality
    $('#assignmentSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        
        if (assignmentsTable) {
            assignmentsTable.search(value).draw();
        }
    });
    
    // Clear assignment search
    $('#clearSearch').on('click', function() {
        $('#assignmentSearch').val('');
        if (assignmentsTable) {
            assignmentsTable.search('').draw();
        }
    });
    
    // NEW: Filter functionality for students
    var studentsTable;
    
    // Apply filters button
    $('#applyFilters').on('click', function() {
        applyStudentFilters();
    });
    
    // Reset filters button
    $('#resetFilters').on('click', function() {
        $('#yearLevelFilter').val('');
        $('#studentsSearch').val('');
        applyStudentFilters();
    });
    
    // Function to apply student filters
    function applyStudentFilters() {
        if (!studentsTable) return;
        
        var yearLevel = $('#yearLevelFilter').val();
        var search = $('#studentsSearch').val().toLowerCase();
        
        // Apply year level filter (column 3)
        studentsTable.column(3).search(yearLevel ? '^' + yearLevel + '$' : '', true, false);
        
        // Apply global search filter
        studentsTable.search(search).draw();
    }
    
    // Real-time filter on change
    $('#yearLevelFilter').on('change', function() {
        applyStudentFilters();
    });
    
    // Real-time search on keyup with debounce
    var searchTimeout;
    $('#studentsSearch').on('keyup', function() {
        clearTimeout(searchTimeout);
        var self = this;
        searchTimeout = setTimeout(function() {
            applyStudentFilters();
        }, 300);
    });
    
    // Functions to load data
    function loadAssignments() {
        // Show loading state
        $('#assignmentsTable tbody').html('<tr><td colspan="9" class="text-center no-assignments"><i class="fas fa-spinner fa-spin"></i> Loading assignments...</td></tr>');
        
        $.ajax({
            url: 'index.php?action=get_subject_assignments',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var html = '';
                
                if (data.length > 0) {
                    // Destroy existing DataTable if it exists
                    if (assignmentsTable) {
                        assignmentsTable.destroy();
                    }
                    
                    // Build table HTML
                    $.each(data, function(index, assignment) {
                        var notesHtml = assignment.notes ? 
                            `<span class="notes-display" title="${htmlspecialchars(assignment.notes)}">${htmlspecialchars(assignment.notes)}</span>` : 
                            '-';
                        
                        html += '<tr>' +
                            '<td>' + htmlspecialchars(assignment.teacher_name) + '</td>' +
                            '<td>' + htmlspecialchars(assignment.subject_code) + ' - ' + htmlspecialchars(assignment.subject_description) + '</td>' +
                            '<td>' + htmlspecialchars(assignment.coursename) + ' (' + htmlspecialchars(assignment.courselevel) + ')</td>' +
                            '<td>' + (assignment.section ? htmlspecialchars(assignment.section) : '-') + '</td>' +
                            '<td>' + (assignment.hours ? htmlspecialchars(assignment.hours) + ' hrs' : '-') + '</td>' +
                            '<td>' + htmlspecialchars(assignment.academic_year) + '</td>' +
                            '<td>' + htmlspecialchars(assignment.semester) + '</td>' +
                            '<td>' + notesHtml + '</td>' +
                            '<td>' +
                                '<button type="button" class="btn btn-sm btn-primary edit-assignment" data-id="' + assignment.id + '">' +
                                    '<i class="fas fa-edit"></i>' +
                                '</button>' +
                                '<button type="button" class="btn btn-sm btn-danger delete-assignment" data-id="' + assignment.id + '">' +
                                    '<i class="fas fa-trash"></i>' +
                                '</button>' +
                            '</td>' +
                        '</tr>';
                    });
                    
                    $('#assignmentsTable tbody').html(html);
                    
                    // Initialize DataTable with pagination
                    assignmentsTable = $('#assignmentsTable').DataTable({
                        "pageLength": 10,
                        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        "order": [[5, "desc"], [6, "desc"], [2, "asc"], [1, "asc"]],
                        "responsive": true,
                        "autoWidth": false,
                        "language": {
                            "emptyTable": "No assignments available",
                            "zeroRecords": "No matching assignments found"
                        }
                    });
                    
                    // Hide the default search box
                    $('#assignmentsTable_filter').hide();
                } else {
                    html = '<tr><td colspan="9" class="text-center no-assignments">' +
                        '<div class="empty-state">' +
                            '<i class="fas fa-chalkboard-teacher"></i>' +
                            '<h3>No Assignments Found</h3>' +
                            '<p>There are currently no subject assignments in your department.</p>' +
                            '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#assignmentModal">' +
                                '<i class="fas fa-plus"></i> Create First Assignment' +
                            '</button>' +
                        '</div>' +
                    '</td></tr>';
                    
                    $('#assignmentsTable tbody').html(html);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                $('#assignmentsTable tbody').html('<tr><td colspan="9" class="text-center text-danger">Error loading assignments: ' + error + '</td></tr>');
            }
        });
    }
    
    function loadTeachers() {
        // Return a promise to allow for async loading
        return $.ajax({
            url: 'index.php?action=get_teachers',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var html = '<option value="">Unassigned</option>';
                if (data.length > 0) {
                    $.each(data, function(index, teacher) {
                        html += '<option value="' + teacher.id + '">' + htmlspecialchars(teacher.name) + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No teachers available</option>';
                }
                $('#teacher_id').html(html);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                $('#teacher_id').html('<option value="">Error loading teachers</option>');
                showToast('Error loading teachers', 'error');
            }
        });
    }
    
    function loadCourses() {
        // Return a promise to allow for async loading
        return $.ajax({
            url: 'index.php?action=get_courses_by_department',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var html = '<option value="">Select Course</option>';
                if (data.length > 0) {
                    $.each(data, function(index, course) {
                        html += '<option value="' + course.id + '">' + htmlspecialchars(course.coursename) + ' (' + htmlspecialchars(course.courselevel) + ')</option>';
                    });
                } else {
                    html += '<option value="" disabled>No courses available</option>';
                }
                $('#course_id').html(html);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                $('#course_id').html('<option value="">Error loading courses</option>');
                showToast('Error loading courses', 'error');
            }
        });
    }
    
    function loadSubjectsByCourse(courseId, callback) {
        // Show loading state
        $('#subject_id').html('<option value="">Loading subjects...</option>');
        
        $.ajax({
            url: 'index.php?action=get_subjects_by_course',
            type: 'POST',
            data: { course_id: courseId },
            dataType: 'json',
            success: function(data) {
                var html = '<option value="">Select Subject</option>';
                if (data.length > 0) {
                    $.each(data, function(index, subject) {
                        html += '<option value="' + subject.id + '">' + htmlspecialchars(subject.subject_code) + ' - ' + htmlspecialchars(subject.subject_description) + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No subjects available for this course</option>';
                }
                $('#subject_id').html(html);
                
                if (callback) callback();
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                $('#subject_id').html('<option value="">Error loading subjects</option>');
                showToast('Error loading subjects', 'error');
            }
        });
    }
    
    function loadAcademicYears() {
        // Return a promise to allow for async loading
        return $.ajax({
            url: 'index.php?action=get_academic_years',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var html = '<option value="">Select Year</option>';
                if (data.length > 0) {
                    $.each(data, function(index, year) {
                        html += '<option value="' + year + '">' + year + '</option>';
                    });
                } else {
                    html += '<option value="" disabled>No academic years available</option>';
                }
                $('#academic_year').html(html);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                $('#academic_year').html('<option value="">Error loading years</option>');
                showToast('Error loading academic years', 'error');
            }
        });
    }
    
    // Initialize students table with DataTables
    function initStudentsTable() {
        // Destroy existing DataTable if it exists
        if (studentsTable) {
            studentsTable.destroy();
        }
        
        // Initialize DataTable with pagination
        studentsTable = $('#studentsTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            "order": [[1, "asc"]],
            "responsive": true,
            "autoWidth": false,
            "language": {
                "emptyTable": "No students available",
                "zeroRecords": "No matching students found"
            },
            "columnDefs": [
                {
                    "targets": 5, // Section column
                    "render": function(data, type, row) {
                        // For filtering, we need the raw section value
                        if (type === 'filter' || type === 'sort') {
                            return data;
                        }
                        // For display, return the HTML
                        return data;
                    }
                }
            ]
        });
        
        // Hide the default search box
        $('#studentsTable_filter').hide();
    }
    
    // Load assignments when the assignment section is active
    if (activeTab === 'subject-assignment') {
        loadAssignments();
    }
    
    // Initialize students table when the students section is active
    if (activeTab === 'students') {
        initStudentsTable();
    }
    
    // Helper function to escape HTML
    function htmlspecialchars(str) {
        if (!str) return str;
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
</script>
</body>
</html>