<?php
require_once dirname(__DIR__) . '/includes/functions.php';
osis_require_any_role(['anggota','pengurus']);
$pageTitle = 'Absensi saya';
$stmt = $pdo->prepare('SELECT id FROM anggota WHERE user_id=? LIMIT 1'); $stmt->execute([osis_current_user()['id']]); $member = $stmt->fetch();
$counts = ['hadir'=>0,'izin'=>0,'sakit'=>0,'alpa'=>0]; $items=[];
if ($member) {
    $stmt=$pdo->prepare('SELECT status,COUNT(*) total FROM absensi WHERE anggota_id=? GROUP BY status'); $stmt->execute([$member['id']]); foreach($stmt->fetchAll() as $r){$counts[$r['status']]=(int)$r['total'];}
    $stmt=$pdo->prepare('SELECT a.status,a.keterangan,s.judul,s.tanggal,s.keterangan AS keterangan_sesi FROM absensi a JOIN absensi_sesi s ON s.id=a.sesi_id WHERE a.anggota_id=? ORDER BY s.tanggal DESC,s.id DESC'); $stmt->execute([$member['id']]); $items=$stmt->fetchAll();
}
$total=array_sum($counts); $percent=$total ? round($counts['hadir']/$total*100) : 0;
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="section section-paper"><div class="container member-page"><div class="member-page-heading"><a class="back-link" href="<?= osis_e(osis_url('anggota/dashboard.php')) ?>">← Dashboard</a><span class="eyebrow">REKAP KEHADIRAN</span><h1>Absensi saya<span class="text-accent">.</span></h1><p>Catatan kehadiranmu pada rapat dan pertemuan OSIS.</p></div><?php if (!$member): ?><div class="alert alert-warning">Akunmu belum terhubung dengan data anggota. Hubungi admin OSIS.</div><?php else: ?><div class="attendance-summary"><div class="attendance-total"><span class="eyebrow">PERSENTASE HADIR</span><strong><?= $percent ?><small>%</small></strong><span><?= $counts['hadir'] ?> dari <?= $total ?> sesi tercatat</span></div><div class="attendance-counts"><?php foreach (['hadir'=>'Hadir','izin'=>'Izin','sakit'=>'Sakit','alpa'=>'Alpa'] as $key=>$label): ?><div class="attendance-count attendance-<?= $key ?>"><span><?= osis_e($label) ?></span><strong><?= $counts[$key] ?></strong></div><?php endforeach; ?></div></div><div class="panel-card table-card"><div class="panel-heading"><div><span class="eyebrow">RIWAYAT SESI</span><h2>Catatan kehadiran</h2></div><span class="muted-copy">Rekap bersifat hanya-baca</span></div><?php if ($items): ?><div class="table-scroll"><table class="data-table"><thead><tr><th>Tanggal</th><th>Sesi</th><th>Status</th><th>Keterangan</th></tr></thead><tbody><?php foreach($items as $item): ?><tr><td><?= osis_e(osis_format_date($item['tanggal'])) ?></td><td><strong><?= osis_e($item['judul']) ?></strong></td><td><span class="badge badge-status badge-<?= osis_e($item['status']) ?>"><?= osis_e(ucfirst($item['status'])) ?></span></td><td><?= osis_e($item['keterangan'] ?: '—') ?></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><div class="empty-inline">Belum ada catatan kehadiran.</div><?php endif; ?></div><?php endif; ?></div></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
