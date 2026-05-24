<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";
requireAdmin();

$message = "";
$messageType = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $resident_id = $_POST["resident_id"] ?? "";
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if($resident_id == "" || $username == "" || $password == ""){
        $message = "Lütfen tüm alanları doldurun.";
        $messageType = "error";
    } elseif(strlen($username) < 3){
        $message = "Kullanıcı adı en az 3 karakter olmalıdır. (CHECK Constraint)";
        $messageType = "error";
    } elseif(strlen($password) < 4){
        $message = "Şifre en az 4 karakter olmalıdır. (CHECK Constraint)";
        $messageType = "error";
    } else {
        // Kullanıcı adı benzersiz mi?
        $check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $check->execute([$username]);
        if($check->fetch()){
            $message = "Bu kullanıcı adı zaten kullanılıyor.";
            $messageType = "error";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, resident_id) VALUES (?, ?, 'resident', ?)");
            $stmt->execute([$username, $hash, $resident_id]);
            $message = "Sakin kullanıcısı başarıyla oluşturuldu.";
            $messageType = "success";
        }
    }
}

// Sakin listesi (henüz kullanıcısı olmayanlar veya tümü)
$residentsSql = "SELECT 
                    r.resident_id,
                    r.name,
                    r.surname,
                    b.block_name,
                    a.apartment_no,
                    u.username as existing_user
                 FROM residents r
                 INNER JOIN apartments a ON r.apartment_id = a.apartment_id
                 INNER JOIN blocks b ON a.block_id = b.block_id
                 LEFT JOIN users u ON r.resident_id = u.resident_id
                 ORDER BY r.name ASC";
$residents = $conn->query($residentsSql)->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Sakin Kullanıcı Ekle</h1>
                <p>Apartman sakinleri için giriş hesabı oluşturun.</p>
            </div>
        </div>

        <?php if($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="form-box">
            <form method="POST">
                <div class="form-group">
                    <label>Sakin Seç</label>
                    <select name="resident_id" required>
                        <option value="">Sakin seçiniz</option>
                        <?php foreach($residents as $r): ?>
                            <option value="<?php echo $r['resident_id']; ?>" <?php echo $r['existing_user'] ? 'disabled' : ''; ?>>
                                <?php echo $r['name'] . " " . $r['surname'] . " - " . $r['block_name'] . " / Daire " . $r['apartment_no']; ?>
                                <?php echo $r['existing_user'] ? ' (Zaten kullanıcısı var)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Kullanıcı Adı</label>
                    <input type="text" name="username" placeholder="Örn: ahmet.yilmaz" required>
                </div>

                <div class="form-group">
                    <label>Şifre</label>
                    <input type="text" name="password" placeholder="En az 4 karakter" required>
                </div>

                <button type="submit" class="submit-btn">Kullanıcı Oluştur</button>
            </form>
        </div>

        <div style="margin-top:24px;">
            <h2 style="margin-bottom:12px; font-size:18px;">Mevcut Kullanıcılar</h2>
            <div class="table-box">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Kullanıcı Adı</th>
                        <th>Sakin</th>
                        <th>Blok / Daire</th>
                        <th>Rol</th>
                    </tr>
                    <?php
                    $usersSql = "SELECT u.user_id, u.username, u.role, r.name, r.surname, b.block_name, a.apartment_no
                                 FROM users u
                                 LEFT JOIN residents r ON u.resident_id = r.resident_id
                                 LEFT JOIN apartments a ON r.apartment_id = a.apartment_id
                                 LEFT JOIN blocks b ON a.block_id = b.block_id
                                 ORDER BY u.role DESC, u.user_id ASC";
                    $users = $conn->query($usersSql)->fetchAll(PDO::FETCH_ASSOC);
                    foreach($users as $u):
                    ?>
                    <tr>
                        <td><?php echo $u['user_id']; ?></td>
                        <td><?php echo $u['username']; ?></td>
                        <td><?php echo $u['name'] ? $u['name'] . ' ' . $u['surname'] : '-'; ?></td>
                        <td><?php echo $u['block_name'] ? $u['block_name'] . ' / Daire ' . $u['apartment_no'] : '-'; ?></td>
                        <td>
                            <?php if($u['role'] == 'admin'): ?>
                                <span class="badge badge-paid">Yönetici</span>
                            <?php else: ?>
                                <span class="badge badge-unpaid">Sakin</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
