<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= SCHOOL_NAME ?> - Leading Education">
    <meta name="keywords" content="Colegio De Santa Rita De San Carlos, Inc.">
    <title><?= SCHOOL_NAME ?> | Portals</title>
    <link rel="icon" href="csr.png" type="image/x-icon">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Poppins', sans-serif;
        /* Blue Theme Gradient */
        background: linear-gradient(135deg, rgba(21, 67, 96, 0.95), rgba(1, 87, 155, 0.95)), 
                    url('uploads/csrschool.png') center/cover no-repeat;
        min-height: 100vh;
        color: #ffffff;
        overflow-x: hidden;
    }
    
    /* Header & Navigation */
    header {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        padding: 15px 0;
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 1000;
        /* Blue Border */
        border-bottom: 2px solid rgba(1, 87, 155, 0.2);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
    .header-container {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
    }
    
    .logo-container {
        display: flex;
        align-items: center;
    }
    
    .logo {
        height: 50px;
        border-radius: 50%;
        margin-right: 15px;
        box-shadow: 0 2px 10px rgba(1, 87, 155, 0.2);
    }
    
    .school-name {
        font-size: 1.1rem;
        font-weight: 600;
        /* Dark Blue Text */
        color: #0D47A1;
        line-height: 1.3;
    }
    
    .college-name {
        font-size: 0.9rem;
        /* Light Blue Accent */
        color: #0277BD;
        font-weight: 500;
    }
    
    nav ul {
        display: flex;
        list-style: none;
    }
    
    nav ul li {
        margin-left: 25px;
    }
    
    nav ul li a {
        /* Dark Blue Links */
        color: #0D47A1;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
        padding: 8px 16px;
        border-radius: 25px;
        position: relative;
    }
    
    nav ul li a:hover {
        background: rgba(13, 71, 161, 0.1);
        color: #01579B;
    }
    
    nav ul li a.active {
        /* Blue Gradient Button */
        background: linear-gradient(90deg, #1565C0, #0277BD);
        color: white;
    }
    
    /* Mobile Menu Toggle */
    .menu-toggle {
        display: none;
        flex-direction: column;
        justify-content: space-between;
        width: 30px;
        height: 21px;
        cursor: pointer;
    }
    
    .menu-toggle span {
        display: block;
        height: 3px;
        width: 100%;
        background-color: #0D47A1;
        border-radius: 3px;
        transition: all 0.3s ease;
    }
    
    /* Hero Section */
    .hero {
        padding: 150px 20px 80px;
        text-align: center;
        position: relative;
    }
    
    .hero::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at center, rgba(21, 101, 192, 0.1) 0%, rgba(1, 87, 155, 0.1) 70%);
        pointer-events: none;
    }
    
    .hero h1 {
        font-size: 3.5rem;
        margin-bottom: 20px;
        font-weight: 700;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        color: #ffffff;
        position: relative;
        z-index: 1;
    }
    
    .hero .subtitle {
        font-size: 1.8rem;
        margin-bottom: 20px;
        font-weight: 400;
        color: #BBDEFB;
    }
    
    .hero p {
        font-size: 1.2rem;
        max-width: 700px;
        margin: 0 auto 40px;
        font-weight: 300;
        line-height: 1.6;
        color: #E3F2FD;
        position: relative;
        z-index: 1;
    }
    
    .tech-icons {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-top: 40px;
        flex-wrap: wrap;
    }
    
    .tech-icon {
        font-size: 2.5rem;
        color: rgba(255, 255, 255, 0.8);
        transition: all 0.3s ease;
    }
    
    .tech-icon:hover {
        color: #ffffff;
        transform: translateY(-5px) scale(1.1);
    }
    
    /* Portals Section */
    .portals {
        padding: 80px 20px;
        max-width: 1200px;
        margin: 0 auto;
        text-align: center;
    }
    
    .section-title {
        font-size: 2.8rem;
        text-align: center;
        margin-bottom: 20px;
        position: relative;
        display: inline-block;
        width: 100%;
        color: #ffffff;
        font-weight: 700;
    }
    
    .section-title:after {
        content: "";
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 4px;
        /* Blue Underline */
        background: linear-gradient(90deg, #1565C0, #0277BD);
        border-radius: 2px;
    }
    
    .portals-description {
        color: #E3F2FD;
        font-size: 1.1rem;
        margin-bottom: 50px;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.7;
    }
    
    .portal-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-top: 50px;
    }
    
    .portal-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 40px 30px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .portal-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #1565C0, #0277BD);
        transform: translateX(-100%);
        transition: transform 0.6s ease;
    }
    
    .portal-card:hover::before {
        transform: translateX(0);
    }
    
    .portal-card:hover {
        transform: translateY(-15px);
        box-shadow: 0 20px 40px rgba(13, 71, 161, 0.25);
    }
    
    .portal-icon-wrapper {
        width: 80px;
        height: 80px;
        margin: 0 auto 25px;
        background: linear-gradient(135deg, #E3F2FD, #B3E5FC);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .portal-card:hover .portal-icon-wrapper {
        transform: scale(1.1) rotate(5deg);
        background: linear-gradient(135deg, #1565C0, #0277BD);
    }
    
    .portal-icon {
        font-size: 2.5rem;
        color: #1565C0;
        transition: all 0.3s ease;
    }
    
    .portal-card:hover .portal-icon {
        color: #ffffff;
    }
    
    .portal-card h3 {
        font-size: 1.5rem;
        margin-bottom: 15px;
        color: #0D47A1;
        font-weight: 600;
    }
    
    .portal-card p {
        margin-bottom: 25px;
        line-height: 1.6;
        color: #616161;
        font-size: 0.95rem;
    }
    
    .portal-btn {
        background: linear-gradient(135deg, #1565C0, #0277BD);
        color: white;
        border: none;
        padding: 14px 32px;
        border-radius: 30px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        font-size: 1rem;
        box-shadow: 0 4px 15px rgba(21, 101, 192, 0.3);
    }
    
    .portal-btn:hover {
        background: linear-gradient(135deg, #1976D2, #0288D1);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(21, 101, 192, 0.4);
    }

    /* Coming Soon Overlay Styles */
    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .overlay.active {
        display: flex;
        opacity: 1;
    }

    .coming-soon-box {
        background: white;
        padding: 50px;
        border-radius: 20px;
        text-align: center;
        max-width: 500px;
        width: 90%;
        position: relative;
        transform: translateY(20px);
        transition: transform 0.3s ease;
        box-shadow: 0 20px 50px rgba(0,0,0,0.3);
    }

    .overlay.active .coming-soon-box {
        transform: translateY(0);
    }

    .coming-soon-box i {
        font-size: 4rem;
        color: #0277BD;
        margin-bottom: 20px;
    }

    .coming-soon-box h2 {
        color: #0D47A1;
        font-size: 2rem;
        margin-bottom: 10px;
    }

    .coming-soon-box p {
        color: #666;
        margin-bottom: 30px;
        line-height: 1.6;
    }

    .close-btn {
        background: #e0e0e0;
        color: #333;
        border: none;
        padding: 10px 25px;
        border-radius: 25px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .close-btn:hover {
        background: #d6d6d6;
        transform: scale(1.05);
    }
    
    /* Quick Links Section */
    .quick-links {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        padding: 40px 20px;
        margin: 60px 0;
        border-radius: 20px;
        max-width: 1000px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .quick-links h3 {
        color: #ffffff;
        margin-bottom: 25px;
        font-size: 1.8rem;
    }
    
    .links-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .link-item {
        background: rgba(255, 255, 255, 0.9);
        padding: 15px 20px;
        border-radius: 12px;
        text-align: center;
        transition: all 0.3s ease;
        text-decoration: none;
        color: #0D47A1;
        font-weight: 500;
    }
    
    .link-item:hover {
        background: #ffffff;
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        color: #0277BD;
    }
    
    /* Footer */
    footer {
        /* Blue Footer Gradient */
        background: linear-gradient(90deg, #0D47A1, #01579B);
        padding: 50px 20px 30px;
        text-align: center;
        color: #ffffff;
        margin-top: 80px;
    }
    
    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .footer-logo {
        margin-bottom: 20px;
    }
    
    .footer-logo img {
        height: 60px;
        border-radius: 50%;
        border: 3px solid #ffffff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .footer-links {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }
    
    .footer-links a {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        transition: all 0.3s ease;
        padding: 8px 16px;
        border-radius: 20px;
    }
    
    .footer-links a:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }
    
    .social-media {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .social-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
        transition: all 0.3s ease;
    }
    
    .social-icon:hover {
        background: #ffffff;
        color: #0D47A1;
        transform: translateY(-5px) scale(1.1);
    }
    
    .footer-contact {
        margin-bottom: 20px;
        font-size: 0.95rem;
    }

    .footer-contact p {
        margin: 5px 0;
    }

    .copyright {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    /* Responsive Design */
    @media (max-width: 992px) {
        .hero h1 {
            font-size: 2.8rem;
        }
        
        .hero .subtitle {
            font-size: 1.5rem;
        }
        
        .hero p {
            font-size: 1.1rem;
        }
    }
    
    @media (max-width: 768px) {
        header {
            padding: 10px 0;
        }
        
        .header-container {
            flex-direction: column;
            gap: 15px;
        }
        
        .menu-toggle {
            display: flex;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        nav {
            width: 100%;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        nav.active {
            max-height: 300px;
        }
        
        nav ul {
            flex-direction: column;
            width: 100%;
            text-align: center;
            padding: 10px 0;
        }
        
        nav ul li {
            margin: 10px 0;
        }
        
        nav ul li a {
            display: block;
            padding: 10px 0;
            width: 100%;
        }
        
        .hero {
            padding: 120px 20px 60px;
        }
        
        .hero h1 {
            font-size: 2.2rem;
        }
        
        .hero .subtitle {
            font-size: 1.3rem;
        }
        
        .portals {
            padding: 60px 20px;
        }
        
        .section-title {
            font-size: 2.2rem;
        }
        
        .portal-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .portal-card {
            max-width: 400px;
            margin: 0 auto;
        }
    }
    
    @media (max-width: 576px) {
        .hero h1 {
            font-size: 1.8rem;
        }
        
        .hero .subtitle {
            font-size: 1.1rem;
        }
        
        .hero p {
            font-size: 1rem;
        }
        
        .tech-icons {
            gap: 20px;
        }
        
        .tech-icon {
            font-size: 2rem;
        }
        
        .section-title {
            font-size: 1.9rem;
        }
        
        .portal-btn {
            padding: 12px 28px;
            width: 100%;
        }
        
        .footer-links {
            flex-direction: column;
            gap: 10px;
        }
        
        .links-grid {
            grid-template-columns: 1fr;
        }
    }
    
    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .portal-card {
        animation: fadeInUp 0.6s ease forwards;
    }
    
    .portal-card:nth-child(1) { animation-delay: 0.1s; }
    .portal-card:nth-child(2) { animation-delay: 0.2s; }
    .portal-card:nth-child(3) { animation-delay: 0.3s; }
    .portal-card:nth-child(4) { animation-delay: 0.4s; }
    .portal-card:nth-child(5) { animation-delay: 0.5s; }
    .portal-card:nth-child(6) { animation-delay: 0.6s; }
</style>
</head>
<body>
    <!-- Coming Soon Overlay -->
    <div class="overlay" id="comingSoonOverlay">
        <div class="coming-soon-box">
            <i class="fas fa-hammer"></i>
            <h2>Coming Soon</h2>
            <p>The Guidance Portal is currently under development. Please check back later for updates.</p>
            <button class="close-btn" onclick="closeOverlay()">Close</button>
        </div>
    </div>

    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="csr.png" alt="School Logo" class="logo">
                <div>
                    <div class="school-name"><?= SCHOOL_NAME ?></div>
                    <div class="college-name">Excellence in Education</div>
                </div>
            </div>
            <div class="menu-toggle" id="menuToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <nav id="navMenu">
                <ul>
                    <li><a href="#home" class="active">Home</a></li>
                    <li><a href="#portals">Portals</a></li>
                    <li><a href="#quick-links">Quick Links</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <h1><?= SCHOOL_NAME ?></h1>
        <div class="subtitle">Empowering Future Leaders</div>
        <p>Academic Excellence | Values Formation | Community Service</p>
        <div class="tech-icons">
            <i class="fas fa-book-reader tech-icon"></i>
            <i class="fas fa-graduation-cap tech-icon"></i>
            <i class="fas fa-school tech-icon"></i>
            <i class="fas fa-laptop-code tech-icon"></i>
            <i class="fas fa-users tech-icon"></i>
            <i class="fas fa-award tech-icon"></i>
        </div>
    </section>

    <!-- Portals Section -->
    <section class="portals" id="portals">
        <h2 class="section-title">Access Portals</h2>
        <p class="portals-description">
            Access your personalized dashboard for academic resources, grades, and school services.
        </p>
        
        <div class="portal-grid">
            <div class="portal-card">
                <div class="portal-icon-wrapper">
                    <i class="fas fa-user-shield portal-icon"></i>
                </div>
                <h3>Admin Portal</h3>
                <p>System administration, user management, and institutional analytics dashboard.</p>
                <a href="login.php" class="portal-btn">
                    <i class="fas fa-sign-in-alt"></i> Admin Login
                </a>
            </div>
            
            <div class="portal-card">
                <div class="portal-icon-wrapper">
                    <i class="fas fa-chalkboard-teacher portal-icon"></i>
                </div>
                <h3>Faculty Portal</h3>
                <p>Manage classes, grade submissions, course materials, and student progress tracking.</p>
                <a href="teacher/tlogin.php" class="portal-btn">
                    <i class="fas fa-sign-in-alt"></i> Faculty Login
                </a>
            </div>
            
            <div class="portal-card">
                <div class="portal-icon-wrapper">
                    <i class="fas fa-user-graduate portal-icon"></i>
                </div>
                <h3>Student Portal</h3>
                <p>Access grades, schedules, online learning resources, and campus services.</p>
                <a href="student/slogin.php" class="portal-btn">
                    <i class="fas fa-sign-in-alt"></i> Student Login
                </a>
            </div>
            
            <div class="portal-card">
                <div class="portal-icon-wrapper">
                    <i class="fas fa-user-tie portal-icon"></i>
                </div>
                <h3>Department Head Portal</h3>
                <p>Department management, curriculum oversight, faculty evaluation, and reporting tools.</p>
                <a href="programhead/login.php" class="portal-btn">
                    <i class="fas fa-sign-in-alt"></i> Department Login
                </a>
            </div>

            <!-- Registration Portal Card -->
            <div class="portal-card">
                <div class="portal-icon-wrapper">
                    <i class="fas fa-user-plus portal-icon"></i>
                </div>
                <h3>New Registration</h3>
                <p>Create a new account to access the student portal and enrollment services.</p>
                <a href="registration_form.php" class="portal-btn">
                    <i class="fas fa-user-plus"></i> Register Now
                </a>
            </div>

            <!-- Guidance Portal Card (New) -->
            <div class="portal-card">
                <div class="portal-icon-wrapper">
                    <i class="fas fa-heartbeat portal-icon"></i>
                </div>
                <h3>Alumni Tracer</h3>
                <p>Hear Stories from alumni and track their status</p>
                <a href="./counselor/alumnitracer.php" class="portal-btn">
                    <i class="fas fa-user-plus"></i> Tell Us Now!
                </a>
            </div>
        </div>
    </section>

    <!-- Quick Links Section -->
    <section class="quick-links" id="quick-links">
        <h3>Quick Links</h3>
        <div class="links-grid">
            <a href="#" class="link-item">
                <i class="fas fa-book"></i> Course Catalog
            </a>
            <a href="#" class="link-item">
                <i class="fas fa-calendar-alt"></i> Academic Calendar
            </a>
            <a href="#" class="link-item">
                <i class="fas fa-download"></i> Downloads
            </a>
            <a href="#" class="link-item">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>
            <a href="#" class="link-item">
                <i class="fas fa-envelope"></i> Email Access
            </a>
            <a href="#" class="link-item">
                <i class="fas fa-laptop"></i> Online Learning
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-logo">
                <img src="csr.png" alt="School Logo">
            </div>
            
            <!-- Added dynamic contact details -->
            <div class="footer-contact">
                <p><strong><?= SCHOOL_NAME ?></strong></p>
                <p><i class="fas fa-map-marker-alt"></i> <?= SCHOOL_ADDRESS ?></p>
                <p><i class="fas fa-phone"></i> <?= SCHOOL_CONTACT_NO ?> | <i class="fas fa-envelope"></i> <?= SCHOOL_EMAIL ?></p>
            </div>

            <div class="footer-links">
                <a href="#home">Home</a>
                <a href="#portals">Portals</a>
                <a href="#quick-links">Quick Links</a>
                <a href="#contact">Contact</a>
            </div>
            <div class="social-media">
                <a href="https://www.facebook.com/profile.php?id=100095237395235" class="social-icon">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="social-icon">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="social-icon">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="social-icon">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                <a href="#" class="social-icon">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
            <div class="copyright">
                © <?= date('Y') ?> <?= SCHOOL_NAME ?>. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Coming Soon Overlay Functions
        function openOverlay() {
            const overlay = document.getElementById('comingSoonOverlay');
            overlay.classList.add('active');
        }

        function closeOverlay() {
            const overlay = document.getElementById('comingSoonOverlay');
            overlay.classList.remove('active');
        }

        // Close overlay if clicking outside the box
        document.getElementById('comingSoonOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOverlay();
            }
        });

        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const navMenu = document.getElementById('navMenu');
        
        menuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            
            // Animate hamburger menu
            const spans = menuToggle.querySelectorAll('span');
            if (navMenu.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translateY(8px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translateY(-8px)';
            } else {
                spans[0].style.transform = 'rotate(0) translateY(0)';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'rotate(0) translateY(0)';
            }
        });
        
        // Close mobile menu when clicking on a link
        document.querySelectorAll('nav a').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                
                // Reset hamburger menu
                const spans = menuToggle.querySelectorAll('span');
                spans[0].style.transform = 'rotate(0) translateY(0)';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'rotate(0) translateY(0)';
                
                // Update active state
                document.querySelectorAll('nav a').forEach(a => a.classList.remove('active'));
                link.classList.add('active');
            });
        });
        
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Add scroll effect to header
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.15)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
            }
        });
        
        // Add entrance animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observe all portal cards
        document.querySelectorAll('.portal-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>