<?php
require_once __DIR__ . '/helpers.php';

// Enable CORS for cross-origin clients (adjust origin if needed)
enable_cors();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

if (session_status() === PHP_SESSION_NONE) session_start();
// If Authorization: Bearer <token> provided, blacklist the token
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['Authorization'] ?? null);
if ($authHeader && stripos($authHeader, 'Bearer ') === 0) {
    $token = trim(substr($authHeader, 7));
    // Validate signature and get payload (ignore blacklist when checking for revocation)
    $payload = validate_jwt($token, false);
    if ($payload && isset($payload['jti']) && isset($payload['exp'])) {
        blacklist_token($payload['jti'], $payload['exp']);
    }
}

// Destroy session as well
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}
session_destroy();

send_json([
    'success' => true,
    'message' => 'Logged out (session destroyed, token revoked if provided).',
    'note' => 'If you used a JWT token, it is now blacklisted until its expiry.'
]);
