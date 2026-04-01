<?php
if(session_status() === PHP_SESSION_NONE) session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
include 'includes/functions.php';
$error = '';
$success = '';
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$email = $_GET['email'] ?? '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($step === 1) {
        $email = $_POST['email'] ?? '';
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT id, username, recovery_question FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user) {
            $_SESSION['recover_user_id'] = $user['id'];
            $_SESSION['recover_email'] = $email;
            header('Location: recover.php?step=2');
            exit;
        } else {
            $error = 'Email no encontrado';
        }
    } elseif($step === 2) {
        $answer = $_POST['answer'] ?? '';
        $userId = $_SESSION['recover_user_id'] ?? 0;
        
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT recovery_answer FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user && strtolower(trim($answer)) === strtolower(trim($user['recovery_answer']))) {
            $_SESSION['recover_verified'] = true;
            header('Location: recover.php?step=3');
            exit;
        } else {
            $error = 'Respuesta incorrecta';
        }
    } elseif($step === 3) {
        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $userId = $_SESSION['recover_user_id'] ?? 0;
        
        if($newPassword !== $confirmPassword) {
            $error = 'Las contraseñas no coinciden';
        } elseif(strlen($newPassword) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres';
        } else {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $pdo = getDB();
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $userId]);
            
            $success = 'Contraseña actualizada correctamente';
            session_destroy();
            $step = 4;
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
    <title>Recuperar Contraseña - <?= CONFIG['site_name'] ?></title>
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
        .card {
            background: var(--bg-secondary);
            border: 3px solid var(--primary);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .card h1 {
            color: var(--primary);
            text-align: center;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        .card .subtitle {
            color: var(--text-secondary);
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            background: var(--bg);
            color: var(--text);
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }
        .btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn:hover { transform: translateY(-2px); }
        .error { color: #dc3545; background: rgba(220,53,69,0.1); padding: 1rem; border-radius: 10px; margin-bottom: 1rem; text-align: center; }
        .success { color: #28a745; background: rgba(40,167,69,0.1); padding: 1rem; border-radius: 10px; margin-bottom: 1rem; text-align: center; }
        .back-link { display: block; text-align: center; margin-top: 1.5rem; color: var(--link); text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <?php if($step === 1): ?>
        <h1><i class="fas fa-key"></i> Recuperar Contraseña</h1>
        <p class="subtitle">Ingresa tu email para comenzar</p>
        <?php elseif($step === 2): ?>
        <h1><i class="fas fa-question-circle"></i> Pregunta de Seguridad</h1>
        <p class="subtitle">Responde para continuar</p>
        <?php elseif($step === 3): ?>
        <h1><i class="fas fa-lock"></i> Nueva Contraseña</h1>
        <p class="subtitle">Ingresa tu nueva contraseña</p>
        <?php elseif($step === 4): ?>
        <h1><i class="fas fa-check-circle"></i> Listo!</h1>
        <p class="subtitle">Tu contraseña ha sido actualizada</p>
        <?php endif; ?>
        
        <?php if($error): ?><div class="error"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div><?php endif; ?>
        <?php if($success): ?><div class="success"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        
        <?php if($step === 1): ?>
        <form method="post">
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" placeholder="tu@email.com" required>
            </div>
            <button type="submit" class="btn"><i class="fas fa-paper-plane"></i> Continuar</button>
        </form>
        <?php elseif($step === 2): ?>
        <?php
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT recovery_question FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['recover_user_id']]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <form method="post">
            <div class="form-group">
                <label><i class="fas fa-question"></i> <?= htmlspecialchars($question['recovery_question'] ?? 'Pregunta de seguridad') ?></label>
                <input type="text" name="answer" placeholder="Tu respuesta" required>
            </div>
            <button type="submit" class="btn"><i class="fas fa-check"></i> Verificar</button>
        </form>
        <?php elseif($step === 3): ?>
        <form method="post">
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Nueva Contraseña</label>
                <input type="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Confirmar Contraseña</label>
                <input type="password" name="confirm_password" placeholder="Repite la contraseña" required minlength="6">
            </div>
            <button type="submit" class="btn"><i class="fas fa-save"></i> Cambiar Contraseña</button>
        </form>
        <?php elseif($step === 4): ?>
        <a href="auth.php" class="btn" style="display:inline-block; text-align:center;"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
        <?php endif; ?>
        
        <a href="auth.php" class="back-link"><i class="fas fa-arrow-left"></i> Volver al Login</a>
    </div>
</body>
</html>