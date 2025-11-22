<?php
session_start();
require_once('config/connect.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validation
    if (empty($username) || empty($password) || empty($email) || empty($full_name)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } else {
        try {
            // Kiểm tra username đã tồn tại
            $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
            $check_stmt->bind_param("s", $username);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                $error = 'Tên đăng nhập đã tồn tại';
            } else {
                // Kiểm tra email đã tồn tại
                $check_email = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
                $check_email->bind_param("s", $email);
                $check_email->execute();
                
                if ($check_email->get_result()->num_rows > 0) {
                    $error = 'Email đã được sử dụng';
                } else {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert user
                    $insert_stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, phone, address, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                    $insert_stmt->bind_param("ssssss", $username, $hashed_password, $email, $full_name, $phone, $address);
                    
                    if ($insert_stmt->execute()) {
                        $success = 'Đăng ký thành công! Đang chuyển đến trang đăng nhập...';
                        header("refresh:2;url=login_page.php");
                    } else {
                        $error = 'Có lỗi xảy ra, vui lòng thử lại';
                    }
                    $insert_stmt->close();
                }
                $check_email->close();
            }
            $check_stmt->close();
        } catch (Exception $e) {
            $error = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - TTHUONG Store</title>
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="form-container">
        <h2><i class="fas fa-user-plus"></i> Đăng ký tài khoản</h2>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Tên đăng nhập *</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       pattern="[a-zA-Z0-9_]{3,20}" 
                       title="3-20 ký tự, chỉ chữ cái, số và dấu gạch dưới">
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mật khẩu *</label>
                <div class="input-icon">
                    <input type="password" id="password" name="password" required minlength="6">
                    <i class="fas fa-eye" onclick="togglePassword('password')"></i>
                </div>
                <div class="password-strength" id="strength">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> Xác nhận mật khẩu *</label>
                <div class="input-icon">
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    <i class="fas fa-eye" onclick="togglePassword('confirm_password')"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="full_name"><i class="fas fa-id-card"></i> Họ và tên *</label>
                <input type="text" id="full_name" name="full_name" required 
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> Số điện thoại</label>
                <input type="tel" id="phone" name="phone" pattern="[0-9]{10,11}" 
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                       title="10-11 chữ số">
            </div>

            <div class="form-group">
                <label for="address"><i class="fas fa-map-marker-alt"></i> Địa chỉ</label>
                <textarea id="address" name="address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>

            <button type="submit" id="submitBtn">
                <i class="fas fa-user-plus"></i> Đăng ký
            </button>

            <div class="form-footer">
                <p>Đã có tài khoản? <a href="login_page.php"><i class="fas fa-sign-in-alt"></i> Đăng nhập ngay</a></p>
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

        // Password strength meter
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            let strength = 0;
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z\d]/.test(password)) strength++;
            
            strengthBar.className = 'password-strength-bar';
            if (strength <= 2) {
                strengthBar.classList.add('password-strength-weak');
            } else if (strength <= 4) {
                strengthBar.classList.add('password-strength-medium');
            } else {
                strengthBar.classList.add('password-strength-strong');
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 6 ký tự!');
                return false;
            }
        });
    </script>
</body>
</html> 