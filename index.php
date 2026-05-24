<?php
require_once "includes/auth.php";
require_once "includes/db.php";
require_once "includes/functions.php";
requireAdmin();

$blockCount = $conn->query("SELECT COUNT(*) FROM blocks")->fetchColumn();
$apartmentCount = $conn->query("SELECT COUNT(*) FROM apartments")->fetchColumn();
$residentCount = $conn->query("SELECT COUNT(*) FROM residents")->fetchColumn();
$dueCount = $conn->query("SELECT COUNT(*) FROM dues")->fetchColumn();
$paidCount = $conn->query("SELECT COUNT(*) FROM dues WHERE status = 'odendi'")->fetchColumn();
$unpaidCount = $conn->query("SELECT COUNT(*) FROM dues WHERE status = 'odenmedi'")->fetchColumn();
$paymentCount = $conn->query("SELECT COUNT(*) FROM payments")->fetchColumn();

// sp_get_monthly_income Stored Procedure kullanimi (VTYS projesi gereksinimi)
try {
    $stmt = $conn->prepare("CALL sp_get_monthly_income(2026, 'Mart')");
    $stmt->execute();
    $monthlyIncome = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
} catch (PDOException $e) {
    // Procedure yoksa direkt sorgu ile hesapla
    $stmt = $conn->prepare("SELECT SUM(p.paid_amount) AS total_income FROM payments p INNER JOIN dues d ON p.dues_id = d.dues_id WHERE d.year = 2026 AND d.month = 'Mart' AND p.status = 'onaylandi'");
    $stmt->execute();
    $monthlyIncome = $stmt->fetch(PDO::FETCH_ASSOC);
}

// v_payment_details VIEW kullanimi (VTYS projesi gereksinimi)
$recentPaymentsSql = "SELECT 
                        payment_date,
                        paid_amount,
                        payment_method,
                        block_name,
                        apartment_no
                      FROM v_payment_details
                      WHERE payment_status = 'onaylandi'
                      ORDER BY payment_id DESC
                      LIMIT 5";
$recentPayments = $conn->query($recentPaymentsSql)->fetchAll(PDO::FETCH_ASSOC);

date_default_timezone_set('Europe/Istanbul');
$currentDate = date('d.m.Y');
$currentTime = date('H:i');

$pageTitle = 'Dashboard';
require_once "includes/header_admin.php";
?>

        <div class="hero">
            <div class="hero-top">
                <div>
                    <h1>Site Yönetimi ve Aidat Takip Otomasyon Sistemi</h1>
                    <p>
                        Bu panel; site içerisindeki blok, daire, sakin, aidat ve ödeme süreçlerini
                        yönetmek amacıyla geliştirilmiştir. Veritabanı tarafında ilişkisel yapı,
                        trigger, view, stored procedure ve index kullanılarak güçlü bir sistem
                        oluşturulmuştur.
                    </p>
                    <div class="hero-badge">Veritabanı Yönetim Sistemleri Dönem Projesi</div>
                </div>

                <div class="date-box">
                    Tarih
                    <strong><?php echo $currentDate; ?></strong>
                    <div style="margin-top:6px;">Saat: <?php echo $currentTime; ?></div>
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-city"></i></div>
                <h3>Toplam Blok</h3>
                <p><?php echo $blockCount; ?></p>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-door-open"></i></div>
                <h3>Toplam Daire</h3>
                <p><?php echo $apartmentCount; ?></p>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                <h3>Toplam Sakin</h3>
                <p><?php echo $residentCount; ?></p>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <h3>Toplam Aidat</h3>
                <p><?php echo $dueCount; ?></p>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                <h3>Ödenen Aidatlar</h3>
                <p><?php echo $paidCount; ?></p>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-circle-xmark"></i></div>
                <h3>Ödenmeyen Aidatlar</h3>
                <p><?php echo $unpaidCount; ?></p>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-credit-card"></i></div>
                <h3>Toplam Ödeme</h3>
                <p><?php echo $paymentCount; ?></p>
            </div>
        </div>

        <div class="content-grid">
            <div class="panel">
                <h2>Son Ödemeler</h2>
                <div class="panel-subtitle">Sisteme son eklenen ödeme kayıtları</div>

                <?php foreach($recentPayments as $payment): ?>
                    <div class="payment-item">
                        <div class="payment-left">
                            <strong><?php echo $payment['block_name']; ?> / Daire <?php echo $payment['apartment_no']; ?></strong>
                            <span><?php echo $payment['payment_date']; ?></span><br>
                            <span class="method-badge <?php echo paymentMethodClass($payment['payment_method']); ?>">
                                <?php echo paymentMethodText($payment['payment_method']); ?>
                            </span>
                        </div>
                        <div class="amount"><?php echo formatMoney($payment['paid_amount']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="panel">
                <h2>Sistem Özeti</h2>
                <div class="panel-subtitle">Genel durum bilgileri</div>

                <div class="summary-list">
                    <div class="summary-box">
                        <h3>Borçlu Daire Sayısı</h3>
                        <p><?php echo $unpaidCount; ?></p>
                    </div>

                    <div class="summary-box">
                        <h3>Tahsil Edilen Ödeme Sayısı</h3>
                        <p><?php echo $paymentCount; ?></p>
                    </div>

                    <div class="summary-box">
                        <h3>Toplam Sakin Sayısı</h3>
                        <p><?php echo $residentCount; ?></p>
                    </div>

                    <div class="summary-box">
                        <h3>Mart 2026 Geliri (SP)</h3>
                        <p><?php echo isset($monthlyIncome['total_income']) && $monthlyIncome['total_income'] ? formatMoney($monthlyIncome['total_income']) : '0,00 ₺'; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="menu-title">Hızlı Erişim</div>

        <div class="menu-grid">
            <a class="menu-card" href="blocks.php">
                <div class="menu-icon"><i class="fa-solid fa-city"></i></div>
                <h3>Bloklar</h3>
                <p>Sitede yer alan blok bilgilerini görüntüleyin.</p>
            </a>

            <a class="menu-card" href="apartments.php">
                <div class="menu-icon"><i class="fa-solid fa-door-open"></i></div>
                <h3>Daireler</h3>
                <p>Daire listesi, kat bilgileri ve doluluk durumlarını inceleyin.</p>
            </a>

            <a class="menu-card" href="residents.php">
                <div class="menu-icon"><i class="fa-solid fa-users"></i></div>
                <h3>Sakinler</h3>
                <p>Ev sahibi ve kiracı kayıtlarını görüntüleyin.</p>
            </a>

            <a class="menu-card" href="dues.php">
                <div class="menu-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <h3>Aidatlar</h3>
                <p>Aidatların ödeme durumlarını ve borç kayıtlarını takip edin.</p>
            </a>

            <a class="menu-card" href="payments.php">
                <div class="menu-icon"><i class="fa-solid fa-credit-card"></i></div>
                <h3>Ödemeler</h3>
                <p>Ödeme hareketlerini ve tahsilat kayıtlarını inceleyin.</p>
            </a>

            <a class="menu-card" href="complaints.php">
                <div class="menu-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                <h3>Şikayetler</h3>
                <p>Sakinlerden gelen şikayet ve talepleri görüntüleyin.</p>
            </a>

            <a class="menu-card" href="expenses.php">
                <div class="menu-icon"><i class="fa-solid fa-receipt"></i></div>
                <h3>Giderler</h3>
                <p>Siteye ait gider kayıtlarını ve masraf türlerini inceleyin.</p>
            </a>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
