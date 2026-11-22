<?php
require_once dirname(__DIR__) . '/includes/functions.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT id,nama,kelas,foto,status FROM anggota WHERE id=? AND status='aktif'");
$stmt->execute([$id]); $member = $stmt->fetch();
if (!$member) { http_response_code(404); }
$pageTitle = $member ? $member['nama'] : 'Anggota tidak ditemukan';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="section section-paper"><div class="container narrow-container"><div class="breadcrumb breadcrumb-dark"><a href="<?= osis_e(osis_url('index.php')) ?>">Beranda</a><span>/</span><a href="<?= osis_e(osis_url('public/anggota.php')) ?>">Anggota</a><span>/</span><span>Profil</span></div><?php if (!$member): ?><div class="empty-state"><h1>Anggota tidak ditemukan</h1><a class="button button-primary" href="<?= osis_e(osis_url('public/anggota.php')) ?>">Kembali ke anggota</a></div><?php else: ?><div class="profile-detail"><div class="profile-detail-photo"><?php if ($member['foto']): ?><img src="<?= osis_e(osis_image_url($member['foto'])) ?>" alt="<?= osis_e($member['nama']) ?>"><?php else: ?><span><?= osis_e(mb_strtoupper(mb_substr($member['nama'], 0, 1, 'UTF-8'), 'UTF-8')) ?></span><?php endif; ?></div><div><span class="eyebrow">ANGGOTA OSIS</span><h1><?= osis_e($member['nama']) ?></h1><p class="profile-class"><?= osis_e($member['kelas'] ?: 'Siswa aktif') ?></p><div class="profile-public-note">Bagian dari komunitas aktif OSIS.</div><a class="text-link" href="<?= osis_e(osis_url('public/anggota.php')) ?>">← Kembali ke daftar anggota</a></div></div><?php endif; ?></div></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
