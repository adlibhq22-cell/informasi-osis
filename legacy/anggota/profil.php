<?php
require_once dirname(__DIR__) . '/includes/functions.php';
osis_require_any_role(['anggota','pengurus']);
$pageTitle = 'Profil saya';
$stmt=$pdo->prepare('SELECT a.*,u.username,u.email AS user_email FROM anggota a JOIN users u ON u.id=a.user_id WHERE u.id=? LIMIT 1'); $stmt->execute([osis_current_user()['id']]); $member=$stmt->fetch();
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    osis_verify_csrf();
    $nama=osis_post_string('nama',120); $username=osis_post_string('username',50); $email=osis_post_string('email',120); $hp=osis_post_string('no_hp',30); $alamat=osis_post_string('alamat',2000); $password=isset($_POST['password'])?(string)$_POST['password']:'';
    if(!$member){$error='Data anggota tidak ditemukan.';}
    elseif($nama===''||$username===''){$error='Nama dan username wajib diisi.';}
    elseif($password!==''&&strlen($password)<8){$error='Kata sandi baru minimal 8 karakter.';}
    else{
        $stmt=$pdo->prepare('SELECT id FROM users WHERE (username=? OR (email IS NOT NULL AND email=?)) AND id<>?'); $stmt->execute([$username,$email!==''?$email:null,osis_current_user()['id']]);
        if($stmt->fetch()){$error='Username atau email tersebut sudah digunakan.';}
        else{
            try{
                $oldPhoto=$member['foto'];
                $photo=osis_store_uploaded_image(isset($_FILES['foto'])?$_FILES['foto']:null,'anggota');
                $pdo->beginTransaction();
                $sql='UPDATE users SET nama=?,username=?,email=?'; $params=[$nama,$username,$email!==''?$email:null];
                if($password!==''){$sql.=',password_hash=?';$params[]=password_hash($password,PASSWORD_DEFAULT);}
                $sql.=' WHERE id=?';$params[]=osis_current_user()['id'];$pdo->prepare($sql)->execute($params);
                $sql='UPDATE anggota SET nama=?,email=?,no_hp=?,alamat=?';$params=[$nama,$email!==''?$email:null,$hp,$alamat];
                if($photo){$sql.=',foto=?';$params[]=$photo;}
                $sql.=' WHERE id=?';$params[]=$member['id'];$pdo->prepare($sql)->execute($params);
                $pdo->commit();
                if($photo) osis_delete_uploaded_file($oldPhoto);
                $_SESSION['user']['nama']=$nama;$_SESSION['user']['username']=$username;$_SESSION['user']['email']=$email;
                osis_log_activity('Anggota memperbarui profil');osis_set_flash('success','Profil berhasil diperbarui.');osis_redirect('anggota/profil.php');
            }catch(Throwable $ex){if($pdo->inTransaction())$pdo->rollBack();if(isset($photo)&&$photo)osis_delete_uploaded_file($photo);$error=$ex instanceof RuntimeException?$ex->getMessage():'Profil gagal disimpan. Username atau email mungkin sudah digunakan.';}
        }
    }
}
if(!$member){$stmt=$pdo->prepare('SELECT a.*,u.username,u.email AS user_email FROM anggota a JOIN users u ON u.id=a.user_id WHERE u.id=? LIMIT 1');$stmt->execute([osis_current_user()['id']]);$member=$stmt->fetch();}
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="section section-paper"><div class="container member-page"><div class="member-page-heading"><a class="back-link" href="<?= osis_e(osis_url('anggota/dashboard.php')) ?>">← Dashboard</a><span class="eyebrow">AKUN ANGGOTA</span><h1>Profil saya<span class="text-accent">.</span></h1><p>Perbarui informasi kontak dan data akunmu.</p></div><?php if(!$member):?><div class="alert alert-warning">Data anggota belum terhubung dengan akun ini.</div><?php else:?><div class="form-card profile-form-card"><?php if($error):?><div class="alert alert-error"><?=osis_e($error)?></div><?php endif;?><form method="post" enctype="multipart/form-data" data-validate-form><?=osis_csrf_input()?><div class="profile-form-head"><div class="profile-form-avatar"><?php if($member['foto']):?><img src="<?=osis_e(osis_image_url($member['foto']))?>" alt="Foto profil"><?php else:?><span><?=osis_e(mb_strtoupper(mb_substr($member['nama'],0,1,'UTF-8'),'UTF-8'))?></span><?php endif;?></div><div><strong><?=osis_e($member['nama'])?></strong><small><?=osis_e($member['kelas']?:'Anggota OSIS')?></small></div></div><div class="form-grid"><div><label for="nama">Nama lengkap</label><input id="nama" name="nama" required maxlength="120" value="<?=osis_e(isset($_POST['nama'])?$_POST['nama']:$member['nama'])?>"></div><div><label for="username">Username</label><input id="username" name="username" required maxlength="50" value="<?=osis_e(isset($_POST['username'])?$_POST['username']:$member['username'])?>"></div><div><label for="email">Email</label><input id="email" name="email" type="email" maxlength="120" value="<?=osis_e(isset($_POST['email'])?$_POST['email']:$member['user_email'])?>"></div><div><label for="no_hp">Nomor kontak</label><input id="no_hp" name="no_hp" maxlength="30" value="<?=osis_e(isset($_POST['no_hp'])?$_POST['no_hp']:$member['no_hp'])?>"></div></div><label for="alamat">Alamat</label><textarea id="alamat" name="alamat" rows="3"><?=osis_e(isset($_POST['alamat'])?$_POST['alamat']:$member['alamat'])?></textarea><label for="foto">Foto profil <span class="optional">(JPG, PNG, WEBP; maks. 5 MB)</span></label><input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp"><label for="password">Kata sandi baru <span class="optional">(kosongkan jika tidak diubah)</span></label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password"><div class="form-actions"><button class="button button-primary" type="submit">Simpan perubahan <span>↗</span></button></div></form></div><?php endif;?></div></section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
