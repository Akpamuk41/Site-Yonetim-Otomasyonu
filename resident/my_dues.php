<?php
require_once "../includes/db.php";
require_once "../includes/functions.php";
require_once "../includes/auth.php";
requireResident();

$resident_id = $_SESSION['resident_id'] ?? 0;

// Sakinin daire ID'sini bul
$resident = $conn->prepare("SELECT apartment_id FROM residents WHERE resident_id = ?");
$resident->execute([$resident_id]);
$apartment_id = $resident->fetchColumn();

$sql = "SELECT 
            d.dues_id,
            d.year,
            d.month,
            d.amount,
            d.status,
            b.block_name,
            a.apartment_no
        FROM dues d
        INNER JOIN apartments a ON d.apartment_id = a.apartment_id
        INNER JOIN blocks b ON a.block_id = b.block_id
        WHERE d.apartment_id = ?
        ORDER BY d.year DESC, d.month DESC";

$stmt = $conn->prepare($sql);
$stmt->execute([$apartment_id]);
$dues = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($dues);
$paid = 0;
$unpaid = 0;
$debt = 0;

foreach($dues as $d){
    if($d['status'] == 'odendi'){
        $paid++;
    } else {
        $unpaid++;
        $debt += $d['amount'];
    }
}

require_once "../includes/header_resident.php";
?>

        <div class="top-bar">
            <div>
                <h1>Aidatlarım</h1>
                <p>Dairenize ait aidat kayıtları ve ödeme durumları.</p>
            </div>
            <a class="btn btn-success" href="pay_dues.php">💳 Aidat Öde</a>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Toplam Aidat</h3>
                <p><?php echo $total; ?></p>
            </div>
            <div class="card">
                <h3>Ödenen</h3>
                <p><?php echo $paid; ?></p>
            </div>
            <div class="card">
                <h3>Borçlu</h3>
                <p><?php echo $unpaid; ?></p>
            </div>
            <div class="card">
                <h3>Toplam Borç</h3>
                <p><?php echo formatMoney($debt); ?></p>
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

<?php require_once "../includes/footer_resident.php"; ?>
