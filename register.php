<?php
require_once 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    
    // Kiểm tra username đã tồn tại chưa
    $check_stmt = $conn->prepare("SELECT admin_id FROM administrators WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error_message = "Tên đăng nhập đã tồn tại";
    } else {
        // Thêm admin mới
        $stmt = $conn->prepare("INSERT INTO administrators (username, password, email, full_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $password, $email, $full_name);
        
        if ($stmt->execute()) {
            $success_message = "Đăng ký thành công";
        } else {
            $error_message = "Lỗi: " . $conn->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}
?> 