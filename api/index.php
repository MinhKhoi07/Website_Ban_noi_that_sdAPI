<?php
// Simple router that forwards to the specific endpoint file based on PATH_INFO or query
// Examples:
//  - /api/products.php (direct)
//  - /api/index.php/products  -> includes products.php

$path = $_SERVER['PATH_INFO'] ?? '';
$script = '';
if ($path) {
    $parts = explode('/', trim($path, '/'));
    $script = $parts[0] . '.php';
}

if (!$script && isset($_GET['r'])) {
    $script = basename($_GET['r']) . '.php';
}

$allowed = ['products.php','product.php','auth.php','cart.php','orders.php'];
if ($script && in_array($script, $allowed)) {
    include __DIR__ . '/' . $script;
    exit();
}

// Default: show simple index
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'API root',
    'endpoints' => ['/api/products.php','/api/product.php?id=...','/api/auth.php','/api/cart.php','/api/orders.php']
], JSON_UNESCAPED_UNICODE);
