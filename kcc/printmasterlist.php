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
 $sql .= " ORDER BY `LNAME` ASC, `FNAME` ASC";

 $result = $conn->query($sql);

// --- 3. Process Data for Grouping ---
 $maleStudents = [];
 $femaleStudents = [];

// Variables to hold class details for the header (Grab from first row found)
 $classDetails = [
    'instructor' => $is_instructor ? $user_display_name : 'All Instructors',
    'year'       => $filterAcadYear,
    'sem'        => $filterSem,
    'course'     => $filterCourse,
    'subject'    => $filterSubject,
    'sched'      => '',
    'units'      => '',
    'description' => ''
];

if ($result && $result->num_rows > 0) {
    // Fetch first row to get specific descriptions if available
    $firstRow = $result->fetch_assoc();
    
    // Update class details with DB data
    $classDetails['instructor'] = $firstRow['INSTNAME'];
    if(empty($classDetails['year'])) $classDetails['year'] = $firstRow['ACADEMICYR'];
    if(empty($classDetails['sem'])) $classDetails['sem'] = $firstRow['SEMESTER'];
    if(empty($classDetails['course'])) $classDetails['course'] = $firstRow['CRSCODE'];
    // Combine Code and Description for the Subject Label
    if(empty($classDetails['subject'])) $classDetails['subject'] = $firstRow['SUBJCODE'] . ' - ' . $firstRow['SUBJDESC'];
    
    // Capture details for the Info Box
    $classDetails['sched'] = $firstRow['SUBJSCHEDULE'];
    $classDetails['units'] = $firstRow['SUBJUNIT'];
    $classDetails['description'] = $firstRow['SUBJDESC'];

    // Process the first row
    $idno = $firstRow['IDNO'];
    $gender = strtoupper(trim($firstRow['GENDER'])); 
    if ($gender === 'MALE' || $gender === 'M') { $targetArray =& $maleStudents; } else { $targetArray =& $femaleStudents; }
    
    // Add to array
    if (!isset($targetArray[$idno])) {
        $targetArray[$idno] = [
            'LNAME'       => $firstRow['LNAME'], 
            'FNAME'       => $firstRow['FNAME'], 
            'MNAME'       => $firstRow['MNAME'],
            'GENDER'      => $firstRow['GENDER'], 
            'IDNO'        => $firstRow['IDNO'], 
            'CRSCODE'     => $firstRow['CRSCODE'],
            'CRSLEVEL'    => $firstRow['CRSLEVEL'], 
            'CRSMAJOR'    => $firstRow['CRSMAJOR'],
            'SUBJDESC'    => $firstRow['SUBJDESC'],
            'SUBJUNIT'    => $firstRow['SUBJUNIT'],
            'SUBJSCHED'   => $firstRow['SUBJSCHEDULE']
        ];
    }

    // Process remaining rows
    while($row = $result->fetch_assoc()) {
        $idno = $row['IDNO'];
        $gender = strtoupper(trim($row['GENDER'])); 
        
        if ($gender === 'MALE' || $gender === 'M') {
            $targetArray =& $maleStudents;
        } else {
            $targetArray =& $femaleStudents;
        }

        // Only add student if not already listed
        if (!isset($targetArray[$idno])) {
            $targetArray[$idno] = [
                'LNAME'       => $row['LNAME'], 
                'FNAME'       => $row['FNAME'], 
                'MNAME'       => $row['MNAME'],
                'GENDER'      => $row['GENDER'], 
                'IDNO'        => $row['IDNO'], 
                'CRSCODE'     => $row['CRSCODE'],
                'CRSLEVEL'    => $row['CRSLEVEL'],
                'CRSMAJOR'    => $row['CRSMAJOR'],
                'SUBJDESC'    => $row['SUBJDESC'],
                'SUBJUNIT'    => $row['SUBJUNIT'],
                'SUBJSCHED'   => $row['SUBJSCHEDULE']
            ];
        }
    }
}

 $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Class Masterlist</title>
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
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .header-logo { flex: 0 0 100px; margin-right: 30px; }
        .header-logo img { height: 80px; width: auto; }
        .header-text { flex: 1; text-align: left; }
        .header-text h3 { margin: 0; font-weight: bold; text-transform: uppercase; font-size: 24px; }
        .header-text p { margin: 5px 0 0; font-size: 16px; font-weight: 500; }

        /* --- CLASS INFO BOX --- */
        .class-info-box {
            border: 1px solid #000;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 4px;
        }
        .info-row:last-child { border-bottom: none; margin-bottom: 0; }
        .info-label { font-weight: bold; width: 140px; flex-shrink: 0; }
        .info-value { flex-grow: 1; }

        /* --- TABLE STYLES --- */
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 5px 6px;
            vertical-align: middle;
            border: 1px solid #000;
            font-size: 11px;
        }
        .table thead th {
            background-color: #e2e2e2;
            color: #000;
            text-align: center;
            border-bottom: 2px solid #000;
            font-weight: bold;
        }
        .gender-header {
            margin-top: 25px;
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            text-align: center;
        }
        .no-print { display: block; }
        
        @media print {
            .no-print { display: none !important; }
            body { background-color: white; }
            .container { width: 100%; max-width: 100%; padding: 0; margin: 0; }
            .table, tr, td, th { page-break-inside: avoid; }
            .student-sheet { page-break-inside: avoid; }
            .class-info-box { border: 1px solid #000; background-color: #fff !important; -webkit-print-color-adjust: exact; }
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
            <p>Class Masterlist</p>
        </div>
    </div>

    <!-- Class Details Section -->
    <div class="class-info-box">
        <div class="info-row">
            <span class="info-label">Instructor:</span>
            <span class="info-value"><?php echo htmlspecialchars($classDetails['instructor']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Subject:</span>
            <span class="info-value"><?php echo htmlspecialchars($classDetails['subject'] ?: 'All Subjects'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Course Description:</span>
            <span class="info-value"><?php echo htmlspecialchars($classDetails['description'] ?? '-'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Schedule:</span>
            <span class="info-value"><?php echo htmlspecialchars($classDetails['sched'] ?? '-'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Units:</span>
            <span class="info-value"><?php echo htmlspecialchars($classDetails['units'] ?? '-'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Academic Year:</span>
            <span class="info-value"><?php echo htmlspecialchars($classDetails['year'] ?: 'All Years'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Semester:</span>
            <span class="info-value"><?php echo htmlspecialchars($classDetails['sem'] ?: 'All Semesters'); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Course:</span>
            <span class="info-value"><?php echo htmlspecialchars($classDetails['course'] ?: 'All Courses'); ?></span>
        </div>
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
                        <th style="width: 4%">No.</th>
                        <!-- Name columns separated -->
                        <th style="width: 20%">Last Name</th>
                        <th style="width: 15%">First Name</th>
                        <th style="width: 15%">Middle Name</th>
                        
                        <th style="width: 12%">Course</th>
                        <th style="width: 8%">Level</th>
                        <th style="width: 12%">Major</th>
                        
                        <!-- Unit removed from table, moved above -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td class="text-center"><?php echo $counter++; ?></td>
                            
                            <!-- Separated Name Columns -->
                            <td><?php echo htmlspecialchars($student['LNAME']); ?></td>
                            <td><?php echo htmlspecialchars($student['FNAME']); ?></td>
                            <td><?php echo htmlspecialchars($student['MNAME']); ?></td>
                            
                            <td class="text-center"><?php echo htmlspecialchars($student['CRSCODE']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($student['CRSLEVEL']); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($student['CRSMAJOR']); ?></td>
                            
                            <!-- Unit removed from here -->
                        </tr>
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