<?php
// student_viewer.php
require 'config.php';

// Initialize variables
 $studentData = null;
 $error = "";
 $searchID = "";
 $searchFname = "";
 $searchMname = "";
 $searchLname = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $searchID = $conn->real_escape_string($_POST['idno']);
    $searchFname = $conn->real_escape_string($_POST['firstname']);
    $searchMname = $conn->real_escape_string($_POST['middlename']);
    $searchLname = $conn->real_escape_string($_POST['lastname']);

    // SQL to find student matching ID AND Name
    $sql = "SELECT * FROM `tblnewgradesheetfinal` 
            WHERE `IDNO` = '$searchID' 
            AND `FNAME` LIKE '%$searchFname%' 
            AND `MNAME` LIKE '%$searchMname%' 
            AND `LNAME` LIKE '%$searchLname%'
            ORDER BY `SUBJCODE` ASC";
            
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Organize data
        $studentData = [
            'IDNO'       => '',
            'LNAME'      => '',
            'FNAME'      => '',
            'MNAME'      => '',
            'GENDER'     => '',
            'SEMESTER'   => '',
            'ACADEMICYR' => '',
            'CRSCODE'    => '',
            'CRSLEVEL'   => '',
            'CRSMAJOR'   => '',
            'COURSE_INFO' => []
        ];

        $firstRow = true;

        while($row = $result->fetch_assoc()) {
            if ($firstRow) {
                $studentData['IDNO'] = $row['IDNO'];
                $studentData['LNAME'] = $row['LNAME'];
                $studentData['FNAME'] = $row['FNAME'];
                $studentData['MNAME'] = $row['MNAME'];
                $studentData['GENDER'] = $row['GENDER'];
                $studentData['SEMESTER'] = $row['SEMESTER'];
                $studentData['ACADEMICYR'] = $row['ACADEMICYR'];
                $studentData['CRSCODE'] = $row['CRSCODE'];
                $studentData['CRSLEVEL'] = $row['CRSLEVEL'];
                $studentData['CRSMAJOR'] = $row['CRSMAJOR'];
                $firstRow = false;
            }

            $studentData['COURSE_INFO'][] = [
                'CRSCODE'     => $row['CRSCODE'],
                'CRSLEVEL'    => $row['CRSLEVEL'],
                'CRSMAJOR'    => $row['CRSMAJOR'],
                'SUBJCODE'    => $row['SUBJCODE'],
                'SUBJDESC'    => $row['SUBJDESC'],
                'SUBJUNIT'    => $row['SUBJUNIT'],
                'SUBJSCHEDULE'=> $row['SUBJSCHEDULE'],
                'INSTNAME'    => $row['INSTNAME'],
                'PG'          => $row['PG'],
                'MG'          => $row['MG'],
                'FG'          => $row['FG'],
                'FA'          => $row['FA'],
                'REMARKS'     => $row['REMARKS'],
                'STATUS'      => $row['STATUS']
            ];
        }
    } else {
        $error = "No record found. Please check your ID Number and Name spelling.";
    }
}
 $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Grade Viewer</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- CSS Variables --- */
        :root {
            --primary-color: #2c3e50;
            --primary-dark: #1a252f;
            --accent-color: #3498db;
            --accent-hover: #2980b9;
            --bg-light: #f4f6f9;
            --white: #ffffff;
            --text-dark: #2c3e50;
            --text-muted: #7f8c8d;
            --border-color: #e2e8f0;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* --- Header --- */
        header {
            background: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .brand i {
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .nav-link {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: var(--accent-color);
        }

        /* --- Main Layout --- */
        main {
            flex: 1;
            padding: 3rem 1rem;
            max-width: 1000px;
            width: 100%;
            margin: 0 auto;
        }

        .page-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-title h1 {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: var(--text-muted);
        }

        /* --- Search Card --- */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .search-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--primary-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 0.95rem;
            transition: border-color 0.3s;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: #e2e8f0;
            color: var(--text-dark);
        }

        .btn-secondary:hover {
            background-color: #cbd5e1;
        }

        /* --- Error Message --- */
        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
        }

        .alert-danger {
            background-color: #fef2f2;
            color: var(--danger-color);
            border: 1px solid #fecaca;
        }

        /* --- Student Profile Header --- */
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border-radius: var(--radius) var(--radius) 0 0;
            padding: 2rem;
            color: var(--white);
            position: relative;
            margin-bottom: 4rem; /* Space for avatar overlap */
        }

        .profile-name {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .profile-id {
            font-size: 1rem;
            opacity: 0.9;
            font-family: monospace;
            background: rgba(255,255,255,0.2);
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: var(--radius);
        }

        .stat-item h4 {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .stat-item span {
            font-size: 0.85rem;
            opacity: 0.9;
            text-transform: uppercase;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            background: var(--white);
            border-radius: 50%;
            padding: 4px;
            position: absolute;
            bottom: -60px;
            right: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            background-color: #eee;
        }

        /* --- Grades Table --- */
        .table-container {
            overflow-x: auto;
            padding-top: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th {
            background-color: #f8f9fa;
            text-align: left;
            padding: 1rem;
            font-weight: 600;
            color: var(--primary-color);
            border-bottom: 2px solid var(--border-color);
            white-space: nowrap;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .subject-code {
            font-weight: 700;
            color: var(--primary-color);
            display: block;
        }

        .subject-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }

        .schedule-badge {
            font-size: 0.75rem;
            background: #eef2f7;
            color: var(--text-muted);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            border: 1px solid #dfe6ed;
        }

        .grade-box {
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            font-size: 0.85rem;
        }

        .grade-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.25rem;
        }

        .grade-row:last-child {
            margin-bottom: 0;
        }

        .grade-val {
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 4px;
            margin-top: 0.5rem;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* --- Footer --- */
        footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: auto;
        }

        /* --- Mobile Responsive --- */
        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }

            .profile-header {
                padding-right: 1rem;
                padding-left: 1rem;
            }

            .profile-avatar {
                right: 1.5rem;
                width: 100px;
                height: 100px;
                bottom: -50px;
            }

            .profile-name {
                font-size: 1.5rem;
            }

            /* Responsive Table Stacking */
            thead {
                display: none;
            }

            tr {
                display: block;
                margin-bottom: 1.5rem;
                border: 1px solid var(--border-color);
                border-radius: var(--radius);
                padding: 1rem;
                background: var(--white);
            }

            td {
                display: flex;
                justify-content: space-between;
                padding: 0.5rem 0;
                border: none;
                text-align: right;
            }

            td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--text-muted);
                text-align: left;
                margin-right: 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <nav class="navbar">
            <a href="index.php" class="brand">
                <i class="fas fa-university"></i>
                <span>KCC Grading System</span>
            </a>
            <div>
                <a href="login.php" class="nav-link"><i class="fas fa-user-shield"></i> Admin Login</a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <div class="page-title">
            <h1>Grade Inquiry</h1>
            <p>Enter your details below to view your academic record.</p>
        </div>

        <!-- Search Form Card -->
        <div class="card">
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="search-form">
                    <div class="form-group">
                        <label for="idno">ID Number</label>
                        <input type="text" name="idno" id="idno" class="form-control" placeholder="Ex: 2023-0001" required value="<?php echo htmlspecialchars($searchID); ?>">
                    </div>
                    <div class="form-group">
                        <label for="firstname">First Name</label>
                        <input type="text" name="firstname" id="firstname" class="form-control" placeholder="First Name" required value="<?php echo htmlspecialchars($searchFname); ?>">
                    </div>
                    <div class="form-group">
                        <label for="middlename">Middle Name</label>
                        <input type="text" name="middlename" id="middlename" class="form-control" placeholder="Middle Name (Optional)" value="<?php echo htmlspecialchars($searchMname); ?>">
                    </div>
                    <div class="form-group">
                        <label for="lastname">Last Name</label>
                        <input type="text" name="lastname" id="lastname" class="form-control" placeholder="Last Name" required value="<?php echo htmlspecialchars($searchLname); ?>">
                    </div>
                    <div class="form-group" style="flex-direction: row; gap: 0.5rem;">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                        <a href="student_viewer.php" class="btn btn-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Student Results Section -->
        <?php if ($studentData): ?>
            <div class="card" style="padding: 0; overflow: hidden; border: none;">
                
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-name">
                        <?php echo htmlspecialchars($studentData['LNAME'] . ', ' . $studentData['FNAME'] . ' ' . substr($studentData['MNAME'], 0, 1) . '.'); ?>
                    </div>
                    <div class="profile-id">ID: <?php echo htmlspecialchars($studentData['IDNO']); ?></div>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <h4><?php echo htmlspecialchars($studentData['CRSCODE'] . ' ' . $studentData['CRSLEVEL']); ?></h4>
                            <span>Course & Level</span>
                        </div>
                        <div class="stat-item">
                            <h4><?php echo htmlspecialchars($studentData['SEMESTER']); ?></h4>
                            <span>Semester</span>
                        </div>
                        <div class="stat-item">
                            <h4><?php echo htmlspecialchars($studentData['ACADEMICYR']); ?></h4>
                            <span>School Year</span>
                        </div>
                    </div>

                    <div class="profile-avatar">
                        <!-- Generic Placeholder or dynamic user image -->
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($studentData['FNAME'] . '+' . $studentData['LNAME']); ?>&background=2c3e50&color=fff&size=128" alt="Student Avatar">
                    </div>
                </div>

                <!-- Grade Table -->
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 35%;">Subject Description</th>
                                <th style="width: 10%;">Units</th>
                                <th style="width: 25%;">Instructor</th>
                                <th style="width: 30%;">Grades & Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($studentData['COURSE_INFO'] as $course): ?>
                            <tr>
                                <td data-label="Subject">
                                    <span class="subject-code"><?php echo htmlspecialchars($course['SUBJCODE']); ?></span>
                                    <div class="subject-desc"><?php echo htmlspecialchars($course['SUBJDESC']); ?></div>
                                    <?php if(!empty($course['SUBJSCHEDULE'])): ?>
                                        <span class="schedule-badge"><i class="far fa-clock"></i> <?php echo htmlspecialchars($course['SUBJSCHEDULE']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Units" class="text-center"><?php echo htmlspecialchars($course['SUBJUNIT']); ?></td>
                                <td data-label="Instructor"><?php echo htmlspecialchars($course['INSTNAME']); ?></td>
                                <td data-label="Grades">
                                    <div class="grade-box">
                                        <div class="grade-row">
                                            <span>Prelim:</span>
                                            <span class="grade-val"><?php echo htmlspecialchars($course['PG']); ?></span>
                                        </div>
                                        <div class="grade-row">
                                            <span>Midterm:</span>
                                            <span class="grade-val"><?php echo htmlspecialchars($course['MG']); ?></span>
                                        </div>
                                        <div class="grade-row">
                                            <span>Final:</span>
                                            <span class="grade-val"><?php echo htmlspecialchars($course['FG']); ?></span>
                                        </div>
                                    </div>
                                    <?php if($course['REMARKS']): ?>
                                        <div class="badge badge-warning">
                                            <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($course['REMARKS']); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Passed
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> KCC College. All rights reserved.</p>
    </footer>

</body>
</html>