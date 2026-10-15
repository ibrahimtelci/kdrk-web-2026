<?php
declare(strict_types=1);

namespace App\Core;

/** KVKK alanları (TC kimlik, sağlık notu) için şifreleme (CLAUDE.md §15). */
final class Crypto
{
    private static function key(): string
    {
        $key = base64_decode((string) App::config('app_key'), true);
        if ($key === false || strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new \RuntimeException('config.php içindeki app_key geçersiz (base64, 32 bayt olmalı).');
        }
        return $key;
    }

    public static function encrypt(?string $plain): ?string
    {
        if ($plain === null || $plain === '') {
            return null;
        }
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        return $nonce . sodium_crypto_secretbox($plain, $nonce, self::key());
    }

    public static function decrypt(?string $cipher): ?string
    {
        if ($cipher === null || $cipher === '') {
            return null;
        }
        $nonce = substr($cipher, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plain = sodium_crypto_secretbox_open(substr($cipher, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES), $nonce, self::key());
        return $plain === false ? null : $plain;
    }
}
