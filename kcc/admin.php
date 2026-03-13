<?php
session_start();

// --- DATABASE CONNECTION ---
if (file_exists('config.php')) {
    require 'config.php';
} else {
    die("<div style='padding:20px; color:red;'><h3>Fatal Error:</h3> <b>config.php</b> file not found. Please ensure it is in the same folder.</div>");
}

// --- CHECK LOGGED IN USER ---
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['instructor_logged_in'])) {
    header("Location: login.php");
    exit;
}

// --- HANDLE TOAST MESSAGES FROM SESSION (Persistent after redirect) ---
 $toastMessage = "";
 $toastType = "";
if (isset($_SESSION['toastMessage'])) {
    $toastMessage = $_SESSION['toastMessage'];
    $toastType = $_SESSION['toastType'] ?? 'info';
    // Clear them so they don't show again
    unset($_SESSION['toastMessage']);
    unset($_SESSION['toastType']);
}

// Determine User Role and Details
 $is_admin = isset($_SESSION['admin_logged_in']);
 $is_instructor = isset($_SESSION['instructor_logged_in']);
 $user_role = $is_admin ? 'Admin' : 'Instructor';

// For Admins, we use the session username. 
// For Instructors, we MUST use their Full Name to match INSTNAME in grades.
 $user_display_name = $is_admin ? $_SESSION['admin_user'] : $_SESSION['instructor_fullname']; 
 $user_login_name = $is_admin ? $_SESSION['admin_user'] : $_SESSION['instructor_user'];

// --- HANDLE TAB SELECTION & PERSISTENCE ---
if (isset($_POST['tab'])) {
    $_SESSION['active_tab'] = $_POST['tab'];
} elseif (isset($_GET['tab'])) {
    $_SESSION['active_tab'] = $_GET['tab'];
}
if (!isset($_SESSION['active_tab'])) {
    $_SESSION['active_tab'] = 'grades';
}

// --- HANDLE FORM SUBMISSIONS ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- 0. BATCH UPDATE GRADES LOGIC ---
    if (isset($_POST['action']) && $_POST['action'] == 'batch_update_grades') {
        $updateCount = 0;
        $errorCount = 0;
        
        if (isset($_POST['grades']) && is_array($_POST['grades'])) {
            foreach ($_POST['grades'] as $id => $gradeData) {
                $id = intval($id);
                // Escape inputs
                $pg = $conn->real_escape_string($gradeData['pg']);
                $mg = $conn->real_escape_string($gradeData['mg']);
                $fg = $conn->real_escape_string($gradeData['fg']);
                $fa = $conn->real_escape_string($gradeData['fa']);
                $remarks = $conn->real_escape_string($gradeData['remarks']);

                $sql = "UPDATE tblnewgradesheetfinal 
                        SET PG = '$pg', MG = '$mg', FG = '$fg', FA = '$fa', REMARKS = '$remarks' 
                        WHERE AUTOID = $id";

                if ($conn->query($sql)) {
                    $updateCount++;
                } else {
                    $errorCount++;
                }
            }
        }

        // Store message in SESSION so it survives the redirect
        if ($updateCount > 0) {
            $_SESSION['toastMessage'] = "Successfully updated $updateCount grade(s)!";
            $_SESSION['toastType'] = "success";
        } elseif ($errorCount > 0) {
            $_SESSION['toastMessage'] = "Updated $updateCount grades, but $errorCount failed.";
            $_SESSION['toastType'] = "warning";
        } else {
            $_SESSION['toastMessage'] = "No changes detected.";
            $_SESSION['toastType'] = "info";
        }

        // Redirect to preserve filters
        if(isset($_POST['qs'])) {
            header("Location: admin.php?" . $_POST['qs']);
            exit;
        } else {
            header("Location: admin.php?tab=grades");
            exit;
        }
    }

    // --- 1. ADD/EDIT INSTRUCTOR LOGIC (Admin Only) ---
    if ($is_admin && isset($_POST['action']) && ($_POST['action'] == 'add_instructor' || $_POST['action'] == 'edit_instructor')) {
        $fullname = $conn->real_escape_string($_POST['fullname']); 
        $username = $conn->real_escape_string($_POST['username']);
        $password = $_POST['password'];

        if (!empty($fullname) && !empty($username)) {
            if ($_POST['action'] == 'add_instructor') {
                if (!empty($password)) {
                    $check = $conn->query("SELECT id FROM instructors WHERE username = '$username'");
                    if ($check->num_rows > 0) {
                        $_SESSION['toastMessage'] = "Username already exists!";
                        $_SESSION['toastType'] = "danger";
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $sql = "INSERT INTO instructors (fullname, username, password) VALUES ('$fullname', '$username', '$hashed_password')";
                        if ($conn->query($sql)) {
                            $_SESSION['toastMessage'] = "Instructor added successfully!";
                            $_SESSION['toastType'] = "success";
                        } else {
                            $_SESSION['toastMessage'] = "Error adding instructor: " . $conn->error;
                            $_SESSION['toastType'] = "danger";
                        }
                    }
                } else {
                    $_SESSION['toastMessage'] = "Password is required for new instructors.";
                    $_SESSION['toastType'] = "warning";
                }
            } else {
                // Edit Existing
                $id = intval($_POST['instructor_id']);
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE instructors SET fullname = '$fullname', username = '$username', password = '$hashed_password' WHERE id = $id";
                } else {
                    $sql = "UPDATE instructors SET fullname = '$fullname', username = '$username' WHERE id = $id";
                }
                
                if ($conn->query($sql)) {
                    $_SESSION['toastMessage'] = "Instructor updated successfully!";
                    $_SESSION['toastType'] = "success";
                    if($is_instructor && $id == $_SESSION['instructor_id']) {
                        $_SESSION['instructor_fullname'] = $fullname;
                        $_SESSION['instructor_user'] = $username;
                        $user_display_name = $fullname; 
                        $user_login_name = $username;
                    }
                } else {
                    $_SESSION['toastMessage'] = "Error updating instructor: " . $conn->error;
                    $_SESSION['toastType'] = "danger";
                }
            }
        } else {
            $_SESSION['toastMessage'] = "Name and Username are required.";
            $_SESSION['toastType'] = "warning";
        }
    }

    // --- 2. DELETE INSTRUCTOR LOGIC (Admin Only) ---
    if ($is_admin && isset($_POST['action']) && $_POST['action'] == 'delete_instructor') {
        $id = intval($_POST['instructor_id']);
        $sql = "DELETE FROM instructors WHERE id = $id";
        if ($conn->query($sql)) {
            $_SESSION['toastMessage'] = "Instructor deleted successfully!";
            $_SESSION['toastType'] = "success";
        } else {
            $_SESSION['toastMessage'] = "Error deleting instructor: " . $conn->error;
            $_SESSION['toastType'] = "danger";
        }
    }

    // --- 3. ADMIN USER LOGIC (Admin Only) ---
    if ($is_admin && isset($_POST['action']) && $_POST['action'] == 'add_user') {
        $fullname = $conn->real_escape_string($_POST['fullname']);
        $username = $conn->real_escape_string($_POST['username']);
        $password = $_POST['password'];

        if (!empty($username) && !empty($password)) {
            $check = $conn->query("SELECT id FROM admin_users WHERE username = '$username'");
            if ($check->num_rows > 0) {
                $_SESSION['toastMessage'] = "Username already exists!";
                $_SESSION['toastType'] = "danger";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO admin_users (fullname, username, password) VALUES ('$fullname', '$username', '$hashed_password')";
                if ($conn->query($sql)) {
                    $_SESSION['toastMessage'] = "User added successfully!";
                    $_SESSION['toastType'] = "success";
                } else {
                    $_SESSION['toastMessage'] = "Error adding user: " . $conn->error;
                    $_SESSION['toastType'] = "danger";
                }
            }
        }
    }
    
    if ($is_admin && isset($_POST['action']) && $_POST['action'] == 'edit_user') {
        $id = intval($_POST['user_id']);
        $fullname = $conn->real_escape_string($_POST['fullname']);
        $username = $conn->real_escape_string($_POST['username']);
        $password = $_POST['password']; 

        $check = $conn->query("SELECT id FROM admin_users WHERE username = '$username' AND id != $id");
        if ($check->num_rows > 0) {
            $_SESSION['toastMessage'] = "Username already taken by another user.";
            $_SESSION['toastType'] = "danger";
        } else {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE admin_users SET fullname = '$fullname', username = '$username', password = '$hashed_password' WHERE id = $id";
            } else {
                $sql = "UPDATE admin_users SET fullname = '$fullname', username = '$username' WHERE id = $id";
            }
            
            if ($conn->query($sql)) {
                $_SESSION['toastMessage'] = "User updated successfully!";
                $_SESSION['toastType'] = "success";
                if($id == $_SESSION['admin_id']) {
                    $_SESSION['admin_user'] = $username;
                    $user_display_name = $username;
                }
            } else {
                $_SESSION['toastMessage'] = "Error updating user: " . $conn->error;
                $_SESSION['toastType'] = "danger";
            }
        }
    }

    if ($is_admin && isset($_POST['action']) && $_POST['action'] == 'delete_user') {
        $id = intval($_POST['user_id']);
        if ($id == 1 && isset($_SESSION['admin_id']) && $_SESSION['admin_id'] == 1) { 
             $_SESSION['toastMessage'] = "You cannot delete the primary admin account.";
             $_SESSION['toastType'] = "danger";
        } else {
            $sql = "DELETE FROM admin_users WHERE id = $id";
            if ($conn->query($sql)) {
                $_SESSION['toastMessage'] = "User deleted successfully!";
                $_SESSION['toastType'] = "success";
            } else {
                $_SESSION['toastMessage'] = "Error deleting user: " . $conn->error;
                $_SESSION['toastType'] = "danger";
            }
        }
    }

    // --- 4. UPDATE MY PROFILE (Instructor: Username/Pass Only) ---
    if (isset($_POST['action']) && $_POST['action'] == 'update_profile') {
        $fullname = $is_admin ? $conn->real_escape_string($_POST['fullname']) : $_SESSION['instructor_fullname'];
        $username = $conn->real_escape_string($_POST['username']);
        $password = $_POST['password']; 

        if ($is_admin) {
            $id = $_SESSION['admin_id'];
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE admin_users SET fullname = '$fullname', username = '$username', password = '$hashed_password' WHERE id = $id";
            } else {
                $sql = "UPDATE admin_users SET fullname = '$fullname', username = '$username' WHERE id = $id";
            }
        } else {
            $id = $_SESSION['instructor_id'];
            $check = $conn->query("SELECT id FROM instructors WHERE username = '$username' AND id != $id");
            if ($check->num_rows > 0) {
                $_SESSION['toastMessage'] = "Username is already taken.";
                $_SESSION['toastType'] = "danger";
            } else {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE instructors SET username = '$username', password = '$hashed_password' WHERE id = $id";
                } else {
                    $sql = "UPDATE instructors SET username = '$username' WHERE id = $id";
                }

                if ($conn->query($sql)) {
                    $_SESSION['toastMessage'] = "Profile updated successfully!";
                    $_SESSION['toastType'] = "success";
                    $_SESSION['instructor_user'] = $username;
                    $user_login_name = $username; 
                } else {
                    $_SESSION['toastMessage'] = "Error updating profile: " . $conn->error;
                    $_SESSION['toastType'] = "danger";
                }
            }
        }
        
        if($is_admin && isset($sql) && $conn->query($sql)){
             $_SESSION['admin_user'] = $username;
             $user_login_name = $username;
             if(isset($fullname)) $user_display_name = $fullname;
        }
    }
}

// --- FETCH DATA FOR TABS ---

 $instructorsResult = null;
if ($is_admin) {
    $instructorsResult = $conn->query("SELECT id, fullname, username, created_at FROM instructors ORDER BY id ASC");
}

 $usersResult = null;
if ($is_admin) {
    $usersResult = $conn->query("SELECT id, fullname, username, created_at FROM admin_users ORDER BY id ASC");
}

 $availableInstructors = [];
if ($is_admin) {
    $instNamesQuery = "SELECT DISTINCT INSTNAME FROM tblnewgradesheetfinal WHERE INSTNAME IS NOT NULL AND INSTNAME != '' ORDER BY INSTNAME ASC";
    $instNamesResult = $conn->query($instNamesQuery);
    if ($instNamesResult) {
        while($row = $instNamesResult->fetch_assoc()) {
            $availableInstructors[] = $row['INSTNAME'];
        }
    }
}

// --- GRADE SHEET LOGIC ---
 $combinedData = [];

 $searchTerm = $_GET['search'] ?? '';
 $filterAcadYear = $_GET['acadyear'] ?? '';
 $filterSem = $_GET['semester'] ?? '';
 $filterCourse = $_GET['crscode'] ?? '';
 $filterSubject = $_GET['subjcode'] ?? '';

 $is_active_filter = (!empty($searchTerm) || !empty($filterAcadYear) || !empty($filterSem) || !empty($filterCourse) || !empty($filterSubject));

 $sql = "SELECT * FROM `tblnewgradesheetfinal` WHERE 1=1";
 $params = [];

if ($is_instructor) {
    $safeInstructorName = $conn->real_escape_string($user_display_name);
    $sql .= " AND `INSTNAME` = '$safeInstructorName'";
}

if ($is_active_filter) {
    if (!empty($searchTerm)) {
        $safeSearch = $conn->real_escape_string($searchTerm);
        $sql .= " AND (`IDNO` LIKE '%$safeSearch%' OR `LNAME` LIKE '%$safeSearch%' OR `FNAME` LIKE '%$safeSearch%')";
        $params['search'] = $searchTerm;
    }
    if (!empty($filterAcadYear)) {
        $safeYear = $conn->real_escape_string($filterAcadYear);
        $sql .= " AND `ACADEMICYR` = '$safeYear'";
        $params['acadyear'] = $filterAcadYear;
    }
    if (!empty($filterSem)) {
        $safeSem = $conn->real_escape_string($filterSem);
        $sql .= " AND `SEMESTER` = '$safeSem'";
        $params['semester'] = $filterSem;
    }
    if (!empty($filterCourse)) {
        $safeCourse = $conn->real_escape_string($filterCourse);
        $sql .= " AND `CRSCODE` = '$safeCourse'";
        $params['crscode'] = $filterCourse;
    }
    if (!empty($filterSubject)) {
        $safeSubject = $conn->real_escape_string($filterSubject);
        $sql .= " AND `SUBJCODE` = '$safeSubject'";
        $params['subjcode'] = $filterSubject;
    }

    $sql .= " ORDER BY `IDNO` ASC, `SUBJCODE` ASC";

    $result = $conn->query($sql);
    $currentStudentID = null;

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $idno = $row['IDNO'];
            if ($currentStudentID != $idno) {
                $combinedData[$idno] = [
                    'IDNO' => $row['IDNO'], 'LNAME' => $row['LNAME'], 'FNAME' => $row['FNAME'],
                    'MNAME' => $row['MNAME'], 'GENDER' => $row['GENDER'], 'SEMESTER' => $row['SEMESTER'],
                    'ACADEMICYR' => $row['ACADEMICYR'], 'CRSCODE' => $row['CRSCODE'], 
                    'CRSLEVEL' => $row['CRSLEVEL'], 'CRSMAJOR' => $row['CRSMAJOR'], 'COURSE_INFO' => [] 
                ];
                $combinedData[$idno]['COURSE_INFO'][] = $row; 
                $currentStudentID = $idno;
            } else {
                $combinedData[$idno]['COURSE_INFO'][] = $row;
            }
        }
    }
}

// --- POPULATE FILTER OPTIONS ---
 $yearsResult = $conn->query("SELECT DISTINCT ACADEMICYR FROM tblnewgradesheetfinal " . ($is_instructor ? "WHERE INSTNAME = '" . $conn->real_escape_string($user_display_name) . "'" : "") . " ORDER BY ACADEMICYR DESC");
 $academicYears = [];
if ($yearsResult) { while($row = $yearsResult->fetch_assoc()) { $academicYears[] = $row['ACADEMICYR']; } }

 $semResult = $conn->query("SELECT DISTINCT SEMESTER FROM tblnewgradesheetfinal " . ($is_instructor ? "WHERE INSTNAME = '" . $conn->real_escape_string($user_display_name) . "'" : "") . " ORDER BY SEMESTER");
 $semesters = [];
if ($semResult) { while($row = $semResult->fetch_assoc()) { $semesters[] = $row['SEMESTER']; } }

 $courseResult = $conn->query("SELECT DISTINCT CRSCODE FROM tblnewgradesheetfinal " . ($is_instructor ? "WHERE INSTNAME = '" . $conn->real_escape_string($user_display_name) . "'" : "") . " ORDER BY CRSCODE ASC");
 $courses = [];
if ($courseResult) { while($row = $courseResult->fetch_assoc()) { $courses[] = $row['CRSCODE']; } }

 $subjResult = $conn->query("SELECT DISTINCT SUBJCODE FROM tblnewgradesheetfinal " . ($is_instructor ? "WHERE INSTNAME = '" . $conn->real_escape_string($user_display_name) . "'" : "") . " ORDER BY SUBJCODE ASC");
 $subjects = [];
if ($subjResult) { while($row = $subjResult->fetch_assoc()) { $subjects[] = $row['SUBJCODE']; } }

function buildQueryString($params) { return http_build_query($params); }
 $queryString = buildQueryString($params);
 $currentFullQS = $_SERVER['QUERY_STRING'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KCC Admin - Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #f4f6f9; }
        
        /* Table Styling */
        .table-fixed-header thead th {
            position: sticky;
            top: 0;
            background-color: #0d6efd;
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }
        
        .table td, .table th { 
            vertical-align: middle; 
        }
        
        .bg-student-header { 
            background-color: #e9ecef !important; 
            color: #000 !important; 
            border-bottom: 2px solid #adb5bd !important; 
        }

        /* Main Header */
        .main-header.navbar-dark { background-color: #003d7a !important; border-bottom: 1px solid #00305c; }
        .main-header a { color: #fff !important; }
        .sidebar-clock {
            padding: 15px;
            text-align: center;
            border-top: 1px solid #4b545c;
            background-color: #343a40;
            color: #c2c7d0;
            font-size: 13px;
        }
        .clock-time { font-size: 16px; font-weight: bold; color: #fff; display: block; margin-bottom: 2px; }
        
        /* --- REDESIGNED GRADE INPUT STYLES --- */
        
        /* Container for the 2-row layout */
        .grade-edit-container {
            display: flex;
            flex-direction: column;
            gap: 8px; /* Space between the two rows */
        }

        /* Top Row: PG, MG, FG, FA */
        .grade-numbers-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 5px;
        }

        /* Bottom Row: Remarks */
        .grade-remarks-row {
            display: flex;
            justify-content: flex-end;
        }

        .grade-box {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .grade-label {
            font-size: 11px;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .grade-input {
            width: 100%;
            text-align: center;
            font-weight: bold;
            border: 1px solid transparent;
            background-color: rgba(255, 255, 255, 0.05);
            padding: 6px 4px;
            border-radius: 4px;
            font-size: 13px;
            transition: all 0.2s;
        }

        .grade-input:hover {
            background-color: rgba(255, 255, 255, 0.3);
            border-color: #ced4da;
        }

        .grade-input:focus {
            background-color: #fff;
            border-color: #80bdff;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }

        /* Specific styling for Remarks Select */
        .grade-select {
            width: 100%;
            padding: 4px 8px;
            font-size: 12px;
            border: 1px solid transparent;
            border-radius: 4px;
            cursor: pointer;
            background-color: transparent;
            text-align: left;
        }
        .grade-select:hover {
            background-color: rgba(255, 255, 255, 0.3);
            border-color: #ced4da;
        }
        .grade-select:focus {
            background-color: #fff;
            border-color: #80bdff;
            outline: none;
        }

        /* FAB Save Button */
        .fab-save {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            font-size: 24px;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }
        .fab-save:hover {
            transform: scale(1.1);
        }

        /* Toast */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-dark">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
      <li class="nav-item d-none d-sm-inline-block"><a href="#" class="nav-link font-weight-bold">KCC Admin</a></li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="#">
          <i class="fas fa-user-circle mr-1"></i> <?php echo htmlspecialchars($user_login_name); ?> 
          <span class="badge badge-info"><?php echo $user_role; ?></span>
        </a>
      </li>
    </ul>
  </nav>

  <!-- Main Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="admin.php" class="brand-link">
      <img src="uploads/kcc.jpg" alt="KCC Logo" class="brand-image img-circle elevation-3" style="opacity: .8; height: 35px; margin-right: .5rem;">
      <span class="brand-text font-weight-light">KCC Grading System</span>
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo htmlspecialchars($user_login_name); ?></a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          
          <li class="nav-item">
            <a href="admin.php?tab=grades" class="nav-link <?php echo ($_SESSION['active_tab'] == 'grades') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-file-alt"></i>
              <p>Grade Sheets</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="admin.php?tab=profile" class="nav-link <?php echo ($_SESSION['active_tab'] == 'profile') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-user-cog"></i>
              <p>My Profile</p>
            </a>
          </li>

          <?php if($is_admin): ?>
          <li class="nav-item">
            <a href="admin.php?tab=instructors" class="nav-link <?php echo ($_SESSION['active_tab'] == 'instructors') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-chalkboard-teacher"></i>
              <p>Manage Instructors</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="admin.php?tab=users" class="nav-link <?php echo ($_SESSION['active_tab'] == 'users') ? 'active' : ''; ?>">
              <i class="nav-icon fas fa-users-cog"></i>
              <p>Admin Users</p>
            </a>
          </li>
          <?php endif; ?>

          <li class="nav-item">
            <a href="logout.php" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt text-danger"></i>
              <p>Logout</p>
            </a>
          </li>
        </ul>

        <div class="sidebar-clock">
            <span id="clock-date" style="font-size: 12px; opacity: 0.8;"></span>
            <span id="clock-time" class="clock-time"></span>
        </div>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">
                <?php 
                    if ($_SESSION['active_tab'] == 'instructors') echo 'Manage Instructors';
                    elseif ($_SESSION['active_tab'] == 'users') echo 'Manage Admin Users';
                    elseif ($_SESSION['active_tab'] == 'profile') echo 'My Profile';
                    else echo 'My Grade Sheets'; 
                ?>
            </h1>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">

        <!-- ================= TAB: GRADE SHEETS (REDESIGNED EDIT) ================= -->
        <?php if ($_SESSION['active_tab'] == 'grades'): ?>
            <!-- Filter Bar -->
            <div class="card card-primary card-outline">
                <div class="card-header"><h3 class="card-title">Search & Filter Grade Sheets</h3></div>
                <div class="card-body">
                <form action="admin.php" method="GET" class="form-horizontal">
                    <input type="hidden" name="tab" value="grades">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="input-group mb-2">
                                <input type="text" name="search" class="form-control" placeholder="Student ID/Name..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    <a href="admin.php?tab=grades" class="btn btn-default" title="Clear Filters"><i class="fas fa-times"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 mb-2">
                            <select name="acadyear" class="form-control select2">
                                <option value="">Academic Year</option>
                                <?php foreach($academicYears as $year): ?>
                                    <option value="<?php echo $year; ?>" <?php echo ($filterAcadYear == $year) ? 'selected' : ''; ?>><?php echo $year; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2 mb-2">
                            <select name="semester" class="form-control select2">
                                <option value="">Semester</option>
                                <?php foreach($semesters as $sem): ?>
                                    <option value="<?php echo $sem; ?>" <?php echo ($filterSem == $sem) ? 'selected' : ''; ?>><?php echo $sem; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2 mb-2">
                            <select name="crscode" class="form-control select2">
                                <option value="">All Courses</option>
                                <?php foreach($courses as $crs): ?>
                                    <option value="<?php echo $crs; ?>" <?php echo ($filterCourse == $crs) ? 'selected' : ''; ?>><?php echo $crs; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2">
                            <select name="subjcode" class="form-control select2">
                                <option value="">All Subjects</option>
                                <?php foreach($subjects as $subj): ?>
                                    <option value="<?php echo $subj; ?>" <?php echo ($filterSubject == $subj) ? 'selected' : ''; ?>><?php echo $subj; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>
                </form>
                </div>
            </div>

            <!-- Results Table -->
            <div class="card">
                <div class="card-header bg-white border-bottom-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <?php echo $is_instructor ? 'My Classes' : 'Grade Sheet Records'; ?>
                        </h3>
                        
                        <!-- Print Buttons -->
                        <?php if ($is_active_filter): ?>
                        <div class="btn-group">
                            <a href="print.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="btn btn-default btn-sm bg-light border">
                                <i class="fas fa-print mr-1"></i> Print Gradesheet
                            </a>
                            <a href="printmasterlist.php?<?php echo http_build_query($_GET); ?>" target="_blank" class="btn btn-default btn-sm bg-light border">
                                <i class="fas fa-users mr-1"></i> Print Masterlist
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    
                    <?php if (!$is_active_filter): ?>
                        <div class="p-5 text-center text-muted">
                            <i class="fas fa-filter fa-3x mb-3 text-secondary opacity-50"></i>
                            <h4>Please apply filters to view records.</h4>
                            <p>Select an Academic Year, Semester, Course, Subject, or search by Name to load data.</p>
                        </div>
                    <?php else: ?>
                        <?php if (count($combinedData) > 0): ?>
                            <!-- BATCH EDIT FORM START -->
                            <form action="admin.php" method="POST" id="batchGradeForm">
                                <input type="hidden" name="action" value="batch_update_grades">
                                <input type="hidden" name="qs" value="<?php echo htmlspecialchars($currentFullQS); ?>">
                                
                                <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                                <table class="table table-striped table-bordered projects table-fixed-header">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th style="width: 18%">Student Name</th>
                                            <th style="width: 8%">Sem/Yr</th>
                                            <th style="width: 22%">Subject</th>
                                            <th style="width: 5%">Units</th>
                                            <th style="width: 10%">Instructor</th>
                                            <th style="width: 37%">Grades & Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($combinedData as $student): ?>
                                            <?php $count = count($student['COURSE_INFO']); $fullName = $student['LNAME'] . ', ' . $student['FNAME'] . ' ' . $student['MNAME']; ?>
                                            <?php foreach ($student['COURSE_INFO'] as $index => $course): ?>
                                                <tr>
                                                    <?php if ($index === 0): ?>
                                                        <td rowspan="<?php echo $count; ?>" class="bg-student-header">
                                                            <span class="badge badge-secondary mb-1"><?php echo htmlspecialchars($student['IDNO']); ?></span><br>
                                                            <strong><?php echo htmlspecialchars($fullName); ?></strong><br>
                                                            <span class="text-sm text-primary"><b><?php echo htmlspecialchars($student['CRSCODE']); ?></b> - <?php echo htmlspecialchars($student['CRSLEVEL']); ?> - <?php echo htmlspecialchars($student['CRSMAJOR']); ?></span><br>
                                                            <small class="text-muted"><i class="fas fa-venus-mars mr-1"></i><?php echo htmlspecialchars($student['GENDER']); ?></small>
                                                        </td>
                                                        <td rowspan="<?php echo $count; ?>" class="text-center">
                                                            <?php echo htmlspecialchars($student['SEMESTER']); ?><br>
                                                            <span class="badge badge-light text-dark border"><?php echo htmlspecialchars($student['ACADEMICYR']); ?></span>
                                                        </td>
                                                    <?php endif; ?>
                                                    <td>
                                                        <span class="badge badge-light border text-dark"><?php echo htmlspecialchars($course['SUBJCODE']); ?></span><br>
                                                        <small class="d-block mb-1"><?php echo htmlspecialchars($course['SUBJDESC']); ?></small>
                                                        <small class="text-muted"><i class="far fa-clock"></i> <?php echo htmlspecialchars($course['SUBJSCHEDULE']); ?></small>
                                                    </td>
                                                    <td class="text-center"><?php echo htmlspecialchars($course['SUBJUNIT']); ?></td>
                                                    <td><?php echo htmlspecialchars($course['INSTNAME']); ?></td>
                                                    
                                                    <!-- REDESIGNED EDIT AREA -->
                                                    <td>
                                                        <div class="grade-edit-container">
                                                            
                                                            <!-- Row 1: 4 Columns for PG, MG, FG, FA -->
                                                            <div class="grade-numbers-row">
                                                                <div class="grade-box">
                                                                    <span class="grade-label">PG</span>
                                                                    <input type="number" step="0.01" name="grades[<?php echo $course['AUTOID']; ?>][pg]" class="grade-input" value="<?php echo htmlspecialchars($course['PG']); ?>" required>
                                                                </div>
                                                                <div class="grade-box">
                                                                    <span class="grade-label">MG</span>
                                                                    <input type="number" step="0.01" name="grades[<?php echo $course['AUTOID']; ?>][mg]" class="grade-input" value="<?php echo htmlspecialchars($course['MG']); ?>" required>
                                                                </div>
                                                                <div class="grade-box">
                                                                    <span class="grade-label">FG</span>
                                                                    <input type="number" step="0.01" name="grades[<?php echo $course['AUTOID']; ?>][fg]" class="grade-input" value="<?php echo htmlspecialchars($course['FG']); ?>" required>
                                                                </div>
                                                                <div class="grade-box">
                                                                    <span class="grade-label">FA</span>
                                                                    <input type="number" step="0.01" name="grades[<?php echo $course['AUTOID']; ?>][fa]" class="grade-input text-success" value="<?php echo htmlspecialchars($course['FA']); ?>" required>
                                                                </div>
                                                            </div>

                                                            <!-- Row 2: Remarks (Full Width) -->
                                                            <div class="grade-remarks-row">
                                                                <div class="w-100">
                                                                    <select name="grades[<?php echo $course['AUTOID']; ?>][remarks]" class="grade-select">
                                                                        <option value="">Select Status...</option>
                                                                        <option value="Passed" <?php echo (trim($course['REMARKS']) == 'Passed') ? 'selected' : ''; ?>>Passed</option>
                                                                        <option value="Failed" <?php echo (trim($course['REMARKS']) == 'Failed') ? 'selected' : ''; ?>>Failed</option>
                                                                        <option value="Incomplete" <?php echo (trim($course['REMARKS']) == 'Incomplete') ? 'selected' : ''; ?>>Incomplete</option>
                                                                        <option value="Dropped" <?php echo (trim($course['REMARKS']) == 'Dropped') ? 'selected' : ''; ?>>Dropped</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>
                            </form>
                            <!-- BATCH EDIT FORM END -->
                            
                            <!-- Floating Save Button -->
                            <button type="submit" form="batchGradeForm" class="btn btn-success fab-save" title="Save All Changes" onclick="return confirm('Are you sure you want to save all changes on this page?');">
                                <i class="fas fa-save"></i>
                            </button>

                        <?php else: ?>
                            <div class="p-5 text-center text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50"></i>
                                <h4>No records found matching your filters.</h4>
                                <p>Try adjusting the Course or Subject filter.</p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ================= TAB: MY PROFILE (Instructor & Admin) ================= -->
        <?php if ($_SESSION['active_tab'] == 'profile'): ?>
            <div class="card card-info card-outline">
                <div class="card-header"><h3 class="card-title">My Profile & Credentials</h3></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <img src="https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg" class="img-circle elevation-3" alt="User Image" style="width: 100px;">
                            <h4 class="mt-2"><?php echo htmlspecialchars($user_display_name); ?></h4>
                            <p class="text-muted"><?php echo $user_role; ?></p>
                        </div>
                        <div class="col-md-9">
                            <form action="admin.php" method="POST">
                                <input type="hidden" name="tab" value="profile">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Username</label>
                                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user_login_name); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Role</label>
                                            <input type="text" class="form-control" value="<?php echo $user_role; ?>" disabled>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Full Name (Display Name)</label>
                                    <?php if($is_instructor): ?>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_display_name); ?>" disabled>
                                        <small class="text-danger">Full Name is locked. Contact Admin to change INSTNAME.</small>
                                    <?php else: ?>
                                        <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($user_display_name); ?>" required>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label>New Password (Leave blank to keep current password)</label>
                                    <input type="password" name="password" class="form-control">
                                </div>

                                <button type="submit" class="btn btn-info"><i class="fas fa-save"></i> Update Profile</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ================= TAB: INSTRUCTORS (ADMIN ONLY) ================= -->
        <?php if ($_SESSION['active_tab'] == 'instructors' && $is_admin): ?>
            <div class="card card-info card-outline">
                <div class="card-header"><h3 class="card-title">Manage Instructors</h3></div>
                <div class="card-body">
                    <form action="admin.php" method="POST" class="mb-4 bg-light p-3 rounded border">
                        <input type="hidden" name="tab" value="instructors">
                        <input type="hidden" name="action" value="add_instructor">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Instructor Name (Matches INSTNAME)</label>
                                <input type="text" name="fullname" list="instructorList" class="form-control" placeholder="Type or Select Name" required>
                                <datalist id="instructorList">
                                    <?php foreach($availableInstructors as $instName): ?>
                                        <option value="<?php echo htmlspecialchars($instName); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            <div class="col-md-3">
                                <label>Username (for Login)</label>
                                <input type="text" name="username" class="form-control" placeholder="e.g. jdelacruz" required>
                            </div>
                            <div class="col-md-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-info btn-block"><i class="fas fa-plus"></i> Add</button>
                            </div>
                        </div>
                    </form>

                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>Instructor Name</th>
                                <th>Username</th>
                                <th>Created At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($instructorsResult && $instructorsResult->num_rows > 0): ?>
                                <?php while($uRow = $instructorsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $uRow['id']; ?></td>
                                    <td><?php echo htmlspecialchars($uRow['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($uRow['username']); ?></td>
                                    <td><?php echo $uRow['created_at']; ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-info btn-sm" onclick="editInstructor(<?php echo $uRow['id']; ?>, '<?php echo htmlspecialchars($uRow['username'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($uRow['fullname'], ENT_QUOTES); ?>')"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDeleteInstructor(<?php echo $uRow['id']; ?>, '<?php echo htmlspecialchars($uRow['fullname'], ENT_QUOTES); ?>')"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">No instructors found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ================= TAB: ADMIN USERS (ADMIN ONLY) ================= -->
        <?php if ($_SESSION['active_tab'] == 'users' && $is_admin): ?>
            <div class="card card-success card-outline">
                <div class="card-header"><h3 class="card-title">Manage Admin Users</h3></div>
                <div class="card-body">
                    <form action="admin.php" method="POST" class="mb-4 bg-light p-3 rounded border">
                        <input type="hidden" name="tab" value="users">
                        <input type="hidden" name="action" value="add_user">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="fullname" class="form-control" placeholder="Full Name" required>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="username" class="form-control" placeholder="Username" required>
                            </div>
                            <div class="col-md-3">
                                <input type="password" name="password" class="form-control" placeholder="Password" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success btn-block"><i class="fas fa-plus"></i> Add User</button>
                            </div>
                        </div>
                    </form>

                    <table class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 50px">ID</th>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Created At</th>
                                <th class="text-center" style="width: 150px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($usersResult && $usersResult->num_rows > 0): ?>
                                <?php while($uRow = $usersResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $uRow['id']; ?></td>
                                    <td><?php echo htmlspecialchars($uRow['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($uRow['username']); ?></td>
                                    <td><?php echo $uRow['created_at']; ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-info btn-sm" onclick="editUser(<?php echo $uRow['id']; ?>, '<?php echo htmlspecialchars($uRow['username'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($uRow['fullname'], ENT_QUOTES); ?>')"><i class="fas fa-pencil-alt"></i></button>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDeleteUser(<?php echo $uRow['id']; ?>, '<?php echo htmlspecialchars($uRow['fullname'], ENT_QUOTES); ?>')"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">No users found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

      </div>
    </section>
  </div>

  <footer class="main-footer"><strong>Copyright &copy; <?php echo date('Y'); ?> KCC College.</strong> All rights reserved.</footer>
</div>

<!-- ================= TOAST NOTIFICATION CONTAINER ================= -->
<div class="toast-container">
    <div id="liveToast" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">
                <!-- Message goes here -->
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- ================= MODALS ================= -->

<!-- Instructor Edit Modal -->
<div class="modal fade" id="editInstructorModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h4 class="modal-title">Edit Instructor</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="admin.php" method="POST">
        <input type="hidden" name="tab" value="instructors">
        <div class="modal-body">
            <input type="hidden" name="action" value="edit_instructor">
            <input type="hidden" name="instructor_id" id="edit_ins_id">
            
            <div class="form-group">
                <label>Instructor Name (Matches INSTNAME)</label>
                <input type="text" name="fullname" id="edit_ins_fullname" list="instructorListModal" class="form-control" required>
                <datalist id="instructorListModal">
                     <?php foreach($availableInstructors as $instName): ?>
                        <option value="<?php echo htmlspecialchars($instName); ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="edit_ins_username" class="form-control" required>
            </div>

            <div class="form-group">
                <label>New Password (Optional)</label>
                <input type="password" name="password" class="form-control">
            </div>
        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-info">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Instructor Modal -->
<div class="modal fade" id="deleteInstructorModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h4 class="modal-title">Delete Instructor</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="admin.php" method="POST">
        <input type="hidden" name="tab" value="instructors">
        <div class="modal-body">
            <input type="hidden" name="action" value="delete_instructor">
            <input type="hidden" name="instructor_id" id="del_ins_id">
            <p>Are you sure you want to delete <b><span id="del_ins_name"></span></b>?</p>
        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Admin User Edit Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h4 class="modal-title">Edit Admin User</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="admin.php" method="POST">
        <input type="hidden" name="tab" value="users">
        <div class="modal-body">
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="form-group"><label>Full Name</label><input type="text" name="fullname" id="edit_fullname" class="form-control" required></div>
            <div class="form-group"><label>Username</label><input type="text" name="username" id="edit_username" class="form-control" required></div>
            <div class="form-group"><label>New Password (Optional)</label><input type="password" name="password" class="form-control"></div>
        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-info">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h4 class="modal-title">Delete Admin User</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="admin.php" method="POST">
        <input type="hidden" name="tab" value="users">
        <div class="modal-body">
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="user_id" id="delete_user_id">
            <p>Are you sure you want to delete user <b><span id="delete_username_display"></span></b>?</p>
        </div>
        <div class="modal-footer justify-content-between">
            <button type="button" class="btn btn-default" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
function updateClock() {
    const now = new Date();
    document.getElementById('clock-date').innerText = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    document.getElementById('clock-time').innerText = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}
setInterval(updateClock, 1000);
updateClock();

// Instructor Management Functions
function editInstructor(id, username, fullname) {
    $('#edit_ins_id').val(id);
    $('#edit_ins_username').val(username);
    $('#edit_ins_fullname').val(fullname);
    var myModal = new bootstrap.Modal(document.getElementById('editInstructorModal'));
    myModal.show();
}
function confirmDeleteInstructor(id, fullname) {
    $('#del_ins_id').val(id);
    $('#del_ins_name').text(fullname);
    var myModal = new bootstrap.Modal(document.getElementById('deleteInstructorModal'));
    myModal.show();
}

// Admin User Functions
function editUser(id, username, fullname) {
    $('#edit_user_id').val(id);
    $('#edit_username').val(username);
    $('#edit_fullname').val(fullname);
    var myModal = new bootstrap.Modal(document.getElementById('editUserModal'));
    myModal.show();
}
function confirmDeleteUser(id, fullname) {
    $('#delete_user_id').val(id);
    $('#delete_username_display').text(fullname);
    var myModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    myModal.show();
}

// TOAST NOTIFICATION LOGIC
document.addEventListener("DOMContentLoaded", function(event) {
    <?php if(!empty($toastMessage)): ?>
        var toastEl = document.getElementById('liveToast');
        var toastBody = document.getElementById('toastMessage');
        
        // Set message
        toastBody.innerText = "<?php echo $toastMessage; ?>";
        
        // Set Color based on type
        toastEl.className = 'toast align-items-center text-white border-0';
        <?php if($toastType == 'success'): ?>
            toastEl.classList.add('bg-success');
        <?php elseif($toastType == 'danger'): ?>
            toastEl.classList.add('bg-danger');
        <?php elseif($toastType == 'warning'): ?>
            toastEl.classList.add('bg-warning');
        <?php else: ?>
            toastEl.classList.add('bg-primary');
        <?php endif; ?>

        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    <?php endif; ?>
});
</script>

</body>
</html>