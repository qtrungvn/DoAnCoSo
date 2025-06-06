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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
}
$result = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
        }
        .table img {
            border-radius: 10px;
        }
        .btn-custom {
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            padding: 8px 15px;
            text-decoration: none;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center my-4">Danh Sách Sản Phẩm</h2>

    <div class="d-flex justify-content-between mb-3">
        <a href="admin_add_product.php" class="btn btn-success">➕ Thêm sản phẩm</a>
        <a href="admin_dashboard.php" class="btn btn-secondary">🏠 Quay về trang quản trị</a>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Giá</th>
                <th>Hình ảnh</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo number_format($row['price'], 0, ',', '.'); ?> VNĐ</td>
                    <td>
                        <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Ảnh sản phẩm" width="80">
                    </td>
                    <td>
                        <a href="admin_edit_product.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">✏️ Sửa</a>
                        <a href="admin_delete_product.php?id=<?php echo $row['id']; ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Bạn có chắc muốn xóa?');">🗑️ Xóa</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
