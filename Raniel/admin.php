<?php
require_once 'config.php';

// ACCESS CONTROL
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'ADMIN') {
    redirect('login.php');
}

// --- ACTION HANDLERS ---

// 1. EDIT USER
if (isset($_POST['edit_user'])) {
    $id = (int)$_POST['user_id'];
    // Prevent editing self
    if($id != $_SESSION['user_id']) {
        $name = $conn->real_escape_string($_POST['full_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $role = $conn->real_escape_string($_POST['role']);
        $status = $conn->real_escape_string($_POST['status']);
        
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, role=?, status=? WHERE user_id=?");
        $stmt->bind_param("ssssi", $name, $email, $role, $status, $id);
        $stmt->execute();
        
        $_SESSION['toast'] = ['msg' => 'User updated successfully', 'type' => 'success'];
    } else {
        $_SESSION['toast'] = ['msg' => 'You cannot edit your own profile here.', 'type' => 'warning'];
    }
    redirect('admin.php?view=users');
}

// 2. EDIT MERCHANT
if (isset($_POST['edit_merchant'])) {
    $mid = (int)$_POST['merchant_id'];
    $business_name = $conn->real_escape_string($_POST['business_name']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE merchants SET business_name=?, status=? WHERE merchant_id=?");
    $stmt->bind_param("ssi", $business_name, $status, $mid);
    $stmt->execute();
    
    $_SESSION['toast'] = ['msg' => 'Merchant updated successfully', 'type' => 'success'];
    redirect('admin.php?view=merchants');
}

// 1. DETERMINE ACTIVE PAGE
 $page = isset($_GET['view']) ? $_GET['view'] : 'dashboard';
 $allowed_views = ['dashboard', 'users', 'merchants', 'transactions', 'ewallets'];
if (!in_array($page, $allowed_views)) {
    $page = 'dashboard';
}

// 2. FETCH GLOBAL STATS
 $stats = [
    'customers' => $conn->query("SELECT COUNT(*) as c FROM users WHERE role='CUSTOMER'")->fetch_assoc()['c'],
    'merchants' => $conn->query("SELECT COUNT(*) as c FROM users WHERE role='MERCHANT'")->fetch_assoc()['c'],
    'revenue' => $conn->query("SELECT COALESCE(SUM(amount), 0) as t FROM payments WHERE status='SUCCESS'")->fetch_assoc()['t']
];

// --- DASHBOARD CHART DATA ---
 $bill_merchant_labels = [];
 $bill_merchant_data = [];
 $ewallet_merchant_labels = [];
 $ewallet_merchant_data = [];

if ($page == 'dashboard') {
    // Top 10 Merchants by Bill Payments
    $bill_chart_q = $conn->query("
        SELECT m.business_name, COALESCE(SUM(p.amount), 0) as total
        FROM payments p
        JOIN bills b ON p.bill_id = b.bill_id
        JOIN utility_accounts ua ON b.account_id = ua.account_id
        JOIN billers bl ON ua.biller_id = bl.biller_id
        JOIN merchants m ON bl.merchant_id = m.merchant_id
        WHERE p.status = 'SUCCESS'
        GROUP BY m.merchant_id, m.business_name
        ORDER BY total DESC
        LIMIT 10
    ");
    while($row = $bill_chart_q->fetch_assoc()){
        $bill_merchant_labels[] = $row['business_name'];
        $bill_merchant_data[] = $row['total'];
    }

    // Top 10 Merchants by E-Wallet Transactions
    $ewallet_chart_q = $conn->query("
        SELECT m.business_name, COALESCE(SUM(et.total_amount), 0) as total
        FROM ewallet_transactions et
        JOIN merchants m ON et.merchant_id = m.merchant_id
        WHERE et.status = 'COMPLETED'
        GROUP BY m.merchant_id, m.business_name
        ORDER BY total DESC
        LIMIT 10
    ");
    while($row = $ewallet_chart_q->fetch_assoc()){
        $ewallet_merchant_labels[] = $row['business_name'];
        $ewallet_merchant_data[] = $row['total'];
    }
}

// 3. FETCH DATA FOR OTHER PAGES

// USERS DATA
 $users = [];
if ($page == 'users') {
    $users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
}

// MERCHANTS DATA
 $merchants = [];
if ($page == 'merchants') {
    $merchants = $conn->query("SELECT m.*, u.full_name, u.email FROM merchants m JOIN users u ON m.user_id = u.user_id ORDER BY m.merchant_id DESC");
}

// TRANSACTIONS DATA (With Search)
 $transactions = [];
 $search_query = "";

if ($page == 'transactions' || $page == 'ewallets') {
    
    if ($page == 'transactions') {
        $sql = "
            SELECT p.*, m.business_name, u.full_name as customer_name
            FROM payments p
            JOIN bills b ON p.bill_id = b.bill_id
            JOIN utility_accounts ua ON b.account_id = ua.account_id
            JOIN billers bl ON ua.biller_id = bl.biller_id
            JOIN merchants m ON bl.merchant_id = m.merchant_id
            LEFT JOIN users u ON p.customer_id = u.user_id
            WHERE 1=1
        ";
    } else {
        // E-Wallets Query
        $sql = "
            SELECT et.*, m.business_name
            FROM ewallet_transactions et
            JOIN merchants m ON et.merchant_id = m.merchant_id
            WHERE 1=1
        ";
    }

    // SEARCH LOGIC
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $term = $conn->real_escape_string($_GET['search']);
        $sql .= " AND reference_number LIKE '%$term%'";
        $search_query = $_GET['search'];
    }

    $order_limit = " ORDER BY created_at DESC LIMIT 100";
    if($page == 'transactions') $order_limit = " ORDER BY paid_at DESC LIMIT 100";
    
    $sql .= $order_limit;

    if ($page == 'transactions') {
        $transactions = $conn->query($sql);
    } else {
        $ewallets = $conn->query($sql);
    }
}

// If we are not searching, still load standard data for E-Wallets
if ($page == 'ewallets' && !isset($ewallets)) {
    $ewallets = $conn->query("
        SELECT et.*, m.business_name
        FROM ewallet_transactions et
        JOIN merchants m ON et.merchant_id = m.merchant_id
        ORDER BY et.created_at DESC LIMIT 100
    ");
}

// Check for toast message
 $msg = '';
 $toast_type = 'success';
if (isset($_SESSION['toast'])) {
    $msg = $_SESSION['toast']['msg'];
    $toast_type = $_SESSION['toast']['type'];
    unset($_SESSION['toast']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://adminLTE.io/themes/v3/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://adminLTE.io/themes/v3/dist/css/adminlte.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .nav-link { cursor: pointer; }
        .table-sm td, .table-sm th { padding: .5rem; vertical-align: middle; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="admin.php" class="nav-link">Home</a>
      </li>
    </ul>

    <!-- SEARCH BAR (Visible on Transactions and E-Wallets) -->
    <?php if($page == 'transactions' || $page == 'ewallets'): ?>
    <form class="form-inline ml-3" action="admin.php" method="get">
        <input type="hidden" name="view" value="<?= $page ?>">
        <div class="input-group input-group-sm">
            <input class="form-control form-control-navbar" type="search" name="search" placeholder="Search Reference #..." aria-label="Search" value="<?= htmlspecialchars($search_query) ?>">
            <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                    <i class="fas fa-search"></i>
                </button>
                <?php if(!empty($search_query)): ?>
                <a href="admin.php?view=<?= $page ?>" class="btn btn-navbar text-danger">
                    <i class="fas fa-times"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </form>
    <?php endif; ?>

    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button"><i class="fas fa-expand-arrows-alt"></i></a>
      </li>
    </ul>
  </nav>

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="admin.php" class="brand-link">
      <span class="brand-text font-weight-light">UtilitySys Admin</span>
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
            <div class="img-circle elevation-2 bg-secondary d-flex align-items-center justify-content-center text-white" style="width:35px; height:35px;">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
        <div class="info pl-2">
          <a href="#" class="d-block"><?= $_SESSION['full_name'] ?></a>
          <span class="text-xs text-success">Administrator</span>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item">
            <a href="admin.php?view=dashboard" class="nav-link <?= $page == 'dashboard' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="admin.php?view=users" class="nav-link <?= $page == 'users' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-users"></i>
              <p>System Users</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="admin.php?view=merchants" class="nav-link <?= $page == 'merchants' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-store"></i>
              <p>Merchants</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="admin.php?view=transactions" class="nav-link <?= $page == 'transactions' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-list-alt"></i>
              <p>Transactions</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="admin.php?view=ewallets" class="nav-link <?= $page == 'ewallets' ? 'active' : '' ?>">
              <i class="nav-icon fa-solid fa-money-bill-wave"></i>
              <p>E-Wallets</p>
            </a>
          </li>
          <li class="nav-header">ACCOUNT</li>
          <li class="nav-item">
            <a href="logout.php" class="nav-link text-danger">
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
            <h1 class="m-0 text-capitalize"><?= $page ?> Overview</h1>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        
        <?php if($page == 'dashboard'): ?>
            <!-- DASHBOARD STATS -->
            <div class="row">
              <div class="col-lg-4 col-6">
                <div class="small-box bg-info">
                  <div class="inner">
                    <h3><?= $stats['customers'] ?></h3>
                    <p>Total Customers</p>
                  </div>
                  <div class="icon"><i class="fas fa-users"></i></div>
                </div>
              </div>
              <div class="col-lg-4 col-6">
                <div class="small-box bg-success">
                  <div class="inner">
                    <h3><?= $stats['merchants'] ?></h3>
                    <p>Active Merchants</p>
                  </div>
                  <div class="icon"><i class="fas fa-store"></i></div>
                </div>
              </div>
              <div class="col-lg-4 col-6">
                <div class="small-box bg-warning">
                  <div class="inner">
                    <h3>₱<?= number_format($stats['revenue'],2) ?></h3>
                    <p>Platform Revenue</p>
                  </div>
                  <div class="icon"><i class="fas fa-wallet"></i></div>
                </div>
              </div>
            </div>

            <!-- DASHBOARD CHARTS -->
            <div class="row mt-4">
                <!-- Bill Payments Chart -->
                <div class="col-md-6">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Top 10 Merchants (Bill Payments)</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="billMerchantChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <!-- E-Wallet Chart -->
                <div class="col-md-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Top 10 Merchants (E-Wallet)</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="ewalletMerchantChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($page == 'users'): ?>
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">System Users</h3>
              </div>
              <div class="card-body table-responsive p-0">
                <table class="table table-striped table-hover table-sm">
                  <thead class="thead-light">
                    <tr>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Role</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if($users->num_rows > 0): while($u = $users->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($u['full_name']) ?></td>
                      <td><?= htmlspecialchars($u['email']) ?></td>
                      <td><span class="badge badge-<?= $u['role']=='ADMIN'?'danger':($u['role']=='MERCHANT'?'warning':'primary') ?>"><?= $u['role'] ?></span></td>
                      <td><span class="badge badge-<?= $u['status']=='ACTIVE'?'success':'secondary' ?>"><?= $u['status'] ?></span></td>
                      <td>
                        <?php if($u['user_id'] != $_SESSION['user_id']): ?>
                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editUserModal<?= $u['user_id'] ?>"><i class="fas fa-edit"></i></button>
                        <?php else: ?>
                            <span class="text-muted small">Self</span>
                        <?php endif; ?>
                      </td>
                    </tr>

                    <!-- Edit User Modal -->
                    <div class="modal fade" id="editUserModal<?= $u['user_id'] ?>">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <form method="post">
                            <div class="modal-header">
                              <h5 class="modal-title">Edit User</h5>
                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                              </button>
                            </div>
                            <div class="modal-body">
                              <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                              <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" value="<?= $u['full_name'] ?>" class="form-control" required>
                              </div>
                              <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?= $u['email'] ?>" class="form-control" required>
                              </div>
                              <div class="form-group">
                                <label>Role</label>
                                <select name="role" class="form-control">
                                  <option value="CUSTOMER" <?= $u['role']=='CUSTOMER'?'selected':'' ?>>CUSTOMER</option>
                                  <option value="MERCHANT" <?= $u['role']=='MERCHANT'?'selected':'' ?>>MERCHANT</option>
                                  <option value="ADMIN" <?= $u['role']=='ADMIN'?'selected':'' ?>>ADMIN</option>
                                </select>
                              </div>
                              <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                  <option value="ACTIVE" <?= $u['status']=='ACTIVE'?'selected':'' ?>>ACTIVE</option>
                                  <option value="SUSPENDED" <?= $u['status']=='SUSPENDED'?'selected':'' ?>>SUSPENDED</option>
                                </select>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                              <button type="submit" name="edit_user" class="btn btn-primary">Save Changes</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>

                    <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center">No users found.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
        <?php endif; ?>

        <?php if($page == 'merchants'): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Merchant List</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-bordered table-sm table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Owner</th>
                                <th>Business Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($merchants->num_rows > 0): while($m = $merchants->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['full_name']) ?></td>
                                <td><?= htmlspecialchars($m['business_name']) ?></td>
                                <td><?= htmlspecialchars($m['email']) ?></td>
                                <td><span class="badge badge-<?= $m['status']=='APPROVED'?'success':($m['status']=='REJECTED'?'danger':'warning') ?>"><?= $m['status'] ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editMerchantModal<?= $m['merchant_id'] ?>"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>

                            <!-- Edit Merchant Modal -->
                            <div class="modal fade" id="editMerchantModal<?= $m['merchant_id'] ?>">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <form method="post">
                                    <div class="modal-header">
                                      <h5 class="modal-title">Edit Merchant</h5>
                                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                      </button>
                                    </div>
                                    <div class="modal-body">
                                      <input type="hidden" name="merchant_id" value="<?= $m['merchant_id'] ?>">
                                      <div class="form-group">
                                        <label>Owner Name</label>
                                        <input type="text" value="<?= htmlspecialchars($m['full_name']) ?>" class="form-control" disabled>
                                        <small class="text-muted">Editing owner name requires editing the user profile.</small>
                                      </div>
                                      <div class="form-group">
                                        <label>Business Name</label>
                                        <input type="text" name="business_name" value="<?= htmlspecialchars($m['business_name']) ?>" class="form-control" required>
                                      </div>
                                      <div class="form-group">
                                        <label>Application Status</label>
                                        <select name="status" class="form-control">
                                          <option value="PENDING" <?= $m['status']=='PENDING'?'selected':'' ?>>PENDING</option>
                                          <option value="APPROVED" <?= $m['status']=='APPROVED'?'selected':'' ?>>APPROVED</option>
                                          <option value="REJECTED" <?= $m['status']=='REJECTED'?'selected':'' ?>>REJECTED</option>
                                        </select>
                                      </div>
                                    </div>
                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                      <button type="submit" name="edit_merchant" class="btn btn-primary">Save Changes</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>

                            <?php endwhile; else: ?>
                                <tr><td colspan="5" class="text-center">No merchant records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if($page == 'transactions'): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bill Payment Transactions</h3>
                    <?php if(!empty($search_query)): ?>
                        <small class="text-muted">Filter: "<?= htmlspecialchars($search_query) ?>"</small>
                    <?php endif; ?>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-bordered table-sm table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Merchant</th>
                                <th>Ref #</th>
                                <th>Customer</th>
                                <th>Method</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($transactions->num_rows > 0): while($t = $transactions->fetch_assoc()): ?>
                            <tr>
                                <td><small><?= date('M d, h:i A', strtotime($t['paid_at'])) ?></small></td>
                                <td><?= htmlspecialchars($t['business_name']) ?></td>
                                <td><small class="font-weight-bold text-primary"><?= $t['reference_number'] ?></small></td>
                                <td><?= htmlspecialchars($t['customer_name'] ?? 'Walk-in') ?></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($t['payment_method']) ?></span></td>
                                <td class="text-right font-weight-bold text-success">₱<?= number_format($t['amount'], 2) ?></td>
                            </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="6" class="text-center">No transactions found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <?php if($page == 'ewallets'): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">E-Wallet Transactions</h3>
                    <?php if(!empty($search_query)): ?>
                        <small class="text-muted">Filter: "<?= htmlspecialchars($search_query) ?>"</small>
                    <?php endif; ?>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-bordered table-sm table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Merchant</th>
                                <th>Type</th>
                                <th>Ref #</th>
                                <th>Provider</th>
                                <th>Mobile #</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($ewallets) && $ewallets->num_rows > 0): while($e = $ewallets->fetch_assoc()): ?>
                            <tr>
                                <td><small><?= date('M d, h:i A', strtotime($e['created_at'])) ?></small></td>
                                <td><?= htmlspecialchars($e['business_name']) ?></td>
                                <td>
                                    <?php if($e['transaction_type'] == 'CASH_IN'): ?>
                                        <span class="badge badge-success">IN</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">OUT</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="font-weight-bold text-primary"><?= $e['reference_number'] ?></small></td>
                                <td><?= htmlspecialchars($e['provider']) ?></td>
                                <td><small><?= htmlspecialchars($e['mobile_number']) ?></small></td>
                                <td class="text-right font-weight-bold text-success">₱<?= number_format($e['total_amount'], 2) ?></td>
                            </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="7" class="text-center">No e-wallet transactions found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

      </div>
    </section>
  </div>
</div>
<script src="https://adminLTE.io/themes/v3/plugins/jquery/jquery.min.js"></script>
<script src="https://adminLTE.io/themes/v3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://adminLTE.io/themes/v3/dist/js/adminlte.min.js"></script>

<script>
    $(function () {
        // Toast Notification
        <?php if($msg): ?>
        $(document).Toasts('create', {
            title: 'System Notification',
            body: '<?= addslashes($msg) ?>',
            class: 'bg-<?= $toast_type ?>', 
            autohide: true,
            delay: 5000,
            position: 'topRight'
        });
        <?php endif; ?>

        <?php if($page == 'dashboard'): ?>
            // --- BILL PAYMENTS CHART ---
            var ctxBill = document.getElementById('billMerchantChart').getContext('2d');
            var billChart = new Chart(ctxBill, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($bill_merchant_labels) ?>,
                    datasets: [{
                        label: 'Total Sales (₱)',
                        data: <?= json_encode($bill_merchant_data) ?>,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // --- E-WALLET CHART ---
            var ctxEw = document.getElementById('ewalletMerchantChart').getContext('2d');
            var ewalletChart = new Chart(ctxEw, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($ewallet_merchant_labels) ?>,
                    datasets: [{
                        label: 'Total Sales (₱)',
                        data: <?= json_encode($ewallet_merchant_data) ?>,
                        backgroundColor: 'rgba(0, 123, 255, 0.7)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        <?php endif; ?>
    });
</script>
</body>
</html>