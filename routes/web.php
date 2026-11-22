<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Existing local media remains readable; new media is fetched privately from Drive.
Route::get('/uploads/{path}', function (string $path) {
    if (preg_match('~\Adrive/([A-Za-z0-9_-]{10,128})\.(jpg|png|webp|pdf)\z~', $path, $matches)) {
        try {
            $media = app(\App\Services\GoogleDriveScriptStorage::class)->read($matches[1]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal membaca file Drive: '.$e->getMessage());
            abort(502, 'Media Google Drive tidak tersedia.');
        }
        $extensionMime = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'pdf' => 'application/pdf'];
        abort_unless($media['mimeType'] === $extensionMime[$matches[2]], 404);
        return response($media['content'], 200, [
            'Content-Type' => $media['mimeType'],
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
    abort_unless((bool) preg_match('~\A(?:anggota|pengurus|kegiatan|galeri|laporan|tugas)/[a-zA-Z0-9_-]+\.(?:jpe?g|png|webp|pdf)\z~i', $path), 404);
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
