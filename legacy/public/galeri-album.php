<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT a.*, k.judul AS kegiatan_judul FROM galeri_album a LEFT JOIN kegiatan k ON k.id=a.kegiatan_id WHERE a.id=?');
$stmt->execute([$id]);
$album = $stmt->fetch();
$fotos = [];
if ($album) { $stmt = $pdo->prepare('SELECT * FROM galeri_foto WHERE album_id=? ORDER BY created_at DESC, id DESC'); $stmt->execute([$id]); $fotos = $stmt->fetchAll(); } else { http_response_code(404); }
$pageTitle = $album ? $album['judul'] : 'Album tidak ditemukan';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="section section-paper"><div class="container"><div class="breadcrumb breadcrumb-dark"><a href="<?= osis_e(osis_url('index.php')) ?>">Beranda</a><span>/</span><a href="<?= osis_e(osis_url('public/galeri.php')) ?>">Galeri</a><span>/</span><span>Album</span></div>
<?php if (!$album): ?><div class="empty-state"><span>?</span><h1>Album tidak ditemukan</h1><a class="button button-primary" href="<?= osis_e(osis_url('public/galeri.php')) ?>">Kembali ke galeri</a></div><?php else: ?><div class="album-heading"><div><span class="eyebrow">ALBUM FOTO · <?= osis_e(osis_format_date($album['tanggal'])) ?></span><h1><?= osis_e($album['judul']) ?></h1><p><?= nl2br(osis_e($album['deskripsi'])) ?></p><?php if ($album['kegiatan_judul']): ?><span class="badge badge-soft">Dokumentasi: <?= osis_e($album['kegiatan_judul']) ?></span><?php endif; ?></div><a class="text-link" href="<?= osis_e(osis_url('public/galeri.php')) ?>">← Semua album</a></div>
<?php if ($fotos): ?><div class="photo-grid" data-lightbox-gallery><?php foreach ($fotos as $photo): ?><button class="photo-tile" type="button" data-lightbox-src="<?= osis_e(osis_image_url($photo['file'])) ?>" data-lightbox-caption="<?= osis_e($photo['keterangan']) ?>"><img loading="lazy" src="<?= osis_e(osis_image_url($photo['file'])) ?>" alt="<?= osis_e($photo['keterangan'] ?: $album['judul']) ?>"><span class="photo-tile-caption"><?= osis_e($photo['keterangan'] ?: 'Lihat foto') ?></span></button><?php endforeach; ?></div><?php else: ?><div class="empty-state"><span>▧</span><h3>Belum ada foto di album ini</h3><p>Foto dokumentasi akan ditambahkan segera.</p></div><?php endif; ?><?php endif; ?></div></section>
<div class="lightbox" data-lightbox aria-hidden="true"><button type="button" class="lightbox-close" data-lightbox-close aria-label="Tutup">×</button><button type="button" class="lightbox-prev" data-lightbox-prev aria-label="Foto sebelumnya">‹</button><figure><img src="" alt="" data-lightbox-image><figcaption data-lightbox-caption-output></figcaption></figure><button type="button" class="lightbox-next" data-lightbox-next aria-label="Foto berikutnya">›</button></div>
<script src="<?= osis_e(osis_asset('js/galeri-lightbox.js')) ?>" defer></script>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
