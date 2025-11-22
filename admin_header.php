<header>
    <div class="header-content">
        <div class="logo-section">
            <img src="./images/logoo.jpg" alt="Logo" class="admin-logo" style="width: 60px; height: auto;">
            <h1>TTHUONG STORE - Trang Quản Trị</h1>
        </div>
        <div class="logout-section">
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </div>
    <nav>
        <ul>
            <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="admin_products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_products.php' ? 'active' : ''; ?>"><i class="fas fa-box"></i> Sản phẩm</a></li>
            <li><a href="admin_orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_orders.php' ? 'active' : ''; ?>"><i class="fas fa-shopping-cart"></i> Đơn hàng</a></li>
            <li><a href="admin_users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Khách hàng</a></li>
            <li><a href="admin_reviews.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_reviews.php' ? 'active' : ''; ?>"><i class="fas fa-star"></i> Đánh giá</a></li>
            <li><a href="admin_revenue.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_revenue.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Doanh thu</a></li>
            <li><a href="admin_log.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_log.php' ? 'active' : ''; ?>"><i class="fas fa-list"></i> Lịch sử đăng nhập</a></li>
        </ul>
    </nav>
</header>
