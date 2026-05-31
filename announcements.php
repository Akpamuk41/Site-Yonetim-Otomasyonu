<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";
requireAdmin();

$message = "";
$messageType = "";

// Duyuru sil
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $conn->prepare("DELETE FROM announcements WHERE announcement_id = ?")->execute([(int)$_GET['delete']]);
    $message = "Duyuru silindi.";
    $messageType = "success";
}

// Duyuru aktif/pasif toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $conn->prepare("UPDATE announcements SET is_active = 1 - is_active WHERE announcement_id = ?")->execute([(int)$_GET['toggle']]);
    $message = "Duyuru durumu güncellendi.";
    $messageType = "success";
}

// Yeni duyuru ekle
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title    = trim($_POST["title"] ?? "");
    $content  = trim($_POST["content"] ?? "");
    $priority = $_POST["priority"] ?? "normal";

    if ($title == "" || $content == "") {
        $message = "Lütfen tüm alanları doldurun.";
        $messageType = "error";
    } elseif (strlen($title) < 3) {
        $message = "Başlık en az 3 karakter olmalıdır.";
        $messageType = "error";
    } else {
        $conn->prepare("INSERT INTO announcements (title, content, priority) VALUES (?, ?, ?)")
             ->execute([$title, $content, $priority]);
        $message = "Duyuru başarıyla eklendi.";
        $messageType = "success";
    }
}

$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Duyuru Yönetimi</h1>
                <p>Sakinlere yönelik duyuru ve bildirimler.</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Yeni Duyuru Formu -->
        <div class="form-box" style="margin-bottom:28px;">
            <h2 style="font-size:18px; margin-bottom:18px; color:#0f172a;">+ Yeni Duyuru Ekle</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Başlık *</label>
                    <input type="text" name="title" placeholder="Örn: Elektrik Kesintisi" required>
                </div>
                <div class="form-group">
                    <label>İçerik *</label>
                    <textarea name="content" placeholder="Duyuru detayını yazınız..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Öncelik</label>
                    <select name="priority">
                        <option value="normal">Normal</option>
                        <option value="onemli">Önemli</option>
                        <option value="acil">Acil</option>
                    </select>
                </div>
                <button type="submit" class="submit-btn">Duyuruyu Yayınla</button>
            </form>
        </div>

        <!-- Duyuru Listesi -->
        <div class="list-box">
            <?php if (count($announcements) > 0): ?>
                <?php foreach ($announcements as $a): ?>
                    <?php
                        $borderColor = $a['priority'] == 'acil' ? '#ef4444' : ($a['priority'] == 'onemli' ? '#f59e0b' : '#3b82f6');
                        $priorityLabel = ['normal' => 'Normal', 'onemli' => 'Önemli', 'acil' => 'Acil'][$a['priority']];
                        $priorityBg = ['normal' => '#dbeafe', 'onemli' => '#fef3c7', 'acil' => '#fee2e2'][$a['priority']];
                        $priorityColor = ['normal' => '#1d4ed8', 'onemli' => '#92400e', 'acil' => '#991b1b'][$a['priority']];
                    ?>
                    <div class="complaint-card" style="border-left-color:<?php echo $borderColor; ?>; opacity:<?php echo $a['is_active'] ? '1' : '0.5'; ?>;">
                        <div class="complaint-header">
                            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                <div class="complaint-title"><?php echo htmlspecialchars($a['title']); ?></div>
                                <span class="badge" style="background:<?php echo $priorityBg; ?>; color:<?php echo $priorityColor; ?>;">
                                    <?php echo $priorityLabel; ?>
                                </span>
                                <?php if (!$a['is_active']): ?>
                                    <span class="badge" style="background:#f1f5f9; color:#64748b;">Pasif</span>
                                <?php endif; ?>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <a href="announcements.php?toggle=<?php echo $a['announcement_id']; ?>"
                                   class="btn" style="padding:6px 12px; font-size:12px; background:<?php echo $a['is_active'] ? '#64748b' : '#16a34a'; ?>;">
                                    <?php echo $a['is_active'] ? 'Pasife Al' : 'Aktife Al'; ?>
                                </a>
                                <a href="announcements.php?delete=<?php echo $a['announcement_id']; ?>"
                                   class="btn" style="padding:6px 12px; font-size:12px; background:#ef4444;"
                                   onclick="return confirm('Bu duyuruyu silmek istediğinize emin misiniz?')">Sil</a>
                            </div>
                        </div>
                        <div class="meta-row">
                            <div class="meta-item">📅 <?php echo date('d.m.Y H:i', strtotime($a['created_at'])); ?></div>
                        </div>
                        <div class="description-box"><?php echo nl2br(htmlspecialchars($a['content'])); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-box">Henüz duyuru eklenmemiştir.</div>
            <?php endif; ?>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
