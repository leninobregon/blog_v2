<?php 
if(session_status() === PHP_SESSION_NONE) session_start();
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
include 'includes/functions.php';

// Handle language switch
if(isset($_GET['lang']) && in_array($_GET['lang'], ['es', 'en'])) {
    setLanguage($_GET['lang']);
    $redirectUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    header('Location: ' . $redirectUrl);
    exit;
}

// Log visit
$currentPage = $_SERVER['REQUEST_URI'] ?? '';
$clientIP = $_SERVER['REMOTE_ADDR'] ?? '';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
logVisit($currentPage, $clientIP, $userAgent, $referer);

$currentTheme = getActiveTheme();
$colors = getThemeColors($currentTheme);
$currentLang = getActiveLanguage();
$lang = getLanguageStrings($currentLang);
$loggedUser = isset($_SESSION['user_id']) ? getUser($_SESSION['user_id']) : null;
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= CONFIG['site_name'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        <?= renderStyles() ?>
        :root {
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.12);
            --shadow-lg: 0 8px 40px rgba(0,0,0,0.15);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
            --transition: all 0.3s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: var(--font-main); 
            background: var(--bg); 
            color: var(--text); 
            line-height: 1.7;
            min-height: 100vh;
        }
        
        /* Navbar */
        .navbar {
            background: var(--header-bg);
            padding: 0.8rem 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-md);
        }
        .navbar-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .navbar-brand {
            color: var(--header-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        .brand-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--accent), var(--primary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .brand-text {
            display: flex;
            flex-direction: column;
        }
        .brand-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--header-text);
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        .brand-tagline {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.95);
            font-weight: 600;
            letter-spacing: 0.5px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .nav-btn {
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--header-text);
            background: rgba(255,255,255,0.1);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        .nav-btn:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }
        
        /* Theme Switcher */
        .theme-dropdown {
            position: relative;
        }
        .theme-btn {
            padding: 0.5rem 1rem;
            border: 1px solid rgba(255,255,255,0.3);
            background: transparent;
            color: var(--header-text);
            cursor: pointer;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }
        .theme-btn:hover { background: rgba(255,255,255,0.2); }
        .theme-colors {
            display: flex;
            gap: 0.3rem;
            padding: 0.5rem;
        }
        .theme-color {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: var(--transition);
        }
        .theme-color:hover, .theme-color.active { transform: scale(1.2); border-color: white; }
        
        /* Theme floating button */
        .theme-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 8px;
            background: var(--bg-secondary);
            padding: 10px 14px;
            border-radius: 50px;
            box-shadow: var(--shadow-lg);
            z-index: 999;
            flex-wrap: wrap;
            max-width: 320px;
            justify-content: center;
        }
        .theme-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        .theme-dot:hover, .theme-dot.active { transform: scale(1.2); border-color: var(--text); }
        
        /* Categories */
        .categories {
            background: var(--bg-secondary);
            padding: 1rem 2rem;
            border-bottom: 1px solid var(--border);
        }
        .categories-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        .cat-btn {
            padding: 0.5rem 1.2rem;
            border: 1px solid var(--border);
            border-radius: 50px;
            color: var(--text);
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.9rem;
        }
        .cat-btn:hover, .cat-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        /* Container */
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        /* Post Cards */
        .post-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        .post-card h2 {
            color: var(--primary);
            margin-bottom: 0.8rem;
            font-size: 1.6rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .post-card h2 i { color: var(--accent); }
        .post-card .meta {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .post-card .meta span {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .post-card img {
            max-width: 100%;
            height: auto;
            border-radius: var(--radius-md);
            margin: 1.5rem 0;
            box-shadow: var(--shadow-md);
        }
        .post-card .excerpt {
            color: var(--text);
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }
        .post-card .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.5rem;
            font-weight: 500;
        }
        
        /* Post Content */
        .post-content {
            line-height: 1.9;
            font-size: 1.05rem;
        }
        .post-content h2, .post-content h3 {
            color: var(--primary);
            margin: 2rem 0 1rem;
            font-size: 1.4rem;
        }
        .post-content pre {
            background: var(--code-bg);
            color: var(--code-text);
            padding: 1.5rem;
            border-radius: var(--radius-md);
            overflow-x: auto;
            font-family: var(--font-code);
            margin: 1.5rem 0;
            border: 1px solid var(--border);
            font-size: 0.9rem;
            line-height: 1.6;
        }
        .post-content code.inline {
            background: var(--code-bg);
            color: var(--code-text);
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            font-family: var(--font-code);
        }
        .post-content blockquote {
            border-left: 4px solid var(--primary);
            padding: 1rem 1.5rem;
            background: var(--bg);
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            color: var(--text-secondary);
            margin: 1.5rem 0;
        }
        .post-content ul, .post-content ol {
            margin: 1rem 0 1rem 1.5rem;
        }
        .post-content li {
            margin-bottom: 0.5rem;
        }
        .post-content a {
            color: var(--link);
            text-decoration: underline;
        }
        .post-image {
            max-width: 100%;
            border-radius: var(--radius-md);
            margin: 1.5rem 0;
        }
        .video-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            margin: 1.5rem 0;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
        }
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: var(--radius-md);
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary);
            color: var(--header-text);
            padding: 0.6rem 1.2rem;
            text-decoration: none;
            border-radius: var(--radius-sm);
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
        }
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .btn-secondary { background: var(--secondary); }
        
        /* Comments */
        .comments-section {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-top: 2rem;
        }
        .comments-section h3 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .comment-form textarea {
            width: 100%;
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--bg);
            color: var(--text);
            min-height: 120px;
            margin-bottom: 1rem;
            font-family: inherit;
            resize: vertical;
        }
        .comment {
            background: var(--bg);
            padding: 1.2rem;
            border-radius: var(--radius-md);
            margin-bottom: 1rem;
            border: 1px solid var(--border);
        }
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            color: var(--primary);
            font-weight: 500;
        }
        .comment-date {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        .login-prompt {
            background: var(--bg);
            padding: 1.5rem;
            border-radius: var(--radius-md);
            text-align: center;
        }
        
        /* Footer */
        footer {
            background: var(--bg-secondary);
            color: var(--text-secondary);
            text-align: center;
            padding: 3rem 2rem;
            margin-top: 4rem;
            border-top: 1px solid var(--border);
        }
        footer a { color: var(--link); }
        
        /* Newsletter */
        .newsletter-section {
            background: var(--primary);
            padding: 3rem 1rem;
            margin-top: 2rem;
        }
        .newsletter-content {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
            flex-wrap: wrap;
        }
        .newsletter-text h3 { color: white; font-size: 1.5rem; margin-bottom: 0.5rem; }
        .newsletter-text p { color: rgba(255,255,255,0.9); }
        .newsletter-form { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .newsletter-form input { padding: 0.8rem 1rem; border: none; border-radius: 8px; font-size: 1rem; min-width: 200px; }
        .newsletter-form button { padding: 0.8rem 1.5rem; background: var(--header-bg); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .newsletter-form button:hover { transform: translateY(-2px); }
        
        /* Footer */
        footer { background: var(--header-bg); color: var(--header-text); padding: 2rem 1rem; margin-top: 0; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 2rem; margin-bottom: 2rem; }
        .footer-section h4 { color: var(--accent); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .footer-section p { color: rgba(255,255,255,0.8); font-size: 0.9rem; }
        .footer-section a { display: flex; align-items: center; gap: 0.5rem; color: rgba(255,255,255,0.8); text-decoration: none; padding: 0.3rem 0; font-size: 0.9rem; }
        .footer-section a:hover { color: var(--accent); }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.2); padding-top: 1.5rem; text-align: center; color: rgba(255,255,255,0.6); font-size: 0.9rem; }
        
        /* Language Switcher */
        .lang-switcher {
            display: flex;
            gap: 0.3rem;
        }
        .lang-switcher .nav-btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
        .lang-switcher .nav-btn.active {
            background: var(--accent);
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .navbar { padding: 0.8rem 1rem; }
            .navbar-content { flex-direction: column; gap: 0.8rem; }
            .navbar-menu { width: 100%; justify-content: center; }
            .categories { padding: 1rem; }
            .categories-content { justify-content: center; }
            .container { padding: 1rem; }
            .post-card { padding: 1.2rem; }
            .post-card h2 { font-size: 1.3rem; }
            .post-content pre { padding: 1rem; font-size: 0.85rem; }
            .newsletter-content { flex-direction: column; text-align: center; }
            .footer-grid { grid-template-columns: 1fr !important; text-align: center; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="index.php" class="navbar-brand">
                <div class="brand-icon"><i class="fas fa-blog"></i></div>
                <div class="brand-text">
                    <span class="brand-name"><?= CONFIG['site_name'] ?></span>
                    <span class="brand-tagline"><?= CONFIG['description'] ?></span>
                </div>
            </a>
            <div class="navbar-menu">
                <?php if($loggedUser): ?>
                    <a href="profile.php" class="nav-btn">
                        <?php if(!empty($loggedUser['avatar'])): ?>
                        <img src="<?= htmlspecialchars($loggedUser['avatar']) ?>" alt="" style="width:24px;height:24px;border-radius:50%;object-fit:cover;">
                        <?php else: ?>
                        <i class="fas fa-user"></i>
                        <?php endif; ?>
                        <?= htmlspecialchars($loggedUser['username']) ?>
                    </a>
                    <?php if(canPost($loggedUser)): ?>
                    <a href="user/" class="nav-btn"><i class="fas fa-pen"></i> <?= $currentLang === 'es' ? 'Mi Panel' : 'My Panel' ?></a>
                    <?php endif; ?>
                    <?php if($loggedUser['role'] === 'admin'): ?>
                    <a href="admin/" class="nav-btn" title="Admin"><i class="fas fa-cog"></i></a>
                    <?php endif; ?>
                    <a href="user/logout.php" class="nav-btn"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="auth.php" class="nav-btn"><i class="fas fa-sign-in-alt"></i> <?= t('nav_login') ?></a>
                <?php endif; ?>
                
                <div class="theme-dropdown">
                    <button class="theme-btn">
                        <i class="fas fa-palette"></i> <span class="theme-label"><?= t('nav_categories') ?></span>
                    </button>
                </div>
                <div class="lang-switcher">
                    <a href="?lang=es" class="nav-btn <?= $currentLang === 'es' ? 'active' : '' ?>" title="Español">🇪🇸 ES</a>
                    <a href="?lang=en" class="nav-btn <?= $currentLang === 'en' ? 'active' : '' ?>" title="English">🇬🇧 EN</a>
                </div>
                <a href="about.php" class="nav-btn" title="<?= t('nav_about') ?>"><i class="fas fa-user-circle"></i></a>
                <a href="https://www.youtube.com/@leninobregonespinoza2160" target="_blank" class="nav-btn" title="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
    </nav>
    
    <div class="categories">
        <div class="categories-content">
            <a href="index.php" class="cat-btn"><i class="fas fa-home"></i> <?= t('nav_home') ?></a>
            <?php foreach(getCategories() as $cat): ?>
            <a href="index.php?cat=<?= urlencode($cat) ?>" class="cat-btn"><?= htmlspecialchars($cat) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="theme-float">
        <div class="theme-dot" onclick="setTheme('white')" title="Blanco" style="background: linear-gradient(135deg,#fff,#e2e8f0)"></div>
        <div class="theme-dot" onclick="setTheme('blue')" title="Azul" style="background: linear-gradient(135deg,#2563eb,#1e40af)"></div>
        <div class="theme-dot" onclick="setTheme('dark-blue')" title="Azul Oscuro" style="background: linear-gradient(135deg,#3b82f6,#1e3a8a)"></div>
        <div class="theme-dot" onclick="setTheme('black')" title="Negro" style="background: linear-gradient(135deg,#333,#000)"></div>
        <div class="theme-dot" onclick="setTheme('green')" title="Verde" style="background: linear-gradient(135deg,#38a169,#276749)"></div>
        <div class="theme-dot" onclick="setTheme('red')" title="Rojo" style="background: linear-gradient(135deg,#e53e3e,#c53030)"></div>
        <div class="theme-dot" onclick="setTheme('purple')" title="Morado" style="background: linear-gradient(135deg,#805ad5,#6b46c1)"></div>
        <div class="theme-dot" onclick="setTheme('orange')" title="Naranja" style="background: linear-gradient(135deg,#ea580c,#9a3412)"></div>
        <div class="theme-dot" onclick="setTheme('pink')" title="Rosa" style="background: linear-gradient(135deg,#d53f8c,#97266d)"></div>
        <div class="theme-dot" onclick="setTheme('teal')" title="Verde Azulado" style="background: linear-gradient(135deg,#0d9488,#0f766e)"></div>
        <div class="theme-dot" onclick="setTheme('yellow')" title="Amarillo" style="background: linear-gradient(135deg,#eab308,#a16207)"></div>
        <div class="theme-dot" onclick="setTheme('cyan')" title="Cian" style="background: linear-gradient(135deg,#06b6d4,#0e7490)"></div>
        <div class="theme-dot" onclick="setTheme('brown')" title="Marrón" style="background: linear-gradient(135deg,#d97706,#92400e)"></div>
        <div class="theme-dot" onclick="setTheme('indigo')" title="Índigo" style="background: linear-gradient(135deg,#6366f1,#4338ca)"></div>
        <div class="theme-dot" onclick="setTheme('lime')" title="Lima" style="background: linear-gradient(135deg,#84cc16,#4d7c0f)"></div>
        <div class="theme-dot" onclick="setTheme('amber')" title="Ámbar" style="background: linear-gradient(135deg,#f59e0b,#b45309)"></div>
        <div class="theme-dot" onclick="setTheme('rose')" title="Rojo Rosa" style="background: linear-gradient(135deg,#f43f5e,#be123c)"></div>
        <div class="theme-dot" onclick="setTheme('slate')" title="Pizarra" style="background: linear-gradient(135deg,#475569,#334155)"></div>
        <div class="theme-dot" onclick="setTheme('emerald')" title="Esmeralda" style="background: linear-gradient(135deg,#10b981,#047857)"></div>
        <div class="theme-dot" onclick="setTheme('sky')" title="Cielo" style="background: linear-gradient(135deg,#0ea5e9,#0369a1)"></div>
        <div class="theme-dot" onclick="setTheme('violet')" title="Violeta" style="background: linear-gradient(135deg,#8b5cf6,#6d28d9)"></div>
    </div>
    
    <script>
        function setTheme(theme) {
            document.cookie = 'theme=' + theme + '; path=/; max-age=31536000';
            location.reload();
        }
        var currentTheme = '<?= $currentTheme ?>';
        document.querySelectorAll('.theme-color').forEach(function(el) {
            if(el.classList.contains('theme-' + currentTheme)) {
                el.classList.add('active');
            }
        });
    </script>
