<?php
require_once "includes/db.php";
require_once "includes/auth.php";
require_once "includes/functions.php";
requireAdmin();

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $dues_id = $_POST["dues_id"];
    $paid_amount = trim($_POST["paid_amount"]);
    $payment_method = $_POST["payment_method"];

    if($dues_id != "" && $paid_amount != "" && $payment_method != ""){
        $sql = "INSERT INTO payments (dues_id, payment_date, paid_amount, payment_method)
                VALUES (?, CURDATE(), ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$dues_id, $paid_amount, $payment_method]);

        $message = "Ödeme başarıyla eklendi. Trigger otomatik olarak çalıştı.";
    } else {
        $message = "Lütfen tüm alanları doldurun.";
    }
}

$duesSql = "SELECT 
                d.dues_id,
                d.amount,
                b.block_name,
                a.apartment_no
            FROM dues d
            INNER JOIN apartments a ON d.apartment_id = a.apartment_id
            INNER JOIN blocks b ON a.block_id = b.block_id
            WHERE d.status = 'odenmedi'
            ORDER BY d.dues_id ASC";

$duesList = $conn->query($duesSql)->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Yeni Ödeme Ekle</h1>
                <p>Ödenmemiş aidat kayıtları için yeni ödeme işlemi oluşturun.</p>
            </div>
            <a class="btn" href="payments.php">Ödeme Listesi</a>
        </div>

        <div class="form-box">

            <?php if($message != ""): ?>
                <div class="message <?php echo strpos($message, 'başarıyla') !== false ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Aidat Seç</label>
                    <select name="dues_id" required>
                        <option value="">Aidat seçiniz</option>
                        <?php foreach($duesList as $due): ?>
                            <option value="<?php echo $due['dues_id']; ?>">
                                <?php echo $due['block_name'] . " / Daire " . $due['apartment_no'] . " - " . formatMoney($due['amount']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ödenen Tutar</label>
                    <input type="number" step="0.01" name="paid_amount" placeholder="Örn: 500.00" required>
                </div>

                <div class="form-group">
                    <label>Ödeme Yöntemi</label>
                    <select name="payment_method" required>
                        <option value="">Yöntem seçiniz</option>
                        <option value="nakit">Nakit</option>
                        <option value="kart">Kart</option>
                        <option value="havale">Havale</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Ödemeyi Kaydet</button>
            </form>

        </div>

<?php require_once "includes/footer_admin.php"; ?>
