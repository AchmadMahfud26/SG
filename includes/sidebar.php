<?php
// includes/sidebar.php
?>
<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse vh-100">
    <button type="button" class="btn btn-link text-end w-100 d-lg-none mb-2" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-label="Tutup Sidebar" style="font-size:1.5rem;">&times;</button>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" aria-current="page" href="index.php">
          <img src="assets/img/001-dashboard.png" alt="Dashboard Icon" width="20" height="20" class="me-2" />
          Dashboard
        </a>
      </li>
            <li class="nav-item">
              <a class="nav-link d-flex align-items-center <?php echo in_array($current_page, ['profile.php']) ? '' : 'collapsed'; ?>" href="#settingsSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="<?php echo in_array($current_page, ['profile.php']) ? 'true' : 'false'; ?>" aria-controls="settingsSubmenu">
                <img src="assets/img/002-cogwheel.png" alt="Settings Icon" width="20" height="20" class="me-2" />
                Pengaturan
              </a>
              <div class="collapse <?php echo in_array($current_page, ['profile.php']) ? 'show' : ''; ?>" id="settingsSubmenu">
                <ul class="nav flex-column ms-3">
                  <li class="nav-item">
                    <a class="nav-link d-flex align-items-center <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>" href="profile.php">
                      <img src="assets/img/003-profile.png" alt="Profil User Icon" width="16" height="16" class="me-2" />
                      Profil User
                    </a>
                  </li>
                </ul>
              </div>
            </li>
      <li class="nav-item mt-auto">
        <a class="nav-link text-danger" href="logout.php">
          <img src="assets/img/004-logout.png" alt="Settings Icon" width="20" height="20" class="me-2" />
          Logout
        </a>
      </li>
    </ul>
    <div class="sidebar-footer text-center mt-4 mb-2">
      <small>SMART GARDEN - SIRAMIN</small>
    </div>
  </div>
</nav>
