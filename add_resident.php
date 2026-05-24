<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";
requireAdmin();

$message = "";
$messageType = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $apartment_id = $_POST["apartment_id"] ?? "";
    $name = trim($_POST["name"] ?? "");
    $surname = trim($_POST["surname"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $type = $_POST["type"] ?? "";

    if($apartment_id == "" || $name == "" || $surname == "" || $type == ""){
        $message = "Lütfen zorunlu alanları doldurun.";
        $messageType = "error";
    } elseif(strlen($name) < 2 || strlen($surname) < 2){
        $message = "Ad ve soyad en az 2 karakter olmalıdır. (CHECK Constraint)";
        $messageType = "error";
    } else {
        $sql = "INSERT INTO residents (apartment_id, name, surname, phone, email, type)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$apartment_id, $name, $surname, $phone, $email, $type]);
        $message = "Sakin başarıyla eklendi.";
        $messageType = "success";
    }
}

// Daire listesi
$apartmentsSql = "SELECT a.apartment_id, a.apartment_no, a.floor_no, b.block_name 
                  FROM apartments a 
                  INNER JOIN blocks b ON a.block_id = b.block_id 
                  ORDER BY b.block_name, a.apartment_no ASC";
$apartments = $conn->query($apartmentsSql)->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Yeni Sakin Ekle</h1>
                <p>Siteye yeni sakin kaydı oluşturun.</p>
            </div>
            <a class="btn" href="residents.php">Sakin Listesi</a>
        </div>

        <?php if($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="form-box">
            <form method="POST">
                <div class="form-group">
                    <label>Daire Seç *</label>
                    <select name="apartment_id" required>
                        <option value="">Daire seçiniz</option>
                        <?php foreach($apartments as $a): ?>
                            <option value="<?php echo $a['apartment_id']; ?>">
                                <?php echo $a['block_name'] . " / Daire " . $a['apartment_no'] . " (Kat " . $a['floor_no'] . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:18px;">
                    <div class="form-group">
                        <label>Ad *</label>
                        <input type="text" name="name" placeholder="Örn: Ahmet" required>
                    </div>
                    <div class="form-group">
                        <label>Soyad *</label>
                        <input type="text" name="surname" placeholder="Örn: Yılmaz" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Telefon</label>
                    <input type="text" name="phone" placeholder="Örn: 0555 123 45 67">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Örn: ahmet@example.com">
                </div>

                <div class="form-group">
                    <label>Sakin Türü *</label>
                    <select name="type" required>
                        <option value="">Tür seçiniz</option>
                        <option value="ev_sahibi">Ev Sahibi</option>
                        <option value="kiraci">Kiracı</option>
                    </select>
                </div>

                <button type="submit" class="submit-btn">Sakini Kaydet</button>
            </form>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
