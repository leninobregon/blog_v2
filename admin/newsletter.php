<?php
if(session_status() === PHP_SESSION_NONE) session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
if(empty($_SESSION['logged'])) { header('Location: login.php'); exit; }
include '../includes/functions.php';

$currentTheme = getActiveTheme();
$colors = getThemeColors($currentTheme);
$message = '';
$messageType = '';

// Handle actions
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if($action === 'toggle') {
        $id = (int)$_POST['id'];
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE newsletter SET active = NOT active WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Suscriptor actualizado';
        $messageType = 'success';
    }
    
    if($action === 'delete') {
        $id = (int)$_POST['id'];
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM newsletter WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Suscriptor eliminado';
        $messageType = 'success';
    }
    
    if($action === 'send') {
        $subject = $_POST['subject'] ?? '';
        $content = $_POST['content'] ?? '';
        $subscribers = getNewsletterSubscribers(true);
        
        $sent = 0;
        foreach($subscribers as $sub) {
            $headers = "From: " . CONFIG['site_name'] . " <noreply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $htmlContent = "<h2>" . htmlspecialchars($subject) . "</h2>" . nl2br(htmlspecialchars($content));
            $htmlContent .= "<hr><p><a href='" . CONFIG['site_url'] . "'>Visitar " . CONFIG['site_name'] . "</a></p>";
            
            if(mail($sub['email'], $subject, $htmlContent, $headers)) {
                $sent++;
                $pdo = getDB();
                $stmt = $pdo->prepare("UPDATE newsletter SET last_sent = NOW(), total_sent = total_sent + 1 WHERE id = ?");
                $stmt->execute([$sub['id']]);
            }
        }
        $message = "Newsletter enviado a $sent suscriptores";
        $messageType = 'success';
    }
}

$allSubscribers = getNewsletterSubscribers(false);
$activeSubscribers = getNewsletterSubscribers(true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter - <?= CONFIG['site_name'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        .navbar h1 { color: var(--header-text); display: flex; align-items: center; gap: 0.5rem; }
        .navbar nav { display: flex; gap: 1rem; flex-wrap: wrap; }
        .navbar a { color: var(--header-text); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: var(--radius-sm); transition: all 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.2); }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem 1rem; }
        
        .card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            margin-bottom: 1.5rem;
        }
        .card h2 { margin-bottom: 1rem; color: var(--primary); display: flex; align-items: center; gap: 0.5rem; }
        
        .message {
            padding: 1rem;
            border-radius: var(--radius-sm);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .message.success { background: #d1fae5; color: #065f46; }
        .message.error { background: #fee2e2; color: #991b1b; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-item {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1rem;
            text-align: center;
        }
        .stat-item .number { font-size: 2rem; font-weight: 700; color: var(--primary); }
        .stat-item .label { font-size: 0.85rem; color: var(--text-secondary); }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.8rem; text-align: left; border-bottom: 1px solid var(--border); }
        th { color: var(--text-secondary); font-weight: 500; font-size: 0.85rem; text-transform: uppercase; }
        tr:hover { background: var(--bg); }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-active { background: #d1fae5; color: #059669; }
        .badge-inactive { background: #fee2e2; color: #dc2626; }
        
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: var(--primary); color: white; text-decoration: none; border-radius: var(--radius-sm); border: none; cursor: pointer; font-size: 0.85rem; transition: all 0.3s; }
        .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-sm { padding: 0.3rem 0.6rem; font-size: 0.8rem; }
        .btn-secondary { background: var(--secondary); }
        .btn-danger { background: #dc3545; }
        
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background: var(--bg);
            color: var(--text);
            font-family: inherit;
        }
        .form-group textarea { min-height: 150px; resize: vertical; }
        
        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--border);
            padding-bottom: 0;
        }
        .tab {
            padding: 0.8rem 1.5rem;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-secondary);
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.3s;
        }
        .tab:hover { color: var(--primary); }
        .tab.active { color: var(--primary); border-bottom-color: var(--primary); }
        
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        @media (max-width: 768px) {
            .navbar { flex-direction: column; text-align: center; }
            .container { padding: 1rem; }
            table { font-size: 0.85rem; }
            th, td { padding: 0.5rem; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1><i class="fas fa-envelope"></i> Newsletter</h1>
        <nav>
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="index.php"><i class="fas fa-file-alt"></i> Publicaciones</a>
            <a href="users.php"><i class="fas fa-users"></i> Usuarios</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </nav>
    </nav>
    
    <main class="container">
        <?php if($message): ?>
        <div class="message <?= $messageType ?>">
            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= $message ?>
        </div>
        <?php endif; ?>
        
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-item">
                <div class="number"><?= count($activeSubscribers) ?></div>
                <div class="label">Activos</div>
            </div>
            <div class="stat-item">
                <div class="number"><?= count($allSubscribers) ?></div>
                <div class="label">Total</div>
            </div>
            <div class="stat-item">
                <div class="number"><?= count($allSubscribers) - count($activeSubscribers) ?></div>
                <div class="label">Inactivos</div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="showTab('send')"><i class="fas fa-paper-plane"></i> Enviar Newsletter</button>
            <button class="tab" onclick="showTab('list')"><i class="fas fa-list"></i> Lista de Suscriptores</button>
        </div>
        
        <!-- Send Newsletter Tab -->
        <div id="tab-send" class="tab-content active">
            <div class="card">
                <h2><i class="fas fa-paper-plane"></i> Enviar Newsletter</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="send">
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Asunto</label>
                        <input type="text" name="subject" required placeholder="Asunto del newsletter...">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Contenido</label>
                        <textarea name="content" required placeholder="Escribe el contenido del newsletter..."></textarea>
                    </div>
                    <button type="submit" class="btn">
                        <i class="fas fa-paper-plane"></i> Enviar a <?= count($activeSubscribers) ?> suscriptores
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Subscribers List Tab -->
        <div id="tab-list" class="tab-content">
            <div class="card">
                <h2><i class="fas fa-users"></i> Lista de Suscriptores</h2>
                <?php if(empty($allSubscribers)): ?>
                <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">No hay suscriptores registrados</p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Registrado</th>
                            <th>Enviados</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($allSubscribers as $sub): ?>
                        <tr>
                            <td><?= htmlspecialchars($sub['email']) ?></td>
                            <td><?= htmlspecialchars($sub['name'] ?: '-') ?></td>
                            <td>
                                <span class="badge <?= $sub['active'] ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= $sub['active'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td><?= strftime('%d/%m/%Y', strtotime($sub['created_at'])) ?></td>
                            <td><?= $sub['total_sent'] ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-<?= $sub['active'] ? 'pause' : 'play' ?>"></i>
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('¿Eliminar este suscriptor?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <script>
        function showTab(name) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
            document.getElementById('tab-' + name).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html>
