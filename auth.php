<?php 
if(session_status() === PHP_SESSION_NONE) session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
include './includes/functions.php';

$currentLang = getActiveLanguage();
$lang = getLanguageStrings($currentLang);

$error = '';
$success = '';
$isLogin = isset($_GET['action']) && $_GET['action'] === 'login';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['register'])) {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $recoveryQuestion = $_POST['recovery_question'] ?? '';
        $recoveryAnswer = $_POST['recovery_answer'] ?? '';
        
        if(registerUser($username, $email, $password, $firstName, $lastName, $phone, $recoveryQuestion, $recoveryAnswer)) {
            $success = $currentLang === 'es' ? 'Registro exitoso. <a href="?action=login" style="color:var(--link);">Inicia sesión</a>' : 'Registration successful. <a href="?action=login" style="color:var(--link);">Login</a>';
            $isLogin = false;
        } else {
            $error = $currentLang === 'es' ? 'El usuario o email ya existe' : 'Username or email already exists';
        }
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $user = loginUser($email, $password);
        if($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit;
        } else {
            $error = $currentLang === 'es' ? 'Credenciales incorrectas' : 'Invalid credentials';
        }
    }
}

$colors = getThemeColors(getActiveTheme());
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isLogin ? 'Login' : 'Registro' ?> - <?= CONFIG['site_name'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            <?php foreach($colors as $k=>$v): ?><?php echo "--$k: $v;"; ?><?php endforeach; ?>
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--bg); 
            color: var(--text); 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .auth-card {
            background: var(--bg-secondary);
            border: 3px solid var(--primary);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .auth-card h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        .auth-card .subtitle {
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 2rem;
        }
        .auth-tabs {
            display: flex;
            margin-bottom: 1.5rem;
            background: var(--bg);
            border-radius: 12px;
            padding: 4px;
        }
        .auth-tabs a {
            flex: 1;
            padding: 0.8rem;
            text-align: center;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .auth-tabs a.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text);
        }
        .form-group input {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            background: var(--bg);
            color: var(--text);
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn-auth {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        .error {
            color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        .success {
            color: #28a745;
            background: rgba(40, 167, 69, 0.1);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--link);
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
        
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
            box-shadow: 0 8px 40px rgba(0,0,0,0.15);
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
    </style>
</head>
<body>
    <div class="auth-card">
        <h1><i class="fas fa-<?= $isLogin ? 'sign-in-alt' : 'user-plus' ?>"></i> <?= $isLogin ? ($currentLang === 'es' ? 'Bienvenido' : 'Welcome') : ($currentLang === 'es' ? 'Crear Cuenta' : 'Create Account') ?></h1>
        <p class="subtitle"><?= CONFIG['site_name'] ?></p>
        
        <div class="auth-tabs">
            <a href="?action=login" class="<?= $isLogin ? 'active' : '' ?>"><i class="fas fa-sign-in-alt"></i> <?= t('nav_login') ?></a>
            <a href="?" class="<?= !$isLogin ? 'active' : '' ?>"><i class="fas fa-user-plus"></i> <?= $currentLang === 'es' ? 'Registro' : 'Register' ?></a>
        </div>
        
        <?php if($error): ?><div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        
        <form method="post">
            <?php if(!$isLogin): ?>
            <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> <?= $currentLang === 'es' ? 'Nombre' : 'First Name' ?></label>
                    <input type="text" name="first_name" placeholder="<?= $currentLang === 'es' ? 'Tu nombre' : 'Your name' ?>">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> <?= $currentLang === 'es' ? 'Apellido' : 'Last Name' ?></label>
                    <input type="text" name="last_name" placeholder="<?= $currentLang === 'es' ? 'Tu apellido' : 'Your last name' ?>">
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-at"></i> <?= t('register_username') ?></label>
                <input type="text" name="username" placeholder="<?= $currentLang === 'es' ? 'Nombre de usuario' : 'Username' ?>" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-phone"></i> <?= $currentLang === 'es' ? 'Teléfono' : 'Phone' ?></label>
                <input type="text" name="phone" placeholder="+5051234567">
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> <?= $currentLang === 'es' ? 'Email o Usuario' : 'Email or Username' ?></label>
                <input type="text" name="email" placeholder="<?= $currentLang === 'es' ? 'tu@email.com o usuario' : 'your@email.com or username' ?>" required>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> <?= t('login_password') ?></label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <?php if(!$isLogin): ?>
            <div class="form-group">
                <label><i class="fas fa-question-circle"></i> <?= t('register_question') ?></label>
                <select name="recovery_question" style="width:100%; padding:0.9rem; border:2px solid var(--border); border-radius:10px; background:var(--bg); color:var(--text);">
                    <option value=""><?= $currentLang === 'es' ? 'Selecciona una pregunta' : 'Select a question' ?></option>
                    <option value="¿Cuál es el nombre de tu primera mascota?"><?= $currentLang === 'es' ? '¿Cuál es el nombre de tu primera mascota?' : 'What is your first pet\'s name?' ?></option>
                    <option value="¿Cuál es tu ciudad natal?"><?= $currentLang === 'es' ? '¿Cuál es tu ciudad natal?' : 'What is your hometown?' ?></option>
                    <option value="¿Cuál es el nombre de tu mejor amigo?"><?= $currentLang === 'es' ? '¿Cuál es el nombre de tu mejor amigo?' : 'What is your best friend\'s name?' ?></option>
                    <option value="¿Cuál es tu comida favorita?"><?= $currentLang === 'es' ? '¿Cuál es tu comida favorita?' : 'What is your favorite food?' ?></option>
                    <option value="¿Cuál es el nombre de tu escuela primaria?"><?= $currentLang === 'es' ? '¿Cuál es el nombre de tu escuela primaria?' : 'What is your elementary school name?' ?></option>
                </select>
            </div>
            <div class="form-group">
                <label><i class="fas fa-key"></i> <?= t('register_answer') ?></label>
                <input type="text" name="recovery_answer" placeholder="<?= $currentLang === 'es' ? 'Tu respuesta secreta' : 'Your secret answer' ?>">
            </div>
            <?php endif; ?>
            <button type="submit" name="<?= $isLogin ? 'login' : 'register' ?>" class="btn-auth">
                <i class="fas fa-<?= $isLogin ? 'sign-in-alt' : 'user-plus' ?>"></i> 
                <?= $isLogin ? t('login_submit') : t('register_submit') ?>
            </button>
        </form>
        <?php if($isLogin): ?>
        <a href="recover.php" class="back-link"><i class="fas fa-key"></i> <?= t('login_forgot') ?></a>
        <?php endif; ?>
        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> <?= $currentLang === 'es' ? 'Volver al Blog' : 'Back to Blog' ?></a>
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
    </script>
</body>
</html>
