<?php
session_start();
require_once 'config/connect.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Xử lý cập nhật trạng thái đơn hàng
if(isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = trim($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if($stmt->execute()) {
        echo "<script>alert('Cập nhật trạng thái thành công!'); window.location.href='admin_orders.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi cập nhật: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Lấy danh sách đơn hàng
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đơn Hàng - Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_orders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>       
    <div class="admin-orders">
        <h1>Quản Lý Đơn Hàng</h1>

        <table class="order-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Ngày đặt</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                        <td><?php echo number_format($order['total_amount'], 0, ',', '.'); ?> VNĐ</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <form method="POST" action="" style="display: flex; align-items: center; gap: 5px;">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                <select name="status" class="status-select">
                                    <option value="Chờ xác nhận" <?php if($order['order_status'] == 'Chờ xác nhận') echo 'selected'; ?>>Chờ xác nhận</option>
                                    <option value="Đã xác nhận" <?php if($order['order_status'] == 'Đã xác nhận') echo 'selected'; ?>>Đã xác nhận</option>
                                    <option value="Đang giao" <?php if($order['order_status'] == 'Đang giao') echo 'selected'; ?>>Đang giao</option>
                                    <option value="Hoàn thành" <?php if($order['order_status'] == 'Hoàn thành') echo 'selected'; ?>>Hoàn thành</option>
                                    <option value="Đã hủy" <?php if($order['order_status'] == 'Đã hủy') echo 'selected'; ?>>Đã hủy</option>
                                </select>
                                <button type="submit" class="btn-update-status">
                                    <i class="fas fa-save"></i> Lưu
                                </button>
                            </form>
                        </td>
                        <td>
                            <span class="view-details" onclick="toggleDetails(<?php echo $order['order_id']; ?>)">
                                <i class="fas fa-eye"></i> Chi tiết
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6">
                            <div id="details-<?php echo $order['order_id']; ?>" class="order-details">
                                <?php
                                $detail_sql = "SELECT od.*, p.product_name 
                                             FROM order_details od 
                                             JOIN products p ON od.product_id = p.product_id 
                                             WHERE od.order_id = ?";
                                $detail_stmt = $conn->prepare($detail_sql);
                                $detail_stmt->bind_param("i", $order['order_id']);
                                $detail_stmt->execute();
                                $details = $detail_stmt->get_result();
                                ?>
                                <h4>Chi tiết đơn hàng:</h4>
                                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                                <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                                <?php if ($order['notes']): ?>
                                    <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                                <?php endif; ?>
                                <table style="width: 100%; margin-top: 10px;">
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th>Số lượng</th>
                                        <th>Giá</th>
                                    </tr>
                                    <?php while ($detail = $details->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($detail['product_name']); ?></td>
                                            <td><?php echo $detail['quantity']; ?></td>
                                            <td><?php echo number_format($detail['price'], 0, ',', '.'); ?> VNĐ</td>
                                        </tr>
                                    <?php endwhile; ?>
                                </table>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
    function toggleDetails(orderId) {
        const detailsDiv = document.getElementById(`details-${orderId}`);
        if (detailsDiv.style.display === 'none' || detailsDiv.style.display === '') {
            detailsDiv.style.display = 'block';
        } else {
            detailsDiv.style.display = 'none';
        }
    }

    document.querySelectorAll('select[name="new_status"]').forEach(select => {
        select.addEventListener('change', function() {
            if(confirm('Bạn có chắc muốn cập nhật trạng thái?')) {
                this.closest('form').submit();
            }
        });
    });
    </script>
    </main>

    <?php include 'admin_footer.php'; ?>
</body>
</html>

<?php $conn->close(); ?>
