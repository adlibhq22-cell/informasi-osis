<?php
require_once dirname(__DIR__) . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if(!osis_current_user()){http_response_code(401);echo json_encode(['error'=>'Silakan masuk.']);exit;}
$id=isset($_GET['id'])?(int)$_GET['id']:0;
$stmt=$pdo->prepare('SELECT l.id,l.judul,l.status,l.balasan,l.created_at,l.updated_at,a.user_id FROM laporan l JOIN anggota a ON a.id=l.anggota_id WHERE l.id=?');
$stmt->execute([$id]);$item=$stmt->fetch();
if(!$item|| (osis_current_user()['role']!=='admin' && (int)$item['user_id']!==(int)osis_current_user()['id'])){http_response_code(404);echo json_encode(['error'=>'Laporan tidak ditemukan.']);exit;}
unset($item['user_id']);echo json_encode($item,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
