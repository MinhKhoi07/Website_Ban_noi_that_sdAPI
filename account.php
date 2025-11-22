<?php
session_start();
require_once('config/connect.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin người dùng
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Lấy lịch sử đơn hàng
$stmt = $conn->prepare("
    SELECT o.*, od.quantity, od.price 
    FROM orders o 
    LEFT JOIN order_details od ON o.order_id = od.order_id 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản - TTHUONG STORE</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="profile-section">
            <h2>Thông tin tài khoản</h2>
            <form method="POST" action="update_profile.php">
                <div class="form-group">
                    <label>Họ tên:</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                <div class="form-group">
                    <label>Số điện thoại:</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                <div class="form-group">
                    <label>Địa chỉ:</label>
                    <textarea name="address"><?php echo htmlspecialchars($user['address']); ?></textarea>
                </div>
                <button type="submit">Cập nhật thông tin</button>
            </form>
        </div>

        <div class="orders-section">
            <h2>Lịch sử đơn hàng</h2>
            <table>
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                        <td><?php echo number_format($order['total_amount']); ?>đ</td>
                        <td><?php echo $order['order_status']; ?></td>
                        <td><a href="order_detail.php?id=<?php echo $order['order_id']; ?>">Xem</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>