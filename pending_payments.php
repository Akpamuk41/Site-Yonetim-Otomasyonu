<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";
requireAdmin();

$message = "";
$messageType = "";

// Onay / Reddet işlemi
if(isset($_GET['action']) && isset($_GET['id'])){
    $payment_id = (int)$_GET['id'];
    $action = $_GET['action'];

    if($action === 'approve'){
        $stmt = $conn->prepare("SELECT * FROM payments WHERE payment_id = ? AND status = 'beklemede'");
        $stmt->execute([$payment_id]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if($payment){
            $conn->prepare("UPDATE payments SET status = 'onaylandi' WHERE payment_id = ?")
                 ->execute([$payment_id]);
            $conn->prepare("UPDATE dues SET status = 'odendi' WHERE dues_id = ?")
                 ->execute([$payment['dues_id']]);
            $message = "Ödeme #" . $payment_id . " onaylandı.";
            $messageType = "success";
        }
    } elseif($action === 'reject'){
        $conn->prepare("UPDATE payments SET status = 'reddedildi' WHERE payment_id = ?")
             ->execute([$payment_id]);
        $message = "Ödeme #" . $payment_id . " reddedildi.";
        $messageType = "error";
    }
}

// ====== FİLTRE PARAMETRELERİ ======
$filterStatus = isset($_GET['filter_status']) ? trim($_GET['filter_status']) : 'all';
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';

// ====== TÜM AİDATLAR (Ödenmiş + Ödenmemiş Birleşik Liste) ======
// v_apartment_summary VIEW ile birleştirilmiş aidat listesi
$sql = "SELECT 
            d.dues_id,
            d.year,
            d.month,
            d.amount,
            d.status,
            b.block_name,
            a.apartment_no,
            a.floor_no,
            r.name,
            r.surname,
            r.phone,
            p.payment_id,
            p.payment_date,
            p.paid_amount,
            p.payment_method,
            p.status AS payment_status,
            p.is_simulation,
            p.card_holder,
            p.card_mask
        FROM dues d
        INNER JOIN apartments a ON d.apartment_id = a.apartment_id
        INNER JOIN blocks b ON a.block_id = b.block_id
        LEFT JOIN residents r ON a.apartment_id = r.apartment_id
        LEFT JOIN payments p ON d.dues_id = p.dues_id
        WHERE 1=1";

$params = [];

// Durum filtresi
if($filterStatus === 'paid'){
    $sql .= " AND d.status = 'odendi'";
} elseif($filterStatus === 'unpaid'){
    $sql .= " AND d.status = 'odenmedi'";
}

// Tarih aralığı filtresi
if($dateFrom !== ''){
    $sql .= " AND p.payment_date >= ?";
    $params[] = $dateFrom;
}
if($dateTo !== ''){
    $sql .= " AND p.payment_date <= ?";
    $params[] = $dateTo;
}

$sql .= " ORDER BY d.year DESC, d.month DESC, d.dues_id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$allDues = $stmt->fetchAll(PDO::FETCH_ASSOC);

// İstatistikler
$totalCount = count($allDues);
$paidCount = 0;
$unpaidCount = 0;
$totalPaidAmount = 0;
$totalUnpaidAmount = 0;

foreach($allDues as $d){
    if($d['status'] === 'odendi'){
        $paidCount++;
        $totalPaidAmount += $d['paid_amount'] ?? $d['amount'];
    } else {
        $unpaidCount++;
        $totalUnpaidAmount += $d['amount'];
    }
}

// ====== BEKLEYEN ÖDEME SİMÜLASYONLARI ======
$pendingSql = "SELECT 
                p.payment_id,
                p.payment_date,
                p.paid_amount,
                p.payment_method,
                p.card_holder,
                p.card_mask,
                p.is_simulation,
                d.dues_id,
                d.amount as due_amount,
                d.year,
                d.month,
                b.block_name,
                a.apartment_no,
                r.name,
                r.surname
              FROM payments p
              INNER JOIN dues d ON p.dues_id = d.dues_id
              INNER JOIN apartments a ON d.apartment_id = a.apartment_id
              INNER JOIN blocks b ON a.block_id = b.block_id
              LEFT JOIN residents r ON a.apartment_id = r.apartment_id
              WHERE p.status = 'beklemede'
              ORDER BY p.payment_id DESC";
$pending = $conn->query($pendingSql)->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Aidat ve Ödeme Yönetimi</h1>
                <p>Tüm aidat kayıtları, ödeme durumları ve bekleyen simülasyonlar.</p>
            </div>
        </div>

        <?php if($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- === ÖZET KARTLARI === -->
        <div class="cards" style="margin-bottom:24px;">
            <div class="card">
                <h3>Toplam Kayıt</h3>
                <p><?php echo $totalCount; ?></p>
            </div>
            <div class="card">
                <h3>Ödenen</h3>
                <p style="color:#16a34a;"><?php echo $paidCount; ?></p>
            </div>
            <div class="card">
                <h3>Ödenmemiş</h3>
                <p style="color:#dc2626;"><?php echo $unpaidCount; ?></p>
            </div>
            <div class="card">
                <h3>Tahsil Edilen</h3>
                <p><?php echo formatMoney($totalPaidAmount); ?></p>
            </div>
            <div class="card">
                <h3>Bekleyen Borç</h3>
                <p><?php echo formatMoney($totalUnpaidAmount); ?></p>
            </div>
        </div>

        <!-- === FİLTRELEME === -->
        <div class="filter-box" style="margin-bottom:24px;">
            <form class="filter-form" method="GET" style="grid-template-columns: 1fr 1fr 1fr auto; gap:12px;">
                <select name="filter_status">
                    <option value="all" <?php echo $filterStatus == 'all' ? 'selected' : ''; ?>>Tüm Durumlar</option>
                    <option value="paid" <?php echo $filterStatus == 'paid' ? 'selected' : ''; ?>>✅ Ödenenler</option>
                    <option value="unpaid" <?php echo $filterStatus == 'unpaid' ? 'selected' : ''; ?>>❌ Ödenmemişler</option>
                </select>
                <input type="date" name="date_from" placeholder="Başlangıç Tarihi" value="<?php echo htmlspecialchars($dateFrom); ?>">
                <input type="date" name="date_to" placeholder="Bitiş Tarihi" value="<?php echo htmlspecialchars($dateTo); ?>">
                <div style="display:flex; gap:8px;">
                    <button type="submit" class="btn btn-dark">Filtrele</button>
                    <a href="pending_payments.php" class="btn" style="background:#64748b;">Sıfırla</a>
                </div>
            </form>
        </div>

        <!-- === TÜM AİDATLAR (Birleşik Liste) === -->
        <div style="margin-bottom:32px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px;">
                <h2 style="font-size:20px; color:#0f172a;">Aidat Kayıtları</h2>
                <span class="badge badge-unpaid" style="font-size:12px;"><?php echo $totalCount; ?> Kayıt</span>
            </div>

            <?php if(count($allDues) > 0): ?>
                <div class="table-box">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Sakin</th>
                            <th>Blok / Daire</th>
                            <th>Dönem</th>
                            <th>Aidat Tutarı</th>
                            <th>Ödenen</th>
                            <th>Yöntem</th>
                            <th>Durum</th>
                        </tr>
                        <?php foreach($allDues as $d): ?>
                        <tr>
                            <td>#<?php echo $d['dues_id']; ?></td>
                            <td>
                                <?php echo $d['name'] ? htmlspecialchars($d['name'] . ' ' . $d['surname']) : '<span style="color:#94a3b8;">Atanmamış</span>'; ?>
                            </td>
                            <td><?php echo $d['block_name']; ?> / Daire <?php echo $d['apartment_no']; ?></td>
                            <td><?php echo $d['year'] . ' / ' . $d['month']; ?></td>
                            <td class="amount-expense"><?php echo formatMoney($d['amount']); ?></td>
                            <td class="amount"><?php echo $d['paid_amount'] ? formatMoney($d['paid_amount']) : '-'; ?></td>
                            <td>
                                <?php if($d['payment_method']): ?>
                                    <span class="badge <?php echo paymentMethodClass($d['payment_method']); ?>">
                                        <?php echo paymentMethodText($d['payment_method']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:#94a3b8;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($d['status'] == 'odendi'): ?>
                                    <span class="badge badge-paid">Ödendi</span>
                                    <?php if($d['is_simulation']): ?>
                                        <span class="badge" style="background:#fef3c7; color:#92400e; font-size:10px; margin-left:4px;">Simülasyon</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-unpaid">Ödenmedi</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-box">Seçilen filtrelere uygun kayıt bulunamadı.</div>
            <?php endif; ?>
        </div>

        <!-- === BEKLEYEN ÖDEME SİMÜLASYONLARI === -->
        <div style="margin-bottom:32px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px;">
                <h2 style="font-size:20px; color:#0f172a;">Onay Bekleyen Ödeme Simülasyonları</h2>
                <span class="badge" style="background:#fef3c7; color:#92400e; font-size:12px;"><?php echo count($pending); ?> Kayıt</span>
            </div>

            <?php if(count($pending) > 0): ?>
                <div class="list-box">
                    <?php foreach($pending as $p): ?>
                        <div class="complaint-card open" style="border-left-color:#f59e0b;">
                            <div class="complaint-header">
                                <div class="complaint-title">
                                    <?php echo $p['block_name']; ?> / Daire <?php echo $p['apartment_no']; ?>
                                    <span class="badge" style="background:#fef3c7; color:#92400e; margin-left:8px;">Simülasyon</span>
                                </div>
                                <div style="display:flex; gap:8px;">
                                    <a href="pending_payments.php?action=approve&id=<?php echo $p['payment_id']; ?>" class="btn btn-success" onclick="return confirm('Onaylamak istediğinize emin misiniz?')">✅ Onayla</a>
                                    <a href="pending_payments.php?action=reject&id=<?php echo $p['payment_id']; ?>" class="btn" style="background:#ef4444;" onclick="return confirm('Reddetmek istediğinize emin misiniz?')">❌ Reddet</a>
                                </div>
                            </div>

                            <div class="meta-row">
                                <div class="meta-item">
                                    <i class="fa-solid fa-user"></i> 
                                    <?php echo $p['name'] ? $p['name'] . ' ' . $p['surname'] : 'Bilinmiyor'; ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fa-solid fa-file-invoice"></i> Aidat #<?php echo $p['dues_id']; ?> (<?php echo $p['year'] . '/' . $p['month']; ?>)
                                </div>
                                <div class="meta-item">
                                    <i class="fa-solid fa-money-bill"></i> <?php echo formatMoney($p['paid_amount']); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fa-solid fa-calendar"></i> <?php echo $p['payment_date']; ?>
                                </div>
                                <?php if($p['card_holder']): ?>
                                <div class="meta-item">
                                    <i class="fa-solid fa-credit-card"></i> <?php echo $p['card_holder']; ?> (**** <?php echo $p['card_mask']; ?>)
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-box">Onay bekleyen ödeme simülasyonu bulunmamaktadır.</div>
            <?php endif; ?>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
