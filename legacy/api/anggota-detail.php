<?php
require_once dirname(__DIR__) . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$id=isset($_GET['id'])?(int)$_GET['id']:0;
$stmt=$pdo->prepare("SELECT id,nama,kelas,foto FROM anggota WHERE id=? AND status='aktif' LIMIT 1");
$stmt->execute([$id]);$member=$stmt->fetch();
if(!$member){http_response_code(404);echo json_encode(['error'=>'Anggota tidak ditemukan.'],JSON_UNESCAPED_UNICODE);exit;}
$member['foto_url']=$member['foto']?osis_image_url($member['foto']):null;
unset($member['foto']);
echo json_encode($member,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
