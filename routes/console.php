<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('osis:buat-admin {username : Username admin} {--nama=Administrator OSIS : Nama admin}', function () {
    $username = trim($this->argument('username'));
    $nama = trim($this->option('nama'));
    if ($username === '' || mb_strlen($username) > 50 || $nama === '' || mb_strlen($nama) > 120) {
        $this->error('Username wajib diisi (maks. 50 karakter) dan nama wajib diisi (maks. 120 karakter).');
        return 1;
    }
    if (DB::table('users')->where('role', 'admin')->exists()) {
        $this->error('Admin sudah ada. Tidak ada perubahan.');
        return 1;
    }
    $password = $this->secret('Kata sandi (minimal 8 karakter)');
    $confirm = $this->secret('Ulangi kata sandi');
    if (!is_string($password) || strlen($password) < 8 || $password !== $confirm) {
        $this->error('Kata sandi kurang dari 8 karakter atau konfirmasi tidak cocok.');
        return 1;
    }
    try {
        DB::table('users')->insert(['nama' => $nama, 'username' => $username, 'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'role' => 'admin', 'aktif' => 1]);
    } catch (Throwable $e) {
        $this->error('Admin gagal dibuat. Pastikan username belum digunakan dan database sudah diimpor.');
        return 1;
    }
    $this->info('Admin berhasil dibuat: '.$username);
    return 0;
})->purpose('Membuat admin pertama OSIS secara lokal');
