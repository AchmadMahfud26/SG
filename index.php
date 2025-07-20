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
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="card-title mb-0" id="soilMoistureValue">-</h3>
                </div>
            </div>
        </div>

        <!-- Card Suhu DS18B20 -->
        <div class="col-sm-6 col-md-3">
            <div class="card text-white bg-info h-100">
                <div class="card-header">Suhu Tanah (DS18B20)</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="card-title mb-0" id="soilTempValue">-</h3>
                </div>
            </div>
        </div>

        <!-- Card Suhu DHT11 -->
        <div class="col-sm-6 col-md-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-header">Kelembaban Udara (DHT11)</div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <h3 class="card-title mb-0" id="airTempValue">-</h3>
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
</script>

<?php include 'includes/footer.php'; ?>
