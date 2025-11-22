<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

require_once('config/connect.php');

// Lấy tháng và năm hiện tại hoặc từ tham số
$current_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$current_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Tính tháng trước
$prev_month = $current_month - 1;
$prev_year = $current_year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

// Doanh thu theo tháng hiện tại
$revenue_query = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(order_id) as total_orders,
        SUM(total_amount) as total_revenue,
        AVG(total_amount) as avg_order_value
    FROM orders 
    WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?
    AND order_status != 'Đã hủy'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
";

$stmt = $conn->prepare($revenue_query);
$stmt->bind_param("ii", $current_year, $current_month);
$stmt->execute();
$current_revenue = $stmt->get_result()->fetch_assoc();

// Doanh thu tháng trước
$stmt = $conn->prepare($revenue_query);
$stmt->bind_param("ii", $prev_year, $prev_month);
$stmt->execute();
$prev_revenue = $stmt->get_result()->fetch_assoc();

// Doanh thu theo ngày trong tháng được chọn
$daily_query = "
    SELECT 
        DAY(created_at) as day,
        COUNT(order_id) as orders_count,
        SUM(total_amount) as daily_revenue
    FROM orders 
    WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?
    AND order_status != 'Đã hủy'
    GROUP BY DAY(created_at)
    ORDER BY DAY(created_at)
";

$stmt = $conn->prepare($daily_query);
$stmt->bind_param("ii", $current_year, $current_month);
$stmt->execute();
$daily_revenue = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Top sản phẩm bán chạy trong tháng
$top_products_query = "
    SELECT 
        p.product_id,
        p.product_name,
        p.image_url,
        SUM(od.quantity) as total_sold,
        SUM(od.quantity * od.price) as product_revenue
    FROM order_details od
    JOIN products p ON od.product_id = p.product_id
    JOIN orders o ON od.order_id = o.order_id
    WHERE YEAR(o.created_at) = ? AND MONTH(o.created_at) = ?
    AND o.order_status != 'Đã hủy'
    GROUP BY p.product_id
    ORDER BY total_sold DESC
    LIMIT 5
";

$stmt = $conn->prepare($top_products_query);
$stmt->bind_param("ii", $current_year, $current_month);
$stmt->execute();
$top_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Doanh thu theo trạng thái đơn hàng
$status_query = "
    SELECT 
        order_status,
        COUNT(*) as count,
        SUM(total_amount) as revenue
    FROM orders
    WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?
    GROUP BY order_status
";

$stmt = $conn->prepare($status_query);
$stmt->bind_param("ii", $current_year, $current_month);
$stmt->execute();
$status_stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Tính phần trăm tăng trưởng
$growth_rate = 0;
if (isset($prev_revenue['total_revenue']) && $prev_revenue['total_revenue'] > 0) {
    $growth_rate = (($current_revenue['total_revenue'] - $prev_revenue['total_revenue']) / $prev_revenue['total_revenue']) * 100;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo doanh thu - TTHUONG STORE</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_revenue.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>
        <div class="page-header">
            <h2><i class="fas fa-chart-bar"></i> Báo cáo doanh thu</h2>
            <div class="month-selector">
                <form method="GET" action="">
                    <select name="month" id="month">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo $m == $current_month ? 'selected' : ''; ?>>
                                Tháng <?php echo $m; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select name="year" id="year">
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $y == $current_year ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Lọc</button>
                </form>
            </div>
        </div>

        <!-- Thống kê tổng quan -->
        <div class="revenue-stats">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>Doanh thu tháng <?php echo $current_month; ?></h3>
                    <p class="stat-value"><?php echo number_format($current_revenue['total_revenue'] ?? 0, 0, ',', '.'); ?>đ</p>
                    <span class="stat-change <?php echo $growth_rate >= 0 ? 'positive' : 'negative'; ?>">
                        <i class="fas fa-arrow-<?php echo $growth_rate >= 0 ? 'up' : 'down'; ?>"></i>
                        <?php echo number_format(abs($growth_rate), 1); ?>% so với tháng trước
                    </span>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3>Tổng đơn hàng</h3>
                    <p class="stat-value"><?php echo number_format($current_revenue['total_orders'] ?? 0); ?></p>
                    <span class="stat-subtitle">Đơn hàng trong tháng</span>
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-info">
                    <h3>Giá trị TB/Đơn</h3>
                    <p class="stat-value"><?php echo number_format($current_revenue['avg_order_value'] ?? 0, 0, ',', '.'); ?>đ</p>
                    <span class="stat-subtitle">Trung bình mỗi đơn</span>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>Tháng trước</h3>
                    <p class="stat-value"><?php echo number_format($prev_revenue['total_revenue'] ?? 0, 0, ',', '.'); ?>đ</p>
                    <span class="stat-subtitle">Tháng <?php echo $prev_month; ?>/<?php echo $prev_year; ?></span>
                </div>
            </div>
        </div>

        <!-- Biểu đồ doanh thu theo ngày -->
        <div class="chart-section">
            <h3><i class="fas fa-chart-line"></i> Doanh thu theo ngày - Tháng <?php echo $current_month; ?>/<?php echo $current_year; ?></h3>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="two-columns">
            <!-- Top sản phẩm bán chạy -->
            <div class="section-box">
                <h3><i class="fas fa-fire"></i> Top 5 sản phẩm bán chạy</h3>
                <div class="top-products">
                    <?php foreach ($top_products as $index => $product): ?>
                    <div class="product-item">
                        <div class="product-rank">#<?php echo $index + 1; ?></div>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <div class="product-details">
                            <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                            <p class="product-sold">Đã bán: <strong><?php echo number_format($product['total_sold']); ?></strong> sản phẩm</p>
                            <p class="product-revenue">Doanh thu: <strong><?php echo number_format($product['product_revenue'], 0, ',', '.'); ?>đ</strong></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($top_products)): ?>
                        <p class="no-data">Chưa có dữ liệu sản phẩm</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Thống kê theo trạng thái -->
            <div class="section-box">
                <h3><i class="fas fa-tasks"></i> Thống kê theo trạng thái</h3>
                <div class="status-stats">
                    <?php foreach ($status_stats as $stat): ?>
                    <div class="status-item">
                        <div class="status-info">
                            <span class="status-label status-<?php echo strtolower(str_replace(' ', '-', $stat['order_status'])); ?>">
                                <?php echo htmlspecialchars($stat['order_status']); ?>
                            </span>
                            <span class="status-count"><?php echo number_format($stat['count']); ?> đơn</span>
                        </div>
                        <div class="status-revenue">
                            <?php echo number_format($stat['revenue'], 0, ',', '.'); ?>đ
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($status_stats)): ?>
                        <p class="no-data">Chưa có dữ liệu</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'admin_footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Dữ liệu cho biểu đồ
        const dailyData = <?php echo json_encode($daily_revenue); ?>;
        
        // Tính số ngày trong tháng
        const year = <?php echo $current_year; ?>;
        const month = <?php echo $current_month; ?>;
        const daysInMonth = new Date(year, month, 0).getDate();
        
        // Tạo mảng labels và revenues cho tất cả các ngày trong tháng
        const labels = [];
        const revenues = [];
        
        for (let day = 1; day <= daysInMonth; day++) {
            labels.push(`Ngày ${day}`);
            
            // Tìm doanh thu cho ngày này
            const dayData = dailyData.find(item => parseInt(item.day) === day);
            revenues.push(dayData ? parseFloat(dayData.daily_revenue) : 0);
        }

        // Vẽ biểu đồ
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: revenues,
                    backgroundColor: 'rgba(51, 51, 51, 0.7)',
                    borderColor: '#333',
                    borderWidth: 2,
                    borderRadius: 6,
                    hoverBackgroundColor: 'rgba(51, 51, 51, 0.9)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + context.parsed.y.toLocaleString('vi-VN') + 'đ';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + 'đ';
                            }
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
