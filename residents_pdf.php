<?php
require_once "includes/db.php";
require_once "includes/auth.php";
requireAdmin();

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
        ORDER BY b.block_name, a.apartment_no ASC";

$residents = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
$totalCount = count($residents);
$evSahibiCount = count(array_filter($residents, fn($r) => $r['type'] == 'ev_sahibi'));
$kiraciCount = $totalCount - $evSahibiCount;

date_default_timezone_set('Europe/Istanbul');
$printDate = date('d.m.Y H:i');

// PHP array'i JS için JSON'a çevir
$residentsJson = json_encode($residents, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sakin Listesi PDF</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0f172a, #1d4ed8);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 40px 48px;
            text-align: center;
            box-shadow: 0 24px 48px rgba(0,0,0,0.3);
            max-width: 420px;
            width: 100%;
        }
        .icon { font-size: 56px; margin-bottom: 16px; }
        h1 { font-size: 22px; color: #0f172a; margin-bottom: 8px; }
        p { color: #64748b; font-size: 14px; margin-bottom: 28px; line-height: 1.6; }
        .stats {
            display: flex;
            gap: 12px;
            margin-bottom: 28px;
        }
        .stat {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
        }
        .stat .num { font-size: 24px; font-weight: bold; color: #0f172a; }
        .stat .lbl { font-size: 11px; color: #64748b; margin-top: 2px; }
        .btn-download {
            width: 100%;
            background: #dc2626;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            margin-bottom: 12px;
        }
        .btn-download:hover { background: #b91c1c; }
        .btn-back {
            display: block;
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
            padding: 10px;
        }
        .btn-back:hover { color: #0f172a; }
        #status { font-size: 13px; color: #16a34a; margin-top: 10px; min-height: 20px; }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">📄</div>
    <h1>Sakin Listesi Raporu</h1>
    <p>Rapor tarihi: <?php echo $printDate; ?></p>

    <div class="stats">
        <div class="stat">
            <div class="num"><?php echo $totalCount; ?></div>
            <div class="lbl">Toplam Sakin</div>
        </div>
        <div class="stat">
            <div class="num" style="color:#166534;"><?php echo $evSahibiCount; ?></div>
            <div class="lbl">Ev Sahibi</div>
        </div>
        <div class="stat">
            <div class="num" style="color:#991b1b;"><?php echo $kiraciCount; ?></div>
            <div class="lbl">Kiracı</div>
        </div>
    </div>

    <button class="btn-download" onclick="downloadPDF()">
        ⬇ PDF İndir
    </button>
    <div id="status"></div>
    <a href="residents.php" class="btn-back">← Sakinler Listesine Dön</a>
</div>

<script>
const residents = <?php echo $residentsJson; ?>;

function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

    // Başlık
    doc.setFillColor(29, 78, 216);
    doc.rect(0, 0, 297, 22, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('Site Yonetimi - Sakin Listesi Raporu', 148.5, 13, { align: 'center' });

    // Alt başlık
    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(100, 116, 139);
    doc.text('Kocaeli Universitesi - Veritabani Yonetim Sistemleri - Donem Projesi', 148.5, 28, { align: 'center' });
    doc.text('Rapor Tarihi: <?php echo $printDate; ?>', 148.5, 34, { align: 'center' });

    // Özet bilgi
    doc.setFontSize(10);
    doc.setTextColor(30, 30, 30);
    doc.text('Toplam Sakin: <?php echo $totalCount; ?>   |   Ev Sahibi: <?php echo $evSahibiCount; ?>   |   Kiraci: <?php echo $kiraciCount; ?>', 14, 42);

    // Tablo verisi
    const tableData = residents.map((r, i) => [
        i + 1,
        r.name + ' ' + r.surname,
        r.phone || '-',
        r.email || '-',
        r.type === 'ev_sahibi' ? 'Ev Sahibi' : 'Kiraci',
        r.block_name,
        r.apartment_no
    ]);

    doc.autoTable({
        startY: 46,
        head: [['#', 'Ad Soyad', 'Telefon', 'E-posta', 'Tur', 'Blok', 'Daire No']],
        body: tableData,
        theme: 'grid',
        headStyles: {
            fillColor: [29, 78, 216],
            textColor: 255,
            fontStyle: 'bold',
            fontSize: 10,
            halign: 'left'
        },
        bodyStyles: {
            fontSize: 9,
            textColor: [31, 41, 55]
        },
        alternateRowStyles: {
            fillColor: [248, 250, 252]
        },
        columnStyles: {
            0: { cellWidth: 10, halign: 'center' },
            1: { cellWidth: 50 },
            2: { cellWidth: 35 },
            3: { cellWidth: 65 },
            4: { cellWidth: 25 },
            5: { cellWidth: 30 },
            6: { cellWidth: 20, halign: 'center' }
        },
        margin: { left: 14, right: 14 },
        didDrawCell: function(data) {
            // Tür sütununa renk
            if (data.section === 'body' && data.column.index === 4) {
                const val = data.cell.raw;
                if (val === 'Ev Sahibi') {
                    doc.setFillColor(220, 252, 231);
                    doc.rect(data.cell.x, data.cell.y, data.cell.width, data.cell.height, 'F');
                    doc.setTextColor(22, 101, 52);
                    doc.setFontSize(9);
                    doc.text(val, data.cell.x + data.cell.width / 2, data.cell.y + data.cell.height / 2 + 1, { align: 'center' });
                } else {
                    doc.setFillColor(254, 226, 226);
                    doc.rect(data.cell.x, data.cell.y, data.cell.width, data.cell.height, 'F');
                    doc.setTextColor(153, 27, 27);
                    doc.setFontSize(9);
                    doc.text(val, data.cell.x + data.cell.width / 2, data.cell.y + data.cell.height / 2 + 1, { align: 'center' });
                }
            }
        }
    });

    // Footer
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(148, 163, 184);
        doc.text('Site Yonetim Sistemi - Sayfa ' + i + ' / ' + pageCount, 148.5, 205, { align: 'center' });
    }

    doc.save('sakin_listesi_<?php echo date("Ymd_Hi"); ?>.pdf');
    document.getElementById('status').textContent = '✅ PDF başarıyla indirildi!';
}

// Sayfa açılınca otomatik indir
window.onload = function() {
    setTimeout(downloadPDF, 600);
};
</script>
</body>
</html>
