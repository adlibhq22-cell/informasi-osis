<?php
require_once dirname(__DIR__) . '/includes/task_functions.php';
osis_require_any_role(['admin','pengurus']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Metode tidak diizinkan.'); }
osis_verify_csrf();
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$task = osis_task_get($id);
if (!$task) { osis_set_flash('error','Tugas tidak ditemukan.'); osis_redirect(osis_current_user()['role']==='admin'?'admin/tugas/index.php':'pengurus/tugas.php'); }
if (!osis_task_can_manage($task)) { http_response_code(403); exit('Hanya admin atau pengurus pembuat tugas yang dapat menyelesaikannya.'); }
if ($task['status'] !== 'aktif') { osis_set_flash('info','Tugas ini sudah diselesaikan.'); osis_redirect('tugas/detail.php?id='.$id); }
$progress = osis_task_progress($id);
if ($progress['total'] < 1 || $progress['total'] !== $progress['terkirim']) {
    osis_set_flash('error','Tugas belum bisa ditutup. Pastikan semua penerima sudah mengirim laporan.');
    osis_redirect('tugas/detail.php?id='.$id);
}
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE tugas SET status='selesai',diselesaikan_oleh=?,diselesaikan_pada=NOW() WHERE id=? AND status='aktif'");
    $stmt->execute([osis_current_user()['id'],$id]);
    if ($stmt->rowCount() !== 1) throw new RuntimeException('Tugas sudah berubah status.');
    osis_task_record_event($id, osis_current_user()['id'], 'selesai', 'Tugas ditandai selesai setelah seluruh penerima mengirim laporan dan dokumentasi.');
    $pdo->commit();
    osis_log_activity('Menyelesaikan tugas: '.$task['judul']);
    osis_set_flash('success','Tugas berhasil ditandai selesai.');
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    osis_set_flash('error','Tugas tidak dapat diselesaikan. Coba kembali.');
}
osis_redirect('tugas/detail.php?id='.$id);
