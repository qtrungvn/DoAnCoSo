<?php
session_start();
require_once "../config.php";

// Kiểm tra nếu có ID đơn hàng được truyền vào
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Không tìm thấy đơn hàng.");
}

$order_id = intval($_GET['id']);

// Lấy thông tin đơn hàng
$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

// Kiểm tra nếu đơn hàng không tồn tại
if (!$order) {
    die("Đơn hàng không tồn tại.");
}

// Lấy danh sách sản phẩm trong đơn hàng
$sql_items = "SELECT p.name, p.price, od.quantity
              FROM order_details od
              JOIN products p ON od.product_id = p.id
              WHERE od.order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Đơn Hàng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        h2 {
            color: #343a40;
        }
        h4 {
            margin-top: 20px;
            color: #495057;
        }
        .table {
            background-color: #ffffff;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4 text-center">Chi tiết Đơn Hàng #<?php echo $order['id']; ?></h2>
    
    <a href="admin_orders.php" class="btn btn-secondary mb-3">← Quay về</a>

    <h4>Thông tin khách hàng</h4>
    <p><strong>Họ và tên:</strong> <?php echo htmlspecialchars($order['name']); ?></p>
    <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
    <p><strong>Tổng tiền:</strong> <span class="text-danger"><?php echo number_format($order['total_price'], 0, ',', '.'); ?> đ</span></p>
    <p><strong>Trạng thái:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($order['status']); ?></span></p>

    <h4 class="mt-4">Danh sách sản phẩm</h4>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Tổng</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items_result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo number_format($item['price'], 0, ',', '.'); ?> đ</td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><strong><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?> đ</strong></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
