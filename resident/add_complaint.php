<?php
require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";
requireResident();

$resident_id = $_SESSION['resident_id'] ?? 0;
$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if($title != "" && $description != ""){
        if(strlen($title) < 3){
            $message = "Şikayet başlığı en az 3 karakter olmalıdır. (CHECK Constraint)";
        } else {
            $sql = "INSERT INTO complaints (resident_id, title, description, complaint_date, status)
                    VALUES (?, ?, ?, CURDATE(), 'acik')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$resident_id, $title, $description]);
            $message = "Şikayetiniz başarıyla gönderildi.";
        }
    } else {
        $message = "Lütfen tüm alanları doldurun.";
    }
}

require_once "../includes/header_resident.php";
?>

        <div class="top-bar">
            <div>
                <h1>Yeni Şikayet</h1>
                <p>Site yönetimine şikayet veya talebinizi iletin.</p>
            </div>
            <a class="btn" href="my_complaints.php">Şikayetlerim</a>
        </div>

        <?php if($message != ""): ?>
            <div class="message <?php echo strpos($message, 'başarıyla') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-box">
            <form method="POST">
                <div class="form-group">
                    <label>Şikayet Başlığı</label>
                    <input type="text" name="title" placeholder="Örn: Asansör arızası" required>
                </div>

                <div class="form-group">
                    <label>Açıklama</label>
                    <textarea name="description" placeholder="Şikayet veya talep detayını yazınız..." required></textarea>
                </div>

                <button type="submit" class="submit-btn">Şikayeti Gönder</button>
            </form>
        </div>

<?php require_once "../includes/footer_resident.php"; ?>
