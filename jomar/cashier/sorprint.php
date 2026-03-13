<?php
session_start();
require_once '../config.php';
// Require login and cashier role
requireRole('cashier');

// Get current date and week range
 $today = date('Y-m-d');
 $monday = date('Y-m-d', strtotime('monday this week'));
 $sunday = date('Y-m-d', strtotime('sunday this week'));

// Initialize variables
 $reportType = isset($_GET['type']) ? $_GET['type'] : 'daily';
 $startDate = $reportType == 'weekly' ? $monday : $today;
 $endDate = $reportType == 'weekly' ? $sunday : $today;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reportType = $_POST['report_type'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    
    if ($reportType == 'daily') {
        $startDate = $_POST['daily_date'];
        $endDate = $_POST['daily_date'];
    }
}

// Get student payments
 $payments = [];
 $query = "SELECT p.*, s.last_name, s.first_name, s.middle_name, s.id_number 
          FROM payments p
          JOIN students s ON p.student_id = s.id
          WHERE p.payment_date BETWEEN '$startDate' AND '$endDate'
          ORDER BY p.payment_date, p.or_number";
 $result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $payments[] = $row;
    }
}

// Calculate totals
 $totalAmount = 0;
 $totalDiscount = 0;
 $totalNetAmount = 0;

foreach ($payments as $payment) {
    $totalAmount += $payment['amount'];
    $totalDiscount += $payment['discount'];
    $totalNetAmount += ($payment['amount'] - $payment['discount']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Students Payment OR Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }
        .school-address, .school-contact {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 20px;
            margin: 20px 0 10px;
            text-align: center;
            text-decoration: underline;
        }
        .date-range {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .filter-form {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-success {
            background-color: #28a745;
            color: white;
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
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .no-print {
            margin-bottom: 20px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                background-color: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="../uploads/csr.png" alt="School Logo" class="logo">
            <!-- Updated to use config constants -->
            <div class="school-name"><?= SCHOOL_NAME ?></div>
            <div class="school-address"><?= SCHOOL_ADDRESS ?></div>
            <div class="school-contact">Tel: <?= SCHOOL_CONTACT_NO ?> | Email: <?= SCHOOL_EMAIL ?></div>
            <div>Students Payment OR Report</div>
        </div>

        <div class="no-print">
            <div class="filter-form">
                <form method="post">
                    <div class="form-group">
                        <label>Report Type:</label>
                        <select name="report_type" class="form-control" id="reportType" onchange="toggleDateFields()">
                            <option value="daily" <?= $reportType == 'daily' ? 'selected' : '' ?>>Daily Report</option>
                            <option value="weekly" <?= $reportType == 'weekly' ? 'selected' : '' ?>>Weekly Report</option>
                        </select>
                    </div>
                    
                    <div id="dailyFields" style="display: <?= $reportType == 'daily' ? 'block' : 'none' ?>;">
                        <div class="form-group">
                            <label>Select Date:</label>
                            <input type="date" name="daily_date" class="form-control" value="<?= $startDate ?>">
                        </div>
                    </div>
                    
                    <div id="weeklyFields" style="display: <?= $reportType == 'weekly' ? 'block' : 'none' ?>;">
                        <div class="form-group">
                            <label>Start Date:</label>
                            <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
                        </div>
                        <div class="form-group">
                            <label>End Date:</label>
                            <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                    <button type="button" class="btn btn-success" onclick="window.print()">Print Report</button>
                </form>
            </div>
        </div>

        <div class="report-title">
            <?= $reportType == 'daily' ? 'Daily' : 'Weekly' ?> Students Payment OR Report
        </div>
        
        <div class="date-range">
            Date Range: <?= date('F d, Y', strtotime($startDate)) ?> to <?= date('F d, Y', strtotime($endDate)) ?>
        </div>

        <?php if (empty($payments)): ?>
            <div style="text-align: center; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">
                No student payments found for the selected period.
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>OR Number</th>
                        <th>Date</th>
                        <th>Student Name</th>
                        <th>ID Number</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Discount</th>
                        <th class="text-right">Net Amount</th>
                        <th>Payment Method</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= $payment['or_number'] ?></td>
                        <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                        <td><?= $payment['last_name'] . ', ' . $payment['first_name'] . ' ' . $payment['middle_name'] ?></td>
                        <td><?= $payment['id_number'] ?></td>
                        <td class="text-right">₱<?= number_format($payment['amount'], 2) ?></td>
                        <td class="text-right">₱<?= number_format($payment['discount'], 2) ?></td>
                        <td class="text-right">₱<?= number_format($payment['amount'] - $payment['discount'], 2) ?></td>
                        <td><?= $payment['payment_method'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="total-row">
                        <td colspan="4">TOTAL</td>
                        <td class="text-right">₱<?= number_format($totalAmount, 2) ?></td>
                        <td class="text-right">₱<?= number_format($totalDiscount, 2) ?></td>
                        <td class="text-right">₱<?= number_format($totalNetAmount, 2) ?></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        function toggleDateFields() {
            const reportType = document.getElementById('reportType').value;
            const dailyFields = document.getElementById('dailyFields');
            const weeklyFields = document.getElementById('weeklyFields');
            
            if (reportType === 'daily') {
                dailyFields.style.display = 'block';
                weeklyFields.style.display = 'none';
            } else {
                dailyFields.style.display = 'none';
                weeklyFields.style.display = 'block';
            }
        }
    </script>
</body>
</html>