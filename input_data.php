<?php
// input_data.php
require_once 'config/db.php';

// Terima data sensor dari ESP8266/ESP32 via GET atau POST
$suhu_ds18b20 = isset($_REQUEST['suhu_ds18b20']) ? floatval($_REQUEST['suhu_ds18b20']) : null;
$suhu_dht11 = isset($_REQUEST['suhu_dht11']) ? floatval($_REQUEST['suhu_dht11']) : null;
$kelembaban_dht11 = isset($_REQUEST['kelembaban_dht11']) ? floatval($_REQUEST['kelembaban_dht11']) : null;
$kelembaban_tanah = isset($_REQUEST['kelembaban_tanah']) ? floatval($_REQUEST['kelembaban_tanah']) : null;

if ($suhu_ds18b20 === null || $suhu_dht11 === null || $kelembaban_dht11 === null || $kelembaban_tanah === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Data sensor tidak lengkap']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO sensor_data (waktu, suhu_ds18b20, suhu_dht11, kelembaban_dht11, kelembaban_tanah) VALUES (NOW(), ?, ?, ?, ?)");
    $stmt->execute([$suhu_ds18b20, $suhu_dht11, $kelembaban_dht11, $kelembaban_tanah]);
    echo json_encode(['status' => 'success', 'message' => 'Data sensor berhasil disimpan']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data sensor: ' . $e->getMessage()]);
}
?>
