<?php
require_once __DIR__ . '/../config/connect.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

$sql = "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_id DESC";
$result = $conn->query($sql);

if (!$result) {
    send_json(['success' => false, 'message' => 'DB error: ' . $conn->error], 500);
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

send_json(['success' => true, 'products' => $products]);
