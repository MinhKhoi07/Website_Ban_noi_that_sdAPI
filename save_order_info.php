<?php
session_start();
require_once 'config/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

// Validate dữ liệu
if (empty($data['full_name']) || empty($data['email']) || empty($data['phone']) || empty($data['address'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin bắt buộc']);
    exit();
}

try {
    $conn->begin_transaction();

    // Cập nhật thông tin user
    $update_user_sql = "UPDATE users SET 
            full_name = ?, 
            email = ?, 
            phone = ?, 
            address = ? 
            WHERE user_id = ?";
            
    $stmt = $conn->prepare($update_user_sql);
    $stmt->bind_param("ssssi", 
        $data['full_name'],
        $data['email'],
        $data['phone'],
        $data['address'],
        $user_id
    );

    if (!$stmt->execute()) {
        throw new Exception("Lỗi khi cập nhật thông tin");
    }

    // Kiểm tra xem có phải đặt hàng trực tiếp không (không có sản phẩm trong giỏ)
    $is_direct_order = isset($data['direct_order']) && $data['direct_order'] === true;
    
    if ($is_direct_order) {
        // Đặt hàng trực tiếp - chỉ lưu thông tin yêu cầu
        $order_sql = "INSERT INTO orders (user_id, full_name, email, phone, address, total_amount, notes, order_status) 
                      VALUES (?, ?, ?, ?, ?, 0, ?, 'Chờ xác nhận')";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("isssss", 
            $user_id,
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['notes']
        );

        if (!$order_stmt->execute()) {
            throw new Exception("Lỗi khi tạo đơn hàng");
        }

        $order_id = $conn->insert_id;
        $order_stmt->close();

        $conn->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Đã gửi yêu cầu đặt hàng',
            'order_id' => $order_id
        ]);
        
    } else {
        // Đặt hàng từ giỏ hàng
        $cart_sql = "SELECT c.*, p.price 
                     FROM cart c 
                     JOIN products p ON c.product_id = p.product_id 
                     WHERE c.user_id = ?";
        $cart_stmt = $conn->prepare($cart_sql);
        $cart_stmt->bind_param("i", $user_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();

        if ($cart_result->num_rows == 0) {
            throw new Exception("Giỏ hàng trống");
        }

        // Lưu items vào mảng để dùng lại
        $cart_items = [];
        $total_amount = 0;
        while ($item = $cart_result->fetch_assoc()) {
            $cart_items[] = $item;
            $total_amount += $item['price'] * $item['quantity'];
        }

        // Tạo đơn hàng mới
        $order_sql = "INSERT INTO orders (user_id, full_name, email, phone, address, total_amount, notes) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("issssds", 
            $user_id,
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $total_amount,
            $data['notes']
        );

        if (!$order_stmt->execute()) {
            throw new Exception("Lỗi khi tạo đơn hàng");
        }

        $order_id = $conn->insert_id;

        // Thêm chi tiết đơn hàng từ mảng đã lưu
        $detail_sql = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $detail_stmt = $conn->prepare($detail_sql);

        foreach ($cart_items as $item) {
            $detail_stmt->bind_param("isid", 
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            );
            if (!$detail_stmt->execute()) {
                throw new Exception("Lỗi khi thêm chi tiết đơn hàng");
            }
        }
        
        $detail_stmt->close();

        // Xóa giỏ hàng sau khi đặt hàng thành công
        $clear_cart = "DELETE FROM cart WHERE user_id = ?";
        $clear_stmt = $conn->prepare($clear_cart);
        $clear_stmt->bind_param("i", $user_id);
        $clear_stmt->execute();

        $conn->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Đặt hàng thành công',
            'order_id' => $order_id
        ]);
    }

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();