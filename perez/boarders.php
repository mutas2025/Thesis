<?php
session_start();
require 'config.php';
requireLogin();
checkRole(['boarder']);

 $boarder_id = $_SESSION['user_id'];
 $msg = "";

// --- LOGGING HELPER FUNCTION ---
function logActivity($pdo, $user_id, $action, $entity, $entity_id = null, $description = "") {
    try {
        $ip = $_SERVER['REMOTE_ADDR'];
        $desc = htmlspecialchars($description); 
        $stmt = $pdo->prepare("INSERT INTO ActivityLogs (user_id, action_type, entity_type, entity_id, description, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $entity, $entity_id, $desc, $ip]);
    } catch (Exception $e) {
        // Fail silently
        error_log("Logging failed: " . $e->getMessage());
    }
}

// --- 1. TOAST HANDLING (GET/SESSION) ---
 $toast_message = "";
 $toast_type = "success"; 

if (isset($_SESSION['toast'])) {
    $toast_message = $_SESSION['toast']['message'];
    $toast_type = $_SESSION['toast']['type'];
    unset($_SESSION['toast']); 
}

// --- 2. POST HANDLING ---

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Handle Application
    if (isset($_POST['apply_room'])) {
        $room_id = $_POST['room_id'];
        $check = $pdo->prepare("SELECT * FROM Applications WHERE room_id = ? AND boarder_id = ?");
        $check->execute([$room_id, $boarder_id]);
        if ($check->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO Applications (room_id, boarder_id, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$room_id, $boarder_id]);
            
            // LOG: Applied for Room
            logActivity($pdo, $boarder_id, 'create', 'application', $room_id, "Applied for Room ID: $room_id");

            $_SESSION['toast'] = ['message' => 'Application sent successfully!', 'type' => 'success'];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $_SESSION['toast'] = ['message' => 'You have already applied for this room.', 'type' => 'warning'];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    // Handle Maintenance Request
    if (isset($_POST['submit_maintenance'])) {
        $house_id = $_POST['house_id'];
        $stmtRoom = $pdo->prepare("SELECT r.room_id, r.room_number FROM Rentals re JOIN Rooms r ON re.room_id = r.room_id WHERE re.boarder_id = ? AND r.house_id = ? AND re.status = 'active' LIMIT 1");
        $stmtRoom->execute([$boarder_id, $house_id]);
        $roomData = $stmtRoom->fetch();
        
        if($roomData) {
            $room_id = $roomData['room_id'];
            $category = $_POST['category'];
            $desc = $_POST['description'];
            
            $stmt = $pdo->prepare("INSERT INTO MaintenanceRequests (house_id, room_id, reported_by, category, description, status) VALUES (?, ?, ?, ?, ?, 'open')");
            $stmt->execute([$house_id, $room_id, $boarder_id, $category, $desc]);
            $reqId = $pdo->lastInsertId();

            // LOG: Submitted Maintenance
            logActivity($pdo, $boarder_id, 'create', 'maintenance', $reqId, "Submitted maintenance request for Room {$roomData['room_number']}: $category - $desc");
            
            $_SESSION['toast'] = ['message' => 'Maintenance request submitted.', 'type' => 'success'];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $_SESSION['toast'] = ['message' => 'Error locating your active room for this house.', 'type' => 'danger'];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    // --- Handle Payment Submission ---
    if (isset($_POST['add_payment'])) {
        $rental_id = $_POST['rental_id'];
        $amount = $_POST['amount'];
        $method = $_POST['payment_method'];
        
        // File Upload Logic
        $proof_url = null;
        if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] == 0) {
            // CHANGED: Specific folder for proofs
            $target_dir = "uploads/proof/"; 
            
            // Create directory if it doesn't exist
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_tmp = $_FILES['proof_image']['tmp_name'];
            $file_name = time() . '_' . basename($_FILES['proof_image']['name']);
            $target_file = $target_dir . $file_name;
            
            $upload_ok = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            $allowed_types = array("jpg", "png", "jpeg", "pdf", "gif");
            if(in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($file_tmp, $target_file)) {
                    // Store only the filename in DB
                    $proof_url = $file_name;
                } else {
                    $_SESSION['toast'] = ['message' => 'Error uploading file.', 'type' => 'danger'];
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }
            } else {
                $_SESSION['toast'] = ['message' => 'Invalid file type. Only JPG, JPEG, PNG, PDF allowed.', 'type' => 'danger'];
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        } else {
            $_SESSION['toast'] = ['message' => 'Proof of payment image is required.', 'type' => 'warning'];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        // Insert into Database
        $due_date = date('Y-m-d'); 

        try {
            $stmt = $pdo->prepare("INSERT INTO Payments (rental_id, amount, payment_date, payment_method, proof_image_url, status, due_date) VALUES (?, ?, NOW(), ?, ?, 'pending', ?)");
            $stmt->execute([$rental_id, $amount, $method, $proof_url, $due_date]);
            $paymentId = $pdo->lastInsertId();

            // LOG: Submitted Payment
            logActivity($pdo, $boarder_id, 'create', 'payment', $paymentId, "Submitted payment of $amount via $method. File: $proof_url");

            $_SESSION['toast'] = ['message' => 'Payment recorded successfully. Pending verification.', 'type' => 'success'];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } catch (PDOException $e) {
            $_SESSION['toast'] = ['message' => 'Database error: ' . $e->getMessage(), 'type' => 'danger'];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}

// --- 3. FETCH DATA ---

 $sql = "SELECT r.*, bh.house_name, a.city, a.state, bh.house_id 
     FROM Rooms r 
     JOIN BoardingHouses bh ON r.house_id = bh.house_id 
     JOIN Addresses a ON bh.address_id = a.address_id 
     WHERE r.status = 'available' ORDER BY r.price_per_month ASC";
 $stmtRooms = $pdo->query($sql);
 $rooms = $stmtRooms->fetchAll();

 $stmtMyApps = $pdo->prepare("SELECT a.*, r.room_number, r.room_image, bh.house_name 
                           FROM Applications a 
                           JOIN Rooms r ON a.room_id = r.room_id 
                           JOIN BoardingHouses bh ON r.house_id = bh.house_id 
                           WHERE a.boarder_id = ?");
 $stmtMyApps->execute([$boarder_id]);
 $myApps = $stmtMyApps->fetchAll();

 $stmtRentals = $pdo->prepare("SELECT re.rental_id, r.house_id, bh.house_name, r.room_number, r.price_per_month 
                          FROM Rentals re 
                          JOIN Rooms r ON re.room_id = r.room_id 
                          JOIN BoardingHouses bh ON r.house_id = bh.house_id 
                          WHERE re.boarder_id = ? AND re.status = 'active'");
 $stmtRentals->execute([$boarder_id]);
 $rentals = $stmtRentals->fetchAll();

 $announcements = [];
 $activeHouseIds = [];
foreach($rentals as $r) { $activeHouseIds[] = $r['house_id']; }

if (!empty($activeHouseIds)) {
    $placeholders = str_repeat('?,', count($activeHouseIds) - 1) . '?';
    $sqlAnn = "SELECT * FROM Announcements WHERE house_id IN ($placeholders) ORDER BY is_pinned DESC, created_at DESC";
    $stmtAnn = $pdo->prepare($sqlAnn);
    $stmtAnn->execute($activeHouseIds);
    $announcements = $stmtAnn->fetchAll();
}

 $stmtPayments = $pdo->prepare("
   SELECT p.*, bh.house_name, r.room_number 
   FROM Payments p
   JOIN Rentals re ON p.rental_id = re.rental_id
   JOIN Rooms r ON re.room_id = r.room_id
   JOIN BoardingHouses bh ON r.house_id = bh.house_id
   WHERE re.boarder_id = ?
   ORDER BY p.created_at DESC
");
 $stmtPayments->execute([$boarder_id]);
 $payments = $stmtPayments->fetchAll();

 $stmtMaint = $pdo->prepare("
   SELECT mr.*, bh.house_name, r.room_number 
   FROM MaintenanceRequests mr
   JOIN Rooms r ON mr.room_id = r.room_id
   JOIN BoardingHouses bh ON r.house_id = bh.house_id
   WHERE mr.reported_by = ?
   ORDER BY mr.created_at DESC
");
 $stmtMaint->execute([$boarder_id]);
 $maintenanceRequests = $stmtMaint->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Boarder | AdminLTE</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <style>
    .d-none-custom { display: none !important; }
    
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    }

    /* Thumbnail Style */
    .room-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 5px;
        border: 1px solid #dee2e6;
        transition: transform 0.2s;
    }

    /* Interaction: Hover effect and Pointer cursor */
    .room-thumb.view-room-img {
        cursor: pointer;
    }
    .room-thumb.view-room-img:hover {
        opacity: 0.8;
        transform: scale(1.1);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a></li>
    </ul>
  </nav>

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-info elevation-4">
    <a href="index.php" class="brand-link"><span class="brand-text font-weight-light">Boarder Portal</span></a>
    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['first_name']; ?>&background=17a2b8&color=fff" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info"><a href="#" class="d-block"><?php echo htmlspecialchars($_SESSION['first_name']); ?> <span class="badge badge-info">Boarder</span></a></div>
      </div>
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="#" onclick="showSection('find-room', this)" class="nav-link active" id="link-find-room">
                <i class="nav-icon fas fa-home"></i><p>Find Room</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" onclick="showSection('my-apps', this)" class="nav-link" id="link-my-apps">
                <i class="nav-icon fas fa-file-contract"></i><p>My Applications</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" onclick="showSection('payments', this)" class="nav-link" id="link-payments">
                <i class="nav-icon fas fa-credit-card"></i><p>My Payments</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" onclick="showSection('maintenance', this)" class="nav-link" id="link-maintenance">
                <i class="nav-icon fas fa-tools"></i><p>My Maintenance</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" onclick="showSection('announcements', this)" class="nav-link" id="link-announcements">
                <i class="nav-icon fas fa-bullhorn"></i><p>Announcements</p>
            </a>
          </li>
          <li class="nav-header">ACCOUNT</li>
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
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2"><div class="col-sm-6"><h1 class="m-0" id="page-title">Find a Room</h1></div></div>
      </div>
    </div>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">

        <!-- Toast Container -->
        <div class="toast-container">
            <?php if($toast_message): ?>
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                <div class="toast-header bg-<?php echo $toast_type; ?> text-white">
                    <strong class="mr-auto">Notification</strong>
                    <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    <?php echo $toast_message; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-md-8">
                
                <!-- SECTION 1: Find Room -->
                <div id="section-find-room">
                    <div class="card">
                      <div class="card-header"><h3 class="card-title">Available Rooms</h3></div>
                      <div class="card-body p-0">
                        <table class="table table-hover">
                          <thead><tr><th>Image</th><th>House</th><th>Room</th><th>Type</th><th>Price</th><th>Action</th></tr></thead>
                          <tbody>
                            <?php if(count($rooms) > 0): ?>
                                <?php foreach($rooms as $room): 
                                    $imgUrl = !empty($room['room_image']) ? $room['room_image'] : "https://picsum.photos/seed/room" . $room['room_id'] . "/50/50";
                                ?>
                                <tr>
                                  <td>
                                      <!-- ADDED: class 'view-room-img cursor-pointer' to make it clickable -->
                                      <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="Room" class="room-thumb view-room-img cursor-pointer">
                                  </td>
                                  <td>
                                      <strong><?php echo htmlspecialchars($room['house_name']); ?></strong><br>
                                      <small class="text-muted"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($room['city']); ?></small>
                                  </td>
                                  <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                  <td><span class="badge badge-secondary"><?php echo ucfirst($room['room_type']); ?></span></td>
                                  <td>₱<?php echo number_format($room['price_per_month'], 2); ?></td>
                                  <td>
                                    <form method="POST" onsubmit="return confirm('Apply for this room?');">
                                      <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                      <input type="hidden" name="apply_room" value="1">
                                      <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                                    </form>
                                  </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">No available rooms found.</td></tr>
                            <?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                </div>

                <!-- SECTION 2: My Applications -->
                <div id="section-my-apps" class="d-none-custom">
                    <div class="card">
                      <div class="card-header"><h3 class="card-title">My Applications History</h3></div>
                      <div class="card-body p-0">
                        <table class="table table-hover">
                          <thead><tr><th>Image</th><th>House</th><th>Room</th><th>Applied Date</th><th>Status</th></tr></thead>
                          <tbody>
                            <?php if(count($myApps) > 0): ?>
                                <?php foreach($myApps as $app): 
                                     $badge = 'bg-secondary'; 
                                     if($app['status'] == 'approved') $badge = 'bg-success';
                                     if($app['status'] == 'rejected') $badge = 'bg-danger';
                                     $imgUrl = !empty($app['room_image']) ? $app['room_image'] : "https://picsum.photos/seed/room" . $app['room_number'] . "/50/50";
                                ?>
                                <tr>
                                  <td>
                                      <!-- ADDED: class 'view-room-img cursor-pointer' to make it clickable -->
                                      <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="Room" class="room-thumb view-room-img cursor-pointer">
                                  </td>
                                  <td><strong><?php echo htmlspecialchars($app['house_name']); ?></strong></td>
                                  <td>Room <?php echo htmlspecialchars($app['room_number']); ?></td>
                                  <td><?php echo date('M d, Y', strtotime($app['application_date'] ?? 'now')); ?></td>
                                  <td><span class="badge <?php echo $badge; ?>"><?php echo ucfirst($app['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">You haven't applied for any rooms yet.</td></tr>
                            <?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                </div>

                <!-- SECTION 3: My Payments -->
                <div id="section-payments" class="d-none-custom">
                    
                    <?php if(count($rentals) > 0): ?>
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Record New Payment</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Select Rental / Room</label>
                                            <select name="rental_id" id="payment_rental_select" class="form-control select2" required style="width: 100%;">
                                                <?php foreach($rentals as $r): ?>
                                                    <option value="<?php echo $r['rental_id']; ?>" data-price="<?php echo $r['price_per_month']; ?>">
                                                        <?php echo htmlspecialchars($r['house_name']); ?> - Room <?php echo htmlspecialchars($r['room_number']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Amount (₱)</label>
                                            <input type="number" name="amount" id="payment_amount" class="form-control" step="0.01" required placeholder="0.00" readonly>
                                            <small class="form-text text-muted">Amount is auto-filled based on selected room rent.</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Payment Method</label>
                                            <select name="payment_method" class="form-control" required>
                                                <option value="gcash">GCash</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="cash">Cash</option>
                                                <option value="paypal">PayPal</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Proof of Payment (Image/PDF)</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" name="proof_image" class="custom-file-input" id="proofInput" required accept="image/*,application/pdf">
                                                    <label class="custom-file-label" for="proofInput">Choose file</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" name="add_payment" class="btn btn-success">Submit Payment</button>
                            </div>
                        </form>
                    </div>
                    <div class="alert alert-info alert-dismissible mt-2">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <h5><i class="icon fas fa-info"></i> Info!</h5>
                        Please upload a clear screenshot or photo of your receipt.
                    </div>
                    <?php endif; ?>

                    <div class="card mt-3">
                      <div class="card-header"><h3 class="card-title">Payment History</h3></div>
                      <div class="card-body p-0">
                        <table class="table table-hover">
                          <thead>
                            <tr>
                                <th>Date</th>
                                <th>Property / Room</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Proof</th>
                                <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if(count($payments) > 0): ?>
                                <?php foreach($payments as $pay): 
                                    $pBadge = 'bg-secondary';
                                    if($pay['status'] == 'verified') $pBadge = 'bg-success';
                                    if($pay['status'] == 'pending') $pBadge = 'bg-warning';
                                ?>
                                <tr>
                                  <td>
                                      <?php echo date('M d, Y', strtotime($pay['created_at'])); ?><br>
                                      <small class="text-muted">Due: <?php echo date('M d', strtotime($pay['due_date'])); ?></small>
                                  </td>
                                  <td>
                                      <?php echo htmlspecialchars($pay['house_name']); ?><br>
                                      <small class="text-muted">Room <?php echo htmlspecialchars($pay['room_number']); ?></small>
                                  </td>
                                  <td><strong>₱<?php echo number_format($pay['amount'], 2); ?></strong></td>
                                  <td><?php echo ucfirst($pay['payment_method']); ?></td>
                                  <td>
                                      <?php if($pay['proof_image_url']): ?>
                                        <!-- UPDATED: Link to uploads/proof/ directory -->
                                        <a href="uploads/proof/<?php echo htmlspecialchars($pay['proof_image_url']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                      <?php else: ?>
                                        <span class="text-muted">-</span>
                                      <?php endif; ?>
                                  </td>
                                  <td><span class="badge <?php echo $pBadge; ?>"><?php echo ucfirst($pay['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">No payment history found.</td></tr>
                            <?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                </div>

                <!-- SECTION 4: My Maintenance Requests -->
                <div id="section-maintenance" class="d-none-custom">
                    <div class="card">
                      <div class="card-header"><h3 class="card-title">My Maintenance Requests</h3></div>
                      <div class="card-body p-0">
                        <table class="table table-hover">
                          <thead>
                            <tr>
                                <th>Date Reported</th>
                                <th>Property / Room</th>
                                <th>Category</th>
                                <th>Priority</th>
                                <th>Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php if(count($maintenanceRequests) > 0): ?>
                                <?php foreach($maintenanceRequests as $req): 
                                    $mBadge = 'bg-secondary';
                                    if($req['status'] == 'resolved') $mBadge = 'bg-success';
                                    if($req['status'] == 'in_progress') $mBadge = 'bg-info';
                                    $pClass = (strtolower($req['priority']) == 'high') ? 'text-danger font-weight-bold' : '';
                                ?>
                                <tr>
                                  <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                                  <td>
                                      <?php echo htmlspecialchars($req['house_name']); ?><br>
                                      <small class="text-muted">Room <?php echo htmlspecialchars($req['room_number']); ?></small>
                                  </td>
                                  <td><?php echo ucfirst($req['category']); ?></td>
                                  <td class="<?php echo $pClass; ?>"><?php echo ucfirst($req['priority']); ?></td>
                                  <td>
                                      <span class="badge <?php echo $mBadge; ?>"><?php echo ucfirst($req['status']); ?></span>
                                      <?php if($req['status'] == 'resolved' && $req['resolved_at']): ?>
                                        <br><small class="text-muted">Resolved: <?php echo date('M d', strtotime($req['resolved_at'])); ?></small>
                                      <?php endif; ?>
                                  </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center">No maintenance requests found.</td></tr>
                            <?php endif; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                </div>

                <!-- SECTION 5: Announcements -->
                <div id="section-announcements" class="d-none-custom">
                    <div class="card">
                      <div class="card-header"><h3 class="card-title">Announcements</h3></div>
                      <div class="card-body">
                        <?php if(count($announcements) > 0): ?>
                            <?php foreach($announcements as $ann): ?>
                                <div class="callout callout-info">
                                    <h5><?php echo htmlspecialchars($ann['title']); ?> <?php echo $ann['is_pinned'] ? '<i class="fas fa-thumbtack text-danger float-right"></i>' : ''; ?></h5>
                                    <p><?php echo nl2br(htmlspecialchars($ann['content'])); ?></p>
                                    <small class="text-muted"><i class="far fa-clock"></i> <?php echo date('M d, Y g:i A', strtotime($ann['created_at'])); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-secondary">
                                <i class="fas fa-info-circle"></i> No announcements found for your current property.
                            </div>
                        <?php endif; ?>
                      </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Widgets -->
            <div class="col-md-4">
                
                <?php if(count($rentals) > 0): ?>
                <div class="card card-danger">
                  <div class="card-header"><h3 class="card-title">Report Issue</h3></div>
                  <form method="POST">
                    <div class="card-body">
                        <div class="form-group">
                            <label>House</label>
                            <select name="house_id" class="form-control">
                                <?php foreach($rentals as $r): ?>
                                    <option value="<?php echo $r['house_id']; ?>"><?php echo htmlspecialchars($r['house_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" class="form-control">
                                <option>Plumbing</option>
                                <option>Electrical</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2" required></textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" name="submit_maintenance" class="btn btn-danger btn-sm">Submit Request</button>
                    </div>
                  </form>
                </div>
                <?php else: ?>
                <div class="card card-info">
                    <div class="card-header"><h3 class="card-title">Move In?</h3></div>
                    <div class="card-body"><p>Apply for a room to access maintenance requests.</p></div>
                </div>
                <?php endif; ?>

                <div class="card card-info mt-2">
                  <div class="card-header"><h3 class="card-title">Need Help?</h3></div>
                  <div class="card-body"><p>Contact the landlord directly for urgent matters.</p></div>
                </div>

            </div>
        </div>
      </div>
    </section>
  </div>
  
  <footer class="main-footer"><strong>Copyright &copy; 2023 Boarding System.</strong></footer>
</div>

<!-- NEW: IMAGE VIEW MODAL -->
<div class="modal fade" id="viewImageModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Room Image</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center bg-light">
        <img id="preview-room-image" src="" class="img-fluid" alt="Room Preview">
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<script>
    // File Input Label Update
    $('.custom-file-input').on('change', function(event) {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // NEW: Handle Image Click to Open Modal
    $('.view-room-img').on('click', function() {
        var src = $(this).attr('src');
        $('#preview-room-image').attr('src', src);
        $('#viewImageModal').modal('show');
    });

    // Auto-fill Amount based on selected Rental
    $(document).ready(function() {
        function updateAmount() {
            var selectedOption = $('#payment_rental_select option:selected');
            var price = selectedOption.data('price');
            if(price) {
                $('#payment_amount').val(price);
            } else {
                $('#payment_amount').val('');
            }
        }

        // Trigger on change
        $('#payment_rental_select').on('change', function() {
            updateAmount();
        });

        // Trigger on page load (for the first/default option)
        updateAmount();
    });

    // View Switcher
    function showSection(sectionId, linkElement) {
        // Hide all
        document.getElementById('section-find-room').classList.add('d-none-custom');
        document.getElementById('section-my-apps').classList.add('d-none-custom');
        document.getElementById('section-payments').classList.add('d-none-custom');
        document.getElementById('section-maintenance').classList.add('d-none-custom');
        document.getElementById('section-announcements').classList.add('d-none-custom');

        // Reset Active Links
        const links = ['link-find-room', 'link-my-apps', 'link-payments', 'link-maintenance', 'link-announcements'];
        links.forEach(id => document.getElementById(id).classList.remove('active'));

        // Show selected
        document.getElementById('section-' + sectionId).classList.remove('d-none-custom');
        
        if(linkElement) linkElement.classList.add('active');

        // Title
        const titles = {
            'find-room': 'Find a Room',
            'my-apps': 'My Applications',
            'payments': 'My Payments',
            'maintenance': 'My Maintenance Requests',
            'announcements': 'Announcements'
        };
        document.getElementById('page-title').innerText = titles[sectionId] || 'Dashboard';
    }

    // Auto-hide toast after 5 seconds
    setTimeout(function() {
        $('.toast').toast('hide');
    }, 5000);
</script>

</body>
</html>