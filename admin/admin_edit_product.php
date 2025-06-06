<?php
require_once "../config.php";

// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user_id'])) {
    die("Bạn cần đăng nhập! <a href='../login.php'>Đăng nhập</a>");
}

// Lấy thông tin user
$user_id = $_SESSION['user_id'];
$sql = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Kiểm tra quyền admin
if ($user['role'] !== 'admin') {
    die("Bạn không có quyền truy cập! <a href='../index.php'>Quay lại</a>");
}

if (!isset($_GET['id'])) {
    die("Thiếu ID sản phẩm.");
}

$product_id = $_GET['id'];

// Lấy thông tin sản phẩm
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Sản phẩm không tồn tại.");
}

// Xử lý khi submit form cập nhật
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];

    // Xử lý hình ảnh nếu có upload mới
    if (!empty($_FILES["image"]["name"])) {
        $image = "uploads/" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
    } else {
        $image = $product['image'];
    }

    // Cập nhật dữ liệu vào database
    $sql = "UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, image = ?, stock = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdsii", $category_id, $name, $description, $price, $image, $stock, $product_id);

    if ($stmt->execute()) {
        echo "<script>alert('Cập nhật sản phẩm thành công!'); window.location.href='admin_products.php';</script>";
    } else {
        echo "Lỗi khi cập nhật sản phẩm.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <h2 class="text-center text-dark">Chỉnh Sửa Sản Phẩm</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Tên sản phẩm:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Mô tả:</label>
                <textarea id="description" name="description" class="form-control"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Giá (VNĐ):</label>
                <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="stock" class="form-label">Số lượng:</label>
                <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <p>Hình ảnh hiện tại:</p>
                <img src="<?php echo htmlspecialchars($product['image']); ?>" class="img-thumbnail" width="100" alt="Hình ảnh <?php echo htmlspecialchars($product['name']); ?>">
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Chọn hình ảnh mới:</label>
                <input type="file" id="image" name="image" class="form-control">
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Danh mục:</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="1" <?php if ($product['category_id'] == 1) { echo 'selected'; } ?>>Đồ ăn</option>
                    <option value="2" <?php if ($product['category_id'] == 2) { echo 'selected'; } ?>>Đồ uống</option>
                    <option value="3" <?php if ($product['category_id'] == 3) { echo 'selected'; } ?>>Combo</option>
                </select>
            </div>
            <button type="submit" class="btn btn-custom w-100">💾 Lưu thay đổi</button>
        </form>
        <br>
        <a href="admin_products.php" class="btn btn-secondary w-100">⬅️ Quay lại danh sách sản phẩm</a>
    </div>
</div>

</body>
</html>
