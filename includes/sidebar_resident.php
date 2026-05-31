    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <i class="fa-solid fa-building-user"></i>
                <span class="brand-text">Sakin Paneli</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle" title="Menüyü Daralt/Genişlet">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        </div>

        <div class="user-info" style="margin-bottom:16px; padding-bottom:16px; border-bottom:1px solid rgba(255,255,255,0.1);">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                <div style="width:36px; height:36px; border-radius:50%; background:rgba(34,197,94,0.35); display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:14px;">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <div style="font-weight:600; font-size:14px;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Sakin'); ?></div>
                    <div style="font-size:11px; color:#94a3b8;">Apartman Sakini</div>
                </div>
            </div>
        </div>

        <div class="nav-menu">
            <a class="nav-link <?php echo activePage('index.php'); ?>" href="index.php">
                <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span>
                <span class="nav-text">Ana Sayfa</span>
            </a>
            <a class="nav-link <?php echo activePage('my_dues.php'); ?>" href="my_dues.php">
                <span class="nav-icon"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                <span class="nav-text">Aidatlarım</span>
            </a>
            <a class="nav-link <?php echo activePage('pay_dues.php'); ?>" href="pay_dues.php">
                <span class="nav-icon"><i class="fa-solid fa-credit-card"></i></span>
                <span class="nav-text">Aidat Öde</span>
            </a>
            <a class="nav-link <?php echo activePage('my_complaints.php') . ' ' . activePage('add_complaint.php'); ?>" href="my_complaints.php">
                <span class="nav-icon"><i class="fa-solid fa-circle-exclamation"></i></span>
                <span class="nav-text">Şikayetlerim</span>
            </a>
            <a class="nav-link <?php echo activePage('expenses.php'); ?>" href="expenses.php">
                <span class="nav-icon"><i class="fa-solid fa-receipt"></i></span>
                <span class="nav-text">Site Giderleri</span>
            </a>
            <a class="nav-link <?php echo activePage('announcements.php'); ?>" href="announcements.php">
                <span class="nav-icon"><i class="fa-solid fa-bullhorn"></i></span>
                <span class="nav-text">Duyurular</span>
            </a>
        </div>

        <div style="margin-top:auto; padding-top:16px; border-top:1px solid rgba(255,255,255,0.1);">
            <a class="nav-link" href="../logout.php" style="background:rgba(239,68,68,0.15); color:#fca5a5;">
                <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                <span class="nav-text">Çıkış Yap</span>
            </a>
        </div>
    </div>
