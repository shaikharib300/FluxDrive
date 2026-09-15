<?php
declare(strict_types=1);

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

if (is_file(__DIR__ . '/../.env')) {
    Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

function envv(string $key, ?string $default = null): ?string {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

date_default_timezone_set('Asia/Kolkata');

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
