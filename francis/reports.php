<?php
require_once 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get system settings
function getSystemSetting($conn, $key, $default = '') {
    $query = "SELECT setting_value FROM system_settings WHERE setting_key = '$key'";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['setting_value'];
    }
    return $default;
}

 $system_settings = [
    'company_name' => getSystemSetting($conn, 'company_name', 'Accounting System'),
    'company_address' => getSystemSetting($conn, 'company_address', '123 Main Street, Manila, Philippines'),
    'company_email' => getSystemSetting($conn, 'company_email', 'info@accountingsystem.com'),
    'company_phone' => getSystemSetting($conn, 'company_phone', '+63 (2) 123-4567'),
    'currency' => getSystemSetting($conn, 'currency', 'PHP'),
    'currency_symbol' => getSystemSetting($conn, 'currency_symbol', '₱')
];

// Get report parameters
 $report_type = isset($_GET['report_type']) ? $_GET['report_type'] : '';
 $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
 $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
 $account_id = isset($_GET['account_id']) ? $_GET['account_id'] : '';

// Set the page title based on report type
 $report_titles = [
    'journal-entries' => 'General Journal',
    'ledger' => 'General Ledger',
    'trial-balance' => 'Trial Balance',
    'income-statement' => 'Statement of Comprehensive Income',
    'balance-sheet' => 'Statement of Financial Position',
    'cashflow' => 'Statement of Cash Flows',
    'equity' => 'Statement of Changes in Equity'
];

 $page_title = isset($report_titles[$report_type]) ? $report_titles[$report_type] : 'Accounting Report';

// Get accounts data for reports
 $accounts_data = [];
 $accounts_query = "SELECT * FROM chart_of_accounts WHERE is_active = TRUE ORDER BY account_code";
 $accounts_result = $conn->query($accounts_query);
if ($accounts_result) {
    while ($row = $accounts_result->fetch_assoc()) {
        $accounts_data[] = $row;
    }
}

// Start output buffering
ob_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?> - <?php echo $system_settings['company_name']; ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    
    <!-- Formal Document Style -->
    <style>
        :root {
            --brand-primary: #1b5e20; /* Deep Forest Green */
            --brand-light: #e8f5e9;
            --text-dark: #2c3e50;
            --border-color: #000;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12pt; /* Standard print font size */
            line-height: 1.4;
            background-color: #f0f0f0; /* Light gray background for screen viewing */
            color: #000;
            margin: 0;
            padding: 20px;
        }

        /* The Paper Sheet */
        .document-sheet {
            background-color: #fff;
            width: 210mm; /* A4 Width */
            min-height: 297mm; /* A4 Height */
            margin: 0 auto;
            padding: 20mm; /* Standard margins */
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
        }

        /* Report Header / Letterhead */
        .report-header {
            display: flex;
            align-items: center;
            border-bottom: 3px double var(--brand-primary);
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .logo-container {
            flex: 0 0 auto;
            margin-right: 25px;
        }

        .logo-container img {
            max-height: 80px;
            width: auto;
        }

        .header-content {
            flex: 1;
        }

        .company-name {
            font-family: 'Times New Roman', serif;
            font-size: 24pt;
            font-weight: bold;
            color: var(--brand-primary);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .company-address {
            font-size: 10pt;
            color: #555;
            margin-bottom: 5px;
        }

        .report-meta {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .report-title {
            font-family: 'Times New Roman', serif;
            font-size: 18pt;
            font-weight: bold;
            text-align: center;
            color: #000;
            text-transform: uppercase;
            width: 100%;
            margin: 10px 0 20px 0;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }

        .date-range {
            font-size: 11pt;
            text-align: right;
            font-weight: 600;
        }

        /* Formal Tables */
        .table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 8px 10px;
            vertical-align: middle;
            border: 1px solid var(--border-color);
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid var(--border-color);
            background-color: var(--brand-light);
            color: var(--brand-primary);
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        
        /* Remove striped rows for formal look */
        .table tbody tr:nth-of-type(odd) {
            background-color: transparent;
        }

        /* Accounting specific alignment */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .currency {
            text-align: right;
            font-family: 'Courier New', monospace; /* Monospace for numbers aligns better */
            font-weight: 500;
        }

        .report-total td {
            background-color: var(--brand-light) !important;
            color: var(--brand-primary);
            font-weight: bold;
            border-top: 2px solid var(--border-color);
        }

        .account-group {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .account-group h6 {
            font-family: 'Times New Roman', serif;
            font-size: 14pt;
            font-weight: bold;
            color: var(--brand-primary);
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 10px;
            margin-top: 10px;
        }

        .indent-1 { padding-left: 25px; }
        .indent-2 { padding-left: 50px; }

        /* Action Buttons (No Print) */
        .action-bar {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #fff;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        .btn-formal {
            background-color: var(--brand-primary);
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 5px;
            transition: background 0.2s;
        }

        .btn-formal:hover {
            background-color: #144a17;
        }

        .btn-secondary-formal {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 5px;
        }
        
        .btn-secondary-formal:hover {
            background-color: #5a6268;
        }

        /* Print Optimization */
        @media print {
            body {
                background-color: #fff;
                padding: 0;
                margin: 0;
            }
            .document-sheet {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
                min-height: auto;
            }
            .action-bar {
                display: none !important;
            }
            .table th, .table td {
                border-color: #000 !important; /* Force black borders */
            }
            a { text-decoration: none; color: #000; }
            
            /* Ensure table headers repeat */
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <!-- Floating Action Bar (Hidden on Print) -->
    <div class="action-bar no-print">
        <button class="btn-formal" onclick="window.print()"><i class="fas fa-print"></i> Print Document</button>
        <button class="btn-secondary-formal" onclick="window.close()">Close</button>
    </div>

    <!-- Document Sheet -->
    <div class="document-sheet">
        
        <!-- Formal Header -->
        <div class="report-header">
            <div class="logo-container">
                <!-- Ensure path is correct -->
                <img src="uploads/diocese.png" alt="Company Logo">
            </div>
            <div class="header-content">
                <div class="company-name"><?php echo $system_settings['company_name']; ?></div>
                <div class="company-address">
                    <?php echo $system_settings['company_address']; ?><br>
                    <?php echo $system_settings['company_email']; ?> | <?php echo $system_settings['company_phone']; ?>
                </div>
                <div class="report-meta">
                    <div>
                        <!-- Additional metadata could go here like User Generated By -->
                    </div>
                    <div class="date-range">
                        Date Range: <br>
                        <strong><?php echo date('F d, Y', strtotime($start_date)); ?> - <?php echo date('F d, Y', strtotime($end_date)); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Title -->
        <div class="report-title">
            <?php echo $page_title; ?>
        </div>

        <?php
        // Generate the report based on the report type
        switch ($report_type) {
            case 'journal-entries':
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 12%;">Date</th>
                                <th style="width: 15%;">Reference</th>
                                <th style="width: 30%;">Description / Account</th>
                                <th style="width: 18%;">Account Name</th>
                                <th class="text-right" style="width: 12.5%;">Debit</th>
                                <th class="text-right" style="width: 12.5%;">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $journal_query = "SELECT t.transaction_date, t.reference_no, t.description, 
                                                  coa.account_name, coa.account_category, te.entry_type, te.amount
                                                  FROM transaction_entries te
                                                  JOIN transactions t ON te.transaction_id = t.id
                                                  JOIN chart_of_accounts coa ON te.account_id = coa.id
                                                  WHERE t.transaction_date BETWEEN '$start_date' AND '$end_date'
                                                  ORDER BY t.transaction_date, t.id";
                            $journal_result = $conn->query($journal_query);
                            
                            $total_debit = 0;
                            $total_credit = 0;
                            
                            if ($journal_result) {
                                while ($entry = $journal_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . date('M d, Y', strtotime($entry['transaction_date'])) . "</td>";
                                    echo "<td>" . htmlspecialchars($entry['reference_no']) . "</td>";
                                    echo "<td>" . htmlspecialchars($entry['description']) . "</td>";
                                    echo "<td><small>" . htmlspecialchars($entry['account_category']) . "</small><br>" . htmlspecialchars($entry['account_name']) . "</td>";
                                    
                                    if ($entry['entry_type'] == 'debit') {
                                        echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($entry['amount'], 2) . "</td>";
                                        echo "<td class='currency'></td>";
                                        $total_debit += $entry['amount'];
                                    } else {
                                        echo "<td class='currency'></td>";
                                        echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($entry['amount'], 2) . "</td>";
                                        $total_credit += $entry['amount'];
                                    }
                                    
                                    echo "</tr>";
                                }
                            }
                            ?>
                            <tr class="report-total">
                                <td colspan="4" class="text-right">TOTALS</td>
                                <td class="currency"><?php echo $system_settings['currency_symbol'] . number_format($total_debit, 2); ?></td>
                                <td class="currency"><?php echo $system_settings['currency_symbol'] . number_format($total_credit, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php
                break;
                
            case 'ledger':
                $account_filter = '';
                if (!empty($account_id)) {
                    $account_filter = " AND coa.id = $account_id";
                }
                
                $ledger_accounts_query = "SELECT coa.id, coa.account_code, coa.account_name, coa.account_category, coa.normal_balance
                                           FROM chart_of_accounts coa
                                           WHERE coa.is_active = 1 $account_filter
                                           ORDER BY coa.account_code";
                $ledger_accounts_result = $conn->query($ledger_accounts_query);
                
                if ($ledger_accounts_result) {
                    while ($account = $ledger_accounts_result->fetch_assoc()) {
                        echo "<div class='account-group'>";
                        echo "<h6>" . $account['account_code'] . " - " . $account['account_name'] . "</h6>";
                        echo "<small class='text-muted mb-2 d-block'>Category: " . $account['account_category'] . " | Normal Balance: " . ucfirst($account['normal_balance']) . "</small>";
                        
                        // Get opening balance
                        $opening_balance = 0;
                        $opening_query = "SELECT SUM(CASE WHEN te.entry_type = 'debit' THEN te.amount ELSE -te.amount END) as balance
                                         FROM transaction_entries te
                                         JOIN transactions t ON te.transaction_id = t.id
                                         WHERE te.account_id = " . $account['id'] . "
                                         AND t.transaction_date < '$start_date'";
                        $opening_result = $conn->query($opening_query);
                        if ($opening_result) {
                            $opening_data = $opening_result->fetch_assoc();
                            $opening_balance = $opening_data['balance'] ?? 0;
                        }
                        
                        echo "<div class='table-responsive'>";
                        echo "<table class='table'>";
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th style='width: 15%;'>Date</th>";
                        echo "<th style='width: 15%;'>Reference</th>";
                        echo "<th style='width: 35%;'>Description</th>";
                        echo "<th class='text-right' style='width: 10%;'>Debit</th>";
                        echo "<th class='text-right' style='width: 10%;'>Credit</th>";
                        echo "<th class='text-right' style='width: 15%;'>Balance</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";
                        
                        // Opening balance row
                        echo "<tr>";
                        echo "<td colspan='2'><strong>As of " . date('F d, Y', strtotime($start_date . ' -1 day')) . "</strong></td>";
                        echo "<td><strong>Opening Balance</strong></td>";
                        if ($opening_balance >= 0) {
                             echo "<td class='currency'>-</td>";
                             echo "<td class='currency'>-</td>";
                        } else {
                             echo "<td class='currency'>-</td>";
                             echo "<td class='currency'>-</td>";
                        }
                        echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($opening_balance, 2) . "</strong></td>";
                        echo "</tr>";
                        
                        // Get transactions
                        $running_balance = $opening_balance;
                        $transactions_query = "SELECT t.transaction_date, t.reference_no, t.description, te.entry_type, te.amount
                                               FROM transaction_entries te
                                               JOIN transactions t ON te.transaction_id = t.id
                                               WHERE te.account_id = " . $account['id'] . "
                                               AND t.transaction_date BETWEEN '$start_date' AND '$end_date'
                                               ORDER BY t.transaction_date, t.id";
                        $transactions_result = $conn->query($transactions_query);
                        
                        if ($transactions_result) {
                            while ($transaction = $transactions_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . date('M d, Y', strtotime($transaction['transaction_date'])) . "</td>";
                                echo "<td>" . htmlspecialchars($transaction['reference_no']) . "</td>";
                                echo "<td>" . htmlspecialchars($transaction['description']) . "</td>";
                                
                                if ($transaction['entry_type'] == 'debit') {
                                    echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($transaction['amount'], 2) . "</td>";
                                    echo "<td class='currency'></td>";
                                    $running_balance += $transaction['amount'];
                                } else {
                                    echo "<td class='currency'></td>";
                                    echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($transaction['amount'], 2) . "</td>";
                                    $running_balance -= $transaction['amount'];
                                }
                                
                                echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($running_balance, 2) . "</td>";
                                echo "</tr>";
                            }
                        }
                        
                        // Closing balance
                        echo "<tr class='report-total'>";
                        echo "<td colspan='3'><strong>Closing Balance</strong></td>";
                        echo "<td class='currency'></td>";
                        echo "<td class='currency'></td>";
                        echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($running_balance, 2) . "</strong></td>";
                        echo "</tr>";
                        
                        echo "</tbody>";
                        echo "</table>";
                        echo "</div>";
                        echo "</div>";
                    }
                }
                break;
                
            case 'trial-balance':
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Account Code</th>
                                <th style="width: 45%;">Account Name</th>
                                <th class="text-right" style="width: 20%;">Debit</th>
                                <th class="text-right" style="width: 20%;">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $trial_balance_query = "SELECT coa.account_code, coa.account_name, coa.normal_balance,
                                                       SUM(CASE WHEN te.entry_type = 'debit' THEN te.amount ELSE 0 END) as debit_total,
                                                       SUM(CASE WHEN te.entry_type = 'credit' THEN te.amount ELSE 0 END) as credit_total
                                                       FROM chart_of_accounts coa
                                                       LEFT JOIN transaction_entries te ON coa.id = te.account_id
                                                       LEFT JOIN transactions t ON te.transaction_id = t.id
                                                       WHERE coa.is_active = 1
                                                       AND (t.transaction_date BETWEEN '$start_date' AND '$end_date' OR t.transaction_date IS NULL)
                                                       GROUP BY coa.id
                                                       ORDER BY coa.account_code";
                            $trial_balance_result = $conn->query($trial_balance_query);
                            
                            $total_debit = 0;
                            $total_credit = 0;
                            
                            if ($trial_balance_result) {
                                while ($account = $trial_balance_result->fetch_assoc()) {
                                    $debit = 0;
                                    $credit = 0;
                                    
                                    if ($account['normal_balance'] == 'debit') {
                                        $debit = $account['debit_total'] - $account['credit_total'];
                                        if ($debit < 0) {
                                            $credit = abs($debit);
                                            $debit = 0;
                                        }
                                    } else {
                                        $credit = $account['credit_total'] - $account['debit_total'];
                                        if ($credit < 0) {
                                            $debit = abs($credit);
                                            $credit = 0;
                                        }
                                    }
                                    
                                    $total_debit += $debit;
                                    $total_credit += $credit;
                                    
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($account['account_code']) . "</td>";
                                    echo "<td>" . htmlspecialchars($account['account_name']) . "</td>";
                                    echo "<td class='currency'>" . ($debit > 0 ? $system_settings['currency_symbol'] . number_format($debit, 2) : '') . "</td>";
                                    echo "<td class='currency'>" . ($credit > 0 ? $system_settings['currency_symbol'] . number_format($credit, 2) : '') . "</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                            <tr class="report-total">
                                <td colspan="2" class="text-right"><strong>TOTALS</strong></td>
                                <td class="currency"><strong><?php echo $system_settings['currency_symbol'] . number_format($total_debit, 2); ?></strong></td>
                                <td class="currency"><strong><?php echo $system_settings['currency_symbol'] . number_format($total_credit, 2); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php
                break;
                
            case 'income-statement':
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 70%;">Account</th>
                                <th class="text-right" style="width: 30%;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Revenue Section
                            echo "<tr><td colspan='2' style='background-color: #f9f9f9; font-weight:bold; color: var(--brand-primary);'>REVENUE</td></tr>";
                            
                            $revenue_accounts_query = "SELECT coa.account_name, coa.account_category, SUM(te.amount) as total 
                                                       FROM transaction_entries te 
                                                       JOIN transactions t ON te.transaction_id = t.id
                                                       JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                       WHERE coa.account_type = 'revenue' AND te.entry_type = 'credit'
                                                       AND t.transaction_date BETWEEN '$start_date' AND '$end_date'
                                                       GROUP BY coa.id";
                            $revenue_accounts_result = $conn->query($revenue_accounts_query);
                            
                            $total_revenue = 0;
                            if ($revenue_accounts_result) {
                                while ($account = $revenue_accounts_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td class='indent-1'>" . htmlspecialchars($account['account_category']) . " - " . htmlspecialchars($account['account_name']) . "</td>";
                                    echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($account['total'], 2) . "</td>";
                                    echo "</tr>";
                                    $total_revenue += $account['total'];
                                }
                            }
                            
                            echo "<tr class='report-total'><td class='text-right'>Total Revenue</td><td class='currency'>" . $system_settings['currency_symbol'] . number_format($total_revenue, 2) . "</td></tr>";
                            echo "<tr><td colspan='2'>&nbsp;</td></tr>"; // Spacer

                            // Expenses Section
                            echo "<tr><td colspan='2' style='background-color: #f9f9f9; font-weight:bold; color: var(--brand-primary);'>EXPENSES</td></tr>";

                            $expense_accounts_query = "SELECT coa.account_name, coa.account_category, SUM(te.amount) as total 
                                                        FROM transaction_entries te 
                                                        JOIN transactions t ON te.transaction_id = t.id
                                                        JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                        WHERE coa.account_type = 'expense' AND te.entry_type = 'debit'
                                                        AND t.transaction_date BETWEEN '$start_date' AND '$end_date'
                                                        GROUP BY coa.id";
                            $expense_accounts_result = $conn->query($expense_accounts_query);
                            
                            $total_expenses = 0;
                            if ($expense_accounts_result) {
                                while ($account = $expense_accounts_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td class='indent-1'>" . htmlspecialchars($account['account_category']) . " - " . htmlspecialchars($account['account_name']) . "</td>";
                                    echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($account['total'], 2) . "</td>";
                                    echo "</tr>";
                                    $total_expenses += $account['total'];
                                }
                            }
                            
                            echo "<tr class='report-total'><td class='text-right'>Total Expenses</td><td class='currency'>" . $system_settings['currency_symbol'] . number_format($total_expenses, 2) . "</td></tr>";
                            
                            $net_income = $total_revenue - $total_expenses;
                            
                            echo "<tr><td colspan='2'>&nbsp;</td></tr>"; // Spacer
                            echo "<tr class='report-total' style='font-size: 14pt;'>";
                            echo "<td class='text-right'>NET INCOME / (LOSS)</td>";
                            echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($net_income, 2) . "</td>";
                            echo "</tr>";
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php
                break;
                
            case 'balance-sheet':
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 70%;">Account</th>
                                <th class="text-right" style="width: 30%;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Assets
                            echo "<tr><td colspan='2' style='background-color: #f9f9f9; font-weight:bold; color: var(--brand-primary);'>ASSETS</td></tr>";
                            
                            $asset_accounts_query = "SELECT coa.account_name, coa.account_category,
                                                      SUM(CASE WHEN te.entry_type = 'debit' THEN te.amount ELSE -te.amount END) as total 
                                                      FROM transaction_entries te 
                                                      JOIN transactions t ON te.transaction_id = t.id
                                                      JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                      WHERE coa.account_type = 'asset'
                                                      AND t.transaction_date BETWEEN '$start_date' AND '$end_date'
                                                      GROUP BY coa.id";
                            $asset_accounts_result = $conn->query($asset_accounts_query);
                            
                            $total_assets = 0;
                            if ($asset_accounts_result) {
                                while ($account = $asset_accounts_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td class='indent-1'>" . htmlspecialchars($account['account_category']) . " - " . htmlspecialchars($account['account_name']) . "</td>";
                                    echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($account['total'], 2) . "</td>";
                                    echo "</tr>";
                                    $total_assets += $account['total'];
                                }
                            }
                            echo "<tr class='report-total'><td class='text-right'>Total Assets</td><td class='currency'>" . $system_settings['currency_symbol'] . number_format($total_assets, 2) . "</td></tr>";
                            
                            echo "<tr><td colspan='2'>&nbsp;</td></tr>";

                            // Liabilities
                            echo "<tr><td colspan='2' style='background-color: #f9f9f9; font-weight:bold; color: var(--brand-primary);'>LIABILITIES</td></tr>";
                            
                            $liability_accounts_query = "SELECT coa.account_name, coa.account_category,
                                                          SUM(CASE WHEN te.entry_type = 'credit' THEN te.amount ELSE -te.amount END) as total 
                                                          FROM transaction_entries te 
                                                          JOIN transactions t ON te.transaction_id = t.id
                                                          JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                          WHERE coa.account_type = 'liability'
                                                          AND t.transaction_date BETWEEN '$start_date' AND '$end_date'
                                                          GROUP BY coa.id";
                            $liability_accounts_result = $conn->query($liability_accounts_query);
                            
                            $total_liabilities = 0;
                            if ($liability_accounts_result) {
                                while ($account = $liability_accounts_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td class='indent-1'>" . htmlspecialchars($account['account_category']) . " - " . htmlspecialchars($account['account_name']) . "</td>";
                                    echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($account['total'], 2) . "</td>";
                                    echo "</tr>";
                                    $total_liabilities += $account['total'];
                                }
                            }
                            echo "<tr class='report-total'><td class='text-right'>Total Liabilities</td><td class='currency'>" . $system_settings['currency_symbol'] . number_format($total_liabilities, 2) . "</td></tr>";

                            echo "<tr><td colspan='2'>&nbsp;</td></tr>";

                            // Equity
                            echo "<tr><td colspan='2' style='background-color: #f9f9f9; font-weight:bold; color: var(--brand-primary);'>EQUITY</td></tr>";
                            
                            $equity_accounts_query = "SELECT coa.account_name, coa.account_category,
                                                         SUM(CASE WHEN te.entry_type = 'credit' THEN te.amount ELSE -te.amount END) as total 
                                                         FROM transaction_entries te 
                                                         JOIN transactions t ON te.transaction_id = t.id
                                                         JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                         WHERE coa.account_type = 'equity'
                                                         AND t.transaction_date BETWEEN '$start_date' AND '$end_date'
                                                         GROUP BY coa.id";
                            $equity_accounts_result = $conn->query($equity_accounts_query);
                            
                            $total_equity = 0;
                            if ($equity_accounts_result) {
                                while ($account = $equity_accounts_result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td class='indent-1'>" . htmlspecialchars($account['account_category']) . " - " . htmlspecialchars($account['account_name']) . "</td>";
                                    echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($account['total'], 2) . "</td>";
                                    echo "</tr>";
                                    $total_equity += $account['total'];
                                }
                            }
                            
                            // Net Income added to Equity
                            $net_income_query = "SELECT 
                                                  (SELECT SUM(te.amount) FROM transaction_entries te 
                                                   JOIN transactions t ON te.transaction_id = t.id
                                                   JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                   WHERE coa.account_type = 'revenue' AND te.entry_type = 'credit'
                                                   AND t.transaction_date BETWEEN '$start_date' AND '$end_date') -
                                                  (SELECT SUM(te.amount) FROM transaction_entries te 
                                                   JOIN transactions t ON te.transaction_id = t.id
                                                   JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                   WHERE coa.account_type = 'expense' AND te.entry_type = 'debit'
                                                   AND t.transaction_date BETWEEN '$start_date' AND '$end_date') as net_income";
                            $net_income_result = $conn->query($net_income_query);
                            $net_income = 0;
                            if ($net_income_result) {
                                $net_income = $net_income_result->fetch_assoc()['net_income'] ?? 0;
                            }
                            
                            echo "<tr>";
                            echo "<td class='indent-1'><strong>Current Net Income</strong></td>";
                            echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($net_income, 2) . "</td>";
                            echo "</tr>";
                            
                            $total_equity += $net_income;
                            echo "<tr class='report-total'><td class='text-right'>Total Equity</td><td class='currency'>" . $system_settings['currency_symbol'] . number_format($total_equity, 2) . "</td></tr>";
                            
                            $total_liabilities_equity = $total_liabilities + $total_equity;
                            
                            echo "<tr><td colspan='2'>&nbsp;</td></tr>";
                            echo "<tr class='report-total' style='font-size: 14pt;'>";
                            echo "<td class='text-right'>TOTAL LIABILITIES & EQUITY</td>";
                            echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($total_liabilities_equity, 2) . "</td>";
                            echo "</tr>";
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php
                break;
                
            case 'cashflow':
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 70%;">Category</th>
                                <th class="text-right" style="width: 30%;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Operating Activities
                            echo "<tr><td colspan='2' style='background-color: #f9f9f9; font-weight:bold; color: var(--brand-primary);'>CASH FLOWS FROM OPERATING ACTIVITIES</td></tr>";
                            
                            $operating_revenue_query = "SELECT SUM(te.amount) as total 
                                                         FROM transaction_entries te 
                                                         JOIN transactions t ON te.transaction_id = t.id
                                                         JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                         WHERE coa.account_type = 'revenue' AND te.entry_type = 'credit'
                                                         AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
                            $operating_revenue_result = $conn->query($operating_revenue_query);
                            $operating_revenue = $operating_revenue_result ? $operating_revenue_result->fetch_assoc()['total'] ?? 0 : 0;
                            
                            $operating_expense_query = "SELECT SUM(te.amount) as total 
                                                        FROM transaction_entries te 
                                                        JOIN transactions t ON te.transaction_id = t.id
                                                        JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                        WHERE coa.account_type = 'expense' AND te.entry_type = 'debit'
                                                        AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
                            $operating_expense_result = $conn->query($operating_expense_query);
                            $operating_expense = $operating_expense_result ? $operating_expense_result->fetch_assoc()['total'] ?? 0 : 0;
                            
                            $net_operating = $operating_revenue - $operating_expense;
                            
                            echo "<tr><td class='indent-1'>Cash Received from Revenue</td><td class='currency'>" . $system_settings['currency_symbol'] . number_format($operating_revenue, 2) . "</td></tr>";
                            echo "<tr><td class='indent-1'>Cash Paid for Expenses</td><td class='currency'>(" . $system_settings['currency_symbol'] . number_format($operating_expense, 2) . ")</td></tr>";
                            echo "<tr class='report-total'><td class='text-right indent-1'>Net Cash from Operating Activities</td><td class='currency'>" . $system_settings['currency_symbol'] . number_format($net_operating, 2) . "</td></tr>";
                            
                            echo "<tr><td colspan='2'>&nbsp;</td></tr>";

                            // Investing Activities
                            echo "<tr><td colspan='2' style='background-color: #f9f9f9; font-weight:bold; color: var(--brand-primary);'>CASH FLOWS FROM INVESTING ACTIVITIES</td></tr>";
                            
                            $investing_query = "SELECT SUM(te.amount) as total 
                                                FROM transaction_entries te 
                                                JOIN transactions t ON te.transaction_id = t.id
                                                JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                WHERE coa.account_type = 'asset' AND te.entry_type = 'debit'
                                                AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
                            $investing_result = $conn->query($investing_query);
                            $investing_outflow = $investing_result ? $investing_result->fetch_assoc()['total'] ?? 0 : 0;
                            
                            echo "<tr><td class='indent-1'>Purchase of Assets</td><td class='currency'>(" . $system_settings['currency_symbol'] . number_format($investing_outflow, 2) . ")</td></tr>";
                            echo "<tr class='report-total'><td class='text-right indent-1'>Net Cash from Investing Activities</td><td class='currency'>(" . $system_settings['currency_symbol'] . number_format($investing_outflow, 2) . ")</td></tr>";
                            
                            echo "<tr><td colspan='2'>&nbsp;</td></tr>";

                            // Financing Activities
                            echo "<tr><td colspan='2' style='background-color: #f9f9f9; font-weight:bold; color: var(--brand-primary);'>CASH FLOWS FROM FINANCING ACTIVITIES</td></tr>";
                            
                            $financing_query = "SELECT SUM(te.amount) as total 
                                               FROM transaction_entries te 
                                               JOIN transactions t ON te.transaction_id = t.id
                                               JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                               WHERE coa.account_type = 'liability' AND te.entry_type = 'credit'
                                               AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
                            $financing_result = $conn->query($financing_query);
                            $financing_inflow = $financing_result ? $financing_result->fetch_assoc()['total'] ?? 0 : 0;
                            
                            echo "<tr><td class='indent-1'>Proceeds from Liabilities</td><td class='currency'>" . $system_settings['currency_symbol'] . number_format($financing_inflow, 2) . "</td></tr>";
                            echo "<tr class='report-total'><td class='text-right indent-1'>Net Cash from Financing Activities</td><td class='currency'>" . $system_settings['currency_symbol'] . number_format($financing_inflow, 2) . "</td></tr>";
                            
                            $net_cash_flow = $net_operating - $investing_outflow + $financing_inflow;
                            
                            echo "<tr><td colspan='2'>&nbsp;</td></tr>";
                            echo "<tr class='report-total' style='font-size: 14pt;'>";
                            echo "<td class='text-right'>NET INCREASE / (DECREASE) IN CASH</td>";
                            echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($net_cash_flow, 2) . "</td>";
                            echo "</tr>";
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php
                break;
                
            case 'equity':
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 30%;">Equity Component</th>
                                <th class="text-right" style="width: 17.5%;">Beginning Balance</th>
                                <th class="text-right" style="width: 17.5%;">Additions</th>
                                <th class="text-right" style="width: 17.5%;">Deductions</th>
                                <th class="text-right" style="width: 17.5%;">Ending Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $equity_accounts_query = "SELECT coa.id, coa.account_name, coa.account_category
                                                       FROM chart_of_accounts coa
                                                       WHERE coa.account_type = 'equity' AND coa.is_active = 1
                                                       ORDER BY coa.account_code";
                            $equity_accounts_result = $conn->query($equity_accounts_query);
                            
                            $total_beginning = 0;
                            $total_additions = 0;
                            $total_deductions = 0;
                            $total_ending = 0;
                            
                            if ($equity_accounts_result) {
                                while ($account = $equity_accounts_result->fetch_assoc()) {
                                    $beginning_query = "SELECT SUM(CASE WHEN te.entry_type = 'credit' THEN te.amount ELSE -te.amount END) as balance
                                                       FROM transaction_entries te
                                                       JOIN transactions t ON te.transaction_id = t.id
                                                       WHERE te.account_id = " . $account['id'] . "
                                                       AND t.transaction_date < '$start_date'";
                                    $beginning_result = $conn->query($beginning_query);
                                    $beginning_balance = $beginning_result ? $beginning_result->fetch_assoc()['balance'] ?? 0 : 0;
                                    
                                    $additions_query = "SELECT SUM(te.amount) as total
                                                       FROM transaction_entries te
                                                       JOIN transactions t ON te.transaction_id = t.id
                                                       WHERE te.account_id = " . $account['id'] . "
                                                       AND te.entry_type = 'credit'
                                                       AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
                                    $additions_result = $conn->query($additions_query);
                                    $additions = $additions_result ? $additions_result->fetch_assoc()['total'] ?? 0 : 0;
                                    
                                    $deductions_query = "SELECT SUM(te.amount) as total
                                                        FROM transaction_entries te
                                                        JOIN transactions t ON te.transaction_id = t.id
                                                        WHERE te.account_id = " . $account['id'] . "
                                                        AND te.entry_type = 'debit'
                                                        AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
                                    $deductions_result = $conn->query($deductions_query);
                                    $deductions = $deductions_result ? $deductions_result->fetch_assoc()['total'] ?? 0 : 0;
                                    
                                    $ending_balance = $beginning_balance + $additions - $deductions;
                                    
                                    $total_beginning += $beginning_balance;
                                    $total_additions += $additions;
                                    $total_deductions += $deductions;
                                    $total_ending += $ending_balance;
                                    
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($account['account_category']) . " - " . htmlspecialchars($account['account_name']) . "</td>";
                                    echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($beginning_balance, 2) . "</td>";
                                    echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($additions, 2) . "</td>";
                                    echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($deductions, 2) . "</td>";
                                    echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($ending_balance, 2) . "</td>";
                                    echo "</tr>";
                                }
                            }
                            
                            $net_income_query = "SELECT 
                                                  (SELECT SUM(te.amount) FROM transaction_entries te 
                                                   JOIN transactions t ON te.transaction_id = t.id
                                                   JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                   WHERE coa.account_type = 'revenue' AND te.entry_type = 'credit'
                                                   AND t.transaction_date BETWEEN '$start_date' AND '$end_date') -
                                                  (SELECT SUM(te.amount) FROM transaction_entries te 
                                                   JOIN transactions t ON te.transaction_id = t.id
                                                   JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                   WHERE coa.account_type = 'expense' AND te.entry_type = 'debit'
                                                   AND t.transaction_date BETWEEN '$start_date' AND '$end_date') as net_income";
                            $net_income_result = $conn->query($net_income_query);
                            $net_income = $net_income_result ? $net_income_result->fetch_assoc()['net_income'] ?? 0 : 0;
                            
                            $total_additions += $net_income;
                            $total_ending += $net_income;
                            
                            echo "<tr>";
                            echo "<td><strong>Net Income</strong></td>";
                            echo "<td class='currency'></td>";
                            echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($net_income, 2) . "</strong></td>";
                            echo "<td class='currency'></td>";
                            echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($net_income, 2) . "</strong></td>";
                            echo "</tr>";
                            
                            echo "<tr class='report-total'>";
                            echo "<td><strong>TOTAL EQUITY</strong></td>";
                            echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($total_beginning, 2) . "</strong></td>";
                            echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($total_additions, 2) . "</strong></td>";
                            echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($total_deductions, 2) . "</strong></td>";
                            echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($total_ending, 2) . "</strong></td>";
                            echo "</tr>";
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php
                break;
                
            default:
                echo '<div class="alert alert-danger">Invalid report type specified.</div>';
        }
        ?>

        <!-- Document Footer (Print Only) -->
        <div style="margin-top: 50px; border-top: 1px solid #ccc; padding-top: 10px; font-size: 10pt; color: #777; text-align: center;" class="no-screen-break">
            <p>This is a computer-generated document. No signature is required.</p>
            <p>Generated on <?php echo date('F d, Y h:i:s A'); ?> by <?php echo $_SESSION['firstname'] . ' ' . $_SESSION['lastname']; ?></p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// End output buffering and send the output
ob_end_flush();
?>