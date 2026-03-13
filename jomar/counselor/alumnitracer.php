<?php
// Include database connection
include '../config.php';

 $success = false;
 $errors = [];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Sanitize and Collect Data
    $email = trim($_POST['email'] ?? '');
    $family_name = trim($_POST['family_name'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $year_graduated = trim($_POST['year_graduated'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $birthday = trim($_POST['birthday'] ?? '');
    $civil_status = trim($_POST['civil_status'] ?? '');
    $spouse_name = trim($_POST['spouse_name'] ?? '');
    $children_count = trim($_POST['children_count'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    // Handle Array inputs (implode to string for DB)
    // If 'program' is not set or is empty, default to empty string
    $programs = isset($_POST['program']) && is_array($_POST['program']) ? implode(', ', $_POST['program']) : '';
    
    $post_grad = trim($_POST['post_grad'] ?? '');
    
    // Handle 'Other' for Honors
    $honors = trim($_POST['honors'] ?? '');
    $honors_other = trim($_POST['honors_other'] ?? '');
    if (!empty($honors_other)) {
        $honors = $honors_other;
    }
    
    $board_exam = trim($_POST['board_exam'] ?? '');
    $other_schools = trim($_POST['other_schools'] ?? '');

    $occupation = trim($_POST['occupation'] ?? '');
    $company = trim($_POST['company'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $company_address = trim($_POST['company_address'] ?? '');
    $employment_date = trim($_POST['employment_date'] ?? '');
    
    // Handle Salary (Array to string)
    $salary = isset($_POST['salary']) && is_array($_POST['salary']) ? implode(', ', $_POST['salary']) : '';
    
    $prev_company = trim($_POST['prev_company'] ?? '');
    $prev_position = trim($_POST['prev_position'] ?? '');
    $prev_address = trim($_POST['prev_address'] ?? '');
    $employment_time = trim($_POST['employment_time'] ?? '');
    $success_story = trim($_POST['success_story'] ?? '');
    $consent = isset($_POST['consent']) ? 'Agreed' : 'Disagreed';

    // 2. Basic Validation
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($family_name)) $errors[] = "Family Name is required.";
    if (empty($first_name)) $errors[] = "First Name is required.";
    if (empty($year_graduated)) $errors[] = "Year Graduated is required.";
    if (empty($gender)) $errors[] = "Gender is required.";
    if (empty($birthday)) $errors[] = "Birthday is required.";
    if (empty($address)) $errors[] = "Home Address is required.";
    // Note: 'Programs' is checkbox, checking if empty string after implode
    if (empty($programs)) $errors[] = "Please select at least one program.";
    if (empty($company)) $errors[] = "Company Name is required.";
    if (empty($position)) $errors[] = "Position is required.";
    if (empty($company_address)) $errors[] = "Company Address is required.";
    if (empty($employment_date)) $errors[] = "Date of Employment is required.";
    if (empty($success_story)) $errors[] = "Success Story is required.";
    if (!isset($_POST['consent'])) $errors[] = "You must agree to the Data Privacy Consent.";

    // 3. Database Insertion
    if (empty($errors)) {
        // SQL Statement
        $sql = "INSERT INTO graduate_tracer (
            email, family_name, first_name, middle_name, year_graduated, gender, birthday, civil_status, 
            spouse_name, children_count, address, contact, programs, post_grad, honors, board_exam, other_schools, 
            occupation, company, position, company_address, employment_date, salary, 
            prev_company, prev_position, prev_address, employment_time, success_story, consent, submitted_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Type definition string: 
            // FIXED: Changed from 28 's' to 29 's' to match the 29 variables below.
            $stmt->bind_param("sssssssssssssssssssssssssssss", 
                $email,           // 1
                $family_name,     // 2
                $first_name,      // 3
                $middle_name,     // 4
                $year_graduated,  // 5
                $gender,          // 6
                $birthday,        // 7
                $civil_status,    // 8
                $spouse_name,     // 9
                $children_count,  // 10
                $address,         // 11
                $contact,         // 12
                $programs,        // 13 (Array handled above)
                $post_grad,       // 14
                $honors,          // 15
                $board_exam,      // 16
                $other_schools,   // 17
                $occupation,      // 18
                $company,         // 19
                $position,        // 20
                $company_address, // 21
                $employment_date, // 22
                $salary,          // 23 (Array handled above)
                $prev_company,    // 24
                $prev_position,   // 25
                $prev_address,    // 26
                $employment_time, // 27
                $success_story,   // 28
                $consent          // 29 (Note: submitted_at is NOW() in SQL, not bound)
            );

            if ($stmt->execute()) {
                $success = true;
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Database preparation error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Graduate Tracer Survey</title>
  <link rel="icon" href="uploads/csr.png" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg-deep: #f8fafc;
      --bg-surface: #ffffff;
      --bg-card: #ffffff;
      --fg: #1e293b;
      --fg-muted: #64748b;
      --accent: #2563eb;
      --accent-glow: rgba(37, 99, 235, 0.15);
      --accent-soft: #3b82f6;
      --accent-light: #dbeafe;
      --border: #e2e8f0;
      --success: #16a34a;
      --error: #dc2626;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg-deep);
      color: var(--fg);
      min-height: 100vh;
    }

    h1, h2, h3, .display-font { font-family: 'Space Grotesk', sans-serif; }

    .bg-pattern {
      position: fixed;
      inset: 0;
      background-image: 
        radial-gradient(circle at 25% 25%, rgba(37, 99, 235, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, rgba(59, 130, 246, 0.04) 0%, transparent 50%);
      pointer-events: none;
      z-index: 0;
    }

    .bg-grid {
      position: fixed;
      inset: 0;
      background-image: 
        linear-gradient(rgba(37, 99, 235, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(37, 99, 235, 0.04) 1px, transparent 1px);
      background-size: 40px 40px;
      pointer-events: none;
      z-index: 0;
    }

    .form-wrapper {
      position: relative;
      z-index: 1;
      max-width: 820px;
      margin: 0 auto;
      padding: 50px 20px 80px;
    }

    .form-header {
      text-align: center;
      margin-bottom: 40px;
      opacity: 0;
      transform: translateY(30px);
      animation: fadeUp 0.8s ease forwards;
    }

    .form-title {
      font-size: clamp(1.8rem, 5vw, 2.6rem);
      font-weight: 700;
      letter-spacing: -0.02em;
      margin-bottom: 14px;
      color: var(--accent);
    }

    .form-subtitle {
      font-size: 1rem;
      color: var(--fg-muted);
      max-width: 540px;
      margin: 0 auto;
      line-height: 1.7;
    }

    .progress-container { margin-bottom: 40px; opacity: 0; animation: fadeUp 0.8s ease 0.1s forwards; }
    .progress-bar { height: 6px; background: var(--border); border-radius: 3px; overflow: hidden; }
    .progress-fill { height: 100%; background: linear-gradient(90deg, var(--accent), var(--accent-soft)); width: 0%; transition: width 0.5s ease; border-radius: 3px; }
    .progress-text { display: flex; justify-content: space-between; margin-top: 10px; font-size: 0.85rem; color: var(--fg-muted); font-weight: 500; }

    .section-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 32px;
      margin-bottom: 24px;
      position: relative;
      overflow: hidden;
      opacity: 0;
      transform: translateY(30px);
      box-shadow: 0 4px 24px -8px rgba(0, 0, 0, 0.06);
    }

    .section-card.visible { animation: fadeUp 0.6s ease forwards; }
    .section-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--accent), var(--accent-soft));
    }

    .section-title {
      font-size: 1.2rem;
      font-weight: 600;
      margin-bottom: 24px;
      padding-bottom: 14px;
      border-bottom: 1px solid var(--border);
      color: var(--accent);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .section-title svg { color: var(--accent); }

    .field-group { margin-bottom: 22px; opacity: 0; transform: translateY(15px); }
    .field-group.visible { animation: fieldReveal 0.5s ease forwards; }
    
    .field-label {
      display: block;
      font-weight: 600;
      font-size: 0.9rem;
      margin-bottom: 8px;
      color: var(--fg);
    }

    .required-star { color: var(--error); margin-left: 2px; }

    .form-input, .form-select, .form-textarea {
      width: 100%;
      padding: 12px 16px;
      background: var(--bg-surface);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      color: var(--fg);
      font-family: inherit;
      font-size: 0.95rem;
      transition: all 0.25s ease;
      outline: none;
    }

    .form-input:hover, .form-select:hover, .form-textarea:hover { border-color: #cbd5e1; }
    .form-input:focus, .form-select:focus, .form-textarea:focus { 
      border-color: var(--accent); 
      box-shadow: 0 0 0 4px var(--accent-glow); 
    }
    
    .form-input::placeholder, .form-textarea::placeholder { color: var(--fg-muted); opacity: 0.7; }
    
    .form-select {
      cursor: pointer;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 12px center;
      padding-right: 40px;
    }

    .form-textarea { min-height: 110px; resize: vertical; }

    .choice-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 10px;
    }

    .choice-item {
      position: relative;
      display: flex;
      align-items: center;
      padding: 12px 14px;
      background: var(--bg-deep);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 0.9rem;
    }

    .choice-item:hover { 
      border-color: var(--accent-soft); 
      background: var(--accent-light); 
    }
    .choice-item input { position: absolute; opacity: 0; width: 0; height: 0; }
    
    .choice-box {
      width: 20px;
      height: 20px;
      border: 2px solid #cbd5e1;
      border-radius: 5px;
      margin-right: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
      flex-shrink: 0;
      background: white;
    }

    .choice-item input:checked ~ .choice-box { 
      background: var(--accent); 
      border-color: var(--accent); 
    }
    .choice-item input:checked ~ .choice-box::after {
      content: '';
      width: 6px;
      height: 10px;
      border: solid white;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg) translate(-1px, -1px);
    }
    
    .choice-item input[type="radio"] ~ .choice-box { border-radius: 50%; }
    .choice-item input[type="radio"]:checked ~ .choice-box::after {
      width: 10px;
      height: 10px;
      border: none;
      border-radius: 50%;
      background: white;
      transform: none;
    }

    .choice-item input:focus-visible ~ .choice-box { box-shadow: 0 0 0 3px var(--accent-glow); }
    .choice-item input:checked ~ .choice-text { color: var(--fg); font-weight: 500; }
    .choice-text { color: var(--fg-muted); transition: all 0.2s ease; }

    .consent-box {
      background: var(--accent-light);
      border: 1px solid rgba(37, 99, 235, 0.2);
      border-radius: 12px;
      padding: 20px;
      font-size: 0.9rem;
      line-height: 1.7;
      color: var(--fg-muted);
    }

    .submit-btn {
      width: 100%;
      padding: 16px;
      background: linear-gradient(135deg, var(--accent) 0%, var(--accent-soft) 100%);
      border: none;
      border-radius: 12px;
      color: white;
      font-family: 'Space Grotesk', sans-serif;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .submit-btn:hover { 
      transform: translateY(-2px); 
      box-shadow: 0 12px 28px -8px rgba(37, 99, 235, 0.35); 
    }
    .submit-btn:active { transform: translateY(0); }
    .submit-btn.loading { pointer-events: none; }
    .submit-btn .spinner { display: none; width: 20px; height: 20px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto; }
    .submit-btn.loading .btn-text { visibility: hidden; }
    .submit-btn.loading .spinner { display: block; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); }

    .success-overlay {
      position: fixed;
      inset: 0;
      background: rgba(255, 255, 255, 0.98);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 100;
      opacity: 0;
      visibility: hidden;
      transition: all 0.4s ease;
    }
    .success-overlay.show { opacity: 1; visibility: visible; }
    .success-content { text-align: center; transform: scale(0.9); transition: transform 0.4s ease; }
    .success-overlay.show .success-content { transform: scale(1); }
    .success-icon { 
      width: 80px; 
      height: 80px; 
      background: linear-gradient(135deg, var(--success), #22c55e); 
      border-radius: 50%; 
      display: flex; 
      align-items: center; 
      justify-content: center; 
      margin: 0 auto 24px;
      box-shadow: 0 12px 32px -8px rgba(22, 163, 74, 0.4);
    }
    .success-title { font-size: 1.6rem; margin-bottom: 10px; color: var(--fg); }
    .success-desc { color: var(--fg-muted); margin-bottom: 28px; }

    .error-box {
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 12px;
      padding: 16px 20px;
      margin-bottom: 24px;
      opacity: 0;
      animation: fadeUp 0.4s ease forwards;
    }
    .error-box h4 { color: var(--error); font-weight: 600; margin-bottom: 8px; font-size: 0.95rem; }
    .error-box ul { margin-left: 20px; color: #991b1b; font-size: 0.9rem; }
    .error-box li { margin-bottom: 4px; }

    @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
    @keyframes fieldReveal { to { opacity: 1; transform: translateY(0); } }
    @keyframes spin { to { transform: translate(-50%, -50%) rotate(360deg); } }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
    }

    @media (max-width: 640px) {
      .form-wrapper { padding: 24px 16px 60px; }
      .section-card { padding: 20px 16px; }
      .choice-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <div class="bg-pattern"></div>
  <div class="bg-grid"></div>

  <div class="form-wrapper">
    <header class="form-header">
      <h1 class="form-title display-font">Graduate Tracer Survey</h1>
      <p class="form-subtitle">
        The Guidance and Counseling Services of Colegio de Santa Rita de San Carlos, Inc. would like to know the whereabouts of our graduates and their employability status. Your answers will be treated with utmost confidentiality.
      </p>
    </header>

    <div class="progress-container">
      <div class="progress-bar"><div class="progress-fill" id="progressFill"></div></div>
      <div class="progress-text"><span id="progressLabel">Start filling the form</span><span id="progressPercent">0%</span></div>
    </div>

    <?php if ($success): ?>
    <div class="success-overlay show" id="successOverlay">
      <div class="success-content">
        <div class="success-icon">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
        <h2 class="success-title display-font">Response Recorded</h2>
        <p class="success-desc">Thank you for completing the Graduate Tracer Survey.</p>
        <button onclick="window.location.href=window.location.pathname;" class="submit-btn" style="width: auto; padding: 14px 36px;">Submit another response</button>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
    <div class="error-box">
      <h4>Please correct the following errors:</h4>
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?php echo $error; ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <form id="tracerForm" method="POST" action="">
      <!-- Personal Data Section -->
      <section class="section-card" data-section="personal">
        <h2 class="section-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
          Personal Data
        </h2>

        <div class="field-group">
          <label class="field-label">Email <span class="required-star">*</span></label>
          <input type="email" class="form-input" name="email" required placeholder="example@email.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        <div class="field-group">
          <label class="field-label">Family Name <span class="required-star">*</span></label>
          <input type="text" class="form-input" name="family_name" required placeholder="Dela Cruz" value="<?php echo htmlspecialchars($_POST['family_name'] ?? ''); ?>">
        </div>
        <div class="field-group">
          <label class="field-label">First Name <span class="required-star">*</span></label>
          <input type="text" class="form-input" name="first_name" required placeholder="Juan" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
        </div>
        <div class="field-group">
          <label class="field-label">Middle Name</label>
          <input type="text" class="form-input" name="middle_name" placeholder="Santos" value="<?php echo htmlspecialchars($_POST['middle_name'] ?? ''); ?>">
        </div>
        <div class="field-group">
          <label class="field-label">Year Graduated <span class="required-star">*</span></label>
          <input type="text" class="form-input" name="year_graduated" required placeholder="2020" value="<?php echo htmlspecialchars($_POST['year_graduated'] ?? ''); ?>">
        </div>
        
        <div class="field-group">
          <label class="field-label">Gender <span class="required-star">*</span></label>
          <div class="choice-grid">
            <label class="choice-item">
              <input type="radio" name="gender" value="Male" required <?php echo (($_POST['gender'] ?? '') === 'Male') ? 'checked' : ''; ?>>
              <span class="choice-box"></span><span class="choice-text">Male</span>
            </label>
            <label class="choice-item">
              <input type="radio" name="gender" value="Female" <?php echo (($_POST['gender'] ?? '') === 'Female') ? 'checked' : ''; ?>>
              <span class="choice-box"></span><span class="choice-text">Female</span>
            </label>
            <label class="choice-item">
              <input type="radio" name="gender" value="Prefer not to say" <?php echo (($_POST['gender'] ?? '') === 'Prefer not to say') ? 'checked' : ''; ?>>
              <span class="choice-box"></span><span class="choice-text">Prefer not to say</span>
            </label>
          </div>
        </div>

        <div class="field-group">
          <label class="field-label">Birthday <span class="required-star">*</span></label>
          <input type="date" class="form-input" name="birthday" required value="<?php echo htmlspecialchars($_POST['birthday'] ?? ''); ?>">
        </div>
        
        <div class="field-group">
          <label class="field-label">Civil Status</label>
          <div class="choice-grid">
            <label class="choice-item">
              <input type="radio" name="civil_status" value="Single" <?php echo (($_POST['civil_status'] ?? '') === 'Single') ? 'checked' : ''; ?>>
              <span class="choice-box"></span><span class="choice-text">Single</span>
            </label>
            <label class="choice-item">
              <input type="radio" name="civil_status" value="Married" <?php echo (($_POST['civil_status'] ?? '') === 'Married') ? 'checked' : ''; ?>>
              <span class="choice-box"></span><span class="choice-text">Married</span>
            </label>
          </div>
        </div>

        <div class="field-group hidden-if-single" style="display:<?php echo (($_POST['civil_status'] ?? '') === 'Married') ? 'block' : 'none'; ?>;">
          <label class="field-label">Name of Spouse</label>
          <input type="text" class="form-input" name="spouse_name" placeholder="Spouse Name" value="<?php echo htmlspecialchars($_POST['spouse_name'] ?? ''); ?>">
        </div>
        <div class="field-group hidden-if-single" style="display:<?php echo (($_POST['civil_status'] ?? '') === 'Married') ? 'block' : 'none'; ?>;">
          <label class="field-label">Number of Children</label>
          <input type="number" class="form-input" name="children_count" placeholder="0" value="<?php echo htmlspecialchars($_POST['children_count'] ?? ''); ?>">
        </div>

        <div class="field-group">
          <label class="field-label">Home Address <span class="required-star">*</span></label>
          <input type="text" class="form-input" name="address" required placeholder="Street, City, Province" value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
        </div>
        <div class="field-group">
          <label class="field-label">Contact Number/s</label>
          <input type="tel" class="form-input" name="contact" placeholder="+63 912 345 6789" value="<?php echo htmlspecialchars($_POST['contact'] ?? ''); ?>">
        </div>
      </section>

      <!-- Educational Background Section -->
      <section class="section-card" data-section="education">
        <h2 class="section-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
          Educational Background
        </h2>

        <div class="field-group">
          <label class="field-label">Program / Level Completed <span class="required-star">*</span></label>
          <p style="font-size:0.85rem; color:var(--fg-muted); margin-bottom:10px;">Check all that apply.</p>
          <div class="choice-grid">
            <?php 
            $programs_list = ['Kindergarten', 'Grade 6', 'Grade 10', 'Grade 12', 'BEED', 'BSED', 'BSBA', 'BSIT', 'BSTM', 'MIDWIFERY', 'Nursing Aide', 'CAREGIVING', 'BSCS', 'BSA', 'BSC', 'AB'];
            $selected_programs = $_POST['program'] ?? [];
            foreach ($programs_list as $prog): 
            ?>
            <label class="choice-item">
              <input type="checkbox" name="program[]" value="<?php echo $prog; ?>" <?php echo in_array($prog, $selected_programs) ? 'checked' : ''; ?>>
              <span class="choice-box"></span><span class="choice-text"><?php echo $prog; ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="field-group">
          <label class="field-label">Post Graduate Studies</label>
          <p style="font-size:0.85rem; color:var(--fg-muted); margin-bottom:10px;">Mark only one oval.</p>
          <div class="choice-grid" style="grid-template-columns: 1fr;">
            <?php 
            $post_grads = [
              'MAED - Educational Management',
              'MAED - Administration and Supervision',
              'MAED - Guidance and Counseling'
            ];
            foreach ($post_grads as $pg):
            ?>
            <label class="choice-item">
              <input type="radio" name="post_grad" value="<?php echo $pg; ?>" <?php echo (($_POST['post_grad'] ?? '') === $pg) ? 'checked' : ''; ?>>
              <span class="choice-box"></span><span class="choice-text"><?php echo $pg; ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="field-group">
          <label class="field-label">Honor/s Received or Awards <span class="required-star">*</span></label>
          <div class="choice-grid" style="grid-template-columns: 1fr 1fr;">
            <?php 
            $honors_list = ['Magna Cum Laude', 'Cum Laude', 'Academic Distinction', 'Loyalty Award'];
            foreach ($honors_list as $h):
            ?>
            <label class="choice-item">
              <input type="radio" name="honors" value="<?php echo $h; ?>" required <?php echo (($_POST['honors'] ?? '') === $h) ? 'checked' : ''; ?>>
              <span class="choice-box"></span><span class="choice-text"><?php echo $h; ?></span>
            </label>
            <?php endforeach; ?>
          </div>
          <input type="text" class="form-input" name="honors_other" placeholder="Other (please specify)" style="margin-top:10px;" value="<?php echo htmlspecialchars($_POST['honors_other'] ?? ''); ?>">
        </div>

        <div class="field-group">
          <label class="field-label">Professional Board Examination/Assessment Passed</label>
          <input type="text" class="form-input" name="board_exam" placeholder="e.g., LET, CPA, Nursing Board" value="<?php echo htmlspecialchars($_POST['board_exam'] ?? ''); ?>">
        </div>
        <div class="field-group">
          <label class="field-label">Other Schools Attended after Graduation</label>
          <input type="text" class="form-input" name="other_schools" placeholder="Name of School / Studies Pursued" value="<?php echo htmlspecialchars($_POST['other_schools'] ?? ''); ?>">
        </div>
      </section>

      <!-- Employment Report Section -->
      <section class="section-card" data-section="employment">
        <h2 class="section-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
          Employment Report
        </h2>

        <div class="field-group">
          <label class="field-label">Present Occupation</label>
          <input type="text" class="form-input" name="occupation" placeholder="Current Job Title" value="<?php echo htmlspecialchars($_POST['occupation'] ?? ''); ?>">
        </div>
        <div class="field-group">
          <label class="field-label">Name of Company / Organization <span class="required-star">*</span></label>
          <input type="text" class="form-input" name="company" required placeholder="Company Name" value="<?php echo htmlspecialchars($_POST['company'] ?? ''); ?>">
        </div>
        <div class="field-group">
          <label class="field-label">Position / Designation <span class="required-star">*</span></label>
          <input type="text" class="form-input" name="position" required placeholder="Position" value="<?php echo htmlspecialchars($_POST['position'] ?? ''); ?>">
        </div>
        <div class="field-group">
          <label class="field-label">Company Address <span class="required-star">*</span></label>
          <input type="text" class="form-input" name="company_address" required placeholder="Company Location" value="<?php echo htmlspecialchars($_POST['company_address'] ?? ''); ?>">
        </div>
        <div class="field-group">
          <label class="field-label">Date of Employment <span class="required-star">*</span></label>
          <input type="date" class="form-input" name="employment_date" required value="<?php echo htmlspecialchars($_POST['employment_date'] ?? ''); ?>">
        </div>

        <div class="field-group">
          <label class="field-label">Salary <span class="required-star">*</span></label>
          <div class="choice-grid" style="grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));">
            <?php 
            $salaries = ['10,000 and below', '10,000 - 15,000', '15,000-20,000', '20,000-25,000', '25,000-30,000', '30,000-35,000', '35,000-40,000', '40,000-50,000'];
            $selected_salaries = $_POST['salary'] ?? [];
            $salary_labels = ['10k & below', '10k-15k', '15k-20k', '20k-25k', '25k-30k', '30k-35k', '35k-40k', '40k-50k'];
            foreach ($salaries as $i => $sal):
            ?>
            <label class="choice-item">
              <input type="checkbox" name="salary[]" value="<?php echo $sal; ?>" <?php echo in_array($sal, $selected_salaries) ? 'checked' : ''; ?>>
              <span class="choice-box"></span><span class="choice-text"><?php echo $salary_labels[$i]; ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="field-group">
          <label class="field-label">Previous Employment</label>
          <input type="text" class="form-input" name="prev_company" placeholder="Name of Company / Organization" style="margin-bottom:8px;" value="<?php echo htmlspecialchars($_POST['prev_company'] ?? ''); ?>">
          <input type="text" class="form-input" name="prev_position" placeholder="Position / Designation" style="margin-bottom:8px;" value="<?php echo htmlspecialchars($_POST['prev_position'] ?? ''); ?>">
          <input type="text" class="form-input" name="prev_address" placeholder="Company Address" value="<?php echo htmlspecialchars($_POST['prev_address'] ?? ''); ?>">
        </div>

        <div class="field-group">
          <label class="field-label">How long did it take before you were employed after graduation? <span class="required-star">*</span></label>
          <div class="choice-grid" style="grid-template-columns: 1fr 1fr;">
            <?php 
            $emp_times = ['Less than 1 month', '1–3 months', '4–6 months', '7–12 months', 'More than 1 year', 'N/A'];
            $emp_labels = ['< 1 month', '1–3 months', '4–6 months', '7–12 months', '> 1 year', 'N/A'];
            foreach ($emp_times as $i => $et):
            ?>
            <label class="choice-item">
              <input type="radio" name="employment_time" value="<?php echo $et; ?>" required <?php echo (($_POST['employment_time'] ?? '') === $et) ? 'checked' : ''; ?>>
              <span class="choice-box"></span><span class="choice-text"><?php echo $emp_labels[$i]; ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <!-- Success Story & Consent -->
      <section class="section-card" data-section="final">
        <h2 class="section-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
          Success Story & Consent
        </h2>

        <div class="field-group">
          <label class="field-label">SUCCESS STORY <span class="required-star">*</span></label>
          <p style="font-size:0.85rem; color:var(--fg-muted); margin-bottom:10px;">Share your inspiring stories, lessons learned, improvements made, and overall growth experience.</p>
          <textarea class="form-textarea" name="success_story" required placeholder="Write your success story here..."><?php echo htmlspecialchars($_POST['success_story'] ?? ''); ?></textarea>
        </div>

        <div class="field-group">
          <label class="field-label">DATA PRIVACY CONSENT <span class="required-star">*</span></label>
          <div class="consent-box">
            <label class="choice-item" style="background: transparent; border: none; padding: 0; align-items: flex-start; margin-bottom: 12px;">
              <input type="checkbox" name="consent" required <?php echo isset($_POST['consent']) ? 'checked' : ''; ?>>
              <span class="choice-box" style="margin-right: 12px; flex-shrink: 0;"></span>
              <span class="choice-text" style="color: var(--fg-muted); font-size: 0.9rem; line-height: 1.6;">
                In compliance with RA 10173, the Data Privacy Act of 2012, I hereby authorize Colegio de Santa Rita de San Carlos, Inc. to collect, use, and process the above information solely for educational purposes.
              </span>
            </label>
            <p style="font-size: 0.85rem; color: var(--fg-muted); opacity: 0.8;">
              All information contained in this form is confidential and shall be processed by authorized personnel in accordance with its data privacy policies.
            </p>
          </div>
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">
          <span class="btn-text">Submit Response</span>
          <div class="spinner"></div>
        </button>
      </section>
    </form>
  </div>

  <script>
    // Elements
    const form = document.getElementById('tracerForm');
    const progressFill = document.getElementById('progressFill');
    const progressLabel = document.getElementById('progressLabel');
    const progressPercent = document.getElementById('progressPercent');
    const submitBtn = document.getElementById('submitBtn');

    // Required fields count for progress
    const totalRequired = document.querySelectorAll('[required]').length;

    // Intersection Observer for Section Reveal
    const sections = document.querySelectorAll('.section-card');
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          
          // Reveal child fields with stagger
          const fields = entry.target.querySelectorAll('.field-group');
          fields.forEach((field, i) => {
            setTimeout(() => field.classList.add('visible'), i * 60);
          });
        }
      });
    }, { threshold: 0.1 });

    sections.forEach(section => observer.observe(section));

    // Logic: Show Spouse/Children fields only if Married
    const civilStatusInputs = document.querySelectorAll('input[name="civil_status"]');
    const hiddenFamilyFields = document.querySelectorAll('.hidden-if-single');

    civilStatusInputs.forEach(input => {
      input.addEventListener('change', (e) => {
        hiddenFamilyFields.forEach(field => {
          field.style.display = (e.target.value === 'Married') ? 'block' : 'none';
        });
      });
    });

    // Progress Bar Update
    function updateProgress() {
      let uniqueFilled = 0;
      let checkedGroups = [];
      const requiredInputs = form.querySelectorAll('[required]');
      
      requiredInputs.forEach(input => {
        if (input.type === 'radio' || input.type === 'checkbox') {
           if (!checkedGroups.includes(input.name) && form.querySelector(`input[name="${input.name}"]:checked`)) {
             uniqueFilled++;
             checkedGroups.push(input.name);
           }
        } else if (input.value.trim() !== '') {
          uniqueFilled++;
        }
      });

      const percent = Math.round((uniqueFilled / totalRequired) * 100);
      progressFill.style.width = `${percent}%`;
      progressPercent.textContent = `${percent}%`;
      progressLabel.textContent = percent < 100 ? `${uniqueFilled} of ${totalRequired} required fields filled` : 'All required fields filled';
    }

    form.addEventListener('input', updateProgress);
    form.addEventListener('change', updateProgress);
    
    // Initial progress check
    setTimeout(updateProgress, 300);

    // Form Submit Animation
    form.addEventListener('submit', function(e) {
      // Allow default submission but show loading state
      submitBtn.classList.add('loading');
      // Note: The page will reload due to POST, so the loading state is visual feedback before the refresh
    });
  </script>
</body>
</html>