<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $user_id = require_login();

    $sql = "SELECT c.cart_id, c.product_id, c.quantity, p.product_name, p.price, p.image_url FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $items = [];
    while ($row = $res->fetch_assoc()) { $items[] = $row; }
    send_json(['success' => true, 'cart' => $items]);
}

if ($method === 'POST') {
    $user_id = require_login();
    $data = get_json_input();
    $product_id = $data['product_id'] ?? $data['productId'] ?? null;
    $quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;

    if (!$product_id) send_json(['success' => false, 'message' => 'missing_product_id'], 400);
    if ($quantity <= 0) send_json(['success' => false, 'message' => 'invalid_quantity'], 400);

    // Check product exists
    $check_stmt = $conn->prepare("SELECT product_id, price, stock_quantity, product_name FROM products WHERE product_id = ?");
    $check_stmt->bind_param('s', $product_id);
    $check_stmt->execute();
    $prod = $check_stmt->get_result()->fetch_assoc();
    if (!$prod) send_json(['success' => false, 'message' => 'product_not_found'], 404);
    if ($prod['stock_quantity'] < $quantity) send_json(['success' => false, 'message' => 'not_enough_stock'], 400);

    // Update or insert
    $cart_stmt = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $cart_stmt->bind_param('is', $user_id, $product_id);
    $cart_stmt->execute();
    $cart_res = $cart_stmt->get_result();

    if ($cart_res->num_rows > 0) {
        $cart_item = $cart_res->fetch_assoc();
        $new_q = $cart_item['quantity'] + $quantity;
        if ($prod['stock_quantity'] < $new_q) send_json(['success' => false, 'message' => 'not_enough_stock'], 400);
        $upd = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $upd->bind_param('ii', $new_q, $cart_item['cart_id']);
        $upd->execute();
    } else {
        $ins = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, created_at) VALUES (?, ?, ?, NOW())");
        $ins->bind_param('isi', $user_id, $product_id, $quantity);
        $ins->execute();
    }

    // Return cart count
    $count_stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $count_stmt->bind_param('i', $user_id);
    $count_stmt->execute();
    $total = $count_stmt->get_result()->fetch_assoc()['total'] ?? 0;

    send_json(['success' => true, 'message' => 'added_to_cart', 'cart_count' => intval($total)]);
}

send_json(['success' => false, 'message' => 'Method not allowed'], 405);
