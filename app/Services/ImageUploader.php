<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Stores uploaded images on the public disk. When the GD extension is available (it is on virtually all cPanel hosts)
 * large photos are scaled down to $maxEdge px and re-encoded, which keeps product pages fast and strips camera
 * metadata. If GD is missing or the file cannot be decoded, the original is stored unchanged.
 */
class ImageUploader
{
    public static function store(UploadedFile $file, string $dir, int $maxEdge = 1600): string
    {
        $resized = self::resize($file, $maxEdge);
        if ($resized === null) {
            return $file->store($dir, 'public');
        }

        [$binary, $ext] = $resized;
        $path = trim($dir, '/').'/'.Str::random(40).'.'.$ext;
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $binary);

        return $path;
    }

    /** @return array{0: string, 1: string}|null  [binary, extension] */
    private static function resize(UploadedFile $file, int $maxEdge): ?array
    {
        if (! extension_loaded('gd')) {
            return null;
        }
        $info = @getimagesize($file->getRealPath());
        if (! $info) {
            return null;
        }
        [$w, $h, $type] = $info;
        $img = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file->getRealPath()),
            IMAGETYPE_PNG => @imagecreatefrompng($file->getRealPath()),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getRealPath()) : false,
            default => false,
        };
        if (! $img) {
            return null;
        }

        // Respect camera rotation for JPEGs.
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $orientation = @exif_read_data($file->getRealPath())['Orientation'] ?? 1;
            $img = match ($orientation) {
                3 => imagerotate($img, 180, 0) ?: $img,
                6 => imagerotate($img, -90, 0) ?: $img,
                8 => imagerotate($img, 90, 0) ?: $img,
                default => $img,
            };
            $w = imagesx($img);
            $h = imagesy($img);
        }

        if (max($w, $h) > $maxEdge) {
            $scale = $maxEdge / max($w, $h);
            $scaled = imagescale($img, max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale)));
            if ($scaled) {
                $img = $scaled;
            }
        }

        ob_start();
        if ($type === IMAGETYPE_PNG) {
            imagesavealpha($img, true);
            imagepng($img, null, 7);
            $ext = 'png';
        } elseif ($type === IMAGETYPE_WEBP && function_exists('imagewebp')) {
            imagewebp($img, null, 85);
            $ext = 'webp';
        } else {
            imageinterlace($img, true);
            imagejpeg($img, null, 85);
            $ext = 'jpg';
        }
        $binary = (string) ob_get_clean();

        return $binary === '' ? null : [$binary, $ext];
    }
}
