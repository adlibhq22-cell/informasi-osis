<?php
$pageTitle = isset($pageTitle) ? $pageTitle : 'Panel Admin';
$flash = osis_get_flash();
$user = osis_current_user();
$currentPath = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
function osis_admin_nav_active($part, $currentPath) { return strpos($currentPath, $part) !== false ? ' is-active' : ''; }
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#203b41">
    <script>(function(){try{var t=localStorage.getItem('osis-theme');var d=window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches;document.documentElement.setAttribute('data-theme',t==='dark'||t==='light'?t:(d?'dark':'light'));}catch(e){document.documentElement.setAttribute('data-theme','light');}})();</script>
    <title><?= osis_e($pageTitle) ?> · Admin OSIS</title>
    <link rel="icon" type="image/svg+xml" href="<?= osis_e(osis_asset('img/favicon.svg')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;550;600;650;700;750;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= osis_e(osis_asset('css/style.css')) ?>"><link rel="stylesheet" href="<?= osis_e(osis_asset('css/admin.css')) ?>"><link rel="stylesheet" href="<?= osis_e(osis_asset('css/responsive.css')) ?>">
    <link rel="stylesheet" href="<?= osis_e(osis_asset('css/admin-polish.css')) ?>">
    <link rel="stylesheet" href="<?= osis_e(osis_asset('css/tasks.css')) ?>">
    <link rel="stylesheet" href="<?= osis_e(osis_asset('css/theme.css')) ?>">
    <link rel="stylesheet" href="<?= osis_e(osis_asset('css/polish.css')) ?>">
</head>
<body class="admin-body" data-admin-login-url="<?= osis_e(osis_url('admin/login.php')) ?>">
<div class="admin-shell">
    <aside class="admin-sidebar" data-admin-sidebar>
        <a class="brand admin-brand" href="<?= osis_e(osis_url('admin/dashboard.php')) ?>"><span class="brand-mark">✳</span><span>OSIS<span class="brand-light">.INFO</span><small>ADMIN WORKSPACE</small></span></a>
        <div class="sidebar-label">MENU UTAMA</div>
        <nav class="admin-nav">
            <a class="<?= osis_admin_nav_active('/admin/dashboard.php', $currentPath) ?>" href="<?= osis_e(osis_url('admin/dashboard.php')) ?>"><span>⌂</span> Dashboard</a>
            <a class="<?= osis_admin_nav_active('/admin/pengurus/', $currentPath) ?>" href="<?= osis_e(osis_url('admin/pengurus/index.php')) ?>"><span>♙</span> Pengurus</a>
            <a class="<?= osis_admin_nav_active('/admin/anggota/', $currentPath) ?>" href="<?= osis_e(osis_url('admin/anggota/index.php')) ?>"><span>♧</span> Anggota</a>
            <a class="<?= osis_admin_nav_active('/admin/tugas/', $currentPath) ?>" href="<?= osis_e(osis_url('admin/tugas/index.php')) ?>"><span>☷</span> Tugas</a>
            <a class="<?= osis_admin_nav_active('/admin/kegiatan/', $currentPath) ?>" href="<?= osis_e(osis_url('admin/kegiatan/index.php')) ?>"><span>◷</span> Kegiatan</a>
            <a class="<?= osis_admin_nav_active('/admin/pengumuman/', $currentPath) ?>" href="<?= osis_e(osis_url('admin/pengumuman/index.php')) ?>"><span>▤</span> Pengumuman</a>
            <a class="<?= osis_admin_nav_active('/admin/galeri/', $currentPath) ?>" href="<?= osis_e(osis_url('admin/galeri/index.php')) ?>"><span>▧</span> Galeri</a>
            <a class="<?= osis_admin_nav_active('/admin/absensi/', $currentPath) ?>" href="<?= osis_e(osis_url('admin/absensi/index.php')) ?>"><span>✓</span> Absensi</a>
            <a class="<?= osis_admin_nav_active('/admin/laporan/', $currentPath) ?>" href="<?= osis_e(osis_url('admin/laporan/index.php')) ?>"><span>▱</span> Laporan aspirasi</a>
            <a class="<?= osis_admin_nav_active('/admin/log.php', $currentPath) ?>" href="<?= osis_e(osis_url('admin/log.php')) ?>"><span>◴</span> Log aktivitas</a>
        </nav>
        <div class="sidebar-bottom"><a href="<?= osis_e(osis_url('index.php')) ?>" target="_blank" rel="noopener">↗ Lihat situs</a><div class="sidebar-user"><span class="avatar avatar-small"><?= osis_e(mb_strtoupper(mb_substr($user['nama'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span><div><strong><?= osis_e($user['nama']) ?></strong><small>Administrator</small></div></div>
            <form method="post" action="<?= osis_e(osis_url('auth/logout.php')) ?>"><?= osis_csrf_input() ?><button class="logout-link" type="submit">Keluar ↗</button></form>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar"><button class="admin-menu-toggle" type="button" data-admin-toggle aria-label="Buka menu">☰</button><div class="admin-topbar-title"><span class="eyebrow">INFORMASI OSIS</span><h1><?= osis_e($pageTitle) ?></h1></div><div class="admin-topbar-actions"><button class="theme-toggle theme-toggle-admin" type="button" data-theme-toggle aria-label="Aktifkan mode gelap" aria-pressed="false"><span class="theme-toggle-icon" data-theme-icon aria-hidden="true">☾</span><span class="theme-toggle-label" data-theme-label>Mode gelap</span></button><a class="topbar-view" href="<?= osis_e(osis_url('index.php')) ?>" target="_blank" rel="noopener">Lihat situs ↗</a></div></header>
        <main class="admin-content">
            <?php if ($flash): ?><div class="alert alert-<?= osis_e($flash['type']) ?>" role="status"><?= osis_e($flash['message']) ?></div><?php endif; ?>
