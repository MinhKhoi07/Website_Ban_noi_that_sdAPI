<?php
require_once __DIR__ . '/helpers.php';

enable_cors();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = get_json_input();
$token = $data['token'] ?? null;

// Allow Authorization header as well
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['Authorization'] ?? null);
if (!$token && $authHeader && stripos($authHeader, 'Bearer ') === 0) {
    $token = trim(substr($authHeader, 7));
}

if (!$token) {
    send_json(['success' => false, 'message' => 'missing_token'], 400);
}

// Validate signature and get payload (ignore blacklist)
$payload = validate_jwt($token, false);
if (!$payload || !isset($payload['jti']) || !isset($payload['exp'])) {
    send_json(['success' => false, 'message' => 'invalid_token'], 400);
}

blacklist_token($payload['jti'], $payload['exp']);

send_json(['success' => true, 'message' => 'token_revoked', 'jti' => $payload['jti']]);
