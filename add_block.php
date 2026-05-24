<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";
requireAdmin();

$message = "";
$messageType = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $block_name = trim($_POST["block_name"] ?? "");

    if($block_name == ""){
        $message = "Lütfen blok adını girin.";
        $messageType = "error";
    } else {
        $sql = "INSERT INTO blocks (block_name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$block_name]);
        $message = "Blok başarıyla eklendi.";
        $messageType = "success";
    }
}

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Yeni Blok Ekle</h1>
                <p>Siteye yeni blok kaydı oluşturun.</p>
            </div>
            <a class="btn" href="blocks.php">Blok Listesi</a>
        </div>

        <?php if($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="form-box">
            <form method="POST">
                <div class="form-group">
                    <label>Blok Adı *</label>
                    <input type="text" name="block_name" placeholder="Örn: A Blok" required>
                </div>

                <button type="submit" class="submit-btn">Bloku Kaydet</button>
            </form>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
