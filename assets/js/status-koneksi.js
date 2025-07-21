document.addEventListener('DOMContentLoaded', function () {
    const badge = document.getElementById('status-koneksi-badge');

    function updateStatusKoneksi() {
        fetch('get_status.php')
            .then(response => response.json())
            .then(data => {
                if (data.koneksi === 'Terhubung') {
                    badge.textContent = 'Terhubung';
                    badge.className = 'badge bg-success me-3';
                } else {
                    badge.textContent = 'Terputus';
                    badge.className = 'badge bg-danger me-3';
                }
            })
            .catch(() => {
                badge.textContent = 'Error';
                badge.className = 'badge bg-secondary me-3';
            });
    }

    updateStatusKoneksi();
    setInterval(updateStatusKoneksi, 5000);
});
