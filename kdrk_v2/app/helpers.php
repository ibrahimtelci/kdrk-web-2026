<?php
declare(strict_types=1);

use App\Core\App;
use App\Core\Csrf;
use App\Core\HttpException;
use App\Core\Settings;

/** HTML çıktısı için kaçış — her dinamik değer bununla basılır (CLAUDE.md §15). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Ada göre route adresi: url('faaliyet', ['slug' => 'kackar-zirve-2027']) */
function url(string $name, array $params = []): string
{
    return App::router()->url($name, $params);
}

/** public_html/assets altındaki dosya; değişince önbellek kırılsın diye ?v=<filemtime>. */
function asset(string $path): string
{
    $file = App::config('public_path') . '/assets/' . $path;
    $v = is_file($file) ? '?v=' . filemtime($file) : '';
    return rtrim((string) App::config('base_url'), '/') . '/assets/' . $path . $v;
}

/**
 * Görsel adresi. Veritabanında ya yüklenen dosyanın göreli yolu (uploads/…) ya da
 * geçiş dönemi için tam URL (Unsplash yer tutucu) durur.
 */
/** Uzantısız temel yol mu? (uploads/… panel yüklemesi ya da assets/… sabit içerik; -400/-900/-1600/-2400.webp türevleri) */
function is_variant_base(string $src): bool
{
    return (str_starts_with($src, 'uploads/') || str_starts_with($src, 'assets/')) && !preg_match('/\.\w{3,4}$/', $src);
}

function img(?string $src, int $width = 900): string
{
    if ($src === null || $src === '') {
        return '';
    }
    if (preg_match('#^https://images\.unsplash\.com/#', $src)) {
        return strtok($src, '?') . '?w=' . $width . '&q=80&auto=format&fit=crop';
    }
    if (preg_match('#^https?://#', $src)) {
        return $src;
    }
    // Panelden yüklenen (uploads/) ya da sabit içerik (assets/) görseli: uzantısız temel yol → en uygun WebP türevi
    if (is_variant_base($src)) {
        $src = \App\Core\Image::variant($src, $width);
    }
    return rtrim((string) App::config('base_url'), '/') . '/' . ltrim($src, '/');
}

/**
 * srcset dizgesi (CLAUDE.md §16). Yüklenen görselde yalnızca diskte olan türevler,
 * Unsplash'ta tüm genişlikler yazılır; başka tam URL'de boş döner.
 */
function img_srcset(?string $src): string
{
    if ($src === null || $src === '') {
        return '';
    }
    $widths = [];
    if (preg_match('#^https://images\.unsplash\.com/#', $src)) {
        $widths = \App\Core\Image::WIDTHS;
    } elseif (is_variant_base($src)) {
        $root = App::config('public_path') . '/';
        $widths = array_filter(\App\Core\Image::WIDTHS, static fn (int $w) => is_file("$root$src-$w.webp"));
    }
    return implode(', ', array_map(static fn (int $w) => img($src, $w) . " {$w}w", $widths));
}

/**
 * Kutuyu dolduran <img> (.img-cover). Hero görselinde $lazy = false verilir.
 * $sizes örnek: '(min-width: 1100px) 33vw, (min-width: 640px) 50vw, 100vw'
 */
function img_tag(?string $src, string $sizes, int $fallback = 900, string $alt = '', bool $lazy = true): string
{
    if ($src === null || $src === '') {
        return '';
    }
    $srcset = img_srcset($src);
    return '<img class="img-cover" src="' . e(img($src, $fallback)) . '"'
        . ($srcset !== '' ? ' srcset="' . e($srcset) . '" sizes="' . e($sizes) . '"' : '')
        . ' alt="' . e($alt) . '"'
        . ($lazy ? ' loading="lazy" decoding="async"' : ' fetchpriority="high"') . '>';
}

/** Panel ikonu (app/admin-icons.php). */
function icon(string $name, int $size = 18): string
{
    static $icons = null;
    $icons ??= require KDRK_ROOT . '/app/admin-icons.php';
    return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? '') . '</svg>';
}

/** Düz metni paragraflara çevirir (boş satır = yeni paragraf). Panelden HTML girilmez; XSS riski yok. */
function paragraphs(?string $text): string
{
    $out = '';
    foreach (preg_split('/\R{2,}/', trim((string) $text)) ?: [] as $p) {
        if (trim($p) !== '') {
            $out .= '<p>' . nl2br(e(trim($p)), false) . "</p>\n";
        }
    }
    return $out;
}

/** Panelden girilen bağlantı: "/faaliyetler" gibi site içi yol ya da tam adres. */
function site_url(?string $path): string
{
    if ($path === null || $path === '') {
        return '';
    }
    return str_starts_with($path, '/') ? rtrim((string) App::config('base_url'), '/') . $path : $path;
}

/** Metindeki {yil}, {zirve}, {yuruyus}, {aramaKurtarma}, {kurulus} belirteçlerini kulüp rakamlarıyla doldurur. */
function fill_stats(?string $text): string
{
    $map = [];
    foreach (Settings::stats() as $k => $v) {
        $map['{' . $k . '}'] = (string) $v;
    }
    return strtr((string) $text, $map);
}

/** Kulüp rakamı (KDRK_STATS tek kaynağı settings tablosunda). */
function club_stat(string $key): int
{
    return Settings::stats()[$key] ?? 0;
}

function setting(string $key, ?string $default = null): ?string
{
    return Settings::get($key, $default);
}

function csrf_field(): string
{
    return Csrf::field();
}

function redirect(string $to, int $status = 302): never
{
    header('Location: ' . $to, true, $status);
    exit;
}

function abort(int $status = 404): never
{
    throw new HttpException($status);
}

const TR_MONTHS = ['OCA', 'ŞUB', 'MAR', 'NİS', 'MAY', 'HAZ', 'TEM', 'AĞU', 'EYL', 'EKİ', 'KAS', 'ARA'];
const TR_MONTHS_LONG = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];

/** Mono etiket tarihi: 12 TEM 2026 */
function tr_date(string $ymd, bool $withYear = true): string
{
    $t = strtotime($ymd);
    return date('d', $t) . ' ' . TR_MONTHS[(int) date('n', $t) - 1] . ($withYear ? ' ' . date('Y', $t) : '');
}

function tr_month(string $ymd): string
{
    return TR_MONTHS[(int) date('n', strtotime($ymd)) - 1];
}

/** Uzun tarih: 12 Temmuz 2026 */
function tr_date_long(string $ymd): string
{
    $t = strtotime($ymd);
    return (int) date('j', $t) . ' ' . TR_MONTHS_LONG[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
}

/** Tutar: ₺1.500 / ₺1.500,50 (kuruş yalnız varsa yazılır). */
function tr_money(float $v): string
{
    return '₺' . number_format($v, fmod($v, 1.0) === 0.0 ? 0 : 2, ',', '.');
}

/** Elle girilen tutar: "1.500,50", "1500.5", "₺ 750" → float; geçersizse null. */
function parse_money(string $raw): ?float
{
    $v = str_replace(['₺', 'TL', 'tl', ' ', "\u{00A0}"], '', trim($raw));
    if (str_contains($v, ',')) {
        $v = str_replace(['.', ','], ['', '.'], $v);
    } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $v)) {
        $v = str_replace('.', '', $v); // "1.500" Türkçe yazımda bin beş yüzdür
    }
    return $v !== '' && is_numeric($v) && (float) $v >= 0 && (float) $v <= 1000000 ? round((float) $v, 2) : null;
}

/** Tarih aralığı: 12–15 TEM 2026 / 30 TEM – 02 AĞU 2026 */
function tr_range(string $start, ?string $end): string
{
    if ($end === null || $end === $start) {
        return tr_date($start);
    }
    $s = strtotime($start);
    $f = strtotime($end);
    if (date('Y-m', $s) === date('Y-m', $f)) {
        return date('d', $s) . '–' . tr_date($end);
    }
    return tr_date($start, date('Y', $s) !== date('Y', $f)) . ' – ' . tr_date($end);
}

/** Türkçe büyük harf (i → İ, ı → I). */
function tr_upper(string $s): string
{
    return mb_strtoupper(strtr($s, ['i' => 'İ', 'ı' => 'I']), 'UTF-8');
}

/** URL dostu kısa ad: "Kaçkar Zirve Tırmanışı" → "kackar-zirve-tirmanisi" */
function slugify(string $s): string
{
    $s = strtr($s, ['ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'I' => 'i', 'İ' => 'i',
        'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u', 'â' => 'a', 'î' => 'i', 'û' => 'u']);
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim((string) $s, '-');
}

/** Standard icon for a server-rendered list filter. Unknown states use the filter glyph. */
function admin_filter_icon(string $key): string
{
    return match ($key) {
        '', 'tumu', 'all' => 'view-grid',
        'yaklasan', 'upcoming', 'scheduled' => 'activities',
        'gecmis', 'past' => 'history',
        'bekleyen', 'pending' => 'clock',
        'published', 'yayinda', 'active', 'approved' => 'publish',
        'draft', 'taslak' => 'edit',
        'unread', 'okunmamis' => 'mail',
        'waitlist' => 'users',
        'rejected', 'cancelled', 'inactive' => 'x',
        default => 'filter',
    };
}

/**
 * Panel listesinin görünümü (tablo | kart). Sıra: ?gorunum= → kdrk_view_<anahtar> çerezi → varsayılan.
 * Adresle seçilen görünüm çereze yazılır; JS kapalıyken de tercih hatırlanır.
 */
function admin_list_view(string $key, string $default = 'tablo'): string
{
    $views = ['tablo', 'kart'];
    $cookie = 'kdrk_view_' . $key;
    $asked = $_GET['gorunum'] ?? null;
    if (is_string($asked) && in_array($asked, $views, true)) {
        if (!headers_sent()) {
            setcookie($cookie, $asked, ['expires' => time() + 31536000, 'path' => '/yonetim', 'samesite' => 'Lax', 'httponly' => false, 'secure' => !empty($_SERVER['HTTPS'])]);
        }
        return $asked;
    }
    $saved = $_COOKIE[$cookie] ?? null;
    return is_string($saved) && in_array($saved, $views, true) ? $saved : $default;
}
