<?php
session_start();
require_once '../config.php';
// Require login and cashier role
requireRole('cashier');

// Check if payment ID is provided
if (!isset($_GET['payment_id'])) {
    $_SESSION['error'] = "Payment ID is required";
    header("Location: cashier.php#payments");
    exit();
}

 $paymentId = mysqli_real_escape_string($conn, $_GET['payment_id']);

// Get payment details
 $query = "SELECT p.*, s.id_number, s.last_name, s.first_name, s.middle_name 
          FROM payments p
          JOIN students s ON p.student_id = s.id
          WHERE p.id = $paymentId";
 $result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Payment not found";
    header("Location: cashier.php#payments");
    exit();
}

 $payment = mysqli_fetch_assoc($result);

// Get student's current course
 $query = "SELECT c.coursename, c.courselevel 
          FROM enrollments e
          JOIN courses c ON e.course_id = c.id
          WHERE e.student_id = " . $payment['student_id'] . " 
          AND e.status IN ('Enrolled', 'Registered')
          ORDER BY e.enrollment_date DESC
          LIMIT 1";
 $courseResult = mysqli_query($conn, $query);
 $course = mysqli_num_rows($courseResult) > 0 ? mysqli_fetch_assoc($courseResult) : null;

// --- NEW: Get Specific Payment Allocations for this Receipt ---
// This fetches exactly what was paid for in THIS transaction
 $allocationsQuery = "SELECT pa.allocated_amount, f.fee_name, f.fee_type, sf.academic_year, sf.semester, pa.student_fee_id
                     FROM payment_allocations pa
                     LEFT JOIN student_fees sf ON pa.student_fee_id = sf.id
                     LEFT JOIN fees f ON sf.fee_id = f.id
                     WHERE pa.payment_id = $paymentId";
 $allocationsResult = mysqli_query($conn, $allocationsQuery);

 $allocations = [];
 $totalAllocated = 0;

if ($allocationsResult) {
    while ($row = mysqli_fetch_assoc($allocationsResult)) {
        $allocations[] = $row;
        $totalAllocated += $row['allocated_amount'];
    }
}

// Calculate net amount for this payment
 $discount = $payment['discount'] ? $payment['discount'] : 0;
 $netAmount = $payment['amount'] - $discount;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }
        
        .receipts-container {
            display: flex;
            width: 100%;
            height: 100vh;
        }
        
        .receipt {
            flex: 1;
            padding: 20px;
            border: 1px solid #ccc;
            position: relative;
        }
        
        .receipt:first-child {
            border-right: 2px dashed #999;
        }
        
        .receipt-copy {
            position: absolute;
            top: 10px;
            right: 10px;
            font-weight: bold;
            font-size: 14px;
            color: #666;
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        
        .receipt-header img {
            max-height: 60px;
            margin-bottom: 10px;
        }
        
        .receipt-header h1 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        
        .receipt-header h2 {
            margin: 5px 0 0;
            font-size: 16px;
            color: #666;
        }
        
        .school-address, .school-contact {
            font-size: 14px;
            margin: 5px 0;
            color: #555;
        }
        
        .receipt-title {
            text-align: center;
            margin: 15px 0;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .receipt-details {
            margin-bottom: 15px;
        }
        
        .receipt-details table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .receipt-details td {
            padding: 5px 0;
            font-size: 14px;
        }
        
        .receipt-details .label {
            font-weight: bold;
            width: 100px;
        }
        
        .receipt-info {
            margin-bottom: 15px;
        }
        
        .receipt-info h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .receipt-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .receipt-info td {
            padding: 5px 0;
            font-size: 14px;
        }
        
        .receipt-info .label {
            font-weight: bold;
            width: 100px;
        }
        
        /* Styles for the Dynamic Allocation Table */
        .fee-breakdown {
            margin: 15px 0;
        }
        
        .fee-breakdown h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .fee-breakdown table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .fee-breakdown th, .fee-breakdown td {
            padding: 5px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 13px; /* Slightly smaller to fit more items if needed */
        }
        
        .fee-breakdown th {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        
        .fee-breakdown .amount {
            text-align: right;
        }
        
        .receipt-amount {
            text-align: center;
            margin: 15px 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .receipt-amount .amount {
            color: #28a745;
        }
        
        .discount-amount {
            color: #dc3545;
        }
        
        .net-amount {
            color: #28a745;
        }
        
        .receipt-footer {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        
        .receipt-signature {
            width: 45%;
            text-align: center;
            font-size: 14px;
        }
        
        .receipt-signature .line {
            border-top: 1px solid #333;
            margin-top: 30px;
            padding-top: 5px;
        }
        
        .receipt-actions {
            display: none;
        }
        
        @media print {
            .receipts-container {
                page-break-after: always;
            }
            
            .receipt {
                border: none;
                page-break-inside: avoid;
            }
            
            .receipt:first-child {
                border-right: none;
                border-bottom: 2px dashed #999;
            }
        }
    </style>
</head>
<body>
    <div class="receipts-container">
        <!-- School Copy -->
        <div class="receipt">
            <div class="receipt-copy">SCHOOL COPY</div>
            
            <div class="receipt-header">
                <img src="../uploads/csr.png" alt="CSR Logo">
                <h1><?= SCHOOL_NAME ?></h1>
                <div class="school-address"><?= SCHOOL_ADDRESS ?></div>
                <div class="school-contact">Tel: <?= SCHOOL_CONTACT_NO ?> | Email: <?= SCHOOL_EMAIL ?></div>
                <h2>Cashier's Office</h2>
            </div>
            
            <div class="receipt-title">
                Official Receipt
            </div>
            
            <div class="receipt-details">
                <table>
                    <tr>
                        <td class="label">OR Number:</td>
                        <td><?= $payment['or_number'] ?></td>
                        <td class="label">Date:</td>
                        <td><?= date('F d, Y', strtotime($payment['payment_date'])) ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="receipt-info">
                <h3>Student Information</h3>
                <table>
                    <tr>
                        <td class="label">ID Number:</td>
                        <td><?= $payment['id_number'] ?></td>
                    </tr>
                    <tr>
                        <td class="label">Name:</td>
                        <td><?= $payment['last_name'] . ', ' . $payment['first_name'] . ' ' . $payment['middle_name'] ?></td>
                    </tr>
                    <tr>
                        <td class="label">Course:</td>
                        <td>
                            <?php 
                            if ($course) {
                                echo $course['coursename'] . ' - ' . $course['courselevel'];
                            } else {
                                echo "Not enrolled";
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="fee-breakdown">
                <h3>Payment Particulars</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Particular / Fee Name</th>
                            <th>Type</th>
                            <th>AY / Sem</th>
                            <th class="amount">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allocations)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No specific allocations recorded (General Payment).</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allocations as $item): ?>
                                <tr>
                                    <td>
                                        <?php 
                                            if (!empty($item['fee_name'])) {
                                                echo htmlspecialchars($item['fee_name']);
                                            } else {
                                                echo "<em>General Payment / Overpayment</em>";
                                            }
                                        ?>
                                    </td>
                                    <td><?= ucfirst($item['fee_type'] ?? '-') ?></td>
                                    <td>
                                        <?php 
                                            if (!empty($item['academic_year'])) {
                                                echo htmlspecialchars($item['academic_year']) . ' ' . htmlspecialchars($item['semester']);
                                            } else {
                                                echo "-";
                                            }
                                        ?>
                                    </td>
                                    <td class="amount">₱<?= number_format($item['allocated_amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Summary Row for Allocation -->
                        <tr style="border-top: 2px solid #333; background-color: #f0f0f0;">
                            <td colspan="3" style="text-align: right; font-weight: bold;">Total Allocated:</td>
                            <td class="amount" style="font-weight: bold;">₱<?= number_format($totalAllocated, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="receipt-amount">
                Amount Tendered: <span class="amount">₱<?= number_format($payment['amount'], 2) ?></span>
                <?php if ($discount > 0): ?>
                <br>
                Discount Applied: <span class="discount-amount">- ₱<?= number_format($discount, 2) ?></span>
                <br>
                <strong>Net Amount Received: <span class="net-amount">₱<?= number_format($netAmount, 2) ?></span></strong>
                <?php else: ?>
                <br>
                <strong>Net Amount Received: <span class="net-amount">₱<?= number_format($netAmount, 2) ?></span></strong>
                <?php endif; ?>
            </div>
            
            <div class="receipt-info">
                <h3>Payment Details</h3>
                <table>
                    <tr>
                        <td class="label">Payment Method:</td>
                        <td><?= $payment['payment_method'] ?></td>
                    </tr>
                    <tr>
                        <td class="label">Remarks:</td>
                        <td><?= $payment['remarks'] ?: 'None' ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="receipt-footer">
                <div class="receipt-signature">
                    <div>Received By:</div>
                    <div class="line">Cashier</div>
                </div>
                
                <div class="receipt-signature">
                    <div>Student's Signature:</div>
                    <div class="line"></div>
                </div>
            </div>
        </div>
        
        <!-- Student Copy -->
        <div class="receipt">
            <div class="receipt-copy">STUDENT COPY</div>
            
            <div class="receipt-header">
                <img src="../uploads/csr.png" alt="CSR Logo">
                <h1><?= SCHOOL_NAME ?></h1>
                <div class="school-address"><?= SCHOOL_ADDRESS ?></div>
                <div class="school-contact">Tel: <?= SCHOOL_CONTACT_NO ?> | Email: <?= SCHOOL_EMAIL ?></div>
                <h2>Cashier's Office</h2>
            </div>
            
            <div class="receipt-title">
                Official Receipt
            </div>
            
            <div class="receipt-details">
                <table>
                    <tr>
                        <td class="label">OR Number:</td>
                        <td><?= $payment['or_number'] ?></td>
                        <td class="label">Date:</td>
                        <td><?= date('F d, Y', strtotime($payment['payment_date'])) ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="receipt-info">
                <h3>Student Information</h3>
                <table>
                    <tr>
                        <td class="label">ID Number:</td>
                        <td><?= $payment['id_number'] ?></td>
                    </tr>
                    <tr>
                        <td class="label">Name:</td>
                        <td><?= $payment['last_name'] . ', ' . $payment['first_name'] . ' ' . $payment['middle_name'] ?></td>
                    </tr>
                    <tr>
                        <td class="label">Course:</td>
                        <td>
                            <?php 
                            if ($course) {
                                echo $course['coursename'] . ' - ' . $course['courselevel'];
                            } else {
                                echo "Not enrolled";
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="fee-breakdown">
                <h3>Payment Particulars</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Particular / Fee Name</th>
                            <th>Type</th>
                            <th>AY / Sem</th>
                            <th class="amount">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allocations)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No specific allocations recorded (General Payment).</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allocations as $item): ?>
                                <tr>
                                    <td>
                                        <?php 
                                            if (!empty($item['fee_name'])) {
                                                echo htmlspecialchars($item['fee_name']);
                                            } else {
                                                echo "<em>General Payment / Overpayment</em>";
                                            }
                                        ?>
                                    </td>
                                    <td><?= ucfirst($item['fee_type'] ?? '-') ?></td>
                                    <td>
                                        <?php 
                                            if (!empty($item['academic_year'])) {
                                                echo htmlspecialchars($item['academic_year']) . ' ' . htmlspecialchars($item['semester']);
                                            } else {
                                                echo "-";
                                            }
                                        ?>
                                    </td>
                                    <td class="amount">₱<?= number_format($item['allocated_amount'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- Summary Row for Allocation -->
                        <tr style="border-top: 2px solid #333; background-color: #f0f0f0;">
                            <td colspan="3" style="text-align: right; font-weight: bold;">Total Allocated:</td>
                            <td class="amount" style="font-weight: bold;">₱<?= number_format($totalAllocated, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="receipt-amount">
                Amount Tendered: <span class="amount">₱<?= number_format($payment['amount'], 2) ?></span>
                <?php if ($discount > 0): ?>
                <br>
                Discount Applied: <span class="discount-amount">- ₱<?= number_format($discount, 2) ?></span>
                <br>
                <strong>Net Amount Received: <span class="net-amount">₱<?= number_format($netAmount, 2) ?></span></strong>
                <?php else: ?>
                <br>
                <strong>Net Amount Received: <span class="net-amount">₱<?= number_format($netAmount, 2) ?></span></strong>
                <?php endif; ?>
            </div>
            
            <div class="receipt-info">
                <h3>Payment Details</h3>
                <table>
                    <tr>
                        <td class="label">Payment Method:</td>
                        <td><?= $payment['payment_method'] ?></td>
                    </tr>
                    <tr>
                        <td class="label">Remarks:</td>
                        <td><?= $payment['remarks'] ?: 'None' ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="receipt-footer">
                <div class="receipt-signature">
                    <div>Received By:</div>
                    <div class="line">Cashier</div>
                </div>
                
                <div class="receipt-signature">
                    <div>Student's Signature:</div>
                    <div class="line"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="receipt-actions">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Print Receipt
        </button>
        <a href="cashier.php#payments" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Payments
        </a>
    </div>
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>