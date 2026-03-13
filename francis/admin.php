<?php
require_once 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user info
 $user_id = $_SESSION['user_id'];
 $user_query = "SELECT firstname, lastname, position, username, email FROM employee WHERE id = $user_id";
 $user_result = $conn->query($user_query);
 $user = $user_result->fetch_assoc();

// Create system_settings table if not exists
 $create_settings_table = "CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
 $conn->query($create_settings_table);

// Function to get system setting
function getSystemSetting($conn, $key, $default = '') {
    $query = "SELECT setting_value FROM system_settings WHERE setting_key = '$key'";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['setting_value'];
    }
    return $default;
}

// Function to update system setting
function updateSystemSetting($conn, $key, $value) {
    // Check if setting exists
    $check_query = "SELECT id FROM system_settings WHERE setting_key = '$key'";
    $check_result = $conn->query($check_query);
    
    if ($check_result && $check_result->num_rows > 0) {
        // Update existing setting
        $update_query = "UPDATE system_settings SET setting_value = '$value' WHERE setting_key = '$key'";
        return $conn->query($update_query);
    } else {
        // Insert new setting
        $insert_query = "INSERT INTO system_settings (setting_key, setting_value) VALUES ('$key', '$value')";
        return $conn->query($insert_query);
    }
}

// Get system settings
 $system_settings = [
    'company_name' => getSystemSetting($conn, 'company_name', 'Accounting System'),
    'company_address' => getSystemSetting($conn, 'company_address', '123 Main Street, Manila, Philippines'),
    'company_email' => getSystemSetting($conn, 'company_email', 'info@accountingsystem.com'),
    'company_phone' => getSystemSetting($conn, 'company_phone', '+63 (2) 123-4567'),
    'currency' => getSystemSetting($conn, 'currency', 'PHP'),
    'currency_symbol' => getSystemSetting($conn, 'currency_symbol', '₱')
];

// Handle settings form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    updateSystemSetting($conn, 'company_name', $_POST['company_name']);
    updateSystemSetting($conn, 'company_address', $_POST['company_address']);
    updateSystemSetting($conn, 'company_email', $_POST['company_email']);
    updateSystemSetting($conn, 'company_phone', $_POST['company_phone']);
    updateSystemSetting($conn, 'currency', $_POST['currency']);
    updateSystemSetting($conn, 'currency_symbol', $_POST['currency_symbol']);
    
    $_SESSION['toast'] = [
        'type' => 'success',
        'message' => 'Settings updated successfully!'
    ];
    header('Location: admin.php?page=settings');
    exit();
}

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $email = $conn->real_escape_string($_POST['email']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    
    $update_query = "UPDATE employee SET firstname='$firstname', lastname='$lastname', email='$email', username='$username'";
    
    if ($password) {
        $update_query .= ", password_hash='$password'";
    }
    
    $update_query .= " WHERE id=$user_id";
    
    if ($conn->query($update_query)) {
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Profile updated successfully!'
        ];
        // Update session variables
        $_SESSION['firstname'] = $firstname;
        $_SESSION['lastname'] = $lastname;
    } else {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => 'Error updating profile: ' . $conn->error
        ];
    }
    
    header('Location: admin.php?page=settings');
    exit();
}

// Add new employee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_employee'])) {
    $firstname = $conn->real_escape_string($_POST['employee_firstname']);
    $lastname = $conn->real_escape_string($_POST['employee_lastname']);
    $position = $conn->real_escape_string($_POST['employee_position']);
    $username = $conn->real_escape_string($_POST['employee_username']);
    $email = $conn->real_escape_string($_POST['employee_email']);
    $password = password_hash($_POST['employee_password'], PASSWORD_DEFAULT);
    
    $insert_query = "INSERT INTO employee (firstname, lastname, position, username, email, password_hash, is_active) 
                     VALUES ('$firstname', '$lastname', '$position', '$username', '$email', '$password', 1)";
    
    if ($conn->query($insert_query)) {
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Employee added successfully!'
        ];
    } else {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => 'Error adding employee: ' . $conn->error
        ];
    }
    
    header('Location: admin.php?page=settings');
    exit();
}

// Delete employee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_employee'])) {
    $id = $_POST['employee_id'];
    
    // Prevent deletion of current user
    if ($id == $user_id) {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => 'You cannot delete your own account!'
        ];
    } else {
        $delete_query = "DELETE FROM employee WHERE id = $id";
        if ($conn->query($delete_query)) {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Employee deleted successfully!'
            ];
        } else {
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => 'Error deleting employee: ' . $conn->error
            ];
        }
    }
    
    header('Location: admin.php?page=settings');
    exit();
}

// Get dashboard statistics
 $dashboard_stats = [
    'active_employees' => 0,
    'total_transactions' => 0,
    'total_revenue' => 0,
    'total_expenses' => 0,
    'net_income' => 0
];

 $employees_query = "SELECT COUNT(*) as count FROM employee WHERE is_active = TRUE";
 $employees_result = $conn->query($employees_query);
if ($employees_result) {
    $dashboard_stats['active_employees'] = $employees_result->fetch_assoc()['count'];
}

 $transactions_query = "SELECT COUNT(*) as count FROM transactions";
 $transactions_result = $conn->query($transactions_query);
if ($transactions_result) {
    $dashboard_stats['total_transactions'] = $transactions_result->fetch_assoc()['count'];
}

 $revenue_query = "SELECT SUM(amount) as total FROM transaction_entries te 
                  JOIN chart_of_accounts coa ON te.account_id = coa.id 
                  WHERE coa.account_type = 'revenue' AND te.entry_type = 'credit'";
 $revenue_result = $conn->query($revenue_query);
if ($revenue_result) {
    $dashboard_stats['total_revenue'] = $revenue_result->fetch_assoc()['total'] ?? 0;
}

 $expense_query = "SELECT SUM(amount) as total FROM transaction_entries te 
                 JOIN chart_of_accounts coa ON te.account_id = coa.id 
                 WHERE coa.account_type = 'expense' AND te.entry_type = 'debit'";
 $expense_result = $conn->query($expense_query);
if ($expense_result) {
    $dashboard_stats['total_expenses'] = $expense_result->fetch_assoc()['total'] ?? 0;
}

 $dashboard_stats['net_income'] = $dashboard_stats['total_revenue'] - $dashboard_stats['total_expenses'];

// Get monthly data for charts
 $current_year = date('Y');
 $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
 $monthly_expenses = [];
 $monthly_revenue = [];
 $monthly_net_income = [];

for ($month = 1; $month <= 12; $month++) {
    $start_date = date('Y-m-d', mktime(0, 0, 0, $month, 1, $current_year));
    $end_date = date('Y-m-t', mktime(0, 0, 0, $month, 1, $current_year));
    
    // Get expenses for the month
    $expense_query = "SELECT SUM(te.amount) as total 
                     FROM transaction_entries te 
                     JOIN transactions t ON te.transaction_id = t.id
                     JOIN chart_of_accounts coa ON te.account_id = coa.id 
                     WHERE coa.account_type = 'expense' AND te.entry_type = 'debit'
                     AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
    $expense_result = $conn->query($expense_query);
    $expense_total = 0;
    if ($expense_result) {
        $row = $expense_result->fetch_assoc();
        $expense_total = $row['total'] ?? 0;
    }
    $monthly_expenses[] = $expense_total;
    
    // Get revenue for the month
    $revenue_query = "SELECT SUM(te.amount) as total 
                     FROM transaction_entries te 
                     JOIN transactions t ON te.transaction_id = t.id
                     JOIN chart_of_accounts coa ON te.account_id = coa.id 
                     WHERE coa.account_type = 'revenue' AND te.entry_type = 'credit'
                     AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
    $revenue_result = $conn->query($revenue_query);
    $revenue_total = 0;
    if ($revenue_result) {
        $row = $revenue_result->fetch_assoc();
        $revenue_total = $row['total'] ?? 0;
    }
    $monthly_revenue[] = $revenue_total;
    
    // Calculate net income for the month
    $monthly_net_income[] = $revenue_total - $expense_total;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add new transaction with multiple entries
    if (isset($_POST['add_transaction'])) {
        $transaction_date = $conn->real_escape_string($_POST['transaction_date']);
        $reference_no = $conn->real_escape_string($_POST['reference_no']);
        $description = $conn->real_escape_string($_POST['description']);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert transaction header
            $insert_query = "INSERT INTO transactions (transaction_date, reference_no, description, created_by) 
                             VALUES ('$transaction_date', '$reference_no', '$description', $user_id)";
            
            if (!$conn->query($insert_query)) {
                throw new Exception("Error adding transaction: " . $conn->error);
            }
            
            $transaction_id = $conn->insert_id;
            
            // Process debit entries
            if (isset($_POST['debit_accounts']) && is_array($_POST['debit_accounts'])) {
                foreach ($_POST['debit_accounts'] as $index => $account_id) {
                    $account_id = $conn->real_escape_string($account_id);
                    $amount = $conn->real_escape_string($_POST['debit_amounts'][$index]);
                    $description = $conn->real_escape_string($_POST['debit_descriptions'][$index]);
                    
                    if (!empty($account_id) && !empty($amount) && $amount > 0) {
                        $debit_query = "INSERT INTO transaction_entries (transaction_id, account_id, amount, entry_type, description) 
                                        VALUES ($transaction_id, $account_id, $amount, 'debit', '$description')";
                        
                        if (!$conn->query($debit_query)) {
                            throw new Exception("Error adding debit entry: " . $conn->error);
                        }
                    }
                }
            }
            
            // Process credit entries
            if (isset($_POST['credit_accounts']) && is_array($_POST['credit_accounts'])) {
                foreach ($_POST['credit_accounts'] as $index => $account_id) {
                    $account_id = $conn->real_escape_string($account_id);
                    $amount = $conn->real_escape_string($_POST['credit_amounts'][$index]);
                    $description = $conn->real_escape_string($_POST['credit_descriptions'][$index]);
                    
                    if (!empty($account_id) && !empty($amount) && $amount > 0) {
                        $credit_query = "INSERT INTO transaction_entries (transaction_id, account_id, amount, entry_type, description) 
                                         VALUES ($transaction_id, $account_id, $amount, 'credit', '$description')";
                        
                        if (!$conn->query($credit_query)) {
                            throw new Exception("Error adding credit entry: " . $conn->error);
                        }
                    }
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Transaction added successfully!'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => $e->getMessage()
            ];
        }
        
        header('Location: admin.php?page=transactions');
        exit();
    }
    
    // Delete transaction
    if (isset($_POST['delete_transaction'])) {
        $id = $_POST['id'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete related entries first
            if (!$conn->query("DELETE FROM transaction_entries WHERE transaction_id = $id")) {
                throw new Exception("Error deleting transaction entries: " . $conn->error);
            }
            
            // Delete transaction
            if (!$conn->query("DELETE FROM transactions WHERE id = $id")) {
                throw new Exception("Error deleting transaction: " . $conn->error);
            }
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Transaction deleted successfully!'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => $e->getMessage()
            ];
        }
        
        header('Location: admin.php?page=transactions');
        exit();
    }
    
    // Update transaction with multiple entries
    if (isset($_POST['update_transaction'])) {
        $id = $_POST['transaction_id'];
        $transaction_date = $conn->real_escape_string($_POST['transaction_date']);
        $reference_no = $conn->real_escape_string($_POST['reference_no']);
        $description = $conn->real_escape_string($_POST['description']);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update transaction header
            $update_query = "UPDATE transactions SET transaction_date='$transaction_date', reference_no='$reference_no', description='$description' WHERE id=$id";
            
            if (!$conn->query($update_query)) {
                throw new Exception("Error updating transaction: " . $conn->error);
            }
            
            // Delete existing entries
            if (!$conn->query("DELETE FROM transaction_entries WHERE transaction_id = $id")) {
                throw new Exception("Error deleting existing entries: " . $conn->error);
            }
            
            // Process debit entries
            if (isset($_POST['debit_accounts']) && is_array($_POST['debit_accounts'])) {
                foreach ($_POST['debit_accounts'] as $index => $account_id) {
                    $account_id = $conn->real_escape_string($account_id);
                    $amount = $conn->real_escape_string($_POST['debit_amounts'][$index]);
                    $description = $conn->real_escape_string($_POST['debit_descriptions'][$index]);
                    
                    if (!empty($account_id) && !empty($amount) && $amount > 0) {
                        $debit_query = "INSERT INTO transaction_entries (transaction_id, account_id, amount, entry_type, description) 
                                        VALUES ($id, $account_id, $amount, 'debit', '$description')";
                        
                        if (!$conn->query($debit_query)) {
                            throw new Exception("Error updating debit entry: " . $conn->error);
                        }
                    }
                }
            }
            
            // Process credit entries
            if (isset($_POST['credit_accounts']) && is_array($_POST['credit_accounts'])) {
                foreach ($_POST['credit_accounts'] as $index => $account_id) {
                    $account_id = $conn->real_escape_string($account_id);
                    $amount = $conn->real_escape_string($_POST['credit_amounts'][$index]);
                    $description = $conn->real_escape_string($_POST['credit_descriptions'][$index]);
                    
                    if (!empty($account_id) && !empty($amount) && $amount > 0) {
                        $credit_query = "INSERT INTO transaction_entries (transaction_id, account_id, amount, entry_type, description) 
                                         VALUES ($id, $account_id, $amount, 'credit', '$description')";
                        
                        if (!$conn->query($credit_query)) {
                            throw new Exception("Error updating credit entry: " . $conn->error);
                        }
                    }
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Transaction updated successfully!'
            ];
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => $e->getMessage()
            ];
        }
        
        header('Location: admin.php?page=transactions');
        exit();
    }
    
    // Add new account
    if (isset($_POST['add_account'])) {
        $account_category = empty($_POST['account_category']) || $_POST['account_category'] == 'None' ? null : $conn->real_escape_string($_POST['account_category']);
        $account_name = $conn->real_escape_string($_POST['account_name']);
        $account_type = $conn->real_escape_string($_POST['account_type']);
        $normal_balance = $conn->real_escape_string($_POST['normal_balance']);
        $parent_account_id = !empty($_POST['parent_account_id']) ? $conn->real_escape_string($_POST['parent_account_id']) : 'NULL';
        $description = $conn->real_escape_string($_POST['description']);
        
        // Generate account code based on category if available, otherwise use account type
        if ($account_category) {
            $category_codes = [
                'Tuition Fee' => '100',
                'Miscellaneous Fee' => '200',
                'Other Fees' => '300',
                'Other Income' => '400',
                'Other Collection' => '500',
                'Operating Expense' => '600',
                'Education and Training Expense' => '700',
                'Miscellaneous Expense' => '800',
                'Other Expense' => '900',
                'Meetings and Representations' => '1000'
            ];
            
            // Get the next available code for this category
            $base_code = $category_codes[$account_category] ?? '000';
            $next_code_query = "SELECT MAX(CAST(SUBSTRING(account_code, 5) AS UNSIGNED)) as max_code 
                               FROM chart_of_accounts 
                               WHERE account_code LIKE '$base_code%'";
            $next_code_result = $conn->query($next_code_query);
            $next_code = 1;
            
            if ($next_code_result && $next_code_result->num_rows > 0) {
                $max_code = $next_code_result->fetch_assoc()['max_code'];
                if ($max_code) {
                    $next_code = $max_code + 1;
                }
            }
            
            $account_code = $base_code . str_pad($next_code, 3, '0', STR_PAD_LEFT);
        } else {
            // Use account type for code generation
            $type_codes = [
                'asset' => '1000',
                'liability' => '2000',
                'equity' => '3000',
                'revenue' => '4000',
                'expense' => '5000'
            ];
            
            $base_code = $type_codes[$account_type] ?? '0000';
            $next_code_query = "SELECT MAX(CAST(SUBSTRING(account_code, 5) AS UNSIGNED)) as max_code 
                               FROM chart_of_accounts 
                               WHERE account_code LIKE '$base_code%'";
            $next_code_result = $conn->query($next_code_query);
            $next_code = 1;
            
            if ($next_code_result && $next_code_result->num_rows > 0) {
                $max_code = $next_code_result->fetch_assoc()['max_code'];
                if ($max_code) {
                    $next_code = $max_code + 1;
                }
            }
            
            $account_code = $base_code . str_pad($next_code, 3, '0', STR_PAD_LEFT);
        }
        
        $insert_query = "INSERT INTO chart_of_accounts (account_code, account_category, account_name, account_type, normal_balance, parent_account_id, description) 
                         VALUES ('$account_code', " . ($account_category ? "'$account_category'" : "NULL") . ", '$account_name', '$account_type', '$normal_balance', $parent_account_id, '$description')";
        
        if ($conn->query($insert_query)) {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Account added successfully!'
            ];
        } else {
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => 'Error adding account: ' . $conn->error
            ];
        }
        header('Location: admin.php?page=accounts');
        exit();
    }
    
    // Update account
    if (isset($_POST['update_account'])) {
        $id = $_POST['id'];
        $account_category = empty($_POST['account_category']) || $_POST['account_category'] == 'None' ? null : $conn->real_escape_string($_POST['account_category']);
        $account_name = $conn->real_escape_string($_POST['account_name']);
        $account_type = $conn->real_escape_string($_POST['account_type']);
        $normal_balance = $conn->real_escape_string($_POST['normal_balance']);
        $parent_account_id = !empty($_POST['parent_account_id']) ? $conn->real_escape_string($_POST['parent_account_id']) : 'NULL';
        $description = $conn->real_escape_string($_POST['description']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Get the current account code prefix
        $current_query = "SELECT account_code FROM chart_of_accounts WHERE id = $id";
        $current_result = $conn->query($current_query);
        $current_code = '';
        
        if ($current_result && $current_result->num_rows > 0) {
            $current_code = $current_result->fetch_assoc()['account_code'];
            $code_prefix = substr($current_code, 0, 4);
        }
        
        $update_query = "UPDATE chart_of_accounts SET account_category=" . ($account_category ? "'$account_category'" : "NULL") . ", account_name='$account_name', 
                         account_type='$account_type', normal_balance='$normal_balance', 
                         parent_account_id=$parent_account_id, description='$description', is_active=$is_active 
                         WHERE id=$id";
        
        if ($conn->query($update_query)) {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Account updated successfully!'
            ];
        } else {
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => 'Error updating account: ' . $conn->error
            ];
        }
        header('Location: admin.php?page=accounts');
        exit();
    }
    
    // Delete account
    if (isset($_POST['delete_account'])) {
        $id = $_POST['id'];
        
        // Check if account has transactions
        $check_query = "SELECT COUNT(*) as count FROM transaction_entries WHERE account_id = $id";
        $check_result = $conn->query($check_query);
        $has_transactions = 0;
        
        if ($check_result) {
            $has_transactions = $check_result->fetch_assoc()['count'];
        }
        
        if ($has_transactions > 0) {
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => 'Cannot delete account. It has associated transactions.'
            ];
        } else {
            $delete_query = "DELETE FROM chart_of_accounts WHERE id = $id";
            
            if ($conn->query($delete_query)) {
                $_SESSION['toast'] = [
                    'type' => 'success',
                    'message' => 'Account deleted successfully!'
                ];
            } else {
                $_SESSION['toast'] = [
                    'type' => 'error',
                    'message' => 'Error deleting account: ' . $conn->error
                ];
            }
        }
        
        header('Location: admin.php?page=accounts');
        exit();
    }
}

// Get data for tables
 $transactions_data = [];
 $transactions_query = "SELECT t.*, CONCAT(e.firstname, ' ', e.lastname) as created_by_name 
                      FROM transactions t 
                      JOIN employee e ON t.created_by = e.id 
                      ORDER BY t.created_at DESC LIMIT 10";
 $transactions_result = $conn->query($transactions_query);
if ($transactions_result) {
    while ($row = $transactions_result->fetch_assoc()) {
        $transactions_data[] = $row;
    }
}

 $accounts_data = [];
 $accounts_query = "SELECT * FROM chart_of_accounts WHERE is_active = TRUE ORDER BY account_code";
 $accounts_result = $conn->query($accounts_query);
if ($accounts_result) {
    while ($row = $accounts_result->fetch_assoc()) {
        $accounts_data[] = $row;
    }
}

// Get all employees for settings page
 $employees_data = [];
 $employees_query = "SELECT * FROM employee ORDER BY lastname, firstname";
 $employees_result = $conn->query($employees_query);
if ($employees_result) {
    while ($row = $employees_result->fetch_assoc()) {
        $employees_data[] = $row;
    }
}

// Get current page
 $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="uploads/diocese.png" type="image/x-icon">
    <title><?php echo $system_settings['company_name']; ?> - Accounting System</title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom style -->
    <style>
        .content-wrapper {
            min-height: calc(100vh - 56px);
        }
        .small-box {
            border-radius: 0.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        .nav-sidebar .nav-link>p {
            display: inline;
            margin-left: 0.5rem;
        }
        .nav-sidebar .nav-link.active {
            background-color: #28a745;
            color: white;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .btn-group-sm>.btn, .btn-sm {
            padding: .25rem .5rem;
            font-size: .875rem;
            line-height: 1.5;
            border-radius: .2rem;
        }
        .modal-header {
            background-color: #28a745;
            color: white;
        }
        .currency {
            font-weight: bold;
        }
        .nav-tabs {
            margin-bottom: 1rem;
        }
        
        /* Green theme adjustments */
        .main-header {
            background-color: #28a745;
        }
        .main-header .navbar-nav .nav-link {
            color: rgba(255,255,255,.8);
        }
        .main-header .navbar-nav .nav-link:hover {
            color: #fff;
        }
        .main-sidebar {
            background-color: #28a745;
        }
        .brand-link {
            background-color: #1e7e34;
        }
        .user-panel {
            background-color: #1e7e34;
        }
        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active {
            background-color: #1e7e34;
        }
        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link:hover {
            background-color: #20c997;
        }
        .btn-primary {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-primary:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .bg-success {
            background-color: #28a745 !important;
        }
        .bg-info {
            background-color: #17a2b8 !important;
        }
        .bg-warning {
            background-color: #ffc107 !important;
        }
        .bg-danger {
            background-color: #dc3545 !important;
        }
        
        /* Custom toast styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast {
            background-color: white;
            border-radius: 0.25rem;
            box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.1);
            margin-bottom: 0.75rem;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        
        .toast.show {
            opacity: 1;
        }
        
        .toast-header {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .toast-body {
            padding: 0.75rem;
        }
        
        .toast-success {
            border-left: 4px solid #28a745;
        }
        
        .toast-error {
            border-left: 4px solid #dc3545;
        }
        
        .toast-warning {
            border-left: 4px solid #ffc107;
        }
        
        .toast-info {
            border-left: 4px solid #17a2b8;
        }
        
        /* Chart container styles */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        
        /* Transaction table improvements */
        .transaction-actions {
            white-space: nowrap;
        }
        
        .transaction-actions .btn {
            margin-right: 5px;
        }
        
        .transaction-details td {
            vertical-align: middle;
        }
        
        .transaction-entries {
            margin-top: 15px;
        }
        
        .entry-type-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        /* Report specific styles */
        .report-table {
            margin-bottom: 20px;
        }
        
        .report-table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        
        .report-section {
            margin-bottom: 30px;
        }
        
        .report-section h5 {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .report-total {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        
        .account-group {
            margin-bottom: 20px;
        }
        
        .account-group h6 {
            font-weight: bold;
            color: #495057;
        }
        
        .indent-1 {
            padding-left: 20px;
        }
        
        .indent-2 {
            padding-left: 40px;
        }
        
        /* Transaction entry styles */
        .entry-row {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        
        .entry-row .form-group {
            margin-bottom: 5px;
        }
        
        .entry-row .remove-entry {
            margin-top: 25px;
        }
        
        .add-entry-btn {
            margin-top: 10px;
            margin-bottom: 20px;
        }
        
        .entry-section {
            margin-bottom: 20px;
        }
        
        .entry-section h5 {
            padding: 10px;
            border-radius: 5px;
            color: white;
        }
        
        .debit-section h5 {
            background-color: #17a2b8;
        }
        
        .credit-section h5 {
            background-color: #28a745;
        }
        
        .balance-info {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        
        .balance-info .balance-label {
            font-weight: bold;
        }
        
        .balance-info .balance-value {
            font-size: 1.2rem;
        }
        
        .balance-info .balanced {
            color: #28a745;
        }
        
        .balance-info .unbalanced {
            color: #dc3545;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="admin.php" class="nav-link">Home</a>
            </li>
        </ul>
        
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-user-circle"></i> <?php echo $user['firstname'] . ' ' . $user['lastname']; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="#"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profile</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i> Settings</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout</a>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-success elevation-4">
        <a href="admin.php" class="brand-link">
            <span class="brand-text font-weight-light"><?php echo $system_settings['company_name']; ?></span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="uploads/diocese.png" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?php echo $user['firstname'] . ' ' . $user['lastname']; ?></a>
                    <span class="text-muted"><?php echo $user['position']; ?></span>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="admin.php?page=dashboard" class="nav-link <?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="admin.php?page=transactions" class="nav-link <?php echo $page == 'transactions' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-exchange-alt"></i>
                            <p>Transactions</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="admin.php?page=accounts" class="nav-link <?php echo $page == 'accounts' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-book"></i>
                            <p>Chart of Accounts</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="admin.php?page=reports" class="nav-link <?php echo $page == 'reports' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-chart-line"></i>
                            <p>Reports</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="admin.php?page=settings" class="nav-link <?php echo $page == 'settings' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>Settings</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">
                            <?php
                            switch($page) {
                                case 'dashboard': echo 'Dashboard'; break;
                                case 'transactions': echo 'Transactions'; break;
                                case 'accounts': echo 'Chart of Accounts'; break;
                                case 'reports': echo 'Reports'; break;
                                case 'settings': echo 'Settings'; break;
                                default: echo 'Dashboard';
                            }
                            ?>
                        </h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="admin.php">Home</a></li>
                            <li class="breadcrumb-item active">
                                <?php
                                switch($page) {
                                    case 'dashboard': echo 'Dashboard'; break;
                                    case 'transactions': echo 'Transactions'; break;
                                    case 'accounts': echo 'Chart of Accounts'; break;
                                    case 'reports': echo 'Reports'; break;
                                    case 'settings': echo 'Settings'; break;
                                    default: echo 'Dashboard';
                                }
                                ?>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Toast Container -->
                <div class="toast-container"></div>

                <!-- Dashboard Content -->
                <?php if ($page == 'dashboard'): ?>
                    <!-- Small boxes (Stat box) -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?php echo $dashboard_stats['active_employees']; ?></h3>
                                    <p>Active Employees</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?php echo $dashboard_stats['total_transactions']; ?></h3>
                                    <p>Total Transactions</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-exchange-alt"></i>
                                </div>
                                <a href="admin.php?page=transactions" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?php echo $system_settings['currency_symbol'] . number_format($dashboard_stats['total_revenue'], 2); ?></h3>
                                    <p>Total Revenue</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-coins"></i>
                                </div>
                                <a href="admin.php?page=reports" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3><?php echo $system_settings['currency_symbol'] . number_format($dashboard_stats['net_income'], 2); ?></h3>
                                    <p>Net Income</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <a href="admin.php?page=reports" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <!-- /.row -->

                    <!-- Charts Row -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Monthly Expenses (Line Chart)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="expenseChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Monthly Revenue (Bar Chart)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="revenueChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6 offset-md-3">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Net Income by Month (Pie Chart)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="netIncomeChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.row (charts row) -->

                    <!-- Main row -->
                    <div class="row mt-4">
                        <!-- Left col -->
                        <section class="col-lg-7 connectedSortable">
                            <!-- Recent Transactions -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Recent Transactions</h5>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Reference</th>
                                                    <th>Description</th>
                                                    <th>Created By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($transactions_data as $transaction): ?>
                                                <tr>
                                                    <td><?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?></td>
                                                    <td><?php echo $transaction['reference_no']; ?></td>
                                                    <td><?php echo $transaction['description']; ?></td>
                                                    <td><?php echo $transaction['created_by_name']; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card -->
                        </section>
                        <!-- /.Left col -->

                        <!-- Right col -->
                        <section class="col-lg-5 connectedSortable">
                            <!-- Quick Actions -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Quick Actions</h5>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="admin.php?page=transactions" class="btn btn-success">
                                            <i class="fas fa-plus-circle"></i> Add Transaction
                                        </a>
                                        <a href="admin.php?page=accounts" class="btn btn-info">
                                            <i class="fas fa-plus-circle"></i> Add Account
                                        </a>
                                        <a href="admin.php?page=reports" class="btn btn-danger">
                                            <i class="fas fa-chart-line"></i> View Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card -->
                        </section>
                        <!-- /.Right col -->
                    </div>
                    <!-- /.row (main row) -->
                <?php endif; ?>

                <!-- Transactions Content -->
                <?php if ($page == 'transactions'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Transactions Management</h5>
                            <div class="card-tools">
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addTransactionModal">
                                    <i class="fas fa-plus"></i> Add Transaction
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="transactionsTable">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Reference</th>
                                            <th>Description</th>
                                            <th>Created By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $all_transactions_query = "SELECT t.*, CONCAT(e.firstname, ' ', e.lastname) as created_by_name 
                                                                    FROM transactions t 
                                                                    JOIN employee e ON t.created_by = e.id 
                                                                    ORDER BY t.created_at DESC";
                                        $all_transactions_result = $conn->query($all_transactions_query);
                                        if ($all_transactions_result) {
                                            while ($transaction = $all_transactions_result->fetch_assoc()) {
                                        ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?></td>
                                            <td><?php echo $transaction['reference_no']; ?></td>
                                            <td><?php echo $transaction['description']; ?></td>
                                            <td><?php echo $transaction['created_by_name']; ?></td>
                                            <td class="transaction-actions">
                                                <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editTransactionModal<?php echo $transaction['id']; ?>" title="Edit Transaction">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="admin.php?page=transactions" method="post" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?php echo $transaction['id']; ?>">
                                                    <button type="submit" name="delete_transaction" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this transaction?');" title="Delete Transaction">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        
                                      
                                        
                                        <!-- Edit Transaction Modal -->
                                        <div class="modal fade" id="editTransactionModal<?php echo $transaction['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-xl" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Transaction</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="admin.php?page=transactions" method="post" id="editTransactionForm<?php echo $transaction['id']; ?>">
                                                            <input type="hidden" name="transaction_id" value="<?php echo $transaction['id']; ?>">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Transaction Date</label>
                                                                        <input type="date" class="form-control" name="transaction_date" value="<?php echo $transaction['transaction_date']; ?>" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label>Reference No.</label>
                                                                        <input type="text" class="form-control" name="reference_no" value="<?php echo $transaction['reference_no']; ?>" required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Description</label>
                                                                <textarea class="form-control" name="description" rows="2" required><?php echo $transaction['description']; ?></textarea>
                                                            </div>
                                                            
                                                            <h5 class="mt-4 mb-3">Transaction Entries</h5>
                                                            
                                                            <?php
                                                            // Get the transaction entries
                                                            $entries_query = "SELECT te.*, coa.account_name, coa.account_category 
                                                                            FROM transaction_entries te 
                                                                            JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                                            WHERE te.transaction_id = " . $transaction['id'] . "
                                                                            ORDER BY te.entry_type DESC";
                                                            $entries_result = $conn->query($entries_query);
                                                            $entries = [];
                                                            if ($entries_result) {
                                                                while ($entry = $entries_result->fetch_assoc()) {
                                                                    $entries[] = $entry;
                                                                }
                                                            }
                                                            
                                                            // Group entries by type
                                                            $debit_entries = array_filter($entries, function($entry) {
                                                                return $entry['entry_type'] == 'debit';
                                                            });
                                                            
                                                            $credit_entries = array_filter($entries, function($entry) {
                                                                return $entry['entry_type'] == 'credit';
                                                            });
                                                            ?>
                                                            
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="card entry-section debit-section">
                                                                        <div class="card-header">
                                                                            <h6 class="card-title">Debit Entries</h6>
                                                                        </div>
                                                                        <div class="card-body" id="editDebitEntries<?php echo $transaction['id']; ?>">
                                                                            <?php if (count($debit_entries) > 0): ?>
                                                                                <?php foreach ($debit_entries as $index => $entry): ?>
                                                                                    <div class="entry-row">
                                                                                        <div class="row">
                                                                                            <div class="col-md-5">
                                                                                                <div class="form-group">
                                                                                                    <label>Account</label>
                                                                                                    <select class="form-control debit-account" name="debit_accounts[]" required>
                                                                                                        <option value="">Select Account</option>
                                                                                                        <?php foreach ($accounts_data as $account): ?>
                                                                                                        <option value="<?php echo $account['id']; ?>" <?php echo ($entry['account_id'] == $account['id']) ? 'selected' : ''; ?>>
                                                                                                            <?php echo $account['account_category'] . ' - ' . $account['account_name']; ?>
                                                                                                        </option>
                                                                                                        <?php endforeach; ?>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-4">
                                                                                                <div class="form-group">
                                                                                                    <label>Amount</label>
                                                                                                    <input type="number" class="form-control debit-amount" name="debit_amounts[]" step="0.01" value="<?php echo $entry['amount']; ?>" required>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-3">
                                                                                                <div class="form-group">
                                                                                                    <label>Action</label>
                                                                                                    <button type="button" class="btn btn-danger btn-sm remove-entry" <?php echo (count($debit_entries) == 1) ? 'disabled' : ''; ?>>
                                                                                                        <i class="fas fa-trash"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group">
                                                                                            <label>Description</label>
                                                                                            <input type="text" class="form-control" name="debit_descriptions[]" value="<?php echo $entry['description']; ?>">
                                                                                        </div>
                                                                                    </div>
                                                                                <?php endforeach; ?>
                                                                            <?php else: ?>
                                                                                <div class="entry-row">
                                                                                    <div class="row">
                                                                                        <div class="col-md-5">
                                                                                            <div class="form-group">
                                                                                                <label>Account</label>
                                                                                                <select class="form-control debit-account" name="debit_accounts[]" required>
                                                                                                    <option value="">Select Account</option>
                                                                                                    <?php foreach ($accounts_data as $account): ?>
                                                                                                    <option value="<?php echo $account['id']; ?>"><?php echo $account['account_category'] . ' - ' . $account['account_name']; ?></option>
                                                                                                    <?php endforeach; ?>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-4">
                                                                                            <div class="form-group">
                                                                                                <label>Amount</label>
                                                                                                <input type="number" class="form-control debit-amount" name="debit_amounts[]" step="0.01" required>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-3">
                                                                                            <div class="form-group">
                                                                                                <label>Action</label>
                                                                                                <button type="button" class="btn btn-danger btn-sm remove-entry" disabled>
                                                                                                    <i class="fas fa-trash"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group">
                                                                                        <label>Description</label>
                                                                                        <input type="text" class="form-control" name="debit_descriptions[]">
                                                                                    </div>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                            
                                                                            <button type="button" class="btn btn-info btn-sm add-debit-entry" data-transaction-id="<?php echo $transaction['id']; ?>">
                                                                                <i class="fas fa-plus"></i> Add Debit Entry
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                
                                                                <div class="col-md-6">
                                                                    <div class="card entry-section credit-section">
                                                                        <div class="card-header">
                                                                            <h6 class="card-title">Credit Entries</h6>
                                                                        </div>
                                                                        <div class="card-body" id="editCreditEntries<?php echo $transaction['id']; ?>">
                                                                            <?php if (count($credit_entries) > 0): ?>
                                                                                <?php foreach ($credit_entries as $index => $entry): ?>
                                                                                    <div class="entry-row">
                                                                                        <div class="row">
                                                                                            <div class="col-md-5">
                                                                                                <div class="form-group">
                                                                                                    <label>Account</label>
                                                                                                    <select class="form-control credit-account" name="credit_accounts[]" required>
                                                                                                        <option value="">Select Account</option>
                                                                                                        <?php foreach ($accounts_data as $account): ?>
                                                                                                        <option value="<?php echo $account['id']; ?>" <?php echo ($entry['account_id'] == $account['id']) ? 'selected' : ''; ?>>
                                                                                                            <?php echo $account['account_category'] . ' - ' . $account['account_name']; ?>
                                                                                                        </option>
                                                                                                        <?php endforeach; ?>
                                                                                                    </select>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-4">
                                                                                                <div class="form-group">
                                                                                                    <label>Amount</label>
                                                                                                    <input type="number" class="form-control credit-amount" name="credit_amounts[]" step="0.01" value="<?php echo $entry['amount']; ?>" required>
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="col-md-3">
                                                                                                <div class="form-group">
                                                                                                    <label>Action</label>
                                                                                                    <button type="button" class="btn btn-danger btn-sm remove-entry" <?php echo (count($credit_entries) == 1) ? 'disabled' : ''; ?>>
                                                                                                        <i class="fas fa-trash"></i>
                                                                                                    </button>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="form-group">
                                                                                            <label>Description</label>
                                                                                            <input type="text" class="form-control" name="credit_descriptions[]" value="<?php echo $entry['description']; ?>">
                                                                                        </div>
                                                                                    </div>
                                                                                <?php endforeach; ?>
                                                                            <?php else: ?>
                                                                                <div class="entry-row">
                                                                                    <div class="row">
                                                                                        <div class="col-md-5">
                                                                                            <div class="form-group">
                                                                                                <label>Account</label>
                                                                                                <select class="form-control credit-account" name="credit_accounts[]" required>
                                                                                                    <option value="">Select Account</option>
                                                                                                    <?php foreach ($accounts_data as $account): ?>
                                                                                                    <option value="<?php echo $account['id']; ?>"><?php echo $account['account_category'] . ' - ' . $account['account_name']; ?></option>
                                                                                                    <?php endforeach; ?>
                                                                                                </select>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-4">
                                                                                            <div class="form-group">
                                                                                                <label>Amount</label>
                                                                                                <input type="number" class="form-control credit-amount" name="credit_amounts[]" step="0.01" required>
                                                                                            </div>
                                                                                        </div>
                                                                                        <div class="col-md-3">
                                                                                            <div class="form-group">
                                                                                                <label>Action</label>
                                                                                                <button type="button" class="btn btn-danger btn-sm remove-entry" disabled>
                                                                                                    <i class="fas fa-trash"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="form-group">
                                                                                        <label>Description</label>
                                                                                        <input type="text" class="form-control" name="credit_descriptions[]">
                                                                                    </div>
                                                                                </div>
                                                                            <?php endif; ?>
                                                                            
                                                                            <button type="button" class="btn btn-info btn-sm add-credit-entry" data-transaction-id="<?php echo $transaction['id']; ?>">
                                                                                <i class="fas fa-plus"></i> Add Credit Entry
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="balance-info">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <span class="balance-label">Total Debit:</span>
                                                                        <span class="balance-value" id="editTotalDebit<?php echo $transaction['id']; ?>">0.00</span>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <span class="balance-label">Total Credit:</span>
                                                                        <span class="balance-value" id="editTotalCredit<?php echo $transaction['id']; ?>">0.00</span>
                                                                    </div>
                                                                </div>
                                                                <div class="row mt-2">
                                                                    <div class="col-md-12 text-center">
                                                                        <span class="balance-label">Balance:</span>
                                                                        <span class="balance-value" id="editBalance<?php echo $transaction['id']; ?>">0.00</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="form-group mt-4">
                                                                <button type="submit" name="update_transaction" class="btn btn-success">Update Transaction</button>
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php 
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Add Transaction Modal -->
                    <div class="modal fade" id="addTransactionModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add New Transaction</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form action="admin.php?page=transactions" method="post" id="addTransactionForm">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Transaction Date</label>
                                                    <input type="date" class="form-control" name="transaction_date" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Reference No.</label>
                                                    <input type="text" class="form-control" name="reference_no" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Description</label>
                                            <textarea class="form-control" name="description" rows="2" required></textarea>
                                        </div>
                                        
                                        <h5 class="mt-4 mb-3">Transaction Entries</h5>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card entry-section debit-section">
                                                    <div class="card-header">
                                                        <h6 class="card-title">Debit Entries</h6>
                                                    </div>
                                                    <div class="card-body" id="debitEntries">
                                                        <div class="entry-row">
                                                            <div class="row">
                                                                <div class="col-md-5">
                                                                    <div class="form-group">
                                                                        <label>Account</label>
                                                                        <select class="form-control debit-account" name="debit_accounts[]" required>
                                                                            <option value="">Select Account</option>
                                                                            <?php foreach ($accounts_data as $account): ?>
                                                                            <option value="<?php echo $account['id']; ?>"><?php echo $account['account_category'] . ' - ' . $account['account_name']; ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label>Amount</label>
                                                                        <input type="number" class="form-control debit-amount" name="debit_amounts[]" step="0.01" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>Action</label>
                                                                        <button type="button" class="btn btn-danger btn-sm remove-entry" disabled>
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Description</label>
                                                                <input type="text" class="form-control" name="debit_descriptions[]">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <button type="button" class="btn btn-info btn-sm add-debit-entry">
                                                        <i class="fas fa-plus"></i> Add Debit Entry
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="card entry-section credit-section">
                                                    <div class="card-header">
                                                        <h6 class="card-title">Credit Entries</h6>
                                                    </div>
                                                    <div class="card-body" id="creditEntries">
                                                        <div class="entry-row">
                                                            <div class="row">
                                                                <div class="col-md-5">
                                                                    <div class="form-group">
                                                                        <label>Account</label>
                                                                        <select class="form-control credit-account" name="credit_accounts[]" required>
                                                                            <option value="">Select Account</option>
                                                                            <?php foreach ($accounts_data as $account): ?>
                                                                            <option value="<?php echo $account['id']; ?>"><?php echo $account['account_category'] . ' - ' . $account['account_name']; ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label>Amount</label>
                                                                        <input type="number" class="form-control credit-amount" name="credit_amounts[]" step="0.01" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-group">
                                                                        <label>Action</label>
                                                                        <button type="button" class="btn btn-danger btn-sm remove-entry" disabled>
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Description</label>
                                                                <input type="text" class="form-control" name="credit_descriptions[]">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <button type="button" class="btn btn-info btn-sm add-credit-entry">
                                                        <i class="fas fa-plus"></i> Add Credit Entry
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="balance-info">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <span class="balance-label">Total Debit:</span>
                                                    <span class="balance-value" id="totalDebit">0.00</span>
                                                </div>
                                                <div class="col-md-6">
                                                    <span class="balance-label">Total Credit:</span>
                                                    <span class="balance-value" id="totalCredit">0.00</span>
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-md-12 text-center">
                                                    <span class="balance-label">Balance:</span>
                                                    <span class="balance-value" id="balance">0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group mt-4">
                                            <button type="submit" name="add_transaction" class="btn btn-success" id="submitTransaction" disabled>Add Transaction</button>
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Chart of Accounts Content -->
                <?php if ($page == 'accounts'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Chart of Accounts</h5>
                            <div class="card-tools">
                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addAccountModal">
                                    <i class="fas fa-plus"></i> Add Account
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="accountsTable">
                                    <thead>
                                        <tr>
                                            <th>Account Category</th>
                                            <th>Account Name</th>
                                            <th>Type</th>
                                            <th>Normal Balance</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $all_accounts_query = "SELECT * FROM chart_of_accounts ORDER BY account_code";
                                        $all_accounts_result = $conn->query($all_accounts_query);
                                        if ($all_accounts_result) {
                                            while ($account = $all_accounts_result->fetch_assoc()) {
                                        ?>
                                        <tr>
                                            <td><?php echo $account['account_category'] ?? 'None'; ?></td>
                                            <td><?php echo $account['account_name']; ?></td>
                                            <td><?php echo ucfirst($account['account_type']); ?></td>
                                            <td><?php echo ucfirst($account['normal_balance']); ?></td>
                                            <td>
                                                <?php if ($account['is_active']): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editAccountModal<?php echo $account['id']; ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="admin.php?page=accounts" method="post" style="display:inline;">
                                                    <input type="hidden" name="id" value="<?php echo $account['id']; ?>">
                                                    <button type="submit" name="delete_account" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this account?');">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Account Modal -->
                                        <div class="modal fade" id="editAccountModal<?php echo $account['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Account</h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="admin.php?page=accounts" method="post">
                                                            <input type="hidden" name="id" value="<?php echo $account['id']; ?>">
                                                            <div class="form-group">
                                                                <label>Account Name</label>
                                                                <input type="text" class="form-control" name="account_name" value="<?php echo $account['account_name']; ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Account Type</label>
                                                                <select class="form-control" name="account_type" required>
                                                                    <option value="asset" <?php echo $account['account_type'] == 'asset' ? 'selected' : ''; ?>>Asset</option>
                                                                    <option value="liability" <?php echo $account['account_type'] == 'liability' ? 'selected' : ''; ?>>Liability</option>
                                                                    <option value="equity" <?php echo $account['account_type'] == 'equity' ? 'selected' : ''; ?>>Equity</option>
                                                                    <option value="revenue" <?php echo $account['account_type'] == 'revenue' ? 'selected' : ''; ?>>Revenue</option>
                                                                    <option value="expense" <?php echo $account['account_type'] == 'expense' ? 'selected' : ''; ?>>Expense</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Account Category</label>
                                                                <select class="form-control" name="account_category">
                                                                    <option value="None" <?php echo empty($account['account_category']) ? 'selected' : ''; ?>>None</option>
                                                                    <option value="Cash" <?php echo $account['account_category'] == 'Cash' ? 'selected' : ''; ?>>Cash</option>
                                                                    <option value="Tuition Fee" <?php echo $account['account_category'] == 'Tuition Fee' ? 'selected' : ''; ?>>Tuition Fee</option>
                                                                    <option value="Miscellaneous Fee" <?php echo $account['account_category'] == 'Miscellaneous Fee' ? 'selected' : ''; ?>>Miscellaneous Fee</option>
                                                                    <option value="Other Fees" <?php echo $account['account_category'] == 'Other Fees' ? 'selected' : ''; ?>>Other Fees</option>
                                                                    <option value="Other Income" <?php echo $account['account_category'] == 'Other Income' ? 'selected' : ''; ?>>Other Income</option>
                                                                    <option value="Other Collection" <?php echo $account['account_category'] == 'Other Collection' ? 'selected' : ''; ?>>Other Collection</option>
                                                                    <option value="Operating Expense" <?php echo $account['account_category'] == 'Operating Expense' ? 'selected' : ''; ?>>Operating Expense</option>
                                                                    <option value="Education and Training Expense" <?php echo $account['account_category'] == 'Education and Training Expense' ? 'selected' : ''; ?>>Education and Training Expense</option>
                                                                    <option value="Miscellaneous Expense" <?php echo $account['account_category'] == 'Miscellaneous Expense' ? 'selected' : ''; ?>>Miscellaneous Expense</option>
                                                                    <option value="Other Expense" <?php echo $account['account_category'] == 'Other Expense' ? 'selected' : ''; ?>>Other Expense</option>
                                                                    <option value="Meetings and Representations" <?php echo $account['account_category'] == 'Meetings and Representations' ? 'selected' : ''; ?>>Meetings and Representations</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Normal Balance</label>
                                                                <select class="form-control" name="normal_balance" required>
                                                                    <option value="debit" <?php echo $account['normal_balance'] == 'debit' ? 'selected' : ''; ?>>Debit</option>
                                                                    <option value="credit" <?php echo $account['normal_balance'] == 'credit' ? 'selected' : ''; ?>>Credit</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Parent Account</label>
                                                                <select class="form-control" name="parent_account_id">
                                                                    <option value="">None</option>
                                                                    <?php foreach ($accounts_data as $parent_account): ?>
                                                                        <?php if ($parent_account['id'] != $account['id']): ?>
                                                                            <option value="<?php echo $parent_account['id']; ?>" <?php echo $account['parent_account_id'] == $parent_account['id'] ? 'selected' : ''; ?>>
                                                                                <?php echo $parent_account['account_category'] . ' - ' . $parent_account['account_name']; ?>
                                                                            </option>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Description</label>
                                                                <textarea class="form-control" name="description" rows="2"><?php echo $account['description']; ?></textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input class="custom-control-input" type="checkbox" id="isActive<?php echo $account['id']; ?>" name="is_active" value="1" <?php echo $account['is_active'] ? 'checked' : ''; ?>>
                                                                    <label for="isActive<?php echo $account['id']; ?>" class="custom-control-label">Active</label>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <button type="submit" name="update_account" class="btn btn-success">Update Account</button>
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php 
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Add Account Modal -->
                    <div class="modal fade" id="addAccountModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add New Account</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form action="admin.php?page=accounts" method="post">
                                        <div class="form-group">
                                            <label>Account Name</label>
                                            <input type="text" class="form-control" name="account_name" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Account Type</label>
                                            <select class="form-control" name="account_type" required>
                                                <option value="asset">Asset</option>
                                                <option value="liability">Liability</option>
                                                <option value="equity">Equity</option>
                                                <option value="revenue">Revenue</option>
                                                <option value="expense">Expense</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Account Category</label>
                                            <select class="form-control" name="account_category">
                                                <option value="None">None</option>
                                                <option value="Cash">Cash</option>
                                                <option value="Tuition Fee">Tuition Fee</option>
                                                <option value="Miscellaneous Fee">Miscellaneous Fee</option>
                                                <option value="Other Fees">Other Fees</option>
                                                <option value="Other Income">Other Income</option>
                                                <option value="Other Collection">Other Collection</option>
                                                <option value="Operating Expense">Operating Expense</option>
                                                <option value="Education and Training Expense">Education and Training Expense</option>
                                                <option value="Miscellaneous Expense">Miscellaneous Expense</option>
                                                <option value="Other Expense">Other Expense</option>
                                                <option value="Meetings and Representations">Meetings and Representations</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Normal Balance</label>
                                            <select class="form-control" name="normal_balance" required>
                                                <option value="debit">Debit</option>
                                                <option value="credit">Credit</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Parent Account</label>
                                            <select class="form-control" name="parent_account_id">
                                                <option value="">None</option>
                                                <?php foreach ($accounts_data as $account): ?>
                                                <option value="<?php echo $account['id']; ?>"><?php echo $account['account_category'] . ' - ' . $account['account_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Description</label>
                                            <textarea class="form-control" name="description" rows="2"></textarea>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" name="add_account" class="btn btn-success btn-block">Add Account</button>
                                            <button type="button" class="btn btn-secondary btn-block" data-dismiss="modal">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Reports Content -->
                <?php if ($page == 'reports'): ?>
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="reportsTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="journal-entries-tab" data-toggle="tab" href="#journal-entries" role="tab" aria-controls="journal-entries" aria-selected="true">Journal Entries</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="ledger-tab" data-toggle="tab" href="#ledger" role="tab" aria-controls="ledger" aria-selected="false">Ledger</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="trial-balance-tab" data-toggle="tab" href="#trial-balance" role="tab" aria-controls="trial-balance" aria-selected="false">Trial Balance</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="income-statement-tab" data-toggle="tab" href="#income-statement" role="tab" aria-controls="income-statement" aria-selected="false">Income Statement</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="balance-sheet-tab" data-toggle="tab" href="#balance-sheet" role="tab" aria-controls="balance-sheet" aria-selected="false">Balance Sheet</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="cashflow-tab" data-toggle="tab" href="#cashflow" role="tab" aria-controls="cashflow" aria-selected="false">Cashflow Statement</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="equity-tab" data-toggle="tab" href="#equity" role="tab" aria-controls="equity" aria-selected="false">Statement of Changes in Equity</a>
                        </li>
                    </ul>
                    
                    <!-- Tab panes -->
                    <div class="tab-content" id="reportsTabsContent">
                        <!-- Journal Entries Report -->
                        <div class="tab-pane fade show active" id="journal-entries" role="tabpanel" aria-labelledby="journal-entries-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Journal Entries</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="reports.php" target="_blank">
                                        <input type="hidden" name="report_type" value="journal-entries">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Start Date</label>
                                                    <input type="date" class="form-control" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>End Date</label>
                                                    <input type="date" class="form-control" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="submit" class="btn btn-success btn-block">Generate Report</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <div class="table-responsive mt-4">
                                        <table class="table table-bordered report-table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Reference</th>
                                                    <th>Description</th>
                                                    <th>Account</th>
                                                    <th>Debit</th>
                                                    <th>Credit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
                                                $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
                                                
                                                $journal_query = "SELECT t.transaction_date, t.reference_no, t.description, 
                                                                      coa.account_name, coa.account_category, te.entry_type, te.amount
                                                                      FROM transaction_entries te
                                                                      JOIN transactions t ON te.transaction_id = t.id
                                                                      JOIN chart_of_accounts coa ON te.account_id = coa.id
                                                                      WHERE t.transaction_date BETWEEN '$start_date' AND '$end_date'
                                                                      ORDER BY t.transaction_date, t.id";
                                                $journal_result = $conn->query($journal_query);
                                                
                                                if ($journal_result) {
                                                    while ($entry = $journal_result->fetch_assoc()) {
                                                        echo "<tr>";
                                                        echo "<td>" . date('M d, Y', strtotime($entry['transaction_date'])) . "</td>";
                                                        echo "<td>" . $entry['reference_no'] . "</td>";
                                                        echo "<td>" . $entry['description'] . "</td>";
                                                        echo "<td>" . ($entry['account_category'] ?? 'None') . " - " . $entry['account_name'] . "</td>";
                                                        
                                                        if ($entry['entry_type'] == 'debit') {
                                                            echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($entry['amount'], 2) . "</td>";
                                                            echo "<td></td>";
                                                        } else {
                                                            echo "<td></td>";
                                                            echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($entry['amount'], 2) . "</td>";
                                                        }
                                                        
                                                        echo "</tr>";
                                                    }
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ledger Report -->
                        <div class="tab-pane fade" id="ledger" role="tabpanel" aria-labelledby="ledger-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Ledger</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="reports.php" target="_blank">
                                        <input type="hidden" name="report_type" value="ledger">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Start Date</label>
                                                    <input type="date" class="form-control" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>End Date</label>
                                                    <input type="date" class="form-control" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Account</label>
                                                    <select class="form-control" name="account_id">
                                                        <option value="">All Accounts</option>
                                                        <?php foreach ($accounts_data as $account): ?>
                                                        <option value="<?php echo $account['id']; ?>" <?php echo (isset($_GET['account_id']) && $_GET['account_id'] == $account['id']) ? 'selected' : ''; ?>>
                                                            <?php echo ($account['account_category'] ?? 'None') . ' - ' . $account['account_name']; ?>
                                                        </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="submit" class="btn btn-success btn-block">Generate Report</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <div class="mt-4">
                                        <?php
                                        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
                                        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
                                        $account_id = isset($_GET['account_id']) ? $_GET['account_id'] : '';
                                        
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
                                                echo "<div class='report-section'>";
                                                echo "<h5>" . $account['account_code'] . " - " . ($account['account_category'] ?? 'None') . " - " . $account['account_name'] . "</h5>";
                                                
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
                                                echo "<table class='table table-bordered report-table'>";
                                                echo "<thead>";
                                                echo "<tr>";
                                                echo "<th>Date</th>";
                                                echo "<th>Reference</th>";
                                                echo "<th>Description</th>";
                                                echo "<th>Debit</th>";
                                                echo "<th>Credit</th>";
                                                echo "<th>Balance</th>";
                                                echo "</tr>";
                                                echo "</thead>";
                                                echo "<tbody>";
                                                
                                                // Opening balance row
                                                echo "<tr class='report-total'>";
                                                echo "<td colspan='3'><strong>Opening Balance</strong></td>";
                                                if ($opening_balance > 0) {
                                                    if ($account['normal_balance'] == 'debit') {
                                                        echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($opening_balance, 2) . "</strong></td>";
                                                        echo "<td></td>";
                                                    } else {
                                                        echo "<td></td>";
                                                        echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($opening_balance, 2) . "</strong></td>";
                                                    }
                                                } else {
                                                    $abs_balance = abs($opening_balance);
                                                    if ($account['normal_balance'] == 'debit') {
                                                        echo "<td></td>";
                                                        echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($abs_balance, 2) . "</strong></td>";
                                                    } else {
                                                        echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($abs_balance, 2) . "</strong></td>";
                                                        echo "<td></td>";
                                                    }
                                                }
                                                echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($opening_balance, 2) . "</strong></td>";
                                                echo "</tr>";
                                                
                                                // Get transactions for the account
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
                                                        echo "<td>" . $transaction['reference_no'] . "</td>";
                                                        echo "<td>" . $transaction['description'] . "</td>";
                                                        
                                                        if ($transaction['entry_type'] == 'debit') {
                                                            echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($transaction['amount'], 2) . "</td>";
                                                            echo "<td></td>";
                                                            $running_balance += $transaction['amount'];
                                                        } else {
                                                            echo "<td></td>";
                                                            echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($transaction['amount'], 2) . "</td>";
                                                            $running_balance -= $transaction['amount'];
                                                        }
                                                        
                                                        echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($running_balance, 2) . "</td>";
                                                        echo "</tr>";
                                                    }
                                                }
                                                
                                                // Closing balance row
                                                echo "<tr class='report-total'>";
                                                echo "<td colspan='3'><strong>Closing Balance</strong></td>";
                                                if ($running_balance > 0) {
                                                    if ($account['normal_balance'] == 'debit') {
                                                        echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($running_balance, 2) . "</strong></td>";
                                                        echo "<td></td>";
                                                    } else {
                                                        echo "<td></td>";
                                                        echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($running_balance, 2) . "</strong></td>";
                                                    }
                                                } else {
                                                    $abs_balance = abs($running_balance);
                                                    if ($account['normal_balance'] == 'debit') {
                                                        echo "<td></td>";
                                                        echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($abs_balance, 2) . "</strong></td>";
                                                    } else {
                                                        echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($abs_balance, 2) . "</strong></td>";
                                                        echo "<td></td>";
                                                    }
                                                }
                                                echo "<td class='currency'><strong>" . $system_settings['currency_symbol'] . number_format($running_balance, 2) . "</strong></td>";
                                                echo "</tr>";
                                                
                                                echo "</tbody>";
                                                echo "</table>";
                                                echo "</div>";
                                                echo "</div>";
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Trial Balance Report -->
                        <div class="tab-pane fade" id="trial-balance" role="tabpanel" aria-labelledby="trial-balance-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Trial Balance</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="reports.php" target="_blank">
                                        <input type="hidden" name="report_type" value="trial-balance">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Start Date</label>
                                                    <input type="date" class="form-control" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>End Date</label>
                                                    <input type="date" class="form-control" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="submit" class="btn btn-success btn-block">Generate Report</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <div class="table-responsive mt-4">
                                        <table class="table table-bordered report-table">
                                            <thead>
                                                <tr>
                                                    <th>Account Code</th>
                                                    <th>Account Name</th>
                                                    <th>Debit</th>
                                                    <th>Credit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
                                                $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
                                                
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
                                                        echo "<td>" . $account['account_code'] . "</td>";
                                                        echo "<td>" . $account['account_name'] . "</td>";
                                                        echo "<td class='currency'>" . ($debit > 0 ? $system_settings['currency_symbol'] . number_format($debit, 2) : '') . "</td>";
                                                        echo "<td class='currency'>" . ($credit > 0 ? $system_settings['currency_symbol'] . number_format($credit, 2) : '') . "</td>";
                                                        echo "</tr>";
                                                    }
                                                }
                                                ?>
                                                <tr class="report-total">
                                                    <td colspan="2"><strong>Totals</strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($total_debit, 2); ?></strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($total_credit, 2); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Income Statement Report -->
                        <div class="tab-pane fade" id="income-statement" role="tabpanel" aria-labelledby="income-statement-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Income Statement</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="reports.php" target="_blank">
                                        <input type="hidden" name="report_type" value="income-statement">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Start Date</label>
                                                    <input type="date" class="form-control" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>End Date</label>
                                                    <input type="date" class="form-control" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="submit" class="btn btn-success btn-block">Generate Report</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <div class="table-responsive mt-4">
                                        <table class="table table-bordered report-table">
                                            <thead>
                                                <tr>
                                                    <th>Account</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
                                                $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
                                                
                                                // Revenue
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
                                                        echo "<td>" . ($account['account_category'] ?? 'None') . " - " . $account['account_name'] . "</td>";
                                                        echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($account['total'], 2) . "</td>";
                                                        echo "</tr>";
                                                        $total_revenue += $account['total'];
                                                    }
                                                }
                                                
                                                // Expenses
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
                                                        echo "<td>" . ($account['account_category'] ?? 'None') . " - " . $account['account_name'] . "</td>";
                                                        echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($account['total'], 2) . "</td>";
                                                        echo "</tr>";
                                                        $total_expenses += $account['total'];
                                                    }
                                                }
                                                
                                                $net_income = $total_revenue - $total_expenses;
                                                ?>
                                                <tr class="report-total">
                                                    <td><strong>Total Revenue</strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($total_revenue, 2); ?></strong></td>
                                                </tr>
                                                <tr class="report-total">
                                                    <td><strong>Total Expenses</strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($total_expenses, 2); ?></strong></td>
                                                </tr>
                                                <tr class="report-total">
                                                    <td><strong>Net Income</strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($net_income, 2); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Balance Sheet Report -->
                        <div class="tab-pane fade" id="balance-sheet" role="tabpanel" aria-labelledby="balance-sheet-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Balance Sheet</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="reports.php" target="_blank">
                                        <input type="hidden" name="report_type" value="balance-sheet">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Start Date</label>
                                                    <input type="date" class="form-control" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>End Date</label>
                                                    <input type="date" class="form-control" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="submit" class="btn btn-success btn-block">Generate Report</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <div class="table-responsive mt-4">
                                        <table class="table table-bordered report-table">
                                            <thead>
                                                <tr>
                                                    <th>Account</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
                                                $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
                                                
                                                // Assets
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
                                                        echo "<td>" . ($account['account_category'] ?? 'None') . " - " . $account['account_name'] . "</td>";
                                                        echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($account['total'], 2) . "</td>";
                                                        echo "</tr>";
                                                        $total_assets += $account['total'];
                                                    }
                                                }
                                                
                                                // Liabilities
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
                                                        echo "<td>" . ($account['account_category'] ?? 'None') . " - " . $account['account_name'] . "</td>";
                                                        echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($account['total'], 2) . "</td>";
                                                        echo "</tr>";
                                                        $total_liabilities += $account['total'];
                                                    }
                                                }
                                                
                                                // Equity
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
                                                        echo "<td>" . ($account['account_category'] ?? 'None') . " - " . $account['account_name'] . "</td>";
                                                        echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($account['total'], 2) . "</td>";
                                                        echo "</tr>";
                                                        $total_equity += $account['total'];
                                                    }
                                                }
                                                
                                                // Add net income to equity
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
                                                
                                                $total_equity += $net_income;
                                                
                                                $total_liabilities_equity = $total_liabilities + $total_equity;
                                                ?>
                                                <tr class="report-total">
                                                    <td><strong>Total Assets</strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($total_assets, 2); ?></strong></td>
                                                </tr>
                                                <tr class="report-total">
                                                    <td><strong>Total Liabilities</strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($total_liabilities, 2); ?></strong></td>
                                                </tr>
                                                <tr class="report-total">
                                                    <td><strong>Total Equity</strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($total_equity, 2); ?></strong></td>
                                                </tr>
                                                <tr class="report-total">
                                                    <td><strong>Total Liabilities & Equity</strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($total_liabilities_equity, 2); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cashflow Statement Report -->
                        <div class="tab-pane fade" id="cashflow" role="tabpanel" aria-labelledby="cashflow-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Cashflow Statement</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="reports.php" target="_blank">
                                        <input type="hidden" name="report_type" value="cashflow">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Start Date</label>
                                                    <input type="date" class="form-control" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>End Date</label>
                                                    <input type="date" class="form-control" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="submit" class="btn btn-success btn-block">Generate Report</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <div class="table-responsive mt-4">
                                        <table class="table table-bordered report-table">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
                                                $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
                                                
                                                // Operating Activities
                                                $operating_revenue_query = "SELECT SUM(te.amount) as total 
                                                                             FROM transaction_entries te 
                                                                             JOIN transactions t ON te.transaction_id = t.id
                                                                             JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                                             WHERE coa.account_type = 'revenue' AND te.entry_type = 'credit'
                                                                             AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
                                                $operating_revenue_result = $conn->query($operating_revenue_query);
                                                $operating_revenue = 0;
                                                if ($operating_revenue_result) {
                                                    $operating_revenue = $operating_revenue_result->fetch_assoc()['total'] ?? 0;
                                                }
                                                
                                                $operating_expense_query = "SELECT SUM(te.amount) as total 
                                                                            FROM transaction_entries te 
                                                                            JOIN transactions t ON te.transaction_id = t.id
                                                                            JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                                            WHERE coa.account_type = 'expense' AND te.entry_type = 'debit'
                                                                            AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
                                                $operating_expense_result = $conn->query($operating_expense_query);
                                                $operating_expense = 0;
                                                if ($operating_expense_result) {
                                                    $operating_expense = $operating_expense_result->fetch_assoc()['total'] ?? 0;
                                                }
                                                
                                                $net_operating = $operating_revenue - $operating_expense;
                                                
                                                // Investing Activities (simplified - would need more specific accounts in a real system)
                                                $investing_query = "SELECT SUM(te.amount) as total 
                                                                    FROM transaction_entries te 
                                                                    JOIN transactions t ON te.transaction_id = t.id
                                                                    JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                                    WHERE coa.account_type = 'asset' AND te.entry_type = 'debit'
                                                                    AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
                                                $investing_result = $conn->query($investing_query);
                                                $investing_outflow = 0;
                                                if ($investing_result) {
                                                    $investing_outflow = $investing_result->fetch_assoc()['total'] ?? 0;
                                                }
                                                
                                                // Financing Activities (simplified - would need more specific accounts in a real system)
                                                $financing_query = "SELECT SUM(te.amount) as total 
                                                                   FROM transaction_entries te 
                                                                   JOIN transactions t ON te.transaction_id = t.id
                                                                   JOIN chart_of_accounts coa ON te.account_id = coa.id 
                                                                   WHERE coa.account_type = 'liability' AND te.entry_type = 'credit'
                                                                   AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
                                                $financing_result = $conn->query($financing_query);
                                                $financing_inflow = 0;
                                                if ($financing_result) {
                                                    $financing_inflow = $financing_result->fetch_assoc()['total'] ?? 0;
                                                }
                                                
                                                $net_cash_flow = $net_operating - $investing_outflow + $financing_inflow;
                                                ?>
                                                <tr>
                                                    <td><strong>Operating Activities</strong></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td class="indent-1">Revenue</td>
                                                    <td class='currency'><?php echo $system_settings['currency_symbol'] . number_format($operating_revenue, 2); ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="indent-1">Expenses</td>
                                                    <td class='currency'><?php echo $system_settings['currency_symbol'] . number_format($operating_expense, 2); ?></td>
                                                </tr>
                                                <tr class="report-total">
                                                    <td class="indent-1"><strong>Net Cash from Operating Activities</strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($net_operating, 2); ?></strong></td>
                                                </tr>
                                                
                                                <tr>
                                                    <td><strong>Investing Activities</strong></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td class="indent-1">Asset Purchases</td>
                                                    <td class='currency'><?php echo $system_settings['currency_symbol'] . number_format($investing_outflow, 2); ?></td>
                                                </tr>
                                                <tr class="report-total">
                                                    <td class="indent-1"><strong>Net Cash from Investing Activities</strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format(-$investing_outflow, 2); ?></strong></td>
                                                </tr>
                                                
                                                <tr>
                                                    <td><strong>Financing Activities</strong></td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td class="indent-1">Liabilities</td>
                                                    <td class='currency'><?php echo $system_settings['currency_symbol'] . number_format($financing_inflow, 2); ?></td>
                                                </tr>
                                                <tr class="report-total">
                                                    <td class="indent-1"><strong>Net Cash from Financing Activities</strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($financing_inflow, 2); ?></strong></td>
                                                </tr>
                                                
                                                <tr class="report-total">
                                                    <td><strong>Net Increase in Cash</strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($net_cash_flow, 2); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Statement of Changes in Equity Report -->
                        <div class="tab-pane fade" id="equity" role="tabpanel" aria-labelledby="equity-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Statement of Changes in Equity</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="reports.php" target="_blank">
                                        <input type="hidden" name="report_type" value="equity">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Start Date</label>
                                                    <input type="date" class="form-control" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>End Date</label>
                                                    <input type="date" class="form-control" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="submit" class="btn btn-success btn-block">Generate Report</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <div class="table-responsive mt-4">
                                        <table class="table table-bordered report-table">
                                            <thead>
                                                <tr>
                                                    <th>Equity Component</th>
                                                    <th>Beginning Balance</th>
                                                    <th>Additions</th>
                                                    <th>Deductions</th>
                                                    <th>Ending Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
                                                $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
                                                
                                                // Get equity accounts
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
                                                        // Beginning balance
                                                        $beginning_query = "SELECT SUM(CASE WHEN te.entry_type = 'credit' THEN te.amount ELSE -te.amount END) as balance
                                                                           FROM transaction_entries te
                                                                           JOIN transactions t ON te.transaction_id = t.id
                                                                           WHERE te.account_id = " . $account['id'] . "
                                                                           AND t.transaction_date < '$start_date'";
                                                        $beginning_result = $conn->query($beginning_query);
                                                        $beginning_balance = 0;
                                                        if ($beginning_result) {
                                                            $beginning_balance = $beginning_result->fetch_assoc()['balance'] ?? 0;
                                                        }
                                                        
                                                        // Additions (credits during the period)
                                                        $additions_query = "SELECT SUM(te.amount) as total
                                                                           FROM transaction_entries te
                                                                           JOIN transactions t ON te.transaction_id = t.id
                                                                           WHERE te.account_id = " . $account['id'] . "
                                                                           AND te.entry_type = 'credit'
                                                                           AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
                                                        $additions_result = $conn->query($additions_query);
                                                        $additions = 0;
                                                        if ($additions_result) {
                                                            $additions = $additions_result->fetch_assoc()['total'] ?? 0;
                                                        }
                                                        
                                                        // Deductions (debits during the period)
                                                        $deductions_query = "SELECT SUM(te.amount) as total
                                                                            FROM transaction_entries te
                                                                            JOIN transactions t ON te.transaction_id = t.id
                                                                            WHERE te.account_id = " . $account['id'] . "
                                                                            AND te.entry_type = 'debit'
                                                                            AND t.transaction_date BETWEEN '$start_date' AND '$end_date'";
                                                        $deductions_result = $conn->query($deductions_query);
                                                        $deductions = 0;
                                                        if ($deductions_result) {
                                                            $deductions = $deductions_result->fetch_assoc()['total'] ?? 0;
                                                        }
                                                        
                                                        // Ending balance
                                                        $ending_balance = $beginning_balance + $additions - $deductions;
                                                        
                                                        $total_beginning += $beginning_balance;
                                                        $total_additions += $additions;
                                                        $total_deductions += $deductions;
                                                        $total_ending += $ending_balance;
                                                        
                                                        echo "<tr>";
                                                        echo "<td>" . ($account['account_category'] ?? 'None') . " - " . $account['account_name'] . "</td>";
                                                        echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($beginning_balance, 2) . "</td>";
                                                        echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($additions, 2) . "</td>";
                                                        echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($deductions, 2) . "</td>";
                                                        echo "<td class='currency'>" . $system_settings['currency_symbol'] . number_format($ending_balance, 2) . "</td>";
                                                        echo "</tr>";
                                                    }
                                                }
                                                
                                                // Add net income as an addition to equity
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
                                                
                                                $total_additions += $net_income;
                                                $total_ending += $net_income;
                                                ?>
                                                <tr>
                                                    <td><strong>Net Income</strong></td>
                                                    <td></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($net_income, 2); ?></strong></td>
                                                    <td></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($net_income, 2); ?></strong></td>
                                                </tr>
                                                
                                                <tr class="report-total">
                                                    <td><strong>Total Equity</strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($total_beginning, 2); ?></strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($total_additions, 2); ?></strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($total_deductions, 2); ?></strong></td>
                                                    <td class='currency'><strong><?php echo $system_settings['currency_symbol'] . number_format($total_ending, 2); ?></strong></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Settings Content -->
                <?php if ($page == 'settings'): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">System Settings</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="form-group">
                                            <label>Company Name</label>
                                            <input type="text" class="form-control" name="company_name" value="<?php echo $system_settings['company_name']; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Address</label>
                                            <textarea class="form-control" name="company_address" rows="2"><?php echo $system_settings['company_address']; ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>Contact Email</label>
                                            <input type="email" class="form-control" name="company_email" value="<?php echo $system_settings['company_email']; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Contact Phone</label>
                                            <input type="text" class="form-control" name="company_phone" value="<?php echo $system_settings['company_phone']; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Currency</label>
                                            <select class="form-control" name="currency" required>
                                                <option value="PHP" <?php echo $system_settings['currency'] == 'PHP' ? 'selected' : ''; ?>>Philippine Peso (PHP)</option>
                                                <option value="USD" <?php echo $system_settings['currency'] == 'USD' ? 'selected' : ''; ?>>US Dollar (USD)</option>
                                                <option value="EUR" <?php echo $system_settings['currency'] == 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                                                <option value="GBP" <?php echo $system_settings['currency'] == 'GBP' ? 'selected' : ''; ?>>British Pound (GBP)</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Currency Symbol</label>
                                            <input type="text" class="form-control" name="currency_symbol" value="<?php echo $system_settings['currency_symbol']; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" name="update_settings" class="btn btn-success">Save Settings</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">User Profile</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="form-group">
                                            <label>First Name</label>
                                            <input type="text" class="form-control" name="firstname" value="<?php echo $user['firstname']; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Last Name</label>
                                            <input type="text" class="form-control" name="lastname" value="<?php echo $user['lastname']; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" class="form-control" name="email" value="<?php echo $user['email'] ?? ''; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Username</label>
                                            <input type="text" class="form-control" name="username" value="<?php echo $user['username']; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label>New Password</label>
                                            <input type="password" class="form-control" name="password">
                                            <small class="form-text text-muted">Leave blank to keep current password</small>
                                        </div>
                                        <div class="form-group">
                                            <label>Confirm New Password</label>
                                            <input type="password" class="form-control" name="confirm_password">
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" name="update_profile" class="btn btn-success">Update Profile</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Employee Management Section -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Employee Management</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>First Name</label>
                                                    <input type="text" class="form-control" name="employee_firstname" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Last Name</label>
                                                    <input type="text" class="form-control" name="employee_lastname" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Position</label>
                                                    <input type="text" class="form-control" name="employee_position" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Username</label>
                                                    <input type="text" class="form-control" name="employee_username" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="email" class="form-control" name="employee_email" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Password</label>
                                                    <input type="password" class="form-control" name="employee_password" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" name="add_employee" class="btn btn-success">Add Employee</button>
                                        </div>
                                    </form>

                                    <div class="table-responsive mt-4">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Position</th>
                                                    <th>Username</th>
                                                    <th>Email</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($employees_data as $emp): ?>
                                                <tr>
                                                    <td><?php echo $emp['firstname'] . ' ' . $emp['lastname']; ?></td>
                                                    <td><?php echo $emp['position']; ?></td>
                                                    <td><?php echo $emp['username']; ?></td>
                                                    <td><?php echo $emp['email']; ?></td>
                                                    <td>
                                                        <?php if ($emp['is_active']): ?>
                                                            <span class="badge badge-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-danger">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <form action="admin.php?page=settings" method="post" style="display:inline;">
                                                            <input type="hidden" name="employee_id" value="<?php echo $emp['id']; ?>">
                                                            <button type="submit" name="delete_employee" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this employee?');">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
        <strong>Copyright &copy; <?php echo date('Y'); ?> <?php echo $system_settings['company_name']; ?>.</strong> All rights reserved.
    </footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTables
        $('#transactionsTable').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "pageLength": 10
        });
        
        $('#accountsTable').DataTable({
            "responsive": true,
            "lengthChange": false,
            "autoWidth": false,
            "pageLength": 10
        });
        
        // Toast notification system
        <?php if (isset($_SESSION['toast'])): ?>
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            
            toastr["<?php echo $_SESSION['toast']['type']; ?>"]("<?php echo $_SESSION['toast']['message']; ?>");
            <?php unset($_SESSION['toast']); ?>
        <?php endif; ?>
        
        // Initialize Charts for Dashboard
        <?php if ($page == 'dashboard'): ?>
            // Expense Line Chart
            var expenseCtx = document.getElementById('expenseChart').getContext('2d');
            var expenseChart = new Chart(expenseCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: [{
                        label: 'Monthly Expenses',
                        data: <?php echo json_encode($monthly_expenses); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '<?php echo $system_settings['currency_symbol']; ?>' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, values) {
                                    return '<?php echo $system_settings['currency_symbol']; ?>' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            
            // Revenue Bar Chart
            var revenueCtx = document.getElementById('revenueChart').getContext('2d');
            var revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: [{
                        label: 'Monthly Revenue',
                        data: <?php echo json_encode($monthly_revenue); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '<?php echo $system_settings['currency_symbol']; ?>' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, values) {
                                    return '<?php echo $system_settings['currency_symbol']; ?>' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            
            // Net Income Pie Chart
            var netIncomeCtx = document.getElementById('netIncomeChart').getContext('2d');
            var netIncomeChart = new Chart(netIncomeCtx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: [{
                        label: 'Net Income',
                        data: <?php echo json_encode($monthly_net_income); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(199, 199, 199, 0.7)',
                            'rgba(83, 102, 255, 0.7)',
                            'rgba(40, 159, 64, 0.7)',
                            'rgba(210, 99, 132, 0.7)',
                            'rgba(20, 162, 235, 0.7)',
                            'rgba(255, 106, 86, 0.7)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(199, 199, 199, 1)',
                            'rgba(83, 102, 255, 1)',
                            'rgba(40, 159, 64, 1)',
                            'rgba(210, 99, 132, 1)',
                            'rgba(20, 162, 235, 1)',
                            'rgba(255, 106, 86, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': <?php echo $system_settings['currency_symbol']; ?>' + context.parsed.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        <?php endif; ?>
        
        // Transaction entry management
        let debitEntryCount = 1;
        let creditEntryCount = 1;
        
        // Add debit entry
        $(document).on('click', '.add-debit-entry', function() {
            let containerId = $(this).data('transaction-id') ? '#editDebitEntries' + $(this).data('transaction-id') : '#debitEntries';
            let newEntry = createEntryRow('debit', debitEntryCount++);
            $(containerId).append(newEntry);
            updateRemoveButtons();
            updateBalance();
        });
        
        // Add credit entry
        $(document).on('click', '.add-credit-entry', function() {
            let containerId = $(this).data('transaction-id') ? '#editCreditEntries' + $(this).data('transaction-id') : '#creditEntries';
            let newEntry = createEntryRow('credit', creditEntryCount++);
            $(containerId).append(newEntry);
            updateRemoveButtons();
            updateBalance();
        });
        
        // Remove entry
        $(document).on('click', '.remove-entry', function() {
            $(this).closest('.entry-row').remove();
            updateRemoveButtons();
            updateBalance();
        });
        
        // Update balance when amount changes
        $(document).on('input', '.debit-amount, .credit-amount', function() {
            updateBalance();
        });
        
        // Create entry row HTML
        function createEntryRow(type, index) {
            let accountsSelect = '<select class="form-control ' + type + '-account" name="' + type + '_accounts[]" required>' +
                                '<option value="">Select Account</option>';
            
            <?php foreach ($accounts_data as $account): ?>
                accountsSelect += '<option value="<?php echo $account['id']; ?>"><?php echo addslashes(($account['account_category'] ?? 'None') . ' - ' . $account['account_name']); ?></option>';
            <?php endforeach; ?>
            
            accountsSelect += '</select>';
            
            return '<div class="entry-row">' +
                   '<div class="row">' +
                   '<div class="col-md-5">' +
                   '<div class="form-group">' +
                   '<label>Account</label>' +
                   accountsSelect +
                   '</div>' +
                   '</div>' +
                   '<div class="col-md-4">' +
                   '<div class="form-group">' +
                   '<label>Amount</label>' +
                   '<input type="number" class="form-control ' + type + '-amount" name="' + type + '_amounts[]" step="0.01" required>' +
                   '</div>' +
                   '</div>' +
                   '<div class="col-md-3">' +
                   '<div class="form-group">' +
                   '<label>Action</label>' +
                   '<button type="button" class="btn btn-danger btn-sm remove-entry">' +
                   '<i class="fas fa-trash"></i>' +
                   '</button>' +
                   '</div>' +
                   '</div>' +
                   '</div>' +
                   '<div class="form-group">' +
                   '<label>Description</label>' +
                   '<input type="text" class="form-control" name="' + type + '_descriptions[]">' +
                   '</div>' +
                   '</div>';
        }
        
        // Update remove buttons (disable if only one entry)
        function updateRemoveButtons() {
            $('.debit-section .remove-entry').each(function() {
                $(this).prop('disabled', $('.debit-section .entry-row').length <= 1);
            });
            
            $('.credit-section .remove-entry').each(function() {
                $(this).prop('disabled', $('.credit-section .entry-row').length <= 1);
            });
        }
        
        // Update balance display
        function updateBalance() {
            let totalDebit = 0;
            let totalCredit = 0;
            
            // Calculate total debit
            $('.debit-amount').each(function() {
                let value = parseFloat($(this).val()) || 0;
                totalDebit += value;
            });
            
            // Calculate total credit
            $('.credit-amount').each(function() {
                let value = parseFloat($(this).val()) || 0;
                totalCredit += value;
            });
            
            // Update balance display
            $('#totalDebit, #editTotalDebit<?php echo $transaction['id'] ?? ''; ?>').text('<?php echo $system_settings['currency_symbol']; ?>' + totalDebit.toFixed(2));
            $('#totalCredit, #editTotalCredit<?php echo $transaction['id'] ?? ''; ?>').text('<?php echo $system_settings['currency_symbol']; ?>' + totalCredit.toFixed(2));
            
            let balance = totalDebit - totalCredit;
            let balanceText = '<?php echo $system_settings['currency_symbol']; ?>' + Math.abs(balance).toFixed(2);
            
            if (balance === 0) {
                $('#balance, #editBalance<?php echo $transaction['id'] ?? ''; ?>').text(balanceText).removeClass('unbalanced').addClass('balanced');
                $('#submitTransaction').prop('disabled', false);
            } else {
                $('#balance, #editBalance<?php echo $transaction['id'] ?? ''; ?>').text((balance > 0 ? 'Debit: ' : 'Credit: ') + balanceText).removeClass('balanced').addClass('unbalanced');
                $('#submitTransaction').prop('disabled', true);
            }
        }
        
        // Initialize balance on page load
        updateBalance();
    });
</script>
</body>
</html>