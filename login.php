<?php
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Kiểm tra thông tin đăng nhập
    $stmt = $conn->prepare("SELECT * FROM administrators WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Đăng nhập thành công
            session_start();
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['username'] = $row['username'];
            
            // Cập nhật thời gian đăng nhập
            $update_stmt = $conn->prepare("UPDATE administrators SET last_login = CURRENT_TIMESTAMP WHERE admin_id = ?");
            $update_stmt->bind_param("i", $row['admin_id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            header("Location: admin_dashboard.php");
            exit();
        }
    }
    
    // Đăng nhập thất bại
    $error_message = "Tên đăng nhập hoặc mật khẩu không đúng";
    $stmt->close();
}
?>