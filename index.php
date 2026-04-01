<?php 
if(session_status() === PHP_SESSION_NONE) session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
include 'header.php'; 

$search = $_GET['search'] ?? '';
$cat = $_GET['cat'] ?? null;
$mes = $_GET['mes'] ?? null;
$page = (int)($_GET['page'] ?? 1);
$offset = ($page - 1) * CONFIG['posts_per_page'];
$categories = getCategories();

// Get posts - search or by category or archive
if($search) {
    $posts = searchPosts($search, 100, 0);
    $pageTitle = 'Resultados de búsqueda: ' . htmlspecialchars($search);
} elseif($mes) {
    $posts = getPostsByMonth($mes, 100, 0);
    $nombreMes = strftime('%B %Y', strtotime($mes . '-01'));
    $pageTitle = 'Archivo: ' . ucfirst($nombreMes);
} elseif($cat) {
    $posts = getPosts($cat, 100, 0);
    $pageTitle = 'Categoría: ' . htmlspecialchars($cat);
} else {
    $posts = getPosts(null, 100, 0);
    $pageTitle = 'Últimas Publicaciones';
}

// Get archives by month
$pdo = getDB();
$archives = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as mes, 
           MONTH(created_at) as mes_num,
           YEAR(created_at) as anio,
           COUNT(*) as total 
    FROM posts 
    GROUP BY mes 
    ORDER BY mes DESC
    LIMIT 12
")->fetchAll(PDO::FETCH_ASSOC);
$mesesEsp = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$mesesEng = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
$meses = $currentLang === 'en' ? $mesesEng : $mesesEsp;

// Count total posts
$totalPosts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$totalHits = getTotalHits();
$totalViews = $pdo->query("SELECT SUM(views) FROM posts")->fetchColumn();
?>
    <div class="layout-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <!-- Acerca de mí -->
            <div class="sidebar-widget about-widget">
                <h3 class="widget-title"><i class="fas fa-user-circle"></i> <?= t('sidebar_about') ?></h3>
                <p class="about-mini"><?= $currentLang === 'es' ? 'Ingeniero en Computación de Nicaragua' : 'Nicaraguan Computer Engineer' ?></p>
                <a href="about.php" class="about-link"><?= $currentLang === 'es' ? 'Ver perfil completo' : 'View full profile' ?> <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <!-- Search -->
            <div class="sidebar-widget">
                <h3 class="widget-title"><i class="fas fa-search"></i> <?= t('nav_search') ?></h3>
                <form method="get" action="index.php" class="sidebar-search">
                    <input type="text" name="search" placeholder="<?= t('nav_search') ?>..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            
            <!-- Categories -->
            <div class="sidebar-widget">
                <h3 class="widget-title"><i class="fas fa-folder"></i> <?= t('sidebar_categories') ?></h3>
                <ul class="category-list">
                    <li><a href="index.php" class="<?= !$cat && !$search ? 'active' : '' ?>">
                        <i class="fas fa-angle-right"></i> <?= $currentLang === 'es' ? 'Todas' : 'All' ?> <span class="count">(<?= $totalPosts ?>)</span>
                    </a></li>
                    <?php foreach($categories as $c): 
                        $count = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE category = ?");
                        $count->execute([$c]);
                        $total = $count->fetchColumn();
                    ?>
                    <li><a href="index.php?cat=<?= urlencode($c) ?>" class="<?= $cat === $c ? 'active' : '' ?>">
                        <i class="fas fa-angle-right"></i> <?= htmlspecialchars($c) ?> <span class="count">(<?= $total ?>)</span>
                    </a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Archives -->
            <div class="sidebar-widget">
                <h3 class="widget-title"><i class="fas fa-calendar-alt"></i> <?= t('sidebar_archives') ?></h3>
                <ul class="archive-list">
                    <?php foreach($archives as $a): ?>
                    <li>
                        <a href="index.php?mes=<?= $a['mes'] ?>">
                            <i class="fas fa-chevron-right"></i> <?= $meses[(int)$a['mes_num']] . ' ' . $a['anio'] ?> <span class="count">(<?= $a['total'] ?>)</span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <!-- Stats -->
            <div class="sidebar-widget stats-widget">
                <h3 class="widget-title"><i class="fas fa-chart-line"></i> <?= t('sidebar_stats') ?></h3>
                <div class="stats-grid">
                    <div class="stat-box stat-visits">
                        <div class="stat-icon"><i class="fas fa-eye"></i></div>
                        <div class="stat-number"><?= number_format($totalHits) ?></div>
                        <div class="stat-label"><?= $currentLang === 'es' ? 'Visitas' : 'Visits' ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                        <div class="stat-number"><?= $totalPosts ?></div>
                        <div class="stat-label"><?= $currentLang === 'es' ? 'Posts' : 'Posts' ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon"><i class="fas fa-folder"></i></div>
                        <div class="stat-number"><?= count($categories) ?></div>
                        <div class="stat-label"><?= $currentLang === 'es' ? 'Categorías' : 'Categories' ?></div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-number"><?= $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?></div>
                        <div class="stat-label"><?= $currentLang === 'es' ? 'Usuarios' : 'Users' ?></div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Category Tabs -->
            <div class="category-tabs">
                <a href="index.php" class="cat-tab <?= !$cat && !$search ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> <?= $currentLang === 'es' ? 'Todo' : 'All' ?>
                </a>
                <?php foreach($categories as $c): ?>
                <a href="index.php?cat=<?= urlencode($c) ?>" class="cat-tab <?= $cat === $c ? 'active' : '' ?>">
                    <i class="fas fa-folder"></i> <?= htmlspecialchars($c) ?>
                </a>
                <?php endforeach; ?>
            </div>
            
            <h1 class="page-title"><?= $pageTitle ?></h1>
            
            <?php if($search || $cat): ?>
            <a href="index.php" class="clear-search"><i class="fas fa-times"></i> <?= $currentLang === 'es' ? 'Limpiar filtro' : 'Clear filter' ?></a>
            <?php endif; ?>
            
            <?php if(empty($posts)): ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3><?= t('no_results') ?></h3>
                <p><?= $currentLang === 'es' ? 'Intenta con otras palabras o verifica las categorías' : 'Try different words or check categories' ?></p>
            </div>
            <?php else: ?>
            <div class="posts-timeline">
                <?php foreach($posts as $post): ?>
                <article class="post-item">
                    <div class="post-date">
                        <span class="day"><?= strftime('%d', strtotime($post['created_at'])) ?></span>
                        <span class="month"><?= strftime('%b', strtotime($post['created_at'])) ?></span>
                        <span class="year"><?= strftime('%Y', strtotime($post['created_at'])) ?></span>
                    </div>
                    <div class="post-content">
                        <div class="post-category">
                            <i class="fas fa-tag"></i> <?= htmlspecialchars($post['category']) ?>
                        </div>
                        <h2><a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
                        <p class="post-excerpt"><?= htmlspecialchars(mb_substr(strip_tags($post['content']), 0, 180)) ?>...</p>
                        <div class="post-meta">
                            <span><i class="fas fa-user"></i> <?= htmlspecialchars($post['author_name'] ?? 'Anonimo') ?></span>
                            <a href="post.php?id=<?= $post['id'] ?>" class="read-more"><?= t('read_more') ?> <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <style>
        .layout-container {
            display: flex;
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            flex-shrink: 0;
        }
        
        .sidebar-widget {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.2rem;
            margin-bottom: 1.5rem;
        }
        
        .widget-title {
            color: var(--primary);
            font-size: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .about-widget {
            background: linear-gradient(135deg, var(--primary), var(--header-bg));
            border-color: var(--primary);
        }
        
        .about-widget .widget-title {
            color: white;
            border-bottom-color: rgba(255,255,255,0.3);
        }
        
        .about-mini {
            color: white;
            font-size: 0.95rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .about-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.2);
            border-radius: var(--radius-sm);
            transition: all 0.3s;
        }
        
        .about-link:hover {
            background: rgba(255,255,255,0.3);
            transform: translateX(5px);
        }
        
        .sidebar-search {
            display: flex;
            gap: 0.3rem;
        }
        .sidebar-search input {
            flex: 1;
            padding: 0.6rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--bg);
            color: var(--text);
        }
        .sidebar-search button {
            padding: 0.6rem 0.8rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
        }
        
        .category-list, .archive-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .category-list li {
            margin-bottom: 0.8rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.8rem;
        }
        
        .category-list li:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .archive-list li {
            margin-bottom: 0.3rem;
        }
        
        .category-list a, .archive-list a {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 0.8rem;
            color: var(--text);
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: all 0.3s;
            font-size: 0.95rem;
        }
        
        .category-list a:hover, .archive-list a:hover,
        .category-list a.active, .archive-list a.active {
            background: var(--primary);
            color: white;
        }
        
        .category-list a i, .archive-list a i {
            font-size: 0.8rem;
        }
        
        .count {
            font-size: 0.8rem;
            opacity: 0.7;
            background: rgba(0,0,0,0.1);
            padding: 0.1rem 0.5rem;
            border-radius: 10px;
            margin-left: 0.3rem;
        }
        
        .category-list a:hover .count, .category-list a.active .count {
            background: rgba(255,255,255,0.3);
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8rem;
        }
        
        .stat-box {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1rem;
            text-align: center;
            transition: all 0.3s;
        }
        
        .stat-box:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        
        .stat-visits {
            grid-column: span 2;
            background: linear-gradient(135deg, var(--primary), var(--header-bg));
            border: none;
        }
        
        .stat-visits .stat-icon,
        .stat-visits .stat-number,
        .stat-visits .stat-label {
            color: white;
        }
        
        .stat-visits .stat-number {
            font-size: 2rem;
        }
        
        .stat-icon {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 0.3rem;
        }
        
        .stat-number {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin-top: 0.2rem;
        }
        
        /* Stats Widget */
        .stats-widget {
            background: linear-gradient(135deg, var(--bg-secondary), var(--bg));
            border: 2px solid var(--primary);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            min-width: 0;
        }
        
        .category-tabs {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
        }
        .cat-tab {
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            text-decoration: none;
            color: var(--text);
            background: var(--bg);
            border: 1px solid var(--border);
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        .cat-tab:hover, .cat-tab.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .page-title {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .clear-search {
            display: inline-block;
            margin-bottom: 1rem;
            color: var(--link);
            text-decoration: none;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
        }
        .empty-state i { font-size: 3rem; margin-bottom: 1rem; }
        
        .posts-timeline {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        .post-item {
            display: flex;
            gap: 1.5rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            transition: all 0.3s;
        }
        .post-item:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary);
        }
        
        .post-date {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 70px;
            padding: 0.8rem;
            background: var(--primary);
            border-radius: var(--radius-md);
            color: white;
            text-align: center;
        }
        .post-date .day { font-size: 1.5rem; font-weight: bold; }
        .post-date .month { font-size: 0.8rem; text-transform: uppercase; }
        .post-date .year { font-size: 0.7rem; opacity: 0.8; }
        
        .post-content { flex: 1; }
        .post-category {
            font-size: 0.8rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .post-content h2 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }
        .post-content h2 a {
            color: var(--text);
            text-decoration: none;
        }
        .post-content h2 a:hover { color: var(--primary); }
        .post-excerpt {
            color: var(--text-secondary);
            margin-bottom: 1rem;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        .read-more {
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-weight: 500;
        }
        
        @media (max-width: 900px) {
            .layout-container { flex-direction: column; }
            .sidebar { width: 100%; }
            .sidebar-widget { margin-bottom: 1rem; }
        }
        
        @media (max-width: 600px) {
            .post-item { flex-direction: column; }
            .post-date { flex-direction: row; gap: 0.5rem; padding: 0.5rem; }
            .category-tabs { overflow-x: auto; flex-wrap: nowrap; }
        }
    </style>
    
<?php include 'footer.php'; ?>