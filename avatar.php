<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}
require_once 'config/db.php';

$username = $_SESSION['user'];

// Get avatar data from database
$stmt = $pdo->prepare("SELECT avatar FROM users WHERE username = :username LIMIT 1");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $user['avatar']) {
    $avatarData = $user['avatar'];

    // Detect image type from binary data (simple check)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->buffer($avatarData);

    header('Content-Type: ' . $mimeType);
    echo $avatarData;
} else {
    // No avatar found, serve default avatar image
    header('Content-Type: image/png');
    readfile('assets/img/default_avatar.png');
}
?>
