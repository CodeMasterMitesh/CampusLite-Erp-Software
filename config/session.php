<?php
// config/session.php
// Centralized session configuration and starter.
// Call start_secure_session() from entry points (index.php, api scripts, login.php).

function start_secure_session(): void {
    if (session_status() !== PHP_SESSION_NONE) return;

    // Determine secure flag (HTTPS)
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    // Default cookie params: session cookie only, httponly, samesite=Lax
    $cookieParams = session_get_cookie_params();
    $params = [
        'lifetime' => 0,
        'path' => '/',
        'domain' => $cookieParams['domain'] ?? '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ];

    // Use array signature when available (PHP 7.3+)
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($params);
    } else {
        // Fallback for older PHP: samesite not supported via API
        session_set_cookie_params($params['lifetime'], $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    ini_set('session.use_only_cookies', '1');
    session_start();
}

?>
