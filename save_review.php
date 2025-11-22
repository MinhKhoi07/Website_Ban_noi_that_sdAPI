<?php
session_start();
require_once 'config/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

// Xử lý dữ liệu từ FormData hoặc JSON
$user_id = $_SESSION['user_id'];

if ($_SERVER['CONTENT_TYPE'] && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    // JSON data (old method)
    $data = json_decode(file_get_contents('php://input'), true);
} else {
    // FormData (new method with images)
    $data = $_POST;
}

// Validate dữ liệu
if (empty($data['product_id']) || empty($data['rating']) || empty($data['review_content'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đánh giá']);
    exit();
}

// product_id là VARCHAR, giữ nguyên string và pad với 0
$product_id = str_pad($data['product_id'], 3, '0', STR_PAD_LEFT);
$rating = intval($data['rating']);
$order_id = isset($data['order_id']) ? intval($data['order_id']) : null;

// Debug: log received data
error_log("Received product_id: " . $product_id);
error_log("Received rating: " . $rating);
error_log("Received order_id: " . ($order_id ?? 'NULL'));

// Kiểm tra product_id có tồn tại không
$check_product = "SELECT product_id FROM products WHERE product_id = ?";
$check_product_stmt = $conn->prepare($check_product);
$check_product_stmt->bind_param("s", $product_id);
$check_product_stmt->execute();
$product_result = $check_product_stmt->get_result();

if ($product_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại (ID: ' . $product_id . ')']);
    exit();
}

try {
    // Kiểm tra xem đã đánh giá chưa
    $check_sql = "SELECT * FROM reviews WHERE user_id = ? AND product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $user_id, $product_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        throw new Exception("Bạn đã đánh giá sản phẩm này rồi");
    }

    // Xử lý upload ảnh
    $imagePaths = [];
    if (isset($_FILES['review_images'])) {
        $uploadDir = 'uploads/reviews/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileCount = count($_FILES['review_images']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['review_images']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['review_images']['tmp_name'][$i];
                $fileName = uniqid() . '_' . basename($_FILES['review_images']['name'][$i]);
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmpName, $filePath)) {
                    $imagePaths[] = $filePath;
                }
            }
        }
    }
    
    $imagesJson = !empty($imagePaths) ? json_encode($imagePaths) : null;

    // Thêm đánh giá mới
    $sql = "INSERT INTO reviews (user_id, product_id, rating, content, review_date, order_id, images) 
            VALUES (?, ?, ?, ?, NOW(), ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isisis", 
        $user_id,
        $product_id,
        $rating,
        $data['review_content'],
        $order_id,
        $imagesJson
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Đánh giá đã được gửi']);
    } else {
        throw new Exception("Lỗi khi lưu đánh giá");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>