<?php
// index.php — Transfer Listesi (Read)
require_once 'config.php';
requireLogin(); // Oturum yoksa login.php'ye yönlendir

// ── Filtre Parametreleri ─────────────────────────────────────
$filterStatus = $_GET['status'] ?? 'all';
$filterSearch = trim($_GET['search'] ?? '');

// ── Transfer Sorgusu ─────────────────────────────────────────
$where  = ['t.user_id = ?'];
$params = [(int)$_SESSION['user_id']];

if (in_array($filterStatus, ['bekliyor', 'tamamlandı', 'iptal'], true)) {
    $where[]  = 't.status = ?';
    $params[] = $filterStatus;
}

if ($filterSearch !== '') {
    $where[]  = '(t.customer_name LIKE ? OR t.flight_no LIKE ? OR t.pickup_location LIKE ? OR t.dropoff_location LIKE ?)';
    $like     = '%' . $filterSearch . '%';
    $params   = array_merge($params, [$like, $like, $like, $like]);
}

$sql = 'SELECT * FROM transfers t
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY t.transfer_datetime DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transfers = $stmt->fetchAll();

// ── İstatistikler (filtresiz, kullanıcıya ait) ───────────────
$statsStmt = $pdo->prepare(
    'SELECT
        COUNT(*)                                         AS total,
        SUM(status = "bekliyor")                         AS bekliyor,
        SUM(status = "tamamlandı")                       AS tamamlandi,
        SUM(status = "iptal")                            AS iptal,
        COALESCE(SUM(CASE WHEN status = "tamamlandı"
                     THEN price ELSE 0 END), 0)          AS toplam_gelir
     FROM transfers
     WHERE user_id = ?'
);
$statsStmt->execute([(int)$_SESSION['user_id']]);
$stats = $statsStmt->fetch();

// ── Yardımcı: Durum rozet sınıfı ────────────────────────────
function statusBadgeClass(string $status): string {
    return match($status) {
        'bekliyor'   => 'badge-bekliyor',
        'tamamlandı' => 'badge-tamamlandi',
        'iptal'      => 'badge-iptal',
        default      => 'bg-secondary',
    };
}

function statusIcon(string $status): string {
    return match($status) {
        'bekliyor'   => 'bi-clock',
        'tamamlandı' => 'bi-check-circle-fill',
        'iptal'      => 'bi-x-circle-fill',
        default      => 'bi-circle',
    };
}

$pageTitle = 'Transfer Listesi';
require_once 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════════
     SAYFA BAŞLIĞI
══════════════════════════════════════════════════════════ -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h2 class="fw-700 mb-1" style="color:var(--at-dark);">
            <i class="bi bi-list-ul me-2" style="color:var(--at-coral);"></i>Transfer Listesi
        </h2>
        <p class="text-muted small mb-0">
            Hoş geldiniz, <strong><?= e($_SESSION['user_name']) ?></strong> —
            toplam <strong><?= (int)$stats['total'] ?></strong> transfer kaydınız var.
        </p>
    </div>
    <a href="add_transfer.php" class="btn btn-coral">
        <i class="bi bi-plus-lg me-1"></i>Yeni Transfer Ekle
    </a>
</div>

<!-- ══════════════════════════════════════════════════════════
     İSTATİSTİK KARTLARI
══════════════════════════════════════════════════════════ -->
<div class="row g-3 mb-4">

    <!-- Toplam -->
    <div class="col-6 col-md-3">
        <div class="card-at p-3 text-center h-100">
            <div style="font-size:1.8rem; color:var(--at-coral);">
                <i class="bi bi-send"></i>
            </div>
            <div class="fw-700 fs-4 mt-1"><?= (int)$stats['total'] ?></div>
            <div class="text-muted small">Toplam Transfer</div>
        </div>
    </div>

    <!-- Bekliyor -->
    <div class="col-6 col-md-3">
        <div class="card-at p-3 text-center h-100" style="border-top:3px solid #ffc107;">
            <div style="font-size:1.8rem; color:#ffc107;">
                <i class="bi bi-clock"></i>
            </div>
            <div class="fw-700 fs-4 mt-1"><?= (int)$stats['bekliyor'] ?></div>
            <div class="text-muted small">Bekliyor</div>
        </div>
    </div>

    <!-- Tamamlandı -->
    <div class="col-6 col-md-3">
        <div class="card-at p-3 text-center h-100" style="border-top:3px solid #198754;">
            <div style="font-size:1.8rem; color:#198754;">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="fw-700 fs-4 mt-1"><?= (int)$stats['tamamlandi'] ?></div>
            <div class="text-muted small">Tamamlandı</div>
        </div>
    </div>

    <!-- Toplam Gelir -->
    <div class="col-6 col-md-3">
        <div class="card-at p-3 text-center h-100" style="border-top:3px solid var(--at-coral);">
            <div style="font-size:1.8rem; color:var(--at-coral);">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div class="fw-700 fs-4 mt-1">
                <?= number_format((float)$stats['toplam_gelir'], 2, ',', '.') ?> ₺
            </div>
            <div class="text-muted small">Toplam Gelir</div>
        </div>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════════
     FİLTRE & ARAMA ÇUBUĞU
══════════════════════════════════════════════════════════ -->
<div class="card-at p-3 mb-4">
    <form method="GET" action="index.php" class="row g-2 align-items-end">

        <!-- Arama -->
        <div class="col-12 col-md-5">
            <label class="form-label fw-600 small mb-1">
                <i class="bi bi-search me-1"></i>Ara
            </label>
            <input
                type="text"
                name="search"
                class="form-control form-control-sm"
                placeholder="Müşteri adı, uçuş no, lokasyon..."
                value="<?= e($filterSearch) ?>"
            >
        </div>

        <!-- Durum Filtresi -->
        <div class="col-12 col-md-4">
            <label class="form-label fw-600 small mb-1">
                <i class="bi bi-funnel me-1"></i>Durum
            </label>
            <select name="status" class="form-select form-select-sm">
                <option value="all"        <?= $filterStatus === 'all'        ? 'selected' : '' ?>>Tümü</option>
                <option value="bekliyor"   <?= $filterStatus === 'bekliyor'   ? 'selected' : '' ?>>Bekliyor</option>
                <option value="tamamlandı" <?= $filterStatus === 'tamamlandı' ? 'selected' : '' ?>>Tamamlandı</option>
                <option value="iptal"      <?= $filterStatus === 'iptal'      ? 'selected' : '' ?>>İptal</option>
            </select>
        </div>

        <!-- Butonlar -->
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-coral btn-sm flex-fill">
                <i class="bi bi-funnel-fill me-1"></i>Filtrele
            </button>
            <a href="index.php" class="btn btn-outline-secondary btn-sm flex-fill">
                <i class="bi bi-x-lg me-1"></i>Temizle
            </a>
        </div>

    </form>
</div>

<!-- ══════════════════════════════════════════════════════════
     TRANSFER TABLOSU
══════════════════════════════════════════════════════════ -->
<div class="card-at">
    <?php if (empty($transfers)): ?>
        <!-- Boş durum -->
        <div class="text-center py-5 px-3">
            <div style="font-size:3.5rem; color:var(--at-border);">
                <i class="bi bi-inbox"></i>
            </div>
            <h5 class="mt-3 text-muted">Henüz transfer kaydı yok</h5>
            <p class="text-muted small">
                <?= $filterSearch || $filterStatus !== 'all'
                    ? 'Arama kriterlerinizle eşleşen kayıt bulunamadı.'
                    : 'İlk transferinizi eklemek için butona tıklayın.' ?>
            </p>
            <?php if (!$filterSearch && $filterStatus === 'all'): ?>
                <a href="add_transfer.php" class="btn btn-coral mt-2">
                    <i class="bi bi-plus-lg me-1"></i>Yeni Transfer Ekle
                </a>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- Masaüstü tablo -->
        <div class="table-responsive">
            <table class="table table-at mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><i class="bi bi-person me-1"></i>Müşteri</th>
                        <th><i class="bi bi-airplane me-1"></i>Uçuş No</th>
                        <th><i class="bi bi-geo-alt me-1"></i>Güzergah</th>
                        <th><i class="bi bi-calendar-event me-1"></i>Tarih & Saat</th>
                        <th><i class="bi bi-people me-1"></i>Yolcu</th>
                        <th><i class="bi bi-cash me-1"></i>Ücret</th>
                        <th><i class="bi bi-circle me-1"></i>Durum</th>
                        <th class="text-center"><i class="bi bi-gear me-1"></i>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transfers as $i => $t):
                        $isPast = strtotime($t['transfer_datetime']) < time()
                                  && $t['status'] === 'bekliyor';
                        $rowClass = $isPast ? 'row-past' : '';
                    ?>
                        <tr class="<?= $rowClass ?>">

                            <!-- Sıra No -->
                            <td class="text-muted small"><?= $i + 1 ?></td>

                            <!-- Müşteri -->
                            <td>
                                <div class="fw-600"><?= e($t['customer_name']) ?></div>
                                <div class="text-muted small">
                                    <i class="bi bi-telephone me-1"></i><?= e($t['customer_phone']) ?>
                                </div>
                            </td>

                            <!-- Uçuş No -->
                            <td>
                                <span class="badge bg-light text-dark border fw-600">
                                    <?= e($t['flight_no']) ?>
                                </span>
                            </td>

                            <!-- Güzergah -->
                            <td style="max-width:220px;">
                                <div class="small">
                                    <i class="bi bi-box-arrow-up-right text-success me-1"></i>
                                    <?= e($t['pickup_location']) ?>
                                </div>
                                <div class="small mt-1">
                                    <i class="bi bi-box-arrow-in-down-right text-danger me-1"></i>
                                    <?= e($t['dropoff_location']) ?>
                                </div>
                            </td>

                            <!-- Tarih & Saat -->
                            <td class="small">
                                <?php
                                    $dt = new DateTime($t['transfer_datetime']);
                                    echo $dt->format('d.m.Y');
                                    echo '<br><span class="text-muted">' . $dt->format('H:i') . '</span>';
                                    if ($isPast) {
                                        echo '<br><span class="badge bg-warning text-dark" style="font-size:.65rem;">Geçmiş</span>';
                                    }
                                ?>
                            </td>

                            <!-- Yolcu -->
                            <td class="text-center">
                                <i class="bi bi-person-fill me-1 text-muted"></i>
                                <?= (int)$t['passenger_count'] ?>
                            </td>

                            <!-- Ücret -->
                            <td class="fw-600" style="white-space:nowrap; color:var(--at-coral);">
                                <?= number_format((float)$t['price'], 2, ',', '.') ?> ₺
                            </td>

                            <!-- Durum -->
                            <td>
                                <span class="badge badge-at <?= statusBadgeClass($t['status']) ?>">
                                    <i class="bi <?= statusIcon($t['status']) ?> me-1"></i>
                                    <?= e(ucfirst($t['status'])) ?>
                                </span>
                            </td>

                            <!-- İşlemler -->
                            <td class="text-center" style="white-space:nowrap;">
                                <!-- Detay/Düzenle -->
                                <a
                                    href="edit_transfer.php?id=<?= (int)$t['id'] ?>"
                                    class="btn btn-sm btn-outline-secondary me-1"
                                    title="Düzenle"
                                >
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <!-- Sil -->
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Sil"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteModal"
                                    data-id="<?= (int)$t['id'] ?>"
                                    data-name="<?= e($t['customer_name']) ?>"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tablo altı: toplam kayıt sayısı -->
        <div class="px-3 py-2 border-top text-muted small d-flex justify-content-between">
            <span><?= count($transfers) ?> kayıt gösteriliyor</span>
            <?php if ($filterSearch || $filterStatus !== 'all'): ?>
                <a href="index.php" class="text-decoration-none" style="color:var(--at-coral);">
                    <i class="bi bi-x-circle me-1"></i>Filtreyi temizle
                </a>
            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════
     SİLME ONAY MODALI
══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:var(--at-radius); border:none;">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-700" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                    Transferi Sil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>

            <div class="modal-body">
                <p class="mb-0">
                    <strong id="deleteCustomerName"></strong> adlı müşteriye ait transfer kaydı
                    kalıcı olarak silinecek. Bu işlem geri alınamaz.
                </p>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Vazgeç
                </button>
                <form method="POST" action="delete_transfer.php" id="deleteForm">
                    <input type="hidden" name="id" id="deleteTransferId">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Evet, Sil
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
// Silme modalına transfer bilgilerini aktar
const deleteModal = document.getElementById('deleteModal');
deleteModal.addEventListener('show.bs.modal', function (e) {
    const btn  = e.relatedTarget;
    document.getElementById('deleteTransferId').value    = btn.dataset.id;
    document.getElementById('deleteCustomerName').textContent = btn.dataset.name;
});
</script>

<?php require_once 'includes/footer.php'; ?>
