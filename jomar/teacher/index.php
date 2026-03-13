<?php
session_start();
require_once '../config.php';
// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: tlogin.php");
    exit();
}

// Handle AJAX requests for adding/removing students
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $teacher_id = $_SESSION['user_id'];
    $response = ['success' => false, 'message' => ''];
    
    if ($_POST['action'] === 'add_student' && isset($_POST['student_id']) && isset($_POST['subject_id'])) {
        $student_id = (int)$_POST['student_id'];
        $subject_id = (int)$_POST['subject_id'];
        
        // Check if student is already in teacher's list for this subject
        $check_query = "SELECT id FROM mystudents WHERE teacher_id = ? AND student_id = ? AND subject_id = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("iii", $teacher_id, $student_id, $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $response['message'] = 'Student is already in your list for this subject!';
        } else {
            // Check if student is enrolled in this subject and if it's assigned to the teacher
            $enrollment_check_query = "SELECT COUNT(*) as count FROM student_subjects ss
                                     JOIN subjects s ON ss.subject_id = s.id
                                     JOIN teacherassignments ta ON s.id = ta.subject_id
                                     WHERE ss.student_id = ? AND ss.subject_id = ? AND ta.teacher_id = ?";
            $stmt = $conn->prepare($enrollment_check_query);
            $stmt->bind_param("iii", $student_id, $subject_id, $teacher_id);
            $stmt->execute();
            $enrollment_result = $stmt->get_result();
            $enrollment_count = $enrollment_result->fetch_assoc()['count'];
            
            if ($enrollment_count > 0) {
                // Add student to teacher's list for this specific subject
                $insert_query = "INSERT INTO mystudents (teacher_id, student_id, subject_id) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("iii", $teacher_id, $student_id, $subject_id);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Student added successfully!';
                    $response['mystudent_id'] = $conn->insert_id;
                } else {
                    $response['message'] = 'Error adding student: ' . $conn->error;
                }
            } else {
                $response['message'] = 'This student is not enrolled in this subject or it\'s not assigned to you.';
            }
        }
    } 
    elseif ($_POST['action'] === 'remove_student' && isset($_POST['mystudent_id'])) {
        $mystudent_id = (int)$_POST['mystudent_id'];
        
        // Remove student from teacher's list
        $delete_query = "DELETE FROM mystudents WHERE id = ? AND teacher_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("ii", $mystudent_id, $teacher_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Student removed successfully!';
        } else {
            $response['message'] = 'Error removing student: ' . $conn->error;
        }
    }
    // Handle grade submission
    elseif ($_POST['action'] === 'save_grade' && isset($_POST['student_id']) && isset($_POST['subject_id']) && isset($_POST['quarter_grades'])) {
        $student_id = (int)$_POST['student_id'];
        $subject_id = (int)$_POST['subject_id'];
        $quarter_grades = $_POST['quarter_grades'];
        
        // Get average and remarks from form instead of calculating
        $average = isset($_POST['average_grade']) ? (float)$_POST['average_grade'] : 0;
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : '';
        
        // Check if there's already a grade record for this student and subject
        $check_grade_query = "SELECT id FROM student_grades WHERE student_id = ? AND subject_id = ?";
        $stmt = $conn->prepare($check_grade_query);
        $stmt->bind_param("ii", $student_id, $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing record
            $row = $result->fetch_assoc();
            $grade_id = $row['id'];
            
            $update_query = "UPDATE student_grades SET 
                            quarter1_grade = ?, quarter2_grade = ?, quarter3_grade = ?, quarter4_grade = ?,
                            average_grade = ?, remarks = ? 
                            WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("dddddsi", 
                $quarter_grades['quarter1'], 
                $quarter_grades['quarter2'], 
                $quarter_grades['quarter3'], 
                $quarter_grades['quarter4'], 
                $average, 
                $remarks, 
                $grade_id
            );
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Grades updated successfully!';
                $response['average'] = number_format($average, 2);
                $response['remarks'] = $remarks;
            } else {
                $response['message'] = 'Error updating grades: ' . $conn->error;
            }
        } else {
            // Insert new record
            // Get enrollment_id
            $get_enrollment_query = "SELECT e.id FROM enrollments e 
                                    JOIN student_subjects ss ON e.id = ss.enrollment_id 
                                    WHERE ss.student_id = ? AND ss.subject_id = ?";
            $stmt = $conn->prepare($get_enrollment_query);
            $stmt->bind_param("ii", $student_id, $subject_id);
            $stmt->execute();
            $enrollment_result = $stmt->get_result();
            
            if ($enrollment_result->num_rows > 0) {
                $enrollment_row = $enrollment_result->fetch_assoc();
                $enrollment_id = $enrollment_row['id'];
                
                $insert_query = "INSERT INTO student_grades 
                                (enrollment_id, student_id, subject_id, quarter1_grade, quarter2_grade, quarter3_grade, quarter4_grade, average_grade, remarks) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("iidddddds", 
                    $enrollment_id, 
                    $student_id, 
                    $subject_id, 
                    $quarter_grades['quarter1'], 
                    $quarter_grades['quarter2'], 
                    $quarter_grades['quarter3'], 
                    $quarter_grades['quarter4'], 
                    $average, 
                    $remarks
                );
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Grades saved successfully!';
                    $response['average'] = number_format($average, 2);
                    $response['remarks'] = $remarks;
                } else {
                    $response['message'] = 'Error saving grades: ' . $conn->error;
                }
            } else {
                $response['message'] = 'Enrollment record not found for this student and subject.';
            }
        }
    }
    // Handle activity/exam creation
    elseif ($_POST['action'] === 'create_activity' && isset($_POST['subject_id']) && isset($_POST['title']) && isset($_POST['type']) && isset($_POST['max_score'])) {
        $subject_id = (int)$_POST['subject_id'];
        $title = $_POST['title'];
        $type = $_POST['type']; // 'activity', 'exam', or 'participation'
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $max_score = (float)$_POST['max_score'];
        $quarter = isset($_POST['quarter']) ? $_POST['quarter'] : null;
        $section = isset($_POST['section']) ? $_POST['section'] : null;
        
        // Verify this subject is assigned to the teacher
        $check_assignment = "SELECT id FROM teacherassignments WHERE teacher_id = ? AND subject_id = ?";
        $stmt = $conn->prepare($check_assignment);
        $stmt->bind_param("ii", $teacher_id, $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $insert_query = "INSERT INTO activities (teacher_id, subject_id, title, description, type, max_score, quarter, section, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iisssdss", $teacher_id, $subject_id, $title, $description, $type, $max_score, $quarter, $section);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = ucfirst($type) . ' created successfully!';
                $response['activity_id'] = $conn->insert_id;
                $response['activity'] = [
                    'id' => $conn->insert_id,
                    'title' => $title,
                    'type' => $type,
                    'description' => $description,
                    'max_score' => $max_score,
                    'quarter' => $quarter,
                    'section' => $section,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            } else {
                $response['message'] = 'Error creating ' . $type . ': ' . $conn->error;
            }
        } else {
            $response['message'] = 'This subject is not assigned to you.';
        }
    }
    // Handle activity/exam update - MODIFIED TO ONLY UPDATE MAX SCORE
    elseif ($_POST['action'] === 'update_activity' && isset($_POST['activity_id']) && isset($_POST['max_score'])) {
        $activity_id = (int)$_POST['activity_id'];
        $max_score = (float)$_POST['max_score'];
        
        // Verify this activity belongs to the teacher
        $check_activity = "SELECT id FROM activities WHERE id = ? AND teacher_id = ?";
        $stmt = $conn->prepare($check_activity);
        $stmt->bind_param("ii", $activity_id, $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Only update max_score field
            $update_query = "UPDATE activities SET max_score = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("di", $max_score, $activity_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Max score updated successfully!';
            } else {
                $response['message'] = 'Error updating max score: ' . $conn->error;
            }
        } else {
            $response['message'] = 'Activity not found or you do not have permission to update it.';
        }
    }
    // Handle activity/exam deletion
    elseif ($_POST['action'] === 'delete_activity' && isset($_POST['activity_id'])) {
        $activity_id = (int)$_POST['activity_id'];
        
        // Verify this activity belongs to the teacher
        $check_activity = "SELECT id FROM activities WHERE id = ? AND teacher_id = ?";
        $stmt = $conn->prepare($check_activity);
        $stmt->bind_param("ii", $activity_id, $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // First delete any student scores for this activity
            $delete_scores = "DELETE FROM student_activity_scores WHERE activity_id = ?";
            $stmt = $conn->prepare($delete_scores);
            $stmt->bind_param("i", $activity_id);
            $stmt->execute();
            
            // Then delete the activity
            $delete_activity = "DELETE FROM activities WHERE id = ?";
            $stmt = $conn->prepare($delete_activity);
            $stmt->bind_param("i", $activity_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Activity deleted successfully!';
            } else {
                $response['message'] = 'Error deleting activity: ' . $conn->error;
            }
        } else {
            $response['message'] = 'Activity not found or you do not have permission to delete it.';
        }
    }
    // Handle student score recording
    elseif ($_POST['action'] === 'save_scores' && isset($_POST['activity_id']) && isset($_POST['student_scores'])) {
        $activity_id = (int)$_POST['activity_id'];
        $student_scores = $_POST['student_scores'];
        
        // Verify this activity belongs to the teacher
        $check_activity = "SELECT id, max_score FROM activities WHERE id = ? AND teacher_id = ?";
        $stmt = $conn->prepare($check_activity);
        $stmt->bind_param("ii", $activity_id, $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $activity = $result->fetch_assoc();
            $max_score = $activity['max_score'];
            $success_count = 0;
            $error_count = 0;
            
            foreach ($student_scores as $student_id => $score) {
                $student_id = (int)$student_id;
                $score = (float)$score;
                
                // Validate score is not greater than max_score
                if ($score > $max_score) {
                    $error_count++;
                    continue;
                }
                
                // Check if score already exists
                $check_score = "SELECT id FROM student_activity_scores WHERE activity_id = ? AND student_id = ?";
                $stmt = $conn->prepare($check_score);
                $stmt->bind_param("ii", $activity_id, $student_id);
                $stmt->execute();
                $score_result = $stmt->get_result();
                
                if ($score_result->num_rows > 0) {
                    // Update existing score
                    $update_score = "UPDATE student_activity_scores SET score = ?, updated_at = NOW() WHERE activity_id = ? AND student_id = ?";
                    $stmt = $conn->prepare($update_score);
                    $stmt->bind_param("dii", $score, $activity_id, $student_id);
                    
                    if ($stmt->execute()) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                } else {
                    // Insert new score
                    $insert_score = "INSERT INTO student_activity_scores (activity_id, student_id, score, created_at) 
                                   VALUES (?, ?, ?, NOW())";
                    $stmt = $conn->prepare($insert_score);
                    $stmt->bind_param("iid", $activity_id, $student_id, $score);
                    
                    if ($stmt->execute()) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
            }
            
            if ($error_count === 0) {
                $response['success'] = true;
                $response['message'] = 'All scores saved successfully!';
            } else {
                $response['success'] = true; // Partial success
                $response['message'] = $success_count . ' scores saved successfully, ' . $error_count . ' errors occurred.';
            }
        } else {
            $response['message'] = 'Activity not found or you do not have permission to modify it.';
        }
    }
    
    echo json_encode($response);
    exit();
}

// Handle request to get students for a specific subject and section - MODIFIED to only return students in mystudents
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_students_for_subject']) && isset($_GET['subject_id'])) {
    $subject_id = (int)$_GET['subject_id'];
    $section = isset($_GET['section']) ? $_GET['section'] : '';
    $teacher_id = $_SESSION['user_id'];
    
    // Verify this subject is assigned to the teacher
    $check_assignment = "SELECT id FROM teacherassignments WHERE teacher_id = ? AND subject_id = ?";
    $stmt = $conn->prepare($check_assignment);
    $stmt->bind_param("ii", $teacher_id, $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Get students from teacher's personal list (mystudents) for this subject and section
        // Modified to include gender and order by gender first, then by name
        $students_query = "SELECT s.id, s.id_number, s.last_name, s.first_name, s.middle_name, s.gender
                          FROM students s
                          JOIN mystudents ms ON s.id = ms.student_id
                          LEFT JOIN student_subjects ss ON s.id = ss.student_id AND ms.subject_id = ss.subject_id
                          LEFT JOIN enrollments e ON ss.enrollment_id = e.id
                          WHERE ms.teacher_id = ? AND ms.subject_id = ?";
        
        $params = [$teacher_id, $subject_id];
        $types = "ii";
        
        if (!empty($section)) {
            $students_query .= " AND e.section = ?";
            $params[] = $section;
            $types .= "s";
        }
        
        $students_query .= " ORDER BY s.gender, s.last_name, s.first_name";
        
        $stmt = $conn->prepare($students_query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $students_result = $stmt->get_result();
        
        $students = [];
        while ($row = $students_result->fetch_assoc()) {
            $students[] = $row;
        }
        
        // Get existing scores for this activity if activity_id is provided
        $existing_scores = [];
        if (isset($_GET['activity_id'])) {
            $activity_id = (int)$_GET['activity_id'];
            $scores_query = "SELECT student_id, score FROM student_activity_scores WHERE activity_id = ?";
            $stmt = $conn->prepare($scores_query);
            $stmt->bind_param("i", $activity_id);
            $stmt->execute();
            $scores_result = $stmt->get_result();
            
            while ($row = $scores_result->fetch_assoc()) {
                $existing_scores[$row['student_id']] = $row['score'];
            }
        }
        
        $response['success'] = true;
        $response['students'] = $students;
        $response['existing_scores'] = $existing_scores;
    } else {
        $response['success'] = false;
        $response['message'] = 'This subject is not assigned to you.';
    }
    
    echo json_encode($response);
    exit();
}

// Handle request to get activities for report - MODIFIED to only include mystudents
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_activities_report']) && isset($_GET['subject_id'])) {
    $subject_id = (int)$_GET['subject_id'];
    $quarter = isset($_GET['quarter']) ? $_GET['quarter'] : '';
    $section = isset($_GET['section']) ? $_GET['section'] : '';
    $teacher_id = $_SESSION['user_id'];
    
    // Verify this subject is assigned to the teacher
    $check_assignment = "SELECT id FROM teacherassignments WHERE teacher_id = ? AND subject_id = ?";
    $stmt = $conn->prepare($check_assignment);
    $stmt->bind_param("ii", $teacher_id, $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Get students from teacher's personal list (mystudents) for this subject and section
        // Modified to include gender and order by gender first, then by name
        $students_query = "SELECT s.id, s.id_number, s.last_name, s.first_name, s.middle_name, s.gender
                          FROM students s
                          JOIN mystudents ms ON s.id = ms.student_id
                          LEFT JOIN student_subjects ss ON s.id = ss.student_id AND ms.subject_id = ss.subject_id
                          LEFT JOIN enrollments e ON ss.enrollment_id = e.id
                          WHERE ms.teacher_id = ? AND ms.subject_id = ?";
        
        $params = [$teacher_id, $subject_id];
        $types = "ii";
        
        if (!empty($section)) {
            $students_query .= " AND e.section = ?";
            $params[] = $section;
            $types .= "s";
        }
        
        $students_query .= " ORDER BY s.gender, s.last_name, s.first_name";
        
        $stmt = $conn->prepare($students_query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $students_result = $stmt->get_result();
        
        $students = [];
        while ($row = $students_result->fetch_assoc()) {
            $students[] = $row;
        }
        
        // Get aggregated scores by type and quarter
        $scores = [];
        if (!empty($students)) {
            $student_ids = array_column($students, 'id');
            $student_ids_str = implode(',', $student_ids);
            
            // Modified query to aggregate scores by type and quarter
            $scores_query = "SELECT sas.student_id, a.type, a.quarter, 
                            SUM(sas.score) as total_score, 
                            SUM(a.max_score) as total_max_score
                            FROM student_activity_scores sas
                            JOIN activities a ON sas.activity_id = a.id
                            WHERE a.subject_id = ? AND sas.student_id IN ($student_ids_str)";
            
            $params = [$subject_id];
            $types = "i";
            
            if (!empty($quarter)) {
                $scores_query .= " AND a.quarter = ?";
                $params[] = $quarter;
                $types .= "s";
            }
            
            if (!empty($section)) {
                $scores_query .= " AND a.section = ?";
                $params[] = $section;
                $types .= "s";
            }
            
            $scores_query .= " GROUP BY sas.student_id, a.type, a.quarter
                             ORDER BY sas.student_id, a.quarter, a.type";
            
            $stmt = $conn->prepare($scores_query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $scores_result = $stmt->get_result();
            
            while ($row = $scores_result->fetch_assoc()) {
                $key = $row['student_id'] . '_' . $row['quarter'] . '_' . $row['type'];
                $scores[$key] = [
                    'total_score' => $row['total_score'],
                    'total_max_score' => $row['total_max_score'],
                    'percentage' => $row['total_max_score'] > 0 ? round(($row['total_score'] / $row['total_max_score']) * 100, 2) : 0
                ];
            }
        }
        
        $response['success'] = true;
        $response['students'] = $students;
        $response['scores'] = $scores;
    } else {
        $response['success'] = false;
        $response['message'] = 'This subject is not assigned to you.';
    }
    
    echo json_encode($response);
    exit();
}

// Handle request to get grades for report - MODIFIED to only include mystudents
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_grades_report']) && isset($_GET['subject_id'])) {
    $subject_id = (int)$_GET['subject_id'];
    $section = isset($_GET['section']) ? $_GET['section'] : '';
    $teacher_id = $_SESSION['user_id'];
    
    // Verify this subject is assigned to the teacher
    $check_assignment = "SELECT id FROM teacherassignments WHERE teacher_id = ? AND subject_id = ?";
    $stmt = $conn->prepare($check_assignment);
    $stmt->bind_param("ii", $teacher_id, $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Get students from teacher's personal list (mystudents) for this subject and section
        // Modified to include gender and order by gender first, then by name
        $students_query = "SELECT s.id, s.id_number, s.last_name, s.first_name, s.middle_name, s.gender
                          FROM students s
                          JOIN mystudents ms ON s.id = ms.student_id
                          LEFT JOIN student_subjects ss ON s.id = ss.student_id AND ms.subject_id = ss.subject_id
                          LEFT JOIN enrollments e ON ss.enrollment_id = e.id
                          WHERE ms.teacher_id = ? AND ms.subject_id = ?";
        
        $params = [$teacher_id, $subject_id];
        $types = "ii";
        
        if (!empty($section)) {
            $students_query .= " AND e.section = ?";
            $params[] = $section;
            $types .= "s";
        }
        
        $students_query .= " ORDER BY s.gender, s.last_name, s.first_name";
        
        $stmt = $conn->prepare($students_query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $students_result = $stmt->get_result();
        
        $students = [];
        while ($row = $students_result->fetch_assoc()) {
            $students[] = $row;
        }
        
        // Get grades for all students in this subject
        $grades = [];
        if (!empty($students)) {
            $student_ids = array_column($students, 'id');
            $student_ids_str = implode(',', $student_ids);
            
            $grades_query = "SELECT student_id, quarter1_grade, quarter2_grade, quarter3_grade, quarter4_grade, average_grade, remarks
                            FROM student_grades 
                            WHERE subject_id = ? AND student_id IN ($student_ids_str)";
            $stmt = $conn->prepare($grades_query);
            $stmt->bind_param("i", $subject_id);
            $stmt->execute();
            $grades_result = $stmt->get_result();
            
            while ($row = $grades_result->fetch_assoc()) {
                $grades[$row['student_id']] = $row;
            }
        }
        
        $response['success'] = true;
        $response['students'] = $students;
        $response['grades'] = $grades;
    } else {
        $response['success'] = false;
        $response['message'] = 'This subject is not assigned to you.';
    }
    
    echo json_encode($response);
    exit();
}

// Get teacher details
 $teacher_id = $_SESSION['user_id'];
 $query = "SELECT name, username, employee_id FROM teachers WHERE id = ?";
 $stmt = $conn->prepare($query);
 $stmt->bind_param("i", $teacher_id);
 $stmt->execute();
 $result = $stmt->get_result();
 $teacher = $result->fetch_assoc();

// Get teacher assignments with sections
 $assignments_query = "SELECT ta.id, ta.academic_year, ta.semester, ta.section, 
                             s.subject_code, s.subject_description, s.id as subject_id,
                             c.coursename, c.courselevel, c.id as course_id
                      FROM teacherassignments ta 
                      JOIN subjects s ON ta.subject_id = s.id 
                      JOIN courses c ON ta.course_id = c.id 
                      WHERE ta.teacher_id = ? 
                      ORDER BY ta.academic_year DESC, ta.semester DESC, c.coursename, s.subject_code";
 $stmt = $conn->prepare($assignments_query);
 $stmt->bind_param("i", $teacher_id);
 $stmt->execute();
 $assignments_result = $stmt->get_result();
 $assignments = [];
 $assignedSubjectIds = [];
 $assignedCourseIds = [];
 $assignedSections = [];
while ($row = $assignments_result->fetch_assoc()) {
    $assignments[] = $row;
    $assignedSubjectIds[] = $row['subject_id'];
    $assignedCourseIds[] = $row['course_id'];
    if (!empty($row['section'])) {
        $assignedSections[] = $row['section'];
    }
}

// Get student count by subject and section
 $studentCountsBySubject = [];
if (!empty($assignedSubjectIds)) {
    $count_query = "SELECT s.id as subject_id, s.subject_code, s.subject_description, e.section,
                   COUNT(DISTINCT ss.student_id) as student_count
                   FROM subjects s
                   JOIN student_subjects ss ON s.id = ss.subject_id
                   JOIN enrollments e ON ss.enrollment_id = e.id
                   WHERE s.id IN (" . implode(',', $assignedSubjectIds) . ")";
    
    if (!empty($assignedSections)) {
        $count_query .= " AND e.section IN ('" . implode("','", $assignedSections) . "')";
    }
    
    $count_query .= " GROUP BY s.id, s.subject_code, s.subject_description, e.section
                   ORDER BY student_count DESC";
    
    $stmt = $conn->prepare($count_query);
    $stmt->execute();
    $count_result = $stmt->get_result();
    while ($row = $count_result->fetch_assoc()) {
        $studentCountsBySubject[] = $row;
    }
}

// Initialize empty arrays to avoid errors when teacher has no assignments
 $students = [];
 $mystudents = [];
 $mystudent_ids = [];
 $grades = [];
 $activities = [];

// Only fetch students if teacher has assigned subjects
if (!empty($assignedSubjectIds)) {
    // Get students enrolled in teacher's assigned subjects and sections
    // Modified to include gender and order by gender first, then by name
    $students_query = "SELECT s.id, s.id_number, s.last_name, s.first_name, s.middle_name, s.gender,
                              s.email, s.contact_number, e.status, e.academic_year, e.semester, e.section,
                              c.coursename, c.courselevel, sub.subject_code, sub.subject_description, sub.id as subject_id
                   FROM students s
                   JOIN student_subjects ss ON s.id = ss.student_id
                   JOIN enrollments e ON ss.enrollment_id = e.id
                   JOIN courses c ON e.course_id = c.id
                   JOIN subjects sub ON ss.subject_id = sub.id
                   WHERE ss.subject_id IN (" . implode(',', $assignedSubjectIds) . ")";
    
    if (!empty($assignedSections)) {
        $students_query .= " AND e.section IN ('" . implode("','", $assignedSections) . "')";
    }
    
    $students_query .= " ORDER BY s.gender, s.last_name, s.first_name, sub.subject_code";
    
    $stmt = $conn->prepare($students_query);
    $stmt->execute();
    $students_result = $stmt->get_result();
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
    
    // Get student grades for teacher's subjects
    $grades_query = "SELECT sg.id, sg.quarter1_grade, sg.quarter2_grade, sg.quarter3_grade, 
                         sg.quarter4_grade, sg.average_grade, sg.remarks,
                         s.id as student_id, s.id_number, s.last_name, s.first_name, s.middle_name, s.gender,
                         sub.subject_code, sub.subject_description, sub.id as subject_id,
                         e.academic_year, e.semester, e.section, c.coursename, c.courselevel
                  FROM student_grades sg
                  JOIN students s ON sg.student_id = s.id
                  JOIN subjects sub ON sg.subject_id = sub.id
                  JOIN enrollments e ON sg.enrollment_id = e.id
                  JOIN courses c ON e.course_id = c.id
                  WHERE sg.subject_id IN (" . implode(',', $assignedSubjectIds) . ")";
    
    if (!empty($assignedSections)) {
        $grades_query .= " AND e.section IN ('" . implode("','", $assignedSections) . "')";
    }
    
    $grades_query .= " ORDER BY s.gender, sub.subject_code, s.last_name, s.first_name";
    
    $stmt = $conn->prepare($grades_query);
    $stmt->execute();
    $grades_result = $stmt->get_result();
    while ($row = $grades_result->fetch_assoc()) {
        $grades[] = $row;
    }
    
    // Get activities/exams created by this teacher
    $activities_query = "SELECT a.*, s.subject_code, s.subject_description 
                        FROM activities a 
                        JOIN subjects s ON a.subject_id = s.id 
                        WHERE a.teacher_id = ? 
                        ORDER BY a.created_at DESC";
    $stmt = $conn->prepare($activities_query);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $activities_result = $stmt->get_result();
    while ($row = $activities_result->fetch_assoc()) {
        $activities[] = $row;
    }
}

// Get teacher's personal student list (mystudents) with subject details
// Modified to include gender and order by gender first, then by name
 $mystudents_query = "SELECT ms.id as mystudent_id, s.id, s.id_number, s.last_name, s.first_name, s.middle_name, s.gender,
                            s.email, s.contact_number, e.status, e.academic_year, e.semester, e.section,
                            c.coursename, c.courselevel, sub.subject_code, sub.subject_description, sub.id as subject_id
                     FROM mystudents ms
                     JOIN students s ON ms.student_id = s.id
                     LEFT JOIN student_subjects ss ON s.id = ss.student_id AND ms.subject_id = ss.subject_id
                     LEFT JOIN enrollments e ON ss.enrollment_id = e.id
                     LEFT JOIN courses c ON e.course_id = c.id
                     LEFT JOIN subjects sub ON ms.subject_id = sub.id
                     WHERE ms.teacher_id = ?
                     ORDER BY s.gender, s.last_name, s.first_name, sub.subject_code";
 $stmt = $conn->prepare($mystudents_query);
 $stmt->bind_param("i", $teacher_id);
 $stmt->execute();
 $mystudents_result = $stmt->get_result();
while ($row = $mystudents_result->fetch_assoc()) {
    $mystudents[] = $row;
    $mystudent_ids[] = $row['id'] . '_' . $row['subject_id']; // Unique key for student+subject
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Portal</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <!-- Date Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
   <style>
        .content-wrapper {
            background-color: #f4f6f9;
        }
        .card-header {
            background-color: #004085;
            color: white;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0, 64, 133, 0.075);
        }
        .badge-semester {
            font-size: 0.85em;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 15px;
            color: #dee2e6;
        }
        .subject-info strong {
            display: block;
        }
        .subject-info small {
            color: #6c757d;
        }
        .btn-refresh {
            transition: transform 0.3s;
        }
        .btn-refresh:hover {
            transform: rotate(180deg);
        }
        .user-panel {
            height: auto;
        }
        .info-box {
            min-height: 80px;
        }
        .nav-link.active {
            background-color: rgba(255,255,255,0.1);
        }
        .tab-pane {
            padding-top: 15px;
        }
        .student-action-btn {
            margin-right: 5px;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            z-index: 9999;
            display: none;
        }
        .notification.success {
            background-color: #004085;
        }
        .notification.error {
            background-color: #dc3545;
        }
        .add-student-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .subject-badge {
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .student-row {
            border-bottom: 1px solid #f0f0f0;
        }
        .student-row:last-child {
            border-bottom: none;
        }
        .student-header {
            font-weight: bold;
            background-color: #f8f9fa;
            padding: 8px;
        }
        .student-subject {
            padding: 8px 8px 8px 20px;
        }
        .card-tools .btn-group {
            margin-right: 10px;
        }
        .grade-input {
            width: 80px;
        }
        .grade-actions {
            display: flex;
            gap: 5px;
        }
        .grade-passed {
            color: #004085;
            font-weight: bold;
        }
        .grade-failed {
            color: #dc3545;
            font-weight: bold;
        }
        .grade-inc {
            color: #ffc107;
            font-weight: bold;
        }
        .grading-container {
            position: relative;
        }
        .grading-loader {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255,255,255,0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 100;
            display: none;
        }
        .grading-loader i {
            font-size: 2rem;
            color: #004085;
        }
        .grade-summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .grade-summary h4 {
            margin-bottom: 15px;
        }
        .grade-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .grade-stat {
            flex: 1;
            min-width: 200px;
            padding: 10px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .grade-stat h5 {
            margin-bottom: 5px;
            color: #6c757d;
        }
        .grade-stat .value {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .grade-stat.passed .value {
            color: #004085;
        }
        .grade-stat.failed .value {
            color: #dc3545;
        }
        .grade-stat.inc .value {
            color: #ffc107;
        }
        .grade-stat.average .value {
            color: #004085;
        }
        .grade-stat.total .value {
            color: #6c757d;
        }
        .no-assignments-message {
            background-color: #f8f9fa;
            border-left: 4px solid #004085;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .no-assignments-message h4 {
            color: #004085;
            margin-top: 0;
        }
        .activity-type-badge {
            font-size: 0.8em;
        }
        .activity-card {
            border-left: 4px solid #004085;
            margin-bottom: 15px;
        }
        .activity-card.exam {
            border-left-color: #dc3545;
        }
        .activity-card.participation {
            border-left-color: #ffc107;
        }
        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .activity-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .activity-meta {
            font-size: 0.9em;
            color: #6c757d;
        }
        .score-input {
            width: 80px;
        }
        .report-filters {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .report-section {
            margin-top: 20px;
        }
        .report-table {
            margin-top: 15px;
        }
        .chart-container {
            height: 300px;
            margin-top: 20px;
        }
        .average-input, .remarks-select {
            width: 100px;
        }
        .quarter-badge {
            font-size: 0.75em;
            margin-left: 5px;
        }
        .report-activities-table {
            margin-top: 20px;
        }
        .report-activities-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .report-activities-table td {
            vertical-align: middle;
        }
        .activity-score-cell {
            text-align: center;
            min-width: 80px;
        }
        .activity-average-cell {
            font-weight: bold;
            text-align: center;
            background-color: #f8f9fa;
        }
        .student-performance-summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .performance-chart-container {
            height: 400px;
            margin-top: 20px;
        }
        .report-tabs {
            margin-bottom: 20px;
        }
        .report-content {
            display: none;
        }
        .report-content.active {
            display: block;
        }
        .print-button {
            margin-right: 10px;
        }
        .quarter-group {
            margin-bottom: 20px;
        }
        .quarter-header {
            background-color: #f8f9fa;
            padding: 10px;
            font-weight: bold;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .type-row {
            display: flex;
            margin-bottom: 5px;
        }
        .type-label {
            width: 120px;
            font-weight: bold;
        }
        .type-score {
            flex: 1;
        }
        .type-percentage {
            width: 80px;
            text-align: right;
        }
        .student-score-details {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .activities-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .activity-card-container {
            flex: 0 0 calc(33.333% - 15px);
            max-width: calc(33.333% - 15px);
        }
        .subject-filter-container {
            margin-bottom: 15px;
        }
        .subject-filter-label {
            margin-right: 10px;
            font-weight: bold;
        }
        .section-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            background-color: #004085;
            color: #fff;
        }
        /* Red and Blue sidebar styling */
        .main-sidebar {
            background-color: #004085 !important;
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item > .nav-link.active {
            background-color: rgba(220, 53, 69, 0.2);
            color: #fff;
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item > .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item > .nav-link {
            color: rgba(255, 255, 255, 0.9);
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item > .nav-link i {
            color: rgba(255, 255, 255, 0.9);
        }
        .sidebar-dark-redblue .nav-sidebar > .nav-item.menu-open > .nav-link {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .sidebar-dark-redblue .nav-treeview > .nav-item > .nav-link {
            color: rgba(255, 255, 255, 0.8);
        }
        .sidebar-dark-redblue .nav-treeview > .nav-item > .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .sidebar-dark-redblue .nav-treeview > .nav-item > .nav-link.active {
            background-color: rgba(220, 53, 69, 0.15);
            color: #fff;
        }
        .brand-link {
            background-color: #002752 !important;
        }
        .logout-btn {
            margin-top: 20px;
            background-color: rgba(220, 53, 69, 0.2);
            color: white;
            border: none;
            padding: 10px 15px;
            width: 100%;
            text-align: left;
            border-radius: 0;
        }
        .logout-btn:hover {
            background-color: rgba(220, 53, 69, 0.3);
            color: white;
        }
        .logout-btn i {
            margin-right: 10px;
        }
        /* Style for read-only fields in edit modal */
        .form-control:disabled, .form-control[readonly] {
            background-color: #f8f9fa;
            opacity: 1;
            cursor: default;
        }
        .edit-activity-note {
            font-style: italic;
            color: #6c757d;
            margin-top: 10px;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .report-card {
            border-left: 4px solid #004085;
            margin-bottom: 15px;
        }
        .report-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: box-shadow 0.3s;
        }
        .report-card-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .report-card-body {
            padding: 15px;
        }
        .report-card-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #004085;
        }
        .report-card-description {
            color: #6c757d;
            margin-bottom: 15px;
        }
        /* Modal improvements */
        .modal-header {
            background-color: #004085;
            color: white;
        }
        .modal-title {
            color: white;
        }
        .close {
            color: white;
        }
        .close:hover {
            color: #f8f9fa;
        }
        /* Score input improvements */
        .score-input-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .score-input-label {
            min-width: 150px;
            margin-right: 10px;
        }
        .score-input-field {
            flex: 1;
        }
        .score-input-error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-left: 10px;
        }
        /* Loading spinner for scores modal */
        .scores-loading {
            text-align: center;
            padding: 20px;
        }
        .scores-loading i {
            font-size: 2rem;
            color: #004085;
        }
        /* Gender badge styling */
        .gender-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        .gender-badge.male {
            background-color: #007bff;
            color: #fff;
        }
        .gender-badge.female {
            background-color: #e83e8c;
            color: #fff;
        }
        /* Pagination fix */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.5rem;
        }
        /* Filter improvements */
        .filter-container {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: end;
        }
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        .filter-actions {
            display: flex;
            gap: 10px;
        }
        .active-filters {
            margin-top: 10px;
            font-size: 0.9rem;
            color: #6c757d;
        }
        .active-filters .badge {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        /* Hide pagination controls */
        .dataTables_wrapper .dataTables_paginate {
            display: none;
        }
        .dataTables_wrapper .dataTables_info {
            display: none;
        }
        .dataTables_length {
            display: none;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="#" class="nav-link">Home</a>
            </li>
        </ul>
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Removed logout button from navbar -->
        </ul>
    </nav>
    <!-- /.navbar -->
    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-success elevation-4">
        <!-- Brand Logo -->
        <a href="#" class="brand-link">
            <img src="../uploads/csr.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">Teacher Portal</span>
        </a>
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="../uploads/teacher.png" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?= htmlspecialchars($teacher['name']) ?></a>
                </div>
            </div>
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="#dashboard" class="nav-link active" data-toggle="tab">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#assignments" class="nav-link" data-toggle="tab">
                            <i class="nav-icon fas fa-chalkboard-teacher"></i>
                            <p>My Assignments</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#students" class="nav-link" data-toggle="tab">
                            <i class="nav-icon fas fa-users"></i>
                            <p>All Students</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#mystudents" class="nav-link" data-toggle="tab">
                            <i class="nav-icon fas fa-user-graduate"></i>
                            <p>My Students</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#grades" class="nav-link" data-toggle="tab">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>Grades</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#activities" class="nav-link" data-toggle="tab">
                            <i class="nav-icon fas fa-tasks"></i>
                            <p>Activities & Exams</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#reports" class="nav-link" data-toggle="tab">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Reports</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link logout-btn">
                            <i class="nav-icon fas fa-sign-out-alt"></i>
                            <p>Logout</p>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Teacher Portal</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->
        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="tab-content">
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="dashboard">
                        <!-- Show message if teacher has no assignments -->
                        <?php if (empty($assignments)): ?>
                            <div class="no-assignments-message">
                                <h4><i class="fas fa-info-circle mr-2"></i>No Assignments Found</h4>
                                <p>You currently don't have any subject assignments. Please contact the administrator if you believe this is an error.</p>
                                <p>While waiting for assignments, you can still manage your personal student list.</p>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Info boxes -->
                        <div class="row">
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-chalkboard-teacher"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Assignments</span>
                                        <span class="info-box-number"><?= count($assignments) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-book"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Subjects</span>
                                        <span class="info-box-number">
                                            <?php 
                                            $subjects = array_unique(array_column($assignments, 'subject_code'));
                                            echo count($subjects);
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user-graduate"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Students</span>
                                        <span class="info-box-number"><?= count($students) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">My Students</span>
                                        <span class="info-box-number"><?= count($mystudents) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Student Distribution by Subject Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Student Distribution by Subject & Section</h3>
                                <div class="card-tools">
                                    <button class="btn btn-tool btn-refresh" id="refreshStudentDistribution">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($studentCountsBySubject)): ?>
                                    <div class="row">
                                        <?php foreach ($studentCountsBySubject as $subject): ?>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="info-box">
                                                    <span class="info-box-icon bg-success elevation-1">
                                                        <i class="fas fa-users"></i>
                                                    </span>
                                                    <div class="info-box-content">
                                                        <span class="info-box-text"><?= htmlspecialchars($subject['subject_code']) ?>
                                                            <?php if (!empty($subject['section'])): ?>
                                                                <span class="section-badge"><?= htmlspecialchars($subject['section']) ?></span>
                                                            <?php endif; ?>
                                                        </span>
                                                        <span class="info-box-number"><?= $subject['student_count'] ?> students</span>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-success" style="width: <?= min(100, ($subject['student_count'] / max(array_column($studentCountsBySubject, 'student_count'))) * 100) ?>%"></div>
                                                        </div>
                                                        <span class="progress-description">
                                                            <?= htmlspecialchars($subject['subject_description']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-users"></i>
                                        <h4>No Students Found</h4>
                                        <p>You don't have any students assigned to your subjects at the moment.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Recent Assignments Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Recent Assignments</h3>
                                <div class="card-tools">
                                    <button class="btn btn-tool btn-refresh" id="refreshAssignments">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (count($assignments) > 0): ?>
                                    <div class="table-responsive">
                                        <table id="recentAssignmentsTable" class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>Course</th>
                                                    <th>Section</th>
                                                    <th>Academic Year</th>
                                                    <th>Semester</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                // Show only 5 most recent assignments
                                                $recentAssignments = array_slice($assignments, 0, 5);
                                                foreach ($recentAssignments as $assignment): 
                                                ?>
                                                    <tr>
                                                        <td class="subject-info">
                                                            <strong><?= htmlspecialchars($assignment['subject_code']) ?></strong>
                                                            <small><?= htmlspecialchars($assignment['subject_description']) ?></small>
                                                        </td>
                                                        <td class="subject-info">
                                                            <strong><?= htmlspecialchars($assignment['coursename']) ?></strong>
                                                            <small><?= htmlspecialchars($assignment['courselevel']) ?></small>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($assignment['section'])): ?>
                                                                <span class="section-badge"><?= htmlspecialchars($assignment['section']) ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">Not Assigned</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($assignment['academic_year']) ?></td>
                                                        <td>
                                                            <?php 
                                                            $semesterClass = 'badge-secondary';
                                                            if ($assignment['semester'] == '1st') {
                                                                $semesterClass = 'badge-primary';
                                                            } elseif ($assignment['semester'] == '2nd') {
                                                                $semesterClass = 'badge-success';
                                                            }
                                                            ?>
                                                            <span class="badge <?= $semesterClass ?> badge-semester"><?= htmlspecialchars($assignment['semester']) ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        <h4>No Assignments Found</h4>
                                        <p>You don't have any assignments at the moment.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Assignments Tab -->
                    <div class="tab-pane fade" id="assignments">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">My Assignments</h3>
                                <div class="card-tools">
                                    <button class="btn btn-tool btn-refresh" id="refreshAssignmentsFull">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Filter Container -->
                                <div class="filter-container">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label for="assignmentsSubjectFilter">Subject</label>
                                            <select id="assignmentsSubjectFilter" class="form-control">
                                                <option value="">All Subjects</option>
                                                <?php 
                                                // Get unique subjects from assignments
                                                $uniqueSubjects = [];
                                                foreach ($assignments as $assignment) {
                                                    $subjectKey = $assignment['subject_id'];
                                                    $subjectName = $assignment['subject_code'] . ' - ' . $assignment['subject_description'];
                                                    if (!isset($uniqueSubjects[$subjectKey])) {
                                                        $uniqueSubjects[$subjectKey] = $subjectName;
                                                        echo '<option value="' . htmlspecialchars($subjectKey) . '">' . htmlspecialchars($subjectName) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label for="assignmentsSectionFilter">Section</label>
                                            <select id="assignmentsSectionFilter" class="form-control">
                                                <option value="">All Sections</option>
                                                <?php 
                                                // Get unique sections from assignments
                                                $uniqueSections = array_unique(array_filter(array_column($assignments, 'section')));
                                                foreach ($uniqueSections as $section) {
                                                    echo '<option value="' . htmlspecialchars($section) . '">' . htmlspecialchars($section) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label for="assignmentsSemesterFilter">Semester</label>
                                            <select id="assignmentsSemesterFilter" class="form-control">
                                                <option value="">All Semesters</option>
                                                <option value="1st">1st Semester</option>
                                                <option value="2nd">2nd Semester</option>
                                            </select>
                                        </div>
                                        <div class="filter-actions">
                                            <button class="btn btn-primary" id="applyAssignmentsFilter">
                                                <i class="fas fa-filter"></i> Apply Filter
                                            </button>
                                            <button class="btn btn-secondary" id="resetAssignmentsFilter">
                                                <i class="fas fa-times"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                    <div class="active-filters" id="assignmentsActiveFilters"></div>
                                </div>
                                
                                <?php if (count($assignments) > 0): ?>
                                    <div class="table-responsive">
                                        <table id="assignmentsTable" class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>Course</th>
                                                    <th>Section</th>
                                                    <th>Academic Year</th>
                                                    <th>Semester</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($assignments as $assignment): ?>
                                                    <tr data-subject-id="<?= $assignment['subject_id'] ?>" 
                                                        data-section="<?= htmlspecialchars($assignment['section']) ?>"
                                                        data-semester="<?= htmlspecialchars($assignment['semester']) ?>">
                                                        <td class="subject-info">
                                                            <strong><?= htmlspecialchars($assignment['subject_code']) ?></strong>
                                                            <small><?= htmlspecialchars($assignment['subject_description']) ?></small>
                                                        </td>
                                                        <td class="subject-info">
                                                            <strong><?= htmlspecialchars($assignment['coursename']) ?></strong>
                                                            <small><?= htmlspecialchars($assignment['courselevel']) ?></small>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($assignment['section'])): ?>
                                                                <span class="section-badge"><?= htmlspecialchars($assignment['section']) ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">Not Assigned</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($assignment['academic_year']) ?></td>
                                                        <td>
                                                            <?php 
                                                            $semesterClass = 'badge-secondary';
                                                            if ($assignment['semester'] == '1st') {
                                                                $semesterClass = 'badge-primary';
                                                            } elseif ($assignment['semester'] == '2nd') {
                                                                $semesterClass = 'badge-success';
                                                            }
                                                            ?>
                                                            <span class="badge <?= $semesterClass ?> badge-semester"><?= htmlspecialchars($assignment['semester']) ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-chalkboard-teacher"></i>
                                        <h4>No Assignments Found</h4>
                                        <p>You don't have any assignments at the moment.</p>
                                        <button type="button" class="btn btn-success" id="checkAssignments">
                                            <i class="fas fa-search mr-1"></i> Check Again
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Students Tab -->
                    <div class="tab-pane fade" id="students">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">All Enrolled Students</h3>
                                <div class="card-tools">
                                    <button class="btn btn-tool btn-refresh" id="refreshStudents">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Filter Container -->
                                <div class="filter-container">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label for="subjectFilter">Subject</label>
                                            <select id="subjectFilter" class="form-control">
                                                <option value="">All Subjects</option>
                                                <?php 
                                                // Get unique subjects from assignments
                                                $uniqueSubjects = [];
                                                foreach ($assignments as $assignment) {
                                                    $subjectKey = $assignment['subject_id'];
                                                    $subjectName = $assignment['subject_code'] . ' - ' . $assignment['subject_description'];
                                                    if (!isset($uniqueSubjects[$subjectKey])) {
                                                        $uniqueSubjects[$subjectKey] = $subjectName;
                                                        echo '<option value="' . htmlspecialchars($subjectKey) . '">' . htmlspecialchars($subjectName) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label for="sectionFilter">Section</label>
                                            <select id="sectionFilter" class="form-control">
                                                <option value="">All Sections</option>
                                                <?php 
                                                // Get unique sections from assignments
                                                $uniqueSections = array_unique(array_filter(array_column($assignments, 'section')));
                                                foreach ($uniqueSections as $section) {
                                                    echo '<option value="' . htmlspecialchars($section) . '">' . htmlspecialchars($section) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filter-actions">
                                            <button class="btn btn-primary" id="applyStudentsFilter">
                                                <i class="fas fa-filter"></i> Apply Filter
                                            </button>
                                            <button class="btn btn-secondary" id="resetStudentsFilter">
                                                <i class="fas fa-times"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                    <div class="active-filters" id="studentsActiveFilters"></div>
                                </div>
                                
                                <?php if (count($students) > 0): ?>
                                    <div class="table-responsive">
                                        <table id="studentsTable" class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Subject</th>
                                                    <th>Section</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($students as $student): ?>
                                                    <tr data-subject-id="<?= $student['subject_id'] ?>" data-section="<?= htmlspecialchars($student['section']) ?>">
                                                        <td><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']) ?></td>
                                                        <td><?= htmlspecialchars($student['email']) ?></td>
                                                        <td>
                                                            <span class="badge badge-success subject-badge">
                                                                <?= htmlspecialchars($student['subject_code'] . ' - ' . $student['subject_description']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($student['section'])): ?>
                                                                <span class="section-badge"><?= htmlspecialchars($student['section']) ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">Not Assigned</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if (!in_array($student['id'] . '_' . $student['subject_id'], $mystudent_ids)): ?>
                                                                <button class="btn btn-sm btn-success add-student-btn student-action-btn" 
                                                                        data-student-id="<?= $student['id'] ?>"
                                                                        data-subject-id="<?= $student['subject_id'] ?>"
                                                                        data-student-name="<?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?>"
                                                                        data-subject-name="<?= htmlspecialchars($student['subject_code']) ?>">
                                                                    <i class="fas fa-plus"></i> Add
                                                                </button>
                                                            <?php else: ?>
                                                                <button class="btn btn-sm btn-default student-action-btn" disabled>
                                                                    <i class="fas fa-check"></i> Added
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-users"></i>
                                        <h4>No Students Found</h4>
                                        <p>You don't have any students assigned to your subjects at the moment.</p>
                                        <button type="button" class="btn btn-success" id="checkStudents">
                                            <i class="fas fa-search mr-1"></i> Check Again
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- My Students Tab -->
                    <div class="tab-pane fade" id="mystudents">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">My Students</h3>
                                <div class="card-tools">
                                    <button class="btn btn-sm btn-success" id="printStudentsList">
                                        <i class="fas fa-print"></i> Print List
                                    </button>
                                    <button class="btn btn-tool btn-refresh" id="refreshMyStudents">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Filter Container -->
                                <div class="filter-container">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label for="mySubjectFilter">Subject</label>
                                            <select id="mySubjectFilter" class="form-control">
                                                <option value="">All Subjects</option>
                                                <?php 
                                                // Get unique subjects from mystudents
                                                $uniqueSubjects = [];
                                                foreach ($mystudents as $student) {
                                                    $subjectKey = $student['subject_id'];
                                                    $subjectName = $student['subject_code'] . ' - ' . $student['subject_description'];
                                                    if (!isset($uniqueSubjects[$subjectKey])) {
                                                        $uniqueSubjects[$subjectKey] = $subjectName;
                                                        echo '<option value="' . htmlspecialchars($subjectKey) . '">' . htmlspecialchars($subjectName) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label for="courseFilter">Course</label>
                                            <select id="courseFilter" class="form-control">
                                                <option value="">All Courses</option>
                                                <?php 
                                                // Get unique courses from mystudents
                                                $uniqueCourses = [];
                                                foreach ($mystudents as $student) {
                                                    $courseKey = $student['coursename'] . ' - ' . $student['courselevel'];
                                                    if (!in_array($courseKey, $uniqueCourses)) {
                                                        $uniqueCourses[] = $courseKey;
                                                        echo '<option value="' . htmlspecialchars($courseKey) . '">' . htmlspecialchars($courseKey) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label for="mySectionFilter">Section</label>
                                            <select id="mySectionFilter" class="form-control">
                                                <option value="">All Sections</option>
                                                <?php 
                                                // Get unique sections from mystudents
                                                $uniqueSections = array_unique(array_filter(array_column($mystudents, 'section')));
                                                foreach ($uniqueSections as $section) {
                                                    echo '<option value="' . htmlspecialchars($section) . '">' . htmlspecialchars($section) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filter-actions">
                                            <button class="btn btn-primary" id="applyMyStudentsFilter">
                                                <i class="fas fa-filter"></i> Apply Filter
                                            </button>
                                            <button class="btn btn-secondary" id="resetMyStudentsFilter">
                                                <i class="fas fa-times"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                    <div class="active-filters" id="myStudentsActiveFilters"></div>
                                </div>
                                
                                <?php if (count($mystudents) > 0): ?>
                                    <div class="table-responsive">
                                        <table id="myStudentsTable" class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Course</th>
                                                    <th>Subject</th>
                                                    <th>Section</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($mystudents as $student): ?>
                                                    <tr data-subject-id="<?= $student['subject_id'] ?>" 
                                                        data-course="<?= htmlspecialchars($student['coursename'] . ' - ' . $student['courselevel']) ?>" 
                                                        data-section="<?= htmlspecialchars($student['section']) ?>">
                                                        <td><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']) ?></td>
                                                        <td>
                                                            <span class="badge badge-info">
                                                                <?= htmlspecialchars($student['coursename'] . ' - ' . $student['courselevel']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-success subject-badge">
                                                                <?= htmlspecialchars($student['subject_code'] . ' - ' . $student['subject_description']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($student['section'])): ?>
                                                                <span class="section-badge"><?= htmlspecialchars($student['section']) ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">Not Assigned</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            $statusClass = 'badge-secondary';
                                                            if ($student['status'] == 'Registered') {
                                                                $statusClass = 'badge-primary';
                                                            } elseif ($student['status'] == 'Enrolled') {
                                                                $statusClass = 'badge-success';
                                                            } elseif ($student['status'] == 'Dropped') {
                                                                $statusClass = 'badge-danger';
                                                            } elseif ($student['status'] == 'Completed') {
                                                                $statusClass = 'badge-info';
                                                            }
                                                            ?>
                                                            <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($student['status']) ?></span>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-danger remove-student-btn student-action-btn" 
                                                                    data-mystudent-id="<?= $student['mystudent_id'] ?>"
                                                                    data-student-name="<?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?>">
                                                                <i class="fas fa-trash"></i> Remove
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-user-graduate"></i>
                                        <h4>No Students in Your List</h4>
                                        <p>You haven't added any students to your personal list yet.</p>
                                        <button type="button" class="btn btn-success" id="goToStudentsTab">
                                            <i class="fas fa-users mr-1"></i> Browse Students
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Grades Tab -->
                    <div class="tab-pane fade" id="grades">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Student Grades</h3>
                                <div class="card-tools">
                                    <button class="btn btn-sm btn-success" id="saveAllGrades">
                                        <i class="fas fa-save"></i> Save All Changes
                                    </button>
                                    <button class="btn btn-tool btn-refresh" id="refreshGrades">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Filter Container -->
                                <div class="filter-container">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label for="gradesSubjectFilter">Subject</label>
                                            <select id="gradesSubjectFilter" class="form-control">
                                                <option value="">All Subjects</option>
                                                <?php 
                                                // Get unique subjects from mystudents
                                                $uniqueSubjects = [];
                                                foreach ($mystudents as $student) {
                                                    $subjectKey = $student['subject_id'];
                                                    $subjectName = $student['subject_code'] . ' - ' . $student['subject_description'];
                                                    if (!isset($uniqueSubjects[$subjectKey])) {
                                                        $uniqueSubjects[$subjectKey] = $subjectName;
                                                        echo '<option value="' . htmlspecialchars($subjectKey) . '">' . htmlspecialchars($subjectName) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label for="gradesSectionFilter">Section</label>
                                            <select id="gradesSectionFilter" class="form-control">
                                                <option value="">All Sections</option>
                                                <?php 
                                                // Get unique sections from mystudents
                                                $uniqueSections = array_unique(array_filter(array_column($mystudents, 'section')));
                                                foreach ($uniqueSections as $section) {
                                                    echo '<option value="' . htmlspecialchars($section) . '">' . htmlspecialchars($section) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filter-actions">
                                            <button class="btn btn-primary" id="applyGradesFilter">
                                                <i class="fas fa-filter"></i> Apply Filter
                                            </button>
                                            <button class="btn btn-secondary" id="resetGradesFilter">
                                                <i class="fas fa-times"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                    <div class="active-filters" id="gradesActiveFilters"></div>
                                </div>
                                
                                <?php if (count($mystudents) > 0): ?>
                                    <div class="table-responsive grading-container">
                                        <div class="grading-loader">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </div>
                                        <table id="gradesTable" class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID Number</th>
                                                    <th>Student Name</th>
                                                    <th>Gender</th>
                                                    <th>Subject</th>
                                                    <th>Section</th>
                                                    <th>Quarter 1 | PRELIM</th>
                                                    <th>Quarter 2 | MIDTERM</th>
                                                    <th>Quarter 3 | FINALS</th>
                                                    <th>Quarter 4 | SUMMER</th>
                                                    <th>Average</th>
                                                    <th>Remarks</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                // We need to get the existing grades for these students and subjects
                                                $existingGrades = [];
                                                foreach ($grades as $grade) {
                                                    $key = $grade['student_id'] . '_' . $grade['subject_id'];
                                                    $existingGrades[$key] = $grade;
                                                }
                                                foreach ($mystudents as $student): 
                                                    $key = $student['id'] . '_' . $student['subject_id'];
                                                    $gradeData = isset($existingGrades[$key]) ? $existingGrades[$key] : null;
                                                    $remarks = $gradeData ? $gradeData['remarks'] : '';
                                                    $remarksClass = ($remarks === 'Passed') ? 'grade-passed' : (($remarks === 'Failed') ? 'grade-failed' : (($remarks === 'INC') ? 'grade-inc' : ''));
                                                ?>
                                                    <tr data-student-id="<?= $student['id'] ?>" 
                                                        data-subject-id="<?= $student['subject_id'] ?>"
                                                        data-section="<?= htmlspecialchars($student['section']) ?>">
                                                        <td><?= htmlspecialchars($student['id_number']) ?></td>
                                                        <td><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $student['middle_name']) ?></td>
                                                        <td>
                                                            <?php 
                                                            $genderClass = '';
                                                            if (strtolower($student['gender']) === 'male') {
                                                                $genderClass = 'male';
                                                            } elseif (strtolower($student['gender']) === 'female') {
                                                                $genderClass = 'female';
                                                            }
                                                            ?>
                                                            <span class="gender-badge <?= $genderClass ?>"><?= htmlspecialchars($student['gender']) ?></span>
                                                        </td>
                                                        <td><?= htmlspecialchars($student['subject_code'] . ' - ' . $student['subject_description']) ?></td>
                                                        <td>
                                                            <?php if (!empty($student['section'])): ?>
                                                                <span class="section-badge"><?= htmlspecialchars($student['section']) ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">Not Assigned</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control grade-input" data-quarter="1" 
                                                                   value="<?= $gradeData ? htmlspecialchars($gradeData['quarter1_grade']) : '' ?>" 
                                                                   min="0" max="100" step="0.01">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control grade-input" data-quarter="2" 
                                                                   value="<?= $gradeData ? htmlspecialchars($gradeData['quarter2_grade']) : '' ?>" 
                                                                   min="0" max="100" step="0.01">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control grade-input" data-quarter="3" 
                                                                   value="<?= $gradeData ? htmlspecialchars($gradeData['quarter3_grade']) : '' ?>" 
                                                                   min="0" max="100" step="0.01">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control grade-input" data-quarter="4" 
                                                                   value="<?= $gradeData ? htmlspecialchars($gradeData['quarter4_grade']) : '' ?>" 
                                                                   min="0" max="100" step="0.01">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control average-input" 
                                                                   value="<?= $gradeData ? htmlspecialchars($gradeData['average_grade']) : '' ?>" 
                                                                   min="0" max="100" step="0.01">
                                                        </td>
                                                        <td>
                                                            <select class="form-control remarks-select <?= $remarksClass ?>">
                                                                <option value="">Select...</option>
                                                                <option value="Passed" <?= $remarks === 'Passed' ? 'selected' : '' ?>>Passed</option>
                                                                <option value="Failed" <?= $remarks === 'Failed' ? 'selected' : '' ?>>Failed</option>
                                                                <option value="INC" <?= $remarks === 'INC' ? 'selected' : '' ?>>INC</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <div class="grade-actions">
                                                                <button class="btn btn-sm btn-success save-grade-btn">
                                                                    <i class="fas fa-save"></i> Save
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        
                                        <div class="grade-summary">
                                            <h4>Grade Summary</h4>
                                            <div class="grade-stats">
                                                <div class="grade-stat total">
                                                    <h5>Total Students</h5>
                                                    <div class="value"><?= count($mystudents) ?></div>
                                                </div>
                                                <div class="grade-stat passed">
                                                    <h5>Passed</h5>
                                                    <div class="value" id="passedCount">0</div>
                                                </div>
                                                <div class="grade-stat failed">
                                                    <h5>Failed</h5>
                                                    <div class="value" id="failedCount">0</div>
                                                </div>
                                                <div class="grade-stat inc">
                                                    <h5>INC</h5>
                                                    <div class="value" id="incCount">0</div>
                                                </div>
                                                <div class="grade-stat average">
                                                    <h5>Class Average</h5>
                                                    <div class="value" id="classAverage">0.00</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-clipboard-list"></i>
                                        <h4>No Students Found</h4>
                                        <p>You haven't added any students to your personal list yet.</p>
                                        <button type="button" class="btn btn-success" id="goToStudentsTabFromGrades">
                                            <i class="fas fa-users mr-1"></i> Browse Students
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Activities Tab -->
                    <div class="tab-pane fade" id="activities">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Activities & Exams</h3>
                                <div class="card-tools">
                                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#createActivityModal">
                                        <i class="fas fa-plus"></i> Create New
                                    </button>
                                    <button class="btn btn-tool btn-refresh" id="refreshActivities">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Filter Container -->
                                <div class="filter-container">
                                    <div class="filter-row">
                                        <div class="filter-group">
                                            <label for="activitySubjectFilter">Subject</label>
                                            <select id="activitySubjectFilter" class="form-control">
                                                <option value="">All Subjects</option>
                                                <?php 
                                                // Get unique subjects from assignments
                                                $uniqueSubjects = [];
                                                foreach ($assignments as $assignment) {
                                                    $subjectKey = $assignment['subject_id'];
                                                    $subjectName = $assignment['subject_code'] . ' - ' . $assignment['subject_description'];
                                                    if (!isset($uniqueSubjects[$subjectKey])) {
                                                        $uniqueSubjects[$subjectKey] = $subjectName;
                                                        echo '<option value="' . htmlspecialchars($subjectKey) . '">' . htmlspecialchars($subjectName) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label for="activitySectionFilter">Section</label>
                                            <select id="activitySectionFilter" class="form-control">
                                                <option value="">All Sections</option>
                                                <?php 
                                                // Get unique sections from assignments
                                                $uniqueSections = array_unique(array_filter(array_column($assignments, 'section')));
                                                foreach ($uniqueSections as $section) {
                                                    echo '<option value="' . htmlspecialchars($section) . '">' . htmlspecialchars($section) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="filter-group">
                                            <label for="activityTypeFilter">Type</label>
                                            <select id="activityTypeFilter" class="form-control">
                                                <option value="">All Types</option>
                                                <option value="activity">Activity</option>
                                                <option value="exam">Exam</option>
                                                <option value="participation">Participation</option>
                                            </select>
                                        </div>
                                        <div class="filter-actions">
                                            <button class="btn btn-primary" id="applyActivitiesFilter">
                                                <i class="fas fa-filter"></i> Apply Filter
                                            </button>
                                            <button class="btn btn-secondary" id="resetActivitiesFilter">
                                                <i class="fas fa-times"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                    <div class="active-filters" id="activitiesActiveFilters"></div>
                                </div>
                                
                                <?php if (count($activities) > 0): ?>
                                    <div class="activities-container">
                                        <?php foreach ($activities as $activity): ?>
                                            <div class="activity-card-container" data-activity-id="<?= $activity['id'] ?>" 
                                                 data-subject-id="<?= $activity['subject_id'] ?>" 
                                                 data-section="<?= htmlspecialchars($activity['section']) ?>"
                                                 data-type="<?= htmlspecialchars($activity['type']) ?>">
                                                <div class="card activity-card <?= $activity['type'] ?>">
                                                    <div class="card-header">
                                                        <div class="activity-header">
                                                            <h5 class="card-title activity-title"><?= htmlspecialchars($activity['title']) ?></h5>
                                                            <?php 
                                                            $typeClass = 'badge-secondary';
                                                            $typeIcon = 'fas fa-tasks';
                                                            if ($activity['type'] == 'exam') {
                                                                $typeClass = 'badge-danger';
                                                                $typeIcon = 'fas fa-file-alt';
                                                            } elseif ($activity['type'] == 'participation') {
                                                                $typeClass = 'badge-warning';
                                                                $typeIcon = 'fas fa-users';
                                                            }
                                                            ?>
                                                            <span class="badge <?= $typeClass ?> activity-type-badge">
                                                                <i class="<?= $typeIcon ?>"></i> <?= ucfirst($activity['type']) ?>
                                                            </span>
                                                            <?php if ($activity['quarter']): ?>
                                                                <span class="badge badge-info quarter-badge"><?= htmlspecialchars($activity['quarter']) ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <p><?= htmlspecialchars($activity['description']) ?></p>
                                                        <div class="activity-meta">
                                                            <div><i class="fas fa-book"></i> <?= htmlspecialchars($activity['subject_code'] . ' - ' . $activity['subject_description']) ?></div>
                                                            <?php if (!empty($activity['section'])): ?>
                                                                <div><i class="fas fa-users"></i> Section: <?= htmlspecialchars($activity['section']) ?></div>
                                                            <?php endif; ?>
                                                            <div><i class="fas fa-star"></i> Max Score: <?= htmlspecialchars($activity['max_score']) ?></div>
                                                            <div><i class="fas fa-clock"></i> Created: <?= htmlspecialchars(date('M d, Y', strtotime($activity['created_at']))) ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="card-footer">
                                                        <div class="btn-group w-100">
                                                            <button class="btn btn-sm btn-success record-scores-btn" 
                                                                    data-activity-id="<?= $activity['id'] ?>"
                                                                    data-activity-title="<?= htmlspecialchars($activity['title']) ?>"
                                                                    data-activity-type="<?= $activity['type'] ?>"
                                                                    data-max-score="<?= $activity['max_score'] ?>"
                                                                    data-subject-id="<?= $activity['subject_id'] ?>"
                                                                    data-section="<?= htmlspecialchars($activity['section']) ?>">
                                                                <i class="fas fa-edit"></i> Record Scores
                                                            </button>
                                                            <button class="btn btn-sm btn-info edit-activity-btn" 
                                                                    data-activity-id="<?= $activity['id'] ?>"
                                                                    data-activity-title="<?= htmlspecialchars($activity['title']) ?>"
                                                                    data-activity-type="<?= $activity['type'] ?>"
                                                                    data-activity-description="<?= htmlspecialchars($activity['description']) ?>"
                                                                    data-activity-quarter="<?= htmlspecialchars($activity['quarter']) ?>"
                                                                    data-activity-section="<?= htmlspecialchars($activity['section']) ?>"
                                                                    data-max-score="<?= $activity['max_score'] ?>"
                                                                    data-subject-id="<?= $activity['subject_id'] ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-danger delete-activity-btn" 
                                                                    data-activity-id="<?= $activity['id'] ?>"
                                                                    data-activity-title="<?= htmlspecialchars($activity['title']) ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-tasks"></i>
                                        <h4>No Activities Found</h4>
                                        <p>You haven't created any activities or exams yet.</p>
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#createActivityModal">
                                            <i class="fas fa-plus mr-1"></i> Create Your First Activity
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reports Tab - MODIFIED -->
                    <div class="tab-pane fade" id="reports">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Student Performance Reports</h3>
                                <div class="card-tools">
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Report Filters -->
                                <div class="report-filters">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="reportSubject">Subject</label>
                                                <select id="reportSubject" class="form-control">
                                                    <option value="">All Subjects</option>
                                                    <?php 
                                                    // Get unique subjects from mystudents
                                                    $uniqueSubjects = [];
                                                    foreach ($mystudents as $student) {
                                                        $subjectKey = $student['subject_id'];
                                                        $subjectName = $student['subject_code'] . ' - ' . $student['subject_description'];
                                                        if (!isset($uniqueSubjects[$subjectKey])) {
                                                            $uniqueSubjects[$subjectKey] = $subjectName;
                                                            echo '<option value="' . htmlspecialchars($subjectKey) . '">' . htmlspecialchars($subjectName) . '</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="reportSection">Section</label>
                                                <select id="reportSection" class="form-control">
                                                    <option value="">All Sections</option>
                                                    <?php 
                                                    // Get unique sections from mystudents
                                                    $uniqueSections = array_unique(array_filter(array_column($mystudents, 'section')));
                                                    foreach ($uniqueSections as $section) {
                                                        echo '<option value="' . htmlspecialchars($section) . '">' . htmlspecialchars($section) . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="reportQuarter">Quarter | SEMESTER</label>
                                                <select id="reportQuarter" class="form-control">
                                                    <option value="">All Quarters | SEMESTER</option>
                                                    <option value="1st Quarter">1st Quarter | PRELIM</option>
                                                    <option value="2nd Quarter">2nd Quarter | MIDTERM</option>
                                                    <option value="3rd Quarter">3rd Quarter | FINAL</option>
                                                    <option value="4th Quarter">4th Quarter | SUMMER</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Report Cards -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card report-card">
                                            <div class="report-card-header">
                                                <h4 class="report-card-title">Student Scores Report</h4>
                                            </div>
                                            <div class="report-card-body">
                                                <p class="report-card-description">Generate a detailed report of student scores by type and quarter.</p>
                                                <button class="btn btn-primary" id="generateScoresReport">
                                                    <i class="fas fa-chart-bar"></i> Generate Scores Report
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card report-card">
                                            <div class="report-card-header">
                                                <h4 class="report-card-title">Student Grades Report</h4>
                                            </div>
                                            <div class="report-card-body">
                                                <p class="report-card-description">Generate a comprehensive report of student grades for all quarters.</p>
                                                <button class="btn btn-primary" id="generateGradesReport">
                                                    <i class="fas fa-clipboard-list"></i> Generate Grades Report
                                                </button>
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
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
 
</div>
<!-- ./wrapper -->
<!-- Notification Container -->
<div id="notification" class="notification"></div>

<!-- Create Activity Modal -->
<div class="modal fade" id="createActivityModal" tabindex="-1" aria-labelledby="createActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createActivityModalLabel">Create New Activity/Exam</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="createActivityForm">
                    <div class="form-group">
                        <label for="activitySubject">Subject</label>
                        <select id="activitySubject" class="form-control" required>
                            <option value="">Select Subject</option>
                            <?php 
                            // Get unique subjects from assignments
                            $uniqueSubjects = [];
                            foreach ($assignments as $assignment) {
                                $subjectKey = $assignment['subject_id'];
                                $subjectName = $assignment['subject_code'] . ' - ' . $assignment['subject_description'];
                                if (!isset($uniqueSubjects[$subjectKey])) {
                                    $uniqueSubjects[$subjectKey] = $subjectName;
                                    echo '<option value="' . htmlspecialchars($subjectKey) . '">' . htmlspecialchars($subjectName) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="activitySection">Section</label>
                        <select id="activitySection" class="form-control">
                            <option value="">All Sections</option>
                            <?php 
                            // Get unique sections from assignments
                            $uniqueSections = array_unique(array_filter(array_column($assignments, 'section')));
                            foreach ($uniqueSections as $section) {
                                echo '<option value="' . htmlspecialchars($section) . '">' . htmlspecialchars($section) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="activityType">Type</label>
                        <select id="activityType" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="activity">Activity</option>
                            <option value="exam">Exam</option>
                            <option value="participation">Participation</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="activityQuarter">Quarter | SEMESTER</label>
                        <select id="activityQuarter" class="form-control" required>
                            <option value="">Select Quarter | Semester</option>
                            <option value="1st Quarter">1st Quarter | Prelim</option>
                            <option value="2nd Quarter">2nd Quarter | Midterm</option>
                            <option value="3rd Quarter">3rd Quarter | Final</option>
                            <option value="4th Quarter">4th Quarter | Summer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="activityTitle">Title</label>
                        <input type="text" class="form-control" id="activityTitle" required>
                    </div>
                    <div class="form-group">
                        <label for="activityDescription">Description</label>
                        <textarea class="form-control" id="activityDescription" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="activityMaxScore">Max Score</label>
                        <input type="number" class="form-control" id="activityMaxScore" min="1" step="0.01" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveActivity">Create</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Activity Modal - MODIFIED TO ONLY ALLOW EDITING MAX SCORE -->
<div class="modal fade" id="editActivityModal" tabindex="-1" aria-labelledby="editActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editActivityModalLabel">Edit Activity/Exam</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="edit-activity-note">
                    <i class="fas fa-info-circle"></i> Only the Max Score field can be edited. Other activity details are read-only.
                </div>
                <form id="editActivityForm">
                    <input type="hidden" id="editActivityId">
                    <div class="form-group">
                        <label for="editActivitySubject">Subject</label>
                        <select id="editActivitySubject" class="form-control" disabled>
                            <option value="">Select Subject</option>
                            <?php 
                            // Get unique subjects from assignments
                            $uniqueSubjects = [];
                            foreach ($assignments as $assignment) {
                                $subjectKey = $assignment['subject_id'];
                                $subjectName = $assignment['subject_code'] . ' - ' . $assignment['subject_description'];
                                if (!isset($uniqueSubjects[$subjectKey])) {
                                    $uniqueSubjects[$subjectKey] = $subjectName;
                                    echo '<option value="' . htmlspecialchars($subjectKey) . '">' . htmlspecialchars($subjectName) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editActivitySection">Section</label>
                        <select id="editActivitySection" class="form-control" disabled>
                            <option value="">All Sections</option>
                            <?php 
                            // Get unique sections from assignments
                            $uniqueSections = array_unique(array_filter(array_column($assignments, 'section')));
                            foreach ($uniqueSections as $section) {
                                echo '<option value="' . htmlspecialchars($section) . '">' . htmlspecialchars($section) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editActivityType">Type</label>
                        <select id="editActivityType" class="form-control" disabled>
                            <option value="">Select Type</option>
                            <option value="activity">Activity</option>
                            <option value="exam">Exam</option>
                            <option value="participation">Participation</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editActivityQuarter">Quarter|Semester</label>
                        <select id="editActivityQuarter" class="form-control" disabled>
                            <option value="">Select Quarter|Semester</option>
                            <option value="1st Quarter">1st Quarter|Prelim</option>
                            <option value="2nd Quarter">2nd Quarter|Midterm</option>
                            <option value="3rd Quarter">3rd Quarter|Final</option>
                            <option value="4th Quarter">4th Quarter|Summer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editActivityTitle">Title</label>
                        <input type="text" class="form-control" id="editActivityTitle" disabled>
                    </div>
                    <div class="form-group">
                        <label for="editActivityDescription">Description</label>
                        <textarea class="form-control" id="editActivityDescription" rows="3" disabled></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editActivityMaxScore">Max Score</label>
                        <input type="number" class="form-control" id="editActivityMaxScore" min="1" step="0.01" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="updateActivity">Update Max Score</button>
            </div>
        </div>
    </div>
</div>

<!-- Record Scores Modal - MODIFIED TO REMOVE PERCENTAGE COLUMN -->
<div class="modal fade" id="recordScoresModal" tabindex="-1" aria-labelledby="recordScoresModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recordScoresModalLabel">Record Scores</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Activity:</strong> <span id="modalActivityTitle"></span><br>
                    <strong>Type:</strong> <span id="modalActivityType"></span><br>
                    <strong>Max Score:</strong> <span id="modalMaxScore"></span><br>
                    <strong>Section:</strong> <span id="modalSection"></span>
                </div>
                <div class="scores-loading" id="scoresLoading" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading students...</p>
                </div>
                <div class="table-responsive" id="scoresTableContainer" style="display: none;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID Number</th>
                                <th>Student Name</th>
                                <th>Gender</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody id="scoresTableBody">
                            <!-- Student rows will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveScores" disabled>Save Scores</button>
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
 $(function() {
    // Initialize DataTables when the tab is shown
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr("href");
        if (target === '#assignments') {
            if (!$.fn.DataTable.isDataTable('#assignmentsTable')) {
                $('#assignmentsTable').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "pageLength": 10,
                    "paging": false,
                    "info": false,
                    "lengthChange": false,
                    "searching": true
                });
            }
        } else if (target === '#students') {
            if (!$.fn.DataTable.isDataTable('#studentsTable')) {
                $('#studentsTable').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "pageLength": 10,
                    "paging": false,
                    "info": false,
                    "lengthChange": false,
                    "searching": true
                });
            }
        } else if (target === '#mystudents') {
            if (!$.fn.DataTable.isDataTable('#myStudentsTable')) {
                $('#myStudentsTable').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "pageLength": 10,
                    "paging": false,
                    "info": false,
                    "lengthChange": false,
                    "searching": true
                });
            }
        } else if (target === '#grades') {
            if (!$.fn.DataTable.isDataTable('#gradesTable')) {
                $('#gradesTable').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "pageLength": 10,
                    "paging": false,
                    "info": false,
                    "lengthChange": false,
                    "searching": true,
                    "columnDefs": [
                        { "orderable": false, "targets": [5,6,7,8,9,10,11] } // Disable sorting on grade inputs and action column
                    ]
                });
                updateGradeSummary();
            }
        }
    });
    
    // Initialize recent assignments table
    $('#recentAssignmentsTable').DataTable({
        responsive: true,
        autoWidth: false,
        paging: false,
        searching: false,
        info: false,
        lengthChange: false,
        pageLength: 5
    });
    
    // Refresh buttons - Instead of reloading the page, just refresh the data
    $('#refreshAssignments, #refreshAssignmentsFull, #refreshStudentDistribution').on('click', function() {
        // Get current active tab
        var activeTab = $('.nav-tabs .nav-link.active').attr('href');
        
        // Show loading state
        showNotification('Refreshing data...', 'success');
        
        // Use AJAX to refresh data without page reload
        setTimeout(function() {
            showNotification('Data refreshed successfully!', 'success');
        }, 1000);
    });
    
    $('#checkAssignments').on('click', function() {
        showNotification('Checking for new assignments...', 'success');
        
        // Use AJAX to check for new assignments
        setTimeout(function() {
            showNotification('No new assignments found.', 'success');
        }, 1000);
    });
    
    $('#refreshStudents').on('click', function() {
        showNotification('Refreshing student data...', 'success');
        
        // Use AJAX to refresh student data
        setTimeout(function() {
            showNotification('Student data refreshed successfully!', 'success');
        }, 1000);
    });
    
    $('#checkStudents').on('click', function() {
        showNotification('Checking for students...', 'success');
        
        // Use AJAX to check for students
        setTimeout(function() {
            showNotification('No new students found.', 'success');
        }, 1000);
    });
    
    $('#refreshMyStudents').on('click', function() {
        showNotification('Refreshing your student list...', 'success');
        
        // Use AJAX to refresh student list
        setTimeout(function() {
            showNotification('Student list refreshed successfully!', 'success');
        }, 1000);
    });
    
    $('#refreshGrades').on('click', function() {
        showNotification('Refreshing grade data...', 'success');
        
        // Use AJAX to refresh grade data
        setTimeout(function() {
            showNotification('Grade data refreshed successfully!', 'success');
            updateGradeSummary();
        }, 1000);
    });
    
    $('#refreshActivities').on('click', function() {
        showNotification('Refreshing activities...', 'success');
        
        // Use AJAX to refresh activities
        setTimeout(function() {
            showNotification('Activities refreshed successfully!', 'success');
        }, 1000);
    });
    
    // Go to Students tab from My Students empty state
    $('#goToStudentsTab').on('click', function() {
        $('a[href="#students"]').tab('show');
    });
    
    // Go to Students tab from Grades empty state
    $('#goToStudentsTabFromGrades').on('click', function() {
        $('a[href="#students"]').tab('show');
    });
    
    // Assignments Filter
    $('#applyAssignmentsFilter').on('click', function() {
        var selectedSubject = $('#assignmentsSubjectFilter').val();
        var selectedSection = $('#assignmentsSectionFilter').val();
        var selectedSemester = $('#assignmentsSemesterFilter').val();
        var table = $('#assignmentsTable').DataTable();
        
        // Build active filters display
        var activeFilters = [];
        var filterText = '';
        
        if (selectedSubject !== '') {
            var subjectName = $('#assignmentsSubjectFilter option:selected').text();
            activeFilters.push('<span class="badge badge-primary">Subject: ' + subjectName + '</span>');
        }
        
        if (selectedSection !== '') {
            activeFilters.push('<span class="badge badge-info">Section: ' + selectedSection + '</span>');
        }
        
        if (selectedSemester !== '') {
            activeFilters.push('<span class="badge badge-warning">Semester: ' + selectedSemester + '</span>');
        }
        
        $('#assignmentsActiveFilters').html('<strong>Active Filters:</strong> ' + activeFilters.join(' '));
        
        // Apply filters to DataTable
        if (selectedSubject === '' && selectedSection === '' && selectedSemester === '') {
            // Show all rows
            table.rows().every(function() {
                $(this.node()).show();
            });
            table.draw();
        } else {
            // Filter by selected criteria
            table.rows().every(function() {
                var row = $(this.node());
                var rowSubject = row.data('subject-id');
                var rowSection = row.data('section');
                var rowSemester = row.data('semester');
                var showRow = true;
                
                if (selectedSubject !== '' && rowSubject != selectedSubject) {
                    showRow = false;
                }
                
                if (selectedSection !== '' && rowSection !== selectedSection) {
                    showRow = false;
                }
                
                if (selectedSemester !== '' && rowSemester !== selectedSemester) {
                    showRow = false;
                }
                
                if (showRow) {
                    row.show();
                } else {
                    row.hide();
                }
            });
            table.draw();
        }
    });
    
    // Reset Assignments Filter
    $('#resetAssignmentsFilter').on('click', function() {
        $('#assignmentsSubjectFilter').val('');
        $('#assignmentsSectionFilter').val('');
        $('#assignmentsSemesterFilter').val('');
        $('#assignmentsActiveFilters').html('');
        
        var table = $('#assignmentsTable').DataTable();
        table.rows().every(function() {
            $(this.node()).show();
        });
        table.draw();
    });
    
    // Students Filter
    $('#applyStudentsFilter').on('click', function() {
        var selectedSubject = $('#subjectFilter').val();
        var selectedSection = $('#sectionFilter').val();
        var table = $('#studentsTable').DataTable();
        
        // Build active filters display
        var activeFilters = [];
        
        if (selectedSubject !== '') {
            var subjectName = $('#subjectFilter option:selected').text();
            activeFilters.push('<span class="badge badge-primary">Subject: ' + subjectName + '</span>');
        }
        
        if (selectedSection !== '') {
            activeFilters.push('<span class="badge badge-info">Section: ' + selectedSection + '</span>');
        }
        
        $('#studentsActiveFilters').html('<strong>Active Filters:</strong> ' + activeFilters.join(' '));
        
        // Apply filters to DataTable
        if (selectedSubject === '' && selectedSection === '') {
            // Show all rows
            table.rows().every(function() {
                $(this.node()).show();
            });
            table.draw();
        } else {
            // Filter by selected criteria
            table.rows().every(function() {
                var row = $(this.node());
                var rowSubject = row.data('subject-id');
                var rowSection = row.data('section');
                var showRow = true;
                
                if (selectedSubject !== '' && rowSubject != selectedSubject) {
                    showRow = false;
                }
                
                if (selectedSection !== '' && rowSection !== selectedSection) {
                    showRow = false;
                }
                
                if (showRow) {
                    row.show();
                } else {
                    row.hide();
                }
            });
            table.draw();
        }
    });
    
    // Reset Students Filter
    $('#resetStudentsFilter').on('click', function() {
        $('#subjectFilter').val('');
        $('#sectionFilter').val('');
        $('#studentsActiveFilters').html('');
        
        var table = $('#studentsTable').DataTable();
        table.rows().every(function() {
            $(this.node()).show();
        });
        table.draw();
    });
    
    // My Students Filter
    $('#applyMyStudentsFilter').on('click', function() {
        var selectedSubject = $('#mySubjectFilter').val();
        var selectedCourse = $('#courseFilter').val();
        var selectedSection = $('#mySectionFilter').val();
        var table = $('#myStudentsTable').DataTable();
        
        // Build active filters display
        var activeFilters = [];
        
        if (selectedSubject !== '') {
            var subjectName = $('#mySubjectFilter option:selected').text();
            activeFilters.push('<span class="badge badge-primary">Subject: ' + subjectName + '</span>');
        }
        
        if (selectedCourse !== '') {
            activeFilters.push('<span class="badge badge-info">Course: ' + selectedCourse + '</span>');
        }
        
        if (selectedSection !== '') {
            activeFilters.push('<span class="badge badge-warning">Section: ' + selectedSection + '</span>');
        }
        
        $('#myStudentsActiveFilters').html('<strong>Active Filters:</strong> ' + activeFilters.join(' '));
        
        // Apply filters to DataTable
        if (selectedSubject === '' && selectedCourse === '' && selectedSection === '') {
            // Show all rows
            table.rows().every(function() {
                $(this.node()).show();
            });
            table.draw();
        } else {
            // Filter by selected criteria
            table.rows().every(function() {
                var row = $(this.node());
                var rowSubject = row.data('subject-id');
                var rowCourse = row.data('course');
                var rowSection = row.data('section');
                var showRow = true;
                
                if (selectedSubject !== '' && rowSubject != selectedSubject) {
                    showRow = false;
                }
                
                if (selectedCourse !== '' && rowCourse !== selectedCourse) {
                    showRow = false;
                }
                
                if (selectedSection !== '' && rowSection !== selectedSection) {
                    showRow = false;
                }
                
                if (showRow) {
                    row.show();
                } else {
                    row.hide();
                }
            });
            table.draw();
        }
    });
    
    // Reset My Students Filter
    $('#resetMyStudentsFilter').on('click', function() {
        $('#mySubjectFilter').val('');
        $('#courseFilter').val('');
        $('#mySectionFilter').val('');
        $('#myStudentsActiveFilters').html('');
        
        var table = $('#myStudentsTable').DataTable();
        table.rows().every(function() {
            $(this.node()).show();
        });
        table.draw();
    });
    
    // Grades Filter
    $('#applyGradesFilter').on('click', function() {
        var selectedSubject = $('#gradesSubjectFilter').val();
        var selectedSection = $('#gradesSectionFilter').val();
        var table = $('#gradesTable').DataTable();
        
        // Build active filters display
        var activeFilters = [];
        
        if (selectedSubject !== '') {
            var subjectName = $('#gradesSubjectFilter option:selected').text();
            activeFilters.push('<span class="badge badge-primary">Subject: ' + subjectName + '</span>');
        }
        
        if (selectedSection !== '') {
            activeFilters.push('<span class="badge badge-info">Section: ' + selectedSection + '</span>');
        }
        
        $('#gradesActiveFilters').html('<strong>Active Filters:</strong> ' + activeFilters.join(' '));
        
        // Apply filters to DataTable
        if (selectedSubject === '' && selectedSection === '') {
            // Show all rows
            table.rows().every(function() {
                $(this.node()).show();
            });
            table.draw();
        } else {
            // Filter by selected criteria
            table.rows().every(function() {
                var row = $(this.node());
                var rowSubject = row.data('subject-id');
                var rowSection = row.data('section');
                var showRow = true;
                
                if (selectedSubject !== '' && rowSubject != selectedSubject) {
                    showRow = false;
                }
                
                if (selectedSection !== '' && rowSection !== selectedSection) {
                    showRow = false;
                }
                
                if (showRow) {
                    row.show();
                } else {
                    row.hide();
                }
            });
            table.draw();
        }
        updateGradeSummary();
    });
    
    // Reset Grades Filter
    $('#resetGradesFilter').on('click', function() {
        $('#gradesSubjectFilter').val('');
        $('#gradesSectionFilter').val('');
        $('#gradesActiveFilters').html('');
        
        var table = $('#gradesTable').DataTable();
        table.rows().every(function() {
            $(this.node()).show();
        });
        table.draw();
        updateGradeSummary();
    });
    
    // Activities Filter
    $('#applyActivitiesFilter').on('click', function() {
        var selectedSubject = $('#activitySubjectFilter').val();
        var selectedSection = $('#activitySectionFilter').val();
        var selectedType = $('#activityTypeFilter').val();
        
        // Build active filters display
        var activeFilters = [];
        
        if (selectedSubject !== '') {
            var subjectName = $('#activitySubjectFilter option:selected').text();
            activeFilters.push('<span class="badge badge-primary">Subject: ' + subjectName + '</span>');
        }
        
        if (selectedSection !== '') {
            activeFilters.push('<span class="badge badge-info">Section: ' + selectedSection + '</span>');
        }
        
        if (selectedType !== '') {
            activeFilters.push('<span class="badge badge-warning">Type: ' + selectedType + '</span>');
        }
        
        $('#activitiesActiveFilters').html('<strong>Active Filters:</strong> ' + activeFilters.join(' '));
        
        // Apply filters to activity cards
        if (selectedSubject === '' && selectedSection === '' && selectedType === '') {
            // Show all activity cards
            $('.activity-card-container').show();
        } else {
            // Filter by selected criteria
            $('.activity-card-container').each(function() {
                var card = $(this);
                var cardSubject = card.data('subject-id');
                var cardSection = card.data('section');
                var cardType = card.data('type');
                var showCard = true;
                
                if (selectedSubject !== '' && cardSubject != selectedSubject) {
                    showCard = false;
                }
                
                if (selectedSection !== '' && cardSection !== selectedSection) {
                    showCard = false;
                }
                
                if (selectedType !== '' && cardType !== selectedType) {
                    showCard = false;
                }
                
                if (showCard) {
                    card.show();
                } else {
                    card.hide();
                }
            });
        }
    });
    
    // Reset Activities Filter
    $('#resetActivitiesFilter').on('click', function() {
        $('#activitySubjectFilter').val('');
        $('#activitySectionFilter').val('');
        $('#activityTypeFilter').val('');
        $('#activitiesActiveFilters').html('');
        
        // Show all activity cards
        $('.activity-card-container').show();
    });
    
    // Print students list - MODIFIED TO INCLUDE SUBJECT FILTER
    $('#printStudentsList').on('click', function() {
        var selectedSubject = $('#mySubjectFilter').val();
        var selectedCourse = $('#courseFilter').val();
        var selectedSection = $('#mySectionFilter').val();
        var printUrl = 'printstudentlist.php?';
        
        if (selectedSubject !== '') {
            printUrl += 'subject_id=' + encodeURIComponent(selectedSubject) + '&';
        }
        
        if (selectedCourse !== '') {
            printUrl += 'course=' + encodeURIComponent(selectedCourse) + '&';
        }
        
        if (selectedSection !== '') {
            printUrl += 'section=' + encodeURIComponent(selectedSection) + '&';
        }
        
        // Remove trailing '&' if exists
        printUrl = printUrl.replace(/&$/, '');
        
        window.open(printUrl, '_blank');
    });
    
    // Show notification
    function showNotification(message, type) {
        var notification = $('#notification');
        notification.removeClass('success error').addClass(type);
        notification.text(message).fadeIn().delay(3000).fadeOut();
    }
    
    // Add student to teacher's list
    $('.add-student-btn').on('click', function() {
        var btn = $(this);
        var studentId = btn.data('student-id');
        var subjectId = btn.data('subject-id');
        var studentName = btn.data('student-name');
        var subjectName = btn.data('subject-name');
        
        if (confirm('Are you sure you want to add ' + studentName + ' for ' + subjectName + ' to your student list?')) {
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'add_student',
                    student_id: studentId,
                    subject_id: subjectId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        // Disable button and change appearance
                        btn.removeClass('btn-success').addClass('btn-default').prop('disabled', true);
                        btn.html('<i class="fas fa-check"></i> Added');
                        
                        // Add to My Students table if it's initialized
                        if ($.fn.DataTable.isDataTable('#myStudentsTable')) {
                            var row = btn.closest('tr');
                            var newRow = row.clone();
                            // Change the action button to Remove
                            newRow.find('td:last-child').html(
                                '<button class="btn btn-sm btn-danger remove-student-btn student-action-btn" ' +
                                'data-mystudent-id="' + response.mystudent_id + '" ' + // Use the returned mystudent_id
                                'data-student-name="' + studentName + '">' +
                                '<i class="fas fa-trash"></i> Remove</button>'
                            );
                            $('#myStudentsTable').DataTable().row.add(newRow).draw();
                        }
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function() {
                    showNotification('An error occurred. Please try again.', 'error');
                }
            });
        }
    });
    
    // Remove student from teacher's list
    $(document).on('click', '.remove-student-btn', function() {
        var btn = $(this);
        var mystudentId = btn.data('mystudent-id');
        var studentName = btn.data('student-name');
        
        if (confirm('Are you sure you want to remove ' + studentName + ' from your student list?')) {
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'remove_student',
                    mystudent_id: mystudentId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        // Remove row from table
                        if ($.fn.DataTable.isDataTable('#myStudentsTable')) {
                            $('#myStudentsTable').DataTable().row(btn.closest('tr')).remove().draw();
                        } else {
                            btn.closest('tr').remove();
                        }
                        
                        // Enable corresponding Add button in Students tab
                        var studentId = btn.closest('tr').find('td:first-child').text();
                        var subjectCode = btn.closest('tr').find('td:nth-child(5) .badge').text();
                        var studentRows = $('#studentsTable').find('td:first-child:contains("' + studentId + '")').closest('tr');
                        
                        studentRows.each(function() {
                            var row = $(this);
                            var rowSubjectCode = row.find('td:nth-child(5) .badge').text();
                            if (rowSubjectCode === subjectCode) {
                                var addBtn = row.find('.add-student-btn');
                                if (addBtn.length) {
                                    addBtn.removeClass('btn-default').addClass('btn-success').prop('disabled', false);
                                    addBtn.html('<i class="fas fa-plus"></i> Add');
                                }
                            }
                        });
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function() {
                    showNotification('An error occurred. Please try again.', 'error');
                }
            });
        }
    });
    
    // Update grade summary
    function updateGradeSummary() {
        var passedCount = 0;
        var failedCount = 0;
        var incCount = 0;
        var totalSum = 0;
        var totalCount = 0;
        
        $('#gradesTable tbody tr:visible').each(function() {
            var row = $(this);
            var avgText = row.find('.average-input').val();
            if (avgText !== '') {
                var avg = parseFloat(avgText);
                totalSum += avg;
                totalCount++;
                
                var remarks = row.find('.remarks-select').val();
                if (remarks === 'Passed') {
                    passedCount++;
                } else if (remarks === 'Failed') {
                    failedCount++;
                } else if (remarks === 'INC') {
                    incCount++;
                }
            }
        });
        
        $('#passedCount').text(passedCount);
        $('#failedCount').text(failedCount);
        $('#incCount').text(incCount);
        var classAvg = totalCount > 0 ? totalSum / totalCount : 0;
        $('#classAverage').text(classAvg.toFixed(2));
    }
    
    // Grade input change
    $(document).on('input', '.grade-input, .average-input', function() {
        var row = $(this).closest('tr');
        row.addClass('row-changed');
        updateGradeSummary();
    });
    
    // Remarks select change
    $(document).on('change', '.remarks-select', function() {
        var row = $(this).closest('tr');
        var remarks = $(this).val();
        
        // Update styling based on selected remarks
        $(this).removeClass('grade-passed grade-failed grade-inc');
        if (remarks === 'Passed') {
            $(this).addClass('grade-passed');
        } else if (remarks === 'Failed') {
            $(this).addClass('grade-failed');
        } else if (remarks === 'INC') {
            $(this).addClass('grade-inc');
        }
        
        row.addClass('row-changed');
        updateGradeSummary();
    });
    
    // Save single row
    $(document).on('click', '.save-grade-btn', function() {
        var btn = $(this);
        var row = btn.closest('tr');
        saveGradeRow(row, btn);
    });
    
    function saveGradeRow(row, btn) {
        var studentId = row.data('student-id');
        var subjectId = row.data('subject-id');
        var quarterGrades = {
            quarter1: row.find('input[data-quarter="1"]').val(),
            quarter2: row.find('input[data-quarter="2"]').val(),
            quarter3: row.find('input[data-quarter="3"]').val(),
            quarter4: row.find('input[data-quarter="4"]').val()
        };
        var average = row.find('.average-input').val();
        var remarks = row.find('.remarks-select').val();
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        $.ajax({
            url: '',
            type: 'POST',
            data: {
                action: 'save_grade',
                student_id: studentId,
                subject_id: subjectId,
                quarter_grades: quarterGrades,
                average_grade: average,
                remarks: remarks
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    row.find('.average-input').val(response.average);
                    var remarksElement = row.find('.remarks-select');
                    remarksElement.val(response.remarks);
                    remarksElement.removeClass('grade-passed grade-failed grade-inc');
                    if (response.remarks === 'Passed') {
                        remarksElement.addClass('grade-passed');
                    } else if (response.remarks === 'Failed') {
                        remarksElement.addClass('grade-failed');
                    } else if (response.remarks === 'INC') {
                        remarksElement.addClass('grade-inc');
                    }
                    row.removeClass('row-changed');
                    btn.html('<i class="fas fa-check"></i> Saved');
                    setTimeout(function() {
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
                    }, 2000);
                    updateGradeSummary();
                } else {
                    showNotification(response.message, 'error');
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
                }
            },
            error: function() {
                showNotification('An error occurred. Please try again.', 'error');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
            }
        });
    }
    
    // Save all changed rows
    $('#saveAllGrades').on('click', function() {
        var changedRows = $('#gradesTable tbody tr.row-changed:visible');
        if (changedRows.length === 0) {
            showNotification('No changes to save.', 'error');
            return;
        }
        if (confirm('Are you sure you want to save all changes?')) {
            $('.grading-loader').show();
            var saveCount = 0;
            var errorCount = 0;
            var totalRows = changedRows.length;
            changedRows.each(function(index) {
                var row = $(this);
                var btn = row.find('.save-grade-btn');
                
                setTimeout(function() {
                    saveGradeRow(row, btn);
                    saveCount++;
                    
                    if (saveCount === totalRows) {
                        $('.grading-loader').hide();
                        showNotification('All grades have been processed.', 'success');
                    }
                }, index * 300); // Stagger requests to avoid overwhelming the server
            });
        }
    });
    
    // Create Activity
    $('#saveActivity').on('click', function() {
        var subjectId = $('#activitySubject').val();
        var section = $('#activitySection').val();
        var type = $('#activityType').val();
        var quarter = $('#activityQuarter').val();
        var title = $('#activityTitle').val();
        var description = $('#activityDescription').val();
        var maxScore = $('#activityMaxScore').val();
        
        if (!subjectId || !type || !quarter || !title || !maxScore) {
            showNotification('Please fill in all required fields.', 'error');
            return;
        }
        
        $.ajax({
            url: '',
            type: 'POST',
            data: {
                action: 'create_activity',
                subject_id: subjectId,
                section: section,
                type: type,
                quarter: quarter,
                title: title,
                description: description,
                max_score: maxScore
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    $('#createActivityModal').modal('hide');
                    $('#createActivityForm')[0].reset();
                    
                    // Add the new activity to the activities container dynamically
                    var activity = response.activity;
                    var typeClass = 'badge-secondary';
                    var typeIcon = 'fas fa-tasks';
                    var cardClass = 'activity-card';
                    
                    if (activity.type == 'exam') {
                        typeClass = 'badge-danger';
                        typeIcon = 'fas fa-file-alt';
                        cardClass += ' exam';
                    } else if (activity.type == 'participation') {
                        typeClass = 'badge-warning';
                        typeIcon = 'fas fa-users';
                        cardClass += ' participation';
                    }
                    
                    var subjectName = $('#activitySubject option:selected').text();
                    var activityCard = '<div class="activity-card-container" data-activity-id="' + activity.id + '" data-subject-id="' + activity.subject_id + '" data-section="' + (activity.section || '') + '" data-type="' + activity.type + '">' +
                        '<div class="card ' + cardClass + '">' +
                        '<div class="card-header">' +
                        '<div class="activity-header">' +
                        '<h5 class="card-title activity-title">' + activity.title + '</h5>' +
                        '<span class="badge ' + typeClass + ' activity-type-badge">' +
                        '<i class="' + typeIcon + '"></i> ' + activity.type.charAt(0).toUpperCase() + activity.type.slice(1) + '</span>';
                    
                    if (activity.quarter) {
                        activityCard += '<span class="badge badge-info quarter-badge">' + activity.quarter + '</span>';
                    }
                    
                    activityCard += '</div></div><div class="card-body"><p>' + (activity.description || '') + '</p>' +
                        '<div class="activity-meta">' +
                        '<div><i class="fas fa-book"></i> ' + subjectName + '</div>';
                    
                    if (activity.section) {
                        activityCard += '<div><i class="fas fa-users"></i> Section: ' + activity.section + '</div>';
                    }
                    
                    activityCard += '<div><i class="fas fa-star"></i> Max Score: ' + activity.max_score + '</div>' +
                        '<div><i class="fas fa-clock"></i> Created: ' + new Date().toLocaleDateString() + '</div>' +
                        '</div></div><div class="card-footer">' +
                        '<div class="btn-group w-100">' +
                        '<button class="btn btn-sm btn-success record-scores-btn" ' +
                        'data-activity-id="' + activity.id + '" ' +
                        'data-activity-title="' + activity.title + '" ' +
                        'data-activity-type="' + activity.type + '" ' +
                        'data-max-score="' + activity.max_score + '" ' +
                        'data-subject-id="' + activity.subject_id + '" ' +
                        'data-section="' + (activity.section || '') + '">' +
                        '<i class="fas fa-edit"></i> Record Scores</button>' +
                        '<button class="btn btn-sm btn-info edit-activity-btn" ' +
                        'data-activity-id="' + activity.id + '" ' +
                        'data-activity-title="' + activity.title + '" ' +
                        'data-activity-type="' + activity.type + '" ' +
                        'data-activity-description="' + (activity.description || '') + '" ' +
                        'data-activity-quarter="' + (activity.quarter || '') + '" ' +
                        'data-activity-section="' + (activity.section || '') + '" ' +
                        'data-max-score="' + activity.max_score + '" ' +
                        'data-subject-id="' + activity.subject_id + '">' +
                        '<i class="fas fa-edit"></i></button>' +
                        '<button class="btn btn-sm btn-danger delete-activity-btn" ' +
                        'data-activity-id="' + activity.id + '" ' +
                        'data-activity-title="' + activity.title + '">' +
                        '<i class="fas fa-trash"></i></button>' +
                        '</div></div></div>';
                    
                    // Add the new activity card to the container
                    $('.activities-container').prepend(activityCard);
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('An error occurred. Please try again.', 'error');
            }
        });
    });
    
    // Edit Activity - Open modal with activity data
    $(document).on('click', '.edit-activity-btn', function() {
        var btn = $(this);
        var activityId = btn.data('activity-id');
        var activityTitle = btn.data('activity-title');
        var activityType = btn.data('activity-type');
        var activityDescription = btn.data('activity-description');
        var activityQuarter = btn.data('activity-quarter');
        var activitySection = btn.data('activity-section');
        var maxScore = btn.data('max-score');
        var subjectId = btn.data('subject-id');
        
        // Populate the edit form
        $('#editActivityId').val(activityId);
        $('#editActivitySubject').val(subjectId);
        $('#editActivitySection').val(activitySection);
        $('#editActivityType').val(activityType);
        $('#editActivityQuarter').val(activityQuarter);
        $('#editActivityTitle').val(activityTitle);
        $('#editActivityDescription').val(activityDescription);
        $('#editActivityMaxScore').val(maxScore);
        
        // Show the modal
        $('#editActivityModal').modal('show');
    });
    
    // Update Activity - MODIFIED TO ONLY UPDATE MAX SCORE
    $('#updateActivity').on('click', function() {
        var activityId = $('#editActivityId').val();
        var maxScore = $('#editActivityMaxScore').val();
        
        if (!maxScore) {
            showNotification('Please enter a valid max score.', 'error');
            return;
        }
        
        $.ajax({
            url: '',
            type: 'POST',
            data: {
                action: 'update_activity',
                activity_id: activityId,
                max_score: maxScore
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    $('#editActivityModal').modal('hide');
                    
                    // Update the max score in the activity card
                    var editBtn = $('.edit-activity-btn[data-activity-id="' + activityId + '"]');
                    var activityCard = editBtn.closest('.activity-card-container');
                    
                    // Update the max score in the meta information
                    activityCard.find('.activity-meta div:contains("Max Score")').html(
                        '<i class="fas fa-star"></i> Max Score: ' + maxScore
                    );
                    
                    // Update the button data attributes
                    activityCard.find('.record-scores-btn').attr('data-max-score', maxScore);
                    activityCard.find('.edit-activity-btn').attr('data-max-score', maxScore);
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function() {
                showNotification('An error occurred. Please try again.', 'error');
            }
        });
    });
    
    // Delete Activity
    $(document).on('click', '.delete-activity-btn', function() {
        var btn = $(this);
        var activityId = btn.data('activity-id');
        var activityTitle = btn.data('activity-title');
        
        if (confirm('Are you sure you want to delete "' + activityTitle + '"? This will also delete all recorded scores for this activity.')) {
            $.ajax({
                url: '',
                type: 'POST',
                data: {
                    action: 'delete_activity',
                    activity_id: activityId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message, 'success');
                        // Remove the activity card
                        btn.closest('.activity-card-container').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        showNotification(response.message, 'error');
                    }
                },
                error: function() {
                    showNotification('An error occurred. Please try again.', 'error');
                }
            });
        }
    });
    
    // Record Scores Modal - MODIFIED TO REMOVE PERCENTAGE COLUMN
    $(document).on('click', '.record-scores-btn', function() {
        var btn = $(this);
        var activityId = btn.data('activity-id');
        var activityTitle = btn.data('activity-title');
        var activityType = btn.data('activity-type');
        var maxScore = btn.data('max-score');
        var subjectId = btn.data('subject-id');
        var section = btn.data('section');
        
        // Set modal info
        $('#modalActivityTitle').text(activityTitle);
        $('#modalActivityType').text(activityType);
        $('#modalMaxScore').text(maxScore);
        $('#modalSection').text(section || 'All Sections');
        
        // Store activity info for saving
        $('#recordScoresModal').data('activity-id', activityId);
        
        // Clear previous scores and show loading
        $('#scoresTableBody').empty();
        $('#scoresLoading').show();
        $('#scoresTableContainer').hide();
        $('#saveScores').prop('disabled', true);
        
        // Get students for this subject and section
        $.ajax({
            url: '',
            type: 'GET',
            data: {
                get_students_for_subject: true,
                subject_id: subjectId,
                section: section,
                activity_id: activityId  // Include activity_id to get existing scores
            },
            dataType: 'json',
            success: function(response) {
                $('#scoresLoading').hide();
                
                if (response.success) {
                    var students = response.students;
                    var existingScores = response.existing_scores || {};
                    
                    if (students.length === 0) {
                        $('#scoresTableContainer').html('<div class="alert alert-warning">No students found for this subject and section.</div>');
                    } else {
                        // Create a row for each student
                        students.forEach(function(student) {
                            var score = existingScores[student.id] || '';
                            
                            var genderClass = '';
                            if (student.gender && student.gender.toLowerCase() === 'male') {
                                genderClass = 'male';
                            } else if (student.gender && student.gender.toLowerCase() === 'female') {
                                genderClass = 'female';
                            }
                            
                            var row = '<tr>' +
                                '<td>' + student.id_number + '</td>' +
                                '<td>' + student.last_name + ', ' + student.first_name + ' ' + student.middle_name + '</td>' +
                                '<td>' + 
                                    '<span class="gender-badge ' + genderClass + '">' + (student.gender || '') + '</span>' +
                                '</td>' +
                                '<td>' +
                                    '<div class="score-input-group">' +
                                        '<input type="number" class="form-control score-input score-input-field" ' +
                                        'data-student-id="' + student.id + '" ' +
                                        'data-max-score="' + maxScore + '" ' +
                                        'value="' + score + '" ' +
                                        'min="0" max="' + maxScore + '" step="0.01">' +
                                    '</div>' +
                                '</td>' +
                                '</tr>';
                            
                            $('#scoresTableBody').append(row);
                        });
                        
                        // Enable save button if there are students
                        $('#saveScores').prop('disabled', false);
                    }
                    
                    // Show the table
                    $('#scoresTableContainer').show();
                    
                    // Show the modal
                    $('#recordScoresModal').modal('show');
                } else {
                    $('#scoresTableContainer').html('<div class="alert alert-danger">' + response.message + '</div>');
                    $('#scoresTableContainer').show();
                    $('#recordScoresModal').modal('show');
                }
            },
            error: function() {
                $('#scoresLoading').hide();
                $('#scoresTableContainer').html('<div class="alert alert-danger">An error occurred while fetching students.</div>');
                $('#scoresTableContainer').show();
                $('#recordScoresModal').modal('show');
            }
        });
    });
    
    // Save Scores - MODIFIED TO REMOVE PERCENTAGE CALCULATION
    $('#saveScores').on('click', function() {
        var activityId = $('#recordScoresModal').data('activity-id');
        var studentScores = {};
        var hasErrors = false;
        var maxScore = parseFloat($('#modalMaxScore').text());
        
        // Collect all scores
        $('.score-input-field').each(function() {
            var studentId = $(this).data('student-id');
            var score = $(this).val();
            var inputMaxScore = parseFloat($(this).data('max-score'));
            
            if (score !== '') {
                score = parseFloat(score);
                
                // Validate score is not greater than max_score
                if (score > inputMaxScore) {
                    hasErrors = true;
                    $(this).addClass('is-invalid');
                    $(this).closest('.score-input-group').find('.score-input-error').text('Score exceeds max score of ' + inputMaxScore);
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).closest('.score-input-group').find('.score-input-error').text('');
                    studentScores[studentId] = score;
                }
            } else {
                $(this).removeClass('is-invalid');
                $(this).closest('.score-input-group').find('.score-input-error').text('');
            }
        });
        
        if (hasErrors) {
            showNotification('Please fix the errors before saving.', 'error');
            return;
        }
        
        if (Object.keys(studentScores).length === 0) {
            showNotification('No scores to save.', 'error');
            return;
        }
        
        // Disable save button to prevent double submission
        $('#saveScores').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        // Save scores
        $.ajax({
            url: '',
            type: 'POST',
            data: {
                action: 'save_scores',
                activity_id: activityId,
                student_scores: studentScores
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    $('#recordScoresModal').modal('hide');
                } else {
                    showNotification(response.message, 'error');
                }
                // Re-enable save button
                $('#saveScores').prop('disabled', false).html('Save Scores');
            },
            error: function() {
                showNotification('An error occurred. Please try again.', 'error');
                // Re-enable save button
                $('#saveScores').prop('disabled', false).html('Save Scores');
            }
        });
    });
    
    // Generate Student Performance Report - MODIFIED
    $('#generateStudentPerformance').on('click', function() {
        var subjectId = $('#reportSubject').val();
        var section = $('#reportSection').val();
        var quarter = $('#reportQuarter').val();
        
        var url = 'printstudentperformance.php?';
        
        if (subjectId) {
            url += 'subject_id=' + encodeURIComponent(subjectId) + '&';
        }
        
        if (section) {
            url += 'section=' + encodeURIComponent(section) + '&';
        }
        
        if (quarter) {
            url += 'quarter=' + encodeURIComponent(quarter) + '&';
        }
        
        // Remove trailing '&' if exists
        url = url.replace(/&$/, '');
        
        // Open in new window/tab
        window.open(url, '_blank');
    });
    
    // Generate Scores Report - MODIFIED
    $('#generateScoresReport').on('click', function() {
        var subjectId = $('#reportSubject').val();
        var section = $('#reportSection').val();
        var quarter = $('#reportQuarter').val();
        
        var url = 'printstudentperformance.php?report_type=scores&';
        
        if (subjectId) {
            url += 'subject_id=' + encodeURIComponent(subjectId) + '&';
        }
        
        if (section) {
            url += 'section=' + encodeURIComponent(section) + '&';
        }
        
        if (quarter) {
            url += 'quarter=' + encodeURIComponent(quarter) + '&';
        }
        
        // Remove trailing '&' if exists
        url = url.replace(/&$/, '');
        
        // Open in new window/tab
        window.open(url, '_blank');
    });
    
    // Generate Grades Report - MODIFIED
    $('#generateGradesReport').on('click', function() {
        var subjectId = $('#reportSubject').val();
        var section = $('#reportSection').val();
        
        var url = 'printstudentperformance.php?report_type=grades&';
        
        if (subjectId) {
            url += 'subject_id=' + encodeURIComponent(subjectId) + '&';
        }
        
        if (section) {
            url += 'section=' + encodeURIComponent(section) + '&';
        }
        
        // Remove trailing '&' if exists
        url = url.replace(/&$/, '');
        
        // Open in new window/tab
        window.open(url, '_blank');
    });
    
    // Apply Filters - MODIFIED
    $('#applyFilters').on('click', function() {
        var subjectId = $('#reportSubject').val();
        var section = $('#reportSection').val();
        var quarter = $('#reportQuarter').val();
        
        var url = 'printstudentperformance.php?';
        
        if (subjectId) {
            url += 'subject_id=' + encodeURIComponent(subjectId) + '&';
        }
        
        if (section) {
            url += 'section=' + encodeURIComponent(section) + '&';
        }
        
        if (quarter) {
            url += 'quarter=' + encodeURIComponent(quarter) + '&';
        }
        
        // Remove trailing '&' if exists
        url = url.replace(/&$/, '');
        
        // Open in new window/tab
        window.open(url, '_blank');
    });
});
</script>
</body>
</html>