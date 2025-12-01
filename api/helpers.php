<?php
function send_json($data, $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

function get_json_input() {
    $data = json_decode(file_get_contents('php://input'), true);
    return is_array($data) ? $data : [];
}

function require_login() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    // First try session
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }

    // Next try Authorization Bearer token
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['Authorization'] ?? null);
    if ($authHeader) {
        if (stripos($authHeader, 'Bearer ') === 0) {
            $token = trim(substr($authHeader, 7));
            $payload = validate_jwt($token);
            if ($payload && isset($payload['user_id'])) {
                return $payload['user_id'];
            }
        }
    }

    send_json(['success' => false, 'message' => 'not_logged_in'], 401);
}

function enable_cors($allowed_origins = '*') {
    // Allow all origins by default; change to specific domain(s) if needed
    if ($allowed_origins === '*') {
        header('Access-Control-Allow-Origin: *');
    } else {
        header('Access-Control-Allow-Origin: ' . $allowed_origins);
    }
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

    // Handle preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// --- Minimal JWT helpers (no external deps) ---
// Note: for production use a vetted library. This is a small helper.
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $padlen = 4 - $remainder;
        $data .= str_repeat('=', $padlen);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function load_api_config() {
    $cfg = [];
    $file = __DIR__ . '/../config/api_config.php';
    if (file_exists($file)) {
        require $file;
        $cfg['secret'] = $API_JWT_SECRET ?? null;
        $cfg['lifetime'] = $API_JWT_LIFETIME ?? 86400;
    }
    return $cfg;
}

function generate_jwt($payload) {
    $cfg = load_api_config();
    $secret = $cfg['secret'] ?? null;
    $lifetime = $cfg['lifetime'] ?? 86400;
    if (!$secret) return null;

    $header = ['alg' => 'HS256', 'typ' => 'JWT'];
    $payload['iat'] = time();
    if (!isset($payload['exp'])) $payload['exp'] = time() + $lifetime;
    if (!isset($payload['jti'])) {
        try {
            $payload['jti'] = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            $payload['jti'] = uniqid('', true);
        }
    }

    $b64hdr = base64url_encode(json_encode($header));
    $b64pl = base64url_encode(json_encode($payload));
    $sig = hash_hmac('sha256', $b64hdr . '.' . $b64pl, $secret, true);
    $b64sig = base64url_encode($sig);
    return $b64hdr . '.' . $b64pl . '.' . $b64sig;
}

function validate_jwt($token, $checkBlacklist = true) {
    $cfg = load_api_config();
    $secret = $cfg['secret'] ?? null;
    if (!$secret) return null;

    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    list($b64hdr, $b64pl, $b64sig) = $parts;
    $dataToSign = $b64hdr . '.' . $b64pl;
    $sig = base64url_decode($b64sig);
    $calc = hash_hmac('sha256', $dataToSign, $secret, true);
    if (!hash_equals($calc, $sig)) return null;

    $payloadJson = base64url_decode($b64pl);
    $payload = json_decode($payloadJson, true);
    if (!$payload) return null;
    if (isset($payload['exp']) && time() > $payload['exp']) return null;

    // Check blacklist if requested
    if ($checkBlacklist && isset($payload['jti'])) {
        if (is_token_blacklisted($payload['jti'])) return null;
    }

    return $payload;
}

function get_blacklist_file() {
    return __DIR__ . '/token_blacklist.json';
}

function is_token_blacklisted($jti) {
    $file = get_blacklist_file();
    if (!file_exists($file)) return false;
    $raw = file_get_contents($file);
    $data = json_decode($raw, true);
    if (!is_array($data)) return false;

    // Clean expired entries
    $changed = false;
    foreach ($data as $k => $exp) {
        if ($exp < time()) {
            unset($data[$k]);
            $changed = true;
        }
    }
    if ($changed) {
        file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    return isset($data[$jti]);
}

function blacklist_token($jti, $exp) {
    $file = get_blacklist_file();
    $data = [];
    if (file_exists($file)) {
        $raw = file_get_contents($file);
        $data = json_decode($raw, true);
        if (!is_array($data)) $data = [];
    }
    $data[$jti] = $exp;
    // write atomically
    file_put_contents($file . '.tmp', json_encode($data, JSON_UNESCAPED_UNICODE));
    rename($file . '.tmp', $file);
}


