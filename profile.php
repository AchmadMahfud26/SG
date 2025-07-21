<?php
// profile.php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/db.php';

$username = $_SESSION['user'];
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Ambil data user dari database
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    // User tidak ditemukan, logout
    session_destroy();
    header('Location: login.php');
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<div class="mt-5 pt-5 main-content">
    <h2>Profil User</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form action="profile_process.php" method="post" enctype="multipart/form-data">
        <div class="card mb-4">
            <div class="card-header">Ubah Avatar</div>
            <div class="card-body">
                <div class="mb-3">
                    <img src="avatar.php" alt="Avatar" class="rounded-circle mb-3" width="100" height="100" />
                    <input type="file" name="avatar" accept="image/*" class="form-control" />
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Atur Ulang Kata Sandi</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="current_password" class="form-label">Kata Sandi Saat Ini</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">Kata Sandi Baru</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" />
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" />
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Ubah Informasi User</div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required />
                </div>
            </div>
        </div>
        <div class="text-end">
            <button type="submit" name="action" value="save_all" class="btn btn-success">Simpan Perubahan</button>
        </div>
    </form>

    <div class="card mb-4">
        <div class="card-header">Hapus Akun</div>
        <div class="card-body">
            <form action="profile_process.php" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini? Tindakan ini tidak dapat dibatalkan.');">
                <input type="hidden" name="action" value="delete_user" />
                <button type="submit" class="btn btn-danger">Hapus Akun</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
