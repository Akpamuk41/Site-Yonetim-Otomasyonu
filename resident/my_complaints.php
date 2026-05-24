<?php
require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";
requireResident();

$resident_id = $_SESSION['resident_id'] ?? 0;

$complaints = $conn->prepare("SELECT * FROM complaints WHERE resident_id = ? ORDER BY complaint_id DESC");
$complaints->execute([$resident_id]);
$complaints = $complaints->fetchAll(PDO::FETCH_ASSOC);

$total = count($complaints);
$open = 0;
$closed = 0;

foreach($complaints as $c){
    if($c['status'] == 'acik') $open++;
    else $closed++;
}

require_once "../includes/header_resident.php";
?>

        <div class="top-bar">
            <div>
                <h1>Şikayetlerim</h1>
                <p>Gönderdiğiniz şikayet ve taleplerin durum takibi.</p>
            </div>
            <a class="btn btn-success" href="add_complaint.php">+ Yeni Şikayet</a>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Toplam Şikayet</h3>
                <p><?php echo $total; ?></p>
            </div>
            <div class="card">
                <h3>Açık</h3>
                <p><?php echo $open; ?></p>
            </div>
            <div class="card">
                <h3>Çözüldü</h3>
                <p><?php echo $closed; ?></p>
            </div>
        </div>

        <div class="list-box">
            <?php if(count($complaints) > 0): ?>
                <?php foreach($complaints as $c): ?>
                    <div class="complaint-card <?php echo $c['status'] == 'acik' ? 'open' : 'closed'; ?>">
                        <div class="complaint-header">
                            <div class="complaint-title"><?php echo $c['title']; ?></div>
                            <?php echo complaintStatusBadge($c['status']); ?>
                        </div>
                        <div class="meta-row">
                            <div class="meta-item">📅 <?php echo $c['complaint_date']; ?></div>
                        </div>
                        <div class="description-box"><?php echo $c['description']; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-box">Henüz şikayet kaydınız bulunmamaktadır.</div>
            <?php endif; ?>
        </div>

<?php require_once "../includes/footer_resident.php"; ?>
