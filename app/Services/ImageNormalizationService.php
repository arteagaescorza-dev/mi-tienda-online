<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageNormalizationService
{
    /**
     * Dimensiones oficiales para catálogo e-commerce (proporción 4:5 vertical).
     */
    public const TARGET_WIDTH = 1080;
    public const TARGET_HEIGHT = 1350;
    public const WEBP_QUALITY = 85;

    /**
     * Normaliza cualquier imagen subida (archivo o URL remota) a WebP 1080x1350 centrado sobre lienzo blanco.
     *
     * @param UploadedFile|string $input
     * @param string|null $disk
     * @param string $directory
     * @return array
     * @throws \Exception
     */
    public function normalizeToWebp($input, ?string $disk = 'public', string $directory = 'products'): array
    {
        $rawBytes = null;
        $originalFilename = 'product_img';

        if ($input instanceof UploadedFile) {
            $rawBytes = file_get_contents($input->getRealPath());
            $originalFilename = pathinfo($input->getClientOriginalName(), PATHINFO_FILENAME);
        } elseif (is_string($input)) {
            if (filter_var($input, FILTER_VALIDATE_URL)) {
                $ctx = stream_context_create([
                    'http' => [
                        'timeout' => 15,
                        'user_agent' => 'MiTiendaOnline-ImageOptimizer/1.0',
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ]);
                $rawBytes = @file_get_contents($input, false, $ctx);
                if ($rawBytes === false) {
                    throw new \Exception("No se pudo descargar la imagen remota desde la URL proporcionada.");
                }
                $originalFilename = pathinfo(parse_url($input, PHP_URL_PATH) ?? 'remote_img', PATHINFO_FILENAME);
            } elseif (file_exists($input)) {
                $rawBytes = file_get_contents($input);
                $originalFilename = pathinfo($input, PATHINFO_FILENAME);
            } else {
                throw new \Exception("La ruta de archivo o URL especificada no es válida.");
            }
        }

        if (empty($rawBytes)) {
            throw new \Exception("Contenido de imagen vacío o corrupto.");
        }

        // Crear recurso GD desde los bytes
        $sourceImage = @imagecreatefromstring($rawBytes);
        if (!$sourceImage) {
            throw new \Exception("Formato de imagen incompatible o corrupto. Se requiere JPG, PNG, WEBP, AVIF o BMP.");
        }

        $srcWidth = imagesx($sourceImage);
        $srcHeight = imagesy($sourceImage);

        // Crear lienzo estandarizado 1080x1350
        $targetImage = imagecreatetruecolor(self::TARGET_WIDTH, self::TARGET_HEIGHT);

        // Fondo blanco e-commerce
        $whiteBg = imagecolorallocate($targetImage, 255, 255, 255);
        imagefilledrectangle($targetImage, 0, 0, self::TARGET_WIDTH, self::TARGET_HEIGHT, $whiteBg);

        // Habilitar antialiasing para máxima nitidez
        if (function_exists('imageantialias')) {
            imageantialias($targetImage, true);
        }

        // Calcular escalado preservando aspecto (contain)
        $ratio = min(self::TARGET_WIDTH / $srcWidth, self::TARGET_HEIGHT / $srcHeight);
        $newWidth = (int) round($srcWidth * $ratio);
        $newHeight = (int) round($srcHeight * $ratio);

        // Centrar imagen en el lienzo
        $dstX = (int) round((self::TARGET_WIDTH - $newWidth) / 2);
        $dstY = (int) round((self::TARGET_HEIGHT - $newHeight) / 2);

        // Redimensionar con interpolación bilineal de alta calidad
        imagecopyresampled(
            $targetImage,
            $sourceImage,
            $dstX,
            $dstY,
            0,
            0,
            $newWidth,
            $newHeight,
            $srcWidth,
            $srcHeight
        );

        // Exportar a WebP en memoria
        ob_start();
        $webpSuccess = imagewebp($targetImage, null, self::WEBP_QUALITY);
        $webpBytes = ob_get_clean();

        // Liberar memoria GD
        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        if (!$webpSuccess || empty($webpBytes)) {
            throw new \Exception("Error al codificar imagen a formato WebP.");
        }

        // Generar nombre de archivo único WebP
        $cleanSlug = Str::slug(substr($originalFilename, 0, 40)) ?: 'prenda';
        $filename = "{$cleanSlug}-" . date('Ymd-His') . '-' . Str::random(6) . '.webp';
        $storagePath = trim($directory, '/') . '/' . $filename;

        // Guardar mediante MediaStorageService (Supabase S3 en cloud o disco public en local)
        $mediaStorage = app(\App\Services\Storage\MediaStorageService::class);
        $stored = $mediaStorage->storeRawBytes($webpBytes, $storagePath, $disk);

        $url = $stored['url'];
        $relativeUrl = str_starts_with($url, 'http') ? $url : $url;
        $fullUrl = str_starts_with($url, 'http') ? $url : url($url);

        return [
            'filename' => $filename,
            'path' => $stored['path'],
            'url' => $url,
            'relative_url' => $relativeUrl,
            'full_url' => $fullUrl,
            'format' => 'webp',
            'width' => self::TARGET_WIDTH,
            'height' => self::TARGET_HEIGHT,
            'size_bytes' => strlen($webpBytes),
        ];
    }
}
