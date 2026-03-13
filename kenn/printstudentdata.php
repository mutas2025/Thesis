<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// --- MODE 1: PRINT FORM FOR A SINGLE STUDENT ---
if (isset($_GET['print_form_id'])) {
    $student_id = (int)$_GET['print_form_id'];
    
    // Securely fetch the specific student's data
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        header('Location: printstudentdata.php?error=not_found');
        exit();
    }

    $page_title = "Student Profile Record";
    $content_mode = 'single_form';

} else { 
    // --- MODE 2: LIST ALL STUDENTS (Default View) ---
    $year_level = isset($_GET['year_level']) ? $_GET['year_level'] : '';
    $course = isset($_GET['course']) ? $_GET['course'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';

    // Build query
    $query = "SELECT * FROM students WHERE 1=1";
    if (!empty($year_level)) {
        $query .= " AND year_level = '$year_level'";
    }
    if (!empty($course)) {
        $query .= " AND course_or_strand LIKE '%$course%'";
    }
    if (!empty($status)) {
        $query .= " AND enrollment_status = '$status'";
    }
    $query .= " ORDER BY full_name";

    $students_result = $conn->query($query);

    // Get distinct values for filters
    $year_levels = $conn->query("SELECT DISTINCT year_level FROM students ORDER BY year_level");
    $courses = $conn->query("SELECT DISTINCT course_or_strand FROM students ORDER BY course_or_strand");
    $statuses = $conn->query("SELECT DISTINCT enrollment_status FROM students ORDER BY enrollment_status");

    $page_title = "Student Data Master List";
    $content_mode = 'list';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $page_title; ?></title>
    <link rel="icon" href="uploads/csr.png" type="image/x-icon">
    <style>
        /* --- General & Typography --- */
        body {
            font-family: 'Times New Roman', Times, serif; /* Formal Font */
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #f0f0f0;
        }

        /* --- Header Styles --- */
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .header-logo img {
            height: 90px;
            width: auto;
            vertical-align: middle;
        }
        .header-school {
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0 5px 0;
            letter-spacing: 1px;
        }
        .header-meta {
            font-size: 14px;
            color: #555;
            font-style: italic;
        }
        .header-title {
            font-size: 22px;
            font-weight: bold;
            margin-top: 15px;
            text-decoration: underline;
            text-decoration-thickness: 2px;
        }

        /* --- Filters (Screen Only) --- */
        .filters {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: Arial, sans-serif; /* Sans-serif for UI */
            font-size: 13px;
        }
        .filters form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        .filter-group label {
            margin-bottom: 4px;
            font-weight: bold;
            font-size: 12px;
        }
        .filter-group select {
            padding: 6px;
            border: 1px solid #999;
            border-radius: 3px;
            min-width: 150px;
        }
        .filter-actions button {
            padding: 7px 15px;
            cursor: pointer;
            border: none;
            border-radius: 3px;
            color: white;
            font-weight: bold;
            background-color: #1a3a5f;
        }
        .filter-actions button:hover {
            background-color: #0f2238;
        }
        .btn-secondary {
            background-color: #666 !important;
            margin-left: 5px !important;
        }

        /* --- Table Styles --- */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 8px 12px;
            text-align: left;
        }
        .data-table th {
            background-color: #1a3a5f;
            color: white;
            text-transform: uppercase;
            font-size: 14px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .action-link {
            color: #1a3a5f;
            text-decoration: none;
            font-weight: bold;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .action-link:hover {
            text-decoration: underline;
        }

        /* --- Formal Single Form Styles --- */
        .paper-container {
            background-color: white;
            padding: 40px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border: 1px solid #ddd;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-section {
            margin-bottom: 25px;
        }
        .section-header {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #333;
            margin-bottom: 15px;
            padding-bottom: 5px;
            color: #1a3a5f;
            background-color: #f0f4f8;
            padding: 5px 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Two columns */
            column-gap: 30px;
            row-gap: 10px;
        }

        .form-group-full {
            grid-column: 1 / -1; /* Full width */
            margin-bottom: 5px;
        }

        .field-label {
            font-weight: bold;
            font-size: 14px;
            display: block;
            margin-bottom: 3px;
        }

        .field-value {
            font-size: 15px;
            padding: 5px 0;
            border-bottom: 1px dotted #666; /* Dotted line like official forms */
            min-height: 24px;
        }

        /* --- Footer --- */
        .report-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        /* --- Print Media --- */
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .paper-container {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
                width: 100%;
                max-width: none;
            }
            .filters, .action-link, .no-print {
                display: none !important;
            }
            .data-table th {
                background-color: #ddd !important;
                color: black !important;
                -webkit-print-color-adjust: exact;
            }
            .field-value {
                border-bottom: 1px solid #000; /* Solid line for printing */
            }
        }
    </style>
</head>
<body>

    <!-- Common Header -->
    <div class="report-header">
        <div class="header-logo">
            <img src="uploads/csr.png" alt="School Logo">
        </div>
        <div class="header-school"><?php echo SCHOOL_NAME; ?></div>
        <div class="header-meta"><?php echo SCHOOL_ADDRESS; ?> | Contact: <?php echo SCHOOL_CONTACT; ?></div>
        <div class="header-title"><?php echo $page_title; ?></div>
        <div class="header-meta">Date Generated: <?php echo date('F j, Y'); ?></div>
    </div>

    <?php if ($content_mode === 'single_form'): ?>
        <!-- SINGLE STUDENT FORM VIEW -->
        <div class="paper-container">
            
            <div class="form-section">
                <div class="section-header">Personal Information</div>
                <div class="form-grid">
                    <div>
                        <span class="field-label">Student Number (LRN):</span>
                        <div class="field-value"><?php echo htmlspecialchars($student['student_number']); ?></div>
                    </div>
                    <div>
                        <span class="field-label">Full Name:</span>
                        <div class="field-value"><?php echo htmlspecialchars($student['full_name']); ?></div>
                    </div>
                    <div>
                        <span class="field-label">Gender:</span>
                        <div class="field-value"><?php echo htmlspecialchars($student['gender']); ?></div>
                    </div>
                    <div>
                        <span class="field-label">Date of Birth:</span>
                        <div class="field-value"><?php echo date('F j, Y', strtotime($student['date_of_birth'])); ?></div>
                    </div>
                    <div class="form-group-full">
                        <span class="field-label">Home Address:</span>
                        <div class="field-value"><?php echo htmlspecialchars($student['address']); ?></div>
                    </div>
                    <div>
                        <span class="field-label">Contact Number:</span>
                        <div class="field-value"><?php echo htmlspecialchars($student['contact_number']); ?></div>
                    </div>
                    <div>
                        <span class="field-label">Email Address:</span>
                        <div class="field-value"><?php echo htmlspecialchars($student['email']); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <div class="section-header">Academic Information</div>
                <div class="form-grid">
                    <div>
                        <span class="field-label">Year Level:</span>
                        <div class="field-value"><?php echo htmlspecialchars($student['year_level']); ?></div>
                    </div>
                    <div>
                        <span class="field-label">Course / Strand:</span>
                        <div class="field-value"><?php echo htmlspecialchars($student['course_or_strand']); ?></div>
                    </div>
                    <div>
                        <span class="field-label">Section:</span>
                        <div class="field-value"><?php echo htmlspecialchars($student['section']); ?></div>
                    </div>
                    <div>
                        <span class="field-label">Enrollment Status:</span>
                        <div class="field-value"><?php echo htmlspecialchars($student['enrollment_status']); ?></div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-header">Guardian / Parent Information</div>
                <div class="form-grid">
                    <div>
                        <span class="field-label">Guardian's Name:</span>
                        <div class="field-value"><?php echo htmlspecialchars($student['guardian_name']); ?></div>
                    </div>
                    <div>
                        <span class="field-label">Relationship:</span>
                        <div class="field-value">Parent/Guardian</div> <!-- Assuming relationship, adjust if DB has specific field -->
                    </div>
                    <div class="form-group-full">
                        <span class="field-label">Contact Number:</span>
                        <div class="field-value"><?php echo htmlspecialchars($student['guardian_contact']); ?></div>
                    </div>
                </div>
            </div>

        </div>

        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; background-color: #1a3a5f; color: white; border: none; border-radius: 4px; cursor: pointer;">
                <i class="fas fa-print"></i> Print Record
            </button>
            <button onclick="history.back()" style="padding: 10px 20px; background-color: #666; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                Back to List
            </button>
        </div>

    <?php else: ?>
        <!-- LIST ALL STUDENTS VIEW -->
        <div class="filters no-print">
            <form method="GET" action="">
                <div class="filter-group">
                    <label for="year_level">Year Level:</label>
                    <select id="year_level" name="year_level">
                        <option value="">All Levels</option>
                        <?php 
                        if ($year_levels->num_rows > 0) {
                            $year_levels->data_seek(0); 
                            while ($row = $year_levels->fetch_assoc()): 
                        ?>
                        <option value="<?php echo $row['year_level']; ?>" <?php echo ($year_level == $row['year_level']) ? 'selected' : ''; ?>>
                            <?php echo $row['year_level']; ?>
                        </option>
                        <?php endwhile; } ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="course">Course/Strand:</label>
                    <select id="course" name="course">
                        <option value="">All Courses</option>
                        <?php 
                        if ($courses->num_rows > 0) {
                            $courses->data_seek(0);
                            while ($row = $courses->fetch_assoc()): 
                        ?>
                        <option value="<?php echo $row['course_or_strand']; ?>" <?php echo ($course == $row['course_or_strand']) ? 'selected' : ''; ?>>
                            <?php echo $row['course_or_strand']; ?>
                        </option>
                        <?php endwhile; } ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <?php 
                        if ($statuses->num_rows > 0) {
                            $statuses->data_seek(0);
                            while ($row = $statuses->fetch_assoc()): 
                        ?>
                        <option value="<?php echo $row['enrollment_status']; ?>" <?php echo ($status == $row['enrollment_status']) ? 'selected' : ''; ?>>
                            <?php echo $row['enrollment_status']; ?>
                        </option>
                        <?php endwhile; } ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="submit">Apply Filters</button>
                    <button type="button" onclick="window.print()" class="btn-secondary">Print List</button>
                </div>
            </form>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Student No.</th>
                    <th style="width: 25%;">Full Name</th>
                    <th style="width: 10%;">Gender</th>
                    <th style="width: 10%;">Year Lvl</th>
                    <th style="width: 20%;">Course/Strand</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 10%;" class="no-print">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students_result->num_rows > 0): ?>
                    <?php while ($row = $students_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['student_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                        <td><?php echo htmlspecialchars($row['year_level']); ?></td>
                        <td><?php echo htmlspecialchars($row['course_or_strand']); ?></td>
                        <td><?php echo htmlspecialchars($row['enrollment_status']); ?></td>
                        <td class="no-print" style="text-align: center;">
                            <a href="?print_form_id=<?php echo $row['student_id']; ?>" class="action-link">View / Print</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">No students found matching the current filters.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <div class="report-footer">
        <p>Report generated by: <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'System'; ?> on <?php echo date('F j, Y h:i A'); ?></p>
        <p>&copy; <?php echo date('Y'); ?> <?php echo SCHOOL_NAME; ?>. Guidance Office System.</p>
    </div>
</body>
</html>