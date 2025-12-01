<?php
require_once 'config/connect.php';

// Lấy flash sale đang hoạt động
$now = date('Y-m-d H:i:s');
$flash_sale_query = "SELECT * FROM promotions 
                     WHERE promotion_type = 'flash_sale' 
                     AND status = 'active' 
                     AND '$now' BETWEEN start_date AND end_date 
                     ORDER BY discount_value DESC 
                     LIMIT 1";
$flash_sale_result = $conn->query($flash_sale_query);
$flash_sale = $flash_sale_result->num_rows > 0 ? $flash_sale_result->fetch_assoc() : null;

// Lấy các khuyến mãi nổi bật khác
$promotions_query = "SELECT * FROM promotions 
                     WHERE status = 'active' 
                     AND '$now' BETWEEN start_date AND end_date 
                     AND promotion_type IN ('coupon', 'minimum_order')
                     ORDER BY created_at DESC 
                     LIMIT 3";
$promotions_result = $conn->query($promotions_query);

// Lấy 4 sản phẩm mới nhất
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.product_id DESC 
        LIMIT 4";
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
    <title>TTHUONG Store</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/trangchu.css">
    <link rel="stylesheet" href="css/promotions.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once 'header.php'; ?>

    <!-- Flash Sale Banner -->
    <?php if ($flash_sale): ?>
    <section class="flash-sale-banner">
        <div class="flash-sale-content">
            <div class="flash-sale-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="flash-sale-info">
                <h2><i class="fas fa-fire"></i> FLASH SALE - <?php echo htmlspecialchars($flash_sale['promotion_name']); ?></h2>
                <p class="flash-sale-desc">
                    Giảm ngay 
                    <strong>
                        <?php echo $flash_sale['discount_type'] == 'percentage' 
                            ? $flash_sale['discount_value'] . '%' 
                            : number_format($flash_sale['discount_value']) . 'đ'; ?>
                    </strong>
                    cho toàn bộ đơn hàng!
                    <?php if ($flash_sale['min_order_amount'] > 0): ?>
                        <span class="min-order">Đơn tối thiểu: <?php echo number_format($flash_sale['min_order_amount']); ?>đ</span>
                    <?php endif; ?>
                </p>
                <p class="flash-sale-time">
                    <i class="fas fa-clock"></i> Kết thúc: 
                    <span class="countdown" data-end="<?php echo $flash_sale['end_date']; ?>"></span>
                </p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Promotions Section -->
    <?php if ($promotions_result->num_rows > 0): ?>
    <section class="promotions-section">
        <h2><i class="fas fa-tags"></i> Khuyến mãi hot</h2>
        <div class="promotions-grid">
            <?php while ($promo = $promotions_result->fetch_assoc()): ?>
            <div class="promo-card">
                <div class="promo-badge">
                    <?php 
                    $badge_icon = $promo['promotion_type'] == 'coupon' ? 'ticket-alt' : 'gift';
                    ?>
                    <i class="fas fa-<?php echo $badge_icon; ?>"></i>
                </div>
                <div class="promo-content">
                    <h3><?php echo htmlspecialchars($promo['promotion_name']); ?></h3>
                    <p class="promo-discount">
                        Giảm <strong>
                            <?php echo $promo['discount_type'] == 'percentage' 
                                ? $promo['discount_value'] . '%' 
                                : number_format($promo['discount_value']) . 'đ'; ?>
                        </strong>
                    </p>
                    <?php if ($promo['promotion_type'] == 'coupon'): ?>
                        <div class="promo-code">
                            Mã: <span class="code-text"><?php echo htmlspecialchars($promo['promotion_code']); ?></span>
                            <button class="copy-code" onclick="copyCode('<?php echo $promo['promotion_code']; ?>')">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                    <?php if ($promo['min_order_amount'] > 0): ?>
                        <p class="promo-condition">
                            <i class="fas fa-info-circle"></i> Đơn từ <?php echo number_format($promo['min_order_amount']); ?>đ
                        </p>
                    <?php endif; ?>
                    <p class="promo-expire">
                        HSD: <?php echo date('d/m/Y', strtotime($promo['end_date'])); ?>
                    </p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>
    <?php endif; ?>

    <section id="home">
        <div class="slideshow-container">
            <div class="mySlides fade">
                <img src="images/banner1.jpg" style="width:100%">
            </div>
            <div class="mySlides fade">
                <img src="images/banner2.jpg" style="width:100%">
            </div>
            <div class="mySlides fade">
                <img src="images/banner3.jpg" style="width:100%">
            </div>
        </div>
        <h2>Chào mừng đến với HKT Store</h2>
        <p>HKT - "Hãy biến không gian của bạn thành một tác phẩm nghệ thuật"</p>
        <p>HKT Store là điểm đến hoàn hảo cho những ai yêu thích sự sáng tạo và mong muốn mang vẻ đẹp nghệ thuật vào không gian sống. Với đa dạng sản phẩm decor, nội thất, và phụ kiện tinh tế, cửa hàng không chỉ cung cấp mà còn truyền cảm hứng để bạn tạo nên một không gian đậm chất riêng.
        <p>Từng món đồ tại HKT Store đều được chọn lọc kỹ lưỡng, kết hợp giữa phong cách hiện đại và nét tinh tế thủ công, giúp biến từng góc nhỏ trong nhà thành nơi chứa đựng cảm xúc và phong cách nghệ thuật độc đáo.</p>
        <p>Hãy để HKT Store đồng hành cùng bạn trong hành trình tô điểm tổ ấm và biến từng khoảnh khắc sống trở nên đáng nhớ.</p>
    </section>

    <section id="products">
        <h2>Sản phẩm nổi bật</h2>
        <div class="products">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    ?>
                    <div class="product" onclick="showProductDetails(
                        '<?php echo htmlspecialchars($row['product_name']); ?>',
                        '<?php echo htmlspecialchars($row['description']); ?>',
                        '<?php echo number_format($row['price'], 0, ',', '.'); ?>',
                        '<?php echo htmlspecialchars($row['image_url']); ?>',
                        '<?php echo htmlspecialchars($row['product_id']); ?>',
                        '<?php echo htmlspecialchars($row['category_name']); ?>',
                        <?php echo $row['stock_quantity']; ?>
                    )">
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                        <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                        <p>MSP: <?php echo htmlspecialchars($row['product_id']); ?></p>
                        <p><?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ</p>
                        <div class="button-group">
                            <button onclick="event.stopPropagation(); addToCart('<?php echo $row['product_id']; ?>', 
                                                     '<?php echo addslashes($row['product_name']); ?>', 
                                                     <?php echo $row['price']; ?>)" 
                                    class="add-to-cart">
                                Thêm vào giỏ hàng
                            </button>
                            <button onclick="event.stopPropagation(); window.location.href='order.php?id=<?php echo $row['product_id']; ?>'" 
                                    class="buy-now">
                                Mua ngay
                            </button>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p>Không có sản phẩm nào.</p>";
            }
            ?>
        </div>
    </section>

    <!-- Modal chi tiết sản phẩm -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="product-details">
                <div class="product-image">
                    <img id="modalImage" src="" alt="">
                </div>
                <div class="product-info">
                    <h2 id="modalTitle"></h2>
                    <p><strong>Mã sản phẩm:</strong> <span id="modalId"></span></p>
                    <p><strong>Danh mục:</strong> <span id="modalCategory"></span></p>
                    <p><strong>Giá:</strong> <span id="modalPrice"></span> VNĐ</p>
                    <p><strong>Số lượng còn:</strong> <span id="modalStock"></span></p>
                    <p><strong>Mô tả:</strong></p>
                    <p id="modalDescription"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let slideIndex = 0;
        showSlides();

        function showSlides() {
            let i;
            let slides = document.getElementsByClassName("mySlides");
            
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";  
            }
            
            slideIndex++;
            if (slideIndex > slides.length) {slideIndex = 1}
            
            slides[slideIndex-1].style.display = "block";
            setTimeout(showSlides, 4000);
        }

        function showProductDetails(name, description, price, image, id, category, stock) {
            document.getElementById('modalTitle').textContent = name;
            document.getElementById('modalDescription').textContent = description;
            document.getElementById('modalPrice').textContent = price;
            document.getElementById('modalImage').src = image;
            document.getElementById('modalId').textContent = id;
            document.getElementById('modalCategory').textContent = category;
            document.getElementById('modalStock').textContent = stock;
            document.getElementById('productModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('productModal').style.display = 'none';
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            if (event.target == document.getElementById('productModal')) {
                closeModal();
            }
        }
        function addToCart(productId, productName, price) {
    // Ngăn chặn sự kiện click lan ra ngoài
    event.stopPropagation();
    
    // Gửi request AJAX để thêm vào giỏ hàng
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đã thêm ' + productName + ' vào giỏ hàng!');
        } else if (data.message === 'not_logged_in') {
            if (confirm('Bạn cần đăng nhập để thêm vào giỏ hàng. Đến trang đăng nhập?')) {
                window.location.href = 'login_page.php';
            }
        } else {
            alert(data.message || 'Có lỗi xảy ra khi thêm vào giỏ hàng');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra: ' + error.message);
    });
}

// Countdown timer for flash sale
document.addEventListener('DOMContentLoaded', function() {
    const countdownEl = document.querySelector('.countdown');
    if (countdownEl) {
        const endDate = new Date(countdownEl.dataset.end).getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = endDate - now;
            
            if (distance < 0) {
                countdownEl.textContent = 'Đã kết thúc';
                clearInterval(countdownInterval);
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            let countdown = '';
            if (days > 0) countdown += days + ' ngày ';
            countdown += hours.toString().padStart(2, '0') + ':' + 
                        minutes.toString().padStart(2, '0') + ':' + 
                        seconds.toString().padStart(2, '0');
            
            countdownEl.textContent = countdown;
        }
        
        updateCountdown();
        const countdownInterval = setInterval(updateCountdown, 1000);
    }
});

// Copy coupon code
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        alert('Đã sao chép mã: ' + code);
    }).catch(err => {
        console.error('Lỗi sao chép:', err);
        // Fallback method
        const textArea = document.createElement('textarea');
        textArea.value = code;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Đã sao chép mã: ' + code);
    });
}
    </script>
      <?php include 'footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>
