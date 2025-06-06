<?php
session_start();
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

// Lấy số lượng sản phẩm
$queryProducts = "SELECT COUNT(*) AS total_products FROM products";
$resultProducts = $conn->query($queryProducts);
$totalProducts = $resultProducts->fetch_assoc()['total_products'];

// Lấy tổng số đơn hàng
$queryOrders = "SELECT COUNT(*) AS total_orders FROM orders";
$resultOrders = $conn->query($queryOrders);
$totalOrders = $resultOrders->fetch_assoc()['total_orders'];

// Lấy tổng doanh thu
$queryRevenue = "SELECT SUM(total_price) AS total_revenue FROM orders";
$resultRevenue = $conn->query($queryRevenue);
$totalRevenue = $resultRevenue->fetch_assoc()['total_revenue'] ?? 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .sidebar {
            height: 100vh;
            background: #343a40;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px;
            display: block;
        }
        .sidebar a:hover {
            background: #495057;
            border-radius: 5px;
        }
        .content {
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar">
            <h4 class="text-center">Admin Panel</h4>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="admin_products.php">📦 Quản lý sản phẩm</a></li>
                <li class="nav-item"><a href="admin_orders.php">📜 Quản lý đơn hàng</a></li>
                <li class="nav-item"><a href="admin_users.php">👤 Quản lý người dùng</a></li>
                <li class="nav-item"><a href="../index.php">🏠 Trang chủ</a></li>
                <li class="nav-item"><a href="../logout.php">🚪 Đăng xuất</a></li>
            </ul>
        </nav>

        <!-- Nội dung -->
        <main class="col-md-10 content">
            <h2 class="mt-3">👋 Chào mừng Admin!</h2>

            <!-- Thống kê -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-header">📦 Sản phẩm</div>
                        <div class="card-body">
                            <h4 class="card-title"><?php echo $totalProducts; ?></h4>
                            <p class="card-text">Tổng số sản phẩm trong cửa hàng.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-header">📜 Đơn hàng</div>
                        <div class="card-body">
                            <h4 class="card-title"><?php echo $totalOrders; ?></h4>
                            <p class="card-text">Số đơn hàng đã đặt.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-header">💰 Doanh thu</div>
                        <div class="card-body">
                            <h4 class="card-title"><?php echo number_format($totalRevenue, 0, ',', '.'); ?> VNĐ</h4>
                            <p class="card-text">Tổng doanh thu của cửa hàng.</p>
                        </div>
                    </div>
                </div>
            </div>

            <h3 class="mt-4">📢 Thông báo mới</h3>
            <p>Không có thông báo nào.</p>
        </main>
    </div>
</div>

</body>
</html>
