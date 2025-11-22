<?php
session_start();
require_once 'config/connect.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Xử lý xóa sản phẩm
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];
    
    try {
        // Debug để kiểm tra ID sản phẩm
        error_log("Đang xóa sản phẩm ID: " . $product_id);
        
        // Bắt đầu transaction
        $conn->begin_transaction();
        
        // Sử dụng Prepared Statement để tránh SQL injection
        $delete_query = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $delete_query->bind_param("s", $product_id);
        
        if ($delete_query->execute()) {
            $conn->commit();
            echo "<script>
                alert('Xóa sản phẩm ID: " . $product_id . " thành công!');
                window.location.href = 'admin_products.php';
            </script>";
        } else {
            throw new Exception($conn->error);
        }
        
        $delete_query->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
            alert('Lỗi khi xóa sản phẩm: " . $e->getMessage() . "');
            window.location.href = 'admin_products.php';
        </script>";
    }
}

// Xử lý cập nhật sản phẩm
if (isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $product_name = trim($_POST['product_name']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $category_id = intval($_POST['category_id']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE products SET product_name = ?, price = ?, stock_quantity = ?, category_id = ?, description = ? WHERE product_id = ?");
    $stmt->bind_param("sdiisi", $product_name, $price, $stock_quantity, $category_id, $description, $product_id);

    if ($stmt->execute()) {
        echo "<script>alert('Cập nhật sản phẩm thành công!');</script>";
    } else {
        echo "<script>alert('Lỗi khi cập nhật sản phẩm: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Lấy danh sách sản phẩm
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        ORDER BY p.product_id DESC";
$result = $conn->query($sql);

// Lấy danh sách danh mục cho form cập nhật
$categories_sql = "SELECT * FROM categories";
$categories_result = $conn->query($categories_sql);
$categories = [];
while ($category = $categories_result->fetch_assoc()) {
    $categories[] = $category;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - TTHUONG Store</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/admin_products.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <main>
    <div class="container">
        <h1>Quản Lý Sản Phẩm</h1>
        
        <div class="button-container">
            <a href="add_product.php" class="add-product-btn">
                <i class="fas fa-plus"></i> Thêm Sản Phẩm Mới
            </a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Danh Mục</th>
                    <th>Giá</th>
                    <th>Số Lượng</th>
                    <th>Đã Bán</th>
                    <th>Hình Ảnh</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['product_id']; ?></td>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo $row['category_name']; ?></td>
                        <td><?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ</td>
                        <td><?php echo $row['stock_quantity']; ?></td>
                        <td><?php echo $row['sold_quantity']; ?></td>
                        <td><img src="<?php echo $row['image_url']; ?>" width="50"></td>
                        <td>
                            <button class="btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                Sửa
                            </button>
                            <form method="POST" style="display: inline;" onsubmit="return confirmDelete(<?php echo $row['product_id']; ?>)">
                                <input type="hidden" name="delete_product" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <button type="submit" class="btn-delete">Xóa</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Sửa Sản Phẩm -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Sửa Thông Tin Sản Phẩm</h2>
            <form id="editForm" method="POST">
                <input type="hidden" name="product_id" id="edit_product_id">
                
                <div class="form-group">
                    <label>Tên sản phẩm:</label>
                    <input type="text" name="product_name" id="edit_product_name" required>
                </div>
                
                <div class="form-group">
                    <label>Giá:</label>
                    <input type="number" name="price" id="edit_price" required>
                </div>
                
                <label>Số lượng:</label>
                <input type="number" name="stock_quantity" id="edit_stock_quantity" required>
                
                <div class="form-group">
                    <label>Danh mục:</label>
                    <select name="category_id" id="edit_category_id" required>
                        <?php foreach($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo $category['category_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Mô tả:</label>
                    <textarea name="description" id="edit_description"></textarea>
                </div>
                
                <button type="submit" name="update_product">Cập nhật</button>
            </form>
        </div>
    </div>

    <script>
        // Xử lý xóa sản phẩm
        function confirmDelete(productId) {
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm có ID: ' + productId + '?')) {
                return true;
            }
            return false;
        }

        // Xử lý modal sửa sản phẩm
        function openEditModal(product) {
            const modal = document.getElementById('editModal');
            modal.style.display = 'flex';
            
            // Điền thông tin vào form
            document.getElementById('edit_product_id').value = product.product_id;
            document.getElementById('edit_product_name').value = product.product_name;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_stock_quantity').value = product.stock_quantity;
            document.getElementById('edit_category_id').value = product.category_id;
            document.getElementById('edit_description').value = product.description;
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
    </main>

    <?php include 'admin_footer.php'; ?>
</body>
</html>

<?php
$conn->close();
?>
