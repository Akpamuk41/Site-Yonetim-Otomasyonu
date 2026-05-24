    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <i class="fa-solid fa-building"></i>
                <span class="brand-text">Site Yönetimi</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle" title="Menüyü Daralt/Genişlet">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        </div>
        <div class="brand-subtitle">
            Blok, daire, sakin, aidat ve ödeme süreçlerini tek ekranda yönetin.
        </div>

        <div class="nav-menu">
            <a class="nav-link <?php echo activePage('index.php'); ?>" href="index.php">
                <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a class="nav-link <?php echo activePage('blocks.php'); ?>" href="blocks.php">
                <span class="nav-icon"><i class="fa-solid fa-city"></i></span>
                <span class="nav-text">Bloklar</span>
            </a>
            <a class="nav-link <?php echo activePage('apartments.php'); ?>" href="apartments.php">
                <span class="nav-icon"><i class="fa-solid fa-door-open"></i></span>
                <span class="nav-text">Daireler</span>
            </a>
            <a class="nav-link <?php echo activePage('residents.php'); ?>" href="residents.php">
                <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
                <span class="nav-text">Sakinler</span>
            </a>
            <a class="nav-link <?php echo activePage('dues.php'); ?>" href="dues.php">
                <span class="nav-icon"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                <span class="nav-text">Aidatlar</span>
            </a>
            <a class="nav-link <?php echo activePage('payments.php') . ' ' . activePage('add_payment.php'); ?>" href="payments.php">
                <span class="nav-icon"><i class="fa-solid fa-credit-card"></i></span>
                <span class="nav-text">Ödemeler</span>
            </a>
            <a class="nav-link <?php echo activePage('complaints.php') . ' ' . activePage('add_complaint.php'); ?>" href="complaints.php">
                <span class="nav-icon"><i class="fa-solid fa-circle-exclamation"></i></span>
                <span class="nav-text">Şikayetler</span>
            </a>
            <a class="nav-link <?php echo activePage('expenses.php') . ' ' . activePage('add_expense.php'); ?>" href="expenses.php">
                <span class="nav-icon"><i class="fa-solid fa-receipt"></i></span>
                <span class="nav-text">Giderler</span>
            </a>
        </div>
    </div>
