<?php
require 'includes/db.php';

// Tablo durumunu kontrol et
$tables = ['blocks','apartments','residents','dues','payments','complaints','expenses','users','announcements'];
foreach ($tables as $t) {
    try {
        $count = $conn->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "OK: $t ($count kayit)\n";
    } catch (Exception $e) {
        echo "HATA: $t -> " . $e->getMessage() . "\n";
    }
}
