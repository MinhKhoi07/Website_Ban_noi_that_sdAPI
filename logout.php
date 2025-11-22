<?php
session_start();
require_once('config/connect.php');

if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    
    // Ghi log đăng xuất
    $action = "logout";
    $description = "Đăng xuất khỏi hệ thống";
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $log_stmt->bind_param("isss", 
        $admin_id,
        $action,
        $description,
        $ip_address
    );
    $log_stmt->execute();
}

// Xóa session
session_destroy();

// Chuyển hướng về trang đăng nhập
header('Location: login_page.php');
exit();
?> 