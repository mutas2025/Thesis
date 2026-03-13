<?php
session_start();
require_once '../config.php';
requireRole('registrar');

// Get data for dropdowns
 $courses = mysqli_query($conn, "SELECT *, CONCAT(coursename, ' - ', courselevel) as course_full_name FROM courses ORDER BY coursename, courselevel");
 $activeAcademicYears = mysqli_query($conn, "SELECT * FROM academic_years WHERE is_active = 1 ORDER BY academic_year DESC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'load_students':
                // Load students based on filters
                $conditions = [];
                if (!empty($_POST['course'])) $conditions[] = "e.course_id = '" . mysqli_real_escape_string($conn, $_POST['course']) . "'";
                if (!empty($_POST['academic_year'])) $conditions[] = "e.academic_year = '" . mysqli_real_escape_string($conn, $_POST['academic_year']) . "'";
                if (!empty($_POST['semester'])) $conditions[] = "e.semester = '" . mysqli_real_escape_string($conn, $_POST['semester']) . "'";
                if (!empty($_POST['status'])) $conditions[] = "e.status = '" . mysqli_real_escape_string($conn, $_POST['status']) . "'";
                
                $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
                
                $query = "SELECT e.id, s.id as student_id, s.last_name, s.first_name, s.middle_name, s.id_number,
                          c.id as course_id, c.coursename, c.courselevel, e.academic_year, e.semester, 
                          e.enrollment_date, e.status, e.year_level
                          FROM enrollments e
                          JOIN students s ON e.student_id = s.id
                          JOIN courses c ON e.course_id = c.id
                          $whereClause
                          ORDER BY s.last_name, s.first_name";
                
                $result = mysqli_query($conn, $query);
                $students = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $row['student_name'] = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                    $row['course_full_name'] = $row['coursename'] . ' - ' . $row['courselevel'];
                    $students[] = $row;
                }
                
                header('Content-Type: application/json');
                echo json_encode($students);
                exit();
                
            case 'load_subjects':
                // Load subjects based on filters
                $conditions = [];
                if (!empty($_POST['course'])) $conditions[] = "course_id = '" . mysqli_real_escape_string($conn, $_POST['course']) . "'";
                if (!empty($_POST['academic_year'])) $conditions[] = "academic_year = '" . mysqli_real_escape_string($conn, $_POST['academic_year']) . "'";
                if (!empty($_POST['semester'])) $conditions[] = "semester = '" . mysqli_real_escape_string($conn, $_POST['semester']) . "'";
                
                $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
                
                $query = "SELECT * FROM subjects $whereClause ORDER BY subject_code";
                
                $result = mysqli_query($conn, $query);
                $subjects = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $subjects[] = $row;
                }
                
                header('Content-Type: application/json');
                echo json_encode($subjects);
                exit();
                
            case 'assign_to_all':
                // Assign selected subjects to all filtered students
                $courseId = mysqli_real_escape_string($conn, $_POST['course']);
                $academicYear = mysqli_real_escape_string($conn, $_POST['academic_year']);
                $semester = mysqli_real_escape_string($conn, $_POST['semester']);
                $status = mysqli_real_escape_string($conn, $_POST['status']);
                $selectedSubjects = $_POST['subjects'];
                
                // Get all students matching the filters
                $conditions = [];
                if (!empty($courseId)) $conditions[] = "e.course_id = '$courseId'";
                if (!empty($academicYear)) $conditions[] = "e.academic_year = '$academicYear'";
                if (!empty($semester)) $conditions[] = "e.semester = '$semester'";
                if (!empty($status)) $conditions[] = "e.status = '$status'";
                
                $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
                
                $query = "SELECT e.id as enrollment_id, s.id as student_id
                          FROM enrollments e
                          JOIN students s ON e.student_id = s.id
                          $whereClause";
                
                $result = mysqli_query($conn, $query);
                $students = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $students[] = $row;
                }
                
                $successCount = 0;
                $errorCount = 0;
                $errors = [];
                
                // Assign each subject to each student
                foreach ($students as $student) {
                    foreach ($selectedSubjects as $subjectId) {
                        $subjectId = mysqli_real_escape_string($conn, $subjectId);
                        
                        // Check if subject is already assigned
                        $checkQuery = "SELECT id FROM student_subjects 
                                       WHERE student_id = {$student['student_id']} 
                                       AND subject_id = $subjectId 
                                       AND enrollment_id = {$student['enrollment_id']}";
                        
                        if (mysqli_num_rows(mysqli_query($conn, $checkQuery)) == 0) {
                            // Assign the subject
                            $insertQuery = "INSERT INTO student_subjects (student_id, subject_id, enrollment_id) 
                                            VALUES ({$student['student_id']}, $subjectId, {$student['enrollment_id']})";
                            
                            if (mysqli_query($conn, $insertQuery)) {
                                $successCount++;
                            } else {
                                $errorCount++;
                                $errors[] = mysqli_error($conn);
                            }
                        }
                    }
                }
                
                $_SESSION['message'] = "Subjects assigned successfully to $successCount student enrollments";
                if ($errorCount > 0) {
                    $_SESSION['message'] .= ". $errorCount assignments failed.";
                    $_SESSION['message_type'] = "warning";
                } else {
                    $_SESSION['message_type'] = "success";
                }
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => $_SESSION['message'],
                    'successCount' => $successCount,
                    'errorCount' => $errorCount
                ]);
                exit();
        }
    }
}

// Get current user info for display
 $current_user_id = $_SESSION['user_id'] ?? 0;
 $current_user = $conn->query("SELECT username, fullname, role FROM users WHERE id = $current_user_id")->fetch_assoc();
 $display_name = $current_user['fullname'] ?? $current_user['username'] ?? 'Admin User';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bulk Assign Subjects</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    
    <style>
        .sidebar-menu li.active {
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar-menu li.active > a {
            color: #fff;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        .nav-tabs {
            margin-bottom: 15px;
        }
        .search-box {
            margin-bottom: 15px;
        }
        .subject-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }
        .subject-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 5px;
        }
        .subject-item:hover {
            background-color: #f5f5f5;
        }
        .unit-counter {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-weight: bold;
        }
        .brand-link {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .brand-link img {
            max-height: 35px;
            margin-right: 10px;
        }
        .filter-container {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .filter-container .form-group {
            margin-bottom: 10px;
        }
        .student-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }
        .student-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 5px;
        }
        .student-item:hover {
            background-color: #f5f5f5;
        }
        .requirements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
        }
        /* Red and Blue Theme Customizations */
        .main-sidebar {
            background-color: #004085 !important;
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item > .nav-link.active {
            background-color: rgba(220,53,69,0.2);
            color: #fff;
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item > .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }
        .brand-link {
            background-color: #002752 !important;
            border-bottom: 1px solid #004085;
        }
        .btn-primary {
            background-color: #004085;
            border-color: #004085;
        }
        .btn-primary:hover {
            background-color: #003366;
            border-color: #002244;
        }
        .btn-success {
            background-color: #004085;
            border-color: #004085;
        }
        .btn-success:hover {
            background-color: #003366;
            border-color: #002244;
        }
        .nav-tabs .nav-link.active {
            color: #004085;
            border-color: #004085 #004085 #fff;
        }
        .nav-tabs .nav-link:hover {
            border-color: #e9ecef #e9ecef #004085;
            color: #004085;
        }
        .pagination .page-item.active .page-link {
            background-color: #004085;
            border-color: #004085;
        }
        .page-link {
            color: #004085;
        }
        .page-link:hover {
            color: #003366;
        }
        .dropdown-item.active, .dropdown-item:active {
            background-color: #004085;
        }
        .logout-btn {
            margin-top: auto;
            padding: 10px 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .logout-btn .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 10px 15px;
        }
        .logout-btn .nav-link:hover {
            color: #fff;
            background-color: rgba(220,53,69,0.2);
        }
        .progress-container {
            margin-top: 20px;
            display: none;
        }
        .progress {
            height: 25px;
        }
        .progress-bar {
            line-height: 25px;
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
        </ul>
        <!-- Removed logout button from navbar -->
    </nav>
    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-success elevation-4">
        <a href="#" class="brand-link">
            <img src="../uploads/csr.png" alt="CSR Logo">
            <span class="brand-text font-weight-light">Registrar</span>
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
                        <a href="registrar.php#dashboard" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="registrar.php#students" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Students</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="registrar.php#enrollments" class="nav-link">
                            <i class="nav-icon fas fa-user-graduate"></i>
                            <p>Enrollments</p>
                        </a>
                    </li>
                     <li class="nav-item">
                        <a href="registrar.php#grades" class="nav-link">
                            <i class="nav-icon fas fa-id-card"></i>
                            <p>Grades</p>
                        </a>
                    </li>
                      <li class="nav-item">
                        <a href="registrar.php#reports" class="nav-link">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            <p>Reports</p>
                        </a>
                    </li>
                    <!-- Logout button moved to sidebar -->
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
                <h1 class="m-0">Bulk Assign Subjects</h1>
            </div>
        </div>
        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <!-- Toast Container -->
                <div class="toast-container"></div>
                
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bulk Subject Assignment</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="filter-container">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filterCourse" class="form-label">Course</label>
                                        <select class="form-control" id="filterCourse">
                                            <option value="">All Courses</option>
                                            <?php 
                                            mysqli_data_seek($courses, 0);
                                            while ($course = mysqli_fetch_assoc($courses)): ?>
                                            <option value="<?= $course['id'] ?>"><?= $course['course_full_name'] ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="filterAcademicYear" class="form-label">Academic Year</label>
                                        <select class="form-control" id="filterAcademicYear">
                                            <option value="">All Years</option>
                                            <?php 
                                            $yearQuery = "SELECT DISTINCT academic_year FROM academic_years ORDER BY academic_year DESC";
                                            $yearResult = mysqli_query($conn, $yearQuery);
                                            while ($yearRow = mysqli_fetch_assoc($yearResult)) {
                                                echo '<option value="' . $yearRow['academic_year'] . '">' . $yearRow['academic_year'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="filterSemester" class="form-label">Semester</label>
                                        <select class="form-control" id="filterSemester">
                                            <option value="">All Semesters</option>
                                            <option value="1st">1st Semester</option>
                                            <option value="2nd">2nd Semester</option>
                                            <option value="Summer">Summer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="filterStatus" class="form-label">Status</label>
                                        <select class="form-control" id="filterStatus">
                                            <option value="">All Statuses</option>
                                            <option value="Registered">Registered</option>
                                            <option value="Enrolled">Enrolled</option>
                                            <option value="Dropped">Dropped</option>
                                            <option value="Completed">Completed</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label">&nbsp;</label>
                                        <div>
                                            <button type="button" id="applyFilters" class="btn btn-primary">
                                                <i class="fas fa-filter"></i> Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Students List -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Students</h5>
                                        <div class="card-tools">
                                            <span class="badge badge-info" id="studentCount">0 students</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="studentsList" class="student-list">
                                            <div class="text-center text-muted p-4">
                                                <i class="fas fa-filter fa-3x mb-3"></i>
                                                <p>Apply filters to view students</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Subjects List -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Subjects</h5>
                                        <div class="card-tools">
                                            <button type="button" id="selectAllSubjects" class="btn btn-xs btn-default">
                                                <i class="fas fa-check-square"></i> Select All
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div id="subjectsList" class="subject-list">
                                            <div class="text-center text-muted p-4">
                                                <i class="fas fa-book fa-3x mb-3"></i>
                                                <p>Apply filters to view subjects</p>
                                            </div>
                                        </div>
                                        <div id="unitCounter" class="unit-counter">
                                            Total Units: <span id="totalUnits">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="progress-container">
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" id="progressBar" style="width: 0%">
                                    0%
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="row mt-3">
                            <div class="col-md-12 text-center">
                                <button type="button" id="assignToAllBtn" class="btn btn-success btn-lg" disabled>
                                    <i class="fas fa-users-cog"></i> Assign Selected Subjects to All Students
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
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
    // Show toast notification
    function showToast(message, type = 'success') {
        const toastId = 'toast-' + Date.now();
        const toastClass = type === 'success' ? 'toast-success' : (type === 'warning' ? 'toast-warning' : 'toast-error');
        const icon = type === 'success' ? 'fa-check-circle' : (type === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle');
        
        const toastHtml = `
            <div id="${toastId}" class="toast ${toastClass}" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                <div class="toast-header">
                    <i class="fas ${icon} mr-2"></i>
                    <strong class="mr-auto">Registrar</strong>
                    <small>Just now</small>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        $('.toast-container').append(toastHtml);
        
        const toastElement = $(`#${toastId}`);
        toastElement.toast('show');
        
        toastElement.on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }
    
    // Check for session messages and show toasts
    <?php if(isset($_SESSION['message'])): ?>
        showToast('<?=$_SESSION['message']?>', '<?=$_SESSION['message_type']?>');
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>
    
    // Apply filters button
    $('#applyFilters').on('click', function() {
        loadStudents();
        loadSubjects();
    });
    
    // Load students function
    function loadStudents() {
        const course = $('#filterCourse').val();
        const academicYear = $('#filterAcademicYear').val();
        const semester = $('#filterSemester').val();
        const status = $('#filterStatus').val();
        
        $.ajax({
            url: 'assignsubjects.php',
            type: 'POST',
            data: {
                action: 'load_students',
                course: course,
                academic_year: academicYear,
                semester: semester,
                status: status
            },
            dataType: 'json',
            success: function(data) {
                let studentsHtml = '';
                
                if (data.length === 0) {
                    studentsHtml = '<div class="text-center text-muted p-4"><i class="fas fa-user-slash fa-3x mb-3"></i><p>No students found</p></div>';
                    $('#studentCount').text('0 students');
                    $('#assignToAllBtn').prop('disabled', true);
                } else {
                    studentsHtml = '<div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input" id="selectAllStudents"><label class="custom-control-label" for="selectAllStudents">Select All Students</label></div>';
                    
                    $.each(data, function(index, student) {
                        studentsHtml += `
                            <div class="student-item">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input student-checkbox" id="student${student.student_id}" value="${student.student_id}">
                                    <label class="custom-control-label" for="student${student.student_id}">
                                        <strong>${student.student_name}</strong><br>
                                        <small>ID: ${student.id_number} | Course: ${student.course_full_name}</small>
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    
                    $('#studentCount').text(data.length + ' students');
                    
                    // Check if there are subjects selected
                    if ($('.subject-checkbox:checked').length > 0) {
                        $('#assignToAllBtn').prop('disabled', false);
                    }
                }
                
                $('#studentsList').html(studentsHtml);
                
                // Select All Students functionality
                $('#selectAllStudents').on('change', function() {
                    $('.student-checkbox').prop('checked', $(this).prop('checked'));
                    checkAssignButton();
                });
                
                // Check individual student selection
                $('.student-checkbox').on('change', function() {
                    checkAssignButton();
                });
            },
            error: function() {
                $('#studentsList').html('<div class="text-center text-danger p-4"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><p>Error loading students</p></div>');
                $('#studentCount').text('0 students');
                $('#assignToAllBtn').prop('disabled', true);
            }
        });
    }
    
    // Load subjects function
    function loadSubjects() {
        const course = $('#filterCourse').val();
        const academicYear = $('#filterAcademicYear').val();
        const semester = $('#filterSemester').val();
        
        $.ajax({
            url: 'assignsubjects.php',
            type: 'POST',
            data: {
                action: 'load_subjects',
                course: course,
                academic_year: academicYear,
                semester: semester
            },
            dataType: 'json',
            success: function(data) {
                let subjectsHtml = '';
                
                if (data.length === 0) {
                    subjectsHtml = '<div class="text-center text-muted p-4"><i class="fas fa-book-open fa-3x mb-3"></i><p>No subjects found</p></div>';
                    $('#totalUnits').text('0');
                    $('#assignToAllBtn').prop('disabled', true);
                } else {
                    subjectsHtml = '';
                    
                    $.each(data, function(index, subject) {
                        subjectsHtml += `
                            <div class="subject-item">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input subject-checkbox" id="subject${subject.id}" value="${subject.id}" data-units="${subject.unit}">
                                    <label class="custom-control-label" for="subject${subject.id}">
                                        <strong>${subject.subject_code}</strong> - ${subject.subject_description}
                                        <span class="badge badge-info">${subject.unit} unit(s)</span>
                                        ${subject.year_level ? '<span class="badge badge-secondary">' + subject.year_level + '</span>' : ''}
                                        ${subject.academic_year ? '<span class="badge badge-primary">' + subject.academic_year + '</span>' : ''}
                                        ${subject.semester ? '<span class="badge badge-warning">' + subject.semester + '</span>' : ''}
                                        ${subject.pre_requisite ? '<span class="badge badge-danger">Prerequisite: ' + subject.pre_requisite + '</span>' : ''}
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    
                    // Check if there are students loaded
                    if ($('.student-checkbox').length > 0) {
                        $('#assignToAllBtn').prop('disabled', false);
                    }
                }
                
                $('#subjectsList').html(subjectsHtml);
                
                // Select All Subjects functionality
                $('#selectAllSubjects').on('click', function() {
                    const allChecked = $('.subject-checkbox:checked').length === $('.subject-checkbox').length;
                    $('.subject-checkbox').prop('checked', !allChecked);
                    updateUnitCounter();
                    checkAssignButton();
                });
                
                // Update unit counter when subjects are selected/deselected
                $('.subject-checkbox').on('change', function() {
                    updateUnitCounter();
                    checkAssignButton();
                });
            },
            error: function() {
                $('#subjectsList').html('<div class="text-center text-danger p-4"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><p>Error loading subjects</p></div>');
                $('#totalUnits').text('0');
                $('#assignToAllBtn').prop('disabled', true);
            }
        });
    }
    
    // Update unit counter
    function updateUnitCounter() {
        let totalUnits = 0;
        $('.subject-checkbox:checked').each(function() {
            totalUnits += parseInt($(this).data('units'));
        });
        $('#totalUnits').text(totalUnits);
    }
    
    // Check if assign button should be enabled
    function checkAssignButton() {
        const hasStudents = $('.student-checkbox:checked').length > 0;
        const hasSubjects = $('.subject-checkbox:checked').length > 0;
        $('#assignToAllBtn').prop('disabled', !(hasStudents && hasSubjects));
    }
    
    // Assign to all button
    $('#assignToAllBtn').on('click', function() {
        const course = $('#filterCourse').val();
        const academicYear = $('#filterAcademicYear').val();
        const semester = $('#filterSemester').val();
        const status = $('#filterStatus').val();
        
        const selectedSubjects = [];
        $('.subject-checkbox:checked').each(function() {
            selectedSubjects.push($(this).val());
        });
        
        if (selectedSubjects.length === 0) {
            showToast('Please select at least one subject', 'error');
            return;
        }
        
        // Show progress bar
        $('.progress-container').show();
        $('#progressBar').css('width', '10%').text('10%');
        
        // Disable button during operation
        $('#assignToAllBtn').prop('disabled', true);
        
        $.ajax({
            url: 'assignsubjects.php',
            type: 'POST',
            data: {
                action: 'assign_to_all',
                course: course,
                academic_year: academicYear,
                semester: semester,
                status: status,
                subjects: selectedSubjects
            },
            dataType: 'json',
            success: function(response) {
                // Update progress bar
                $('#progressBar').css('width', '100%').text('100%');
                
                // Show success message
                if (response.errorCount > 0) {
                    showToast(response.message, 'warning');
                } else {
                    showToast(response.message, 'success');
                }
                
                // Hide progress bar after delay
                setTimeout(function() {
                    $('.progress-container').hide();
                    $('#progressBar').css('width', '0%').text('0%');
                }, 2000);
                
                // Reload data
                loadStudents();
            },
            error: function(xhr) {
                // Update progress bar to show error
                $('#progressBar').removeClass('progress-bar-animated').addClass('bg-danger').css('width', '100%').text('Error');
                
                // Show error message
                showToast('Error assigning subjects: ' + xhr.responseText, 'error');
                
                // Hide progress bar after delay
                setTimeout(function() {
                    $('.progress-container').hide();
                    $('#progressBar').removeClass('bg-danger').addClass('progress-bar-animated').css('width', '0%').text('0%');
                }, 3000);
                
                // Re-enable button
                $('#assignToAllBtn').prop('disabled', false);
            }
        });
    });
});
</script>
</body>
</html>