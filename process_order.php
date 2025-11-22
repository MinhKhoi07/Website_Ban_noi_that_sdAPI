<?php
session_start();
require_once 'config/connect.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đặt hàng']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được phép']);
    exit();
}

// Lấy dữ liệu từ form
$user_id = $_SESSION['user_id'];
$full_name = $_POST['full_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$notes = $_POST['notes'] ?? '';
$total_amount = $_POST['total_amount'] ?? 0;

// Validate dữ liệu
if (empty($full_name) || empty($email) || empty($phone) || empty($address)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
    exit();
}

try {
    // Bắt đầu transaction
    $conn->begin_transaction();

    // Kiểm tra giỏ hàng có sản phẩm không
    $check_cart_sql = "SELECT COUNT(*) as cart_count FROM cart WHERE user_id = ?";
    $check_stmt = $conn->prepare($check_cart_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $cart_count = $check_stmt->get_result()->fetch_assoc()['cart_count'];
    
    if ($cart_count == 0) {
        throw new Exception("Giỏ hàng trống. Vui lòng thêm sản phẩm trước khi đặt hàng.");
    }

    // Lấy sản phẩm từ giỏ hàng
    $cart_sql = "SELECT c.*, p.price, p.product_name FROM cart c 
                 JOIN products p ON c.product_id = p.product_id 
                 WHERE c.user_id = ?";
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();
    $cart_items = $cart_stmt->get_result();

    // Tính tổng tiền và lưu items
    $items = [];
    $calculated_total = 0;
    while ($item = $cart_items->fetch_assoc()) {
        $items[] = $item;
        $calculated_total += $item['price'] * $item['quantity'];
    }

    // Tạo đơn hàng mới
    $order_sql = "INSERT INTO orders (user_id, full_name, email, phone, address, notes, total_amount, order_status, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'Chờ xác nhận', NOW())";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("isssssd", $user_id, $full_name, $email, $phone, $address, $notes, $calculated_total);
    $order_stmt->execute();
    $order_id = $conn->insert_id;

    // Thêm chi tiết đơn hàng
    $detail_sql = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $detail_stmt = $conn->prepare($detail_sql);

    foreach ($items as $item) {
        $detail_stmt->bind_param("isid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $detail_stmt->execute();
    }

    // Xóa giỏ hàng
    $clear_cart_sql = "DELETE FROM cart WHERE user_id = ?";
    $clear_cart_stmt = $conn->prepare($clear_cart_sql);
    $clear_cart_stmt->bind_param("i", $user_id);
    $clear_cart_stmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Đặt hàng thành công',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    // Rollback nếu có lỗi
    $conn->rollback();
    echo json_encode([
        'success' => false, 
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
    ]);
}

// Đóng kết nối
$conn->close();