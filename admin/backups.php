<?php
if(session_status() === PHP_SESSION_NONE) session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
if(empty($_SESSION['logged'])) { header('Location: login.php'); exit; }
include '../includes/functions.php';

$currentTheme = getActiveTheme();
$colors = getThemeColors($currentTheme);

// Get all backup files - db folder is in root
$backupDir = dirname(__DIR__) . '/db/';
$backups = [];
if(is_dir($backupDir)) {
    $files = scandir($backupDir, SCANDIR_SORT_DESCENDING);
    foreach($files as $file) {
        if(pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = [
                'name' => $file,
                'size' => filesize($backupDir . $file),
                'date' => filemtime($backupDir . $file)
            ];
        }
    }
}

// Delete backup
if(isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $filepath = $backupDir . $file;
    if(file_exists($filepath)) {
        unlink($filepath);
        header('Location: backups.php?msg=deleted');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respaldos - <?= CONFIG['site_name'] ?></title>
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
        
        .navbar { background: var(--header-bg); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: var(--header-text); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: var(--radius-sm); transition: all 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.2); }
        
        .container { max-width: 900px; margin: 0 auto; padding: 2rem 1rem; }
        
        .msg { padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
        .msg.success { background: #d1fae5; color: #065f46; }
        
        .card { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); }
        .card h2 { color: var(--primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
        
        .backup-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            margin-bottom: 0.8rem;
            transition: all 0.3s;
        }
        .backup-item:hover { transform: translateX(5px); box-shadow: var(--shadow-sm); }
        
        .backup-info { display: flex; align-items: center; gap: 1rem; }
        .backup-icon { 
            width: 50px; 
            height: 50px; 
            background: linear-gradient(135deg, #10b981, #059669); 
            border-radius: 12px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: white; 
            font-size: 1.3rem; 
        }
        .backup-name { font-weight: 600; color: var(--text); }
        .backup-meta { font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.2rem; }
        
        .backup-actions { display: flex; gap: 0.5rem; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.6rem 1rem; background: var(--primary); color: white; text-decoration: none; border-radius: var(--radius-sm); border: none; cursor: pointer; font-weight: 500; font-size: 0.9rem; transition: all 0.3s; }
        .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-success { background: #10b981; }
        .btn-danger { background: #dc3545; }
        .btn-secondary { background: var(--secondary); }
        
        .empty { text-align: center; padding: 3rem; color: var(--text-secondary); }
        .empty i { font-size: 3rem; margin-bottom: 1rem; display: block; }
        
        .toolbar { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
        
        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 1rem; }
            .backup-item { flex-direction: column; gap: 1rem; text-align: center; }
            .backup-info { flex-direction: column; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Volver al Dashboard</a>
        <a href="../index.php"><i class="fas fa-home"></i> <?= CONFIG['site_name'] ?></a>
    </nav>
    
    <main class="container">
        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="msg success"><i class="fas fa-check-circle"></i> Respaldo eliminado correctamente</div>
        <?php endif; ?>
        
        <div class="card">
            <h2><i class="fas fa-database"></i> Respaldos de Base de Datos (<?= count($backups) ?>)</h2>
            
            <div class="toolbar">
                <a href="dashboard.php?backup=1" class="btn btn-success" onclick="return confirm('¿Crear nuevo respaldo?')">
                    <i class="fas fa-plus"></i> Crear Nuevo Respaldo
                </a>
            </div>
            
            <?php if(empty($backups)): ?>
            <div class="empty">
                <i class="fas fa-database"></i>
                <p>No hay respaldos creados</p>
                <a href="dashboard.php?backup=1" class="btn btn-success" style="margin-top:1rem;">Crear primer respaldo</a>
            </div>
            <?php else: ?>
                <?php foreach($backups as $backup): ?>
                <div class="backup-item">
                    <div class="backup-info">
                        <div class="backup-icon"><i class="fas fa-file-alt"></i></div>
                        <div>
                            <div class="backup-name"><?= htmlspecialchars($backup['name']) ?></div>
                            <div class="backup-meta">
                                <i class="fas fa-calendar"></i> <?= strftime('%d/%m/%Y %H:%M', $backup['date']) ?> | 
                                <i class="fas fa-weight"></i> <?= round($backup['size'] / 1024, 2) ?> KB
                            </div>
                        </div>
                    </div>
                    <div class="backup-actions">
                        <a href="download_backup.php?file=<?= urlencode($backup['name']) ?>" class="btn btn-success">
                            <i class="fas fa-download"></i> Descargar
                        </a>
                        <a href="backups.php?delete=<?= urlencode($backup['name']) ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar este respaldo?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
