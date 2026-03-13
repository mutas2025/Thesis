
<?php
session_start();
require_once 'config.php';

// Function to check for duplicate student records
function checkDuplicateStudent($conn, $firstName, $lastName, $middleName) {
    $escapedFirstName = mysqli_real_escape_string($conn, $firstName);
    $escapedLastName = mysqli_real_escape_string($conn, $lastName);
    $escapedMiddleName = mysqli_real_escape_string($conn, $middleName);
    
    $query = "SELECT id_number, first_name, last_name, middle_name FROM students 
              WHERE first_name = '$escapedFirstName' 
              AND last_name = '$escapedLastName' 
              AND middle_name = '$escapedMiddleName'";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }
    
    return mysqli_num_rows($result) > 0;
}

// Generate ID Number based on birthdate (YYYYMMDD format)
function generateIdNumber($conn, $birthdate) {
    // Format birthdate as YYYYMMDD
    $datePart = date('Ymd', strtotime($birthdate));
    
    // Check for existing IDs with same date part
    $escapedDatePart = mysqli_real_escape_string($conn, $datePart);
    $query = "SELECT id_number FROM students WHERE id_number LIKE '$escapedDatePart%' ORDER BY id_number DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $lastId = $row['id_number'];
        $sequence = (int)substr($lastId, -2);
        $sequence++;
    } else {
        $sequence = 1;
    }
    
    // Format sequence as two digits (01, 02, etc.)
    $sequencePart = str_pad($sequence, 2, '0', STR_PAD_LEFT);
    
    return $datePart . $sequencePart;
}

// Calculate age from birthdate
function calculateAge($birthdate) {
    $today = new DateTime();
    $birthDate = new DateTime($birthdate);
    $age = $today->diff($birthDate)->y;
    return $age;
}

// Send registration confirmation email
function sendRegistrationEmail($email, $firstName, $lastName, $idNumber) {
    $to = $email;
    $subject = "Registration Confirmation - SJC Canlaon System";
    
    $message = "
    <html>
    <head>
    <title>Registration Confirmation</title>
    </head>
    <body>
    <h2>Registration Successful!</h2>
    <p>Dear $firstName $lastName,</p>
    <p>Thank you for registering with the SJC Canlaon System. Your registration has been successfully processed.</p>
    <p>Your Student ID Number is: <strong>$idNumber</strong></p>
    <p>Please keep this ID number for your records. You will need it to access your student portal.</p>
    <p>Visit our website at: <a href='https://sjc-canlaon.com'>sjc-canlaon.com</a></p>
    <p>If you have any questions or concerns, please contact the administrator.</p>
    <p>Best regards,<br>SJC Canlaon System Team</p>
    </body>
    </html>
    ";
    
    // Always set content-type when sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    
    // More headers
    $headers .= 'From: <saintjoseph_cc@yahoo.com.ph>' . "\r\n";
    
    // Send email
    return mail($to, $subject, $message, $headers);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $middleName = mysqli_real_escape_string($conn, $_POST['middleName']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $birthDate = mysqli_real_escape_string($conn, $_POST['birthDate']);
    $age = calculateAge($birthDate);
    $birthPlace = mysqli_real_escape_string($conn, $_POST['birthPlace']);
    $civilStatus = mysqli_real_escape_string($conn, $_POST['civilStatus']);
    $nationality = mysqli_real_escape_string($conn, $_POST['nationality']);
    $religion = mysqli_real_escape_string($conn, $_POST['religion']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $contactNumber = mysqli_real_escape_string($conn, $_POST['contactNumber']);
    $homeAddress = mysqli_real_escape_string($conn, $_POST['homeAddress']);
    
    // Check for duplicate student records
    if (checkDuplicateStudent($conn, $firstName, $lastName, $middleName)) {
        $_SESSION['error'] = "A student with the name $firstName $lastName $middleName already exists in our system. Please contact the administrator if you believe this is an error.";
        header("Location: registration_form.php");
        exit();
    }
    
    // Generate ID number
    $idNumber = generateIdNumber($conn, $birthDate);
    
    // Insert primary details into database
    $query = "INSERT INTO students (
        id_number, last_name, first_name, middle_name, gender, 
        birth_date, age, birth_place, civil_status, nationality, religion, 
        email, password, contact_number, home_address
    ) VALUES (
        '$idNumber', '$lastName', '$firstName', '$middleName', '$gender', 
        '$birthDate', $age, '$birthPlace', '$civilStatus', '$nationality', '$religion', 
        '$email', '$password', '$contactNumber', '$homeAddress'
    )";
    
    if (mysqli_query($conn, $query)) {
        $studentId = mysqli_insert_id($conn);
        
        // Insert secondary details (Now Required)
        $fatherName = mysqli_real_escape_string($conn, $_POST['fatherName']);
        $fatherOccupation = mysqli_real_escape_string($conn, $_POST['fatherOccupation']);
        $motherName = mysqli_real_escape_string($conn, $_POST['motherName']);
        $motherOccupation = mysqli_real_escape_string($conn, $_POST['motherOccupation']);
        $guardianName = mysqli_real_escape_string($conn, $_POST['guardianName']);
        $guardianAddress = mysqli_real_escape_string($conn, $_POST['guardianAddress']);
        $otherSupport = mysqli_real_escape_string($conn, $_POST['otherSupport']);
        
        // Handling Radio Buttons (Default to '0' if not set, though 'required' attribute enforces selection)
        $boarding = isset($_POST['boarding']) ? 1 : 0;
        $withFamily = isset($_POST['withFamily']) ? 1 : 0;
        $familyAddress = mysqli_real_escape_string($conn, $_POST['familyAddress']);
        
        $query = "UPDATE students SET 
            father_name = '$fatherName',
            father_occupation = '$fatherOccupation',
            mother_name = '$motherName',
            mother_occupation = '$motherOccupation',
            guardian_name = '$guardianName',
            guardian_address = '$guardianAddress',
            other_support = '$otherSupport',
            is_boarding = $boarding,
            with_family = $withFamily,
            family_address = '$familyAddress'
            WHERE id = $studentId";
        
        mysqli_query($conn, $query);
        
        // Send registration confirmation email
        $emailSent = sendRegistrationEmail($email, $firstName, $lastName, $idNumber);
        
        // Set success message and ID number
        $_SESSION['registration_success'] = true;
        $_SESSION['id_number'] = $idNumber;
        $_SESSION['email_sent'] = $emailSent;
        header("Location: registration_form.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        header("Location: registration_form.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Student Registration Form</title>
    <link rel="icon" href="../uploads/csr.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #28a745;
            --dark-green: #1e7e34;
            --light-green: #f0fff4;
            --accent-green: #d4edda;
        }

        body {
            background-color: #eef5ee;
            padding-top: 20px;
            padding-bottom: 40px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .registration-container {
            max-width: 800px; /* Narrower for single column readability */
            margin: 0 auto;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(40, 167, 69, 0.15);
            padding: 30px;
            border-top: 5px solid var(--primary-green);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f1f1f1;
        }

        .form-header h2 {
            color: var(--dark-green);
            font-weight: 700;
            margin-bottom: 5px;
        }

        .form-section-title {
            color: var(--primary-green);
            font-size: 1.25rem;
            font-weight: 600;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 1px dashed #ccc;
        }

        .required-field::after {
            content: " *";
            color: #dc3545;
        }

        .btn-submit {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
            padding: 12px 40px;
            font-size: 18px;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            background-color: var(--dark-green);
            border-color: var(--dark-green);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
        }

        .error-message {
            color: #dc3545;
            margin-bottom: 20px;
            text-align: center;
        }

        .submit-section {
            text-align: center;
            margin-top: 30px;
            padding: 25px;
            background-color: var(--light-green);
            border-radius: 10px;
            border: 1px dashed var(--primary-green);
        }

        /* Modal Styling */
        .success-modal .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .success-modal .modal-header {
            background-color: var(--primary-green);
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
            justify-content: center;
        }

        .success-modal .modal-body {
            padding: 40px 30px;
            text-align: center;
        }

        .success-modal .modal-footer {
            border-top: none;
            text-align: center;
            justify-content: center;
            padding-bottom: 30px;
        }

        .success-icon {
            font-size: 70px;
            color: var(--primary-green);
            margin-bottom: 20px;
        }

        .id-number {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-green);
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            display: inline-block;
        }

        .duplicate-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 5px solid #ffc107;
        }

        .email-notification {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .form-control:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .custom-control-input:checked ~ .custom-control-label::before {
            background-color: var(--primary-green);
            border-color: var(--dark-green);
        }

        /* Mobile Friendly Styles */
        @media (max-width: 768px) {
            body {
                padding-top: 10px;
                padding-bottom: 20px;
            }
            
            .registration-container {
                padding: 15px;
                border-radius: 10px;
                margin: 0 10px;
            }

            .form-header h2 {
                font-size: 1.5rem;
            }

            .submit-section {
                padding: 15px;
            }

            .btn-submit {
                padding: 10px;
                font-size: 16px;
            }

            .success-modal .modal-dialog {
                margin: 10px;
            }

            .id-number {
                font-size: 18px;
            }
        }

        @media (max-width: 576px) {
            .custom-control-inline {
                display: block;
                margin-bottom: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-container">
            <div class="form-header">
                <h2>Student Registration Form</h2>
                <p class="text-muted">Please fill out all required fields</p>
            </div>
            
            <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger error-message">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
                ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="registration_form.php" id="registrationForm">
                
                <div class="duplicate-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Note:</strong> Our system will check for duplicate records based on exact first name, last name, and middle name. 
                    If a student with the same name already exists, registration will be prevented.
                </div>

                <!-- Primary Details Section -->
                <h4 class="form-section-title"><i class="fas fa-user mr-2"></i>Personal Information</h4>
                
                <div class="form-group">
                    <label for="lastName" class="required-field">Last Name</label>
                    <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Enter Last Name" required>
                </div>
                
                <div class="form-group">
                    <label for="firstName" class="required-field">First Name</label>
                    <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Enter First Name" required>
                </div>
                
                <div class="form-group">
                    <label for="middleName" class="required-field">Middle Name</label>
                    <input type="text" class="form-control" id="middleName" name="middleName" placeholder="Enter Middle Name" required>
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
                    <label for="email" class="required-field">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="required-field">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required>
                </div>
                
                <div class="form-group">
                    <label for="contactNumber" class="required-field">Contact Number</label>
                    <input type="text" class="form-control" id="contactNumber" name="contactNumber" placeholder="Enter Contact Number" required>
                </div>
                
                <div class="form-group">
                    <label for="homeAddress" class="required-field">Home Address</label>
                    <textarea class="form-control" id="homeAddress" name="homeAddress" rows="3" placeholder="Enter Home Address" required></textarea>
                </div>

                <!-- Secondary Details Section -->
                <h4 class="form-section-title"><i class="fas fa-users mr-2"></i>Family Information</h4>
                
                <div class="form-group">
                    <label for="fatherName" class="required-field">Father's Name</label>
                    <input type="text" class="form-control" id="fatherName" name="fatherName" placeholder="Enter Father's Name" required>
                </div>
                
                <div class="form-group">
                    <label for="fatherOccupation" class="required-field">Father's Occupation</label>
                    <input type="text" class="form-control" id="fatherOccupation" name="fatherOccupation" placeholder="Enter Father's Occupation" required>
                </div>
                
                <div class="form-group">
                    <label for="motherName" class="required-field">Mother's Name</label>
                    <input type="text" class="form-control" id="motherName" name="motherName" placeholder="Enter Mother's Name" required>
                </div>
                
                <div class="form-group">
                    <label for="motherOccupation" class="required-field">Mother's Occupation</label>
                    <input type="text" class="form-control" id="motherOccupation" name="motherOccupation" placeholder="Enter Mother's Occupation" required>
                </div>
                
                <div class="form-group">
                    <label for="guardianName" class="required-field">Guardian's Name</label>
                    <input type="text" class="form-control" id="guardianName" name="guardianName" placeholder="Enter Guardian's Name" required>
                </div>
                
                <div class="form-group">
                    <label for="guardianAddress" class="required-field">Guardian's Address</label>
                    <input type="text" class="form-control" id="guardianAddress" name="guardianAddress" placeholder="Enter Guardian's Address" required>
                </div>
                
                <div class="form-group">
                    <label for="otherSupport" class="required-field">Other Person Supporting</label>
                    <input type="text" class="form-control" id="otherSupport" name="otherSupport" placeholder="Enter Other Supporting Person" required>
                </div>
                
                <div class="form-group">
                    <label class="required-field">Are you Boarding?</label>
                    <div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="boardingYes" name="boarding" class="custom-control-input" value="1" required>
                            <label class="custom-control-label" for="boardingYes">Yes</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="boardingNo" name="boarding" class="custom-control-input" value="0" required>
                            <label class="custom-control-label" for="boardingNo">No</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="required-field">With Family?</label>
                    <div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="withFamilyYes" name="withFamily" class="custom-control-input" value="1" required>
                            <label class="custom-control-label" for="withFamilyYes">Yes</label>
                        </div>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" id="withFamilyNo" name="withFamily" class="custom-control-input" value="0" required>
                            <label class="custom-control-label" for="withFamilyNo">No</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="familyAddress" class="required-field">Family Address</label>
                    <textarea class="form-control" id="familyAddress" name="familyAddress" rows="3" placeholder="Enter Family Address" required></textarea>
                </div>
                
                <div class="submit-section">
                    <p class="text-muted mb-3">Please review all information before submitting</p>
                    <button type="submit" class="btn btn-success btn-submit">
                        <i class="fas fa-check-circle"></i> Submit Registration
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade success-modal" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Registration Successful!</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h4>Thank you for registering!</h4>
                    <p>Your registration has been successfully submitted.</p>
                    <div class="id-number">
                        Your ID Number: <strong id="generatedIdNumber"></strong>
                    </div>
                    <p>Please save your ID number for future reference.</p>
                    
                    <?php if(isset($_SESSION['email_sent']) && $_SESSION['email_sent']): ?>
                    <div class="email-notification">
                        <i class="fas fa-envelope"></i> A confirmation email has been sent to your registered email address with your ID number and a link to the student portal.
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <a href="student/slogin.php" class="btn btn-success">
                        <i class="fas fa-home"></i> Login to Your Student Portal
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Required Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(function() {
        // Auto-calculate age when birthdate is entered
        $('#birthDate').on('change', function() {
            const birthDate = new Date($(this).val());
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            $('#age').val(age);
        });
        
        // Form validation before submission
        $('#registrationForm').on('submit', function(e) {
            let isValid = true;
            
            // Check all required inputs
            $('input[required], select[required], textarea[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            // Check radio buttons
            $('input[type="radio"][required]').each(function() {
                const name = $(this).attr('name');
                if (!$('input[name="' + name + '"]:checked').val()) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                // Scroll to the first invalid element
                const firstInvalid = $('.is-invalid').first();
                if(firstInvalid.length){
                    $('html, body').animate({
                        scrollTop: firstInvalid.offset().top - 100
                    }, 500);
                }
            }
        });
        
        // Show success modal if registration was successful
        <?php if(isset($_SESSION['registration_success']) && $_SESSION['registration_success'] === true): ?>
        $(document).ready(function() {
            $('#generatedIdNumber').text('<?=$_SESSION['id_number']?>');
            $('#successModal').modal('show');
            
            // Clear session variables
            <?php 
            unset($_SESSION['registration_success']);
            unset($_SESSION['id_number']);
            unset($_SESSION['email_sent']);
            ?>
        });
        <?php endif; ?>
    });
    </script>
</body>
</html>
```