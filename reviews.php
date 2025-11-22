<?php
session_start();
require_once 'config/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách sản phẩm đã mua thành công
$sql = "SELECT DISTINCT p.*, od.order_id, o.order_date, o.order_status
        FROM products p
        JOIN order_details od ON p.product_id = od.product_id
        JOIN orders o ON od.order_id = o.order_id
        WHERE o.user_id = ? AND o.order_status IN ('Đã giao hàng', 'Hoàn thành')
        AND NOT EXISTS (
            SELECT 1 FROM reviews r 
            WHERE r.user_id = ? AND r.product_id = p.product_id
        )";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$products = $stmt->get_result();

// Lấy danh sách đánh giá đã gửi
$reviews_sql = "SELECT r.*, p.product_name, p.image_url, o.order_id
                FROM reviews r
                JOIN products p ON r.product_id = p.product_id
                LEFT JOIN orders o ON r.order_id = o.order_id
                WHERE r.user_id = ?
                ORDER BY r.review_date DESC";
$reviews_stmt = $conn->prepare($reviews_sql);
$reviews_stmt->bind_param("i", $user_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá sản phẩm - TTHUONG Store</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/reviews.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="reviews-container">
        <h2>Đánh giá sản phẩm</h2>

        <!-- Phần sản phẩm chưa đánh giá -->
        <div class="review-section">
            <h3>Sản phẩm chờ đánh giá</h3>
            <?php if ($products->num_rows > 0): ?>
                <?php while ($product = $products->fetch_assoc()): ?>
                    <div class="product-to-review">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <div class="product-info">
                            <h4><?php echo htmlspecialchars($product['product_name']); ?></h4>
                            <p>Đơn hàng #<?php echo $product['order_id']; ?></p>
                            <p>Ngày mua: <?php echo date('d/m/Y', strtotime($product['order_date'])); ?></p>
                            
                            <button type="button" class="btn-review" onclick="openReviewModal(<?php echo $product['product_id']; ?>, <?php echo $product['order_id']; ?>, '<?php echo htmlspecialchars($product['product_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($product['image_url'], ENT_QUOTES); ?>')">
                                <i class="fas fa-star"></i> Đánh giá ngay
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Không có sản phẩm nào chờ đánh giá.</p>
            <?php endif; ?>
        </div>

        <!-- Phần đánh giá đã gửi -->
        <div class="past-reviews">
            <h3>Đánh giá đã gửi</h3>
            <?php if ($reviews->num_rows > 0): ?>
                <?php while ($review = $reviews->fetch_assoc()): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <div>
                                <img src="<?php echo htmlspecialchars($review['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($review['product_name']); ?>"
                                     class="review-product-img">
                                <div>
                                    <h4><?php echo htmlspecialchars($review['product_name']); ?></h4>
                                    <p class="review-order-id">Đơn hàng #<?php echo $review['order_id']; ?></p>
                                </div>
                            </div>
                            <div class="review-date">
                                <?php echo date('d/m/Y H:i', strtotime($review['review_date'])); ?>
                            </div>
                        </div>
                        <div class="rating">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo $i <= $review['rating'] ? 'yellow-star' : ''; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <p><?php echo htmlspecialchars($review['content']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Bạn chưa có đánh giá nào.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal đánh giá -->
    <div id="reviewModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Đánh giá sản phẩm</h3>
                <span class="close" onclick="closeReviewModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="product-preview">
                    <img id="modalProductImage" src="" alt="" class="modal-product-img">
                    <div class="modal-product-info">
                        <h4 id="modalProductName"></h4>
                        <p id="modalOrderId"></p>
                    </div>
                </div>
                
                <form id="reviewForm" enctype="multipart/form-data" style="display: block !important; visibility: visible !important;">
                    <input type="hidden" id="reviewProductId" name="product_id">
                    <input type="hidden" id="reviewOrderId" name="order_id">
                    
                    <div class="form-group">
                        <label>Đánh giá của bạn: <span class="required">*</span></label>
                        <div class="rating-stars" id="modalRating">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>">
                                <label for="star<?php echo $i; ?>" title="<?php echo $i; ?> sao">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reviewContent">Nội dung đánh giá: <span class="required">*</span></label>
                        <textarea name="review_content" id="reviewContent" rows="4" placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="reviewImages">Thêm ảnh (tùy chọn):</label>
                        <input type="file" name="review_images[]" id="reviewImages" accept="image/*" multiple onchange="previewImages(this)" title="Chọn ảnh đánh giá" aria-label="Tải lên ảnh đánh giá sản phẩm">
                        <div id="imagePreview" class="image-preview"></div>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeReviewModal()">Hủy</button>
                <button type="submit" form="reviewForm" class="btn-submit">Gửi đánh giá</button>
            </div>
        </div>
    </div>

    <script>
    let currentProductId = null;
    let currentOrderId = null;

    function openReviewModal(productId, orderId, productName, productImage) {
        currentProductId = productId;
        currentOrderId = orderId;
        
        document.getElementById('modalProductImage').src = productImage;
        document.getElementById('modalProductName').textContent = productName;
        document.getElementById('modalOrderId').textContent = 'Đơn hàng #' + orderId;
        document.getElementById('reviewProductId').value = productId;
        document.getElementById('reviewOrderId').value = orderId;
        document.getElementById('reviewModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').style.display = 'none';
        document.getElementById('reviewForm').reset();
        document.getElementById('imagePreview').innerHTML = '';
        document.body.style.overflow = 'auto';
    }

    function previewImages(input) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        }
    }

    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('Form submitted');
        
        // Validate rating
        const ratingInputs = this.querySelectorAll('input[name="rating"]');
        const isRatingSelected = Array.from(ratingInputs).some(input => input.checked);
        
        console.log('Rating selected:', isRatingSelected);
        
        if (!isRatingSelected) {
            alert('Vui lòng chọn số sao đánh giá');
            return false;
        }
        
        // Validate content
        const content = this.querySelector('#reviewContent').value.trim();
        console.log('Content:', content);
        
        if (!content) {
            alert('Vui lòng nhập nội dung đánh giá');
            return false;
        }
        
        const formData = new FormData(this);
        console.log('FormData created');
        
        // Log form data
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        const submitBtn = document.querySelector('.modal-footer .btn-submit');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang gửi...';
        }
        
        console.log('Sending request to save_review.php');
        
        fetch('save_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            const submitBtn = document.querySelector('.modal-footer .btn-submit');
            if (data.success) {
                alert('Cảm ơn bạn đã đánh giá sản phẩm!');
                closeReviewModal();
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Gửi đánh giá';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi gửi đánh giá');
            const submitBtn = document.querySelector('.modal-footer .btn-submit');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Gửi đánh giá';
            }
        });
    });

    // Close modal when clicking outside
    document.getElementById('reviewModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeReviewModal();
        }
    });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>

<?php $conn->close(); ?>
