<?php
include 'config.php';

// 1. Fetch Distinct Departments for Filter
 $dept_query = "SELECT DISTINCT department_name FROM exam_results ORDER BY department_name ASC";
 $dept_result = $conn->query($dept_query);

// 2. Fetch Distinct Grades for Filter
// We assume the Grade is the first part of the department name (e.g., "GRADE 1" from "GRADE 1 - ELEMENTARY")
// If you have a specific 'grade' column, change the SQL below to "SELECT DISTINCT grade FROM exam_results"
 $grade_query = "SELECT DISTINCT SUBSTRING_INDEX(department_name, ' ', 2) as grade_name FROM exam_results ORDER BY grade_name ASC";
 $grade_result = $conn->query($grade_query);

// 3. Handle Filters
 $filter_department = isset($_GET['department']) ? $_GET['department'] : '';
 $filter_grade = isset($_GET['grade']) ? $_GET['grade'] : '';

// 4. Build Main Query
 $query = "SELECT * FROM exam_results WHERE 1=1";

if (!empty($filter_department)) {
    $query .= " AND department_name = '" . $conn->real_escape_string($filter_department) . "'";
}

if (!empty($filter_grade)) {
    // Checks if the department name starts with the selected grade
    $query .= " AND department_name LIKE '" . $conn->real_escape_string($filter_grade) . "%'";
}

 $query .= " ORDER BY department_name ASC, student_name ASC";
 $result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrance Exam Results</title>
    <link rel="icon" href="uploads/csr.png" type="image/x-icon">
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 20px;
            background-color: #f0f0f0;
            -webkit-print-color-adjust: exact;
        }
        
        .certificate-container {
            width: 100%;
            min-height: 297mm;
            margin: 0 auto;
            background-color: white;
            padding: 20mm;
            box-sizing: border-box;
            position: relative;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        /* Watermark Styling */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            height: 400px;
            opacity: 0.05;
            pointer-events: none;
            z-index: 0;
            mix-blend-mode: multiply;
        }

        .content-wrapper {
            position: relative;
            z-index: 1;
        }
        
        /* Header Section */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .school-header {
            display: flex;
            align-items: center;
            width: 70%;
        }
        
        .school-logo {
            width: 90px;
            height: 90px;
            margin-right: 20px;
            object-fit: contain;
        }
        
        .school-details {
            text-align: left;
        }
        
        .school-name {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
            color: #1a3a5f;
        }
        
        .school-address {
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .school-contact {
            font-size: 12px;
            font-style: italic;
        }

        .report-date {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .separator {
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
            width: 100%;
        }
        
        .report-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 10px 0 20px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: underline;
            text-underline-offset: 5px;
        }
        
        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 10px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        /* Filter Form Styling */
        .filter-controls {
            background: white;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        select, button, a.btn-reset {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn-primary { background-color: #1a3a5f; color: white; border: none; cursor: pointer; }
        .btn-success { background-color: #28a745; color: white; border: none; cursor: pointer; }
        .btn-secondary { background-color: #6c757d; color: white; text-decoration: none; border: none; cursor: pointer; }

        /* Footer */
        .report-footer {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            padding-right: 20px;
        }
        
        .signature-box {
            text-align: center;
            width: 250px;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 60px;
            margin-bottom: 5px;
        }
        
        .signatory-name {
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .signatory-title {
            font-size: 13px;
            text-transform: uppercase;
        }

        /* Print Controls */
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .certificate-container {
                box-shadow: none;
                border: none;
                margin: 0;
                width: 100%;
                min-height: auto;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Filter Form (No Print) -->
    <div class="no-print filter-controls">
        <form method="GET" action="printexamresults.php" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; width: 100%;">
            
            <div class="filter-group">
                <label><strong>Grade:</strong></label>
                <select name="grade">
                    <option value="">All Grades</option>
                    <?php 
                    if ($grade_result && $grade_result->num_rows > 0):
                        while ($g_row = $grade_result->fetch_assoc()):
                            $selected = ($filter_grade == $g_row['grade_name']) ? 'selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($g_row['grade_name']); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($g_row['grade_name']); ?>
                        </option>
                    <?php 
                        endwhile; 
                    endif; 
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label><strong>Department:</strong></label>
                <select name="department">
                    <option value="">All Departments</option>
                    <?php 
                    if ($dept_result && $dept_result->num_rows > 0):
                        while ($d_row = $dept_result->fetch_assoc()):
                            $selected = ($filter_department == $d_row['department_name']) ? 'selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($d_row['department_name']); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($d_row['department_name']); ?>
                        </option>
                    <?php 
                        endwhile; 
                    endif; 
                    ?>
                </select>
            </div>

            <button type="submit" class="btn-primary">Filter</button>
            <a href="printexamresults.php" class="btn-secondary">Reset</a>
            <button type="button" onclick="window.print()" class="btn-success">Print Report</button>
        </form>
    </div>

    <div class="certificate-container">
        <!-- Watermark Background -->
        <img src="uploads/csr.png" alt="Watermark" class="watermark">
        
        <div class="content-wrapper">
            <!-- Header Section (Copied from Good Moral) -->
            <div class="header-section">
                <div class="school-header">
                    <img src="uploads/csr.png" alt="School Logo" class="school-logo">
                    <div class="school-details">
                        <div class="school-name"><?php echo SCHOOL_NAME; ?></div>
                        <div class="school-address"><?php echo SCHOOL_ADDRESS; ?></div>
                        <div class="school-contact"><?php echo SCHOOL_EMAIL; ?> | <?php echo SCHOOL_CONTACT; ?></div>
                    </div>
                </div>
                
                <div class="report-date">
                    <?php echo date("F j, Y"); ?>
                </div>
            </div>

            <!-- Decorative Separator Line -->
            <div class="separator"></div>
            
            <div class="report-title">
                Entrance Exam Results Report
            </div>

            <!-- Filter Note -->
            <?php if(!empty($filter_department) || !empty($filter_grade)): ?>
            <div style="text-align: center; margin-bottom: 15px; font-style: italic;">
                Showing results for: 
                <?php if(!empty($filter_grade)) echo "Grade: <strong>" . htmlspecialchars($filter_grade) . "</strong> "; ?>
                <?php if(!empty($filter_department)) echo "Department: <strong>" . htmlspecialchars($filter_department) . "</strong>"; ?>
            </div>
            <?php endif; ?>
            
            <table>
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 5%;">#</th>
                        <th rowspan="2" style="width: 30%;">Student Name</th>
                        <th colspan="3">Standardized Test</th>
                        <th colspan="2">Teacher Made Test</th>
                    </tr>
                    <tr>
                        <th>Raw Score</th>
                        <th>% Rank</th>
                        <th>Verbal Desc.</th>
                        <th>Raw Score</th>
                        <th>Interpretation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result && $result->num_rows > 0):
                        $count = 1;
                        while ($row = $result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td style="text-align: center;"><?php echo $count++; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['student_name']); ?></strong><br>
                            <small style="color: #555;"><?php echo htmlspecialchars($row['department_name']); ?></small>
                        </td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['std_raw_score']); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['std_percentile_rank']); ?></td>
                        <td><?php echo htmlspecialchars($row['std_verbal_desc']); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($row['tmt_raw_score']); ?></td>
                        <td><?php echo htmlspecialchars($row['tmt_interpretation']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">No results found for the selected filters.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="report-footer">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signatory-name">Sr. Joy A. Dula, AR</div>
                    <div class="signatory-title">Guidance Counselor</div>
                </div>
            </div>

            <!-- Note at the very bottom -->
            <div style="margin-top: 40px; text-align: center; font-size: 11px; font-weight: bold; font-style: italic; color: #555;">
                NOTE: This document is not valid without the Official School Seal.
            </div>
        </div>
    </div>
</body>
</html>