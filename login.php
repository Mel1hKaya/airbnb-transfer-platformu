<?php
// login.php
require_once 'config.php';
redirectIfLoggedIn(); // Zaten giriş yapmışsa index'e yönlendir

$errors = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Girdi Temizleme ──────────────────────────────────────
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $oldEmail = $email;

    // ── Temel Doğrulama ──────────────────────────────────────
    if ($email === '') {
        $errors[] = 'E-posta alanı boş bırakılamaz.';
    }
    if ($password === '') {
        $errors[] = 'Şifre alanı boş bırakılamaz.';
    }

    // ── Kullanıcı Arama & Şifre Doğrulama ───────────────────
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Hem kullanıcı bulunamadı hem de şifre yanlış için
        // aynı mesaj → kullanıcı numaralandırma saldırısını engeller
        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'E-posta veya şifre hatalı.';
        }
    }

    // ── Oturum Aç ────────────────────────────────────────────
    if (empty($errors)) {
        // Session fixation saldırısına karşı ID yenile
        session_regenerate_id(true);

        $_SESSION['user_id']   = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];

        $_SESSION['flash'][] = [
            'type'    => 'success',
            'message' => 'Tekrar hoş geldiniz, ' . $user['name'] . '!',
        ];

        header('Location: index.php');
        exit;
    }
}

$pageTitle = 'Giriş Yap';
require_once 'includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card card-at p-4 p-md-5">

        <!-- Logo -->
        <div class="text-center mb-4">
            <div style="font-size:2.5rem; color:var(--at-coral);">
                <i class="bi bi-send-fill"></i>
            </div>
            <h1 class="h4 fw-700 mt-2 mb-0" style="color:var(--at-dark);">
                AirTransfer'e Giriş Yap
            </h1>
            <p class="text-muted small mt-1">Hesabınıza erişmek için bilgilerinizi giriniz</p>
        </div>

        <hr class="divider-coral">

        <!-- Hata Mesajları -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-at-danger mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php foreach ($errors as $err): ?>
                    <div><?= e($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="login.php" novalidate>

            <!-- E-posta -->
            <div class="mb-3">
                <label for="email" class="form-label fw-600 small">
                    <i class="bi bi-envelope me-1"></i>E-posta
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="ornek@email.com"
                    value="<?= e($oldEmail) ?>"
                    required
                    autofocus
                >
            </div>

            <!-- Şifre -->
            <div class="mb-4">
                <label for="password" class="form-label fw-600 small">
                    <i class="bi bi-lock me-1"></i>Şifre
                </label>
                <div class="input-group">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Şifrenizi giriniz"
                        required
                    >
                    <button
                        class="btn btn-outline-secondary"
                        type="button"
                        id="togglePassword"
                        tabindex="-1"
                        title="Şifreyi göster/gizle"
                    >
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Giriş Butonu -->
            <button type="submit" class="btn btn-coral w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-1"></i>Giriş Yap
            </button>

        </form>

        <hr class="divider-coral mt-4">

        <!-- Test Bilgileri (geliştirme kolaylığı için) -->
        <div class="text-center mb-3">
            <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Test hesabı: <strong>admin@airbnb-transfer.com</strong> / <strong>Test1234</strong>
            </small>
        </div>

        <p class="text-center small mb-0">
            Hesabın yok mu?
            <a href="register.php" style="color:var(--at-coral); font-weight:600;">
                Kayıt Ol
            </a>
        </p>

    </div>
</div>

<script>
// Şifre göster/gizle
document.getElementById('togglePassword').addEventListener('click', function () {
    const pwd  = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
