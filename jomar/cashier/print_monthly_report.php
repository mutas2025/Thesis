<?php
require_once '../config.php';

// Get current month and year
 $currentMonth = date('m');
 $currentYear = date('Y');
 $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
 $monthName = date('F', mktime(0, 0, 0, $currentMonth, 1));

// Get daily collections for the month (combining student and customer payments)
 $dailyCollections = [];
 $totalCollection = 0;
for ($day = 1; $day <= $daysInMonth; $day++) {
    $date = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $day, $currentYear));
    
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
        'day' => $day,
        'studentTotal' => $studentTotal,
        'customerTotal' => $customerTotal,
        'total' => $dailyTotal,
        'studentCount' => $studentCount,
        'customerCount' => $customerCount,
        'count' => $dailyCount
    ];
    
    $totalCollection += $dailyTotal;
}

// Get payment method breakdown for the month (combining student and customer payments)
 $paymentMethods = [];

// Get student payment methods
 $query = "SELECT payment_method, SUM(amount) as total, COUNT(*) as count 
          FROM payments 
          WHERE payment_date LIKE '$currentYear-$currentMonth%'
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
          WHERE payment_date LIKE '$currentYear-$currentMonth%'
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

// Get weekly breakdown for the month (combining student and customer payments)
 $weeklyBreakdown = [];
 $weekStart = 1;
while ($weekStart <= $daysInMonth) {
    $weekEnd = min($weekStart + 6, $daysInMonth);
    $weekStartDate = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $weekStart, $currentYear));
    $weekEndDate = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $weekEnd, $currentYear));
    
    // Get student payments for the week
    $query = "SELECT SUM(amount) as total, COUNT(*) as count 
              FROM payments 
              WHERE payment_date BETWEEN '$weekStartDate' AND '$weekEndDate'";
    $result = mysqli_query($conn, $query);
    
    $studentTotal = 0;
    $studentCount = 0;
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $studentTotal = $row['total'] ? $row['total'] : 0;
        $studentCount = $row['count'] ? $row['count'] : 0;
    }
    
    // Get customer payments for the week
    $query = "SELECT SUM(amount) as total, COUNT(*) as count 
              FROM customer_payments 
              WHERE payment_date BETWEEN '$weekStartDate' AND '$weekEndDate'";
    $result = mysqli_query($conn, $query);
    
    $customerTotal = 0;
    $customerCount = 0;
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $customerTotal = $row['total'] ? $row['total'] : 0;
        $customerCount = $row['count'] ? $row['count'] : 0;
    }
    
    $weekTotal = $studentTotal + $customerTotal;
    $weekCount = $studentCount + $customerCount;
    
    $weeklyBreakdown[] = [
        'week' => 'Week ' . ceil($weekStart / 7),
        'start' => $weekStartDate,
        'end' => $weekEndDate,
        'studentTotal' => $studentTotal,
        'customerTotal' => $customerTotal,
        'total' => $weekTotal,
        'studentCount' => $studentCount,
        'customerCount' => $customerCount,
        'count' => $weekCount
    ];
    
    $weekStart = $weekEnd + 1;
}

// Get top payments for the month (combining student and customer payments)
 $topPayments = [];

// Get top student payments
 $query = "SELECT p.*, s.id_number, s.last_name, s.first_name, s.middle_name, 'student' as payment_type
          FROM payments p
          JOIN students s ON p.student_id = s.id
          WHERE p.payment_date LIKE '$currentYear-$currentMonth%'
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
          WHERE payment_date LIKE '$currentYear-$currentMonth%'
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
    <title>Monthly Collection Report</title>
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
        <h2>Monthly Collection Report</h2>
        <p>Period: <?= $monthName ?> <?= $currentYear ?></p>
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
                <td class="text-right">₱<?= number_format($totalCollection / $daysInMonth, 2) ?></td>
            </tr>
        </table>
    </div>
    <h4>Weekly Collection Breakdown</h4>
    <table>
        <thead>
            <tr>
                <th>Week</th>
                <th>Period</th>
                <th class="text-right">Student Amount</th>
                <th class="text-right">Customer Amount</th>
                <th class="text-right">Total Amount</th>

            </tr>
        </thead>
        <tbody>
            <?php foreach ($weeklyBreakdown as $week): ?>
            <tr>
                <td><?= $week['week'] ?></td>
                <td><?= date('M d', strtotime($week['start'])) ?> - <?= date('M d, Y', strtotime($week['end'])) ?></td>
                <td class="text-right">₱<?= number_format($week['studentTotal'], 2) ?></td>
                <td class="text-right">₱<?= number_format($week['customerTotal'], 2) ?></td>
                <td class="text-right">₱<?= number_format($week['total'], 2) ?></td>

            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total</th>
                <th class="text-right">₱<?= number_format(array_sum(array_column($weeklyBreakdown, 'studentTotal')), 2) ?></th>
                <th class="text-right">₱<?= number_format(array_sum(array_column($weeklyBreakdown, 'customerTotal')), 2) ?></th>
                <th class="text-right">₱<?= number_format($totalCollection, 2) ?></th>
             
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