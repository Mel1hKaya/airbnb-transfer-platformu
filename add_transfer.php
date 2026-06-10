<?php
// add_transfer.php — Yeni Transfer Ekle (Create)
require_once 'config.php';
requireLogin();

$errors = [];
$old = [
    'customer_name'     => '',
    'customer_phone'    => '',
    'flight_no'         => '',
    'pickup_location'   => '',
    'dropoff_location'  => '',
    'passenger_count'   => 1,
    'transfer_datetime' => '',
    'price'             => '',
    'notes'             => '',
    'status'            => 'bekliyor',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Girdi Temizleme ──────────────────────────────────────
    $old['customer_name']     = trim($_POST['customer_name']    ?? '');
    $old['customer_phone']    = trim($_POST['customer_phone']   ?? '');
    $old['flight_no']         = trim($_POST['flight_no']        ?? '');
    $old['pickup_location']   = trim($_POST['pickup_location']  ?? '');
    $old['dropoff_location']  = trim($_POST['dropoff_location'] ?? '');
    $old['passenger_count']   = (int)($_POST['passenger_count'] ?? 1);
    $old['transfer_datetime'] = trim($_POST['transfer_datetime'] ?? '');
    $old['price']             = trim($_POST['price']            ?? '');
    $old['notes']             = trim($_POST['notes']            ?? '');
    $old['status']            = $_POST['status']                ?? 'bekliyor';

    // ── Doğrulama ────────────────────────────────────────────
    if ($old['customer_name'] === '') {
        $errors[] = 'Müşteri adı zorunludur.';
    }
    if ($old['customer_phone'] === '') {
        $errors[] = 'Telefon numarası zorunludur.';
    }
    if ($old['flight_no'] === '') {
        $errors[] = 'Uçuş / sefer numarası zorunludur.';
    }
    if ($old['pickup_location'] === '') {
        $errors[] = 'Alınacak yer zorunludur.';
    }
    if ($old['dropoff_location'] === '') {
        $errors[] = 'Bırakılacak yer zorunludur.';
    }
    if ($old['passenger_count'] < 1 || $old['passenger_count'] > 99) {
        $errors[] = 'Yolcu sayısı 1 ile 99 arasında olmalıdır.';
    }
    if ($old['transfer_datetime'] === '') {
        $errors[] = 'Transfer tarihi ve saati zorunludur.';
    }
    if ($old['price'] === '' || !is_numeric($old['price']) || (float)$old['price'] < 0) {
        $errors[] = 'Geçerli bir ücret giriniz (0 veya daha büyük).';
    }
    if (!in_array($old['status'], ['bekliyor', 'tamamlandı', 'iptal'], true)) {
        $errors[] = 'Geçersiz durum seçimi.';
    }

    // ── Kaydet ───────────────────────────────────────────────
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'INSERT INTO transfers
                (user_id, customer_name, customer_phone, flight_no,
                 pickup_location, dropoff_location, passenger_count,
                 transfer_datetime, price, notes, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            (int)$_SESSION['user_id'],
            $old['customer_name'],
            $old['customer_phone'],
            $old['flight_no'],
            $old['pickup_location'],
            $old['dropoff_location'],
            $old['passenger_count'],
            $old['transfer_datetime'],
            (float)$old['price'],
            $old['notes'] ?: null,
            $old['status'],
        ]);

        $_SESSION['flash'][] = [
            'type'    => 'success',
            'message' => $old['customer_name'] . ' adlı müşteriye ait transfer başarıyla eklendi.',
        ];
        header('Location: index.php');
        exit;
    }
}

$pageTitle = 'Yeni Transfer Ekle';
require_once 'includes/header.php';
?>

<!-- Sayfa Başlığı -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-700 mb-1" style="color:var(--at-dark);">
            <i class="bi bi-plus-circle me-2" style="color:var(--at-coral);"></i>Yeni Transfer Ekle
        </h2>
        <p class="text-muted small mb-0">Tüm zorunlu alanları doldurunuz.</p>
    </div>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Listeye Dön
    </a>
</div>

<!-- Hata Mesajları -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-at-danger mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Lütfen aşağıdaki hataları düzeltin:</strong>
        <ul class="mb-0 mt-2 ps-3">
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- Form Kartı -->
<div class="card-at p-4">
    <form method="POST" action="add_transfer.php" novalidate>

        <!-- ─ Bölüm 1: Müşteri Bilgileri ─────────────────── -->
        <h6 class="fw-700 mb-3 pb-2 border-bottom" style="color:var(--at-coral);">
            <i class="bi bi-person-lines-fill me-2"></i>Müşteri Bilgileri
        </h6>
        <div class="row g-3 mb-4">

            <div class="col-md-6">
                <label class="form-label fw-600 small">
                    Müşteri Adı Soyadı <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    name="customer_name"
                    class="form-control"
                    placeholder="Örn: Ahmet Yılmaz"
                    value="<?= e($old['customer_name']) ?>"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label fw-600 small">
                    Telefon Numarası <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    name="customer_phone"
                    class="form-control"
                    placeholder="Örn: +90 532 111 2233"
                    value="<?= e($old['customer_phone']) ?>"
                    required
                >
            </div>

        </div>

        <!-- ─ Bölüm 2: Transfer Detayları ────────────────── -->
        <h6 class="fw-700 mb-3 pb-2 border-bottom" style="color:var(--at-coral);">
            <i class="bi bi-airplane me-2"></i>Transfer Detayları
        </h6>
        <div class="row g-3 mb-4">

            <div class="col-md-4">
                <label class="form-label fw-600 small">
                    Uçuş / Sefer No <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    name="flight_no"
                    class="form-control"
                    placeholder="Örn: TK1234"
                    value="<?= e($old['flight_no']) ?>"
                    required
                >
            </div>

            <div class="col-md-4">
                <label class="form-label fw-600 small">
                    Transfer Tarihi ve Saati <span class="text-danger">*</span>
                </label>
                <input
                    type="datetime-local"
                    name="transfer_datetime"
                    class="form-control"
                    value="<?= e($old['transfer_datetime']) ?>"
                    required
                >
            </div>

            <div class="col-md-4">
                <label class="form-label fw-600 small">
                    Yolcu Sayısı <span class="text-danger">*</span>
                </label>
                <input
                    type="number"
                    name="passenger_count"
                    class="form-control"
                    min="1"
                    max="99"
                    value="<?= (int)$old['passenger_count'] ?>"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label fw-600 small">
                    <i class="bi bi-box-arrow-up-right text-success me-1"></i>
                    Alınacak Yer <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    name="pickup_location"
                    class="form-control"
                    placeholder="Örn: İstanbul Havalimanı"
                    value="<?= e($old['pickup_location']) ?>"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label fw-600 small">
                    <i class="bi bi-box-arrow-in-down-right text-danger me-1"></i>
                    Bırakılacak Yer <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    name="dropoff_location"
                    class="form-control"
                    placeholder="Örn: Sultanahmet Oteli"
                    value="<?= e($old['dropoff_location']) ?>"
                    required
                >
            </div>

        </div>

        <!-- ─ Bölüm 3: Ücret & Durum ──────────────────────── -->
        <h6 class="fw-700 mb-3 pb-2 border-bottom" style="color:var(--at-coral);">
            <i class="bi bi-cash-stack me-2"></i>Ücret & Durum
        </h6>
        <div class="row g-3 mb-4">

            <div class="col-md-4">
                <label class="form-label fw-600 small">
                    Ücret (₺) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text">₺</span>
                    <input
                        type="number"
                        name="price"
                        class="form-control"
                        min="0"
                        step="0.01"
                        placeholder="0.00"
                        value="<?= e($old['price']) ?>"
                        required
                    >
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-600 small">
                    Durum <span class="text-danger">*</span>
                </label>
                <select name="status" class="form-select">
                    <option value="bekliyor"   <?= $old['status'] === 'bekliyor'   ? 'selected' : '' ?>>⏳ Bekliyor</option>
                    <option value="tamamlandı" <?= $old['status'] === 'tamamlandı' ? 'selected' : '' ?>>✅ Tamamlandı</option>
                    <option value="iptal"      <?= $old['status'] === 'iptal'      ? 'selected' : '' ?>>❌ İptal</option>
                </select>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-600 small">
                    <i class="bi bi-chat-left-text me-1"></i>Notlar
                    <span class="text-muted fw-400">(isteğe bağlı)</span>
                </label>
                <textarea
                    name="notes"
                    class="form-control"
                    rows="3"
                    placeholder="Özel istekler, araç tipi, ek bilgi..."
                ><?= e($old['notes']) ?></textarea>
            </div>

        </div>

        <!-- ─ Gönder Butonları ────────────────────────────── -->
        <hr class="divider-coral">
        <div class="d-flex justify-content-end gap-2">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i>İptal
            </a>
            <button type="submit" class="btn btn-coral px-4">
                <i class="bi bi-check-lg me-1"></i>Transferi Kaydet
            </button>
        </div>

    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
