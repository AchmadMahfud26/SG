<?php
// monitoring.php
require_once 'config/db.php';

// Ambil data sensor untuk grafik (misal 50 data terakhir)
$stmt = $pdo->query("SELECT waktu, kelembaban_tanah FROM sensor_data ORDER BY waktu DESC LIMIT 50");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Urutkan data dari waktu terlama ke terbaru
$data = array_reverse($data);

include 'includes/header.php';
?>

<div class="container mt-4">
    <h1>Grafik Histori Kelembaban Tanah</h1>
    <canvas id="soilMoistureChart" width="400" height="200"></canvas>
    <a href="index.php" class="btn btn-secondary mt-3">Kembali ke Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="assets/js/chart-history.js"></script>
<script>
    // Inisialisasi chart dengan data awal dari PHP
    const initialData = <?php echo json_encode($data); ?>;
    const soilMoistureChart = initSoilMoistureChart(initialData, []);

    // Fungsi untuk mengambil data sensor terbaru via AJAX dan update chart
    function fetchLatestSensorData() {
        console.log('Fetching latest sensor data...');
        axios.get('get_sensor_data.php')
            .then(response => {
                console.log('Received data:', response.data);
                if (response.data) {
                    updateChartData(soilMoistureChart, response.data);
                }
            })
            .catch(error => {
                console.error('Gagal mengambil data sensor:', error);
            });
    }

    // Polling data sensor tiap 5 detik
    setInterval(fetchLatestSensorData, 5000);
</script>

<?php include 'includes/footer.php'; ?>
