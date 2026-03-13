<?php
require_once 'config.php';

// 1. ACCESS CONTROL
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'CUSTOMER') {
    if(function_exists('redirect')){
        redirect('login.php');
    } else {
        header("Location: login.php");
        exit;
    }
}

 $uid = $_SESSION['user_id'];
 $msg = '';
 $toast_type = 'success';

// 2. FETCH CUSTOMER DETAILS
 $stmt = $conn->prepare("SELECT full_name, email FROM users WHERE user_id = ?");
 $stmt->bind_param("i", $uid);
 $stmt->execute();
 $userRes = $stmt->get_result();

if ($userRes->num_rows == 0) {
    die("User profile not found.");
}

 $user = $userRes->fetch_assoc();
 $customer_name = $user['full_name'];

// 3. DETERMINE CURRENT PAGE
 $page = isset($_GET['page']) ? $_GET['page'] : 'history';
 $allowed_pages = ['history', 'profile', 'merchants'];
if (!in_array($page, $allowed_pages)) {
    $page = 'history';
}

// --- DATA FETCHING ---

// Dashboard/History Data
 $transactions = [];
 $stats = ['total_paid' => 0, 'total_trans' => 0];
 $search_query = "";

// Fetch transactions with Search Logic
if ($page == 'history') {
    $sql = "
        SELECT p.payment_id, p.paid_at, p.reference_number, p.amount as total_amount, p.status, p.payment_method,
               b.amount as bill_amount,
               bl.biller_name, 
               ua.account_number, 
               ua.account_holder_name,
               m.business_name
        FROM payments p 
        JOIN bills b ON p.bill_id = b.bill_id 
        JOIN utility_accounts ua ON b.account_id = ua.account_id 
        JOIN billers bl ON ua.biller_id = bl.biller_id 
        JOIN merchants m ON bl.merchant_id = m.merchant_id
        WHERE p.customer_id = ? 
    ";

    // SEARCH FILTER
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $term = $conn->real_escape_string($_GET['search']);
        $sql .= " AND p.reference_number LIKE '%$term%'";
        $search_query = $_GET['search'];
    }

    $sql .= " ORDER BY p.paid_at DESC";

    $trans_q = $conn->prepare($sql);
    $trans_q->bind_param("i", $uid);
    $trans_q->execute();
    $transactions = $trans_q->get_result();

    // Calculate Stats (Separate query to avoid search affecting stats)
    $stats_q = $conn->prepare("
        SELECT COUNT(p.payment_id) as cnt, COALESCE(SUM(p.amount), 0) as total
        FROM payments p
        WHERE p.customer_id = ? AND p.status = 'SUCCESS'
    ");
    $stats_q->bind_param("i", $uid);
    $stats_q->execute();
    $stats_res = $stats_q->get_result()->fetch_assoc();
    $stats['total_paid'] = $stats_res['total'];
    $stats['total_trans'] = $stats_res['cnt'];
}

// Merchants Data
 $merchants_list = [];
if ($page == 'merchants') {
    // Only show APPROVED merchants
    $m_q = $conn->query("SELECT merchant_id, business_name, status FROM merchants WHERE status='APPROVED' ORDER BY business_name ASC");
    $merchants_list = $m_q;
}

// Check Toast
if (isset($_SESSION['toast'])) {
    $msg = $_SESSION['toast']['msg'];
    $toast_type = $_SESSION['toast']['type'];
    unset($_SESSION['toast']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Panel | Utilities</title>
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/dist/css/adminlte.min.css">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Source Sans Pro', sans-serif; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* Sidebar Active State */
        .nav-sidebar .nav-link.active { 
            background-color: #17a2b8 !important; 
            color: white !important; 
            box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        }
        
        .content-wrapper { flex: 1; }
        
        /* Merchant Card Styles */
        .merchant-card {
            transition: transform 0.2s;
            border: none;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        }
        .merchant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,.15);
        }
        
        /* --- PRINT STYLES (Kept for browser default behavior if user Ctrl+P, though button removed) --- */
        @media print {
            body * { visibility: hidden; }
            #receiptModal.show, #receiptModal.show * { visibility: visible; }
            #receiptModal.show { position: absolute; left: 0; top: 0; width: 100%; height: 100%; background: white; z-index: 9999; }
            .modal-footer, .modal-header { display: none !important; }
            .receipt-container { box-shadow: none; border: none; margin: 0; }
        }

        /* Receipt Styling */
        .receipt-container {
            font-family: 'Courier New', Courier, monospace;
            background: #fff;
            max-width: 320px;
            margin: 0 auto;
            border: 1px solid #eee;
            padding: 20px;
            font-size: 14px;
            color: #000;
        }
        .receipt-header { text-align: center; margin-bottom: 20px; border-bottom: 2px dashed #333; padding-bottom: 10px; }
        .receipt-header h3 { margin: 0; font-size: 18px; text-transform: uppercase; letter-spacing: 1px; }
        .receipt-info-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; }
        .receipt-items { margin: 15px 0; border-top: 1px dashed #333; border-bottom: 1px dashed #333; padding: 10px 0; }
        .receipt-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; margin-top: 10px; }
        .receipt-footer { text-align: center; margin-top: 20px; font-size: 12px; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="customer.php?page=history" class="nav-link">Home</a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-info elevation-4">
    <a href="customer.php?page=history" class="brand-link">
      <span class="brand-text font-weight-light pl-3">UtilitySys Customer</span>
    </a>
    
    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
            <div class="img-circle elevation-2 bg-info d-flex align-items-center justify-content-center text-white" style="width:35px; height:35px;">
                <i class="fas fa-user"></i>
            </div>
        </div>
        <div class="info pl-2">
            <a href="#" class="d-block"><?= htmlspecialchars($customer_name) ?></a>
            <small class="text-warning">Customer Account</small>
        </div>
      </div>
      
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item">
            <a href="customer.php?page=history" class="nav-link <?= $page == 'history' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-history"></i>
              <p>Transaction History</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="customer.php?page=merchants" class="nav-link <?= $page == 'merchants' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-store"></i>
              <p>Find Merchants</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="customer.php?page=profile" class="nav-link <?= $page == 'profile' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-id-card"></i>
              <p>My Profile</p>
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
    
    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        
        <?php if($page == 'history'): ?>
            <!-- HISTORY DASHBOARD -->
            <div class="row mb-4">
                <div class="col-lg-6 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>₱<?= number_format($stats['total_paid'], 2) ?></h3>
                            <p>Total Paid</p>
                        </div>
                        <div class="icon"><i class="fas fa-wallet"></i></div>
                    </div>
                </div>
                <div class="col-lg-6 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $stats['total_trans'] ?></h3>
                            <p>Total Transactions</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">My Payments</h3>
                    <!-- Search Bar -->
                    <div class="card-tools">
                        <form action="customer.php" method="get" class="form-inline">
                            <input type="hidden" name="page" value="history">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Search Ref #..." value="<?= htmlspecialchars($search_query) ?>">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                                    <?php if(!empty($search_query)): ?>
                                    <a href="customer.php?page=history" class="btn btn-default text-danger"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Reference #</th>
                                    <th>Business / Service</th>
                                    <th>Account #</th>
                                    <th>Method</th>
                                    <th>Total Amount</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($transactions->num_rows == 0): ?>
                                    <tr><td colspan="7" class="text-center p-4">No transactions found.</td></tr>
                                <?php else: ?>
                                    <?php while($t = $transactions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date('M d, h:i A', strtotime($t['paid_at'])) ?></td>
                                        <td><small class="text-muted font-weight-bold"><?= $t['reference_number'] ?></small></td>
                                        <td>
                                            <div><?= htmlspecialchars($t['business_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($t['biller_name']) ?></small>
                                        </td>
                                        <td><small><?= htmlspecialchars($t['account_number']) ?></small></td>
                                        <td><span class="badge badge-secondary"><?= htmlspecialchars($t['payment_method']) ?></span></td>
                                        <td class="font-weight-bold text-success">₱<?= number_format($t['total_amount'], 2) ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-default text-info" title="View Receipt" onclick='openReceiptModal(<?= json_encode($t) ?>)'>
                                                <i class="fas fa-receipt"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif($page == 'merchants'): ?>
            <!-- FIND MERCHANTS PAGE -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Available Merchants</h3>
                    <div class="card-tools">
                        <span class="badge badge-success">Approved Partners</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if($merchants_list->num_rows > 0): ?>
                        <div class="row">
                            <?php while($m = $merchants_list->fetch_assoc()): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card merchant-card h-100">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <div class="img-circle bg-light d-flex align-items-center justify-content-center text-info mx-auto" style="width: 60px; height: 60px; font-size: 24px;">
                                                <i class="fas fa-store"></i>
                                            </div>
                                        </div>
                                        <h5 class="card-title"><?= htmlspecialchars($m['business_name']) ?></h5>
                                        <p class="text-muted small mb-3">Status: <?= $m['status'] ?></p>
                                        
                                        <!-- Action buttons removed as requested -->
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning text-center">
                            <h5><i class="icon fas fa-exclamation-triangle"></i> No Merchants Found</h5>
                            There are currently no approved merchants available.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif($page == 'profile'): ?>
            <!-- PROFILE PAGE -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">My Profile</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="img-circle elevation-2 bg-info d-flex align-items-center justify-content-center text-white mx-auto mb-3" style="width:100px; height:100px; font-size: 40px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <h3><?= htmlspecialchars($customer_name) ?></h3>
                            <p class="text-muted">Customer</p>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 150px;">Full Name:</th>
                                    <td><?= htmlspecialchars($customer_name) ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                </tr>
                                <tr>
                                    <th>Role:</th>
                                    <td>CUSTOMER</td>
                                </tr>
                                <tr>
                                    <th>Member Since:</th>
                                    <td>-</td> 
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

      </div>
    </section>
  </div>

  <!-- RECEIPT MODAL (Print Button Removed) -->
  <div class="modal fade" id="receiptModal" tabindex="-1">
      <div class="modal-dialog modal-sm">
          <div class="modal-content">
              <div class="modal-header border-0 pb-0">
                  <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                  <div id="printArea">
                      <div class="receipt-container">
                          <div class="receipt-header">
                              <h3 id="r_business_name">Business Name</h3>
                              <p>Official Receipt</p>
                          </div>
                          <div class="receipt-body">
                              <div class="receipt-info-row">
                                  <span>Date:</span>
                                  <span id="r_date">-</span>
                              </div>
                              <div class="receipt-info-row">
                                  <span>Ref #:</span>
                                  <span id="r_ref">-</span>
                              </div>
                              <div class="receipt-info-row">
                                  <span>Customer:</span>
                                  <span id="r_cust">-</span>
                              </div>
                              
                              <div class="receipt-items">
                                  <div style="font-weight:bold; margin-bottom:5px; text-transform:uppercase;" id="r_service">Service Name</div>
                                  
                                  <div class="receipt-info-row">
                                      <span>Acc #:</span>
                                      <span id="r_acc">-</span>
                                  </div>
                                  <div class="receipt-info-row">
                                      <span>Acc Name:</span>
                                      <span id="r_acc_name">-</span>
                                  </div>
                                  
                                  <hr style="border-top: 1px dashed #ccc; margin: 10px 0;">
                                  <div class="receipt-info-row">
                                      <span>Bill Subtotal:</span>
                                      <span id="r_subtotal">₱0.00</span>
                                  </div>
                                  <div class="receipt-info-row">
                                      <span>Service Fee:</span>
                                      <span id="r_fee">₱0.00</span>
                                  </div>

                                  <div class="receipt-info-row">
                                      <span>Method:</span>
                                      <span id="r_method">-</span>
                                  </div>
                              </div>

                              <div class="receipt-total">
                                  <span>TOTAL PAID:</span>
                                  <span id="r_amount">₱0.00</span>
                              </div>
                          </div>
                          <div class="receipt-footer">
                              <span style="font-weight:bold; display:block; margin-bottom:5px;">Thank you!</span>
                              <small>Powered by UtilitySys</small>
                          </div>
                      </div>
                  </div>
              </div>
              <div class="modal-footer border-0">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <!-- Print Button Removed Here -->
              </div>
          </div>
      </div>
  </div>

</div>

<script src="https://adminlte.io/themes/v3/plugins/jquery/jquery.min.js"></script>
<script src="https://adminlte.io/themes/v3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://adminlte.io/themes/v3/dist/js/adminlte.min.js"></script>

<script>
 $(function () {
    // Toast
    <?php if($msg): ?>
    $(document).Toasts('create', {
        title: 'Notification',
        body: '<?= addslashes($msg) ?>',
        class: 'bg-<?= $toast_type ?>', 
        autohide: true,
        delay: 5000,
        position: 'topRight'
    });
    <?php endif; ?>
});

function openReceiptModal(data) {
    var dateObj = new Date(data.paid_at);
    var dateStr = dateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) 
                  + ' ' + dateObj.toLocaleTimeString('en-US');

    var billAmount = parseFloat(data.bill_amount || 0);
    var totalAmount = parseFloat(data.total_amount || 0);
    var fee = totalAmount - billAmount;

    $('#r_business_name').text(data.business_name || "Merchant");
    $('#r_date').text(dateStr);
    $('#r_ref').text(data.reference_number);
    $('#r_cust').text("<?= addslashes($customer_name) ?>");
    
    $('#r_service').text(data.biller_name);
    $('#r_acc').text(data.account_number);
    $('#r_acc_name').text(data.account_holder_name || 'N/A'); 
    $('#r_method').text(data.payment_method || 'Cash');

    $('#r_subtotal').text('₱' + billAmount.toFixed(2));
    $('#r_fee').text('₱' + fee.toFixed(2));
    $('#r_amount').text('₱' + totalAmount.toFixed(2));

    $('#receiptModal').modal('show');
}
</script>
</body>
</html>