<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = get_json_input();
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    send_json(['success' => false, 'message' => 'missing_credentials'], 400);
}

if (session_status() === PHP_SESSION_NONE) session_start();

// Check admin
$admin_sql = "SELECT * FROM administrators WHERE username = ? LIMIT 1";
$admin_stmt = $conn->prepare($admin_sql);
$admin_stmt->bind_param('s', $username);
$admin_stmt->execute();
$admin = $admin_stmt->get_result()->fetch_assoc();

if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['admin_id'] = $admin['admin_id'];
    $_SESSION['admin_username'] = $admin['username'];
    $response = ['success' => true, 'is_admin' => true];
    if (!empty($data['issue_token'])) {
        $token = generate_jwt(['user_id' => $admin['admin_id'], 'username' => $admin['username'], 'is_admin' => true]);
        if ($token) $response['token'] = $token;
    }
    send_json($response);
}

// Check regular user
$sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $response = ['success' => true, 'is_admin' => false];
    if (!empty($data['issue_token'])) {
        $token = generate_jwt(['user_id' => $user['user_id'], 'username' => $user['username'], 'is_admin' => false]);
        if ($token) $response['token'] = $token;
    }
    send_json($response);
}

send_json(['success' => false, 'message' => 'invalid_credentials'], 401);
