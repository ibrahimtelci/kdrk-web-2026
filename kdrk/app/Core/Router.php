<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Basit router. Desen örneği: /faaliyetler/{slug}
 * Linkler elle yazılmaz; url('faaliyet', ['slug' => ...]) ile üretilir (CLAUDE.md §13).
 */
final class Router
{
    /** @var array<int, array{method:string, pattern:string, regex:string, handler:callable|array, name:?string}> */
    private array $routes = [];
    /** @var array<string, string> ad => desen */
    private array $named = [];
    /** @var array<string, array{0:string, 1:array}> eski yol => [route adı, parametreler] (301) */
    private array $legacy = [];

    public function get(string $pattern, callable|array $handler, ?string $name = null): void
    {
        $this->add('GET', $pattern, $handler, $name);
    }

    public function post(string $pattern, callable|array $handler, ?string $name = null): void
    {
        $this->add('POST', $pattern, $handler, $name);
    }

    public function add(string $method, string $pattern, callable|array $handler, ?string $name = null): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[a-z0-9-]+)', rtrim($pattern, '/') ?: '/') . '/?$#';
        $this->routes[] = compact('method', 'pattern', 'regex', 'handler', 'name');
        if ($name !== null) {
            $this->named[$name] = $pattern;
        }
    }

    /** Eski prototip .html adresini yeni route'a kalıcı yönlendirir. */
    public function legacy(string $oldPath, string $routeName, array $params = []): void
    {
        $this->legacy[$oldPath] = [$routeName, $params];
    }

    public function url(string $name, array $params = []): string
    {
        if (!isset($this->named[$name])) {
            throw new \InvalidArgumentException("Tanımsız route: $name");
        }
        $path = preg_replace_callback('#\{(\w+)\}#', static function ($m) use (&$params, $name) {
            if (!isset($params[$m[1]])) {
                throw new \InvalidArgumentException("Route '$name' için '{$m[1]}' parametresi eksik");
            }
            $v = (string) $params[$m[1]];
            unset($params[$m[1]]);
            return rawurlencode($v);
        }, $this->named[$name]);
        $query = $params ? '?' . http_build_query($params) : '';
        return rtrim((string) App::config('base_url'), '/') . $path . $query;
    }

    public function has(string $name): bool
    {
        return isset($this->named[$name]);
    }

    public function dispatch(string $method, string $path): void
    {
        $base = (string) parse_url((string) App::config('base_url'), PHP_URL_PATH);
        if ($base !== '' && $base !== '/' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        $path = rawurldecode($path);

        if (isset($this->legacy[$path])) {
            [$name, $params] = $this->legacy[$path];
            $qs = $_SERVER['QUERY_STRING'] ?? '';
            redirect($this->url($name, $params) . ($qs !== '' ? '?' . $qs : ''), 301);
        }

        $allowed = [];
        foreach ($this->routes as $r) {
            if (!preg_match($r['regex'], $path, $m)) {
                continue;
            }
            if ($r['method'] !== $method && !($method === 'HEAD' && $r['method'] === 'GET')) {
                $allowed[] = $r['method'];
                continue;
            }
            $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
            $handler = $r['handler'];
            if (is_array($handler)) {
                [$class, $action] = $handler;
                $handler = [new $class(), $action];
            }
            $handler(...$params);
            return;
        }
        if ($allowed) {
            header('Allow: ' . implode(', ', array_unique($allowed)));
            throw new HttpException(405);
        }
        throw new HttpException(404);
    }
}
