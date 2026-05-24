<?php
require_once "includes/db.php";

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
            <a class="btn btn-success" href="add_resident.php">+ Yeni Sakin Ekle</a>
        </div>

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
                </tr>

                <?php foreach($residents as $r): ?>
                <tr>
                    <td><?php echo $r['resident_id']; ?></td>
                    <td><?php echo $r['name']; ?></td>
                    <td><?php echo $r['surname']; ?></td>
                    <td><?php echo $r['phone']; ?></td>
                    <td><?php echo $r['email']; ?></td>
                    <td>
                        <?php if($r['type'] == 'ev_sahibi'): ?>
                            <span class="badge badge-paid">Ev Sahibi</span>
                        <?php else: ?>
                            <span class="badge badge-unpaid">Kiracı</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $r['block_name']; ?></td>
                    <td><?php echo $r['apartment_no']; ?></td>
                </tr>
                <?php endforeach; ?>

            </table>
        </div>

<?php require_once "includes/footer_admin.php"; ?>
