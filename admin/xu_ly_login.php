<?php
session_start();
require_once '../config/connect.php';

// Bật hiển thị lỗi để debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Phương thức không được phép');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    
    // Debug để xem dữ liệu nhận được
    error_log("Received data: " . print_r($data, true));
    
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        throw new Exception('Vui lòng nhập đầy đủ thông tin');
    }

    // Kiểm tra trong bảng administrators
    $sql = "SELECT admin_id, username, password FROM administrators WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Lỗi prepare statement: ' . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_username'] = $admin['username'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Tên đăng nhập hoặc mật khẩu không đúng'
        ]);
    }
    
    $stmt->close();

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
} 