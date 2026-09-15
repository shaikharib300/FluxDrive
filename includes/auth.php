<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}

function verify_csrf(?string $token): void {
    if (!$token || !hash_equals($_SESSION['_csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid CSRF token.');
    }
}

function login_user(int $userId): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p=session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function current_user(): ?array {
    static $user = null;
    static $loaded = false;
    if ($loaded) return $user;
    $loaded = true;
    if (empty($_SESSION['user_id'])) return null;
    $st=db()->prepare('SELECT * FROM users WHERE id=? LIMIT 1');
    $st->execute([(int)$_SESSION['user_id']]);
    return $user=$st->fetch() ?: null;
}

function require_auth(): array {
    $u=current_user();
    if (!$u) {
        if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            json_response(['error'=>'Authentication required.'], 401);
        }
        header('Location: /FluxDrive/public/login.php');
        exit;
    }
    return $u;
}
