<nav class="admin-sidebar">
    <div class="logo">
        <img src="../images/logoo.jpg" alt="TTHUONG Store" width="80">
        <h2>TTHUONG Admin</h2>
    </div>
    
    <ul class="menu">
        <li>
            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Tổng quan</span>
            </a>
        </li>
        <li>
            <a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                <span>Sản phẩm</span>
            </a>
        </li>
        <li>
            <a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i>
                <span>Danh mục</span>
            </a>
        </li>
        <li>
            <a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Đơn hàng</span>
            </a>
        </li>
        <li>
            <a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Người dùng</span>
            </a>
        </li>
        <li>
            <a href="../admin_reviews.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_reviews.php' ? 'active' : ''; ?>">
                <i class="fas fa-star"></i>
                <span>Đánh giá</span>
            </a>
        </li>
        <li>
            <a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Báo cáo</span>
            </a>
        </li>
        <li>
            <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Cài đặt</span>
            </a>
        </li>
        <li>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Đăng xuất</span>
            </a>
        </li>
    </ul>
</nav> 