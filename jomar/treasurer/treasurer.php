<?php
session_start();
require_once '../config.php';

// Require login and treasurer role
requireRole('treasurer');

// Helper function to format amounts
function formatAmount($amount) {
    return number_format((float)$amount, 2);
}

// Helper to generate Voucher Number
function generateVoucherNo($conn) {
    $prefix = "DISB-" . date('Ym') . "-";
    
    $query = "SELECT voucher_no FROM disbursements WHERE voucher_no LIKE '$prefix%' ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $lastNo = $row['voucher_no'];
        $num = (int)substr($lastNo, -4);
        $num++;
    } else {
        $num = 1;
    }
    
    return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
}

// --- HANDLE ACTIONS ---

// 1. Add Disbursement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_disbursement') {
    $payeeName = mysqli_real_escape_string($conn, $_POST['payeeName']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    $customCategoryInput = isset($_POST['customCategory']) ? trim($_POST['customCategory']) : '';
    if ($category !== 'Others' || empty($customCategoryInput)) {
        $customCategoryDB = "NULL";
    } else {
        $customCategoryDB = "'" . mysqli_real_escape_string($conn, $customCategoryInput) . "'";
    }

    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $paymentDate = mysqli_real_escape_string($conn, $_POST['paymentDate']);
    
    // Payment Mode Fields
    $paymentMode = mysqli_real_escape_string($conn, $_POST['paymentMode']);
    $bankName = isset($_POST['bankName']) ? mysqli_real_escape_string($conn, $_POST['bankName']) : '';
    $bankAccountNumber = isset($_POST['bankAccountNumber']) ? mysqli_real_escape_string($conn, $_POST['bankAccountNumber']) : '';
    $checkNumber = isset($_POST['checkNumber']) ? mysqli_real_escape_string($conn, $_POST['checkNumber']) : '';
    $checkDate = isset($_POST['checkDate']) ? mysqli_real_escape_string($conn, $_POST['checkDate']) : '';

    if($paymentMode == 'Cash') {
        $bankName = "NULL";
        $bankAccountNumber = "NULL";
        $checkNumber = "NULL";
        $checkDate = "NULL";
    } elseif($paymentMode == 'Bank') {
        $checkNumber = "NULL";
        $checkDate = "NULL";
        $bankName = "'" . $bankName . "'";
        $bankAccountNumber = "'" . $bankAccountNumber . "'";
    } elseif($paymentMode == 'Check') {
        $bankName = "'" . $bankName . "'";
        $bankAccountNumber = "'" . $bankAccountNumber . "'";
        $checkNumber = "'" . $checkNumber . "'";
        $checkDate = !empty($checkDate) ? "'" . $checkDate . "'" : "NULL";
    }
    
    $voucherNo = generateVoucherNo($conn);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    $checkVoucher = "SELECT id FROM disbursements WHERE voucher_no = '$voucherNo'";
    $resultV = mysqli_query($conn, $checkVoucher);

    if (mysqli_num_rows($resultV) > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: Voucher Number generation conflict. Please try again.']);
        exit();
    }

    $query = "INSERT INTO disbursements (payee_name, category, custom_category, amount, payment_date, voucher_no, remarks, payment_mode, bank_name, bank_account_number, check_number, check_date) 
              VALUES ('$payeeName', '$category', $customCategoryDB, '$amount', '$paymentDate', '$voucherNo', '$remarks', '$paymentMode', $bankName, $bankAccountNumber, $checkNumber, $checkDate)";

    if (mysqli_query($conn, $query)) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => "Disbursement recorded successfully.", 
            'voucher_no' => $voucherNo,
            'payee' => $payeeName,
            'category' => $category,
            'custom_category' => $customCategoryInput,
            'amount' => $amount,
            'date' => $paymentDate,
            'remarks' => $remarks,
            'payment_mode' => $paymentMode,
            'bank_name' => $paymentMode == 'Cash' ? '' : $_POST['bankName'],
            'bank_account_number' => $paymentMode == 'Cash' ? '' : $_POST['bankAccountNumber'],
            'check_number' => isset($_POST['checkNumber']) ? $_POST['checkNumber'] : '',
            'check_date' => isset($_POST['checkDate']) ? $_POST['checkDate'] : ''
        ]);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . mysqli_error($conn)]);
        exit();
    }
}

// 2. Cancel Disbursement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'cancel_disbursement') {
    $disbursementId = mysqli_real_escape_string($conn, $_POST['disbursementId']);
    $verificationCode = isset($_POST['verification_code']) ? $_POST['verification_code'] : '';

    if ($verificationCode !== 'sjc2026') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid verification code.']);
        exit();
    }

    $query = "DELETE FROM disbursements WHERE id = $disbursementId";
    if (mysqli_query($conn, $query)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Disbursement cancelled successfully']);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
        exit();
    }
}

// 3. Edit Disbursement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_disbursement') {
    $id = mysqli_real_escape_string($conn, $_POST['edit_id']);
    $category = mysqli_real_escape_string($conn, $_POST['edit_category']);
    $amount = mysqli_real_escape_string($conn, $_POST['edit_amount']);
    $remarks = mysqli_real_escape_string($conn, $_POST['edit_remarks']);

    $paymentMode = mysqli_real_escape_string($conn, $_POST['edit_paymentMode']);
    $bankName = isset($_POST['edit_bankName']) ? mysqli_real_escape_string($conn, $_POST['edit_bankName']) : '';
    $bankAccountNumber = isset($_POST['edit_bankAccountNumber']) ? mysqli_real_escape_string($conn, $_POST['edit_bankAccountNumber']) : '';
    $checkNumber = isset($_POST['edit_checkNumber']) ? mysqli_real_escape_string($conn, $_POST['edit_checkNumber']) : '';
    $checkDate = isset($_POST['edit_checkDate']) ? mysqli_real_escape_string($conn, $_POST['edit_checkDate']) : '';

    if($paymentMode == 'Cash') {
        $bankName = "NULL";
        $bankAccountNumber = "NULL";
        $checkNumber = "NULL";
        $checkDate = "NULL";
    } elseif($paymentMode == 'Bank') {
        $checkNumber = "NULL";
        $checkDate = "NULL";
        $bankName = "'" . $bankName . "'";
        $bankAccountNumber = "'" . $bankAccountNumber . "'";
    } elseif($paymentMode == 'Check') {
        $bankName = "'" . $bankName . "'";
        $bankAccountNumber = "'" . $bankAccountNumber . "'";
        $checkNumber = "'" . $checkNumber . "'";
        $checkDate = !empty($checkDate) ? "'" . $checkDate . "'" : "NULL";
    }

    $customCategoryInput = isset($_POST['edit_customCategory']) ? trim($_POST['edit_customCategory']) : '';
    if ($category !== 'Others' || empty($customCategoryInput)) {
        $customCategoryDB = "NULL";
    } else {
        $customCategoryDB = "'" . mysqli_real_escape_string($conn, $customCategoryInput) . "'";
    }

    $query = "UPDATE disbursements 
              SET category = '$category', 
                  custom_category = $customCategoryDB, 
                  amount = '$amount', 
                  remarks = '$remarks',
                  payment_mode = '$paymentMode',
                  bank_name = $bankName,
                  bank_account_number = $bankAccountNumber,
                  check_number = $checkNumber,
                  check_date = $checkDate
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Disbursement updated successfully']);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . mysqli_error($conn)]);
        exit();
    }
}

// 4. Add Bank Transaction (UPDATED with Reference No)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_bank_transaction') {
    $bankId = mysqli_real_escape_string($conn, $_POST['bank_id']);
    $transType = mysqli_real_escape_string($conn, $_POST['trans_type']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $transDate = mysqli_real_escape_string($conn, $_POST['trans_date']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    // Reference Number (For Deposit/Withdrawal)
    $referenceNo = isset($_POST['reference_no']) ? trim($_POST['reference_no']) : '';
    $refDB = (!empty($referenceNo) && ($transType == 'Deposit' || $transType == 'Withdrawal')) ? "'" . mysqli_real_escape_string($conn, $referenceNo) . "'" : "NULL";

    $checkNumber = isset($_POST['check_number']) ? mysqli_real_escape_string($conn, $_POST['check_number']) : '';
    $checkDate = isset($_POST['check_date']) ? mysqli_real_escape_string($conn, $_POST['check_date']) : '';

    // If not a check transaction, nullify check fields
    if ($transType != 'Check Deposit' && $transType != 'Check Payment') {
        $checkNumber = "NULL";
        $checkDate = "NULL";
    } else {
        $checkNumber = "'" . $checkNumber . "'";
        $checkDate = !empty($checkDate) ? "'" . $checkDate . "'" : "NULL";
    }

    $query = "INSERT INTO bank_transactions (bank_id, transaction_type, reference_no, amount, check_number, check_date, transaction_date, remarks) 
              VALUES ('$bankId', '$transType', $refDB, '$amount', $checkNumber, $checkDate, '$transDate', '$remarks')";

    if (mysqli_query($conn, $query)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Bank transaction recorded successfully.']);
        exit();
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . mysqli_error($conn)]);
        exit();
    }
}

// --- REPORT GENERATION ACTIONS (AJAX) ---

// A. Search Student
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search_students'])) {
    $searchTerm = isset($_GET['term']) ? $_GET['term'] : '';

    $query = "SELECT id, id_number, last_name, first_name, middle_name 
              FROM students 
              WHERE id_number LIKE ? 
                 OR last_name LIKE ? 
                 OR first_name LIKE ?
                 OR CONCAT(last_name, ', ', first_name, ' ', middle_name) LIKE ?
              ORDER BY last_name, first_name
              LIMIT 20";

    $stmt = mysqli_prepare($conn, $query);
    $likeTerm = '%' . $searchTerm . '%';
    mysqli_stmt_bind_param($stmt, "ssss", $likeTerm, $likeTerm, $likeTerm, $likeTerm);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $students = [];

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $students[] = [
                'id' => $row['id'],
                'idNumber' => $row['id_number'],
                'lastName' => $row['last_name'],
                'firstName' => $row['first_name'],
                'middleName' => $row['middle_name'],
                'fullName' => $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name']
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($students);
    exit();
}

// B. Get Student Ledger Data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'get_student_ledger') {
    $studentId = isset($_POST['student_id']) ? mysqli_real_escape_string($conn, $_POST['student_id']) : '';

    if (empty($studentId)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No student ID provided']);
        exit;
    }

    $query = "SELECT * FROM students WHERE id = $studentId";
    $result = mysqli_query($conn, $query);
    $selectedStudent = null;

    if ($result && mysqli_num_rows($result) > 0) {
        $selectedStudent = mysqli_fetch_assoc($result);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }

    $feesQuery = "SELECT sf.id, sf.amount, sf.academic_year, sf.semester, 
                    f.fee_name, f.fee_type, f.base_amount,
                    COALESCE(SUM(pa.allocated_amount), 0) as paid_amount
             FROM student_fees sf
             JOIN fees f ON sf.fee_id = f.id
             LEFT JOIN payment_allocations pa ON sf.id = pa.student_fee_id
             WHERE sf.student_id = $studentId
             GROUP BY sf.id, sf.amount, sf.academic_year, sf.semester, f.fee_name, f.fee_type, f.base_amount
             ORDER BY sf.academic_year DESC, sf.semester DESC, f.fee_type, f.fee_name";
    
    $feesResult = mysqli_query($conn, $feesQuery);
    $studentFees = [];
    $totalFees = 0;

    if ($feesResult && mysqli_num_rows($feesResult) > 0) {
        while ($row = mysqli_fetch_assoc($feesResult)) {
            $balance = $row['amount'] - $row['paid_amount'];
            
            if ($balance <= 0) {
                $status = 'fully-paid';
            } elseif ($row['paid_amount'] > 0) {
                $status = 'partially-paid';
            } else {
                $status = 'not-paid';
            }

            $studentFees[] = [
                'id' => $row['id'],
                'fee_name' => $row['fee_name'],
                'fee_type' => $row['fee_type'],
                'amount' => $row['amount'],
                'base_amount' => $row['base_amount'],
                'paid_amount' => $row['paid_amount'],
                'balance' => $balance,
                'status' => $status,
                'academic_year' => $row['academic_year'],
                'semester' => $row['semester'],
                'units' => 0 
            ];
            $totalFees += $row['amount'];
        }
    }

    $paymentsQuery = "SELECT * FROM payments WHERE student_id = $studentId ORDER BY payment_date DESC";
    $paymentsResult = mysqli_query($conn, $paymentsQuery);
    $studentPayments = [];
    $totalPayments = 0;

    if ($paymentsResult && mysqli_num_rows($paymentsResult) > 0) {
        while ($row = mysqli_fetch_assoc($paymentsResult)) {
            $studentPayments[] = $row;
            $totalPayments += $row['amount'];
        }
    }

    $courseInfo = "Not enrolled";
    $courseQuery = "SELECT c.coursename, c.courselevel 
              FROM enrollments e
              JOIN courses c ON e.course_id = c.id
              WHERE e.student_id = $studentId 
              AND e.status IN ('Enrolled', 'Registered')
              ORDER BY e.enrollment_date DESC
              LIMIT 1";
    $courseResult = mysqli_query($conn, $courseQuery);
    if ($courseResult && $row = mysqli_fetch_assoc($courseResult)) {
        $courseInfo = $row['coursename'] . ' - ' . $row['courselevel'];
    }

    $studentBalance = $totalFees - $totalPayments;

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'student' => $selectedStudent,
        'course' => $courseInfo,
        'fees' => $studentFees,
        'payments' => $studentPayments,
        'summary' => [
            'total_fees' => $totalFees,
            'total_payments' => $totalPayments,
            'balance' => $studentBalance
        ]
    ]);
    exit();
}

// B-2. Get Collections & Receivables
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'get_collections_receivables') {
    $academicYear = mysqli_real_escape_string($conn, $_POST['academic_year']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);

    $query = "SELECT f.fee_name, 
                     COALESCE(SUM(sf.amount),0) as assessed, 
                     COALESCE(SUM(pa.allocated_amount),0) as collected
              FROM student_fees sf
              JOIN fees f ON sf.fee_id = f.id
              LEFT JOIN payment_allocations pa ON sf.id = pa.student_fee_id
              WHERE sf.academic_year = '$academicYear' AND sf.semester = '$semester'
              GROUP BY f.fee_name
              ORDER BY f.fee_name ASC";
    
    $result = mysqli_query($conn, $query);
    $data = [];
    $totalAssessed = 0;
    $totalCollected = 0;
    $totalBalance = 0;

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $bal = $row['assessed'] - $row['collected'];
            $data[] = [
                'fee_name' => $row['fee_name'],
                'assessed' => $row['assessed'],
                'collected' => $row['collected'],
                'balance' => $bal
            ];
            $totalAssessed += $row['assessed'];
            $totalCollected += $row['collected'];
            $totalBalance += $bal;
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'data' => $data, 
        'totals' => [
            'assessed' => $totalAssessed,
            'collected' => $totalCollected,
            'balance' => $totalBalance
        ],
        'ay' => $academicYear, 
        'sem' => $semester
    ]);
    exit();
}

// C. Get Customer Ledger
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'get_customer_ledger') {
    $startDate = mysqli_real_escape_string($conn, $_POST['start_date']);
    $endDate = mysqli_real_escape_string($conn, $_POST['end_date']);
    $search = mysqli_real_escape_string($conn, $_POST['search']);

    $query = "SELECT * FROM customer_payments 
              WHERE payment_date BETWEEN '$startDate' AND '$endDate 23:59:59'";
    
    if ($search) {
        $query .= " AND (customer_name LIKE '%$search%' OR or_number LIKE '%$search%')";
    }

    $query .= " ORDER BY payment_date DESC";
    $result = mysqli_query($conn, $query);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $data]);
    exit();
}

// D. Get Disbursement Ledger
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'get_disbursement_ledger') {
    $startDate = mysqli_real_escape_string($conn, $_POST['start_date']);
    $endDate = mysqli_real_escape_string($conn, $_POST['end_date']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);

    $query = "SELECT * FROM disbursements 
              WHERE payment_date BETWEEN '$startDate' AND '$endDate 23:59:59'";
    
    if ($category && $category !== 'all') {
        $query .= " AND category = '$category'";
    }

    $query .= " ORDER BY payment_date DESC";
    $result = mysqli_query($conn, $query);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $data]);
    exit();
}

// E. Get Financial Statement Data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'get_financial_statement') {
    $startDate = mysqli_real_escape_string($conn, $_POST['start_date']);
    $endDate = mysqli_real_escape_string($conn, $_POST['end_date']);

    $studIncome = 0;
    $q = "SELECT COALESCE(SUM(amount - discount), 0) as total FROM payments WHERE payment_date BETWEEN '$startDate' AND '$endDate 23:59:59'";
    $r = mysqli_query($conn, $q);
    if($r && $row = mysqli_fetch_assoc($r)) $studIncome = (float)$row['total'];

    $custIncome = 0;
    $q = "SELECT COALESCE(SUM(amount), 0) as total FROM customer_payments WHERE payment_date BETWEEN '$startDate' AND '$endDate 23:59:59'";
    $r = mysqli_query($conn, $q);
    if($r && $row = mysqli_fetch_assoc($r)) $custIncome = (float)$row['total'];

    $totalIncome = $studIncome + $custIncome;

    $expenses = [];
    $totalExpenses = 0;
    
    $q = "SELECT category, custom_category, COALESCE(SUM(amount), 0) as total FROM disbursements 
          WHERE payment_date BETWEEN '$startDate' AND '$endDate 23:59:59' 
          GROUP BY category, custom_category 
          ORDER BY total DESC";
    $r = mysqli_query($conn, $q);
    
    while ($row = mysqli_fetch_assoc($r)) {
        $catName = $row['category'] == 'Others' ? $row['custom_category'] : $row['category'];
        $expenses[] = ['category' => $catName, 'amount' => (float)$row['total']];
        $totalExpenses += (float)$row['total'];
    }

    $netIncome = $totalIncome - $totalExpenses;

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'income' => [
            'students' => $studIncome,
            'customers' => $custIncome,
            'total' => $totalIncome
        ],
        'expenses' => [
            'breakdown' => $expenses,
            'total' => $totalExpenses
        ],
        'net' => $netIncome
    ]);
    exit();
}

// F. Get Student Balances List
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'get_student_balances_list') {
    $academicYear = mysqli_real_escape_string($conn, $_POST['academic_year']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);

    $query = "SELECT 
                s.id_number, 
                CONCAT(s.last_name, ', ', s.first_name, ' ', COALESCE(s.middle_name, '')) as student_name,
                COALESCE(SUM(sf.amount), 0) as total_assessed,
                COALESCE(SUM(pa.allocated_amount), 0) as total_paid
              FROM student_fees sf
              JOIN students s ON sf.student_id = s.id
              LEFT JOIN payment_allocations pa ON sf.id = pa.student_fee_id
              WHERE sf.academic_year = '$academicYear' AND sf.semester = '$semester'
              GROUP BY s.id
              ORDER BY s.last_name, s.first_name";

    $result = mysqli_query($conn, $query);
    $data = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['balance'] = $row['total_assessed'] - $row['total_paid'];
            $data[] = $row;
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $data, 'ay' => $academicYear, 'sem' => $semester]);
    exit();
}

// G. Get Bank Transactions Data (UPDATED to include Reference No)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['get_bank_transactions'])) {
    $bankId = isset($_GET['bank_id']) ? (int)$_GET['bank_id'] : 0;
    
    // Calculate Running Balance
    // We fetch all transactions sorted by date. 
    // If a specific bank is selected, we filter.
    
    $query = "SELECT bt.*, b.bank_name, b.branch, b.account_number 
              FROM bank_transactions bt
              JOIN banks b ON bt.bank_id = b.id";
              
    if ($bankId > 0) {
        $query .= " WHERE bt.bank_id = $bankId";
    }
    
    $query .= " ORDER BY bt.transaction_date ASC, bt.id ASC"; // ASC for running balance calculation

    $result = mysqli_query($conn, $query);
    $transactions = [];
    $bankBalances = []; // Track balance per bank ID

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $bid = $row['bank_id'];
            $amt = (float)$row['amount'];
            $type = $row['transaction_type'];
            
            // Initialize balance if not exists
            if (!isset($bankBalances[$bid])) {
                $bankBalances[$bid] = 0;
            }

            // Update Balance Logic
            if ($type == 'Deposit' || $type == 'Check Deposit') {
                $bankBalances[$bid] += $amt;
            } elseif ($type == 'Withdrawal' || $type == 'Check Payment') {
                $bankBalances[$bid] -= $amt;
            }
            
            $row['balance_after'] = $bankBalances[$bid];
            $transactions[] = $row;
        }
    }

    // Reverse for display (Newest first) but keep the calculated balance
    $transactions = array_reverse($transactions);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $transactions]);
    exit();
}

// H. Get Single Bank Details (AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['get_bank_details'])) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $query = "SELECT * FROM banks WHERE id = $id LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        echo json_encode(['success' => true, 'data' => mysqli_fetch_assoc($result)]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

// --- CALCULATE STATISTICS (DASHBOARD) ---
 $totalStudentFees = 0;
 $query = "SELECT COALESCE(SUM(amount), 0) as total FROM student_fees";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) { $totalStudentFees = (float)$row['total']; }

 $totalStudentPayments = 0;
 $query = "SELECT COALESCE(SUM(amount - discount), 0) as total FROM payments";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) { $totalStudentPayments = (float)$row['total']; }

 $accountsReceivable = $totalStudentFees - $totalStudentPayments;

 $today = date('Y-m-d');
 $dailyStudent = 0;
 $query = "SELECT COALESCE(SUM(amount - discount), 0) as total FROM payments WHERE payment_date = '$today'";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) { $dailyStudent = (float)$row['total']; }

 $dailyCustomer = 0;
 $query = "SELECT COALESCE(SUM(amount), 0) as total FROM customer_payments WHERE payment_date = '$today'";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) { $dailyCustomer = (float)$row['total']; }

 $dailyCollection = $dailyStudent + $dailyCustomer;

 $currentMonth = date('Y-m');
 $monthlyStudent = 0;
 $query = "SELECT COALESCE(SUM(amount - discount), 0) as total FROM payments WHERE payment_date LIKE '$currentMonth%'";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) { $monthlyStudent = (float)$row['total']; }

 $monthlyCustomer = 0;
 $query = "SELECT COALESCE(SUM(amount), 0) as total FROM customer_payments WHERE payment_date LIKE '$currentMonth%'";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) { $monthlyCustomer = (float)$row['total']; }

 $monthlyCollection = $monthlyStudent + $monthlyCustomer;

 $totalDisbursements = 0;
 $query = "SELECT COALESCE(SUM(amount), 0) as total FROM disbursements";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) { $totalDisbursements = (float)$row['total']; }

 $totalCustomerAllTime = 0;
 $query = "SELECT COALESCE(SUM(amount), 0) as total FROM customer_payments";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) { $totalCustomerAllTime = (float)$row['total']; }

 $totalIncome = $totalStudentPayments + $totalCustomerAllTime;
 $netCashFlow = $totalIncome - $totalDisbursements;

// User Info
 $current_user_id = $_SESSION['user_id'] ?? 0;
 $current_user = $conn->query("SELECT username, fullname, role FROM users WHERE id = $current_user_id")->fetch_assoc();
 $display_name = $current_user['fullname'] ?? $current_user['username'] ?? 'Treasurer';
 
// Fetch Banks List for Dropdowns
 $banksList = [];
 $bRes = mysqli_query($conn, "SELECT id, bank_name, branch, account_number FROM banks ORDER BY bank_name ASC");
 if($bRes) {
     while($b = mysqli_fetch_assoc($bRes)) {
         $banksList[] = $b;
     }
 }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Treasurer Dashboard & Reports</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <style>
        :root {
            --primary-color: #004085;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        body { background-color: #f4f6f9; font-family: 'Source Sans Pro', sans-serif; }
        
        .main-sidebar { background-color: var(--primary-color) !important; }
        .main-sidebar .brand-link { background-color: #002752 !important; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .nav-sidebar > .nav-item > .nav-link { color: rgba(255,255,255,0.8); }
        .nav-sidebar > .nav-item > .nav-link.active { background-color: rgba(255,255,255,0.2); color: #fff; font-weight: bold; }
        
        .nav-sidebar .nav-link.logout-link {
            background-color: rgba(220, 53, 69, 0.2); 
            color: #ffcccc;
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .nav-sidebar .nav-link.logout-link:hover {
            background-color: var(--danger-color);
            color: #fff;
        }

        .small-box {
            border-radius: 0.5rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            display: block;
            margin-bottom: 20px;
            position: relative;
        }
        .small-box .inner { padding: 15px; z-index: 2; position: relative; }
        .small-box .icon { font-size: 4.5rem; position: absolute; right: 15px; top: 15px; z-index: 0; opacity: 0.15; transition: all 0.3s linear; }
        .small-box:hover .icon { transform: scale(1.1); opacity: 0.3; }
        .small-box h3 { font-size: 2.2rem; font-weight: 700; margin: 0 0 10px 0; white-space: nowrap; }
        
        .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2); margin-bottom: 1rem; }
        .card-header { background-color: #fff; border-bottom: 1px solid rgba(0,0,0,.125); padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
        
        .report-filters { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ddd; }
        .student-search-container { position: relative; }
        .student-search-results {
            position: absolute; top: 100%; left: 0; right: 0; background: white; 
            border: 1px solid #ddd; border-top: none; max-height: 200px; 
            overflow-y: auto; z-index: 1000; display: none;
        }
        .student-search-result { padding: 8px; cursor: pointer; border-bottom: 1px solid #eee; }
        .student-search-result:hover { background-color: #f0f0f0; }

        .modal-header { background-color: var(--primary-color); color: white; }
        .modal-header .close { color: white; opacity: 0.8; }
        
        .fs-section-title { background-color: #e9ecef; font-weight: bold; }
        .fs-total-row { border-top: 2px solid #333; font-weight: bold; background-color: #fff; }
        .fs-net-row { font-size: 1.1em; }
        .text-pos { color: var(--success-color); }
        .text-neg { color: var(--danger-color); }

        .balance-negative { color: #dc3545; font-weight: bold; }
        .balance-positive { color: #28a745; font-weight: bold; }
        .balance-zero { color: #6c757d; font-weight: bold; }

        @media print {
            .main-sidebar, .main-header, .no-print, .report-filters, .nav-tabs, .nav-pills, .btn, .modal, .toast, .student-search-results, .dashboard-stats, .disbursement-dashboard, .card-footer, .pagination { display: none !important; }
            
            .content-wrapper { margin-left: 0 !important; padding-top: 0 !important; background-color: white !important; min-height: auto !important; }
            body { background-color: white; font-size: 12pt; color: black; }
            .tab-content { display: block !important; }
            
            .card { border: none !important; box-shadow: none !important; margin-bottom: 2rem; page-break-inside: avoid; }
            .card-header { border-bottom: 2px solid #000 !important; padding: 10px 0 !important; margin-bottom: 20px !important; background: none !important; }
            .card-body { padding: 0 !important; }
            
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11pt; }
            th, td { border: 1px solid #000 !important; padding: 8px !important; text-align: left !important; color: #000 !important; }
            th { background-color: #f0f0f0 !important; -webkit-print-color-adjust: exact; font-weight: bold; text-transform: uppercase; font-size: 10pt; }
            tr:nth-child(even) { background-color: transparent !important; } 
            .text-right { text-align: right !important; }
            
            .print-header {
                display: block !important;
                text-align: center;
                margin-bottom: 40px;
                border-bottom: 3px double #000;
                padding-bottom: 20px;
            }
            .print-header h2 { margin: 0; font-size: 20pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
            .print-header h3 { margin: 5px 0; font-size: 14pt; font-weight: normal; }
            .print-header p { margin: 0; font-size: 10pt; }
            
            .report-title-display {
                display: block !important;
                text-align: center;
                margin-bottom: 30px;
                text-transform: uppercase;
                font-weight: bold;
                font-size: 16pt;
                text-decoration: underline;
            }

            .print-footer {
                display: block !important;
                position: fixed;
                bottom: 0;
                width: 100%;
                text-align: center;
                font-size: 9pt;
                border-top: 1px solid #000;
                padding-top: 10px;
                color: #000;
            }
            
            .progress { display: none !important; }
            .badge { border: 1px solid #ccc; color: #000 !important; padding: 2px 5px; font-weight: normal; }
        }

        .print-header, .print-footer, .report-title-display { display: none; }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light no-print">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
            <li class="nav-item d-none d-sm-inline-block"><span class="nav-link" style="cursor: default; font-weight: bold;">Treasurer Office</span></li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar elevation-4 no-print">
        <a href="treasurer.php" class="brand-link">
            <img src="../uploads/csr.png" alt="CSR Logo" style="opacity: .8; height: 35px; margin-right: 10px;">
            <span class="brand-text font-weight-light" style="color: white;">Treasurer</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 d-flex">
                <div class="image"><img src="../uploads/cashier.jpg" class="img-circle elevation-2" style="height: 34px;"></div>
                <div class="info"><a href="#" class="d-block" style="color: white;"><?= htmlspecialchars($display_name) ?></a></div>
            </div>
            <div class="p-3">
                <div class="text-white text-xs mb-1" style="opacity: 0.7;">NET CASH FLOW</div>
                <div class="h3 mb-0 text-white"><?= $netCashFlow >= 0 ? '₱' . formatAmount($netCashFlow) : '-₱' . formatAmount(abs($netCashFlow)) ?></div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
                    <li class="nav-item">
                        <a href="#dashboard" class="nav-link active" data-toggle="tab">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#disbursements-area" class="nav-link" data-toggle="tab">
                            <i class="nav-icon fas fa-money-bill-wave"></i><p>Disbursements</p>
                        </a>
                    </li>
                    <!-- BANK TRANSACTIONS LINK -->
                    <li class="nav-item">
                        <a href="#bank-transactions" class="nav-link" data-toggle="tab">
                            <i class="nav-icon fa fa-university"></i>
                            <p>Bank Transactions</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#reports" class="nav-link" data-toggle="tab">
                            <i class="nav-icon fas fa-file-alt"></i><p>Reports & Ledgers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link logout-link">
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
        <div class="tab-content">
            
            <!-- 1. DASHBOARD TAB -->
            <div class="tab-pane fade show active" id="dashboard">
                <div class="content-header">
                    <div class="container-fluid"><h1 class="m-0 text-dark">Financial Overview</h1></div>
                </div>
                <section class="content dashboard-stats">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner"><h3>₱<?= formatAmount($accountsReceivable) ?></h3><p>Accounts Receivable</p></div>
                                    <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner"><h3>₱<?= formatAmount($dailyCollection) ?></h3><p>Daily Collection</p></div>
                                    <div class="icon"><i class="fas fa-calendar-day"></i></div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner"><h3>₱<?= formatAmount($monthlyCollection) ?></h3><p>Monthly Collection</p></div>
                                    <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner"><h3>₱<?= formatAmount($totalDisbursements) ?></h3><p>Total Disbursements</p></div>
                                    <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card card-outline card-primary">
                                    <div class="card-header"><h3 class="card-title">Student Fees Summary</h3></div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-striped">
                                            <tr><th>Total Fees Assessed</th><td class="text-right">₱<?= formatAmount($totalStudentFees) ?></td></tr>
                                            <tr><th>Total Collected</th><td class="text-right text-success">₱<?= formatAmount($totalStudentPayments) ?></td></tr>
                                            <tr><th>Balance (Receivable)</th><td class="text-right font-weight-bold <?= $accountsReceivable > 0 ? 'text-danger' : 'text-success' ?>">₱<?= formatAmount($accountsReceivable) ?></td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-outline card-info">
                                    <div class="card-header"><h3 class="card-title">Customer Payments Summary</h3></div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-striped">
                                            <tr><th>Total Customer Collections</th><td class="text-right text-success">₱<?= formatAmount($totalCustomerAllTime) ?></td></tr>
                                            <tr><th>Daily Customer</th><td class="text-right">₱<?= formatAmount($dailyCustomer) ?></td></tr>
                                            <tr><th>Monthly Customer</th><td class="text-right">₱<?= formatAmount($monthlyCustomer) ?></td></tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- 2. DISBURSEMENTS AREA TAB -->
            <div class="tab-pane fade" id="disbursements-area">
                <div class="content-header">
                    <div class="container-fluid"><h1 class="m-0 text-dark">Disbursements Management</h1></div>
                </div>
                <section class="content disbursement-dashboard">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Recent Disbursements</h3>
                                        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#addDisbursementModal"><i class="fas fa-plus"></i> Add Disbursement</button>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-striped projects" id="disbursementsTable">
                                                <thead>
                                                    <tr>
                                                        <th>Voucher #</th><th>Date</th><th>Payee</th><th>Category</th><th>Mode</th><th>Amount</th><th>Remarks</th><th class="no-print">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $query = "SELECT * FROM disbursements ORDER BY payment_date DESC LIMIT 10";
                                                    $result = mysqli_query($conn, $query);
                                                    if ($result && mysqli_num_rows($result) > 0) {
                                                        while ($d = mysqli_fetch_assoc($result)): ?>
                                                    <tr>
                                                        <td><?= $d['voucher_no'] ?></td>
                                                        <td><?= date('M d, Y', strtotime($d['payment_date'])) ?></td>
                                                        <td><?= htmlspecialchars($d['payee_name']) ?></td>
                                                        <td><span class="badge badge-warning"><?= $d['category'] == 'Others' ? htmlspecialchars($d['custom_category']) : ucfirst($d['category']) ?></span></td>
                                                        <td><?= htmlspecialchars($d['payment_mode']) ?></td>
                                                        <td class="text-danger font-weight-bold">₱<?= formatAmount($d['amount']) ?></td>
                                                        <td class="text-muted small"><?= htmlspecialchars($d['remarks']) ?></td>
                                                        <td class="no-print">
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-sm btn-info print-voucher-btn" 
                                                                    data-id="<?= $d['id'] ?>" 
                                                                    data-voucher="<?= $d['voucher_no'] ?>" 
                                                                    data-payee="<?= htmlspecialchars($d['payee_name']) ?>"
                                                                    data-category="<?= $d['category'] ?>" 
                                                                    data-custom="<?= htmlspecialchars($d['custom_category']) ?>"
                                                                    data-amount="<?= $d['amount'] ?>" 
                                                                    data-remarks="<?= htmlspecialchars($d['remarks']) ?>"
                                                                    data-date="<?= $d['payment_date'] ?>"
                                                                    data-mode="<?= htmlspecialchars($d['payment_mode']) ?>"
                                                                    data-bank="<?= htmlspecialchars($d['bank_name']) ?>"
                                                                    data-bank-account="<?= htmlspecialchars($d['bank_account_number']) ?>"
                                                                    data-check-no="<?= htmlspecialchars($d['check_number']) ?>"
                                                                    data-check-date="<?= htmlspecialchars($d['check_date']) ?>"
                                                                    title="Print Voucher"><i class="fas fa-print"></i></button>
                                                                <button type="button" class="btn btn-sm btn-primary edit-disbursement-btn" 
                                                                    data-id="<?= $d['id'] ?>" 
                                                                    data-payee="<?= htmlspecialchars($d['payee_name']) ?>"
                                                                    data-category="<?= $d['category'] ?>" 
                                                                    data-custom="<?= htmlspecialchars($d['custom_category']) ?>"
                                                                    data-amount="<?= $d['amount'] ?>" 
                                                                    data-remarks="<?= htmlspecialchars($d['remarks']) ?>"
                                                                    data-mode="<?= htmlspecialchars($d['payment_mode']) ?>"
                                                                    data-bank="<?= htmlspecialchars($d['bank_name']) ?>"
                                                                    data-bank-account="<?= htmlspecialchars($d['bank_account_number']) ?>"
                                                                    data-check-no="<?= htmlspecialchars($d['check_number']) ?>"
                                                                    data-check-date="<?= htmlspecialchars($d['check_date']) ?>"
                                                                    title="Edit"><i class="fas fa-edit"></i></button>
                                                                <button type="button" class="btn btn-sm btn-danger cancel-disbursement-btn" data-id="<?= $d['id'] ?>" data-or="<?= $d['voucher_no'] ?>" title="Cancel"><i class="fas fa-ban"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php endwhile; } else { ?>
                                                        <tr><td colspan="8" class="text-center p-3">No recent disbursements.</td></tr>
                                                    <?php } ?>
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

            <!-- 3. BANK TRANSACTIONS TAB -->
            <div class="tab-pane fade" id="bank-transactions">
                <div class="content-header">
                    <div class="container-fluid"><h1 class="m-0 text-dark">Bank Transactions Management</h1></div>
                </div>
                <section class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">Transaction History</h3>
                                        <div>
                                            <select id="bankFilterSelect" class="form-control d-inline-block w-auto mr-2">
                                                <option value="0">All Banks</option>
                                                <?php foreach($banksList as $b): ?>
                                                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['bank_name']) ?> - <?= htmlspecialchars($b['account_number']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addBankTransactionModal"><i class="fas fa-plus"></i> New Transaction</button>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Bank</th>
                                                        <th>Type</th>
                                                        <th>Ref/Check #</th>
                                                        <th class="text-right">Amount</th>
                                                        <th class="text-right">Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="bankTransactionsBody">
                                                    <!-- Populated by JS -->
                                                    <tr><td colspan="6" class="text-center">Loading...</td></tr>
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

            <!-- 4. REPORTS TAB -->
            <div class="tab-pane fade" id="reports">
                <div class="content-header no-print">
                    <div class="container-fluid"><h1 class="m-0 text-dark">Reports & Ledgers</h1></div>
                </div>
                <section class="content">
                    <div class="container-fluid">
                        
                        <ul class="nav nav-tabs" id="reportTab" role="tablist">
                            <li class="nav-item"><a class="nav-link active" id="student-tab" data-toggle="tab" href="#student-ledger">Student Ledger</a></li>
                            <li class="nav-item"><a class="nav-link" id="cust-rec-tab" data-toggle="tab" href="#collections-receivables">Collections & Receivables</a></li>
                            <li class="nav-item"><a class="nav-link" id="balances-tab" data-toggle="tab" href="#student-balances">Student Balances List</a></li>
                            <li class="nav-item"><a class="nav-link" id="customer-tab" data-toggle="tab" href="#customer-ledger">Customer Ledger</a></li>
                            <li class="nav-item"><a class="nav-link" id="disb-tab" data-toggle="tab" href="#disb-ledger">Disbursement Ledger</a></li>
                            <li class="nav-item"><a class="nav-link" id="fin-tab" data-toggle="tab" href="#fin-stmt">Financial Statement</a></li>
                        </ul>

                        <div class="tab-content" id="reportTabContent">
                            
                            <!-- A. Student Ledger -->
                            <div class="tab-pane fade show active" id="student-ledger">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="print-header">
                                            <h2><?= defined('SCHOOL_NAME') ? SCHOOL_NAME : 'School Name' ?></h2>
                                            <h3><?= defined('SCHOOL_ADDRESS') ? SCHOOL_ADDRESS : 'School Address' ?></h3>
                                            <p><?= defined('SCHOOL_CONTACT_NO') ? SCHOOL_CONTACT_NO : '' ?> | <?= defined('SCHOOL_EMAIL') ? SCHOOL_EMAIL : '' ?></p>
                                        </div>
                                        <div class="report-title-display">Student Statement of Account</div>

                                        <div class="report-filters row no-print">
                                            <div class="col-md-4 student-search-container">
                                                <label>Select Student</label>
                                                <input type="text" class="form-control" id="studentSearchReport" placeholder="Type Name or ID Number...">
                                                <div class="student-search-results" id="studentSearchResultsReport"></div>
                                                <input type="hidden" id="reportStudentId">
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button class="btn btn-primary w-100" id="btnGenerateStudent"><i class="fas fa-search"></i> View Account</button>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button class="btn btn-secondary w-100" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                                            </div>
                                        </div>

                                        <div id="studentAccountContainer" style="display:none;"></div>
                                        <div id="studentLedgerPlaceholder" class="text-center p-5">
                                            <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Select a student to view their Account Summary and Ledger.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- B. Collections & Receivables -->
                            <div class="tab-pane fade" id="collections-receivables">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="print-header">
                                            <h2><?= defined('SCHOOL_NAME') ? SCHOOL_NAME : 'School Name' ?></h2>
                                            <h3><?= defined('SCHOOL_ADDRESS') ? SCHOOL_ADDRESS : 'School Address' ?></h3>
                                            <p><?= defined('SCHOOL_CONTACT_NO') ? SCHOOL_CONTACT_NO : '' ?> | <?= defined('SCHOOL_EMAIL') ? SCHOOL_EMAIL : '' ?></p>
                                        </div>
                                        <div class="report-title-display">Summary of Collections & Receivables (By Fee Name)</div>
                                        
                                        <div class="report-filters row no-print">
                                            <div class="col-md-3">
                                                <label>Academic Year</label>
                                                <select class="form-control" id="colAY">
                                                    <option value="">Select Year...</option>
                                                    <?php 
                                                    $years = mysqli_query($conn, "SELECT DISTINCT academic_year FROM student_fees ORDER BY academic_year DESC");
                                                    while($y = mysqli_fetch_assoc($years)) echo "<option value='".$y['academic_year']."'>".$y['academic_year']."</option>";
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Semester</label>
                                                <select class="form-control" id="colSem">
                                                    <option value="">Select Semester...</option>
                                                    <option value="1st">1st Semester</option>
                                                    <option value="2nd">2nd Semester</option>
                                                    <option value="Summer">Summer</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button class="btn btn-primary w-100" id="btnGenerateCollections"><i class="fas fa-list"></i> Generate</button>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button class="btn btn-secondary w-100" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                                            </div>
                                        </div>

                                        <div id="collectionsContainer" class="table-responsive">
                                            <div class="text-center p-5 text-muted">Select filters to generate report.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- C. Student Balances List -->
                            <div class="tab-pane fade" id="student-balances">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="print-header">
                                            <h2><?= defined('SCHOOL_NAME') ? SCHOOL_NAME : 'School Name' ?></h2>
                                            <h3><?= defined('SCHOOL_ADDRESS') ? SCHOOL_ADDRESS : 'School Address' ?></h3>
                                            <p><?= defined('SCHOOL_CONTACT_NO') ? SCHOOL_CONTACT_NO : '' ?> | <?= defined('SCHOOL_EMAIL') ? SCHOOL_EMAIL : '' ?></p>
                                        </div>
                                        <div class="report-title-display">Student Balances Masterlist</div>

                                        <div class="report-filters row no-print">
                                            <div class="col-md-3">
                                                <label>Academic Year</label>
                                                <select class="form-control" id="balanceAY">
                                                    <option value="">Select Year...</option>
                                                    <?php 
                                                    mysqli_data_seek($years, 0); // Reuse result
                                                    while($y = mysqli_fetch_assoc($years)) echo "<option value='".$y['academic_year']."'>".$y['academic_year']."</option>";
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Semester</label>
                                                <select class="form-control" id="balanceSem">
                                                    <option value="">Select Semester...</option>
                                                    <option value="1st">1st Semester</option>
                                                    <option value="2nd">2nd Semester</option>
                                                    <option value="Summer">Summer</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button class="btn btn-primary w-100" id="btnGenerateBalances"><i class="fas fa-list"></i> Generate</button>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button class="btn btn-secondary w-100" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                                            </div>
                                        </div>

                                        <div id="balancesListContainer" class="table-responsive">
                                            <div class="text-center p-5 text-muted">Select filters to generate list.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- D. Customer Ledger -->
                            <div class="tab-pane fade" id="customer-ledger">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="print-header">
                                            <h2><?= defined('SCHOOL_NAME') ? SCHOOL_NAME : 'School Name' ?></h2>
                                            <h3><?= defined('SCHOOL_ADDRESS') ? SCHOOL_ADDRESS : 'School Address' ?></h3>
                                            <p><?= defined('SCHOOL_CONTACT_NO') ? SCHOOL_CONTACT_NO : '' ?> | <?= defined('SCHOOL_EMAIL') ? SCHOOL_EMAIL : '' ?></p>
                                        </div>
                                        <div class="report-title-display">Customer Collections Report</div>

                                        <div class="report-filters row no-print">
                                            <div class="col-md-4"><label>Search</label><input type="text" class="form-control" id="customerSearch" placeholder="Leave empty for all..."></div>
                                            <div class="col-md-2"><label>Start</label><input type="date" class="form-control" id="custStartDate" value="<?= date('Y-m-01') ?>"></div>
                                            <div class="col-md-2"><label>End</label><input type="date" class="form-control" id="custEndDate" value="<?= date('Y-m-t') ?>"></div>
                                            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100" id="btnGenerateCustomer"><i class="fas fa-search"></i> View</button></div>
                                            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-secondary w-100" onclick="window.print()"><i class="fas fa-print"></i> Print</button></div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm" id="customerLedgerTable">
                                                <thead class="thead-light"><tr><th>Date</th><th>OR #</th><th>Customer Name</th><th>Type</th><th>Amount</th><th>Method</th></tr></thead>
                                                <tbody id="customerLedgerBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- E. Disbursement Ledger -->
                            <div class="tab-pane fade" id="disb-ledger">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="print-header">
                                            <h2><?= defined('SCHOOL_NAME') ? SCHOOL_NAME : 'School Name' ?></h2>
                                            <h3><?= defined('SCHOOL_ADDRESS') ? SCHOOL_ADDRESS : 'School Address' ?></h3>
                                            <p><?= defined('SCHOOL_CONTACT_NO') ? SCHOOL_CONTACT_NO : '' ?> | <?= defined('SCHOOL_EMAIL') ? SCHOOL_EMAIL : '' ?></p>
                                        </div>
                                        <div class="report-title-display">Disbursement Report</div>

                                        <div class="report-filters row no-print">
                                            <div class="col-md-3"><label>Category</label><select class="form-control" id="disbCategoryFilter"><option value="all">All Categories</option><option value="Salary">Salary</option><option value="Teacher Loan">Teacher Loan</option><option value="Utilities">Utilities</option><option value="Procurement">Procurement</option><option value="Maintenance">Maintenance</option><option value="Allowance">Allowance</option><option value="Others">Others</option></select></div>
                                            <div class="col-md-2"><label>Start</label><input type="date" class="form-control" id="disbStartDate" value="<?= date('Y-m-01') ?>"></div>
                                            <div class="col-md-2"><label>End</label><input type="date" class="form-control" id="disbEndDate" value="<?= date('Y-m-t') ?>"></div>
                                            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100" id="btnGenerateDisb"><i class="fas fa-search"></i> View</button></div>
                                            <div class="col-md-3 d-flex align-items-end"><button class="btn btn-secondary w-100" onclick="window.print()"><i class="fas fa-print"></i> Print Report</button></div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm" id="disbLedgerTable">
                                                <thead class="thead-light"><tr><th>Date</th><th>Voucher #</th><th>Payee</th><th>Category</th><th>Mode</th><th>Amount</th><th>Remarks</th></tr></thead>
                                                <tbody id="disbLedgerBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- F. Financial Statement -->
                            <div class="tab-pane fade" id="fin-stmt">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="print-header">
                                            <h2><?= defined('SCHOOL_NAME') ? SCHOOL_NAME : 'School Name' ?></h2>
                                            <h3><?= defined('SCHOOL_ADDRESS') ? SCHOOL_ADDRESS : 'School Address' ?></h3>
                                            <p><?= defined('SCHOOL_CONTACT_NO') ? SCHOOL_CONTACT_NO : '' ?> | <?= defined('SCHOOL_EMAIL') ? SCHOOL_EMAIL : '' ?></p>
                                        </div>
                                        <div class="report-title-display">Statement of Financial Performance</div>

                                        <div class="report-filters row no-print">
                                            <div class="col-md-3"><label>Start Date</label><input type="date" class="form-control" id="fsStartDate" value="<?= date('Y-m-01') ?>"></div>
                                            <div class="col-md-3"><label>End Date</label><input type="date" class="form-control" id="fsEndDate" value="<?= date('Y-m-t') ?>"></div>
                                            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100" id="btnGenerateFS"><i class="fas fa-calculator"></i> Generate</button></div>
                                            <div class="col-md-4 d-flex align-items-end"><button class="btn btn-secondary w-100" onclick="window.print()"><i class="fas fa-print"></i> Print Statement</button></div>
                                        </div>

                                        <div id="fsContainer" style="display:none;"></div>
                                        <div id="fsPlaceholder" class="text-center p-5">
                                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Select date range and click Generate to view Financial Statement.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Modals -->

    <!-- Add Disbursement Modal -->
    <div class="modal fade" id="addDisbursementModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Record Disbursement</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <form id="addDisbursementForm">
                <input type="hidden" name="action" value="add_disbursement">
                <div class="modal-body">
                    <div class="form-group"><label>Payee Name <span class="text-danger">*</span></label><input type="text" class="form-control" name="payeeName" required></div>
                    <div class="form-group"><label>Category <span class="text-danger">*</span></label>
                        <select class="form-control" name="category" id="disbCategory" required>
                            <option value="">-- Select Category --</option><option value="Salary">Salary</option><option value="Teacher Loan">Teacher Loan</option><option value="Utilities">Utilities (Bills)</option><option value="Procurement">Procurement</option><option value="Maintenance">Maintenance</option><option value="Allowance">Allowance</option><option value="Others">Others (Specify)</option>
                        </select>
                    </div>
                    <div class="form-group custom-category-group" id="customCategoryGroup" style="display:none;"><label>Specify Category <span class="text-danger">*</span></label><input type="text" class="form-control" name="customCategory"></div>
                    
                    <div class="form-group">
                        <label>Payment Mode <span class="text-danger">*</span></label>
                        <select class="form-control" name="paymentMode" id="paymentMode" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank Transfer</option>
                            <option value="Check">Check</option>
                        </select>
                    </div>

                    <div class="bank-details" style="display:none;">
                        <div class="form-group">
                            <label>Bank Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="bankName" id="bankName">
                        </div>
                        <div class="form-group">
                            <label>Bank Account Number</label>
                            <input type="text" class="form-control" name="bankAccountNumber" id="bankAccountNumber" placeholder="e.g. 1234-5678-90">
                        </div>
                    </div>

                    <div class="check-details" style="display:none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Check Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="checkNumber" id="checkNumber">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Check Date</label>
                                    <input type="date" class="form-control" name="checkDate" id="checkDate">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6"><div class="form-group"><label>Amount (₱) <span class="text-danger">*</span></label><input type="number" class="form-control text-right" name="amount" step="0.01" min="0" required></div></div>
                        <div class="col-md-6"><div class="form-group"><label>Voucher No.</label><input type="text" class="form-control" value="Auto-generated" disabled style="background-color: #e9ecef;"></div></div>
                    </div>
                    <div class="form-group"><label>Payment Date <span class="text-danger">*</span></label><input type="date" class="form-control" name="paymentDate" value="<?= date('Y-m-d') ?>" required></div>
                    <div class="form-group"><label>Remarks</label><textarea class="form-control" name="remarks" rows="2"></textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Record Expense</button></div>
            </form>
        </div></div>
    </div>

    <!-- NEW: Add Bank Transaction Modal (UPDATED) -->
    <div class="modal fade" id="addBankTransactionModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header bg-info"><h5 class="modal-title">Record Bank Transaction</h5><button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button></div>
            <form id="addBankTransactionForm">
                <input type="hidden" name="action" value="add_bank_transaction">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Bank <span class="text-danger">*</span></label>
                        <select class="form-control" name="bank_id" id="bt_bank_id" required>
                            <option value="">-- Select Bank --</option>
                            <?php foreach($banksList as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['bank_name']) ?> (<?= htmlspecialchars($b['account_number']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Branch</label>
                                <input type="text" class="form-control" id="bt_branch" readonly>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Account Number</label>
                                <input type="text" class="form-control" id="bt_account_number" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Transaction Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="trans_type" id="bt_type" required>
                            <option value="Deposit">Deposit</option>
                            <option value="Withdrawal">Withdrawal</option>
                            <option value="Check Deposit">Check Deposit</option>
                            <option value="Check Payment">Check Payment</option>
                        </select>
                    </div>

                    <!-- Reference No. (For Deposit/Withdrawal) -->
                    <div class="bt-ref-fields" style="background: #e3f2fd; padding: 10px; border-radius: 5px; border: 1px solid #90caf9;">
                        <div class="form-group">
                            <label>Reference No. <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="reference_no" id="bt_ref_no" placeholder="e.g. Slip #000123">
                        </div>
                    </div>

                    <!-- Check Fields (For Check Deposits/Payments) -->
                    <div class="bt-check-fields" style="display:none; background: #f0f8ff; padding: 10px; border-radius: 5px; border: 1px solid #b8daff;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Check Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="check_number" id="bt_check_no">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Check Date</label>
                                    <input type="date" class="form-control" name="check_date" id="bt_check_date">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Transaction Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="trans_date" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Amount (₱) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control text-right" name="amount" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Transaction</button>
                </div>
            </form>
        </div></div>
    </div>

    <!-- Print Voucher Success Modal -->
    <div class="modal fade" id="printSuccessModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title"><i class="fas fa-check-circle"></i> Success</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    <h3 class="mb-3">Voucher Created!</h3>
                    <p class="lead">Voucher Number: <strong id="printVoucherNoDisplay" class="text-primary"></strong></p>
                    <p>Would you like to print this voucher now?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnPrintVoucher"><i class="fas fa-print"></i> Print Voucher</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Disbursement Modal -->
    <div class="modal fade" id="editDisbursementModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header bg-info"><h5 class="modal-title">Edit Disbursement</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <form id="editDisbursementForm">
                <input type="hidden" name="action" value="edit_disbursement">
                <input type="hidden" name="edit_id" id="edit_disb_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Payee Name</label>
                        <input type="text" class="form-control" id="edit_disb_payee" disabled>
                        <small class="text-muted">Payee name cannot be changed.</small>
                    </div>
                    <div class="form-group">
                        <label>Category <span class="text-danger">*</span></label>
                        <select class="form-control" name="edit_category" id="edit_disb_category" required>
                            <option value="Salary">Salary</option><option value="Teacher Loan">Teacher Loan</option><option value="Utilities">Utilities (Bills)</option><option value="Procurement">Procurement</option><option value="Maintenance">Maintenance</option><option value="Allowance">Allowance</option><option value="Others">Others (Specify)</option>
                        </select>
                    </div>
                    <div class="form-group custom-category-group" id="editCustomCategoryGroup" style="display:none;">
                        <label>Specify Category <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="edit_customCategory" id="edit_disb_custom_cat">
                    </div>

                    <div class="form-group">
                        <label>Payment Mode <span class="text-danger">*</span></label>
                        <select class="form-control" name="edit_paymentMode" id="edit_paymentMode" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank">Bank Transfer</option>
                            <option value="Check">Check</option>
                        </select>
                    </div>

                    <div class="edit-bank-details" style="display:none;">
                        <div class="form-group">
                            <label>Bank Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="edit_bankName" id="edit_bankName">
                        </div>
                        <div class="form-group">
                            <label>Bank Account Number</label>
                            <input type="text" class="form-control" name="edit_bankAccountNumber" id="edit_bankAccountNumber">
                        </div>
                    </div>

                    <div class="edit-check-details" style="display:none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Check Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="edit_checkNumber" id="edit_checkNumber">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Check Date</label>
                                    <input type="date" class="form-control" name="edit_checkDate" id="edit_checkDate">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Amount (₱) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control text-right" name="edit_amount" id="edit_disb_amount" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea class="form-control" name="edit_remarks" id="edit_disb_remarks" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Save Changes</button>
                </div>
            </form>
        </div></div>
    </div>

    <!-- Cancel Disbursement Modal -->
    <div class="modal fade" id="cancelDisbursementModal" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header bg-warning"><h5 class="modal-title">Cancel Disbursement</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this disbursement?</p>
                <div class="form-group mt-3"><label>Verification Code</label><input type="password" class="form-control" id="cancelDisbCode"></div>
                <input type="hidden" id="cancelDisbId">
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="button" id="confirmCancelDisb" class="btn btn-danger">Confirm Cancel</button></div>
        </div></div>
    </div>

    <!-- Print Footer -->
    <div class="print-footer">
        <p><strong><?= defined('SCHOOL_NAME') ? SCHOOL_NAME : 'School Name' ?></strong> | Printed by: <?= htmlspecialchars($display_name) ?> on <?= date('F j, Y, g:i a') ?></p>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script>
 $(function() {
    let voucherDataForPrint = {}; // Global variable to hold data for the print popup

    function formatAmount(amount) { return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'); }
    
    function showToast(msg, type='success') {
        const colorClass = type === 'success' ? 'bg-success' : 'bg-danger';
        const html = `<div class="toast ${colorClass} text-white show" style="position:fixed; top:20px; right:20px; z-index:9999; min-width: 250px;" role="alert">
                        <div class="toast-body">${msg}</div>
                      </div>`;
        $('body').append(html); 
        setTimeout(() => $('.toast').remove(), 3000);
    }

    // Payment Mode Toggle (Disbursement)
    function togglePaymentFields(mode, prefix) {
        const bankClass = prefix === 'add' ? '.bank-details' : '.edit-bank-details';
        const checkClass = prefix === 'add' ? '.check-details' : '.edit-check-details';
        
        if(mode === 'Cash') {
            $(bankClass).hide();
            $(checkClass).hide();
        } else if (mode === 'Bank') {
            $(bankClass).slideDown();
            $(checkClass).hide();
        } else if (mode === 'Check') {
            $(bankClass).slideDown();
            $(checkClass).slideDown();
        }
    }

    $('#paymentMode').on('change', function() { togglePaymentFields($(this).val(), 'add'); });
    $('#edit_paymentMode').on('change', function() { togglePaymentFields($(this).val(), 'edit'); });

    // --- DISBURSEMENT LOGIC ---
    $('#addDisbursementForm').on('submit', function(e) {
        e.preventDefault();
        $.post('treasurer.php', $(this).serialize(), function(res) {
            if(res.success) {
                voucherDataForPrint = res;
                $('#addDisbursementForm')[0].reset();
                $('#addDisbursementModal').modal('hide');
                showToast(res.message);
                $('#printVoucherNoDisplay').text(res.voucher_no);
                $('#printSuccessModal').modal('show');
            } else {
                showToast(res.message, 'error');
            }
        }, 'json');
    });

    $('#btnPrintVoucher').on('click', function() {
        printVoucher(voucherDataForPrint);
        $('#printSuccessModal').modal('hide');
        setTimeout(() => location.reload(), 500);
    });

    $('#printSuccessModal').on('hidden.bs.modal', function () {
        location.reload();
    });

    function printVoucher(data) {
        var form = $('<form>', {
            'method': 'POST',
            'action': 'printvoucher.php',
            'target': '_blank'
        });
        $.each(data, function(key, value) {
            if (value !== undefined && value !== null) {
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': key,
                    'value': value
                }));
            }
        });
        $('body').append(form);
        form.submit();
        form.remove();
    }

    $('.print-voucher-btn').on('click', function() {
        printVoucher($(this).data());
    });

    $('.edit-disbursement-btn').on('click', function() {
        const btn = $(this);
        $('#edit_disb_id').val(btn.data('id'));
        $('#edit_disb_payee').val(btn.data('payee'));
        $('#edit_disb_category').val(btn.data('category'));
        $('#edit_disb_custom_cat').val(btn.data('custom'));
        $('#edit_disb_amount').val(btn.data('amount'));
        $('#edit_disb_remarks').val(btn.data('remarks'));

        const mode = btn.data('mode');
        $('#edit_paymentMode').val(mode);
        $('#edit_bankName').val(btn.data('bank'));
        $('#edit_bankAccountNumber').val(btn.data('bankAccount'));
        $('#edit_checkNumber').val(btn.data('checkNo'));
        $('#edit_checkDate').val(btn.data('checkDate'));

        if(btn.data('category') === 'Others') $('#editCustomCategoryGroup').show();
        else $('#editCustomCategoryGroup').hide();

        togglePaymentFields(mode, 'edit');
        $('#editDisbursementModal').modal('show');
    });

    $('#edit_disb_category').on('change', function() {
        if($(this).val() === 'Others') $('#editCustomCategoryGroup').slideDown();
        else $('#editCustomCategoryGroup').slideUp();
    });

    $('#editDisbursementForm').on('submit', function(e) {
        e.preventDefault();
        $.post('treasurer.php', $(this).serialize(), function(res) {
            if(res.success) { showToast(res.message); $('#editDisbursementModal').modal('hide'); setTimeout(()=>location.reload(), 1000); }
            else showToast(res.message, 'error');
        }, 'json');
    });

    $('.cancel-disbursement-btn').on('click', function() {
        $('#cancelDisbId').val($(this).data('id')); $('#cancelDisbCode').val(''); $('#cancelDisbursementModal').modal('show');
    });

    $('#confirmCancelDisb').on('click', function() {
        $.post('treasurer.php', { action: 'cancel_disbursement', disbursementId: $('#cancelDisbId').val(), verification_code: $('#cancelDisbCode').val() }, function(res) {
            if(res.success) { showToast(res.message); $('#cancelDisbursementModal').modal('hide'); setTimeout(()=>location.reload(), 1000); }
            else showToast(res.message, 'error');
        }, 'json');
    });

    // --- UPDATED: BANK TRANSACTION LOGIC ---
    
    // Load initial bank transactions
    function loadBankTransactions() {
        const bankId = $('#bankFilterSelect').val();
        $('#bankTransactionsBody').html('<tr><td colspan="6" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');
        
        $.get('treasurer.php', { get_bank_transactions: 1, bank_id: bankId }, function(res) {
            let html = '';
            if(res.success && res.data.length > 0) {
                res.data.forEach(t => {
                    let amountClass = (t.transaction_type == 'Withdrawal' || t.transaction_type == 'Check Payment') ? 'text-danger' : 'text-success';
                    let detail = t.remarks ? t.remarks : '-';
                    
                    // Logic to show Reference No OR Check No
                    let refDisplay = '-';
                    if(t.reference_no) {
                        refDisplay = `<strong>Ref #${t.reference_no}</strong>`;
                    } else if (t.check_number) {
                        refDisplay = `<strong>Check #${t.check_number}</strong> <span class="text-muted">(${t.check_date})</span>`;
                    }

                    html += `<tr>
                        <td>${t.transaction_date}</td>
                        <td>
                            <strong>${t.bank_name}</strong><br>
                            <small class="text-muted">${t.account_number} (${t.branch})</small>
                        </td>
                        <td><span class="badge badge-secondary">${t.transaction_type}</span></td>
                        <td>${refDisplay}</td>
                        <td class="text-right font-weight-bold ${amountClass}">${formatAmount(t.amount)}</td>
                        <td class="text-right font-weight-bold">${formatAmount(t.balance_after)}</td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="6" class="text-center">No transactions found.</td></tr>';
            }
            $('#bankTransactionsBody').html(html);
        }, 'json');
    }

    // Load when tab is shown or filter changes
    $('a[href="#bank-transactions"]').on('shown.bs.tab', loadBankTransactions);
    $('#bankFilterSelect').on('change', loadBankTransactions);

    // Handle Bank Selection in Modal
    $('#bt_bank_id').on('change', function() {
        const bankId = $(this).val();
        if(bankId) {
            $.get('treasurer.php', { get_bank_details: 1, id: bankId }, function(res) {
                if(res.success) {
                    $('#bt_branch').val(res.data.branch);
                    $('#bt_account_number').val(res.data.account_number);
                }
            }, 'json');
        } else {
            $('#bt_branch').val('');
            $('#bt_account_number').val('');
        }
    });

    // UPDATED: Handle Transaction Type Toggle (Ref No vs Check No)
    $('#bt_type').on('change', function() {
        const type = $(this).val();
        if(type === 'Deposit' || type === 'Withdrawal') {
            $('.bt-check-fields').slideUp();
            $('.bt-ref-fields').slideDown();
            $('#bt_ref_no').prop('required', true);
            $('#bt_check_no').prop('required', false);
        } else if (type === 'Check Deposit' || type === 'Check Payment') {
            $('.bt-ref-fields').slideUp();
            $('.bt-check-fields').slideDown();
            $('#bt_ref_no').prop('required', false);
            $('#bt_check_no').prop('required', true);
        }
    });

    // Submit Bank Transaction
    $('#addBankTransactionForm').on('submit', function(e) {
        e.preventDefault();
        $.post('treasurer.php', $(this).serialize(), function(res) {
            if(res.success) {
                showToast(res.message);
                $('#addBankTransactionForm')[0].reset();
                // Reset field visibility
                $('.bt-check-fields').hide();
                $('.bt-ref-fields').show(); 
                $('#bt_type').trigger('change'); // Reset required props
                
                $('#addBankTransactionModal').modal('hide');
                loadBankTransactions(); // Refresh list
            } else {
                showToast(res.message, 'error');
            }
        }, 'json');
    });


    // --- REPORTS LOGIC ---

    // Student Search
    $('#studentSearchReport').on('input', function() {
        const term = $(this).val();
        if(term.length < 2) return $('#studentSearchResultsReport').hide();
        $.get('treasurer.php', { search_students: 1, term: term }, function(data) {
            let html = '';
            if(data.length) {
                data.forEach(s => {
                    html += `<div class="student-search-result" data-id="${s.id}">
                                <strong>${s.lastName}, ${s.firstName}</strong> <small>(${s.idNumber})</small>
                             </div>`;
                });
            } else {
                html = '<div class="student-search-result text-muted">No student found</div>';
            }
            $('#studentSearchResultsReport').html(html).show();
        }, 'json');
    });

    $(document).on('click', '.student-search-result', function() {
        const id = $(this).data('id');
        const name = $(this).find('strong').text();
        $('#studentSearchReport').val(name);
        $('#reportStudentId').val(id);
        $('#studentSearchResultsReport').hide();
    });

    // Report: Student Ledger
    $('#btnGenerateStudent').on('click', function() {
        const sid = $('#reportStudentId').val();
        if(!sid) return showToast('Please select a student.', 'error');
        $('#studentLedgerPlaceholder').hide();
        $('#studentAccountContainer').show().html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');

        $.post('treasurer.php', {
            action: 'get_student_ledger',
            student_id: sid
        }, function(res) {
            if(res.success) {
                renderStudentAccount(res);
            } else {
                showToast(res.message, 'error');
                $('#studentAccountContainer').hide();
                $('#studentLedgerPlaceholder').show();
            }
        }, 'json');
    });

    function renderStudentAccount(data) {
        const s = data.student;
        const c = data.course;
        const sum = data.summary;

        let balanceHtml = '';
        if (sum.balance > 0) balanceHtml = `<span class="balance-negative">${formatAmount(sum.balance)} (Outstanding)</span>`;
        else if (sum.balance < 0) balanceHtml = `<span class="balance-positive">${formatAmount(Math.abs(sum.balance))} (Overpayment)</span>`;
        else balanceHtml = `<span class="balance-zero">₱0.00 (Paid in Full)</span>`;

        const summaryCard = `
            <div class="card card-outline card-info mb-4">
                <div class="card-header"><h3 class="card-title">Student Account Summary</h3></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Student Information</h4>
                            <p><strong>ID Number:</strong> ${s.id_number}</p>
                            <p><strong>Name:</strong> ${s.last_name}, ${s.first_name} ${s.middle_name}</p>
                            <p><strong>Course:</strong> ${c}</p>
                        </div>
                        <div class="col-md-6">
                            <h4>Account Balance</h4>
                            <p><strong>Total Fees:</strong> ${formatAmount(sum.total_fees)}</p>
                            <p><strong>Total Payments:</strong> ${formatAmount(sum.total_payments)}</p>
                            <p><strong>Balance:</strong> ${balanceHtml}</p>
                        </div>
                    </div>
                </div>
            </div>
        `;

        let feesHtml = '';
        if(data.fees.length === 0) feesHtml = '<tr><td colspan="9" class="text-center">No fees found.</td></tr>';
        else {
            data.fees.forEach(f => {
                let typeBadge = f.fee_type === 'tuition' ? '<span class="badge bg-primary">Tuition</span>' : (f.fee_type === 'miscellaneous' ? '<span class="badge bg-info">Miscellaneous</span>' : '<span class="badge bg-warning">Other</span>');
                let statusBadge = f.status === 'fully-paid' ? '<span class="badge bg-success">Fully Paid</span>' : (f.status === 'partially-paid' ? '<span class="badge bg-warning">Partially Paid</span>' : '<span class="badge bg-danger">Not Paid</span>');
                feesHtml += `<tr>
                    <td>${f.fee_name}</td><td>${typeBadge}</td><td>${formatAmount(f.base_amount)}</td><td>${formatAmount(f.amount)}</td>
                    <td>${f.academic_year}</td><td>${f.semester}</td><td class="text-success">${formatAmount(f.paid_amount)}</td>
                    <td class="font-weight-bold">${formatAmount(f.balance)}</td><td>${statusBadge}</td>
                </tr>`;
            });
        }

        let paymentsHtml = '';
        if(data.payments.length === 0) paymentsHtml = '<tr><td colspan="5" class="text-center">No payments found.</td></tr>';
        else {
            data.payments.forEach(p => {
                paymentsHtml += `<tr>
                    <td>${p.or_number}</td><td>${p.payment_date}</td><td class="text-success font-weight-bold">${formatAmount(p.amount - (p.discount || 0))}</td>
                    <td>${p.payment_method}</td><td><small>${p.remarks || ''}</small></td>
                </tr>`;
            });
        }

        const htmlStructure = `
            ${summaryCard}
            <ul class="nav nav-tabs" id="studentAccountTabs">
                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#studentFeesTab">Fees Breakdown</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#studentPaymentsTab">Payment History</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="studentFeesTab">
                    <div class="table-responsive mt-2"><table class="table table-bordered table-striped">
                        <thead><tr><th>Fee Name</th><th>Type</th><th>Base Amt</th><th>Total Amt</th><th>Academic Year</th><th>Semester</th><th>Paid</th><th>Balance</th><th>Status</th></tr></thead>
                        <tbody>${feesHtml}</tbody>
                    </table></div>
                </div>
                <div class="tab-pane fade" id="studentPaymentsTab">
                    <div class="table-responsive mt-2"><table class="table table-bordered table-striped">
                        <thead><tr><th>OR Number</th><th>Date</th><th>Amount</th><th>Method</th><th>Remarks</th></tr></thead>
                        <tbody>${paymentsHtml}</tbody>
                    </table></div>
                </div>
            </div>
        `;
        $('#studentAccountContainer').html(htmlStructure).show();
    }

    // Report: Collections & Receivables
    $('#btnGenerateCollections').on('click', function() {
        const ay = $('#colAY').val();
        const sem = $('#colSem').val();
        if(!ay || !sem) return showToast('Please select Academic Year and Semester.', 'error');

        $('#collectionsContainer').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');

        $.post('treasurer.php', {
            action: 'get_collections_receivables',
            academic_year: ay,
            semester: sem
        }, function(res) {
            if(res.success) {
                let html = `<h4 class="text-center mb-3">Academic Year: ${res.ay} - ${res.sem}</h4>
                            <table class="table table-bordered">
                                <thead><tr><th>Fee Name</th><th class="text-right">Total Assessed</th><th class="text-right">Collected</th><th class="text-right">Balance</th></tr></thead>
                                <tbody>`;
                
                if(res.data.length > 0) {
                    res.data.forEach(row => {
                        html += `<tr>
                                    <td>${row.fee_name}</td>
                                    <td class="text-right">${formatAmount(row.assessed)}</td>
                                    <td class="text-right text-success">${formatAmount(row.collected)}</td>
                                    <td class="text-right font-weight-bold">${formatAmount(row.balance)}</td>
                                 </tr>`;
                    });
                    html += `</tbody><tfoot>
                                <tr style="font-weight:bold; background:#f0f0f0;">
                                    <td class="text-right">TOTALS:</td>
                                    <td class="text-right">${formatAmount(res.totals.assessed)}</td>
                                    <td class="text-right">${formatAmount(res.totals.collected)}</td>
                                    <td class="text-right">${formatAmount(res.totals.balance)}</td>
                                </tr>
                             </tfoot></table>`;
                } else {
                    html += `<tr><td colspan="4" class="text-center">No records found for this period.</td></tr></tbody></table>`;
                }
                $('#collectionsContainer').html(html);
            } else {
                showToast(res.message, 'error');
                $('#collectionsContainer').html('');
            }
        }, 'json');
    });

    // Report: Customer Ledger
    $('#btnGenerateCustomer').on('click', function() {
        $.post('treasurer.php', {
            action: 'get_customer_ledger',
            start_date: $('#custStartDate').val(),
            end_date: $('#custEndDate').val(),
            search: $('#customerSearch').val()
        }, function(res) {
            if(res.success) {
                let html = '';
                res.data.forEach(row => {
                    html += `<tr>
                        <td>${row.payment_date}</td><td>${row.or_number}</td><td>${row.customer_name}</td><td>${row.customer_type}</td>
                        <td class="text-right">${formatAmount(row.amount)}</td><td>${row.payment_method}</td>
                    </tr>`;
                });
                $('#customerLedgerBody').html(html || '<tr><td colspan="6" class="text-center">No records found.</td></tr>');
            }
        }, 'json');
    });

    // Report: Disbursement Ledger
    $('#btnGenerateDisb').on('click', function() {
        $.post('treasurer.php', {
            action: 'get_disbursement_ledger',
            start_date: $('#disbStartDate').val(),
            end_date: $('#disbEndDate').val(),
            category: $('#disbCategoryFilter').val()
        }, function(res) {
            if(res.success) {
                let html = '';
                res.data.forEach(row => {
                    html += `<tr>
                        <td>${row.payment_date}</td>
                        <td>${row.voucher_no}</td>
                        <td>${row.payee_name}</td>
                        <td>${row.category=='Others' ? row.custom_category : row.category}</td>
                        <td>${row.payment_mode}</td>
                        <td class="text-right">${formatAmount(row.amount)}</td>
                        <td>${row.remarks}</td>
                    </tr>`;
                });
                $('#disbLedgerBody').html(html || '<tr><td colspan="7" class="text-center">No records found.</td></tr>');
            }
        }, 'json');
    });

    // Report: Student Balances List
    $('#btnGenerateBalances').on('click', function() {
        const ay = $('#balanceAY').val();
        const sem = $('#balanceSem').val();
        if(!ay || !sem) return showToast('Please select Academic Year and Semester.', 'error');

        $('#balancesListContainer').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');

        $.post('treasurer.php', {
            action: 'get_student_balances_list',
            academic_year: ay,
            semester: sem
        }, function(res) {
            if(res.success) {
                let html = `<h4 class="text-center mb-3">Academic Year: ${res.ay} - ${res.sem}</h4>
                            <table class="table table-bordered table-sm">
                                <thead><tr><th>ID Number</th><th>Student Name</th><th class="text-right">Total Assessed</th><th class="text-right">Total Paid</th><th class="text-right">Balance</th></tr></thead>
                                <tbody>`;
                
                let totalAssessed = 0;
                let totalPaid = 0;
                let totalBalance = 0;

                if(res.data.length > 0) {
                    res.data.forEach(row => {
                        totalAssessed += parseFloat(row.total_assessed);
                        totalPaid += parseFloat(row.total_paid);
                        totalBalance += parseFloat(row.balance);

                        html += `<tr>
                                    <td>${row.id_number}</td>
                                    <td>${row.student_name}</td>
                                    <td class="text-right">${formatAmount(row.total_assessed)}</td>
                                    <td class="text-right text-success">${formatAmount(row.total_paid)}</td>
                                    <td class="text-right font-weight-bold ${row.balance > 0 ? 'text-danger' : 'text-success'}">${formatAmount(row.balance)}</td>
                                 </tr>`;
                    });
                    html += `</tbody><tfoot>
                                <tr style="font-weight:bold; background:#f0f0f0;">
                                    <td colspan="2" class="text-right">TOTALS:</td>
                                    <td class="text-right">${formatAmount(totalAssessed)}</td>
                                    <td class="text-right">${formatAmount(totalPaid)}</td>
                                    <td class="text-right">${formatAmount(totalBalance)}</td>
                                </tr>
                             </tfoot></table>`;
                } else {
                    html += `<tr><td colspan="5" class="text-center">No students found for this period.</td></tr></tbody></table>`;
                }
                
                $('#balancesListContainer').html(html);
            } else {
                showToast(res.message, 'error');
                $('#balancesListContainer').html('');
            }
        }, 'json');
    });

    // Report: Financial Statement
    $('#btnGenerateFS').on('click', function() {
        const sDate = $('#fsStartDate').val();
        const eDate = $('#fsEndDate').val();
        
        $('#fsPlaceholder').hide();
        $('#fsContainer').show().html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading Financial Data...</div>');

        $.post('treasurer.php', {
            action: 'get_financial_statement',
            start_date: sDate,
            end_date: eDate
        }, function(res) {
            $('#fsContainer').html(`
                <div class="text-center mb-4">
                    <h3>For the period ${sDate} to ${eDate}</h3>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-outline card-success">
                            <div class="card-header fs-section-title"><h5 class="card-title mb-0">REVENUE / INCOME</h5></div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr><td width="70%">Student Collections</td><td class="text-right" id="fsStudentIncome"></td></tr>
                                    <tr><td>Customer Collections</td><td class="text-right" id="fsCustomerIncome"></td></tr>
                                    <tr class="fs-total-row"><td>Total Income</td><td class="text-right" id="fsTotalIncome"></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-outline card-danger">
                            <div class="card-header fs-section-title"><h5 class="card-title mb-0">EXPENSES</h5></div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-borderless mb-0">
                                    <tbody id="fsExpenseBody"></tbody>
                                    <tr class="fs-total-row"><td width="70%">Total Expenses</td><td class="text-right" id="fsTotalExpenses"></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        ` + (res.net >= 0 ? 
                        `<div class="card card-outline card-success"><div class="card-body"><div class="row fs-net-row align-items-center"><div class="col-md-6 text-right text-muted">NET INCOME</div><div class="col-md-6 text-right"><h2 id="fsNetIncome" class="mb-0 text-success"></h2></div></div></div></div>` : 
                        `<div class="card card-outline card-danger"><div class="card-body"><div class="row fs-net-row align-items-center"><div class="col-md-6 text-right text-muted">NET LOSS</div><div class="col-md-6 text-right"><h2 id="fsNetIncome" class="mb-0 text-danger"></h2></div></div></div></div>`
                        ) + `
                    </div>
                </div>
            `);

            $('#fsStudentIncome').text(formatAmount(res.income.students));
            $('#fsCustomerIncome').text(formatAmount(res.income.customers));
            $('#fsTotalIncome').text(formatAmount(res.income.total));

            let expenseHtml = '';
            res.expenses.breakdown.forEach(ex => {
                expenseHtml += `<tr><td>${ex.category}</td><td class="text-right">${formatAmount(ex.amount)}</td></tr>`;
            });
            $('#fsExpenseBody').html(expenseHtml);
            $('#fsTotalExpenses').text(formatAmount(res.expenses.total));

            $('#fsNetIncome').text(formatAmount(res.net));

        }, 'json');
    });

});
</script>
</body>
</html>