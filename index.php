<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code Warriors | System Hub</title>
    <meta name="description" content="Central hub for Code Warriors available systems and tools.">
    
    <!-- Phosphor Icons (Lightweight, modern icon library) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="icon" href="code.png" type="image/x-icon">

    <style>
        /* --- CSS VARIABLES & RESET --- */
        :root {
            --bg-body: #0f1115;
            --bg-card: #161b22;
            --bg-card-hover: #1f242e;
            --primary: #00f0ff; /* Cyber Blue */
            --primary-dim: rgba(0, 240, 255, 0.1);
            --text-main: #e6edf3;
            --text-muted: #8b949e;
            --accent-purple: #bd34fe;
            --border: #30363d;
            --font-main: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            --container-width: 1200px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        /* --- LAYOUT UTILITIES --- */
        .container {
            max-width: var(--container-width);
            margin: 0 auto;
            padding: 0 20px;
        }

        .section-padding {
            padding: 80px 0;
        }

        /* --- HEADER --- */
        header {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(15, 17, 21, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
        }

        .nav-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .logo {
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-main);
            transition: var(--transition);
        }

        .logo:hover {
            color: var(--primary);
        }

        /* Nav Logo Image Styling */
        .logo-img {
            height: 40px;
            width: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid var(--border);
            transition: var(--transition);
        }

        .logo:hover .logo-img {
            border-color: var(--primary);
            box-shadow: 0 0 10px var(--primary-dim);
        }

        .logo span {
            background: linear-gradient(90deg, #fff, #8b949e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        nav ul {
            display: flex;
            gap: 30px;
        }

        nav a {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-muted);
            transition: var(--transition);
            position: relative;
        }

        nav a:hover, nav a.active {
            color: var(--primary);
        }

        nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background-color: var(--primary);
            transition: var(--transition);
        }

        nav a:hover::after, nav a.active::after {
            width: 100%;
        }

        /* --- HERO SECTION --- */
        .hero {
            padding-top: 160px;
            padding-bottom: 100px;
            text-align: center;
            background: radial-gradient(circle at 50% 10%, rgba(0, 240, 255, 0.08), transparent 60%);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .hero-logo-container {
            margin-bottom: 40px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: float 6s ease-in-out infinite;
        }

        .hero-logo-ring {
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            border: 1px dashed var(--primary-dim);
            border-top: 2px solid var(--primary);
            animation: spin 10s linear infinite;
        }

        .hero-logo-img {
            width: 160px;
            height: 160px;
            object-fit: cover;
            border-radius: 50%;
            z-index: 2;
            box-shadow: 0 0 30px var(--primary-dim);
            border: 4px solid var(--bg-card);
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            line-height: 1.1;
            letter-spacing: -1px;
            z-index: 2;
        }

        .hero h1 span {
            color: var(--primary);
            text-shadow: 0 0 20px var(--primary-dim);
        }

        .hero p {
            font-size: 1.2rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto 40px;
            z-index: 2;
        }

        .btn {
            display: inline-block;
            padding: 14px 36px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            z-index: 2;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #0f1115;
            border: 1px solid var(--primary);
        }

        .btn-primary:hover {
            background-color: transparent;
            color: var(--primary);
            box-shadow: 0 0 20px var(--primary-dim);
            transform: translateY(-2px);
        }

        /* --- ANIMATIONS --- */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* --- SYSTEMS GRID --- */
        .section-title {
            font-size: 2rem;
            margin-bottom: 10px;
            text-align: center;
            font-weight: 700;
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-muted);
            margin-bottom: 50px;
        }

        .systems-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .system-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 30px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }

        .system-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 40px -10px rgba(0, 240, 255, 0.15);
        }

        .card-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-dim);
            color: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
            transition: var(--transition);
        }

        .system-card:hover .card-icon {
            background: var(--primary);
            color: #000;
        }

        .card-header {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .card-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 5px;
        }

        .card-status {
            display: inline-block;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 100px;
            background: rgba(46, 160, 67, 0.15);
            color: #3fb950;
            border: 1px solid rgba(46, 160, 67, 0.3);
            font-weight: 600;
            white-space: nowrap;
        }

        .card-desc {
            color: var(--text-muted);
            margin-bottom: 25px;
            font-size: 0.95rem;
            flex-grow: 1;
        }

        .card-link {
            margin-top: auto;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            font-weight: 600;
            font-size: 0.9rem;
            transition: var(--transition);
            cursor: pointer;
        }

        .card-link:hover {
            gap: 12px;
            color: #fff;
        }
        
        /* Unavailable link style */
        .card-link.unavailable {
            color: var(--text-muted);
            cursor: not-allowed;
        }
        .card-link.unavailable:hover {
            gap: 8px;
            color: var(--text-muted);
        }

        /* --- FEATURES SECTION --- */
        .features {
            background-color: var(--bg-card);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            text-align: center;
        }

        .feature-item i {
            font-size: 2.5rem;
            color: var(--accent-purple);
            margin-bottom: 15px;
            display: inline-block;
        }

        .feature-item h3 {
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        .feature-item p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* --- FOOTER --- */
        footer {
            padding: 50px 0;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
            border-top: 1px solid var(--border);
        }

        footer p span {
            color: var(--primary);
            font-weight: bold;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.2rem;
            }
            .hero-logo-img {
                width: 120px;
                height: 120px;
            }
            .hero-logo-ring {
                width: 160px;
                height: 160px;
            }
            .nav-wrapper {
                flex-direction: column;
                gap: 15px;
                height: auto;
                padding: 15px 0;
            }
            header {
                position: relative;
            }
            .hero {
                padding-top: 40px;
                padding-bottom: 60px;
            }
            nav ul {
                gap: 20px;
                font-size: 0.9rem;
            }
            .systems-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Header / Navigation -->
    <header>
        <div class="container nav-wrapper">
            <a href="#" class="logo">
                <!-- NAV LOGO: Circular code.png -->
                <img src="code.png" alt="Code Warriors Logo" class="logo-img">
                <span>Code Warriors</span>
            </a>
            <nav>
                <ul>
                    <li><a href="#" class="active">Home</a></li>
                    <li><a href="#systems">Systems</a></li>
                    <li><a href="#about">About</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <!-- LANDING PAGE LOGO SHOWCASE -->
                <div class="hero-logo-container">
                    <div class="hero-logo-ring"></div>
                    <img src="code.png" alt="Code Warriors Main Logo" class="hero-logo-img">
                </div>

                <h1>Deploy. Access. <span>Conquer.</span></h1>
                <p>Welcome to the Code Warriors central command. Access our suite of internal tools and systems designed for efficiency and performance.</p>
                <a href="#systems" class="btn btn-primary">View Available Systems</a>
            </div>
        </section>

        <!-- Systems Section -->
        <section id="systems" class="section-padding">
            <div class="container">
                <h2 class="section-title">System Dashboard</h2>
                <p class="section-subtitle">Monitor the status and launch our suite of applications.</p>

                <div class="systems-grid">
                    
                    <!-- SYSTEM 1: Dorm Finder (Active) -->
                    <article class="system-card">
                        <div>
                            <div class="card-icon">
                                <i class="ph ph-house-line"></i>
                            </div>
                            <div class="card-header">
                                <h3 class="card-title">Dorm Finder</h3>
                                <span class="card-status">Active</span>
                            </div>
                            <p class="card-desc">
                                Efficiently locate and manage available dormitory units. View status, capacity, and details in real-time.
                            </p>
                        </div>
                        <a href="./perez/" target="_blank" class="card-link">
                            Launch System <i class="ph ph-arrow-right"></i>
                        </a>
                    </article>

                    <!-- SYSTEM 2: UtiliTYS (Active) -->
                    <article class="system-card">
                        <div>
                            <div class="card-icon">
                                <i class="ph ph-receipt"></i>
                            </div>
                            <div class="card-header">
                                <h3 class="card-title">UtilitySYS</h3>
                                <span class="card-status">Active</span>
                            </div>
                            <p class="card-desc">
                                Centralized billing and utility management. Track consumption, view statements, and handle financial records.
                            </p>
                        </div>
                        <a href="./Raniel/" target="_blank" class="card-link">
                            Launch System <i class="ph ph-arrow-right"></i>
                        </a>
                    </article>

                    <!-- SYSTEM 3: KCC Grades Record System (Active) -->
                    <article class="system-card">
                        <div>
                            <div class="card-icon">
                                <i class="ph ph-exam"></i>
                            </div>
                            <div class="card-header">
                                <h3 class="card-title">KCC Grades Record</h3>
                                <span class="card-status">Active</span>
                            </div>
                            <p class="card-desc">
                                Secure access to student grade reports, transcript generation, and academic performance history.
                            </p>
                        </div>
                        <a href="./kcc/" target="_blank" class="card-link">
                            Launch System <i class="ph ph-arrow-right"></i>
                        </a>
                    </article>

                    <!-- SYSTEM 4: SMART School (UPDATED TO ACTIVE - Link: csrccs-systems.site) -->
                    <article class="system-card">
                        <div>
                            <div class="card-icon">
                                <i class="ph ph-graduation-cap"></i>
                            </div>
                            <div class="card-header">
                                <h3 class="card-title">SMART School</h3>
                                <span class="card-status">Active</span>
                            </div>
                            <p class="card-desc">
                                Comprehensive school management platform. Live and operational for all administrative tasks.
                            </p>
                        </div>
                        <a href="./jomar/" target="_blank" class="card-link">
                            Launch System <i class="ph ph-arrow-right"></i>
                        </a>
                    </article>

        


                    <!-- SYSTEM 7: Guidance Office (UPDATED TO ACTIVE - Link: kenn) -->
                    <article class="system-card">
                        <div>
                            <div class="card-icon">
                                <i class="ph ph-folder-user"></i>
                            </div>
                            <div class="card-header">
                                <h3 class="card-title">Guidance Office</h3>
                                <span class="card-status">Active</span>
                            </div>
                            <p class="card-desc">
                                Administrative tools and document management for the guidance department staff.
                            </p>
                        </div>
                        <a href="./kenn/" target="_blank" class="card-link">
                            Launch System <i class="ph ph-arrow-right"></i>
                        </a>
                    </article>

                    <!-- SYSTEM 8: Accounting System (UPDATED TO ACTIVE - Link: francis) -->
                    <article class="system-card">
                        <div>
                            <div class="card-icon">
                                <i class="ph ph-calculator"></i>
                            </div>
                            <div class="card-header">
                                <h3 class="card-title">Accounting System</h3>
                                <span class="card-status">Active</span>
                            </div>
                            <p class="card-desc">
                                Financial ledgers, balance sheets, payroll, and tuition billing modules.
                            </p>
                        </div>
                        <a href="./francis/" target="_blank" class="card-link">
                            Launch System <i class="ph ph-arrow-right"></i>
                        </a>
                    </article>

   

  

                </div>
            </div>
        </section>

        <!-- About / Features Section -->
        <section id="about" class="section-padding features">
            <div class="container">
                <div class="features-grid">
                    <div class="feature-item">
                        <i class="ph ph-lightning"></i>
                        <h3>Fast Access</h3>
                        <p>Direct links to resources without the bloat. Optimized for speed.</p>
                    </div>
                    <div class="feature-item">
                        <i class="ph ph-shield-check"></i>
                        <h3>Secure</h3>
                        <p>Internal access protocols ensure your data remains within the warrior network.</p>
                    </div>
                    <div class="feature-item">
                        <i class="ph ph-users-three"></i>
                        <h3>Collaborative</h3>
                        <p>Built by the Code Warriors team, for the team. Open communication channels.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2026 Code Warriors. All Systems Operational. <span>System Hub v2.0</span></p>
        </div>
    </footer>

</body>
</html>