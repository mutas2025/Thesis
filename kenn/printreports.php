<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get report type parameter
 $report_type = isset($_GET['type']) ? $_GET['type'] : 'counseling';

// Get student filter parameter
 $selected_student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : null;

// Get selected record IDs from checkbox array
 $selected_record_ids = isset($_GET['record_ids']) ? $_GET['record_ids'] : [];

 $selected_student_name = '';
if ($selected_student_id) {
    $stmt = $conn->prepare("SELECT full_name FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $selected_student_id);
    $stmt->execute();
    $student_res = $stmt->get_result();
    if ($student_res->num_rows > 0) {
        $selected_student_name = $student_res->fetch_assoc()['full_name'];
    }
}

// Get all students for the dropdown
 $all_students_for_filter = $conn->query("SELECT student_id, full_name FROM students ORDER BY full_name");

// Initialize variables
 $title = '';
 $query = '';

// 1. Build Base Query based on type
switch ($report_type) {
    case 'counseling':
        $title = 'Counseling Sessions Report';
        $query = "SELECT cs.*, s.full_name as student_name, st.full_name as counselor_name 
                  FROM counseling_sessions cs 
                  JOIN students s ON cs.student_id = s.student_id 
                  JOIN staff st ON cs.counselor_id = st.staff_id";
        $id_field = "cs.session_id"; // The ID to use for checkboxes
        break;
        
    case 'appointments':
        $title = 'Appointments Report';
        $query = "SELECT a.*, s.full_name as student_name, st.full_name as counselor_name 
                  FROM appointments a 
                  JOIN students s ON a.student_id = s.student_id 
                  JOIN staff st ON a.counselor_id = st.staff_id";
        $id_field = "a.appointment_id";
        break;
        
    case 'incidents':
        $title = 'Incidents Report';
        $query = "SELECT i.*, s.full_name as student_name 
                  FROM incidents i 
                  JOIN students s ON i.student_id = s.student_id";
        $id_field = "i.incident_id";
        break;
        
    case 'assessments':
        $title = 'Assessments Report';
        $query = "SELECT a.*, s.full_name as student_name 
                  FROM assessments a 
                  JOIN students s ON a.student_id = s.student_id";
        $id_field = "a.assessment_id";
        break;
        
    default:
        $title = 'Counseling Sessions Report';
        $query = "SELECT cs.*, s.full_name as student_name, st.full_name as counselor_name 
                  FROM counseling_sessions cs 
                  JOIN students s ON cs.student_id = s.student_id 
                  JOIN staff st ON cs.counselor_id = st.staff_id";
        $id_field = "cs.session_id";
}

// 2. Apply Filters
 $where_clauses = [];

// Filter by Student
if ($selected_student_id) {
    $where_clauses[] = "s.student_id = $selected_student_id";
}

// Filter by Selected IDs (If checkboxes are used)
// Only apply this if specific IDs were selected, otherwise just use the student filter
if (!empty($selected_record_ids)) {
    // Sanitize IDs
    $safe_ids = array_map('intval', $selected_record_ids);
    $ids_string = implode(',', $safe_ids);
    $where_clauses[] = "$id_field IN ($ids_string)";
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}

// Order By
switch ($report_type) {
    case 'counseling': $query .= " ORDER BY cs.session_date DESC"; break;
    case 'appointments': $query .= " ORDER BY a.appointment_datetime DESC"; break;
    case 'incidents': $query .= " ORDER BY i.incident_date DESC"; break;
    case 'assessments': $query .= " ORDER BY a.assessment_date DESC"; break;
}

// Append student name to title if filtered
if ($selected_student_name) {
    $title .= ' for ' . htmlspecialchars($selected_student_name);
}

 $results = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <link rel="icon" href="uploads/csr.png" type="image/x-icon">
    <style>
        /* General Body and Font Settings */
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 20px;
            color: #000;
            background-color: #f0f0f0;
        }

        /* --- Header Styles --- */
        .report-header {
            text-align: left;
            margin-bottom: 40px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .header-logo img {
            height: 100px;
            width: auto;
        }
        .header-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .header-school {
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0 0 5px 0;
            letter-spacing: 1px;
            line-height: 1.2;
        }
        .header-subtitle {
            font-size: 22px;
            font-weight: bold;
            margin: 5px 0;
            text-decoration: underline;
        }
        .header-meta {
            font-size: 14px;
            margin-top: 5px;
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
        .filters form {
            display: block;
        }
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
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
        .filter-group input, .filter-group select {
            padding: 6px;
            border: 1px solid #999;
            border-radius: 3px;
        }
        .filter-actions button {
            padding: 7px 15px;
            cursor: pointer;
            border: none;
            border-radius: 3px;
            color: white;
            font-weight: bold;
        }
        .btn-generate { background-color: #1a3a5f; }
        .btn-print { background-color: #555; }
        .btn-generate:hover { background-color: #0f2238; }
        .btn-print:hover { background-color: #333; }

        /* Checkbox List for Records */
        .record-selection-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        .record-checkbox-item {
            display: flex;
            align-items: center;
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .record-checkbox-item:last-child { border-bottom: none; }
        .record-checkbox-item input { margin-right: 10px; }
        .record-checkbox-info { font-size: 12px; }
        .record-checkbox-date { color: #666; font-size: 11px; }

        /* --- Report Content Styles --- */
        .paper {
            background-color: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            min-height: 297mm;
        }

        .summary-box {
            text-align: center;
            margin-bottom: 30px;
            padding: 10px;
            border: 1px solid #000;
            background-color: #fafafa;
        }
        .summary-box strong {
            font-size: 16px;
        }

        /* Record Entry Styles */
        .record-entry {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            position: relative;
            page-break-inside: avoid;
            background: #fff;
        }
        .record-entry::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #1a3a5f;
        }

        .record-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 1px solid #ccc;
            margin-bottom: 15px;
            padding-bottom: 5px;
            color: #1a3a5f;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px 30px;
            margin-bottom: 15px;
        }
        .form-row-full {
            grid-column: 1 / -1;
            margin-bottom: 15px;
        }

        .data-label {
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
            color: #444;
            margin-bottom: 2px;
            display: block;
        }

        .data-value {
            font-size: 15px;
            padding-bottom: 8px;
            border-bottom: 1px dotted #bbb;
            line-height: 1.4;
            min-height: 24px;
        }

        /* Footer */
        .report-footer {
            margin-top: 50px;
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
            .filters, .no-print { display: none !important; }
            .record-entry { border: none; margin-bottom: 40px; }
            .record-entry::after { height: 2px; }
        }
    </style>
</head>
<body>
    <!-- Filter Section (Visible on Screen Only) -->
    <div class="filters no-print">
        <form method="GET" action="">
            <input type="hidden" name="type" value="<?php echo $report_type; ?>">
            
            <div class="filter-row">
                <div class="filter-group">
                    <label for="report_type">Report Type:</label>
                    <select id="report_type" name="type" onchange="this.form.submit()">
                        <option value="counseling" <?php echo ($report_type == 'counseling') ? 'selected' : ''; ?>>Counseling Sessions</option>
                        <option value="appointments" <?php echo ($report_type == 'appointments') ? 'selected' : ''; ?>>Appointments</option>
                        <option value="incidents" <?php echo ($report_type == 'incidents') ? 'selected' : ''; ?>>Incidents</option>
                        <option value="assessments" <?php echo ($report_type == 'assessments') ? 'selected' : ''; ?>>Assessments</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="student_id">Student:</label>
                    <select id="student_id" name="student_id" onchange="loadStudentRecords(this.value)">
                        <option value="">-- Select Student to Choose Records --</option>
                        <?php 
                        if ($all_students_for_filter->num_rows > 0) {
                            $all_students_for_filter->data_seek(0);
                            while ($row = $all_students_for_filter->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $row['student_id']; ?>" <?php echo ($selected_student_id == $row['student_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['full_name']); ?>
                            </option>
                        <?php 
                            endwhile; 
                        }
                        ?>
                    </select>
                </div>
                <div class="filter-actions">
                    <button type="button" class="btn-generate" onclick="checkAllRecords()">Select All Records</button>
                    <button type="button" class="btn-generate" onclick="document.getElementById('record_ids[]').checked = false;">Clear Selection</button>
                    <button type="submit" class="btn-print">Update View</button>
                    <button type="button" class="btn-print" onclick="window.print()">Print Report</button>
                </div>
            </div>

            <!-- Record Selection Area (Only shows if student is selected) -->
            <?php if ($selected_student_id): ?>
                <div class="filter-group">
                    <label>Select Specific Records to Print:</label>
                    
                    <?php
                    // Fetch ALL records for this student to show in the checklist
                    // We re-run a simplified query just to get the list
                    $list_query = "";
                    $date_field = "";
                    
                    if ($report_type == 'counseling') {
                        $list_query = "SELECT session_id as id, session_date as dt, counseling_type as type, 'session' as src FROM counseling_sessions WHERE student_id = $selected_student_id ORDER BY session_date DESC";
                        $date_field = "session_date";
                    } elseif ($report_type == 'appointments') {
                        $list_query = "SELECT appointment_id as id, appointment_datetime as dt, purpose as type, 'appointment' as src FROM appointments WHERE student_id = $selected_student_id ORDER BY appointment_datetime DESC";
                        $date_field = "appointment_datetime";
                    } elseif ($report_type == 'incidents') {
                        $list_query = "SELECT incident_id as id, incident_date as dt, incident_type as type, 'incident' as src FROM incidents WHERE student_id = $selected_student_id ORDER BY incident_date DESC";
                        $date_field = "incident_date";
                    } elseif ($report_type == 'assessments') {
                        $list_query = "SELECT assessment_id as id, assessment_date as dt, assessment_type as type, 'assessment' as src FROM assessments WHERE student_id = $selected_student_id ORDER BY assessment_date DESC";
                        $date_field = "assessment_date";
                    }

                    $list_result = $conn->query($list_query);
                    
                    if ($list_result && $list_result->num_rows > 0):
                    ?>
                        <div class="record-selection-list">
                            <?php while($list_row = $list_result->fetch_assoc()): 
                                // Format date for display
                                $display_date = ($report_type == 'appointments') 
                                    ? date('F j, Y g:i A', strtotime($list_row['dt']))
                                    : date('F j, Y', strtotime($list_row['dt']));
                                
                                // Check if this ID is in the selected array
                                $is_checked = in_array($list_row['id'], $selected_record_ids) ? 'checked' : '';
                            ?>
                                <div class="record-checkbox-item">
                                    <input type="checkbox" name="record_ids[]" value="<?php echo $list_row['id']; ?>" <?php echo $is_checked; ?> class="record-cb">
                                    <div class="record-checkbox-info">
                                        <strong><?php echo htmlspecialchars($display_date); ?></strong>
                                        <div class="record-checkbox-date"><?php echo htmlspecialchars($list_row['type']); ?></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <small style="color:#666; display:block; margin-top:5px;">Check the boxes above and click "Update View" to filter the report.</small>
                    <?php else: ?>
                        <p style="color: #999; font-style: italic;">No records found for this student.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Main Report Content -->
    <div class="paper">
        <div class="report-header">
            <div class="header-logo">
                <img src="uploads/csr.png" alt="School Logo">
            </div>
            <div class="header-text">
                <div class="header-school"><?php echo SCHOOL_NAME; ?></div>
                <div class="header-meta"><?php echo SCHOOL_ADDRESS; ?> | Contact: <?php echo SCHOOL_CONTACT; ?></div>
                <div class="header-subtitle"><?php echo $title; ?></div>
            </div>
        </div>
        
        <div class="summary-box">
            Total Records Generated: <strong><?php echo $results->num_rows; ?></strong>
        </div>

        <div class="reports-container">
            <?php if ($results->num_rows > 0): ?>
                <?php while ($row = $results->fetch_assoc()): ?>
                    <div class="record-entry">
                        <?php if ($report_type == 'counseling'): ?>
                            <div class="record-title">Counseling Session Record</div>
                            <div class="form-grid">
                                <div>
                                    <span class="data-label">Student Name</span>
                                    <div class="data-value"><?php echo htmlspecialchars($row['student_name']); ?></div>
                                </div>
                                <div>
                                    <span class="data-label">Counselor</span>
                                    <div class="data-value"><?php echo htmlspecialchars($row['counselor_name']); ?></div>
                                </div>
                                <div>
                                    <span class="data-label">Session Date</span>
                                    <div class="data-value"><?php echo date('F j, Y', strtotime($row['session_date'])); ?></div>
                                </div>
                                <div>
                                    <span class="data-label">Counseling Type</span>
                                    <div class="data-value"><?php echo htmlspecialchars($row['counseling_type']); ?></div>
                                </div>
                                <div>
                                    <span class="data-label">Status</span>
                                    <div class="data-value"><?php echo htmlspecialchars($row['session_status']); ?></div>
                                </div>
                                <?php if (!empty($row['follow_up_date'])): ?>
                                <div>
                                    <span class="data-label">Follow-up Date</span>
                                    <div class="data-value"><?php echo date('F j, Y', strtotime($row['follow_up_date'])); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-row-full">
                                <span class="data-label">Reason for Session</span>
                                <div class="data-value"><?php echo nl2br(htmlspecialchars($row['reason'])); ?></div>
                            </div>
                            <div class="form-row-full">
                                <span class="data-label">Session Notes & Observations</span>
                                <div class="data-value"><?php echo nl2br(htmlspecialchars($row['session_notes'])); ?></div>
                            </div>
                            <div class="form-row-full">
                                <span class="data-label">Recommendations</span>
                                <div class="data-value"><?php echo nl2br(htmlspecialchars($row['recommendations'])); ?></div>
                            </div>

                        <?php elseif ($report_type == 'appointments'): ?>
                            <div class="record-title">Appointment Record</div>
                            <div class="form-grid">
                                <div>
                                    <span class="data-label">Student Name</span>
                                    <div class="data-value"><?php echo htmlspecialchars($row['student_name']); ?></div>
                                </div>
                                <div>
                                    <span class="data-label">Counselor</span>
                                    <div class="data-value"><?php echo htmlspecialchars($row['counselor_name']); ?></div>
                                </div>
                                <div>
                                    <span class="data-label">Date & Time</span>
                                    <div class="data-value"><?php echo date('F j, Y - g:i A', strtotime($row['appointment_datetime'])); ?></div>
                                </div>
                                <div>
                                    <span class="data-label">Status</span>
                                    <div class="data-value"><?php echo htmlspecialchars($row['status']); ?></div>
                                </div>
                            </div>
                            <div class="form-row-full">
                                <span class="data-label">Purpose</span>
                                <div class="data-value"><?php echo nl2br(htmlspecialchars($row['purpose'])); ?></div>
                            </div>

                        <?php elseif ($report_type == 'incidents'): ?>
                            <div class="record-title">Incident Report</div>
                            <div class="form-grid">
                                <div>
                                    <span class="data-label">Student Name</span>
                                    <div class="data-value"><?php echo htmlspecialchars($row['student_name']); ?></div>
                                </div>
                                <div>
                                    <span class="data-label">Incident Date</span>
                                    <div class="data-value"><?php echo date('F j, Y', strtotime($row['incident_date'])); ?></div>
                                </div>
                                <div>
                                    <span class="data-label">Incident Type</span>
                                    <div class="data-value"><?php echo htmlspecialchars($row['incident_type']); ?></div>
                                </div>
                                <div>
                                    <span class="data-label">Resolution Status</span>
                                    <div class="data-value"><?php echo htmlspecialchars($row['resolution_status']); ?></div>
                                </div>
                            </div>
                            <div class="form-row-full">
                                <span class="data-label">Description</span>
                                <div class="data-value"><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
                            </div>
                            <div class="form-row-full">
                                <span class="data-label">Action Taken</span>
                                <div class="data-value"><?php echo nl2br(htmlspecialchars($row['action_taken'])); ?></div>
                            </div>
                            <div class="form-row-full">
                                <span class="data-label">Counselor Remarks</span>
                                <div class="data-value"><?php echo nl2br(htmlspecialchars($row['counselor_remarks'])); ?></div>
                            </div>

                        <?php elseif ($report_type == 'assessments'): ?>
                            <div class="record-title">Assessment Record</div>
                            <div class="form-grid">
                                <div>
                                    <span class="data-label">Student Name</span>
                                    <div class="data-value"><?php echo htmlspecialchars($row['student_name']); ?></div>
                                </div>
                                <div>
                                    <span class="data-label">Assessment Date</span>
                                    <div class="data-value"><?php echo date('F j, Y', strtotime($row['assessment_date'])); ?></div>
                                </div>
                            </div>
                            <div class="form-row-full">
                                <span class="data-label">Assessment Type</span>
                                <div class="data-value"><?php echo htmlspecialchars($row['assessment_type']); ?></div>
                            </div>
                            <div class="form-row-full">
                                <span class="data-label">Result</span>
                                <div class="data-value"><?php echo nl2br(htmlspecialchars($row['result'])); ?></div>
                            </div>
                            <div class="form-row-full">
                                <span class="data-label">Interpretation</span>
                                <div class="data-value"><?php echo nl2br(htmlspecialchars($row['interpretation'])); ?></div>
                            </div>
                            <div class="form-row-full">
                                <span class="data-label">Recommendations</span>
                                <div class="data-value"><?php echo nl2br(htmlspecialchars($row['recommendations'])); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; padding: 40px; font-style: italic;">No records found for the selected criteria.</p>
            <?php endif; ?>
        </div>
        
        <div class="report-footer">
            <p>This report was generated electronically by the Guidance Office System on <?php echo date('F j, Y g:i A'); ?>.</p>
            <p>&copy; <?php echo date('Y'); ?> <?php echo SCHOOL_NAME; ?>. All Rights Reserved.</p>
        </div>
    </div>
    
    <script>
        function loadStudentRecords(studentId) {
            // When student changes, just submit the form to reload the list
            document.querySelector('.filters form').submit();
        }

        function checkAllRecords() {
            var checkboxes = document.querySelectorAll('.record-cb');
            checkboxes.forEach(function(box) {
                box.checked = true;
            });
        }
    </script>
</body>
</html>