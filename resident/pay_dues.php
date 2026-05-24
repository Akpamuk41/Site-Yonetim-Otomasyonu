<?php
require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";
requireResident();

$resident_id = $_SESSION['resident_id'] ?? 0;
$message = "";
$messageType = "";

// Sakinin daire ID'sini bul
$resident = $conn->prepare("SELECT apartment_id FROM residents WHERE resident_id = ?");
$resident->execute([$resident_id]);
$apartment_id = $resident->fetchColumn();

// Ödenmemiş aidatları çek
$duesSql = "SELECT d.dues_id, d.amount, d.year, d.month 
            FROM dues d 
            WHERE d.apartment_id = ? AND d.status = 'odenmedi' 
            ORDER BY d.year DESC, d.month DESC";
$duesList = $conn->prepare($duesSql);
$duesList->execute([$apartment_id]);
$duesList = $duesList->fetchAll(PDO::FETCH_ASSOC);

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $dues_id = $_POST["dues_id"] ?? "";
    $paid_amount = trim($_POST["paid_amount"] ?? "");
    $card_holder = trim($_POST["card_holder"] ?? "");
    $card_number = preg_replace('/\s+/', '', $_POST["card_number"] ?? "");
    $card_mask = substr($card_number, -4);

    if($dues_id == "" || $paid_amount == "" || $card_holder == "" || strlen($card_number) < 13){
        $message = "Lütfen tüm kart bilgilerini eksiksiz doldurun.";
        $messageType = "error";
    } elseif($paid_amount <= 0){
        $message = "Ödenecek tutar 0'dan büyük olmalıdır.";
        $messageType = "error";
    } else {
        // Seçilen aidatın gerçek tutarını DB'den doğrula
        $checkDue = $conn->prepare("SELECT amount FROM dues WHERE dues_id = ? AND apartment_id = ? AND status = 'odenmedi'");
        $checkDue->execute([$dues_id, $apartment_id]);
        $dueRow = $checkDue->fetch(PDO::FETCH_ASSOC);

        if (!$dueRow) {
            $message = "Geçersiz aidat seçimi.";
            $messageType = "error";
        } elseif (abs((float)$paid_amount - (float)$dueRow['amount']) > 0.001) {
            $message = "Ödeme tutarı aidat tutarıyla tam olarak eşleşmelidir. Ödenecek tutar: " . number_format($dueRow['amount'], 2, ',', '.') . " ₺";
            $messageType = "error";
        } else {
            try {
                $sql = "INSERT INTO payments (dues_id, payment_date, paid_amount, payment_method, status, is_simulation, card_holder, card_mask)
                        VALUES (?, CURDATE(), ?, 'kart', 'beklemede', 1, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$dues_id, $dueRow['amount'], $card_holder, $card_mask]);

                $insertedId = $conn->lastInsertId();
                $message = "Ödeme simülasyonu başarıyla oluşturuldu. Yönetici onayından sonra işleme alınacaktır. (Payment ID: " . $insertedId . ")";
                $messageType = "success";

                // Listeyi güncelle
                $duesList = $conn->prepare($duesSql);
                $duesList->execute([$apartment_id]);
                $duesList = $duesList->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $message = "Ödeme kaydedilirken hata oluştu: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
}

require_once "../includes/header_resident.php";
?>

        <div class="top-bar">
            <div>
                <h1>Aidat Öde</h1>
                <p>Kart simülasyonu ile aidat ödemesi yapın.</p>
            </div>
            <a class="btn" href="my_dues.php">Aidatlarım</a>
        </div>

        <?php if($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if(count($duesList) == 0): ?>
            <div class="empty-box" style="margin-bottom:24px;">
                <i class="fa-solid fa-circle-check" style="font-size:32px; color:#16a34a; margin-bottom:10px;"></i>
                <h3 style="margin-bottom:6px;">Tebrikler!</h3>
                <p>Borçlu aidatınız bulunmamaktadır.</p>
            </div>
        <?php else: ?>
            <div class="form-box">
                <form method="POST" id="payForm">
                    <div class="form-group">
                        <label>Ödenecek Aidat</label>
                        <select name="dues_id" id="duesSelect" required onchange="setAmount(this)">
                            <option value="">Aidat seçiniz</option>
                            <?php foreach($duesList as $due): ?>
                                <option value="<?php echo $due['dues_id']; ?>" data-amount="<?php echo $due['amount']; ?>">
                                    <?php echo $due['year'] . " / " . $due['month'] . " - " . formatMoney($due['amount']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ödenecek Tutar</label>
                        <input type="number" step="0.01" name="paid_amount" id="paidAmount"
                               placeholder="Aidat seçince otomatik dolar"
                               readonly required
                               style="background:#f1f5f9; cursor:not-allowed; color:#374151;">
                        <small style="color:#64748b; font-size:12px; margin-top:4px; display:block;">
                            Tutar aidat miktarına göre otomatik belirlenir, değiştirilemez.
                        </small>
                    </div>

                    <div style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:16px; padding:20px; margin-bottom:18px;">
                        <h3 style="font-size:16px; margin-bottom:16px; color:#0f172a;"><i class="fa-solid fa-credit-card" style="margin-right:8px;"></i>Kart Bilgileri (Simülasyon)</h3>

                        <div class="form-group">
                            <label>Kart Üzerindeki İsim</label>
                            <input type="text" name="card_holder" placeholder="AHMET YILMAZ" required>
                        </div>

                        <div class="form-group">
                            <label>Kart Numarası</label>
                            <input type="text" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                            <div class="form-group">
                                <label>Son Kullanma Tarihi</label>
                                <input type="text" placeholder="MM/YY" maxlength="5" required>
                            </div>
                            <div class="form-group">
                                <label>CVV</label>
                                <input type="text" placeholder="123" maxlength="3" required>
                            </div>
                        </div>
                    </div>

                    <div style="background:#fef3c7; border:1px solid #fde68a; border-radius:12px; padding:14px; margin-bottom:18px; color:#92400e; font-size:14px;">
                        <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px;"></i>
                        Bu bir <strong>simülasyon ödemesidir</strong>. Gerçek bir tahsilat yapılmaz. Ödeme yöneticinin onayından geçtikten sonra işleme alınır.
                    </div>

                    <button type="submit" class="submit-btn" style="background:#16a34a;">
                        <i class="fa-solid fa-paper-plane" style="margin-right:8px;"></i>Ödemeyi Gönder
                    </button>
                </form>
            </div>
        <?php endif; ?>

<script>
function setAmount(select) {
    var opt = select.options[select.selectedIndex];
    var amount = opt.getAttribute('data-amount') || '';
    document.getElementById('paidAmount').value = amount ? parseFloat(amount).toFixed(2) : '';
}
</script>

<?php require_once "../includes/footer_resident.php"; ?>
