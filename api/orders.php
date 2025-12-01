<?php
require_once __DIR__ . '/helpers.php';

// Enable CORS for cross-origin clients (adjust origin if needed)
enable_cors();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'OPTIONS') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

// If request is multipart/form-data, PHP will populate $_POST and $_FILES automatically.
// If it's JSON, decode and populate $_POST so existing `process_order.php` can use it.
$contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'multipart/form-data') === false) {
    $data = get_json_input();
    if (is_array($data) && count($data) > 0) {
        // Merge JSON body into $_POST so process_order.php can read expected fields
        $_POST = array_merge($_POST, $data);
    }
}

if (session_status() === PHP_SESSION_NONE) session_start();

// Change working directory to project root so relative includes in process_order.php work
chdir(__DIR__ . '/..');

// Include existing process_order.php which outputs JSON. It will use $_POST/$_FILES and session.
include __DIR__ . '/../process_order.php';

// If include returns control unexpectedly, send a generic error
send_json(['success' => false, 'message' => 'order_processing_failed'], 500);
