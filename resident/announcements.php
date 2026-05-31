<?php
require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";
requireResident();

$announcements = $conn->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

require_once "../includes/header_resident.php";
?>

        <div class="top-bar">
            <div>
                <h1>Duyurular</h1>
                <p>Site yönetiminden gelen duyuru ve bildirimler.</p>
            </div>
        </div>

        <div class="list-box">
            <?php if (count($announcements) > 0): ?>
                <?php foreach ($announcements as $a): ?>
                    <?php
                        $borderColor = $a['priority'] == 'acil' ? '#ef4444' : ($a['priority'] == 'onemli' ? '#f59e0b' : '#3b82f6');
                        $priorityLabel = ['normal' => 'Normal', 'onemli' => 'Önemli', 'acil' => 'Acil'][$a['priority']];
                        $priorityBg = ['normal' => '#dbeafe', 'onemli' => '#fef3c7', 'acil' => '#fee2e2'][$a['priority']];
                        $priorityColor = ['normal' => '#1d4ed8', 'onemli' => '#92400e', 'acil' => '#991b1b'][$a['priority']];
                    ?>
                    <div class="complaint-card" style="border-left-color:<?php echo $borderColor; ?>;">
                        <div class="complaint-header">
                            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                <div class="complaint-title"><?php echo htmlspecialchars($a['title']); ?></div>
                                <span class="badge" style="background:<?php echo $priorityBg; ?>; color:<?php echo $priorityColor; ?>;">
                                    <?php echo $priorityLabel; ?>
                                </span>
                            </div>
                        </div>
                        <div class="meta-row">
                            <div class="meta-item">📅 <?php echo date('d.m.Y H:i', strtotime($a['created_at'])); ?></div>
                        </div>
                        <div class="description-box"><?php echo nl2br(htmlspecialchars($a['content'])); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-box">Şu an aktif duyuru bulunmamaktadır.</div>
            <?php endif; ?>
        </div>

<?php require_once "../includes/footer_resident.php"; ?>
