<?php
require_once dirname(__DIR__) . '/_common.php';
require_once dirname(__DIR__, 2) . '/includes/task_functions.php';
$pageTitle = 'Tugas';
$items = $pdo->query("SELECT t.*,u.nama AS pembuat_nama,(SELECT COUNT(*) FROM tugas_penerima p WHERE p.tugas_id=t.id) AS penerima_total,(SELECT COALESCE(SUM(p.status='terkirim'),0) FROM tugas_penerima p WHERE p.tugas_id=t.id) AS penerima_terkirim FROM tugas t JOIN users u ON u.id=t.dibuat_oleh ORDER BY (t.status='aktif') DESC,t.created_at DESC")->fetchAll();
$active = (int) $pdo->query("SELECT COUNT(*) FROM tugas WHERE status='aktif'")->fetchColumn();
$completed = (int) $pdo->query("SELECT COUNT(*) FROM tugas WHERE status='selesai'")->fetchColumn();
osis_admin_page_start($pageTitle);
?>
<div class="task-admin-heading"><div><span class="eyebrow">PENUGASAN OSIS</span><h2>Semua tugas <span class="text-accent">.</span></h2><p>Bagikan tanggung jawab, tinjau kiriman, dan tutup tugas setelah semua penerima mengirim hasil.</p></div><a class="button button-primary" href="<?= osis_e(osis_url('admin/tugas/tambah.php')) ?>">＋ Buat tugas</a></div>
<div class="task-overview-stats"><div><span>AKTIF</span><strong><?= $active ?></strong></div><div><span>SELESAI</span><strong><?= $completed ?></strong></div><div><span>TOTAL</span><strong><?= count($items) ?></strong></div></div>
<?php if ($items): ?><div class="task-card-grid"><?php foreach ($items as $task) echo osis_task_card_html($task); ?></div><?php else: ?><div class="task-empty-state"><span>☷</span><h3>Belum ada tugas</h3><p>Buat tugas pertama dan bagikan ke anggota atau pengurus.</p><a class="button button-primary" href="<?= osis_e(osis_url('admin/tugas/tambah.php')) ?>">Buat tugas <span>↗</span></a></div><?php endif; ?>
<?php osis_admin_page_end(); ?>
