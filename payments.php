<?php
require_once "includes/db.php";
require_once "includes/auth.php";
require_once "includes/functions.php";
requireAdmin();

// v_payment_details VIEW kullanimi (VTYS projesi gereksinimi)
$sql = "SELECT 
            payment_id,
            block_name,
            apartment_no,
            payment_date,
            paid_amount,
            payment_method,
            dues_id
        FROM v_payment_details
        ORDER BY payment_id DESC";

$query = $conn->query($sql);
$payments = $query->fetchAll(PDO::FETCH_ASSOC);

$totalPayments = count($payments);
$totalAmount = 0;
$cashCount = 0;
$cardCount = 0;
$transferCount = 0;

foreach($payments as $p){
    $totalAmount += $p['paid_amount'];
    if($p['payment_method'] == 'nakit'){
        $cashCount++;
    } elseif($p['payment_method'] == 'kart'){
        $cardCount++;
    } elseif($p['payment_method'] == 'havale'){
        $transferCount++;
    }
}

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Ödeme Yönetimi</h1>
                <p>Ödeme hareketleri ve tahsilat kayıtları.</p>
            </div>
            <a class="btn btn-success" href="add_payment.php">+ Yeni Ödeme</a>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Toplam Ödeme Sayısı</h3>
                <p><?php echo $totalPayments; ?></p>
            </div>

            <div class="card">
                <h3>Toplam Tahsilat</h3>
                <p><?php echo formatMoney($totalAmount); ?></p>
            </div>

            <div class="card">
                <h3>Kart ile Ödeme</h3>
                <p><?php echo $cardCount; ?></p>
            </div>

            <div class="card">
                <h3>Nakit / Havale</h3>
                <p><?php echo $cashCount + $transferCount; ?></p>
            </div>
        </div>

        <div class="table-box">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Blok</th>
                    <th>Daire</th>
                    <th>Aidat ID</th>
                    <th>Ödeme Tarihi</th>
                    <th>Tutar</th>
                    <th>Yöntem</th>
                </tr>

                <?php foreach($payments as $p): ?>
                <tr>
                    <td><?php echo $p['payment_id']; ?></td>
                    <td><?php echo $p['block_name']; ?></td>
                    <td><?php echo $p['apartment_no']; ?></td>
                    <td>#<?php echo $p['dues_id']; ?></td>
                    <td><?php echo $p['payment_date']; ?></td>
                    <td class="amount"><?php echo formatMoney($p['paid_amount']); ?></td>
                    <td>
                        <span class="badge <?php echo paymentMethodClass($p['payment_method']); ?>">
                            <?php echo paymentMethodText($p['payment_method']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
