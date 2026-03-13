<?php
require_once 'config.php';

// 1. ACCESS CONTROL
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'MERCHANT') {
    redirect('login.php');
}

 $uid = $_SESSION['user_id'];
 $msg = '';
 $error = '';
 $toast_type = 'success';

// 2. FETCH MERCHANT DETAILS
 $stmt = $conn->prepare("SELECT merchant_id, business_name, status FROM merchants WHERE user_id = ?");
 $stmt->bind_param("i", $uid);
 $stmt->execute();
 $merchantRes = $stmt->get_result();

if ($merchantRes->num_rows == 0) {
    die("Merchant profile not found. Please contact admin.");
}

 $merchant = $merchantRes->fetch_assoc();
 $mid = $merchant['merchant_id'];
 $merchant_status = $merchant['status'];
 $merchant_name = $merchant['business_name'];

// 3. DETERMINE CURRENT PAGE
 $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
 $allowed_pages = ['dashboard', 'payment', 'transactions', 'services', 'reports', 'ewallet'];
if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// --- ACTION HANDLERS ---

// A. Delete Biller
if (isset($_GET['action']) && $_GET['action'] == 'delete_biller' && isset($_GET['id'])) {
    if (isset($_POST['access_code']) && $_POST['access_code'] === 'utilitySYS') {
        $bid = (int)$_GET['id'];
        $stmt = $conn->prepare("DELETE FROM billers WHERE biller_id = ? AND merchant_id = ?");
        $stmt->bind_param("ii", $bid, $mid);
        if($stmt->execute()) {
            $_SESSION['toast'] = ['msg' => "Service Deleted Successfully", 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['msg' => "Error deleting service: " . $stmt->error, 'type' => 'danger'];
        }
    } else {
        $_SESSION['toast'] = ['msg' => "Invalid Access Code! Deletion cancelled.", 'type' => 'danger'];
    }
    redirect("merchant.php?page=services");
    exit;
}

// B. Add Biller
if (isset($_POST['add_biller'])) {
    $name = trim($_POST['biller_name']);
    $cat = trim($_POST['category']);
    $acc_format = trim($_POST['account_format']);

    $stmt = $conn->prepare("INSERT INTO billers (merchant_id, biller_name, category, account_format) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $mid, $name, $cat, $acc_format);
    
    if ($stmt->execute()) {
        $_SESSION['toast'] = ['msg' => "Service Added Successfully", 'type' => 'success'];
    } else {
        $_SESSION['toast'] = ['msg' => "Error adding service: " . $stmt->error, 'type' => 'danger'];
    }
    redirect("merchant.php?page=services");
    exit;
}

// C. Edit Biller
if (isset($_POST['edit_biller'])) { 
    $bid = (int)$_POST['biller_id'];
    $name = trim($_POST['biller_name']);
    $cat = trim($_POST['category']);
    $acc_format = trim($_POST['account_format']);

    $stmt = $conn->prepare("UPDATE billers SET biller_name=?, category=?, account_format=? WHERE biller_id=? AND merchant_id=?");
    $stmt->bind_param("sssii", $name, $cat, $acc_format, $bid, $mid);
    
    if ($stmt->execute()) {
        $_SESSION['toast'] = ['msg' => "Service Updated Successfully", 'type' => 'success'];
    } else {
        $_SESSION['toast'] = ['msg' => "Error updating service: " . $stmt->error, 'type' => 'danger'];
    }
    redirect("merchant.php?page=services");
    exit;
}

// --- DELETE TRANSACTION (WITH ACCESS CODE) ---
if (isset($_GET['action']) && $_GET['action'] == 'delete_transaction' && isset($_GET['id'])) {
    if (isset($_POST['access_code']) && $_POST['access_code'] === 'utilitySYS') {
        $tid = (int)$_GET['id'];
        $stmt = $conn->prepare("DELETE p FROM payments p JOIN bills b ON p.bill_id = b.bill_id JOIN utility_accounts ua ON b.account_id = ua.account_id JOIN billers bl ON ua.biller_id = bl.biller_id WHERE p.payment_id = ? AND bl.merchant_id = ?");
        $stmt->bind_param("ii", $tid, $mid);
        
        if($stmt->execute()) {
            $_SESSION['toast'] = ['msg' => "Transaction Deleted Successfully", 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['msg' => "Error deleting transaction: " . $stmt->error, 'type' => 'danger'];
        }
    } else {
        $_SESSION['toast'] = ['msg' => "Invalid Access Code! Deletion cancelled.", 'type' => 'danger'];
    }
    redirect("merchant.php?page=transactions");
    exit;
}

// --- DELETE EWALLET TRANSACTION (WITH ACCESS CODE) ---
if (isset($_GET['action']) && $_GET['action'] == 'delete_ewallet' && isset($_GET['id'])) {
    if (isset($_POST['access_code']) && $_POST['access_code'] === 'utilitySYS') {
        $tid = (int)$_GET['id'];
        $stmt = $conn->prepare("DELETE FROM ewallet_transactions WHERE ewallet_id = ? AND merchant_id = ?");
        $stmt->bind_param("ii", $tid, $mid);
        
        if($stmt->execute()) {
            $_SESSION['toast'] = ['msg' => "E-Wallet Transaction Deleted", 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['msg' => "Error deleting transaction: " . $stmt->error, 'type' => 'danger'];
        }
    } else {
        $_SESSION['toast'] = ['msg' => "Invalid Access Code! Deletion cancelled.", 'type' => 'danger'];
    }
    redirect("merchant.php?page=ewallet");
    exit;
}

// --- UPDATE TRANSACTION (RESTRICTED FIELDS + ACCESS CODE) ---
if (isset($_POST['update_transaction'])) {
    if (!isset($_POST['access_code']) || $_POST['access_code'] !== 'utilitySYS') {
        $_SESSION['toast'] = ['msg' => "Invalid Access Code! Update cancelled.", 'type' => 'danger'];
        redirect("merchant.php?page=transactions");
        exit;
    }

    $pay_id = (int)$_POST['payment_id'];
    $new_amount = floatval($_POST['amount']);
    $new_cust_name = trim($_POST['customer_name']);
    $new_method = trim($_POST['payment_method']);
    
    $u_stmt = $conn->prepare("UPDATE utility_accounts ua JOIN bills b ON ua.account_id = b.account_id JOIN payments p ON b.bill_id = p.bill_id SET ua.account_holder_name = ? WHERE p.payment_id = ?");
    $u_stmt->bind_param("si", $new_cust_name, $pay_id);
    $u_stmt->execute();
    
    $p_stmt = $conn->prepare("UPDATE payments SET amount = ?, payment_method = ? WHERE payment_id = ?");
    $p_stmt->bind_param("dsi", $new_amount, $new_method, $pay_id);
    
    if($p_stmt->execute()) {
        $_SESSION['toast'] = ['msg' => "Transaction Updated Successfully", 'type' => 'success'];
    } else {
        $_SESSION['toast'] = ['msg' => "Error updating transaction: " . $p_stmt->error, 'type' => 'danger'];
    }
    redirect("merchant.php?page=transactions");
    exit;
}

// --- UPDATE EWALLET TRANSACTION (RESTRICTED FIELDS + ACCESS CODE) ---
if (isset($_POST['update_ewallet'])) {
    if (!isset($_POST['access_code']) || $_POST['access_code'] !== 'utilitySYS') {
        $_SESSION['toast'] = ['msg' => "Invalid Access Code! Update cancelled.", 'type' => 'danger'];
        redirect("merchant.php?page=ewallet");
        exit;
    }

    $ew_id = (int)$_POST['ewallet_id'];
    $new_amount = floatval($_POST['amount']);
    $new_acc_name = trim($_POST['account_name']);
    $new_provider = trim($_POST['provider']);

    $stmt = $conn->prepare("SELECT transaction_type, fee FROM ewallet_transactions WHERE ewallet_id = ?");
    $stmt->bind_param("i", $ew_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row = $res->fetch_assoc()){
        // Recalculate fee based on new logic (2%) or keep existing? 
        // Usually edits preserve the specific deal, but let's update Total based on new Amount + Existing Fee for simplicity
        $fee = $row['fee']; 
        $new_total = $new_amount + $fee;

        $upd = $conn->prepare("UPDATE ewallet_transactions SET provider = ?, account_name = ?, amount = ?, total_amount = ? WHERE ewallet_id = ?");
        $upd->bind_param("ssddi", $new_provider, $new_acc_name, $new_amount, $new_total, $ew_id);
        
        if($upd->execute()) {
            $_SESSION['toast'] = ['msg' => "E-Wallet Transaction Updated Successfully", 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['msg' => "Error updating: " . $upd->error, 'type' => 'danger'];
        }
    }
    redirect("merchant.php?page=ewallet");
    exit;
}

// --- PROCESS CASH IN ---
if (isset($_POST['process_ewallet_in'])) {
    $provider = trim($_POST['provider']);
    $mobile = trim($_POST['mobile_number']);
    $acc_name = trim($_POST['account_name']);
    $amount = floatval($_POST['amount']);
    
    // UPDATED: 2% Fee
    $fee_rate = 0.02; 
    $fee = $amount * $fee_rate;
    $total = $amount + $fee; 
    
    $ref = 'EW-IN-' . time();
    
    $stmt = $conn->prepare("INSERT INTO ewallet_transactions (merchant_id, transaction_type, provider, mobile_number, account_name, amount, fee, total_amount, reference_number, status) VALUES (?, 'CASH_IN', ?, ?, ?, ?, ?, ?, ?, 'COMPLETED')");
    $stmt->bind_param("isssddds", $mid, $provider, $mobile, $acc_name, $amount, $fee, $total, $ref);
    
    if($stmt->execute()){
        $_SESSION['last_receipt'] = [
            'type' => 'ewallet',
            'ref' => $ref,
            'date' => date('Y-m-d H:i:s'),
            'details' => "$provider CASH IN",
            'account' => "$acc_name ($mobile)",
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total
        ];
        $_SESSION['toast'] = ['msg' => "Cash In Successful!", 'type' => 'success'];
    } else {
        $_SESSION['toast'] = ['msg' => "Cash In failed: " . $stmt->error, 'type' => 'danger'];
    }
    redirect("merchant.php?page=ewallet");
    exit;
}

// --- PROCESS CASH OUT ---
if (isset($_POST['process_ewallet_out'])) {
    $provider = trim($_POST['provider']);
    $mobile = trim($_POST['mobile_number']);
    $acc_name = trim($_POST['account_name']);
    $amount = floatval($_POST['amount']);
    
    // UPDATED: 2% Fee
    $fee_rate = 0.02;
    $fee = $amount * $fee_rate;
    $total = $amount + $fee;
    
    $ref = 'EW-OUT-' . time();
    
    $stmt = $conn->prepare("INSERT INTO ewallet_transactions (merchant_id, transaction_type, provider, mobile_number, account_name, amount, fee, total_amount, reference_number, status) VALUES (?, 'CASH_OUT', ?, ?, ?, ?, ?, ?, ?, 'COMPLETED')");
    $stmt->bind_param("isssddds", $mid, $provider, $mobile, $acc_name, $amount, $fee, $total, $ref);
    
    if($stmt->execute()){
        $_SESSION['last_receipt'] = [
            'type' => 'ewallet',
            'ref' => $ref,
            'date' => date('Y-m-d H:i:s'),
            'details' => "$provider CASH OUT",
            'account' => "$acc_name ($mobile)",
            'amount' => $amount,
            'fee' => $fee,
            'total' => $total
        ];
        $_SESSION['toast'] = ['msg' => "Cash Out Successful!", 'type' => 'success'];
    } else {
        $_SESSION['toast'] = ['msg' => "Cash Out failed: " . $stmt->error, 'type' => 'danger'];
    }
    redirect("merchant.php?page=ewallet");
    exit;
}

// D. PROCESS PAYMENT (WITH 3% FEE)
 $last_payment_id = 0; 
if (isset($_POST['process_payment'])) {
    $biller_id = (int)$_POST['biller_id'];
    $account_number = trim($_POST['account_number']);
    $account_name = trim($_POST['account_name']); 
    $amount = floatval($_POST['amount']);
    $payment_method = trim($_POST['payment_method']); 
    
    $selected_cust_id = isset($_POST['customer_id']) ? $_POST['customer_id'] : 'manual';
    $cust_name = trim($_POST['customer_name']);
    $cust_email = trim($_POST['customer_email']);

    // Verify biller
    $check = $conn->prepare("SELECT biller_name FROM billers WHERE biller_id = ? AND merchant_id = ?");
    $check->bind_param("ii", $biller_id, $mid);
    $check->execute();
    $res = $check->get_result();

    if($res->num_rows > 0) {
        $biller_data = $res->fetch_assoc();
        
        // --- STEP 1: CUSTOMER LOGIC ---
        $customer_user_id = NULL;
        
        if ($selected_cust_id != 'manual' && is_numeric($selected_cust_id)) {
            $customer_user_id = (int)$selected_cust_id;
        } 
        else {
            if (!empty($cust_email)) {
                $u_check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
                $u_check->bind_param("s", $cust_email);
                $u_check->execute();
                $u_res = $u_check->get_result();
                if ($u_res->num_rows > 0) {
                    $customer_user_id = $u_res->fetch_assoc()['user_id'];
                }
            }

            if (!$customer_user_id && !empty($cust_name)) {
                $u_check = $conn->prepare("SELECT user_id FROM users WHERE full_name = ? LIMIT 1");
                $u_check->bind_param("s", $cust_name);
                $u_check->execute();
                $u_res = $u_check->get_result();
                if ($u_res->num_rows > 0) {
                    $customer_user_id = $u_res->fetch_assoc()['user_id'];
                }
            }

            if (!$customer_user_id) {
                $stmt_walkin = $conn->prepare("INSERT INTO walkin_customers (full_name, email, created_at) VALUES (?, ?, NOW())");
                $stmt_walkin->bind_param("ss", $cust_name, $cust_email);
                $stmt_walkin->execute();
            }
        }
        
        // --- STEP 2: UTILITY ACCOUNT LOGIC ---
        $acc_q = $conn->prepare("SELECT account_id FROM utility_accounts WHERE biller_id = ? AND account_number = ?");
        $acc_q->bind_param("is", $biller_id, $account_number);
        $acc_q->execute();
        $acc_res = $acc_q->get_result();

        $utility_account_id = 0;

        if($acc_res->num_rows > 0) {
            $acc_row = $acc_res->fetch_assoc();
            $utility_account_id = $acc_row['account_id'];
            $upd_acc = $conn->prepare("UPDATE utility_accounts SET account_holder_name = ?, user_id = ? WHERE account_id = ?");
            $upd_acc->bind_param("sii", $account_name, $customer_user_id, $utility_account_id);
            $upd_acc->execute();
        } else {
            $stmt_ins_acc = $conn->prepare("INSERT INTO utility_accounts (biller_id, account_number, account_holder_name, user_id, status) VALUES (?, ?, ?, ?, 'ACTIVE')");
            $stmt_ins_acc->bind_param("issi", $biller_id, $account_number, $account_name, $customer_user_id);
            $stmt_ins_acc->execute();
            $utility_account_id = $stmt_ins_acc->insert_id;
        }

        // --- FEE CALCULATION ---
        $service_fee_rate = 0.03; // 3%
        $service_fee = $amount * $service_fee_rate;
        $total_amount = $amount + $service_fee;

        // --- STEP 3: CREATE BILL ---
        $stmt_bill = $conn->prepare("INSERT INTO bills (account_id, amount, status, bill_date, due_date) VALUES (?, ?, 'UNPAID', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY))");
        $stmt_bill->bind_param("id", $utility_account_id, $amount);
        $stmt_bill->execute();
        $bill_id = $stmt_bill->insert_id;

        // --- STEP 4: PROCESS PAYMENT ---
        $ref = 'M' . $mid . '-' . time() . rand(100,999);
        $stmt_pay = $conn->prepare("INSERT INTO payments (bill_id, customer_id, amount, status, paid_at, reference_number, payment_method) VALUES (?, ?, ?, 'SUCCESS', NOW(), ?, ?)");
        $stmt_pay->bind_param("iidsd", $bill_id, $customer_user_id, $total_amount, $ref, $payment_method);
        
        if($stmt_pay->execute()){
            $conn->query("UPDATE bills SET status='PAID' WHERE bill_id=$bill_id");
            $last_payment_id = $conn->insert_id;
            
            // Store Receipt Data in Session
            $_SESSION['last_receipt'] = [
                'type' => 'bill',
                'ref' => $ref,
                'date' => date('Y-m-d H:i:s'),
                'details' => $biller_data['biller_name'] . " - " . $account_number,
                'account' => $account_name,
                'amount' => $amount,
                'fee' => $service_fee,
                'total' => $total_amount
            ];
            
            $_SESSION['toast'] = ['msg' => "Payment Processed! Ref: " . $ref, 'type' => 'success'];
        } else {
            $_SESSION['toast'] = ['msg' => "Payment failed: " . $stmt_pay->error, 'type' => 'danger'];
        }
    } else {
        $_SESSION['toast'] = ['msg' => "Invalid Service selected.", 'type' => 'danger'];
    }
    redirect("merchant.php?page=payment");
    exit;
}

// Check Toast
if (isset($_SESSION['toast'])) {
    $msg = $_SESSION['toast']['msg'];
    $toast_type = $_SESSION['toast']['type'];
    unset($_SESSION['toast']);
}

// --- DATA FETCHING ---

// Dashboard Data
 $dash_stats = ['sales' => 0, 'trans' => 0, 'services' => 0];
 $chart_labels = [];
 $chart_data = [];
 $payment_methods_labels = [];
 $payment_methods_data = [];
 $ewallet_labels = [];
 $ewallet_data = [];

if ($page == 'dashboard') {
    $stats_q = $conn->prepare("
        SELECT COUNT(p.payment_id) as t_count, COALESCE(SUM(p.amount), 0) as t_sales 
        FROM payments p 
        JOIN bills b ON p.bill_id = b.bill_id 
        JOIN utility_accounts ua ON b.account_id = ua.account_id 
        JOIN billers bl ON ua.biller_id = bl.biller_id 
        WHERE bl.merchant_id = ? AND p.status='SUCCESS'
    ");
    $stats_q->bind_param("i", $mid);
    $stats_q->execute();
    $stats_res = $stats_q->get_result()->fetch_assoc();
    $dash_stats['trans'] = $stats_res['t_count'];
    $dash_stats['sales'] = $stats_res['t_sales'];
    $dash_stats['services'] = $conn->query("SELECT COUNT(*) as cnt FROM billers WHERE merchant_id=$mid")->fetch_assoc()['cnt'];

    for($i=6; $i>=0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $chart_labels[] = date('M d', strtotime($d));
        
        $c_q = $conn->prepare("
            SELECT COALESCE(SUM(p.amount), 0) as daily_total
            FROM payments p 
            JOIN bills b ON p.bill_id = b.bill_id 
            JOIN utility_accounts ua ON b.account_id = ua.account_id 
            JOIN billers bl ON ua.biller_id = bl.biller_id 
            WHERE bl.merchant_id = ? AND DATE(p.paid_at) = ?
        ");
        $c_q->bind_param("is", $mid, $d);
        $c_q->execute();
        $chart_data[] = $c_q->get_result()->fetch_assoc()['daily_total'];
    }

    $method_q = $conn->prepare("
        SELECT p.payment_method, COALESCE(SUM(p.amount), 0) as total 
        FROM payments p 
        JOIN bills b ON p.bill_id = b.bill_id 
        JOIN utility_accounts ua ON b.account_id = ua.account_id 
        JOIN billers bl ON ua.biller_id = bl.biller_id 
        WHERE bl.merchant_id = ? AND p.status='SUCCESS'
        GROUP BY p.payment_method
        ORDER BY total DESC
    ");
    $method_q->bind_param("i", $mid);
    $method_q->execute();
    $method_res = $method_q->get_result();
    while($row = $method_res->fetch_assoc()){
        $payment_methods_labels[] = $row['payment_method'];
        $payment_methods_data[] = $row['total'];
    }

    $ewallet_q = $conn->prepare("
        SELECT provider, COALESCE(SUM(amount), 0) as total 
        FROM ewallet_transactions 
        WHERE merchant_id = ? AND status='COMPLETED'
        GROUP BY provider
        ORDER BY total DESC
    ");
    $ewallet_q->bind_param("i", $mid);
    $ewallet_q->execute();
    $ewallet_res = $ewallet_q->get_result();
    while($row = $ewallet_res->fetch_assoc()){
        $ewallet_labels[] = $row['provider'];
        $ewallet_data[] = $row['total'];
    }
}

// E-Wallet Data
 $ewallet_stats = ['in' => 0, 'out' => 0];
 $ewallet_list = [];
if ($page == 'ewallet') {
    $estats = $conn->query("SELECT 
        SUM(CASE WHEN transaction_type='CASH_IN' THEN total_amount ELSE 0 END) as total_in,
        SUM(CASE WHEN transaction_type='CASH_OUT' THEN total_amount ELSE 0 END) as total_out
        FROM ewallet_transactions WHERE merchant_id=$mid")->fetch_assoc();
    $ewallet_stats['in'] = $estats['total_in'] ?: 0;
    $ewallet_stats['out'] = $estats['total_out'] ?: 0;

    $el_q = $conn->prepare("SELECT * FROM ewallet_transactions WHERE merchant_id = ? ORDER BY created_at DESC LIMIT 50");
    $el_q->bind_param("i", $mid);
    $el_q->execute();
    $ewallet_list = $el_q->get_result();
}

// Transactions Data
 $transactions = [];
if ($page == 'transactions') {
    $trans = $conn->prepare("
        SELECT p.payment_id, p.paid_at, p.reference_number, p.amount as total_amount, p.status, p.payment_method,
               b.amount as bill_amount,
               COALESCE(u.full_name, ua.account_holder_name, 'Walk-in') as cust_name,
               bl.biller_name, ua.account_number, ua.account_holder_name
        FROM payments p 
        LEFT JOIN users u ON p.customer_id = u.user_id 
        JOIN bills b ON p.bill_id = b.bill_id 
        JOIN utility_accounts ua ON b.account_id = ua.account_id 
        JOIN billers bl ON ua.biller_id = bl.biller_id 
        WHERE bl.merchant_id = ? 
        ORDER BY p.paid_at DESC LIMIT 100
    ");
    $trans->bind_param("i", $mid);
    $trans->execute();
    $transactions = $trans->get_result();
}

// Services Data
 $services_list = [];
if ($page == 'services') {
    $services_list = $conn->query("SELECT * FROM billers WHERE merchant_id=$mid ORDER BY biller_name ASC");
}

// Payment Data
 $billers_dropdown = [];
 $customers_dropdown = []; 
if ($page == 'payment') {
    $billers_dropdown = $conn->query("SELECT biller_id, biller_name FROM billers WHERE merchant_id=$mid ORDER BY biller_name ASC");
    $customers_dropdown = $conn->query("SELECT user_id, full_name, email FROM users WHERE role='CUSTOMER' ORDER BY full_name ASC");
}

// Reports Data
 $report_data = [];
 $report_summary = ['total' => 0, 'count' => 0];
 $date_start = isset($_GET['date_start']) ? $_GET['date_start'] : date('Y-m-01');
 $date_end = isset($_GET['date_end']) ? $_GET['date_end'] : date('Y-m-d');

if ($page == 'reports') {
    $r_q = $conn->prepare("
        SELECT p.payment_id, p.paid_at, p.reference_number, p.amount as total_amount, p.payment_method,
               b.amount as bill_amount,
               COALESCE(u.full_name, ua.account_holder_name, 'Walk-in') as cust_name,
               bl.biller_name, ua.account_number
        FROM payments p 
        LEFT JOIN users u ON p.customer_id = u.user_id 
        JOIN bills b ON p.bill_id = b.bill_id 
        JOIN utility_accounts ua ON b.account_id = ua.account_id 
        JOIN billers bl ON ua.biller_id = bl.biller_id 
        WHERE bl.merchant_id = ? AND DATE(p.paid_at) BETWEEN ? AND ?
        ORDER BY p.paid_at DESC
    ");
    $r_q->bind_param("iss", $mid, $date_start, $date_end);
    $r_q->execute();
    $report_data = $r_q->get_result();
    
    $sum_q = $conn->prepare("
        SELECT COUNT(p.payment_id) as cnt, COALESCE(SUM(p.amount), 0) as total_amt
        FROM payments p 
        JOIN bills b ON p.bill_id = b.bill_id 
        JOIN utility_accounts ua ON b.account_id = ua.account_id 
        JOIN billers bl ON ua.biller_id = bl.biller_id 
        WHERE bl.merchant_id = ? AND DATE(p.paid_at) BETWEEN ? AND ?
    ");
    $sum_q->bind_param("iss", $mid, $date_start, $date_end);
    $sum_q->execute();
    $res_sum = $sum_q->get_result()->fetch_assoc();
    $report_summary['total'] = $res_sum['total_amt'];
    $report_summary['count'] = $res_sum['cnt'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Merchant Panel | Utilities</title>
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://adminlte.io/themes/v3/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Source Sans Pro', sans-serif; }
        
        .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); }
        .card-title { font-weight: 600; color: #495057; }
        .small-box { border-radius: 0.5rem; }
        .small-box .inner h3 { font-size: 2.2rem; font-weight: 700; }
        
        .inline-label { font-size: 0.85rem; font-weight: 600; color: #555; margin-bottom: 2px; }
        .form-section-title {
            font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px; 
            color: #007bff; font-weight: 700; margin: 10px 0 5px 0; border-bottom: 2px solid #eee; padding-bottom: 5px;
        }
        
        .fee-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-left: 5px solid #28a745;
            padding: 10px 15px;
            border-radius: 4px;
        }
        .fee-row { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: 0.9rem; }
        .fee-total { font-weight: bold; font-size: 1.1rem; color: #28a745; border-top: 1px solid #ccc; padding-top: 5px; margin-top: 5px; }
        
        #paymentForm .inline-label {
            font-size: 1.1rem; font-weight: 700; margin-bottom: 8px;
        }
        #paymentForm .form-control, #paymentForm .custom-select {
            font-size: 1.2rem; height: 55px; padding: 10px 15px; border-radius: 6px;
        }
        #paymentForm .input-group-text { font-size: 1.2rem; height: 55px; display: flex; align-items: center; }
        #paymentForm .btn { font-size: 1.2rem; padding: 12px 24px; border-radius: 6px; }
        #paymentForm .fee-box { transform: scale(1.1); transform-origin: top left; width: 80%; margin-bottom: 10px; padding: 15px 20px; }
        #paymentForm .fee-row { font-size: 1.1rem; margin-bottom: 4px; }
        #paymentForm .fee-total { font-size: 1.4rem; }
        #paymentForm select.form-control { padding-left: 8px; } 

        /* RECEIPT STYLES */
        .receipt-box {
            background: #fff;
            padding: 20px;
            border: 1px dashed #333;
            font-family: 'Courier New', Courier, monospace;
            max-width: 400px;
            margin: 0 auto;
        }
        .receipt-header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
        .receipt-title { font-size: 1.2rem; font-weight: bold; text-transform: uppercase; }
        .receipt-body { font-size: 0.9rem; }
        .receipt-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .receipt-divider { border-top: 1px dashed #000; margin: 10px 0; }
        .receipt-total { font-weight: bold; font-size: 1.1rem; display: flex; justify-content: space-between; }
        
        /* PRINT MEDIA QUERY */
        @media print {
            body * { visibility: hidden; }
            #receiptModal, #receiptModal * { visibility: visible; }
            #receiptModal { position: absolute; left: 0; top: 0; width: 100%; margin: 0; padding: 0; border: none; background: white; }
            .no-print { display: none !important; }
            .modal-dialog { max-width: 100%; margin: 0; }
            .modal-content { border: none; box-shadow: none; }
            .receipt-box { border: none; padding: 0; max-width: 100%; }
        }
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
        <a href="merchant.php?page=dashboard" class="nav-link">Home</a>
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
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="merchant.php?page=dashboard" class="brand-link">
      <span class="brand-text font-weight-light pl-3">UtilitySys Merchant</span>
    </a>
    
    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
            <div class="img-circle elevation-2 bg-secondary d-flex align-items-center justify-content-center text-white" style="width:35px; height:35px;">
                <i class="fas fa-user"></i>
            </div>
        </div>
        <div class="info pl-2">
            <a href="#" class="d-block"><?= htmlspecialchars($_SESSION['full_name']) ?></a>
            <small class="text-success"><?= htmlspecialchars($merchant_name) ?></small>
        </div>
      </div>
      
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item">
            <a href="merchant.php?page=dashboard" class="nav-link <?= $page == 'dashboard' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="merchant.php?page=payment" class="nav-link <?= $page == 'payment' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-cash-register"></i>
              <p>Accept Payment</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="merchant.php?page=ewallet" class="nav-link <?= $page == 'ewallet' ? 'active' : '' ?>">
              <i class="nav-icon fa-solid fa-money-bill-wave"></i>
              <p>E-Wallet</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="merchant.php?page=transactions" class="nav-link <?= $page == 'transactions' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-list-alt"></i>
              <p>Bills Transactions</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="merchant.php?page=reports" class="nav-link <?= $page == 'reports' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-chart-line"></i>
              <p>Reports</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="merchant.php?page=services" class="nav-link <?= $page == 'services' ? 'active' : '' ?>">
              <i class="nav-icon fas fa-cogs"></i>
              <p>Manage Services</p>
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
    
    <?php if($merchant_status == 'PENDING'): ?>
    <div class="alert alert-warning m-3 shadow-sm">
        <h5><i class="icon fas fa-exclamation-triangle"></i> Account Pending!</h5>
        Your account is under review. You can configure services, but payments are disabled.
    </div>
    <?php elseif($merchant_status == 'REJECTED'): ?>
    <div class="alert alert-danger m-3 shadow-sm">
        <h5><i class="icon fas fa-ban"></i> Account Rejected!</h5>
        Please contact support.
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <section class="content">
      <div class="container-fluid">
        
        <?php if($page == 'dashboard'): ?>
            <!-- DASHBOARD -->
            <div class="row mb-4">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>₱<?= number_format($dash_stats['sales'], 2) ?></h3>
                            <p>Total Revenue</p>
                        </div>
                        <div class="icon"><i class="fas fa-wallet"></i></div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= $dash_stats['trans'] ?></h3>
                            <p>Payments</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3><?= $dash_stats['services'] ?></h3>
                            <p>Services</p>
                        </div>
                        <div class="icon"><i class="fas fa-bolt"></i></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card card-default">
                        <div class="card-header">
                            <h3 class="card-title">Transaction Trend (Last 7 Days)</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" height="150"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-default">
                        <div class="card-header">
                            <h3 class="card-title">Sales by Method</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="methodChart" height="250"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-default">
                        <div class="card-header">
                            <h3 class="card-title">E-Wallet Usage</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="ewalletChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-default mt-4">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <a href="merchant.php?page=payment" class="btn btn-success"><i class="fas fa-cash-register mr-1"></i> New Bill Payment</a>
                    <a href="merchant.php?page=ewallet" class="btn btn-primary"><i class="fas fa-wallet mr-1"></i> E-Wallet Load</a>
                    <a href="merchant.php?page=services" class="btn btn-info"><i class="fas fa-plus mr-1"></i> Add Service</a>
                    <a href="merchant.php?page=reports" class="btn btn-secondary"><i class="fas fa-file-invoice mr-1"></i> View Reports</a>
                </div>
            </div>

        <?php elseif($page == 'ewallet'): ?>
            <!-- E-WALLET PAGE -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card bg-success">
                        <div class="card-body">
                            <h3 class="text-white">₱<?= number_format($ewallet_stats['in'], 2) ?></h3>
                            <p class="text-white mb-0">Total Cash In</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-danger">
                        <div class="card-body">
                            <h3 class="text-white">₱<?= number_format($ewallet_stats['out'], 2) ?></h3>
                            <p class="text-white mb-0">Total Cash Out</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Cash In Form -->
                <div class="col-md-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-arrow-down mr-2"></i> Cash In</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="merchant.php?page=ewallet">
                                <div class="form-group">
                                    <label>Provider</label>
                                    <select name="provider" class="form-control" required>
                                        <option value="GCASH">GCASH</option>
                                        <option value="MAYA">Maya</option>
                                        <option value="PAYPAL">PayPal</option>
                                        <option value="DPAY">Dana Pay</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Mobile Number</label>
                                    <input type="text" name="mobile_number" class="form-control" required placeholder="09xxxxxxxxx">
                                </div>
                                <div class="form-group">
                                    <label>Account Name</label>
                                    <input type="text" name="account_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Amount (₱)</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" required>
                                </div>
                                <div class="alert alert-info small">
                                    <i class="fas fa-info-circle"></i> A 2% service fee will be added to the total.
                                </div>
                                <button type="submit" name="process_ewallet_in" class="btn btn-success btn-block">Process Cash In</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Cash Out Form -->
                <div class="col-md-6">
                    <div class="card card-danger card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-arrow-up mr-2"></i> Cash Out</h3>
                        </div>
                        <div class="card-body">
                            <form method="post" action="merchant.php?page=ewallet">
                                <div class="form-group">
                                    <label>Provider</label>
                                    <select name="provider" class="form-control" required>
                                        <option value="GCASH">GCASH</option>
                                        <option value="MAYA">Maya</option>
                                        <option value="PAYPAL">PayPal</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Mobile Number</label>
                                    <input type="text" name="mobile_number" class="form-control" required placeholder="09xxxxxxxxx">
                                </div>
                                <div class="form-group">
                                    <label>Account Name</label>
                                    <input type="text" name="account_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Amount to Withdraw (₱)</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" required>
                                </div>
                                <div class="alert alert-warning small">
                                    <i class="fas fa-exclamation-triangle"></i> A 2% fee applies.
                                </div>
                                <button type="submit" name="process_ewallet_out" class="btn btn-danger btn-block">Process Cash Out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inline Edit E-Wallet Form -->
            <div class="card card-warning card-outline mb-4" id="ewalletEditContainer" style="display:none;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-edit mr-2"></i> Edit E-Wallet Transaction</h3>
                    <button type="button" class="btn btn-sm btn-default float-right" onclick="$('#ewalletEditContainer').slideUp();">Cancel</button>
                </div>
                <div class="card-body">
                    <form method="post" action="merchant.php?page=ewallet" id="ewalletEditForm">
                        <input type="hidden" name="ewallet_id" id="edit_ew_id">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Provider</label>
                                    <select name="provider" id="edit_ew_provider" class="form-control">
                                        <option value="GCASH">GCASH</option>
                                        <option value="MAYA">Maya</option>
                                        <option value="PAYPAL">PayPal</option>
                                        <option value="DPAY">Dana Pay</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Account Name</label>
                                    <input type="text" name="account_name" id="edit_ew_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Amount</label>
                                    <input type="number" step="0.01" name="amount" id="edit_ew_amount" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" name="update_ewallet" class="btn btn-warning btn-block">Update</button>
                            </div>
                        </div>
                        <input type="hidden" name="access_code" id="ew_edit_code" value="">
                    </form>
                </div>
            </div>

            <div class="card card-primary card-outline mt-4">
                <div class="card-header">
                    <h3 class="card-title">Recent E-Wallet Transactions</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Provider</th>
                                    <th>Mobile #</th>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>Fee</th>
                                    <th>Total</th>
                                    <th>Ref</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($ewallet_list->num_rows == 0): ?>
                                    <tr><td colspan="10" class="text-center">No E-Wallet transactions yet.</td></tr>
                                <?php else: ?>
                                    <?php while($ew = $ewallet_list->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date('M d, h:i A', strtotime($ew['created_at'])) ?></td>
                                        <td>
                                            <?php if($ew['transaction_type'] == 'CASH_IN'): ?>
                                                <span class="badge badge-success">IN</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">OUT</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($ew['provider']) ?></td>
                                        <td><?= htmlspecialchars($ew['mobile_number']) ?></td>
                                        <td><?= htmlspecialchars($ew['account_name']) ?></td>
                                        <td>₱<?= number_format($ew['amount'], 2) ?></td>
                                        <td>₱<?= number_format($ew['fee'], 2) ?></td>
                                        <td class="font-weight-bold">₱<?= number_format($ew['total_amount'], 2) ?></td>
                                        <td><small><?= $ew['reference_number'] ?></small></td>
                                        <td>
                                            <button class="btn btn-xs btn-default text-primary" onclick='editEwallet(<?= json_encode($ew) ?>)'><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-xs btn-default text-danger" onclick='confirmDeleteEwallet(<?= $ew['ewallet_id'] ?>)'><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif($page == 'payment'): ?>
            <!-- ACCEPT PAYMENT -->
            <div class="card card-success card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cash-register mr-2"></i> Process Payment</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="merchant.php?page=payment" id="paymentForm">
                        
                        <!-- Section 1: Biller -->
                        <div class="form-section-title"><i class="fas fa-file-invoice-dollar"></i> Biller Information</div>
                        <div class="row mb-2">
                            <div class="col-md-4 mb-3">
                                <div class="form-group mb-0">
                                    <label class="inline-label">Service / Biller</label>
                                    <select name="biller_id" class="form-control custom-select" required style="width: 100%;">
                                        <option value="" disabled selected>Select Biller</option>
                                        <?php 
                                        if($billers_dropdown->num_rows > 0) $billers_dropdown->data_seek(0);
                                        while($b = $billers_dropdown->fetch_assoc()): ?>
                                            <option value="<?= $b['biller_id'] ?>"><?= htmlspecialchars($b['biller_name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-group mb-0">
                                    <label class="inline-label">Account Number</label>
                                    <input type="text" name="account_number" class="form-control" required placeholder="e.g. 09123456789">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-group mb-0">
                                    <label class="inline-label">Account Name</label>
                                    <input type="text" name="account_name" class="form-control" required placeholder="Name on bill">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Section 2: Customer -->
                        <div class="form-section-title"><i class="fas fa-user"></i> Customer Information</div>
                        <div class="row mb-2">
                            <div class="col-md-4 mb-3">
                                <div class="form-group mb-0">
                                    <label class="inline-label">Customer Type</label>
                                    <select name="customer_id" id="cust_select" class="form-control custom-select" onchange="toggleCustomerInput()" required>
                                        <option value="manual">-- Walk-in / New --</option>
                                        <?php 
                                        if($customers_dropdown->num_rows > 0) $customers_dropdown->data_seek(0);
                                        while($c = $customers_dropdown->fetch_assoc()): ?>
                                            <option value="<?= $c['user_id'] ?>"><?= htmlspecialchars($c['full_name']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <div id="manual_cust_fields" style="display:none;" class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group mb-0">
                                            <label class="inline-label">Name</label>
                                            <input type="text" name="customer_name" id="input_cust_name" class="form-control" placeholder="Full Name">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-group mb-0">
                                            <label class="inline-label">Email</label>
                                            <input type="email" name="customer_email" id="input_cust_email" class="form-control" placeholder="Optional">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Payment -->
                        <div class="form-section-title"><i class="fas fa-money-bill-wave"></i> Payment Details</div>
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-3">
                                <div class="form-group mb-0">
                                    <label class="inline-label">Bill Amount</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend"><span class="input-group-text">₱</span></div>
                                        <input type="number" step="0.01" name="amount" class="form-control" id="input_amount" required>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="fee-box">
                                    <div class="fee-row">
                                        <span>Bill Amount:</span>
                                        <span id="display_bill">₱0.00</span>
                                    </div>
                                    <div class="fee-row text-muted">
                                        <small>Fee (3%):</small>
                                        <small id="display_fee">₱0.00</small>
                                    </div>
                                    <div class="fee-row fee-total">
                                        <span>Total:</span>
                                        <span id="display_total">₱0.00</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <div class="form-group mb-0">
                                    <label class="inline-label">Method</label>
                                    <select name="payment_method" class="form-control custom-select" required>
                                        <option value="Cash">Cash</option>
                                        <option value="GCASH">GCASH</option>
                                        <option value="MAYA">MAYA</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mt-2 text-right">
                                <button type="submit" name="process_payment" class="btn btn-success shadow px-4"><i class="fas fa-check mr-1"></i> Process Payment</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif($page == 'transactions'): ?>
            <!-- TRANSACTIONS -->
            
            <!-- Inline Edit Form -->
            <div class="card card-info card-outline mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-edit mr-2"></i> Edit Transaction</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="merchant.php?page=transactions" id="editFormInline">
                        <input type="hidden" name="payment_id" id="edit_pay_id">
                        <input type="hidden" name="access_code" id="trans_edit_code" value="">
                        
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Amount (Total)</label>
                                    <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control" required placeholder="Select a transaction">
                                    <small class="text-muted">Editable</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Customer Name</label>
                                    <input type="text" name="customer_name" id="edit_cust_name" class="form-control" required placeholder="Select a transaction">
                                    <small class="text-muted">Editable</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Payment Method</label>
                                    <select name="payment_method" id="edit_method" class="form-control">
                                        <option value="Cash">Cash</option>
                                        <option value="GCASH">GCASH</option>
                                        <option value="MAYA">MAYA</option>
                                        <option value="Others">Others</option>
                                    </select>
                                    <small class="text-muted">Editable</small>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" onclick="submitTransEdit()" class="btn btn-primary btn-block">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bill Payment History</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sm" id="transactionsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Ref #</th>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Acc #</th>
                                    <th>Method</th>
                                    <th>Total</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($transactions->num_rows == 0): ?>
                                    <tr><td colspan="8" class="text-center p-4">No transactions found</td></tr>
                                <?php else: ?>
                                    <?php while($t = $transactions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date('M d, h:i A', strtotime($t['paid_at'])) ?></td>
                                        <td><small class="text-muted font-weight-bold"><?= $t['reference_number'] ?></small></td>
                                        <td><?= htmlspecialchars($t['cust_name']) ?></td>
                                        <td><?= htmlspecialchars($t['biller_name']) ?></td>
                                        <td><small><?= htmlspecialchars($t['account_number']) ?></small></td>
                                        <td><span class="badge badge-secondary"><?= htmlspecialchars($t['payment_method']) ?></span></td>
                                        <td class="font-weight-bold text-success">₱<?= number_format($t['total_amount'], 2) ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-default text-info" onclick='editTransaction(<?= json_encode($t) ?>)'><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-sm btn-default text-danger" onclick='confirmDeleteTrans(<?= $t['payment_id'] ?>)'><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif($page == 'reports'): ?>
            <!-- REPORTS -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Financial Reports</h3>
                </div>
                <div class="card-body">
                    <form method="get" class="form-row align-items-end mb-4 bg-light p-3 rounded">
                        <input type="hidden" name="page" value="reports">
                        <div class="col-md-4 mb-2">
                            <label class="mb-1 small font-weight-bold">Start Date:</label>
                            <input type="date" name="date_start" value="<?= $date_start ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="mb-1 small font-weight-bold">End Date:</label>
                            <input type="date" name="date_end" value="<?= $date_end ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4 mb-2">
                            <button type="submit" class="btn btn-primary btn-sm btn-block"><i class="fas fa-filter mr-1"></i> Filter</button>
                        </div>
                    </form>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-peso-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Revenue</span>
                                    <span class="info-box-number h3" id="reportTotalDisplay">₱<?= number_format($report_summary['total'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-receipt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Transactions</span>
                                    <span class="info-box-number h3" id="reportCountDisplay"><?= $report_summary['count'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm" id="reportsTable">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Reference</th>
                                    <th>Customer</th>
                                    <th>Service</th>
                                    <th>Method</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($report_data->num_rows > 0): while($r = $report_data->fetch_assoc()): ?>
                                <tr>
                                    <td><?= date('M d, Y', strtotime($r['paid_at'])) ?></td>
                                    <td><?= $r['reference_number'] ?></td>
                                    <td><?= htmlspecialchars($r['cust_name']) ?></td>
                                    <td><?= htmlspecialchars($r['biller_name']) ?></td>
                                    <td><?= htmlspecialchars($r['payment_method']) ?></td>
                                    <td class="font-weight-bold text-success">₱<?= number_format($r['total_amount'], 2) ?></td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="7" class="text-center p-4">No data for selected range</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif($page == 'services'): ?>
            <!-- SERVICES -->
            
            <!-- Inline Add Form -->
            <div class="card card-info card-outline mb-4">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-plus mr-2"></i> Add New Service</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="merchant.php?page=services">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group"><label>Service Name</label><input type="text" name="biller_name" class="form-control" required></div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group"><label>Category</label>
                                    <select name="category" class="form-control">
                                        <option>Electricity</option><option>Water</option><option>WiFi</option><option>Cable</option><option>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group"><label>Account Format</label><input type="text" name="account_format" class="form-control" placeholder="e.g. 11 digits"></div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="add_biller" class="btn btn-primary btn-block">Add</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Inline Edit Form (Initially Hidden) -->
            <div id="editServiceContainer" class="card card-warning card-outline mb-4" style="display:none;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-edit mr-2"></i> Edit Service</h3>
                    <button type="button" class="btn btn-sm btn-default float-right" onclick="$('#editServiceContainer').slideUp();">Cancel</button>
                </div>
                <div class="card-body">
                    <form method="post" action="merchant.php?page=services">
                        <input type="hidden" name="biller_id" id="edit_service_id">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group"><label>Service Name</label><input type="text" name="biller_name" id="edit_service_name" class="form-control" required></div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group"><label>Category</label>
                                    <select name="category" id="edit_service_cat" class="form-control">
                                        <option value="Electricity">Electricity</option>
                                        <option value="Water">Water</option>
                                        <option value="WiFi">WiFi</option>
                                        <option value="Cable">Cable</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group"><label>Account Format</label><input type="text" name="account_format" id="edit_service_fmt" class="form-control"></div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="edit_biller" class="btn btn-warning btn-block">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Service List</h3> 
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="thead-light">
                                <tr><th>Name</th><th>Category</th><th>Format</th><th style="width:150px">Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php if($services_list->num_rows == 0): ?>
                                    <tr><td colspan="4" class="text-center p-4">No services added yet.</td></tr>
                                <?php else: ?>
                                    <?php while($s = $services_list->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($s['biller_name']) ?></td>
                                        <td><span class="badge badge-secondary"><?= htmlspecialchars($s['category']) ?></span></td>
                                        <td><code><?= htmlspecialchars($s['account_format'] ?: 'Any') ?></code></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" onclick='showEditService(<?= json_encode($s) ?>)'><i class="fas fa-edit"></i></button>
                                            <button class="btn btn-danger btn-sm" onclick='confirmDeleteBiller(<?= $s['biller_id'] ?>)'><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

      </div>
    </section>
  </div>

</div>

<!-- Access Code Modal -->
<div class="modal fade" id="accessCodeModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Security Check</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Please enter the access code to proceed with this action.</p>
        <div class="form-group">
            <label>Access Code</label>
            <input type="password" class="form-control" id="modalAccessCode" placeholder="Enter code...">
        </div>
        <p class="text-danger small" id="accessError"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmAccessBtn">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- SUCCESSFUL TRANSACTION MODAL -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content text-center">
            <div class="modal-body p-5">
                <div class="mb-3">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                <h3 class="font-weight-bold mb-3">Transaction Successful!</h3>
                <p class="text-muted mb-4">The transaction has been completed successfully.</p>
                
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-primary" id="btnViewReceipt"><i class="fas fa-receipt mr-2"></i>View Receipt</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- RECEIPT MODAL -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header no-print">
                <h5 class="modal-title">Transaction Receipt</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="receipt-box" id="printableReceipt">
                    <div class="receipt-header">
                        <div class="receipt-title"><?= htmlspecialchars($merchant_name) ?></div>
                        <div class="small text-muted"><?= date('F j, Y') ?></div>
                    </div>
                    <div class="receipt-body">
                        <div class="receipt-row">
                            <span>Ref #:</span>
                            <span id="rcpt_ref"></span>
                        </div>
                        <div class="receipt-row">
                            <span>Date:</span>
                            <span id="rcpt_date"></span>
                        </div>
                        <div class="receipt-divider"></div>
                        <div class="receipt-row">
                            <span>Description:</span>
                            <span id="rcpt_desc"></span>
                        </div>
                        <div class="receipt-row">
                            <span>Account/Name:</span>
                            <span id="rcpt_acc"></span>
                        </div>
                        <div class="receipt-divider"></div>
                        <div class="receipt-row">
                            <span>Amount:</span>
                            <span id="rcpt_amt"></span>
                        </div>
                        <div class="receipt-row">
                            <span>Fee:</span>
                            <span id="rcpt_fee"></span>
                        </div>
                        <div class="receipt-total">
                            <span>TOTAL:</span>
                            <span id="rcpt_total"></span>
                        </div>
                        <div class="receipt-divider"></div>
                        <div class="text-center small text-muted mt-2">
                            Thank you for your transaction!<br>
                            This serves as your official receipt.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer no-print">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-dark" onclick="window.print()"><i class="fas fa-print mr-2"></i>Print</button>
            </div>
        </div>
    </div>
</div>

<script src="https://adminlte.io/themes/v3/plugins/jquery/jquery.min.js"></script>
<script src="https://adminlte.io/themes/v3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://adminlte.io/themes/v3/dist/js/adminlte.min.js"></script>

<script>
 var pendingAction = null;

 $(function () {
    // Toast
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
    
    // Success Pop-up Logic
    <?php if(isset($_SESSION['last_receipt'])): ?>
        var receiptData = <?= json_encode($_SESSION['last_receipt']) ?>;
        $('#successModal').modal('show');
        $('#btnViewReceipt').on('click', function(){
            showReceipt(receiptData);
            $('#successModal').modal('hide');
        });
        // Clear receipt from session so it doesn't pop up again on refresh
        // (Ideally you'd unset it in PHP, but JS handles the UI here)
    <?php unset($_SESSION['last_receipt']); endif; ?>

    toggleCustomerInput();
    calculateFee();
    initChart();
    initMethodChart();
    initEWalletChart();
});

// --- ACCESS CODE MODAL HANDLING ---
function promptAccess(callback) {
    $('#modalAccessCode').val('');
    $('#accessError').text('');
    pendingAction = callback;
    $('#accessCodeModal').modal('show');
    $('#modalAccessCode').focus();
}

 $('#confirmAccessBtn').click(function(){
    var code = $('#modalAccessCode').val();
    if(code === 'utilitySYS') {
        $('#accessCodeModal').modal('hide');
        if(pendingAction) pendingAction();
    } else {
        $('#accessError').text('Invalid Access Code');
    }
});

// Init Sales Chart (Line)
function initChart() {
    if(document.getElementById('salesChart')) {
        var ctx = document.getElementById('salesChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Revenue (₱)',
                    data: <?= json_encode($chart_data) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
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
    }
}

// Init Payment Method Chart (Bar)
function initMethodChart() {
    if(document.getElementById('methodChart')) {
        var ctx = document.getElementById('methodChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($payment_methods_labels) ?>,
                datasets: [{
                    label: 'Total Sales',
                    data: <?= json_encode($payment_methods_data) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
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
    }
}

// Init E-Wallet Chart (Bar)
function initEWalletChart() {
    if(document.getElementById('ewalletChart')) {
        var ctx = document.getElementById('ewalletChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($ewallet_labels) ?>,
                datasets: [{
                    label: 'Total Amount',
                    data: <?= json_encode($ewallet_data) ?>,
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
    }
}

// Fee Calc
 $('#input_amount').on('input', calculateFee);

function calculateFee() {
    var amount = parseFloat($('#input_amount').val()) || 0;
    var fee = amount * 0.03;
    var total = amount + fee;
    
    $('#display_bill').text('₱' + amount.toFixed(2));
    $('#display_fee').text('₱' + fee.toFixed(2));
    $('#display_total').text('₱' + total.toFixed(2));
}

function toggleCustomerInput() {
    var val = $('#cust_select').val();
    var manualDiv = $('#manual_cust_fields');
    var nameInput = $('#input_cust_name');
    
    if (val === 'manual') {
        manualDiv.slideDown();
        nameInput.prop('required', true);
    } else {
        manualDiv.slideUp();
        nameInput.prop('required', false);
    }
}

// --- TRANSACTION ACTIONS ---

function editTransaction(data) {
    $('#edit_pay_id').val(data.payment_id);
    $('#edit_amount').val(data.total_amount);
    $('#edit_cust_name').val(data.cust_name); 
    $('#edit_method').val(data.payment_method);
    
    $('html, body').animate({
        scrollTop: $("#editFormInline").offset().top - 100
    }, 500);
}

function submitTransEdit() {
    promptAccess(function(){
        $('#trans_edit_code').val('utilitySYS');
        $('#editFormInline').submit();
    });
}

function confirmDeleteTrans(id) {
    promptAccess(function(){
        var form = $('<form action="merchant.php" method="post">');
        form.append('<input type="hidden" name="action" value="delete_transaction">');
        form.append('<input type="hidden" name="id" value="'+id+'">');
        form.append('<input type="hidden" name="page" value="transactions">');
        form.append('<input type="hidden" name="access_code" value="utilitySYS">');
        $('body').append(form);
        form.submit();
    });
}

// --- EWALLET ACTIONS ---

function editEwallet(data) {
    $('#edit_ew_id').val(data.ewallet_id);
    $('#edit_ew_provider').val(data.provider);
    $('#edit_ew_name').val(data.account_name);
    $('#edit_ew_amount').val(data.amount);

    $('#ewalletEditContainer').slideDown();
    $('html, body').animate({
        scrollTop: $("#ewalletEditContainer").offset().top - 100
    }, 500);
}

 $('#ewalletEditForm button[type="submit"]').click(function(e){
    e.preventDefault();
    promptAccess(function(){
        $('#ew_edit_code').val('utilitySYS');
        $('#ewalletEditForm').off('submit').submit(); 
    });
});

function confirmDeleteEwallet(id) {
    promptAccess(function(){
        var form = $('<form action="merchant.php" method="post">');
        form.append('<input type="hidden" name="action" value="delete_ewallet">');
        form.append('<input type="hidden" name="id" value="'+id+'">');
        form.append('<input type="hidden" name="page" value="ewallet">');
        form.append('<input type="hidden" name="access_code" value="utilitySYS">');
        $('body').append(form);
        form.submit();
    });
}

// --- BILLER ACTIONS ---

function showEditService(data) {
    $('#edit_service_id').val(data.biller_id);
    $('#edit_service_name').val(data.biller_name);
    $('#edit_service_cat').val(data.category);
    $('#edit_service_fmt').val(data.account_format);
    
    $('#editServiceContainer').slideDown();
    $('html, body').animate({
        scrollTop: $("#editServiceContainer").offset().top - 100
    }, 500);
}

function confirmDeleteBiller(id) {
    promptAccess(function(){
        var form = $('<form action="merchant.php" method="post">');
        form.append('<input type="hidden" name="action" value="delete_biller">');
        form.append('<input type="hidden" name="id" value="'+id+'">');
        form.append('<input type="hidden" name="page" value="services">');
        form.append('<input type="hidden" name="access_code" value="utilitySYS">');
        $('body').append(form);
        form.submit();
    });
}

// --- RECEIPT FUNCTIONS ---

function showReceipt(data) {
    $('#rcpt_ref').text(data.ref);
    $('#rcpt_date').text(data.date);
    $('#rcpt_desc').text(data.details);
    $('#rcpt_acc').text(data.account);
    $('#rcpt_amt').text('₱' + parseFloat(data.amount).toFixed(2));
    $('#rcpt_fee').text('₱' + parseFloat(data.fee).toFixed(2));
    $('#rcpt_total').text('₱' + parseFloat(data.total).toFixed(2));
    
    $('#receiptModal').modal('show');
}
</script>
</body>
</html>