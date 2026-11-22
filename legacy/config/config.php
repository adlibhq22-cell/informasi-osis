<?php
// Lapisan kompatibilitas PHP native: nilai berasal dari konfigurasi Laravel.
date_default_timezone_set('Asia/Jakarta');
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => request()->isSecure(), 'path' => '/']);
    session_start();
}
define('BASE_URL', rtrim(request()->getBaseUrl(), '/'));
define('UPLOAD_ROOT', storage_path('app/osis-uploads'));
