<?php
// profile_process.php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
require_once 'config/db.php';

$username = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_avatar') {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['avatar']['tmp_name'];
            $fileName = $_FILES['avatar']['name'];
            $fileSize = $_FILES['avatar']['size'];
            $fileType = $_FILES['avatar']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedfileExtensions)) {
                // Read the file content
                $avatarData = file_get_contents($fileTmpPath);

                // Update avatar in database
                $stmt = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE username = :username");
                $stmt->bindParam(':avatar', $avatarData, PDO::PARAM_LOB);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Avatar berhasil diunggah.';
                } else {
                    $_SESSION['error'] = 'Terjadi kesalahan saat menyimpan avatar ke database.';
                }
            } else {
                $_SESSION['error'] = 'Format file tidak didukung. Gunakan jpg, jpeg, png, atau gif.';
            }
        } else {
            $_SESSION['error'] = 'Tidak ada file avatar yang diunggah.';
        }
        header('Location: profile.php');
        exit;
    }

    // Gabungan semua perubahan profil
    if ($action === 'save_all') {
        $messages = [];
        $hasError = false;
        // 1. Update avatar jika ada file diupload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['avatar']['tmp_name'];
            $fileName = $_FILES['avatar']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
            $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedfileExtensions)) {
                $avatarData = file_get_contents($fileTmpPath);
                $stmt = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE username = :username");
                $stmt->bindParam(':avatar', $avatarData, PDO::PARAM_LOB);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                if ($stmt->execute()) {
                    $messages[] = 'Avatar berhasil diunggah.';
                } else {
                    $messages[] = 'Terjadi kesalahan saat menyimpan avatar ke database.';
                    $hasError = true;
                }
            } else if ($fileName) {
                $messages[] = 'Format file avatar tidak didukung. Gunakan jpg, jpeg, png, atau gif.';
                $hasError = true;
            }
        }
        // 2. Update password jika field diisi
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        if ($current_password || $new_password || $confirm_password) {
            if ($new_password !== $confirm_password) {
                $messages[] = 'Password baru dan konfirmasi tidak cocok.';
                $hasError = true;
            } else {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE username = :username LIMIT 1");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$user || !password_verify($current_password, $user['password'])) {
                    $messages[] = 'Password saat ini salah.';
                    $hasError = true;
                } else {
                    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = :username");
                    $stmt->execute(['password' => $new_password_hash, 'username' => $username]);
                    $messages[] = 'Password berhasil diubah.';
                }
            }
        }
        // 3. Update username jika berbeda
        $new_username = trim($_POST['username'] ?? '');
        if ($new_username && $new_username !== $username) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmt->execute(['username' => $new_username]);
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                $messages[] = 'Username sudah digunakan.';
                $hasError = true;
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = :new_username WHERE username = :old_username");
                $stmt->execute(['new_username' => $new_username, 'old_username' => $username]);
                $_SESSION['user'] = $new_username;
                $messages[] = 'Informasi user berhasil diperbarui.';
            }
        }
        if ($hasError) {
            $_SESSION['error'] = implode(' ', $messages);
        } else {
            $_SESSION['success'] = implode(' ', $messages) ?: 'Perubahan profil berhasil disimpan.';
        }
        header('Location: profile.php');
        exit;
    }

    if ($action === 'reset_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = 'Password baru dan konfirmasi tidak cocok.';
            header('Location: profile.php');
            exit;
        }

        // Ambil password lama dari database
        $stmt = $pdo->prepare("SELECT password FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($current_password, $user['password'])) {
            $_SESSION['error'] = 'Password saat ini salah.';
            header('Location: profile.php');
            exit;
        }

        // Update password baru
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = :username");
        $stmt->execute(['password' => $new_password_hash, 'username' => $username]);

        $_SESSION['success'] = 'Password berhasil diubah.';
        header('Location: profile.php');
        exit;
    }

    if ($action === 'update_user') {
        $new_username = trim($_POST['username'] ?? '');

        if ($new_username === '') {
            $_SESSION['error'] = 'Username tidak boleh kosong.';
            header('Location: profile.php');
            exit;
        }

        // Cek apakah username baru sudah dipakai
        if ($new_username !== $username) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmt->execute(['username' => $new_username]);
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                $_SESSION['error'] = 'Username sudah digunakan.';
                header('Location: profile.php');
                exit;
            }
        }

        // Update username
        $stmt = $pdo->prepare("UPDATE users SET username = :new_username WHERE username = :old_username");
        $stmt->execute(['new_username' => $new_username, 'old_username' => $username]);

        // Update session username
        $_SESSION['user'] = $new_username;

        $_SESSION['success'] = 'Informasi user berhasil diperbarui.';
        header('Location: profile.php');
        exit;
    }
} else {
    header('Location: profile.php');
    exit;
}
?>
