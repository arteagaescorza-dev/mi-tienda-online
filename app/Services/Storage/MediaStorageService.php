<?php

namespace App\Services\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStorageService
{
    /**
     * Determina el disco activo para medios públicos.
     * En local usa `public`; en producción cloud usa `supabase` o `s3`.
     */
    public function activeDisk(): string
    {
        $default = (string) config('filesystems.default', 'local');

        if (in_array($default, ['supabase', 's3'], true)) {
            return $default;
        }

        return 'public';
    }

    public function isRemoteDisk(?string $disk = null): bool
    {
        $target = $disk ?? $this->activeDisk();

        return in_array($target, ['supabase', 's3'], true);
    }

    /**
     * Almacena un archivo subido en el disco configurado (Supabase S3 o local public).
     *
     * @return array{path: string, url: string, filename: string, size: int, mime_type: string, disk: string}
     */
    public function storeUploadedFile(UploadedFile $file, string $directory, ?string $customFilename = null): array
    {
        $disk = $this->activeDisk();
        $cleanDir = trim($directory, '/');
        $extension = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'bin');
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeSlug = Str::slug(substr($baseName, 0, 40)) ?: 'media';
        $filename = $customFilename ?: ($safeSlug . '-' . time() . '-' . Str::random(6) . '.' . strtolower($extension));
        $storagePath = ($cleanDir !== '' ? $cleanDir . '/' : '') . $filename;

        $bytes = file_get_contents($file->getRealPath());
        $size = $file->getSize() ?: ($bytes !== false ? strlen($bytes) : 0);
        $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';

        Storage::disk($disk)->put($storagePath, $bytes, 'public');

        $url = $this->resolvePublicUrl($disk, $storagePath);

        return [
            'path' => $storagePath,
            'url' => $url,
            'filename' => $filename,
            'size' => (int) $size,
            'mime_type' => $mimeType,
            'disk' => $disk,
        ];
    }

    /**
     * Almacena bytes crudos (ej. WebP generado en memoria) en el disco configurado.
     */
    public function storeRawBytes(string $bytes, string $storagePath, ?string $diskOverride = null): array
    {
        $disk = $diskOverride && in_array($diskOverride, ['supabase', 's3'], true)
            ? $diskOverride
            : ($this->isRemoteDisk() ? $this->activeDisk() : ($diskOverride ?? 'public'));

        $cleanPath = ltrim($storagePath, '/');
        Storage::disk($disk)->put($cleanPath, $bytes, 'public');

        $url = $this->resolvePublicUrl($disk, $cleanPath);

        return [
            'path' => $cleanPath,
            'url' => $url,
            'disk' => $disk,
        ];
    }

    /**
     * Elimina un archivo dado su URL pública o ruta relativa.
     */
    public function deleteByUrl(?string $url): void
    {
        if (empty($url)) {
            return;
        }

        $disk = $this->activeDisk();

        if ($this->isRemoteDisk($disk)) {
            $baseUrl = rtrim((string) config("filesystems.disks.{$disk}.url"), '/');
            if ($baseUrl !== '' && str_starts_with($url, $baseUrl)) {
                $relative = ltrim(substr($url, strlen($baseUrl)), '/');
                if ($relative !== '') {
                    Storage::disk($disk)->delete($relative);
                }
                return;
            }
        }

        $cleanRelative = ltrim($url, '/');
        if (str_starts_with($cleanRelative, 'storage/')) {
            $diskPath = substr($cleanRelative, strlen('storage/'));
            Storage::disk('public')->delete($diskPath);
        }

        $publicFile = public_path($cleanRelative);
        if (file_exists($publicFile) && is_file($publicFile)) {
            @unlink($publicFile);
        }
    }

    /**
     * Genera una URL firmada (Presigned PUT) para subidas directas > 4.5 MB (videos MP4)
     * desde el navegador hacia Supabase Storage / S3 sin pasar por Vercel Functions.
     */
    public function createPresignedUploadUrl(
        string $directory,
        string $filename,
        string $contentType = 'application/octet-stream',
        int $expiresMinutes = 15
    ): ?array {
        $disk = $this->activeDisk();
        if (!$this->isRemoteDisk($disk)) {
            return null;
        }

        $cleanDir = trim($directory, '/');
        $safeName = Str::slug(pathinfo($filename, PATHINFO_FILENAME)) ?: 'upload';
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION) ?: 'bin');
        $storagePath = ($cleanDir !== '' ? $cleanDir . '/' : '') . $safeName . '-' . time() . '-' . Str::random(6) . '.' . $ext;

        $adapter = Storage::disk($disk);
        if (!method_exists($adapter, 'temporaryUploadUrl')) {
            return null;
        }

        $signed = $adapter->temporaryUploadUrl(
            $storagePath,
            now()->addMinutes($expiresMinutes),
            ['ContentType' => $contentType]
        );

        return [
            'upload_url' => $signed['url'] ?? null,
            'headers' => $signed['headers'] ?? ['Content-Type' => $contentType],
            'path' => $storagePath,
            'public_url' => $this->resolvePublicUrl($disk, $storagePath),
            'expires_in_minutes' => $expiresMinutes,
        ];
    }

    private function resolvePublicUrl(string $disk, string $storagePath): string
    {
        if ($this->isRemoteDisk($disk)) {
            return Storage::disk($disk)->url($storagePath);
        }

        // Asegurar copia en public/storage si no existe symlink en local
        $publicDiskRoot = Storage::disk($disk)->path('');
        $targetDiskFile = $publicDiskRoot . $storagePath;
        $publicWebPath = public_path('storage/' . $storagePath);
        if (!file_exists(dirname($publicWebPath))) {
            @mkdir(dirname($publicWebPath), 0755, true);
        }
        if (file_exists($targetDiskFile) && !file_exists($publicWebPath)) {
            @copy($targetDiskFile, $publicWebPath);
        }

        return '/storage/' . ltrim($storagePath, '/');
    }
}
