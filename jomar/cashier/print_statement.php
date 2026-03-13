<?php
session_start();
require_once '../config.php';
requireRole('cashier');

// Check if student_id is provided
if (!isset($_GET['student_id'])) {
    echo "<div class='alert alert-danger'>Error: Student ID not provided</div>";
    exit();
}

 $studentId = mysqli_real_escape_string($conn, $_GET['student_id']);

// Get student details
 $query = "SELECT * FROM students WHERE id = $studentId";
 $result = mysqli_query($conn, $query);
if (!$result || mysqli_num_rows($result) == 0) {
    echo "<div class='alert alert-danger'>Error: Student not found</div>";
    exit();
}
 $student = mysqli_fetch_assoc($result);

// Get student fees
 $fees = [];
 $query = "SELECT sf.*, f.fee_name, f.fee_type 
          FROM student_fees sf
          JOIN fees f ON sf.fee_id = f.id
          WHERE sf.student_id = $studentId
          ORDER BY sf.academic_year DESC, sf.semester DESC, f.fee_type";
 $result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $fees[] = $row;
    }
}

// Get student payments
 $payments = [];
 $query = "SELECT * FROM payments WHERE student_id = $studentId ORDER BY payment_date DESC";
 $result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
}

// Calculate totals
 $totalFees = 0;
foreach ($fees as $fee) {
    $totalFees += $fee['amount'];
}

 $totalPayments = 0;
foreach ($payments as $payment) {
    $totalPayments += $payment['amount'];
}

 $balance = $totalFees - $totalPayments;

// Get student's current course
 $courseInfo = "Not enrolled";
 $query = "SELECT c.coursename, c.courselevel 
          FROM enrollments e
          JOIN courses c ON e.course_id = c.id
          WHERE e.student_id = $studentId 
          AND e.status IN ('Enrolled', 'Registered')
          ORDER BY e.enrollment_date DESC
          LIMIT 1";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $courseInfo = $row['coursename'] . ' - ' . $row['courselevel'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statement of Account - <?= $student['last_name'] . ', ' . $student['first_name'] ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 14px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-height: 60px;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
        }
        .school-info {
            margin-bottom: 15px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .school-address, .school-contact {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .student-info {
            margin-bottom: 20px;
        }
        .student-info p {
            margin: 5px 0;
        }
        h3 {
            margin-top: 30px;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .summary-table td:first-child {
            width: 50%;
        }
        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 12px;
        }
        .no-print {
            display: none;
        }
        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../uploads/csr.png" alt="CSR Logo">
        <div class="school-info">
            <!-- Updated to use config constants -->
            <div class="school-name"><?= SCHOOL_NAME ?></div>
            <div class="school-address"><?= SCHOOL_ADDRESS ?></div>
            <div class="school-contact">Tel: <?= SCHOOL_CONTACT_NO ?> | Email: <?= SCHOOL_EMAIL ?></div>
        </div>
        <h2>Statement of Account</h2>
    </div>
    
    <div class="student-info">
        <p><strong>Student Name:</strong> <?= $student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name'] ?></p>
        <p><strong>ID Number:</strong> <?= $student['id_number'] ?></p>
        <p><strong>Course:</strong> <?= $courseInfo ?></p>
        <p><strong>Date:</strong> <?= date('F d, Y') ?></p>
    </div>
    
    <h3>Fees</h3>
    <table>
        <thead>
            <tr>
                <th>Fee Name</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Academic Year</th>
                <th>Semester</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($fees)): ?>
            <tr>
                <td colspan="5" style="text-align: center;">No fees found for this student</td>
            </tr>
            <?php else: ?>
                <?php foreach ($fees as $fee): ?>
                <tr>
                    <td><?= $fee['fee_name'] ?></td>
                    <td><?= ucfirst($fee['fee_type']) ?></td>
                    <td>₱<?= number_format($fee['amount'], 2) ?></td>
                    <td><?= $fee['academic_year'] ?></td>
                    <td><?= $fee['semester'] ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2"><strong>Total Fees</strong></td>
                <td><strong>₱<?= number_format($totalFees, 2) ?></strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    
    <h3>Payments</h3>
    <table>
        <thead>
            <tr>
                <th>OR Number</th>
                <th>Amount</th>
                <th>Payment Date</th>
                <th>Payment Method</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($payments)): ?>
            <tr>
                <td colspan="5" style="text-align: center;">No payments found for this student</td>
            </tr>
            <?php else: ?>
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?= $payment['or_number'] ?></td>
                    <td>₱<?= number_format($payment['amount'], 2) ?></td>
                    <td><?= date('F d, Y', strtotime($payment['payment_date'])) ?></td>
                    <td><?= $payment['payment_method'] ?></td>
                    <td><?= $payment['remarks'] ?: 'N/A' ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="1"><strong>Total Payments</strong></td>
                <td><strong>₱<?= number_format($totalPayments, 2) ?></strong></td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
    
    <h3>Account Summary</h3>
    <table class="summary-table">
        <tr>
            <td><strong>Total Fees</strong></td>
            <td>₱<?= number_format($totalFees, 2) ?></td>
        </tr>
        <tr>
            <td><strong>Total Payments</strong></td>
            <td>₱<?= number_format($totalPayments, 2) ?></td>
        </tr>
        <tr>
            <td><strong>Balance</strong></td>
            <td>₱<?= number_format($balance, 2) ?></td>
        </tr>
    </table>
    
    <div class="footer">
        <p>Generated on <?= date('F d, Y h:i A') ?></p>
        <p>Cashier System</p>
    </div>
    
    <div class="no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>