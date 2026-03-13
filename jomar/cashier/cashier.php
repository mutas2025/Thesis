<?php
session_start();
require_once '../config.php';
// Require login and cashier role
requireRole('cashier');

// Helper function to format amounts with 2 decimal places
function formatAmount($amount) {
    // Always format with 2 decimals
    return number_format($amount, 2);
}

// Function to get the current active tab with fallback to student_management (Dashboard removed)
function getCurrentTab() {
    // First check if current_tab is passed in the request
    if (isset($_REQUEST['current_tab'])) {
        return $_REQUEST['current_tab'];
    }
    // Then check session
    return isset($_SESSION['active_tab']) ? $_SESSION['active_tab'] : 'student_management';
}

// Function to redirect to current tab
function redirectToCurrentTab() {
    $currentTab = getCurrentTab();
    $_SESSION['active_tab'] = $currentTab;
    header("Location: cashier.php#$currentTab");
    exit();
}

// Function to redirect to specific tab
function redirectToTab($tab) {
    $_SESSION['active_tab'] = $tab;
    header("Location: cashier.php#$tab");
    exit();
}

// Function to get student's total units for a specific academic year and semester
// Note: This function is kept for compatibility but no longer used for tuition calculation
function getStudentTotalUnits($conn, $studentId, $academicYear, $semester) {
    $query = "SELECT SUM(s.unit) as total_units 
              FROM student_subjects ss
              JOIN subjects s ON ss.subject_id = s.id
              JOIN enrollments e ON ss.enrollment_id = e.id
              WHERE e.student_id = $studentId 
              AND e.academic_year = '$academicYear'
              AND e.semester = '$semester'";
    
    $result = mysqli_query($conn, $query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['total_units'] ? $row['total_units'] : 0;
    }
    return 0;
}

// Handle active tab setting
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['active_tab'])) {
    $_SESSION['active_tab'] = $_POST['active_tab'];
    exit();
}

// Get all fee types
 $fees = [];
 $query = "SELECT * FROM fees ORDER BY fee_type, fee_name";
 $result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $fees[] = $row;
    }
}

// Get active academic years
 $activeAcademicYears = [];
 $query = "SELECT * FROM academic_years WHERE is_active = 1 ORDER BY academic_year DESC";
 $result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $activeAcademicYears[] = $row;
    }
}

// Get all academic years for reports
 $allAcademicYears = [];
 $query = "SELECT * FROM academic_years ORDER BY academic_year DESC";
 $result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $allAcademicYears[] = $row;
    }
}

// Get default fees with pagination
 $defaultFees = [];
 $limit = 20; // Number of records per page
 $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
 $offset = ($page - 1) * $limit;
 $query = "SELECT * FROM default_fees WHERE is_active = 1 ORDER BY fee_type, fee_name LIMIT $limit OFFSET $offset";
 $result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $defaultFees[] = $row;
    }
}

// Get total count for pagination
 $countQuery = "SELECT COUNT(*) as total FROM default_fees WHERE is_active = 1";
 $countResult = mysqli_query($conn, $countQuery);
 $countRow = mysqli_fetch_assoc($countResult);
 $totalDefaultFees = $countRow['total'];
 $totalPages = ceil($totalDefaultFees / $limit);

// Handle form submission for adding a student fee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_fee') {
    $studentId = mysqli_real_escape_string($conn, $_POST['studentId']);
    $feeType = mysqli_real_escape_string($conn, $_POST['feeType']);
    $feeName = mysqli_real_escape_string($conn, $_POST['feeName']);
    $baseAmount = mysqli_real_escape_string($conn, $_POST['baseAmount']);
    $academicYear = mysqli_real_escape_string($conn, $_POST['academicYear']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $currentTab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'student_management';
    
    // For all fee types, total amount is the same as base amount
    $totalAmount = $baseAmount;
    
    // Check if fee exists in fees table
    $query = "SELECT id FROM fees WHERE fee_name = '$feeName' AND fee_type = '$feeType'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) > 0) {
        // Fee exists, get its ID
        $row = mysqli_fetch_assoc($result);
        $feeId = $row['id'];
        
        // Update the amount in fees table
        $updateQuery = "UPDATE fees SET amount = '$totalAmount', base_amount = '$baseAmount' WHERE id = $feeId";
        mysqli_query($conn, $updateQuery);
    } else {
        // Fee doesn't exist, insert new fee
        $insertQuery = "INSERT INTO fees (fee_name, fee_type, amount, base_amount) VALUES ('$feeName', '$feeType', '$totalAmount', '$baseAmount')";
        mysqli_query($conn, $insertQuery);
        $feeId = mysqli_insert_id($conn);
    }
    
    $query = "INSERT INTO student_fees (student_id, fee_id, amount, academic_year, semester) 
              VALUES ('$studentId', '$feeId', '$totalAmount', '$academicYear', '$semester')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Fee added successfully";
        $_SESSION['message_type'] = "success";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Fee added successfully']);
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
        exit();
    }
}

// Handle form submission for adding a default fee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_default_fee') {
    $feeName = mysqli_real_escape_string($conn, $_POST['feeName']);
    $feeType = mysqli_real_escape_string($conn, $_POST['feeType']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $academicYear = mysqli_real_escape_string($conn, $_POST['academicYear']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $category = mysqli_real_escape_string($conn, $_POST['category']); // Add category
    $currentTab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'default_fees';
    
    $query = "INSERT INTO default_fees (fee_name, fee_type, amount, academic_year, semester, category) 
              VALUES ('$feeName', '$feeType', '$amount', '$academicYear', '$semester', '$category')";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Default fee added successfully";
        $_SESSION['message_type'] = "success";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Default fee added successfully']);
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
        exit();
    }
}

// Handle form submission for editing a default fee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_default_fee') {
    $feeId = mysqli_real_escape_string($conn, $_POST['feeId']);
    $feeName = mysqli_real_escape_string($conn, $_POST['feeName']);
    $feeType = mysqli_real_escape_string($conn, $_POST['feeType']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $academicYear = mysqli_real_escape_string($conn, $_POST['academicYear']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $category = mysqli_real_escape_string($conn, $_POST['category']); // Add category
    $currentTab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'default_fees';
    
    $query = "UPDATE default_fees SET 
              fee_name = '$feeName', 
              fee_type = '$feeType', 
              amount = '$amount', 
              academic_year = '$academicYear', 
              semester = '$semester',
              category = '$category' 
              WHERE id = $feeId";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Default fee updated successfully";
        $_SESSION['message_type'] = "success";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Default fee updated successfully', 'stay_on_tab' => true]);
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
        exit();
    }
}

// Handle form submission for applying default fees to a student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'apply_default_fees') {
    // Debug: Log the entire POST request
    error_log("POST Data: " . print_r($_POST, true));
    
    $studentId = mysqli_real_escape_string($conn, $_POST['studentId']);
    $academicYear = mysqli_real_escape_string($conn, $_POST['academicYear']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $category = mysqli_real_escape_string($conn, $_POST['category']); // Add category
    $currentTab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'student_management';
    
    // Validate required fields
    if (empty($studentId)) {
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: No student selected']);
        exit();
    }
    
    if (empty($academicYear)) {
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: Academic year not selected']);
        exit();
    }
    
    if (empty($semester)) {
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: Semester not selected']);
        exit();
    }
    
    if (empty($category)) {
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: Category not selected']);
        exit();
    }
    
    $successCount = 0;
    $errorCount = 0;
    
    if (isset($_POST['default_fees']) && is_array($_POST['default_fees']) && !empty($_POST['default_fees'])) {
        foreach ($_POST['default_fees'] as $defaultFeeId) {
            $defaultFeeId = mysqli_real_escape_string($conn, $defaultFeeId);
            
            // Get the default fee details
            $query = "SELECT * FROM default_fees WHERE id = $defaultFeeId";
            $result = mysqli_query($conn, $query);
            
            if ($result && $row = mysqli_fetch_assoc($result)) {
                $feeName = $row['fee_name'];
                $feeType = $row['fee_type'];
                $amount = $row['amount'];
                
                // Check if fee exists in fees table
                $query = "SELECT id FROM fees WHERE fee_name = '$feeName' AND fee_type = '$feeType'";
                $result = mysqli_query($conn, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    // Fee exists, get its ID
                    $feeRow = mysqli_fetch_assoc($result);
                    $feeId = $feeRow['id'];
                    
                    // Update the amount in fees table
                    $updateQuery = "UPDATE fees SET amount = '$amount' WHERE id = $feeId";
                    mysqli_query($conn, $updateQuery);
                } else {
                    // Fee doesn't exist, insert new fee
                    $insertQuery = "INSERT INTO fees (fee_name, fee_type, amount) VALUES ('$feeName', '$feeType', '$amount')";
                    if (mysqli_query($conn, $insertQuery)) {
                        $feeId = mysqli_insert_id($conn);
                    } else {
                        $errorCount++;
                        error_log("Error inserting fee: " . mysqli_error($conn));
                        continue;
                    }
                }
                
                // Check if the student already has this fee for the same academic year and semester
                $checkQuery = "SELECT id FROM student_fees WHERE student_id = $studentId AND fee_id = $feeId AND academic_year = '$academicYear' AND semester = '$semester'";
                $checkResult = mysqli_query($conn, $checkQuery);
                
                if (mysqli_num_rows($checkResult) == 0) {
                    // Insert the student fee
                    $insertQuery = "INSERT INTO student_fees (student_id, fee_id, amount, academic_year, semester) 
                                  VALUES ('$studentId', '$feeId', '$amount', '$academicYear', '$semester')";
                    
                    if (mysqli_query($conn, $insertQuery)) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        error_log("Error inserting student fee: " . mysqli_error($conn));
                    }
                } else {
                    // Skip if already exists
                    $errorCount++;
                }
            } else {
                $errorCount++;
                error_log("Default fee not found with ID: $defaultFeeId");
            }
        }
    } else {
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: No default fees selected']);
        exit();
    }
    
    $message = '';
    $hasError = false;
    
    if ($successCount > 0) {
        $message = "$successCount default fees applied successfully";
    }
    
    if ($errorCount > 0) {
        $message .= ($message ? ' ' : '') . "$errorCount fees could not be applied (either already exist or an error occurred)";
        $hasError = true;
    }
    
    // Return JSON response for AJAX
    header('Content-Type: application/json');
    echo json_encode([
        'success' => !$hasError, 
        'message' => $message,
        'successCount' => $successCount,
        'errorCount' => $errorCount
    ]);
    exit();
}

// Handle form submission for editing a student fee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit_fee') {
    $feeId = mysqli_real_escape_string($conn, $_POST['feeId']);
    $studentFeeId = mysqli_real_escape_string($conn, $_POST['studentFeeId']);
    $baseAmount = mysqli_real_escape_string($conn, $_POST['baseAmount']);
    $currentTab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'student_management';
    
    // For all fee types, total amount is the same as base amount
    $totalAmount = $baseAmount;
    
    // Update the fees table
    $updateQuery = "UPDATE fees SET amount = '$totalAmount', base_amount = '$baseAmount' WHERE id = $feeId";
    mysqli_query($conn, $updateQuery);
    
    // Update the student_fees table
    $query = "UPDATE student_fees SET amount = '$totalAmount' WHERE id = $studentFeeId";
    
    if (mysqli_query($conn, $query)) {
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Fee updated successfully']);
        exit();
    } else {
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
        exit();
    }
}

// Handle delete student fee
if (isset($_GET['action']) && $_GET['action'] == 'delete_fee' && isset($_GET['id'])) {
    $feeId = mysqli_real_escape_string($conn, $_GET['id']);
    $currentTab = isset($_GET['current_tab']) ? $_GET['current_tab'] : 'student_management';
    
    $query = "DELETE FROM student_fees WHERE id = $feeId";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Fee deleted successfully";
        $_SESSION['message_type'] = "success";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Fee deleted successfully']);
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
        exit();
    }
}

// Handle delete default fee
if (isset($_GET['action']) && $_GET['action'] == 'delete_default_fee' && isset($_GET['id'])) {
    $feeId = mysqli_real_escape_string($conn, $_GET['id']);
    $currentTab = isset($_GET['current_tab']) ? $_GET['current_tab'] : 'default_fees';
    
    $query = "DELETE FROM default_fees WHERE id = $feeId";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Default fee deleted successfully";
        $_SESSION['message_type'] = "success";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Default fee deleted successfully', 'stay_on_tab' => true]);
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
        exit();
    }
}

// Handle form submission for recording a payment with allocation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_payment') {
    $studentId = mysqli_real_escape_string($conn, $_POST['studentId']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $discount = mysqli_real_escape_string($conn, $_POST['discount']);
    $paymentDate = mysqli_real_escape_string($conn, $_POST['paymentDate']);
    $orNumber = mysqli_real_escape_string($conn, $_POST['orNumber']);
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $currentTab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'student_management';
    
    // Calculate net amount
    $netAmount = $amount - $discount;
    
    // Check if OR number already exists
    $checkQuery = "SELECT id FROM payments WHERE or_number = '$orNumber'";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: OR Number already exists']);
        exit();
    } else {
        // Insert the payment
        $query = "INSERT INTO payments (student_id, amount, discount, payment_date, or_number, payment_method, remarks) 
                  VALUES ('$studentId', '$amount', '$discount', '$paymentDate', '$orNumber', '$paymentMethod', '$remarks')";
        
        if (mysqli_query($conn, $query)) {
            $paymentId = mysqli_insert_id($conn);
            
            // If fee allocations are provided, process them
            if (isset($_POST['fee_allocations']) && is_array($_POST['fee_allocations'])) {
                $totalAllocated = 0;
                $allocations = [];
                
                foreach ($_POST['fee_allocations'] as $feeId => $allocatedAmount) {
                    $feeId = mysqli_real_escape_string($conn, $feeId);
                    $allocatedAmount = mysqli_real_escape_string($conn, $allocatedAmount);
                    
                    if ($allocatedAmount > 0) {
                        // Get the student fee record
                        $query = "SELECT * FROM student_fees WHERE id = $feeId AND student_id = $studentId";
                        $result = mysqli_query($conn, $query);
                        
                        if ($result && $row = mysqli_fetch_assoc($result)) {
                            // Check if there's already an allocation for this fee
                            $checkQuery = "SELECT * FROM payment_allocations WHERE student_fee_id = $feeId";
                            $checkResult = mysqli_query($conn, $checkQuery);
                            
                            if (mysqli_num_rows($checkResult) > 0) {
                                // Update existing allocation
                                $updateQuery = "UPDATE payment_allocations 
                                              SET allocated_amount = allocated_amount + $allocatedAmount 
                                              WHERE student_fee_id = $feeId";
                                mysqli_query($conn, $updateQuery);
                            } else {
                                // Insert new allocation
                                $insertQuery = "INSERT INTO payment_allocations (payment_id, student_fee_id, allocated_amount) 
                                              VALUES ($paymentId, $feeId, $allocatedAmount)";
                                mysqli_query($conn, $insertQuery);
                            }
                            
                            $totalAllocated += $allocatedAmount;
                        }
                    }
                }
                
                // If there's remaining unallocated amount, record it as a general payment
                $unallocatedAmount = $netAmount - $totalAllocated;
                if ($unallocatedAmount > 0) {
                    // Record as general allocation (not tied to a specific fee)
                    $query = "INSERT INTO payment_allocations (payment_id, student_fee_id, allocated_amount) 
                              VALUES ($paymentId, NULL, $unallocatedAmount)";
                    mysqli_query($conn, $query);
                }
            } else {
                // No specific allocations, record as general payment
                $query = "INSERT INTO payment_allocations (payment_id, student_fee_id, allocated_amount) 
                          VALUES ($paymentId, NULL, $netAmount)";
                mysqli_query($conn, $query);
            }
            
            // Return JSON response for AJAX including paymentId for printing
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Payment recorded successfully', 'paymentId' => $paymentId]);
            exit();
        } else {
            // Return JSON response for AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
            exit();
        }
    }
}

// Handle form submission for recording a customer payment (UPDATED FOR MULTIPLE SERVICES)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_customer_payment') {
    $customerName = mysqli_real_escape_string($conn, $_POST['customerName']);
    $customerType = mysqli_real_escape_string($conn, $_POST['customerType']);
    // We no longer take a single serviceId or amount from POST directly
    // We calculate it from the items array
    $amount = mysqli_real_escape_string($conn, $_POST['amount']); // Total calculated amount
    $paymentDate = mysqli_real_escape_string($conn, $_POST['paymentDate']);
    $orNumber = mysqli_real_escape_string($conn, $_POST['orNumber']);
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['paymentMethod']);
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);
    $currentTab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'customer_payments';
    
    // Check if OR number already exists
    $checkQuery = "SELECT id FROM customer_payments WHERE or_number = '$orNumber'";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if (mysqli_num_rows($checkResult) > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: OR Number already exists']);
        exit();
    } else {
        // Insert the main payment record (service_id is NULL now because we have multiple)
        $query = "INSERT INTO customer_payments (customer_name, customer_type, service_id, amount, payment_date, or_number, payment_method, remarks) 
                  VALUES ('$customerName', '$customerType', NULL, '$amount', '$paymentDate', '$orNumber', '$paymentMethod', '$remarks')";
        
        if (mysqli_query($conn, $query)) {
            $newPaymentId = mysqli_insert_id($conn);
            
            // Process items (multiple services)
            if (isset($_POST['services']) && is_array($_POST['services'])) {
                foreach ($_POST['services'] as $item) {
                    $serviceId = mysqli_real_escape_string($conn, $item['id']);
                    $serviceName = mysqli_real_escape_string($conn, $item['name']);
                    $quantity = (int)$item['qty'];
                    $price = (float)$item['price'];
                    $subtotal = $price * $quantity;
                    
                    // Insert into customer_payment_items
                    $itemQuery = "INSERT INTO customer_payment_items (customer_payment_id, service_id, service_name, quantity, price, subtotal) 
                                  VALUES ($newPaymentId, $serviceId, '$serviceName', $quantity, $price, $subtotal)";
                    mysqli_query($conn, $itemQuery);
                }
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Customer payment recorded successfully', 'paymentId' => $newPaymentId]);
            exit();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
            exit();
        }
    }
}

// Handle CANCEL PAYMENT (New Logic replacing Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'cancel_payment') {
    $paymentId = mysqli_real_escape_string($conn, $_POST['paymentId']);
    $verificationCode = isset($_POST['verification_code']) ? $_POST['verification_code'] : '';

    // Verify Code
    if ($verificationCode !== 'ccs2026') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid verification code. Transaction not cancelled.']);
        exit();
    }

    $currentTab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'student_management';
    
    // First, delete all allocations for this payment
    $query = "DELETE FROM payment_allocations WHERE payment_id = $paymentId";
    mysqli_query($conn, $query);
    
    // Then delete the payment
    $query = "DELETE FROM payments WHERE id = $paymentId";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Payment cancelled successfully";
        $_SESSION['message_type'] = "success";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Transaction cancelled successfully']);
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
        exit();
    }
}

// Handle CANCEL CUSTOMER PAYMENT (New Logic replacing Delete)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'cancel_customer_payment') {
    $paymentId = mysqli_real_escape_string($conn, $_POST['paymentId']);
    $verificationCode = isset($_POST['verification_code']) ? $_POST['verification_code'] : '';

    // Verify Code
    if ($verificationCode !== 'sjc2026') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid verification code. Transaction not cancelled.']);
        exit();
    }

    $currentTab = isset($_POST['current_tab']) ? $_POST['current_tab'] : 'customer_payments';
    
    // Delete items first
    mysqli_query($conn, "DELETE FROM customer_payment_items WHERE customer_payment_id = $paymentId");
    
    // Then delete the payment
    $query = "DELETE FROM customer_payments WHERE id = $paymentId";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Customer payment cancelled successfully";
        $_SESSION['message_type'] = "success";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Transaction cancelled successfully']);
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
        // Return JSON response for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . mysqli_error($conn)]);
        exit();
    }
}

// Get customer payments
 $customerPayments = [];
 // We select the payment and count the items
 $query = "SELECT cp.*, COUNT(cpi.id) as item_count 
           FROM customer_payments cp 
           LEFT JOIN customer_payment_items cpi ON cp.id = cpi.customer_payment_id 
           GROUP BY cp.id 
           ORDER BY cp.payment_date DESC";
 $result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $customerPayments[] = $row;
    }
}

// Get Services for Customer Payment Modal
 $services = [];
 $query = "SELECT * FROM services ORDER BY name ASC"; 
 $result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = $row;
    }
}

// Sidebar Statistics
 $totalFeesAmount = 0;
 $totalPaymentsAmount = 0;
 $totalCustomerPaymentsAmount = 0;
 $totalBalance = 0;

// Get total fees
 $query = "SELECT SUM(amount) as total FROM student_fees";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $totalFeesAmount = $row['total'] ? $row['total'] : 0;
}

// Get total payments (net of discounts)
 $query = "SELECT SUM(amount - discount) as total FROM payments";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $totalPaymentsAmount = $row['total'] ? $row['total'] : 0;
}

// Get total customer payments
 $query = "SELECT SUM(amount) as total FROM customer_payments";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $totalCustomerPaymentsAmount = $row['total'] ? $row['total'] : 0;
}

// Calculate total balance (Account Receivables)
 $totalBalance = $totalFeesAmount - $totalPaymentsAmount;

// Get daily collection (today's payments)
 $today = date('Y-m-d');
 $dailyCollection = 0;
 $query = "SELECT SUM(amount - discount) as total FROM payments WHERE payment_date = '$today'";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $dailyCollection = $row['total'] ? $row['total'] : 0;
}

// Get daily customer collection
 $dailyCustomerCollection = 0;
 $query = "SELECT SUM(amount) as total FROM customer_payments WHERE payment_date = '$today'";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $dailyCustomerCollection = $row['total'] ? $row['total'] : 0;
}

// Total daily collection
 $totalDailyCollection = $dailyCollection + $dailyCustomerCollection;

// Get monthly collection (current month's payments)
 $currentMonth = date('Y-m');
 $monthlyCollection = 0;
 $query = "SELECT SUM(amount - discount) as total FROM payments WHERE payment_date LIKE '$currentMonth%'";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $monthlyCollection = $row['total'] ? $row['total'] : 0;
}

// Get monthly customer collection
 $monthlyCustomerCollection = 0;
 $query = "SELECT SUM(amount) as total FROM customer_payments WHERE payment_date LIKE '$currentMonth%'";
 $result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $monthlyCustomerCollection = $row['total'] ? $row['total'] : 0;
}

// Total monthly collection
 $totalMonthlyCollection = $monthlyCollection + $monthlyCustomerCollection;

 // Get current user info for display
 $current_user_id = $_SESSION['user_id'] ?? 0;
 $current_user = $conn->query("SELECT username, fullname, role FROM users WHERE id = $current_user_id")->fetch_assoc();
 $display_name = $current_user['fullname'] ?? $current_user['username'] ?? 'Admin User';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cashier</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <!-- jQuery UI (for autocomplete) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
   <style>
        .sidebar-menu li.active {
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar-menu li.active > a {
            color: #fff;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        .nav-tabs {
            margin-bottom: 15px;
        }
        .search-box {
            margin-bottom: 15px;
        }
        .brand-link {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .brand-link img {
            max-height: 35px;
            margin-right: 10px;
        }
        .small-box {
            border-radius: 0.25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            margin-bottom: 1rem;
            position: relative;
            display: block;
            margin-bottom: 20px;
        }
        .small-box > .inner {
            padding: 10px;
        }
        .small-box > .small-box-footer {
            background: rgba(0,0,0,0.1);
            color: rgba(255,255,255,0.8);
            display: block;
            padding: 3px 0;
            position: relative;
            text-align: center;
            text-decoration: none;
            z-index: 10;
        }
        .small-box > .small-box-footer:hover {
            background: rgba(0,0,0,0.15);
            color: #fff;
        }
        .small-box > .icon {
            color: rgba(0,0,0,0.15);
            z-index: 0;
        }
        .small-box:hover {
            text-decoration: none;
            color: #f8f9fa;
        }
        .small-box:hover .icon {
            font-size: 5rem;
        }
        .small-box .icon {
            transition: all 0.3s linear;
        }
        .small-box p {
            font-size: 1rem;
            margin: 0 0 5px 0;
        }
        .small-box h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 10px 0;
            white-space: nowrap;
            padding: 0;
        }
        @media (max-width: 767.98px) {
            .small-box h3 {
                font-size: 1.5rem;
            }
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .readonly-field {
            background-color: #f8f9fa;
        }
        .filter-container {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .filter-container .form-group {
            margin-bottom: 10px;
        }
        .balance-positive {
            color: blue;
            font-weight: bold;
        }
        .balance-negative {
            color: red;
            font-weight: bold;
        }
        .balance-zero {
            color: #6c757d;
            font-weight: bold;
        }
        .statement-table {
            width: 100%;
            border-collapse: collapse;
        }
        .statement-table th, .statement-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .statement-table th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .statement-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .statement-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .statement-footer {
            margin-top: 20px;
            text-align: right;
        }
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .toast {
            background-color: #fff;
            border-radius: 0.25rem;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
            margin-bottom: 0.75rem;
            opacity: 0;
            transition: opacity 0.15s linear;
        }
        .toast.show {
            opacity: 1;
        }
        .toast-header {
            display: flex;
            align-items: center;
            padding: 0.75rem 0.75rem;
            color: #6c757d;
            background-color: rgba(255, 255, 255, 0.85);
            background-clip: padding-box;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            border-top-left-radius: calc(0.25rem - 1px);
            border-top-right-radius: calc(0.25rem - 1px);
        }
        .toast-body {
            padding: 0.75rem;
        }
        .toast-success .toast-header {
            color: #fff;
            background-color: #004085;
        }
        .toast-error .toast-header {
            color: #fff;
            background-color: #dc3545;
        }
        .toast-info .toast-header {
            color: #fff;
            background-color: #004085;
        }
        .toast-warning .toast-header {
            color: #fff;
            background-color: #ffc107;
        }
        .no-data-message {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #6c757d;
        }
        .student-search-container {
            position: relative;
        }
        .student-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        .student-search-result {
            padding: 8px;
            cursor: pointer;
        }
        .student-search-result:hover {
            background-color: #f8f9fa;
        }
        .adminlte-style .btn {
            border-radius: 3px;
            box-shadow: none;
            border: 1px solid transparent;
        }
        .adminlte-style .btn-primary {
            background-color: #004085;
            border-color: #004085;
        }
        .adminlte-style .btn-success {
            background-color: #004085;
            border-color: #004085;
        }
        .adminlte-style .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        .adminlte-style .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .adminlte-style .btn-info {
            background-color: #004085;
            border-color: #004085;
        }
        .adminlte-style .card {
            border-radius: 0.25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            margin-bottom: 1rem;
        }
        .adminlte-style .card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0,0,0,.125);
            padding: 0.75rem 1.25rem;
        }
        .adminlte-style .table-bordered {
            border: 1px solid #dee2e6;
        }
        .adminlte-style .table-bordered th,
        .adminlte-style .table-bordered td {
            border: 1px solid #dee2e6;
        }
        .adminlte-style .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,.05);
        }
        .adminlte-style .modal-content {
            border-radius: 0.3rem;
            border: none;
            box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,.5);
        }
        .adminlte-style .modal-header {
            border-bottom: 1px solid #dee2e6;
        }
        .adminlte-style .modal-footer {
            border-top: 1px solid #dee2e6;
        }
        .adminlte-style .form-control {
            border-radius: 0;
            border: 1px solid #ced4da;
        }
        .adminlte-style .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,64,133,.25);
        }
        .adminlte-style .form-group {
            margin-bottom: 1rem;
        }
        .adminlte-style .badge {
            border-radius: 0.25rem;
            padding: 0.25em 0.4em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .report-table th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .report-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-footer {
            margin-top: 20px;
            text-align: right;
        }
        .report-summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .report-summary h4 {
            margin-top: 0;
        }
        .report-summary table {
            width: 100%;
        }
        .report-summary td {
            padding: 5px;
        }
        .report-summary td:first-child {
            font-weight: bold;
        }
        .default-fees-container {
            margin-top: 15px;
        }
        .default-fee-item {
            display: flex;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .default-fee-item:last-child {
            border-bottom: none;
        }
        .default-fee-checkbox {
            margin-right: 10px;
        }
        .default-fee-details {
            flex-grow: 1;
        }
        .default-fee-name {
            font-weight: bold;
        }
        .default-fee-amount {
            color: #004085;
        }
        .default-fee-type {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .payment-history-sidebar {
            margin-top: 20px;
            padding: 10px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
        }
        .payment-history-sidebar h4 {
            color: white;
            margin-bottom: 10px;
            font-size: 1rem;
            padding-bottom: 5px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .payment-history-item {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .payment-history-item:last-child {
            border-bottom: none;
        }
        .payment-history-name {
            font-weight: bold;
            color: white;
            font-size: 0.9rem;
        }
        .payment-history-amount {
            color: #004085;
            font-size: 0.85rem;
        }
        .payment-history-date {
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
        }
        .sidebar-collapse .payment-history-sidebar {
            display: none;
        }
        .search-highlight {
            background-color: #fff3cd;
            font-weight: bold;
        }
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .loading-spinner {
            width: 3rem;
            height: 3rem;
        }
        .student-account-container {
            display: none;
        }
        .student-account-container.active {
            display: block;
        }
        .cashflow-positive {
            color: blue;
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
        /* Custom styles for fee type tabs */
        .fee-type-tabs .nav-link {
            color: #495057;
            border: 1px solid transparent;
            border-top-left-radius: 0.25rem;
            border-top-right-radius: 0.25rem;
        }
        .fee-type-tabs .nav-link:hover {
            border-color: #e9ecef #e9ecef #dee2e6;
        }
        .fee-type-tabs .nav-link.active {
            color: #495057;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
        }
        .fee-type-tabs {
            border-bottom: 1px solid #dee2e6;
        }
        .discount-amount {
            color: #dc3545;
            font-weight: bold;
        }
        .net-amount {
            color: #004085;
            font-weight: bold;
        }
        .category-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin-left: 0.5rem;
        }
        .category-jhs {
            background-color: #004085;
            color: white;
        }
        .category-shs {
            background-color: #004085;
            color: white;
        }
        .category-both {
            background-color: #004085;
            color: white;
        }
        .category-kinder {
            background-color: #dc3545;
            color: white;
        }
        .category-elementary {
            background-color: #004085;
            color: white;
        }
        .category-college {
            background-color: #004085;
            color: white;
        }
        .custom-category-group {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #004085;
        }
        /* Payment Allocation Styles */
        .fee-allocation-container {
            margin-top: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .fee-allocation-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #495057;
        }
        .fee-allocation-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .fee-allocation-item:last-child {
            border-bottom: none;
        }
        .fee-allocation-checkbox {
            margin-right: 10px;
        }
        .fee-allocation-details {
            flex-grow: 1;
        }
        .fee-allocation-name {
            font-weight: bold;
        }
        .fee-allocation-amount {
            color: #004085;
        }
        .fee-allocation-balance {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .fee-allocation-input {
            width: 100px;
            margin-left: 10px;
        }
        .allocation-summary {
            margin-top: 15px;
            padding: 10px;
            background-color: #e9f5ff;
            border-radius: 5px;
            border-left: 4px solid #004085;
        }
        .allocation-summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .allocation-summary-row:last-child {
            margin-bottom: 0;
            font-weight: bold;
            border-top: 1px solid #dee2e6;
            padding-top: 5px;
        }
        .allocation-summary-label {
            color: #495057;
        }
        .allocation-summary-value {
            color: #004085;
            font-weight: bold;
        }
        .unallocated-amount {
            color: #dc3545;
        }
        .fully-paid {
            color: #004085;
        }
        .partially-paid {
            color: #ffc107;
        }
        .not-paid {
            color: #dc3545;
        }
        /* Amount input styles */
        .amount-input {
            text-align: right;
        }
        /* Red and Blue theme styles for sidebar */
        .main-sidebar {
            background-color: #004085 !important;
        }
        .main-sidebar .nav-sidebar > .nav-item > .nav-link.active {
            background-color: rgba(220,53,69,0.2);
            color: #fff;
        }
        .main-sidebar .nav-sidebar > .nav-item > .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        .main-sidebar .nav-sidebar > .nav-item > .nav-link i {
            color: rgba(255,255,255,0.8);
        }
        .main-sidebar .nav-sidebar > .nav-item > .nav-link.active i {
            color: #fff;
        }
        .main-sidebar .brand-link {
            background-color: #002752 !important;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item > .nav-link.active, 
        .sidebar-dark-redblue .nav-sidebar > .nav-item > .nav-link:hover {
            background-color: rgba(220,53,69,0.2);
            color: #fff;
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item > .nav-link {
            color: rgba(255,255,255,0.8);
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item > .nav-link i {
            color: rgba(255,255,255,0.8);
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item > .nav-link.active i {
            color: #fff;
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item.menu-open > .nav-link,
        .sidebar-dark-redblue .nav-sidebar > .nav-item:hover > .nav-link {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }
        .sidebar-dark-redblue .nav-treeview > .nav-item > .nav-link.active,
        .sidebar-dark-redblue .nav-treeview > .nav-item > .nav-link:hover {
            background-color: rgba(220,53,69,0.2);
            color: #fff;
        }
        .sidebar-dark-redblue .nav-treeview > .nav-item > .nav-link {
            color: rgba(255,255,255,0.8);
        }
        .sidebar-dark-redblue .nav-treeview > .nav-item > .nav-link i {
            color: rgba(255,255,255,0.8);
        }
        .sidebar-dark-redblue .nav-treeview > .nav-item > .nav-link.active i {
            color: #fff;
        }
        .sidebar-dark-redblue .nav-header {
            color: rgba(255,255,255,0.8);
            background: inherit;
        }
        .sidebar-dark-redblue .sidebar a {
            color: rgba(255,255,255,0.8);
        }
        .sidebar-dark-redblue .user-panel .info {
            color: #fff;
        }
        .sidebar-dark-redblue .user-panel .info > a {
            color: #fff;
        }
        .sidebar-dark-redblue .btn-sidebar {
            color: rgba(255,255,255,0.8);
        }
        .sidebar-dark-redblue .btn-sidebar:hover {
            color: #fff;
        }
        .sidebar-dark-redblue .control-sidebar-content {
            background: #004085;
        }
        .sidebar-dark-redblue .main-header .navbar {
            background-color: #fff;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-link {
            color: #6c757d;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-link:hover {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .logo {
            background-color: #002752;
        }
        .sidebar-dark-redblue .main-header .logo .logo-lg {
            color: #fff;
        }
        .sidebar-dark-redblue .main-header .logo .logo-mini {
            color: #fff;
        }
        .sidebar-dark-redblue .main-header .navbar .sidebar-toggle {
            color: #6c757d;
        }
        .sidebar-dark-redblue .main-header .navbar .sidebar-toggle:hover {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link {
            color: #6c757d;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link:hover {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link.active {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link.active:hover {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link:focus {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link:focus:hover {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link:active {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link:active:hover {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link:visited {
            color: #6c757d;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link:visited:hover {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link:visited:focus {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link:visited:focus:hover {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link:visited:active {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link:visited:active:hover {
            color: #495057;
        }
        .sidebar-dark-redblue .main-header .navbar .nav-item > .nav-link:visited:active:focus {
            color: #495057;
        }
        .sidebar-dark-redblue .payment-history-sidebar h4 {
            color: white;
            margin-bottom: 10px;
            font-size: 1rem;
            padding-bottom: 5px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .logout-link {
            display: block;
            padding: 10px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        .logout-link:hover {
            background-color: rgba(220,53,69,0.2);
            color: #fff;
        }
        .logout-link i {
            margin-right: 10px;
        }
        
        /* Sidebar Statistics Widget Styles */
        .sidebar-stats-container {
            padding: 10px;
            background-color: rgba(0,0,0,0.1);
            margin-bottom: 10px;
            border-top: 1px solid rgba(255,255,255,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-stat-widget {
            background-color: rgba(255,255,255,0.9);
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .sidebar-stat-widget:last-child {
            margin-bottom: 0;
        }
        .sidebar-stat-info {
            flex-grow: 1;
        }
        .sidebar-stat-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: bold;
        }
        .sidebar-stat-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: #333;
            margin-top: 2px;
        }
        .sidebar-stat-icon {
            font-size: 1.5rem;
            opacity: 0.8;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: white;
        }
        .sidebar-stat-icon.red { background-color: #dc3545; }
        .sidebar-stat-icon.green { background-color: #28a745; }
        .sidebar-stat-icon.yellow { background-color: #ffc107; color: #333; }
        
        @media (max-width: 768px) {
            .sidebar-stat-value {
                font-size: 0.9rem;
            }
            .sidebar-stat-icon {
                width: 30px;
                height: 30px;
                font-size: 1.2rem;
            }
        }

        /* Multi-Service Styles */
        .service-row {
            background-color: #f9f9f9;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            position: relative;
        }
        .remove-service-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            color: #dc3545;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1.2rem;
        }
        .remove-service-btn:hover {
            color: #a71d2a;
        }
        .total-display {
            font-size: 1.5rem;
            font-weight: bold;
            color: #004085;
            text-align: right;
            margin-top: 15px;
            padding: 10px;
            background-color: #e9f5ff;
            border-radius: 5px;
        }
        /* Print Prompt Modal Styles */
        .print-prompt-icon {
            font-size: 3rem;
            color: #004085;
            margin-bottom: 15px;
        }
        .print-prompt-title {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 10px;
        }
        .print-prompt-text {
            color: #6c757d;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini adminlte-style">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
    </nav>
    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-success elevation-4">
        <a href="cashier.php" class="brand-link">
            <img src="../uploads/csr.png" alt="CSR Logo">
            <span class="brand-text font-weight-light">Cashier</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 d-flex">
                <div class="image">
                    <img src="../uploads/cashier.jpg" class="img-circle elevation-2" alt="User Image">
                </div>
              <div class="info">
                    <a href="#" class="d-block"><?= htmlspecialchars($display_name) ?></a>
                </div>
            </div>
            
            <!-- Sidebar Stats Widgets -->
            <div class="sidebar-stats-container">
                <div class="sidebar-stat-widget">
                    <div class="sidebar-stat-info">
                        <div class="sidebar-stat-label">Acct. Receivables</div>
                        <div class="sidebar-stat-value">₱<?= formatAmount($totalBalance) ?></div>
                    </div>
                    <div class="sidebar-stat-icon red">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                </div>
                
                <div class="sidebar-stat-widget">
                    <div class="sidebar-stat-info">
                        <div class="sidebar-stat-label">Daily Collection</div>
                        <div class="sidebar-stat-value">₱<?= formatAmount($totalDailyCollection) ?></div>
                    </div>
                    <div class="sidebar-stat-icon green">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
                
                <div class="sidebar-stat-widget">
                    <div class="sidebar-stat-info">
                        <div class="sidebar-stat-label">Monthly Collection</div>
                        <div class="sidebar-stat-value">₱<?= formatAmount($totalMonthlyCollection) ?></div>
                    </div>
                    <div class="sidebar-stat-icon yellow">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
            
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="#student_management" class="nav-link active" data-toggle="tab">
                            <i class="nav-icon fas fa-user-graduate"></i>
                            <p>Student Management</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#customer_payments" class="nav-link" data-toggle="tab">
                            <i class="nav-icon fas fa-user-friends"></i>
                            <p>Customer Payments</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#default_fees" class="nav-link" data-toggle="tab">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>Default Fees</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </a>
                    </li>
                </ul>
                
            </nav>
        </div>
    </aside>
    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Cashier System</h1>
            </div>
        </div>
        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <!-- Toast Container -->
                <div class="toast-container"></div>
                
                <div class="tab-content">
                    
                    <!-- Student Management Tab -->
                    <div class="tab-pane fade show active" id="student_management">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Student Selection</h3>
                            </div>
                            <div class="card-body">
                                <form id="studentSearchForm">
                                    <div class="student-search-container">
                                        <div class="form-group">
                                            <label for="studentSearch" class="mr-2">Search Student:</label>
                                            <input type="text" class="form-control" id="studentSearch" name="student_search" placeholder="Type student name or ID number..." autocomplete="off">
                                            <div id="studentSearchResults" class="student-search-results"></div>
                                        </div>
                                        <input type="hidden" id="selectedStudentId" name="student_id" value="<?= isset($_GET['student_id']) ? $_GET['student_id'] : '' ?>">
                                        <button type="submit" class="btn btn-primary" id="viewStudentBtn" disabled>View Account</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div id="studentAccountContainer" class="student-account-container">
                            <!-- Student account information will be loaded here via AJAX -->
                            <!-- Note: To enable the Cancel button in this section, ensure your 'get_student_account.php' 
                                 file generates buttons with class 'cancel-student-payment-btn' and attributes data-id and data-or -->
                        </div>
                    </div>
                    
                    <!-- Customer Payments Tab -->
                    <div class="tab-pane fade" id="customer_payments">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Customer Payments</h3>
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addCustomerPaymentModal">
                                    <i class="fas fa-plus"></i> Record Customer Payment
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="search-box">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="customerPaymentSearch" placeholder="Search by customer name, OR number, or payment method...">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button" id="clearCustomerPaymentSearch">
                                                <i class="fas fa-times"></i> Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="customerPaymentsTable">
                                        <thead>
                                            <tr>
                                                <th>Customer Name</th>
                                                <th>Customer Type</th>
                                                <th>Services Count</th>
                                                <th>OR Number</th>
                                                <th>Total Amount</th>
                                                <th>Payment Date</th>
                                                <th>Payment Method</th>
                                                <th>Remarks</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($customerPayments)): ?>
                                            <tr>
                                                <td colspan="9" class="no-data-message">No customer payments found</td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach ($customerPayments as $payment): ?>
                                                <tr>
                                                    <td class="customer-name"><?= $payment['customer_name'] ?></td>
                                                    <td class="customer-type">
                                                        <?php
                                                        switch($payment['customer_type']) {
                                                            case 'individual': echo '<span class="badge bg-primary">Individual</span>'; break;
                                                            case 'organization': echo '<span class="badge bg-info">Organization</span>'; break;
                                                            case 'company': echo '<span class="badge bg-warning">Company</span>'; break;
                                                            default: echo '<span class="badge bg-secondary">Other</span>'; break;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="service-count">
                                                        <?php 
                                                            $count = isset($payment['item_count']) ? (int)$payment['item_count'] : 0;
                                                            if($count > 0) {
                                                                echo "<span class='badge badge-info'>$count Items</span>";
                                                            } else {
                                                                echo '<span class="text-muted">Old Record</span>';
                                                            }
                                                        ?>
                                                    </td>
                                                    <td class="or-number"><?= $payment['or_number'] ?></td>
                                                    <td class="amount">₱<?= formatAmount($payment['amount']) ?></td>
                                                    <td class="payment-date"><?= date('F d, Y', strtotime($payment['payment_date'])) ?></td>
                                                    <td class="payment-method"><?= $payment['payment_method'] ?></td>
                                                    <td class="remarks"><?= $payment['remarks'] ?: 'N/A' ?></td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <!-- Edit button removed as requested -->
                                                            <button type="button" class="btn btn-info btn-sm print-customer-receipt" 
                                                                    data-id="<?= $payment['id'] ?>"
                                                                    data-or="<?= $payment['or_number'] ?>">
                                                                <i class="fas fa-print"></i>
                                                            </button>
                                                            <!-- Changed to Cancel -->
                                                            <button type="button" class="btn btn-warning btn-sm cancel-customer-payment-btn" data-toggle="modal" data-target="#cancelTransactionModal" 
                                                                    data-id="<?= $payment['id'] ?>"
                                                                    data-type="customer"
                                                                    data-or="<?= $payment['or_number'] ?>">
                                                                <i class="fas fa-ban"></i> Cancel
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Default Fees Tab -->
                    <div class="tab-pane fade" id="default_fees">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Default Fees</h3>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addDefaultFeeModal">
                                    <i class="fas fa-plus"></i> Add Default Fee
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="search-box">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="defaultFeesSearch" placeholder="Search by fee name, type, academic year, or semester...">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button" id="clearDefaultFeesSearch">
                                                <i class="fas fa-times"></i> Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="defaultFeesTable">
                                        <thead>
                                            <tr>
                                                <th>Fee Name</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Academic Year</th>
                                                <th>Semester</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($defaultFees)): ?>
                                            <tr>
                                                <td colspan="8" class="no-data-message">No default fees found</td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach ($defaultFees as $fee): ?>
                                                <tr>
                                                    <td class="fee-name"><?= $fee['fee_name'] ?></td>
                                                    <td class="fee-type">
                                                        <?php
                                                        switch($fee['fee_type']) {
                                                            case 'miscellaneous': echo '<span class="badge bg-info">Miscellaneous</span>'; break;
                                                            case 'other': echo '<span class="badge bg-warning">Other</span>'; break;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="amount">₱<?= formatAmount($fee['amount']) ?></td>
                                                    <td class="academic-year"><?= $fee['academic_year'] ?></td>
                                                    <td class="semester"><?= $fee['semester'] ?></td>
                                                    <td class="category">
                                                        <?php
                                                        switch($fee['category']) {
                                                            case 'JHS': echo '<span class="badge category-badge category-jhs">JHS</span>'; break;
                                                            case 'SHS': echo '<span class="badge category-badge category-shs">SHS</span>'; break;
                                                            case 'Both': echo '<span class="badge category-badge category-both">Both</span>'; break;
                                                            case 'Kinder': echo '<span class="badge category-badge category-kinder">Kinder</span>'; break;
                                                            case 'Elementary': echo '<span class="badge category-badge category-elementary">Elementary</span>'; break;
                                                            case 'College': echo '<span class="badge category-badge category-college">College</span>'; break;
                                                            case 'All': echo '<span class="badge category-badge category-both">All</span>'; break; // Added
                                                            default: echo '<span class="badge bg-secondary">N/A</span>'; break;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($fee['is_active']): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editDefaultFeeModal" 
                                                                    data-id="<?= $fee['id'] ?>"
                                                                    data-fee-name="<?= $fee['fee_name'] ?>"
                                                                    data-fee-type="<?= $fee['fee_type'] ?>"
                                                                    data-amount="<?= $fee['amount'] ?>"
                                                                    data-academic-year="<?= $fee['academic_year'] ?>"
                                                                    data-semester="<?= $fee['semester'] ?>"
                                                                    data-category="<?= $fee['category'] ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteDefaultFeeModal" 
                                                                    data-id="<?= $fee['id'] ?>"
                                                                    data-name="<?= $fee['fee_name'] ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination for Default Fees -->
                                <?php if ($totalPages > 1): ?>
                                <div class="pagination-container">
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $page - 1 ?>&tab=default_fees">Previous</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?>&tab=default_fees"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $page + 1 ?>&tab=default_fees">Next</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
    
    <!-- Add Fee Modal -->
    <div class="modal fade" id="addFeeModal" tabindex="-1" role="dialog" aria-labelledby="addFeeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="addFeeModalLabel">Add Student Fee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addFeeForm">
                    <input type="hidden" name="action" value="add_fee">
                    <input type="hidden" id="addFeeStudentId" name="studentId">
                    <input type="hidden" name="current_tab" id="current_tab" value="student_management">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="feeType" class="required-field">Fee Type</label>
                            <select class="form-control" id="feeType" name="feeType" required>
                                <option value="">-- Select Fee Type --</option>
                                <option value="tuition">Tuition</option>
                                <option value="miscellaneous">Miscellaneous</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="feeName" class="required-field">Fee Name</label>
                            <input type="text" class="form-control" id="feeName" name="feeName" required>
                        </div>
                        <div class="form-group">
                            <label for="baseAmount" class="required-field">Amount</label>
                            <input type="number" class="form-control amount-input" id="baseAmount" name="baseAmount" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="academicYear" class="required-field">Academic Year</label>
                            <select class="form-control" id="academicYear" name="academicYear" required>
                                <option value="">-- Select Academic Year --</option>
                                <?php foreach ($activeAcademicYears as $academicYear): ?>
                                <option value="<?= $academicYear['academic_year'] ?>"><?= $academicYear['academic_year'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="semester" class="required-field">Semester</label>
                            <select class="form-control" id="semester" name="semester" required>
                                <option value="">-- Select Semester --</option>
                                <option value="1st">1st Semester</option>
                                <option value="2nd">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Fee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Default Fee Modal -->
    <div class="modal fade" id="addDefaultFeeModal" tabindex="-1" role="dialog" aria-labelledby="addDefaultFeeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="addDefaultFeeModalLabel">Add Default Fee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addDefaultFeeForm">
                    <input type="hidden" name="action" value="add_default_fee">
                    <input type="hidden" name="current_tab" id="current_tab" value="default_fees">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="defaultFeeType" class="required-field">Fee Type</label>
                            <select class="form-control" id="defaultFeeType" name="feeType" required>
                                <option value="">-- Select Fee Type --</option>
                                <option value="miscellaneous">Miscellaneous</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="defaultFeeName" class="required-field">Fee Name</label>
                            <input type="text" class="form-control" id="defaultFeeName" name="feeName" required>
                        </div>
                        <div class="form-group">
                            <label for="defaultFeeAmount" class="required-field">Amount</label>
                            <input type="number" class="form-control amount-input" id="defaultFeeAmount" name="amount" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="defaultAcademicYear" class="required-field">Academic Year</label>
                            <select class="form-control" id="defaultAcademicYear" name="academicYear" required>
                                <option value="">-- Select Academic Year --</option>
                                <?php foreach ($allAcademicYears as $academicYear): ?>
                                <option value="<?= $academicYear['academic_year'] ?>"><?= $academicYear['academic_year'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="defaultSemester" class="required-field">Semester</label>
                            <select class="form-control" id="defaultSemester" name="semester" required>
                                <option value="">-- Select Semester --</option>
                                <option value="1st">1st Semester</option>
                                <option value="2nd">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="defaultFeeCategory" class="required-field">Category</label>
                            <select class="form-control" id="defaultFeeCategory" name="category" required>
                                <option value="">-- Select Category --</option>
                                <option value="Kinder">Kinder</option>
                                <option value="Elementary">Elementary</option>
                                <option value="JHS">Junior High School</option>
                                <option value="SHS">Senior High School</option>
                                <option value="College">College</option>
                                <!-- Added All Option -->
                                <option value="All">All</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Default Fee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Default Fee Modal -->
    <div class="modal fade" id="editDefaultFeeModal" tabindex="-1" role="dialog" aria-labelledby="editDefaultFeeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="editDefaultFeeModalLabel">Edit Default Fee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editDefaultFeeForm">
                    <input type="hidden" name="action" value="edit_default_fee">
                    <input type="hidden" id="editDefaultFeeId" name="feeId">
                    <input type="hidden" name="current_tab" id="current_tab" value="default_fees">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="editDefaultFeeName" class="required-field">Fee Name</label>
                            <input type="text" class="form-control" id="editDefaultFeeName" name="feeName" required>
                        </div>
                        <div class="form-group">
                            <label for="editDefaultFeeType" class="required-field">Fee Type</label>
                            <select class="form-control" id="editDefaultFeeType" name="feeType" required>
                                <option value="">-- Select Fee Type --</option>
                                <option value="miscellaneous">Miscellaneous</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editDefaultFeeAmount" class="required-field">Amount</label>
                            <input type="number" class="form-control amount-input" id="editDefaultFeeAmount" name="amount" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="editDefaultAcademicYear" class="required-field">Academic Year</label>
                            <select class="form-control" id="editDefaultAcademicYear" name="academicYear" required>
                                <option value="">-- Select Academic Year --</option>
                                <?php foreach ($allAcademicYears as $academicYear): ?>
                                <option value="<?= $academicYear['academic_year'] ?>"><?= $academicYear['academic_year'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editDefaultSemester" class="required-field">Semester</label>
                            <select class="form-control" id="editDefaultSemester" name="semester" required>
                                <option value="">-- Select Semester --</option>
                                <option value="1st">1st Semester</option>
                                <option value="2nd">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editDefaultFeeCategory" class="required-field">Category</label>
                            <select class="form-control" id="editDefaultFeeCategory" name="category" required>
                                <option value="">-- Select Category --</option>
                                <option value="Kinder">Kinder</option>
                                <option value="Elementary">Elementary</option>
                                <option value="JHS">Junior High School</option>
                                <option value="SHS">Senior High School</option>
                                <option value="College">College</option>
                                <option value="Both">Both</option>
                                <option value="All">All</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Default Fee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Apply Default Fees Modal -->
    <div class="modal fade" id="applyDefaultFeesModal" tabindex="-1" role="dialog" aria-labelledby="applyDefaultFeesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="applyDefaultFeesModalLabel">Apply Default Fees</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="applyDefaultFeesForm">
                    <input type="hidden" name="action" value="apply_default_fees">
                    <input type="hidden" id="applyStudentId" name="studentId">
                    <input type="hidden" name="current_tab" id="current_tab" value="student_management">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="applyAcademicYear" class="required-field">Academic Year</label>
                            <select class="form-control" id="applyAcademicYear" name="academicYear" required>
                                <option value="">-- Select Academic Year --</option>
                                <?php foreach ($activeAcademicYears as $academicYear): ?>
                                <option value="<?= $academicYear['academic_year'] ?>"><?= $academicYear['academic_year'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="applySemester" class="required-field">Semester</label>
                            <select class="form-control" id="applySemester" name="semester" required>
                                <option value="">-- Select Semester --</option>
                                <option value="1st">1st Semester</option>
                                <option value="2nd">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="applyCategory" class="required-field">Category</label>
                            <select class="form-control" id="applyCategory" name="category" required>
                                <option value="">-- Select Category --</option>
                                <option value="Kinder">Kinder</option>
                                <option value="Elementary">Elementary</option>
                                <option value="JHS">Junior High School</option>
                                <option value="SHS">Senior High School</option>
                                <option value="College">College</option>
                            </select>
                        </div>
                        
                        <!-- Tabs for fee types -->
                        <ul class="nav nav-tabs fee-type-tabs" id="feeTypeTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="misc-tab" data-toggle="tab" href="#misc-fees" role="tab">Miscellaneous Fees</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="other-tab" data-toggle="tab" href="#other-fees" role="tab">Other Fees</a>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="feeTypeTabContent">
                            <!-- Miscellaneous Fees Tab -->
                            <div class="tab-pane fade show active" id="misc-fees" role="tabpanel">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="selectAllMiscFees">
                                    <label class="form-check-label" for="selectAllMiscFees">Select All Miscellaneous Fees</label>
                                </div>
                                <div class="default-fees-container">
                                    <div id="miscFeesList">
                                        <!-- Miscellaneous fees will be loaded here -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Other Fees Tab -->
                            <div class="tab-pane fade" id="other-fees" role="tabpanel">
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="selectAllOtherFees">
                                    <label class="form-check-label" for="selectAllOtherFees">Select All Other Fees</label>
                                </div>
                                <div class="default-fees-container">
                                    <div id="otherFeesList">
                                        <!-- Other fees will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Apply Selected Fees</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Fee Modal -->
    <div class="modal fade" id="editFeeModal" tabindex="-1" role="dialog" aria-labelledby="editFeeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="editFeeModalLabel">Edit Student Fee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editFeeForm">
                    <input type="hidden" name="action" value="edit_fee">
                    <input type="hidden" id="editFeeId" name="feeId">
                    <input type="hidden" id="editStudentFeeId" name="studentFeeId">
                    <input type="hidden" name="current_tab" id="current_tab" value="student_management">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="editBaseAmount" class="required-field">Amount</label>
                            <input type="number" class="form-control amount-input" id="editBaseAmount" name="baseAmount" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Fee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Fee Modal -->
    <div class="modal fade" id="deleteFeeModal" tabindex="-1" role="dialog" aria-labelledby="deleteFeeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title" id="deleteFeeModalLabel">Delete Fee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this fee? This action cannot be undone.</p>
                    <input type="hidden" id="deleteFeeId">
                    <p><strong>Fee:</strong> <span id="deleteFeeName"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteFee" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Default Fee Modal -->
    <div class="modal fade" id="deleteDefaultFeeModal" tabindex="-1" role="dialog" aria-labelledby="deleteDefaultFeeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title" id="deleteDefaultFeeModalLabel">Delete Default Fee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this default fee? This action cannot be undone.</p>
                    <input type="hidden" id="deleteDefaultFeeId">
                    <p><strong>Fee:</strong> <span id="deleteDefaultFeeName"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteDefaultFee" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cancel Transaction Modal (Replaces Delete) -->
    <div class="modal fade" id="cancelTransactionModal" tabindex="-1" role="dialog" aria-labelledby="cancelTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="cancelTransactionModalLabel">Cancel Transaction</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>This action will permanently cancel and delete the transaction record.</p>
                    <div class="form-group">
                        <label for="cancelCode">Verification Code</label>
                        <input type="password" class="form-control" id="cancelCode" placeholder="Enter code to confirm">
                    </div>
                    <input type="hidden" id="cancelPaymentId">
                    <input type="hidden" id="cancelPaymentType"> <!-- 'student' or 'customer' -->
                    <p><strong>OR Number:</strong> <span id="cancelPaymentOR"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="confirmCancelTransaction" class="btn btn-danger">Confirm Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal with Allocation (Auto Allocate button removed) -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title" id="addPaymentModalLabel">Record Student Payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addPaymentForm">
                    <input type="hidden" name="action" value="add_payment">
                    <input type="hidden" id="addPaymentStudentId" name="studentId">
                    <input type="hidden" name="current_tab" id="current_tab" value="student_management">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="paymentAmount" class="required-field">Amount</label>
                                    <input type="number" class="form-control amount-input" id="paymentAmount" name="amount" min="0" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="discount">Discount (₱)</label>
                                    <input type="number" class="form-control amount-input" id="discount" name="discount" min="0" step="0.01" value="0">
                                    <small class="form-text text-muted">Enter discount amount to apply to this payment</small>
                                </div>
                                <div class="form-group">
                                    <label for="paymentDate" class="required-field">Payment Date</label>
                                    <input type="date" class="form-control" id="paymentDate" name="paymentDate" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="orNumber" class="required-field">OR Number</label>
                                    <input type="text" class="form-control" id="orNumber" name="orNumber" required>
                                </div>
                                <div class="form-group">
                                    <label for="paymentMethod" class="required-field">Payment Method</label>
                                    <select class="form-control" id="paymentMethod" name="paymentMethod" required>
                                        <option value="">-- Select Payment Method --</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Bank Transfer/Deposit">Bank Transfer/Deposit</option>
                                        <option value="Check">Check</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                                </div>
                                <div class="alert alert-info">
                                    <strong>Net Amount:</strong> ₱<span id="netAmount">0.00</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fee-allocation-container">
                                    <div class="fee-allocation-title">Allocate Payment to Fees</div>
                                    <!-- Auto Allocate button removed -->
                                    <div id="feeAllocationList">
                                        <!-- Fee allocation items will be loaded here -->
                                    </div>
                                    <div class="allocation-summary">
                                        <div class="allocation-summary-row">
                                            <span class="allocation-summary-label">Total Allocated:</span>
                                            <span class="allocation-summary-value" id="totalAllocated">₱0.00</span>
                                        </div>
                                        <div class="allocation-summary-row">
                                            <span class="allocation-summary-label">Unallocated:</span>
                                            <span class="allocation-summary-value unallocated-amount" id="unallocatedAmount">₱0.00</span>
                                        </div>
                                        <div class="allocation-summary-row">
                                            <span class="allocation-summary-label">Net Amount:</span>
                                            <span class="allocation-summary-value" id="netAmountSummary">₱0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Customer Payment Modal (UPDATED FOR MULTI-SERVICE) -->
    <div class="modal fade" id="addCustomerPaymentModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title" id="addCustomerPaymentModalLabel">Record Customer Payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addCustomerPaymentForm">
                    <input type="hidden" name="action" value="add_customer_payment">
                    <input type="hidden" name="current_tab" id="current_tab" value="customer_payments">
                    <input type="hidden" id="customerPaymentTotal" name="amount" value="0">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customerName" class="required-field">Customer Name</label>
                                    <input type="text" class="form-control" id="customerName" name="customerName" required>
                                </div>
                                <div class="form-group">
                                    <label for="customerType" class="required-field">Customer Type</label>
                                    <select class="form-control" id="customerType" name="customerType" required>
                                        <option value="">-- Select Customer Type --</option>
                                        <option value="individual">Individual</option>
                                        <option value="organization">Organization</option>
                                        <option value="company">Company</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="customerPaymentDate" class="required-field">Payment Date</label>
                                    <input type="date" class="form-control" id="customerPaymentDate" name="paymentDate" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="customerORNumber" class="required-field">OR Number</label>
                                    <input type="text" class="form-control" id="customerORNumber" name="orNumber" required>
                                </div>
                                <div class="form-group">
                                    <label for="customerPaymentMethod" class="required-field">Payment Method</label>
                                    <select class="form-control" id="customerPaymentMethod" name="paymentMethod" required>
                                        <option value="">-- Select Payment Method --</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Bank Transfer/Deposit">Bank Transfer/Deposit</option>
                                        <option value="Check">Check</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="customerRemarks">Remarks</label>
                                    <textarea class="form-control" id="customerRemarks" name="remarks" rows="3"></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="required-field">Services & Items</label>
                                    <div id="customerServicesContainer">
                                        <!-- Dynamic Rows will appear here -->
                                    </div>
                                    <button type="button" class="btn btn-sm btn-secondary mt-2" id="addCustomerServiceBtn">
                                        <i class="fas fa-plus"></i> Add Service
                                    </button>
                                </div>
                                
                                <div class="total-display">
                                    Total: ₱<span id="customerGrandTotal">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Statement of Account Modal -->
    <div class="modal fade" id="statementModal" tabindex="-1" role="dialog" aria-labelledby="statementModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="statementModalLabel">Statement of Account</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="statementContent">
                        <!-- Statement content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="printStatementBtn">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title" id="receiptModalLabel">Generate Payment Receipt</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="receiptForm">
                        <div class="form-group">
                            <label for="paymentSelect">Select Payment</label>
                            <select class="form-control" id="paymentSelect" required>
                                <option value="">-- Select Payment --</option>
                                <!-- Payment options will be loaded here -->
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="generateReceiptBtn">
                        <i class="fas fa-receipt"></i> Generate Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Fee Assessment Modal -->
    <div class="modal fade" id="assessmentModal" tabindex="-1" role="dialog" aria-labelledby="assessmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="assessmentModalLabel">Fee Assessment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="assessmentContent">
                        <!-- Assessment content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info" id="printAssessmentBtn">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Prompt Modal (New Design) -->
    <div class="modal fade" id="printPromptModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="printPromptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="print-prompt-icon">
                        <i class="fas fa-print"></i>
                    </div>
                    <h4 class="print-prompt-title">Print Receipt?</h4>
                    <p class="print-prompt-text">The transaction has been recorded successfully. Would you like to print the official receipt now?</p>
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-secondary mr-2" id="printPromptNo">No</button>
                        <button type="button" class="btn btn-primary" id="printPromptYes">
                            <i class="fas fa-check"></i> Yes, Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Required Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script>
    // Pass PHP services array to JavaScript
    const availableServices = <?php echo json_encode($services); ?>;

 $(function() {
    // Show toast notification
    function showToast(message, type = 'success') {
        const toastId = 'toast-' + Date.now();
        const toastClass = type === 'success' ? 'toast-success' : 'toast-error';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const toastHtml = `
            <div id="${toastId}" class="toast ${toastClass}" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                <div class="toast-header">
                    <i class="fas ${icon} mr-2"></i>
                    <strong class="mr-auto">Cashier</strong>
                    <small>Just now</small>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        $('.toast-container').append(toastHtml);
        
        const toastElement = $(`#${toastId}`);
        toastElement.toast('show');
        
        toastElement.on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }
    
    // Check for session messages and show toasts
    <?php if(isset($_SESSION['message'])): ?>
        showToast('<?=$_SESSION['message']?>', '<?=$_SESSION['message_type']?>');
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        showToast('<?=$_SESSION['error']?>', 'error');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    // Format amount function for JavaScript
    function formatAmount(amount) {
        // Always format with 2 decimals
        return '₱' + parseFloat(amount).toFixed(2);
    }
    
    // Handle amount input formatting
    function formatAmountInput(input) {
        let value = parseFloat(input.val());
        if (!isNaN(value)) {
            // Always format with 2 decimals
            input.val(value.toFixed(2));
        }
    }
    
    // Apply formatting to all amount inputs on blur
    $(document).on('blur', '.amount-input', function() {
        formatAmountInput($(this));
    });
    
    // Student search functionality with AJAX
    $('#studentSearch').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        const resultsContainer = $('#studentSearchResults');
        
        if (searchTerm.length < 2) {
            resultsContainer.hide();
            $('#viewStudentBtn').prop('disabled', true);
            return;
        }
        
        $.ajax({
            url: 'search_students.php',
            type: 'GET',
            data: { term: searchTerm },
            dataType: 'json',
            success: function(data) {
                if (data.length === 0) {
                    resultsContainer.html('<div class="student-search-result">No students found</div>');
                } else {
                    let resultsHtml = '';
                    data.forEach(student => {
                        resultsHtml += `<div class="student-search-result" data-id="${student.id}">
                            ${student.fullName} (${student.idNumber})
                        </div>`;
                    });
                    resultsContainer.html(resultsHtml);
                }
                resultsContainer.show();
            },
            error: function() {
                resultsContainer.html('<div class="student-search-result">Error searching students</div>');
                resultsContainer.show();
            }
        });
    });
    
    // Handle student selection
    $(document).on('click', '.student-search-result', function() {
        const studentId = $(this).data('id');
        const studentName = $(this).text();
        
        $('#studentSearch').val(studentName);
        $('#selectedStudentId').val(studentId);
        $('#studentSearchResults').hide();
        $('#viewStudentBtn').prop('disabled', false);
    });
    
    // Hide search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.student-search-container').length) {
            $('#studentSearchResults').hide();
        }
    });
    
    // Handle student search form submission with AJAX
    $('#studentSearchForm').on('submit', function(e) {
        e.preventDefault();
        
        const studentId = $('#selectedStudentId').val();
        
        if (!studentId) {
            showToast('Please select a student', 'error');
            return;
        }
        
        // Load student account information via AJAX
        loadStudentAccount(studentId);
    });
    
    // Function to load student account information via AJAX
    function loadStudentAccount(studentId) {
        // Show loading indicator
        $('#studentAccountContainer').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading student account...</p>
            </div>
        `);
        
        // Make AJAX request to get student account information
        $.ajax({
            url: 'get_student_account.php',
            type: 'GET',
            data: { student_id: studentId },
            dataType: 'html',
            success: function(data) {
                $('#studentAccountContainer').html(data);
                $('#studentAccountContainer').addClass('active');
                
                // Update student ID in all modals
                $('#addFeeStudentId').val(studentId);
                $('#addPaymentStudentId').val(studentId);
                $('#applyStudentId').val(studentId);
                
                // Load payment options for receipt modal
                loadPaymentOptions(studentId);
            },
            error: function() {
                $('#studentAccountContainer').html('<div class="alert alert-danger">Error loading student account. Please try again.</div>');
            }
        });
    }
    
    // Function to load payment options for receipt modal
    function loadPaymentOptions(studentId) {
        $.ajax({
            url: 'get_student_payments.php',
            type: 'GET',
            data: { student_id: studentId },
            dataType: 'json',
            success: function(data) {
                let optionsHtml = '<option value="">-- Select Payment --</option>';
                
                if (data.length > 0) {
                    data.forEach(payment => {
                        optionsHtml += `<option value="${payment.id}">${payment.or_number} - ${formatAmount(parseFloat(payment.amount - payment.discount))} (${payment.payment_date})</option>`;
                    });
                } else {
                    optionsHtml += '<option value="">No payments found</option>';
                }
                
                $('#paymentSelect').html(optionsHtml);
            },
            error: function() {
                $('#paymentSelect').html('<option value="">Error loading payments</option>');
            }
        });
    }
    
    // --- NEW: Customer Payment Multi-Service Logic ---

    // Function to add a service row
    function addCustomerServiceRow() {
        const rowId = Date.now();
        let serviceOptions = '<option value="">-- Select Service --</option>';
        
        if (availableServices && availableServices.length > 0) {
            availableServices.forEach(service => {
                const price = service.price ? parseFloat(service.price).toFixed(2) : '0.00';
                serviceOptions += `<option value="${service.id}" data-price="${price}" data-name="${service.name}">${service.name} (₱${price})</option>`;
            });
        } else {
            serviceOptions += '<option value="">No services available</option>';
        }

        const html = `
            <div class="service-row" id="serviceRow-${rowId}">
                <button type="button" class="remove-service-btn" onclick="removeCustomerServiceRow(${rowId})">&times;</button>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <select class="form-control customer-service-select" name="temp_service[]" required>
                            ${serviceOptions}
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <input type="number" class="form-control customer-service-qty" name="temp_qty[]" value="1" min="1" required placeholder="Qty">
                    </div>
                    <div class="col-md-3 mb-2">
                        <input type="text" class="form-control customer-service-price" name="temp_price[]" value="0.00" readonly placeholder="Price">
                    </div>
                </div>
                <!-- Hidden inputs for final submission -->
                <input type="hidden" class="real-service-id" name="services[${rowId}][id]">
                <input type="hidden" class="real-service-name" name="services[${rowId}][name]">
                <input type="hidden" class="real-service-qty" name="services[${rowId}][qty]">
                <input type="hidden" class="real-service-price" name="services[${rowId}][price]">
            </div>
        `;
        
        $('#customerServicesContainer').append(html);
    }

    // Global function for remove button access
    window.removeCustomerServiceRow = function(id) {
        $(`#serviceRow-${id}`).remove();
        calculateCustomerTotal();
    };

    // Add row on button click
    $('#addCustomerServiceBtn').on('click', function() {
        addCustomerServiceRow();
    });

    // Handle changes in service selection and quantity
    $(document).on('change', '.customer-service-select', function() {
        const row = $(this).closest('.service-row');
        const selectedOption = $(this).find('option:selected');
        const price = selectedOption.data('price') || 0;
        const name = selectedOption.data('name') || '';
        const serviceId = selectedOption.val();

        // Update visible price
        row.find('.customer-service-price').val(parseFloat(price).toFixed(2));
        
        // Update hidden fields
        row.find('.real-service-id').val(serviceId);
        row.find('.real-service-name').val(name);
        row.find('.real-service-price').val(price);
        
        calculateCustomerTotal();
    });

    $(document).on('input', '.customer-service-qty', function() {
        const row = $(this).closest('.service-row');
        row.find('.real-service-qty').val($(this).val());
        calculateCustomerTotal();
    });

    // Calculate total for customer payment
    function calculateCustomerTotal() {
        let total = 0;
        
        $('.service-row').each(function() {
            const price = parseFloat($(this).find('.real-service-price').val()) || 0;
            const qty = parseInt($(this).find('.real-service-qty').val()) || 0;
            total += (price * qty);
        });
        
        $('#customerGrandTotal').text(total.toFixed(2));
        $('#customerPaymentTotal').val(total.toFixed(2));
    }

    // Initialize with one row when modal opens
    $('#addCustomerPaymentModal').on('show.bs.modal', function() {
        $('#customerServicesContainer').empty();
        addCustomerServiceRow();
    });
    
    // --- End New Logic ---

    // Edit fee modal data population
    $('#editFeeModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const feeId = button.data('id');
        const studentFeeId = button.data('student-fee-id');
        const baseAmount = button.data('base-amount');
        
        $('#editFeeId').val(feeId);
        $('#editStudentFeeId').val(studentFeeId);
        $('#editBaseAmount').val(baseAmount);
    });
    
    // Edit default fee modal data population
    $('#editDefaultFeeModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const feeId = button.data('id');
        const feeName = button.data('fee-name');
        const feeType = button.data('fee-type');
        const amount = button.data('amount');
        const academicYear = button.data('academic-year');
        const semester = button.data('semester');
        const category = button.data('category');
        
        $('#editDefaultFeeId').val(feeId);
        $('#editDefaultFeeName').val(feeName);
        $('#editDefaultFeeType').val(feeType);
        $('#editDefaultFeeAmount').val(amount);
        $('#editDefaultAcademicYear').val(academicYear);
        $('#editDefaultSemester').val(semester);
        $('#editDefaultFeeCategory').val(category);
    });
    
    // Delete fee modal
    $('#deleteFeeModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const feeId = button.data('id');
        const feeName = button.data('name');
        
        $('#deleteFeeId').val(feeId);
        $('#deleteFeeName').text(feeName);
    });
    
    // Delete default fee modal
    $('#deleteDefaultFeeModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const feeId = button.data('id');
        const feeName = button.data('name');
        
        $('#deleteDefaultFeeId').val(feeId);
        $('#deleteDefaultFeeName').text(feeName);
    });

    // Cancel Transaction Modal Setup (Replaces Delete Payment)
    
    // Customer Cancel Button Handler
    $('.cancel-customer-payment-btn').on('click', function() {
        const button = $(this);
        const paymentId = button.data('id');
        const orNumber = button.data('or');
        
        $('#cancelPaymentId').val(paymentId);
        $('#cancelPaymentType').val('customer');
        $('#cancelPaymentOR').text(orNumber);
        $('#cancelCode').val(''); // Clear previous code
    });

    // Student Cancel Button Handler (Delegated for dynamic content)
    $(document).on('click', '.cancel-student-payment-btn', function() {
        const button = $(this);
        const paymentId = button.data('id');
        const orNumber = button.data('or');
        
        $('#cancelPaymentId').val(paymentId);
        $('#cancelPaymentType').val('student');
        $('#cancelPaymentOR').text(orNumber);
        $('#cancelCode').val(''); // Clear previous code
        
        // Show modal manually since data-toggle might not work on dynamic elements without delegation
        $('#cancelTransactionModal').modal('show');
    });

    // Generate Statement of Account
    $(document).on('click', '#generateStatementBtn', function() {
        const studentId = $('#addFeeStudentId').val();
        
        if (!studentId) {
            showToast('Please select a student first', 'error');
            return;
        }
        
        // Direct print without showing modal
        window.open('print_statement.php?student_id=' + studentId, '_blank');
    });
    
    // Print Statement of Account
    $('#printStatementBtn').on('click', function() {
        const studentId = $('#addFeeStudentId').val();
        
        if (!studentId) {
            showToast('Please select a student first', 'error');
            return;
        }
        
        window.open('print_statement.php?student_id=' + studentId, '_blank');
    });
    
    // Generate Receipt
    $('#generateReceiptBtn').on('click', function() {
        const paymentId = $('#paymentSelect').val();
        
        if (!paymentId) {
            showToast('Please select a payment', 'error');
            return;
        }
        
        // Direct print without showing modal
        window.open('print_receipt.php?payment_id=' + paymentId, '_blank');
        $('#receiptModal').modal('hide');
    });
    
    // Generate Fee Assessment
    $(document).on('click', '#generateAssessmentBtn', function() {
        const studentId = $('#addFeeStudentId').val();
        
        if (!studentId) {
            showToast('Please select a student first', 'error');
            return;
        }
        
        // Direct print without showing modal
        window.open('print_assessment.php?student_id=' + studentId, '_blank');
    });
    
    // Print Fee Assessment
    $('#printAssessmentBtn').on('click', function() {
        const studentId = $('#addFeeStudentId').val();
        
        if (!studentId) {
            showToast('Please select a student first', 'error');
            return;
        }
        
        window.open('print_assessment.php?student_id=' + studentId, '_blank');
    });
    
    // Print Customer Receipt (direct print)
    $(document).on('click', '.print-customer-receipt', function() {
        const paymentId = $(this).data('id');
        window.open('customerprint.php?payment_id=' + paymentId, '_blank');
    });
    
    // Variable to track loading state
    let isLoadingDefaultFees = false;
    let pendingDefaultFeesLoad = null;
    
    // Function to load default fees
    function loadDefaultFees() {
        const academicYear = $('#applyAcademicYear').val();
        const semester = $('#applySemester').val();
        const category = $('#applyCategory').val();
        
        if (!academicYear || !semester || !category) {
            $('#miscFeesList').html('<div class="no-data-message">Please select academic year, semester, and category</div>');
            $('#otherFeesList').html('<div class="no-data-message">Please select academic year, semester, and category</div>');
            return;
        }
        
        // If we're already loading, store this request for later
        if (isLoadingDefaultFees) {
            pendingDefaultFeesLoad = { academicYear, semester, category };
            return;
        }
        
        // Set loading state
        isLoadingDefaultFees = true;
        
        // Show loading indicator
        $('#miscFeesList').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading miscellaneous fees...</p>
            </div>
        `);
        
        $('#otherFeesList').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading other fees...</p>
            </div>
        `);
        
        // Get default fees for the selected academic year, semester, and category via AJAX
        $.ajax({
            url: 'get_default_fees.php',
            type: 'GET',
            data: { 
                academic_year: academicYear,
                semester: semester,
                category: category
            },
            dataType: 'json',
            success: function(data) {
                // Check for error response
                if (data.error) {
                    console.error('Server error:', data.error);
                    $('#miscFeesList').html('<div class="no-data-message">Error: ' + data.error + '</div>');
                    $('#otherFeesList').html('<div class="no-data-message">Error: ' + data.error + '</div>');
                    return;
                }
                
                // Group fees by type
                const miscFees = data.filter(fee => fee.feeType === 'miscellaneous');
                const otherFees = data.filter(fee => fee.feeType === 'other');
                
                // Build HTML for miscellaneous fees
                if (miscFees.length === 0) {
                    $('#miscFeesList').html('<div class="no-data-message">No miscellaneous fees found for the selected academic year, semester, and category</div>');
                } else {
                    let miscHtml = '';
                    miscFees.forEach(fee => {
                        miscHtml += `
                            <div class="default-fee-item">
                                <input type="checkbox" class="default-fee-checkbox misc-fee-checkbox" name="default_fees[]" value="${fee.id}" id="fee-${fee.id}">
                                <div class="default-fee-details">
                                    <div class="default-fee-name">${fee.feeName}</div>
                                    <div class="default-fee-amount">${formatAmount(parseFloat(fee.amount))}</div>
                                    <div class="default-fee-type">${fee.feeType.charAt(0).toUpperCase() + fee.feeType.slice(1)} - ${fee.category}</div>
                                </div>
                            </div>
                        `;
                    });
                    $('#miscFeesList').html(miscHtml);
                }
                
                // Build HTML for other fees
                if (otherFees.length === 0) {
                    $('#otherFeesList').html('<div class="no-data-message">No other fees found for the selected academic year, semester, and category</div>');
                } else {
                    let otherHtml = '';
                    otherFees.forEach(fee => {
                        otherHtml += `
                            <div class="default-fee-item">
                                <input type="checkbox" class="default-fee-checkbox other-fee-checkbox" name="default_fees[]" value="${fee.id}" id="fee-${fee.id}">
                                <div class="default-fee-details">
                                    <div class="default-fee-name">${fee.feeName}</div>
                                    <div class="default-fee-amount">${formatAmount(parseFloat(fee.amount))}</div>
                                    <div class="default-fee-type">${fee.feeType.charAt(0).toUpperCase() + fee.feeType.slice(1)} - ${fee.category}</div>
                                </div>
                            </div>
                        `;
                    });
                    $('#otherFeesList').html(otherHtml);
                }
                
                // Check if there's a pending request
                if (pendingDefaultFeesLoad) {
                    const pending = pendingDefaultFeesLoad;
                    pendingDefaultFeesLoad = null;
                    isLoadingDefaultFees = false;
                    loadDefaultFees();
                    return;
                }
                
                // Reset loading state
                isLoadingDefaultFees = false;
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                $('#miscFeesList').html('<div class="no-data-message">Error loading default fees. Please try again.</div>');
                $('#otherFeesList').html('<div class="no-data-message">Error loading default fees. Please try again.</div>');
                
                // Check if there's a pending request
                if (pendingDefaultFeesLoad) {
                    const pending = pendingDefaultFeesLoad;
                    pendingDefaultFeesLoad = null;
                    isLoadingDefaultFees = false;
                    loadDefaultFees();
                    return;
                }
                
                // Reset loading state
                isLoadingDefaultFees = false;
            }
        });
    }
    
    // Select all miscellaneous fees
    $(document).on('change', '#selectAllMiscFees', function() {
        $('.misc-fee-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // Select all other fees
    $(document).on('change', '#selectAllOtherFees', function() {
        $('.other-fee-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // Update individual checkbox states when "select all" is used
    $(document).on('change', '.misc-fee-checkbox', function() {
        // Check if all miscellaneous fees are selected
        const allMiscSelected = $('.misc-fee-checkbox').length === $('.misc-fee-checkbox:checked').length;
        $('#selectAllMiscFees').prop('checked', allMiscSelected);
    });
    
    $(document).on('change', '.other-fee-checkbox', function() {
        // Check if all other fees are selected
        const allOtherSelected = $('.other-fee-checkbox').length === $('.other-fee-checkbox:checked').length;
        $('#selectAllOtherFees').prop('checked', allOtherSelected);
    });
    
    // Load default fees when academic year, semester, or category changes in apply modal
    $('#applyAcademicYear, #applySemester, #applyCategory').on('change', function() {
        loadDefaultFees();
    });
    
    // Load default fees when modal is shown
    $('#applyDefaultFeesModal').on('show.bs.modal', function() {
        // Reset to 'student_management' tab
        $(this).find('input[name="current_tab"]').val('student_management');
        
        // Load default fees after a short delay to ensure modal is fully rendered
        setTimeout(function() {
            loadDefaultFees();
        }, 100);
    });
    
    // Track active tab and update current_tab hidden fields
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var tabId = $(e.target).attr("href").substring(1);
        
        // Store active tab in session via AJAX
        $.ajax({
            type: 'POST',
            url: 'cashier.php',
            data: {active_tab: tabId},
            async: false
        });
        
        // Update current_tab hidden fields in all forms
        $('input[name="current_tab"]').val(tabId);
    });
    
    // Set current tab on page load - Default to Student Management (Dashboard removed)
    $(document).ready(function() {
        // Always set student_management as active on page load
        var currentTab = 'student_management';
        $('input[name="current_tab"]').val(currentTab);
        
        // Activate the student_management tab
        $('.nav-link[href="#student_management"]').tab('show');
        
        // If there's a hash in the URL and it's not student_management, activate that tab
        var hash = window.location.hash.substring(1);
        if (hash && hash !== 'student_management') {
            $('.nav-link[href="#' + hash + '"]').tab('show');
            $('input[name="current_tab"]').val(hash);
        }
        
        // If student_id is in URL, load student account
        const urlParams = new URLSearchParams(window.location.search);
        const studentId = urlParams.get('student_id');
        if (studentId) {
            // Set student ID in form
            $('#selectedStudentId').val(studentId);
            // Load student account
            loadStudentAccount(studentId);
            // Activate student management tab
            $('.nav-link[href="#student_management"]').tab('show');
        }
    });
    
    // Customer Payments Search Functionality
    $('#customerPaymentSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("#customerPaymentsTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    
    // Clear Customer Payments Search
    $('#clearCustomerPaymentSearch').on('click', function() {
        $('#customerPaymentSearch').val('');
        $("#customerPaymentsTable tbody tr").show();
    });
    
    // Default Fees Search Functionality
    $('#defaultFeesSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("#defaultFeesTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
    
    // Clear Default Fees Search
    $('#clearDefaultFeesSearch').on('click', function() {
        $('#defaultFeesSearch').val('');
        $("#defaultFeesTable tbody tr").show();
    });
    
    // Calculate net amount when amount or discount changes in Add Payment Modal
    $('#paymentAmount, #discount').on('input', function() {
        const amount = parseFloat($('#paymentAmount').val()) || 0;
        const discount = parseFloat($('#discount').val()) || 0;
        const netAmount = amount - discount;
        $('#netAmount').text(formatAmount(netAmount));
        updateAllocationSummary();
    });
    
    // Load fee allocations for add payment modal
    $('#addPaymentModal').on('show.bs.modal', function() {
        const studentId = $('#addPaymentStudentId').val();
        
        if (studentId) {
            loadFeeAllocations(studentId);
        }
    });
    
    // Load fee allocations for add payment modal
    function loadFeeAllocations(studentId) {
        $.ajax({
            url: 'get_student_fees.php',
            type: 'GET',
            data: { student_id: studentId },
            dataType: 'json',
            success: function(data) {
                let html = '';
                
                if (data.fees && data.fees.length > 0) {
                    data.fees.forEach(fee => {
                        const balance = fee.amount - fee.paid_amount;
                        
                        html += `
                            <div class="fee-allocation-item">
                                <div class="fee-allocation-details">
                                    <div class="fee-allocation-name">${fee.fee_name}</div>
                                    <div class="fee-allocation-amount">${formatAmount(parseFloat(fee.amount))}</div>
                                    <div class="fee-allocation-balance">
                                        Balance: ${formatAmount(parseFloat(balance))} | 
                                        Paid: ${formatAmount(parseFloat(fee.paid_amount))}
                                    </div>
                                </div>
                                <input type="number" class="form-control fee-allocation-input amount-input" 
                                       name="fee_allocations[${fee.id}]" 
                                       value="0" 
                                       min="0" 
                                       max="${balance}" 
                                       step="0.01"
                                       data-fee-id="${fee.id}"
                                       data-balance="${balance}">
                            </div>
                        `;
                    });
                } else {
                    html = '<div class="no-data-message">No fees found for this student</div>';
                }
                
                $('#feeAllocationList').html(html);
                updateAllocationSummary();
            },
            error: function() {
                $('#feeAllocationList').html('<div class="no-data-message">Error loading fees</div>');
            }
        });
    }
    
    // Update allocation summary for add payment modal
    function updateAllocationSummary() {
        let totalAllocated = 0;
        let netAmount = parseFloat($('#paymentAmount').val()) || 0;
        let discount = parseFloat($('#discount').val()) || 0;
        netAmount = netAmount - discount;
        
        $('.fee-allocation-input').each(function() {
            const value = parseFloat($(this).val()) || 0;
            totalAllocated += value;
        });
        
        const unallocated = netAmount - totalAllocated;
        
        $('#totalAllocated').text(formatAmount(totalAllocated));
        $('#unallocatedAmount').text(formatAmount(unallocated));
        $('#netAmountSummary').text(formatAmount(netAmount));
        
        // Update color based on unallocated amount
        if (unallocated < 0) {
            $('#unallocatedAmount').addClass('text-danger');
            $('#unallocatedAmount').removeClass('text-success');
        } else if (unallocated > 0) {
            $('#unallocatedAmount').addClass('text-success');
            $('#unallocatedAmount').removeClass('text-danger');
        } else {
            $('#unallocatedAmount').removeClass('text-danger text-success');
        }
    }
    
    // Handle allocation input changes
    $(document).on('input', '.fee-allocation-input', function() {
        const feeId = $(this).data('fee-id');
        const balance = parseFloat($(this).data('balance'));
        let value = parseFloat($(this).val()) || 0;
        
        // Ensure allocated amount doesn't exceed fee balance
        if (value > balance) {
            $(this).val(balance);
            value = balance;
        }
        
        // Ensure allocated amount is not negative
        if (value < 0) {
            $(this).val(0);
            value = 0;
        }
        
        // Format input value
        formatAmountInput($(this));
        
        // Update the allocation summary
        if ($(this).closest('#addPaymentModal').length) {
            updateAllocationSummary();
        }
    });
    
    // Form submission with AJAX for Add Fee
    $('#addFeeForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'cashier.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#addFeeModal').modal('hide');
                    // Reload student account
                    const studentId = $('#addFeeStudentId').val();
                    loadStudentAccount(studentId);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Error adding fee. Please try again.', 'error');
            }
        });
    });
    
    // Form submission with AJAX for Edit Fee
    $('#editFeeForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'cashier.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#editFeeModal').modal('hide');
                    // Reload student account
                    const studentId = $('#addFeeStudentId').val();
                    loadStudentAccount(studentId);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Error updating fee. Please try again.', 'error');
            }
        });
    });
    
    // Form submission with AJAX for Delete Fee
    $('#confirmDeleteFee').on('click', function() {
        const feeId = $('#deleteFeeId').val();
        
        $.ajax({
            url: 'cashier.php',
            type: 'GET',
            data: { action: 'delete_fee', id: feeId, current_tab: 'student_management' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#deleteFeeModal').modal('hide');
                    // Reload student account
                    const studentId = $('#addFeeStudentId').val();
                    loadStudentAccount(studentId);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Error deleting fee. Please try again.', 'error');
            }
        });
    });
    
    // Form submission with AJAX for Cancel Transaction (Student & Customer)
    $('#confirmCancelTransaction').on('click', function() {
        const paymentId = $('#cancelPaymentId').val();
        const type = $('#cancelPaymentType').val();
        const code = $('#cancelCode').val();
        const currentTab = type === 'customer' ? 'customer_payments' : 'student_management';

        $.ajax({
            url: 'cashier.php',
            type: 'POST',
            data: { 
                action: type === 'customer' ? 'cancel_customer_payment' : 'cancel_payment', 
                paymentId: paymentId, 
                verification_code: code,
                current_tab: currentTab
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#cancelTransactionModal').modal('hide');
                    if(type === 'customer') {
                        location.reload(); 
                    } else {
                        // Reload student account
                        const studentId = $('#addPaymentStudentId').val();
                        loadStudentAccount(studentId);
                    }
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Error cancelling transaction. Please try again.', 'error');
            }
        });
    });
    
    // Form submission with AJAX for Add Payment with Allocation
    $('#addPaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate allocations
        let totalAllocated = 0;
        let netAmount = parseFloat($('#paymentAmount').val()) || 0;
        let discount = parseFloat($('#discount').val()) || 0;
        netAmount = netAmount - discount;
        
        $('.fee-allocation-input').each(function() {
            const value = parseFloat($(this).val()) || 0;
            totalAllocated += value;
        });
        
        if (totalAllocated > netAmount) {
            showToast('Total allocated amount cannot exceed the net payment amount', 'error');
            return;
        }
        
        // If there's unallocated amount, confirm with user
        if (totalAllocated < netAmount) {
            // Use custom modal instead of confirm()
            if (!confirmCustom(`There is an unallocated amount of ${formatAmount(netAmount - totalAllocated)}. Do you want to proceed?`)) {
                return;
            }
        }
        
        // Submit form
        $.ajax({
            url: 'cashier.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#addPaymentModal').modal('hide');
                    
                    // Reload student account
                    const studentId = $('#addPaymentStudentId').val();
                    loadStudentAccount(studentId);
                    
                    // Show Print Prompt
                    showPrintPrompt('print_receipt.php?payment_id=' + response.paymentId);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Error recording payment. Please try again.', 'error');
            }
        });
    });

    // Temporary helper for confirm if custom modal is overkill for unallocated, 
    // but for the main print prompt we use the modal.
    function confirmCustom(msg) {
        return confirm(msg); 
    }
    
    // Form submission with AJAX for Apply Default Fees
    $('#applyDefaultFeesForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'cashier.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#applyDefaultFeesModal').modal('hide');
                    // Reload student account
                    const studentId = $('#applyStudentId').val();
                    loadStudentAccount(studentId);
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Error applying default fees. Please try again.', 'error');
            }
        });
    });
    
    // Form submission with AJAX for Add Default Fee
    $('#addDefaultFeeForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'cashier.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#addDefaultFeeModal').modal('hide');
                    // Reload default fees table
                    loadDefaultFeesTable();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Error adding default fee. Please try again.', 'error');
            }
        });
    });
    
    // Function to reload default fees table
    function loadDefaultFeesTable() {
        return $.ajax({
            url: 'get_default_fees_table.php',
            type: 'GET',
            dataType: 'html',
            success: function(data) {
                $('#defaultFeesTable').html(data);
            }
        });
    }
    
    // Form submission with AJAX for Edit Default Fee
    $('#editDefaultFeeForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'cashier.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#editDefaultFeeModal').modal('hide');
                    
                    // Try to reload the default fees table
                    loadDefaultFeesTable().fail(function() {
                        // If reloading the table fails, reload the whole page
                        console.log('Reloading page because table reload failed');
                        location.reload();
                    });
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                showToast('Error updating default fee. Please try again.', 'error');
            }
        });
    });
    
    // Form submission with AJAX for Delete Default Fee
    $('#confirmDeleteDefaultFee').on('click', function() {
        const feeId = $('#deleteDefaultFeeId').val();
        
        $.ajax({
            url: 'cashier.php',
            type: 'GET',
            data: { action: 'delete_default_fee', id: feeId, current_tab: 'default_fees' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#deleteDefaultFeeModal').modal('hide');
                    
                    // Try to reload the default fees table
                    loadDefaultFeesTable().fail(function() {
                        // If reloading the table fails, reload the whole page
                        console.log('Reloading page because table reload failed');
                        location.reload();
                    });
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Error deleting default fee. Please try again.', 'error');
            }
        });
    });
    
    // Form submission with AJAX for Add Customer Payment (UPDATED)
    $('#addCustomerPaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validate that at least one service is selected
        let validServices = 0;
        let formData = $(this).serializeArray();
        
        // We need to construct the 'services' array manually because serializeArray doesn't handle nested arrays well for PHP
        // But we already have hidden inputs named services[rowId][id], etc., which serializeArray *should* pick up.
        // However, we need to validate.
        
        if($('.service-row').length === 0) {
             showToast('Please add at least one service.', 'error');
             return;
        }

        // Validate rows
        let isValid = true;
        $('.service-row').each(function() {
            const sid = $(this).find('.real-service-id').val();
            const sname = $(this).find('.real-service-name').val();
            const sqty = $(this).find('.real-service-qty').val();
            const sprice = $(this).find('.real-service-price').val();

            if(!sid || !sname || !sqty || !sprice) {
                isValid = false;
            }
        });

        if(!isValid) {
            showToast('Please fill in all service details correctly.', 'error');
            return;
        }
        
        // Submit form using standard serialization (jQuery handles the services[] array correctly if named properly)
        $.ajax({
            url: 'cashier.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast(response.message, 'success');
                    $('#addCustomerPaymentModal').modal('hide');
                    
                    // Show Print Prompt
                    showPrintPrompt('customerprint.php?payment_id=' + response.paymentId);
                    
                    // Reload page to update customer payments list
                    location.reload();
                } else {
                    showToast(response.message, 'error');
                }
            },
            error: function() {
                showToast('Error recording customer payment. Please try again.', 'error');
            }
        });
    });

    // Print Prompt Modal Logic
    function showPrintPrompt(url) {
        $('#printPromptModal').data('url', url).modal('show');
    }

    $('#printPromptYes').on('click', function() {
        const url = $('#printPromptModal').data('url');
        if(url) {
            window.open(url, '_blank');
        }
        $('#printPromptModal').modal('hide');
    });

    $('#printPromptNo').on('click', function() {
        $('#printPromptModal').modal('hide');
    });

});
</script>
</body>
</html>