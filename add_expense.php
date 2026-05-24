<?php
require_once "includes/db.php";

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $expense_type = trim($_POST["expense_type"]);
    $amount = trim($_POST["amount"]);
    $expense_date = $_POST["expense_date"];
    $description = trim($_POST["description"]);

    if($expense_type != "" && $amount != "" && $expense_date != ""){
        if($amount <= 0){
            $message = "Tutar 0'dan büyük olmalıdır. (CHECK Constraint)";
        } else {
            // sp_add_expense Stored Procedure kullanimi (VTYS projesi gereksinimi)
            try {
                $stmt = $conn->prepare("CALL sp_add_expense(?, ?, ?, ?)");
                $stmt->execute([$expense_type, $amount, $expense_date, $description]);
                $stmt->closeCursor();
            } catch (PDOException $e) {
                $stmt = $conn->prepare("INSERT INTO expenses (expense_type, amount, expense_date, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$expense_type, $amount, $expense_date, $description]);
            }
            $message = "Gider kaydı başarıyla eklendi.";
        }
    } else {
        $message = "Lütfen zorunlu alanları doldurun.";
    }
}

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Yeni Gider Ekle</h1>
                <p>Siteye ait yeni gider kayıtlarını sisteme ekleyin.</p>
            </div>
            <a class="btn" href="expenses.php">Gider Listesi</a>
        </div>

        <div class="form-box">

            <?php if($message != ""): ?>
                <div class="message <?php echo strpos($message, 'başarıyla') !== false ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Gider Türü</label>
                    <select name="expense_type" required>
                        <option value="">Gider türü seçiniz</option>
                        <option value="Elektrik">Elektrik</option>
                        <option value="Temizlik">Temizlik</option>
                        <option value="Bakim-Onarim">Bakım-Onarım</option>
                        <option value="Guvenlik">Güvenlik</option>
                        <option value="Su Gideri">Su Gideri</option>
                        <option value="Diger">Diğer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tutar</label>
                    <input type="number" step="0.01" name="amount" placeholder="Örn: 1500.00" required>
                </div>

                <div class="form-group">
                    <label>Tarih</label>
                    <input type="date" name="expense_date" required>
                </div>

                <div class="form-group">
                    <label>Açıklama</label>
                    <textarea name="description" placeholder="Gider ile ilgili kısa açıklama yazınız..."></textarea>
                </div>

                <button type="submit" class="submit-btn">Gideri Kaydet</button>
            </form>

        </div>

<?php require_once "includes/footer_admin.php"; ?>
