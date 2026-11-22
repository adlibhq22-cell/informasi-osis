<?php
require_once dirname(__DIR__) . '/includes/task_functions.php';
osis_require_role('pengurus');
$pageTitle = 'Dashboard pengurus';
$user = osis_current_user();
$memberId = osis_current_member_id();
$assignedCount = 0; $createdCount = 0; $openReports = 0; $attendance = ['hadir'=>0,'izin'=>0,'sakit'=>0,'alpa'=>0]; $recentTasks = [];
if ($memberId) {
    $stmt=$pdo->prepare("SELECT COUNT(*) FROM tugas_penerima p JOIN tugas t ON t.id=p.tugas_id WHERE p.anggota_id=? AND t.status='aktif'");$stmt->execute([$memberId]);$assignedCount=(int)$stmt->fetchColumn();
    $stmt=$pdo->prepare('SELECT status,COUNT(*) AS total FROM absensi WHERE anggota_id=? GROUP BY status');$stmt->execute([$memberId]);foreach($stmt->fetchAll() as $row){$attendance[$row['status']]=(int)$row['total'];}
    $stmt=$pdo->prepare("SELECT COUNT(*) FROM laporan WHERE anggota_id=? AND status IN ('baru','diproses')");$stmt->execute([$memberId]);$openReports=(int)$stmt->fetchColumn();
    $stmt=$pdo->prepare(osis_task_member_task_query()." WHERE tp.anggota_id=? AND t.status='aktif' ORDER BY t.deadline IS NULL,t.deadline ASC,t.created_at DESC LIMIT 3");$stmt->execute([$memberId]);$recentTasks=$stmt->fetchAll();
}
$stmt=$pdo->prepare("SELECT COUNT(*) FROM tugas WHERE dibuat_oleh=? AND status='aktif'");$stmt->execute([$user['id']]);$createdCount=(int)$stmt->fetchColumn();
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="section section-paper task-page"><div class="container task-container"><div class="officer-welcome"><div><span class="eyebrow">DASHBOARD PENGURUS</span><h1>Hai, <?= osis_e($user['nama']) ?><span class="text-accent">.</span></h1><p>Kelola penugasan tim, pantau kiriman, dan akses laporan organisasi.</p></div><a class="button button-primary" href="<?= osis_e(osis_url('pengurus/tugas-buat.php')) ?>">＋ Buat tugas</a></div>
<?php if (!$memberId): ?><div class="alert alert-warning">Akun ini belum ditautkan ke data anggota. Hubungi admin OSIS agar kamu bisa menerima tugas, melihat absensi, dan mengirim laporan.</div><?php endif; ?>
<div class="officer-stats"><a href="<?= osis_e(osis_url('pengurus/tugas.php')) ?>"><span class="eyebrow">TUGAS DITERIMA</span><strong><?= $assignedCount ?></strong><small>Tugas aktif untukmu</small></a><a href="<?= osis_e(osis_url('pengurus/tugas.php')) ?>"><span class="eyebrow">TUGAS DIBUAT</span><strong><?= $createdCount ?></strong><small>Penugasan yang kamu kelola</small></a><a href="<?= osis_e(osis_url('anggota/laporan-saya.php')) ?>"><span class="eyebrow">LAPORAN TERBUKA</span><strong><?= $openReports ?></strong><small>Aspirasi menunggu tindak lanjut</small></a><a href="<?= osis_e(osis_url('anggota/absensi-saya.php')) ?>"><span class="eyebrow">KEHADIRAN</span><strong><?= $attendance['hadir'] ?></strong><small>Sesi dengan status hadir</small></a></div>
<div class="officer-dashboard-grid"><section class="task-panel"><div class="task-section-heading"><div><span class="eyebrow">PRIORITAS SAYA</span><h2>Tugas aktif</h2></div><a class="text-link" href="<?= osis_e(osis_url('pengurus/tugas.php')) ?>">Semua tugas ↗</a></div><?php if ($recentTasks): ?><div class="task-card-grid officer-recent-tasks"><?php foreach ($recentTasks as $task) echo osis_task_card_html($task,true); ?></div><?php else: ?><div class="task-empty-state compact"><p>Belum ada tugas aktif yang diberikan kepadamu.</p><a class="text-link" href="<?= osis_e(osis_url('pengurus/tugas.php')) ?>">Buka daftar tugas ↗</a></div><?php endif; ?></section>
<section class="officer-quick-panel"><span class="eyebrow">AKSES CEPAT</span><h2>Ruang kerja</h2><a href="<?= osis_e(osis_url('pengurus/tugas.php')) ?>"><span>☷</span><span><strong>Tugas saya</strong><small>Kirim progres dan dokumentasi</small></span><b>↗</b></a><a href="<?= osis_e(osis_url('anggota/laporan-kirim.php')) ?>"><span>＋</span><span><strong>Kirim laporan</strong><small>Sampaikan aspirasi ke OSIS</small></span><b>↗</b></a><a href="<?= osis_e(osis_url('anggota/absensi-saya.php')) ?>"><span>✓</span><span><strong>Rekap kehadiran</strong><small>Riwayat absensi milikmu</small></span><b>↗</b></a><a href="<?= osis_e(osis_url('anggota/profil.php')) ?>"><span>♙</span><span><strong>Profil saya</strong><small>Perbarui data akun</small></span><b>↗</b></a></section></div></div></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
