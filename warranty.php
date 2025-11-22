<?php
session_start();
require_once 'config/connect.php'; // Thêm kết nối database nếu cần
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chính sách bảo hành - TTHUONG</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php require_once 'header.php'; ?>

    <main class="warranty-content">
        <h2>Chính sách bảo hành</h2>
        
        <section class="warranty-section">
            <h3>I. Điều kiện bảo hành</h3>
            <ol>
                <li>Sản phẩm còn trong thời hạn bảo hành (12 tháng kể từ ngày mua)</li>
                <li>Sản phẩm bị lỗi kỹ thuật do nhà sản xuất</li>
                <li>Tem bảo hành, mã sản phẩm còn nguyên vẹn</li>
                <li>Sản phẩm không bị biến dạng, cháy nổ, va đập do người sử dụng</li>
            </ol>
        </section>

        <section class="warranty-section">
            <h3>II. Quy trình bảo hành</h3>
            <ol>
                <li>Khách hàng mang sản phẩm đến cửa hàng hoặc gửi về địa chỉ bảo hành</li>
                <li>Nhân viên kiểm tra tình trạng sản phẩm</li>
                <li>Thời gian xử lý bảo hành: 3-7 ngày làm việc</li>
                <li>Thông báo cho khách hàng kết quả và hướng xử lý</li>
            </ol>
        </section>

        <section class="warranty-section">
            <h3>III. Trường hợp không được bảo hành</h3>
            <ol>
                <li>Sản phẩm hết hạn bảo hành</li>
                <li>Sản phẩm bị rách, cắt, đứt chỉ do sử dụng không đúng cách</li>
                <li>Sản phẩm bị biến dạng do va đập mạnh</li>
                <li>Sản phẩm bị hư hỏng do thiên tai, hỏa hoạn</li>
            </ol>
        </section>

        <section class="warranty-section">
            <h3>IV. Địa điểm bảo hành</h3>
            <p>Quý khách có thể mang sản phẩm đến bảo hành tại:</p>
            <address>
                <strong>TTHUONG Store</strong><br>
                Địa chỉ: Trường Thọ, Cầu Ngang, Trà Vinh<br>
                Điện thoại: (+84) 392-656-499<br>
                Email: support@tthuong.com
            </address>
        </section>
    </main>

    <div class="home-flag">
        <a href="home.php" class="back-home">Quay về Trang Chủ</a>
    </div>

    <div class="footer">
        <section id="contact">
            <h2>Liên hệ với chúng tôi</h2>
            <p>Nếu bạn có thắc mắc hoặc cần hỗ trợ, vui lòng liên hệ với chúng tôi:</p>
            <p>Email: support@tthuong.com</p>
            <p>Phone: (+84) 392-656-499</p>
            <p>Address: Trường Thọ, Cầu Ngang, Trà Vinh</p>
            <p class="copyright">&copy; 2024 TTHUONG Store. All rights reserved.</p>
        </section>
    </div>
      <?php include 'footer.php'; ?>
</body>
</html> 