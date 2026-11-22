<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Beranda';
$pengumuman = $pdo->query("SELECT p.*, u.nama AS admin_nama FROM pengumuman p LEFT JOIN users u ON u.id=p.dibuat_oleh ORDER BY (p.penting='ya') DESC, p.created_at DESC LIMIT 3")->fetchAll();
$kegiatan = $pdo->query("SELECT * FROM kegiatan ORDER BY tanggal DESC, id DESC LIMIT 3")->fetchAll();
$jumlahAnggota = (int) $pdo->query("SELECT COUNT(*) FROM anggota WHERE status='aktif'")->fetchColumn();
$jumlahKegiatan = (int) $pdo->query('SELECT COUNT(*) FROM kegiatan')->fetchColumn();
require __DIR__ . '/includes/header.php';
?>
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow"><span class="eyebrow-dot"></span> BERSAMA, KITA BERDAMPAK</span>
            <h1>Ide baik.<br><em>Aksi nyata.</em></h1>
            <p>Temukan kabar terbaru, agenda kegiatan, dan cerita di balik setiap langkah OSIS sekolah kita. Karena perubahan baik dimulai dari kita.</p>
            <div class="hero-actions">
                <a class="button button-light" href="<?= osis_e(osis_url('public/pengumuman.php')) ?>">Jelajahi informasi <span>↗</span></a>
                <a class="button button-ghost" href="<?= osis_e(osis_url('public/pengurus.php')) ?>">Kenali OSIS <span>→</span></a>
            </div>
            <div class="redesign-community"><div class="redesign-avatars" aria-hidden="true"><?php foreach ([1, 3, 2, 5] as $avatar): ?><img src="<?= osis_e(osis_asset('img/avatar-' . $avatar . '.webp')) ?>" alt=""><?php endforeach; ?></div><span><strong>Banyak ide. Satu semangat.</strong><small>Bergerak bareng, berdampak bersama.</small></span></div>
        </div>
        <div class="hero-art redesign-art">
            <div class="redesign-blob" aria-hidden="true"></div><div class="redesign-orbit" aria-hidden="true"></div>
            <figure class="redesign-photo"><img src="<?= osis_e(osis_asset('img/students-hero.webp')) ?>" width="1536" height="1024" alt="Ilustrasi kebersamaan siswa OSIS, dibuat dengan bantuan AI" fetchpriority="high"><figcaption>ILUSTRASI KEBERSAMAAN SISWA · AI</figcaption></figure>
            <div class="redesign-note">Dari siswa,<br><strong>untuk siswa.</strong><span aria-hidden="true">⤵</span></div>
            <span class="redesign-star" aria-hidden="true">✳</span>
            <div class="redesign-sticker"><span aria-hidden="true">✧</span><strong>SATU SEMANGAT.<br>BANYAK CERITA.</strong></div>
            <div class="redesign-heart"><span aria-hidden="true">♡</span><div><strong>Ruang untuk bertumbuh</strong><small>dan jadi versi terbaikmu.</small></div></div>
        </div>
    </div>
    <div class="hero-bottom container"><span>BERKABAR · BERKARYA · BERDAMPAK</span><a href="#informasi">Scroll untuk cerita lainnya ↓</a></div>
</section>
<section class="redesign-stats"><div class="container redesign-stats-grid"><div class="redesign-stat-intro">Hal besar dimulai<br>dari <em>kebersamaan.</em></div><div class="redesign-stat"><span class="redesign-stat-symbol">♧</span><div><strong><?= number_format($jumlahAnggota) ?></strong><small>Anggota aktif</small></div></div><div class="redesign-stat"><span class="redesign-stat-symbol">▦</span><div><strong><?= number_format($jumlahKegiatan) ?></strong><small>Kegiatan & kolaborasi</small></div></div><div class="redesign-stat"><span class="redesign-stat-symbol">♡</span><div><strong>1</strong><small>Semangat bersama</small></div></div></div></section>
<div id="informasi"></div>
<section class="section section-paper"><div class="container"><div class="section-heading"><div><span class="eyebrow">TETAP TERHUBUNG</span><h2>Informasi terbaru<span class="text-accent">.</span></h2><p>Pengumuman penting dan kabar yang perlu kamu tahu.</p></div><a class="text-link" href="<?= osis_e(osis_url('public/pengumuman.php')) ?>">Semua pengumuman <span>↗</span></a></div>
<?php if ($pengumuman): ?><div class="announcement-grid"><?php foreach ($pengumuman as $item): ?><a class="announcement-card" href="<?= osis_e(osis_url('public/pengumuman-detail.php?id=' . (int)$item['id'])) ?>"><div class="card-topline"><span class="mini-date"><?= osis_e(osis_format_date($item['created_at'])) ?></span><?php if ($item['penting'] === 'ya'): ?><span class="badge badge-important">Penting</span><?php else: ?><span class="badge badge-soft">Pengumuman</span><?php endif; ?></div><h3><?= osis_e($item['judul']) ?></h3><p><?= osis_e(mb_strimwidth(strip_tags($item['isi']), 0, 145, '…', 'UTF-8')) ?></p><span class="card-arrow">Baca selengkapnya <b>↗</b></span></a><?php endforeach; ?></div><?php else: ?><div class="empty-state"><span>▤</span><h3>Belum ada pengumuman</h3><p>Informasi baru akan ditampilkan di sini.</p></div><?php endif; ?></div></section>
<section class="section section-cream"><div class="container"><div class="section-heading"><div><span class="eyebrow">KALENDER OSIS</span><h2>Segera dan telah berlangsung<span class="text-accent">.</span></h2><p>Ikuti rangkaian kegiatan dan kontribusi seru dari OSIS.</p></div><a class="text-link" href="<?= osis_e(osis_url('public/kegiatan.php')) ?>">Lihat semua kegiatan <span>↗</span></a></div>
<?php if ($kegiatan): ?><div class="event-grid"><?php foreach ($kegiatan as $i => $event): ?><article class="event-card"><div class="event-image event-image-<?= (int)$i ?>"><?php if ($event['gambar']): ?><img src="<?= osis_e(osis_image_url($event['gambar'])) ?>" alt="<?= osis_e($event['judul']) ?>"><?php else: ?><span class="event-graphic">0<?= $i + 1 ?></span><span class="event-graphic-label">KITA<br>BERGERAK</span><?php endif; ?><span class="event-date-block"><strong><?= osis_e(date('d', strtotime($event['tanggal']))) ?></strong><small><?= osis_e(date('M', strtotime($event['tanggal']))) ?></small></span></div><div class="event-body"><span class="eyebrow">KEGIATAN OSIS</span><h3><?= osis_e($event['judul']) ?></h3><p><?= osis_e(mb_strimwidth(strip_tags($event['deskripsi']), 0, 110, '…', 'UTF-8')) ?></p><div class="event-meta"><span>⌖ <?= osis_e($event['lokasi'] ?: 'Lokasi menyusul') ?></span><span><?= osis_e(osis_format_date($event['tanggal'])) ?></span></div></div></article><?php endforeach; ?></div><?php else: ?><div class="empty-state"><span>◷</span><h3>Agenda sedang disiapkan</h3><p>Silakan kembali lagi untuk melihat kegiatan berikutnya.</p></div><?php endif; ?></div></section>
<section class="callout-section"><div class="container callout-card"><div><span class="eyebrow eyebrow-light">PUNYA CERITA ATAU MASUKAN?</span><h2>Setiap suara<br>punya arti.</h2><p>Kirim laporan atau aspirasi langsung ke pengurus OSIS.</p></div><a href="<?= osis_e(osis_url('auth/login.php')) ?>" class="button button-light">Masuk & kirim aspirasi <span>↗</span></a><span class="callout-mark">O</span></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
