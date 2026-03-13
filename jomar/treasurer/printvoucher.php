<?php
session_start();
require_once '../config.php';

// Require login and treasurer role
requireRole('treasurer');

// Helper function to format amounts
function formatAmount($amount) {
    return '₱' . number_format((float)$amount, 2);
}

// Handle Data Normalization (from button click vs AJAX response)
 $voucherNo = $_POST['voucher_no'] ?? $_POST['voucher'] ?? 'N/A';
 $payee = $_POST['payee'] ?? '';
 $amount = $_POST['amount'] ?? 0;
 $date = $_POST['date'] ?? date('Y-m-d');
 $remarks = $_POST['remarks'] ?? '';
 $mode = $_POST['payment_mode'] ?? $_POST['mode'] ?? 'Cash';
 $category = $_POST['category'] ?? '';

// Handle custom category
// Data can come as 'custom' (from HTML5 data attr) or 'custom_category' (from JSON)
 $custom = $_POST['custom_category'] ?? $_POST['custom'] ?? '';
 $displayCategory = ($category === 'Others' && !empty($custom)) ? $custom : $category;

// Handle bank/check data
// Button click uses 'checkNo', AJAX uses 'check_number'
 $checkNumber = $_POST['check_number'] ?? $_POST['checkNo'] ?? '';
// Button click uses 'checkDate', AJAX uses 'check_date'
 $checkDate = $_POST['check_date'] ?? $_POST['checkDate'] ?? '';
 $bankName = $_POST['bank_name'] ?? $_POST['bank'] ?? '';

// NEW: Handle Bank Account Number
 $bankAccountNumber = $_POST['bank_account_number'] ?? $_POST['bank_account'] ?? $_POST['bankAccount'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Disbursement Voucher</title>
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
            text-transform: uppercase;
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
            width: 130px;
        }
        
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
            font-size: 14px;
        }
        
        .fee-breakdown th {
            font-weight: bold;
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
            color: #dc3545;
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
        <!-- Admin Copy -->
        <div class="receipt">
            <div class="receipt-copy">ADMIN COPY</div>
            
            <div class="receipt-header">
                <img src="../uploads/csr.png" alt="CSR Logo">
                <h1><?= defined('SCHOOL_NAME') ? SCHOOL_NAME : 'SCHOOL NAME' ?></h1>
                <div class="school-address"><?= defined('SCHOOL_ADDRESS') ? SCHOOL_ADDRESS : 'School Address' ?></div>
                <div class="school-contact">Tel: <?= defined('SCHOOL_CONTACT_NO') ? SCHOOL_CONTACT_NO : '' ?> | Email: <?= defined('SCHOOL_EMAIL') ? SCHOOL_EMAIL : '' ?></div>
                <h2>Treasurer's Office</h2>
            </div>
            
            <div class="receipt-title">
                Disbursement Voucher
            </div>
            
            <div class="receipt-details">
                <table>
                    <tr>
                        <td class="label">Voucher No:</td>
                        <td><?= htmlspecialchars($voucherNo) ?></td>
                        <td class="label">Date:</td>
                        <td><?= date('F d, Y', strtotime($date)) ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="receipt-info">
                <h3>Payee Information</h3>
                <table>
                    <tr>
                        <td class="label">Payee Name:</td>
                        <td><?= htmlspecialchars($payee) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Category:</td>
                        <td><?= htmlspecialchars($displayCategory) ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="fee-breakdown">
                <h3>Payment Details</h3>
                <table>
                    <tr>
                        <th>Mode</th>
                        <th>Details</th>
                        <th class="amount">Amount</th>
                    </tr>
                    <tr>
                        <td><?= htmlspecialchars($mode) ?></td>
                        <td>
                            <?php if($mode == 'Bank'): ?>
                                Bank: <?= htmlspecialchars($bankName) ?><br>
                                Account No: <?= htmlspecialchars($bankAccountNumber) ?>
                            <?php elseif($mode == 'Check'): ?>
                                Bank: <?= htmlspecialchars($bankName) ?><br>
                                Account No: <?= htmlspecialchars($bankAccountNumber) ?><br>
                                Check #: <?= htmlspecialchars($checkNumber) ?><br>
                                Check Date: <?= htmlspecialchars($checkDate) ?>
                            <?php else: ?>
                                Cash Payment
                            <?php endif; ?>
                        </td>
                        <td class="amount"><?= formatAmount($amount) ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: right; padding-right: 10px;"><strong>Total Disbursement:</strong></td>
                        <td class="amount"><strong><?= formatAmount($amount) ?></strong></td>
                    </tr>
                </table>
            </div>

            <div class="receipt-info" style="margin-top: 20px;">
                <h3>Particulars / Remarks</h3>
                <p style="font-size: 14px; margin: 0; min-height: 40px; border: 1px solid #eee; padding: 10px;">
                    <?= htmlspecialchars($remarks) ?>
                </p>
            </div>
            
            <div class="receipt-footer">
                <div class="receipt-signature">
                    <div>Prepared By:</div>
                    <div class="line">Treasurer</div>
                </div>
                
                <div class="receipt-signature">
                    <div>Received By:</div>
                    <div class="line"><?= htmlspecialchars($payee) ?></div>
                </div>
            </div>
        </div>
        
        <!-- Payee Copy -->
        <div class="receipt">
            <div class="receipt-copy">PAYEE COPY</div>
            
            <div class="receipt-header">
                <img src="../uploads/csr.png" alt="CSR Logo">
                <h1><?= defined('SCHOOL_NAME') ? SCHOOL_NAME : 'SCHOOL NAME' ?></h1>
                <div class="school-address"><?= defined('SCHOOL_ADDRESS') ? SCHOOL_ADDRESS : 'School Address' ?></div>
                <div class="school-contact">Tel: <?= defined('SCHOOL_CONTACT_NO') ? SCHOOL_CONTACT_NO : '' ?> | Email: <?= defined('SCHOOL_EMAIL') ? SCHOOL_EMAIL : '' ?></div>
                <h2>Treasurer's Office</h2>
            </div>
            
            <div class="receipt-title">
                Disbursement Voucher
            </div>
            
            <div class="receipt-details">
                <table>
                    <tr>
                        <td class="label">Voucher No:</td>
                        <td><?= htmlspecialchars($voucherNo) ?></td>
                        <td class="label">Date:</td>
                        <td><?= date('F d, Y', strtotime($date)) ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="receipt-info">
                <h3>Payee Information</h3>
                <table>
                    <tr>
                        <td class="label">Payee Name:</td>
                        <td><?= htmlspecialchars($payee) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Category:</td>
                        <td><?= htmlspecialchars($displayCategory) ?></td>
                    </tr>
                </table>
            </div>
            
            <div class="fee-breakdown">
                <h3>Payment Details</h3>
                <table>
                    <tr>
                        <th>Mode</th>
                        <th>Details</th>
                        <th class="amount">Amount</th>
                    </tr>
                    <tr>
                        <td><?= htmlspecialchars($mode) ?></td>
                        <td>
                            <?php if($mode == 'Bank'): ?>
                                Bank: <?= htmlspecialchars($bankName) ?><br>
                                Account No: <?= htmlspecialchars($bankAccountNumber) ?>
                            <?php elseif($mode == 'Check'): ?>
                                Bank: <?= htmlspecialchars($bankName) ?><br>
                                Account No: <?= htmlspecialchars($bankAccountNumber) ?><br>
                                Check #: <?= htmlspecialchars($checkNumber) ?><br>
                                Check Date: <?= htmlspecialchars($checkDate) ?>
                            <?php else: ?>
                                Cash Payment
                            <?php endif; ?>
                        </td>
                        <td class="amount"><?= formatAmount($amount) ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: right; padding-right: 10px;"><strong>Total Disbursement:</strong></td>
                        <td class="amount"><strong><?= formatAmount($amount) ?></strong></td>
                    </tr>
                </table>
            </div>

            <div class="receipt-info" style="margin-top: 20px;">
                <h3>Particulars / Remarks</h3>
                <p style="font-size: 14px; margin: 0; min-height: 40px; border: 1px solid #eee; padding: 10px;">
                    <?= htmlspecialchars($remarks) ?>
                </p>
            </div>
            
            <div class="receipt-footer">
                <div class="receipt-signature">
                    <div>Prepared By:</div>
                    <div class="line">Treasurer</div>
                </div>
                
                <div class="receipt-signature">
                    <div>Received By:</div>
                    <div class="line"><?= htmlspecialchars($payee) ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="receipt-actions">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Print Voucher
        </button>
        <a href="treasurer.php#disbursements-area" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Disbursements
        </a>
    </div>
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>