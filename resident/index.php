<?php
require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";
requireResident();

$resident_id = $_SESSION['resident_id'] ?? 0;

// Sakin bilgisi
$resident = $conn->prepare("SELECT r.*, b.block_name, a.apartment_no, a.floor_no 
                            FROM residents r 
                            INNER JOIN apartments a ON r.apartment_id = a.apartment_id 
                            INNER JOIN blocks b ON a.block_id = b.block_id 
                            WHERE r.resident_id = ?");
$resident->execute([$resident_id]);
$resident = $resident->fetch(PDO::FETCH_ASSOC);

// Aidat özetleri
$duesSummary = $conn->prepare("SELECT 
                                COUNT(*) as total,
                                SUM(CASE WHEN status = 'odendi' THEN 1 ELSE 0 END) as paid,
                                SUM(CASE WHEN status = 'odenmedi' THEN 1 ELSE 0 END) as unpaid,
                                SUM(CASE WHEN status = 'odenmedi' THEN amount ELSE 0 END) as debt
                              FROM dues WHERE apartment_id = ?");
$duesSummary->execute([$resident['apartment_id'] ?? 0]);
$duesSummary = $duesSummary->fetch(PDO::FETCH_ASSOC);

// Son şikayetler
$complaints = $conn->prepare("SELECT * FROM complaints WHERE resident_id = ? ORDER BY complaint_id DESC LIMIT 3");
$complaints->execute([$resident_id]);
$complaints = $complaints->fetchAll(PDO::FETCH_ASSOC);

// Son ödemeler
$payments = $conn->prepare("SELECT p.* FROM payments p 
                            INNER JOIN dues d ON p.dues_id = d.dues_id 
                            WHERE d.apartment_id = ? AND p.status = 'onaylandi'
                            ORDER BY p.payment_id DESC LIMIT 3");
$payments->execute([$resident['apartment_id'] ?? 0]);
$payments = $payments->fetchAll(PDO::FETCH_ASSOC);

require_once "../includes/header_resident.php";
?>

        <div class="hero">
            <div class="hero-top">
                <div>
                    <h1>Hoş Geldiniz, <?php echo htmlspecialchars($resident['name'] ?? ''); ?></h1>
                    <p>
                        <?php echo $resident['block_name'] ?? ''; ?> Blok, Daire <?php echo $resident['apartment_no'] ?? ''; ?> 
                        (Kat <?php echo $resident['floor_no'] ?? ''; ?>) - 
                        <?php echo $resident['type'] == 'ev_sahibi' ? 'Ev Sahibi' : 'Kiracı'; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <h3>Toplam Aidat</h3>
                <p><?php echo $duesSummary['total'] ?? 0; ?></p>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                <h3>Ödenen</h3>
                <p><?php echo $duesSummary['paid'] ?? 0; ?></p>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-circle-xmark"></i></div>
                <h3>Borçlu Aidat</h3>
                <p><?php echo $duesSummary['unpaid'] ?? 0; ?></p>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                <h3>Toplam Borç</h3>
                <p><?php echo formatMoney($duesSummary['debt'] ?? 0); ?></p>
            </div>
        </div>

        <div class="content-grid">
            <div class="panel">
                <h2>Hızlı Erişim</h2>
                <div class="panel-subtitle">Sık kullanılan işlemler</div>
                <div class="menu-grid" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
                    <a class="menu-card" href="my_dues.php">
                        <div class="menu-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                        <h3>Aidatlarım</h3>
                        <p>Borç ve ödeme durumunuzu görüntüleyin.</p>
                    </a>
                    <a class="menu-card" href="pay_dues.php">
                        <div class="menu-icon"><i class="fa-solid fa-credit-card"></i></div>
                        <h3>Aidat Öde</h3>
                        <p>Kart simülasyonu ile aidat ödemesi yapın.</p>
                    </a>
                    <a class="menu-card" href="my_complaints.php">
                        <div class="menu-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                        <h3>Şikayetlerim</h3>
                        <p>Şikayet ve taleplerinizi yönetin.</p>
                    </a>
                    <a class="menu-card" href="expenses.php">
                        <div class="menu-icon"><i class="fa-solid fa-receipt"></i></div>
                        <h3>Site Giderleri</h3>
                        <p>Siteye ait gider kayıtlarını görüntüleyin.</p>
                    </a>
                </div>
            </div>

            <div class="panel">
                <h2>Son Ödemelerim</h2>
                <div class="panel-subtitle">Onaylanan son ödemeler</div>
                <?php if(count($payments) > 0): ?>
                    <?php foreach($payments as $p): ?>
                        <div class="payment-item">
                            <div class="payment-left">
                                <strong>Aidat #<?php echo $p['dues_id']; ?></strong>
                                <span><?php echo $p['payment_date']; ?></span><br>
                                <span class="method-badge <?php echo paymentMethodClass($p['payment_method']); ?>">
                                    <?php echo paymentMethodText($p['payment_method']); ?>
                                </span>
                            </div>
                            <div class="amount"><?php echo formatMoney($p['paid_amount']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-box" style="margin-top:10px; padding:20px;">Henüz ödeme kaydı bulunmamaktadır.</div>
                <?php endif; ?>
            </div>
        </div>

        <?php if(count($complaints) > 0): ?>
        <div class="panel" style="margin-top:22px;">
            <h2>Son Şikayetlerim</h2>
            <div class="panel-subtitle">En son gönderdiğiniz şikayetler</div>
            <div class="list-box">
                <?php foreach($complaints as $c): ?>
                    <div class="complaint-card <?php echo $c['status'] == 'acik' ? 'open' : 'closed'; ?>">
                        <div class="complaint-header">
                            <div class="complaint-title"><?php echo $c['title']; ?></div>
                            <?php echo complaintStatusBadge($c['status']); ?>
                        </div>
                        <div class="meta-row">
                            <div class="meta-item">📅 <?php echo $c['complaint_date']; ?></div>
                        </div>
                        <div class="description-box"><?php echo $c['description']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

<?php require_once "../includes/footer_resident.php"; ?>
