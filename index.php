<?php
// index.php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/db.php';

// Ambil data sensor terbaru
$stmt = $pdo->query("SELECT * FROM sensor_data ORDER BY waktu DESC LIMIT 1");
$sensor = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil status pompa dan mode terbaru
$stmt2 = $pdo->query("SELECT * FROM kontrol_pompa ORDER BY waktu DESC LIMIT 1");
$kontrol = $stmt2->fetch(PDO::FETCH_ASSOC);

$pump_status = $kontrol['status'] ?? 'OFF';
$mode = $kontrol['mode'] ?? 'otomatis';

// Ambang batas kelembaban tanah untuk mode otomatis
$soil_moisture_threshold = 40; // contoh nilai, bisa disesuaikan

// Jika mode otomatis dan kelembaban tanah di bawah ambang, pompa ON
if ($mode === 'otomatis' && $sensor && $sensor['kelembaban_tanah'] < $soil_moisture_threshold) {
    $pump_status = 'ON';
}

// Ambil data histori kelembaban tanah dan status pompa (misal 30 data terakhir)
$stmtHist = $pdo->query("SELECT waktu, kelembaban_tanah FROM sensor_data ORDER BY waktu DESC LIMIT 30");
$sensor_data = array_reverse($stmtHist->fetchAll(PDO::FETCH_ASSOC));

$stmtPump = $pdo->query("SELECT waktu, status FROM kontrol_pompa ORDER BY waktu DESC LIMIT 30");
$pump_data = array_reverse($stmtPump->fetchAll(PDO::FETCH_ASSOC));

include 'includes/header.php';
?>

<div class="main-content">
    <h1 class="h2 mb-4 text-success">Dashboard Smart Garden</h1>

    <div class="row g-4">
        <!-- Card Sensor Kelembaban Tanah -->
        <div class="col-sm-6 col-md-3">
            <div class="card text-white bg-success h-100">
                <div class="card-header">Kelembaban Tanah</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="card-title mb-0"><?php echo $sensor ? $sensor['kelembaban_tanah'] . ' %' : '-'; ?></h3>
                </div>
            </div>
        </div>

        <!-- Card Suhu DS18B20 -->
        <div class="col-sm-6 col-md-3">
            <div class="card text-white bg-info h-100">
                <div class="card-header">Suhu Tanah (DS18B20)</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="card-title mb-0"><?php echo $sensor ? $sensor['suhu_ds18b20'] . ' °C' : '-'; ?></h3>
                </div>
            </div>
        </div>

        <!-- Card Suhu DHT11 -->
        <div class="col-sm-6 col-md-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-header">Suhu Udara (DHT11)</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="card-title mb-0"><?php echo $sensor ? $sensor['suhu_dht11'] . ' °C' : '-'; ?></h3>
                </div>
            </div>
        </div>

        <!-- Card Kelembaban Udara DHT11 -->
        <div class="col-sm-6 col-md-3">
            <div class="card text-white bg-warning h-100">
                <div class="card-header">Kelembaban Udara (DHT11)</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="card-title mb-0"><?php echo $sensor ? $sensor['kelembaban_dht11'] . ' %' : '-'; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Mode dan Kontrol Pompa -->
    <div class="card mt-5">
        <div class="card-header">
            Status Mode Penyiraman: <strong><?php echo ucfirst($mode); ?></strong>
        </div>
        <div class="card-body">
            <p>Status Pompa: <strong><?php echo $pump_status; ?></strong></p>

            <?php if ($mode === 'manual'): ?>
                <form method="post" action="update_status.php" class="d-inline">
                    <input type="hidden" name="status" value="<?php echo $pump_status === 'ON' ? 'OFF' : 'ON'; ?>">
                    <button type="submit" class="btn btn-<?php echo $pump_status === 'ON' ? 'danger' : 'success'; ?> d-flex align-items-center">
                        <img src="assets/img/<?php echo $pump_status === 'ON' ? '010-switch-off.png' : '009-switch-on.png'; ?>" alt="Pompa Icon" width="20" height="20" class="me-2" />
                        Pompa <?php echo $pump_status === 'ON' ? 'Mati' : 'Hidup'; ?>
                    </button>
                </form>
            <?php else: ?>
                <p>Pompa dikontrol secara otomatis berdasarkan kelembaban tanah.</p>
            <?php endif; ?>

            <hr>

            <form method="post" action="set_mode.php" class="d-inline">
                <input type="hidden" name="mode" value="<?php echo $mode === 'otomatis' ? 'manual' : 'otomatis'; ?>">
                <button type="submit" class="btn btn-primary d-flex align-items-center">
                    <img src="assets/img/008-mode.png" alt="Mode Icon" width="20" height="20" class="me-2" />
                    Ganti ke Mode <?php echo $mode === 'otomatis' ? 'Manual' : 'Otomatis'; ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Chart Histori Kelembaban Tanah -->
    <div class="card mt-5">
        <div class="card-header">
            Grafik Histori Kelembaban Tanah
        </div>
        <div class="card-body">
            <canvas id="soilMoistureChart" height="100"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/chart-history.js"></script>
<script>
    const sensorData = <?php echo json_encode($sensor_data); ?>;
    const pumpData = <?php echo json_encode($pump_data); ?>;
    initSoilMoistureChart(sensorData, pumpData);
</script>

<?php include 'includes/footer.php'; ?>
