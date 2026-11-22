<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$adminLogin = isset($_GET['admin']) && $_GET['admin'] === '1';
if (osis_current_user() && (!$adminLogin || osis_current_user()['role'] === 'admin')) {
    osis_redirect(osis_dashboard_path_for_role(osis_current_user()['role']));
}
$pageTitle = $adminLogin ? 'Masuk Admin' : 'Masuk';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    osis_verify_csrf();
    $identity = osis_post_string('identity', 120);
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE (username=? OR email=?) AND aktif=1 LIMIT 1');
    $stmt->execute([$identity, $identity]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        if ($adminLogin && $user['role'] !== 'admin') {
            $error = 'Akun ini bukan akun administrator. Silakan gunakan login umum.';
        } else {
            osis_login_user($user);
            osis_set_flash('success', 'Selamat datang kembali, ' . $user['nama'] . '.');
            osis_redirect(osis_dashboard_path_for_role($user['role']));
        }
    } else {
        $error = 'Nama pengguna/email atau kata sandi tidak sesuai.';
    }
}
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="auth-section"><div class="container auth-layout"><div class="auth-intro"><span class="eyebrow eyebrow-light"><?= $adminLogin ? 'PANEL ADMINISTRATOR' : 'AREA ANGGOTA, PENGURUS & ADMIN' ?></span><h1><?= $adminLogin ? 'Kelola OSIS,<br><em>dari satu tempat.</em>' : 'Halo lagi,<br><em>selamat datang.</em>' ?></h1><p><?= $adminLogin ? 'Masuk ke panel admin untuk mengelola informasi, anggota, kegiatan, dan tugas OSIS.' : 'Masuk untuk melihat tugas, menyampaikan dokumentasi, atau mengelola informasi OSIS.' ?></p><div class="auth-note"><span>✳</span><span><?= $adminLogin ? 'Ruang kendali yang aman untuk informasi OSIS.' : 'Ruang yang aman untuk bertukar kabar dan gagasan.' ?></span></div></div><div class="auth-card"><span class="eyebrow"><?= $adminLogin ? 'AKUN ADMIN' : 'AKUN OSIS' ?></span><h2><?= $adminLogin ? 'Masuk ke panel admin' : 'Masuk ke akun' ?></h2><p><?= $adminLogin ? 'Gunakan username atau email akun administrator.' : 'Gunakan username atau email yang terdaftar.' ?></p><?php if ($error): ?><div class="alert alert-error" role="alert"><?= osis_e($error) ?></div><?php endif; ?><form method="post" action="<?= osis_e(osis_url('auth/login.php' . ($adminLogin ? '?admin=1' : ''))) ?>" data-validate-form><?= osis_csrf_input() ?><label for="identity">Username atau email</label><input id="identity" name="identity" type="text" autocomplete="username" required maxlength="120" value="<?= osis_e(isset($_POST['identity']) ? $_POST['identity'] : '') ?>"><label for="password">Kata sandi</label><div class="password-wrap"><input id="password" name="password" type="password" autocomplete="current-password" required><button type="button" class="password-toggle" data-password-toggle="#password">Lihat</button></div><button class="button button-primary button-full" type="submit">Masuk <span>↗</span></button></form><div class="auth-footnote"><?php if ($adminLogin): ?>Khusus akun administrator. <a href="<?= osis_e(osis_url('auth/login.php')) ?>">Kembali ke login umum</a>.<?php else: ?>Belum punya akun? Minta admin OSIS membuatkan akun anggota atau pengurus.<p class="auth-admin-shortcut"><a href="<?= osis_e(osis_url('admin/login.php')) ?>">Login admin <kbd>Ctrl</kbd> + <kbd>M</kbd> <span aria-hidden="true">↗</span></a></p><?php endif; ?></div></div></div></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
