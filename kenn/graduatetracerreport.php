<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// --- 1. Data Fetching and Statistics Calculation ---

// Get Total Graduates
 $total_res = $conn->query("SELECT COUNT(*) as count FROM graduate_tracer");
 $total_graduates = $total_res->fetch_assoc()['count'];

// A. Employment Statistics
// 1. Total Employed (Has a company name)
 $employed_res = $conn->query("SELECT COUNT(*) as count FROM graduate_tracer WHERE company != ''");
 $total_employed = $employed_res->fetch_assoc()['count'];

// 2. Employment Rate
 $employment_rate = ($total_graduates > 0) ? round(($total_employed / $total_graduates) * 100, 2) : 0;

// 3. Time to Employment (Distribution)
 $time_stats = [];
 $time_query = "SELECT employment_time, COUNT(*) as count FROM graduate_tracer WHERE employment_time != '' GROUP BY employment_time";
 $time_res = $conn->query($time_query);
while($row = $time_res->fetch_assoc()) {
    $time_stats[$row['employment_time']] = $row['count'];
}

// B. Salary Statistics (among employed)
// We parse the stored salary strings (e.g., "10,000-15,000") to group them
 $salary_stats = [
    'Below 15k' => 0,
    '15k - 25k' => 0,
    '25k - 35k' => 0,
    '35k - 50k' => 0,
    'Above 50k' => 0
];

 $salary_query = "SELECT salary FROM graduate_tracer WHERE salary != ''";
 $salary_res = $conn->query($salary_query);
while($row = $salary_res->fetch_assoc()) {
    // Simple keyword matching since data is stored as strings
    $val = $row['salary'];
    if (strpos($val, '10,000 and below') !== false || strpos($val, '10,000 - 15,000') !== false) {
        $salary_stats['Below 15k']++;
    } elseif (strpos($val, '15,000-20,000') !== false || strpos($val, '20,000-25,000') !== false) {
        $salary_stats['15k - 25k']++;
    } elseif (strpos($val, '25,000-30,000') !== false || strpos($val, '30,000-35,000') !== false) {
        $salary_stats['25k - 35k']++;
    } elseif (strpos($val, '35,000-40,000') !== false || strpos($val, '40,000-50,000') !== false) {
        $salary_stats['35k - 50k']++;
    } else {
        // Catch any 'Other' or higher ranges not explicitly listed
        $salary_stats['Above 50k']++; 
    }
}

// C. Top Programs
 $programs_query = "SELECT programs, COUNT(*) as count FROM graduate_tracer WHERE programs != '' GROUP BY programs ORDER BY count DESC LIMIT 5";
 $programs_res = $conn->query($programs_query);

// D. Top Employers
 $employer_query = "SELECT company, COUNT(*) as count FROM graduate_tracer WHERE company != '' GROUP BY company ORDER BY count DESC LIMIT 5";
 $employer_res = $conn->query($employer_query);

// E. Gender Distribution
 $gender_stats = ['Male' => 0, 'Female' => 0, 'Prefer not to say' => 0];
 $gender_query = "SELECT gender, COUNT(*) as count FROM graduate_tracer GROUP BY gender";
 $gender_res = $conn->query($gender_query);
while($row = $gender_res->fetch_assoc()) {
    if(isset($gender_stats[$row['gender']])) {
        $gender_stats[$row['gender']] = $row['count'];
    }
}

// F. Recent Graduates for "Raw Data" Section (Limit 10 for report summary)
 $recent_query = "SELECT * FROM graduate_tracer ORDER BY submitted_at DESC LIMIT 10";
 $recent_res = $conn->query($recent_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Graduate Tracer Statistics Report</title>
    <link rel="icon" href="uploads/csr.png" type="image/x-icon">
    <style>
        /* General Body and Font Settings */
        body {
            font-family: 'Times New Roman', Times, serif; /* Formal Font */
            margin: 0;
            padding: 20px;
            color: #000;
            background-color: #f0f0f0;
        }

        /* --- Header Styles --- */
        .report-header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header-logo img {
            height: 80px;
            width: auto;
            vertical-align: middle;
        }
        .header-school {
            font-size: 26px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 10px 0 5px 0;
            letter-spacing: 1px;
        }
        .header-subtitle {
            font-size: 22px;
            font-weight: bold;
            margin: 5px 0;
            text-decoration: underline;
        }
        .header-meta {
            font-size: 14px;
            margin-top: 10px;
            font-style: italic;
        }

        /* --- Filter Section (Screen Only) --- */
        .filters {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            font-size: 13px;
        }
        .filters .actions {
            margin-top: 10px;
            text-align: right;
        }
        .btn-print { 
            padding: 8px 16px; 
            cursor: pointer; 
            border: none; 
            border-radius: 3px; 
            color: white; 
            font-weight: bold; 
            background-color: #555; 
        }
        .btn-print:hover { background-color: #333; }

        /* --- Report Content Styles --- */
        .paper {
            background-color: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        /* Grid Layout for Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            border: 1px solid #000;
            padding: 20px;
            position: relative;
            page-break-inside: avoid;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background-color: #1a3a5f;
        }

        .stat-card h3 {
            text-align: center;
            text-transform: uppercase;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-top: 0;
            color: #1a3a5f;
        }

        .big-number {
            text-align: center;
            font-size: 42px;
            font-weight: bold;
            margin: 15px 0;
            color: #333;
        }

        .stat-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .stat-list li {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dotted #ccc;
            font-size: 15px;
        }
        .stat-list li span:first-child { font-weight: bold; }
        .stat-list li span:last-child { color: #1a3a5f; }

        .chart-bar-container {
            margin-bottom: 12px;
        }
        .chart-label {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
        }
        .progress-track {
            background-color: #eee;
            height: 12px;
            border-radius: 6px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background-color: #1a3a5f;
        }

        /* Raw Data / Recent Entries */
        .record-entry {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #ddd;
            page-break-inside: avoid;
            background: #fff;
        }
        .record-entry::after {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background-color: #1a3a5f;
        }
        .record-title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #ccc;
            margin-bottom: 10px;
            padding-bottom: 5px;
            color: #1a3a5f;
        }
        .data-row {
            display: flex;
            margin-bottom: 6px;
        }
        .data-label {
            font-weight: bold;
            width: 140px;
            flex-shrink: 0;
            font-size: 13px;
            color: #555;
        }
        .data-value {
            font-size: 14px;
        }

        /* Footer */
        .report-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        /* Print Media Query */
        @media print {
            body { background-color: white; padding: 0; }
            .paper { box-shadow: none; padding: 0; margin: 0; width: 100%; }
            .filters { display: none !important; }
            .stat-card { border: 1px solid #000; break-inside: avoid; }
        }
    </style>
</head>
<body>

    <!-- Filter Section (Visible on Screen Only) -->
    <div class="filters no-print">
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin:0;">Report Filters</h3>
            <div class="actions">
                <button type="button" class="btn-print" onclick="window.print()">Print Report</button>
            </div>
        </div>
        <p style="margin:10px 0 0 0; font-size:12px; color:#666;">
            This report displays aggregate statistics based on all data in the Graduate Tracer database.
        </p>
    </div>

    <!-- Main Report Content -->
    <div class="paper">
        <div class="report-header">
            <div class="header-logo">
                <img src="uploads/csr.png" alt="School Logo">
            </div>
            <div class="header-school"><?php echo SCHOOL_NAME; ?></div>
            <div class="header-meta"><?php echo SCHOOL_ADDRESS; ?> | Contact: <?php echo SCHOOL_CONTACT; ?></div>
            <br>
            <div class="header-subtitle">Graduate Tracer Study Report</div>
            <div class="header-meta">
                Generated on: <?php echo date('F j, Y g:i A'); ?>
            </div>
        </div>
        
        <!-- Summary Cards -->
        <div class="stats-grid">
            <!-- Card 1: Total vs Employed -->
            <div class="stat-card">
                <h3>Overall Employability</h3>
                <div class="big-number"><?php echo $employment_rate; ?>%</div>
                <ul class="stat-list">
                    <li>
                        <span>Total Graduates Traced:</span>
                        <span><?php echo number_format($total_graduates); ?></span>
                    </li>
                    <li>
                        <span>Total Employed:</span>
                        <span><?php echo number_format($total_employed); ?></span>
                    </li>
                    <li>
                        <span>Still Seeking / Others:</span>
                        <span><?php echo number_format($total_graduates - $total_employed); ?></span>
                    </li>
                </ul>
            </div>

            <!-- Card 2: Gender Distribution -->
            <div class="stat-card">
                <h3>Demographics (Gender)</h3>
                <div style="margin-top:15px;">
                    <?php foreach($gender_stats as $label => $count): 
                        $percent = ($total_graduates > 0) ? round(($count / $total_graduates) * 100) : 0;
                    ?>
                    <div class="chart-bar-container">
                        <div class="chart-label">
                            <span><?php echo $label; ?></span>
                            <span><?php echo $count; ?> (<?php echo $percent; ?>%)</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: <?php echo $percent; ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Card 3: Top Programs -->
            <div class="stat-card">
                <h3>Top Programs Completed</h3>
                <ul class="stat-list">
                    <?php 
                    if ($programs_res->num_rows > 0) {
                        while($row = $programs_res->fetch_assoc()) { 
                            // Clean up program string for display (truncate if too long)
                            $prog_name = strlen($row['programs']) > 30 ? substr($row['programs'], 0, 30) . '...' : $row['programs'];
                    ?>
                        <li>
                            <span><?php echo htmlspecialchars($prog_name); ?></span>
                            <span><?php echo $row['count']; ?></span>
                        </li>
                    <?php 
                        }
                    } else {
                        echo "<li><span>No data available</span></li>";
                    }
                    ?>
                </ul>
            </div>

            <!-- Card 4: Top Employers -->
            <div class="stat-card">
                <h3>Top Employers</h3>
                <ul class="stat-list">
                    <?php 
                    if ($employer_res->num_rows > 0) {
                        while($row = $employer_res->fetch_assoc()) { 
                    ?>
                        <li>
                            <span><?php echo htmlspecialchars($row['company']); ?></span>
                            <span><?php echo $row['count']; ?></span>
                        </li>
                    <?php 
                        }
                    } else {
                        echo "<li><span>No data available</span></li>";
                    }
                    ?>
                </ul>
            </div>
        </div>

        <!-- Detailed Breakdowns (Full Width) -->
        <div class="stat-card" style="margin-bottom: 30px;">
            <h3>Salary Range Distribution (Among Employed)</h3>
            <div class="stats-grid" style="margin-top:20px; gap:15px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <?php foreach($salary_stats as $range => $count): 
                    $percent = ($total_employed > 0) ? round(($count / $total_employed) * 100) : 0;
                ?>
                <div style="text-align:center; padding:10px; background:#f9f9f9; border-radius:4px;">
                    <div style="font-size:24px; font-weight:bold; color:#1a3a5f;"><?php echo $count; ?></div>
                    <div style="font-size:12px; color:#666;"><?php echo $range; ?></div>
                    <div style="font-size:11px; color:#999; margin-top:4px;"><?php echo $percent; ?>%</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="stat-card" style="margin-bottom: 30px;">
            <h3>Time to Employment</h3>
            <div class="stats-grid" style="margin-top:20px; gap:15px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <?php 
                // Ensure all expected keys exist for display
                $expected_times = ['Less than 1 month', '1–3 months', '4–6 months', '7–12 months', 'More than 1 year', 'N/A'];
                
                foreach($expected_times as $time): 
                    $count = isset($time_stats[$time]) ? $time_stats[$time] : 0;
                    $percent = ($total_employed > 0) ? round(($count / $total_employed) * 100) : 0;
                ?>
                <div style="text-align:center; padding:10px; background:#f9f9f9; border-radius:4px;">
                    <div style="font-size:24px; font-weight:bold; color:#1a3a5f;"><?php echo $count; ?></div>
                    <div style="font-size:12px; color:#666;"><?php echo $time; ?></div>
                    <div style="font-size:11px; color:#999; margin-top:4px;"><?php echo $percent; ?>%</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Raw Entries (Sample) -->
        <h3 style="border-bottom: 2px solid #1a3a5f; padding-bottom: 10px; margin-bottom: 20px;">Recent Graduate Entries (Sample)</h3>
        
        <?php if ($recent_res->num_rows > 0): ?>
            <?php while ($row = $recent_res->fetch_assoc()): ?>
                <div class="record-entry" style="position:relative;">
                    <div class="record-title">
                        <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['family_name']); ?> 
                        <span style="font-size:12px; font-weight:normal; text-transform:none; float:right; color:#666;">
                            Submitted: <?php echo date('M d, Y', strtotime($row['submitted_at'])); ?>
                        </span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Program:</span>
                        <span class="data-value"><?php echo htmlspecialchars($row['programs']); ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Year Graduated:</span>
                        <span class="data-value"><?php echo htmlspecialchars($row['year_graduated']); ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Current Position:</span>
                        <span class="data-value"><?php echo htmlspecialchars($row['position'] ?: 'N/A'); ?></span>
                    </div>
                    <div class="data-row">
                        <span class="data-label">Company:</span>
                        <span class="data-value"><?php echo htmlspecialchars($row['company'] ?: 'N/A'); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; padding: 20px;">No graduate records found in the database.</p>
        <?php endif; ?>
        
        <div class="report-footer">
            <p>Graduate Tracer Study Statistics Report generated electronically by the Guidance Office System.</p>
            <p>&copy; <?php echo date('Y'); ?> <?php echo SCHOOL_NAME; ?>. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>