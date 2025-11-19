<?php
// Simple API helper to send consistent JSON responses
function send_json($success, $message = null, $data = null, $errors = null) {
    header('Content-Type: application/json');
    $out = ['success' => (bool)$success];
    if ($message !== null) $out['message'] = $message;
    if ($data !== null) $out['data'] = $data;
    if ($errors !== null) $out['errors'] = $errors;
    echo json_encode($out);
    exit;
}
