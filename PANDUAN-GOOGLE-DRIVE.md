# Informasi OSIS — penyimpanan file di Google Drive via Apps Script

Proyek ini adalah aplikasi Laravel 12 dari arsip Anda, termasuk lapisan kompatibilitas PHP native di folder `legacy/`. Foto anggota, pengurus, kegiatan, galeri, lampiran laporan, dan dokumentasi tugas sekarang diunggah ke **Google Drive** melalui Apps Script. Database (MySQL) **tetap** dipakai untuk data aplikasi dan hanya menyimpan referensi `drive/FILE_ID.ekstensi`. Tidak diperlukan migrasi tabel.

## 1. Siapkan folder dan Apps Script

1. Buat folder baru di Google Drive, misalnya `Informasi OSIS`. Ambil ID folder dari URL `https://drive.google.com/drive/folders/ID_FOLDER`.
2. Buka [script.google.com](https://script.google.com), buat project baru, lalu salin seluruh isi `google-apps-script/Code.gs` ke editor.
3. Di **Project Settings → Script properties**, tambahkan:
   - `OSIS_FOLDER_ID` = ID folder tadi.
   - `OSIS_SECRET` = token acak yang panjang (contoh buat dengan `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`). **Jangan gunakan token contoh atau bagikan token ini.**
4. Pilih **Deploy → New deployment → Web app**. Atur **Execute as: Me** dan **Who has access: Anyone**. Berikan izin Drive saat diminta, lalu salin URL deployment yang berakhir `/exec`.
5. `Anyone` diperlukan agar server Laravel dapat mengakses web app tanpa login Google; akses operasi file **tetap dibatasi token**. Simpan URL/token hanya di server. File Drive tidak dibagikan publik. Jika mengubah `Code.gs`, buat versi deployment baru via **Manage deployments → Edit → New version**.

## 2. Konfigurasi Laravel

Gunakan pengaturan database, APP_KEY, dan APP_URL aplikasi Anda seperti sebelumnya. Di `.env` Laravel tambahkan:

```dotenv
OSIS_DRIVE_SCRIPT_URL=https://script.google.com/macros/s/ID_DEPLOYMENT/exec
OSIS_DRIVE_SECRET=TOKEN_YANG_SAMA_DENGAN_OSIS_SECRET
```

Jalankan `php artisan config:clear` (atau `php artisan config:cache` setelah mengubah `.env`). Jika memasang dari zip ini di mesin baru, lakukan pemasangan Laravel biasa (`composer install`, salin `.env.example` menjadi `.env`, `php artisan key:generate`, dan siapkan database aplikasi). Jangan masukkan `.env` ke repositori.

## 3. Uji alur

- Login admin, unggah satu foto galeri dan satu foto anggota. Periksa folder `galeri`/`anggota` yang dibuat otomatis di folder Drive utama.
- Buka halaman publiknya: gambar disajikan oleh Laravel dari `/uploads/drive/FILE_ID.jpg`, tanpa menjadikan file Drive publik.
- Unggah dokumentasi tugas berupa gambar atau PDF; batas gambar 5 MB dan lampiran tugas 8 MB. Buka dan hapus untuk menguji akses baca/hapus.
- File lama di `storage/app/osis-uploads` masih dapat dibuka dan dihapus lewat route yang sama. File lama **tidak dipindahkan otomatis** ke Drive; pertahankan folder tersebut sampai migrasi data lama dilakukan terpisah.

## Catatan keamanan & batasan

- URL Apps Script dan token tidak pernah dipanggil dari browser; seluruh request berasal dari PHP Laravel. Jika token bocor, ganti `OSIS_SECRET` di Script Properties dan `OSIS_DRIVE_SECRET` di `.env`.
- Apps Script mengizinkan baca/hapus hanya untuk file di folder OSIS atau subfolder langsungnya; nama file, MIME, kategori, dan ukuran dibatasi. Jangan simpan file aplikasi umum (session, cache, database) di Drive — yang dialihkan hanya unggahan media.
- Apps Script menggunakan base64 dan memiliki kuota/waktu eksekusi Google; desain ini cocok untuk gambar/PDF kecil, bukan video atau file besar. Saat Apps Script gagal, unggahan baru gagal dengan pesan error, tidak diam-diam tersimpan lokal.
- Saat penghapusan file Drive gagal setelah record database dihapus, kegagalan dicatat di log Laravel untuk ditindaklanjuti. File tersebut mungkin perlu dihapus manual dari Drive.
- Server PHP harus bisa mengakses `script.google.com` dan mengikuti redirect `script.googleusercontent.com`. PHP memerlukan ekstensi `fileinfo` dan klien HTTP Laravel (sudah ada dalam Laravel 12).

## Berkas yang diubah

- `app/Services/GoogleDriveScriptStorage.php`: klien upload/read/delete Apps Script.
- `google-apps-script/Code.gs`: endpoint Drive dengan token bersama.
- `legacy/includes/functions.php`: helper upload/hapus lama menggunakan Drive untuk file baru.
- `routes/web.php`: proxy media Drive dan fallback file lokal lama.
- `config/osis.php` dan `.env.example`: konfigurasi lingkungan.
