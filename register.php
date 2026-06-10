<?php
// register.php
require_once 'config.php';
redirectIfLoggedIn(); // Zaten giriş yapmışsa index'e yönlendir

$errors = [];
$old    = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Girdi Temizleme ──────────────────────────────────────
    $name     = trim($_POST['name']  ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password']   ?? '';
    $confirm  = $_POST['confirm']    ?? '';

    $old = ['name' => $name, 'email' => $email];

    // ── Doğrulama ────────────────────────────────────────────
    if ($name === '') {
        $errors[] = 'Ad Soyad alanı boş bırakılamaz.';
    } elseif (mb_strlen($name) < 2) {
        $errors[] = 'Ad Soyad en az 2 karakter olmalıdır.';
    }

    if ($email === '') {
        $errors[] = 'E-posta alanı boş bırakılamaz.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    }

    if ($password === '') {
        $errors[] = 'Şifre alanı boş bırakılamaz.';
    } elseif (mb_strlen($password) < 6) {
        $errors[] = 'Şifre en az 6 karakter olmalıdır.';
    }

    if ($password !== $confirm) {
        $errors[] = 'Şifreler eşleşmiyor.';
    }

    // ── E-posta Benzersizlik Kontrolü ────────────────────────
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Bu e-posta adresi zaten kayıtlı.';
        }
    }

    // ── Kayıt ────────────────────────────────────────────────
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, password) VALUES (?, ?, ?)'
        );
        $stmt->execute([$name, $email, $hashed]);

        // Otomatik giriş yaptır
        $userId = $pdo->lastInsertId();
        $_SESSION['user_id']   = (int)$userId;
        $_SESSION['user_name'] = $name;

        $_SESSION['flash'][] = [
            'type'    => 'success',
            'message' => 'Hoş geldiniz, ' . $name . '! Hesabınız başarıyla oluşturuldu.',
        ];

        header('Location: index.php');
        exit;
    }
}

$pageTitle = 'Kayıt Ol';
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
                AirTransfer'e Katıl
            </h1>
            <p class="text-muted small mt-1">Yeni bir hesap oluştur</p>
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
        <form method="POST" action="register.php" novalidate>

            <!-- Ad Soyad -->
            <div class="mb-3">
                <label for="name" class="form-label fw-600 small">
                    <i class="bi bi-person me-1"></i>Ad Soyad
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    placeholder="Adınızı ve soyadınızı giriniz"
                    value="<?= e($old['name']) ?>"
                    required
                    autofocus
                >
            </div>

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
                    value="<?= e($old['email']) ?>"
                    required
                >
            </div>

            <!-- Şifre -->
            <div class="mb-3">
                <label for="password" class="form-label fw-600 small">
                    <i class="bi bi-lock me-1"></i>Şifre
                </label>
                <div class="input-group">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="En az 6 karakter"
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

            <!-- Şifre Tekrar -->
            <div class="mb-4">
                <label for="confirm" class="form-label fw-600 small">
                    <i class="bi bi-lock-fill me-1"></i>Şifre Tekrar
                </label>
                <input
                    type="password"
                    id="confirm"
                    name="confirm"
                    class="form-control"
                    placeholder="Şifrenizi tekrar giriniz"
                    required
                >
            </div>

            <!-- Kayıt Butonu -->
            <button type="submit" class="btn btn-coral w-100 py-2">
                <i class="bi bi-person-check me-1"></i>Hesap Oluştur
            </button>

        </form>

        <hr class="divider-coral mt-4">

        <p class="text-center small mb-0">
            Zaten hesabın var mı?
            <a href="login.php" style="color:var(--at-coral); font-weight:600;">
                Giriş Yap
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
