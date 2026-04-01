<?php
if(session_status() === PHP_SESSION_NONE) session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
include './includes/functions.php';

if(empty($_SESSION['user_id'])) { header('Location: auth.php'); exit; }

$currentLang = getActiveLanguage();
$lang = getLanguageStrings($currentLang);
$loggedUser = getUser($_SESSION['user_id']);
$msg = '';
$msgType = '';

// Update profile
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if($action === 'update_profile') {
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $bio = $_POST['bio'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $facebook = $_POST['facebook'] ?? '';
        $twitter = $_POST['twitter'] ?? '';
        $telegram = $_POST['telegram'] ?? '';
        $instagram = $_POST['instagram'] ?? '';
        $youtube = $_POST['youtube'] ?? '';
        $linkedin = $_POST['linkedin'] ?? '';
        $website = $_POST['website'] ?? '';
        
        $avatar = null;
        if(!empty($_FILES['avatar']['name'])) {
            $avatar = uploadImage($_FILES['avatar']);
        }
        
        $pdo = getDB();
        if($avatar) {
            $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, bio=?, phone=?, facebook=?, twitter=?, telegram=?, instagram=?, youtube=?, linkedin=?, website=?, avatar=? WHERE id=?");
            $stmt->execute([$firstName, $lastName, $bio, $phone, $facebook, $twitter, $telegram, $instagram, $youtube, $linkedin, $website, $avatar, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=?, bio=?, phone=?, facebook=?, twitter=?, telegram=?, instagram=?, youtube=?, linkedin=?, website=? WHERE id=?");
            $stmt->execute([$firstName, $lastName, $bio, $phone, $facebook, $twitter, $telegram, $instagram, $youtube, $linkedin, $website, $_SESSION['user_id']]);
        }
        
        $loggedUser = getUser($_SESSION['user_id']);
        $msg = $currentLang === 'es' ? 'Perfil actualizado correctamente' : 'Profile updated successfully';
        $msgType = 'success';
    }
    
    if($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if(!password_verify($currentPassword, $loggedUser['password'])) {
            $msg = $currentLang === 'es' ? 'La contraseña actual es incorrecta' : 'Current password is incorrect';
            $msgType = 'error';
        } elseif($newPassword !== $confirmPassword) {
            $msg = $currentLang === 'es' ? 'Las contraseñas no coinciden' : 'Passwords do not match';
            $msgType = 'error';
        } elseif(strlen($newPassword) < 6) {
            $msg = $currentLang === 'es' ? 'La contraseña debe tener al menos 6 caracteres' : 'Password must be at least 6 characters';
            $msgType = 'error';
        } else {
            updateUserPassword($_SESSION['user_id'], $newPassword);
            $loggedUser = getUser($_SESSION['user_id']);
            $msg = $currentLang === 'es' ? 'Contraseña cambiada correctamente' : 'Password changed successfully';
            $msgType = 'success';
        }
    }
}

$currentTheme = getActiveTheme();
$colors = getThemeColors($currentTheme);
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $currentLang === 'es' ? 'Mi Perfil' : 'My Profile' ?> - <?= CONFIG['site_name'] ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            <?php foreach($colors as $k=>$v): ?><?php echo "--$k: $v;"; ?><?php endforeach; ?>
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08); --shadow-md: 0 4px 20px rgba(0,0,0,0.12); --shadow-lg: 0 8px 40px rgba(0,0,0,0.15); --radius-sm: 8px; --radius-md: 12px; --radius-lg: 20px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
        
        .navbar { background: var(--header-bg); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        .navbar a { color: var(--header-text); text-decoration: none; display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: var(--radius-sm); transition: all 0.3s; }
        .navbar a:hover { background: rgba(255,255,255,0.2); }
        
        .container { max-width: 900px; margin: 0 auto; padding: 2rem 1rem; }
        
        .msg { padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
        .msg.success { background: #d1fae5; color: #065f46; }
        .msg.error { background: #fee2e2; color: #991b1b; }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary), var(--header-bg));
            border-radius: var(--radius-lg);
            padding: 2rem;
            text-align: center;
            color: white;
            margin-bottom: 2rem;
        }
        
        .avatar-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 1rem;
            position: relative;
        }
        
        .avatar-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: var(--shadow-md);
        }
        
        .avatar-placeholder {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            border: 4px solid white;
        }
        
        .profile-name { font-size: 1.5rem; font-weight: 700; }
        .profile-username { opacity: 0.9; font-size: 0.9rem; }
        .profile-role { 
            display: inline-block; 
            padding: 0.25rem 1rem; 
            background: rgba(255,255,255,0.2); 
            border-radius: 50px; 
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }
        
        .card { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 1.5rem; box-shadow: var(--shadow-sm); }
        .card h2 { color: var(--primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; font-size: 1.2rem; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text); font-size: 0.9rem; }
        .form-group input, .form-group select, .form-group textarea { 
            width: 100%; 
            padding: 0.8rem; 
            border: 2px solid var(--border); 
            border-radius: var(--radius-sm); 
            background: var(--bg); 
            color: var(--text); 
            font-size: 1rem;
            font-family: inherit;
        }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); }
        .form-group textarea { min-height: 100px; resize: vertical; }
        
        .section-title { font-size: 0.9rem; font-weight: 600; color: var(--primary); margin: 1.5rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border); }
        
        .social-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
        
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.8rem 1.5rem; background: var(--primary); color: white; border: none; border-radius: var(--radius-sm); cursor: pointer; font-weight: 600; font-size: 1rem; transition: all 0.3s; }
        .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-secondary { background: var(--secondary); }
        
        .avatar-preview { 
            width: 80px; 
            height: 80px; 
            border-radius: 50%; 
            object-fit: cover; 
            border: 2px solid var(--border);
            margin-top: 0.5rem;
        }
        
        .password-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .password-toggle input { flex: 1; }
        
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
            flex-wrap: wrap;
            max-width: 320px;
            justify-content: center;
        }
        .theme-dot {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s;
        }
        .theme-dot:hover, .theme-dot.active { transform: scale(1.2); border-color: var(--text); }
        
        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 1rem; }
            .container { padding: 1rem; }
            .form-grid { grid-template-columns: 1fr; }
            .social-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php"><i class="fas fa-home"></i> <?= CONFIG['site_name'] ?></a>
        <a href="index.php"><i class="fas fa-arrow-left"></i> <?= $currentLang === 'es' ? 'Volver al Blog' : 'Back to Blog' ?></a>
    </nav>
    
    <main class="container">
        <?php if($msg): ?>
        <div class="msg <?= $msgType ?>">
            <i class="fas fa-<?= $msgType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= $msg ?>
        </div>
        <?php endif; ?>
        
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="avatar-container">
                <?php if(!empty($loggedUser['avatar'])): ?>
                <img src="<?= htmlspecialchars($loggedUser['avatar']) ?>" alt="Avatar" class="avatar-img">
                <?php else: ?>
                <div class="avatar-placeholder"><i class="fas fa-user"></i></div>
                <?php endif; ?>
            </div>
            <div class="profile-name"><?= htmlspecialchars(($loggedUser['first_name'] ?? '') . ' ' . ($loggedUser['last_name'] ?? '')) ?: $loggedUser['username'] ?></div>
            <div class="profile-username">@<?= htmlspecialchars($loggedUser['username']) ?></div>
            <div class="profile-role"><i class="fas fa-shield-alt"></i> <?= ucfirst($loggedUser['role']) ?></div>
        </div>
        
        <!-- Update Profile -->
        <div class="card">
            <h2><i class="fas fa-user-edit"></i> <?= $currentLang === 'es' ? 'Editar Perfil' : 'Edit Profile' ?></h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_profile">
                
                <p class="section-title"><i class="fas fa-camera"></i> <?= $currentLang === 'es' ? 'Foto de Perfil' : 'Profile Photo' ?></p>
                <div class="form-group">
                    <input type="file" name="avatar" accept="image/*" id="avatarInput">
                    <img src="" alt="Preview" class="avatar-preview" id="avatarPreview" style="display:none;">
                </div>
                
                <p class="section-title"><i class="fas fa-id-card"></i> <?= $currentLang === 'es' ? 'Datos Personales' : 'Personal Info' ?></p>
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> <?= $currentLang === 'es' ? 'Nombre' : 'First Name' ?></label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($loggedUser['first_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> <?= $currentLang === 'es' ? 'Apellido' : 'Last Name' ?></label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($loggedUser['last_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> <?= $currentLang === 'es' ? 'Teléfono' : 'Phone' ?></label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($loggedUser['phone'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> <?= $currentLang === 'es' ? 'Biografía' : 'Bio' ?></label>
                    <textarea name="bio" placeholder="<?= $currentLang === 'es' ? 'Cuéntanos sobre ti...' : 'Tell us about you...' ?>"><?= htmlspecialchars($loggedUser['bio'] ?? '') ?></textarea>
                </div>
                
                <p class="section-title"><i class="fas fa-share-alt"></i> <?= $currentLang === 'es' ? 'Redes Sociales' : 'Social Networks' ?></p>
                <div class="social-grid">
                    <div class="form-group">
                        <label><i class="fab fa-facebook"></i> Facebook</label>
                        <input type="text" name="facebook" value="<?= htmlspecialchars($loggedUser['facebook'] ?? '') ?>" placeholder="https://facebook.com/usuario">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-twitter"></i> Twitter/X</label>
                        <input type="text" name="twitter" value="<?= htmlspecialchars($loggedUser['twitter'] ?? '') ?>" placeholder="https://twitter.com/usuario">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-telegram"></i> Telegram</label>
                        <input type="text" name="telegram" value="<?= htmlspecialchars($loggedUser['telegram'] ?? '') ?>" placeholder="https://t.me/usuario">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-instagram"></i> Instagram</label>
                        <input type="text" name="instagram" value="<?= htmlspecialchars($loggedUser['instagram'] ?? '') ?>" placeholder="https://instagram.com/usuario">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-youtube"></i> YouTube</label>
                        <input type="text" name="youtube" value="<?= htmlspecialchars($loggedUser['youtube'] ?? '') ?>" placeholder="https://youtube.com/@canal">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-linkedin"></i> LinkedIn</label>
                        <input type="text" name="linkedin" value="<?= htmlspecialchars($loggedUser['linkedin'] ?? '') ?>" placeholder="https://linkedin.com/in/usuario">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-globe"></i> Website</label>
                        <input type="text" name="website" value="<?= htmlspecialchars($loggedUser['website'] ?? '') ?>" placeholder="https://miweb.com">
                    </div>
                </div>
                
                <button type="submit" class="btn" style="margin-top: 1rem;">
                    <i class="fas fa-save"></i> <?= $currentLang === 'es' ? 'Guardar Cambios' : 'Save Changes' ?>
                </button>
            </form>
        </div>
        
        <!-- Change Password -->
        <div class="card">
            <h2><i class="fas fa-key"></i> <?= $currentLang === 'es' ? 'Cambiar Contraseña' : 'Change Password' ?></h2>
            <form method="post">
                <input type="hidden" name="action" value="change_password">
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> <?= $currentLang === 'es' ? 'Contraseña Actual' : 'Current Password' ?></label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> <?= $currentLang === 'es' ? 'Nueva Contraseña' : 'New Password' ?></label>
                        <input type="password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> <?= $currentLang === 'es' ? 'Confirmar Contraseña' : 'Confirm Password' ?></label>
                        <input type="password" name="confirm_password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-secondary" style="margin-top: 1rem;">
                    <i class="fas fa-key"></i> <?= $currentLang === 'es' ? 'Cambiar Contraseña' : 'Change Password' ?>
                </button>
            </form>
        </div>
    </main>
    
    <div class="theme-float">
        <div class="theme-dot" onclick="setTheme('white')" title="Blanco" style="background: linear-gradient(135deg,#fff,#e2e8f0)"></div>
        <div class="theme-dot" onclick="setTheme('blue')" title="Azul" style="background: linear-gradient(135deg,#2563eb,#1e40af)"></div>
        <div class="theme-dot" onclick="setTheme('dark-blue')" title="Azul Oscuro" style="background: linear-gradient(135deg,#3b82f6,#1e3a8a)"></div>
        <div class="theme-dot" onclick="setTheme('black')" title="Negro" style="background: linear-gradient(135deg,#333,#000)"></div>
        <div class="theme-dot" onclick="setTheme('green')" title="Verde" style="background: linear-gradient(135deg,#38a169,#276749)"></div>
        <div class="theme-dot" onclick="setTheme('red')" title="Rojo" style="background: linear-gradient(135deg,#e53e3e,#c53030)"></div>
        <div class="theme-dot" onclick="setTheme('orange')" title="Naranja" style="background: linear-gradient(135deg,#ea580c,#9a3412)"></div>
        <div class="theme-dot" onclick="setTheme('yellow')" title="Amarillo" style="background: linear-gradient(135deg,#eab308,#a16207)"></div>
    </div>
    
    <script>
        function setTheme(theme) {
            document.cookie = 'theme=' + theme + '; path=/; max-age=31536000';
            location.reload();
        }
        
        // Avatar preview
        document.getElementById('avatarInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatarPreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
