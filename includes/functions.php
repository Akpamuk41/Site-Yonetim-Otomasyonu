<?php
/**
 * Global Yardımcı Fonksiyonlar
 */

function formatMoney($amount) {
    return number_format($amount, 2, ',', '.') . ' ₺';
}

function paymentMethodText($method) {
    $map = [
        'nakit' => 'Nakit',
        'kart' => 'Kart',
        'havale' => 'Havale'
    ];
    return $map[$method] ?? ucfirst($method);
}

function paymentMethodClass($method) {
    $map = [
        'nakit' => 'badge-cash',
        'kart' => 'badge-card',
        'havale' => 'badge-transfer'
    ];
    return $map[$method] ?? '';
}

function duesStatusBadge($status) {
    if ($status == 'odendi') {
        return '<span class="badge badge-paid">Ödendi</span>';
    }
    return '<span class="badge badge-unpaid">Ödenmedi</span>';
}

function complaintStatusBadge($status) {
    if ($status == 'acik') {
        return '<span class="badge badge-open">Açık</span>';
    }
    return '<span class="badge badge-closed">Çözüldü</span>';
}

function activePage($page) {
    $current = basename($_SERVER['PHP_SELF']);
    return $current === $page ? 'active' : '';
}

function pageTitle($page) {
    $titles = [
        'index.php' => 'Dashboard',
        'blocks.php' => 'Bloklar',
        'apartments.php' => 'Daireler',
        'residents.php' => 'Sakinler',
        'dues.php' => 'Aidatlar',
        'payments.php' => 'Ödemeler',
        'complaints.php' => 'Şikayetler',
        'expenses.php' => 'Giderler',
        'add_payment.php' => 'Yeni Ödeme',
        'add_complaint.php' => 'Yeni Şikayet',
        'add_resident.php' => 'Yeni Sakin',
        'add_block.php' => 'Yeni Blok',
        'add_apartment.php' => 'Yeni Daire',
        'add_due.php' => 'Yeni Aidat',
        'add_expense.php' => 'Yeni Gider',
        'pending_payments.php' => 'Bekleyen Ödemeler',
        'register.php' => 'Sakin Kullanıcı Ekle',
        'resident/index.php' => 'Ana Sayfa',
        'resident/my_dues.php' => 'Aidatlarım',
        'resident/pay_dues.php' => 'Aidat Öde',
        'resident/my_complaints.php' => 'Şikayetlerim',
        'resident/expenses.php' => 'Site Giderleri',
    ];
    return $titles[$page] ?? 'Site Yönetimi';
}
