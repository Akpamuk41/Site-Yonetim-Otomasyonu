<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

require_once __DIR__ . '/db.php';

/**
 * Giriş yapmış kullanıcıyı döndürür.
 */
function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? null,
            'role' => $_SESSION['role'] ?? null,
            'resident_id' => $_SESSION['resident_id'] ?? null
        ];
    }
    return null;
}

/**
 * Kullanıcının giriş yapmış olup olmadığını kontrol eder.
 * Giriş yapmamışsa login sayfasına yönlendirir.
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /site_yonetim/login.php");
        exit;
    }
}

/**
 * Yönetici yetkisi gerektirir.
 */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: /site_yonetim/resident/index.php");
        exit;
    }
}

/**
 * Sakin yetkisi gerektirir.
 */
function requireResident() {
    requireLogin();
    if ($_SESSION['role'] !== 'resident') {
        header("Location: /site_yonetim/index.php");
        exit;
    }
}

/**
 * Kullanıcıyı sisteme giriş yaptırır.
 */
function loginUser($username, $password) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['resident_id'] = $user['resident_id'];
        return true;
    }
    
    return false;
}

/**
 * Çıkış yapar.
 */
function logoutUser() {
    session_unset();
    session_destroy();
}
