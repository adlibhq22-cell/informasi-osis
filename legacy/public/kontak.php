<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle = 'Kontak';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page-hero page-hero-coral"><div class="container"><span class="eyebrow eyebrow-light">KAMI SIAP MENDENGAR</span><h1>Kontak OSIS<span class="text-accent">.</span></h1><p>Terhubung, berkolaborasi, dan sampaikan ide baikmu.</p><div class="breadcrumb"><a href="<?= osis_e(osis_url('index.php')) ?>">Beranda</a><span>/</span><span>Kontak</span></div></div></section>
<section class="section section-paper"><div class="container contact-layout"><div><span class="eyebrow">RUANG KOLABORASI</span><h2>Ada ide?<br>Mulai dari sini<span class="text-accent">.</span></h2><p class="lead">Untuk aspirasi, masukan, atau pertanyaan terkait kegiatan, kamu bisa mengirim laporan lewat akun anggota.</p><a class="button button-primary" href="<?= osis_e(osis_url('auth/login.php')) ?>">Masuk & kirim laporan <span>↗</span></a></div><div class="contact-card"><span class="contact-icon">✳</span><h3>Temukan pengurus</h3><p>Hubungi pengurus OSIS melalui kanal komunikasi resmi sekolah atau sampaikan langsung saat jam kegiatan.</p><a class="text-link" href="<?= osis_e(osis_url('public/pengurus.php')) ?>">Lihat daftar pengurus <span>↗</span></a><div class="contact-divider"></div><span class="eyebrow">ALAMAT</span><p>Silakan hubungi sekretariat OSIS di lingkungan sekolah.</p></div></div></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
