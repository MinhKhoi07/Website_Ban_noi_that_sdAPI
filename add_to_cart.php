<?php
session_start();
require_once 'config/connect.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng']);
    exit();
}

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);

// Hỗ trợ cả 2 tên key: productId và product_id
$product_id = isset($data['productId']) ? $data['productId'] : (isset($data['product_id']) ? $data['product_id'] : null);
$quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
    exit();
}

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Số lượng không hợp lệ']);
    exit();
}

try {
    // Kiểm tra sản phẩm có tồn tại không
    $check_stmt = $conn->prepare("SELECT product_id, product_name, price, stock_quantity FROM products WHERE product_id = ?");
    $check_stmt->bind_param("s", $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
        exit();
    }

    // Kiểm tra tồn kho
    if ($product['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Sản phẩm không đủ số lượng trong kho']);
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
    $cart_stmt = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $cart_stmt->bind_param("is", $user_id, $product_id);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();

    if ($cart_result->num_rows > 0) {
        // Cập nhật số lượng nếu đã có
        $cart_item = $cart_result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;

        // Kiểm tra tồn kho cho số lượng mới
        if ($product['stock_quantity'] < $new_quantity) {
            echo json_encode(['success' => false, 'message' => 'Không đủ số lượng trong kho']);
            exit();
        }

        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $update_stmt->bind_param("ii", $new_quantity, $cart_item['cart_id']);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Thêm mới vào giỏ hàng
        $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
        $insert_stmt->bind_param("isi", $user_id, $product_id, $quantity);
        $insert_stmt->execute();
        $insert_stmt->close();
    }

    $cart_stmt->close();
    $check_stmt->close();

    // Đếm tổng số sản phẩm trong giỏ hàng
    $count_stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $count_stmt->bind_param("i", $user_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_data = $count_result->fetch_assoc();
    $cart_count = $count_data['total'] ?? 0;
    $count_stmt->close();

    echo json_encode([
        'success' => true, 
        'message' => 'Đã thêm sản phẩm vào giỏ hàng',
        'cart_count' => $cart_count
    ]);

} catch (Exception $e) {
    error_log("Add to cart error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại']);
}

$conn->close();
?>
