<?php
session_start();
require_once 'config/connect.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['password']) || !isset($data['email']) || !isset($data['phone'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đăng ký']);
    exit();
}

// Kiểm tra username đã tồn tại
$sql = "SELECT user_id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $data['username']);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Tên đăng nhập đã tồn tại']);
    exit();
}

// Mã hóa mật khẩu
$hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

// Thêm user mới
$sql = "INSERT INTO users (username, password, email, phone) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $data['username'], $hashed_password, $data['email'], $data['phone']);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Đăng ký thất bại']);
}
?> 