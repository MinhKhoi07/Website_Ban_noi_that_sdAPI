<?php
session_start();
require_once('config/connect.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Thêm debug log
        error_log("Attempting login with username: " . $username);
        
        // Kiểm tra trong bảng administrators
        $admin_stmt = $conn->prepare("SELECT * FROM administrators WHERE username = ?");
        $admin_stmt->bind_param("s", $username);
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();
        $admin = $admin_result->fetch_assoc();

        if ($admin) {
            error_log("Admin found: " . print_r($admin, true));
            // Tạo password hash để kiểm tra
            $hash = password_hash('123456', PASSWORD_DEFAULT);
            error_log("Generated hash for 123456: " . $hash);
            error_log("Stored password hash: " . $admin['password']);
            error_log("Password verify result: " . (password_verify($password, $admin['password']) ? 'true' : 'false'));
        }

        // Kiểm tra trong bảng users
        $user_stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $user_stmt->bind_param("s", $username);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();

        if ($user) {
            error_log("User found: " . print_r($user, true));
            error_log("Password verify result: " . (password_verify($password, $user['password']) ? 'true' : 'false'));
        }

        // Kiểm tra admin với password_verify
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['full_name'] = $admin['full_name'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['phone'] = $admin['phone'];
            $_SESSION['role'] = 'admin';
            
            // Ghi log đăng nhập
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $action = "login";
            $description = "Đăng nhập thành công vào hệ thống quản trị";
            
            $log_stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
            $log_stmt->bind_param("isss", $admin['admin_id'], $action, $description, $ip_address);
            $log_stmt->execute();
            
            // Cập nhật last_login
            $update_stmt = $conn->prepare("UPDATE administrators SET last_login = CURRENT_TIMESTAMP WHERE admin_id = ?");
            $update_stmt->bind_param("i", $admin['admin_id']);
            $update_stmt->execute();

            header('Location: dashboard.php');
            exit();
        } elseif ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['address'] = $user['address'];
            $_SESSION['role'] = 'user';
            
            header('Location: home.php');
            exit();
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không chính xác';
        }
    } catch(Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $error = 'Có lỗi xảy ra: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - TTHUONG Store</title>
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="form-container">
        <h2><i class="fas fa-sign-in-alt"></i> Đăng nhập</h2>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Tên đăng nhập</label>
                <input type="text" id="username" name="username" required autofocus
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mật khẩu</label>
                <div class="input-icon">
                    <input type="password" id="password" name="password" required>
                    <i class="fas fa-eye" onclick="togglePassword('password')"></i>
                </div>
            </div>

            <div class="remember-forgot">
                <label>
                    <input type="checkbox" name="remember" id="remember">
                    Ghi nhớ đăng nhập
                </label>
                <!-- <a href="forgot_password.php">Quên mật khẩu?</a> -->
            </div>

            <button type="submit" id="submitBtn">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập
            </button>

            <div class="form-footer">
                <p>Chưa có tài khoản? <a href="register_page.php"><i class="fas fa-user-plus"></i> Đăng ký ngay</a></p>
                <p><a href="home.php"><i class="fas fa-home"></i> Quay về trang chủ</a></p>
            </div>
        </form>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.parentElement.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang đăng nhập...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>