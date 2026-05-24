<?php
require_once "includes/db.php";
require_once "includes/functions.php";
require_once "includes/auth.php";
requireAdmin();

$message = "";
$messageType = "";

// sp_update_complaint_status Stored Procedure kullanimi (VTYS projesi gereksinimi)
if(isset($_GET['action']) && isset($_GET['id'])){
    $complaint_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if($action === 'resolve'){
        try {
            $stmt = $conn->prepare("CALL sp_update_complaint_status(?, 'cozuldu')");
            $stmt->execute([$complaint_id]);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $conn->prepare("UPDATE complaints SET status = 'cozuldu' WHERE complaint_id = ?")->execute([$complaint_id]);
        }
        $message = "Şikayet #" . $complaint_id . " çözüldü olarak işaretlendi.";
        $messageType = "success";
    } elseif($action === 'reopen'){
        try {
            $stmt = $conn->prepare("CALL sp_update_complaint_status(?, 'acik')");
            $stmt->execute([$complaint_id]);
            $stmt->closeCursor();
        } catch (PDOException $e) {
            $conn->prepare("UPDATE complaints SET status = 'acik' WHERE complaint_id = ?")->execute([$complaint_id]);
        }
        $message = "Şikayet #" . $complaint_id . " yeniden açıldı.";
        $messageType = "success";
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : "";

// v_complaint_details VIEW kullanimi (VTYS projesi gereksinimi)
$sql = "SELECT 
            complaint_id,
            title,
            description,
            complaint_status AS status,
            complaint_date,
            resident_name AS name,
            resident_surname AS surname,
            block_name,
            apartment_no
        FROM v_complaint_details
        WHERE 1=1";

$params = [];

if($search !== ""){
    $sql .= " AND (title LIKE ? OR description LIKE ? OR resident_name LIKE ? OR resident_surname LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if($statusFilter !== "" && in_array($statusFilter, ['acik', 'cozuldu'])){
    $sql .= " AND complaint_status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY complaint_id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalComplaints = count($complaints);
$openCount = 0;
$closedCount = 0;

foreach($complaints as $c){
    if($c['status'] == 'acik'){
        $openCount++;
    } else {
        $closedCount++;
    }
}

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Şikayet ve Talep Yönetimi</h1>
                <p>Sakinlerden gelen şikayet ve taleplerin durum takibi.</p>
            </div>
            <a class="btn btn-success" href="add_complaint.php">+ Yeni Şikayet</a>
        </div>

        <?php if($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="cards">
            <div class="card">
                <h3>Toplam Kayıt</h3>
                <p><?php echo $totalComplaints; ?></p>
            </div>

            <div class="card">
                <h3>Açık Şikayetler</h3>
                <p><?php echo $openCount; ?></p>
            </div>

            <div class="card">
                <h3>Çözülen Şikayetler</h3>
                <p><?php echo $closedCount; ?></p>
            </div>
        </div>

        <div class="filter-box">
            <form class="filter-form" method="GET">
                <input type="text" name="search" placeholder="Başlık, açıklama veya sakin adına göre ara" value="<?php echo htmlspecialchars($search); ?>">

                <select name="status">
                    <option value="">Tüm Durumlar</option>
                    <option value="acik" <?php echo $statusFilter == 'acik' ? 'selected' : ''; ?>>Açık</option>
                    <option value="cozuldu" <?php echo $statusFilter == 'cozuldu' ? 'selected' : ''; ?>>Çözüldü</option>
                </select>

                <button type="submit" class="btn btn-dark">Filtrele</button>
            </form>
        </div>

        <div class="list-box">
            <?php if(count($complaints) > 0): ?>
                <?php foreach($complaints as $c): ?>
                    <div class="complaint-card <?php echo $c['status'] == 'acik' ? 'open' : 'closed'; ?>">
                        <div class="complaint-header">
                            <div class="complaint-title"><?php echo $c['title']; ?></div>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <?php echo complaintStatusBadge($c['status']); ?>
                                <?php if($c['status'] == 'acik'): ?>
                                    <a href="complaints.php?action=resolve&id=<?php echo $c['complaint_id']; ?>" class="btn btn-success" style="padding:6px 12px; font-size:12px;" onclick="return confirm('Bu şikayeti çözüldü olarak işaretlemek istediğinize emin misiniz?')">✅ Çözüldü</a>
                                <?php else: ?>
                                    <a href="complaints.php?action=reopen&id=<?php echo $c['complaint_id']; ?>" class="btn" style="padding:6px 12px; font-size:12px; background:#f59e0b;" onclick="return confirm('Bu şikayeti yeniden açmak istediğinize emin misiniz?')">🔄 Yeniden Aç</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="meta-row">
                            <div class="meta-item">
                                👤 <?php echo $c['name'] . " " . $c['surname']; ?>
                            </div>
                            <div class="meta-item">
                                🏢 <?php echo $c['block_name']; ?> / Daire <?php echo $c['apartment_no']; ?>
                            </div>
                            <div class="meta-item">
                                📅 <?php echo $c['complaint_date']; ?>
                            </div>
                        </div>

                        <div class="description-box">
                            <?php echo $c['description']; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-box">
                    Aradığınız kriterlere uygun şikayet kaydı bulunamadı.
                </div>
            <?php endif; ?>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
