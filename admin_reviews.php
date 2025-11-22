<?php
session_start();
require_once 'config/connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Xử lý xóa đánh giá
if (isset($_POST['delete_review'])) {
    $review_id = $_POST['review_id'];
    $delete_sql = "DELETE FROM reviews WHERE review_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $review_id);
    $delete_stmt->execute();
}

// Lấy danh sách đánh giá với thông tin đơn hàng
$sql = "SELECT r.*, u.username, u.full_name, p.product_name, p.image_url, r.order_id
        FROM reviews r 
        JOIN users u ON r.user_id = u.user_id 
        JOIN products p ON r.product_id = p.product_id 
        ORDER BY r.review_date DESC";
$result = $conn->query($sql);

// Thống kê
$stats_sql = "SELECT 
    COUNT(*) as total_reviews,
    AVG(rating) as avg_rating,
    COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
    COUNT(CASE WHEN rating >= 4 THEN 1 END) as four_plus_star
    FROM reviews";
$stats = $conn->query($stats_sql)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đánh Giá - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_reviews.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <div class="admin-reviews-container">
        <h2><i class="fas fa-star"></i> Quản Lý Đánh Giá</h2>
        
        <!-- Statistics -->
        <div class="review-stats">
            <div class="stat-card">
                <h3>Tổng đánh giá</h3>
                <div class="stat-number"><?php echo number_format($stats['total_reviews']); ?></div>
            </div>
            <div class="stat-card">
                <h3>Điểm trung bình</h3>
                <div class="stat-number"><?php echo number_format($stats['avg_rating'], 1); ?> <span style="font-size: 20px;">★</span></div>
            </div>
            <div class="stat-card">
                <h3>5 sao</h3>
                <div class="stat-number"><?php echo number_format($stats['five_star']); ?></div>
            </div>
            <div class="stat-card">
                <h3>4+ sao</h3>
                <div class="stat-number"><?php echo number_format($stats['four_plus_star']); ?></div>
            </div>
        </div>
        
        <!-- Reviews List -->
        <div class="reviews-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($review = $result->fetch_assoc()): ?>
                    <div class="review-card">
                        <div class="review-card-header">
                            <div class="review-user-info">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($review['full_name'] ?: $review['username'], 0, 1)); ?>
                                </div>
                                <div class="user-details">
                                    <h4><?php echo htmlspecialchars($review['full_name'] ?: $review['username']); ?></h4>
                                    <p><?php echo htmlspecialchars($review['username']); ?></p>
                                </div>
                            </div>
                            <div class="review-meta">
                                <div class="review-date">
                                    <i class="far fa-clock"></i> 
                                    <?php echo date('d/m/Y H:i', strtotime($review['review_date'])); ?>
                                </div>
                                <?php if($review['order_id']): ?>
                                    <span class="order-badge">
                                        <i class="fas fa-shopping-bag"></i> Đơn hàng #<?php echo $review['order_id']; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="review-card-body">
                            <div class="product-name">
                                <i class="fas fa-box"></i> 
                                <?php echo htmlspecialchars($review['product_name']); ?>
                            </div>
                            
                            <div class="rating-display">
                                <div class="stars">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <span class="<?php echo $i <= $review['rating'] ? 'filled' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-number"><?php echo $review['rating']; ?>/5</span>
                            </div>
                            
                            <div class="review-content">
                                <?php echo nl2br(htmlspecialchars($review['content'])); ?>
                            </div>
                            
                            <?php if (!empty($review['images'])): ?>
                                <?php 
                                $images = json_decode($review['images'], true);
                                if (is_array($images) && count($images) > 0):
                                ?>
                                    <div class="review-images">
                                        <?php foreach ($images as $image): ?>
                                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                                 alt="Review image" 
                                                 class="review-image"
                                                 onclick="window.open(this.src, '_blank')">
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="review-actions">
                            <?php if($review['order_id']): ?>
                                <a href="admin_orders.php?order_id=<?php echo $review['order_id']; ?>" 
                                   class="btn-view-order">
                                    <i class="fas fa-eye"></i> Xem đơn hàng
                                </a>
                            <?php endif; ?>
                            <form method="POST" 
                                  onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này?');"
                                  style="display: inline;">
                                <input type="hidden" name="review_id" value="<?php echo $review['review_id']; ?>">
                                <button type="submit" name="delete_review" class="btn-delete">
                                    <i class="fas fa-trash"></i> Xóa đánh giá
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-star"></i>
                    <p>Chưa có đánh giá nào</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'admin_footer.php'; ?>
</body>
</html>