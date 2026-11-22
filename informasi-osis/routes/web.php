<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// The original application stores image/PDF uploads outside Laravel's web root.
Route::get('/uploads/{path}', function (string $path) {
    if (!preg_match('~\A(?:anggota|pengurus|kegiatan|galeri|laporan|tugas)/[a-zA-Z0-9_-]+\.(?:jpe?g|png|webp|pdf)\z~i', $path)) {
        abort(404);
    }
    $base = realpath(storage_path('app/osis-uploads'));
    $file = realpath(storage_path('app/osis-uploads/'.$path));
    abort_unless($base && $file && str_starts_with($file, $base.DIRECTORY_SEPARATOR) && is_file($file), 404);
    return response()->file($file, ['X-Content-Type-Options' => 'nosniff']);
})->where('path', '.*');

// Intentionally outside the Laravel web middleware: the legacy app uses its own
// native PHP session and _csrf field, not Laravel's session/CSRF middleware.
Route::match(['GET', 'POST'], '/{path?}', function (Request $request, ?string $path = null) {
    $path = $path ?: 'index.php';
    abort_unless(in_array($path, config('osis.legacy_routes'), true), 404);
    $file = base_path('legacy/'.$path);
    abort_unless(is_file($file), 404);

    // Required because legacy helper functions use global $pdo.
    global $pdo;
    $_SERVER['SCRIPT_NAME'] = $request->getBaseUrl().'/'.$path;
    ob_start();
    try {
        require $file;
        $output = ob_get_clean();
    } catch (Throwable $e) {
        ob_end_clean();
        throw $e;
    }
    $contentType = null;
    foreach (headers_list() as $header) {
        if (stripos($header, 'Content-Type:') === 0) {
            $contentType = trim(substr($header, strlen('Content-Type:')));
        }
    }
    return response($output, http_response_code() ?: 200, $contentType ? ['Content-Type' => $contentType] : []);
})->where('path', '.*');
