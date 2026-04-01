<?php
session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
if(empty($_SESSION['logged'])) { header('Location: login.php'); exit; }
include '../includes/functions.php';

$currentTheme = getActiveTheme();
$colors = getThemeColors($currentTheme);
$about = getAbout();
$msg = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $subtitle = $_POST['subtitle'] ?? '';
    $description = $_POST['description'] ?? '';
    $experience = $_POST['experience'] ?? '';
    $goals = $_POST['goals'] ?? '';
    $youtube = $_POST['youtube_url'] ?? '';
    $facebook = $_POST['facebook_url'] ?? '';
    $twitter = $_POST['twitter_url'] ?? '';
    $telegram = $_POST['telegram_url'] ?? '';
    $email = $_POST['email'] ?? '';
    $photo = null;
    
    if(!empty($_FILES['photo']['name'])) {
        $photo = uploadImage($_FILES['photo']);
    }
    
    if(updateAbout($title, $subtitle, $description, $experience, $goals, $youtube, $facebook, $twitter, $telegram, $email, $photo)) {
        $msg = 'Página actualizada correctamente';
        $about = getAbout();
    } else {
        $msg = 'Error al actualizar';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Acerca de - <?= CONFIG['site_name'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            <?php foreach($colors as $k=>$v): ?><?php echo "--$k: $v;"; ?><?php endforeach; ?>
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08); --shadow-md: 0 4px 20px rgba(0,0,0,0.12); --shadow-lg: 0 8px 40px rgba(0,0,0,0.15); --radius-sm: 8px; --radius-md: 12px; --radius-lg: 20px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); }
        .navbar { background: var(--header-bg); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: var(--header-text); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; }
        .container { max-width: 900px; margin: 0 auto; padding: 2rem 1rem; }
        .card { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 2rem; box-shadow: var(--shadow-sm); }
        .card h2 { color: var(--primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text); }
        .form-group input, .form-group textarea { width: 100%; padding: 0.8rem 1rem; border: 2px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); color: var(--text); font-family: inherit; font-size: 1rem; transition: all 0.3s; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); }
        .form-group textarea { min-height: 150px; resize: vertical; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.8rem 1.5rem; background: var(--primary); color: white; text-decoration: none; border-radius: var(--radius-sm); border: none; cursor: pointer; font-weight: 600; transition: all 0.3s; }
        .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-secondary { background: var(--secondary); }
        .msg { background: rgba(40, 167, 69, 0.1); color: #28a745; padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem; border: 1px solid #28a745; }
        .preview-box { background: var(--bg); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem; }
        .preview-box img { max-width: 150px; border-radius: var(--radius-sm); }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Volver</a>
        <a href="../index.php"><i class="fas fa-home"></i> <?= CONFIG['site_name'] ?></a>
    </nav>
    
    <main class="container">
        <?php if($msg): ?><div class="msg"><i class="fas fa-check-circle"></i> <?= $msg ?></div><?php endif; ?>
        
        <div class="card">
            <h2><i class="fas fa-user-circle"></i> Editar Página "Acerca de Mí"</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Título</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($about['title'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-user-tag"></i> Subtítulo</label>
                    <input type="text" name="subtitle" value="<?= htmlspecialchars($about['subtitle'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Foto de Perfil</label>
                    <?php if(!empty($about['photo'])): ?>
                    <div class="preview-box">
                        <img src="../<?= htmlspecialchars($about['photo']) ?>" alt="Foto actual">
                        <p>Foto actual</p>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="photo" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Descripción</label>
                    <textarea name="description" placeholder="Descripción personal..."><?= htmlspecialchars($about['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-briefcase"></i> Experiencia (una por línea)</label>
                    <textarea name="experience" placeholder="Networking&#10;Administración de Servidores&#10;Monitoreo"><?= htmlspecialchars($about['experience'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-bullseye"></i> Objetivos (uno por línea)</label>
                    <textarea name="goals" placeholder="Promover tecnologías&#10;Software libre"><?= htmlspecialchars($about['goals'] ?? '') ?></textarea>
                </div>
                
                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label><i class="fab fa-youtube"></i> YouTube URL</label>
                        <input type="text" name="youtube_url" value="<?= htmlspecialchars($about['youtube_url'] ?? '') ?>" placeholder="https://youtube.com/@canal">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-facebook"></i> Facebook URL</label>
                        <input type="text" name="facebook_url" value="<?= htmlspecialchars($about['facebook_url'] ?? '') ?>" placeholder="https://facebook.com/pagina">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-twitter"></i> Twitter URL</label>
                        <input type="text" name="twitter_url" value="<?= htmlspecialchars($about['twitter_url'] ?? '') ?>" placeholder="https://twitter.com/usuario">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-telegram"></i> Telegram URL</label>
                        <input type="text" name="telegram_url" value="<?= htmlspecialchars($about['telegram_url'] ?? '') ?>" placeholder="https://t.me/usuario">
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email de contacto</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($about['email'] ?? '') ?>" placeholder="email@ejemplo.com">
                </div>
                
                <button type="submit" class="btn"><i class="fas fa-save"></i> Guardar Cambios</button>
                <a href="../about.php" target="_blank" class="btn btn-secondary"><i class="fas fa-eye"></i> Ver Página</a>
            </form>
        </div>
    </main>
</body>
</html>