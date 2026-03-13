<?php
// Set page title
 $pageTitle = "Enrollment Closed | Colegio De Santa Rita De San Carlos, Inc.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Colegio De Santa Rita De San Carlos, Inc. - College of Computer Studies - Enrollment Information">
    <meta name="keywords" content="Colegio De Santa Rita, CSR, Computer Studies, IT Education, San Carlos City, Negros Occidental">
    <title><?php echo $pageTitle; ?></title>
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
            background: linear-gradient(135deg, rgba(183, 28, 28, 0.95), rgba(21, 101, 192, 0.95)), 
                        url('uploads/newbuilding.jpg') center/cover no-repeat;
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
            border-bottom: 2px solid rgba(183, 28, 28, 0.2);
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
            box-shadow: 0 2px 10px rgba(183, 28, 28, 0.2);
        }
        
        .school-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #b71c1c;
            line-height: 1.3;
        }
        
        .college-name {
            font-size: 0.9rem;
            color: #1565C0;
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
            color: #b71c1c;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 16px;
            border-radius: 25px;
            position: relative;
        }
        
        nav ul li a:hover {
            background: rgba(183, 28, 28, 0.1);
            color: #1565C0;
        }
        
        nav ul li a.active {
            background: linear-gradient(90deg, #b71c1c, #1565C0);
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
            background-color: #b71c1c;
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
            background: radial-gradient(circle at center, rgba(183, 28, 28, 0.1) 0%, rgba(21, 101, 192, 0.1) 70%);
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
            color: #ffcdd2;
        }
        
        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 40px;
            font-weight: 300;
            line-height: 1.6;
            color: #ffebee;
            position: relative;
            z-index: 1;
        }
        
        /* Closed Enrollment Section */
        .closed-enrollment {
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
            background: linear-gradient(90deg, #b71c1c, #1565C0);
            border-radius: 2px;
        }
        
        .closed-enrollment-description {
            color: #ffebee;
            font-size: 1.1rem;
            margin-bottom: 50px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.7;
        }
        
        .closed-message {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px 30px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            margin-bottom: 40px;
        }
        
        .closed-message::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #b71c1c, #1565C0);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        
        .closed-message:hover::before {
            transform: translateX(0);
        }
        
        .closed-message:hover {
            transform: translateY(-15px);
            box-shadow: 0 20px 40px rgba(183, 28, 28, 0.25);
        }
        
        .icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, #ffebee, #e3f2fd);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .closed-message:hover .icon-wrapper {
            transform: scale(1.1) rotate(5deg);
            background: linear-gradient(135deg, #b71c1c, #1565C0);
        }
        
        .closed-icon {
            font-size: 2.5rem;
            color: #b71c1c;
            transition: all 0.3s ease;
        }
        
        .closed-message:hover .closed-icon {
            color: #ffffff;
        }
        
        .closed-message h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #b71c1c;
            font-weight: 600;
        }
        
        .closed-message p {
            margin-bottom: 25px;
            line-height: 1.6;
            color: #616161;
            font-size: 0.95rem;
        }
        
        .home-btn {
            background: linear-gradient(135deg, #b71c1c, #1565C0);
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
            box-shadow: 0 4px 15px rgba(183, 28, 28, 0.3);
        }
        
        .home-btn:hover {
            background: linear-gradient(135deg, #c62828, #1976D2);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(183, 28, 28, 0.4);
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
            color: #b71c1c;
            font-weight: 500;
        }
        
        .link-item:hover {
            background: #ffffff;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            color: #1565C0;
        }
        
        /* Footer */
        footer {
            background: linear-gradient(90deg, #b71c1c, #1565C0);
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
            color: #b71c1c;
            transform: translateY(-5px) scale(1.1);
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
            
            .closed-enrollment {
                padding: 60px 20px;
            }
            
            .section-title {
                font-size: 2.2rem;
            }
            
            .closed-message {
                max-width: 400px;
                margin: 0 auto 40px;
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
            
            .section-title {
                font-size: 1.9rem;
            }
            
            .home-btn {
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
        
        .closed-message {
            animation: fadeInUp 0.6s ease forwards;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="csr.png" alt="CSR Logo" class="logo">
                <div>
                    <div class="school-name">Colegio De Santa Rita De San Carlos, Inc.</div>
                    <div class="college-name">College of Computer Studies</div>
                </div>
            </div>
            <div class="menu-toggle" id="menuToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <nav id="navMenu">
                <ul>
                    <li><a href="index.php" class="active">Home</a></li>
                    <li><a href="index.php#portals">Portals</a></li>
                    <li><a href="index.php#quick-links">Quick Links</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <h1>College of Computer Studies</h1>
        <div class="subtitle">Enrollment Information</div>
        <p>Thank you for your interest in our programs. Please check our enrollment status below.</p>
    </section>

    <!-- Closed Enrollment Section -->
    <section class="closed-enrollment" id="closed-enrollment">
        <h2 class="section-title">Enrollment Status</h2>
        <p class="closed-enrollment-description">
            We appreciate your interest in joining our institution. Please find the current enrollment status below.
        </p>
        
        <div class="closed-message">
            <div class="icon-wrapper">
                <i class="fas fa-calendar-times closed-icon"></i>
            </div>
            <h3>Enrollment Closed</h3>
            <p>Thank you for your interest in Colegio De Santa Rita De San Carlos, Inc.</p>
            <p>The enrollment period for the <?php echo date('Y') . "-" . (date('Y') + 1); ?> academic year has officially ended.</p>
            <p>If you have any questions or need assistance, please contact our registrar's office at <strong>admin@csr-scc.edu.ph</strong>.</p>
            <p>Please check back later for updates about enrollment for the next academic year.</p>
            <a href="index.php" class="home-btn">
                <i class="fas fa-home"></i> Return to Homepage
            </a>
        </div>
    </section>

    <!-- Quick Links Section -->
    <section class="quick-links" id="quick-links">
        <h3>Quick Links</h3>
        <div class="links-grid">
            <a href="index.php" class="link-item">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="index.php#history" class="link-item">
                <i class="fas fa-info-circle"></i> About Us
            </a>
            <a href="index.php#announcements" class="link-item">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>
            <a href="index.php#portal" class="link-item">
                <i class="fas fa-sign-in-alt"></i> Portal Access
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-logo">
                <img src="csr.png" alt="CSR Logo">
            </div>
            <div class="footer-links">
                <a href="index.php">Home</a>
                <a href="index.php#portals">Portals</a>
                <a href="index.php#quick-links">Quick Links</a>
                <a href="index.php#contact">Contact</a>
            </div>
            <div class="social-media">
                <a href="#" class="social-icon">
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
                © <?php echo date('Y'); ?> Colegio De Santa Rita De San Carlos, Inc. - College of Computer Studies. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
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
        
        // Observe the closed message
        const closedMessage = document.querySelector('.closed-message');
        if (closedMessage) {
            closedMessage.style.opacity = '0';
            closedMessage.style.transform = 'translateY(30px)';
            closedMessage.style.transition = 'all 0.6s ease';
            observer.observe(closedMessage);
        }
    </script>
</body>
</html>