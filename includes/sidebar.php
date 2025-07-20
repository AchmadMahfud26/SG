<?php
// includes/sidebar.php
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse vh-100">
  <div class="position-sticky pt-3 sidebar-sticky">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link active" aria-current="page" href="index.php">
          Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#settingsSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="settingsSubmenu">
          Pengaturan
        </a>
        <div class="collapse" id="settingsSubmenu">
          <ul class="nav flex-column ms-3">
            <li class="nav-item">
              <a class="nav-link" href="#">Profil User</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">User</a>
            </li>
          </ul>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link text-danger" href="logout.php">
          Logout
        </a>
      </li>
    </ul>
  </div>
</nav>
