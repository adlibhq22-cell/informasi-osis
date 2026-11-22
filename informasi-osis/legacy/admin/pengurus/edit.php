<?php
require_once dirname(__DIR__) . '/_common.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $pdo->prepare('SELECT * FROM pengurus WHERE id=?'); $stmt->execute([$id]); $item = $stmt->fetch();
if (!$item) { osis_set_flash('error','Data pengurus tidak ditemukan.'); osis_redirect('admin/pengurus/index.php'); }
$pageTitle = 'Edit pengurus'; $error = '';
$members = $pdo->query("SELECT a.id,a.nama,a.kelas,a.user_id,u.username,u.role AS user_role FROM anggota a LEFT JOIN users u ON u.id=a.user_id WHERE a.status='aktif' ORDER BY a.nama")->fetchAll();
if (osis_admin_is_post()) {
    osis_verify_csrf();
    $nama = osis_post_string('nama',120); $jabatan = osis_post_string('jabatan',100); $periode = osis_post_string('periode',40);
    $deskripsi = osis_post_string('deskripsi',4000); $anggotaId = (int)(isset($_POST['anggota_id']) ? $_POST['anggota_id'] : 0);
    $username = osis_post_string('username',50); $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $selected = null;
    foreach ($members as $member) if ((int)$member['id'] === $anggotaId) { $selected = $member; break; }
    if ($nama === '' || $jabatan === '') $error = 'Nama dan jabatan wajib diisi.';
    elseif (!$selected) $error = 'Pilih anggota aktif yang akan menjadi pengurus.';
    elseif ($selected['user_id'] && $selected['user_role'] === 'admin') $error = 'Akun admin tidak dapat dijadikan akun pengurus.';
    elseif (!$selected['user_id'] && ($username === '' || strlen($password) < 8)) $error = 'Anggota belum memiliki akun. Isi username dan kata sandi minimal 8 karakter.';
    else {
        $check = $pdo->prepare('SELECT COUNT(*) FROM pengurus WHERE anggota_id=? AND id<>?'); $check->execute([$anggotaId,$id]);
        if ((int)$check->fetchColumn() > 0) $error = 'Anggota ini sudah tercatat sebagai pengurus lain.';
    }
    if ($error === '') {
        $foto = null;
        try {
            $foto = osis_store_uploaded_image(isset($_FILES['foto']) ? $_FILES['foto'] : null,'pengurus');
            $oldFoto = $item['foto']; $oldMemberId = (int)($item['anggota_id'] ?: 0);
            $pdo->beginTransaction();
            if ($selected['user_id']) {
                $userId = (int)$selected['user_id'];
                $pdo->prepare("UPDATE users SET nama=?,role='pengurus',aktif=1 WHERE id=?")->execute([$nama,$userId]);
                $pdo->prepare('UPDATE anggota SET nama=? WHERE id=?')->execute([$nama,$anggotaId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO users(nama,username,password_hash,role,aktif) VALUES(?,?,?,'pengurus',1)");
                $stmt->execute([$nama,$username,password_hash($password,PASSWORD_DEFAULT)]);
                $userId = (int)$pdo->lastInsertId();
                $pdo->prepare('UPDATE anggota SET user_id=?,nama=? WHERE id=?')->execute([$userId,$nama,$anggotaId]);
            }
            $sql = 'UPDATE pengurus SET anggota_id=?,nama=?,jabatan=?,periode=?,deskripsi=?' . ($foto ? ',foto=?' : '') . ' WHERE id=?';
            $params = [$anggotaId,$nama,$jabatan,$periode?:null,$deskripsi?:null]; if ($foto) $params[] = $foto; $params[] = $id;
            $pdo->prepare($sql)->execute($params);
            if ($oldMemberId && $oldMemberId !== $anggotaId) {
                $other = $pdo->prepare('SELECT COUNT(*) FROM pengurus WHERE anggota_id=? AND id<>?'); $other->execute([$oldMemberId,$id]);
                if (!(int)$other->fetchColumn()) {
                    $pdo->prepare("UPDATE users u JOIN anggota a ON a.user_id=u.id SET u.role='anggota' WHERE a.id=? AND u.role='pengurus'")->execute([$oldMemberId]);
                }
            }
            $pdo->commit();
            if ($foto) osis_delete_uploaded_file($oldFoto);
            osis_log_activity('Memperbarui profil pengurus: '.$nama); osis_set_flash('success','Profil pengurus berhasil diperbarui.'); osis_redirect('admin/pengurus/index.php');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($foto) osis_delete_uploaded_file($foto);
            $error = osis_admin_handle_error($exception);
        }
    }
}
osis_admin_page_start($pageTitle);
?>
<div class="admin-form-wrap"><a class="back-link" href="<?= osis_e(osis_url('admin/pengurus/index.php')) ?>">← Daftar pengurus</a><div class="admin-panel form-panel"><span class="eyebrow">PERBARUI PROFIL & AKUN</span><h2>Edit pengurus</h2><?php if ($error): ?><div class="alert alert-error"><?= osis_e($error) ?></div><?php endif; ?><form method="post" enctype="multipart/form-data" data-validate-form><?= osis_csrf_input() ?>
<div class="form-grid"><div><label for="anggota_id">Anggota tertaut</label><select id="anggota_id" name="anggota_id" required data-officer-member-select><option value="">Pilih anggota…</option><?php foreach ($members as $member): ?><option value="<?= (int)$member['id'] ?>" data-has-account="<?= $member['user_id']?'1':'0' ?>" data-username="<?= osis_e($member['username'] ?: '') ?>" <?= isset($_POST['anggota_id']) ? ((int)$_POST['anggota_id']===(int)$member['id']?'selected':'') : ((int)$item['anggota_id']===(int)$member['id']?'selected':'') ?>><?= osis_e($member['nama'].' · '.($member['kelas'] ?: 'Kelas belum diisi')) ?><?= $member['user_id']?' · akun '.$member['username']:' · belum ada akun' ?></option><?php endforeach; ?></select></div><div><label for="jabatan">Jabatan</label><input id="jabatan" name="jabatan" required maxlength="100" value="<?= osis_e(isset($_POST['jabatan'])?$_POST['jabatan']:$item['jabatan']) ?>"></div><div><label for="nama">Nama pengurus</label><input id="nama" name="nama" required maxlength="120" value="<?= osis_e(isset($_POST['nama'])?$_POST['nama']:$item['nama']) ?>"></div><div><label for="periode">Periode</label><input id="periode" name="periode" maxlength="40" value="<?= osis_e(isset($_POST['periode'])?$_POST['periode']:$item['periode']) ?>"></div></div>
<div class="officer-account-fields" data-officer-account-fields><div class="task-recipient-header"><div><strong>Akun login pengurus</strong><p>Anggota yang dipilih belum mempunyai akun. Buat username dan kata sandi awal.</p></div></div><div class="form-grid"><div><label for="username">Username baru</label><input id="username" name="username" maxlength="50" autocomplete="off" value="<?= osis_e(isset($_POST['username'])?$_POST['username']:'') ?>"></div><div><label for="password">Kata sandi awal <span class="optional">(minimal 8 karakter)</span></label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password"></div></div></div>
<label for="deskripsi">Deskripsi singkat</label><textarea id="deskripsi" name="deskripsi" rows="4"><?= osis_e(isset($_POST['deskripsi'])?$_POST['deskripsi']:$item['deskripsi']) ?></textarea><?php if ($item['foto']): ?><div class="current-upload"><img src="<?= osis_e(osis_image_url($item['foto'])) ?>" alt="Foto saat ini"><span>Foto saat ini</span></div><?php endif; ?><label for="foto">Ganti foto <span class="optional">JPG, PNG, WEBP · maks. 5 MB</span></label><input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp"><div class="form-actions"><a class="button button-outline" href="<?= osis_e(osis_url('admin/pengurus/index.php')) ?>">Batal</a><button class="button button-primary" type="submit">Simpan perubahan <span>↗</span></button></div></form></div></div>
<script src="<?= osis_e(osis_asset('js/pengurus-akun.js')) ?>" defer></script>
<?php osis_admin_page_end(); ?>
