<?php
require_once "includes/db.php";

$sql = "SELECT 
            a.apartment_id,
            b.block_name,
            a.floor_no,
            a.apartment_no,
            a.status
        FROM apartments a
        INNER JOIN blocks b ON a.block_id = b.block_id
        ORDER BY a.apartment_id ASC";

$query = $conn->query($sql);
$apartments = $query->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Daire Yönetimi</h1>
                <p>Site içerisindeki dairelerin listesi ve doluluk durumları.</p>
            </div>
            <a class="btn btn-success" href="add_apartment.php">+ Yeni Daire Ekle</a>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Toplam Daire</h3>
                <p><?php echo count($apartments); ?></p>
            </div>
        </div>

        <div class="table-box">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Blok</th>
                    <th>Kat</th>
                    <th>Daire No</th>
                    <th>Durum</th>
                </tr>

                <?php foreach($apartments as $apartment): ?>
                <tr>
                    <td><?php echo $apartment['apartment_id']; ?></td>
                    <td><?php echo $apartment['block_name']; ?></td>
                    <td><?php echo $apartment['floor_no']; ?></td>
                    <td><?php echo $apartment['apartment_no']; ?></td>
                    <td>
                        <span class="badge <?php echo $apartment['status'] == 'dolu' ? 'badge-paid' : 'badge-unpaid'; ?>">
                            <?php echo ucfirst($apartment['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>

            </table>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
