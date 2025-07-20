<?php
// update_status.php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = isset($_POST['status']) && in_array($_POST['status'], ['ON', 'OFF']) ? $_POST['status'] : null;

    if ($status === null) {
        http_response_code(400);
        echo "Status pompa tidak valid.";
        exit;
    }

    // Ambil mode saat ini
    $stmt = $pdo->query("SELECT mode FROM kontrol_pompa ORDER BY waktu DESC LIMIT 1");
    $current_mode = $stmt->fetchColumn() ?: 'otomatis';

    try {
        $stmt = $pdo->prepare("INSERT INTO kontrol_pompa (status, mode, waktu) VALUES (?, ?, NOW())");
        $stmt->execute([$status, $current_mode]);
        // Kirim perintah ke alat secara real-time
        @file_get_contents("http://192.168.188.122/pompa?status=$status");
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Gagal memperbarui status pompa: " . $e->getMessage();
    }
} else {
    http_response_code(405);
    echo "Metode tidak diizinkan.";
}
?>
