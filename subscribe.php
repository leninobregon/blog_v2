<?php
session_start();
header('Cache-Control: no-cache, no-store, must-revalidate');
include 'includes/functions.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');
    
    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if(subscribeNewsletter($email, $name)) {
            $_SESSION['newsletter_success'] = '¡Gracias por suscribirte!';
        } else {
            $_SESSION['newsletter_error'] = 'Este email ya está suscrito o hubo un error.';
        }
    } else {
        $_SESSION['newsletter_error'] = 'Email inválido.';
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;