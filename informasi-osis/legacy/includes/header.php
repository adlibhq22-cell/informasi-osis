<?php
$pageTitle = isset($pageTitle) ? $pageTitle : 'Informasi OSIS';
$flash = osis_get_flash();
$user = osis_current_user();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f5ef">
    <script>(function(){try{var t=localStorage.getItem('osis-theme');var d=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches;document.documentElement.setAttribute('data-theme',t==='dark'||t==='light'?t:(d?'dark':'light'));}catch(e){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title><?= osis_e($pageTitle) ?> · Informasi OSIS</title>
    <meta name="description" content="OSIS.INFO — Ruang informasi siswa. Berkabar, berkarya, dan berdampak bersama.">
    <link rel="icon" type="image/svg+xml" href="<?= osis_e(osis_asset('img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;550;600;650;700;750;800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= osis_e(osis_asset('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= osis_e(osis_asset('css/responsive.css')) ?>">
    <link rel="stylesheet" href="<?= osis_e(osis_asset('css/redesign.css')) ?>">
    <link rel="stylesheet" href="<?= osis_e(osis_asset('css/tasks.css')) ?>">
    <link rel="stylesheet" href="<?= osis_e(osis_asset('css/theme.css')) ?>">
    <link rel="stylesheet" href="<?= osis_e(osis_asset('css/polish.css')) ?>">
</head>
<body data-admin-login-url="<?= osis_e(osis_url('admin/login.php')) ?>">
<a class="skip-link" href="#main-content">Langsung ke konten</a>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= osis_e(osis_url('index.php')) ?>"><span class="brand-mark" aria-hidden="true">✳</span><span>OSIS<span class="brand-light">.INFO</span><small>RUANG INFORMASI SISWA</small></span></a>
        <button class="nav-toggle" type="button" aria-label="Buka menu" aria-expanded="false" data-nav-toggle><span></span><span></span><span></span></button>
        <nav class="main-nav" data-nav>
            <a href="<?= osis_e(osis_url('index.php')) ?>">Beranda</a>
            <a href="<?= osis_e(osis_url('public/pengurus.php')) ?>">Pengurus</a>
            <a href="<?= osis_e(osis_url('public/anggota.php')) ?>">Anggota</a>
            <a href="<?= osis_e(osis_url('public/kegiatan.php')) ?>">Kegiatan</a>
            <a href="<?= osis_e(osis_url('public/pengumuman.php')) ?>">Pengumuman</a>
            <a href="<?= osis_e(osis_url('public/galeri.php')) ?>">Galeri</a>
            <a href="<?= osis_e(osis_url('public/kontak.php')) ?>">Kontak</a>
            <button class="theme-toggle theme-toggle-public" type="button" data-theme-toggle aria-label="Aktifkan mode gelap" aria-pressed="false"><span class="theme-toggle-icon" data-theme-icon aria-hidden="true">☾</span><span class="theme-toggle-label" data-theme-label>Mode gelap</span></button>
            <?php if ($user && $user['role'] === 'admin'): ?>
                <a class="nav-login" href="<?= osis_e(osis_url('admin/dashboard.php')) ?>">Panel admin <span>↗</span></a>
            <?php elseif ($user && $user['role'] === 'pengurus'): ?>
                <a class="nav-login" href="<?= osis_e(osis_url('pengurus/dashboard.php')) ?>">Dashboard pengurus <span>↗</span></a>
                <form class="nav-logout" method="post" action="<?= osis_e(osis_url('auth/logout.php')) ?>"><?= osis_csrf_input() ?><button type="submit">Keluar</button></form>
            <?php elseif ($user): ?>
                <a class="nav-login" href="<?= osis_e(osis_url('anggota/dashboard.php')) ?>">Akun saya <span>↗</span></a>
                <form class="nav-logout" method="post" action="<?= osis_e(osis_url('auth/logout.php')) ?>"><?= osis_csrf_input() ?><button type="submit">Keluar</button></form>
            <?php else: ?>
                <a class="nav-login" href="<?= osis_e(osis_url('auth/login.php')) ?>">Masuk <span>↗</span></a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main id="main-content">
<?php if ($flash): ?>
    <div class="container"><div class="alert alert-<?= osis_e($flash['type']) ?>" role="status"><?= osis_e($flash['message']) ?></div></div>
<?php endif; ?>
