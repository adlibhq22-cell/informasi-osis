<?php
require_once dirname(__DIR__) . '/_common.php';
if (!osis_admin_is_post()) { http_response_code(405); exit('Metode tidak diizinkan.'); }
osis_verify_csrf();
$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$stmt = $pdo->prepare('SELECT id,nama,foto,user_id FROM anggota WHERE id=?'); $stmt->execute([$id]); $item = $stmt->fetch();
if ($item) {
    $stmt = $pdo->prepare('SELECT foto FROM pengurus WHERE anggota_id=?'); $stmt->execute([$id]); $officerPhotos = $stmt->fetchAll();
    $stmt = $pdo->prepare('SELECT d.file FROM tugas_dokumentasi d JOIN tugas_pengumpulan s ON s.id=d.pengumpulan_id JOIN tugas_penerima p ON p.id=s.penugasan_id WHERE p.anggota_id=?'); $stmt->execute([$id]); $taskFiles = $stmt->fetchAll();
    try {
        $pdo->beginTransaction();
        $pdo->prepare('DELETE FROM pengurus WHERE anggota_id=?')->execute([$id]);
        $pdo->prepare('DELETE FROM anggota WHERE id=?')->execute([$id]);
        if ($item['user_id']) $pdo->prepare("DELETE FROM users WHERE id=? AND role IN ('anggota','pengurus')")->execute([$item['user_id']]);
        $pdo->commit();
        osis_delete_uploaded_file($item['foto']);
        foreach ($officerPhotos as $file) osis_delete_uploaded_file($file['foto']);
        foreach ($taskFiles as $file) osis_delete_uploaded_file($file['file']);
        osis_log_activity('Menghapus anggota: '.$item['nama']);
        osis_set_flash('success','Data anggota, profil pengurus, kiriman tugas, laporan, dan absensinya berhasil dihapus.');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        osis_set_flash('error','Anggota tidak dapat dihapus. Akun mungkin masih menjadi pembuat tugas atau memiliki data terkait.');
    }
} else { osis_set_flash('error','Data anggota tidak ditemukan.'); }
osis_redirect('admin/anggota/index.php');
