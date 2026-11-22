<?php
require_once dirname(__DIR__) . '/includes/task_functions.php';
osis_require_role('anggota');
$pageTitle = 'Tugas saya';
$memberId = osis_current_member_id();
$items = [];
if ($memberId) {
    $sql = osis_task_member_task_query() . ' WHERE tp.anggota_id=? ORDER BY (t.status=\'aktif\') DESC,t.deadline IS NULL,t.deadline ASC,t.created_at DESC';
    $stmt = $pdo->prepare($sql); $stmt->execute([$memberId]); $items = $stmt->fetchAll();
}
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="section section-paper task-page"><div class="container task-container"><div class="task-page-heading"><span class="eyebrow">RUANG KERJA ANGGOTA</span><h1>Tugas saya<span class="text-accent">.</span></h1><p>Lihat instruksi, tenggat, dan kirim laporan beserta dokumentasi untuk tugas yang diberikan.</p></div><?php if (!$memberId): ?><div class="alert alert-warning">Akunmu belum tertaut ke data anggota. Silakan hubungi admin OSIS.</div><?php elseif ($items): ?><div class="task-card-grid"><?php foreach ($items as $task) echo osis_task_card_html($task,true); ?></div><?php else: ?><div class="task-empty-state"><span>☷</span><h3>Belum ada tugas untukmu</h3><p>Ketika admin atau pengurus membagikan tugas, tugas tersebut akan muncul di halaman ini.</p><a class="button button-outline" href="<?= osis_e(osis_url('anggota/dashboard.php')) ?>">Kembali ke dashboard</a></div><?php endif; ?></div></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
