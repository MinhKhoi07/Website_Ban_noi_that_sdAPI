<?php
session_start();
require_once 'config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login_page.php');
    exit();
}

// Lấy thông tin giỏ hàng
$user_id = $_SESSION['user_id'];
// Sửa lại câu truy vấn SQL để lấy thông tin đơn hàng
$sql = "SELECT c.cart_id, c.quantity, p.product_id, p.product_name, p.price, p.image_url 
        FROM cart c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();
$has_items = $cart_items->num_rows > 0;
$total = 0;

// Lấy thông tin người dùng
$user_sql = "SELECT * FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Hàng - TTHUONG Store</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dathang.css">
    <link rel="stylesheet" href="css/modal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php require_once 'header.php'; ?>

    <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn</h1>

    <?php if ($has_items): ?>
        <div class="cart-container">
            <div class="cart-items">
                <?php while ($item = $cart_items->fetch_assoc()): 
                    $subtotal = $item['quantity'] * $item['price'];
                    $total += $subtotal;
                ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                        
                        <div class="item-details">
                            <h3 class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></h3>
                            <p class="item-price">Giá: <?php echo number_format($item['price'], 0, ',', '.'); ?> VNĐ</p>
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, -1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="quantity-display"><?php echo $item['quantity']; ?></span>
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="item-actions">
                            <p class="item-total">Tổng: <?php echo number_format($subtotal, 0, ',', '.'); ?> VNĐ</p>
                            <button class="remove-btn" onclick="removeFromCart(<?php echo $item['cart_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="cart-summary">
                <div class="cart-total">
                    <h3>Tổng cộng:</h3>
                    <span class="total-amount"><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                </div>
                <button class="btn-order-main" onclick="openOrderModal()">
                    <i class="fas fa-shopping-bag"></i> Đặt hàng
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="cart-container">
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <p>Giỏ hàng của bạn đang trống</p>
                <p style="color: #666; margin: 10px 0;">Vui lòng thêm ít nhất 1 sản phẩm để đặt hàng</p>
                <a href="products.php" class="submit-btn">
                    <i class="fas fa-store"></i> Tiếp tục mua sắm
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal xác nhận thông tin đặt hàng -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-check"></i> Xác nhận thông tin đặt hàng</h2>
                <span class="close-modal" onclick="closeOrderModal()">&times;</span>
            </div>
            <form id="orderForm" onsubmit="return handleOrderSubmit(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Họ và tên:</label>
                        <input type="text" id="modal_full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email:</label>
                        <input type="email" id="modal_email" name="email" 
                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Số điện thoại:</label>
                        <input type="tel" id="modal_phone" name="phone" 
                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                               pattern="[0-9]{10,11}" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Địa chỉ giao hàng:</label>
                        <textarea id="modal_address" name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-sticky-note"></i> Ghi chú (không bắt buộc):</label>
                        <textarea id="modal_notes" name="notes" rows="2" placeholder="Ghi chú thêm về đơn hàng..."></textarea>
                    </div>

                    <div class="order-summary-modal">
                        <h3><i class="fas fa-receipt"></i> Tổng đơn hàng:</h3>
                        <span class="modal-total"><?php echo number_format($total, 0, ',', '.'); ?> VNĐ</span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeOrderModal()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn-confirm">
                        <i class="fas fa-check-circle"></i> Xác nhận đặt hàng
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Mở modal đặt hàng
        function openOrderModal() {
            const modal = document.getElementById('orderModal');
            const modalBody = modal.querySelector('.modal-body');
            const formGroups = modal.querySelectorAll('.form-group');
            
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
            document.body.style.overflow = 'hidden';
            
            // Force hiển thị modal-body và form-groups
            if (modalBody) {
                modalBody.style.display = 'block';
                modalBody.style.visibility = 'visible';
                modalBody.style.opacity = '1';
                modalBody.style.height = 'auto';
            }
            
            formGroups.forEach(group => {
                group.style.display = 'block';
                group.style.visibility = 'visible';
                group.style.opacity = '1';
            });
            
            console.log('Modal opened');
            console.log('Modal body:', modalBody);
            console.log('Form groups count:', formGroups.length);
        }

        // Đóng modal
        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target === modal) {
                closeOrderModal();
            }
        }

        // Cập nhật số lượng sản phẩm
        function updateQuantity(cartId, change) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_id: cartId,
                    change: change
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi cập nhật số lượng');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi cập nhật số lượng');
            });
        }

        // Xóa sản phẩm khỏi giỏ hàng
        function removeFromCart(cartId) {
            if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                fetch('delete_cart_item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cart_id: cartId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi xóa sản phẩm');
                });
            }
        }

        // Xử lý submit form đặt hàng
        function handleOrderSubmit(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const totalAmount = <?php echo $total; ?>;
            formData.append('total_amount', totalAmount);

            // Hiển thị loading
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

            fetch('process_order.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đặt hàng thành công! Mã đơn hàng: #' + data.order_id + '\nCảm ơn bạn đã mua hàng!');
                    window.location.href = 'home.php';
                } else {
                    alert('Lỗi: ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại!');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });

            return false;
        }
    </script>
      <?php include 'footer.php'; ?>
</body>
</html>

<?php
if (isset($conn)) {
    $conn->close();
}
?>