<?php
// logout.php
require_once 'config.php';

// Oturum verilerini temizle
$_SESSION = [];

// Session cookie'sini geçersiz kıl
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Session'ı tamamen yok et
session_destroy();

// Yeni session başlat (flash mesaj için)
session_start();
$_SESSION['flash'][] = [
    'type'    => 'success',
    'message' => 'Başarıyla çıkış yaptınız. Güle güle!',
];

header('Location: login.php');
exit;
