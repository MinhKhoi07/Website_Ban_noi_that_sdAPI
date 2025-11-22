<?php
session_start();
require_once 'config/connect.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['submit_review'])) {
    header('Location: track_order.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$rating = $_POST['rating'];
$content = $_POST['review_content'];
$order_id = $_POST['order_id'];

$sql = "INSERT INTO reviews (user_id, product_id, rating, content, order_id) 
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisis", $user_id, $product_id, $rating, $content, $order_id);
$stmt->execute();

header('Location: track_order.php');
exit();
?>