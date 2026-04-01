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
$users = getAllUsers();
$msg = '';

// Create or Update User
if(isset($_POST['create_user']) || isset($_POST['update_user'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $password = $_POST['password'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $bio = $_POST['bio'] ?? '';
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
    
    if(isset($_POST['update_user']) && !empty($_POST['user_id'])) {
        $pdo = getDB();
        if($avatar) {
            $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, role=?, first_name=?, last_name=?, phone=?, bio=?, facebook=?, twitter=?, telegram=?, instagram=?, youtube=?, linkedin=?, website=?, avatar=? WHERE id=?");
            $stmt->execute([$username, $email, $role, $firstName, $lastName, $phone, $bio, $facebook, $twitter, $telegram, $instagram, $youtube, $linkedin, $website, $avatar, $_POST['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, role=?, first_name=?, last_name=?, phone=?, bio=?, facebook=?, twitter=?, telegram=?, instagram=?, youtube=?, linkedin=?, website=? WHERE id=?");
            $stmt->execute([$username, $email, $role, $firstName, $lastName, $phone, $bio, $facebook, $twitter, $telegram, $instagram, $youtube, $linkedin, $website, $_POST['user_id']]);
        }
        if(!empty($password)) {
            updateUserPassword($_POST['user_id'], $password);
        }
        $msg = 'Usuario actualizado';
    } else {
        try {
            $pdo = getDB();
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, first_name, last_name, phone, avatar, bio, facebook, twitter, telegram, instagram, youtube, linkedin, website) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hash, $role, $firstName, $lastName, $phone, $avatar, $bio, $facebook, $twitter, $telegram, $instagram, $youtube, $linkedin, $website]);
            $msg = 'Usuario creado';
        } catch(Exception $e) {
            $msg = 'Error: usuario o email ya existe';
        }
    }
    $users = getAllUsers();
}

// Delete User
if(isset($_GET['delete'])) {
    deleteUser($_GET['delete']);
    header('Location: users.php'); exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - <?= CONFIG['site_name'] ?></title>
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
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem 1rem; }
        .card { background: var(--bg-secondary); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 2rem; box-shadow: var(--shadow-sm); }
        .card h2 { color: var(--primary); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
        .form-grid-full { display: grid; grid-template-columns: 1fr; gap: 1rem; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text); }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.8rem; border: 2px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); color: var(--text); font-size: 1rem; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: var(--primary); }
        .form-group textarea { min-height: 80px; resize: vertical; }
        .social-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
        .section-title { font-size: 1rem; font-weight: 600; color: var(--primary); margin: 1.5rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--border); }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.2rem; background: var(--primary); color: white; text-decoration: none; border-radius: var(--radius-sm); border: none; cursor: pointer; font-weight: 600; transition: all 0.3s; }
        .btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-secondary { background: var(--secondary); }
        .btn-danger { background: #dc3545; }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.85rem; }
        table { width: 100%; border-collapse: collapse; background: var(--bg); border-radius: var(--radius-md); overflow: hidden; }
        th, td { padding: 0.8rem; text-align: left; border-bottom: 1px solid var(--border); }
        th { background: var(--primary); color: white; font-size: 0.9rem; }
        tr:hover { background: var(--bg-secondary); }
        .actions { display: flex; gap: 0.5rem; }
        .role-badge { padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.8rem; }
        .role-admin { background: var(--primary); color: white; }
        .role-author { background: var(--accent); color: white; }
        .role-user { background: var(--secondary); color: white; }
        .msg { background: rgba(40, 167, 69, 0.1); color: #28a745; padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1rem; border: 1px solid #28a745; }
        .theme-float { position: fixed; bottom: 20px; right: 20px; display: flex; gap: 8px; background: var(--bg-secondary); padding: 10px 14px; border-radius: 50px; box-shadow: var(--shadow-lg); z-index: 999; }
        .theme-dot { width: 28px; height: 28px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: all 0.3s; }
        .theme-dot:hover, .theme-dot.active { transform: scale(1.2); border-color: var(--text); }
        @media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } .social-grid { grid-template-columns: 1fr; } }
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
            <h2><i class="fas fa-user-plus"></i> Crear Nuevo Usuario</h2>
            <form method="post" enctype="multipart/form-data">
                <p class="section-title"><i class="fas fa-id-card"></i> Datos Personales</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nombre</label>
                        <input type="text" name="first_name" placeholder="Nombre">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Apellido</label>
                        <input type="text" name="last_name" placeholder="Apellido">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Usuario</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Teléfono</label>
                        <input type="text" name="phone" placeholder="+5911234567">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user-tag"></i> Rol</label>
                        <select name="role">
                            <option value="user">Usuario</option>
                            <option value="author">Autor</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-camera"></i> Foto de Perfil</label>
                        <input type="file" name="avatar" accept="image/*">
                    </div>
                </div>
                <div class="form-grid-full">
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Contraseña</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Biografía</label>
                        <textarea name="bio" placeholder="Breve descripción..."></textarea>
                    </div>
                </div>
                
                <p class="section-title"><i class="fas fa-share-alt"></i> Redes Sociales</p>
                <div class="social-grid">
                    <div class="form-group">
                        <label><i class="fab fa-facebook"></i> Facebook</label>
                        <input type="text" name="facebook" placeholder="https://facebook.com/usuario">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-twitter"></i> Twitter/X</label>
                        <input type="text" name="twitter" placeholder="https://twitter.com/usuario">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-telegram"></i> Telegram</label>
                        <input type="text" name="telegram" placeholder="https://t.me/usuario">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-instagram"></i> Instagram</label>
                        <input type="text" name="instagram" placeholder="https://instagram.com/usuario">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-youtube"></i> YouTube</label>
                        <input type="text" name="youtube" placeholder="https://youtube.com/@canal">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-linkedin"></i> LinkedIn</label>
                        <input type="text" name="linkedin" placeholder="https://linkedin.com/in/usuario">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-globe"></i> Website</label>
                        <input type="text" name="website" placeholder="https://miweb.com">
                    </div>
                </div>
                
                <button type="submit" name="create_user" class="btn"><i class="fas fa-save"></i> Crear Usuario</button>
            </form>
        </div>
        
        <div class="card">
            <h2><i class="fas fa-users"></i> Lista de Usuarios (<?= count($users) ?>)</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td>
                            <?php if(!empty($user['avatar'])): ?>
                            <img src="../<?= htmlspecialchars($user['avatar']) ?>" alt="avatar" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                            <?php else: ?>
                            <i class="fas fa-user-circle" style="font-size:40px;color:var(--primary);"></i>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars(($user['first_name'] ?? '').' '.($user['last_name'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                        <td><span class="role-badge role-<?= $user['role'] ?>"><?= htmlspecialchars($user['role']) ?></span></td>
                        <td class="actions">
                            <button class="btn btn-sm btn-secondary" onclick="editUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>', '<?= htmlspecialchars($user['email']) ?>', '<?= $user['role'] ?>', '<?= htmlspecialchars($user['first_name'] ?? '') ?>', '<?= htmlspecialchars($user['last_name'] ?? '') ?>', '<?= htmlspecialchars($user['phone'] ?? '') ?>', '<?= htmlspecialchars($user['bio'] ?? '') ?>', '<?= htmlspecialchars($user['facebook'] ?? '') ?>', '<?= htmlspecialchars($user['twitter'] ?? '') ?>', '<?= htmlspecialchars($user['telegram'] ?? '') ?>', '<?= htmlspecialchars($user['instagram'] ?? '') ?>', '<?= htmlspecialchars($user['youtube'] ?? '') ?>', '<?= htmlspecialchars($user['linkedin'] ?? '') ?>', '<?= htmlspecialchars($user['website'] ?? '') ?>', '<?= htmlspecialchars($user['avatar'] ?? '') ?>')"><i class="fas fa-edit"></i></button>
                            <?php if($user['id'] != 1): ?>
                            <a href="users.php?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar usuario?')"><i class="fas fa-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card" id="editForm" style="display:none;">
            <h2><i class="fas fa-edit"></i> Editar Usuario</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="user_id" id="edit_user_id">
                <input type="hidden" name="update_user" value="1">
                
                <p class="section-title"><i class="fas fa-id-card"></i> Datos Personales</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Nombre</label>
                        <input type="text" name="first_name" id="edit_first_name">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Apellido</label>
                        <input type="text" name="last_name" id="edit_last_name">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Usuario</label>
                        <input type="text" name="username" id="edit_username" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" id="edit_email" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Teléfono</label>
                        <input type="text" name="phone" id="edit_phone">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user-tag"></i> Rol</label>
                        <select name="role" id="edit_role">
                            <option value="user">Usuario</option>
                            <option value="author">Autor</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-camera"></i> Foto de Perfil</label>
                        <div id="current-avatar"></div>
                        <input type="file" name="avatar" accept="image/*">
                    </div>
                </div>
                <div class="form-grid-full">
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Nueva Contraseña (dejar vacío para mantener)</label>
                        <input type="password" name="password" placeholder="Nueva contraseña">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Biografía</label>
                        <textarea name="bio" id="edit_bio"></textarea>
                    </div>
                </div>
                
                <p class="section-title"><i class="fas fa-share-alt"></i> Redes Sociales</p>
                <div class="social-grid">
                    <div class="form-group">
                        <label><i class="fab fa-facebook"></i> Facebook</label>
                        <input type="text" name="facebook" id="edit_facebook">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-twitter"></i> Twitter/X</label>
                        <input type="text" name="twitter" id="edit_twitter">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-telegram"></i> Telegram</label>
                        <input type="text" name="telegram" id="edit_telegram">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-instagram"></i> Instagram</label>
                        <input type="text" name="instagram" id="edit_instagram">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-youtube"></i> YouTube</label>
                        <input type="text" name="youtube" id="edit_youtube">
                    </div>
                    <div class="form-group">
                        <label><i class="fab fa-linkedin"></i> LinkedIn</label>
                        <input type="text" name="linkedin" id="edit_linkedin">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-globe"></i> Website</label>
                        <input type="text" name="website" id="edit_website">
                    </div>
                </div>
                
                <button type="submit" class="btn"><i class="fas fa-save"></i> Actualizar</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editForm').style.display='none'">Cancelar</button>
            </form>
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
        function editUser(id, username, email, role, firstName, lastName, phone, bio, facebook, twitter, telegram, instagram, youtube, linkedin, website, avatar) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_first_name').value = firstName;
            document.getElementById('edit_last_name').value = lastName;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('edit_bio').value = bio;
            document.getElementById('edit_facebook').value = facebook;
            document.getElementById('edit_twitter').value = twitter;
            document.getElementById('edit_telegram').value = telegram;
            document.getElementById('edit_instagram').value = instagram;
            document.getElementById('edit_youtube').value = youtube;
            document.getElementById('edit_linkedin').value = linkedin;
            document.getElementById('edit_website').value = website;
            
            // Show current avatar
            const avatarDiv = document.getElementById('current-avatar');
            if(avatar) {
                avatarDiv.innerHTML = '<img src="../' + avatar + '" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:0.5rem;"><br><small>Foto actual</small>';
            } else {
                avatarDiv.innerHTML = '<i class="fas fa-user-circle" style="font-size:80px;color:var(--primary);"></i><br><small>Sin foto</small>';
            }
            
            document.getElementById('editForm').style.display = 'block';
            document.getElementById('editForm').scrollIntoView({behavior: 'smooth'});
        }
    </script>
</body>
</html>