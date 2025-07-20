<?php
// set_mode.php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = isset($_POST['mode']) && in_array($_POST['mode'], ['otomatis', 'manual']) ? $_POST['mode'] : null;

    if ($mode === null) {
        http_response_code(400);
        echo "Mode tidak valid.";
        exit;
    }

    // Ambil status pompa saat ini
    $stmt = $pdo->query("SELECT status FROM kontrol_pompa ORDER BY waktu DESC LIMIT 1");
    $current_status = $stmt->fetchColumn() ?: 'OFF';

    try {
        $stmt = $pdo->prepare("INSERT INTO kontrol_pompa (status, mode, waktu) VALUES (?, ?, NOW())");
        $stmt->execute([$current_status, $mode]);
        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Gagal memperbarui mode: " . $e->getMessage();
    }
} else {
    http_response_code(405);
    echo "Metode tidak diizinkan.";
}
?>
