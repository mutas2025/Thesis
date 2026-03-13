<?php
session_start();
require_once '../config.php';
requireRole('registrar');
// Handle AJAX request to store active tab
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['active_tab'])) {
    $_SESSION['active_tab'] = $_POST['active_tab'];
    echo json_encode(['success' => true]);
    exit();
}
// Helper functions
function getCurrentTab() {
    return isset($_SESSION['active_tab']) ? $_SESSION['active_tab'] : 'dashboard';
}
function redirectToTab($tab) {
    $_SESSION['active_tab'] = $tab;
    header("Location: registrar.php#$tab");
    exit();
}
function generateIdNumber($birthdate) {
    global $conn;
    $datePart = date('Ymd', strtotime($birthdate));
    $escapedDatePart = mysqli_real_escape_string($conn, $datePart);
    
    $query = "SELECT id_number FROM students WHERE id_number LIKE '$escapedDatePart%' ORDER BY id_number DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if (!$result) die("Query failed: " . mysqli_error($conn));
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $sequence = (int)substr($row['id_number'], -2) + 1;
    } else {
        $sequence = 1;
    }
    
    return $datePart . str_pad($sequence, 2, '0', STR_PAD_LEFT);
}
function calculateAge($birthdate) {
    return (new DateTime())->diff(new DateTime($birthdate))->y;
}
// Get data for dropdowns
 $courses = mysqli_query($conn, "SELECT *, CONCAT(coursename, ' - ', courselevel) as course_full_name FROM courses ORDER BY coursename, courselevel");
 $activeAcademicYears = mysqli_query($conn, "SELECT * FROM academic_years WHERE is_active = 1 ORDER BY academic_year DESC");
// Get students data
 $students = [];
 $query = "SELECT * FROM students";
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($conn, $_GET['search']);
    $query .= " WHERE last_name LIKE '%$searchTerm%' OR first_name LIKE '%$searchTerm%' OR id_number LIKE '%$searchTerm%'";
}
 $query .= " ORDER BY last_name, first_name";
 $result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}
// Dashboard statistics
 $studentCount = count($students);
 $maleCount = array_filter($students, fn($s) => $s['gender'] === 'Male');
 $femaleCount = $studentCount - count($maleCount);
// Get enrollment statistics
 $enrollmentStats = mysqli_query($conn, "
    SELECT c.coursename, c.courselevel, COUNT(e.id) as enrolled_count
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    WHERE e.status IN ('Enrolled', 'Registered')
    GROUP BY c.coursename, c.courselevel
    ORDER BY c.coursename, c.courselevel
");
 $genderStats = mysqli_query($conn, "SELECT gender, COUNT(*) as count FROM students GROUP BY gender");
 $ageStats = mysqli_query($conn, "
    SELECT CASE 
        WHEN age BETWEEN 15 AND 18 THEN '15-18'
        WHEN age BETWEEN 19 AND 22 THEN '19-22'
        WHEN age BETWEEN 23 AND 26 THEN '23-26'
        ELSE '27+'
    END as age_range, COUNT(*) as count
    FROM students
    GROUP BY age_range
    ORDER BY age_range
");
 $civilStatusStats = mysqli_query($conn, "SELECT civil_status, COUNT(*) as count FROM students GROUP BY civil_status");
 $enrollmentStatusStats = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM enrollments GROUP BY status");
// Calculate enrolled and registered counts
 $enrolledCount = $registeredCount = 0;
while ($stat = mysqli_fetch_assoc($enrollmentStatusStats)) {
    if ($stat['status'] == 'Enrolled') $enrolledCount = $stat['count'];
    elseif ($stat['status'] == 'Registered') $registeredCount = $stat['count'];
}
mysqli_data_seek($enrollmentStatusStats, 0); // Reset pointer for later use
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentTab = $_POST['current_tab'] ?? getCurrentTab();
    
    // Student operations
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add student logic
                $idNumber = generateIdNumber($_POST['birthDate']);
                $age = calculateAge($_POST['birthDate']);
                
                $query = "INSERT INTO students (
                    id_number, last_name, first_name, middle_name, gender, 
                    birth_date, age, birth_place, civil_status, nationality, religion, 
                    email, password, contact_number, home_address, lrn_no, contact_person
                ) VALUES (
                    '$idNumber', '" . mysqli_real_escape_string($conn, $_POST['lastName']) . "', 
                    '" . mysqli_real_escape_string($conn, $_POST['firstName']) . "', 
                    '" . mysqli_real_escape_string($conn, $_POST['middleName']) . "', 
                    '" . mysqli_real_escape_string($conn, $_POST['gender']) . "', 
                    '" . mysqli_real_escape_string($conn, $_POST['birthDate']) . "', 
                    $age, '" . mysqli_real_escape_string($conn, $_POST['birthPlace']) . "', 
                    '" . mysqli_real_escape_string($conn, $_POST['civilStatus']) . "', 
                    '" . mysqli_real_escape_string($conn, $_POST['nationality']) . "', 
                    '" . mysqli_real_escape_string($conn, $_POST['religion']) . "', 
                    '" . mysqli_real_escape_string($conn, $_POST['email']) . "', 
                    '" . password_hash($_POST['password'], PASSWORD_DEFAULT) . "', 
                    '" . mysqli_real_escape_string($conn, $_POST['contactNumber']) . "', 
                    '" . mysqli_real_escape_string($conn, $_POST['homeAddress']) . "', 
                    '" . mysqli_real_escape_string($conn, $_POST['lrnNo']) . "', 
                    '" . mysqli_real_escape_string($conn, $_POST['contactPerson']) . "'
                )";
                
                if (mysqli_query($conn, $query)) {
                    $studentId = mysqli_insert_id($conn);
                    // Add secondary details if provided
                    if (!empty($_POST['fatherName'])) {
                        $secondaryQuery = "UPDATE students SET 
                            father_name = '" . mysqli_real_escape_string($conn, $_POST['fatherName']) . "',
                            father_occupation = '" . mysqli_real_escape_string($conn, $_POST['fatherOccupation']) . "',
                            mother_name = '" . mysqli_real_escape_string($conn, $_POST['motherName']) . "',
                            mother_occupation = '" . mysqli_real_escape_string($conn, $_POST['motherOccupation']) . "',
                            guardian_name = '" . mysqli_real_escape_string($conn, $_POST['guardianName']) . "',
                            guardian_address = '" . mysqli_real_escape_string($conn, $_POST['guardianAddress']) . "',
                            other_support = '" . mysqli_real_escape_string($conn, $_POST['otherSupport']) . "',
                            is_boarding = " . (isset($_POST['boarding']) ? 1 : 0) . ",
                            with_family = " . (isset($_POST['withFamily']) ? 1 : 0) . ",
                            family_address = '" . mysqli_real_escape_string($conn, $_POST['familyAddress']) . "'
                            WHERE id = $studentId";
                        mysqli_query($conn, $secondaryQuery);
                    }
                    
                    // Add requirements if provided
                    $requirements = [
                        'form138', 'moral_cert', 'birth_cert', 'good_moral', 'form137', 
                        'parents_marriage_cert', 'baptism_cert', 'proof_income', 
                        'brown_envelope', 'white_folder', 'id_picture', 'esc_app_form', 
                        'esc_contract', 'esc_cert', 'shsvp_cert'
                    ];
                    
                    foreach ($requirements as $req) {
                        if (isset($_POST[$req])) {
                            $reqQuery = "UPDATE students SET $req = 1 WHERE id = $studentId";
                            mysqli_query($conn, $reqQuery);
                        }
                    }
                    
                    // Add text requirements if provided
                    $textRequirements = ['others1', 'others2', 'others3', 'notes'];
                    foreach ($textRequirements as $req) {
                        if (!empty($_POST[$req])) {
                            $reqQuery = "UPDATE students SET $req = '" . mysqli_real_escape_string($conn, $_POST[$req]) . "' WHERE id = $studentId";
                            mysqli_query($conn, $reqQuery);
                        }
                    }
                    
                    $_SESSION['message'] = "Student added successfully with ID: $idNumber";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Error: " . mysqli_error($conn);
                    $_SESSION['message_type'] = "error";
                }
                redirectToTab($currentTab);
                break;
                
            case 'edit':
                // Edit student logic
                $studentId = mysqli_real_escape_string($conn, $_POST['studentId']);
                $age = calculateAge($_POST['birthDate']);
                
                $query = "UPDATE students SET 
                    last_name = '" . mysqli_real_escape_string($conn, $_POST['lastName']) . "', 
                    first_name = '" . mysqli_real_escape_string($conn, $_POST['firstName']) . "', 
                    middle_name = '" . mysqli_real_escape_string($conn, $_POST['middleName']) . "', 
                    gender = '" . mysqli_real_escape_string($conn, $_POST['gender']) . "', 
                    birth_date = '" . mysqli_real_escape_string($conn, $_POST['birthDate']) . "', 
                    age = $age, 
                    birth_place = '" . mysqli_real_escape_string($conn, $_POST['birthPlace']) . "', 
                    civil_status = '" . mysqli_real_escape_string($conn, $_POST['civilStatus']) . "', 
                    nationality = '" . mysqli_real_escape_string($conn, $_POST['nationality']) . "', 
                    religion = '" . mysqli_real_escape_string($conn, $_POST['religion']) . "', 
                    email = '" . mysqli_real_escape_string($conn, $_POST['email']) . "', 
                    contact_number = '" . mysqli_real_escape_string($conn, $_POST['contactNumber']) . "', 
                    home_address = '" . mysqli_real_escape_string($conn, $_POST['homeAddress']) . "',
                    lrn_no = '" . mysqli_real_escape_string($conn, $_POST['lrnNo']) . "',
                    contact_person = '" . mysqli_real_escape_string($conn, $_POST['contactPerson']) . "'";
                
                // Add secondary details if provided
                $secondaryFields = [
                    'fatherName' => 'father_name',
                    'fatherOccupation' => 'father_occupation',
                    'motherName' => 'mother_name',
                    'motherOccupation' => 'mother_occupation',
                    'guardianName' => 'guardian_name',
                    'guardianAddress' => 'guardian_address',
                    'otherSupport' => 'other_support',
                    'familyAddress' => 'family_address'
                ];
                
                foreach ($secondaryFields as $formField => $dbField) {
                    if (!empty($_POST[$formField])) {
                        $query .= ", $dbField = '" . mysqli_real_escape_string($conn, $_POST[$formField]) . "'";
                    }
                }
                
                // Add boarding and family status
                $query .= ", is_boarding = " . (isset($_POST['boarding']) ? 1 : 0);
                $query .= ", with_family = " . (isset($_POST['withFamily']) ? 1 : 0);
                
                // Add requirements
                $requirements = [
                    'form138', 'moral_cert', 'birth_cert', 'good_moral', 'form137', 
                    'parents_marriage_cert', 'baptism_cert', 'proof_income', 
                    'brown_envelope', 'white_folder', 'id_picture', 'esc_app_form', 
                    'esc_contract', 'esc_cert', 'shsvp_cert'
                ];
                
                foreach ($requirements as $req) {
                    $query .= ", $req = " . (isset($_POST[$req]) ? 1 : 0);
                }
                
                // Add text requirements
                $textRequirements = ['others1', 'others2', 'others3', 'notes'];
                foreach ($textRequirements as $req) {
                    $query .= ", $req = '" . mysqli_real_escape_string($conn, $_POST[$req]) . "'";
                }
                
                $query .= " WHERE id = $studentId";
                
                if (mysqli_query($conn, $query)) {
                    $_SESSION['message'] = "Student updated successfully";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Error: " . mysqli_error($conn);
                    $_SESSION['message_type'] = "error";
                }
                redirectToTab($currentTab);
                break;
                
            case 'add_enrollment':
                // Add enrollment logic
                $studentId = mysqli_real_escape_string($conn, $_POST['studentId']);
                $courseId = mysqli_real_escape_string($conn, $_POST['courseId']);
                
                // Get course level
                $courseResult = mysqli_query($conn, "SELECT courselevel FROM courses WHERE id = $courseId");
                $courseRow = mysqli_fetch_assoc($courseResult);
                $yearLevel = $courseRow['courselevel'];
                
                // Check for existing enrollment
                $checkQuery = "SELECT id FROM enrollments 
                               WHERE student_id = $studentId 
                               AND academic_year = '" . mysqli_real_escape_string($conn, $_POST['academicYear']) . "' 
                               AND semester = '" . mysqli_real_escape_string($conn, $_POST['semester']) . "'
                               AND status IN ('Registered', 'Enrolled')";
                if (mysqli_num_rows(mysqli_query($conn, $checkQuery)) > 0) {
                    $_SESSION['error'] = "Student is already enrolled for this academic year and semester.";
                    $_SESSION['message_type'] = "error";
                } else {
                    $query = "INSERT INTO enrollments (student_id, course_id, academic_year, semester, enrollment_date, status, year_level) 
                              VALUES ('$studentId', '$courseId', '" . mysqli_real_escape_string($conn, $_POST['academicYear']) . "', 
                              '" . mysqli_real_escape_string($conn, $_POST['semester']) . "', 
                              '" . mysqli_real_escape_string($conn, $_POST['enrollmentDate']) . "', 
                              '" . mysqli_real_escape_string($conn, $_POST['status']) . "', '$yearLevel')";
                    
                    if (mysqli_query($conn, $query)) {
                        $_SESSION['message'] = "Enrollment added successfully";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['error'] = "Error: " . mysqli_error($conn);
                        $_SESSION['message_type'] = "error";
                    }
                }
                redirectToTab($currentTab);
                break;
                
            case 'edit_enrollment':
                // Edit enrollment status
                $enrollmentId = mysqli_real_escape_string($conn, $_POST['enrollmentId']);
                $query = "UPDATE enrollments SET status = '" . mysqli_real_escape_string($conn, $_POST['status']) . "' WHERE id = '$enrollmentId'";
                
                if (mysqli_query($conn, $query)) {
                    $_SESSION['message'] = "Enrollment status updated successfully";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Error: " . mysqli_error($conn);
                    $_SESSION['message_type'] = "error";
                }
                redirectToTab($currentTab);
                break;
                
            case 'assign_subjects':
                // Assign subjects logic
                $enrollmentId = mysqli_real_escape_string($conn, $_POST['enrollmentId']);
                $studentId = mysqli_real_escape_string($conn, $_POST['studentId']);
                
                // First, delete any existing subjects for this enrollment
                $deleteQuery = "DELETE FROM student_subjects WHERE enrollment_id = $enrollmentId";
                mysqli_query($conn, $deleteQuery);
                
                // Then, add the selected subjects
                if (isset($_POST['subjects']) && is_array($_POST['subjects'])) {
                    foreach ($_POST['subjects'] as $subjectId) {
                        $subjectId = mysqli_real_escape_string($conn, $subjectId);
                        $insertQuery = "INSERT INTO student_subjects (student_id, subject_id, enrollment_id) 
                                        VALUES ($studentId, $subjectId, $enrollmentId)";
                        mysqli_query($conn, $insertQuery);
                    }
                    
                    $_SESSION['message'] = "Subjects assigned successfully";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "No subjects selected";
                    $_SESSION['message_type'] = "error";
                }
                redirectToTab($currentTab);
                break;
                
            case 'add_drop_subject':
                // Add or drop subject logic
                $enrollmentId = mysqli_real_escape_string($conn, $_POST['enrollmentId']);
                $studentId = mysqli_real_escape_string($conn, $_POST['studentId']);
                $subjectId = mysqli_real_escape_string($conn, $_POST['subjectId']);
                $operation = mysqli_real_escape_string($conn, $_POST['operation']);
                
                if ($operation === 'add') {
                    // Add subject
                    $checkQuery = "SELECT id FROM student_subjects 
                                   WHERE student_id = $studentId 
                                   AND subject_id = $subjectId 
                                   AND enrollment_id = $enrollmentId";
                    
                    if (mysqli_num_rows(mysqli_query($conn, $checkQuery)) > 0) {
                        $_SESSION['error'] = "Subject is already assigned to this student";
                        $_SESSION['message_type'] = "error";
                    } else {
                        $insertQuery = "INSERT INTO student_subjects (student_id, subject_id, enrollment_id) 
                                        VALUES ($studentId, $subjectId, $enrollmentId)";
                        
                        if (mysqli_query($conn, $insertQuery)) {
                            $_SESSION['message'] = "Subject added successfully";
                            $_SESSION['message_type'] = "success";
                        } else {
                            $_SESSION['error'] = "Error: " . mysqli_error($conn);
                            $_SESSION['message_type'] = "error";
                        }
                    }
                } elseif ($operation === 'drop') {
                    // Drop subject
                    $deleteQuery = "DELETE FROM student_subjects 
                                   WHERE student_id = $studentId 
                                   AND subject_id = $subjectId 
                                   AND enrollment_id = $enrollmentId";
                    
                    if (mysqli_query($conn, $deleteQuery)) {
                        $_SESSION['message'] = "Subject dropped successfully";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['error'] = "Error: " . mysqli_error($conn);
                        $_SESSION['message_type'] = "error";
                    }
                }
                redirectToTab($currentTab);
                break;
                
            case 'save_grade':
                // Save grade logic
                header('Content-Type: application/json');
                
                $query = "INSERT INTO student_grades (student_id, subject_id, enrollment_id, quarter1_grade, quarter2_grade, quarter3_grade, quarter4_grade, average_grade, remarks)
                          VALUES (" . mysqli_real_escape_string($conn, $_POST['studentId']) . ", 
                          " . mysqli_real_escape_string($conn, $_POST['subjectId']) . ", 
                          " . mysqli_real_escape_string($conn, $_POST['enrollmentId']) . ", 
                          '" . mysqli_real_escape_string($conn, $_POST['quarter1Grade']) . "', 
                          '" . mysqli_real_escape_string($conn, $_POST['quarter2Grade']) . "', 
                          '" . mysqli_real_escape_string($conn, $_POST['quarter3Grade']) . "', 
                          '" . mysqli_real_escape_string($conn, $_POST['quarter4Grade']) . "', 
                          '" . mysqli_real_escape_string($conn, $_POST['averageGrade']) . "', 
                          '" . mysqli_real_escape_string($conn, $_POST['remarks']) . "')
                          ON DUPLICATE KEY UPDATE
                          quarter1_grade = VALUES(quarter1_grade),
                          quarter2_grade = VALUES(quarter2_grade),
                          quarter3_grade = VALUES(quarter3_grade),
                          quarter4_grade = VALUES(quarter4_grade),
                          average_grade = VALUES(average_grade),
                          remarks = VALUES(remarks)";
                
                if (mysqli_query($conn, $query)) {
                    $_SESSION['active_tab'] = 'grades';
                    echo json_encode(['success' => true, 'message' => 'Grade saved successfully']);
                } else {
                    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
                }
                exit();
                
            case 'save_all_grades':
                // Save all grades logic
                header('Content-Type: application/json');
                
                if (isset($_POST['grades']) && is_array($_POST['grades'])) {
                    $successCount = 0;
                    $errorCount = 0;
                    $errors = [];
                    
                    foreach ($_POST['grades'] as $grade) {
                        $studentId = mysqli_real_escape_string($conn, $grade['student_id']);
                        $subjectId = mysqli_real_escape_string($conn, $grade['subject_id']);
                        $enrollmentId = mysqli_real_escape_string($conn, $grade['enrollment_id']);
                        $quarter1Grade = mysqli_real_escape_string($conn, $grade['quarter1_grade']);
                        $quarter2Grade = mysqli_real_escape_string($conn, $grade['quarter2_grade']);
                        $quarter3Grade = mysqli_real_escape_string($conn, $grade['quarter3_grade']);
                        $quarter4Grade = mysqli_real_escape_string($conn, $grade['quarter4_grade']);
                        $averageGrade = mysqli_real_escape_string($conn, $grade['average_grade']);
                        $remarks = mysqli_real_escape_string($conn, $grade['remarks']);
                        
                        $query = "INSERT INTO student_grades (student_id, subject_id, enrollment_id, quarter1_grade, quarter2_grade, quarter3_grade, quarter4_grade, average_grade, remarks)
                                  VALUES ($studentId, $subjectId, $enrollmentId, '$quarter1Grade', '$quarter2Grade', '$quarter3Grade', '$quarter4Grade', '$averageGrade', '$remarks')
                                  ON DUPLICATE KEY UPDATE
                                  quarter1_grade = VALUES(quarter1_grade),
                                  quarter2_grade = VALUES(quarter2_grade),
                                  quarter3_grade = VALUES(quarter3_grade),
                                  quarter4_grade = VALUES(quarter4_grade),
                                  average_grade = VALUES(average_grade),
                                  remarks = VALUES(remarks)";
                        
                        if (mysqli_query($conn, $query)) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = mysqli_error($conn);
                        }
                    }
                    
                    $_SESSION['active_tab'] = 'grades';
                    
                    if ($errorCount === 0) {
                        echo json_encode(['success' => true, 'message' => "All $successCount grades saved successfully"]);
                    } else {
                        echo json_encode(['success' => false, 'message' => "$successCount grades saved, $errorCount grades failed", 'errors' => $errors]);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'No grades data provided']);
                }
                exit();
        }
    }
}
// Handle delete operations
if (isset($_GET['action'])) {
    $currentTab = $_GET['current_tab'] ?? getCurrentTab();
    
    switch ($_GET['action']) {
        case 'delete':
            $studentId = mysqli_real_escape_string($conn, $_GET['id']);
            
            // Check if student has enrollments
            $checkResult = mysqli_query($conn, "SELECT id FROM enrollments WHERE student_id = $studentId");
            if (mysqli_num_rows($checkResult) > 0) {
                $_SESSION['error'] = "Cannot delete student with existing enrollments";
                $_SESSION['message_type'] = "error";
            } else {
                $query = "DELETE FROM students WHERE id = $studentId";
                if (mysqli_query($conn, $query)) {
                    $_SESSION['message'] = "Student deleted successfully";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['error'] = "Error: " . mysqli_error($conn);
                    $_SESSION['message_type'] = "error";
                }
            }
            redirectToTab($currentTab);
            break;
            
        case 'delete_enrollment':
            $enrollmentId = mysqli_real_escape_string($conn, $_GET['id']);
            
            // Delete student subjects first
            mysqli_query($conn, "DELETE FROM student_subjects WHERE enrollment_id = $enrollmentId");
            
            // Delete enrollment
            $query = "DELETE FROM enrollments WHERE id = $enrollmentId";
            if (mysqli_query($conn, $query)) {
                $_SESSION['message'] = "Enrollment deleted successfully";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['error'] = "Error: " . mysqli_error($conn);
                $_SESSION['message_type'] = "error";
            }
            redirectToTab($currentTab);
            break;
    }
}
// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'load_grades':
            // Load grades logic
            $conditions = [];
            if (!empty($_POST['course'])) $conditions[] = "e.course_id = '" . mysqli_real_escape_string($conn, $_POST['course']) . "'";
            if (!empty($_POST['academic_year'])) $conditions[] = "e.academic_year = '" . mysqli_real_escape_string($conn, $_POST['academic_year']) . "'";
            if (!empty($_POST['year_level'])) $conditions[] = "e.year_level = '" . mysqli_real_escape_string($conn, $_POST['year_level']) . "'";
            if (!empty($_POST['semester'])) $conditions[] = "e.semester = '" . mysqli_real_escape_string($conn, $_POST['semester']) . "'";
            if (!empty($_POST['subject'])) $conditions[] = "sub.id = '" . mysqli_real_escape_string($conn, $_POST['subject']) . "'";
            
            $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $query = "SELECT ss.student_id, ss.subject_id, ss.enrollment_id, s.id_number,
                      CONCAT(s.last_name, ', ', s.first_name, ' ', s.middle_name) as student_name,
                      sub.subject_code, sub.subject_description, c.coursename, c.courselevel,
                      e.academic_year, e.semester, sg.quarter1_grade, sg.quarter2_grade,
                      sg.quarter3_grade, sg.quarter4_grade, sg.average_grade, sg.remarks
                      FROM student_subjects ss
                      JOIN students s ON ss.student_id = s.id
                      JOIN subjects sub ON ss.subject_id = sub.id
                      JOIN enrollments e ON ss.enrollment_id = e.id
                      JOIN courses c ON e.course_id = c.id
                      LEFT JOIN student_grades sg ON ss.student_id = sg.student_id 
                          AND ss.subject_id = sg.subject_id AND ss.enrollment_id = sg.enrollment_id
                      $whereClause
                      ORDER BY s.last_name, s.first_name, sub.subject_code";
            
            $result = mysqli_query($conn, $query);
            $grades = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $grades[] = $row;
            }
            
            header('Content-Type: application/json');
            echo json_encode($grades);
            exit();
            
        case 'load_subjects':
            // Load subjects logic
            $conditions = [];
            if (!empty($_POST['course'])) $conditions[] = "e.course_id = '" . mysqli_real_escape_string($conn, $_POST['course']) . "'";
            if (!empty($_POST['academic_year'])) $conditions[] = "e.academic_year = '" . mysqli_real_escape_string($conn, $_POST['academic_year']) . "'";
            if (!empty($_POST['semester'])) $conditions[] = "e.semester = '" . mysqli_real_escape_string($conn, $_POST['semester']) . "'";
            
            $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $query = "SELECT DISTINCT sub.id, sub.subject_code, sub.subject_description
                      FROM subjects sub
                      JOIN student_subjects ss ON sub.id = ss.subject_id
                      JOIN enrollments e ON ss.enrollment_id = e.id
                      $whereClause
                      ORDER BY sub.subject_code";
            
            $result = mysqli_query($conn, $query);
            $subjects = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $subjects[] = $row;
            }
            
            header('Content-Type: application/json');
            echo json_encode($subjects);
            exit();
            
        case 'load_enrollments':
            // Load enrollments logic
            $conditions = [];
            if (!empty($_POST['course'])) $conditions[] = "e.course_id = '" . mysqli_real_escape_string($conn, $_POST['course']) . "'";
            if (!empty($_POST['academic_year'])) $conditions[] = "e.academic_year = '" . mysqli_real_escape_string($conn, $_POST['academic_year']) . "'";
            if (!empty($_POST['semester'])) $conditions[] = "e.semester = '" . mysqli_real_escape_string($conn, $_POST['semester']) . "'";
            if (!empty($_POST['status'])) $conditions[] = "e.status = '" . mysqli_real_escape_string($conn, $_POST['status']) . "'";
            if (!empty($_POST['search'])) {
                $search = mysqli_real_escape_string($conn, $_POST['search']);
                $conditions[] = "(s.last_name LIKE '%$search%' OR s.first_name LIKE '%$search%' OR s.middle_name LIKE '%$search%' OR s.id_number LIKE '%$search%' OR c.coursename LIKE '%$search%')";
            }
            
            $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
            
            $query = "SELECT e.id, s.id as student_id, s.last_name, s.first_name, s.middle_name, s.id_number,
                      c.id as course_id, c.coursename, c.courselevel, e.academic_year, e.semester, 
                      e.enrollment_date, e.status, e.year_level
                      FROM enrollments e
                      JOIN students s ON e.student_id = s.id
                      JOIN courses c ON e.course_id = c.id
                      $whereClause
                      ORDER BY e.enrollment_date DESC LIMIT 10";
            
            $result = mysqli_query($conn, $query);
            $enrollments = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $row['student_name'] = $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'];
                $row['course_full_name'] = $row['coursename'] . ' - ' . $row['courselevel'];
                $enrollments[] = $row;
            }
            
            header('Content-Type: application/json');
            echo json_encode($enrollments);
            exit();
            
        case 'load_enrollment_per_year':
            // Load enrollment per year data
            $query = "SELECT academic_year, COUNT(*) as enrollment_count 
                      FROM enrollments 
                      GROUP BY academic_year 
                      ORDER BY academic_year ASC";
            
            $result = mysqli_query($conn, $query);
            
            $labels = [];
            $values = [];
            
            while ($row = mysqli_fetch_assoc($result)) {
                $labels[] = $row['academic_year'];
                $values[] = (int)$row['enrollment_count'];
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'labels' => $labels,
                'values' => $values
            ]);
            exit();
    }
}

 // Get current user info for display
 $current_user_id = $_SESSION['user_id'] ?? 0;
 $current_user = $conn->query("SELECT username, fullname, role FROM users WHERE id = $current_user_id")->fetch_assoc();
 $display_name = $current_user['fullname'] ?? $current_user['username'] ?? 'Admin User';

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
   <style>
        .sidebar-menu li.active {
            background-color: rgba(255,255,255,0.1);
        }
        .sidebar-menu li.active > a {
            color: #fff;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        .nav-tabs {
            margin-bottom: 15px;
        }
        .search-box {
            margin-bottom: 15px;
        }
        .subject-list {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }
        .subject-item {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 5px;
        }
        .subject-item:hover {
            background-color: #f5f5f5;
        }
        .education-card {
            margin-bottom: 20px;
        }
        .unit-counter {
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-weight: bold;
        }
        .print-btn {
            margin-left: 5px;
        }
        .card-header {
            background-color: #004085;
            color: white;
        }
        .card-header h3 {
            margin: 0;
        }
        .card-header .btn {
            color: #004085;
            background-color: white;
            border: none;
        }
        .card-header .btn:hover {
            background-color: #f8f9fa;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .badge {
            font-size: 0.85em;
        }
        .small-box {
            border-radius: 0.25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            margin-bottom: 1rem;
            position: relative;
            display: block;
            margin-bottom: 20px;
        }
        .small-box > .inner {
            padding: 10px;
        }
        .small-box > .small-box-footer {
            background: rgba(0,0,0,0.1);
            color: rgba(255,255,255,0.8);
            display: block;
            padding: 3px 0;
            position: relative;
            text-align: center;
            text-decoration: none;
            z-index: 10;
        }
        .small-box > .small-box-footer:hover {
            background: rgba(0,0,0,0.15);
            color: #fff;
        }
        .small-box > .icon {
            color: rgba(0,0,0,0.15);
            z-index: 0;
        }
        .small-box:hover {
            text-decoration: none;
            color: #f8f9fa;
        }
        .small-box:hover .icon {
            font-size: 5rem;
        }
        .small-box .icon {
            transition: all 0.3s linear;
        }
        .small-box p {
            font-size: 1rem;
            margin: 0 0 5px 0;
        }
        .small-box h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 10px 0;
            white-space: nowrap;
            padding: 0;
        }
        @media (max-width: 767.98px) {
            .small-box h3 {
                font-size: 1.5rem;
            }
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .readonly-field {
            background-color: #f8f9fa;
        }
        .filter-container {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .filter-container .form-group {
            margin-bottom: 10px;
        }
        .subject-actions {
            margin-top: 5px;
        }
        .subject-actions button {
            margin-right: 5px;
            padding: 2px 8px;
            font-size: 0.8rem;
        }
        .brand-link {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .brand-link img {
            max-height: 35px;
            margin-right: 10px;
        }
        .dashboard-student-card {
            height: 100%;
        }
        .dashboard-student-card .card-body {
            max-height: 400px;
            overflow-y: auto;
        }
        .info-box {
            border-radius: 0.25rem;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            display: block;
            min-height: 90px;
            margin-bottom: 1rem;
            background: #fff;
            width: 100%;
            box-shadow: 0 1px 1px rgba(0,0,0,0.1);
            border-radius: 0.25rem;
        }
        .info-box .info-box-icon {
            border-radius: 0.25rem;
            display: block;
            float: left;
            height: 90px;
            width: 90px;
            text-align: center;
            font-size: 45px;
            line-height: 90px;
            background: rgba(0,0,0,0.2);
        }
        .info-box .info-box-content {
            padding: 5px 10px;
            margin-left: 90px;
        }
        .info-box .info-box-number {
            display: block;
            font-weight: 700;
            font-size: 18px;
        }
        .info-box .info-box-text {
            display: block;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .info-box .progress-description {
            display: block;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .info-box .progress {
            background: rgba(0,0,0,0.125);
            margin: 5px 0;
        }
        .info-box .progress-bar {
            background: #fff;
        }
        .info-box-icon {
            color: #fff;
        }
        .bg-info, .bg-info>a {
            background-color: #004085 !important;
        }
        .bg-success, .bg-success>a {
            background-color: #004085 !important;
        }
        .bg-warning, .bg-warning>a {
            background-color: #ffc107 !important;
        }
        .bg-danger, .bg-danger>a {
            background-color: #dc3545 !important;
        }
        .stats-card {
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .stats-card .card-header {
            font-weight: bold;
        }
        .stats-table {
            margin-bottom: 0;
        }
        .stats-table th {
            background-color: #f8f9fa;
        }
        .stats-table td {
            padding: 8px;
        }
        .stats-number {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            padding: 15px 0;
        }
        .stats-label {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }
        .chart-container {
            height: 300px;
            margin-bottom: 20px;
        }
        .print-buttons {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .dashboard-print-buttons {
            margin: 20px 0;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .dashboard-print-buttons .btn {
            min-width: 200px;
        }
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .toast {
            background-color: #fff;
            border-radius: 0.25rem;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
            margin-bottom: 0.75rem;
            opacity: 0;
            transition: opacity 0.15s linear;
        }
        .toast.show {
            opacity: 1;
        }
        .toast-header {
            display: flex;
            align-items: center;
            padding: 0.75rem 0.75rem;
            color: #6c757d;
            background-color: rgba(255, 255, 255, 0.85);
            background-clip: padding-box;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            border-top-left-radius: calc(0.25rem - 1px);
            border-top-right-radius: calc(0.25rem - 1px);
        }
        .toast-body {
            padding: 0.75rem;
        }
        .toast-success .toast-header {
            color: #fff;
            background-color: #004085;
        }
        .toast-error .toast-header {
            color: #fff;
            background-color: #dc3545;
        }
        .toast-info .toast-header {
            color: #fff;
            background-color: #004085;
        }
        .toast-warning .toast-header {
            color: #fff;
            background-color: #ffc107;
        }
        .no-data-message {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #6c757d;
        }
        .enrollment-status-badge {
            font-size: 0.85em;
        }
        .quarter-grade-container {
            display: flex;
            gap: 10px;
        }
        .quarter-grade {
            flex: 1;
        }
        .enrollment-card {
            margin-bottom: 20px;
            border-left: 4px solid #004085;
        }
        .enrollment-card.card {
            border: 1px solid rgba(0,0,0,.125);
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
        }
        .enrollment-card .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
            color: #495057;
        }
        .enrollment-card .card-body {
            padding: 1.25rem;
        }
        .enrollment-card .card-footer {
            background-color: #f8f9fa;
            border-top: 1px solid rgba(0,0,0,.125);
            padding: 0.75rem 1.25rem;
        }
        .enrollment-card .badge {
            font-size: 0.75em;
        }
        .enrollment-card .btn-group {
            display: flex;
            gap: 5px;
        }
        .enrollment-card .btn-group .btn {
            flex: 1;
        }
        .enrollment-card .enrollment-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .enrollment-card .enrollment-meta-item {
            display: flex;
            align-items: center;
        }
        .enrollment-card .enrollment-meta-item i {
            margin-right: 5px;
            color: #6c757d;
        }
        .enrollment-card .enrollment-actions {
            display: flex;
            justify-content: flex-end;
            gap: 5px;
        }
        .enrollment-card .enrollment-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .enrollment-card .enrollment-status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        .enrollment-card .enrollment-status-registered {
            color: #fff;
            background-color: #004085;
        }
        .enrollment-card .enrollment-status-enrolled {
            color: #fff;
            background-color: #004085;
        }
        .enrollment-card .enrollment-status-dropped {
            color: #fff;
            background-color: #dc3545;
        }
        .enrollment-card .enrollment-status-completed {
            color: #fff;
            background-color: #004085;
        }
        .enrollment-card .enrollment-status-pending {
            color: #212529;
            background-color: #ffc107;
        }
        .enrollment-card .enrollment-info {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        .enrollment-card .enrollment-info-item {
            flex: 1;
            min-width: 120px;
        }
        .enrollment-card .enrollment-info-label {
            font-size: 0.75rem;
            color: #6c757d;
            margin-bottom: 2px;
        }
        .enrollment-card .enrollment-info-value {
            font-weight: 500;
        }
        .enrollment-card .enrollment-student {
            font-weight: 600;
            margin-bottom: 5px;
        }
        .enrollment-card .enrollment-course {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        .enrollment-card .enrollment-details {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        .enrollment-card .enrollment-detail {
            flex: 1;
            min-width: 100px;
        }
        .enrollment-card .enrollment-detail-label {
            font-size: 0.75rem;
            color: #6c757d;
        }
        .enrollment-card .enrollment-detail-value {
            font-weight: 500;
        }
        .enrollment-card .enrollment-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .enrollment-card .enrollment-date {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .enrollment-card .enrollment-actions-footer {
            display: flex;
            gap: 5px;
        }
        .enrollment-card .enrollment-actions-footer .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        .enrollment-filters {
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            padding: 15px;
            margin-bottom: 20px;
        }
        .enrollment-filters .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -5px;
            margin-left: -5px;
        }
        .enrollment-filters .form-row > div {
            padding-right: 5px;
            padding-left: 5px;
            margin-bottom: 10px;
        }
        .enrollment-filters .form-label {
            font-weight: 500;
            margin-bottom: 5px;
        }
        .enrollment-filters .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .enrollment-filters .btn-group .btn {
            flex: 1;
        }
        .enrollment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .enrollment-grid .enrollment-card {
            height: 100%;
        }
        .enrollment-loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 200px;
        }
        .enrollment-loading .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        .enrollment-empty {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 200px;
            color: #6c757d;
        }
        .enrollment-empty i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .enrollment-empty h4 {
            margin-bottom: 10px;
        }
        .enrollment-empty p {
            margin-bottom: 20px;
        }
        .enrollment-empty .btn {
            min-width: 150px;
        }
        .enrollment-search-container {
            margin-bottom: 15px;
        }
        .enrollment-search-container .input-group {
            max-width: 500px;
        }
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .requirements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
        }
        /* Red and Blue Theme Customizations */
        .main-sidebar {
            background-color: #004085 !important;
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item > .nav-link.active {
            background-color: rgba(220,53,69,0.2);
            color: #fff;
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item > .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }
        .brand-link {
            background-color: #002752 !important;
            border-bottom: 1px solid #004085;
        }
        .btn-primary {
            background-color: #004085;
            border-color: #004085;
        }
        .btn-primary:hover {
            background-color: #003366;
            border-color: #002244;
        }
        .btn-success {
            background-color: #004085;
            border-color: #004085;
        }
        .btn-success:hover {
            background-color: #003366;
            border-color: #002244;
        }
        .nav-tabs .nav-link.active {
            color: #004085;
            border-color: #004085 #004085 #fff;
        }
        .nav-tabs .nav-link:hover {
            border-color: #e9ecef #e9ecef #004085;
            color: #004085;
        }
        .pagination .page-item.active .page-link {
            background-color: #004085;
            border-color: #004085;
        }
        .page-link {
            color: #004085;
        }
        .page-link:hover {
            color: #003366;
        }
        .dropdown-item.active, .dropdown-item:active {
            background-color: #004085;
        }
        .logout-btn {
            margin-top: auto;
            padding: 10px 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .logout-btn .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 10px 15px;
        }
        .logout-btn .nav-link:hover {
            color: #fff;
            background-color: rgba(220,53,69,0.2);
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <!-- Removed logout button from navbar -->
    </nav>
    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-success elevation-4">
        <a href="#" class="brand-link">
            <img src="../uploads/csr.png" alt="CSR Logo">
            <span class="brand-text font-weight-light">Registrar</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="../uploads/registrar.jpg" class="img-circle elevation-2" alt="User Image">
                </div>
                   <div class="info">
                    <a href="#" class="d-block"><?= htmlspecialchars($display_name) ?></a>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="#dashboard" class="nav-link <?= getCurrentTab() == 'dashboard' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#students" class="nav-link <?= getCurrentTab() == 'students' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Students</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#enrollments" class="nav-link <?= getCurrentTab() == 'enrollments' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-user-graduate"></i>
                            <p>Enrollments</p>
                        </a>
                    </li>
                     <li class="nav-item">
                        <a href="#grades" class="nav-link <?= getCurrentTab() == 'grades' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-id-card"></i>
                            <p>Grades</p>
                        </a>
                    </li>
                      <li class="nav-item">
                        <a href="#reports" class="nav-link <?= getCurrentTab() == 'reports' ? 'active' : '' ?>" data-toggle="tab">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            <p>Reports</p>
                        </a>
                    </li>
                    <!-- Logout button moved to sidebar -->
                    <li class="nav-item logout-btn">
                        <a href="logout.php" class="nav-link">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <h1 class="m-0">Registrar Dashboard</h1>
            </div>
        </div>
        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <!-- Toast Container -->
                <div class="toast-container"></div>
                
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade <?= getCurrentTab() == 'dashboard' ? 'show active' : '' ?>" id="dashboard">
                        <div class="row">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3><?= $enrolledCount ?></h3>
                                        <p>Enrolled Students</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-primary">
                                    <div class="inner">
                                        <h3><?= $registeredCount ?></h3>
                                        <p>Registered Students</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3><?= count($students) - ($enrolledCount + $registeredCount) ?></h3>
                                        <p>Inactive Students</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user-times"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3><?= date('Y') ?></h3>
                                        <p>Current Year</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <!-- Active Students Card -->
                            <div class="col-md-6">
                                <div class="card dashboard-student-card">
                                    <div class="card-header bg-success">
                                        <h5 class="card-title">Active Students</h5>
                                        <button class="btn btn-primary" data-toggle="modal" data-target="#addStudentModal">
                                            <i class="fas fa-plus"></i> New Student
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="search-box">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="activeSearch" placeholder="Search by Last Name">
                                                <div class="input-group-append">
                                                    <button class="btn btn-default" type="button">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <table class="table table-bordered table-striped" id="activeStudentsTable">
                                            <thead>
                                                <tr>
                                                    <th>ID Number</th>
                                                    <th>Name</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $activeStudents = [];
                                                $inactiveStudents = [];
                                                
                                                foreach ($students as $student) {
                                                    $statusQuery = "SELECT status FROM enrollments 
                                                                   WHERE student_id = {$student['id']} 
                                                                   AND status IN ('Registered', 'Enrolled')
                                                                   ORDER BY enrollment_date DESC
                                                                   LIMIT 1";
                                                    $statusResult = mysqli_query($conn, $statusQuery);
                                                    $currentStatus = '';
                                                    
                                                    if (mysqli_num_rows($statusResult) > 0) {
                                                        $statusRow = mysqli_fetch_assoc($statusResult);
                                                        $currentStatus = $statusRow['status'];
                                                        $activeStudents[] = $student;
                                                    } else {
                                                        $inactiveStudents[] = $student;
                                                    }
                                                }
                                                
                                                foreach ($activeStudents as $student): 
                                                    $statusQuery = "SELECT status FROM enrollments 
                                                                   WHERE student_id = {$student['id']} 
                                                                   AND status IN ('Registered', 'Enrolled')
                                                                   ORDER BY enrollment_date DESC
                                                                   LIMIT 1";
                                                    $statusResult = mysqli_query($conn, $statusQuery);
                                                    $currentStatus = '';
                                                    $statusClass = '';
                                                    
                                                    if (mysqli_num_rows($statusResult) > 0) {
                                                        $statusRow = mysqli_fetch_assoc($statusResult);
                                                        $currentStatus = $statusRow['status'];
                                                        
                                                        switch($currentStatus) {
                                                            case 'Registered': $statusClass = 'bg-primary'; break;
                                                            case 'Enrolled': $statusClass = 'bg-success'; break;
                                                            case 'Dropped': $statusClass = 'bg-danger'; break;
                                                            case 'Completed': $statusClass = 'bg-info'; break;
                                                        }
                                                    }
                                                ?>
                                                <tr>
                                                    <td><?= $student['id_number'] ?></td>
                                                    <td><?= $student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name'] ?></td>
                                                    <td>
                                                        <?php if (!empty($currentStatus)): ?>
                                                            <span class="badge enrollment-status-badge <?= $statusClass ?>"><?= $currentStatus ?></span>
                                                        <?php else: ?>
                                                            <span class="badge enrollment-status-badge bg-secondary">No Active Enrollment</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <?php if (empty($currentStatus) || $currentStatus == 'Dropped' || $currentStatus == 'Completed'): ?>
                                                                <button type="button" class="btn btn-success btn-sm enroll-student" data-id="<?= $student['id'] ?>" data-toggle="modal" data-target="#addEnrollmentModal">
                                                                    <i class="fas fa-user-plus"></i> Enroll
                                                                </button>
                                                            <?php else: ?>
                                                                <button type="button" class="btn btn-warning btn-sm" disabled title="Student already has an active enrollment">
                                                                    <i class="fas fa-user-plus"></i> Enroll
                                                                </button>
                                                            <?php endif; ?>
                                                            <button type="button" class="btn btn-info btn-sm print-student-form" data-id="<?= $student['id'] ?>">
                                                                <i class="fas fa-file-alt"></i> Print Form
                                                            </button>
                                                            <button type="button" class="btn btn-primary btn-sm view-enrollment-history" data-id="<?= $student['id'] ?>" data-toggle="modal" data-target="#enrollmentHistoryModal">
                                                                <i class="fas fa-history"></i> History
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Inactive Students Card -->
                            <div class="col-md-6">
                                <div class="card dashboard-student-card">
                                    <div class="card-header bg-warning">
                                        <h5 class="card-title">Inactive Students</h5>
                                        <div class="search-box">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="inactiveSearch" placeholder="Search by Last Name">
                                                <div class="input-group-append">
                                                    <button class="btn btn-default" type="button">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered table-striped" id="inactiveStudentsTable">
                                            <thead>
                                                <tr>
                                                    <th>ID Number</th>
                                                    <th>Name</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($inactiveStudents as $student): ?>
                                                <tr>
                                                    <td><?= $student['id_number'] ?></td>
                                                    <td><?= $student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name'] ?></td>
                                                    <td>
                                                        <span class="badge enrollment-status-badge bg-secondary">No Enrollment</span>
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button type="button" class="btn btn-success btn-sm enroll-student" data-id="<?= $student['id'] ?>" data-toggle="modal" data-target="#addEnrollmentModal">
                                                                <i class="fas fa-user-plus"></i> Enroll
                                                            </button>
                                                            <button type="button" class="btn btn-info btn-sm print-student-form" data-id="<?= $student['id'] ?>">
                                                                <i class="fas fa-file-alt"></i> Print Form
                                                            </button>
                                                            <button type="button" class="btn btn-primary btn-sm view-enrollment-history" data-id="<?= $student['id'] ?>" data-toggle="modal" data-target="#enrollmentHistoryModal">
                                                                <i class="fas fa-history"></i> History
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Students Tab -->
                    <div class="tab-pane fade <?= getCurrentTab() == 'students' ? 'show active' : '' ?>" id="students">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Student List</h3>
                                
                            </div>
                            <div class="card-body">
                                <div class="search-box mb-3">
                                    <form method="GET" action="registrar.php" class="form-inline">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="search" placeholder="Search by name or ID number" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                            <div class="input-group-append">
                                                <button class="btn btn-default" type="submit">
                                                    <i class="fas fa-search"></i> Search
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <table id="studentsTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID Number</th>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Age</th>
                                            <th>Contact Number</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?= $student['id_number'] ?></td>
                                            <td><?= $student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name'] ?></td>
                                            <td><?= $student['gender'] ?></td>
                                            <td><?= $student['age'] ?></td>
                                            <td><?= $student['contact_number'] ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" class="btn btn-info btn-sm print-student-form" data-id="<?= $student['id'] ?>">
                                                        <i class="fas fa-file-alt"></i> Print Form
                                                    </button>
                                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editStudentModal" data-id="<?= $student['id'] ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteStudentModal" data-id="<?= $student['id'] ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Enrollments Tab -->
                    <div class="tab-pane fade <?= getCurrentTab() == 'enrollments' ? 'show active' : '' ?>" id="enrollments">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Enrollments</h3>

                            </div>
                            <div class="card-body">
                                <!-- Enrollment Search Bar -->
                                <div class="enrollment-search-container">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="enrollmentSearch" placeholder="Search by student name, ID, or course...">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="button" id="enrollmentSearchBtn">
                                                <i class="fas fa-search"></i> Search
                                            </button>
                                            <button class="btn btn-default" type="button" id="enrollmentSearchClear">
                                                <i class="fas fa-times"></i> Clear
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Enrollment Filters -->
                                <div class="enrollment-filters">
                                    <div class="form-row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="enrollmentFilterCourse" class="form-label">Course</label>
                                                <select class="form-control" id="enrollmentFilterCourse">
                                                    <option value="">All Courses</option>
                                                    <?php 
                                                    mysqli_data_seek($courses, 0);
                                                    while ($course = mysqli_fetch_assoc($courses)): ?>
                                                    <option value="<?= $course['id'] ?>"><?= $course['course_full_name'] ?></option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="enrollmentFilterAcademicYear" class="form-label">Academic Year</label>
                                                <select class="form-control" id="enrollmentFilterAcademicYear">
                                                    <option value="">All Years</option>
                                                    <?php 
                                                    $yearQuery = "SELECT DISTINCT academic_year FROM academic_years ORDER BY academic_year DESC";
                                                    $yearResult = mysqli_query($conn, $yearQuery);
                                                    while ($yearRow = mysqli_fetch_assoc($yearResult)) {
                                                        echo '<option value="' . $yearRow['academic_year'] . '">' . $yearRow['academic_year'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="enrollmentFilterSemester" class="form-label">Semester</label>
                                                <select class="form-control" id="enrollmentFilterSemester">
                                                    <option value="">All Semesters</option>
                                                    <option value="1st">1st Semester</option>
                                                    <option value="2nd">2nd Semester</option>
                                                    <option value="Summer">Summer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="enrollmentFilterStatus" class="form-label">Status</label>
                                                <select class="form-control" id="enrollmentFilterStatus">
                                                    <option value="">All Statuses</option>
                                                    <option value="Registered">Registered</option>
                                                    <option value="Enrolled">Enrolled</option>
                                                    <option value="Dropped">Dropped</option>
                                                    <option value="Completed">Completed</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label class="form-label">&nbsp;</label>
                                                <div class="btn-group">
                                                    <button type="button" id="applyEnrollmentFilters" class="btn btn-primary">
                                                        <i class="fas fa-filter"></i> Apply
                                                    </button>
                                                    <button type="button" id="resetEnrollmentFilters" class="btn btn-default">
                                                        <i class="fas fa-redo"></i> Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Enrollment Cards Container -->
                                <div id="enrollmentCardsContainer" class="enrollment-grid">
                                    <!-- Loading spinner -->
                                    <div id="enrollmentLoading" class="enrollment-loading">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Empty state (hidden by default) -->
                                    <div id="enrollmentEmpty" class="enrollment-empty" style="display: none;">
                                        <i class="fas fa-user-graduate"></i>
                                        <h4>No Enrollments Found</h4>
                                        <p>Try adjusting your filters or add a new enrollment.</p>
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addEnrollmentModal">
                                            <i class="fas fa-plus"></i> Add Enrollment
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Pagination -->
                                <div class="pagination-container">
                                    <nav aria-label="Enrollment pagination">
                                        <ul class="pagination" id="enrollmentPagination">
                                            <!-- Pagination will be dynamically added here -->
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Grades Tab -->
                    <div class="tab-pane fade <?= getCurrentTab() == 'grades' ? 'show active' : '' ?>" id="grades">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Student Grades Management</h3>
                            </div>
                            <div class="card-body">
                                <!-- Grade Filters -->
                                <div class="filter-container mb-3">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="gradeCourse">Course</label>
                                                <select class="form-control" id="gradeCourse">
                                                    <option value="">All Courses</option>
                                                    <?php 
                                                    mysqli_data_seek($courses, 0);
                                                    while ($course = mysqli_fetch_assoc($courses)): ?>
                                                    <option value="<?= $course['id'] ?>"><?= $course['course_full_name'] ?></option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="gradeAcademicYear">Academic Year</label>
                                                <select class="form-control" id="gradeAcademicYear">
                                                    <option value="">All Years</option>
                                                    <?php 
                                                    $yearQuery = "SELECT DISTINCT academic_year FROM academic_years ORDER BY academic_year DESC";
                                                    $yearResult = mysqli_query($conn, $yearQuery);
                                                    while ($yearRow = mysqli_fetch_assoc($yearResult)) {
                                                        echo '<option value="' . $yearRow['academic_year'] . '">' . $yearRow['academic_year'] . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="gradeSemester">Semester</label>
                                                <select class="form-control" id="gradeSemester">
                                                    <option value="">All Semesters</option>
                                                    <option value="1st">1st Semester</option>
                                                    <option value="2nd">2nd Semester</option>
                                                    <option value="Summer">Summer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="gradeSubject">Subject</label>
                                                <select class="form-control" id="gradeSubject">
                                                    <option value="">All Subjects</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div>
                                                    <button type="button" id="loadGradesBtn" class="btn btn-primary">
                                                        <i class="fas fa-filter"></i> Apply Filters
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button" id="resetGradesBtn" class="btn btn-default">
                                                <i class="fas fa-redo"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Grades Table -->
                                <div class="table-responsive">
                                    <table id="gradesTable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Student Name</th>
                                                <th>Subject</th>
                                                <th>Course</th>
                                                <th>Academic Year</th>
                                                <th>Semester</th>
                                                <th>Quarter 1 | Prelim</th>
                                                <th>Quarter 2 | Midterm</th>
                                                <th>Quarter 3 | Final</th>
                                                <th>Quarter 4 | NA</th>
                                                <th>Average</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="12" class="no-data-message">Please apply filters to view student grades</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="button" id="saveAllGradesBtn" class="btn btn-success" disabled>
                                        <i class="fas fa-save"></i> Save All Grades
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reports Tab -->
                    <div class="tab-pane fade <?= getCurrentTab() == 'reports' ? 'show active' : '' ?>" id="reports">
                        <!-- Print Buttons -->
                        <div class="print-buttons">
                            <a href="printenrollmentmasterlist.php" target="_blank" class="btn btn-primary">
                                <i class="fas fa-print"></i> Print Masterlist
                            </a>
                            <a href="printgradesheet.php" target="_blank" class="btn btn-primary">
                                <i class="fas fa-print"></i> Print Gradesheet
                            </a>
                            <a href="printcoursebreakdown.php" target="_blank" class="btn btn-primary">
                                <i class="fas fa-print"></i> Print Course Enrollment Breakdown
                            </a>
                            
                            <a href="printstudentspersubject.php" target="_blank" class="btn btn-primary">
                                <i class="fas fa-print"></i> Print Students per Subject
                            </a>
                        </div>
                        
                        <div class="row">
                            <!-- Enrollment Statistics -->
                            <div class="col-md-6">
                                <div class="card stats-card">
                                    <div class="card-header bg-primary">
                                        <h5 class="card-title">Enrollment Statistics</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered stats-table">
                                                <thead>
                                                    <tr>
                                                        <th>Course</th>
                                                        <th>Year Level</th>
                                                        <th>Enrolled Students</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    mysqli_data_seek($enrollmentStats, 0);
                                                    while ($stat = mysqli_fetch_assoc($enrollmentStats)): ?>
                                                    <tr>
                                                        <td><?= $stat['coursename'] ?></td>
                                                        <td><?= $stat['courselevel'] ?></td>
                                                        <td><?= $stat['enrolled_count'] ?></td>
                                                    </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Enrollment per School Year Chart -->
                            <div class="col-md-6">
                                <div class="card stats-card">
                                    <div class="card-header bg-info">
                                        <h5 class="card-title">Enrollment per School Year</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-container">
                                            <canvas id="enrollmentChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Student Modal -->
    <div class="modal fade" id="viewStudentModal" tabindex="-1" role="dialog" aria-labelledby="viewStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="viewStudentModalLabel">Student Information</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ID Number</label>
                                <p id="viewIdNumber" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>LRN No</label>
                                <p id="viewLrnNo" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <p id="viewLastName" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>First Name</label>
                                <p id="viewFirstName" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>Middle Name</label>
                                <p id="viewMiddleName" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>Gender</label>
                                <p id="viewGender" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>Birth Date</label>
                                <p id="viewBirthDate" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>Age</label>
                                <p id="viewAge" class="form-control-static"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Birth Place</label>
                                <p id="viewBirthPlace" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>Civil Status</label>
                                <p id="viewCivilStatus" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>Nationality</label>
                                <p id="viewNationality" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>Religion</label>
                                <p id="viewReligion" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <p id="viewEmail" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>Contact Number</label>
                                <p id="viewContactNumber" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>Contact Person</label>
                                <p id="viewContactPerson" class="form-control-static"></p>
                            </div>
                            <div class="form-group">
                                <label>Home Address</label>
                                <p id="viewHomeAddress" class="form-control-static"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enrollment History Modal -->
    <div class="modal fade" id="enrollmentHistoryModal" tabindex="-1" role="dialog" aria-labelledby="enrollmentHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="enrollmentHistoryModalLabel">Enrollment History</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Student Name</label>
                        <input type="text" class="form-control readonly-field" id="historyStudentName" readonly>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Year Level</th>
                                    <th>Academic Year</th>
                                    <th>Semester</th>
                                    <th>Enrollment Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="enrollmentHistoryBody">
                                <!-- Enrollment history will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog" aria-labelledby="addStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="registrar.php">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="current_tab" id="addStudentCurrentTab" value="<?= getCurrentTab() ?>">
                    <div class="modal-body">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs" id="enrollmentTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="primary-tab" data-toggle="tab" href="#primary" role="tab" aria-controls="primary" aria-selected="true">Primary Details</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="secondary-tab" data-toggle="tab" href="#secondary" role="tab" aria-controls="secondary" aria-selected="false">Secondary Details</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="education-tab" data-toggle="tab" href="#education" role="tab" aria-controls="education" aria-selected="false">Education</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="requirements-tab" data-toggle="tab" href="#requirements" role="tab" aria-controls="requirements" aria-selected="false">Requirements</a>
                            </li>
                        </ul>
                        
                        <!-- Tab Content -->
                        <div class="tab-content" id="enrollmentTabContent">
                            <!-- Primary Tab -->
                            <div class="tab-pane fade show active" id="primary" role="tabpanel" aria-labelledby="primary-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="lastName" class="required-field">Last Name</label>
                                            <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter Last Name" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="firstName" class="required-field">First Name</label>
                                            <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter First Name" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="middleName">Middle Name</label>
                                            <input type="text" class="form-control" id="middleName" name="middleName" placeholder="Enter Middle Name">
                                        </div>
                                        <div class="form-group">
                                            <label class="required-field">Gender</label>
                                            <div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="genderMale" name="gender" class="custom-control-input" value="Male" required>
                                                    <label class="custom-control-label" for="genderMale">Male</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="genderFemale" name="gender" class="custom-control-input" value="Female" required>
                                                    <label class="custom-control-label" for="genderFemale">Female</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="birthDate" class="required-field">Birth Date</label>
                                            <input type="date" class="form-control" id="birthDate" name="birthDate" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="age">Age</label>
                                            <input type="number" class="form-control" id="age" name="age" placeholder="Auto-calculated" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="birthPlace" class="required-field">Birth Place</label>
                                            <input type="text" class="form-control" id="birthPlace" name="birthPlace" placeholder="Enter Birth Place" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="civilStatus" class="required-field">Civil Status</label>
                                            <select class="form-control" id="civilStatus" name="civilStatus" required>
                                                <option value="">Select Civil Status</option>
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                                <option value="Widowed">Widowed</option>
                                                <option value="Separated">Separated</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="nationality" class="required-field">Nationality</label>
                                            <input type="text" class="form-control" id="nationality" name="nationality" placeholder="Enter Nationality" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="religion" class="required-field">Religion</label>
                                            <input type="text" class="form-control" id="religion" name="religion" placeholder="Enter Religion" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email">
                                        </div>
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password">
                                        </div>
                                        <div class="form-group">
                                            <label for="contactNumber" >Contact Number</label>
                                            <input type="text" class="form-control" id="contactNumber" name="contactNumber" placeholder="Enter Contact Number">
                                        </div>
                                        <div class="form-group">
                                            <label for="homeAddress" class="required-field">Home Address</label>
                                            <textarea class="form-control" id="homeAddress" name="homeAddress" rows="3" placeholder="Enter Home Address" required></textarea>
                                        </div>
                                        <!-- New fields -->
                                        <div class="form-group">
                                            <label for="lrnNo">LRN No</label>
                                            <input type="text" class="form-control" id="lrnNo" name="lrnNo" placeholder="Enter LRN Number">
                                        </div>
                                        <div class="form-group">
                                            <label for="contactPerson">Contact Person</label>
                                            <input type="text" class="form-control" id="contactPerson" name="contactPerson" placeholder="Enter Contact Person">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Secondary Tab -->
                            <div class="tab-pane fade" id="secondary" role="tabpanel" aria-labelledby="secondary-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fatherName">Father</label>
                                            <input type="text" class="form-control" id="fatherName" name="fatherName" placeholder="Enter Father's Name">
                                        </div>
                                        <div class="form-group">
                                            <label for="fatherOccupation">Occupation</label>
                                            <input type="text" class="form-control" id="fatherOccupation" name="fatherOccupation" placeholder="Enter Father's Occupation">
                                        </div>
                                        <div class="form-group">
                                            <label for="motherName">Mother</label>
                                            <input type="text" class="form-control" id="motherName" name="motherName" placeholder="Enter Mother's Name">
                                        </div>
                                        <div class="form-group">
                                            <label for="motherOccupation">Occupation</label>
                                            <input type="text" class="form-control" id="motherOccupation" name="motherOccupation" placeholder="Enter Mother's Occupation">
                                        </div>
                                        <div class="form-group">
                                            <label for="guardianName">Guardian</label>
                                            <input type="text" class="form-control" id="guardianName" name="guardianName" placeholder="Enter Guardian's Name">
                                        </div>
                                        <div class="form-group">
                                            <label for="guardianAddress">Address</label>
                                            <input type="text" class="form-control" id="guardianAddress" name="guardianAddress" placeholder="Enter Guardian's Address">
                                        </div>
                                        <div class="form-group">
                                            <label for="otherSupport">Other Person Supporting</label>
                                            <input type="text" class="form-control" id="otherSupport" name="otherSupport" placeholder="Enter Other Supporting Person">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Are you Boarding?</label>
                                            <div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="boardingYes" name="boarding" class="custom-control-input" value="1">
                                                    <label class="custom-control-label" for="boardingYes">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="boardingNo" name="boarding" class="custom-control-input" value="0" checked>
                                                    <label class="custom-control-label" for="boardingNo">No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>With Family?</label>
                                            <div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="withFamilyYes" name="withFamily" class="custom-control-input" value="1" checked>
                                                    <label class="custom-control-label" for="withFamilyYes">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="withFamilyNo" name="withFamily" class="custom-control-input" value="0">
                                                    <label class="custom-control-label" for="withFamilyNo">No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="familyAddress">Address</label>
                                            <textarea class="form-control" id="familyAddress" name="familyAddress" rows="3" placeholder="Enter Family Address"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Education Tab -->
                            <div class="tab-pane fade" id="education" role="tabpanel" aria-labelledby="education-tab">
                                <div class="card education-card">
                                    <div class="card-header bg-info">
                                        <h5 class="card-title">Elementary</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="elemAddress">School Name</label>
                                                    <input type="text" class="form-control" id="elemAddress" name="elemAddress" placeholder="Enter Elementary School Name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="elemYear">Academic Year</label>
                                                    <input type="text" class="form-control" id="elemYear" name="elemYear" placeholder="Enter Academic Year">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card education-card">
                                    <div class="card-header bg-info">
                                        <h5 class="card-title">Secondary</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="secAddress">School Name</label>
                                                    <input type="text" class="form-control" id="secAddress" name="secAddress" placeholder="Enter Secondary School Name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="secYear">Academic Year</label>
                                                    <input type="text" class="form-control" id="secYear" name="secYear" placeholder="Enter Academic Year">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card education-card">
                                    <div class="card-header bg-info">
                                        <h5 class="card-title">College</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="collegeAddress">School Name</label>
                                                    <input type="text" class="form-control" id="collegeAddress" name="collegeAddress" placeholder="Enter College Name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="collegeYear">Academic Year</label>
                                                    <input type="text" class="form-control" id="collegeYear" name="collegeYear" placeholder="Enter Academic Year">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card education-card">
                                    <div class="card-header bg-info">
                                        <h5 class="card-title">Vocational</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="vocAddress">School Name/Vocational</label>
                                                    <input type="text" class="form-control" id="vocAddress" name="vocAddress" placeholder="Enter Vocational School Name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="vocYear">Academic Year</label>
                                                    <input type="text" class="form-control" id="vocYear" name="vocYear" placeholder="Enter Academic Year">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card education-card">
                                    <div class="card-header bg-info">
                                        <h5 class="card-title">Others</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="othersAddress">School Name</label>
                                                    <input type="text" class="form-control" id="othersAddress" name="othersAddress" placeholder="Enter Other School Name">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="othersYear">Academic Year</label>
                                                    <input type="text" class="form-control" id="othersYear" name="othersYear" placeholder="Enter Academic Year">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Requirements Tab -->
                            <div class="tab-pane fade" id="requirements" role="tabpanel" aria-labelledby="requirements-tab">
                                <div class="requirements-grid">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="form138" name="form138" value="1">
                                            <label class="custom-control-label" for="form138">Form 138/SF9</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="moral_cert" name="moral_cert" value="1">
                                            <label class="custom-control-label" for="moral_cert">Moral Certificate</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="birth_cert" name="birth_cert" value="1">
                                            <label class="custom-control-label" for="birth_cert">Birth Certificate</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="good_moral" name="good_moral" value="1">
                                            <label class="custom-control-label" for="good_moral">Good Moral Certificate</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="form137" name="form137" value="1">
                                            <label class="custom-control-label" for="form137">Form 137/SF10</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="parents_marriage_cert" name="parents_marriage_cert" value="1">
                                            <label class="custom-control-label" for="parents_marriage_cert">Parents Marriage Certificate</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="baptism_cert" name="baptism_cert" value="1">
                                            <label class="custom-control-label" for="baptism_cert">Baptism Certificate</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="proof_income" name="proof_income" value="1">
                                            <label class="custom-control-label" for="proof_income">Proof of Income</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="brown_envelope" name="brown_envelope" value="1">
                                            <label class="custom-control-label" for="brown_envelope">1 pc Brown Long Envelope</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="white_folder" name="white_folder" value="1">
                                            <label class="custom-control-label" for="white_folder">2 pcs white Long Folder</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="id_picture" name="id_picture" value="1">
                                            <label class="custom-control-label" for="id_picture">2 pcs 2X2 ID Picture white background-with collar</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="esc_app_form" name="esc_app_form" value="1">
                                            <label class="custom-control-label" for="esc_app_form">ESC Application Form</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="esc_contract" name="esc_contract" value="1">
                                            <label class="custom-control-label" for="esc_contract">ESC Contract</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="esc_cert" name="esc_cert" value="1">
                                            <label class="custom-control-label" for="esc_cert">ESC Certificate</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="shsvp_cert" name="shsvp_cert" value="1">
                                            <label class="custom-control-label" for="shsvp_cert">SHSVP Certificate-if applicable</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="others1">Others 1</label>
                                    <input type="text" class="form-control" id="others1" name="others1" placeholder="Enter other requirement">
                                </div>
                                <div class="form-group">
                                    <label for="others2">Others 2</label>
                                    <input type="text" class="form-control" id="others2" name="others2" placeholder="Enter other requirement">
                                </div>
                                <div class="form-group">
                                    <label for="others3">Others 3</label>
                                    <input type="text" class="form-control" id="others3" name="others3" placeholder="Enter other requirement">
                                </div>
                                <div class="form-group">
                                    <label for="notes">Notes:</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Enter additional notes"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" role="dialog" aria-labelledby="editStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="registrar.php">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="studentId" id="editStudentId">
                    <input type="hidden" name="current_tab" id="editStudentCurrentTab" value="<?= getCurrentTab() ?>">
                    <div class="modal-body">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs" id="editEnrollmentTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="edit-primary-tab" data-toggle="tab" href="#edit-primary" role="tab" aria-controls="edit-primary" aria-selected="true">Primary Details</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="edit-secondary-tab" data-toggle="tab" href="#edit-secondary" role="tab" aria-controls="edit-secondary" aria-selected="false">Secondary Details</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="edit-education-tab" data-toggle="tab" href="#edit-education" role="tab" aria-controls="edit-education" aria-selected="false">Education</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="edit-requirements-tab" data-toggle="tab" href="#edit-requirements" role="tab" aria-controls="edit-requirements" aria-selected="false">Requirements</a>
                            </li>
                        </ul>
                        
                        <!-- Tab Content -->
                        <div class="tab-content" id="editEnrollmentTabContent">
                            <!-- Primary Tab -->
                            <div class="tab-pane fade show active" id="edit-primary" role="tabpanel" aria-labelledby="edit-primary-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="editIdNumber">ID Number</label>
                                            <input type="text" class="form-control" id="editIdNumber" name="idNumber" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="editLrnNo">LRN No</label>
                                            <input type="text" class="form-control" id="editLrnNo" name="lrnNo">
                                        </div>
                                        <div class="form-group">
                                            <label for="editLastName" class="required-field">Last Name</label>
                                            <input type="text" class="form-control" id="editLastName" name="lastName" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="editFirstName" class="required-field">First Name</label>
                                            <input type="text" class="form-control" id="editFirstName" name="firstName" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="editMiddleName">Middle Name</label>
                                            <input type="text" class="form-control" id="editMiddleName" name="middleName">
                                        </div>
                                        <div class="form-group">
                                            <label class="required-field">Gender</label>
                                            <div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="editGenderMale" name="gender" class="custom-control-input" value="Male" required>
                                                    <label class="custom-control-label" for="editGenderMale">Male</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="editGenderFemale" name="gender" class="custom-control-input" value="Female" required>
                                                    <label class="custom-control-label" for="editGenderFemale">Female</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="editBirthDate" class="required-field">Birth Date</label>
                                            <input type="date" class="form-control" id="editBirthDate" name="birthDate" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="editAge">Age</label>
                                            <input type="number" class="form-control" id="editAge" name="age" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="editBirthPlace" class="required-field">Birth Place</label>
                                            <input type="text" class="form-control" id="editBirthPlace" name="birthPlace" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="editCivilStatus" class="required-field">Civil Status</label>
                                            <select class="form-control" id="editCivilStatus" name="civilStatus" required>
                                                <option value="">Select Civil Status</option>
                                                <option value="Single">Single</option>
                                                <option value="Married">Married</option>
                                                <option value="Widowed">Widowed</option>
                                                <option value="Separated">Separated</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="editNationality" class="required-field">Nationality</label>
                                            <input type="text" class="form-control" id="editNationality" name="nationality" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="editReligion" class="required-field">Religion</label>
                                            <input type="text" class="form-control" id="editReligion" name="religion" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="editEmail">Email</label>
                                            <input type="email" class="form-control" id="editEmail" name="email">
                                        </div>
                                        <div class="form-group">
                                            <label for="editContactNumber">Contact Number</label>
                                            <input type="text" class="form-control" id="editContactNumber" name="contactNumber">
                                        </div>
                                        <div class="form-group">
                                            <label for="editHomeAddress" class="required-field">Home Address</label>
                                            <textarea class="form-control" id="editHomeAddress" name="homeAddress" rows="3" required></textarea>
                                        </div>
                                        <!-- New fields -->
                                        <div class="form-group">
                                            <label for="editContactPerson">Contact Person</label>
                                            <input type="text" class="form-control" id="editContactPerson" name="contactPerson">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Secondary Tab -->
                            <div class="tab-pane fade" id="edit-secondary" role="tabpanel" aria-labelledby="edit-secondary-tab">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="editFatherName">Father</label>
                                            <input type="text" class="form-control" id="editFatherName" name="fatherName">
                                        </div>
                                        <div class="form-group">
                                            <label for="editFatherOccupation">Occupation</label>
                                            <input type="text" class="form-control" id="editFatherOccupation" name="fatherOccupation">
                                        </div>
                                        <div class="form-group">
                                            <label for="editMotherName">Mother</label>
                                            <input type="text" class="form-control" id="editMotherName" name="motherName">
                                        </div>
                                        <div class="form-group">
                                            <label for="editMotherOccupation">Occupation</label>
                                            <input type="text" class="form-control" id="editMotherOccupation" name="motherOccupation">
                                        </div>
                                        <div class="form-group">
                                            <label for="editGuardianName">Guardian</label>
                                            <input type="text" class="form-control" id="editGuardianName" name="guardianName">
                                        </div>
                                        <div class="form-group">
                                            <label for="editGuardianAddress">Address</label>
                                            <input type="text" class="form-control" id="editGuardianAddress" name="guardianAddress">
                                        </div>
                                        <div class="form-group">
                                            <label for="editOtherSupport">Other Person Supporting</label>
                                            <input type="text" class="form-control" id="editOtherSupport" name="otherSupport">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Are you Boarding?</label>
                                            <div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="editBoardingYes" name="boarding" class="custom-control-input" value="1">
                                                    <label class="custom-control-label" for="editBoardingYes">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="editBoardingNo" name="boarding" class="custom-control-input" value="0">
                                                    <label class="custom-control-label" for="editBoardingNo">No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>With Family?</label>
                                            <div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="editWithFamilyYes" name="withFamily" class="custom-control-input" value="1">
                                                    <label class="custom-control-label" for="editWithFamilyYes">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="editWithFamilyNo" name="withFamily" class="custom-control-input" value="0">
                                                    <label class="custom-control-label" for="editWithFamilyNo">No</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="editFamilyAddress">Address</label>
                                            <textarea class="form-control" id="editFamilyAddress" name="familyAddress" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Education Tab -->
                            <div class="tab-pane fade" id="edit-education" role="tabpanel" aria-labelledby="edit-education-tab">
                                <div class="card education-card">
                                    <div class="card-header bg-info">
                                        <h5 class="card-title">Elementary</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="editElemAddress">School Name</label>
                                                    <input type="text" class="form-control" id="editElemAddress" name="elemAddress">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="editElemYear">Academic Year</label>
                                                    <input type="text" class="form-control" id="editElemYear" name="elemYear">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card education-card">
                                    <div class="card-header bg-info">
                                        <h5 class="card-title">Secondary</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="editSecAddress">School Name</label>
                                                    <input type="text" class="form-control" id="editSecAddress" name="secAddress">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="editSecYear">Academic Year</label>
                                                    <input type="text" class="form-control" id="editSecYear" name="secYear">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card education-card">
                                    <div class="card-header bg-info">
                                        <h5 class="card-title">College</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="editCollegeAddress">School Name</label>
                                                    <input type="text" class="form-control" id="editCollegeAddress" name="collegeAddress">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="editCollegeYear">Academic Year</label>
                                                    <input type="text" class="form-control" id="editCollegeYear" name="collegeYear">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card education-card">
                                    <div class="card-header bg-info">
                                        <h5 class="card-title">Vocational</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="editVocAddress">School Name</label>
                                                    <input type="text" class="form-control" id="editVocAddress" name="vocAddress">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="editVocYear">Academic Year</label>
                                                    <input type="text" class="form-control" id="editVocYear" name="vocYear">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card education-card">
                                    <div class="card-header bg-info">
                                        <h5 class="card-title">Others</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="editOthersAddress">Other School Name</label>
                                                    <input type="text" class="form-control" id="editOthersAddress" name="othersAddress">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="editOthersYear">Academic Year</label>
                                                    <input type="text" class="form-control" id="editOthersYear" name="othersYear">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Requirements Tab -->
                            <div class="tab-pane fade" id="edit-requirements" role="tabpanel" aria-labelledby="edit-requirements-tab">
                                <div class="requirements-grid">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editForm138" name="form138" value="1">
                                            <label class="custom-control-label" for="editForm138">Form 138/SF9</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editMoralCert" name="moral_cert" value="1">
                                            <label class="custom-control-label" for="editMoralCert">Moral Certificate</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editBirthCert" name="birth_cert" value="1">
                                            <label class="custom-control-label" for="editBirthCert">Birth Certificate</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editGoodMoral" name="good_moral" value="1">
                                            <label class="custom-control-label" for="editGoodMoral">Good Moral Certificate</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editForm137" name="form137" value="1">
                                            <label class="custom-control-label" for="editForm137">Form 137/SF10</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editParentsMarriageCert" name="parents_marriage_cert" value="1">
                                            <label class="custom-control-label" for="editParentsMarriageCert">Parents Marriage Certificate</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editBaptismCert" name="baptism_cert" value="1">
                                            <label class="custom-control-label" for="editBaptismCert">Baptism Certificate</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editProofIncome" name="proof_income" value="1">
                                            <label class="custom-control-label" for="editProofIncome">Proof of Income</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editBrownEnvelope" name="brown_envelope" value="1">
                                            <label class="custom-control-label" for="editBrownEnvelope">1 pc Brown Long Envelope</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editWhiteFolder" name="white_folder" value="1">
                                            <label class="custom-control-label" for="editWhiteFolder">2 pcs white Long Folder</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editIdPicture" name="id_picture" value="1">
                                            <label class="custom-control-label" for="editIdPicture">2 pcs 2X2 ID Picture white background-with collar</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editEscAppForm" name="esc_app_form" value="1">
                                            <label class="custom-control-label" for="editEscAppForm">ESC Application Form</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editEscContract" name="esc_contract" value="1">
                                            <label class="custom-control-label" for="editEscContract">ESC Contract</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editEscCert" name="esc_cert" value="1">
                                            <label class="custom-control-label" for="editEscCert">ESC Certificate</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="editShsvpCert" name="shsvp_cert" value="1">
                                            <label class="custom-control-label" for="editShsvpCert">SHSVP Certificate-if applicable</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="editOthers1">Others 1</label>
                                    <input type="text" class="form-control" id="editOthers1" name="others1">
                                </div>
                                <div class="form-group">
                                    <label for="editOthers2">Others 2</label>
                                    <input type="text" class="form-control" id="editOthers2" name="others2">
                                </div>
                                <div class="form-group">
                                    <label for="editOthers3">Others 3</label>
                                    <input type="text" class="form-control" id="editOthers3" name="others3">
                                </div>
                                <div class="form-group">
                                    <label for="editNotes">Notes:</label>
                                    <textarea class="form-control" id="editNotes" name="notes" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Update Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Student Modal -->
    <div class="modal fade" id="deleteStudentModal" tabindex="-1" role="dialog" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title" id="deleteStudentModalLabel">Delete Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this student? This action cannot be undone.</p>
                    <input type="hidden" id="deleteStudentId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Enrollment Modal -->
    <div class="modal fade" id="addEnrollmentModal" tabindex="-1" role="dialog" aria-labelledby="addEnrollmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="addEnrollmentModalLabel">Add New Enrollment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="registrar.php">
                    <input type="hidden" name="action" value="add_enrollment">
                    <input type="hidden" name="current_tab" id="addEnrollmentCurrentTab" value="<?= getCurrentTab() ?>">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="studentId" class="required-field">Student</label>
                            <select class="form-control" id="studentId" name="studentId" required>
                                <option value="">Select Student</option>
                                <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>"><?= $student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="courseId" class="required-field">Course</label>
                            <select class="form-control" id="courseId" name="courseId" required>
                                <option value="">Select Course</option>
                                <?php 
                                mysqli_data_seek($courses, 0);
                                while ($course = mysqli_fetch_assoc($courses)): ?>
                                <option value="<?= $course['id'] ?>"><?= $course['course_full_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="academicYear" class="required-field">Academic Year</label>
                            <select class="form-control" id="academicYear" name="academicYear" required>
                                <option value="">Select Academic Year</option>
                                <?php 
                                mysqli_data_seek($activeAcademicYears, 0);
                                while ($academicYear = mysqli_fetch_assoc($activeAcademicYears)): ?>
                                <option value="<?= $academicYear['academic_year'] ?>"><?= $academicYear['academic_year'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="semester" class="required-field">Semester</label>
                            <select class="form-control" id="semester" name="semester" required>
                                <option value="">Select Semester</option>
                                <option value="1st">1st Semester</option>
                                <option value="2nd">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="enrollmentDate" class="required-field">Enrollment Date</label>
                            <input type="date" class="form-control" id="enrollmentDate" name="enrollmentDate" required>
                        </div>
                        <div class="form-group">
                            <label for="status" class="required-field">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="Registered">Registered</option>
                                <option value="Enrolled">Enrolled</option>
                                <option value="Dropped">Dropped</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Enrollment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Enrollment Modal - ONLY STATUS CAN BE EDITED -->
    <div class="modal fade" id="editEnrollmentModal" tabindex="-1" role="dialog" aria-labelledby="editEnrollmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="editEnrollmentModalLabel">Edit Enrollment Status</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="registrar.php">
                    <input type="hidden" name="action" value="edit_enrollment">
                    <input type="hidden" name="enrollmentId" id="editEnrollmentId">
                    <input type="hidden" name="current_tab" id="editEnrollmentCurrentTab" value="<?= getCurrentTab() ?>">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Student</label>
                            <input type="text" class="form-control readonly-field" id="editStudentDisplay" readonly>
                        </div>
                        <div class="form-group">
                            <label>Course</label>
                            <input type="text" class="form-control readonly-field" id="editCourseDisplay" readonly>
                        </div>
                        <div class="form-group">
                            <label>Year Level</label>
                            <input type="text" class="form-control readonly-field" id="editYearLevelDisplay" readonly>
                        </div>
                        <div class="form-group">
                            <label>Academic Year</label>
                            <input type="text" class="form-control readonly-field" id="editAcademicYearDisplay" readonly>
                        </div>
                        <div class="form-group">
                            <label>Semester</label>
                            <input type="text" class="form-control readonly-field" id="editSemesterDisplay" readonly>
                        </div>
                        <div class="form-group">
                            <label>Enrollment Date</label>
                            <input type="text" class="form-control readonly-field" id="editEnrollmentDateDisplay" readonly>
                        </div>
                        <div class="form-group">
                            <label for="editStatus" class="required-field">Status</label>
                            <select class="form-control" id="editStatus" name="status" required>
                                <option value="Registered">Registered</option>
                                <option value="Enrolled">Enrolled</option>
                                <option value="Dropped">Dropped</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Enrollment Modal -->
    <div class="modal fade" id="deleteEnrollmentModal" tabindex="-1" role="dialog" aria-labelledby="deleteEnrollmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title" id="deleteEnrollmentModalLabel">Delete Enrollment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this enrollment? This action cannot be undone.</p>
                    <input type="hidden" id="deleteEnrollmentId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteEnrollment" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Assign Subjects Modal -->
    <div class="modal fade" id="assignSubjectsModal" tabindex="-1" role="dialog" aria-labelledby="assignSubjectsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="assignSubjectsModalLabel">Assign Subjects</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="registrar.php">
                    <input type="hidden" name="action" value="assign_subjects">
                    <input type="hidden" name="enrollmentId" id="assignEnrollmentId">
                    <input type="hidden" name="studentId" id="assignStudentId">
                    <input type="hidden" name="current_tab" id="assignSubjectsCurrentTab" value="<?= getCurrentTab() ?>">
                    <div class="modal-body">
                        <div class="filter-container">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="subjectCourse">Course</label>
                                        <select class="form-control" id="subjectCourse" readonly>
                                            <option value="">Select Course</option>
                                            <?php 
                                            mysqli_data_seek($courses, 0);
                                            while ($course = mysqli_fetch_assoc($courses)): ?>
                                            <option value="<?= $course['id'] ?>"><?= $course['course_full_name'] ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="subjectAcademicYear">Academic Year</label>
                                        <select class="form-control" id="subjectAcademicYear">
                                            <option value="">All Years</option>
                                            <?php 
                                            $yearQuery = "SELECT DISTINCT academic_year FROM subjects ORDER BY academic_year DESC";
                                            $yearResult = mysqli_query($conn, $yearQuery);
                                            while ($yearRow = mysqli_fetch_assoc($yearResult)) {
                                                echo '<option value="' . $yearRow['academic_year'] . '">' . $yearRow['academic_year'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="subjectSemester">Semester</label>
                                        <select class="form-control" id="subjectSemester">
                                            <option value="">All Semesters</option>
                                            <option value="1st">1st Semester</option>
                                            <option value="2nd">2nd Semester</option>
                                            <option value="Summer">Summer</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <h5>Available Subjects</h5>
                            <div id="subjectsList" class="subject-list">
                                <!-- Subjects will be loaded here via AJAX -->
                            </div>
                            <div id="unitCounter" class="unit-counter">
                                Total Units: <span id="totalUnits">0</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Subjects</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add/Drop Subject Modal -->
    <div class="modal fade" id="addDropSubjectModal" tabindex="-1" role="dialog" aria-labelledby="addDropSubjectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="addDropSubjectModalLabel">Add/Drop Subject</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="registrar.php">
                    <input type="hidden" name="action" value="add_drop_subject">
                    <input type="hidden" name="enrollmentId" id="addDropEnrollmentId">
                    <input type="hidden" name="studentId" id="addDropStudentId">
                    <input type="hidden" name="subjectId" id="addDropSubjectId">
                    <input type="hidden" name="operation" id="addDropOperation">
                    <input type="hidden" name="current_tab" id="addDropSubjectCurrentTab" value="<?= getCurrentTab() ?>">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" class="form-control readonly-field" id="addDropSubjectDisplay" readonly>
                        </div>
                        <div class="form-group">
                            <label>Operation</label>
                            <input type="text" class="form-control readonly-field" id="addDropOperationDisplay" readonly>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <span id="addDropMessage"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Grade Entry Modal -->
    <div class="modal fade" id="gradeEntryModal" tabindex="-1" role="dialog" aria-labelledby="gradeEntryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="gradeEntryModalLabel">Student Grade Entry</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="gradeEntryForm">
                    <div class="modal-body">
                        <input type="hidden" id="gradeStudentId">
                        <input type="hidden" id="gradeSubjectId">
                        <input type="hidden" id="gradeEnrollmentId">
                        
                        <div class="form-group">
                            <label>Student</label>
                            <input type="text" class="form-control readonly-field" id="gradeStudentDisplay" readonly>
                        </div>
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" class="form-control readonly-field" id="gradeSubjectDisplay" readonly>
                        </div>
                        <div class="form-group">
                            <label>Course</label>
                            <input type="text" class="form-control readonly-field" id="gradeCourseDisplay" readonly>
                        </div>
                        <div class="form-group">
                            <label>Academic Year</label>
                            <input type="text" class="form-control readonly-field" id="gradeAcademicYearDisplay" readonly>
                        </div>
                        <div class="form-group">
                            <label>Semester</label>
                            <input type="text" class="form-control readonly-field" id="gradeSemesterDisplay" readonly>
                        </div>
                        <div class="quarter-grade-container">
                            <div class="quarter-grade">
                                <div class="form-group">
                                    <label for="quarter1Grade">Quarter 1 | Prelim</label>
                                    <input type="number" class="form-control" id="quarter1Grade" min="0" max="100" step="0.01">
                                </div>
                            </div>
                            <div class="quarter-grade">
                                <div class="form-group">
                                    <label for="quarter2Grade">Quarter 2 | Midterm</label>
                                    <input type="number" class="form-control" id="quarter2Grade" min="0" max="100" step="0.01">
                                </div>
                            </div>
                            <div class="quarter-grade">
                                <div class="form-group">
                                    <label for="quarter3Grade">Quarter 3 | Final</label>
                                    <input type="number" class="form-control" id="quarter3Grade" min="0" max="100" step="0.01">
                                </div>
                            </div>
                            <div class="quarter-grade">
                                <div class="form-group">
                                    <label for="quarter4Grade">Quarter 4 | NA</label>
                                    <input type="number" class="form-control" id="quarter4Grade" min="0" max="100" step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="averageGrade">Average Grade</label>
                            <input type="number" class="form-control" id="averageGrade" min="0" max="100" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="gradeRemarks">Remarks</label>
                            <textarea class="form-control" id="gradeRemarks" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Grade</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Required Scripts -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
 $(function() {
    // Show toast notification
    function showToast(message, type = 'success') {
        const toastId = 'toast-' + Date.now();
        const toastClass = type === 'success' ? 'toast-success' : 'toast-error';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const toastHtml = `
            <div id="${toastId}" class="toast ${toastClass}" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
                <div class="toast-header">
                    <i class="fas ${icon} mr-2"></i>
                    <strong class="mr-auto">Registrar</strong>
                    <small>Just now</small>
                    <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        $('.toast-container').append(toastHtml);
        
        const toastElement = $(`#${toastId}`);
        toastElement.toast('show');
        
        toastElement.on('hidden.bs.toast', function () {
            $(this).remove();
        });
    }
    
    // Check for session messages and show toasts
    <?php if(isset($_SESSION['message'])): ?>
        showToast('<?=$_SESSION['message']?>', '<?=$_SESSION['message_type']?>');
        <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        showToast('<?=$_SESSION['error']?>', 'error');
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    // Initialize DataTables when the tab is shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if (target === '#students') {
            if (!$.fn.DataTable.isDataTable('#studentsTable')) {
                $('#studentsTable').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "pageLength": 10
                });
            }
        }
    });
    
    // If the students tab is active on page load, initialize its table
    if ($('#students').hasClass('show active')) {
        $('#studentsTable').DataTable({
            "responsive": true,
            "autoWidth": false,
            "pageLength": 10
        });
    }
    
    // Auto-calculate age when birthdate is entered
    $('#birthDate, #editBirthDate').on('change', function() {
        const birthDate = new Date($(this).val());
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        if ($(this).attr('id') === 'birthDate') {
            $('#age').val(age);
        } else {
            $('#editAge').val(age);
        }
    });
    
    // View student modal data population
    $('#viewStudentModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const studentId = button.data('id');
        
        // Get student data via AJAX
        $.ajax({
            url: 'get_student.php',
            type: 'GET',
            data: { id: studentId },
            dataType: 'json',
            success: function(data) {
                // Populate form fields
                $('#viewIdNumber').text(data.id_number);
                $('#viewLrnNo').text(data.lrn_no);
                $('#viewLastName').text(data.last_name);
                $('#viewFirstName').text(data.first_name);
                $('#viewMiddleName').text(data.middle_name);
                $('#viewGender').text(data.gender);
                $('#viewBirthDate').text(data.birth_date);
                $('#viewAge').text(data.age);
                $('#viewBirthPlace').text(data.birth_place);
                $('#viewCivilStatus').text(data.civil_status);
                $('#viewNationality').text(data.nationality);
                $('#viewReligion').text(data.religion);
                $('#viewEmail').text(data.email);
                $('#viewContactNumber').text(data.contact_number);
                $('#viewContactPerson').text(data.contact_person);
                $('#viewHomeAddress').text(data.home_address);
            },
            error: function() {
                showToast('Error loading student data', 'error');
            }
        });
    });
    
    // Grades Tab Functionality
    $(document).ready(function() {
        // Load subjects when course, academic year, or semester changes
        $('#gradeCourse, #gradeAcademicYear, #gradeSemester').on('change', function() {
            const course = $('#gradeCourse').val();
            const academicYear = $('#gradeAcademicYear').val();
            const semester = $('#gradeSemester').val();
            
            $.ajax({
                url: 'registrar.php',
                type: 'POST',
                data: {
                    action: 'load_subjects',
                    course: course,
                    academic_year: academicYear,
                    semester: semester
                },
                dataType: 'json',
                success: function(data) {
                    let options = '<option value="">All Subjects</option>';
                    $.each(data, function(index, subject) {
                        options += '<option value="' + subject.id + '">' + subject.subject_code + ' - ' + subject.subject_description + '</option>';
                    });
                    $('#gradeSubject').html(options);
                },
                error: function() {
                    $('#gradeSubject').html('<option value="">All Subjects</option>');
                }
            });
        });
        
        // Load grades when filters are applied
        $('#loadGradesBtn').on('click', function() {
            // Check if at least one filter is applied
            const course = $('#gradeCourse').val();
            const academicYear = $('#gradeAcademicYear').val();
            const semester = $('#gradeSemester').val();
            const subject = $('#gradeSubject').val();
            
            if (!course && !academicYear && !semester && !subject) {
                showToast('Please apply at least one filter to view grades', 'error');
                return;
            }
            
            loadGrades();
        });
        
        // Reset filters
        $('#resetGradesBtn').on('click', function() {
            $('#gradeCourse, #gradeAcademicYear, #gradeSemester, #gradeSubject').val('');
            $('#gradesTable tbody').html('<tr><td colspan="12" class="no-data-message">Please apply filters to view student grades</td></tr>');
            $('#saveAllGradesBtn').prop('disabled', true);
        });
        
        // Load grades function
        function loadGrades() {
            const course = $('#gradeCourse').val();
            const academicYear = $('#gradeAcademicYear').val();
            const semester = $('#gradeSemester').val();
            const subject = $('#gradeSubject').val();
            
            $.ajax({
                url: 'registrar.php',
                type: 'POST',
                data: {
                    action: 'load_grades',
                    course: course,
                    academic_year: academicYear,
                    semester: semester,
                    subject: subject
                },
                dataType: 'json',
                success: function(data) {
                    let html = '';
                    if (data.length === 0) {
                        html = '<tr><td colspan="12" class="text-center">No records found</td></tr>';
                        $('#saveAllGradesBtn').prop('disabled', true);
                    } else {
                        $.each(data, function(index, grade) {
                            html += '<tr>';
                            html += '<td>' + grade.id_number + '</td>';
                            html += '<td>' + grade.student_name + '</td>';
                            html += '<td>' + grade.subject_code + ' - ' + grade.subject_description + '</td>';
                            html += '<td>' + grade.coursename + ' - ' + grade.courselevel + '</td>';
                            html += '<td>' + grade.academic_year + '</td>';
                            html += '<td>' + grade.semester + '</td>';
                            html += '<td><input type="number" class="form-control grade-input" data-field="quarter1" min="0" max="100" step="0.01" value="' + (grade.quarter1_grade || '') + '"></td>';
                            html += '<td><input type="number" class="form-control grade-input" data-field="quarter2" min="0" max="100" step="0.01" value="' + (grade.quarter2_grade || '') + '"></td>';
                            html += '<td><input type="number" class="form-control grade-input" data-field="quarter3" min="0" max="100" step="0.01" value="' + (grade.quarter3_grade || '') + '"></td>';
                            html += '<td><input type="number" class="form-control grade-input" data-field="quarter4" min="0" max="100" step="0.01" value="' + (grade.quarter4_grade || '') + '"></td>';
                            html += '<td><input type="number" class="form-control average-grade-input" min="0" max="100" step="0.01" value="' + (grade.average_grade || '') + '"></td>';
                            html += '<td><input type="text" class="form-control remarks-input" value="' + (grade.remarks || '') + '"></td>';
                            html += '<input type="hidden" class="student-id" value="' + grade.student_id + '">';
                            html += '<input type="hidden" class="subject-id" value="' + grade.subject_id + '">';
                            html += '<input type="hidden" class="enrollment-id" value="' + grade.enrollment_id + '">';
                            html += '</tr>';
                        });
                        $('#saveAllGradesBtn').prop('disabled', false);
                    }
                    $('#gradesTable tbody').html(html);
                },
                error: function() {
                    $('#gradesTable tbody').html('<tr><td colspan="12" class="text-center">Error loading data</td></tr>');
                    $('#saveAllGradesBtn').prop('disabled', true);
                }
            });
        }
        
        // Save grade form submission
        $('#gradeEntryForm').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: 'registrar.php',
                type: 'POST',
                data: {
                    action: 'save_grade',
                    studentId: $('#gradeStudentId').val(),
                    subjectId: $('#gradeSubjectId').val(),
                    enrollmentId: $('#gradeEnrollmentId').val(),
                    quarter1Grade: $('#quarter1Grade').val(),
                    quarter2Grade: $('#quarter2Grade').val(),
                    quarter3Grade: $('#quarter3Grade').val(),
                    quarter4Grade: $('#quarter4Grade').val(),
                    averageGrade: $('#averageGrade').val(),
                    remarks: $('#gradeRemarks').val()
                },
                dataType: 'json',
                success: function(response) {
                    $('#gradeEntryModal').modal('hide');
                    loadGrades();
                    showToast(response.message || 'Grade saved successfully', 'success');
                },
                error: function(xhr, status, error) {
                    const response = JSON.parse(xhr.responseText);
                    showToast('Error saving grade: ' + (response.error || error), 'error');
                }
            });
        });
        
        // Save all grades button click
        $('#saveAllGradesBtn').on('click', function() {
            const grades = [];
            $('#gradesTable tbody tr').each(function() {
                const row = $(this);
                const studentId = row.find('.student-id').val();
                const subjectId = row.find('.subject-id').val();
                const enrollmentId = row.find('.enrollment-id').val();
                const quarter1 = row.find('input[data-field="quarter1"]').val();
                const quarter2 = row.find('input[data-field="quarter2"]').val();
                const quarter3 = row.find('input[data-field="quarter3"]').val();
                const quarter4 = row.find('input[data-field="quarter4"]').val();
                const average = row.find('.average-grade-input').val();
                const remarks = row.find('.remarks-input').val();
                
                grades.push({
                    student_id: studentId,
                    subject_id: subjectId,
                    enrollment_id: enrollmentId,
                    quarter1_grade: quarter1,
                    quarter2_grade: quarter2,
                    quarter3_grade: quarter3,
                    quarter4_grade: quarter4,
                    average_grade: average,
                    remarks: remarks
                });
            });
            
            if (grades.length === 0) {
                showToast('No grades to save', 'error');
                return;
            }
            
            $.ajax({
                url: 'registrar.php',
                type: 'POST',
                data: {
                    action: 'save_all_grades',
                    grades: grades
                },
                dataType: 'json',
                success: function(response) {
                    loadGrades();
                    showToast(response.message || 'All grades saved successfully', 'success');
                },
                error: function(xhr, status, error) {
                    const response = JSON.parse(xhr.responseText);
                    showToast('Error saving grades: ' + (response.error || error), 'error');
                }
            });
        });
        
        // Load grades when tab is shown
        $('a[href="#grades"]').on('shown.bs.tab', function() {
            // Reset the grades table when switching to the tab
            $('#gradesTable tbody').html('<tr><td colspan="12" class="no-data-message">Please apply filters to view student grades</td></tr>');
            $('#saveAllGradesBtn').prop('disabled', true);
        });
    });
    
    // Enrollment History modal data population
    $('#enrollmentHistoryModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const studentId = button.data('id');
        
        // Get student data via AJAX
        $.ajax({
            url: 'get_student.php',
            type: 'GET',
            data: { id: studentId },
            dataType: 'json',
            success: function(studentData) {
                // Populate student name
                $('#historyStudentName').val(studentData.last_name + ', ' + studentData.first_name + ' ' + studentData.middle_name);
                
                // Get enrollment history via AJAX
                $.ajax({
                    url: 'get_student_enrollments.php',
                    type: 'GET',
                    data: { student_id: studentId },
                    dataType: 'json',
                    success: function(enrollmentData) {
                        let historyHtml = '';
                        
                        if (enrollmentData.length === 0) {
                            historyHtml = '<tr><td colspan="6" class="text-center">No enrollment history found</td></tr>';
                        } else {
                            $.each(enrollmentData, function(index, enrollment) {
                                let statusClass = '';
                                switch(enrollment.status) {
                                    case 'Pending': statusClass = 'bg-warning'; break;
                                    case 'Enrolled': statusClass = 'bg-success'; break;
                                    case 'Dropped': statusClass = 'bg-danger'; break;
                                    case 'Completed': statusClass = 'bg-info'; break;
                                    case 'Registered': statusClass = 'bg-primary'; break;
                                }
                                
                                historyHtml += '<tr>';
                                historyHtml += '<td>' + enrollment.course_full_name + '</td>';
                                historyHtml += '<td>' + enrollment.year_level + '</td>';
                                historyHtml += '<td>' + enrollment.academic_year + '</td>';
                                historyHtml += '<td>' + enrollment.semester + '</td>';
                                historyHtml += '<td>' + enrollment.enrollment_date + '</td>';
                                historyHtml += '<td><span class="badge ' + statusClass + '">' + enrollment.status + '</span></td>';
                                historyHtml += '</tr>';
                            });
                        }
                        
                        $('#enrollmentHistoryBody').html(historyHtml);
                    },
                    error: function() {
                        $('#enrollmentHistoryBody').html('<tr><td colspan="6" class="text-center">Error loading enrollment history</td></tr>');
                    }
                });
            },
            error: function() {
                showToast('Error loading student data', 'error');
            }
        });
    });
    
    // Edit student modal data population
    $('#editStudentModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const studentId = button.data('id');
        
        // Get student data via AJAX
        $.ajax({
            url: 'get_student.php',
            type: 'GET',
            data: { id: studentId },
            dataType: 'json',
            success: function(data) {
                // Populate form fields
                $('#editStudentId').val(data.id);
                $('#editIdNumber').val(data.id_number);
                $('#editLrnNo').val(data.lrn_no);
                $('#editLastName').val(data.last_name);
                $('#editFirstName').val(data.first_name);
                $('#editMiddleName').val(data.middle_name);
                
                if (data.gender === 'Male') {
                    $('#editGenderMale').prop('checked', true);
                } else {
                    $('#editGenderFemale').prop('checked', true);
                }
                
                $('#editBirthDate').val(data.birth_date);
                $('#editAge').val(data.age);
                $('#editBirthPlace').val(data.birth_place);
                $('#editCivilStatus').val(data.civil_status);
                $('#editNationality').val(data.nationality);
                $('#editReligion').val(data.religion);
                $('#editEmail').val(data.email);
                $('#editContactNumber').val(data.contact_number);
                $('#editHomeAddress').val(data.home_address);
                
                // Secondary details
                $('#editFatherName').val(data.father_name);
                $('#editFatherOccupation').val(data.father_occupation);
                $('#editMotherName').val(data.mother_name);
                $('#editMotherOccupation').val(data.mother_occupation);
                $('#editGuardianName').val(data.guardian_name);
                $('#editGuardianAddress').val(data.guardian_address);
                $('#editOtherSupport').val(data.other_support);
                
                if (data.is_boarding == 1) {
                    $('#editBoardingYes').prop('checked', true);
                } else {
                    $('#editBoardingNo').prop('checked', true);
                }
                
                if (data.with_family == 1) {
                    $('#editWithFamilyYes').prop('checked', true);
                } else {
                    $('#editWithFamilyNo').prop('checked', true);
                }
                
                $('#editFamilyAddress').val(data.family_address);
                
                // Education details
                $('#editElemAddress').val(data.elem_address);
                $('#editElemYear').val(data.elem_year);
                $('#editSecAddress').val(data.sec_address);
                $('#editSecYear').val(data.sec_year);
                $('#editCollegeAddress').val(data.college_address);
                $('#editCollegeYear').val(data.college_year);
                $('#editVocAddress').val(data.voc_address);
                $('#editVocYear').val(data.voc_year);
                $('#editOthersAddress').val(data.others_address);
                $('#editOthersYear').val(data.others_year);
                
                // Requirements
                $('#editForm138').prop('checked', data.form138 == 1);
                $('#editMoralCert').prop('checked', data.moral_cert == 1);
                $('#editBirthCert').prop('checked', data.birth_cert == 1);
                $('#editGoodMoral').prop('checked', data.good_moral == 1);
                $('#editForm137').prop('checked', data.form137 == 1);
                $('#editParentsMarriageCert').prop('checked', data.parents_marriage_cert == 1);
                $('#editBaptismCert').prop('checked', data.baptism_cert == 1);
                $('#editProofIncome').prop('checked', data.proof_income == 1);
                $('#editBrownEnvelope').prop('checked', data.brown_envelope == 1);
                $('#editWhiteFolder').prop('checked', data.white_folder == 1);
                $('#editIdPicture').prop('checked', data.id_picture == 1);
                $('#editEscAppForm').prop('checked', data.esc_app_form == 1);
                $('#editEscContract').prop('checked', data.esc_contract == 1);
                $('#editEscCert').prop('checked', data.esc_cert == 1);
                $('#editShsvpCert').prop('checked', data.shsvp_cert == 1);
                
                // Text requirements (others1, others2, others3)
                $('#editOthers1').val(data.others1 || '');
                $('#editOthers2').val(data.others2 || '');
                $('#editOthers3').val(data.others3 || '');
                
                $('#editNotes').val(data.notes);
            },
            error: function() {
                showToast('Error loading student data', 'error');
            }
        });
    });
    
    // Delete student modal
    $('#deleteStudentModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const studentId = button.data('id');
        $('#deleteStudentId').val(studentId);
        
        // Get current tab for redirect
        const currentTab = $('.nav-tabs .active').attr('href').substring(1);
        $('#confirmDelete').attr('href', 'registrar.php?action=delete&id=' + studentId + '&current_tab=' + currentTab);
    });
    
    // Set student ID when opening enrollment modal from dashboard or student list
    $('.enroll-student').on('click', function() {
        const studentId = $(this).data('id');
        $('#studentId').val(studentId);
    });
    
    // Edit enrollment modal data population - ONLY STATUS CAN BE EDITED
    $('#editEnrollmentModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const enrollmentId = button.data('id');
        
        // Set enrollment ID
        $('#editEnrollmentId').val(enrollmentId);
        
        // Set display fields (read-only)
        $('#editStudentDisplay').val(button.data('student'));
        $('#editCourseDisplay').val(button.data('course'));
        $('#editYearLevelDisplay').val(button.data('year'));
        $('#editAcademicYearDisplay').val(button.data('academic-year'));
        $('#editSemesterDisplay').val(button.data('semester'));
        $('#editEnrollmentDateDisplay').val(button.data('enrollment-date'));
        
        // Set status dropdown
        $('#editStatus').val(button.data('status'));
    });
    
    // Delete enrollment modal
    $('#deleteEnrollmentModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const enrollmentId = button.data('id');
        $('#deleteEnrollmentId').val(enrollmentId);
        
        // Get current tab for redirect
        const currentTab = $('.nav-tabs .active').attr('href').substring(1);
        $('#confirmDeleteEnrollment').attr('href', 'registrar.php?action=delete_enrollment&id=' + enrollmentId + '&current_tab=' + currentTab);
    });
    
    // Handle course selection for subjects
    $('#subjectCourse, #subjectAcademicYear, #subjectSemester').on('change', function() {
        const courseId = $('#subjectCourse').val();
        const academicYear = $('#subjectAcademicYear').val();
        const semester = $('#subjectSemester').val();
        
        if (courseId) {
            // Get available subjects via AJAX
            $.ajax({
                url: 'get_subjects.php',
                type: 'GET',
                data: { 
                    course_id: courseId,
                    academic_year: academicYear,
                    semester: semester
                },
                dataType: 'json',
                success: function(data) {
                    let subjectsHtml = '';
                    
                    if (data.length === 0) {
                        subjectsHtml = '<p>No subjects available for this course, academic year, and semester.</p>';
                    } else {
                        subjectsHtml = '<div class="form-group">';
                        subjectsHtml += '<div class="custom-control custom-checkbox">';
                        subjectsHtml += '<input type="checkbox" class="custom-control-input" id="selectAllSubjects">';
                        subjectsHtml += '<label class="custom-control-label" for="selectAllSubjects">Select All</label>';
                        subjectsHtml += '</div>';
                        subjectsHtml += '</div>';
                        
                        $.each(data, function(index, subject) {
                            subjectsHtml += '<div class="subject-item">';
                            subjectsHtml += '<div class="custom-control custom-checkbox">';
                            subjectsHtml += '<input type="checkbox" class="custom-control-input subject-checkbox" id="subject' + subject.id + '" name="subjects[]" value="' + subject.id + '" data-units="' + subject.unit + '">';
                            subjectsHtml += '<label class="custom-control-label" for="subject' + subject.id + '">';
                            subjectsHtml += '<strong>' + subject.subject_code + '</strong> - ' + subject.subject_description;
                            subjectsHtml += ' <span class="badge badge-info">' + subject.unit + ' unit(s)</span>';
                            if (subject.year_level) {
                                subjectsHtml += ' <span class="badge badge-secondary">' + subject.year_level + '</span>';
                            }
                            if (subject.academic_year) {
                                subjectsHtml += ' <span class="badge badge-primary">' + subject.academic_year + '</span>';
                            }
                            if (subject.semester) {
                                subjectsHtml += ' <span class="badge badge-warning">' + subject.semester + '</span>';
                            }
                            if (subject.pre_requisite) {
                                subjectsHtml += ' <span class="badge badge-danger">Prerequisite: ' + subject.pre_requisite + '</span>';
                            }
                            subjectsHtml += '</label>';
                            subjectsHtml += '</div>';
                            
                            // Add Add/Drop buttons for each subject
                            subjectsHtml += '<div class="subject-actions">';
                            subjectsHtml += '<button type="button" class="btn btn-success btn-sm add-subject-btn" data-subject-id="' + subject.id + '" data-subject-name="' + subject.subject_code + ' - ' + subject.subject_description + '">';
                            subjectsHtml += '<i class="fas fa-plus"></i> Add';
                            subjectsHtml += '</button>';
                            subjectsHtml += '<button type="button" class="btn btn-danger btn-sm drop-subject-btn" data-subject-id="' + subject.id + '" data-subject-name="' + subject.subject_code + ' - ' + subject.subject_description + '">';
                            subjectsHtml += '<i class="fas fa-minus"></i> Drop';
                            subjectsHtml += '</button>';
                            subjectsHtml += '</div>';
                            
                            subjectsHtml += '</div>';
                        });
                    }
                    
                    $('#subjectsList').html(subjectsHtml);
                    
                    // Check if subjects are already assigned
                    const enrollmentId = $('#assignEnrollmentId').val();
                    $.ajax({
                        url: 'get_student_subjects.php',
                        type: 'GET',
                        data: { enrollment_id: enrollmentId },
                        dataType: 'json',
                        success: function(assignedSubjects) {
                            $.each(assignedSubjects, function(index, subject) {
                                $('#subject' + subject.subject_id).prop('checked', true);
                            });
                            updateUnitCounter();
                        },
                        error: function() {
                            console.log('Error loading assigned subjects');
                        }
                    });
                    
                    // Select All functionality
                    $('#selectAllSubjects').on('change', function() {
                        $('.subject-checkbox').prop('checked', $(this).prop('checked'));
                        updateUnitCounter();
                    });
                    
                    // Update unit counter when subjects are selected/deselected
                    $('.subject-checkbox').on('change', function() {
                        updateUnitCounter();
                    });
                    
                    // Add Subject button click
                    $('.add-subject-btn').on('click', function() {
                        const subjectId = $(this).data('subject-id');
                        const subjectName = $(this).data('subject-name');
                        
                        $('#addDropSubjectId').val(subjectId);
                        $('#addDropSubjectDisplay').val(subjectName);
                        $('#addDropOperation').val('add');
                        $('#addDropOperationDisplay').val('Add Subject');
                        $('#addDropMessage').text('This will add the selected subject to the student\'s enrollment.');
                        
                        $('#assignSubjectsModal').modal('hide');
                        $('#addDropSubjectModal').modal('show');
                    });
                    
                    // Drop Subject button click
                    $('.drop-subject-btn').on('click', function() {
                        const subjectId = $(this).data('subject-id');
                        const subjectName = $(this).data('subject-name');
                        
                        $('#addDropSubjectId').val(subjectId);
                        $('#addDropSubjectDisplay').val(subjectName);
                        $('#addDropOperation').val('drop');
                        $('#addDropOperationDisplay').val('Drop Subject');
                        $('#addDropMessage').text('This will remove the selected subject from the student\'s enrollment.');
                        
                        $('#assignSubjectsModal').modal('hide');
                        $('#addDropSubjectModal').modal('show');
                    });
                },
                error: function() {
                    $('#subjectsList').html('<p>Error loading subjects. Please try again.</p>');
                }
            });
        } else {
            $('#subjectsList').html('<p>Please select course to view available subjects.</p>');
        }
    });
    
    // Update unit counter
    function updateUnitCounter() {
        let totalUnits = 0;
        $('.subject-checkbox:checked').each(function() {
            totalUnits += parseInt($(this).data('units'));
        });
        $('#totalUnits').text(totalUnits);
    }
    
    // Assign subjects modal
    $('#assignSubjectsModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const enrollmentId = button.data('id');
        const studentId = button.data('student-id');
        const courseId = button.data('course-id');
        const academicYear = button.data('academic-year');
        const semester = button.data('semester');
        
        $('#assignEnrollmentId').val(enrollmentId);
        $('#assignStudentId').val(studentId);
        
        // Set course and make it readonly
        $('#subjectCourse').val(courseId);
        $('#subjectCourse').prop('readonly', true);
        
        // Set academic year if available
        if (academicYear) {
            $('#subjectAcademicYear').val(academicYear);
        }
        
        // Set semester if available
        if (semester) {
            $('#subjectSemester').val(semester);
        }
        
        // Trigger change to load subjects
        $('#subjectCourse').trigger('change');
    });
    
    // Add/Drop Subject Modal - Set enrollment and student IDs
    $('#addDropSubjectModal').on('show.bs.modal', function (event) {
        const enrollmentId = $('#assignEnrollmentId').val();
        const studentId = $('#assignStudentId').val();
        
        $('#addDropEnrollmentId').val(enrollmentId);
        $('#addDropStudentId').val(studentId);
    });
    
    // When Add/Drop modal is hidden, show back the Assign Subjects modal
    $('#addDropSubjectModal').on('hidden.bs.modal', function () {
        $('#assignSubjectsModal').modal('show');
    });
    
    // Print student form button
    $('.print-student-form').on('click', function() {
        const studentId = $(this).data('id');
        // Open print student form page in a new window
        window.open('print_student_form.php?id=' + studentId, '_blank');
    });
    
    // Print certification button
    $('.print-certification').on('click', function() {
        const enrollmentId = $(this).data('id');
        // Open print certification page in a new window
        window.open('print_certification.php?id=' + enrollmentId, '_blank');
    });
    
    // Print enrollment button
    $('.print-enrollment').on('click', function() {
        const enrollmentId = $(this).data('id');
        // Open print enrollment form page in a new window
        window.open('print_enrollment.php?id=' + enrollmentId, '_blank');
    });
    
    // Print grades button
    $('.print-grades').on('click', function() {
        const enrollmentId = $(this).data('id');
        // Open print certificate of grades page in a new window
        window.open('print_certificateofgrades.php?id=' + enrollmentId, '_blank');
    });
    
    // Track active tab and update current_tab hidden fields
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var tabId = $(e.target).attr("href").substring(1);
        
        // Update current_tab hidden fields in all modals
        $('#addStudentCurrentTab').val(tabId);
        $('#editStudentCurrentTab').val(tabId);
        $('#addEnrollmentCurrentTab').val(tabId);
        $('#editEnrollmentCurrentTab').val(tabId);
        $('#assignSubjectsCurrentTab').val(tabId);
        $('#addDropSubjectCurrentTab').val(tabId);
        
        // Store active tab in session via AJAX
        $.ajax({
            type: 'POST',
            url: 'registrar.php',
            data: {active_tab: tabId}
        });
    });
    
    // Dashboard search functionality for Active Students
    $('#activeSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("#activeStudentsTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Dashboard search functionality for Inactive Students
    $('#inactiveSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("#inactiveStudentsTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Enrollments Tab Functionality
    $(document).ready(function() {
        let currentPage = 1;
        const itemsPerPage = 10;
        
        // Load enrollments when tab is shown
        $('a[href="#enrollments"]').on('shown.bs.tab', function() {
            currentPage = 1;
            loadEnrollments();
        });
        
        // Apply filters button
        $('#applyEnrollmentFilters').on('click', function() {
            currentPage = 1;
            loadEnrollments();
        });
        
        // Reset filters button
        $('#resetEnrollmentFilters').on('click', function() {
            $('#enrollmentFilterCourse, #enrollmentFilterAcademicYear, #enrollmentFilterSemester, #enrollmentFilterStatus, #enrollmentSearch').val('');
            currentPage = 1;
            loadEnrollments();
        });
        
        // Search button click
        $('#enrollmentSearchBtn').on('click', function() {
            currentPage = 1;
            loadEnrollments();
        });
        
        // Clear search button click
        $('#enrollmentSearchClear').on('click', function() {
            $('#enrollmentSearch').val('');
            currentPage = 1;
            loadEnrollments();
        });
        
        // Search on Enter key
        $('#enrollmentSearch').on('keypress', function(e) {
            if (e.which === 13) { // Enter key
                currentPage = 1;
                loadEnrollments();
            }
        });
        
        // Function to load enrollments
        function loadEnrollments() {
            const searchTerm = $('#enrollmentSearch').val().trim();
            const course = $('#enrollmentFilterCourse').val();
            const academicYear = $('#enrollmentFilterAcademicYear').val();
            const semester = $('#enrollmentFilterSemester').val();
            const status = $('#enrollmentFilterStatus').val();
            
            // Show loading spinner
            $('#enrollmentLoading').show();
            $('#enrollmentEmpty').hide();
            $('#enrollmentCardsContainer .enrollment-card').remove();
            
            $.ajax({
                url: 'registrar.php',
                type: 'POST',
                data: {
                    action: 'load_enrollments',
                    search: searchTerm,
                    course: course,
                    academic_year: academicYear,
                    semester: semester,
                    status: status,
                    page: currentPage,
                    limit: itemsPerPage
                },
                dataType: 'json',
                success: function(data) {
                    // Hide loading spinner
                    $('#enrollmentLoading').hide();
                    
                    if (data.length === 0) {
                        // Show empty state
                        $('#enrollmentEmpty').show();
                        $('#enrollmentPagination').html('');
                    } else {
                        // Hide empty state
                        $('#enrollmentEmpty').hide();
                        
                        // Create enrollment cards
                        $.each(data, function(index, enrollment) {
                            let statusClass = '';
                            switch(enrollment.status) {
                                case 'Pending': statusClass = 'enrollment-status-pending'; break;
                                case 'Enrolled': statusClass = 'enrollment-status-enrolled'; break;
                                case 'Dropped': statusClass = 'enrollment-status-dropped'; break;
                                case 'Completed': statusClass = 'enrollment-status-completed'; break;
                                case 'Registered': statusClass = 'enrollment-status-registered'; break;
                            }
                            
                            const cardHtml = `
                                <div class="enrollment-card card">
                                    <div class="card-header">
                                        <h5 class="enrollment-student">${enrollment.student_name}</h5>
                                        <span class="enrollment-status ${statusClass}">${enrollment.status}</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="enrollment-course">${enrollment.course_full_name}</div>
                                        <div class="enrollment-info">
                                            <div class="enrollment-info-item">
                                                <span class="enrollment-info-label">Year Level:</span>
                                                <span class="enrollment-info-value">${enrollment.year_level}</span>
                                            </div>
                                            <div class="enrollment-info-item">
                                                <span class="enrollment-info-label">Academic Year:</span>
                                                <span class="enrollment-info-value">${enrollment.academic_year}</span>
                                            </div>
                                            <div class="enrollment-info-item">
                                                <span class="enrollment-info-label">Semester:</span>
                                                <span class="enrollment-info-value">${enrollment.semester}</span>
                                            </div>
                                        </div>
                                        <div class="enrollment-details">
                                            <div class="enrollment-detail">
                                                <div class="enrollment-detail-label">Enrollment Date</div>
                                                <div class="enrollment-detail-value">${enrollment.enrollment_date}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="enrollment-footer">
                                            <div class="enrollment-actions">
                                                <button type="button" class="btn btn-info btn-sm edit-enrollment" 
                                                        data-id="${enrollment.id}" 
                                                        data-student="${enrollment.student_name}" 
                                                        data-course="${enrollment.course_full_name}" 
                                                        data-year="${enrollment.year_level}" 
                                                        data-academic-year="${enrollment.academic_year}" 
                                                        data-semester="${enrollment.semester}" 
                                                        data-enrollment-date="${enrollment.enrollment_date}" 
                                                        data-status="${enrollment.status}"
                                                        data-toggle="modal" data-target="#editEnrollmentModal">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm assign-subjects" 
                                                        data-id="${enrollment.id}" 
                                                        data-student-id="${enrollment.student_id}" 
                                                        data-course-id="${enrollment.course_id}" 
                                                        data-year-level="${enrollment.year_level}" 
                                                        data-academic-year="${enrollment.academic_year}" 
                                                        data-semester="${enrollment.semester}"
                                                        data-toggle="modal" data-target="#assignSubjectsModal">
                                                    <i class="fas fa-book"></i> Subjects
                                                </button>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-warning btn-sm dropdown-toggle" data-toggle="dropdown">
                                                        <i class="fas fa-print"></i> Print <span class="caret"></span>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item print-certification" href="#" data-id="${enrollment.id}">
                                                            <i class="fas fa-certificate"></i> Certification
                                                        </a>
                                                        <a class="dropdown-item print-enrollment" href="#" data-id="${enrollment.id}">
                                                            <i class="fas fa-file-alt"></i> Enrollment
                                                        </a>
                                                        <a class="dropdown-item print-grades" href="#" data-id="${enrollment.id}">
                                                            <i class="fas fa-graduation-cap"></i> Grades
                                                        </a>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteEnrollmentModal" data-id="${enrollment.id}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            
                            $('#enrollmentCardsContainer').append(cardHtml);
                        });
                        
                        // Create pagination
                        updatePagination();
                    }
                },
                error: function() {
                    // Hide loading spinner
                    $('#enrollmentLoading').hide();
                    
                    // Show error message
                    $('#enrollmentCardsContainer').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Error loading enrollments. Please try again.
                        </div>
                    `);
                }
            });
        }
        
        // Function to update pagination
        function updatePagination() {
            // For simplicity, we'll just show a basic pagination
            // In a real application, you would need to get the total count from the server
            let paginationHtml = '';
            
            if (currentPage > 1) {
                paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
            }
            
            paginationHtml += `<li class="page-item active"><a class="page-link" href="#">${currentPage}</a></li>`;
            
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${currentPage + 1}">Next</a></li>`;
            
            $('#enrollmentPagination').html(paginationHtml);
            
            // Add click event for pagination links
            $('#enrollmentPagination .page-link').on('click', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page) {
                    currentPage = page;
                    loadEnrollments();
                }
            });
        }
        
        // Event delegation for dynamically created elements
        $(document).on('click', '.edit-enrollment', function() {
            const button = $(this);
            const enrollmentId = button.data('id');
            
            // Set enrollment ID
            $('#editEnrollmentId').val(enrollmentId);
            
            // Set display fields (read-only)
            $('#editStudentDisplay').val(button.data('student'));
            $('#editCourseDisplay').val(button.data('course'));
            $('#editYearLevelDisplay').val(button.data('year'));
            $('#editAcademicYearDisplay').val(button.data('academic-year'));
            $('#editSemesterDisplay').val(button.data('semester'));
            $('#editEnrollmentDateDisplay').val(button.data('enrollment-date'));
            
            // Set status dropdown
            $('#editStatus').val(button.data('status'));
        });
        
        $(document).on('click', '.assign-subjects', function() {
            const button = $(this);
            const enrollmentId = button.data('id');
            const studentId = button.data('student-id');
            const courseId = button.data('course-id');
            const academicYear = button.data('academic-year');
            const semester = button.data('semester');
            
            $('#assignEnrollmentId').val(enrollmentId);
            $('#assignStudentId').val(studentId);
            
            // Set course and make it readonly
            $('#subjectCourse').val(courseId);
            $('#subjectCourse').prop('readonly', true);
            
            // Set academic year if available
            if (academicYear) {
                $('#subjectAcademicYear').val(academicYear);
            }
            
            // Set semester if available
            if (semester) {
                $('#subjectSemester').val(semester);
            }
            
            // Trigger change to load subjects
            $('#subjectCourse').trigger('change');
        });
        
        $(document).on('click', '.print-certification', function(e) {
            e.preventDefault();
            const enrollmentId = $(this).data('id');
            window.open('print_certification.php?id=' + enrollmentId, '_blank');
        });
        
        $(document).on('click', '.print-enrollment', function(e) {
            e.preventDefault();
            const enrollmentId = $(this).data('id');
            window.open('print_enrollment.php?id=' + enrollmentId, '_blank');
        });
        
        $(document).on('click', '.print-grades', function(e) {
            e.preventDefault();
            const enrollmentId = $(this).data('id');
            window.open('print_certificateofgrades.php?id=' + enrollmentId, '_blank');
        });
        
        // If you enrollments tab is active on page load, load enrollments
        if ($('#enrollments').hasClass('show active')) {
            loadEnrollments();
        }
    });
    
    // Function to load enrollment data per school year
    function loadEnrollmentPerYearData() {
        $.ajax({
            url: 'registrar.php',
            type: 'POST',
            data: {
                action: 'load_enrollment_per_year'
            },
            dataType: 'json',
            success: function(data) {
                const ctx = document.getElementById('enrollmentChart').getContext('2d');
                
                // Destroy existing chart if it exists
                if (window.enrollmentChartInstance) {
                    window.enrollmentChartInstance.destroy();
                }
                
                window.enrollmentChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Number of Enrollments',
                            data: data.values,
                            backgroundColor: 'rgba(40, 167, 69, 0.2)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Enrollment Trends per School Year'
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        }
                    }
                });
            },
            error: function() {
                showToast('Error loading enrollment data', 'error');
            }
        });
    }
    
    // Load enrollment data when reports tab is shown
    $('a[href="#reports"]').on('shown.bs.tab', function() {
        loadEnrollmentPerYearData();
    });
    
    // If reports tab is active on page load, load enrollment data
    if ($('#reports').hasClass('show active')) {
        loadEnrollmentPerYearData();
    }
});
</script>
</body>
</html>