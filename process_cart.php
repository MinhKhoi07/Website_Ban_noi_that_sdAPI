<?php
session_start();
require_once 'config/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
    exit();
}

switch ($data['action']) {
    case 'add':
        if (!isset($data['productId']) || !isset($data['quantity'])) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
            exit();
        }

        // Kiểm tra sản phẩm tồn tại
        $sql = "SELECT * FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $data['productId']);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
            exit();
        }

        // Thêm vào giỏ hàng
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] === $data['productId']) {
                $item['quantity'] += $data['quantity'];
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $data['productId'],
                'name' => $product['product_name'],
                'price' => $product['price'],
                'quantity' => $data['quantity']
            ];
        }

        echo json_encode(['success' => true]);
        break;

    case 'remove':
        if (!isset($data['productId'])) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin sản phẩm']);
            exit();
        }

        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $key => $item) {
                if ($item['product_id'] === $data['productId']) {
                    unset($_SESSION['cart'][$key]);
                    break;
                }
            }
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }

        echo json_encode(['success' => true]);
        break;

    case 'update':
        if (!isset($data['productId']) || !isset($data['quantity'])) {
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            exit();
        }

        if (isset($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['product_id'] === $data['productId']) {
                    $item['quantity'] = $data['quantity'];
                    break;
                }
            }
        }

        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
}
?> 