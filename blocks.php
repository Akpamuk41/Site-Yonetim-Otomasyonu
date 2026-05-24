<?php
require_once "includes/auth.php";
require_once "includes/db.php";
require_once "includes/functions.php";
requireAdmin();

$query = $conn->query("SELECT * FROM blocks");
$blocks = $query->fetchAll(PDO::FETCH_ASSOC);

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Blok Yönetimi</h1>
                <p>Sitede yer alan blokların listesi ve detayları.</p>
            </div>
            <a class="btn btn-success" href="add_block.php">+ Yeni Blok Ekle</a>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Toplam Blok</h3>
                <p><?php echo count($blocks); ?></p>
            </div>
        </div>

        <div class="table-box">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Blok Adı</th>
                </tr>

                <?php foreach($blocks as $b): ?>
                <tr>
                    <td><?php echo $b['block_id']; ?></td>
                    <td><?php echo $b['block_name']; ?></td>
                </tr>
                <?php endforeach; ?>

            </table>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
