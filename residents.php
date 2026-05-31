<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";
requireAdmin();

$message = "";
$messageType = "";

// Sakin sil
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Sakinin daire ID'sini al
        $aptStmt = $conn->prepare("SELECT apartment_id FROM residents WHERE resident_id = ?");
        $aptStmt->execute([$id]);
        $apartment_id = $aptStmt->fetchColumn();

        // Bağlı şikayetleri sil
        $conn->prepare("DELETE FROM complaints WHERE resident_id = ?")->execute([$id]);
        // Bağlı kullanıcı hesabını sil
        $conn->prepare("DELETE FROM users WHERE resident_id = ?")->execute([$id]);
        // Sakini sil (trigger daire durumunu otomatik günceller)
        $conn->prepare("DELETE FROM residents WHERE resident_id = ?")->execute([$id]);

        $message = "Sakin başarıyla silindi.";
        $messageType = "success";
    } catch (PDOException $e) {
        $message = "Silme işlemi başarısız: " . $e->getMessage();
        $messageType = "error";
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";

$sql = "SELECT 
            r.resident_id,
            r.name,
            r.surname,
            r.phone,
            r.email,
            r.type,
            a.apartment_no,
            b.block_name
        FROM residents r
        INNER JOIN apartments a ON r.apartment_id = a.apartment_id
        INNER JOIN blocks b ON a.block_id = b.block_id
        WHERE r.name LIKE ? OR r.surname LIKE ?
        ORDER BY r.resident_id ASC";

$stmt = $conn->prepare($sql);
$like = "%$search%";
$stmt->execute([$like, $like]);
$residents = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Sakin Yönetimi</h1>
                <p>Site sakinlerinin kayıtları ve iletişim bilgileri.</p>
            </div>
            <div style="display:flex; gap:10px;">
                <a class="btn" href="residents_pdf.php" target="_blank" style="background:#dc2626;">
                    <i class="fa-solid fa-file-pdf"></i> PDF İndir
                </a>
                <a class="btn btn-success" href="add_resident.php">+ Yeni Sakin Ekle</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="filter-box">
            <form class="filter-form" method="GET" style="grid-template-columns: 1fr auto;">
                <input type="text" name="search" placeholder="İsim veya soyisim ara..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-dark">Ara</button>
            </form>
        </div>

        <div class="table-box">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Ad</th>
                    <th>Soyad</th>
                    <th>Telefon</th>
                    <th>Email</th>
                    <th>Tür</th>
                    <th>Blok</th>
                    <th>Daire</th>
                    <th>İşlem</th>
                </tr>

                <?php foreach($residents as $r): ?>
                <tr>
                    <td><?php echo $r['resident_id']; ?></td>
                    <td><?php echo htmlspecialchars($r['name']); ?></td>
                    <td><?php echo htmlspecialchars($r['surname']); ?></td>
                    <td><?php echo htmlspecialchars($r['phone']); ?></td>
                    <td><?php echo htmlspecialchars($r['email']); ?></td>
                    <td>
                        <?php if($r['type'] == 'ev_sahibi'): ?>
                            <span class="badge badge-paid">Ev Sahibi</span>
                        <?php else: ?>
                            <span class="badge badge-unpaid">Kiracı</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $r['block_name']; ?></td>
                    <td><?php echo $r['apartment_no']; ?></td>
                    <td>
                        <a href="residents.php?delete=<?php echo $r['resident_id']; ?>&search=<?php echo urlencode($search); ?>"
                           class="btn" style="background:#ef4444; padding:6px 12px; font-size:12px;"
                           onclick="return confirm('<?php echo htmlspecialchars($r['name'] . ' ' . $r['surname']); ?> adlı sakini silmek istediğinize emin misiniz? Bu işlem geri alınamaz.')">
                            🗑 Sil
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>

            </table>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
