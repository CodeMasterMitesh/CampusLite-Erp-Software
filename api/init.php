<?php
// api/init.php - centralized session start and auth enforcement for API endpoints
// Mark that the application is initialized so controllers/views included by APIs allow access
if (!defined('APP_INIT')) define('APP_INIT', true);
require_once __DIR__ . '/../config/session.php';
start_secure_session();

// list of API scripts that are allowed to be public (no session required)
$publicApis = [
    'auth.php', // login/register endpoints
];

$currentScript = basename($_SERVER['SCRIPT_FILENAME']);
if (!in_array($currentScript, $publicApis)) {
    if (empty($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['status' => false, 'message' => 'Unauthorized']);
        exit;
    }
}
