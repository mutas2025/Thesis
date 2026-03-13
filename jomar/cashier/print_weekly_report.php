<?php
require_once '../config.php';

// Get current week's start and end dates
 $monday = date('Y-m-d', strtotime('monday this week'));
 $sunday = date('Y-m-d', strtotime('sunday this week'));

// Get daily collections for the week (combining student and customer payments)
 $dailyCollections = [];
 $totalCollection = 0;
for ($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime($monday . ' + ' . $i . ' days'));
    $dayName = date('l', strtotime($date));
    
    // Get student payments for the day
    $query = "SELECT SUM(amount) as total, COUNT(*) as count 
              FROM payments 
              WHERE payment_date = '$date'";
    $result = mysqli_query($conn, $query);
    
    $studentTotal = 0;
    $studentCount = 0;
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $studentTotal = $row['total'] ? $row['total'] : 0;
        $studentCount = $row['count'] ? $row['count'] : 0;
    }
    
    // Get customer payments for the day
    $query = "SELECT SUM(amount) as total, COUNT(*) as count 
              FROM customer_payments 
              WHERE payment_date = '$date'";
    $result = mysqli_query($conn, $query);
    
    $customerTotal = 0;
    $customerCount = 0;
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $customerTotal = $row['total'] ? $row['total'] : 0;
        $customerCount = $row['count'] ? $row['count'] : 0;
    }
    
    $dailyTotal = $studentTotal + $customerTotal;
    $dailyCount = $studentCount + $customerCount;
    
    $dailyCollections[] = [
        'date' => $date,
        'dayName' => $dayName,
        'studentTotal' => $studentTotal,
        'customerTotal' => $customerTotal,
        'total' => $dailyTotal,
        'studentCount' => $studentCount,
        'customerCount' => $customerCount,
        'count' => $dailyCount
    ];
    
    $totalCollection += $dailyTotal;
}

// Get payment method breakdown for the week (combining student and customer payments)
 $paymentMethods = [];

// Get student payment methods
 $query = "SELECT payment_method, SUM(amount) as total, COUNT(*) as count 
          FROM payments 
          WHERE payment_date BETWEEN '$monday' AND '$sunday'
          GROUP BY payment_method";
 $result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $paymentMethods[] = $row;
    }
}

// Get customer payment methods
 $query = "SELECT payment_method, SUM(amount) as total, COUNT(*) as count 
          FROM customer_payments 
          WHERE payment_date BETWEEN '$monday' AND '$sunday'
          GROUP BY payment_method";
 $result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Check if payment method already exists from student payments
        $found = false;
        foreach ($paymentMethods as $key => $method) {
            if ($method['payment_method'] == $row['payment_method']) {
                // Combine the totals and counts
                $paymentMethods[$key]['total'] += $row['total'];
                $paymentMethods[$key]['count'] += $row['count'];
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $paymentMethods[] = $row;
        }
    }
}

// Sort payment methods by total amount
usort($paymentMethods, function($a, $b) {
    return $b['total'] - $a['total'];
});

// Get top payments for the week (combining student and customer payments)
 $topPayments = [];

// Get top student payments
 $query = "SELECT p.*, s.id_number, s.last_name, s.first_name, s.middle_name, 'student' as payment_type
          FROM payments p
          JOIN students s ON p.student_id = s.id
          WHERE p.payment_date BETWEEN '$monday' AND '$sunday'
          ORDER BY p.amount DESC
          LIMIT 10";
 $result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $topPayments[] = $row;
    }
}

// Get top customer payments
 $query = "SELECT *, 'customer' as payment_type
          FROM customer_payments 
          WHERE payment_date BETWEEN '$monday' AND '$sunday'
          ORDER BY amount DESC
          LIMIT 10";
 $result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $topPayments[] = $row;
    }
}

// Sort all payments by amount and take top 10
usort($topPayments, function($a, $b) {
    return $b['amount'] - $a['amount'];
});
 $topPayments = array_slice($topPayments, 0, 10);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Weekly Collection Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
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
        .report-header h2 {
            margin: 10px 0 5px;
            font-size: 18px;
        }
        .report-header p {
            margin: 5px 0;
        }
        .report-summary {
            margin-bottom: 20px;
        }
        .report-summary h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .report-summary table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-summary td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .report-summary td:first-child {
            font-weight: bold;
            width: 50%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tfoot th {
            background-color: #f2f2f2;
        }
        h4 {
            margin: 20px 0 10px 0;
            font-size: 14px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 10px;
            font-weight: bold;
            line-height: 1;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 4px;
        }
        .badge-student {
            background-color: #007bff;
        }
        .badge-customer {
            background-color: #17a2b8;
        }
        @media print {
            body {
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="report-header">
        <div class="school-info">
            <!-- Updated to use config constants -->
            <div class="school-name"><?= SCHOOL_NAME ?></div>
            <div class="school-address"><?= SCHOOL_ADDRESS ?></div>
            <div class="school-contact">Tel: <?= SCHOOL_CONTACT_NO ?> | Email: <?= SCHOOL_EMAIL ?></div>
        </div>
        <h2>Weekly Collection Report</h2>
        <p>Period: <?= date('F d, Y', strtotime($monday)) ?> to <?= date('F d, Y', strtotime($sunday)) ?></p>
        <p>Generated on: <?= date('F d, Y h:i A') ?></p>
    </div>
    <div class="report-summary">
        <h4>Summary</h4>
        <table>
            <tr>
                <td>Total Collection:</td>
                <td class="text-right">₱<?= number_format($totalCollection, 2) ?></td>
            </tr>
            <tr>
                <td>Total Transactions:</td>
                <td class="text-right"><?= array_sum(array_column($dailyCollections, 'count')) ?></td>
            </tr>
            <tr>
                <td>Student Transactions:</td>
                <td class="text-right"><?= array_sum(array_column($dailyCollections, 'studentCount')) ?></td>
            </tr>
            <tr>
                <td>Customer Transactions:</td>
                <td class="text-right"><?= array_sum(array_column($dailyCollections, 'customerCount')) ?></td>
            </tr>
            <tr>
                <td>Average Daily Collection:</td>
                <td class="text-right">₱<?= number_format($totalCollection / 7, 2) ?></td>
            </tr>
        </table>
    </div>
    <h4>Daily Collection Breakdown</h4>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Day</th>
                <th class="text-right">Student Amount</th>
                <th class="text-right">Customer Amount</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">Student Count</th>
                <th class="text-right">Customer Count</th>
                <th class="text-right">Total Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dailyCollections as $day): ?>
            <tr>
                <td><?= date('F d, Y', strtotime($day['date'])) ?></td>
                <td><?= $day['dayName'] ?></td>
                <td class="text-right">₱<?= number_format($day['studentTotal'], 2) ?></td>
                <td class="text-right">₱<?= number_format($day['customerTotal'], 2) ?></td>
                <td class="text-right">₱<?= number_format($day['total'], 2) ?></td>
                <td class="text-right"><?= $day['studentCount'] ?></td>
                <td class="text-right"><?= $day['customerCount'] ?></td>
                <td class="text-right"><?= $day['count'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total</th>
                <th class="text-right">₱<?= number_format(array_sum(array_column($dailyCollections, 'studentTotal')), 2) ?></th>
                <th class="text-right">₱<?= number_format(array_sum(array_column($dailyCollections, 'customerTotal')), 2) ?></th>
                <th class="text-right">₱<?= number_format($totalCollection, 2) ?></th>
                <th class="text-right"><?= array_sum(array_column($dailyCollections, 'studentCount')) ?></th>
                <th class="text-right"><?= array_sum(array_column($dailyCollections, 'customerCount')) ?></th>
                <th class="text-right"><?= array_sum(array_column($dailyCollections, 'count')) ?></th>
            </tr>
        </tfoot>
    </table>
    <h4>Payment Method Breakdown</h4>
    <table>
        <thead>
            <tr>
                <th>Payment Method</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Transactions</th>
                <th class="text-right">Percentage</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($paymentMethods as $method): ?>
            <tr>
                <td><?= $method['payment_method'] ?></td>
                <td class="text-right">₱<?= number_format($method['total'], 2) ?></td>
                <td class="text-right"><?= $method['count'] ?></td>
                <td class="text-right"><?= number_format(($method['total'] / $totalCollection) * 100, 2) ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h4>Top 10 Payments</h4>
    <table>
        <thead>
            <tr>
                <th>OR Number</th>
                <th>Payer</th>
                <th>ID/Type</th>
                <th class="text-right">Amount</th>
                <th>Date</th>
                <th>Payment Method</th>
                <th class="text-center">Payment Type</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($topPayments as $payment): ?>
            <tr>
                <td><?= $payment['or_number'] ?></td>
                <td>
                    <?php if ($payment['payment_type'] == 'student'): ?>
                        <?= $payment['last_name'] . ', ' . $payment['first_name'] . ' ' . $payment['middle_name'] ?>
                    <?php else: ?>
                        <?= $payment['customer_name'] ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($payment['payment_type'] == 'student'): ?>
                        <?= $payment['id_number'] ?>
                    <?php else: ?>
                        <?= ucfirst($payment['customer_type']) ?>
                    <?php endif; ?>
                </td>
                <td class="text-right">₱<?= number_format($payment['amount'], 2) ?></td>
                <td><?= date('F d, Y', strtotime($payment['payment_date'])) ?></td>
                <td><?= $payment['payment_method'] ?></td>
                <td class="text-center">
                    <?php if ($payment['payment_type'] == 'student'): ?>
                        <span class="badge badge-student">Student</span>
                    <?php else: ?>
                        <span class="badge badge-customer">Customer</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Print Report</button>
    </div>
</body>
</html>