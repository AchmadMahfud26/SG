<?php
require_once 'config/db.php';
header('Content-Type: application/json');

$stmt = $pdo->query("SELECT status, mode FROM kontrol_pompa ORDER BY waktu DESC LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil waktu terakhir dari sensor_data
$stmt_time = $pdo->query("SELECT waktu FROM sensor_data ORDER BY waktu DESC LIMIT 1");
$row_time = $stmt_time->fetch(PDO::FETCH_ASSOC);

$status_koneksi = 'Terputus';
if ($row_time && isset($row_time['waktu'])) {
    $last_time = strtotime($row_time['waktu']);
    $now = time();
    if (($now - $last_time) < 60) { // 60 detik
        $status_koneksi = 'Terhubung';
    }
}

if ($row) {
    echo json_encode([
        'mode' => $row['mode'],
        'status' => $row['status'],
        'koneksi' => $status_koneksi
    ]);
} else {
    echo json_encode([
        'mode' => 'otomatis',
        'status' => 'OFF',
        'koneksi' => $status_koneksi
    ]);
}
?>
