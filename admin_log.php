<?php
session_start();
require_once 'config/connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Lấy lịch sử hoạt động từ database
$sql = "SELECT l.*, a.username, a.full_name 
        FROM activity_logs l
        LEFT JOIN administrators a ON l.admin_id = a.admin_id
        ORDER BY l.created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Sử Hoạt Động - TTHUONG Store</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_log.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>
    <div class="container">
        <h1>Lịch Sử Hoạt Động</h1>

        <table>
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Quản trị viên</th>
                    <th>Hành động</th>
                    <th>Mô tả</th>
                    <th>Địa chỉ IP</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        // Xác định class cho loại hành động
                        $action_class = '';
                        switch(strtolower($row['action'])) {
                            case 'login':
                                $action_class = 'login';
                                break;
                            case 'logout':
                                $action_class = 'logout';
                                break;
                            case 'update':
                                $action_class = 'update';
                                break;
                            case 'delete':
                                $action_class = 'delete';
                                break;
                            default:
                                $action_class = '';
                        }
                        ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i:s', strtotime($row['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']) . ' (' . htmlspecialchars($row['username']) . ')'; ?></td>
                            <td><span class="action-type <?php echo $action_class; ?>"><?php echo htmlspecialchars($row['action']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align: center;'>Không có lịch sử hoạt động nào.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    </main>

    <?php include 'admin_footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?> 