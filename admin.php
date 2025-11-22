<?php
session_start();
require_once 'config/connect.php';

// Kiểm tra đăng nhập admin
if(!isset($_SESSION['admin_id'])) {
    header('Location: login_page.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Trị - TTHUONG Store</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'admin/includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="header">
                <h1>Trang Quản Trị - TTHUONG Store</h1>
            </div>
            
            <div class="dashboard-stats">
                <div class="stat-card">
                    <h3>Tổng Đơn Hàng</h3>
                    <?php
                    $orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc();
                    ?>
                    <p><?php echo $orders['count']; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Tổng Sản Phẩm</h3>
                    <?php
                    $products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc();
                    ?>
                    <p><?php echo $products['count']; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Tổng Người Dùng</h3>
                    <?php
                    $users = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0")->fetch_assoc();
                    ?>
                    <p><?php echo $users['count']; ?></p>
                </div>
            </div>

            <div class="quick-actions">
                <h2>Quản Lý Nhanh</h2>
                <div class="action-grid">
                    <a href="admin/products.php" class="action-card">
                        <i class="fas fa-box"></i>
                        <span>Quản Lý Sản Phẩm</span>
                    </a>
                    
                    <a href="admin/orders.php" class="action-card">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Quản Lý Đơn Hàng</span>
                    </a>
                    
                    <a href="admin/users.php" class="action-card">
                        <i class="fas fa-users"></i>
                        <span>Quản Lý Người Dùng</span>
                    </a>
                    
                    <a href="admin_reviews.php" class="action-card">
                        <i class="fas fa-star"></i>
                        <span>Quản Lý Đánh Giá</span>
                    </a>
                    
                    <a href="admin/categories.php" class="action-card">
                        <i class="fas fa-list"></i>
                        <span>Quản Lý Danh Mục</span>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script src="https://kit.fontawesome.com/your-code.js"></script>
</body>
</html>
