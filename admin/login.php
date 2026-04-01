<?php 
session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
include '../includes/functions.php';

$currentTheme = getActiveTheme();
$colors = getThemeColors($currentTheme);
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    if($user === 'admin' && $pass === 'blog$$') {
        $_SESSION['logged'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Credenciales incorrectas';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= CONFIG['site_name'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            <?php foreach($colors as $k=>$v): ?><?php echo "--$k: $v;"; ?><?php endforeach; ?>
            --shadow-lg: 0 15px 35px rgba(0,0,0,0.2);
            --radius-lg: 15px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--bg);
            color: var(--text);
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            padding: 1rem; 
        }
        .login-box { 
            background: var(--bg-secondary); 
            border: 3px solid var(--primary);
            padding: 2.5rem; 
            border-radius: var(--radius-lg); 
            box-shadow: var(--shadow-lg); 
            width: 100%; 
            max-width: 400px; 
            border: 1px solid var(--border);
        }
        .login-box h1 { color: var(--primary); margin-bottom: 0.5rem; text-align: center; font-size: 1.8rem; }
        .login-box .subtitle { color: var(--text-secondary); text-align: center; margin-bottom: 2rem; }
        .login-box .input-group { position: relative; margin-bottom: 1.2rem; }
        .login-box .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--primary); }
        .login-box input { 
            width: 100%; 
            padding: 0.8rem 0.8rem 0.8rem 2.8rem; 
            border: 2px solid var(--border); 
            border-radius: 8px; 
            background: var(--bg); 
            color: var(--text); 
            font-size: 1rem; 
            transition: all 0.3s; 
        }
        .login-box input:focus { outline: none; border-color: var(--primary); }
        .login-box button { 
            width: 100%; 
            padding: 0.9rem; 
            background: var(--primary); 
            color: white; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 1.1rem; 
            font-weight: 600; 
            transition: all 0.3s; 
        }
        .login-box button:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
        .error { 
            color: #dc3545; 
            margin-bottom: 1rem; 
            text-align: center; 
            background: rgba(220, 53, 69, 0.1); 
            padding: 0.8rem; 
            border-radius: 8px; 
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        .config-link { display: block; text-align: center; margin-top: 1.5rem; color: var(--link); text-decoration: none; }
        .config-link:hover { text-decoration: underline; }
        
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
    </style>
</head>
<body>
    <div class="login-box">
        <h1><i class="fas fa-user-shield"></i> Admin</h1>
        <p class="subtitle"><?= CONFIG['site_name'] ?></p>
        <?php if($error): ?><p class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></p><?php endif; ?>
        <form method="post">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="user" placeholder="Usuario" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="pass" placeholder="Contraseña" required>
            </div>
            <button type="submit"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</button>
        </form>
        <a href="users.php" class="config-link"><i class="fas fa-users"></i> Gestionar Usuarios</a>
        <a href="../index.php" class="config-link"><i class="fas fa-home"></i> Volver al Blog</a>
    </div>
    
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
        var currentTheme = '<?= $currentTheme ?>';
        document.querySelectorAll('.theme-dot').forEach(function(el) {
            if(el.classList.contains('theme-' + currentTheme)) {
                el.classList.add('active');
            }
        });
    </script>
</body>
</html>
