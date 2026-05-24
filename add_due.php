<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";
requireAdmin();

$message = "";
$messageType = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $resident_id = $_POST["resident_id"] ?? "";
    $year = trim($_POST["year"] ?? "");
    $month = trim($_POST["month"] ?? "");
    $amount = trim($_POST["amount"] ?? "");

    if($resident_id == "" || $year == "" || $month == "" || $amount == ""){
        $message = "Lütfen tüm alanları doldurun.";
        $messageType = "error";
    } elseif($year < 2020 || $year > 2030){
        $message = "Yıl 2020 ile 2030 arasında olmalıdır. (CHECK Constraint)";
        $messageType = "error";
    } elseif($amount <= 0){
        $message = "Tutar 0'dan büyük olmalıdır. (CHECK Constraint)";
        $messageType = "error";
    } else {
        // Sakinin daire ID'sini bul
        $stmt = $conn->prepare("SELECT apartment_id FROM residents WHERE resident_id = ?");
        $stmt->execute([$resident_id]);
        $apartment_id = $stmt->fetchColumn();

        if(!$apartment_id){
            $message = "Seçilen sakin için daire bulunamadı.";
            $messageType = "error";
        } else {
            $sql = "INSERT INTO dues (apartment_id, year, month, amount, status)
                    VALUES (?, ?, ?, ?, 'odenmedi')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$apartment_id, $year, $month, $amount]);
            $message = "Aidat başarıyla eklendi. Sakin panelinde görünecektir.";
            $messageType = "success";
        }
    }
}

// Sakin listesi (daire bilgileriyle)
$residentsSql = "SELECT 
                    r.resident_id,
                    r.name,
                    r.surname,
                    b.block_name,
                    a.apartment_no,
                    a.apartment_id
                 FROM residents r
                 INNER JOIN apartments a ON r.apartment_id = a.apartment_id
                 INNER JOIN blocks b ON a.block_id = b.block_id
                 ORDER BY r.name ASC";
$residents = $conn->query($residentsSql)->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Yeni Aidat Ekle</h1>
                <p>Sakin seçerek aidat kaydı oluşturun. Kaydedilen aidat sakin panelinde görünecektir.</p>
            </div>
            <a class="btn" href="dues.php">Aidat Listesi</a>
        </div>

        <?php if($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="form-box">
            <form method="POST">
                <div class="form-group">
                    <label>Sakin Seç *</label>
                    <select name="resident_id" required>
                        <option value="">Sakin seçiniz</option>
                        <?php foreach($residents as $r): ?>
                            <option value="<?php echo $r['resident_id']; ?>">
                                <?php echo $r['name'] . " " . $r['surname'] . " - " . $r['block_name'] . " / Daire " . $r['apartment_no']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:18px;">
                    <div class="form-group">
                        <label>Yıl *</label>
                        <input type="number" name="year" placeholder="Örn: 2026" value="<?php echo date('Y'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Ay *</label>
                        <select name="month" required>
                            <option value="">Ay seçiniz</option>
                            <option value="Ocak">Ocak</option>
                            <option value="Şubat">Şubat</option>
                            <option value="Mart">Mart</option>
                            <option value="Nisan">Nisan</option>
                            <option value="Mayıs">Mayıs</option>
                            <option value="Haziran">Haziran</option>
                            <option value="Temmuz">Temmuz</option>
                            <option value="Ağustos">Ağustos</option>
                            <option value="Eylül">Eylül</option>
                            <option value="Ekim">Ekim</option>
                            <option value="Kasım">Kasım</option>
                            <option value="Aralık">Aralık</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tutar (₺) *</label>
                    <input type="number" step="0.01" name="amount" placeholder="Örn: 500.00" required>
                </div>

                <div style="background:#dbeafe; border:1px solid #93c5fd; border-radius:12px; padding:14px; margin-bottom:18px; color:#1e40af; font-size:14px;">
                    <i class="fa-solid fa-circle-info" style="margin-right:6px;"></i>
                    Eklediğiniz aidat otomatik olarak <strong>"Ödenmedi"</strong> durumunda kaydedilecektir. Sakin panelinde görünür ve ödeme yapabilir.
                </div>

                <button type="submit" class="submit-btn">Aidatı Kaydet</button>
            </form>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
