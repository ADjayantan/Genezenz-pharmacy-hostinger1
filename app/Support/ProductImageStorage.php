<?php
declare(strict_types=1);
namespace App\Support;

use RuntimeException;

final class ProductImageStorage
{
    public static function validate(string $path, string $name): array
    {
        if (!is_file($path) || filesize($path) < 1 || filesize($path) > 5 * 1024 * 1024) {
            throw new RuntimeException('Product image must be between 1 byte and 5 MB.');
        }
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg','jpeg','png','webp'], true)) throw new RuntimeException('Use a JPG, PNG or WebP image.');
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, ['image/jpeg','image/png','image/webp'], true)) throw new RuntimeException('The file is not a supported image.');
        $size = @getimagesize($path);
        if (!$size || $size[0] < 1 || $size[1] < 1 || $size[0] > 4096 || $size[1] > 4096 || $size[0] * $size[1] > 12000000) {
            throw new RuntimeException('Use an image up to 4096 pixels per side and 12 megapixels.');
        }
        return ['width'=>$size[0], 'height'=>$size[1], 'mime'=>$mime];
    }

    public static function store(array $file): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        if (($file['error'] ?? null) !== UPLOAD_ERR_OK || !is_uploaded_file((string)($file['tmp_name'] ?? ''))) throw new RuntimeException('The product image could not be uploaded.');
        if (!extension_loaded('gd') || !function_exists('imagewebp')) throw new RuntimeException('Enable PHP GD with WebP support to upload product images.');
        $path = (string)$file['tmp_name'];
        $size = self::validate($path, (string)$file['name']);
        $image = @imagecreatefromstring(file_get_contents($path));
        if (!$image) throw new RuntimeException('The image could not be decoded.');
        $scaled = false;
        $destination = null;
        try {
            $ratio = min(1, 1600 / max($size['width'], $size['height']));
            $scaled = imagescale($image, max(1,(int)round($size['width']*$ratio)), max(1,(int)round($size['height']*$ratio)));
            if (!$scaled) throw new RuntimeException('The image could not be resized.');
            imagealphablending($scaled, false);
            imagesavealpha($scaled, true);
            $directory = PUBLIC_PATH.'/uploads/products';
            if (!is_dir($directory) && !mkdir($directory,0755,true)) throw new RuntimeException('Product image storage is unavailable.');
            $name = bin2hex(random_bytes(20)).'.webp';
            $destination = $directory.'/'.$name;
            if (!imagewebp($scaled,$destination,82) || !is_file($destination) || filesize($destination) === 0) throw new RuntimeException('The image could not be saved.');
            return '/uploads/products/'.$name;
        } catch (\Throwable $error) {
            if ($destination && is_file($destination)) unlink($destination);
            throw $error;
        } finally {
            imagedestroy($image);
            if ($scaled) imagedestroy($scaled);
        }
    }

    public static function discard(?string $url): void
    {
        // Only newly generated upload paths can be cleaned up after a failed product save.
        if ($url && preg_match('#^/uploads/products/[a-f0-9]{40}\.webp$#D',$url)) {
            $path = PUBLIC_PATH.$url;
            if (is_file($path)) unlink($path);
        }
    }
}
