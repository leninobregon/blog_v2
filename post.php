<?php 
ob_start();
session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
include 'header.php'; 
$post = getPost((int)$_GET['id']);
if(!$post) { 
    echo '<main class="container"><div class="post-card" style="text-align:center;"><i class="fas fa-exclamation-triangle" style="font-size:3rem;color:var(--text-secondary);"></i><h3>Publicación no encontrada</h3><a href="index.php" class="btn"><i class="fas fa-arrow-left"></i> Volver</a></div></main>'; 
    include 'footer.php'; 
    exit; 
}
incrementViews($post['id']);
incrementTotalHits();
$content = parseMarkdown($post['content']);
$comments = getComments($post['id']);
$pdo = getDB();

// Tiempo de lectura (200 palabras por minuto)
$wordCount = str_word_count(strip_tags($post['content']));
$readingTime = max(1, ceil($wordCount / 200));

// Posts relacionados (misma categoría)
$relatedPosts = $pdo->prepare("SELECT id, title, category, created_at FROM posts WHERE category = ? AND id != ? ORDER BY created_at DESC LIMIT 3");
$relatedPosts->execute([$post['category'], $post['id']]);
$relatedPosts = $relatedPosts->fetchAll(PDO::FETCH_ASSOC);

// Posts más vistos
$mostViewed = $pdo->query("SELECT id, title, views FROM posts ORDER BY views DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Extraer headings para tabla de contenidos
preg_match_all('/^#{1,3} (.+)$/m', $post['content'], $headings);
$toc = [];
if(!empty($headings[0])) {
    foreach($headings[1] as $i => $heading) {
        $level = preg_match('/^#+/', $headings[0][$i]);
        $slug = strtolower(str_replace([' ', 'á','é','í','ó','ú','ñ'], ['','-','e','i','o','u','n'], $heading));
        $toc[] = ['text' => $heading, 'level' => $level, 'slug' => $slug];
    }
}

// URL actual para compartir
$currentUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$shareTitle = urlencode($post['title']);
$shareUrl = urlencode($currentUrl);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment']) && $loggedUser) {
    $comment = trim($_POST['comment'] ?? '');
    if($comment) {
        addComment($post['id'], $_SESSION['user_id'], $comment);
        ob_end_clean();
        header("Location: post.php?id={$post['id']}");
        exit;
    }
}
ob_end_flush();
?>
    <!-- Barra de progreso -->
    <div class="reading-progress" id="readingProgress"></div>
    
    <!-- Visit Counter Badge -->
    <div class="visit-counter">
        <i class="fas fa-eye"></i> <?= number_format(getTotalHits()) ?> <?= $currentLang === 'es' ? 'visitas' : 'views' ?>
    </div>
    
    <main class="container post-layout">
        <article class="post-card">
            <h1 style="font-size:1.8rem;"><?= htmlspecialchars($post['title']) ?></h1>
            <div class="meta">
                <span><i class="fas fa-folder"></i> <?= htmlspecialchars($post['category']) ?></span>
                <?php if(!empty($post['author_name'])): ?>
                <span><i class="fas fa-user"></i> <?= htmlspecialchars($post['author_name']) ?></span>
                <?php endif; ?>
                <span><i class="fas fa-calendar-alt"></i> <?= strftime('%d %b, %Y', strtotime($post['created_at'])) ?></span>
                <span><i class="fas fa-clock"></i> <?= $readingTime ?> <?= t('post_reading_time') ?></span>
                <span><i class="fas fa-eye"></i> <?= number_format($post['views']) ?> <?= t('post_views') ?></span>
            </div>
            
            <?php if($post['image']): ?>
            <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="post-image">
            <?php endif; ?>
            
            <!-- Tabla de contenidos -->
            <?php if(count($toc) > 2): ?>
            <div class="toc-box" id="tocBox">
                <div class="toc-header" onclick="toggleTOC()">
                    <span><i class="fas fa-list"></i> <?= t('post_contents') ?></span>
                    <i class="fas fa-chevron-down" id="tocToggle"></i>
                </div>
                <ul class="toc-list" id="tocList">
                    <?php foreach($toc as $item): ?>
                    <li class="toc-item toc-level-<?= $item['level'] ?>">
                        <a href="#<?= $item['slug'] ?>"><?= htmlspecialchars($item['text']) ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="post-content" spellcheck="true">
                <?= $content ?>
            </div>
            
            <?php if($post['video']): 
                $videoUrl = $post['video'];
                if(preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
                    $videoUrl = 'https://www.youtube.com/embed/' . $matches[1];
                } elseif(preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
                    $videoUrl = 'https://www.youtube.com/embed/' . $matches[1];
                }
            ?>
            <div class="video-container">
                <iframe src="<?= htmlspecialchars($videoUrl) ?>" frameborder="0" allowfullscreen></iframe>
            </div>
            <?php endif; ?>
            
            <!-- Botones de compartir -->
            <div class="share-box">
                <p class="share-title"><i class="fas fa-share-alt"></i> <?= t('post_share') ?></p>
                <div class="share-buttons">
                    <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" target="_blank" class="share-btn whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    <a href="https://t.me/share/url?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>" target="_blank" class="share-btn telegram"><i class="fab fa-telegram"></i> Telegram</a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" class="share-btn facebook"><i class="fab fa-facebook"></i> Facebook</a>
                    <a href="https://twitter.com/intent/tweet?text=<?= $shareTitle ?>&url=<?= $shareUrl ?>" target="_blank" class="share-btn twitter"><i class="fab fa-twitter"></i> Twitter</a>
                    <button class="share-btn copy-url" onclick="copyUrl()"><i class="fas fa-link"></i> <?= $currentLang === 'es' ? 'Copiar URL' : 'Copy URL' ?></button>
                </div>
            </div>
            
            <a href="index.php" class="btn btn-secondary" style="margin-top:2rem;"><i class="fas fa-arrow-left"></i> <?= $currentLang === 'es' ? 'Volver' : 'Back' ?></a>
        </article>
        
        <!-- Sidebar del post -->
        <aside class="post-sidebar">
            <!-- Botón volver -->
            <div class="sidebar-widget" style="text-align:center;">
                <a href="index.php" class="btn" style="width:100%;justify-content:center;">
                    <i class="fas fa-arrow-left"></i> <?= $currentLang === 'es' ? 'Volver al Blog' : 'Back to Blog' ?>
                </a>
            </div>
            
            <!-- Buscador -->
            <div class="sidebar-widget">
                <h3 class="widget-title"><i class="fas fa-search"></i> <?= t('nav_search') ?></h3>
                <form method="get" action="index.php" class="sidebar-search">
                    <input type="text" name="search" placeholder="<?= t('nav_search') ?>...">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            
            <!-- Categorías -->
            <?php 
            $categories = getCategories();
            $categoriesCount = count($categories);
            ?>
            <div class="sidebar-widget">
                <h3 class="widget-title"><i class="fas fa-folder"></i> <?= t('sidebar_categories') ?></h3>
                <ul class="category-list">
                    <?php foreach($categories as $cat): ?>
                    <li><a href="index.php?cat=<?= urlencode($cat) ?>">
                        <i class="fas fa-angle-right"></i> <?= htmlspecialchars($cat) ?>
                    </a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <?php if(!empty($relatedPosts)): ?>
            <div class="sidebar-widget">
                <h3 class="widget-title"><i class="fas fa-bookmark"></i> <?= t('post_related') ?></h3>
                <?php foreach($relatedPosts as $rp): ?>
                <a href="post.php?id=<?= $rp['id'] ?>" class="related-item">
                    <span class="related-title"><?= htmlspecialchars($rp['title']) ?></span>
                    <span class="related-date"><?= strftime('%d %b %Y', strtotime($rp['created_at'])) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="sidebar-widget">
                <h3 class="widget-title"><i class="fas fa-fire"></i> <?= $currentLang === 'es' ? 'Más Vistos' : 'Most Viewed' ?></h3>
                <?php foreach($mostViewed as $mv): ?>
                <a href="post.php?id=<?= $mv['id'] ?>" class="popular-item">
                    <span class="popular-views"><?= number_format($mv['views']) ?></span>
                    <span class="popular-title"><?= htmlspecialchars(mb_substr($mv['title'], 0, 40)) ?>...</span>
                </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Archivos -->
            <?php
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
            ?>
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
            
            <!-- Estadísticas -->
            <?php
            $totalPosts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
            $totalHits = getTotalHits();
            $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            ?>
            <div class="sidebar-widget">
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
                        <div class="stat-number"><?= $categoriesCount ?></div>
                        <div class="stat-label"><?= $currentLang === 'es' ? 'Categorías' : 'Categories' ?></div>
                    </div>
                </div>
            </div>
                    </div>
                </div>
            </div>
            
            <div class="sidebar-widget share-sidebar">
                <h3 class="widget-title"><i class="fas fa-share-alt"></i> <?= t('post_share') ?></h3>
                <div class="share-mini">
                    <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>" target="_blank" class="share-mini-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://t.me/share/url?url=<?= $shareUrl ?>" target="_blank" class="share-mini-btn telegram"><i class="fab fa-telegram"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>" target="_blank" class="share-mini-btn facebook"><i class="fab fa-facebook"></i></a>
                    <a href="https://twitter.com/intent/tweet?text=<?= $shareTitle ?>&url=<?= $shareUrl ?>" target="_blank" class="share-mini-btn twitter"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </aside>
    </main>
    
    <!-- Comentarios (debajo) -->
    <main class="container">
        <section class="comments-section">
            <h3><i class="fas fa-comments"></i> <?= t('comments_title') ?> (<?= count($comments) ?>)</h3>
            
            <?php if($loggedUser): ?>
            <form method="post" class="comment-form">
                <textarea name="comment" placeholder="<?= t('comments_write') ?>" required></textarea>
                <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> <?= t('send') ?></button>
            </form>
            <?php else: ?>
            <div class="login-prompt">
                <i class="fas fa-info-circle"></i> 
                <a href="auth.php"><?= $currentLang === 'es' ? 'Inicia sesión' : 'Login' ?></a> <?= $currentLang === 'es' ? 'para comentar' : 'to comment' ?>
            </div>
            <?php endif; ?>
            
            <div class="comments-list" style="margin-top:1.5rem;">
                <?php foreach($comments as $comment): ?>
                <div class="comment">
                    <div class="comment-header">
                        <span><i class="fas fa-user"></i> <?= htmlspecialchars($comment['username']) ?></span>
                        <span class="comment-date"><?= strftime('%d %b, %Y %H:%M', strtotime($comment['created_at'])) ?></span>
                    </div>
                    <div style="margin-top:0.5rem;"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($comments)): ?>
                <p style="text-align:center;color:var(--text-secondary);padding:2rem;"><?= t('comments_empty') ?></p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <!-- Botón volver arriba -->
    <button class="back-to-top" id="backToTop" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <?php include 'footer.php'; ?>
    
    <style>
        /* Barra de progreso */
        .reading-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 4px;
            background: var(--primary);
            z-index: 9999;
            transition: width 0.1s;
        }
        
        /* Visit Counter Badge */
        .visit-counter {
            position: fixed;
            top: 70px;
            left: 20px;
            background: var(--primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: var(--shadow-md);
            z-index: 998;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .visit-counter i {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Layout post + sidebar */
        .post-layout {
            display: flex;
            gap: 2rem;
        }
        
        .post-card { flex: 1; }
        .post-sidebar { width: 320px; flex-shrink: 0; }
        
        /* Tiempo lectura y vistas */
        .meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .post-image {
            width: 100%;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
        }
        
        /* Tabla de contenidos */
        .toc-box {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1rem;
            margin-bottom: 2rem;
        }
        .toc-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-weight: 600;
            color: var(--primary);
        }
        .toc-list {
            list-style: none;
            margin-top: 1rem;
            padding-left: 0;
        }
        .toc-list.collapsed { display: none; }
        .toc-item { padding: 0.3rem 0; }
        .toc-item a { color: var(--text); text-decoration: none; font-size: 0.9rem; }
        .toc-item a:hover { color: var(--primary); }
        .toc-level-1 { padding-left: 0; font-weight: 500; }
        .toc-level-2 { padding-left: 1rem; }
        .toc-level-3 { padding-left: 2rem; font-size: 0.85rem; }
        
        /* Botones compartir */
        .share-box {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: center;
        }
        .share-title {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        .share-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }
        .share-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            border-radius: var(--radius-sm);
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        .share-btn:hover { transform: translateY(-2px); opacity: 0.9; }
        .share-btn.whatsapp { background: #25d366; }
        .share-btn.telegram { background: #0088cc; }
        .share-btn.facebook { background: #1877f2; }
        .share-btn.twitter { background: #1da1f2; }
        .share-btn.copy-url { background: var(--secondary); }
        
        /* Share mini en sidebar */
        .share-mini {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .share-mini-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: white;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        .share-mini-btn:hover { transform: scale(1.1); }
        .share-mini-btn.whatsapp { background: #25d366; }
        .share-mini-btn.telegram { background: #0088cc; }
        .share-mini-btn.facebook { background: #1877f2; }
        .share-mini-btn.twitter { background: #1da1f2; }
        
        /* Sidebar widgets */
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
        }
        .related-item, .popular-item {
            display: block;
            padding: 0.8rem 0;
            border-bottom: 1px solid var(--border);
            text-decoration: none;
            color: var(--text);
            transition: all 0.3s;
        }
        .related-item:hover, .popular-item:hover { color: var(--primary); }
        .related-item:last-child, .popular-item:last-child { border-bottom: none; }
        .related-title { display: block; font-weight: 500; }
        .related-date { font-size: 0.8rem; color: var(--text-secondary); }
        .popular-item { display: flex; align-items: center; gap: 0.8rem; }
        .popular-views { 
            background: var(--primary); 
            color: white; 
            padding: 0.3rem 0.6rem; 
            border-radius: var(--radius-sm); 
            font-size: 0.8rem; 
            font-weight: 600; 
        }
        .popular-title { flex: 1; font-size: 0.9rem; }
        
        /* Search box */
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
        
        /* Category list */
        .category-list, .archive-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .category-list li, .archive-list li {
            margin-bottom: 0.5rem;
        }
        .category-list a, .archive-list a {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0.8rem;
            color: var(--text);
            text-decoration: none;
            border-radius: var(--radius-sm);
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        .category-list a:hover, .archive-list a:hover {
            background: var(--primary);
            color: white;
        }
        .category-list a i, .archive-list a i {
            font-size: 0.8rem;
        }
        .archive-list .count {
            font-size: 0.8rem;
            opacity: 0.7;
            margin-left: 0.3rem;
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
            padding: 0.8rem;
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
            font-size: 1.8rem;
        }
        
        .stat-icon {
            font-size: 1.3rem;
            color: var(--primary);
            margin-bottom: 0.2rem;
        }
        
        .stat-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .stat-label {
            font-size: 0.7rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            margin-top: 0.1rem;
        }
        
        /* Botón volver arriba */
        .back-to-top {
            position: fixed;
            bottom: 80px;
            right: 30px;
            width: 45px;
            height: 45px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            box-shadow: var(--shadow-md);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 998;
        }
        .back-to-top.visible { opacity: 1; visibility: visible; }
        .back-to-top:hover { transform: translateY(-3px); }
        
        /* Código con botón copiar */
        .post-content pre {
            position: relative;
        }
        .copy-code-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            padding: 0.3rem 0.6rem;
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
            transition: all 0.2s;
        }
        .copy-code-btn:hover { background: rgba(255,255,255,0.2); }
        .copy-code-btn.copied { background: #25d366; }
        
        /* Comentarios */
        .comments-section {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            margin-top: 2rem;
        }
        .comments-section h3 { color: var(--primary); margin-bottom: 1.5rem; }
        .comment-form textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border);
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
            color: var(--primary);
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .comment-date { color: var(--text-secondary); font-weight: normal; font-size: 0.85rem; }
        .login-prompt { background: var(--bg); padding: 1.5rem; border-radius: var(--radius-md); text-align: center; color: var(--text-secondary); }
        .login-prompt a { color: var(--primary); font-weight: 600; }
        
        @media (max-width: 900px) {
            .post-layout { flex-direction: column; }
            .post-sidebar { width: 100%; }
        }
    </style>
    
    <script>
        // Barra de progreso
        window.addEventListener('scroll', function() {
            const docHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrolled = (window.scrollY / docHeight) * 100;
            document.getElementById('readingProgress').style.width = scrolled + '%';
            
            // Botón volver arriba
            const backToTop = document.getElementById('backToTop');
            if(window.scrollY > 300) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });
        
        function scrollToTop() {
            window.scrollTo({top: 0, behavior: 'smooth'});
        }
        
        // Tabla de contenidos
        function toggleTOC() {
            document.getElementById('tocList').classList.toggle('collapsed');
            document.getElementById('tocToggle').classList.toggle('fa-chevron-down');
            document.getElementById('tocToggle').classList.toggle('fa-chevron-up');
        }
        
        // Copiar URL
        function copyUrl() {
            navigator.clipboard.writeText(window.location.href);
            event.target.innerHTML = '<i class="fas fa-check"></i> <?= $currentLang === 'es' ? 'Copiado!' : 'Copied!' ?>';
            setTimeout(() => {
                event.target.innerHTML = '<i class="fas fa-link"></i> <?= $currentLang === 'es' ? 'Copiar URL' : 'Copy URL' ?>';
            }, 2000);
        }
        
        // Agregar botones copiar a bloques de código
        document.addEventListener('DOMContentLoaded', function() {
            const copyText = '<?= $currentLang === 'es' ? 'Copiar' : 'Copy' ?>';
            const copiedText = '<?= $currentLang === 'es' ? 'Copiado' : 'Copied' ?>';
            document.querySelectorAll('.post-content pre').forEach(function(pre) {
                const btn = document.createElement('button');
                btn.className = 'copy-code-btn';
                btn.innerHTML = '<i class="fas fa-copy"></i> ' + copyText;
                btn.onclick = function() {
                    const code = pre.querySelector('code').innerText;
                    navigator.clipboard.writeText(code);
                    btn.innerHTML = '<i class="fas fa-check"></i> ' + copiedText;
                    btn.classList.add('copied');
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-copy"></i> ' + copyText;
                        btn.classList.remove('copied');
                    }, 2000);
                };
                pre.appendChild(btn);
            });
        });
    </script>