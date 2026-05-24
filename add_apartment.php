<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";
requireAdmin();

$message = "";
$messageType = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $block_id = $_POST["block_id"] ?? "";
    $apartment_no = trim($_POST["apartment_no"] ?? "");
    $floor_no = trim($_POST["floor_no"] ?? "");
    $status = $_POST["status"] ?? "bos";

    if($block_id == "" || $apartment_no == "" || $floor_no == ""){
        $message = "Lütfen zorunlu alanları doldurun.";
        $messageType = "error";
    } elseif($floor_no < 0 || $floor_no > 50){
        $message = "Kat numarası 0 ile 50 arasında olmalıdır. (CHECK Constraint)";
        $messageType = "error";
    } else {
        $sql = "INSERT INTO apartments (block_id, apartment_no, floor_no, status)
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$block_id, $apartment_no, $floor_no, $status]);
        $message = "Daire başarıyla eklendi.";
        $messageType = "success";
    }
}

// Blok listesi
$blocks = $conn->query("SELECT * FROM blocks ORDER BY block_name ASC")->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Yeni Daire Ekle</h1>
                <p>Siteye yeni daire kaydı oluşturun.</p>
            </div>
            <a class="btn" href="apartments.php">Daire Listesi</a>
        </div>

        <?php if($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="form-box">
            <form method="POST">
                <div class="form-group">
                    <label>Blok Seç *</label>
                    <select name="block_id" required>
                        <option value="">Blok seçiniz</option>
                        <?php foreach($blocks as $b): ?>
                            <option value="<?php echo $b['block_id']; ?>"><?php echo $b['block_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:18px;">
                    <div class="form-group">
                        <label>Daire No *</label>
                        <input type="text" name="apartment_no" placeholder="Örn: 5" required>
                    </div>
                    <div class="form-group">
                        <label>Kat No *</label>
                        <input type="number" name="floor_no" placeholder="Örn: 2" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Durum</label>
                    <select name="status">
                        <option value="bos">Boş</option>
                        <option value="dolu">Dolu</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Daireyi Kaydet</button>
            </form>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
