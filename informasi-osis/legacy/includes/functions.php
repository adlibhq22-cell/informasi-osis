<?php

require_once dirname(__DIR__).'/config/database.php';

function osis_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function osis_url($path = '')
{
    $path = ltrim((string) $path, '/');

    return BASE_URL.($path === '' ? '/' : '/'.$path);
}

function osis_asset($path)
{
    return asset('assets/'.ltrim((string) $path, '/'));
}

function osis_redirect($path)
{
    header('Location: '.osis_url($path));
    exit;
}

function osis_set_flash($type, $message)
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function osis_get_flash()
{
    if (empty($_SESSION['_flash'])) {
        return null;
    }
    $flash = $_SESSION['_flash'];
    unset($_SESSION['_flash']);

    return $flash;
}

function osis_csrf_token()
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function osis_csrf_input()
{
    return '<input type="hidden" name="_csrf" value="'.osis_e(osis_csrf_token()).'">';
}

function osis_verify_csrf()
{
    $sent = isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : '';
    if ($sent === '' || ! hash_equals(osis_csrf_token(), $sent)) {
        http_response_code(419);
        exit('Sesi formulir tidak valid atau sudah kedaluwarsa. Silakan kembali dan muat ulang halaman.');
    }
}

function osis_current_user()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function osis_require_login()
{
    if (! osis_current_user()) {
        osis_set_flash('info', 'Silakan masuk terlebih dahulu.');
        osis_redirect('auth/login.php');
    }
}

function osis_require_role($role)
{
    osis_require_login();
    if (osis_current_user()['role'] !== $role) {
        http_response_code(403);
        exit('Anda tidak memiliki izin untuk membuka halaman ini.');
    }
}

function osis_require_any_role($roles)
{
    osis_require_login();
    if (! in_array(osis_current_user()['role'], (array) $roles, true)) {
        http_response_code(403);
        exit('Anda tidak memiliki izin untuk membuka halaman ini.');
    }
}

function osis_dashboard_path_for_role($role)
{
    if ($role === 'admin') {
        return 'admin/dashboard.php';
    }
    if ($role === 'pengurus') {
        return 'pengurus/dashboard.php';
    }

    return 'anggota/dashboard.php';
}

function osis_current_member_id()
{
    static $cache = [];
    $user = osis_current_user();
    if (! $user) {
        return 0;
    }
    $userId = (int) $user['id'];
    if (! array_key_exists($userId, $cache)) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT id FROM anggota WHERE user_id=? AND status='aktif' LIMIT 1");
        $stmt->execute([$userId]);
        $cache[$userId] = (int) $stmt->fetchColumn();
    }

    return $cache[$userId];
}

function osis_login_user($row)
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $row['id'],
        'nama' => $row['nama'],
        'username' => $row['username'],
        'email' => $row['email'],
        'role' => $row['role'],
    ];
}

function osis_format_date($date, $includeTime = false)
{
    if (! $date) {
        return '—';
    }
    $timestamp = strtotime($date);

    return $timestamp ? date($includeTime ? 'd/m/Y H:i' : 'd/m/Y', $timestamp) : osis_e($date);
}

function osis_log_activity($description)
{
    $directory = storage_path('logs/osis');
    if (! is_dir($directory)) {
        @mkdir($directory, 0755, true);
    }
    $user = osis_current_user();
    $entry = [
        'waktu' => date('Y-m-d H:i:s'),
        'pengguna' => $user ? $user['nama'] : 'Sistem',
        'peran' => $user ? $user['role'] : 'system',
        'aktivitas' => (string) $description,
    ];
    @file_put_contents($directory.'/aktivitas.log', json_encode($entry, JSON_UNESCAPED_UNICODE).PHP_EOL, FILE_APPEND | LOCK_EX);
}

function osis_store_uploaded_image($file, $subdirectory)
{
    if (! is_array($file) || ! isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Unggahan gagal. Periksa kembali file yang dipilih.');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('Ukuran gambar maksimal 5 MB.');
    }
    if (! is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('File unggahan tidak valid.');
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
    if ($finfo) {
        finfo_close($finfo);
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (! isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Format gambar harus JPG, PNG, atau WEBP.');
    }
    $folder = trim(str_replace('\\', '/', $subdirectory), '/');
    $directory = UPLOAD_ROOT.'/'.$folder;
    if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
        throw new RuntimeException('Folder unggahan tidak dapat dibuat.');
    }
    $filename = bin2hex(random_bytes(16)).'.'.$allowed[$mime];
    if (! move_uploaded_file($file['tmp_name'], $directory.'/'.$filename)) {
        throw new RuntimeException('Gambar tidak dapat disimpan.');
    }

    return $folder.'/'.$filename;
}

function osis_delete_uploaded_file($relativePath)
{
    if (! $relativePath) {
        return;
    }
    $relativePath = ltrim(str_replace('\\', '/', (string) $relativePath), '/');
    if (strpos($relativePath, '..') !== false) {
        return;
    }
    $base = realpath(UPLOAD_ROOT);
    $target = realpath(UPLOAD_ROOT.'/'.$relativePath);
    if ($base && $target && strpos($target, $base.DIRECTORY_SEPARATOR) === 0 && is_file($target)) {
        @unlink($target);
    }
}

function osis_image_url($relativePath)
{
    return $relativePath ? osis_url('uploads/'.ltrim($relativePath, '/')) : '';
}

function osis_store_task_attachment($file)
{
    if (! is_array($file) || ! isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ((int) $file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name']) || ! is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Unggahan dokumentasi gagal. Periksa kembali file yang dipilih.');
    }
    if ((int) $file['size'] > 8 * 1024 * 1024) {
        throw new RuntimeException('Ukuran setiap dokumentasi maksimal 8 MB.');
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
    if ($finfo) {
        finfo_close($finfo);
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
    if (! isset($allowed[$mime])) {
        throw new RuntimeException('Dokumentasi harus berupa JPG, PNG, WEBP, atau PDF.');
    }
    if (strpos($mime, 'image/') === 0 && @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('File gambar dokumentasi tidak valid.');
    }
    if ($mime === 'application/pdf' && file_get_contents($file['tmp_name'], false, null, 0, 5) !== '%PDF-') {
        throw new RuntimeException('File PDF dokumentasi tidak valid.');
    }
    $directory = UPLOAD_ROOT.'/tugas';
    if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
        throw new RuntimeException('Folder dokumentasi tugas tidak dapat dibuat.');
    }
    $filename = bin2hex(random_bytes(16)).'.'.$allowed[$mime];
    if (! move_uploaded_file($file['tmp_name'], $directory.'/'.$filename)) {
        throw new RuntimeException('Dokumentasi tidak dapat disimpan.');
    }

    return 'tugas/'.$filename;
}

function osis_is_image_attachment($relativePath)
{
    return in_array(strtolower(pathinfo((string) $relativePath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true);
}

function osis_post_string($key, $maxLength = 5000)
{
    $value = isset($_POST[$key]) ? trim((string) $_POST[$key]) : '';

    return mb_substr($value, 0, $maxLength, 'UTF-8');
}
