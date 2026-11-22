<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Private server-to-server bridge. The shared secret never reaches a browser.
 * Database paths for new files have the form drive/{Google file ID}.{extension}.
 */
class GoogleDriveScriptStorage
{
    private function request(array $payload): array
    {
        $url = (string) config('osis.drive.script_url', '');
        $secret = (string) config('osis.drive.secret', '');
        if (! preg_match('~^https://script\.google\.com/macros/s/[A-Za-z0-9_-]+/exec$~', $url) || $secret === '') {
            throw new RuntimeException('Google Drive belum dikonfigurasi. Isi OSIS_DRIVE_SCRIPT_URL dan OSIS_DRIVE_SECRET.');
        }

        try {
            // Apps Script ContentService redirects its JSON response to googleusercontent.com.
            $response = Http::timeout(90)->connectTimeout(10)
                ->withOptions(['allow_redirects' => ['max' => 3]])
                ->asJson()->post($url, ['secret' => $secret] + $payload);
            if (! $response->successful()) {
                throw new RuntimeException('HTTP '.$response->status());
            }
            $result = $response->json();
            if (! is_array($result) || ($result['ok'] ?? false) !== true) {
                throw new RuntimeException(is_array($result) ? (string) ($result['error'] ?? 'Respons tidak valid.') : 'Respons tidak valid.');
            }
            return $result;
        } catch (\Throwable $e) {
            throw new RuntimeException('Gagal menghubungi penyimpanan Google Drive: '.$e->getMessage(), 0, $e);
        }
    }

    public function upload(string $temporaryPath, string $name, string $mime, string $category): string
    {
        $content = file_get_contents($temporaryPath);
        if ($content === false) {
            throw new RuntimeException('File unggahan tidak dapat dibaca.');
        }
        $result = $this->request([
            'action' => 'upload', 'category' => $category, 'name' => $name,
            'mimeType' => $mime, 'data' => base64_encode($content),
        ]);
        $id = (string) ($result['id'] ?? '');
        if (! self::validId($id)) {
            throw new RuntimeException('ID file dari Google Drive tidak valid.');
        }
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
        if (! isset($extensions[$mime])) {
            throw new RuntimeException('Tipe file tidak diizinkan.');
        }
        return 'drive/'.$id.'.'.$extensions[$mime];
    }

    public function read(string $id): array
    {
        if (! self::validId($id)) {
            throw new RuntimeException('ID file tidak valid.');
        }
        $result = $this->request(['action' => 'read', 'id' => $id]);
        $mime = (string) ($result['mimeType'] ?? '');
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true)) {
            throw new RuntimeException('Tipe file tidak diizinkan.');
        }
        $data = base64_decode((string) ($result['data'] ?? ''), true);
        if ($data === false || strlen($data) > 8 * 1024 * 1024) {
            throw new RuntimeException('Isi file tidak valid atau terlalu besar.');
        }
        return ['content' => $data, 'mimeType' => $mime];
    }

    public function delete(string $id): void
    {
        if (! self::validId($id)) {
            throw new RuntimeException('ID file tidak valid.');
        }
        $this->request(['action' => 'delete', 'id' => $id]);
    }

    public static function validId(string $id): bool
    {
        return (bool) preg_match('/\A[A-Za-z0-9_-]{10,128}\z/D', $id);
    }
}
