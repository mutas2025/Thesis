<?php
require_once '../config.php';

// Get date range from URL parameters
 $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
 $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
 $printMode = isset($_GET['print']);

// Validate dates
if (empty($startDate) || empty($endDate)) {
    die("Error: Please provide both start and end dates.");
}

// Format dates for display
 $startDisplay = date('F d, Y', strtotime($startDate));
 $endDisplay = date('F d, Y', strtotime($endDate));

// Calculate beginning balance (day before start date)
 $beginningDate = date('Y-m-d', strtotime($startDate . ' -1 day'));
 $beginningBalance = 0;

// Get total inflow before start date
 $query = "SELECT SUM(amount - discount) as total FROM payments WHERE payment_date <= '$beginningDate'";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $beginningBalance += $row['total'] ? $row['total'] : 0;
}

 $query = "SELECT SUM(amount) as total FROM customer_payments WHERE payment_date <= '$beginningDate'";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $beginningBalance += $row['total'] ? $row['total'] : 0;
}

// Get total outflow before start date
 $query = "SELECT SUM(amount) as total FROM disbursements WHERE date <= '$beginningDate'";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $beginningBalance -= $row['total'] ? $row['total'] : 0;
}

// Calculate total inflow (collections)
 $totalInflow = 0;
 $totalDiscounts = 0;
 $inflowByType = [];

// Student payments
 $query = "SELECT SUM(amount - discount) as net_total, SUM(discount) as discount_total, SUM(amount) as gross_total, COUNT(*) as count 
          FROM payments 
          WHERE payment_date BETWEEN '$startDate' AND '$endDate'";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $studentNetTotal = $row['net_total'] ? $row['net_total'] : 0;
    $studentDiscount = $row['discount_total'] ? $row['discount_total'] : 0;
    $studentGrossTotal = $row['gross_total'] ? $row['gross_total'] : 0;
    $studentCount = $row['count'] ? $row['count'] : 0;
    $totalInflow += $studentNetTotal;
    $totalDiscounts += $studentDiscount;
    $inflowByType['Student Payments'] = [
        'net_total' => $studentNetTotal,
        'discount_total' => $studentDiscount,
        'gross_total' => $studentGrossTotal,
        'count' => $studentCount
    ];
}

// Customer payments (no discounts for customer payments)
 $query = "SELECT SUM(amount) as total, COUNT(*) as count 
          FROM customer_payments 
          WHERE payment_date BETWEEN '$startDate' AND '$endDate'";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $customerTotal = $row['total'] ? $row['total'] : 0;
    $customerCount = $row['count'] ? $row['count'] : 0;
    $totalInflow += $customerTotal;
    $inflowByType['Customer Payments'] = [
        'net_total' => $customerTotal,
        'discount_total' => 0,
        'gross_total' => $customerTotal,
        'count' => $customerCount
    ];
}

// UPDATED SECTION: Total Collection per Default Fees (Itemized)
 $defaultFeeCollections = [];
 $query = "SELECT f.fee_type, f.fee_name, SUM(pa.allocated_amount) as total_allocated
          FROM payment_allocations pa
          JOIN student_fees sf ON pa.student_fee_id = sf.id
          JOIN fees f ON sf.fee_id = f.id
          JOIN payments p ON pa.payment_id = p.id
          WHERE p.payment_date BETWEEN '$startDate' AND '$endDate'
          AND pa.allocated_amount > 0
          GROUP BY f.fee_type, f.fee_name
          ORDER BY f.fee_type, f.fee_name";
 $result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $feeType = $row['fee_type'];
        $feeName = $row['fee_name'];
        $amount = $row['total_allocated'] ? $row['total_allocated'] : 0;
        
        // Group by fee type for better organization
        if (!isset($defaultFeeCollections[$feeType])) {
            $defaultFeeCollections[$feeType] = [];
        }
        
        $defaultFeeCollections[$feeType][] = [
            'fee_name' => $feeName,
            'amount' => $amount
        ];
    }
}

// Calculate total outflow (disbursements)
 $totalOutflow = 0;
 $outflowByCategory = [];
 $query = "SELECT category, SUM(amount) as total, COUNT(*) as count 
          FROM disbursements 
          WHERE date BETWEEN '$startDate' AND '$endDate' 
          GROUP BY category";
 $result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $category = $row['category'];
        $amount = $row['total'] ? $row['total'] : 0;
        $count = $row['count'] ? $row['count'] : 0;
        $totalOutflow += $amount;
        $outflowByCategory[$category] = [
            'total' => $amount,
            'count' => $count
        ];
    }
}

// Calculate net cashflow and ending balance
 $netCashflow = $totalInflow - $totalOutflow;
 $endingBalance = $beginningBalance + $netCashflow;

// Get weekly breakdown
 $weeklyBreakdown = [];
 $periodStart = new DateTime($startDate);
 $periodEnd = new DateTime($endDate);
 $currentWeekStart = clone $periodStart;
 $currentWeekEnd = clone $periodStart;
 $currentWeekEnd->modify('+6 days');
 $weeklyRunningBalance = $beginningBalance;

while ($currentWeekStart <= $periodEnd) {
    if ($currentWeekEnd > $periodEnd) {
        $currentWeekEnd = clone $periodEnd;
    }
    
    $weekStartStr = $currentWeekStart->format('Y-m-d');
    $weekEndStr = $currentWeekEnd->format('Y-m-d');
    $weekNumber = 'Week ' . ceil($currentWeekStart->format('d') / 7);
    
    // Get inflow for the week
    $weekInflow = 0;
    $weekInflowCount = 0;
    $weekDiscounts = 0;
    
    // Student payments for the week
    $query = "SELECT SUM(amount - discount) as net_total, SUM(discount) as discount_total, COUNT(*) as count 
              FROM payments 
              WHERE payment_date BETWEEN '$weekStartStr' AND '$weekEndStr'";
    $result = mysqli_query($conn, $query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $weekInflow += $row['net_total'] ? $row['net_total'] : 0;
        $weekDiscounts += $row['discount_total'] ? $row['discount_total'] : 0;
        $weekInflowCount += $row['count'] ? $row['count'] : 0;
    }
    
    // Customer payments for the week
    $query = "SELECT SUM(amount) as total, COUNT(*) as count 
              FROM customer_payments 
              WHERE payment_date BETWEEN '$weekStartStr' AND '$weekEndStr'";
    $result = mysqli_query($conn, $query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $weekInflow += $row['total'] ? $row['total'] : 0;
        $weekInflowCount += $row['count'] ? $row['count'] : 0;
    }
    
    // Get outflow for the week
    $weekOutflow = 0;
    $weekOutflowCount = 0;
    
    $query = "SELECT SUM(amount) as total, COUNT(*) as count 
              FROM disbursements 
              WHERE date BETWEEN '$weekStartStr' AND '$weekEndStr'";
    $result = mysqli_query($conn, $query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $weekOutflow += $row['total'] ? $row['total'] : 0;
        $weekOutflowCount += $row['count'] ? $row['count'] : 0;
    }
    
    $weekNet = $weekInflow - $weekOutflow;
    $weekBeginningBalance = $weeklyRunningBalance;
    $weeklyRunningBalance += $weekNet;
    
    $weeklyBreakdown[] = [
        'week' => $weekNumber,
        'start' => $weekStartStr,
        'end' => $weekEndStr,
        'beginning_balance' => $weekBeginningBalance,
        'inflow' => $weekInflow,
        'inflowCount' => $weekInflowCount,
        'discounts' => $weekDiscounts,
        'outflow' => $weekOutflow,
        'outflowCount' => $weekOutflowCount,
        'net' => $weekNet,
        'ending_balance' => $weeklyRunningBalance
    ];
    
    // Move to next week
    $currentWeekStart->modify('+7 days');
    $currentWeekEnd->modify('+7 days');
}

// Determine financial status
if ($endingBalance >= 0) {
    if ($netCashflow >= 0) {
        $financialStatus = "Healthy - Positive cashflow and positive balance";
        $statusClass = "status-healthy";
    } else {
        $financialStatus = "Warning - Spending more than earning but still positive balance";
        $statusClass = "status-warning";
    }
} else {
    if ($netCashflow >= 0) {
        $financialStatus = "Recovering - Positive cashflow but still negative balance";
        $statusClass = "status-recovering";
    } else {
        $financialStatus = "Critical - Negative cashflow and negative balance";
        $statusClass = "status-critical";
    }
}

// School information (Updated to use config constants)
 $schoolLogo = "../uploads/csr.png"; // Path to your school logo
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cashflow Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .school-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 15px;
        }
        .school-logo {
            max-height: 60px;
            margin-bottom: 10px;
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
        .cashflow-positive {
            color: green;
            font-weight: bold;
        }
        .cashflow-negative {
            color: red;
            font-weight: bold;
        }
        .cashflow-zero {
            color: #6c757d;
            font-weight: bold;
        }
        .discount-amount {
            color: #dc3545;
        }
        .balance-row td {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .financial-status {
            margin-top: 30px;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
        }
        .status-healthy {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
        .status-recovering {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .status-critical {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .fee-type-header {
            background-color: #e9ecef;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        .fee-name-cell {
            padding-left: 20px;
        }
        .fee-type-total {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .row {
            margin-bottom: 20px;
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
            <img src="<?= $schoolLogo ?>" alt="School Logo" class="school-logo">
            <!-- Updated to use config constants -->
            <div class="school-name"><?= SCHOOL_NAME ?></div>
            <div class="school-address"><?= SCHOOL_ADDRESS ?></div>
            <div class="school-contact">Tel: <?= SCHOOL_CONTACT_NO ?> | Email: <?= SCHOOL_EMAIL ?></div>
        </div>
        <h2>Cashflow Report</h2>
        <p>Period: <?= $startDisplay ?> to <?= $endDisplay ?></p>
        <p>Generated on: <?= date('F d, Y h:i A') ?></p>
    </div>
    
    <div class="report-summary">
        <h4>Summary</h4>
        <table>
            <tr>
                <td>Beginning Balance:</td>
                <td class="text-right">₱<?= number_format($beginningBalance, 2) ?></td>
            </tr>
            <tr>
                <td>Total Inflows:</td>
                <td class="text-right">₱<?= number_format($totalInflow, 2) ?></td>
            </tr>
            <tr>
                <td>Total Outflows:</td>
                <td class="text-right">₱<?= number_format($totalOutflow, 2) ?></td>
            </tr>
            <tr>
                <td>Net Cashflow:</td>
                <td class="text-right <?= $netCashflow > 0 ? 'cashflow-positive' : ($netCashflow < 0 ? 'cashflow-negative' : 'cashflow-zero') ?>">
                    ₱<?= number_format($netCashflow, 2) ?>
                </td>
            </tr>
            <tr>
                <td>Ending Balance:</td>
                <td class="text-right <?= $endingBalance > 0 ? 'cashflow-positive' : ($endingBalance < 0 ? 'cashflow-negative' : 'cashflow-zero') ?>">
                    ₱<?= number_format($endingBalance, 2) ?>
                </td>
            </tr>
            <tr>
                <td>Total Transactions:</td>
                <td class="text-right"><?= array_sum(array_column($weeklyBreakdown, 'inflowCount')) + array_sum(array_column($weeklyBreakdown, 'outflowCount')) ?></td>
            </tr>
            <tr>
                <td>Inflow Transactions:</td>
                <td class="text-right"><?= array_sum(array_column($weeklyBreakdown, 'inflowCount')) ?></td>
            </tr>
            <tr>
                <td>Outflow Transactions:</td>
                <td class="text-right"><?= array_sum(array_column($weeklyBreakdown, 'outflowCount')) ?></td>
            </tr>
        </table>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <h4>Inflows by Type</h4>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th class="text-right">Gross Amount</th>
                        <th class="text-right">Discounts</th>
                        <th class="text-right">Net Amount</th>
                        <th class="text-right">Transactions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inflowByType as $type => $data): ?>
                    <tr>
                        <td><?= $type ?></td>
                        <td class="text-right">₱<?= number_format($data['gross_total'], 2) ?></td>
                        <td class="text-right discount-amount"><?= $data['discount_total'] > 0 ? '-₱' . number_format($data['discount_total'], 2) : '₱0.00' ?></td>
                        <td class="text-right">₱<?= number_format($data['net_total'], 2) ?></td>
                        <td class="text-right"><?= $data['count'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total</th>
                        <th class="text-right">₱<?= number_format($totalInflow + $totalDiscounts, 2) ?></th>
                        <th class="text-right discount-amount">-₱<?= number_format($totalDiscounts, 2) ?></th>
                        <th class="text-right">₱<?= number_format($totalInflow, 2) ?></th>
                        <th class="text-right"><?= array_sum(array_column($inflowByType, 'count')) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="col-md-6">
            <h4>Total Collection per Default Fees</h4>
            <table>
                <thead>
                    <tr>
                        <th>Fee Type / Name</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($defaultFeeCollections)): ?>
                    <tr>
                        <td colspan="3" class="text-center">No fee collections in this period</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($defaultFeeCollections as $feeType => $fees): ?>
                            <!-- Fee Type Header -->
                            <tr class="fee-type-header">
                                <td><?= ucfirst($feeType) ?> Fees</td>
                                <td class="text-right">₱<?= number_format(array_sum(array_column($fees, 'amount')), 2) ?></td>
                                <td class="text-right"><?= number_format((array_sum(array_column($fees, 'amount')) / $totalInflow) * 100, 1) ?>%</td>
                            </tr>
                            
                            <!-- Individual Fees -->
                            <?php foreach ($fees as $fee): ?>
                            <tr>
                                <td class="fee-name-cell"><?= $fee['fee_name'] ?></td>
                                <td class="text-right">₱<?= number_format($fee['amount'], 2) ?></td>
                                <td class="text-right"><?= number_format(($fee['amount'] / $totalInflow) * 100, 1) ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="fee-type-total">
                        <th>Total</th>
                        <th class="text-right">₱<?= number_format(array_sum(array_map(function($fees) { return array_sum(array_column($fees, 'amount')); }, $defaultFeeCollections)), 2) ?></th>
                        <th class="text-right">100%</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <h4>Outflows by Category</h4>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Transactions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($outflowByCategory as $category => $data): ?>
                    <tr>
                        <td><?= $category ?></td>
                        <td class="text-right">₱<?= number_format($data['total'], 2) ?></td>
                        <td class="text-right"><?= $data['count'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total</th>
                        <th class="text-right">₱<?= number_format($totalOutflow, 2) ?></th>
                        <th class="text-right"><?= array_sum(array_column($outflowByCategory, 'count')) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <h4>Weekly Cashflow Breakdown</h4>
    <table>
        <thead>
            <tr>
                <th>Week</th>
                <th>Period</th>
                <th class="text-right">Beginning Balance</th>
                <th class="text-right">Inflows</th>
                <th class="text-right">Outflows</th>
                <th class="text-right">Net Cashflow</th>
                <th class="text-right">Ending Balance</th>
                <th class="text-right">Inflow Count</th>
                <th class="text-right">Outflow Count</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($weeklyBreakdown as $week): ?>
            <tr>
                <td><?= $week['week'] ?></td>
                <td><?= date('M d', strtotime($week['start'])) ?> - <?= date('M d, Y', strtotime($week['end'])) ?></td>
                <td class="text-right">₱<?= number_format($week['beginning_balance'], 2) ?></td>
                <td class="text-right">₱<?= number_format($week['inflow'], 2) ?></td>
                <td class="text-right">₱<?= number_format($week['outflow'], 2) ?></td>
                <td class="text-right <?= $week['net'] > 0 ? 'cashflow-positive' : ($week['net'] < 0 ? 'cashflow-negative' : 'cashflow-zero') ?>">
                    ₱<?= number_format($week['net'], 2) ?>
                </td>
                <td class="text-right <?= $week['ending_balance'] > 0 ? 'cashflow-positive' : ($week['ending_balance'] < 0 ? 'cashflow-negative' : 'cashflow-zero') ?>">
                    ₱<?= number_format($week['ending_balance'], 2) ?>
                </td>
                <td class="text-right"><?= $week['inflowCount'] ?></td>
                <td class="text-right"><?= $week['outflowCount'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total</th>
                <th class="text-right">₱<?= number_format($beginningBalance, 2) ?></th>
                <th class="text-right">₱<?= number_format(array_sum(array_column($weeklyBreakdown, 'inflow')), 2) ?></th>
                <th class="text-right">₱<?= number_format(array_sum(array_column($weeklyBreakdown, 'outflow')), 2) ?></th>
                <th class="text-right <?= $netCashflow > 0 ? 'cashflow-positive' : ($netCashflow < 0 ? 'cashflow-negative' : 'cashflow-zero') ?>">
                    ₱<?= number_format($netCashflow, 2) ?>
                </th>
                <th class="text-right <?= $endingBalance > 0 ? 'cashflow-positive' : ($endingBalance < 0 ? 'cashflow-negative' : 'cashflow-zero') ?>">
                    ₱<?= number_format($endingBalance, 2) ?>
                </th>
                <th class="text-right"><?= array_sum(array_column($weeklyBreakdown, 'inflowCount')) ?></th>
                <th class="text-right"><?= array_sum(array_column($weeklyBreakdown, 'outflowCount')) ?></th>
            </tr>
        </tfoot>
    </table>
    
    <div class="financial-status <?= $statusClass ?>">
        Financial Status: <?= $financialStatus ?>
    </div>
    
    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Print Report</button>
    </div>
</body>
</html>