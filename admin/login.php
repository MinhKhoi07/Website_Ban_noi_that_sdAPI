<?php
session_start();
require_once '../config/connect.php';

// Nếu đã đăng nhập admin thì chuyển về dashboard
if(isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập Admin - TTHUONG Store</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="../images/logoo.jpg" alt="TTHUONG Store" width="100">
                <h1>Đăng Nhập Admin</h1>
            </div>

            <div id="message" class="message" style="display: none;"></div>

            <form id="loginForm" onsubmit="return handleAdminLogin(event)">
                <div class="form-group">
                    <label for="username">Tên đăng nhập:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-login">Đăng Nhập</button>
                <a href="../home.php" class="btn-back">Quay về Trang Chủ</a>
            </form>
        </div>
    </div>

    <script>
        async function handleAdminLogin(event) {
            event.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('xu_ly_admin_login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();
                if (data.success) {
                    showMessage('Đăng nhập thành công!');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1500);
                } else {
                    showMessage(data.message || 'Đăng nhập thất bại!', false);
                }
            } catch (error) {
                showMessage('Có lỗi xảy ra!', false);
            }
        }

        function showMessage(message, isSuccess = true) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = message;
            messageDiv.className = `message ${isSuccess ? 'success' : 'error'}`;
            messageDiv.style.display = 'block';
            setTimeout(() => messageDiv.style.display = 'none', 3000);
        }
    </script>
</body>
</html> 