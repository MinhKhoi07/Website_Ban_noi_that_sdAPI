<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="user-header">
    <div class="header-top">
        <div class="logo-section">
            <img src="./images/logo.png" alt="Logo" class="site-logo">
            <h1>HKT Store</h1>
        </div>
        
        <div class="header-actions">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="chat.php" class="chat-icon-btn" title="Chat với admin">
                    <i class="fas fa-comments" style="font-size: 1.3rem;"></i>
                    <?php
                    // Đếm tin nhắn chưa đọc (kiểm tra bảng tồn tại)
                    require_once 'config/connect.php';
                    $user_id = $_SESSION['user_id'];
                    $table_check = $conn->query("SHOW TABLES LIKE 'messages'");
                    if ($table_check && $table_check->num_rows > 0) {
                        $unread_query = "SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND sender_type = 'admin' AND is_read = 0";
                        $unread_stmt = $conn->prepare($unread_query);
                        $unread_stmt->bind_param("i", $user_id);
                        $unread_stmt->execute();
                        $unread_result = $unread_stmt->get_result();
                        if ($unread_result) {
                            $unread = $unread_result->fetch_assoc()['count'];
                            if ($unread > 0): ?>
                                <span class="chat-badge"><?php echo $unread; ?></span>
                            <?php endif;
                        }
                    }
                    ?>
                </a>
                
                <a href="profile.php" class="profile-icon-btn" title="Trang cá nhân">
                    <i class="fas fa-user-circle" style="font-size: 1.3rem;"></i>
                </a>
                
                <div class="user-welcome">
                    <span>Xin chào, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                </div>
                
                <a href="logout_page.php" class="btn-logout-user">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="login_page.php" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập
                    </a>
                    <a href="register_page.php" class="btn-register">
                        <i class="fas fa-user-plus"></i> Đăng ký
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <nav class="main-nav">
        <ul>
            <li><a href="home.php"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li><a href="products.php"><i class="fas fa-box"></i> Sản phẩm</a></li>
            <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Đặt hàng</a></li>
            <li><a href="warranty.php"><i class="fas fa-shield-alt"></i> Chính sách bảo hành</a></li>
            <li><a href="track_order.php"><i class="fas fa-truck"></i> Theo dõi đơn hàng</a></li>
            <li><a href="reviews.php"><i class="fas fa-star"></i> Đánh giá</a></li>
            <li><a href="#contact"><i class="fas fa-envelope"></i> Thông tin liên hệ</a></li>
        </ul>
    </nav>
</header>
<link rel="stylesheet" href="css/header.css">

<!-- Thêm Font Awesome cho icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">