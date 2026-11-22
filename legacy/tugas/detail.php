<?php
require_once dirname(__DIR__) . '/includes/task_functions.php';
osis_require_any_role(['admin','pengurus','anggota']);
$taskId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$task = osis_task_get($taskId);
if (!$task) { http_response_code(404); exit('Tugas tidak ditemukan.'); }
$user = osis_current_user();
$canManage = osis_task_can_manage($task);
$myAssignment = in_array($user['role'], ['anggota','pengurus'], true) ? osis_task_my_assignment($taskId) : null;
if (!$canManage && !$myAssignment) { http_response_code(403); exit('Tugas ini bukan tugas yang dibagikan kepada akun Anda.'); }
$pageTitle = $task['judul'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    osis_verify_csrf();
    if (!$myAssignment || $task['status'] !== 'aktif') {
        $error = 'Akun ini tidak dapat mengirim pembaruan untuk tugas tersebut.';
    } else {
        $laporan = osis_post_string('laporan', 10000);
        $files = isset($_FILES['dokumentasi']) ? $_FILES['dokumentasi'] : null;
        $fileIndexes = [];
        if ($files && isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $i => $name) {
                if ((int) $files['error'][$i] !== UPLOAD_ERR_NO_FILE) $fileIndexes[] = $i;
            }
        }
        if (mb_strlen($laporan, 'UTF-8') < 10) {
            $error = 'Isi laporan minimal 10 karakter agar progres tugas jelas.';
        } elseif (!$fileIndexes) {
            $error = 'Lampirkan minimal satu dokumentasi untuk melengkapi laporan tugas.';
        } elseif (count($fileIndexes) > 5) {
            $error = 'Unggah maksimal 5 file dokumentasi dalam satu kiriman.';
        } else {
            $storedFiles = [];
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare('INSERT INTO tugas_pengumpulan(penugasan_id,user_id,laporan) VALUES(?,?,?)');
                $stmt->execute([$myAssignment['id'],$user['id'],$laporan]);
                $submissionId = (int) $pdo->lastInsertId();
                $insertFile = $pdo->prepare('INSERT INTO tugas_dokumentasi(pengumpulan_id,file) VALUES(?,?)');
                foreach ($fileIndexes as $i) {
                    $one = [
                        'name' => $files['name'][$i], 'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i],
                        'size' => $files['size'][$i],
                    ];
                    $path = osis_store_task_attachment($one);
                    if ($path) {
                        $storedFiles[] = $path;
                        $insertFile->execute([$submissionId,$path]);
                    }
                }
                $pdo->prepare("UPDATE tugas_penerima SET status='terkirim' WHERE id=?")->execute([$myAssignment['id']]);
                osis_task_record_event($taskId, $user['id'], 'pengumpulan', 'Mengirim laporan dengan ' . count($storedFiles) . ' dokumentasi.');
                $pdo->commit();
                osis_log_activity('Mengirim laporan/dokumentasi untuk tugas: ' . $task['judul']);
                osis_set_flash('success','Laporan dan dokumentasi berhasil dikirim ke tugas.');
                osis_redirect('tugas/detail.php?id=' . $taskId);
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                foreach ($storedFiles as $path) osis_delete_uploaded_file($path);
                $error = $exception instanceof RuntimeException ? $exception->getMessage() : 'Kiriman tidak dapat disimpan. Periksa file dan coba lagi.';
            }
        }
    }
}

$progress = osis_task_progress($taskId);
$events = osis_task_get_events($taskId);
$allSubmitted = osis_task_all_assignments_submitted($taskId);
$assignees = [];
if ($canManage) {
    $stmt = $pdo->prepare("SELECT tp.*,a.nama,a.kelas,u.role,(SELECT COUNT(*) FROM tugas_pengumpulan sub WHERE sub.penugasan_id=tp.id) AS jumlah_kiriman,(SELECT MAX(created_at) FROM tugas_pengumpulan sub WHERE sub.penugasan_id=tp.id) AS kiriman_terakhir FROM tugas_penerima tp JOIN anggota a ON a.id=tp.anggota_id LEFT JOIN users u ON u.id=a.user_id WHERE tp.tugas_id=? ORDER BY u.role DESC,a.kelas,a.nama");
    $stmt->execute([$taskId]);
    $assignees = $stmt->fetchAll();
}
$sql = 'SELECT sub.*,u.nama AS pengirim,a.nama AS penerima,a.kelas,tp.id AS penerima_id FROM tugas_pengumpulan sub JOIN users u ON u.id=sub.user_id JOIN tugas_penerima tp ON tp.id=sub.penugasan_id JOIN anggota a ON a.id=tp.anggota_id WHERE tp.tugas_id=?';
$params = [$taskId];
if (!$canManage) { $sql .= ' AND tp.id=?'; $params[] = $myAssignment['id']; }
$sql .= ' ORDER BY sub.created_at DESC,sub.id DESC';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $submissions = $stmt->fetchAll();
$docsBySubmission = [];
if ($submissions) {
    $ids = array_map('intval', array_column($submissions, 'id'));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM tugas_dokumentasi WHERE pengumpulan_id IN ($placeholders) ORDER BY id ASC");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $doc) $docsBySubmission[$doc['pengumpulan_id']][] = $doc;
}
$backUrl = $user['role'] === 'admin' ? 'admin/tugas/index.php' : ($user['role'] === 'pengurus' ? 'pengurus/tugas.php' : 'anggota/tugas.php');
osis_task_page_start('Detail tugas');
?>
<section class="task-page task-detail-page"><div class="container task-container"><a class="back-link" href="<?= osis_e(osis_url($backUrl)) ?>">← Kembali ke daftar tugas</a>
<div class="task-detail-heading"><div class="task-detail-title"><div class="task-badge-row"><span class="task-state-chip state-<?= osis_e($task['status']) ?>"><?= $task['status']==='selesai'?'Selesai':'Aktif' ?></span><span class="task-priority-chip priority-<?= osis_e($task['prioritas']) ?>"><?= osis_e(osis_task_priority_label($task['prioritas'])) ?></span></div><h1><?= osis_e($task['judul']) ?></h1><p>Dibuat oleh <strong><?= osis_e($task['pembuat_nama']) ?></strong> · <?= osis_e(osis_format_date($task['created_at'], true)) ?></p></div><div class="task-deadline-box"><span class="eyebrow">TENGGAT</span><strong><?= osis_e($task['deadline'] ? osis_format_date($task['deadline']) : 'Tidak ditentukan') ?></strong><?php if ($task['deadline'] && $task['status']==='aktif' && strtotime($task['deadline']) < strtotime(date('Y-m-d'))): ?><small class="task-overdue">Terlambat</small><?php endif; ?></div></div>
<div class="task-detail-grid"><div class="task-main-column"><article class="task-panel task-instructions"><div class="task-panel-heading"><span class="eyebrow">INSTRUKSI TUGAS</span><?php if ($myAssignment): ?><span class="task-state-chip state-<?= osis_e($myAssignment['status']) ?>"><?= $myAssignment['status']==='terkirim'?'Laporan terkirim':'Menunggu kiriman' ?></span><?php endif; ?></div><div class="task-description"><?= nl2br(osis_e($task['deskripsi'])) ?></div></article>
<?php if ($error): ?><div class="alert alert-error"><?= osis_e($error) ?></div><?php endif; ?>
<?php if ($myAssignment): ?><section class="task-panel task-submit-panel"><div class="task-panel-heading"><div><span class="eyebrow">LAPORAN & DOKUMENTASI</span><h2><?= $myAssignment['status']==='terkirim'?'Kirim pembaruan tambahan':'Kirim hasil tugas' ?></h2></div><?php if ($task['status']==='aktif'): ?><span class="task-state-chip state-<?= osis_e($myAssignment['status']) ?>"><?= $myAssignment['status']==='terkirim'?'Sudah dikirim':'Belum dikirim' ?></span><?php endif; ?></div><?php if ($task['status']==='aktif'): ?><p class="task-helper-copy">Tuliskan hasil pekerjaan atau progresnya. Lampirkan minimal 1 dan maksimal 5 foto atau PDF (maks. 8 MB per file).</p><form method="post" action="<?= osis_e(osis_task_detail_url($taskId)) ?>" enctype="multipart/form-data" data-validate-form><?= osis_csrf_input() ?><label for="laporan">Laporan / catatan hasil <span class="required">*</span></label><textarea id="laporan" name="laporan" rows="5" required minlength="10" placeholder="Ceritakan pekerjaan yang sudah diselesaikan..."><?= osis_e(isset($_POST['laporan']) ? $_POST['laporan'] : '') ?></textarea><label for="dokumentasi">Dokumentasi <span class="required">(wajib · JPG, PNG, WEBP, PDF)</span></label><input id="dokumentasi" name="dokumentasi[]" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" multiple required data-max-size="8388608" data-max-files="5"><div class="task-form-actions"><button class="button button-primary" type="submit">Kirim laporan & dokumentasi <span>↗</span></button></div></form><?php else: ?><div class="task-closed-note">Tugas ini sudah ditutup. Kiriman baru tidak dapat ditambahkan.</div><?php endif; ?></section><?php endif; ?>
<section class="task-panel task-submissions-panel"><div class="task-panel-heading"><div><span class="eyebrow">RIWAYAT PENGUMPULAN</span><h2><?= $canManage?'Laporan penerima':'Kiriman saya' ?></h2></div><span class="task-selected-count"><?= count($submissions) ?> kiriman</span></div><?php if ($submissions): ?><div class="task-submission-list"><?php foreach ($submissions as $submission): ?><article class="task-submission"><div class="task-submission-top"><div class="task-submission-author"><span class="task-recipient-avatar"><?= osis_e(mb_strtoupper(mb_substr($submission['pengirim'],0,1,'UTF-8'),'UTF-8')) ?></span><div><strong><?= osis_e($submission['pengirim']) ?></strong><?php if ($canManage): ?><small><?= osis_e($submission['kelas'] ?: 'Anggota OSIS') ?></small><?php endif; ?></div></div><time><?= osis_e(osis_format_date($submission['created_at'],true)) ?></time></div><div class="task-submission-text"><?= nl2br(osis_e($submission['laporan'])) ?></div><?php if (!empty($docsBySubmission[$submission['id']])): ?><div class="task-document-list"><?php foreach ($docsBySubmission[$submission['id']] as $doc): ?><a class="task-document" href="<?= osis_e(osis_image_url($doc['file'])) ?>" target="_blank" rel="noopener"><?php if (osis_is_image_attachment($doc['file'])): ?><img src="<?= osis_e(osis_image_url($doc['file'])) ?>" alt="Dokumentasi tugas"><?php else: ?><span class="task-pdf-icon">PDF</span><?php endif; ?><span><?= osis_is_image_attachment($doc['file'])?'Lihat foto dokumentasi':'Buka dokumen PDF' ?> ↗</span></a><?php endforeach; ?></div><?php endif; ?></article><?php endforeach; ?></div><?php else: ?><div class="empty-inline">Belum ada laporan atau dokumentasi yang dikirim.</div><?php endif; ?></section>
<section class="task-panel task-history-panel"><div class="task-panel-heading"><div><span class="eyebrow">JEJAK TUGAS</span><h2>Riwayat aktivitas</h2></div><span class="task-selected-count"><?= count($events) ?> aktivitas</span></div><?php if ($events): ?><ol class="task-event-list"><?php foreach ($events as $event): $eventLabel = ['dibuat'=>'Tugas dibagikan','pengumpulan'=>'Laporan dikirim','selesai'=>'Tugas diselesaikan']; ?><li class="task-event-row event-<?= osis_e($event['aksi']) ?>"><span class="task-event-mark"><?= $event['aksi']==='dibuat'?'＋':($event['aksi']==='pengumpulan'?'↥':'✓') ?></span><div class="task-event-copy"><strong><?= osis_e(isset($eventLabel[$event['aksi']])?$eventLabel[$event['aksi']]:'Aktivitas tugas') ?></strong><p><?= osis_e($event['keterangan']) ?></p><small><?= osis_e($event['pelaku'] ?: 'Akun tidak tersedia') ?> · <?= osis_e(osis_format_date($event['created_at'],true)) ?></small></div></li><?php endforeach; ?></ol><?php else: ?><div class="empty-inline">Belum ada riwayat aktivitas.</div><?php endif; ?></section></div>
<aside class="task-side-column"><?php if ($canManage): ?><section class="task-panel task-progress-panel"><span class="eyebrow">PROGRES PENUGASAN</span><div class="task-progress-number"><strong><?= $progress['terkirim'] ?></strong><span>/ <?= $progress['total'] ?> penerima mengirim</span></div><div class="task-progress-track"><span style="width:<?= $progress['percent'] ?>%"></span></div><div class="task-assignee-list"><h3>Penerima tugas</h3><?php foreach ($assignees as $person): ?><div class="task-assignee-row"><span class="task-recipient-avatar avatar-small"><?= osis_e(mb_strtoupper(mb_substr($person['nama'],0,1,'UTF-8'),'UTF-8')) ?></span><span class="task-recipient-copy"><strong><?= osis_e($person['nama']) ?></strong><small><?= osis_e($person['kelas'] ?: ($person['role']==='pengurus'?'Pengurus':'Anggota')) ?></small></span><span class="task-state-chip state-<?= osis_e($person['status']) ?>"><?= $person['status']==='terkirim'?'Terkirim':'Menunggu' ?></span></div><?php endforeach; ?></div><?php if ($task['status']==='aktif'): ?><?php if ($allSubmitted): ?><form method="post" action="<?= osis_e(osis_url('tugas/selesaikan.php')) ?>" data-confirm="Semua penerima sudah mengirim laporan. Tandai tugas ini selesai?"><?= osis_csrf_input() ?><input type="hidden" name="id" value="<?= $taskId ?>"><button class="button button-primary button-full" type="submit">✓ Tandai tugas selesai</button></form><?php else: ?><div class="task-waiting-note">Tugas dapat ditutup setelah semua penerima mengirim laporan dan dokumentasi.</div><?php endif; ?><?php else: ?><div class="task-complete-note">✓ Tugas diselesaikan<?= $task['penyelesai_nama']?' oleh '.osis_e($task['penyelesai_nama']):'' ?>.</div><?php endif; ?></section><?php else: ?><section class="task-panel task-progress-panel"><span class="eyebrow">STATUS TUGAS</span><h2><?= $task['status']==='selesai'?'Tugas selesai':'Tugas aktif' ?></h2><p class="task-helper-copy"><?= $myAssignment['status']==='terkirim'?'Laporanmu sudah terkirim. Tunggu penerima tugas lainnya menyelesaikan kiriman mereka.':'Kirim laporan dan dokumentasi agar progresmu tercatat.' ?></p><div class="task-progress-number"><strong><?= $progress['terkirim'] ?></strong><span>/ <?= $progress['total'] ?> penerima mengirim</span></div><div class="task-progress-track"><span style="width:<?= $progress['percent'] ?>%"></span></div></section><?php endif; ?><section class="task-panel task-meta-panel"><span class="eyebrow">INFO TUGAS</span><div><small>Dibuat oleh</small><strong><?= osis_e($task['pembuat_nama']) ?></strong></div><div><small>Prioritas</small><strong><?= osis_e(osis_task_priority_label($task['prioritas'])) ?></strong></div><div><small>Dibagikan kepada</small><strong><?= $progress['total'] ?> orang</strong></div><?php if ($task['status']==='selesai'): ?><div><small>Diselesaikan pada</small><strong><?= osis_e(osis_format_date($task['diselesaikan_pada'],true)) ?></strong></div><?php endif; ?></section></aside></div></div></section>
<?php osis_task_page_end(); ?>
