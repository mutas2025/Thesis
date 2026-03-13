<?php
require_once '../config.php';

// Check if student_id is provided
if (!isset($_GET['student_id'])) {
    echo '<div class="alert alert-danger">No student ID provided.</div>';
    exit();
}

 $studentId = intval($_GET['student_id']);

// Get Student Information
 $studentQuery = "SELECT * FROM students WHERE id = $studentId";
 $studentResult = mysqli_query($conn, $studentQuery);

if (!$studentResult || mysqli_num_rows($studentResult) === 0) {
    echo '<div class="alert alert-danger">Student not found.</div>';
    exit();
}

 $student = mysqli_fetch_assoc($studentResult);

// Helper function to format amounts (matches the main file)
function formatAmount($amount) {
    return '₱' . number_format($amount, 2);
}

// Get Student Fees for the Active Academic Years
// Note: We select fees that have an amount greater than 0
 $feesQuery = "SELECT sf.id as student_fee_id, sf.amount, sf.academic_year, sf.semester, f.fee_name, f.fee_type, 
              COALESCE(SUM(pa.allocated_amount), 0) as paid_amount
              FROM student_fees sf
              JOIN fees f ON sf.fee_id = f.id
              LEFT JOIN payment_allocations pa ON sf.id = pa.student_fee_id
              WHERE sf.student_id = $studentId
              GROUP BY sf.id
              ORDER BY sf.academic_year DESC, sf.semester DESC, f.fee_name ASC";

 $feesResult = mysqli_query($conn, $feesQuery);
 $fees = [];
 $totalFees = 0;
 $totalPaid = 0;
 $totalBalance = 0;

if ($feesResult && mysqli_num_rows($feesResult) > 0) {
    while ($row = mysqli_fetch_assoc($feesResult)) {
        $row['balance'] = $row['amount'] - $row['paid_amount'];
        $fees[] = $row;
        $totalFees += $row['amount'];
        $totalPaid += $row['paid_amount'];
        $totalBalance += $row['balance'];
    }
}

// Get Student Payments History
// Join with payments table to get OR number and date
 $paymentsQuery = "SELECT p.id, p.or_number, p.payment_date, p.amount, p.discount, p.payment_method, p.remarks 
                  FROM payments p 
                  WHERE p.student_id = $studentId 
                  ORDER BY p.payment_date DESC, p.id DESC";

 $paymentsResult = mysqli_query($conn, $paymentsQuery);
 $payments = [];

if ($paymentsResult && mysqli_num_rows($paymentsResult) > 0) {
    while ($row = mysqli_fetch_assoc($paymentsResult)) {
        $payments[] = $row;
    }
}

?>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Student Account: <?= htmlspecialchars($student['lastname'] . ', ' . $student['firstname']) ?></h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Fees</span>
                        <span class="info-box-number"><?= formatAmount($totalFees) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Paid</span>
                        <span class="info-box-number"><?= formatAmount($totalPaid) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <?php
                    $balanceColor = 'bg-danger';
                    if ($totalBalance <= 0) $balanceColor = 'bg-primary';
                ?>
                <div class="info-box <?= $balanceColor ?>">
                    <span class="info-box-icon"><i class="fas fa-balance-scale"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Balance</span>
                        <span class="info-box-number"><?= formatAmount($totalBalance) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Fees Breakdown -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Fees Breakdown</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addFeeModal">
                        <i class="fas fa-plus"></i> Add Fee
                    </button>
                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#applyDefaultFeesModal">
                        <i class="fas fa-list"></i> Apply Default Fees
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" id="generateStatementBtn">
                        <i class="fas fa-print"></i> Statement of Account
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" id="generateAssessmentBtn">
                        <i class="fas fa-calculator"></i> Assessment
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fee Name</th>
                                <th>Type</th>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th class="text-right">Amount</th>
                                <th class="text-right">Paid</th>
                                <th class="text-right">Balance</th>
                                <th class="text-right">Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($fees)): ?>
                                <tr>
                                    <td colspan="9" class="text-center">No fees found for this student.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($fees as $fee): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($fee['fee_name']) ?></td>
                                        <td><?= ucfirst($fee['fee_type']) ?></td>
                                        <td><?= htmlspecialchars($fee['academic_year']) ?></td>
                                        <td><?= htmlspecialchars($fee['semester']) ?></td>
                                        <td class="text-right"><?= formatAmount($fee['amount']) ?></td>
                                        <td class="text-right"><?= formatAmount($fee['paid_amount']) ?></td>
                                        <td class="text-right font-weight-bold"><?= formatAmount($fee['balance']) ?></td>
                                        <td class="text-right">
                                            <?php if ($fee['balance'] <= 0): ?>
                                                <span class="badge badge-success">Fully Paid</span>
                                            <?php elseif ($fee['paid_amount'] > 0): ?>
                                                <span class="badge badge-warning">Partial</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Unpaid</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editFeeModal"
                                                    data-id="<?= $fee['fee_id'] ?>"
                                                    data-student-fee-id="<?= $fee['student_fee_id'] ?>"
                                                    data-base-amount="<?= $fee['amount'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteFeeModal"
                                                    data-id="<?= $fee['student_fee_id'] ?>"
                                                    data-name="<?= $fee['fee_name'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <!-- Payment History -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Payment History</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#addPaymentModal">
                        <i class="fas fa-plus"></i> Record Payment
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>OR Number</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th class="text-right">Amount</th>
                                <th class="text-right">Discount</th>
                                <th class="text-right">Net Amount</th>
                                <th>Remarks</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No payment history found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payments as $payment): 
                                    $netAmount = $payment['amount'] - $payment['discount'];
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($payment['or_number']) ?></td>
                                        <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                        <td><?= htmlspecialchars($payment['payment_method']) ?></td>
                                        <td class="text-right"><?= formatAmount($payment['amount']) ?></td>
                                        <td class="text-right text-danger"><?= formatAmount($payment['discount']) ?></td>
                                        <td class="text-right font-weight-bold"><?= formatAmount($netAmount) ?></td>
                                        <td><?= htmlspecialchars($payment['remarks'] ?: '-') ?></td>
                                        <td class="text-right">
                                            <div class="btn-group">
                                                <!-- Print Button -->
                                                <button type="button" class="btn btn-sm btn-info" onclick="window.open('print_receipt.php?payment_id=<?= $payment['id'] ?>', '_blank')">
                                                    <i class="fas fa-print"></i>
                                                </button>
                                                
                                                <!-- 
                                                     CANCEL BUTTON 
                                                     Note: The class 'cancel-student-payment-btn' is required by the JavaScript in cashier.php 
                                                -->
                                                <button type="button" class="btn btn-sm btn-warning cancel-student-payment-btn" 
                                                        data-toggle="modal" 
                                                        data-target="#cancelTransactionModal"
                                                        data-id="<?= $payment['id'] ?>" 
                                                        data-or="<?= htmlspecialchars($payment['or_number']) ?>">
                                                    <i class="fas fa-ban"></i> Cancel
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>