<?php
require_once '../config.php';

// Check if payment_id is provided
if (!isset($_GET['payment_id']) || empty($_GET['payment_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Payment ID is required']);
    exit;
}

$paymentId = mysqli_real_escape_string($conn, $_GET['payment_id']);

// Get payment details
$paymentQuery = "SELECT p.*, s.last_name, s.first_name, s.middle_name 
                 FROM payments p
                 JOIN students s ON p.student_id = s.id
                 WHERE p.id = $paymentId";

$paymentResult = mysqli_query($conn, $paymentQuery);

if (!$paymentResult || mysqli_num_rows($paymentResult) == 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Payment not found']);
    exit;
}

$payment = mysqli_fetch_assoc($paymentResult);

// Get student fees with payment allocations for this specific payment
$query = "SELECT sf.id, sf.amount, 
                f.fee_name, f.fee_type,
                COALESCE(SUM(pa2.allocated_amount), 0) as total_paid_amount,
                COALESCE(pa.allocated_amount, 0) as allocated_amount
         FROM student_fees sf
         JOIN fees f ON sf.fee_id = f.id
         LEFT JOIN payment_allocations pa ON sf.id = pa.student_fee_id AND pa.payment_id = $paymentId
         LEFT JOIN payment_allocations pa2 ON sf.id = pa2.student_fee_id
         WHERE sf.student_id = {$payment['student_id']}
         GROUP BY sf.id, sf.amount, f.fee_name, f.fee_type, pa.allocated_amount
         ORDER BY sf.academic_year DESC, sf.semester DESC, f.fee_type, f.fee_name";

$result = mysqli_query($conn, $query);

if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . mysqli_error($conn)]);
    exit;
}

$fees = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Calculate remaining balance
    $balance = $row['amount'] - $row['total_paid_amount'];
    
    $fees[] = [
        'id' => $row['id'],
        'fee_name' => $row['fee_name'],
        'fee_type' => $row['fee_type'],
        'amount' => $row['amount'],
        'paid_amount' => $row['total_paid_amount'],
        'balance' => $balance,
        'allocated_amount' => $row['allocated_amount']
    ];
}

// Get general allocation (not tied to a specific fee)
$generalAllocationQuery = "SELECT allocated_amount 
                           FROM payment_allocations 
                           WHERE payment_id = $paymentId AND student_fee_id IS NULL";

$generalAllocationResult = mysqli_query($conn, $generalAllocationQuery);
$generalAllocation = 0;

if ($generalAllocationResult && $row = mysqli_fetch_assoc($generalAllocationResult)) {
    $generalAllocation = $row['allocated_amount'];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'payment' => $payment,
    'fees' => $fees,
    'general_allocation' => $generalAllocation
]);
?>