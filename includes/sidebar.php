<?php
// includes/sidebar.php
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse vh-100">
  <div class="position-sticky pt-3 sidebar-sticky">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link active d-flex align-items-center" aria-current="page" href="index.php">
          <img src="assets/img/001-dashboard.png" alt="Dashboard Icon" width="20" height="20" class="me-2" />
          Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center" href="#settingsSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="settingsSubmenu">
          <img src="assets/img/002-cogwheel.png" alt="Settings Icon" width="20" height="20" class="me-2" />
          Pengaturan
        </a>
        <div class="collapse" id="settingsSubmenu">
          <ul class="nav flex-column ms-3">
            <li class="nav-item">
              <a class="nav-link d-flex align-items-center" href="#">
                <img src="assets/img/003-profile.png" alt="Profil User Icon" width="16" height="16" class="me-2" />
                Profil User
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link d-flex align-items-center" href="#">
                <img src="assets/img/003-profile.png" alt="Profil User Icon" width="16" height="16" class="me-2" />
                User
              </a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link text-danger" href="logout.php">
          <img src="assets/img/004-logout.png" alt="Settings Icon" width="20" height="20" class="me-2" />
          Logout
        </a>
      </li>
    </ul>
  </div>
</nav>
