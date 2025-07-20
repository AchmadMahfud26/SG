<?php
// monitoring.php
require_once 'config/db.php';

// Ambil data sensor untuk grafik (misal 50 data terakhir)
$stmt = $pdo->query("SELECT waktu, kelembaban_tanah FROM sensor_data ORDER BY waktu DESC LIMIT 50");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Urutkan data dari waktu terlama ke terbaru
$data = array_reverse($data);

// Siapkan data untuk Chart.js
$labels = [];
$values = [];
foreach ($data as $row) {
    $labels[] = date('H:i:s', strtotime($row['waktu']));
    $values[] = $row['kelembaban_tanah'];
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <h1>Grafik Histori Kelembaban Tanah</h1>
    <canvas id="soilMoistureChart" width="400" height="200"></canvas>
    <a href="index.php" class="btn btn-secondary mt-3">Kembali ke Dashboard</a>
</div>

<script>
    const ctx = document.getElementById('soilMoistureChart').getContext('2d');
    const soilMoistureChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Kelembaban Tanah (%)',
                data: <?php echo json_encode($values); ?>,
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    min: 0,
                    max: 100
                }
            }
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
