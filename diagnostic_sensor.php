<?php
require_once 'config/db.php';

echo "<h2>Diagnostik Sensor Smart Garden</h2>";
echo "<b>Waktu server saat ini:</b> " . date('Y-m-d H:i:s') . "<br><br>";

$stmt = $pdo->query("SELECT * FROM sensor_data ORDER BY waktu DESC LIMIT 1");
$sensor = $stmt->fetch(PDO::FETCH_ASSOC);

if ($sensor) {
    echo "<b>Data sensor terakhir:</b><br>";
    foreach ($sensor as $key => $val) {
        echo htmlspecialchars($key) . ': ' . htmlspecialchars($val) . '<br>';
    }
    $last_time = strtotime($sensor['waktu']);
    $now = time();
    $selisih = $now - $last_time;
    echo "<br><b>Selisih waktu server dengan data sensor (detik):</b> $selisih<br>";
    if ($selisih < 0) {
        echo "<span style='color:red'>Waktu alat lebih MAJU dari server!</span><br>";
    } elseif ($selisih >= 60) {
        echo "<span style='color:red'>Data sensor sudah lebih dari 60 detik, status harusnya TERPUTUS.</span><br>";
    } else {
        echo "<span style='color:green'>Data sensor masih fresh, status harusnya TERHUBUNG.</span><br>";
    }
} else {
    echo "<b>Tidak ada data pada tabel sensor_data.</b>";
}
