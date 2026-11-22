<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$pageTitle = 'Anggota';
$search = trim(isset($_GET['q']) ? (string) $_GET['q'] : '');
$sql = "SELECT id,nama,nis,kelas,foto,status FROM anggota WHERE status='aktif'";
$params = [];
if ($search !== '') { $sql .= ' AND (nama LIKE ? OR kelas LIKE ? OR nis LIKE ?)'; $like = '%' . $search . '%'; $params = [$like, $like, $like]; }
$sql .= ' ORDER BY nama ASC';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $members = $stmt->fetchAll();
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><span class="eyebrow eyebrow-light">SATU TIM, BANYAK CERITA</span><h1>Anggota OSIS<span class="text-accent">.</span></h1><p>Temukan teman-teman yang terlibat dalam perjalanan OSIS.</p><div class="breadcrumb"><a href="<?= osis_e(osis_url('index.php')) ?>">Beranda</a><span>/</span><span>Anggota</span></div></div></section>
<section class="section section-paper"><div class="container"><div class="section-heading"><div><span class="eyebrow">KOMUNITAS KITA</span><h2>Kenali anggota<span class="text-accent">.</span></h2></div><form class="search-form" method="get"><label class="sr-only" for="search-member">Cari nama atau kelas</label><input id="search-member" name="q" value="<?= osis_e($search) ?>" placeholder="Cari nama atau kelas"><button type="submit" aria-label="Cari">⌕</button></form></div>
<?php if ($members): ?><div class="people-grid people-grid-members"><?php foreach ($members as $i => $member): ?><article class="person-card member-card"><button class="person-photo person-photo-<?= $i % 4 ?> member-modal-trigger" type="button" data-member-modal="<?= (int)$member['id'] ?>" aria-label="Lihat profil <?= osis_e($member['nama']) ?>"><?php if ($member['foto']): ?><img src="<?= osis_e(osis_image_url($member['foto'])) ?>" alt="<?= osis_e($member['nama']) ?>"><?php else: ?><span><?= osis_e(mb_strtoupper(mb_substr($member['nama'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span><?php endif; ?></button><div class="person-info"><span class="eyebrow">ANGGOTA OSIS</span><h3><a href="<?= osis_e(osis_url('public/anggota-detail.php?id=' . (int)$member['id'])) ?>"><?= osis_e($member['nama']) ?></a></h3><p><?= osis_e($member['kelas'] ?: 'Siswa aktif') ?></p><a class="text-link" href="<?= osis_e(osis_url('public/anggota-detail.php?id=' . (int)$member['id'])) ?>">Lihat profil <span>↗</span></a></div></article><?php endforeach; ?></div><?php else: ?><div class="empty-state"><span>♧</span><h3><?= $search ? 'Tidak ada anggota yang cocok' : 'Belum ada data anggota' ?></h3><p><?= $search ? 'Coba kata kunci lain.' : 'Data anggota akan ditampilkan di sini.' ?></p></div><?php endif; ?></div></section>
<div class="member-modal" data-member-modal-root aria-hidden="true"><div class="member-modal-backdrop" data-member-close></div><section class="member-modal-panel" role="dialog" aria-modal="true" aria-labelledby="member-modal-name"><button class="member-modal-close" type="button" data-member-close data-member-modal-close-focus aria-label="Tutup">×</button><div data-member-modal-content><p>Memuat profil...</p></div></section></div>
<script src="<?= osis_e(osis_asset('js/anggota-modal.js')) ?>" defer></script>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
