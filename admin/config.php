<?php
session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
if(empty($_SESSION['logged'])) { header('Location: login.php'); exit; }
include '../includes/functions.php';

$msg = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newConfig = CONFIG;
    $newConfig['site_name'] = $_POST['site_name'];
    $newConfig['site_url'] = $_POST['site_url'];
    $newConfig['email'] = $_POST['email'];
    $newConfig['author'] = $_POST['author'];
    $newConfig['description'] = $_POST['description'];
    $newConfig['theme'] = $_POST['theme'];
    $newConfig['posts_per_page'] = (int)$_POST['posts_per_page'];
    file_put_contents('../config.php', '<?php return ' . var_export($newConfig, true) . ';');
    $msg = 'Configuración guardada';
}

$colors = getThemeColors(CONFIG['theme']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - <?= CONFIG['site_name'] ?></title>
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
        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--bg); 
            color: var(--text); 
            min-height: 100vh;
        }
        
        .navbar {
            background: var(--header-bg);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar a {
            color: var(--header-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            padding: 2rem 1rem; 
        }
        
        .card {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
        }
        
        .card h2 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { 
            display: block; 
            margin-bottom: 0.5rem; 
            font-weight: 600;
            color: var(--text);
        }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; 
            padding: 0.8rem 1rem; 
            border: 2px solid var(--border); 
            border-radius: var(--radius-sm); 
            background: var(--bg); 
            color: var(--text);
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .btn { 
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem; 
            background: var(--primary); 
            color: white; 
            text-decoration: none; 
            border-radius: var(--radius-sm); 
            border: none; 
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .success { 
            background: rgba(40, 167, 69, 0.1); 
            color: #28a745; 
            padding: 1rem; 
            border-radius: var(--radius-sm); 
            margin-bottom: 1rem;
            border: 1px solid rgba(40, 167, 69, 0.2);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .divider {
            margin: 2rem 0;
            border-color: var(--border);
        }
        
        .info-card {
            background: var(--bg);
            padding: 1.5rem;
            border-radius: var(--radius-md);
            border: 1px solid var(--border);
        }
        .info-card h3 {
            color: var(--primary);
            margin-bottom: 1rem;
        }
        .info-card p {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .info-card code {
            background: var(--code-bg);
            color: var(--code-text);
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-family: var(--font-code);
        }
        
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
        .theme-white { background: linear-gradient(135deg, #fff, #e2e8f0); }
        .theme-blue { background: linear-gradient(135deg, #2563eb, #1e40af); }
        .theme-dark-blue { background: linear-gradient(135deg, #3b82f6, #1e3a8a); }
        .theme-black { background: linear-gradient(135deg, #333, #000); }
        
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .card { padding: 1.5rem; }
            .theme-float { bottom: 10px; right: 10px; padding: 6px 10px; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Volver</a>
        <a href="../index.php"><i class="fas fa-home"></i> <?= CONFIG['site_name'] ?></a>
    </nav>
    
    <main class="container">
        <div class="card">
            <h2><i class="fas fa-cog"></i> Configuración del Blog</h2>
            <?php if($msg): ?><p class="success"><i class="fas fa-check-circle"></i> <?= $msg ?></p><?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Nombre del Sitio</label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars(CONFIG['site_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-link"></i> URL del Sitio</label>
                    <input type="text" name="site_url" value="<?= htmlspecialchars(CONFIG['site_url']) ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars(CONFIG['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Autor</label>
                    <input type="text" name="author" value="<?= htmlspecialchars(CONFIG['author']) ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Descripción</label>
                    <textarea name="description" rows="3"><?= htmlspecialchars(CONFIG['description']) ?></textarea>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-palette"></i> Tema por Defecto</label>
                    <select name="theme">
                        <option value="white" <?= CONFIG['theme']=='white'?'selected':'' ?>>Blanco</option>
                        <option value="blue" <?= CONFIG['theme']=='blue'?'selected':'' ?>>Azul</option>
                        <option value="dark-blue" <?= CONFIG['theme']=='dark-blue'?'selected':'' ?>>Azul Oscuro</option>
                        <option value="black" <?= CONFIG['theme']=='black'?'selected':'' ?>>Negro</option>
                        <option value="green" <?= CONFIG['theme']=='green'?'selected':'' ?>>Verde</option>
                        <option value="red" <?= CONFIG['theme']=='red'?'selected':'' ?>>Rojo</option>
                        <option value="purple" <?= CONFIG['theme']=='purple'?'selected':'' ?>>Morado</option>
                        <option value="orange" <?= CONFIG['theme']=='orange'?'selected':'' ?>>Naranja</option>
                        <option value="pink" <?= CONFIG['theme']=='pink'?'selected':'' ?>>Rosa</option>
                        <option value="teal" <?= CONFIG['theme']=='teal'?'selected':'' ?>>Teal</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-list"></i> Publicaciones por Página</label>
                    <input type="number" name="posts_per_page" value="<?= CONFIG['posts_per_page'] ?>" min="1" max="50">
                </div>
                <button type="submit" class="btn"><i class="fas fa-save"></i> Guardar Configuración</button>
            </form>
            
            <hr class="divider">
            
            <div class="info-card">
                <h3><i class="fas fa-key"></i> Gestión de Acceso</h3>
                <p><a href="users.php" style="color: var(--link);"><i class="fas fa-users"></i> Gestionar Usuarios</a></p>
                <p style="color: var(--text-secondary); margin-top: 0.5rem; font-size: 0.9rem;">
                    <i class="fas fa-info-circle"></i> Desde aquí puedes crear usuarios, cambiar contraseñas y roles
                </p>
            </div>
        </div>
    </main>
    
    <div class="theme-float">
        <div class="theme-dot" onclick="setTheme('white')" title="Blanco" style="background: linear-gradient(135deg,#fff,#f0f0f0)"></div>
        <div class="theme-dot" onclick="setTheme('blue')" title="Azul" style="background: linear-gradient(135deg,#2563eb,#1e40af)"></div>
        <div class="theme-dot" onclick="setTheme('dark-blue')" title="Azul Oscuro" style="background: linear-gradient(135deg,#3b82f6,#1e3a8a)"></div>
        <div class="theme-dot" onclick="setTheme('black')" title="Negro" style="background: linear-gradient(135deg,#333,#000)"></div>
        <div class="theme-dot" onclick="setTheme('green')" title="Verde" style="background: linear-gradient(135deg,#38a169,#276749)"></div>
        <div class="theme-dot" onclick="setTheme('red')" title="Rojo" style="background: linear-gradient(135deg,#e53e3e,#c53030)"></div>
        <div class="theme-dot" onclick="setTheme('purple')" title="Morado" style="background: linear-gradient(135deg,#805ad5,#6b46c1)"></div>
        <div class="theme-dot" onclick="setTheme('orange')" title="Naranja" style="background: linear-gradient(135deg,#ea580c,#9a3412)"></div>
        <div class="theme-dot" onclick="setTheme('pink')" title="Rosa" style="background: linear-gradient(135deg,#d53f8c,#97266d)"></div>
        <div class="theme-dot" onclick="setTheme('teal')" title="Teal" style="background: linear-gradient(135deg,#0d9488,#0f766e)"></div>
    </div>
    
    <script>
        function setTheme(theme) {
            document.cookie = 'theme=' + theme + '; path=/; max-age=31536000';
            location.reload();
        }
        var currentTheme = '<?= CONFIG['theme'] ?>';
        document.querySelectorAll('.theme-dot').forEach(function(el) {
            if(el.classList.contains('theme-' + currentTheme)) {
                el.classList.add('active');
            }
        });
    </script>
</body>
</html>
