<?php
include 'config.php';

// 1. Handle AJAX Request for Edit Data
if (isset($_GET['get_staff_id'])) {
    $staff_id = intval($_GET['get_staff_id']);
    $query = "SELECT * FROM staff WHERE staff_id = $staff_id";
    $result = $conn->query($query);
    
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Staff not found']);
    }
    exit();
}

// 2. Check Session & Role
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

 $user_id = $_SESSION['user_id'];
 $user_role = $_SESSION['role'];
 $user = $conn->query("SELECT * FROM staff WHERE staff_id = $user_id")->fetch_assoc();

if ($user_role != 'Admin') {
    die("Access Denied.");
}

// 3. Handle File Upload
function handlePhotoUpload($file_input_name, $target_dir = "uploads/staff_photos/") {
    if (!isset($_FILES[$file_input_name]) || $_FILES[$file_input_name]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $file = $_FILES[$file_input_name];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, array('jpg', 'jpeg', 'png', 'gif'))) {
        return null;
    }
    $new_file_name = uniqid() . '.' . $file_ext;
    if (move_uploaded_file($file['tmp_name'], $target_dir . $new_file_name)) {
        return $target_dir . $new_file_name;
    }
    return null;
}

// 4. Handle POST Actions
 $success_message = '';
 $error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_type'])) {
    switch ($_POST['form_type']) {
        case 'add_staff':
        case 'update_staff':
            $is_update = ($_POST['form_type'] == 'update_staff');
            $staff_id = isset($_POST['staff_id']) ? $_POST['staff_id'] : 0;
            
            $full_name = $_POST['full_name'];
            $role = $_POST['role'];
            $email = $_POST['email'];
            $contact_number = $_POST['contact_number'];
            $username = $_POST['username'];
            $office_hours = $_POST['office_hours'];
            
            $photo_path = handlePhotoUpload('staff_photo');
            
            if ($is_update) {
                // Update Logic
                $update_fields = [
                    "full_name = '$full_name'",
                    "role = '$role'",
                    "email = '$email'",
                    "contact_number = '$contact_number'",
                    "username = '$username'",
                    "office_hours = '$office_hours'"
                ];
                if ($photo_path) $update_fields[] = "photo = '$photo_path'";
                if (!empty($_POST['password'])) {
                    $update_fields[] = "password_hash = '" . password_hash($_POST['password'], PASSWORD_DEFAULT) . "'";
                }
                
                $query = "UPDATE staff SET " . implode(', ', $update_fields) . " WHERE staff_id = $staff_id";
                $action = "Updated staff member: $full_name ($role)";
            } else {
                // Add Logic
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                if (!$photo_path) $photo_path = 'dist/img/user2-160x160.jpg';
                
                $query = "INSERT INTO staff (full_name, role, email, contact_number, username, password_hash, office_hours, photo) 
                          VALUES ('$full_name', '$role', '$email', '$contact_number', '$username', '$password', '$office_hours', '$photo_path')";
                $action = "Added new staff member: $full_name ($role)";
            }

            if ($conn->query($query)) {
                $stmt = $conn->prepare("INSERT INTO system_logs (staff_id, action) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, $action);
                $stmt->execute();
                $success_message = $is_update ? "Staff updated successfully!" : "Staff added successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
            break;
            
        case 'delete_staff':
            $staff_id = $_POST['staff_id'];
            $staff_data = $conn->query("SELECT * FROM staff WHERE staff_id = $staff_id")->fetch_assoc();
            if ($staff_data) {
                $conn->query("DELETE FROM staff WHERE staff_id = $staff_id");
                $stmt = $conn->prepare("INSERT INTO system_logs (staff_id, action) VALUES (?, ?)");
                $stmt->bind_param("is", $user_id, "Deleted staff: {$staff_data['full_name']}");
                $stmt->execute();
                $success_message = "Staff deleted successfully!";
            }
            break;
    }
}

// 5. Data Fetching
 $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
 $staff_count = $conn->query("SELECT COUNT(*) as count FROM staff")->fetch_assoc()['count'];
 $logs_count = $conn->query("SELECT COUNT(*) as count FROM system_logs")->fetch_assoc()['count'];
 $logs = $conn->query("SELECT sl.*, s.full_name as staff_name FROM system_logs sl LEFT JOIN staff s ON sl.staff_id = s.staff_id ORDER BY sl.log_timestamp DESC LIMIT 100");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        /* Theme Overrides */
        .main-header { background-color: #1a3a5f; border-bottom: 1px solid #0f2238; }
        .main-sidebar { background-color: #1a3a5f; }
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active { background-color: #d32f2f; color: #fff; }
        .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link:hover { background-color: rgba(211, 47, 47, 0.2); color: #fff; }
        .card-header { background-color: #1a3a5f; color: #fff; }
        .btn-primary { background-color: #1a3a5f; border-color: #1a3a5f; }
        .btn-primary:hover { background-color: #0f2238; }
        .btn-danger { background-color: #d32f2f; border-color: #d32f2f; }
        .main-footer { background-color: #1a3a5f; color: #fff; border-top: 1px solid #0f2238; }
        
        /* Toast Notifications */
        .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; }
        .toast { min-width: 300px; margin-bottom: 10px; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); padding: 15px; display: flex; align-items: center; opacity: 0; transform: translateX(100%); transition: opacity 0.3s, transform 0.3s; }
        .toast.show { opacity: 1; transform: translateX(0); }
        .toast-success { background-color: #00a65a; color: white; }
        .toast-error { background-color: #d32f2f; color: white; }
        .toast-icon { margin-right: 10px; font-size: 18px; }
        .toast-message { flex-grow: 1; }
        .toast-close { background: none; border: none; color: white; opacity: 0.7; cursor: pointer; font-size: 16px; margin-left: 10px; }

        /* Split Layout Styles */
        .staff-layout-row { display: flex; flex-wrap: wrap; margin-right: -10px; margin-left: -10px; }
        .staff-layout-col { padding-right: 10px; padding-left: 10px; }
        .form-col { flex: 0 0 33.333333%; max-width: 33.333333%; }
        .table-col { flex: 0 0 66.666667%; max-width: 66.666667%; }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .form-col, .table-col { flex: 0 0 100%; max-width: 100%; margin-bottom: 20px; }
        }

        /* Form Styling */
        .photo-preview { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto; }
        .left-panel-card { height: 100%; min-height: 600px; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
            <li class="nav-item d-none d-sm-inline-block"><a href="admin.php" class="nav-link">Home</a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
             <li class="nav-item"><a class="nav-link" href="#" data-widget="fullscreen"><i class="fas fa-expand-arrows-alt"></i></a></li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="admin.php" class="brand-link">
            <img src="uploads/csr.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">Admin Dashboard</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="<?php echo !empty($user['photo']) ? $user['photo'] : 'dist/img/user2-160x160.jpg'; ?>" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block text-white"><?php echo $user['full_name']; ?></a>
                    <a href="#" class="d-block text-white small"><?php echo $user['role']; ?></a>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="?tab=dashboard" class="nav-link <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?tab=staff" class="nav-link <?php echo $active_tab == 'staff' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-user-tie"></i><p>Staff Management</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?tab=logs" class="nav-link <?php echo $active_tab == 'logs' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-history"></i><p>System Logs</p>
                        </a>
                    </li>
                    <li class="nav-item">
                         <a href="logout.php" class="nav-link text-danger">
                            <i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Toast Container -->
        <div class="toast-container" id="toastContainer"></div>
        
        <div class="tab-content">
            <!-- Dashboard Tab -->
            <div class="tab-pane <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>" id="dashboard-tab">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6"><h1 class="m-0">Dashboard</h1></div>
                        </div>
                    </div>
                </div>
                <section class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-6 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner"><h3><?php echo $staff_count; ?></h3><p>Total Staff</p></div>
                                    <div class="icon"><i class="fas fa-user-tie"></i></div>
                                    <a href="?tab=staff" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                                </div>
                            </div>
                            <div class="col-lg-6 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner"><h3><?php echo $logs_count; ?></h3><p>System Logs</p></div>
                                    <div class="icon"><i class="fas fa-history"></i></div>
                                    <a href="?tab=logs" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Staff Management Tab (SPLIT LAYOUT) -->
            <div class="tab-pane <?php echo $active_tab == 'staff' ? 'active' : ''; ?>" id="staff-tab">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6"><h1 class="m-0">Staff Management</h1></div>
                        </div>
                    </div>
                </div>
                <section class="content">
                    <div class="container-fluid">
                        <div class="staff-layout-row">
                            
                            <!-- LEFT COLUMN: FORM -->
                            <div class="staff-layout-col form-col">
                                <div class="card left-panel-card">
                                    <div class="card-header">
                                        <h3 class="card-title" id="formTitle">Add New Staff</h3>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" onclick="resetForm()" title="Reset Form"><i class="fas fa-undo"></i></button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <form action="admin.php?tab=staff" method="post" enctype="multipart/form-data" id="staffForm">
                                            <input type="hidden" name="form_type" id="form_type" value="add_staff">
                                            <input type="hidden" name="staff_id" id="staff_id">
                                            
                                            <!-- Photo -->
                                            <div class="text-center mb-3">
                                                <img id="form_photo_preview" src="dist/img/user2-160x160.jpg" class="photo-preview" alt="Photo Preview">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="form_staff_photo" name="staff_photo" accept="image/*">
                                                    <label class="custom-file-label" for="form_staff_photo">Choose photo</label>
                                                </div>
                                            </div>

                                            <!-- Name & Role -->
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <input type="text" class="form-control" name="full_name" id="form_full_name" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Role</label>
                                                <select class="form-control" name="role" id="form_role" required>
                                                    <option value="Admin">Admin</option>
                                                    <option value="Guidance Counselor">Guidance Counselor</option>
                                                    <option value="Staff">Staff</option>
                                                </select>
                                            </div>

                                            <!-- Contact -->
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" class="form-control" name="email" id="form_email">
                                            </div>
                                            <div class="form-group">
                                                <label>Contact Number</label>
                                                <input type="text" class="form-control" name="contact_number" id="form_contact_number">
                                            </div>

                                            <!-- Account -->
                                            <div class="form-group">
                                                <label>Username</label>
                                                <input type="text" class="form-control" name="username" id="form_username" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Password <small id="passHelp" class="text-muted">(Required)</small></label>
                                                <input type="password" class="form-control" name="password" id="form_password">
                                            </div>
                                            <div class="form-group">
                                                <label>Office Hours</label>
                                                <input type="text" class="form-control" name="office_hours" id="form_office_hours">
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-block" id="formSubmitBtn"><i class="fas fa-save"></i> Save Staff</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT COLUMN: TABLE -->
                            <div class="staff-layout-col table-col">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Staff Directory</h3>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table id="staffTable" class="table table-striped table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px;">Photo</th>
                                                        <th>Name</th>
                                                        <th>Role</th>
                                                        <th>Email</th>
                                                        <th>Contact</th>
                                                        <th style="width: 120px;">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $staff = $conn->query("SELECT * FROM staff ORDER BY full_name");
                                                    while ($row = $staff->fetch_assoc()):
                                                    ?>
                                                    <tr>
                                                        <td class="text-center">
                                                            <img src="<?php echo !empty($row['photo']) ? $row['photo'] : 'dist/img/user2-160x160.jpg'; ?>" class="img-circle" width="35" height="35" alt="Photo">
                                                        </td>
                                                        <td><strong><?php echo $row['full_name']; ?></strong></td>
                                                        <td><span class="badge badge-info"><?php echo $row['role']; ?></span></td>
                                                        <td><?php echo $row['email']; ?></td>
                                                        <td><?php echo $row['contact_number']; ?></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <button class="btn btn-warning btn-sm edit-staff-btn" data-id="<?php echo $row['staff_id']; ?>" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <button class="btn btn-danger btn-sm delete-staff-btn" data-id="<?php echo $row['staff_id']; ?>" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
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

            <!-- System Logs Tab -->
            <div class="tab-pane <?php echo $active_tab == 'logs' ? 'active' : ''; ?>" id="logs-tab">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6"><h1 class="m-0">System Logs</h1></div>
                        </div>
                    </div>
                </div>
                <section class="content">
                    <div class="container-fluid">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">System Activity Logs</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="logsTable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Staff</th>
                                                <th>Action</th>
                                                <th>Timestamp</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $logs->data_seek(0);
                                            while ($row = $logs->fetch_assoc()):
                                            ?>
                                            <tr>
                                                <td><?php echo $row['staff_name'] ? $row['staff_name'] : 'System'; ?></td>
                                                <td><?php echo $row['action']; ?></td>
                                                <td><?php echo $row['log_timestamp']; ?></td>
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
        </div>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; <?php echo date('Y'); ?> Admin Dashboard.</strong> All rights reserved.
    </footer>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this staff member? This action cannot be undone.</p>
                <input type="hidden" id="delete_staff_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteStaff">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(function () {
        // Initialize DataTables
        $('#staffTable, #logsTable').DataTable({
            'paging': true, 'lengthChange': true, 'searching': true, 'ordering': true, 'info': true, 'autoWidth': false, 'responsive': true, 'pageLength': 10
        });
        
        // Toast Notification Function
        function showToast(message, type = 'success') {
            var iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            var toastHtml = `<div class="toast toast-${type}"><div class="toast-icon"><i class="${iconClass}"></i></div><div class="toast-message">${message}</div><button class="toast-close"><i class="fas fa-times"></i></button></div>`;
            $('#toastContainer').append(toastHtml);
            setTimeout(() => $('.toast').addClass('show'), 100);
            setTimeout(() => { $('.toast').removeClass('show'); setTimeout(() => $('.toast').remove(), 300); }, 5000);
        }

        <?php if ($success_message): ?>
            showToast('<?php echo $success_message; ?>', 'success');
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            showToast('<?php echo $error_message; ?>', 'error');
        <?php endif; ?>

        // --- EDIT FUNCTIONALITY ---
        $('#staffTable tbody').on('click', '.edit-staff-btn', function() {
            var staffId = $(this).data('id');
            
            $.ajax({
                url: 'admin.php',
                type: 'GET',
                data: { get_staff_id: staffId },
                dataType: 'json',
                success: function(response) {
                    if(response.error) {
                        showToast(response.error, 'error');
                        return;
                    }
                    
                    // Populate Form
                    $('#form_type').val('update_staff');
                    $('#staff_id').val(response.staff_id);
                    $('#form_full_name').val(response.full_name);
                    $('#form_role').val(response.role);
                    $('#form_email').val(response.email);
                    $('#form_contact_number').val(response.contact_number);
                    $('#form_username').val(response.username);
                    $('#form_office_hours').val(response.office_hours);
                    
                    // Photo
                    if(response.photo) {
                        $('#form_photo_preview').attr('src', response.photo);
                    } else {
                        $('#form_photo_preview').attr('src', 'dist/img/user2-160x160.jpg');
                    }
                    
                    // Update UI
                    $('#formTitle').text('Edit Staff: ' + response.full_name);
                    $('#formSubmitBtn').html('<i class="fas fa-save"></i> Update Staff');
                    $('#form_password').prop('required', false).val(''); // Password optional
                    $('#passHelp').text('(Leave blank to keep current)');

                    // On mobile, scroll to form
                    if ($(window).width() < 992) {
                        $('.left-panel-card')[0].scrollIntoView({behavior: "smooth"});
                    }
                },
                error: function() {
                    showToast('Error fetching staff data.', 'error');
                }
            });
        });

        // --- DELETE FUNCTIONALITY ---
        $('#staffTable tbody').on('click', '.delete-staff-btn', function() {
            var staffId = $(this).data('id');
            $('#delete_staff_id').val(staffId);
            var modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            modal.show();
        });

        $('#confirmDeleteStaff').on('click', function() {
            var staffId = $('#delete_staff_id').val();
            var form = $('<form>', { 'method': 'POST', 'action': 'admin.php?tab=staff' });
            form.append($('<input>', { 'type': 'hidden', 'name': 'form_type', 'value': 'delete_staff' }));
            form.append($('<input>', { 'type': 'hidden', 'name': 'staff_id', 'value': staffId }));
            form.appendTo('body').submit();
        });

        // --- RESET FORM FUNCTION ---
        window.resetForm = function() {
            $('#staffForm')[0].reset();
            $('#form_type').val('add_staff');
            $('#staff_id').val('');
            $('#formTitle').text('Add New Staff');
            $('#formSubmitBtn').html('<i class="fas fa-save"></i> Save Staff');
            $('#form_password').prop('required', true);
            $('#passHelp').text('(Required)');
            $('#form_photo_preview').attr('src', 'dist/img/user2-160x160.jpg');
        };

        // --- PHOTO PREVIEW ---
        $('#form_staff_photo').on('change', function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#form_photo_preview').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>
</body>
</html>