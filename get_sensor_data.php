<?php
// get_sensor_data.php
require_once 'config/db.php';

header('Content-Type: application/json');

try {
    // Ambil 50 data sensor terbaru
    $stmt = $pdo->query("SELECT waktu, kelembaban_tanah FROM sensor_data ORDER BY waktu DESC LIMIT 50");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Urutkan data dari waktu terlama ke terbaru
    $data = array_reverse($data);

    echo json_encode($data);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil data sensor: ' . $e->getMessage()]);
}
?>
