<?php
require_once dirname(__DIR__) . '/includes/functions.php';
osis_require_any_role(['anggota','pengurus']);
$pageTitle = 'Kirim laporan';
$stmt = $pdo->prepare('SELECT id FROM anggota WHERE user_id=? LIMIT 1'); $stmt->execute([osis_current_user()['id']]); $member = $stmt->fetch();
$categories = $pdo->query('SELECT id,nama FROM kategori_laporan WHERE aktif=1 ORDER BY nama')->fetchAll();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    osis_verify_csrf();
    $title = osis_post_string('judul', 160); $body = osis_post_string('isi', 10000); $category = (int) (isset($_POST['kategori_id']) ? $_POST['kategori_id'] : 0);
    $categoryOk = !$category;
    if ($category) { $checkCategory=$pdo->prepare('SELECT COUNT(*) FROM kategori_laporan WHERE id=? AND aktif=1'); $checkCategory->execute([$category]); $categoryOk=(int)$checkCategory->fetchColumn()>0; }
    if (!$member) { $error = 'Akunmu belum terhubung dengan data anggota. Hubungi admin.'; }
    elseif (!$categoryOk) { $error = 'Kategori laporan tidak valid.'; }
    elseif ($title === '' || $body === '') { $error = 'Judul dan isi laporan wajib diisi.'; }
    else {
        $file = null;
        try {
            $file = osis_store_uploaded_image(isset($_FILES['lampiran']) ? $_FILES['lampiran'] : null, 'laporan');
            $stmt = $pdo->prepare('INSERT INTO laporan (anggota_id,kategori_id,judul,isi,file,status) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$member['id'], $category ?: null, $title, $body, $file, 'baru']);
            osis_log_activity('Anggota mengirim laporan: ' . $title);
            osis_set_flash('success', 'Laporan berhasil dikirim. Admin OSIS akan meninjau aspirasimu.');
            osis_redirect('anggota/laporan-saya.php');
        } catch (Throwable $exception) { if ($file) osis_delete_uploaded_file($file); $error = $exception instanceof RuntimeException ? $exception->getMessage() : 'Laporan tidak dapat disimpan. Silakan coba kembali.'; }
    }
}
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="section section-paper"><div class="container member-page"><div class="member-page-heading"><a class="back-link" href="<?= osis_e(osis_url('anggota/dashboard.php')) ?>">← Dashboard</a><span class="eyebrow">SUARA ANGGOTA</span><h1>Kirim laporan<span class="text-accent">.</span></h1><p>Sampaikan aspirasi, masukan, atau kendala kepada pengurus OSIS.</p></div><div class="form-card"><div class="form-card-note"><span>✳</span><p>Laporanmu hanya dapat dilihat oleh tim admin OSIS. Sertakan detail yang jelas agar mudah ditindaklanjuti.</p></div><?php if ($error): ?><div class="alert alert-error"><?= osis_e($error) ?></div><?php endif; ?><form method="post" enctype="multipart/form-data" data-validate-form><?= osis_csrf_input() ?><label for="kategori">Kategori</label><select id="kategori" name="kategori_id"><option value="">Pilih kategori (opsional)</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= isset($_POST['kategori_id']) && (int)$_POST['kategori_id']===(int)$category['id']?'selected':'' ?>><?= osis_e($category['nama']) ?></option><?php endforeach; ?></select><label for="judul">Judul laporan <span class="required">*</span></label><input id="judul" name="judul" required maxlength="160" value="<?= osis_e(isset($_POST['judul'])?$_POST['judul']:'') ?>" placeholder="Contoh: Usulan kegiatan kebersihan kelas"><label for="isi">Ceritakan secara detail <span class="required">*</span></label><textarea id="isi" name="isi" rows="7" required minlength="10" placeholder="Jelaskan situasi atau ide yang ingin disampaikan..."><?= osis_e(isset($_POST['isi'])?$_POST['isi']:'') ?></textarea><label for="lampiran">Lampiran gambar <span class="optional">(opsional, maks. 5 MB)</span></label><input id="lampiran" name="lampiran" type="file" accept="image/jpeg,image/png,image/webp"><div class="form-actions"><a class="button button-outline" href="<?= osis_e(osis_url('anggota/laporan-saya.php')) ?>">Batal</a><button class="button button-primary" type="submit">Kirim laporan <span>↗</span></button></div></form></div></div></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
