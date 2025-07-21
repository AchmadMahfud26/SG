<?php
// get_dashboard_data.php
require_once 'config/db.php';

header('Content-Type: application/json');

try {
    // Ambil data sensor terbaru
    $stmt = $pdo->query("SELECT * FROM sensor_data ORDER BY waktu DESC LIMIT 1");
    $sensor = $stmt->fetch(PDO::FETCH_ASSOC);

    // Cek status koneksi alat
    $status_koneksi = 'Terputus';
    $debug = [];
    if ($sensor && isset($sensor['waktu'])) {
        $last_time = strtotime($sensor['waktu']);
        $now = time();
        $debug['waktu_sensor'] = $sensor['waktu'];
        $debug['waktu_server'] = date('Y-m-d H:i:s', $now);
        $debug['selisih_detik'] = $now - $last_time;
        $selisih = $now - $last_time;
        if ($selisih < 0 || $selisih >= 60) {
            $status_koneksi = 'Terputus';
        } else {
            $status_koneksi = 'Terhubung';
        }
    }

    // Ambil status pompa dan mode terbaru
    $stmt2 = $pdo->query("SELECT * FROM kontrol_pompa ORDER BY waktu DESC LIMIT 1");
    $kontrol = $stmt2->fetch(PDO::FETCH_ASSOC);

    // Ambang batas kelembaban tanah untuk mode otomatis
    $soil_moisture_threshold = 40; // contoh nilai, bisa disesuaikan

    $pump_status = $kontrol['status'] ?? 'OFF';
    $mode = $kontrol['mode'] ?? 'otomatis';

    // Jika mode otomatis dan kelembaban tanah di bawah ambang, pompa ON
    if ($mode === 'otomatis' && $sensor && $sensor['kelembaban_tanah'] < $soil_moisture_threshold) {
        $pump_status = 'ON';
    }

    // Ambil data histori kelembaban tanah dan status pompa (misal 30 data terakhir)
    $sensor_data = [];
    $pump_data = [];
    if ($status_koneksi === 'Terhubung') {
        $stmtHist = $pdo->query("SELECT waktu, kelembaban_tanah FROM sensor_data ORDER BY waktu DESC LIMIT 30");
        $sensor_data = array_reverse($stmtHist->fetchAll(PDO::FETCH_ASSOC));

        $stmtPump = $pdo->query("SELECT waktu, status FROM kontrol_pompa ORDER BY waktu DESC LIMIT 30");
        $pump_data = array_reverse($stmtPump->fetchAll(PDO::FETCH_ASSOC));
    } else {
        $sensor = null;
        $pump_status = null;
    }

    $response = [
        'sensor' => $sensor,
        'pump_status' => $pump_status,
        'mode' => $mode,
        'sensor_data' => $sensor_data,
        'pump_data' => $pump_data,
        'koneksi' => $status_koneksi,
        'debug' => $debug
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil data dashboard: ' . $e->getMessage()]);
}
?>
