<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Smart Garden Dashboard</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Optional Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="assets/img/agriculture.png" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <div>
      <button class="btn btn-outline-light me-2" id="sidebarToggle" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle sidebar">☰</button>
      <a class="navbar-brand" href="index.php">Smart Garden</a>
    </div>
    <div class="d-flex align-items-center dropdown">
      <a href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="text-white text-decoration-none d-flex align-items-center">
        <span class="me-2 d-none d-md-block">
          <?php
            echo isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']) : 'Profil User';
          ?>
        </span>
        <img src="avatar.php" alt="Avatar" class="rounded-circle border border-light" width="40" height="40" />
      </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
        <li><a class="dropdown-item d-flex align-items-center" href="profile.php">
          <img src="assets/img/002-cogwheel.png" alt="Settings Icon" width="20" height="20" class="me-2" />
          Pengaturan
        </a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item d-flex align-items-center" href="logout.php">
          <img src="assets/img/004-logout.png" alt="Logout Icon" width="20" height="20" class="me-2" />
          Logout
        </a></li>
      </ul>
    </div>
  </div>
</nav>
<?php include 'includes/sidebar.php'; ?>
<div class="main-content">

<script src="assets/js/sidebar-toggle.js"></script>
<script src="assets/js/status-koneksi.js"></script>
