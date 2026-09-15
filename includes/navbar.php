<header class="topbar">
  <button class="icon-btn d-lg-none" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
  <div class="search-wrap flex-grow-1"><i class="bi bi-search"></i><input id="globalSearch" placeholder="Search files and folders"></div>
  <button class="icon-btn" onclick="toggleTheme()" title="Theme"><i id="themeIcon" class="bi bi-moon-stars"></i></button>
  <button class="icon-btn position-relative" onclick="loadNotifications()" title="Notifications"><i class="bi bi-bell"></i><span id="notifDot" class="notif-dot d-none"></span></button>
  <a class="profile-chip" href="settings.php"><span class="avatar"><?= e(strtoupper(substr($me['full_name'] ?? 'U',0,1))) ?></span><span class="d-none d-md-inline"><?= e($me['full_name'] ?? 'User') ?></span></a>
</header>
