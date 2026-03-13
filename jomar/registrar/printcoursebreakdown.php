<?php
require_once '../config.php';
requireRole('registrar');

// Get all courses
 $courses = [];
 $query = "SELECT *, CONCAT(coursename, ' - ', courselevel) as course_full_name FROM courses ORDER BY coursename, courselevel";
 $result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $courses[] = $row;
    }
}

// Get breakdown data for each course
 $breakdownData = [];

foreach ($courses as $course) {
    // Sanitize course ID to prevent SQL injection
    $courseId = mysqli_real_escape_string($conn, $course['id']);
    
    $courseData = [
        'course_name' => $course['coursename'],
        'course_level' => $course['courselevel'],
        'year_levels' => []
    ];
    
    // Get distinct year levels for this course
    $yearLevelsQuery = "SELECT DISTINCT year_level FROM enrollments 
                         WHERE course_id = '{$courseId}' 
                         AND status IN ('Registered', 'Enrolled')
                         ORDER BY year_level";
    $yearLevelsResult = mysqli_query($conn, $yearLevelsQuery);
    
    if (mysqli_num_rows($yearLevelsResult) > 0) {
        while ($yearLevelRow = mysqli_fetch_assoc($yearLevelsResult)) {
            $yearLevel = mysqli_real_escape_string($conn, $yearLevelRow['year_level']);
            
            // Get statistics for this course and year level
            $statsQuery = "SELECT 
                            s.gender,
                            s.civil_status,
                            e.status,
                            COUNT(*) as count
                          FROM enrollments e
                          JOIN students s ON e.student_id = s.id
                          WHERE e.course_id = '{$courseId}' 
                          AND e.year_level = '{$yearLevel}'
                          AND e.status IN ('Registered', 'Enrolled')
                          GROUP BY s.gender, s.civil_status, e.status";
            
            $statsResult = mysqli_query($conn, $statsQuery);
            
            $yearLevelData = [
                'year_level' => $yearLevel,
                'male_count' => 0,
                'female_count' => 0,
                'civil_status' => [
                    'Single' => 0,
                    'Married' => 0,
                    'Widowed' => 0,
                    'Separated' => 0,
                    'Others' => 0
                ],
                'registered_count' => 0,
                'enrolled_count' => 0
            ];
            
            if (mysqli_num_rows($statsResult) > 0) {
                while ($statsRow = mysqli_fetch_assoc($statsResult)) {
                    // Count by gender
                    if ($statsRow['gender'] === 'Male') {
                        $yearLevelData['male_count'] += $statsRow['count'];
                    } else {
                        $yearLevelData['female_count'] += $statsRow['count'];
                    }
                    
                    // Count by civil status
                    $civilStatus = $statsRow['civil_status'];
                    if (isset($yearLevelData['civil_status'][$civilStatus])) {
                        $yearLevelData['civil_status'][$civilStatus] += $statsRow['count'];
                    } else {
                        $yearLevelData['civil_status']['Others'] += $statsRow['count'];
                    }
                    
                    // Count by enrollment status
                    if ($statsRow['status'] === 'Registered') {
                        $yearLevelData['registered_count'] += $statsRow['count'];
                    } else if ($statsRow['status'] === 'Enrolled') {
                        $yearLevelData['enrolled_count'] += $statsRow['count'];
                    }
                }
            }
            
            $courseData['year_levels'][] = $yearLevelData;
        }
    }
    
    $breakdownData[] = $courseData;
}

// Determine academic year based on current month
 $currentMonth = date('n');
 $currentYear = date('Y');
if ($currentMonth >= 6) { // June or later
    $academicYear = $currentYear . '-' . ($currentYear + 1);
} else { // Before June
    $academicYear = ($currentYear - 1) . '-' . $currentYear;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Enrollment Breakdown</title>
    <style>
        @page {
            size: A4;
            margin: 1cm;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 210mm;
            margin: 0 auto;
            padding: 8mm;
            box-sizing: border-box;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .header .logo {
            margin-bottom: 10px;
        }
        
        .header .logo img {
            height: 50px;
        }
        
        .header h1 {
            font-size: 18pt;
            margin: 5px 0;
            padding: 0;
        }
        
        .header h2 {
            font-size: 14pt;
            margin: 5px 0;
            font-weight: normal;
        }
        
        .header .school-name {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
        }
        
        .header p {
            font-size: 10pt;
            margin: 2px 0;
        }
        
        .course-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .course-header {
            background-color: #f0f0f0;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #000;
            margin-bottom: 10px;
            text-align: center;
            font-size: 12pt;
        }
        
        .year-level-section {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
        }
        
        .year-level-header {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 11pt;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .stats-table th {
            background-color: #f8f9fa;
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
        }
        
        .stats-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-size: 10pt;
        }
        
        .civil-status-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .civil-status-table th {
            background-color: #f8f9fa;
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10pt;
        }
        
        .civil-status-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-size: 10pt;
        }
        
        .no-print {
            display: block;
            margin-bottom: 20px;
        }
        
        .print-btn {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12pt;
        }
        
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        
        .summary-section {
            margin-top: 30px;
            margin-bottom: 20px;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11pt;
        }
        
        .summary-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-size: 11pt;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            
            body {
                background: none;
            }
            
            .container {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print">
            <button class="print-btn" onclick="window.print()">Print Report</button>
        </div>
        
        <div class="header">
            <div class="logo">
                <img src="../uploads/csr.png" alt="School Logo">
            </div>
            <!-- Updated to use config constants -->
            <div class="school-name"><?= SCHOOL_NAME ?></div>
            <p><?= SCHOOL_ADDRESS ?></p>
            <p>Tel: <?= SCHOOL_CONTACT_NO ?> | Email: <?= SCHOOL_EMAIL ?></p>
            <h1>COURSE ENROLLMENT BREAKDOWN</h1>
            <h2>Academic Year: <?= $academicYear ?></h2>
        </div>
        
        <?php if (!empty($breakdownData)): ?>
        <!-- Summary Section -->
        <div class="summary-section">
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th>Male</th>
                        <th>Female</th>
                        <th>Total</th>
                        <th>Registered</th>
                        <th>Enrolled</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalMale = 0;
                    $totalFemale = 0;
                    $totalRegistered = 0;
                    $totalEnrolled = 0;
                    ?>
                    
                    <?php foreach ($breakdownData as $course): ?>
                        <?php foreach ($course['year_levels'] as $yearLevel): ?>
                        <tr>
                            <td><?= $course['course_name'] ?></td>
                            <td><?= $yearLevel['year_level'] ?></td>
                            <td><?= $yearLevel['male_count'] ?></td>
                            <td><?= $yearLevel['female_count'] ?></td>
                            <td><?= $yearLevel['male_count'] + $yearLevel['female_count'] ?></td>
                            <td><?= $yearLevel['registered_count'] ?></td>
                            <td><?= $yearLevel['enrolled_count'] ?></td>
                        </tr>
                        <?php 
                        $totalMale += $yearLevel['male_count'];
                        $totalFemale += $yearLevel['female_count'];
                        $totalRegistered += $yearLevel['registered_count'];
                        $totalEnrolled += $yearLevel['enrolled_count'];
                        ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                    
                    <tr class="total-row">
                        <td><strong>TOTAL</strong></td>
                        <td></td>
                        <td><strong><?= $totalMale ?></strong></td>
                        <td><strong><?= $totalFemale ?></strong></td>
                        <td><strong><?= $totalMale + $totalFemale ?></strong></td>
                        <td><strong><?= $totalRegistered ?></strong></td>
                        <td><strong><?= $totalEnrolled ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Detailed Breakdown -->
        <?php foreach ($breakdownData as $course): ?>
        <div class="course-section">
            <div class="course-header">
                <?= $course['course_name'] ?> - <?= $course['course_level'] ?>
            </div>
            
            <?php foreach ($course['year_levels'] as $yearLevel): ?>
            <div class="year-level-section">
                <div class="year-level-header">
                    Year Level: <?= $yearLevel['year_level'] ?>
                </div>
                
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Male</th>
                            <th>Female</th>
                            <th>Total</th>
                            <th>Registered</th>
                            <th>Enrolled</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $yearLevel['male_count'] ?></td>
                            <td><?= $yearLevel['female_count'] ?></td>
                            <td><?= $yearLevel['male_count'] + $yearLevel['female_count'] ?></td>
                            <td><?= $yearLevel['registered_count'] ?></td>
                            <td><?= $yearLevel['enrolled_count'] ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <table class="civil-status-table">
                    <thead>
                        <tr>
                            <th>Civil Status</th>
                            <th>Count</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalStudents = $yearLevel['male_count'] + $yearLevel['female_count'];
                        foreach ($yearLevel['civil_status'] as $status => $count): 
                            $percentage = $totalStudents > 0 ? round(($count / $totalStudents) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><?= $status ?></td>
                            <td><?= $count ?></td>
                            <td><?= $percentage ?>%</td>
                        </tr>
                        <?php 
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <p style="text-align: center; font-style: italic; margin-top: 30px;">No enrollment data found.</p>
        <?php endif; ?>
        
        <div style="margin-top: 20px; text-align: right;">
            <p>Generated on: <?= date('F d, Y') ?></p>
            <p>Generated by: <?= isset($_SESSION['username']) ? $_SESSION['username'] : 'System' ?></p>
        </div>
    </div>
</body>
</html>