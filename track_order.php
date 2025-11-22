<?php
session_start();
require_once 'config/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách đơn hàng của user
$order_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$orders = $order_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theo dõi đơn hàng - TTHUONG Store</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/theodoidonhang.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="order-tracking">
        <h2>Theo dõi đơn hàng</h2>

        <?php if ($orders->num_rows > 0): ?>
            <?php while ($order = $orders->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <h3>Đơn hàng #<?php echo $order['order_id']; ?></h3>
                            <p>Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                        </div>
                        <span class="order-status status-<?php echo strtolower($order['order_status']); ?>">
                            <?php echo $order['order_status']; ?>
                        </span>
                    </div>

                    <div class="order-details">
                        <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                        <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                        
                        <?php if ($order['notes']): ?>
                            <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                        <?php endif; ?>

                        <div class="product-list">
                            <h4>Sản phẩm đã đặt:</h4>
                            <?php
                            $detail_sql = "SELECT od.*, p.product_name, p.image_url 
                                         FROM order_details od 
                                         JOIN products p ON od.product_id = p.product_id 
                                         WHERE od.order_id = ?";
                            $detail_stmt = $conn->prepare($detail_sql);
                            $detail_stmt->bind_param("i", $order['order_id']);
                            $detail_stmt->execute();
                            $details = $detail_stmt->get_result();
                            
                            while ($detail = $details->fetch_assoc()):
                            ?>
                                <div class="product-item">
                                    <img src="<?php echo htmlspecialchars($detail['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($detail['product_name']); ?>">
                                    <div>
                                        <h5><?php echo htmlspecialchars($detail['product_name']); ?></h5>
                                        <p>Số lượng: <?php echo $detail['quantity']; ?></p>
                                        <p>Giá: <?php echo number_format($detail['price'], 0, ',', '.'); ?> VNĐ</p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <div class="total-amount">
                            Tổng tiền: <?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ
                        </div>
                    </div>

                    <?php if ($order['order_status'] == 'Đã giao hàng'): ?>
                        <div class="review-section">
                            <h4>Đánh giá sản phẩm</h4>
                            <?php
                            $detail_sql = "SELECT od.*, p.product_name, p.image_url, r.rating, r.content as review_content
                                          FROM order_details od 
                                          JOIN products p ON od.product_id = p.product_id 
                                          LEFT JOIN reviews r ON r.product_id = p.product_id AND r.user_id = ?
                                          WHERE od.order_id = ?";
                            $detail_stmt = $conn->prepare($detail_sql);
                            $detail_stmt->bind_param("ii", $_SESSION['user_id'], $order['order_id']);
                            $detail_stmt->execute();
                            $details = $detail_stmt->get_result();
                            
                            while ($detail = $details->fetch_assoc()):
                            ?>
                                <div class="product-review">
                                    <img src="<?php echo htmlspecialchars($detail['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($detail['product_name']); ?>">
                                    <div class="review-form">
                                        <h5><?php echo htmlspecialchars($detail['product_name']); ?></h5>
                                        <?php if (!$detail['rating']): ?>
                                            <form method="POST" action="submit_review.php">
                                                <input type="hidden" name="product_id" value="<?php echo $detail['product_id']; ?>">
                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                <div class="rating">
                                                    <input type="radio" name="rating" value="5" id="5"><label for="5">☆</label>
                                                    <input type="radio" name="rating" value="4" id="4"><label for="4">☆</label>
                                                    <input type="radio" name="rating" value="3" id="3"><label for="3">☆</label>
                                                    <input type="radio" name="rating" value="2" id="2"><label for="2">☆</label>
                                                    <input type="radio" name="rating" value="1" id="1"><label for="1">☆</label>
                                                </div>
                                                <textarea name="review_content" placeholder="Nhập đánh giá của bạn"></textarea>
                                                <button type="submit" name="submit_review">Gửi đánh giá</button>
                                            </form>
                                        <?php else: ?>
                                            <div class="existing-review">
                                                <div class="stars">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                        <span class="<?php echo $i <= $detail['rating'] ? 'filled' : ''; ?>">★</span>
                                                    <?php endfor; ?>
                                                </div>
                                                <p><?php echo htmlspecialchars($detail['review_content']); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-orders">
                <i class="fas fa-box-open fa-3x"></i>
                <h3>Bạn chưa có đơn hàng nào</h3>
                <p>Hãy đặt hàng để xem thông tin đơn hàng tại đây</p>
                <a href="products.php" class="btn-order">Mua sắm ngay</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>