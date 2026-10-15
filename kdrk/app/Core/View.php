<?php
declare(strict_types=1);

namespace App\Core;

/**
 * PHP şablonları. Görünüm dosyası içinde:
 *   $this->section('scripts') … $this->end()   → sayfaya özel script bloğu
 *   $this->partial('partials/activity-card', ['a' => $a])
 */
final class View
{
    private array $sections = [];
    private ?string $current = null;

    private function __construct(private array $data)
    {
    }

    /**
     * @param array $data Görünüme aktarılan değişkenler. 'page' anahtarı layout ayarlarını taşır:
     *   title, description, css[], navLight, nav (aktif grup), current (aktif route adı), robots
     */
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/site'): string
    {
        $v = new self($data);
        $content = $v->capture($view, $data);
        if ($layout === null) {
            return $content;
        }
        return $v->capture($layout, $data + ['content' => $content]);
    }

    public function partial(string $view, array $data = []): string
    {
        return $this->capture($view, $data + $this->data);
    }

    public function section(string $name): void
    {
        $this->current = $name;
        ob_start();
    }

    public function end(): void
    {
        $this->sections[$this->current] = ($this->sections[$this->current] ?? '') . ob_get_clean();
        $this->current = null;
    }

    public function yield(string $name): string
    {
        return $this->sections[$name] ?? '';
    }

    private function capture(string $view, array $data): string
    {
        $file = KDRK_ROOT . '/views/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("Görünüm bulunamadı: $view");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            include $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
