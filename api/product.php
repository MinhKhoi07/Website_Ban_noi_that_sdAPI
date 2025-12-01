<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$id = $_GET['id'] ?? $_GET['product_id'] ?? null;
if (!$id) {
    send_json(['success' => false, 'message' => 'Missing product id'], 400);
}

$stmt = $conn->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ? LIMIT 1");
$stmt->bind_param('s', $id);
$stmt->execute();
$res = $stmt->get_result();
$product = $res->fetch_assoc();

if (!$product) {
    send_json(['success' => false, 'message' => 'Product not found'], 404);
}

send_json(['success' => true, 'product' => $product]);
