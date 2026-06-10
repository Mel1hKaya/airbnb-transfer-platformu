<?php
// delete_transfer.php — Transfer Sil (Delete)
require_once 'config.php';
requireLogin();

// Sadece POST isteğini kabul et
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'][] = [
        'type'    => 'danger',
        'message' => 'Geçersiz transfer ID.',
    ];
    header('Location: index.php');
    exit;
}

// ── Sahiplik Kontrolü (başka kullanıcının kaydını silemesin) ─
$stmt = $pdo->prepare(
    'SELECT id, customer_name FROM transfers WHERE id = ? AND user_id = ? LIMIT 1'
);
$stmt->execute([$id, (int)$_SESSION['user_id']]);
$transfer = $stmt->fetch();

if (!$transfer) {
    $_SESSION['flash'][] = [
        'type'    => 'danger',
        'message' => 'Transfer bulunamadı veya bu kaydı silme yetkiniz yok.',
    ];
    header('Location: index.php');
    exit;
}

// ── Sil ─────────────────────────────────────────────────────
$deleteStmt = $pdo->prepare(
    'DELETE FROM transfers WHERE id = ? AND user_id = ?'
);
$deleteStmt->execute([$id, (int)$_SESSION['user_id']]);

$_SESSION['flash'][] = [
    'type'    => 'success',
    'message' => '"' . $transfer['customer_name'] . '" adlı müşteriye ait transfer kaydı silindi.',
];

header('Location: index.php');
exit;
