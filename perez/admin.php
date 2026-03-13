<?php
session_start();
require 'config.php'; 
requireLogin();      
checkRole(['admin']); 

// --- FLASH MESSAGE HELPER ---
function setFlash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

// --- LOGGING HELPER FUNCTION ---
function logActivity($pdo, $user_id, $action, $entity, $entity_id = null, $description = "") {
    try {
        // Fetch user details immediately to store with log for easier filtering later
        $stmtUser = $pdo->prepare("SELECT first_name, last_name, role FROM Users WHERE user_id = ?");
        $stmtUser->execute([$user_id]);
        $u = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        $userName = ($u) ? $u['first_name'] . ' ' . $u['last_name'] : 'Unknown';
        $userRole = ($u) ? $u['role'] : 'unknown';
        
        $ip = $_SERVER['REMOTE_ADDR'];
        $desc = htmlspecialchars($description); 

        $stmt = $pdo->prepare("INSERT INTO ActivityLogs (user_id, user_role, user_name, action_type, entity_type, entity_id, description, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $userRole, $userName, $action, $entity, $entity_id, $desc, $ip]);
    } catch (Exception $e) {
        error_log("Logging failed: " . $e->getMessage());
    }
}

// --- HANDLE POST REQUESTS (USER MANAGEMENT ONLY) ---

// 1. ADD USER
if (isset($_POST['add_user'])) {
    $fname = trim($_POST['first_name']);
    $lname = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $phone = trim($_POST['phone_number']);

    if (empty($password)) {
        setFlash("Password is required.", "danger");
    } else {
        $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            setFlash("Email already exists.", "warning");
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO Users (first_name, last_name, email, password_hash, phone_number, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$fname, $lname, $email, $hash, $phone, $role, $status])) {
                $newUserId = $pdo->lastInsertId();
                // LOG: Added User
                logActivity($pdo, $_SESSION['user_id'], 'create', 'user', $newUserId, "Created new user account for $fname $lname ($role)");
                setFlash("User added successfully.");
            } else {
                setFlash("Failed to add user.", "danger");
            }
        }
    }
    header("Location: admin.php#users");
    exit;
}

// 2. EDIT USER
if (isset($_POST['edit_user'])) {
    $id = $_POST['user_id'];
    $fname = trim($_POST['first_name']);
    $lname = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $phone = trim($_POST['phone_number']);
    $password = trim($_POST['password']);

    // Check duplicate email
    $stmt = $pdo->prepare("SELECT user_id FROM Users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->rowCount() > 0) {
        setFlash("Email already in use by another user.", "warning");
        header("Location: admin.php#users");
        exit;
    }

    // Update query with or without password
    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE Users SET first_name=?, last_name=?, email=?, password_hash=?, phone_number=?, role=?, status=? WHERE user_id=?";
        $params = [$fname, $lname, $email, $hash, $phone, $role, $status, $id];
    } else {
        $sql = "UPDATE Users SET first_name=?, last_name=?, email=?, phone_number=?, role=?, status=? WHERE user_id=?";
        $params = [$fname, $lname, $email, $phone, $role, $status, $id];
    }

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        // LOG: Edited User
        logActivity($pdo, $_SESSION['user_id'], 'update', 'user', $id, "Updated profile for user ID $id ($fname $lname)");
        setFlash("User updated successfully.");
    } else {
        setFlash("Failed to update user.", "danger");
    }
    header("Location: admin.php#users");
    exit;
}

// --- HANDLE GET REQUESTS (DELETE USER) ---

if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    if ($id == $_SESSION['user_id']) {
        setFlash("You cannot delete your own account.", "danger");
    } else {
        // Get name for log before deleting
        $stmtName = $pdo->prepare("SELECT first_name, last_name FROM Users WHERE user_id = ?");
        $stmtName->execute([$id]);
        $uName = $stmtName->fetch();

        $stmt = $pdo->prepare("DELETE FROM Users WHERE user_id = ?");
        if ($stmt->execute([$id])) {
            // LOG: Deleted User
            $fullName = ($uName) ? $uName['first_name'] . ' ' . $uName['last_name'] : "ID $id";
            logActivity($pdo, $_SESSION['user_id'], 'delete', 'user', $id, "Deleted user account: $fullName");
            setFlash("User deleted successfully.");
        } else {
            setFlash("Failed to delete user.", "danger");
        }
    }
    header("Location: admin.php#users");
    exit;
}

// --- FETCH DATA ---

// 1. Users
 $usersStmt = $pdo->query("SELECT * FROM Users ORDER BY created_at DESC");
 $users = $usersStmt->fetchAll();

// 2. Rooms (FIXED QUERY TO FETCH ALL COLUMNS INCLUDING current_occupancy)
 $rooms = [];
try {
    // We use * to select all columns as requested, ensuring current_occupancy is included
    $roomsStmt = $pdo->query("SELECT * FROM Rooms ORDER BY room_number ASC");
    $rooms = $roomsStmt->fetchAll();
} catch (Exception $e) { /* Table might not exist */ }

// 3. Rentals (ALL ACTIVE RENTALS ACROSS ALL ACCOUNTS)
 $rentals = [];
try {
    $rentalsStmt = $pdo->query("
        SELECT r.*, u.first_name, u.last_name, rm.room_number, rm.price 
        FROM Rentals r 
        JOIN Users u ON r.boarder_id = u.user_id 
        JOIN Rooms rm ON r.room_id = rm.room_id 
        WHERE r.status = 'active' 
        ORDER BY r.start_date DESC
    ");
    $rentals = $rentalsStmt->fetchAll();
} catch (Exception $e) { /* Table might not exist */ }

// 4. Announcements
 $announcements = [];
try {
    $annStmt = $pdo->query("SELECT * FROM Announcements ORDER BY created_at DESC LIMIT 10");
    $announcements = $annStmt->fetchAll();
} catch (Exception $e) { /* Table might not exist */ }

// 5. Payments
 $payments = [];
try {
    $payStmt = $pdo->query("
        SELECT p.*, r.rental_id, u.first_name, u.last_name 
        FROM Payments p 
        JOIN Rentals r ON p.rental_id = r.rental_id
        JOIN Users u ON r.boarder_id = u.user_id
        ORDER BY p.payment_date DESC LIMIT 10
    ");
    $payments = $payStmt->fetchAll();
} catch (Exception $e) { /* Table might not exist */ }

// 6. Stats
 $usersCount = count($users);
 $roomsCount = count($rooms);
 $rentalsCount = count($rentals);
 $openComplaints = 0;
try {
    $openComplaints = $pdo->query("SELECT COUNT(*) FROM Complaints WHERE status != 'resolved'")->fetchColumn();
} catch (Exception $e) {}

// --- FETCH AUDIT LOGS ---
 $filterUser = isset($_GET['filter_user']) ? (int)$_GET['filter_user'] : 0;
 $sqlLogs = "SELECT * FROM ActivityLogs WHERE 1=1";
 $paramsLogs = [];

if ($filterUser > 0) {
    $sqlLogs .= " AND user_id = ?";
    $paramsLogs[] = $filterUser;
}

 $sqlLogs .= " ORDER BY created_at DESC LIMIT 100"; // Limit to last 100 for performance

try {
    $stmtLogs = $pdo->prepare($sqlLogs);
    $stmtLogs->execute($paramsLogs);
    $auditLogs = $stmtLogs->fetchAll();
} catch (Exception $e) {
    $auditLogs = []; 
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | DORMFINDER</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        /* Custom Styles for Section Switching */
        .content-section { display: none; animation: fadeIn 0.3s ease-in-out; }
        .content-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .status-badge { padding: 5px 10px; border-radius: 4px; font-size: 0.85rem; font-weight: 600; }
        .role-badge { text-transform: uppercase; font-size: 0.75rem; padding: 3px 8px; border-radius: 3px; font-weight: bold; }
        .role-admin { background-color: #343a40; color: #fff; }
        .role-landlord { background-color: #17a2b8; color: #fff; }
        .role-boarder { background-color: #28a745; color: #fff; }
        .room-card { transition: transform 0.2s; cursor: pointer; height: 100%; }
        .room-card:hover { transform: translateY(-5px); }
        .nav-sidebar .nav-link.active { background-color: #007bff; color: #fff; }
        
        /* Audit Log specific styles */
        .log-entry { border-left: 3px solid #ccc; padding-left: 10px; margin-bottom: 15px; background: #f9f9f9; padding: 10px; }
        .log-entry.log-create { border-left-color: #28a745; } /* Green */
        .log-entry.log-update { border-left-color: #17a2b8; } /* Cyan */
        .log-entry.log-delete { border-left-color: #dc3545; } /* Red */
        .log-entry.log-login { border-left-color: #6c757d; } /* Grey */
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
      <li class="nav-item d-none d-sm-inline-block"><a href="index.php" class="nav-link">Home</a></li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <span class="nav-link text-muted"><i class="fas fa-user-circle mr-1"></i> Logged in as: <?php echo htmlspecialchars($_SESSION['first_name'] ?? 'Admin'); ?></span>
      </li>
    </ul>
  </nav>

  <!-- Main Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="admin.php" class="brand-link"><span class="brand-text font-weight-light">DORM FINDER </span></a>
    <div class="sidebar">
      <!-- User Panel -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['first_name'] ?? 'Admin'); ?>&background=3b82f6&color=fff" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info"><a href="#" class="d-block"><?php echo htmlspecialchars(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')); ?></a></div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
          <li class="nav-item"><a href="#dashboard" class="nav-link sidebar-link active" data-target="dashboard"><i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p></a></li>
          <li class="nav-item"><a href="#rooms" class="nav-link sidebar-link" data-target="rooms"><i class="nav-icon fas fa-door-open"></i><p>Rooms</p></a></li>
          <li class="nav-item"><a href="#rentals" class="nav-link sidebar-link" data-target="rentals"><i class="nav-icon fas fa-key"></i><p>Rentals</p></a></li>
          <li class="nav-item"><a href="#announcements" class="nav-link sidebar-link" data-target="announcements"><i class="nav-icon fas fa-bullhorn"></i><p>Announcements</p></a></li>
          <li class="nav-item"><a href="#payments" class="nav-link sidebar-link" data-target="payments"><i class="nav-icon fas fa-file-invoice-dollar"></i><p>Payments (Others)</p></a></li>
          <li class="nav-item"><a href="#users" class="nav-link sidebar-link" data-target="users"><i class="nav-icon fas fa-users"></i><p>User Management</p></a></li>
          <!-- ADDED AUDIT LOGS LINK -->
          <li class="nav-item"><a href="#audit" class="nav-link sidebar-link" data-target="audit"><i class="nav-icon fas fa-history"></i><p>Audit Logs</p></a></li>

          <li class="nav-header">ACCOUNT</li>
          <li class="nav-item"><a href="logout.php" class="nav-link text-danger"><i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2"><div class="col-sm-6"><h1 class="m-0" id="page-title">Dashboard</h1></div></div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <!-- Flash Message -->
        <?php if(isset($_SESSION['flash'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <?php echo $_SESSION['flash']['message']; unset($_SESSION['flash']); ?>
            </div>
        <?php endif; ?>

        <!-- DASHBOARD SECTION -->
        <div id="dashboard-section" class="content-section active">
            <div class="row">
              <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3><?php echo $usersCount; ?></h3><p>Total Users</p></div><div class="icon"><i class="fas fa-users"></i></div><a href="#users" class="small-box-footer nav-trigger" data-target="users">More info <i class="fas fa-arrow-circle-right"></i></a></div></div>
              <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3><?php echo $roomsCount; ?></h3><p>Total Rooms</p></div><div class="icon"><i class="fas fa-bed"></i></div><a href="#rooms" class="small-box-footer nav-trigger" data-target="rooms">More info <i class="fas fa-arrow-circle-right"></i></a></div></div>
              <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3><?php echo $rentalsCount; ?></h3><p>Active Rentals</p></div><div class="icon"><i class="fas fa-key"></i></div><a href="#rentals" class="small-box-footer nav-trigger" data-target="rentals">More info <i class="fas fa-arrow-circle-right"></i></a></div></div>
              <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3><?php echo $openComplaints; ?></h3><p>Open Complaints</p></div><div class="icon"><i class="fas fa-exclamation-triangle"></i></div></div></div>
            </div>

            <!-- Recent Activity Summary -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-outline card-info">
                        <div class="card-header"><h3 class="card-title">Recent Rentals</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-sm">
                                <thead><tr><th>Boarder</th><th>Room</th><th>Deposit</th></tr></thead>
                                <tbody>
                                    <?php foreach(array_slice($rentals, 0, 5) as $rental): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($rental['first_name']); ?></td>
                                        <td><?php echo htmlspecialchars($rental['room_number']); ?></td>
                                        <td>₱<?php echo number_format($rental['deposit_amount'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-outline card-success">
                        <div class="card-header"><h3 class="card-title">Recent Payments</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-sm">
                                <thead><tr><th>Tenant</th><th>Amount</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php foreach(array_slice($payments, 0, 5) as $pay): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($pay['first_name']); ?></td>
                                        <td class="text-success">₱<?php echo number_format($pay['amount'], 2); ?></td>
                                        <td><span class="badge badge-<?php echo $pay['status'] == 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($pay['status']); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROOMS SECTION (LOGIC UPDATED TO CHECK OCCUPANCY) -->
        <div id="rooms-section" class="content-section">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Room List</h3>
                        </div>
                        <div class="card-body">
                            <?php if(count($rooms) > 0): ?>
                                <div class="row">
                                    <?php foreach($rooms as $room): ?>
                                    
                                    <?php 
                                        // LOGIC: Determine if occupied based on current_occupancy vs capacity
                                        $isOccupied = ($room['current_occupancy'] >= $room['capacity']); 
                                        $statusBadge = $isOccupied ? 'badge-danger' : 'badge-success';
                                        $statusText = $isOccupied ? 'Occupied' : 'Available';
                                        $cardClass = $isOccupied ? 'card-secondary' : 'card-success';
                                    ?>
                                    
                                    <div class="col-md-4 col-sm-6 mb-3">
                                        <div class="card card-widget room-card <?php echo $cardClass; ?>">
                                            <div class="card-header">
                                                <h5 class="card-title">Room #<?php echo htmlspecialchars($room['room_number']); ?></h5>
                                                <div class="card-tools">
                                                    <span class="badge <?php echo $statusBadge; ?>">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <p><strong>Type:</strong> <?php echo htmlspecialchars($room['room_type'] ?? 'Standard'); ?></p>
                                                <p><strong>Price:</strong> ₱<?php echo number_format($room['price_per_month'], 2); ?> / mo</p>
                                                <p><strong>Occupancy:</strong> <?php echo $room['current_occupancy']; ?> / <?php echo $room['capacity']; ?></p>
                                                <p class="text-muted text-sm"><?php echo htmlspecialchars($room['amenities'] ?? 'No description available.'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">No rooms found in database.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RENTALS SECTION -->
        <div id="rentals-section" class="content-section">
            <div class="row"><div class="col-12"><div class="card card-warning card-outline"><div class="card-header"><h3 class="card-title">All Active Rentals</h3></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-striped table-bordered"><thead><tr><th>ID</th><th>Boarder (ID)</th><th>Room</th><th>Start Date</th><th>End Date</th><th>Deposit</th><th>Status</th></tr></thead><tbody><?php if(count($rentals) > 0): ?><?php foreach($rentals as $rental): ?><tr><td>#<?php echo $rental['rental_id']; ?></td><td><?php echo htmlspecialchars($rental['first_name'] . ' ' . $rental['last_name']); ?><br><small class="text-muted">ID: <?php echo $rental['boarder_id']; ?></small></td><td><?php echo htmlspecialchars($rental['room_number']); ?></td><td><?php echo date('M d, Y', strtotime($rental['start_date'])); ?></td><td><?php echo date('M d, Y', strtotime($rental['end_date'])); ?></td><td>₱<?php echo number_format($rental['deposit_amount'], 2); ?></td><td><span class="badge badge-success"><?php echo ucfirst($rental['status']); ?></span></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="7" class="text-center">No active rentals found in the system.</td></tr><?php endif; ?></tbody></table></div></div></div></div></div>
        </div>

        <!-- ANNOUNCEMENTS SECTION -->
        <div id="announcements-section" class="content-section">
            <div class="row"><div class="col-12"><div class="card card-info card-outline"><div class="card-header"><h3 class="card-title">Announcement History</h3></div><div class="card-body p-0"><ul class="products-list product-list-in-card pl-2 pr-2"><?php if(count($announcements) > 0): ?><?php foreach($announcements as $ann): ?><li class="item"><div class="product-info"><a href="javascript:void(0)" class="product-title text-bold"><?php echo htmlspecialchars($ann['title']); ?><span class="badge badge-info float-right"><?php echo date('M d', strtotime($ann['created_at'])); ?></span></a><span class="product-description"><?php echo nl2br(htmlspecialchars($ann['content'])); ?></span></div></li><?php endforeach; ?><?php else: ?><li class="item"><div class="product-info">No announcements yet.</div></li><?php endif; ?></ul></div></div></div></div>
        </div>

        <!-- PAYMENTS SECTION -->
        <div id="payments-section" class="content-section">
            <div class="row"><div class="col-12"><div class="card card-secondary card-outline"><div class="card-header"><h3 class="card-title">Payment History</h3></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-striped table-bordered"><thead><tr><th>ID</th><th>Tenant</th><th>Amount</th><th>Penalty</th><th>Due Date</th><th>Paid Date</th><th>Method</th><th>Status</th></tr></thead><tbody><?php if(count($payments) > 0): ?><?php foreach($payments as $pay): ?><tr><td>#<?php echo $pay['payment_id']; ?></td><td><?php echo htmlspecialchars($pay['first_name'] . ' ' . $pay['last_name']); ?></td><td class="text-success font-weight-bold">₱<?php echo number_format($pay['amount'], 2); ?></td><td class="text-danger">₱<?php echo number_format($pay['penalty_fee'], 2); ?></td><td><?php echo date('M d, Y', strtotime($pay['due_date'])); ?></td><td><?php echo $pay['payment_date'] ? date('M d, Y', strtotime($pay['payment_date'])) : '-'; ?></td><td><?php echo htmlspecialchars($pay['payment_method'] ?? 'Cash'); ?></td><td><span class="badge badge-<?php echo $pay['status'] == 'paid' ? 'success' : 'warning'; ?>"><?php echo ucfirst($pay['status']); ?></span></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="8" class="text-center">No payment records found.</td></tr><?php endif; ?></tbody></table></div></div></div></div></div>
        </div>

        <!-- USER MANAGEMENT SECTION -->
        <div id="users-section" class="content-section">
            <div class="row"><div class="col-12"><div class="card"><div class="card-header"><h3 class="card-title">User Management</h3><div class="card-tools"><button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal-add-user"><i class="fas fa-plus"></i> Add New User</button></div></div><div class="card-body p-0"><table class="table table-striped projects"><thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php if(count($users) > 0): ?><?php foreach($users as $u): ?><tr><td><?php echo $u['user_id']; ?></td><td><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></td><td><?php echo htmlspecialchars($u['email']); ?></td><td><span class="role-badge role-<?php echo $u['role']; ?>"><?php echo ucfirst($u['role']); ?></span></td><td><?php if($u['status'] == 'active'): ?><span class="badge badge-success">Active</span><?php elseif($u['status'] == 'suspended'): ?><span class="badge badge-warning">Suspended</span><?php else: ?><span class="badge badge-secondary">Pending</span><?php endif; ?></td><td><button class="btn btn-info btn-sm" onclick='editUser(<?php echo json_encode($u); ?>)'><i class="fas fa-pencil-alt"></i></button> <a class="btn btn-danger btn-sm" href="admin.php?delete_user=<?php echo $u['user_id']; ?>" onclick="return confirm('Delete user?');"><i class="fas fa-trash"></i></a></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="6" class="text-center">No users found.</td></tr><?php endif; ?></tbody></table></div></div></div></div>
        </div>

        <!-- AUDIT LOGS SECTION -->
        <div id="audit-section" class="content-section">
            <div class="row">
                <div class="col-12">
                    <div class="card card-dark card-outline">
                        <div class="card-header">
                            <h3 class="card-title">System Audit Logs</h3>
                            <div class="card-tools">
                                <!-- Filter Form -->
                                <form method="GET" action="admin.php" class="form-inline">
                                    <input type="hidden" name="page" value="admin">
                                    <select name="filter_user" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                                        <option value="0">All Users</option>
                                        <?php foreach($users as $u): ?>
                                            <option value="<?php echo $u['user_id']; ?>" <?php echo ($filterUser == $u['user_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name'] . ' (' . ucfirst($u['role']) . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th style="width: 150px;">Timestamp</th>
                                            <th style="width: 200px;">User</th>
                                            <th style="width: 100px;">Action</th>
                                            <th>Description</th>
                                            <th style="width: 120px;">IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(count($auditLogs) > 0): ?>
                                            <?php foreach($auditLogs as $log): ?>
                                            <tr>
                                                <td class="text-muted"><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($log['user_name']); ?></strong><br>
                                                    <small class="text-muted">ID: <?php echo $log['user_id']; ?> (<?php echo ucfirst($log['user_role']); ?>)</small>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $badgeClass = 'secondary';
                                                        if($log['action_type'] == 'create') $badgeClass = 'success';
                                                        if($log['action_type'] == 'update') $badgeClass = 'info';
                                                        if($log['action_type'] == 'delete') $badgeClass = 'danger';
                                                    ?>
                                                    <span class="badge badge-<?php echo $badgeClass; ?>"><?php echo ucfirst($log['action_type']); ?></span>
                                                    <br>
                                                    <small class="text-muted"><?php echo ucfirst($log['entity_type']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($log['description']); ?></td>
                                                <td class="text-muted text-sm"><?php echo $log['ip_address']; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="text-center p-3">No logs found.</td></tr>
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

<footer class="main-footer no-print"><strong>Copyright &copy; <?php echo date("Y"); ?> Dorm Finder.</strong></footer>
</div>

<!-- MODALS -->

<!-- Add User Modal -->
<div class="modal fade" id="modal-add-user">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h4 class="modal-title">Add New User</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <form method="POST" action="admin.php#users">
                <div class="modal-body">
                    <div class="form-group"><label>First Name</label><input type="text" name="first_name" class="form-control" required></div>
                    <div class="form-group"><label>Last Name</label><input type="text" name="last_name" class="form-control" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="form-group"><label>Phone Number</label><input type="text" name="phone_number" class="form-control"></div>
                    <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                    <div class="form-group"><label>Role</label><select name="role" class="form-control"><option value="boarder">Boarder</option><option value="landlord">Landlord</option><option value="admin">Admin</option></select></div>
                    <div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="active">Active</option><option value="pending">Pending</option><option value="suspended">Suspended</option></select></div>
                </div>
                <div class="modal-footer justify-content-between"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button><button type="submit" name="add_user" class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="modal-edit-user">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h4 class="modal-title">Edit User</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
            <form method="POST" action="admin.php#users">
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="edit_id">
                    <div class="form-group"><label>First Name</label><input type="text" name="first_name" id="edit_first_name" class="form-control" required></div>
                    <div class="form-group"><label>Last Name</label><input type="text" name="last_name" id="edit_last_name" class="form-control" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
                    <div class="form-group"><label>Phone Number</label><input type="text" name="phone_number" id="edit_phone_number" class="form-control"></div>
                    <div class="form-group"><label>Password (Leave empty to keep)</label><input type="password" name="password" id="edit_password" class="form-control"></div>
                    <div class="form-group"><label>Role</label><select name="role" id="edit_role" class="form-control"><option value="boarder">Boarder</option><option value="landlord">Landlord</option><option value="admin">Admin</option></select></div>
                    <div class="form-group"><label>Status</label><select name="status" id="edit_status" class="form-control"><option value="active">Active</option><option value="pending">Pending</option><option value="suspended">Suspended</option></select></div>
                </div>
                <div class="modal-footer justify-content-between"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button><button type="submit" name="edit_user" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
    function switchSection(targetId) {
        $('.content-section').removeClass('active');
        $('#' + targetId + '-section').addClass('active');
        $('.sidebar-link').removeClass('active');
        $('a[data-target="' + targetId + '"]').addClass('active');
        var titleMap = { 'dashboard': 'Dashboard', 'rooms': 'Rooms', 'rentals': 'Rentals', 'announcements': 'Announcements', 'payments': 'Payments', 'users': 'User Management', 'audit': 'Audit Logs' };
        $('#page-title').text(titleMap[targetId] || 'Dashboard');
    }
    $('.sidebar-link').on('click', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        history.pushState(null, null, '#' + target);
        switchSection(target);
    });
    $('.nav-trigger').on('click', function(e) {
        e.preventDefault();
        var target = $(this).data('target');
        $('a[data-target="' + target + '"]').trigger('click');
    });
    $(document).ready(function() {
        var hash = window.location.hash.substring(1);
        if (hash) { switchSection(hash); }
    });
    function editUser(user) {
        $('#edit_id').val(user.user_id);
        $('#edit_first_name').val(user.first_name);
        $('#edit_last_name').val(user.last_name);
        $('#edit_email').val(user.email);
        $('#edit_phone_number').val(user.phone_number);
        $('#edit_password').val('');
        $('#edit_role').val(user.role);
        $('#edit_status').val(user.status);
        $('#modal-edit-user').modal('show');
    }
</script>

</body>
</html>