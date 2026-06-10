<?php
// includes/header.php
// config.php zaten include edilmiş olmalı (her sayfanın en üstünde)
// Bu dosya doğrudan include edilmez; config.php'den sonra çağrılır.

// Aktif sayfayı navbar'da vurgulamak için
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>AirTransfer</title>

    <!-- Bootstrap 5.3 CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <!-- Google Fonts: Cedarville Cursive (logo) + Plus Jakarta Sans (gövde) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* ── CSS Değişkenleri (Airbnb Palette) ───────────────── */
        :root {
            --at-coral:       #FF5A5F;
            --at-coral-dark:  #e04347;
            --at-coral-light: #ffe4e5;
            --at-dark:        #222222;
            --at-gray:        #717171;
            --at-light-gray:  #f7f7f7;
            --at-border:      #dddddd;
            --at-white:       #ffffff;
            --at-shadow:      0 2px 16px rgba(0,0,0,.12);
            --at-radius:      12px;
        }

        /* ── Genel ────────────────────────────────────────────── */
        * { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--at-light-gray);
            color: var(--at-dark);
            min-height: 100vh;
        }

        /* ── Navbar ───────────────────────────────────────────── */
        .navbar-airtransfer {
            background: var(--at-white);
            border-bottom: 1px solid var(--at-border);
            padding: .75rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 1px 8px rgba(0,0,0,.08);
        }

        .navbar-brand-logo {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--at-coral) !important;
            letter-spacing: -0.5px;
            text-decoration: none;
        }
        .navbar-brand-logo span {
            color: var(--at-dark);
        }
        .navbar-brand-logo i {
            font-size: 1.4rem;
            margin-right: 4px;
        }

        /* Nav linkleri */
        .nav-link-at {
            color: var(--at-dark) !important;
            font-weight: 500;
            font-size: .9rem;
            padding: .45rem .8rem !important;
            border-radius: 8px;
            transition: background .15s, color .15s;
        }
        .nav-link-at:hover,
        .nav-link-at.active {
            background: var(--at-coral-light);
            color: var(--at-coral) !important;
        }
        .nav-link-at i { margin-right: 5px; }

        /* Kullanıcı avatarı (navbar sağ) */
        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--at-coral);
            color: #fff;
            font-weight: 700;
            font-size: .85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 6px;
            flex-shrink: 0;
        }

        /* ── Butonlar ─────────────────────────────────────────── */
        .btn-coral {
            background: var(--at-coral);
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: .45rem 1.2rem;
            transition: background .2s, transform .1s;
        }
        .btn-coral:hover {
            background: var(--at-coral-dark);
            color: #fff;
            transform: translateY(-1px);
        }
        .btn-coral:active { transform: translateY(0); }

        .btn-outline-coral {
            border: 1.5px solid var(--at-coral);
            color: var(--at-coral);
            background: transparent;
            font-weight: 600;
            border-radius: 8px;
            padding: .45rem 1.2rem;
            transition: all .2s;
        }
        .btn-outline-coral:hover {
            background: var(--at-coral);
            color: #fff;
        }

        /* ── Kartlar ──────────────────────────────────────────── */
        .card-at {
            background: var(--at-white);
            border: 1px solid var(--at-border);
            border-radius: var(--at-radius);
            box-shadow: var(--at-shadow);
            transition: box-shadow .2s;
        }
        .card-at:hover { box-shadow: 0 6px 24px rgba(0,0,0,.15); }

        /* ── Tablolar ─────────────────────────────────────────── */
        .table-at thead th {
            background: var(--at-light-gray);
            border-bottom: 2px solid var(--at-border);
            font-size: .8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--at-gray);
            white-space: nowrap;
        }
        .table-at tbody tr {
            transition: background .15s;
        }
        .table-at tbody tr:hover {
            background: #fff5f5;
        }
        .table-at td {
            vertical-align: middle;
            font-size: .9rem;
        }

        /* ── Durum Rozetleri ──────────────────────────────────── */
        .badge-bekliyor  { background: #fff3cd; color: #856404;  border: 1px solid #ffc107; }
        .badge-tamamlandi{ background: #d1e7dd; color: #0a3622;  border: 1px solid #198754; }
        .badge-iptal     { background: #f8d7da; color: #58151c;  border: 1px solid #dc3545; }
        .badge-at {
            font-size: .75rem;
            font-weight: 600;
            padding: .3em .7em;
            border-radius: 20px;
        }

        /* ── Geçmiş Transfer Satırları (soluk/gri) ───────────── */
        .row-past td {
            color: var(--at-gray) !important;
            opacity: .7;
        }

        /* ── Form Elemanları ──────────────────────────────────── */
        .form-control:focus,
        .form-select:focus {
            border-color: var(--at-coral);
            box-shadow: 0 0 0 .2rem rgba(255,90,95,.2);
        }

        /* ── Sayfa İçeriği Boşluğu ───────────────────────────── */
        .page-content {
            padding: 2rem 0 4rem;
        }

        /* ── Flash Mesajları ──────────────────────────────────── */
        .alert-at-success {
            background: #d1e7dd;
            border: 1px solid #a3cfbb;
            color: #0a3622;
            border-radius: 10px;
        }
        .alert-at-danger {
            background: #f8d7da;
            border: 1px solid #f1aeb5;
            color: #58151c;
            border-radius: 10px;
        }

        /* ── Auth Sayfaları ───────────────────────────────────── */
        .auth-wrapper {
            min-height: calc(100vh - 70px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
        }

        /* ── Logo divider ─────────────────────────────────────── */
        .divider-coral {
            border: none;
            border-top: 2px solid var(--at-coral-light);
            margin: 1.25rem 0;
        }
    </style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════
     NAVBAR
════════════════════════════════════════════════════════ -->
<nav class="navbar navbar-airtransfer navbar-expand-lg">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand-logo" href="<?= BASE_URL ?>index.php">
            <i class="bi bi-send-fill"></i>Air<span>Transfer</span>
        </a>

        <!-- Mobil toggle -->
        <button
            class="navbar-toggler border-0"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNav"
            aria-controls="mainNav"
            aria-expanded="false"
            aria-label="Menüyü aç/kapat"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav linkleri -->
        <div class="collapse navbar-collapse" id="mainNav">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <!-- Giriş YAPILMIŞ menü -->
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-at <?= $currentPage === 'index.php' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>index.php">
                            <i class="bi bi-list-ul"></i>Transferler
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-at <?= $currentPage === 'add_transfer.php' ? 'active' : '' ?>"
                           href="<?= BASE_URL ?>add_transfer.php">
                            <i class="bi bi-plus-circle"></i>Yeni Transfer
                        </a>
                    </li>
                </ul>

                <!-- Kullanıcı bilgisi + çıkış -->
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item d-flex align-items-center">
                        <span class="user-avatar">
                            <?= strtoupper(mb_substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                        </span>
                        <span class="fw-600 text-dark" style="font-size:.9rem;">
                            <?= e($_SESSION['user_name'] ?? '') ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a href="<?= BASE_URL ?>logout.php" class="btn btn-outline-coral btn-sm">
                            <i class="bi bi-box-arrow-right me-1"></i>Çıkış
                        </a>
                    </li>
                </ul>

            <?php else: ?>
                <!-- Giriş YAPILMAMIŞ menü -->
                <ul class="navbar-nav ms-auto gap-2">
                    <li class="nav-item">
                        <a class="btn btn-outline-coral btn-sm" href="<?= BASE_URL ?>login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Giriş Yap
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-coral btn-sm" href="<?= BASE_URL ?>register.php">
                            <i class="bi bi-person-plus me-1"></i>Kayıt Ol
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div><!-- /.collapse -->
    </div><!-- /.container -->
</nav>

<!-- ═══════════════════════════════════════════════════════
     FLASH MESAJLARI (session üzerinden)
════════════════════════════════════════════════════════ -->
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="container mt-3">
        <?php foreach ($_SESSION['flash'] as $flash): ?>
            <div class="alert <?= $flash['type'] === 'success' ? 'alert-at-success' : 'alert-at-danger' ?> alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
                <i class="bi <?= $flash['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?>"></i>
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Kapat"></button>
            </div>
        <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<!-- Sayfa içeriği buradan başlar -->
<div class="page-content">
<div class="container">
