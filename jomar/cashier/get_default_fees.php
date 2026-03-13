<?php
session_start();
require_once '../config.php';
// Require login and cashier role
requireRole('cashier');

// Get academic year, semester, and category from request
$academicYear = isset($_GET['academic_year']) ? $_GET['academic_year'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Validate required parameters
if (empty($academicYear) || empty($semester) || empty($category)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Academic year, semester, and category are required']);
    exit;
}

// Get default fees for the selected academic year, semester, and category
$defaultFees = [];

// Prepare the SQL statement with parameter binding
$query = "SELECT * FROM default_fees 
          WHERE academic_year = ? 
          AND semester = ? 
          AND (category = ? OR category = 'Both')
          AND is_active = 1 
          ORDER BY fee_type, fee_name";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sss", $academicYear, $semester, $category);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $defaultFees[] = [
            'id' => $row['id'],
            'feeName' => $row['fee_name'],
            'feeType' => $row['fee_type'],
            'amount' => $row['amount'],
            'category' => $row['category']
        ];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($defaultFees);
?>