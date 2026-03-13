<?php
require_once 'config.php';
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}



// Require login and admin role
requireRole('admin');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process form data
    $active_tab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'dashboard';
    
    // User operations
    if (isset($_POST['add_user'])) {
        $username = $_POST['username'];
        $fullname = $_POST['fullname'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        
        $sql = "INSERT INTO users (username, fullname, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $fullname, $password, $role);
        $stmt->execute();
        $_SESSION['toast_message'] = "User added successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['edit_user'])) {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $fullname = $_POST['fullname'];
        $role = $_POST['role'];
        
        // Check if password change is requested
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username = ?, fullname = ?, role = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $username, $fullname, $role, $password, $id);
            } else {
                $_SESSION['toast_message'] = "Passwords do not match!";
                $_SESSION['toast_type'] = "error";
                header("Location: " . basename(__FILE__) . "?tab=users");
                exit();
            }
        } else {
            $sql = "UPDATE users SET username = ?, fullname = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $username, $fullname, $role, $id);
        }
        $stmt->execute();
        $_SESSION['toast_message'] = "User updated successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['delete_user'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "User deleted successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    // Teacher operations
    if (isset($_POST['add_teacher'])) {
        $name = $_POST['name'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $employee_id = $_POST['employee_id'];
        
        $sql = "INSERT INTO teachers (name, username, password, employee_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $username, $password, $employee_id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Teacher added successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['edit_teacher'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $username = $_POST['username'];
        $employee_id = $_POST['employee_id'];
        
        // Check if password change is requested
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $sql = "UPDATE teachers SET name = ?, username = ?, employee_id = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $name, $username, $employee_id, $password, $id);
            } else {
                $_SESSION['toast_message'] = "Passwords do not match!";
                $_SESSION['toast_type'] = "error";
                header("Location: " . basename(__FILE__) . "?tab=teachers");
                exit();
            }
        } else {
            $sql = "UPDATE teachers SET name = ?, username = ?, employee_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $username, $employee_id, $id);
        }
        $stmt->execute();
        $_SESSION['toast_message'] = "Teacher updated successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['delete_teacher'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM teachers WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Teacher deleted successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    // Program Head operations
    if (isset($_POST['add_program_head'])) {
        $name = $_POST['name'];
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $department_id = $_POST['department_id'];
        
        $sql = "INSERT INTO program_heads (name, username, password, department_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $username, $password, $department_id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Program Head added successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['edit_program_head'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $username = $_POST['username'];
        $department_id = $_POST['department_id'];
        
        // Check if password change is requested
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $sql = "UPDATE program_heads SET name = ?, username = ?, department_id = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssisi", $name, $username, $department_id, $password, $id);
            } else {
                $_SESSION['toast_message'] = "Passwords do not match!";
                $_SESSION['toast_type'] = "error";
                header("Location: " . basename(__FILE__) . "?tab=program-heads");
                exit();
            }
        } else {
            $sql = "UPDATE program_heads SET name = ?, username = ?, department_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $name, $username, $department_id, $id);
        }
        $stmt->execute();
        $_SESSION['toast_message'] = "Program Head updated successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['delete_program_head'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM program_heads WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Program Head deleted successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    // Student operations
    if (isset($_POST['add_student'])) {
        $student_id = $_POST['student_id'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $sql = "UPDATE students SET email = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $email, $password, $student_id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Student credentials added successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['edit_student'])) {
        $id = $_POST['id'];
        $email = $_POST['email'];
        
        // Check if password change is requested
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $sql = "UPDATE students SET email = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $email, $password, $id);
            } else {
                $_SESSION['toast_message'] = "Passwords do not match!";
                $_SESSION['toast_type'] = "error";
                header("Location: " . basename(__FILE__) . "?tab=students");
                exit();
            }
        } else {
            $sql = "UPDATE students SET email = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $email, $id);
        }
        $stmt->execute();
        $_SESSION['toast_message'] = "Student updated successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['delete_student'])) {
        $id = $_POST['id'];
        $sql = "UPDATE students SET email = NULL, password = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Student credentials removed successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    // Course operations
    if (isset($_POST['add_course'])) {
        $coursename = $_POST['coursename'];
        $courselevel = $_POST['courselevel'];
        $coursedescription = $_POST['coursedescription'];
        $department_id = $_POST['department_id'];
        
        $sql = "INSERT INTO courses (coursename, courselevel, coursedescription, department_id) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $coursename, $courselevel, $coursedescription, $department_id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Course added successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['edit_course'])) {
        $id = $_POST['id'];
        $coursename = $_POST['coursename'];
        $courselevel = $_POST['courselevel'];
        $coursedescription = $_POST['coursedescription'];
        $department_id = $_POST['department_id'];
        
        $sql = "UPDATE courses SET coursename = ?, courselevel = ?, coursedescription = ?, department_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $coursename, $courselevel, $coursedescription, $department_id, $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Course updated successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['delete_course'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM courses WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Course deleted successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    // Department operations
    if (isset($_POST['add_department'])) {
        $dept_name = $_POST['dept_name'];
        $dept_description = $_POST['dept_description'];
        
        $sql = "INSERT INTO departments (dept_name, dept_description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $dept_name, $dept_description);
        $stmt->execute();
        $_SESSION['toast_message'] = "Department added successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['edit_department'])) {
        $id = $_POST['id'];
        $dept_name = $_POST['dept_name'];
        $dept_description = $_POST['dept_description'];
        
        $sql = "UPDATE departments SET dept_name = ?, dept_description = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $dept_name, $dept_description, $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Department updated successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['delete_department'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM departments WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Department deleted successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    // Subject operations
    if (isset($_POST['add_subject'])) {
        $subject_code = $_POST['subject_code'];
        $subject_description = $_POST['subject_description'];
        $unit = $_POST['unit'];
        $pre_requisite = $_POST['pre_requisite'];
        $course_id = $_POST['course_id'];
        $academic_year = $_POST['academic_year'];
        $semester = $_POST['semester'];
        $year_level = $_POST['year_level'];
        $effective_year = $_POST['effective_year'];
        
        $sql = "INSERT INTO subjects (subject_code, subject_description, unit, pre_requisite, course_id, academic_year, semester, year_level, effective_year) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $subject_code, $subject_description, $unit, $pre_requisite, $course_id, $academic_year, $semester, $year_level, $effective_year);
        $stmt->execute();
        $_SESSION['toast_message'] = "Subject added successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['edit_subject'])) {
        $id = $_POST['id'];
        $subject_code = $_POST['subject_code'];
        $subject_description = $_POST['subject_description'];
        $unit = $_POST['unit'];
        $pre_requisite = $_POST['pre_requisite'];
        $course_id = $_POST['course_id'];
        $academic_year = $_POST['academic_year'];
        $semester = $_POST['semester'];
        $year_level = $_POST['year_level'];
        $effective_year = $_POST['effective_year'];
        
        $sql = "UPDATE subjects SET subject_code = ?, subject_description = ?, unit = ?, pre_requisite = ?, course_id = ?, academic_year = ?, semester = ?, year_level = ?, effective_year = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssii", $subject_code, $subject_description, $unit, $pre_requisite, $course_id, $academic_year, $semester, $year_level, $effective_year, $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Subject updated successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['delete_subject'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM subjects WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Subject deleted successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    // Academic Year operations
    if (isset($_POST['add_academic_year'])) {
        $academic_year = $_POST['academic_year'];
        
        $sql = "INSERT INTO academic_years (academic_year, is_active) VALUES (?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $academic_year);
        $stmt->execute();
        $_SESSION['toast_message'] = "Academic Year added successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['edit_academic_year'])) {
        $id = $_POST['id'];
        $academic_year = $_POST['academic_year'];
        
        $sql = "UPDATE academic_years SET academic_year = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $academic_year, $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Academic Year updated successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    if (isset($_POST['delete_academic_year'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM academic_years WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Academic Year deleted successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    // Toggle academic year active status
    if (isset($_POST['toggle_academic_year'])) {
        $id = $_POST['id'];
        $is_active = $_POST['is_active'];
        
        if ($is_active == 1) {
            // If setting to active, first deactivate all others
            $conn->query("UPDATE academic_years SET is_active = 0 WHERE 1");
        }
        
        $sql = "UPDATE academic_years SET is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $is_active, $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Academic Year status updated successfully!";
        $_SESSION['toast_type'] = "success";
    }

    // Bank operations
    if (isset($_POST['add_bank'])) {
        $bank_name = $_POST['bank_name'];
        $branch = $_POST['branch'];
        $account_number = $_POST['account_number'];
        
        $sql = "INSERT INTO banks (bank_name, branch, account_number) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $bank_name, $branch, $account_number);
        $stmt->execute();
        $_SESSION['toast_message'] = "Bank added successfully!";
        $_SESSION['toast_type'] = "success";
    }

    if (isset($_POST['edit_bank'])) {
        $id = $_POST['id'];
        $bank_name = $_POST['bank_name'];
        $branch = $_POST['branch'];
        $account_number = $_POST['account_number'];
        
        $sql = "UPDATE banks SET bank_name = ?, branch = ?, account_number = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $bank_name, $branch, $account_number, $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Bank updated successfully!";
        $_SESSION['toast_type'] = "success";
    }

    if (isset($_POST['delete_bank'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM banks WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Bank deleted successfully!";
        $_SESSION['toast_type'] = "success";
    }

    // Service operations
    if (isset($_POST['add_service'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $sql = "INSERT INTO services (name, description, price, is_active) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdi", $name, $description, $price, $is_active);
        $stmt->execute();
        $_SESSION['toast_message'] = "Service added successfully!";
        $_SESSION['toast_type'] = "success";
    }

    if (isset($_POST['edit_service'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $sql = "UPDATE services SET name = ?, description = ?, price = ?, is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdii", $name, $description, $price, $is_active, $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Service updated successfully!";
        $_SESSION['toast_type'] = "success";
    }

    if (isset($_POST['delete_service'])) {
        $id = $_POST['id'];
        $sql = "DELETE FROM services WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Service deleted successfully!";
        $_SESSION['toast_type'] = "success";
    }

    if (isset($_POST['toggle_service'])) {
        $id = $_POST['id'];
        $is_active = $_POST['is_active'];
        
        $sql = "UPDATE services SET is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $is_active, $id);
        $stmt->execute();
        $_SESSION['toast_message'] = "Service status updated successfully!";
        $_SESSION['toast_type'] = "success";
    }
    
    // Redirect to prevent form resubmission and maintain active tab
    header("Location: " . basename(__FILE__) . "?tab=" . $active_tab);
    exit();
}
// Determine active tab from GET parameter or session
 $active_tab = isset($_GET['tab']) ? $_GET['tab'] : (isset($_SESSION['active_tab']) ? $_SESSION['active_tab'] : 'dashboard');
 $_SESSION['active_tab'] = $active_tab;
// Fetch data for tables
 $users = $conn->query("SELECT * FROM users");
 $teachers = $conn->query("SELECT * FROM teachers");
 $program_heads = $conn->query("SELECT ph.*, d.dept_name FROM program_heads ph JOIN departments d ON ph.department_id = d.id");
 $students = $conn->query("SELECT id, id_number, CONCAT(last_name, ', ', first_name, ' ', middle_name) as full_name, email FROM students");
 $courses = $conn->query("SELECT c.*, d.dept_name FROM courses c JOIN departments d ON c.department_id = d.id");
 $departments = $conn->query("SELECT * FROM departments");
 $subjects = $conn->query("SELECT s.*, c.coursename, c.courselevel FROM subjects s JOIN courses c ON s.course_id = c.id");
 $academic_years = $conn->query("SELECT * FROM academic_years");
 $active_year = $conn->query("SELECT * FROM academic_years WHERE is_active = 1")->fetch_assoc();
 $banks = $conn->query("SELECT * FROM banks");
 $services = $conn->query("SELECT * FROM services");
// Dashboard statistics
 $userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
 $teacherCount = $conn->query("SELECT COUNT(*) as count FROM teachers")->fetch_assoc()['count'];
 $programHeadCount = $conn->query("SELECT COUNT(*) as count FROM program_heads")->fetch_assoc()['count'];
 $studentCount = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
 $courseCount = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];
 $departmentCount = $conn->query("SELECT COUNT(*) as count FROM departments")->fetch_assoc()['count'];
 $subjectCount = $conn->query("SELECT COUNT(*) as count FROM subjects")->fetch_assoc()['count'];
 $activeYearCount = $conn->query("SELECT COUNT(*) as count FROM academic_years WHERE is_active = 1")->fetch_assoc()['count'];
 
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
    <title>Admin</title>
    <link rel="icon" href="uploads/csr.png" type="image/x-icon">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <!-- Green Theme CSS -->
<style>
        :root {
            --primary: #004085; /* Changed from green to blue */
            --secondary: #dc3545; /* Changed from green to red */
            --success: #004085; /* Changed from green to blue */
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #004085; /* Changed from green to blue */
        }
        
        .bg-primary { background-color: var(--primary) !important; }
        .bg-success { background-color: var(--success) !important; }
        .bg-info { background-color: var(--info) !important; }
        .bg-warning { background-color: var(--warning) !important; }
        .bg-danger { background-color: var(--danger) !important; }
        .bg-secondary { background-color: var(--secondary) !important; }
        
        .btn-primary { background-color: var(--primary); border-color: var(--primary); }
        .btn-success { background-color: var(--success); border-color: var(--success); }
        .btn-info { background-color: var(--info); border-color: var(--info); }
        .btn-warning { background-color: var(--warning); border-color: var(--warning); }
        .btn-danger { background-color: var(--danger); border-color: var(--danger); }
        .btn-secondary { background-color: var(--secondary); border-color: var(--secondary); }
        
        .btn-primary:hover { background-color: #003366; border-color: #002244; } /* Updated to darker blue */
        .btn-success:hover { background-color: #003366; border-color: #002244; } /* Updated to darker blue */
        .btn-secondary:hover { background-color: #c82333; border-color: #bd2130; } /* Updated to darker red */
        
        .nav-pills .nav-link.active { background-color: var(--primary); }
        .navbar-white { background-color: var(--primary) !important; }
        .navbar-white .navbar-nav .nav-link { color: white; }
        .main-sidebar { background-color: var(--dark) !important; }
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
            background-color: var(--secondary);
            color: #fff;
        }
        
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        
        .brand-link {
            background-color: rgba(0, 0, 0, 0.1);
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
            background-color: var(--success);
            color: white;
        }
        
        .toast-error {
            background-color: var(--danger);
            color: white;
        }
        
        .toast-warning {
            background-color: var(--warning);
            color: #212529;
        }
        
        .toast-info {
            background-color: var(--info);
            color: white;
        }
        
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 10px;
        }
        
        .nav-pills .nav-link {
            border-radius: 0;
        }
        
        .small-box {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .small-box:hover {
            transform: translateY(-5px);
        }
        
        .user-panel {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .user-panel .info {
            color: #fff;
        }
        
        .user-panel .info > a {
            color: #fff;
        }
        
        /* Reports section styling */
        .report-button {
            margin: 15px;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .report-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        
        .report-button i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .report-button h4 {
            margin: 0;
            font-weight: bold;
        }
        
        .report-button p {
            margin-top: 5px;
            color: #666;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
    </nav>
    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="#" class="brand-link">
            <img src="csr.png" alt="SJCCI" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">Administrator</span>
        </a>
        <div class="sidebar">
            <!-- User Panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="csr.png" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?= htmlspecialchars($display_name) ?></a>
                </div>
            </div>
            
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="#dashboard" class="nav-link <?= $active_tab == 'dashboard' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#departments" class="nav-link <?= $active_tab == 'departments' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-building"></i>
                            <p>Departments</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#courses" class="nav-link <?= $active_tab == 'courses' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-graduation-cap"></i>
                            <p>Courses</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#subjects" class="nav-link <?= $active_tab == 'subjects' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-book"></i>
                            <p>Subjects</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#academic-year" class="nav-link <?= $active_tab == 'academic-year' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Academic Year</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#banks" class="nav-link <?= $active_tab == 'banks' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fa-solid fa-building-columns"></i>
                            <p>Banks</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#services" class="nav-link <?= $active_tab == 'services' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fa-solid fa-cogs"></i>
                            <p>Services</p>
                        </a>
                    </li>
                    <li class="nav-item has-treeview <?= in_array($active_tab, ['users', 'teachers', 'program-heads', 'students']) ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= in_array($active_tab, ['users', 'teachers', 'program-heads', 'students']) ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-users"></i>
                            <p>
                                User Management
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="#users" class="nav-link <?= $active_tab == 'users' ? 'active' : '' ?>" data-toggle="tab">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>System Users</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#teachers" class="nav-link <?= $active_tab == 'teachers' ? 'active' : '' ?>" data-toggle="tab">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Teachers</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#program-heads" class="nav-link <?= $active_tab == 'program-heads' ? 'active' : '' ?>" data-toggle="tab">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Program Heads</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#students" class="nav-link <?= $active_tab == 'students' ? 'active' : '' ?>" data-toggle="tab">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Students</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                    <li class="nav-item">
                        <a href="#reports" class="nav-link <?= $active_tab == 'reports' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Reports</p>
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
                <h1 class="m-0 text-dark">Admin Dashboard</h1>
            </div>
        </div>
        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <!-- Toast Container -->
                <div class="toast-container"></div>
                
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade <?= $active_tab == 'dashboard' ? 'show active' : '' ?>" id="dashboard">
                        <div class="row">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3><?= $userCount ?></h3>
                                        <p>System Users</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-primary">
                                    <div class="inner">
                                        <h3><?= $teacherCount ?></h3>
                                        <p>Teachers</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3><?= $programHeadCount ?></h3>
                                        <p>Program Heads</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3><?= $studentCount ?></h3>
                                        <p>Students</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3><?= $courseCount ?></h3>
                                        <p>Total Courses</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-secondary">
                                    <div class="inner">
                                        <h3><?= $departmentCount ?></h3>
                                        <p>Departments</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3><?= $subjectCount ?></h3>
                                        <p>Subjects</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-book"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3><?= $activeYearCount ?></h3>
                                        <p>Active Academic Year</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-success">
                                    <div class="card-header">
                                        <h5 class="card-title">Active Academic Year</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if($active_year): ?>
                                        <div class="alert alert-success">
                                            <strong>Active Academic Year:</strong> 
                                            <?= $active_year['academic_year'] ?>
                                        </div>
                                        <?php else: ?>
                                        <div class="alert alert-warning">
                                            No active academic year set. Please set one in the Academic Year section.
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Teachers Tab -->
                    <div class="tab-pane fade <?= $active_tab == 'teachers' ? 'show active' : '' ?>" id="teachers">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Manage Teachers</h3>
                                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addTeacherModal">
                                    <i class="fas fa-plus"></i> Add Teacher
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="teachersTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Username</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $teachers->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= $row['employee_id'] ?></td>
                                            <td><?= $row['name'] ?></td>
                                            <td><?= $row['username'] ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editTeacherModal<?= $row['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteTeacherModal<?= $row['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Teacher Modal -->
                                        <div class="modal fade" id="editTeacherModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Teacher</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="teachers">
                                                            <div class="form-group">
                                                                <label>Employee ID</label>
                                                                <input type="text" name="employee_id" class="form-control" value="<?= $row['employee_id'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Full Name</label>
                                                                <input type="text" name="name" class="form-control" value="<?= $row['name'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Username</label>
                                                                <input type="text" name="username" class="form-control" value="<?= $row['username'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <button type="button" class="btn btn-sm btn-info" id="togglePasswordChangeTeacher<?= $row['id'] ?>">Change Password</button>
                                                            </div>
                                                            <div id="passwordChangeFieldsTeacher<?= $row['id'] ?>" style="display: none;">
                                                                <div class="form-group">
                                                                    <label>New Password</label>
                                                                    <div class="input-group">
                                                                        <input type="password" name="new_password" class="form-control" id="newPasswordTeacher<?= $row['id'] ?>">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text password-toggle" onclick="togglePassword('newPasswordTeacher<?= $row['id'] ?>')">
                                                                                <i class="fas fa-eye"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Confirm Password</label>
                                                                    <div class="input-group">
                                                                        <input type="password" name="confirm_password" class="form-control" id="confirmPasswordTeacher<?= $row['id'] ?>">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text password-toggle" onclick="togglePassword('confirmPasswordTeacher<?= $row['id'] ?>')">
                                                                                <i class="fas fa-eye"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="edit_teacher" class="btn btn-primary">Update Teacher</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Delete Teacher Modal -->
                                        <div class="modal fade" id="deleteTeacherModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Delete Teacher</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this teacher?</p>
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="teachers">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="delete_teacher" class="btn btn-danger">Delete Teacher</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Program Heads Tab -->
                    <div class="tab-pane fade <?= $active_tab == 'program-heads' ? 'show active' : '' ?>" id="program-heads">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Manage Program Heads</h3>
                                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addProgramHeadModal">
                                    <i class="fas fa-plus"></i> Add Program Head
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="programHeadsTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Username</th>
                                            <th>Department</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $program_heads->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= $row['name'] ?></td>
                                            <td><?= $row['username'] ?></td>
                                            <td><?= $row['dept_name'] ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editProgramHeadModal<?= $row['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteProgramHeadModal<?= $row['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Program Head Modal -->
                                        <div class="modal fade" id="editProgramHeadModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Program Head</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="program-heads">
                                                            <div class="form-group">
                                                                <label>Full Name</label>
                                                                <input type="text" name="name" class="form-control" value="<?= $row['name'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Username</label>
                                                                <input type="text" name="username" class="form-control" value="<?= $row['username'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Department</label>
                                                                <select name="department_id" class="form-control" required>
                                                                    <?php 
                                                                    $departments->data_seek(0);
                                                                    while($dept = $departments->fetch_assoc()): 
                                                                    ?>
                                                                    <option value="<?= $dept['id'] ?>" <?= $row['department_id'] == $dept['id'] ? 'selected' : '' ?>>
                                                                        <?= $dept['dept_name'] ?>
                                                                    </option>
                                                                    <?php endwhile; ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <button type="button" class="btn btn-sm btn-info" id="togglePasswordChangePH<?= $row['id'] ?>">Change Password</button>
                                                            </div>
                                                            <div id="passwordChangeFieldsPH<?= $row['id'] ?>" style="display: none;">
                                                                <div class="form-group">
                                                                    <label>New Password</label>
                                                                    <div class="input-group">
                                                                        <input type="password" name="new_password" class="form-control" id="newPasswordPH<?= $row['id'] ?>">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text password-toggle" onclick="togglePassword('newPasswordPH<?= $row['id'] ?>')">
                                                                                <i class="fas fa-eye"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Confirm Password</label>
                                                                    <div class="input-group">
                                                                        <input type="password" name="confirm_password" class="form-control" id="confirmPasswordPH<?= $row['id'] ?>">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text password-toggle" onclick="togglePassword('confirmPasswordPH<?= $row['id'] ?>')">
                                                                                <i class="fas fa-eye"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="edit_program_head" class="btn btn-primary">Update Program Head</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Delete Program Head Modal -->
                                        <div class="modal fade" id="deleteProgramHeadModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Delete Program Head</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this program head?</p>
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="program-heads">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="delete_program_head" class="btn btn-danger">Delete Program Head</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Students Tab -->
                    <div class="tab-pane fade <?= $active_tab == 'students' ? 'show active' : '' ?>" id="students">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Manage Student Credentials</h3>
                                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addStudentModal">
                                    <i class="fas fa-plus"></i> Add Student Credentials
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="studentsTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID Number</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $students->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id_number'] ?></td>
                                            <td><?= $row['full_name'] ?></td>
                                            <td><?= $row['email'] ?? 'Not set' ?></td>
                                            <td>
                                                <?php if($row['email']): ?>
                                                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editStudentModal<?= $row['id'] ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteStudentModal<?= $row['id'] ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addStudentCredentialsModal<?= $row['id'] ?>">
                                                        <i class="fas fa-plus"></i> Set Credentials
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Student Modal -->
                                        <?php if($row['email']): ?>
                                        <div class="modal fade" id="editStudentModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Student Credentials</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="students">
                                                            <div class="form-group">
                                                                <label>Student</label>
                                                                <input type="text" class="form-control" value="<?= $row['full_name'] ?>" readonly>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Email</label>
                                                                <input type="email" name="email" class="form-control" value="<?= $row['email'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <button type="button" class="btn btn-sm btn-info" id="togglePasswordChangeStudent<?= $row['id'] ?>">Change Password</button>
                                                            </div>
                                                            <div id="passwordChangeFieldsStudent<?= $row['id'] ?>" style="display: none;">
                                                                <div class="form-group">
                                                                    <label>New Password</label>
                                                                    <div class="input-group">
                                                                        <input type="password" name="new_password" class="form-control" id="newPasswordStudent<?= $row['id'] ?>">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text password-toggle" onclick="togglePassword('newPasswordStudent<?= $row['id'] ?>')">
                                                                                <i class="fas fa-eye"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Confirm Password</label>
                                                                    <div class="input-group">
                                                                        <input type="password" name="confirm_password" class="form-control" id="confirmPasswordStudent<?= $row['id'] ?>">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text password-toggle" onclick="togglePassword('confirmPasswordStudent<?= $row['id'] ?>')">
                                                                                <i class="fas fa-eye"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="edit_student" class="btn btn-primary">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Delete Student Modal -->
                                        <?php if($row['email']): ?>
                                        <div class="modal fade" id="deleteStudentModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Remove Student Credentials</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to remove login credentials for <?= $row['full_name'] ?>?</p>
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="students">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="delete_student" class="btn btn-danger">Remove</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <!-- Add Student Credentials Modal -->
                                        <?php if(!$row['email']): ?>
                                        <div class="modal fade" id="addStudentCredentialsModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Set Student Credentials</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="student_id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="students">
                                                            <div class="form-group">
                                                                <label>Student</label>
                                                                <input type="text" class="form-control" value="<?= $row['full_name'] ?>" readonly>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Email</label>
                                                                <input type="email" name="email" class="form-control" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Password</label>
                                                                <div class="input-group">
                                                                    <input type="password" name="password" class="form-control" id="addStudentPassword<?= $row['id'] ?>" required>
                                                                    <div class="input-group-append">
                                                                        <span class="input-group-text password-toggle" onclick="togglePassword('addStudentPassword<?= $row['id'] ?>')">
                                                                            <i class="fas fa-eye"></i>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="add_student" class="btn btn-primary">Set</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Users Tab -->
                    <div class="tab-pane fade <?= $active_tab == 'users' ? 'show active' : '' ?>" id="users">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Manage System Users</h3>
                                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addUserModal">
                                    <i class="fas fa-plus"></i> Add User
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="usersTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Full Name</th>
                                            <th>Role</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= $row['username'] ?></td>
                                            <td><?= $row['fullname'] ?? 'Not set' ?></td>
                                            <td><?= $row['role'] ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editUserModal<?= $row['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteUserModal<?= $row['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit User Modal -->
                                        <div class="modal fade" id="editUserModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit User</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="users">
                                                            <div class="form-group">
                                                                <label>Username</label>
                                                                <input type="text" name="username" class="form-control" value="<?= $row['username'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Full Name</label>
                                                                <input type="text" name="fullname" class="form-control" value="<?= $row['fullname'] ?? '' ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Role</label>
                                                                <select name="role" class="form-control" required>
                                                                    <option value="admin" <?= $row['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                                                    <option value="cashier" <?= $row['role'] == 'cashier' ? 'selected' : '' ?>>Cashier</option>
                                                                    <option value="registrar" <?= $row['role'] == 'registrar' ? 'selected' : '' ?>>Registrar</option>
                                                                    <option value="treasurer" <?= $row['role'] == 'treasurer' ? 'selected' : '' ?>>Treasurer</option>
                                                                    <option value="counselor" <?= $row['role'] == 'counselor' ? 'selected' : '' ?>>Counselor</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <button type="button" class="btn btn-sm btn-info" id="togglePasswordChangeUser<?= $row['id'] ?>">Change Password</button>
                                                            </div>
                                                            <div id="passwordChangeFieldsUser<?= $row['id'] ?>" style="display: none;">
                                                                <div class="form-group">
                                                                    <label>New Password</label>
                                                                    <div class="input-group">
                                                                        <input type="password" name="new_password" class="form-control" id="newPasswordUser<?= $row['id'] ?>">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text password-toggle" onclick="togglePassword('newPasswordUser<?= $row['id'] ?>')">
                                                                                <i class="fas fa-eye"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Confirm Password</label>
                                                                    <div class="input-group">
                                                                        <input type="password" name="confirm_password" class="form-control" id="confirmPasswordUser<?= $row['id'] ?>">
                                                                        <div class="input-group-append">
                                                                            <span class="input-group-text password-toggle" onclick="togglePassword('confirmPasswordUser<?= $row['id'] ?>')">
                                                                                <i class="fas fa-eye"></i>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="edit_user" class="btn btn-primary">Update User</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Delete User Modal -->
                                        <div class="modal fade" id="deleteUserModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Delete User</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this user?</p>
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="users">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="delete_user" class="btn btn-danger">Delete User</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Courses Tab -->
                    <div class="tab-pane fade <?= $active_tab == 'courses' ? 'show active' : '' ?>" id="courses">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Manage Courses</h3>
                                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addCourseModal">
                                    <i class="fas fa-plus"></i> Add Course
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="coursesTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Course Name</th>
                                            <th>Level</th>
                                            <th>Department</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $courses->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= $row['coursename'] ?></td>
                                            <td><?= $row['courselevel'] ?></td>
                                            <td><?= $row['dept_name'] ?></td>
                                            <td><?= $row['coursedescription'] ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editCourseModal<?= $row['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteCourseModal<?= $row['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Course Modal -->
                                        <div class="modal fade" id="editCourseModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Course</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="courses">
                                                            <div class="form-group">
                                                                <label>Course Name</label>
                                                                <input type="text" name="coursename" class="form-control" value="<?= $row['coursename'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Course Level</label>
                                                                <input type="text" name="courselevel" class="form-control" value="<?= $row['courselevel'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Department</label>
                                                                <select name="department_id" class="form-control" required>
                                                                    <?php 
                                                                    $departments->data_seek(0);
                                                                    while($dept = $departments->fetch_assoc()): 
                                                                    ?>
                                                                    <option value="<?= $dept['id'] ?>" <?= $row['department_id'] == $dept['id'] ? 'selected' : '' ?>>
                                                                        <?= $dept['dept_name'] ?>
                                                                    </option>
                                                                    <?php endwhile; ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Description</label>
                                                                <textarea name="coursedescription" class="form-control" required><?= $row['coursedescription'] ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="edit_course" class="btn btn-primary">Update Course</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Delete Course Modal -->
                                        <div class="modal fade" id="deleteCourseModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Delete Course</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this course?</p>
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="courses">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="delete_course" class="btn btn-danger">Delete Course</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Departments Tab -->
                    <div class="tab-pane fade <?= $active_tab == 'departments' ? 'show active' : '' ?>" id="departments">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Manage Departments</h3>
                                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addDepartmentModal">
                                    <i class="fas fa-plus"></i> Add Department
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="departmentsTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Department Name</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        // Reset the data pointer to the beginning
                                        $departments->data_seek(0);
                                        while($row = $departments->fetch_assoc()): 
                                        ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= $row['dept_name'] ?></td>
                                            <td><?= $row['dept_description'] ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editDepartmentModal<?= $row['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteDepartmentModal<?= $row['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Department Modal -->
                                        <div class="modal fade" id="editDepartmentModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Department</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="departments">
                                                            <div class="form-group">
                                                                <label>Department Name</label>
                                                                <input type="text" name="dept_name" class="form-control" value="<?= $row['dept_name'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Description</label>
                                                                <textarea name="dept_description" class="form-control" required><?= $row['dept_description'] ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="edit_department" class="btn btn-primary">Update Department</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Delete Department Modal -->
                                        <div class="modal fade" id="deleteDepartmentModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Delete Department</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this department?</p>
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="departments">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="delete_department" class="btn btn-danger">Delete Department</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Subjects Tab -->
                    <div class="tab-pane fade <?= $active_tab == 'subjects' ? 'show active' : '' ?>" id="subjects">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Manage Subjects</h3>
                                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addSubjectModal">
                                    <i class="fas fa-plus"></i> Add Subject
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="subjectsTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Subject Code</th>
                                            <th>Description</th>
                                            <th>Units</th>
                                            <th>Course</th>
                                            <th>Academic Year</th>
                                            <th>Semester</th>
                                            <th>Year Level</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $subjects->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= $row['subject_code'] ?></td>
                                            <td><?= $row['subject_description'] ?></td>
                                            <td><?= $row['unit'] ?></td>
                                            <td><?= $row['coursename'] ?> (<?= $row['courselevel'] ?>)</td>
                                            <td><?= $row['academic_year'] ?></td>
                                            <td><?= $row['semester'] ?></td>
                                            <td><?= $row['year_level'] ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editSubjectModal<?= $row['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteSubjectModal<?= $row['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Subject Modal -->
                                        <div class="modal fade" id="editSubjectModal<?= $row['id'] ?>">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Subject</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="subjects">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Subject Code</label>
                                                                        <input type="text" name="subject_code" class="form-control" value="<?= $row['subject_code'] ?>" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Units</label>
                                                                        <input type="number" name="unit" class="form-control" value="<?= $row['unit'] ?>" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Description</label>
                                                                <textarea name="subject_description" class="form-control" required><?= $row['subject_description'] ?></textarea>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Pre-requisite</label>
                                                                        <input type="text" name="pre_requisite" class="form-control" value="<?= $row['pre_requisite'] ?>">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Course</label>
                                                                        <select name="course_id" class="form-control" required>
                                                                            <?php 
                                                                            $courses->data_seek(0);
                                                                            while($course = $courses->fetch_assoc()): 
                                                                            ?>
                                                                            <option value="<?= $course['id'] ?>" <?= $row['course_id'] == $course['id'] ? 'selected' : '' ?>>
                                                                                <?= $course['coursename'] ?> (<?= $course['courselevel'] ?>)
                                                                            </option>
                                                                            <?php endwhile; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>Academic Year</label>
                                                                        <input type="text" name="academic_year" class="form-control" value="<?= $row['academic_year'] ?>" placeholder="YYYY-YYYY" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>Semester</label>
                                                                        <select name="semester" class="form-control" required>
                                                                            <option value="1st" <?= $row['semester'] == '1st' ? 'selected' : '' ?>>1st Semester</option>
                                                                            <option value="2nd" <?= $row['semester'] == '2nd' ? 'selected' : '' ?>>2nd Semester</option>
                                                                            <option value="Summer" <?= $row['semester'] == 'Summer' ? 'selected' : '' ?>>Summer</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>Year Level</label>
                                                                        <select name="year_level" class="form-control" required>
                                                                            <option value="1st Year" <?= $row['year_level'] == '1st Year' ? 'selected' : '' ?>>1st Year</option>
                                                                            <option value="2nd Year" <?= $row['year_level'] == '2nd Year' ? 'selected' : '' ?>>2nd Year</option>
                                                                            <option value="3rd Year" <?= $row['year_level'] == '3rd Year' ? 'selected' : '' ?>>3rd Year</option>
                                                                            <option value="4th Year" <?= $row['year_level'] == '4th Year' ? 'selected' : '' ?>>4th Year</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>Effective Year</label>
                                                                        <input type="text" name="effective_year" class="form-control" value="<?= $row['effective_year'] ?>" placeholder="YYYY" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="edit_subject" class="btn btn-primary">Update Subject</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Delete Subject Modal -->
                                        <div class="modal fade" id="deleteSubjectModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Delete Subject</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this subject?</p>
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="subjects">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="delete_subject" class="btn btn-danger">Delete Subject</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- Academic Year Tab -->
                    <div class="tab-pane fade <?= $active_tab == 'academic-year' ? 'show active' : '' ?>" id="academic-year">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Academic Year Management</h3>
                                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addAcademicYearModal">
                                    <i class="fas fa-plus"></i> Add Year
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-success">
                                    <strong>Active Academic Year:</strong> 
                                    <?= $active_year ? $active_year['academic_year'] : 'Not set' ?>
                                </div>
                                <table id="academicYearsTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Academic Year</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $academic_years->data_seek(0);
                                        while($row = $academic_years->fetch_assoc()): 
                                        ?>
                                        <tr>
                                            <td><?= $row['academic_year'] ?></td>
                                            <td>
                                                <form method="POST" style="display: inline-block;">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <input type="hidden" name="current_tab" value="academic-year">
                                                    <input type="hidden" name="toggle_academic_year" value="1">
                                                    <input type="hidden" name="is_active" value="<?= $row['is_active'] ? 0 : 1 ?>">
                                                    <button type="submit" class="btn btn-sm <?= $row['is_active'] ? 'btn-success' : 'btn-secondary' ?>">
                                                        <i class="fas fa-power-off"></i> 
                                                        <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editAcademicYearModal<?= $row['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteAcademicYearModal<?= $row['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Academic Year Modal -->
                                        <div class="modal fade" id="editAcademicYearModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Academic Year</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="academic-year">
                                                            <div class="form-group">
                                                                <label>Academic Year</label>
                                                                <input type="text" name="academic_year" class="form-control" value="<?= $row['academic_year'] ?>" placeholder="YYYY-YYYY" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="edit_academic_year" class="btn btn-primary">Update Year</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Delete Academic Year Modal -->
                                        <div class="modal fade" id="deleteAcademicYearModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Delete Academic Year</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this academic year?</p>
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="academic-year">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="delete_academic_year" class="btn btn-danger">Delete Year</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Banks Tab -->
                    <div class="tab-pane fade <?= $active_tab == 'banks' ? 'show active' : '' ?>" id="banks">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Manage Banks</h3>
                                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addBankModal">
                                    <i class="fas fa-plus"></i> Add Bank
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="banksTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Bank Name</th>
                                            <th>Branch</th>
                                            <th>Account Number</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $banks->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= $row['bank_name'] ?></td>
                                            <td><?= $row['branch'] ?></td>
                                            <td><?= $row['account_number'] ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editBankModal<?= $row['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteBankModal<?= $row['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Bank Modal -->
                                        <div class="modal fade" id="editBankModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Bank</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="banks">
                                                            <div class="form-group">
                                                                <label>Bank Name</label>
                                                                <input type="text" name="bank_name" class="form-control" value="<?= $row['bank_name'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Branch</label>
                                                                <input type="text" name="branch" class="form-control" value="<?= $row['branch'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Account Number</label>
                                                                <input type="text" name="account_number" class="form-control" value="<?= $row['account_number'] ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="edit_bank" class="btn btn-primary">Update Bank</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Delete Bank Modal -->
                                        <div class="modal fade" id="deleteBankModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Delete Bank</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this bank?</p>
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="banks">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="delete_bank" class="btn btn-danger">Delete Bank</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Services Tab -->
                    <div class="tab-pane fade <?= $active_tab == 'services' ? 'show active' : '' ?>" id="services">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Manage Services</h3>
                                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#addServiceModal">
                                    <i class="fas fa-plus"></i> Add Service
                                </button>
                            </div>
                            <div class="card-body">
                                <table id="servicesTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $services->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= $row['name'] ?></td>
                                            <td><?= $row['description'] ?></td>
                                            <td><?= number_format($row['price'], 2) ?></td>
                                            <td>
                                                <form method="POST" style="display: inline-block;">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <input type="hidden" name="current_tab" value="services">
                                                    <input type="hidden" name="toggle_service" value="1">
                                                    <input type="hidden" name="is_active" value="<?= $row['is_active'] ? 0 : 1 ?>">
                                                    <button type="submit" class="btn btn-sm <?= $row['is_active'] ? 'btn-success' : 'btn-secondary' ?>">
                                                        <i class="fas fa-power-off"></i> 
                                                        <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editServiceModal<?= $row['id'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteServiceModal<?= $row['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Service Modal -->
                                        <div class="modal fade" id="editServiceModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Service</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="services">
                                                            <div class="form-group">
                                                                <label>Service Name</label>
                                                                <input type="text" name="name" class="form-control" value="<?= $row['name'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Description</label>
                                                                <textarea name="description" class="form-control" required><?= $row['description'] ?></textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Price</label>
                                                                <input type="number" step="0.01" name="price" class="form-control" value="<?= $row['price'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <div class="icheck-primary d-inline">
                                                                    <input type="checkbox" id="editActive<?= $row['id'] ?>" name="is_active" value="1" <?= $row['is_active'] ? 'checked' : '' ?>>
                                                                    <label for="editActive<?= $row['id'] ?>">
                                                                        Active
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="edit_service" class="btn btn-primary">Update Service</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Delete Service Modal -->
                                        <div class="modal fade" id="deleteServiceModal<?= $row['id'] ?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Delete Service</h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this service?</p>
                                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                            <input type="hidden" name="current_tab" value="services">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="delete_service" class="btn btn-danger">Delete Service</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reports Tab -->
                    <div class="tab-pane fade <?= $active_tab == 'reports' ? 'show active' : '' ?>" id="reports">
                        <div class="card card-success">
                            <div class="card-header">
                                <h3 class="card-title">Reports</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="report-button bg-primary">
                                            <i class="fas fa-book"></i>
                                            <h4>Academic Curriculum</h4>
                                            <p>Generate and print the complete academic curriculum</p>
                                            <a href="printcurriculum.php" target="_blank" class="btn btn-light mt-3">
                                                <i class="fas fa-print"></i> Print Curriculum
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="report-button bg-info">
                                            <i class="fas fa-users"></i>
                                            <h4>User List</h4>
                                            <p>Generate and print the list of all system users</p>
                                            <a href="printuserlist.php" target="_blank" class="btn btn-light mt-3">
                                                <i class="fas fa-print"></i> Print User List
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modals -->
    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h4 class="modal-title">Add New User</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="current_tab" value="users">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="addUserPassword" required>
                                <div class="input-group-append">
                                    <span class="input-group-text password-toggle" onclick="togglePassword('addUserPassword')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select name="role" class="form-control" required>
                                <option value="admin">Admin</option>
                                <option value="cashier">Cashier</option>
                                <option value="registrar">Registrar</option>
                                 <option value="treasurer">Treasurer</option>
                                 <option value="counselor">Counselor</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Teacher Modal -->
    <div class="modal fade" id="addTeacherModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h4 class="modal-title">Add New Teacher</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="current_tab" value="teachers">
                        <div class="form-group">
                            <label>Employee ID</label>
                            <input type="text" name="employee_id" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="addTeacherPassword" required>
                                <div class="input-group-append">
                                    <span class="input-group-text password-toggle" onclick="togglePassword('addTeacherPassword')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_teacher" class="btn btn-primary">Add Teacher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Program Head Modal -->
    <div class="modal fade" id="addProgramHeadModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h4 class="modal-title">Add New Program Head</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="current_tab" value="program-heads">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="addProgramHeadPassword" required>
                                <div class="input-group-append">
                                    <span class="input-group-text password-toggle" onclick="togglePassword('addProgramHeadPassword')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department_id" class="form-control" required>
                                <?php 
                                $departments->data_seek(0);
                                while($dept = $departments->fetch_assoc()): 
                                ?>
                                <option value="<?= $dept['id'] ?>">
                                    <?= $dept['dept_name'] ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_program_head" class="btn btn-primary">Add Program Head</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Student Credentials</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="current_tab" value="students">
                        <div class="form-group">
                            <label>Select Student</label>
                            <select name="student_id" class="form-control" required>
                                <?php 
                                $students->data_seek(0);
                                while($student = $students->fetch_assoc()): 
                                    if(!$student['email']): // Only show students without credentials
                                ?>
                                <option value="<?= $student['id'] ?>">
                                    <?= $student['full_name'] ?> (<?= $student['id_number'] ?>)
                                </option>
                                <?php 
                                    endif;
                                endwhile; 
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="addStudentPassword" required>
                                <div class="input-group-append">
                                    <span class="input-group-text password-toggle" onclick="togglePassword('addStudentPassword')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_student" class="btn btn-primary">Add Credentials</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Course Modal -->
    <div class="modal fade" id="addCourseModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h4 class="modal-title">Add New Course</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="current_tab" value="courses">
                        <div class="form-group">
                            <label>Course Name</label>
                            <input type="text" name="coursename" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Course Level</label>
                            <input type="text" name="courselevel" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department_id" class="form-control" required>
                                <?php 
                                $departments->data_seek(0);
                                while($dept = $departments->fetch_assoc()): 
                                ?>
                                <option value="<?= $dept['id'] ?>">
                                    <?= $dept['dept_name'] ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="coursedescription" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_course" class="btn btn-primary">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Add Department Modal -->
    <div class="modal fade" id="addDepartmentModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h4 class="modal-title">Add New Department</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="current_tab" value="departments">
                        <div class="form-group">
                            <label>Department Name</label>
                            <input type="text" name="dept_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="dept_description" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_department" class="btn btn-primary">Add Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Add Subject Modal -->
    <div class="modal fade" id="addSubjectModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h4 class="modal-title">Add New Subject</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="current_tab" value="subjects">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Subject Code</label>
                                    <input type="text" name="subject_code" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Units</label>
                                    <input type="number" name="unit" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="subject_description" class="form-control" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Pre-requisite</label>
                                    <input type="text" name="pre_requisite" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Course</label>
                                    <select name="course_id" class="form-control" required>
                                        <?php 
                                        $courses->data_seek(0);
                                        while($course = $courses->fetch_assoc()): 
                                        ?>
                                        <option value="<?= $course['id'] ?>">
                                            <?= $course['coursename'] ?> (<?= $course['courselevel'] ?>)
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Academic Year</label>
                                    <input type="text" name="academic_year" class="form-control" placeholder="YYYY-YYYY" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Semester</label>
                                    <select name="semester" class="form-control" required>
                                        <option value="1st">1st Semester</option>
                                        <option value="2nd">2nd Semester</option>
                                        <option value="Summer">Summer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Year Level</label>
                                    <select name="year_level" class="form-control" required>
                                        <option value="1st Year">1st Year</option>
                                        <option value="2nd Year">2nd Year</option>
                                        <option value="3rd Year">3rd Year</option>
                                        <option value="4th Year">4th Year</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Effective Year</label>
                                    <input type="text" name="effective_year" class="form-control" placeholder="YYYY" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_subject" class="btn btn-primary">Add Subject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Add Academic Year Modal -->
    <div class="modal fade" id="addAcademicYearModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Academic Year</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="current_tab" value="academic-year">
                        <div class="form-group">
                            <label>Academic Year</label>
                            <input type="text" name="academic_year" class="form-control" placeholder="YYYY-YYYY" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_academic_year" class="btn btn-primary">Add Year</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Bank Modal -->
    <div class="modal fade" id="addBankModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h4 class="modal-title">Add New Bank</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="current_tab" value="banks">
                        <div class="form-group">
                            <label>Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Branch</label>
                            <input type="text" name="branch" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Account Number</label>
                            <input type="text" name="account_number" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_bank" class="btn btn-primary">Add Bank</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h4 class="modal-title">Add New Service</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="current_tab" value="services">
                        <div class="form-group">
                            <label>Service Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Price</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <div class="icheck-primary d-inline">
                                <input type="checkbox" id="addActive" name="is_active" value="1" checked>
                                <label for="addActive">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_service" class="btn btn-primary">Add Service</button>
                    </div>
                </form>
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
    // Initialize DataTables
    $('#usersTable, #teachersTable, #programHeadsTable, #studentsTable, #coursesTable, #departmentsTable, #subjectsTable, #academicYearsTable, #banksTable, #servicesTable').DataTable({
        "responsive": true,
        "autoWidth": false,
    });
    
    // Track active tab
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var tabId = $(e.target).attr('href').substring(1);
        $.post('', {active_tab: tabId});
    });
    
    // Show toast notification if exists
    <?php if(isset($_SESSION['toast_message'])): ?>
    showToast("<?= $_SESSION['toast_message'] ?>", "<?= $_SESSION['toast_type'] ?>");
    <?php 
    unset($_SESSION['toast_message']);
    unset($_SESSION['toast_type']);
    endif; 
    ?>
    
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
    
    // Toggle password visibility
    window.togglePassword = function(id) {
        const passwordField = document.getElementById(id);
        const toggleIcon = passwordField.nextElementSibling.querySelector('i');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordField.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    };
    
    // Toggle password change fields for users
    <?php 
    $users->data_seek(0);
    while($row = $users->fetch_assoc()): 
    ?>
    $('#togglePasswordChangeUser<?= $row['id'] ?>').click(function() {
        $('#passwordChangeFieldsUser<?= $row['id'] ?>').toggle();
    });
    <?php endwhile; ?>
    
    // Toggle password change fields for teachers
    <?php 
    $teachers->data_seek(0);
    while($row = $teachers->fetch_assoc()): 
    ?>
    $('#togglePasswordChangeTeacher<?= $row['id'] ?>').click(function() {
        $('#passwordChangeFieldsTeacher<?= $row['id'] ?>').toggle();
    });
    <?php endwhile; ?>
    
    // Toggle password change fields for program heads
    <?php 
    $program_heads->data_seek(0);
    while($row = $program_heads->fetch_assoc()): 
    ?>
    $('#togglePasswordChangePH<?= $row['id'] ?>').click(function() {
        $('#passwordChangeFieldsPH<?= $row['id'] ?>').toggle();
    });
    <?php endwhile; ?>
    
    // Toggle password change fields for students
    <?php 
    $students->data_seek(0);
    while($row = $students->fetch_assoc()): 
        if($row['email']): // Only for students with existing credentials
    ?>
    $('#togglePasswordChangeStudent<?= $row['id'] ?>').click(function() {
        $('#passwordChangeFieldsStudent<?= $row['id'] ?>').toggle();
    });
    <?php 
        endif;
    endwhile; 
    ?>
});
</script>
</body>
</html>