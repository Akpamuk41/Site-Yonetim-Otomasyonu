    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <i class="fa-solid fa-building"></i>
                <span class="brand-text">Yönetim</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle" title="Menüyü Daralt/Genişlet">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        </div>

        <div class="user-info" style="margin-bottom:16px; padding-bottom:16px; border-bottom:1px solid rgba(255,255,255,0.1);">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:6px;">
                <div style="width:36px; height:36px; border-radius:50%; background:rgba(59,130,246,0.45); display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:14px;">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
                <div>
                    <div style="font-weight:600; font-size:14px;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></div>
                    <div style="font-size:11px; color:#94a3b8;">Yönetici</div>
                </div>
            </div>
        </div>

        <div class="nav-menu">
            <a class="nav-link <?php echo activePage('index.php'); ?>" href="index.php">
                <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a class="nav-link <?php echo activePage('blocks.php') . ' ' . activePage('add_block.php'); ?>" href="blocks.php">
                <span class="nav-icon"><i class="fa-solid fa-city"></i></span>
                <span class="nav-text">Bloklar</span>
            </a>
            <a class="nav-link <?php echo activePage('apartments.php') . ' ' . activePage('add_apartment.php'); ?>" href="apartments.php">
                <span class="nav-icon"><i class="fa-solid fa-door-open"></i></span>
                <span class="nav-text">Daireler</span>
            </a>
            <a class="nav-link <?php echo activePage('residents.php') . ' ' . activePage('add_resident.php'); ?>" href="residents.php">
                <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
                <span class="nav-text">Sakinler</span>
            </a>
            <a class="nav-link <?php echo activePage('dues.php') . ' ' . activePage('add_due.php'); ?>" href="dues.php">
                <span class="nav-icon"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                <span class="nav-text">Aidatlar</span>
            </a>
            <a class="nav-link <?php echo activePage('payments.php') . ' ' . activePage('add_payment.php') . ' ' . activePage('pending_payments.php'); ?>" href="payments.php">
                <span class="nav-icon"><i class="fa-solid fa-credit-card"></i></span>
                <span class="nav-text">Ödemeler</span>
            </a>
            <a class="nav-link <?php echo activePage('pending_payments.php'); ?>" href="pending_payments.php">
                <span class="nav-icon"><i class="fa-solid fa-clock"></i></span>
                <span class="nav-text">Bekleyen Ödemeler</span>
            </a>
            <a class="nav-link <?php echo activePage('complaints.php') . ' ' . activePage('add_complaint.php'); ?>" href="complaints.php">
                <span class="nav-icon"><i class="fa-solid fa-circle-exclamation"></i></span>
                <span class="nav-text">Şikayetler</span>
            </a>
            <a class="nav-link <?php echo activePage('expenses.php') . ' ' . activePage('add_expense.php'); ?>" href="expenses.php">
                <span class="nav-icon"><i class="fa-solid fa-receipt"></i></span>
                <span class="nav-text">Giderler</span>
            </a>
            <a class="nav-link <?php echo activePage('register.php'); ?>" href="register.php">
                <span class="nav-icon"><i class="fa-solid fa-user-plus"></i></span>
                <span class="nav-text">Sakin Kullanıcı Ekle</span>
            </a>
            <a class="nav-link <?php echo activePage('announcements.php'); ?>" href="announcements.php">
                <span class="nav-icon"><i class="fa-solid fa-bullhorn"></i></span>
                <span class="nav-text">Duyurular</span>
            </a>
        </div>

        <div style="margin-top:auto; padding-top:16px; border-top:1px solid rgba(255,255,255,0.1);">
            <a class="nav-link" href="logout.php" style="background:rgba(239,68,68,0.15); color:#fca5a5;">
                <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                <span class="nav-text">Çıkış Yap</span>
            </a>
        </div>
    </div>
