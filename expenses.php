<?php
require_once "includes/db.php";
require_once "includes/auth.php";
require_once "includes/functions.php";
requireAdmin();

$search = isset($_GET['search']) ? trim($_GET['search']) : "";

$sql = "SELECT 
            expense_id,
            expense_type,
            amount,
            expense_date,
            description
        FROM expenses
        WHERE 1=1";

$params = [];

if($search !== ""){
    $sql .= " AND (expense_type LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY expense_date DESC, expense_id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalExpenseCount = count($expenses);
$totalExpenseAmount = 0;

$typeTotals = [];

foreach($expenses as $expense){
    $totalExpenseAmount += $expense['amount'];

    if(!isset($typeTotals[$expense['expense_type']])){
        $typeTotals[$expense['expense_type']] = 0;
    }

    $typeTotals[$expense['expense_type']] += $expense['amount'];
}

arsort($typeTotals);
$topTypeName = count($typeTotals) > 0 ? array_key_first($typeTotals) : "-";
$topTypeAmount = count($typeTotals) > 0 ? $typeTotals[$topTypeName] : 0;

require_once "includes/header_admin.php";
?>

        <div class="top-bar">
            <div>
                <h1>Gider Yönetimi</h1>
                <p>Siteye ait gider kayıtları ve masraf takibi.</p>
            </div>
            <a class="btn btn-success" href="add_expense.php">+ Yeni Gider</a>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Toplam Gider Kaydı</h3>
                <p><?php echo $totalExpenseCount; ?></p>
            </div>

            <div class="card">
                <h3>Toplam Gider Tutarı</h3>
                <p><?php echo formatMoney($totalExpenseAmount); ?></p>
            </div>

            <div class="card">
                <h3>En Yüksek Gider Türü</h3>
                <p style="font-size:22px;"><?php echo $topTypeName; ?></p>
            </div>
        </div>

        <div class="filter-box">
            <form class="filter-form" method="GET" style="grid-template-columns: 1fr auto;">
                <input type="text" name="search" placeholder="Gider türü veya açıklamaya göre ara" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-dark">Ara</button>
            </form>
        </div>

        <?php if(count($expenses) > 0): ?>
            <div class="table-box">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Gider Türü</th>
                        <th>Tutar</th>
                        <th>Tarih</th>
                        <th>Açıklama</th>
                    </tr>

                    <?php foreach($expenses as $expense): ?>
                    <tr>
                        <td><?php echo $expense['expense_id']; ?></td>
                        <td>
                            <span class="type-badge"><?php echo $expense['expense_type']; ?></span>
                        </td>
                        <td class="amount-expense">
                            <?php echo formatMoney($expense['amount']); ?>
                        </td>
                        <td><?php echo $expense['expense_date']; ?></td>
                        <td class="desc"><?php echo $expense['description']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-box">
                Aradığınız kriterlere uygun gider kaydı bulunamadı.
            </div>
        <?php endif; ?>

<?php require_once "includes/footer_admin.php"; ?>
