<?php
require_once '../config.php';

// Check if student_id is provided
if (!isset($_GET['student_id']) || empty($_GET['student_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Student ID is required']);
    exit;
}

$studentId = mysqli_real_escape_string($conn, $_GET['student_id']);

// Get student fees with payment allocations
$query = "SELECT sf.id, sf.amount, sf.academic_year, sf.semester, 
                f.fee_name, f.fee_type,
                COALESCE(SUM(pa.allocated_amount), 0) as paid_amount
         FROM student_fees sf
         JOIN fees f ON sf.fee_id = f.id
         LEFT JOIN payment_allocations pa ON sf.id = pa.student_fee_id
         WHERE sf.student_id = $studentId
         GROUP BY sf.id, sf.amount, sf.academic_year, sf.semester, f.fee_name, f.fee_type
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
    $balance = $row['amount'] - $row['paid_amount'];
    
    // Determine payment status
    if ($balance <= 0) {
        $status = 'fully-paid';
    } elseif ($row['paid_amount'] > 0) {
        $status = 'partially-paid';
    } else {
        $status = 'not-paid';
    }
    
    $fees[] = [
        'id' => $row['id'],
        'fee_name' => $row['fee_name'],
        'fee_type' => $row['fee_type'],
        'amount' => $row['amount'],
        'paid_amount' => $row['paid_amount'],
        'balance' => $balance,
        'status' => $status,
        'academic_year' => $row['academic_year'],
        'semester' => $row['semester']
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['fees' => $fees]);
?>