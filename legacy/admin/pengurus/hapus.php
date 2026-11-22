<?php
require_once dirname(__DIR__) . '/_common.php';
if (!osis_admin_is_post()) { http_response_code(405); exit('Metode tidak diizinkan.'); }
osis_verify_csrf();
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$stmt = $pdo->prepare('SELECT p.nama,p.foto,p.anggota_id,a.user_id FROM pengurus p LEFT JOIN anggota a ON a.id=p.anggota_id WHERE p.id=?');
$stmt->execute([$id]); $item = $stmt->fetch();
if ($item) {
    try {
        $pdo->beginTransaction();
        $pdo->prepare('DELETE FROM pengurus WHERE id=?')->execute([$id]);
        if ($item['anggota_id'] && $item['user_id']) {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM pengurus WHERE anggota_id=?'); $stmt->execute([$item['anggota_id']]);
            if (!(int)$stmt->fetchColumn()) $pdo->prepare("UPDATE users SET role='anggota' WHERE id=? AND role='pengurus'")->execute([$item['user_id']]);
        }
        $pdo->commit();
        osis_delete_uploaded_file($item['foto']);
        osis_log_activity('Menghapus profil pengurus: '.$item['nama']);
        osis_set_flash('success','Profil pengurus dihapus. Akun yang ditautkan kembali menjadi akun anggota.');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        osis_set_flash('error','Profil pengurus tidak dapat dihapus.');
    }
} else { osis_set_flash('error','Data pengurus tidak ditemukan.'); }
osis_redirect('admin/pengurus/index.php');
