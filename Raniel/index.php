<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>San Carlos UtilitySYS | Official Billing Portal</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons (Font Awesome 6 Free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <style>
        /* --- Design Variables: GREEN THEME --- */
        :root {
            --primary: #064e3b;       /* Deep Forest Green */
            --accent: #10b981;        /* Bright Emerald Green */
            --accent-hover: #059669;
            --teal: #34d399;          /* Light Leaf Green */
            --text-dark: #1e293b;
            --text-light: #64748b;
            --bg-light: #f0fdf4;      /* Very light green tint */
            --white: #ffffff;
            
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-hover: 0 25px 50px -12px rgba(6, 78, 59, 0.15);
            
            --radius: 16px;
            --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background-color: var(--white);
            overflow-x: hidden;
            line-height: 1.7;
        }

        h1, h2, h3, h4, h5, .heading-font {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }

        /* --- Navbar --- */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            padding: 15px 0;
            box-shadow: 0 2px 15px rgba(0,0,0,0.03);
            transition: all 0.3s ease;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-brand-logo {
            height: 40px;
            width: auto;
            display: block;
        }

        .navbar-brand {
            font-size: 0;
            padding: 0;
            margin-right: 1rem;
        }
        
        .navbar-brand-text {
            font-size: 1.5rem;
            color: var(--primary);
            font-weight: 800;
            letter-spacing: -0.5px;
            vertical-align: middle;
            margin-left: 10px;
        }
        .navbar-brand-text span { color: var(--accent); }

        .nav-link {
            font-weight: 500;
            color: var(--text-dark) !important;
            margin: 0 10px;
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background-color: var(--accent);
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 80%;
        }

        .btn-outline-custom {
            border: 2px solid var(--primary);
            color: var(--primary) !important;
            border-radius: 50px;
            padding: 8px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-outline-custom:hover {
            background: var(--primary);
            color: white !important;
            transform: translateY(-2px);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--accent) 0%, var(--primary) 100%);
            color: white !important;
            border-radius: 50px;
            padding: 10px 28px;
            font-weight: 600;
            border: none;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
            background: linear-gradient(135deg, var(--accent-hover) 0%, #064e3b 100%);
        }

        /* --- Hero Section --- */
        .hero-section {
            padding-top: 140px;
            padding-bottom: 100px;
            background: radial-gradient(circle at 10% 20%, #ecfdf5 0%, #d1fae5 100%);
            position: relative;
            overflow: hidden;
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.6;
        }
        .blob-1 { top: -100px; right: -100px; width: 500px; height: 500px; background: rgba(16, 185, 129, 0.15); }
        .blob-2 { bottom: 50px; left: -100px; width: 400px; height: 400px; background: rgba(6, 78, 59, 0.15); }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .badge-loc {
            display: inline-block;
            background: rgba(16, 185, 129, 0.1);
            color: var(--primary);
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero-title {
            font-size: 3.5rem;
            line-height: 1.1;
            margin-bottom: 24px;
            color: var(--primary);
        }

        .hero-text {
            font-size: 1.15rem;
            color: var(--text-light);
            margin-bottom: 40px;
            max-width: 90%;
        }

        /* --- Hero Image Card --- */
        .hero-card-visual {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            padding: 20px;
            box-shadow: 0 25px 50px -12px rgba(6, 78, 59, 0.1);
            transform: perspective(1000px) rotateY(-5deg) rotateX(2deg);
            transition: transform 0.5s ease;
        }

        .hero-card-visual:hover {
            transform: perspective(1000px) rotateY(0deg) rotateX(0deg);
        }

        .stat-row-hero {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .stat-hero-item {
            text-align: center;
        }
        .stat-hero-icon {
            font-size: 1.5rem;
            color: var(--accent);
            margin-bottom: 8px;
        }
        .stat-hero-lbl {
            font-size: 0.8rem;
            color: var(--text-light);
            font-weight: 600;
        }

        /* --- Trust Section --- */
        .trust-section {
            padding: 40px 0;
            border-bottom: 1px solid var(--bg-light);
        }
        .trust-logo {
            font-weight: 700;
            color: #cbd5e1;
            font-size: 1.1rem;
            text-align: center;
            opacity: 0.7;
            transition: all 0.3s ease;
            cursor: default;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .trust-logo:hover {
            color: var(--accent);
            opacity: 1;
            transform: scale(1.05);
        }

        /* --- Features Section --- */
        .features-section {
            padding: 100px 0;
            background-color: var(--white);
        }

        .section-title {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 60px;
        }
        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }

        .feature-card {
            background: var(--white);
            padding: 40px 30px;
            border-radius: var(--radius);
            border: 1px solid #f1f5f9;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            height: 100%;
            z-index: 1;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: -1;
        }
        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        .icon-wrapper {
            width: 70px;
            height: 70px;
            background: var(--white);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: var(--accent);
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s ease;
        }

        .feature-card:hover .icon-wrapper {
            background: var(--accent);
            color: white;
            transform: rotateY(180deg);
        }

        /* --- How It Works --- */
        .process-section {
            background-color: var(--primary);
            color: white;
            padding: 100px 0;
            position: relative;
        }
        
        .process-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius);
            padding: 30px;
            height: 100%;
            transition: var(--transition);
        }
        .process-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-5px);
        }
        
        .step-number {
            font-size: 3rem;
            font-weight: 800;
            color: rgba(255,255,255,0.1);
            line-height: 1;
            margin-bottom: 10px;
            font-family: 'Poppins', sans-serif;
        }

        /* --- Partners Section --- */
        .partners-section {
            background-color: #f8fafc;
            padding: 80px 0;
            border-top: 1px solid #e2e8f0;
        }
        
        .partner-logo-img {
            height: 60px;
            width: auto;
            opacity: 0.6;
            filter: grayscale(100%);
            transition: all 0.3s ease;
            object-fit: contain;
        }
        
        .partner-logo-img:hover {
            opacity: 1;
            filter: grayscale(0%);
            transform: scale(1.05);
        }

        /* --- CTA Section --- */
        .cta-section {
            padding: 100px 0;
            text-align: center;
            background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%);
        }

        /* --- Developer Section (NEW) --- */
        .developer-section {
            background-color: var(--white);
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        
        .dev-card {
            background: var(--white);
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 2;
        }
        
        .dev-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .dev-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 4px solid #f0fdf4;
            box-shadow: 0 0 0 4px var(--accent);
        }

        .dev-name {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .dev-role {
            color: var(--accent);
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .dev-team {
            font-size: 0.9rem;
            color: var(--text-light);
            font-style: italic;
        }

        /* --- Footer --- */
        .footer {
            background-color: var(--primary);
            color: #94a3b8;
            padding: 80px 0 30px;
        }
        
        .footer-logo {
            height: 50px;
            width: auto;
            margin-bottom: 20px;
            filter: brightness(0) invert(1);
        }

        .footer h5 { color: white; margin-bottom: 25px; }
        .footer-link {
            display: block;
            color: #94a3b8;
            margin-bottom: 12px;
            text-decoration: none;
            transition: 0.2s;
        }
        .footer-link:hover { color: var(--accent); padding-left: 5px; }
        
        .developer-credit {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.85rem;
            text-align: center;
        }
        .developer-credit strong {
            color: var(--white);
        }

        /* --- Mobile Tweaks --- */
        @media (max-width: 991px) {
            .hero-section { text-align: center; padding-top: 120px; }
            .hero-title { font-size: 2.5rem; }
            .hero-text { margin: 0 auto 30px; }
            .hero-card-visual { margin-top: 50px; transform: none !important; }
            .navbar-collapse {
                background: white;
                padding: 20px;
                border-radius: 12px;
                margin-top: 15px;
                box-shadow: var(--shadow-lg);
            }
            .navbar-brand-text { display: none; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#">
                <!-- Logo Placeholder (if file missing, shows alt text) -->
                <img src="utilitySYS.png" onerror="this.style.display='none'" alt="UtilitySYS Logo" class="navbar-brand-logo d-inline-block align-top">
                <span class="navbar-brand-text">Utility<span>SYS</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#process">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#developer">Developer</a></li>
                    <li class="nav-item"><a class="nav-link btn-outline-custom" href="login.php">Log In</a></li>
                    <li class="nav-item">
                        <a class="nav-link btn-primary-custom" href="register.php">Get Started</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>

        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <span class="badge-loc"><i class="fas fa-map-marker-alt mr-1"></i> San Carlos City, Negros Occ.</span>
                    <h1 class="hero-title">Simplifying Utility Bill Management for Everyone</h1>
                    <p class="hero-text">
                        The official centralized platform for recording and managing electricity, water, and telecommunications transactions. Secure, fast, and designed for the modern community.
                    </p>
                    <div class="mt-4">
                        <a href="register.php" class="btn btn-primary-custom btn-lg mr-3">
                            Create Account <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                        <a href="#features" class="btn btn-outline-custom btn-lg">
                            Learn More
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="hero-card-visual">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0 font-weight-bold">System Overview</h5>
                            <i class="fas fa-ellipsis-h text-muted"></i>
                        </div>
                        
                        <!-- Abstract Graph Visual -->
                        <div style="height: 150px; background: #f0fdf4; border-radius: 12px; position: relative; overflow: hidden; margin-bottom: 20px;">
                            <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: flex-end; justify-content: space-between; padding: 0 10px;">
                                <div style="width: 15%; height: 40%; background: #a7f3d0; border-radius: 4px 4px 0 0;"></div>
                                <div style="width: 15%; height: 70%; background: #6ee7b7; border-radius: 4px 4px 0 0;"></div>
                                <div style="width: 15%; height: 50%; background: #34d399; border-radius: 4px 4px 0 0;"></div>
                                <div style="width: 15%; height: 85%; background: #10b981; border-radius: 4px 4px 0 0;"></div>
                                <div style="width: 15%; height: 60%; background: #059669; border-radius: 4px 4px 0 0;"></div>
                            </div>
                        </div>

                        <div class="stat-row-hero">
                            <div class="stat-hero-item">
                                <i class="fas fa-server stat-hero-icon"></i>
                                <div class="stat-hero-lbl">Stable</div>
                            </div>
                            <div class="stat-hero-item">
                                <i class="fas fa-shield-alt stat-hero-icon"></i>
                                <div class="stat-hero-lbl">Secure</div>
                            </div>
                            <div class="stat-hero-item">
                                <i class="fas fa-bolt stat-hero-icon"></i>
                                <div class="stat-hero-lbl">Fast</div>
                            </div>
                        </div>
                        
                        <div style="background: white; padding: 15px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                            <div class="d-flex align-items-center">
                                <div style="width: 40px; height: 40px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #059669; margin-right: 15px;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; font-size: 0.9rem;">System Status</div>
                                    <div style="font-size: 0.8rem; color: #64748b;">All services operational</div>
                                </div>
                                <div style="font-weight: 700; color: #064e3b;">Online</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Bar -->
    <div class="trust-section">
        <div class="container">
            <div class="row">
                <div class="col-6 col-md-3">
                    <div class="trust-logo">Electricity Partner</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="trust-logo">Water District</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="trust-logo">Telecom Provider</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="trust-logo">Internet ISP</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <div class="section-title">
                <h2>Complete Payment Solutions</h2>
                <p class="text-muted">We provide a seamless interface to manage all your essential utility expenses in one secure environment tailored for San Carlos residents.</p>
            </div>
            
            <div class="row">
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="feature-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h5>Electricity</h5>
                        <p class="text-muted">Instant transaction processing for major electricity providers with real-time confirmation.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="feature-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-tint"></i>
                        </div>
                        <h5>Water Utilities</h5>
                        <p class="text-muted">Effortlessly settle water bills and track consumption history through your dashboard.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="feature-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <h5>Telecom</h5>
                        <p class="text-muted">Never lose connectivity. Pay for fiber, DSL, and mobile postpaid plans instantly.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="feature-card">
                        <div class="icon-wrapper">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h5>E-Wallet Hub</h5>
                        <p class="text-muted">Seamless <strong>Cash In and Cash Out</strong>. Transfer funds between your bank account and Maya, GCash, or InstaPay instantly.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="process-section" id="process">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2>How It Works</h2>
                    <p style="opacity: 0.8;">Streamlining the payment process into three simple steps.</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="process-card text-center">
                        <div class="step-number">01</div>
                        <h4 class="mb-3">Register</h4>
                        <p style="opacity: 0.8;">Create your account and verify your identity to unlock full payment capabilities within San Carlos.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="process-card text-center">
                        <div class="step-number">02</div>
                        <h4 class="mb-3">Link Bills</h4>
                        <p style="opacity: 0.8;">Securely add your utility account numbers (Contract/Consumer IDs) to your profile.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="process-card text-center">
                        <div class="step-number">03</div>
                        <h4 class="mb-3">Pay & Track</h4>
                        <p style="opacity: 0.8;">Select GCash, Maya, or Bank Transfer methods and receive digital receipts instantly.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NEW: Developer Section -->
    <section class="developer-section" id="developer">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 style="color: var(--primary);">Meet the Developer</h2>
                    <p class="text-muted">Built with passion and precision by a Code Warriors member.</p>
                </div>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="dev-card">
                        <!-- Avatar Placeholder -->
                        <img src="rj.jpg" alt="Raniel John De Asis" class="dev-avatar">
                        
                        <span class="dev-badge"><i class="fas fa-code mr-1"></i> Full Stack Developer</span>
                        
                        <h3 class="dev-name">Raniel John De Asis</h3>
                        <div class="dev-role">San Carlos UtilitySYS Lead</div>
                        
                        <p class="text-muted mb-4">
                            Specializing in modern web architectures and seamless payment gateway integrations. 
                            Dedicated to providing the residents of San Carlos City with a world-class billing experience.
                        </p>
                        
                        <div class="dev-team">
                            <strong>Proud Member of:</strong> Code Warriors
                        </div>

                        <div class="mt-4">
                            <a href="#" class="btn btn-outline-custom btn-sm"><i class="fab fa-github mr-2"></i>GitHub</a>
                            <a href="#" class="btn btn-outline-custom btn-sm"><i class="fab fa-linkedin mr-2"></i>LinkedIn</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Partners Section -->
    <section class="partners-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h4 style="color: var(--primary); font-weight: 700;">Accepted Payment Partners</h4>
                    <p class="text-muted">We support the most trusted e-wallets and instapay banks in the Philippines.</p>
                </div>
            </div>
            <div class="row justify-content-center align-items-center">
                <!-- Using generic placeholders with onerror fallback to ensure layout holds up -->
                <div class="col-6 col-md-3 mb-4 text-center">
                    <img src="gcash.png" onerror="this.src='https://placehold.co/200x60?text=GCash'" alt="GCash" class="partner-logo-img img-fluid">
                </div>
                <div class="col-6 col-md-3 mb-4 text-center">
                    <img src="paymaya.png" onerror="this.src='https://placehold.co/200x60?text=MAYA'" alt="Maya" class="partner-logo-img img-fluid">
                </div>
                <div class="col-6 col-md-3 mb-4 text-center">
                    <img src="instapay.jpg" onerror="this.src='https://placehold.co/200x60?text=InstaPay'" alt="InstaPay" class="partner-logo-img img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="container">
            <h2 class="mb-3">Ready to take control of your bills?</h2>
            <p class="text-muted mb-4">Join thousands of users in Negros Occidental who trust UtilityPay.</p>
            <a href="register.php" class="btn btn-primary-custom btn-lg">Get Started for Free</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    
                    <h5 class="text-white" style="margin-top: -10px;">UtilityPay System</h5>
                    <p style="font-size: 0.9rem; line-height: 1.6;">An enterprise-grade utility payment aggregator designed to streamline financial operations for individuals and businesses in San Carlos City.</p>
                </div>
                <div class="col-6 col-lg-2 mb-4">
                    <h5>Platform</h5>
                    <a href="#" class="footer-link">Home</a>
                    <a href="#features" class="footer-link">Services</a>
                    <a href="login.php" class="footer-link">Login</a>
                    <a href="register.php" class="footer-link">Sign Up</a>
                </div>
                <div class="col-6 col-lg-2 mb-4">
                    <h5>Support</h5>
                    <a href="#" class="footer-link">Help Center</a>
                    <a href="#" class="footer-link">Privacy Policy</a>
                    <a href="#" class="footer-link">Terms</a>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5>Contact Information</h5>
                    <ul class="list-unstyled" style="font-size: 0.9rem;">
                        <li class="mb-2"><i class="fas fa-map-marker-alt mr-2"></i> San Carlos City, Negros Occ.</li>
                        <li class="mb-2"><i class="fas fa-envelope mr-2"></i> support@utilitypay.com</li>
                        <li class="mb-2"><i class="fas fa-phone mr-2"></i> (034) 123-4567</li>
                    </ul>
                </div>
            </div>
            
            <!-- Developer Credit Line in Footer -->
            <div class="text-center pt-4 mt-4" style="border-top: 1px solid rgba(255,255,255,0.1); font-size: 0.85rem;">
                <p class="mb-2">&copy; 2026 Utility Payment System. All Rights Reserved.</p>
                <div class="developer-credit">
                    Developed by <strong>Raniel John De Asis</strong> | <strong>Code Warriors Member</strong>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- jQuery (Required for Bootstrap 4) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Navbar interaction on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 20) {
                navbar.style.padding = '10px 0';
                navbar.style.boxShadow = '0 4px 20px rgba(0,0,0,0.05)';
            } else {
                navbar.style.padding = '15px 0';
                navbar.style.boxShadow = '0 2px 15px rgba(0,0,0,0.03)';
            }
        });
        
        // Add subtle animation to developer card on scroll into view
        const observerOptions = {
            threshold: 0.2
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        const devCard = document.querySelector('.dev-card');
        if(devCard) {
            devCard.style.opacity = '0';
            devCard.style.transform = 'translateY(20px)';
            devCard.style.transition = 'all 0.6s ease-out';
            observer.observe(devCard);
        }
    </script>
</body>
</html>