<?php
session_start();
require_once 'config/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

try {
    // Lấy dữ liệu từ request
    $data = json_decode(file_get_contents('php://input'), true);
    $cart_id = $data['cart_id'];
    $user_id = $_SESSION['user_id'];

    // Kiểm tra xem sản phẩm có thuộc về user không
    $check_sql = "SELECT * FROM cart WHERE cart_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $cart_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Không tìm thấy sản phẩm trong giỏ hàng');
    }

    // Xóa sản phẩm khỏi giỏ hàng
    $delete_sql = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $cart_id, $user_id);
    
    if ($delete_stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'
        ]);
    } else {
        throw new Exception('Không thể xóa sản phẩm');
    }

} catch (Exception $e) {
    error_log("Lỗi xóa sản phẩm: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Có lỗi xảy ra khi xóa sản phẩm: ' . $e->getMessage()
    ]);
}

$conn->close();
?> 