<?php
if(session_status() === PHP_SESSION_NONE) session_start();
if(empty($_SESSION['logged'])) { header('Location: login.php'); exit; }

$file = $_GET['file'] ?? '';

if(empty($file)) {
    header('Location: dashboard.php');
    exit;
}

// Security: prevent directory traversal
$file = basename($file);
$filepath = dirname(__DIR__) . '/db/' . $file;

if(!file_exists($filepath)) {
    header('Location: dashboard.php?error=notfound');
    exit;
}

// Download file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, must-revalidate');

readfile($filepath);
exit;
