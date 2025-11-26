<?php
// Development helper: returns server-side session info as JSON
// REMOVE or protect this endpoint in production
require_once __DIR__ . '/../config/session.php';
start_secure_session();

header('Content-Type: application/json');

$response = [
    'ok' => true,
    'server_time' => date('c'),
    'session_name' => session_name(),
    'session_id_server' => session_id(),
    'cookie_session' => isset($_COOKIE[session_name()]) ? $_COOKIE[session_name()] : null,
    'session_data' => $_SESSION,
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
];

echo json_encode($response, JSON_PRETTY_PRINT);
