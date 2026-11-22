<?php
require_once dirname(__DIR__) . '/includes/task_functions.php';
osis_require_any_role(['admin','pengurus']);
$user = osis_current_user();
$pageTitle = 'Buat tugas';
$error = '';
$members = osis_task_assignee_options();
$available = [];
foreach ($members as $person) $available[(int) $person['anggota_id']] = $person;
$selected = isset($_POST['anggota_ids']) && is_array($_POST['anggota_ids']) ? array_values(array_unique(array_map('intval', $_POST['anggota_ids']))) : [];
if ($user['role'] === 'pengurus' && !osis_current_member_id()) {
    $error = 'Akun pengurus belum tertaut ke data anggota. Hubungi admin OSIS untuk memperbaiki tautannya.';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    osis_verify_csrf();
    $judul = osis_post_string('judul', 180);
    $deskripsi = osis_post_string('deskripsi', 10000);
    $deadline = osis_post_string('deadline', 10);
    $prioritas = isset($_POST['prioritas']) ? (string) $_POST['prioritas'] : 'normal';
    $validPriority = ['rendah','normal','tinggi'];
    if ($error === '') {
        if ($judul === '' || $deskripsi === '') $error = 'Judul dan instruksi tugas wajib diisi.';
        elseif ($deadline !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) $error = 'Format tenggat tidak valid.';
        elseif (!in_array($prioritas, $validPriority, true)) $error = 'Prioritas tidak valid.';
        elseif (!$selected) $error = 'Pilih minimal satu penerima tugas.';
        else {
            foreach ($selected as $memberId) {
                if (!isset($available[$memberId])) { $error = 'Ada penerima yang tidak aktif atau belum memiliki akun login.'; break; }
            }
        }
    }
    if ($error === '') {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO tugas(judul,deskripsi,deadline,prioritas,dibuat_oleh) VALUES(?,?,?,?,?)');
            $stmt->execute([$judul,$deskripsi,$deadline !== '' ? $deadline : null,$prioritas,$user['id']]);
            $taskId = (int) $pdo->lastInsertId();
            $assignment = $pdo->prepare('INSERT INTO tugas_penerima(tugas_id,anggota_id) VALUES(?,?)');
            foreach ($selected as $memberId) $assignment->execute([$taskId,$memberId]);
            osis_task_record_event($taskId, $user['id'], 'dibuat', 'Tugas dibuat dan dibagikan kepada ' . count($selected) . ' penerima.');
            $pdo->commit();
            osis_log_activity('Membuat tugas: ' . $judul . ' (' . count($selected) . ' penerima)');
            osis_set_flash('success','Tugas berhasil dibuat dan dibagikan kepada penerima.');
            osis_redirect('tugas/detail.php?id=' . $taskId);
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'Tugas tidak dapat disimpan. Periksa data penerima dan coba kembali.';
        }
    }
}

$backUrl = $user['role'] === 'admin' ? 'admin/tugas/index.php' : 'pengurus/tugas.php';
osis_task_page_start($pageTitle);
?>
<section class="task-page task-create-page"><div class="container task-container"><a class="back-link" href="<?= osis_e(osis_url($backUrl)) ?>">← Kembali</a><div class="task-page-heading"><span class="eyebrow">PEMBAGIAN TUGAS</span><h1>Buat tugas<span class="text-accent">.</span></h1><p>Tulis instruksi yang jelas, tentukan tenggat, lalu pilih anggota atau pengurus yang bertanggung jawab.</p></div>
<div class="task-form-card"><?php if ($error): ?><div class="alert alert-error"><?= osis_e($error) ?></div><?php endif; ?><form method="post" data-validate-form><?= osis_csrf_input() ?><label for="judul">Judul tugas <span class="required">*</span></label><input id="judul" name="judul" maxlength="180" required value="<?= osis_e(isset($_POST['judul']) ? $_POST['judul'] : '') ?>" placeholder="Contoh: Dokumentasi Class Meeting">
<label for="deskripsi">Instruksi / rincian tugas <span class="required">*</span></label><textarea id="deskripsi" name="deskripsi" rows="6" required placeholder="Jelaskan pekerjaan, hasil yang diharapkan, dan hal penting lainnya..."><?= osis_e(isset($_POST['deskripsi']) ? $_POST['deskripsi'] : '') ?></textarea>
<div class="form-grid"><div><label for="deadline">Tenggat waktu <span class="optional">(opsional)</span></label><input id="deadline" name="deadline" type="date" value="<?= osis_e(isset($_POST['deadline']) ? $_POST['deadline'] : '') ?>"></div><div><label for="prioritas">Prioritas</label><select id="prioritas" name="prioritas"><option value="rendah" <?= isset($_POST['prioritas']) && $_POST['prioritas']==='rendah'?'selected':'' ?>>Rendah</option><option value="normal" <?= !isset($_POST['prioritas']) || $_POST['prioritas']==='normal'?'selected':'' ?>>Normal</option><option value="tinggi" <?= isset($_POST['prioritas']) && $_POST['prioritas']==='tinggi'?'selected':'' ?>>Tinggi</option></select></div></div>
<div class="task-recipient-header"><div><label>Penerima tugas <span class="required">*</span></label><p>Pilih minimal satu akun aktif. Penerima bisa mengirim laporan dan beberapa file dokumentasi.</p></div><span class="task-selected-count" data-selected-count><?= count($selected) ?> dipilih</span></div>
<?php if ($members): ?><div class="task-recipient-list"><?php foreach ($members as $person): $mid=(int)$person['anggota_id']; ?><label class="task-recipient-option"><input type="checkbox" name="anggota_ids[]" value="<?= $mid ?>" <?= in_array($mid,$selected,true)?'checked':'' ?>><span class="task-recipient-avatar"><?= osis_e(mb_strtoupper(mb_substr($person['nama'],0,1,'UTF-8'),'UTF-8')) ?></span><span class="task-recipient-copy"><strong><?= osis_e($person['nama']) ?></strong><small><?= osis_e($person['kelas'] ?: 'Anggota OSIS') ?> · <?= osis_e($person['username']) ?></small></span><span class="task-role-chip role-<?= osis_e($person['role']) ?>"><?= $person['role']==='pengurus'?'Pengurus':'Anggota' ?></span></label><?php endforeach; ?></div><?php else: ?><div class="empty-inline">Belum ada anggota/pengurus aktif dengan akun login.</div><?php endif; ?>
<div class="task-form-actions"><a class="button button-outline" href="<?= osis_e(osis_url($backUrl)) ?>">Batal</a><button class="button button-primary" type="submit" <?= !$members?'disabled':'' ?>>Bagikan tugas <span>↗</span></button></div></form></div></div></section>
<script src="<?= osis_e(osis_asset('js/task-form.js')) ?>" defer></script>
<?php osis_task_page_end(); ?>
