<?php
require_once 'config.php';
requireRole('admin');

// Get selected department, effective year, and year level from URL parameters
 $selected_dept_id = isset($_GET['dept_id']) ? $_GET['dept_id'] : '';
 $selected_effective_year = isset($_GET['effective_year']) ? $_GET['effective_year'] : '';
 $selected_year_level = isset($_GET['year_level']) ? $_GET['year_level'] : '';

// Fetch all departments for dropdown
 $departments_query = "SELECT * FROM departments ORDER BY dept_name";
 $departments_result = $conn->query($departments_query);

// Fetch distinct effective years for dropdown
 $years_query = "SELECT DISTINCT effective_year FROM subjects ORDER BY effective_year DESC";
 $years_result = $conn->query($years_query);

// Fetch distinct year levels for dropdown
 $year_levels_query = "SELECT DISTINCT year_level FROM subjects ORDER BY year_level";
 $year_levels_result = $conn->query($year_levels_query);

// Initialize variables
 $department_details = null;
 $courses_in_department = [];
 $subjects_by_course = [];

// If a department is selected, fetch its details and courses
if (!empty($selected_dept_id)) {
    // Get department details
    $dept_query = "SELECT * FROM departments WHERE id = ?";
    $stmt = $conn->prepare($dept_query);
    $stmt->bind_param("i", $selected_dept_id);
    $stmt->execute();
    $department_details = $stmt->get_result()->fetch_assoc();
    
    // Build the courses query based on filters
    if (!empty($selected_effective_year) || !empty($selected_year_level)) {
        // If filters are applied, only get courses that have subjects matching the filters
        $courses_query = "SELECT DISTINCT c.* FROM courses c 
                         JOIN subjects s ON c.id = s.course_id 
                         WHERE c.department_id = ?";
        $params = [$selected_dept_id];
        $types = "i";
        
        if (!empty($selected_effective_year)) {
            $courses_query .= " AND s.effective_year = ?";
            $params[] = $selected_effective_year;
            $types .= "s";
        }
        
        if (!empty($selected_year_level)) {
            $courses_query .= " AND s.year_level = ?";
            $params[] = $selected_year_level;
            $types .= "s";
        }
        
        $courses_query .= " ORDER BY c.coursename";
        $stmt = $conn->prepare($courses_query);
        $stmt->bind_param($types, ...$params);
    } else {
        // If no filters, get all courses in the department
        $courses_query = "SELECT * FROM courses WHERE department_id = ? ORDER BY coursename";
        $stmt = $conn->prepare($courses_query);
        $stmt->bind_param("i", $selected_dept_id);
    }
    
    $stmt->execute();
    $courses_result = $stmt->get_result();
    
    // Store all courses in the department
    while ($course = $courses_result->fetch_assoc()) {
        $courses_in_department[] = $course;
        
        // Get all subjects for this course, filtered by effective year and year level if selected
        $subjects_query = "SELECT * FROM subjects WHERE course_id = ?";
        $params = [$course['id']];
        $types = "i";
        
        if (!empty($selected_effective_year)) {
            $subjects_query .= " AND effective_year = ?";
            $params[] = $selected_effective_year;
            $types .= "s";
        }
        
        if (!empty($selected_year_level)) {
            $subjects_query .= " AND year_level = ?";
            $params[] = $selected_year_level;
            $types .= "s";
        }
        
        $subjects_query .= " ORDER BY year_level, semester, subject_code";
        
        $stmt = $conn->prepare($subjects_query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $subjects_result = $stmt->get_result();
        
        // Organize subjects by year level and semester
        $subjects_by_course[$course['id']] = [];
        while ($subject = $subjects_result->fetch_assoc()) {
            $year_level = $subject['year_level'];
            $semester = $subject['semester'];
            
            if (!isset($subjects_by_course[$course['id']][$year_level])) {
                $subjects_by_course[$course['id']][$year_level] = [];
            }
            
            if (!isset($subjects_by_course[$course['id']][$year_level][$semester])) {
                $subjects_by_course[$course['id']][$year_level][$semester] = [];
            }
            
            $subjects_by_course[$course['id']][$year_level][$semester][] = $subject;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Academic Curriculum - Print View</title>
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
        
        .filter-section {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .dept-info {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .course-section {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .year-section {
            margin-bottom: 30px;
        }
        
        .semester-section {
            margin-bottom: 20px;
        }
        
        .semester-title {
            background-color: var(--info);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .subjects-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .subjects-table th {
            background-color: var(--light);
            padding: 8px;
            text-align: left;
            border: 1px solid #dee2e6;
            font-weight: bold;
            font-size: 12px;
        }
        
        .subjects-table td {
            padding: 6px 8px;
            border: 1px solid #dee2e6;
            font-size: 12px;
        }
        
        .subjects-table tr:nth-child(even) {
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
        
        .total-units {
            font-weight: bold;
            text-align: right;
            padding: 8px;
            background-color: var(--light);
            font-size: 12px;
        }
        
        .course-header {
            background-color: var(--primary);
            color: white;
            padding: 15px;
            border-radius: 5px 5px 0 0;
            margin-bottom: 0;
        }
        
        .course-content {
            padding: 20px;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .filter-info {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        /* Long Bond Paper Print Styles (8.5" x 13") */
        @media print {
            @page {
                size: 216mm 330mm; /* Long bond paper size (8.5" x 13") */
                margin: 20mm 15mm 20mm 15mm; /* Top Right Bottom Left */
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            * {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            body {
                background-color: white !important;
                font-size: 12px !important;
                line-height: 1.3 !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .main-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .header-section {
                background-color: var(--primary) !important;
                color: white !important;
                padding: 20px !important;
                margin-bottom: 20px !important;
                border-radius: 0 !important;
                text-align: center !important;
                font-size: 22px !important;
            }
            
            .header-section h1 {
                font-size: 24px !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .header-section p {
                font-size: 14px !important;
                margin: 5px 0 0 0 !important;
            }
            
            .filter-info {
                background-color: #e9ecef !important;
                padding: 10px !important;
                margin-bottom: 20px !important;
                font-size: 12px !important;
            }
            
            .dept-info {
                background-color: white !important;
                padding: 15px !important;
                margin-bottom: 20px !important;
                border: 1px solid #dee2e6 !important;
                border-radius: 0 !important;
            }
            
            .dept-info h3 {
                font-size: 18px !important;
                margin: 0 0 8px 0 !important;
            }
            
            .dept-info p {
                font-size: 12px !important;
                margin: 0 !important;
            }
            
            .course-section {
                background-color: white !important;
                padding: 0 !important;
                margin-bottom: 25px !important;
                box-shadow: none !important;
                border: 1px solid #dee2e6 !important;
                page-break-inside: avoid;
            }
            
            .course-header {
                background-color: var(--primary) !important;
                color: white !important;
                padding: 15px !important;
                border-radius: 0 !important;
                margin-bottom: 0 !important;
            }
            
            .course-header h3 {
                font-size: 16px !important;
                margin: 0 0 5px 0 !important;
            }
            
            .course-header h4 {
                font-size: 14px !important;
                margin: 0 0 5px 0 !important;
            }
            
            .course-header p {
                font-size: 11px !important;
                margin: 0 !important;
            }
            
            .course-content {
                padding: 15px !important;
                border: none !important;
                border-top: none !important;
                border-radius: 0 !important;
            }
            
            .course-content p {
                font-size: 12px !important;
                margin: 0 0 15px 0 !important;
            }
            
            .year-section {
                margin-bottom: 20px !important;
            }
            
            .year-section h4 {
                font-size: 15px !important;
                margin: 0 0 12px 0 !important;
                color: var(--primary) !important;
            }
            
            .semester-section {
                margin-bottom: 18px !important;
            }
            
            .semester-title {
                background-color: var(--info) !important;
                color: white !important;
                padding: 8px 12px !important;
                border-radius: 0 !important;
                font-weight: bold !important;
                margin-bottom: 10px !important;
                font-size: 13px !important;
            }
            
            .subjects-table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-bottom: 18px !important;
                font-size: 11px !important;
                page-break-inside: auto;
            }
            
            .subjects-table th {
                background-color: var(--light) !important;
                padding: 6px 8px !important;
                text-align: left !important;
                border: 1px solid #dee2e6 !important;
                font-weight: bold !important;
                font-size: 10px !important;
            }
            
            .subjects-table td {
                padding: 5px 8px !important;
                border: 1px solid #dee2e6 !important;
                font-size: 10px !important;
                line-height: 1.2 !important;
            }
            
            .subjects-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            .subjects-table tr:nth-child(even) {
                background-color: #f8f9fa !important;
            }
            
            .total-units {
                font-weight: bold !important;
                text-align: right !important;
                padding: 6px 8px !important;
                background-color: var(--light) !important;
                font-size: 10px !important;
            }
            
            .no-data {
                text-align: center !important;
                padding: 15px !important;
                color: #6c757d !important;
                font-size: 12px !important;
            }
            
            .page-break {
                page-break-before: always;
                height: 0;
                line-height: 0;
            }
            
            /* Prevent elements from breaking across pages */
            h1, h2, h3, h4, h5, h6 {
                page-break-after: avoid;
                page-break-inside: avoid;
            }
            
            table {
                page-break-inside: auto;
            }
            
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            td {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            
            img {
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
                <h1>ACADEMIC CURRICULUM</h1>
                <p>Select a department, effective year, and year level to view courses and their curriculum</p>
            </div>
        </div>
    </div>
    
    <!-- Department, Year, and Year Level Selection -->
    <div class="filter-section no-print">
        <form method="GET" class="form-inline justify-content-center">
            <div class="form-group mr-3">
                <label for="dept_id" class="mr-2">Department:</label>
                <select name="dept_id" id="dept_id" class="form-control" style="width: 300px;">
                    <option value="">Select Department</option>
                    <?php while($dept = $departments_result->fetch_assoc()): ?>
                    <option value="<?= $dept['id'] ?>" <?= $selected_dept_id == $dept['id'] ? 'selected' : '' ?>>
                        <?= $dept['dept_name'] ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group mr-3">
                <label for="effective_year" class="mr-2">Effective Year:</label>
                <select name="effective_year" id="effective_year" class="form-control" style="width: 150px;">
                    <option value="">All Years</option>
                    <?php 
                    // Reset result pointer
                    $years_result->data_seek(0);
                    while($year = $years_result->fetch_assoc()): 
                    ?>
                    <option value="<?= $year['effective_year'] ?>" <?= $selected_effective_year == $year['effective_year'] ? 'selected' : '' ?>>
                        <?= $year['effective_year'] ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group mr-3">
                <label for="year_level" class="mr-2">Year Level:</label>
                <select name="year_level" id="year_level" class="form-control" style="width: 150px;">
                    <option value="">All Year Levels</option>
                    <?php 
                    // Reset result pointer
                    $year_levels_result->data_seek(0);
                    while($year_level = $year_levels_result->fetch_assoc()): 
                    ?>
                    <option value="<?= $year_level['year_level'] ?>" <?= $selected_year_level == $year_level['year_level'] ? 'selected' : '' ?>>
                        <?= $year_level['year_level'] ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Load</button>
        </form>
    </div>
    
    <?php if ($department_details): ?>
    <!-- Filter Information -->
    <?php if (!empty($selected_effective_year) || !empty($selected_year_level)): ?>
    <div class="filter-info">
        <strong>Filter Applied:</strong> 
        <?php if (!empty($selected_effective_year)): ?>
        Showing curriculum for effective year <strong><?= $selected_effective_year ?></strong>
        <?php endif; ?>
        <?php if (!empty($selected_effective_year) && !empty($selected_year_level)): ?>
        and 
        <?php endif; ?>
        <?php if (!empty($selected_year_level)): ?>
        year level <strong><?= $selected_year_level ?></strong>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Department Information -->
    <div class="dept-info">
        <h3><?= $department_details['dept_name'] ?></h3>
        <p><?= $department_details['dept_description'] ?></p>
    </div>
    
    <!-- Courses and Curriculum -->
    <?php if (!empty($courses_in_department)): ?>
        <?php foreach ($courses_in_department as $index => $course): ?>
            <?php if ($index > 0): ?>
            <div class="page-break"></div>
            <?php endif; ?>
            
            <div class="course-section">
                <!-- Course Header -->
                <div class="course-header">
                    <h3><?= strtoupper($course['coursename']) ?></h3>
                    <h4><?= $course['courselevel'] ?></h4>
                    <?php if (!empty($selected_effective_year)): ?>
                    <p>Curriculum for Effective Year: <?= $selected_effective_year ?></p>
                    <?php endif; ?>
                    <?php if (!empty($selected_year_level)): ?>
                    <p>Year Level: <?= $selected_year_level ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Course Content -->
                <div class="course-content">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <p><strong>Course Description:</strong> <?= $course['coursedescription'] ?></p>
                        </div>
                    </div>
                    
                    <!-- Curriculum Content -->
                    <?php if (isset($subjects_by_course[$course['id']]) && !empty($subjects_by_course[$course['id']])): ?>
                        <?php foreach ($subjects_by_course[$course['id']] as $year_level => $semesters): ?>
                            <div class="year-section">
                                <h4><?= $year_level ?></h4>
                                
                                <?php foreach ($semesters as $semester => $subjects): ?>
                                    <div class="semester-section">
                                        <div class="semester-title">
                                            <?= $semester ?> Semester
                                        </div>
                                        
                                        <?php if (!empty($subjects)): ?>
                                            <table class="subjects-table">
                                                <thead>
                                                    <tr>
                                                        <th width="15%">Subject Code</th>
                                                        <th width="40%">Subject Description</th>
                                                        <th width="10%">Units</th>
                                                        <th width="15%">Pre-requisite</th>
                                                        <th width="10%">Academic Year</th>
                                                        <th width="10%">Effective Year</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $total_units = 0;
                                                    foreach ($subjects as $subject): 
                                                        $total_units += $subject['unit'];
                                                    ?>
                                                    <tr>
                                                        <td><?= $subject['subject_code'] ?></td>
                                                        <td><?= $subject['subject_description'] ?></td>
                                                        <td><?= $subject['unit'] ?></td>
                                                        <td><?= $subject['pre_requisite'] ?: 'None' ?></td>
                                                        <td><?= $subject['academic_year'] ?></td>
                                                        <td><?= $subject['effective_year'] ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                    <tr class="total-units">
                                                        <td colspan="2"><strong>Total Units for <?= $semester ?> Semester:</strong></td>
                                                        <td><strong><?= $total_units ?></strong></td>
                                                        <td colspan="3"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <div class="no-data">
                                                No subjects found for <?= $semester ?> semester.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-data">
                            <?php if (!empty($selected_effective_year) && !empty($selected_year_level)): ?>
                                No subjects found for this course with effective year <?= $selected_effective_year ?> and year level <?= $selected_year_level ?>.
                            <?php elseif (!empty($selected_effective_year)): ?>
                                No subjects found for this course with effective year <?= $selected_effective_year ?>.
                            <?php elseif (!empty($selected_year_level)): ?>
                                No subjects found for this course with year level <?= $selected_year_level ?>.
                            <?php else: ?>
                                No subjects found for this course.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-data">
            <?php if (!empty($selected_effective_year) || !empty($selected_year_level)): ?>
                <?php if (!empty($selected_effective_year) && !empty($selected_year_level)): ?>
                    No courses found in this department with subjects matching effective year <?= $selected_effective_year ?> and year level <?= $selected_year_level ?>.
                <?php elseif (!empty($selected_effective_year)): ?>
                    No courses found in this department with subjects matching effective year <?= $selected_effective_year ?>.
                <?php elseif (!empty($selected_year_level)): ?>
                    No courses found in this department with subjects matching year level <?= $selected_year_level ?>.
                <?php endif; ?>
            <?php else: ?>
                No courses found in this department.
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Print and Back Buttons -->
    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button class="print-button" onclick="window.print()">
            <i class="fas fa-print"></i> Print Curriculum
        </button>
        <a href="admin.php#reports" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
    </div>
    
    <?php else: ?>
    <!-- No Department Selected -->
    <div class="no-data">
        <h3>No Department Selected</h3>
        <p>Please select a department from the dropdown above to view its curriculum.</p>
    </div>
    <?php endif; ?>
</div>

<!-- Required Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
 $(document).ready(function() {
    // Auto-submit form when department, effective year, or year level is selected
    $('select[name="dept_id"], select[name="effective_year"], select[name="year_level"]').on('change', function() {
        if ($('select[name="dept_id"]').val()) {
            this.form.submit();
        }
    });
    
    // Print functionality
    $('.print-button').on('click', function() {
        window.print();
    });
});
</script>
</body>
</html>