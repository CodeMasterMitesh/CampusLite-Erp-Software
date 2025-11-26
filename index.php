<?php
// public/index.php
require_once __DIR__ . '/config/session.php';
start_secure_session();
define('APP_INIT', true);
require_once __DIR__ . '/config/db.php';

// Load whitelist of pages
$pages = [];
$pagesFile = __DIR__ . '/config/pages.php';
if (file_exists($pagesFile)) {
    $pages = require $pagesFile;
}

// Determine requested page key
// Default to dashboard so root `index.php` shows dashboard for authenticated users
$page = $_GET['page'] ?? 'dashboard';

// If user is not logged in, force login page (except login itself)
// Accept multiple session keys for compatibility with existing code (user, user_id, udata)
$isAuthenticated = false;
if (!empty($_SESSION['user']['id']) || !empty($_SESSION['user_id']) || !empty($_SESSION['udata'])) {
    $isAuthenticated = true;
}
if ($page !== 'login' && !$isAuthenticated) {
    // redirect to standalone login page
    header('Location: login.php');
    exit;
}

// Serve page from whitelist
if (isset($pages[$page]) && file_exists($pages[$page])) {
    require_once $pages[$page];
} else {
    // fallback: show dashboard if available, else 404
    if (isset($pages['dashboard']) && file_exists($pages['dashboard'])) {
        require_once $pages['dashboard'];
    } else {
        http_response_code(404);
        echo 'Page not found.';
    }
}
?>
