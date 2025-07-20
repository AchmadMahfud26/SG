<?php
// config/db.php
// Konfigurasi koneksi database MySQL

$host = 'localhost';
$dbname = 'smartgarden';
$username = 'root';
$password = '';

// Membuat koneksi
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Set error mode ke exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
