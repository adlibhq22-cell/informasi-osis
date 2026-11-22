<?php
require_once dirname(__DIR__) . '/includes/task_functions.php';
osis_require_role('pengurus');
$pageTitle = 'Tugas pengurus';
$user = osis_current_user();
$memberId = osis_current_member_id();
$assigned = [];
$created = [];
if ($memberId) {
    $sql = osis_task_member_task_query() . ' WHERE tp.anggota_id=? ORDER BY (t.status=\'aktif\') DESC, t.deadline IS NULL, t.deadline ASC, t.created_at DESC';
    $stmt = $pdo->prepare($sql); $stmt->execute([$memberId]); $assigned = $stmt->fetchAll();
}
$sql = "SELECT t.*,u.nama AS pembuat_nama,(SELECT COUNT(*) FROM tugas_penerima p WHERE p.tugas_id=t.id) AS penerima_total,(SELECT COALESCE(SUM(p.status='terkirim'),0) FROM tugas_penerima p WHERE p.tugas_id=t.id) AS penerima_terkirim FROM tugas t JOIN users u ON u.id=t.dibuat_oleh WHERE t.dibuat_oleh=? ORDER BY (t.status='aktif') DESC,t.created_at DESC";
$stmt = $pdo->prepare($sql); $stmt->execute([$user['id']]); $created = $stmt->fetchAll();
$pageTitle='Tugas pengurus';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="section section-paper task-page"><div class="container task-container"><div class="task-page-heading task-heading-row"><div><span class="eyebrow">RUANG KERJA PENGURUS</span><h1>Tugas<span class="text-accent">.</span></h1><p>Pantau tugas yang kamu terima dan kelola penugasan yang kamu buat.</p></div><a class="button button-primary" href="<?= osis_e(osis_url('pengurus/tugas-buat.php')) ?>">＋ Buat tugas</a></div>
<?php if (!$memberId): ?><div class="alert alert-warning">Akun pengurus belum tertaut ke data anggota. Hubungi admin OSIS untuk menautkannya.</div><?php endif; ?>
<section class="task-section-block"><div class="task-section-heading"><div><span class="eyebrow">PENUGASAN UNTUK SAYA</span><h2>Tugas yang diterima</h2></div><span class="task-count-pill"><?= count($assigned) ?> tugas</span></div><?php if ($assigned): ?><div class="task-card-grid"><?php foreach ($assigned as $task) echo osis_task_card_html($task,true); ?></div><?php else: ?><div class="task-empty-state compact"><h3>Belum ada penugasan</h3><p>Tugas yang dibagikan kepadamu akan tampil di sini.</p></div><?php endif; ?></section>
<section class="task-section-block"><div class="task-section-heading"><div><span class="eyebrow">TUGAS YANG SAYA BUAT</span><h2>Penugasan dari saya</h2></div><span class="task-count-pill"><?= count($created) ?> tugas</span></div><?php if ($created): ?><div class="task-card-grid"><?php foreach ($created as $task) echo osis_task_card_html($task); ?></div><?php else: ?><div class="task-empty-state compact"><h3>Belum membuat tugas</h3><p>Gunakan tombol “Buat tugas” untuk membagikan pekerjaan kepada anggota.</p></div><?php endif; ?></section></div></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
