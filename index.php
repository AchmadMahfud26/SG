<?php
// index.php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/db.php';

include 'includes/header.php';
?>

<div class="main-content">
    <h1 class="h2 mb-4 text-success">Dashboard Smart Garden</h1>

    <div class="row g-4">
        <!-- Card Sensor Kelembaban Tanah -->
        <div class="col-sm-6 col-md-3">
            <div class="card text-white bg-success h-100">
                <div class="card-header">Kelembaban Tanah</div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <canvas id="soilMoistureGauge" width="150" height="150"></canvas>
                    <div id="soilMoistureValue" class="gauge-value mt-2">-</div>
                </div>
            </div>
        </div>

        <!-- Card Suhu DS18B20 -->
        <div class="col-sm-6 col-md-3">
            <div class="card text-white bg-info h-100">
                <div class="card-header">Suhu Tanah (DS18B20)</div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <canvas id="soilTempGauge" width="150" height="150"></canvas>
                    <div id="soilTempValue" class="gauge-value mt-2">-</div>
                </div>
            </div>
        </div>

        <!-- Card Suhu DHT11 -->
        <div class="col-sm-6 col-md-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-header">Kelembaban Udara (DHT11)</div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <canvas id="airTempGauge" width="150" height="150"></canvas>
                    <div id="airTempValue" class="gauge-value mt-2">-</div>
                </div>
            </div>
        </div>

        <!-- Card Status Koneksi -->
        <div class="col-sm-6 col-md-3">
        <div class="card text-white bg-warning h-100">
        <div class="card-header">Status Koneksi</div>
        <div class="card-body d-flex align-items-center justify-content-center">
        <h3 class="card-title mb-0" id="connectionStatusValue">-</h3>
        </div>
        </div>
        </div>
    </div>

    <!-- Status Mode dan Kontrol Pompa -->
    <div class="card mt-5">
        <div class="card-header">
            Status Mode Penyiraman: <strong id="modeValue">-</strong>
        </div>
        <div class="card-body">
            <p>Status Pompa: <strong id="pumpStatusValue">-</strong></p>

            <form method="post" action="update_status.php" class="d-inline" id="pumpControlForm" style="display:none;">
                <input type="hidden" name="status" id="pumpStatusInput" value="">
                <button type="submit" class="btn d-flex align-items-center" id="pumpControlButton">
                    <img src="" alt="Pompa Icon" width="20" height="20" class="me-2" id="pumpIcon" />
                    <span id="pumpButtonText"></span>
                </button>
            </form>

            <p id="autoControlText" style="display:none;">Pompa dikontrol secara otomatis berdasarkan kelembaban tanah.</p>

            <hr>

            <form method="post" action="set_mode.php" class="d-inline" id="modeControlForm" style="display:none;">
                <input type="hidden" name="mode" id="modeInput" value="">
                <button type="submit" class="btn btn-primary d-flex align-items-center" id="modeControlButton">
                    <img src="assets/img/008-mode.png" alt="Mode Icon" width="20" height="20" class="me-2" />
                    <span id="modeButtonText"></span>
                </button>
            </form>
        </div>
    </div>

    <!-- Chart Histori Kelembaban Tanah -->
    <div class="card mt-5" style="min-height: 400px;">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span id="chartDate" class="fw-bold text-primary"></span>
            <span>Grafik Histori Kelembaban Tanah</span>
        </div>
        <div class="card-body" style="height: 500px;">
            <canvas id="soilMoistureChart" height="300"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/chart-history.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    let soilMoistureChart = null;

    function updateDashboard(data) {
        if (!data) return;

        // Update tanggal di header grafik
        if (data.sensor && data.sensor.waktu) {
            const date = new Date(data.sensor.waktu);
            const tanggal = date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
            document.getElementById('chartDate').textContent = tanggal;
        } else {
            document.getElementById('chartDate').textContent = '';
        }

        // Update sensor values
        document.getElementById('soilMoistureValue').textContent = data.sensor ? data.sensor.kelembaban_tanah + ' %' : '-';
        document.getElementById('soilTempValue').textContent = data.sensor ? data.sensor.suhu_ds18b20 + ' °C' : '-';
        document.getElementById('airTempValue').textContent = data.sensor ? data.sensor.suhu_dht11 + ' °C' : '-';
        // document.getElementById('airHumidityValue').textContent = data.sensor ? data.sensor.kelembaban_dht11 + ' %' : '-';

        // Update mode and pump status
        document.getElementById('modeValue').textContent = data.mode ? data.mode.charAt(0).toUpperCase() + data.mode.slice(1) : '-';
        document.getElementById('pumpStatusValue').textContent = data.pump_status || '-';

        // Update pump control UI
        const pumpControlForm = document.getElementById('pumpControlForm');
        const pumpStatusInput = document.getElementById('pumpStatusInput');
        const pumpControlButton = document.getElementById('pumpControlButton');
        const pumpIcon = document.getElementById('pumpIcon');
        const pumpButtonText = document.getElementById('pumpButtonText');
        const autoControlText = document.getElementById('autoControlText');
        const modeControlForm = document.getElementById('modeControlForm');
        const modeInput = document.getElementById('modeInput');
        const modeControlButton = document.getElementById('modeControlButton');
        const modeButtonText = document.getElementById('modeButtonText');

        if (data.mode === 'manual') {
            pumpControlForm.style.display = 'inline';
            autoControlText.style.display = 'none';
            pumpStatusInput.value = data.pump_status === 'ON' ? 'OFF' : 'ON';
            pumpControlButton.className = 'btn d-flex align-items-center btn-' + (data.pump_status === 'ON' ? 'danger' : 'success');
            pumpIcon.src = 'assets/img/' + (data.pump_status === 'ON' ? '010-switch-off.png' : '009-switch-on.png');
            pumpButtonText.textContent = 'Pompa ' + (data.pump_status === 'ON' ? 'Mati' : 'Hidup');
        } else {
            pumpControlForm.style.display = 'none';
            autoControlText.style.display = 'block';
        }

        if (data.mode) {
            modeControlForm.style.display = 'inline';
            modeInput.value = data.mode === 'otomatis' ? 'manual' : 'otomatis';
            modeButtonText.textContent = 'Ganti ke Mode ' + (data.mode === 'otomatis' ? 'Manual' : 'Otomatis');
        } else {
            modeControlForm.style.display = 'none';
        }

        // Update chart
        if (!soilMoistureChart) {
            soilMoistureChart = initSoilMoistureChart(data.sensor_data, data.pump_data);
        } else {
            updateChartData(soilMoistureChart, data.sensor_data);
        }
    }

    function fetchDashboardData() {
        axios.get('get_dashboard_data.php')
            .then(response => {
                updateDashboard(response.data);
            })
            .catch(error => {
                console.error('Gagal mengambil data dashboard:', error);
            });
    }

    // Fetch data pertama kali saat halaman dimuat
    fetchDashboardData();

    // Polling data tiap 5 detik
    setInterval(fetchDashboardData, 500);

    // Fungsi untuk update status koneksi
    function updateConnectionStatus() {
        axios.get('get_status.php')
            .then(response => {
                const koneksi = response.data.koneksi || '-';
                const el = document.getElementById('connectionStatusValue');
                el.textContent = koneksi;
                el.style.color = (koneksi === 'Terhubung') ? '#198754' : '#dc3545'; // hijau/merah
            })
            .catch(() => {
                const el = document.getElementById('connectionStatusValue');
                el.textContent = '-';
                el.style.color = '#212529';
            });
    }

    // Update status koneksi pertama kali
    updateConnectionStatus();
    // Polling status koneksi tiap 5 detik
    setInterval(updateConnectionStatus, 500);

// AJAX untuk kontrol pompa
    document.getElementById('pumpControlForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        axios.post('update_status.php', formData)
            .then(() => {
                fetchDashboardData();
            });
    });

    // AJAX untuk kontrol mode
    document.getElementById('modeControlForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        axios.post('set_mode.php', formData)
            .then(() => {
                fetchDashboardData();
            });
    });
// Gauge Chart.js setup
let soilMoistureGaugeChart, soilTempGaugeChart, airTempGaugeChart;

function createGaugeChart(ctx, value, color, outlineColor) {
    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [value, 100 - value],
                backgroundColor: [color, '#e9ecef'],
                borderColor: [outlineColor, '#ced4da'],
                borderWidth: [4, 2],
                cutout: '80%',
                circumference: 180,
                rotation: 270,
            }]
        },
        options: {
            responsive: false,
            plugins: {
                tooltip: { enabled: false },
                legend: { display: false },
            }
        }
    });
}

function updateGaugeChart(chart, value) {
    chart.data.datasets[0].data[0] = value;
    chart.data.datasets[0].data[1] = 100 - value;
    chart.update();
}

function initGauges() {
    const soilMoistureCtx = document.getElementById('soilMoistureGauge').getContext('2d');
    const soilTempCtx = document.getElementById('soilTempGauge').getContext('2d');
    const airTempCtx = document.getElementById('airTempGauge').getContext('2d');
    soilMoistureGaugeChart = createGaugeChart(soilMoistureCtx, 0, '#198754', '#145c32'); // hijau
    soilTempGaugeChart = createGaugeChart(soilTempCtx, 0, '#0dcaf0', '#0a6a8a'); // biru muda
    airTempGaugeChart = createGaugeChart(airTempCtx, 0, '#0d6efd', '#083b7a'); // biru tua
}
initGauges();

// Update dashboard override
const oldUpdateDashboard = updateDashboard;
updateDashboard = function(data) {
    oldUpdateDashboard(data);

    // Update gauge values
    if (data && data.sensor) {
        updateGaugeChart(soilMoistureGaugeChart, data.sensor.kelembaban_tanah);
        updateGaugeChart(soilTempGaugeChart, data.sensor.suhu_ds18b20);
        updateGaugeChart(airTempGaugeChart, data.sensor.suhu_dht11);
    }
};
</script>

<?php include 'includes/footer.php'; ?>
