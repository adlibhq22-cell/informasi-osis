<?php
require_once __DIR__ . '/config.php';
try {
    $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Throwable $exception) {
    http_response_code(500);
    echo '<h1>Database belum terhubung</h1><p>Periksa pengaturan DB di file .env dan pastikan database sudah diimpor. Lihat log Laravel untuk detail.</p>';
    \Illuminate\Support\Facades\Log::error('Koneksi database OSIS gagal', ['exception' => $exception]);
    exit;
}
