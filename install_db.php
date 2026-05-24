<?php
/**
 * Site Yönetim Sistemi - Veritabanı Tam Kurulum
 * Eksik tabloları, view'ları, trigger'ları ve stored procedure'leri oluşturur.
 * URL: http://localhost/site_yonetim/install_db.php
 * Çalıştırdıktan sonra bu dosyayı silin!
 */

require_once "includes/db.php";

$results = [];
$hasError = false;

function runSQL($conn, $sql, $label, &$results, &$hasError) {
    try {
        $conn->exec($sql);
        $results[] = ['ok', $label];
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        // "already exists" hatalarını uyarı say, hata değil
        if (strpos($msg, 'already exists') !== false || strpos($msg, '1050') !== false || strpos($msg, '1304') !== false) {
            $results[] = ['warn', $label . ' (zaten mevcut)'];
        } else {
            $results[] = ['err', $label . ' → ' . $msg];
            $hasError = true;
        }
    }
}

// ── TABLOLAR ──────────────────────────────────────────────────────────────────

runSQL($conn, "CREATE TABLE IF NOT EXISTS blocks (
    block_id INT AUTO_INCREMENT PRIMARY KEY,
    block_name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "blocks tablosu", $results, $hasError);

runSQL($conn, "CREATE TABLE IF NOT EXISTS apartments (
    apartment_id INT AUTO_INCREMENT PRIMARY KEY,
    block_id INT NOT NULL,
    floor_no INT NOT NULL,
    apartment_no VARCHAR(10) NOT NULL,
    status ENUM('bos','dolu') NOT NULL DEFAULT 'bos',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (block_id) REFERENCES blocks(block_id) ON DELETE CASCADE,
    UNIQUE KEY uk_block_apartment (block_id, apartment_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "apartments tablosu", $results, $hasError);

runSQL($conn, "CREATE TABLE IF NOT EXISTS residents (
    resident_id INT AUTO_INCREMENT PRIMARY KEY,
    apartment_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    surname VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100),
    type ENUM('ev_sahibi','kiraci') NOT NULL DEFAULT 'ev_sahibi',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (apartment_id) REFERENCES apartments(apartment_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "residents tablosu", $results, $hasError);

runSQL($conn, "CREATE TABLE IF NOT EXISTS dues (
    dues_id INT AUTO_INCREMENT PRIMARY KEY,
    apartment_id INT NOT NULL,
    year INT NOT NULL,
    month VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('odendi','odenmedi') NOT NULL DEFAULT 'odenmedi',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (apartment_id) REFERENCES apartments(apartment_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "dues tablosu", $results, $hasError);

runSQL($conn, "CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    dues_id INT NOT NULL,
    payment_date DATE NOT NULL,
    paid_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('nakit','kart','havale') NOT NULL DEFAULT 'nakit',
    status ENUM('onaylandi','beklemede','reddedildi') NOT NULL DEFAULT 'onaylandi',
    is_simulation TINYINT(1) NOT NULL DEFAULT 0,
    card_holder VARCHAR(100) NULL,
    card_mask VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dues_id) REFERENCES dues(dues_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "payments tablosu", $results, $hasError);

runSQL($conn, "CREATE TABLE IF NOT EXISTS complaints (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('acik','cozuldu') NOT NULL DEFAULT 'acik',
    complaint_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "complaints tablosu", $results, $hasError);

runSQL($conn, "CREATE TABLE IF NOT EXISTS expenses (
    expense_id INT AUTO_INCREMENT PRIMARY KEY,
    expense_type VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "expenses tablosu", $results, $hasError);

runSQL($conn, "CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','resident') NOT NULL DEFAULT 'resident',
    resident_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resident_id) REFERENCES residents(resident_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", "users tablosu", $results, $hasError);

// ── PAYMENTS TABLOSU SÜTUN EKLEMELERİ (eski kurulumlar için) ─────────────────

$alterCols = [
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS status ENUM('onaylandi','beklemede','reddedildi') NOT NULL DEFAULT 'onaylandi'",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS is_simulation TINYINT(1) NOT NULL DEFAULT 0",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS card_holder VARCHAR(100) NULL",
    "ALTER TABLE payments ADD COLUMN IF NOT EXISTS card_mask VARCHAR(20) NULL",
];
foreach ($alterCols as $sql) {
    try { $conn->exec($sql); } catch (PDOException $e) { /* zaten varsa geç */ }
}
$results[] = ['ok', 'payments sütunları kontrol edildi'];

// ── VIEW'LAR ──────────────────────────────────────────────────────────────────

runSQL($conn, "CREATE OR REPLACE VIEW v_apartment_summary AS
SELECT 
    a.apartment_id, b.block_name, a.apartment_no, a.floor_no, a.status,
    r.name AS resident_name, r.surname AS resident_surname, r.type AS resident_type,
    COUNT(DISTINCT d.dues_id) AS total_dues,
    SUM(CASE WHEN d.status = 'odendi' THEN 1 ELSE 0 END) AS paid_dues,
    SUM(CASE WHEN d.status = 'odenmedi' THEN 1 ELSE 0 END) AS unpaid_dues,
    SUM(CASE WHEN d.status = 'odenmedi' THEN d.amount ELSE 0 END) AS total_debt
FROM apartments a
INNER JOIN blocks b ON a.block_id = b.block_id
LEFT JOIN residents r ON a.apartment_id = r.apartment_id
LEFT JOIN dues d ON a.apartment_id = d.apartment_id
GROUP BY a.apartment_id, b.block_name, a.apartment_no, a.floor_no, a.status, r.name, r.surname, r.type",
"v_apartment_summary view", $results, $hasError);

runSQL($conn, "CREATE OR REPLACE VIEW v_payment_details AS
SELECT 
    p.payment_id, p.payment_date, p.paid_amount, p.payment_method,
    p.status AS payment_status, p.is_simulation, p.card_holder, p.card_mask,
    d.dues_id, d.year, d.month, d.amount AS due_amount,
    b.block_name, a.apartment_no, a.floor_no,
    r.name AS resident_name, r.surname AS resident_surname
FROM payments p
INNER JOIN dues d ON p.dues_id = d.dues_id
INNER JOIN apartments a ON d.apartment_id = a.apartment_id
INNER JOIN blocks b ON a.block_id = b.block_id
LEFT JOIN residents r ON a.apartment_id = r.apartment_id",
"v_payment_details view", $results, $hasError);

runSQL($conn, "CREATE OR REPLACE VIEW v_complaint_details AS
SELECT 
    c.complaint_id, c.title, c.description, c.status AS complaint_status, c.complaint_date,
    r.name AS resident_name, r.surname AS resident_surname, r.phone AS resident_phone, r.email AS resident_email,
    b.block_name, a.apartment_no, a.floor_no
FROM complaints c
INNER JOIN residents r ON c.resident_id = r.resident_id
INNER JOIN apartments a ON r.apartment_id = a.apartment_id
INNER JOIN blocks b ON a.block_id = b.block_id",
"v_complaint_details view", $results, $hasError);

runSQL($conn, "CREATE OR REPLACE VIEW v_financial_summary AS
SELECT 'Gelir' AS type, DATE_FORMAT(p.payment_date, '%Y-%m') AS period,
    SUM(p.paid_amount) AS total_amount, COUNT(*) AS record_count
FROM payments p WHERE p.status = 'onaylandi'
GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')
UNION ALL
SELECT 'Gider' AS type, DATE_FORMAT(e.expense_date, '%Y-%m') AS period,
    SUM(e.amount) AS total_amount, COUNT(*) AS record_count
FROM expenses e
GROUP BY DATE_FORMAT(e.expense_date, '%Y-%m')
ORDER BY period DESC, type",
"v_financial_summary view", $results, $hasError);

// ── TRIGGER'LAR ───────────────────────────────────────────────────────────────

$conn->exec("DROP TRIGGER IF EXISTS trg_after_payment_insert");
runSQL($conn, "CREATE TRIGGER trg_after_payment_insert
AFTER INSERT ON payments FOR EACH ROW
BEGIN
    IF NEW.status = 'onaylandi' THEN
        UPDATE dues SET status = 'odendi' WHERE dues_id = NEW.dues_id;
    END IF;
END", "trg_after_payment_insert trigger", $results, $hasError);

$conn->exec("DROP TRIGGER IF EXISTS trg_after_payment_update");
runSQL($conn, "CREATE TRIGGER trg_after_payment_update
AFTER UPDATE ON payments FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        IF NEW.status = 'onaylandi' THEN
            UPDATE dues SET status = 'odendi' WHERE dues_id = NEW.dues_id;
        ELSEIF NEW.status = 'reddedildi' THEN
            UPDATE dues SET status = 'odenmedi' WHERE dues_id = NEW.dues_id;
        END IF;
    END IF;
END", "trg_after_payment_update trigger", $results, $hasError);

$conn->exec("DROP TRIGGER IF EXISTS trg_after_resident_insert");
runSQL($conn, "CREATE TRIGGER trg_after_resident_insert
AFTER INSERT ON residents FOR EACH ROW
BEGIN
    UPDATE apartments SET status = 'dolu' WHERE apartment_id = NEW.apartment_id;
END", "trg_after_resident_insert trigger", $results, $hasError);

$conn->exec("DROP TRIGGER IF EXISTS trg_after_resident_delete");
runSQL($conn, "CREATE TRIGGER trg_after_resident_delete
AFTER DELETE ON residents FOR EACH ROW
BEGIN
    DECLARE resident_count INT;
    SELECT COUNT(*) INTO resident_count FROM residents WHERE apartment_id = OLD.apartment_id;
    IF resident_count = 0 THEN
        UPDATE apartments SET status = 'bos' WHERE apartment_id = OLD.apartment_id;
    END IF;
END", "trg_after_resident_delete trigger", $results, $hasError);

// ── STORED PROCEDURE'LER ──────────────────────────────────────────────────────

$conn->exec("DROP PROCEDURE IF EXISTS sp_get_unpaid_dues");
runSQL($conn, "CREATE PROCEDURE sp_get_unpaid_dues(IN p_block_id INT, IN p_year INT)
BEGIN
    SELECT d.dues_id, d.year, d.month, d.amount, d.status,
           b.block_name, a.apartment_no, a.floor_no, r.name, r.surname, r.phone
    FROM dues d
    INNER JOIN apartments a ON d.apartment_id = a.apartment_id
    INNER JOIN blocks b ON a.block_id = b.block_id
    LEFT JOIN residents r ON a.apartment_id = r.apartment_id
    WHERE d.status = 'odenmedi'
      AND (p_block_id IS NULL OR b.block_id = p_block_id)
      AND (p_year IS NULL OR d.year = p_year)
    ORDER BY d.year DESC, d.month DESC;
END", "sp_get_unpaid_dues procedure", $results, $hasError);

$conn->exec("DROP PROCEDURE IF EXISTS sp_get_monthly_income");
runSQL($conn, "CREATE PROCEDURE sp_get_monthly_income(IN p_year INT, IN p_month VARCHAR(20))
BEGIN
    SELECT p_year AS year, p_month AS month,
        COUNT(*) AS payment_count,
        SUM(p.paid_amount) AS total_income,
        SUM(CASE WHEN p.payment_method = 'nakit' THEN p.paid_amount ELSE 0 END) AS cash_income,
        SUM(CASE WHEN p.payment_method = 'kart' THEN p.paid_amount ELSE 0 END) AS card_income,
        SUM(CASE WHEN p.payment_method = 'havale' THEN p.paid_amount ELSE 0 END) AS transfer_income
    FROM payments p
    INNER JOIN dues d ON p.dues_id = d.dues_id
    WHERE d.year = p_year AND d.month = p_month AND p.status = 'onaylandi';
END", "sp_get_monthly_income procedure", $results, $hasError);

$conn->exec("DROP PROCEDURE IF EXISTS sp_add_expense");
runSQL($conn, "CREATE PROCEDURE sp_add_expense(
    IN p_expense_type VARCHAR(100), IN p_amount DECIMAL(10,2),
    IN p_expense_date DATE, IN p_description TEXT)
BEGIN
    START TRANSACTION;
    INSERT INTO expenses (expense_type, amount, expense_date, description)
    VALUES (p_expense_type, p_amount, p_expense_date, p_description);
    COMMIT;
END", "sp_add_expense procedure", $results, $hasError);

$conn->exec("DROP PROCEDURE IF EXISTS sp_update_complaint_status");
runSQL($conn, "CREATE PROCEDURE sp_update_complaint_status(IN p_complaint_id INT, IN p_new_status VARCHAR(20))
BEGIN
    IF p_new_status IN ('acik', 'cozuldu') THEN
        UPDATE complaints SET status = p_new_status WHERE complaint_id = p_complaint_id;
        SELECT ROW_COUNT() AS affected_rows;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Gecersiz durum degeri.';
    END IF;
END", "sp_update_complaint_status procedure", $results, $hasError);

// ── ADMIN KULLANICISI ─────────────────────────────────────────────────────────

try {
    $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
    $check = $conn->prepare("SELECT user_id FROM users WHERE username = 'admin'");
    $check->execute();
    if ($check->fetch()) {
        $conn->prepare("UPDATE users SET password = ?, role = 'admin', resident_id = NULL WHERE username = 'admin'")->execute([$adminHash]);
        $results[] = ['ok', 'Admin şifresi güncellendi → admin / admin123'];
    } else {
        $conn->prepare("INSERT INTO users (username, password, role, resident_id) VALUES ('admin', ?, 'admin', NULL)")->execute([$adminHash]);
        $results[] = ['ok', 'Admin kullanıcısı oluşturuldu → admin / admin123'];
    }
} catch (PDOException $e) {
    $results[] = ['err', 'Admin kullanıcısı: ' . $e->getMessage()];
    $hasError = true;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Veritabanı Kurulum | Site Yönetimi</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #0f172a, #1d4ed8); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
.box { background: #fff; padding: 36px; border-radius: 20px; width: 100%; max-width: 640px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
h1 { font-size: 22px; color: #0f172a; margin-bottom: 6px; }
.sub { color: #64748b; font-size: 14px; margin-bottom: 24px; }
ul { list-style: none; margin-bottom: 24px; }
li { padding: 10px 14px; border-radius: 8px; margin-bottom: 6px; font-size: 13px; display: flex; align-items: center; gap: 10px; }
li.ok  { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
li.warn{ background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
li.err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.btn { display: block; text-align: center; background: #1d4ed8; color: #fff; text-decoration: none; padding: 14px; border-radius: 12px; font-weight: bold; font-size: 15px; }
.btn:hover { background: #1e40af; }
.warn-box { background: #fef3c7; border: 1px solid #fde68a; color: #92400e; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; }
</style>
</head>
<body>
<div class="box">
    <h1>🗄️ Veritabanı Kurulum Raporu</h1>
    <p class="sub">Tüm tablolar, view'lar, trigger'lar ve stored procedure'ler kontrol edildi.</p>

    <?php if (!$hasError): ?>
    <div class="warn-box">⚠️ Kurulum tamamlandı. Güvenlik için bu dosyayı (<code>install_db.php</code>) silin!</div>
    <?php endif; ?>

    <ul>
        <?php foreach ($results as [$type, $msg]): ?>
            <li class="<?= $type ?>">
                <?= $type === 'ok' ? '✅' : ($type === 'warn' ? '⚠️' : '❌') ?>
                <?= htmlspecialchars($msg) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (!$hasError): ?>
        <a href="login.php" class="btn">Giriş Sayfasına Git →</a>
    <?php else: ?>
        <a href="install_db.php" class="btn" style="background:#dc2626;">Tekrar Dene</a>
    <?php endif; ?>
</div>
</body>
</html>
