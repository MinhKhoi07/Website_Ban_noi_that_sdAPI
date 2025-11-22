<?php
session_start();
require_once('config/connect.php');

// Xử lý đăng nhập POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Kiểm tra trong bảng administrators
    $stmt = $conn->prepare("SELECT admin_id, username, password, full_name FROM administrators WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['username'];
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            header("Location: dashboard.php");
            exit();
        }
    }
    
    $error_message = "Tên đăng nhập hoặc mật khẩu không đúng!";
    $stmt->close();
}

// Xử lý đăng xuất - Sửa lại phần prepare statement
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    if (isset($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id'];
        $sql = "INSERT INTO hoat_dong_nguoi_dung (nguoi_dung_id, hanh_dong) VALUES (?, 'đã đăng xuất')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
    }
    
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

// Thông tin đăng nhập mặc định
$default_username = "Lâm Nhật Hào";
$default_password = "123456789";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy thông tin từ form và loại bỏ khoảng trắng thừa
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // So sánh chính xác với thông tin mặc định
    if (strcmp($username, $default_username) === 0 && strcmp($password, $default_password) === 0) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error_message = "Tên đăng nhập hoặc mật khẩu không đúng";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin - TTHUONG Store</title>
    <link rel="stylesheet" href="css/dangnhapAdmin.css">
</head>
        body {
            font-family: 'Roboto', sans-serif;
            background: #f5f6fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }


        .form-group {
            margin-bottom: 20px;
        }

        h2 {
            text-align: center;
            color: #1e3c72;
            margin-bottom: 30px;
            font-weight: bold;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box; /* Thêm dòng này */
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #71b7e6, #9b59b6);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
            box-sizing: border-box; /* Thêm dòng này */
        }

        button:hover {
            background: linear-gradient(135deg, #9b59b6, #71b7e6);
            transform: translateY(-2px);
        }

        .error-message {
            color: #e74c3c;
            text-align: center;
            margin: 0 0 15px 0;  /* Điều chỉnh margin */
            padding: 8px;  /* Thêm padding */
            background-color: rgba(231, 76, 60, 0.1);  /* Thêm màu nền */
            border: 1px solid #e74c3c;  /* Thêm viền */
            border-radius: 5px;  /* Bo góc */
            font-weight: 500;  /* Làm đậm chữ một chút */
            white-space: nowrap;  /* Giữ text trên cùng một dòng */
            overflow: hidden;  /* Ẩn phần text bị tràn */
            text-overflow: ellipsis;  /* Hiển thị dấu ... nếu text quá dài */
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Đăng nhập Quản trị viên</h2>
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>