<?php
// ============================================================
// config.php — Veritabanı Bağlantısı & Global Ayarlar
// ============================================================

// Hata raporlamayı geliştirme ortamında açık tut,
// canlıya alırken aşağıdaki iki satırı yoruma al.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ── Oturum Başlat ───────────────────────────────────────────
// Tüm sayfalardan önce session başlatılmalı.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Veritabanı Bilgileri ────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'airbnb_transfer');
define('DB_USER', 'root');       // Hosting'e taşırken değiştir
define('DB_PASS', '');           // Hosting'e taşırken değiştir
define('DB_CHARSET', 'utf8mb4');

// ── Uygulama Yolu (URL prefix) ──────────────────────────────
// Klasör adınız farklıysa burası güncellenir.
// Hosting'e taşırken genellikle '' (boş string) olur.
define('BASE_URL', '/airbnb_transfer/');

// ── PDO Bağlantısı ──────────────────────────────────────────
// Uygulama genelinde tek bir $pdo nesnesi kullanılır.
try {
    $dsn = 'mysql:host=' . DB_HOST
         . ';dbname=' . DB_NAME
         . ';charset=' . DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Hataları Exception olarak fırlat
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Sonuçları ilişkisel dizi olarak getir
        PDO::ATTR_EMULATE_PREPARES   => false,                   // Gerçek prepared statements kullan
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // Canlı ortamda bu mesajı kullanıcıya gösterme!
    die('<div style="font-family:sans-serif;padding:2rem;color:#c0392b;">
            <h2>⚠️ Veritabanı Bağlantı Hatası</h2>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
         </div>');
}

// ── Yardımcı Fonksiyonlar ───────────────────────────────────

/**
 * XSS'e karşı çıktıyı güvenli hale getirir.
 * Şablon dosyalarında echo yerine e() kullan.
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Kullanıcı oturum açmış mı kontrol eder.
 * Açmamışsa login sayfasına yönlendirir.
 */
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

/**
 * Oturum açmış kullanıcı zaten varsa,
 * login/register sayfalarına erişimi engeller.
 */
function redirectIfLoggedIn(): void {
    if (!empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}
