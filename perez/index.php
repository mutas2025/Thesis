<?php
require 'config.php';
session_start();

// --- LOGIC: FETCH AVAILABLE ROOMS ---
try {
    $stmt = $pdo->prepare("
        SELECT 
            r.room_id, 
            r.room_number, 
            r.floor_number,
            r.price_per_month, 
            r.room_type, 
            r.amenities as room_amenities,
            r.capacity,
            r.current_occupancy,
            r.status, 
            r.room_image, 
            bh.house_name, 
            bh.description as house_description,
            bh.amenities as house_amenities,
            a.city, 
            a.state, 
            a.street_name,
            bh.landlord_id
        FROM Rooms r
        JOIN BoardingHouses bh ON r.house_id = bh.house_id
        JOIN Addresses a ON bh.address_id = a.address_id
        WHERE bh.is_active = 1
        ORDER BY r.price_per_month ASC
    ");
    $stmt->execute();
    $availableRooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $availableRooms = [];
}

function formatPrice($price) {
    return '₱' . number_format($price, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>DormFinder | Safe & Secure Living</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #3b82f6;       
            --primary-dark: #2563eb;
            --secondary: #1e293b;     
            --accent: #8b5cf6;        
            --success: #10b981;       
            --danger: #ef4444;        
            --warning: #f59e0b;       
            --bg-body: #f1f5f9;       
            --white: #ffffff;
            --text-main: #334155;
            --text-light: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.5);
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 25px 50px -12px rgba(59, 130, 246, 0.25);
            --radius-xl: 20px;
            --radius-full: 9999px;
            --nav-height: 80px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            position: relative;
        }

        /* --- AMBIENT BACKGROUND BLOBS --- */
        .ambient-blob {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.6;
            animation: float 10s infinite ease-in-out alternate;
            pointer-events: none; /* Prevent blocking clicks on mobile */
        }
        .blob-1 { top: -100px; left: -100px; width: 500px; height: 500px; background: rgba(59, 130, 246, 0.2); }
        .blob-2 { bottom: -100px; right: -100px; width: 600px; height: 600px; background: rgba(139, 92, 246, 0.15); animation-delay: -5s; }
        .blob-3 { top: 40%; left: 50%; transform: translate(-50%, -50%); width: 400px; height: 400px; background: rgba(16, 185, 129, 0.1); animation-duration: 15s; }

        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(30px, 50px); }
        }

        .fade-up {
            animation: fadeUp 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }

        h1, h2, h3, h4 {
            font-family: 'Poppins', sans-serif;
            color: var(--secondary);
            line-height: 1.2;
        }

        a { text-decoration: none; color: inherit; transition: 0.3s ease; }

        /* --- NAVIGATION (Glassmorphism & Mobile Toggle) --- */
        nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
            height: var(--nav-height);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        .logo span {
            background: linear-gradient(135deg, var(--primary-dark), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Desktop Nav Links */
        .nav-links {
            display: flex;
            gap: 2.5rem;
            align-items: center;
        }

        .nav-links a {
            font-weight: 500;
            font-size: 0.95rem;
            color: var(--text-main);
            position: relative;
        }
        .nav-links a::after {
            content: ''; position: absolute; width: 0; height: 2px; bottom: -4px; left: 0;
            background-color: var(--primary); transition: width 0.3s ease;
        }
        .nav-links a:hover { color: var(--primary); }
        .nav-links a:hover::after { width: 100%; }

        /* Mobile Menu Button (Hidden on Desktop) */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.8rem;
            color: var(--secondary);
            cursor: pointer;
            padding: 5px;
        }

        .btn {
            padding: 0.75rem 1.75rem;
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(37, 99, 235, 0.5); }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        .btn-outline:hover { background: var(--primary); color: white; }

        /* --- HERO SECTION --- */
        .hero {
            padding: 4rem 1.5rem 6rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            /* Parallax disabled for mobile via media query below */
            background: linear-gradient(rgba(30, 41, 59, 0.85), rgba(30, 41, 59, 0.9)), url('uploads/sancarlos.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed; 
            color: white;
        }

        .hero-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.4);
            color: #ffffff;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero h1 {
            /* Fluid Typography: scales between 2.5rem and 3.8rem */
            font-size: clamp(2.5rem, 5vw, 3.8rem);
            font-weight: 800;
            margin-bottom: 1rem;
            color: #ffffff;
            line-height: 1.1;
        }

        .hero h1 span {
            background: linear-gradient(120deg, #60a5fa, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p {
            font-size: clamp(1rem, 2vw, 1.25rem);
            color: #e2e8f0;
            max-width: 600px;
            margin: 0 auto 3rem;
        }

        /* --- SEARCH BOX --- */
        .search-container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }

        .search-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            padding: 0.5rem;
            border-radius: var(--radius-full);
            box-shadow: 0 20px 50px -10px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .search-icon-wrapper {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            color: var(--primary);
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .search-input {
            flex: 1;
            border: none;
            background: transparent;
            font-size: 1.1rem;
            font-family: 'Inter', sans-serif;
            color: var(--secondary);
            outline: none;
            min-width: 0; /* Prevent overflow on flex child */
        }

        .search-btn {
            background: var(--secondary);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: var(--radius-full);
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            white-space: nowrap;
        }
        .search-btn:hover { background: var(--primary); }

        /* --- TRUST INDICATORS --- */
        .trust-section {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 50px;
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(5px);
        }
        .trust-item i {
            color: var(--success);
            background: rgba(16, 185, 129, 0.2);
            padding: 8px;
            border-radius: 50%;
        }

        /* --- LISTINGS SECTION --- */
        .listings-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 4rem 1.5rem;
            flex: 1;
            width: 100%;
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: clamp(2rem, 4vw, 2.8rem);
            margin-bottom: 1rem;
            background: linear-gradient(to right, var(--secondary), var(--text-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* --- GRID --- */
        .grid {
            display: grid;
            gap: 2rem;
            /* Desktop: 3 Columns */
            grid-template-columns: repeat(3, 1fr); 
        }

        /* --- CARD DESIGN --- */
        .card {
            background: var(--white);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.8);
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .card-image-box {
            height: 220px;
            position: relative;
            background: #e2e8f0;
            overflow: hidden;
        }

        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .card:hover .card-img { transform: scale(1.05); }

        .status-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            z-index: 2;
            text-transform: uppercase;
            backdrop-filter: blur(4px);
            color: white;
        }
        .status-available { background: rgba(16, 185, 129, 0.9); }
        .status-full { background: rgba(239, 68, 68, 0.9); }
        .status-filling { background: rgba(245, 158, 11, 0.9); }

        .verified-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(255, 255, 255, 0.95);
            color: var(--success);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            z-index: 2;
            display: flex; align-items: center; gap: 4px;
        }

        .card-content {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .card-price-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }

        .price {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary);
            font-family: 'Poppins', sans-serif;
        }
        .price span { font-size: 0.8rem; color: var(--text-light); font-weight: 400; }

        .card-type {
            background: #f8fafc;
            color: var(--text-light);
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            border: 1px solid #e2e8f0;
        }

        .card-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--secondary);
            line-height: 1.3;
        }

        .card-location {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .card-location i { color: var(--primary); font-size: 0.8rem; }

        /* Occupancy Bar */
        .occupancy-container { margin-bottom: 1rem; }
        .occupancy-text {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .progress-bg { background: #e2e8f0; height: 6px; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, var(--primary), var(--accent)); border-radius: 10px; }
        .progress-fill.full { background: var(--danger); }
        .progress-fill.filling { background: var(--warning); }

        .amenities-row {
            display: flex;
            gap: 8px;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .amenity-dot {
            font-size: 0.75rem;
            color: var(--text-main);
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 4px 10px;
            border-radius: 6px;
            display: flex; align-items: center; gap: 5px;
        }
        .amenity-dot i { color: var(--accent); font-size: 0.7rem; }

        .card-footer {
            margin-top: auto;
            border-top: 1px solid #f1f5f9;
            padding-top: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .view-btn {
            color: var(--primary);
            font-weight: 700;
            font-size: 0.9rem;
            display: flex; align-items: center; gap: 6px;
        }
        .view-btn.disabled { color: var(--text-light); pointer-events: none; }

        /* --- FOOTER --- */
        footer {
            background: var(--secondary);
            color: rgba(255,255,255,0.7);
            padding: 4rem 1.5rem 2rem;
            margin-top: auto;
        }
        .footer-content {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .footer-brand h2 { color: white; margin-bottom: 1rem; }
        .footer-col h4 { color: white; margin-bottom: 1.2rem; font-size: 1rem; }
        .footer-col ul { list-style: none; }
        .footer-col ul li { margin-bottom: 0.8rem; }
        .footer-col ul li a:hover { color: var(--accent); }

        .developer-credit {
            display: flex; flex-direction: column; align-items: center; gap: 1rem;
            margin-top: 2rem; padding-top: 2rem; border-top: 1px dashed rgba(255,255,255,0.1);
            text-align: center;
        }
        .developer-info { display: flex; align-items: center; gap: 1rem; }
        .developer-avatar { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary); }
        .developer-text { text-align: left; font-size: 0.85rem; }
        .developer-text strong { color: white; display: block; }
        .developer-text span { color: var(--accent); font-size: 0.8rem; }

        /* --- TOAST --- */
        .toast {
            position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%) translateY(150%);
            background: white; border-left: 5px solid var(--primary);
            padding: 1rem 1.5rem; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex; align-items: center; gap: 10px; z-index: 2000;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            width: 90%; max-width: 400px;
        }
        .toast.show { transform: translateX(-50%) translateY(0); }

        /* =========================================
           RESPONSIVE / MOBILE MEDIA QUERIES
           ========================================= */
        @media (max-width: 1024px) {
            .grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            /* --- NAVIGATION MOBILE --- */
            .mobile-menu-btn { display: block; z-index: 1002; }
            
            .nav-links {
                position: fixed;
                top: 0; right: -100%;
                width: 75%;
                height: 100vh;
                background: white;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                gap: 2rem;
                box-shadow: -5px 0 20px rgba(0,0,0,0.1);
                transition: right 0.4s ease;
                z-index: 1001;
            }

            .nav-links.active { right: 0; }
            
            .nav-actions {
                display: none; /* Hide buttons in header on mobile, move to menu if needed */
            }
            
            /* Add auth links inside the mobile menu */
            .mobile-auth-links {
                display: flex;
                flex-direction: column;
                gap: 1rem;
                margin-top: 2rem;
                width: 80%;
            }

            /* --- HERO MOBILE --- */
            .hero { padding: 3rem 1rem 5rem; background-attachment: scroll; /* Fix iOS jitter */ }
            .search-box { 
                flex-direction: column; 
                padding: 1rem; 
                border-radius: 24px; 
                gap: 1rem;
            }
            .search-input { text-align: center; width: 100%; }
            .search-btn { width: 100%; padding: 1rem; }
            
            .trust-section { gap: 1rem; flex-direction: row; overflow-x: auto; justify-content: flex-start; padding-bottom: 10px; -webkit-overflow-scrolling: touch; }
            .trust-item { white-space: nowrap; flex-shrink: 0; }

            /* --- LISTINGS MOBILE --- */
            .listings-container { padding: 3rem 1rem; }
            .grid { grid-template-columns: 1fr; } /* Force 1 column */
            
            .card-image-box { height: 200px; }
            .card-content { padding: 1.25rem; }
            .amenities-row { margin-bottom: 1rem; }
        }
    </style>
</head>
<body>

    <!-- AMBIENT BACKGROUND -->
    <div class="ambient-blob blob-1"></div>
    <div class="ambient-blob blob-2"></div>
    <div class="ambient-blob blob-3"></div>

    <!-- NAVIGATION -->
    <nav>
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-building-shield"></i> Dorm<span>Finder</span>
            </a>
            
            <!-- Hamburger Button -->
            <button class="mobile-menu-btn" onclick="toggleMenu()" aria-label="Toggle Menu">
                <i class="fa-solid fa-bars" id="menu-icon"></i>
            </button>
            
            <!-- Links (Desktop + Mobile Overlay) -->
            <div class="nav-links" id="navLinks">
                <a href="index.php" onclick="toggleMenu()">Home</a>
                <a href="#listings" onclick="toggleMenu()">Browse Rooms</a>
                <a href="#" onclick="toggleMenu()">Safety Guarantee</a>
                <a href="#" onclick="toggleMenu()">For Landlords</a>
                
                <!-- Mobile Specific Auth Links --

            <!-- Desktop Actions (Hidden on Mobile) -->
            <div class="nav-actions">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] == 'landlord'): ?>
                        <a href="landlord.php" class="btn btn-primary">Dashboard</a>
                    <?php elseif($_SESSION['role'] == 'boarder'): ?>
                        <a href="boarders.php" class="btn btn-primary">My Dashboard</a>
                    <?php else: ?>
                        <a href="index.php" class="btn btn-primary">Dashboard</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline" style="margin-right: 10px; display: none; /* Hide login text on tablet for space */">Log In</a>
                    <style> @media(min-width: 769px){ .nav-actions .btn-outline { display: inline-flex !important; } } </style>
                    <a href="register.php" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <header class="hero fade-up">
        <span class="hero-badge"><i class="fa-solid fa-shield-halved"></i> 100% Verified & Secure</span>
        <h1>Find Your Safe Haven <br> <span>Near Campus</span></h1>
        <p>Browse verified boarding houses for students and employees. Safe bookings, transparent pricing.</p>
        
        <!-- SEARCH -->
        <div class="search-container">
            <div class="search-box">
                <div class="search-icon-wrapper">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" id="searchInput" class="search-input" placeholder="Search city, street, or dorm name...">
                <button class="search-btn" onclick="filterRooms()">Search</button>
            </div>

            <!-- Trust Indicators -->
            <div class="trust-section">
              
            </div>
        </div>
    </header>

    <!-- LISTINGS SECTION -->
    <main class="listings-container" id="listings">
        <div class="section-header fade-up">
            <h2>Available Safe Spaces</h2>
            <p style="color: var(--text-light); font-size: 1rem;">Hand-picked listings prioritizing your safety.</p>
        </div>

        <div class="grid" id="roomsGrid">
            <?php if (count($availableRooms) > 0): ?>
                <?php foreach($availableRooms as $room): 
                    $amenities = !empty($room['room_amenities']) ? explode(',', $room['room_amenities']) : [];
                    
                    // STATUS LOGIC
                    $capacity = (int)$room['capacity'];
                    $currentOccupancy = (int)$room['current_occupancy'];
                    $status = strtolower($room['status']); 
                    $remaining = $capacity - $currentOccupancy;
                    
                    $badgeClass = 'status-available';
                    $badgeText = 'Available';
                    $progressClass = '';
                    
                    if ($remaining <= 0 || $status === 'full' || $status === 'unavailable') {
                        $badgeClass = 'status-full';
                        $badgeText = 'Fully Occupied';
                        $progressClass = 'full';
                    } elseif ($remaining < ($capacity / 2)) {
                        $badgeClass = 'status-filling';
                        $badgeText = 'Filling Fast';
                        $progressClass = 'filling';
                    }

                    $percent = ($capacity > 0) ? ($currentOccupancy / $capacity) * 100 : 0;
                    
                    // Image Logic
                    if (!empty($room['room_image'])) {
                        $imgUrl = htmlspecialchars($room['room_image']);
                    } else {
                        $imgUrl = "https://picsum.photos/seed/room" . $room['room_id'] . "/600/400";
                    }
                ?>
                    <article class="card fade-up" 
                             data-city="<?php echo strtolower($room['city']); ?>" 
                             data-name="<?php echo strtolower($room['house_name']); ?>">
                        
                        <div class="card-image-box">
                            <div class="status-badge <?php echo $badgeClass; ?>">
                                <?php echo $badgeText; ?>
                            </div>
                            <div class="verified-badge">
                                <i class="fa-solid fa-circle-check"></i> Verified
                            </div>
                            <img src="<?php echo $imgUrl; ?>" alt="Room Image" class="card-img" loading="lazy">
                        </div>

                        <div class="card-content">
                            <div class="card-price-row">
                                <div class="price">
                                    <?php echo formatPrice($room['price_per_month']); ?>
                                    <span>/mo</span>
                                </div>
                                <span class="card-type"><?php echo htmlspecialchars($room['room_type']); ?></span>
                            </div>

                            <h3 class="card-title"><?php echo htmlspecialchars($room['house_name']); ?></h3>
                            
                            <div class="card-location">
                                <i class="fa-solid fa-location-dot"></i>
                                <?php echo htmlspecialchars($room['street_name'] . ', ' . $room['city']); ?>
                            </div>

                            <div class="occupancy-container">
                                <div class="occupancy-text">
                                    <span>Occupancy</span>
                                    <span><?php echo $currentOccupancy; ?> / <?php echo $capacity; ?></span>
                                </div>
                                <div class="progress-bg">
                                    <div class="progress-fill <?php echo $progressClass; ?>" style="width: <?php echo $percent; ?>%;"></div>
                                </div>
                            </div>

                            <div class="amenities-row">
                                <div class="amenity-dot"><i class="fa-solid fa-user-group"></i> <?php echo $capacity; ?> Pax</div>
                                <?php foreach(array_slice($amenities, 0, 2) as $amenity): ?>
                                    <div class="amenity-dot"><?php echo htmlspecialchars(trim($amenity)); ?></div>
                                <?php endforeach; ?>
                            </div>

                            <div class="card-footer">
                                <span style="font-size: 0.8rem; color: #94a3b8;">Room <?php echo htmlspecialchars($room['room_number']); ?></span>
                                
                                <?php if($remaining <= 0 || $status === 'full'): ?>
                                    <span class="view-btn disabled">Full</span>
                                <?php elseif(isset($_SESSION['user_id']) && $_SESSION['role'] == 'boarder'): ?>
                                    <a href="boarders.php?apply=<?php echo $room['room_id']; ?>" class="view-btn">
                                        View <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                <?php elseif(!isset($_SESSION['user_id'])): ?>
                                    <button class="view-btn" onclick="triggerLogin()">
                                        Book <i class="fa-solid fa-arrow-right"></i>
                                    </button>
                                <?php else: ?>
                                    <span style="font-size: 0.8rem; color: var(--text-light);">Boarders Only</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 3rem 1rem; background: white; border-radius: var(--radius-xl);">
                    <i class="fa-solid fa-magnifying-glass-location" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
                    <h3>No Rooms Found</h3>
                    <p style="color: var(--text-light);">Check back soon for new listings.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        <div class="footer-content">
            <div class="footer-brand">
                <h2>DormFinder</h2>
                <p style="font-size: 0.9rem;">The safest way to find housing near your university or office.</p>
            </div>
            <div class="footer-col">
                <h4>Company</h4>
                <ul>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Careers</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Support</h4>
                <ul>
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <ul>
                    <li><a href="#">support@dormfinder.com</a></li>
                    <li><a href="#">+63 912 345 6789</a></li>
                </ul>
            </div>
        </div>
        
        <div class="copyright" style="text-align: center; font-size: 0.85rem;">
            &copy; <?php echo date('Y'); ?> DormFinder.
        </div>

        <div class="developer-credit">
            <div class="developer-info">
                <img src="uploads/dodoy.jpg" alt="Developer" class="developer-avatar">
                <div class="developer-text">
                    <strong>Drunreb Villacampa Perez</strong>
                    <span>Code Warriors Member</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- TOAST NOTIFICATION -->
    <div id="toast" class="toast">
        <i class="fa-solid fa-circle-info" style="color: var(--primary); font-size: 1.2rem;"></i>
        <span id="toast-msg" style="font-weight: 500; font-size: 0.9rem;">Message</span>
    </div>

    <!-- JAVASCRIPT -->
    <script>
        // Mobile Menu Toggle
        function toggleMenu() {
            const nav = document.getElementById('navLinks');
            const icon = document.getElementById('menu-icon');
            nav.classList.toggle('active');
            
            // Switch icon
            if(nav.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-xmark');
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            } else {
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-bars');
                document.body.style.overflow = '';
            }
        }

        // Toast Logic
        function showToast(message) {
            const toast = document.getElementById('toast');
            const msgSpan = document.getElementById('toast-msg');
            msgSpan.innerText = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function triggerLogin() {
            showToast("Please login to book a room.");
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 1500);
        }

        // Search Logic
        function filterRooms() {
            const query = document.getElementById('searchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.card');
            let count = 0;

            cards.forEach(card => {
                const city = card.getAttribute('data-city');
                const name = card.getAttribute('data-name');
                
                if (city.includes(query) || name.includes(query)) {
                    card.style.display = 'flex';
                    // Small animation for re-appearing items
                    card.style.opacity = '0';
                    setTimeout(() => card.style.opacity = '1', 50);
                    count++;
                } else {
                    card.style.display = 'none';
                }
            });

            if(count === 0) {
                // Optional: handle no results state
            }
        }

        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                filterRooms();
                // Close mobile keyboard
                this.blur();
            }
        });
    </script>
</body>
</html>