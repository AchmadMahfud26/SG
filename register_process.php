<?php
// register_process.php
session_start();
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($username === '' || $password === '' || $password_confirm === '') {
        $_SESSION['error'] = 'Semua field harus diisi.';
        header('Location: register.php');
        exit;
    }

    if ($password !== $password_confirm) {
        $_SESSION['error'] = 'Password dan konfirmasi password tidak cocok.';
        header('Location: register.php');
        exit;
    }

    // Cek apakah username sudah ada
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $_SESSION['error'] = 'Username sudah digunakan.';
        header('Location: register.php');
        exit;
    }

    // Hash password dan simpan user baru
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    $stmt->execute(['username' => $username, 'password' => $password_hash]);

    $_SESSION['success'] = 'Registrasi berhasil. Silakan login.';
    header('Location: login.php');
    exit;
} else {
    header('Location: register.php');
    exit;
}
?>
