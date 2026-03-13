<?php
session_start();
require_once '../config.php';
// Require login and cashier role
requireRole('cashier');

// Check if payment ID is provided
if (!isset($_GET['payment_id'])) {
    $_SESSION['error'] = "Payment ID is required";
    header("Location: cashier.php#customer_payments");
    exit();
}

 $paymentId = mysqli_real_escape_string($conn, $_GET['payment_id']);

// Get payment details
 $query = "SELECT * FROM customer_payments WHERE id = $paymentId";
 $result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = "Payment not found";
    header("Location: cashier.php#customer_payments");
    exit();
}

 $payment = mysqli_fetch_assoc($result);

// --- NEW: Get Payment Items ---
 $items = [];
 $itemQuery = "SELECT * FROM customer_payment_items WHERE customer_payment_id = $paymentId ORDER BY id ASC";
 $itemResult = mysqli_query($conn, $itemQuery);

if ($itemResult && mysqli_num_rows($itemResult) > 0) {
    while ($row = mysqli_fetch_assoc($itemResult)) {
        $items[] = $row;
    }
}
// -----------------------------
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Payment Receipt</title>
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

        .receipt-header .school-address, 
        .receipt-header .school-contact {
            font-size: 14px;
            color: #555;
            margin: 2px 0;
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

        /* NEW: Styles for Items Table */
        .receipt-items {
            margin-bottom: 15px;
        }
        
        .receipt-items h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .receipt-items table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        
        .receipt-items th, 
        .receipt-items td {
            border: 1px solid #ccc;
            padding: 6px 4px;
            text-align: left;
        }
        
        .receipt-items th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        
        .receipt-items .text-right {
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
                <!-- Updated to use config constants -->
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
                <h3>Customer Information</h3>
                <table>
                    <tr>
                        <td class="label">Name:</td>
                        <td><?= $payment['customer_name'] ?></td>
                    </tr>
                    <tr>
                        <td class="label">Type:</td>
                        <td><?= ucfirst($payment['customer_type']) ?></td>
                    </tr>
                </table>
            </div>

            <!-- NEW: Items Table for School Copy -->
            <?php if (!empty($items)): ?>
            <div class="receipt-items">
                <h3>Payment Breakdown</h3>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50%;">Description</th>
                            <th style="width: 15%;">Qty</th>
                            <th style="width: 15%;">Price</th>
                            <th style="width: 20%;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['service_name']) ?></td>
                            <td class="text-right"><?= $item['quantity'] ?></td>
                            <td class="text-right">₱<?= number_format($item['price'], 2) ?></td>
                            <td class="text-right">₱<?= number_format($item['subtotal'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            <!-- --------------------------------- -->
            
            <div class="receipt-amount">
                Total Amount Paid: <span class="amount">₱<?= number_format($payment['amount'], 2) ?></span>
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
                    <div>Customer's Signature:</div>
                    <div class="line"></div>
                </div>
            </div>
        </div>
        
        <!-- Customer Copy -->
        <div class="receipt">
            <div class="receipt-copy">CUSTOMER COPY</div>
            
            <div class="receipt-header">
                <img src="../uploads/csr.png" alt="CSR Logo">
                <!-- Updated to use config constants -->
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
                <h3>Customer Information</h3>
                <table>
                    <tr>
                        <td class="label">Name:</td>
                        <td><?= $payment['customer_name'] ?></td>
                    </tr>
                    <tr>
                        <td class="label">Type:</td>
                        <td><?= ucfirst($payment['customer_type']) ?></td>
                    </tr>
                </table>
            </div>

            <!-- NEW: Items Table for Customer Copy -->
            <?php if (!empty($items)): ?>
            <div class="receipt-items">
                <h3>Payment Breakdown</h3>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50%;">Description</th>
                            <th style="width: 15%;">Qty</th>
                            <th style="width: 15%;">Price</th>
                            <th style="width: 20%;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['service_name']) ?></td>
                            <td class="text-right"><?= $item['quantity'] ?></td>
                            <td class="text-right">₱<?= number_format($item['price'], 2) ?></td>
                            <td class="text-right">₱<?= number_format($item['subtotal'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            <!-- ------------------------------------ -->
            
            <div class="receipt-amount">
                Total Amount Paid: <span class="amount">₱<?= number_format($payment['amount'], 2) ?></span>
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
                    <div>Customer's Signature:</div>
                    <div class="line"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="receipt-actions">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Print Receipt
        </button>
        <a href="cashier.php#customer_payments" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Customer Payments
        </a>
    </div>
    
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>