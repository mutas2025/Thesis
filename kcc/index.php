<?php
// index.php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KCC Grading System - Portal</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- CSS Variables & Reset --- */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-student: #27ae60;
            --accent-admin: #2980b9;
            --bg-light: #f8f9fa;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --white: #ffffff;
            --shadow-sm: 0 4px 6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
            --radius: 12px;
            --transition: all 0.3s ease;
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
            line-height: 1.6;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* --- Header / Navigation --- */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: var(--shadow-sm);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            color: var(--accent-admin);
        }

        .nav-links a {
            margin-left: 2rem;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--accent-admin);
        }

        /* --- Hero Section --- */
        .hero {
            padding: 8rem 2rem 5rem;
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            position: relative;
            overflow: hidden;
        }

        /* Decorative Background Shape */
        .hero::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 50vw;
            height: 50vw;
            background: linear-gradient(45deg, var(--accent-admin), #3498db);
            border-radius: 50%;
            opacity: 0.1;
            z-index: 0;
            filter: blur(80px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            width: 100%;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            line-height: 1.2;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
        }

        .hero-content h1 span {
            color: var(--accent-admin);
            background: -webkit-linear-gradient(45deg, var(--accent-admin), #2ecc71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-content p {
            font-size: 1.125rem;
            color: var(--text-light);
            margin-bottom: 2.5rem;
            max-width: 90%;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            display: inline-block;
            padding: 0.875rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
            cursor: pointer;
            border: none;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(44, 62, 80, 0.3);
        }

        .btn-primary:hover {
            background-color: #1a252f;
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background-color: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        /* --- Role Selection Cards (The Core Functional Area) --- */
        .role-selection {
            background: var(--white);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
        }

        .section-title {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .role-card {
            background: var(--bg-light);
            border-radius: var(--radius);
            padding: 2.5rem 1.5rem;
            text-align: center;
            transition: var(--transition);
            border: 2px solid transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        /* Student Card Specifics */
        .role-card.student {
            border-color: #eafaf1;
        }
        .role-card.student:hover {
            border-color: var(--accent-student);
            background: #eafaf1;
            transform: translateY(-5px);
        }
        .role-card.student .icon-wrapper {
            background: var(--accent-student);
        }

        /* Admin Card Specifics */
        .role-card.admin {
            border-color: #ebf5fb;
        }
        .role-card.admin:hover {
            border-color: var(--accent-admin);
            background: #ebf5fb;
            transform: translateY(-5px);
        }
        .role-card.admin .icon-wrapper {
            background: var(--accent-admin);
        }

        .icon-wrapper {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }

        .role-card h3 {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .role-card p {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 1.5rem;
        }

        .card-btn {
            margin-top: auto;
            padding: 0.5rem 1.5rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition);
        }

        .role-card.student .card-btn {
            background-color: var(--accent-student);
            color: white;
        }
        .role-card.student:hover .card-btn {
            background-color: #219150;
        }

        .role-card.admin .card-btn {
            background-color: var(--accent-admin);
            color: white;
        }
        .role-card.admin:hover .card-btn {
            background-color: #1f6391;
        }

        /* --- Info Section --- */
        .info-section {
            padding: 5rem 2rem;
            background: var(--white);
            text-align: center;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 3rem auto 0;
        }

        .feature-item i {
            font-size: 2.5rem;
            color: var(--accent-admin);
            margin-bottom: 1rem;
        }

        .feature-item h4 {
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .feature-item p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        /* --- Footer --- */
        footer {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 3rem 2rem;
            text-align: center;
        }

        .footer-content p {
            opacity: 0.7;
            font-size: 0.9rem;
        }

        /* --- Responsive Design --- */
        @media (max-width: 992px) {
            .hero-grid {
                grid-template-columns: 1fr;
                gap: 3rem;
                text-align: center;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-content p {
                margin-left: auto;
                margin-right: auto;
            }
        }

        @media (max-width: 600px) {
            .cards-grid {
                grid-template-columns: 1fr;
            }

            .nav-links {
                display: none; /* Simple hide for mobile for this demo */
            }
            
            .hero {
                padding-top: 6rem;
            }
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header>
        <div class="nav-container">
            <div class="logo">
                <i class="fas fa-university"></i>
                <span>KCC Grading System</span>
            </div>
            <nav class="nav-links">
                <a href="#">Home</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
            </nav>
        </div>
    </header>

    <!-- Main Hero Section -->
    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-grid">
                    
                    <!-- Left: Welcome Text -->
                    <div class="hero-content">
                        <h1>Academic Excellence <br><span>Made Simple</span></h1>
                        <p>
                            Welcome to the KCC Grading Portal. A centralized platform designed for students to view their academic progress and for administrators to manage records efficiently.
                        </p>
                        <div class="hero-buttons">
                            <a href="student_viewer.php" class="btn btn-primary">
                                <i class="fas fa-user-graduate"></i> Student Portal
                            </a>
                            <a href="login.php" class="btn btn-outline">
                                <i class="fas fa-lock"></i> Admin Access
                            </a>
                        </div>
                    </div>

                    <!-- Right: Role Selection Card Interface -->
                    <div class="role-selection">
                        <h2 class="section-title">Select Your Role</h2>
                        <div class="cards-grid">
                            
                            <!-- Student Card -->
                            <a href="student_viewer.php" class="role-card student">
                                <div class="icon-wrapper">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <h3>Student</h3>
                                <p>View grades, print records, and track academic performance.</p>
                                <span class="card-btn">View Grades</span>
                            </a>

                            <!-- Admin Card -->
                            <a href="login.php" class="role-card admin">
                                <div class="icon-wrapper">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <h3>Administrator</h3>
                                <p>Manage records, input grades, and oversee system data.</p>
                                <span class="card-btn">Login</span>
                            </a>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Informational / Features Section -->
        <section class="info-section" id="about">
            <div class="container">
                <h2 style="color: var(--primary-color); margin-bottom: 1rem;">Why Use Our Portal?</h2>
                <p style="color: var(--text-light); max-width: 600px; margin: 0 auto;">Designed with security and ease of use in mind for the entire KCC community.</p>
                
                <div class="features-grid">
                    <div class="feature-item">
                        <i class="fas fa-bolt"></i>
                        <h4>Fast Access</h4>
                        <p>Retrieve your grades instantly without long processing times.</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Secure Data</h4>
                        <p>Your academic records are protected with advanced security protocols.</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-mobile-alt"></i>
                        <h4>Mobile Friendly</h4>
                        <p>Access the portal anywhere, anytime, on any device.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="logo" style="justify-content: center; margin-bottom: 1rem; color: white;">
                <i class="fas fa-university"></i>
                <span>KCC Grading System</span>
            </div>
            <p>&copy; <?php echo date("Y"); ?> KCC Institution. All Rights Reserved.</p>
            <p style="font-size: 0.8rem; margin-top: 0.5rem;">
                <a href="#" style="text-decoration: underline;">Privacy Policy</a> | 
                <a href="#" style="text-decoration: underline;">Terms of Service</a> | 
                <a href="#" style="text-decoration: underline;">Support</a>
            </p>
        </div>
    </footer>

</body>
</html>