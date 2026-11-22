<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT p.*, u.nama AS admin_nama FROM pengumuman p LEFT JOIN users u ON u.id=p.dibuat_oleh WHERE p.id=?');
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) { http_response_code(404); }
$pageTitle = $item ? $item['judul'] : 'Pengumuman tidak ditemukan';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="section section-paper"><div class="container narrow-container">
<?php if (!$item): ?><div class="empty-state"><span>?</span><h1>Pengumuman tidak ditemukan</h1><p>Informasi ini mungkin sudah dihapus.</p><a class="button button-primary" href="<?= osis_e(osis_url('public/pengumuman.php')) ?>">Kembali ke pengumuman</a></div><?php else: ?><div class="breadcrumb breadcrumb-dark"><a href="<?= osis_e(osis_url('index.php')) ?>">Beranda</a><span>/</span><a href="<?= osis_e(osis_url('public/pengumuman.php')) ?>">Pengumuman</a><span>/</span><span>Detail</span></div><article class="article-detail"><div class="badge-line"><?php if ($item['penting'] === 'ya'): ?><span class="badge badge-important">Penting</span><?php endif; ?><span class="mini-date">Diterbitkan <?= osis_e(osis_format_date($item['created_at'])) ?></span></div><h1><?= osis_e($item['judul']) ?></h1><div class="article-byline">Oleh <?= osis_e($item['admin_nama'] ?: 'Admin OSIS') ?></div><div class="article-body"><?= nl2br(osis_e($item['isi'])) ?></div><a class="text-link" href="<?= osis_e(osis_url('public/pengumuman.php')) ?>">← Kembali ke semua pengumuman</a></article><?php endif; ?></div></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
