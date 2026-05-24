<?php
require_once "includes/db.php";

$sql = "SELECT 
            d.dues_id,
            b.block_name,
            a.apartment_no,
            d.year,
            d.month,
            d.amount,
            d.status
        FROM dues d
        INNER JOIN apartments a ON d.apartment_id = a.apartment_id
        INNER JOIN blocks b ON a.block_id = b.block_id
        ORDER BY d.dues_id DESC";

$query = $conn->query($sql);
$dues = $query->fetchAll(PDO::FETCH_ASSOC);

$totalDues = count($dues);
$paidCount = 0;
$unpaidCount = 0;
$totalAmount = 0;

foreach($dues as $d){
    $totalAmount += $d['amount'];
    if($d['status'] == 'odendi'){
        $paidCount++;
    } else {
        $unpaidCount++;
    }
}

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Aidat Yönetimi</h1>
                <p>Aidat kayıtlarının ödeme durumları ve borç takibi.</p>
            </div>
            <a class="btn btn-success" href="add_due.php">+ Yeni Aidat Ekle</a>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Toplam Aidat Kaydı</h3>
                <p><?php echo $totalDues; ?></p>
            </div>

            <div class="card">
                <h3>Ödenen Aidatlar</h3>
                <p><?php echo $paidCount; ?></p>
            </div>

            <div class="card">
                <h3>Ödenmeyen Aidatlar</h3>
                <p><?php echo $unpaidCount; ?></p>
            </div>

            <div class="card">
                <h3>Toplam Tutar</h3>
                <p><?php echo formatMoney($totalAmount); ?></p>
            </div>
        </div>

        <div class="table-box">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Blok</th>
                    <th>Daire</th>
                    <th>Yıl</th>
                    <th>Ay</th>
                    <th>Tutar</th>
                    <th>Durum</th>
                </tr>

                <?php foreach($dues as $d): ?>
                <tr>
                    <td><?php echo $d['dues_id']; ?></td>
                    <td><?php echo $d['block_name']; ?></td>
                    <td><?php echo $d['apartment_no']; ?></td>
                    <td><?php echo $d['year']; ?></td>
                    <td><?php echo $d['month']; ?></td>
                    <td class="amount"><?php echo formatMoney($d['amount']); ?></td>
                    <td><?php echo duesStatusBadge($d['status']); ?></td>
                </tr>
                <?php endforeach; ?>

            </table>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
