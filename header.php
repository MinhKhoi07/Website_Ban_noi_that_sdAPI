<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <div class="logo">
        <img src="./images/logoo.jpg" width="120" height="124" align="left" alt="Logo">
    </div>
    <h1>TTHUONG Store</h1>

    <div class="user-section">
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="user-info">
                <i class="fas fa-user"></i>
                <span>Xin chào, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="logout_page.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
            </div>
        <?php else: ?>
            <div class="auth-buttons">
                <a href="login_page.php" class="login-btn"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                <a href="register_page.php" class="register-btn"><i class="fas fa-user-plus"></i> Đăng ký</a>
            </div>
        <?php endif; ?>
    </div>

    <nav>
        <ul>
            <li><a href="home.php">Trang chủ</a></li>
            <li><a href="products.php">Sản phẩm</a></li>
            <li><a href="cart.php">Đặt hàng</a></li>
            <li><a href="warranty.php">Chính sách bảo hành</a></li>
            <li><a href="track_order.php">Theo dõi đơn hàng</a></li>
            <li><a href="reviews.php">Đánh giá</a></li>
            <?php if(isset($_SESSION['admin_id'])): ?>
                <li><a href="admin_reviews.php" style="color: #ff6b6b; font-weight: bold;">
                    <i class="fas fa-star"></i> Quản lý đánh giá
                </a></li>
            <?php endif; ?>
            <li><a href="#contact">Thông tin liên hệ</a></li>
        </ul>
    </nav>
</header>
<link rel="stylesheet" href="css/header.css">

<!-- Thêm Font Awesome cho icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">