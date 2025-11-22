<?php
session_start();
header('Content-Type: application/json');

require_once 'db_connection.php'; // Kết nối đến cơ sở dữ liệu

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'];
$password = $data['password'];

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];

        echo json_encode([
            'success' => true,
            'is_admin' => $user['is_admin'],
            'redirect' => $user['is_admin'] ? 'admin/dashboard.php' : 'home.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sai tên đăng nhập hoặc mật khẩu']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi kết nối database']);
} 