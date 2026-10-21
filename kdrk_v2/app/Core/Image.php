<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Görsel yükleme (CLAUDE.md §15, §16).
 * - Tür finfo ile doğrulanır (jpg/png/webp), en fazla 15 MB
 * - GD ile yeniden kodlanır: EXIF ve gömülü içerik atılır, telefon fotoğrafının yönü düzeltilir
 * - Panelde kapak kırpıldıysa ($crop) özgün görsel önce o alana kırpılır, türevler kırpılmış hâlden üretilir
 * - WebP türevleri: 400 / 900 / 1600 / 2400 px genişlik (özgünden büyük türev üretilmez)
 * - Dosya adı rastgeledir; özgün ad diske yazılmaz
 * Veritabanına uzantısız temel yol yazılır: uploads/activities/3f9c… → img() uygun boyutu seçer.
 */
final class Image
{
    public const WIDTHS = [400, 900, 1600, 2400];
    public const MAX_BYTES = 15 * 1024 * 1024;
    private const TYPES = ['image/jpeg' => 'jpeg', 'image/png' => 'png', 'image/webp' => 'webp'];

    /**
     * @param array      $file $_FILES içindeki tek dosya
     * @param array|null $crop parseCrop() çıktısı: yönü düzeltilmiş görselde 0–1 oranında [x, y, w, h]
     * @return string temel yol (uploads/<klasör>/<ad>)
     * @throws \RuntimeException kullanıcıya gösterilebilir Türkçe mesajla
     */
    public static function store(array $file, string $folder, ?array $crop = null): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(match ($file['error'] ?? 0) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Dosya çok büyük (en fazla 15 MB).',
                UPLOAD_ERR_NO_FILE => 'Dosya seçilmedi.',
                default => 'Dosya yüklenemedi, tekrar dene.',
            });
        }
        if ($file['size'] > self::MAX_BYTES) {
            throw new \RuntimeException('Dosya çok büyük (en fazla 15 MB).');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::TYPES[$mime])) {
            throw new \RuntimeException('Yalnızca JPG, PNG veya WebP görsel yüklenebilir.');
        }
        $src = @('imagecreatefrom' . self::TYPES[$mime])($file['tmp_name']);
        if (!$src) {
            throw new \RuntimeException('Görsel okunamadı; dosya bozuk olabilir.');
        }
        if ($mime === 'image/jpeg') {
            $src = self::orient($src, $file['tmp_name']);
        }
        if ($crop !== null) {
            $src = self::crop($src, $crop);
        }

        $folder = trim(preg_replace('/[^a-z0-9-]/', '', $folder), '-') ?: 'misc';
        $dir = App::config('public_path') . '/uploads/' . $folder;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException('Yükleme klasörü oluşturulamadı.');
        }
        $name = bin2hex(random_bytes(12));
        $w = imagesx($src);
        $h = imagesy($src);
        foreach (self::WIDTHS as $i => $target) {
            if ($i > 0 && $target > $w) {
                break; // küçük görseli büyütme
            }
            $tw = min($target, $w);
            $th = (int) round($h * $tw / $w);
            $dst = imagecreatetruecolor($tw, $th);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $tw, $th, $w, $h);
            imagewebp($dst, "$dir/$name-$target.webp", 80);
            imagedestroy($dst);
        }
        imagedestroy($src);
        return "uploads/$folder/$name";
    }

    /** Var olan en büyük türevi bulur (img() için). */
    public static function variant(string $base, int $want): string
    {
        $root = App::config('public_path') . '/';
        foreach (self::WIDTHS as $w) {
            if ($w >= $want && is_file("$root$base-$w.webp")) {
                return "$base-$w.webp";
            }
        }
        foreach (array_reverse(self::WIDTHS) as $w) {
            if (is_file("$root$base-$w.webp")) {
                return "$base-$w.webp";
            }
        }
        return "$base-400.webp";
    }

    /** @return array{0: ?int, 1: ?int} en büyük türevin genişlik/yüksekliği (masonry oranı için) */
    public static function size(string $base): array
    {
        $info = @getimagesize(App::config('public_path') . '/' . self::variant($base, 2400));
        return $info ? [$info[0], $info[1]] : [null, null];
    }

    public static function delete(?string $base): void
    {
        if ($base === null || !str_starts_with($base, 'uploads/') || str_contains($base, '..')) {
            return;
        }
        foreach (self::WIDTHS as $w) {
            $f = App::config('public_path') . "/$base-$w.webp";
            if (is_file($f)) {
                unlink($f);
            }
        }
    }

    /**
     * Paneldeki kırpma penceresinin gönderdiği "x,y,w,h" (0–1 arası oranlar). Geçersizse null: görsel kırpılmadan saklanır.
     * @return array{0: float, 1: float, 2: float, 3: float}|null
     */
    public static function parseCrop(mixed $value): ?array
    {
        // Her sayı ondalıklı olabilir: "1", "1.0000", "0.25", ".5" (panel toFixed(4) ile gönderir)
        $num = '(?:\d(?:\.\d{1,8})?|\.\d{1,8})';
        if (!is_string($value) || !preg_match("/^$num(?:,$num){3}$/", trim($value))) {
            return null;
        }
        [$x, $y, $w, $h] = array_map('floatval', explode(',', trim($value)));
        if ($w < 0.02 || $h < 0.02 || $w > 1.001 || $h > 1.001 || $x + $w > 1.001 || $y + $h > 1.001) {
            return null;
        }
        return [$x, $y, min($w, 1 - $x), min($h, 1 - $y)];
    }

    private static function crop(\GdImage $img, array $crop): \GdImage
    {
        [$x, $y, $w, $h] = $crop;
        $iw = imagesx($img);
        $ih = imagesy($img);
        $rect = [
            'x'      => (int) round($x * $iw),
            'y'      => (int) round($y * $ih),
            'width'  => max(1, min($iw, (int) round($w * $iw))),
            'height' => max(1, min($ih, (int) round($h * $ih))),
        ];
        $rect['width'] = min($rect['width'], $iw - $rect['x']);
        $rect['height'] = min($rect['height'], $ih - $rect['y']);
        $out = imagecrop($img, $rect);
        if (!$out) {
            return $img;
        }
        imagedestroy($img);
        return $out;
    }

    private static function orient(\GdImage $img, string $path): \GdImage
    {
        if (!function_exists('exif_read_data')) {
            return $img;
        }
        $o = (int) (@exif_read_data($path)['Orientation'] ?? 1);
        $rotated = match ($o) {
            3 => imagerotate($img, 180, 0),
            6 => imagerotate($img, -90, 0),
            8 => imagerotate($img, 90, 0),
            default => null,
        };
        return $rotated ?: $img;
    }
}
