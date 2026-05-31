<?php
require_once "includes/db.php";
require_once "includes/auth.php";
require_once "includes/functions.php";
requireAdmin();

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $resident_id = $_POST["resident_id"];
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);

    if($resident_id != "" && $title != "" && $description != ""){
        if(strlen($title) < 3){
            $message = "Şikayet başlığı en az 3 karakter olmalıdır. (CHECK Constraint)";
        } else {
            $sql = "INSERT INTO complaints (resident_id, title, description, complaint_date, status)
                    VALUES (?, ?, ?, CURDATE(), 'acik')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$resident_id, $title, $description]);
            $message = "Şikayet kaydı başarıyla eklendi.";
        }
    } else {
        $message = "Lütfen tüm alanları doldurun.";
    }
}

$residentsSql = "SELECT 
                    r.resident_id,
                    r.name,
                    r.surname,
                    b.block_name,
                    a.apartment_no
                 FROM residents r
                 INNER JOIN apartments a ON r.apartment_id = a.apartment_id
                 INNER JOIN blocks b ON a.block_id = b.block_id
                 ORDER BY r.name ASC";

$residents = $conn->query($residentsSql)->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Yeni Şikayet Ekle</h1>
                <p>Sakinlerden gelen yeni şikayet ve talepleri sisteme kaydedin.</p>
            </div>
            <a class="btn" href="complaints.php">Şikayet Listesi</a>
        </div>

        <div class="form-box">

            <?php if($message != ""): ?>
                <div class="message <?php echo strpos($message, 'başarıyla') !== false ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Sakin Seç</label>
                    <select name="resident_id" required>
                        <option value="">Sakin seçiniz</option>
                        <?php foreach($residents as $resident): ?>
                            <option value="<?php echo $resident['resident_id']; ?>">
                                <?php echo $resident['name'] . " " . $resident['surname'] . " - " . $resident['block_name'] . " / Daire " . $resident['apartment_no']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Şikayet Başlığı</label>
                    <input type="text" name="title" placeholder="Örn: Asansör arızası" required>
                </div>

                <div class="form-group">
                    <label>Açıklama</label>
                    <textarea name="description" placeholder="Şikayet veya talep detayını yazınız..." required></textarea>
                </div>

                <button type="submit" class="submit-btn">Şikayeti Kaydet</button>
            </form>

        </div>

<?php require_once "includes/footer_admin.php"; ?>
