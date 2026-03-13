<?php
require_once 'config.php';
requireRole('admin');

// Fetch all users from different tables
 $system_users = $conn->query("SELECT * FROM users ORDER BY role, username");
 $teachers = $conn->query("SELECT * FROM teachers ORDER BY name");
 $program_heads = $conn->query("SELECT ph.*, d.dept_name FROM program_heads ph JOIN departments d ON ph.department_id = d.id ORDER BY ph.name");
 $students_with_credentials = $conn->query("SELECT id, id_number, CONCAT(last_name, ', ', first_name, ' ', middle_name) as full_name, email FROM students WHERE email IS NOT NULL ORDER BY last_name, first_name");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User List - Print View</title>
    <link rel="icon" href="uploads/csr.png" type="image/x-icon">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #004085;
            --secondary: #dc3545;
            --success: #004085;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #004085;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header-section {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .user-section {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .section-title {
            background-color: var(--info);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .user-table th {
            background-color: var(--light);
            padding: 12px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: bold;
        }
        
        .user-table td {
            padding: 10px 12px;
            border: 1px solid #dee2e6;
        }
        
        .user-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .print-button {
            background-color: var(--success);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        
        .print-button:hover {
            background-color: #003366;
        }
        
        .back-button {
            background-color: var(--secondary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-button:hover {
            background-color: #c82333;
            text-decoration: none;
            color: white;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .print-date {
            text-align: right;
            margin-bottom: 20px;
            font-style: italic;
        }
        
        .summary-box {
            background-color: var(--light);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .summary-item {
            display: inline-block;
            margin-right: 30px;
            margin-bottom: 10px;
        }
        
        .summary-label {
            font-weight: bold;
        }
        
        @media print {
            body {
                background-color: white;
            }
            
            .no-print {
                display: none !important;
            }
            
            .main-container {
                max-width: 100%;
                padding: 0;
            }
            
            .header-section {
                background-color: var(--primary) !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .section-title {
                background-color: var(--info) !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .user-table th {
                background-color: var(--light) !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .user-table tr:nth-child(even) {
                background-color: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .summary-box {
                background-color: var(--light) !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .user-section {
                box-shadow: none;
                margin-bottom: 30px;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
<div class="main-container">
    <!-- Header Section -->
    <div class="header-section no-print">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1>USER LIST</h1>
                <p>Complete list of all system users</p>
            </div>
        </div>
    </div>
    
    <!-- Print Date -->
    <div class="print-date">
        Printed on: <?= date('F j, Y, g:i a') ?>
    </div>
    
    <!-- Summary Box -->
    <div class="summary-box">
        <div class="summary-item">
            <span class="summary-label">System Users:</span> 
            <?= $system_users->num_rows ?>
        </div>
        <div class="summary-item">
            <span class="summary-label">Teachers:</span> 
            <?= $teachers->num_rows ?>
        </div>
        <div class="summary-item">
            <span class="summary-label">Program Heads:</span> 
            <?= $program_heads->num_rows ?>
        </div>
        <div class="summary-item">
            <span class="summary-label">Students with Credentials:</span> 
            <?= $students_with_credentials->num_rows ?>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Users:</span> 
            <?= $system_users->num_rows + $teachers->num_rows + $program_heads->num_rows + $students_with_credentials->num_rows ?>
        </div>
    </div>
    
    <!-- System Users Section -->
    <div class="user-section">
        <div class="section-title">
            <i class="fas fa-users"></i> System Users
        </div>
        
        <?php if ($system_users->num_rows > 0): ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th width="10%">ID</th>
                        <th width="30%">Username</th>
                        <th width="20%">Role</th>
                        <th width="40%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $system_users->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['username'] ?></td>
                        <td>
                            <span class="badge badge-<?= $row['role'] == 'admin' ? 'danger' : ($row['role'] == 'cashier' ? 'warning' : 'info') ?>">
                                <?= ucfirst($row['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-primary">Can access system administration</span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                No system users found.
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Teachers Section -->
    <div class="user-section">
        <div class="section-title">
            <i class="fas fa-chalkboard-teacher"></i> Teachers
        </div>
        
        <?php if ($teachers->num_rows > 0): ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th width="10%">ID</th>
                        <th width="20%">Employee ID</th>
                        <th width="30%">Name</th>
                        <th width="30%">Username</th>
                        <th width="10%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $teachers->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['employee_id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['username'] ?></td>
                        <td>
                            <span class="badge badge-success">Active</span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                No teachers found.
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Program Heads Section -->
    <div class="user-section">
        <div class="section-title">
            <i class="fas fa-user-tie"></i> Program Heads
        </div>
        
        <?php if ($program_heads->num_rows > 0): ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th width="10%">ID</th>
                        <th width="30%">Name</th>
                        <th width="30%">Username</th>
                        <th width="30%">Department</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $program_heads->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['username'] ?></td>
                        <td><?= $row['dept_name'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                No program heads found.
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Students Section -->
    <div class="user-section">
        <div class="section-title">
            <i class="fas fa-user-graduate"></i> Students with Login Credentials
        </div>
        
        <?php if ($students_with_credentials->num_rows > 0): ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th width="15%">ID Number</th>
                        <th width="40%">Name</th>
                        <th width="45%">Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $students_with_credentials->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id_number'] ?></td>
                        <td><?= $row['full_name'] ?></td>
                        <td><?= $row['email'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                No students with login credentials found.
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Print and Back Buttons -->
    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button class="print-button" onclick="window.print()">
            <i class="fas fa-print"></i> Print User List
        </button>
        <a href="admin.php#reports" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
    </div>
</div>

<!-- Required Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
 $(document).ready(function() {
    // Print functionality
    $('.print-button').on('click', function() {
        window.print();
    });
});
</script>
</body>
</html>