<?php
require_once dirname(__DIR__) . '/_common.php';
$pageTitle='Pengumuman';
$items=$pdo->query('SELECT p.*,u.nama AS admin_nama FROM pengumuman p LEFT JOIN users u ON u.id=p.dibuat_oleh ORDER BY p.created_at DESC')->fetchAll();
osis_admin_page_start($pageTitle);
?>
<div class="admin-page-actions"><p>Kelola informasi resmi yang tampil di situs. Pengumuman penting akan diprioritaskan di beranda.</p><a class="button button-primary" href="<?=osis_e(osis_url('admin/pengumuman/tambah.php'))?>">＋ Tambah pengumuman</a></div>
<div class="admin-panel"><div class="table-scroll"><table class="data-table"><thead><tr><th>Judul</th><th>Prioritas</th><th>Dibuat</th><th>Penulis</th><th>Aksi</th></tr></thead><tbody><?php if($items):foreach($items as $item):?><tr><td><strong><?=osis_e($item['judul'])?></strong><small class="table-subtext"><?=osis_e(mb_strimwidth(strip_tags($item['isi']),0,90,'…','UTF-8'))?></small></td><td><?=$item['penting']==='ya'?'<span class="badge badge-important">Penting</span>':'<span class="badge badge-soft">Normal</span>'?></td><td><?=osis_e(osis_format_date($item['created_at']))?></td><td><?=osis_e($item['admin_nama']?:'—')?></td><td><div class="table-actions"><a class="table-action" href="<?=osis_e(osis_url('admin/pengumuman/edit.php?id='.(int)$item['id']))?>">Edit</a><form method="post" action="<?=osis_e(osis_url('admin/pengumuman/hapus.php'))?>" data-confirm="Hapus pengumuman ini?"><?=osis_csrf_input()?><input type="hidden" name="id" value="<?=(int)$item['id']?>"><button class="table-action danger-text" type="submit">Hapus</button></form></div></td></tr><?php endforeach;else:?><tr><td colspan="5" class="empty-inline">Belum ada pengumuman.</td></tr><?php endif;?></tbody></table></div></div>
<?php osis_admin_page_end(); ?>
