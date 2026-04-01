<?php
if(session_status() === PHP_SESSION_NONE) session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
if(empty($_SESSION['logged'])) { header('Location: login.php'); exit; }
include '../includes/functions.php';

$currentTheme = getActiveTheme();
$colors = getThemeColors($currentTheme);

// Statistics
$totalPosts = getTotalPosts();
$totalUsers = getTotalUsers();
$totalComments = getTotalComments();
$totalVisits = getTotalVisits();
$totalSubscribers = count(getNewsletterSubscribers(true));

// Handle backup
$backupMsg = '';
if(isset($_GET['backup'])) {
    $backupMsg = backupDatabase();
}

// Chart data
$visitStats = getVisitStats(30);
$postsByCategory = getPostsByCategory();
$usersByRole = getUsersByRole();
$topPages = getTopPages(7);
$recentPosts = getRecentPosts(5);
$recentUsers = getRecentUsers(5);
$recentComments = getRecentComments(5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= CONFIG['site_name'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            <?php foreach($colors as $k=>$v): ?><?php echo "--$k: $v;"; ?><?php endforeach; ?>
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 20px rgba(0,0,0,0.12);
            --shadow-lg: 0 8px 40px rgba(0,0,0,0.15);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); }
        
        .navbar {
            background: var(--header-bg);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .navbar h1 { color: var(--header-text); display: flex; align-items: center; gap: 0.5rem; font-size: 1.5rem; }
        .navbar nav { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .navbar a { color: var(--header-text); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: var(--radius-sm); transition: all 0.3s; font-size: 0.9rem; }
        .navbar a:hover { background: rgba(255,255,255,0.2); }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 1.5rem; }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); }
        .stat-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }
        .stat-card .icon.posts { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-card .icon.users { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .stat-card .icon.comments { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .stat-card .icon.visits { background: linear-gradient(135deg, #43e97b, #38f9d7); }
        .stat-card .icon.subscribers { background: linear-gradient(135deg, #fa709a, #fee140); }
        .stat-card .number { font-size: 2.5rem; font-weight: 700; color: var(--primary); }
        .stat-card .label { color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.5rem; }
        
        /* Charts */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .chart-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }
        .chart-card h3 { 
            margin-bottom: 1rem; 
            color: var(--primary); 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
            font-size: 1.1rem;
        }
        .chart-container { position: relative; height: 300px; }
        
        /* Tables */
        .card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }
        .card h3 { 
            margin-bottom: 1rem; 
            color: var(--primary); 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
            font-size: 1.1rem;
        }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.8rem; text-align: left; border-bottom: 1px solid var(--border); }
        th { color: var(--text-secondary); font-weight: 500; font-size: 0.85rem; text-transform: uppercase; }
        tr:hover { background: var(--bg); }
        td { font-size: 0.9rem; }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-admin { background: #fee2e2; color: #dc2626; }
        .badge-author { background: #dbeafe; color: #2563eb; }
        .badge-user { background: #d1fae5; color: #059669; }
        
        .time-ago { color: var(--text-secondary); font-size: 0.8rem; }
        
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.2rem; background: var(--primary); color: white; text-decoration: none; border-radius: var(--radius-sm); border: none; cursor: pointer; font-weight: 500; transition: all 0.3s; }
        .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-secondary { background: var(--secondary); }
        
        .toolbar { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        
        /* Theme switcher */
        .theme-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 8px;
            background: var(--bg-secondary);
            padding: 8px 12px;
            border-radius: 50px;
            box-shadow: var(--shadow-lg);
            z-index: 999;
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
        
        @media (max-width: 768px) {
            .navbar { flex-direction: column; text-align: center; }
            .container { padding: 1rem; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-grid { grid-template-columns: 1fr; }
            .stat-card .number { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
        <nav>
            <a href="../index.php"><i class="fas fa-home"></i> Inicio</a>
            <a href="index.php?action=new"><i class="fas fa-plus"></i> Nueva</a>
            <a href="users.php"><i class="fas fa-users"></i> Usuarios</a>
            <a href="newsletter.php"><i class="fas fa-envelope"></i> Newsletter</a>
            <a href="about.php"><i class="fas fa-user-circle"></i> Acerca de</a>
            <a href="config.php"><i class="fas fa-cog"></i> Config</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </nav>
    </nav>
    
    <main class="container">
        <?php if($backupMsg): ?>
        <?php 
            $backupFile = '';
            if(preg_match('/<strong>(.+\.sql)<\/strong>/', $backupMsg, $matches)) {
                $backupFile = $matches[1];
            }
            ?>
        <div style="background:#d1fae5;color:#065f46;padding:1rem;border-radius:var(--radius-sm);margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
            <i class="fas fa-check-circle"></i> <?= $backupMsg ?>
            <?php if($backupFile): ?>
            <a href="download_backup.php?file=<?= urlencode($backupFile) ?>" style="margin-left:auto;color:#065f46;font-weight:600;"><i class="fas fa-download"></i> Descargar</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Toolbar -->
        <div class="toolbar">
            <a href="index.php?action=new" class="btn"><i class="fas fa-plus"></i> Nueva Publicación</a>
            <a href="?backup=1" class="btn btn-secondary" onclick="return confirm('¿Crear respaldo de la base de datos?')"><i class="fas fa-database"></i> Respaldar DB</a>
            <a href="config.php" class="btn btn-secondary"><i class="fas fa-cog"></i> Configuración</a>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="icon posts"><i class="fas fa-file-alt"></i></div>
                <div class="number"><?= $totalPosts ?></div>
                <div class="label">Publicaciones</div>
            </div>
            <div class="stat-card">
                <div class="icon users"><i class="fas fa-users"></i></div>
                <div class="number"><?= $totalUsers ?></div>
                <div class="label">Usuarios</div>
            </div>
            <div class="stat-card">
                <div class="icon comments"><i class="fas fa-comments"></i></div>
                <div class="number"><?= $totalComments ?></div>
                <div class="label">Comentarios</div>
            </div>
            <div class="stat-card">
                <div class="icon visits"><i class="fas fa-eye"></i></div>
                <div class="number"><?= number_format($totalVisits) ?></div>
                <div class="label">Visitas Totales</div>
            </div>
            <div class="stat-card">
                <div class="icon subscribers"><i class="fas fa-envelope-open-text"></i></div>
                <div class="number"><?= $totalSubscribers ?></div>
                <div class="label">Suscriptores</div>
            </div>
        </div>
        
        <!-- Charts -->
        <div class="charts-grid">
            <div class="chart-card">
                <h3><i class="fas fa-chart-line"></i> Visitas Últimos 30 Días</h3>
                <div class="chart-container">
                    <canvas id="visitsChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h3><i class="fas fa-chart-pie"></i> Publicaciones por Categoría</h3>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h3><i class="fas fa-chart-bar"></i> Páginas Más Visitadas</h3>
                <div class="chart-container">
                    <canvas id="pagesChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h3><i class="fas fa-chart-doughnut"></i> Usuarios por Rol</h3>
                <div class="chart-container">
                    <canvas id="rolesChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
            <!-- Recent Posts -->
            <div class="card">
                <h3><i class="fas fa-file-alt"></i> Publicaciones Recientes</h3>
                <?php if(empty($recentPosts)): ?>
                <p style="color: var(--text-secondary);">No hay publicaciones</p>
                <?php else: ?>
                <table>
                    <tbody>
                        <?php foreach($recentPosts as $post): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars(mb_substr($post['title'], 0, 30)) ?>...</strong><br>
                                <span class="time-ago"><?= htmlspecialchars($post['author_name'] ?? 'Sistema') ?></span>
                            </td>
                            <td style="text-align: right;">
                                <span class="time-ago"><?= strftime('%d/%b', strtotime($post['created_at'])) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            
            <!-- Recent Users -->
            <div class="card">
                <h3><i class="fas fa-user-plus"></i> Usuarios Recientes</h3>
                <?php if(empty($recentUsers)): ?>
                <p style="color: var(--text-secondary);">No hay usuarios</p>
                <?php else: ?>
                <table>
                    <tbody>
                        <?php foreach($recentUsers as $user): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($user['username']) ?></strong><br>
                                <span class="time-ago"><?= htmlspecialchars($user['email']) ?></span>
                            </td>
                            <td style="text-align: right;">
                                <span class="badge badge-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            
            <!-- Recent Comments -->
            <div class="card">
                <h3><i class="fas fa-comments"></i> Comentarios Recientes</h3>
                <?php if(empty($recentComments)): ?>
                <p style="color: var(--text-secondary);">No hay comentarios</p>
                <?php else: ?>
                <table>
                    <tbody>
                        <?php foreach($recentComments as $comment): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($comment['username']) ?></strong> en <em><?= htmlspecialchars(mb_substr($comment['post_title'], 0, 20)) ?>...</em><br>
                                <span class="time-ago"><?= htmlspecialchars(mb_substr($comment['content'], 0, 40)) ?>...</span>
                            </td>
                            <td style="text-align: right;">
                                <span class="time-ago"><?= strftime('%d/%b %H:%M', strtotime($comment['created_at'])) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
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
        document.querySelectorAll('.theme-dot').forEach(function(el) {
            if(el.classList.contains('theme-' + currentTheme)) {
                el.classList.add('active');
            }
        });
        
        // Chart.js Configuration
        Chart.defaults.font.family = "'Poppins', sans-serif";
        Chart.defaults.color = '<?= $colors['text'] ?>';
        
        // Visits Chart (Line)
        const visitsCtx = document.getElementById('visitsChart').getContext('2d');
        new Chart(visitsCtx, {
            type: 'line',
            data: {
                labels: [<?php foreach($visitStats as $v) echo "'" . date('d/m', strtotime($v['date'])) . "',"; ?>],
                datasets: [{
                    label: 'Visitas',
                    data: [<?php foreach($visitStats as $v) echo $v['visits'] . ","; ?>],
                    borderColor: '<?= $colors['primary'] ?>',
                    backgroundColor: '<?= $colors['primary'] ?>33',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '<?= $colors['border'] ?>' } },
                    x: { grid: { display: false } }
                }
            }
        });
        
        // Category Chart (Doughnut)
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryColors = ['#667eea', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#fee140', '#a8edea', '#fed6e3', '#89f7fe', '#66a6ff'];
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php foreach($postsByCategory as $c) echo "'" . htmlspecialchars($c['category']) . "',"; ?>],
                datasets: [{
                    data: [<?php foreach($postsByCategory as $c) echo $c['count'] . ","; ?>],
                    backgroundColor: categoryColors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
        
        // Top Pages Chart (Bar)
        const pagesCtx = document.getElementById('pagesChart').getContext('2d');
        new Chart(pagesCtx, {
            type: 'bar',
            data: {
                labels: [<?php foreach($topPages as $p) echo "'" . htmlspecialchars(substr($p['page'], 0, 15)) . "',"; ?>],
                datasets: [{
                    label: 'Visitas',
                    data: [<?php foreach($topPages as $p) echo $p['visits'] . ","; ?>],
                    backgroundColor: '<?= $colors['primary'] ?>',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: '<?= $colors['border'] ?>' } },
                    y: { grid: { display: false } }
                }
            }
        });
        
        // Roles Chart (Pie)
        const rolesCtx = document.getElementById('rolesChart').getContext('2d');
        new Chart(rolesCtx, {
            type: 'pie',
            data: {
                labels: [<?php foreach($usersByRole as $r) echo "'" . ucfirst($r['role']) . "',"; ?>],
                datasets: [{
                    data: [<?php foreach($usersByRole as $r) echo $r['count'] . ","; ?>],
                    backgroundColor: ['#dc2626', '#2563eb', '#059669', '#d97706']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
    </script>
</body>
</html>
