<?php
session_start();

// --- CHECK LOGGED IN USER ---
if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['instructor_logged_in'])) {
    header("Location: login.php");
    exit;
}

require 'config.php';

// Determine User Role and Details
 $is_admin = isset($_SESSION['admin_logged_in']);
 $is_instructor = isset($_SESSION['instructor_logged_in']);
 $user_role = $is_admin ? 'Admin' : 'Instructor';
 $user_display_name = $is_admin ? $_SESSION['admin_user'] : $_SESSION['instructor_fullname']; 

// --- 1. Retrieve Search Parameters ---
 $searchTerm = $_GET['search'] ?? '';
 $filterAcadYear = $_GET['acadyear'] ?? '';
 $filterSem = $_GET['semester'] ?? '';
 $filterCourse = $_GET['crscode'] ?? '';
 $filterSubject = $_GET['subjcode'] ?? '';

// --- 2. Build Query ---
 $sql = "SELECT * FROM `tblnewgradesheetfinal` WHERE 1=1";

// STRICT INSTRUCTOR FILTER
if ($is_instructor) {
    $safeInstructorName = $conn->real_escape_string($user_display_name);
    $sql .= " AND `INSTNAME` = '$safeInstructorName'";
}

// Apply Filters
if (!empty($searchTerm)) {
    $safeSearch = $conn->real_escape_string($searchTerm);
    $sql .= " AND (`IDNO` LIKE '%$safeSearch%' OR `LNAME` LIKE '%$safeSearch%' OR `FNAME` LIKE '%$safeSearch%')";
}
if (!empty($filterAcadYear)) {
    $safeYear = $conn->real_escape_string($filterAcadYear);
    $sql .= " AND `ACADEMICYR` = '$safeYear'";
}
if (!empty($filterSem)) {
    $safeSem = $conn->real_escape_string($filterSem);
    $sql .= " AND `SEMESTER` = '$safeSem'";
}
if (!empty($filterCourse)) {
    $safeCourse = $conn->real_escape_string($filterCourse);
    $sql .= " AND `CRSCODE` = '$safeCourse'";
}
if (!empty($filterSubject)) {
    $safeSubject = $conn->real_escape_string($filterSubject);
    $sql .= " AND `SUBJCODE` = '$safeSubject'";
}

// IMPORTANT: Sort by Last Name first
 $sql .= " ORDER BY `LNAME` ASC, `FNAME` ASC, `SUBJCODE` ASC";

 $result = $conn->query($sql);

// --- 3. Process Data for Grouping ---
 $maleStudents = [];
 $femaleStudents = [];

// Variable to hold instructor name from DB (if available)
 $instructorNameDisplay = $is_instructor ? $user_display_name : 'All Instructors';

if ($result && $result->num_rows > 0) {
    // We can grab the instructor name from the first row to be precise
    $firstRow = $result->fetch_assoc();
    $instructorNameDisplay = $firstRow['INSTNAME'];
    
    // Process the first row
    $idno = $firstRow['IDNO'];
    $gender = strtoupper(trim($firstRow['GENDER'])); 
    
    if ($gender === 'MALE' || $gender === 'M') {
        $targetArray =& $maleStudents;
    } else {
        $targetArray =& $femaleStudents;
    }

    if (!isset($targetArray[$idno])) {
        $targetArray[$idno] = [
            'LNAME'       => $firstRow['LNAME'],
            'FNAME'       => $firstRow['FNAME'],
            'MNAME'       => $firstRow['MNAME'],
            'GENDER'      => $firstRow['GENDER'],
            'COURSE_INFO' => [] 
        ];
    }
    
    $targetArray[$idno]['COURSE_INFO'][] = [
        'SUBJCODE'    => $firstRow['SUBJCODE'],
        'PG'          => $firstRow['PG'],
        'MG'          => $firstRow['MG'],
        'FG'          => $firstRow['FG'],
        'FA'          => $firstRow['FA'], 
        'REMARKS'     => $firstRow['REMARKS']
    ];

    // Process remaining rows
    while($row = $result->fetch_assoc()) {
        $idno = $row['IDNO'];
        $gender = strtoupper(trim($row['GENDER']));
        
        if ($gender === 'MALE' || $gender === 'M') {
            $targetArray =& $maleStudents;
        } else {
            $targetArray =& $femaleStudents;
        }

        if (!isset($targetArray[$idno])) {
            $targetArray[$idno] = [
                'LNAME'       => $row['LNAME'],
                'FNAME'       => $row['FNAME'],
                'MNAME'       => $row['MNAME'],
                'GENDER'      => $row['GENDER'],
                'COURSE_INFO' => [] 
            ];
        }
        
        $targetArray[$idno]['COURSE_INFO'][] = [
            'SUBJCODE'    => $row['SUBJCODE'],
            'PG'          => $row['PG'],
            'MG'          => $row['MG'],
            'FG'          => $row['FG'],
            'FA'          => $row['FA'], 
            'REMARKS'     => $row['REMARKS']
        ];
    }
} else {
    // If no results, keep the default based on role
    // (Already set above)
}

 $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Grade Sheet</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px; 
            color: #000;
        }
        .container {
            max-width: 100%;
            padding: 20px;
        }
        /* --- HEADER --- */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .header-logo { flex: 0 0 100px; margin-right: 30px; }
        .header-logo img { height: 80px; width: auto; }
        .header-text { flex: 1; text-align: left; }
        .header-text h3 { margin: 0; font-weight: bold; text-transform: uppercase; font-size: 24px; }
        .header-text p { margin: 5px 0 0; font-size: 16px; font-weight: 500; }
        .header-text .sub-info { margin: 2px 0 0; font-size: 14px; font-weight: normal; }

        /* --- TABLE STYLES --- */
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 4px 8px; 
            vertical-align: middle;
            border: 1px solid #000; 
            font-size: 11px;
        }
        .table thead th {
            background-color: #f2f2f2;
            color: #000;
            text-align: center;
            border-bottom: 2px solid #000;
            font-weight: bold;
        }
        .gender-header {
            margin-top: 30px;
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
        }
        .no-print { display: block; }
        
        @media print {
            .no-print { display: none !important; }
            body { background-color: white; }
            .container { width: 100%; max-width: 100%; padding: 0; margin: 0; }
            .table, tr, td, th { page-break-inside: avoid; }
            .student-sheet { page-break-inside: avoid; }
            .gender-header { page-break-after: avoid; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- No Print Buttons -->
    <div class="no-print mb-3 text-right">
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            <i class="fas fa-print"></i> Print Document
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm">Close</button>
    </div>

    <!-- Header -->
    <div class="page-header">
        <div class="header-logo">
            <img src="uploads/kcc.jpg" alt="KCC Logo">
        </div>
        <div class="header-text">
            <h3>Kabankalan Catholic College, Inc.</h3>
            <p>Grade Sheet</p>
            <!-- Added Instructor Name Here -->
            <div class="sub-info">Instructor: <?php echo htmlspecialchars($instructorNameDisplay); ?></div>
        </div>
    </div>

    <!-- Filter Info (Optional) -->
    <div class="mb-2 font-italic text-muted">
        Filters: 
        Year: <?php echo htmlspecialchars($filterAcadYear ?: 'All'); ?> | 
        Sem: <?php echo htmlspecialchars($filterSem ?: 'All'); ?> | 
        Course: <?php echo htmlspecialchars($filterCourse ?: 'All'); ?> | 
        Subject: <?php echo htmlspecialchars($filterSubject ?: 'All'); ?>
    </div>

    <?php 
    // Function to render a gender table
    function renderGenderTable($students, $genderTitle) {
        if (empty($students)) return; 
        
        $counter = 1;
        ?>
        <div class="student-sheet">
            <h4 class="gender-header"><?php echo $genderTitle; ?></h4>
            
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th style="width: 5%">No.</th>
                        <th style="width: 30%">Name</th>
                        <th style="width: 10%">PG</th>
                        <th style="width: 10%">MG</th>
                        <th style="width: 10%">FG</th>
                        <th style="width: 10%">FA</th>
                        <th style="width: 25%">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <?php foreach ($student['COURSE_INFO'] as $course): ?>
                        <tr>
                            <!-- Numbering (Only on the first row for that student if multiple subjects) -->
                            <td class="text-center"><?php echo ($course === reset($student['COURSE_INFO'])) ? $counter++ : ''; ?></td>
                            
                            <!-- Name (Only on the first row) -->
                            <td>
                                <?php if ($course === reset($student['COURSE_INFO'])): ?>
                                    <?php echo htmlspecialchars($student['LNAME'] . ', ' . $student['FNAME'] . ' ' . $student['MNAME']); ?>
                                <?php endif; ?>
                            </td>

                            <!-- Grades -->
                            <td class="text-center"><?php echo htmlspecialchars($course['PG']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($course['MG']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($course['FG']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($course['FA']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($course['REMARKS']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // Render Males
    renderGenderTable($maleStudents, "MALE STUDENTS");

    // Render Females
    renderGenderTable($femaleStudents, "FEMALE STUDENTS");
    ?>

    <?php if (empty($maleStudents) && empty($femaleStudents)): ?>
        <div class="alert alert-warning text-center mt-5">
            <h4>No records found.</h4>
        </div>
    <?php endif; ?>

</div>

</body>
</html>