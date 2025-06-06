<?php
session_start();
require_once "../config.php";

// Lấy danh sách đơn hàng
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng</title>
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
            text-align: center;
            color: #343a40;
        }
        .table {
            background-color: #ffffff;
        }
        .btn-back {
            display: block;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Quản lý Đơn Hàng</h2>

    <!-- Nút quay về trang quản trị -->
    <a href="admin_dashboard.php" class="btn btn-secondary btn-back">← Quay về trang quản trị</a>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Khách hàng</th>
                <th>SĐT</th>
                <th>Địa chỉ</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày đặt</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($order = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td><?php echo htmlspecialchars($order['name']); ?></td>
                    <td><?php echo htmlspecialchars($order['phone']); ?></td>
                    <td><?php echo htmlspecialchars($order['address']); ?></td>
                    <td><?php echo number_format($order['total_price'], 0, ',', '.'); ?> đ</td>
                    <td>
                        <select class="form-select status" data-id="<?php echo $order['id']; ?>">
                            <option value="pending" <?php echo ($order['status'] == 'pending') ? 'selected' : ''; ?>>Đang chờ xử lý</option>
                            <option value="processing" <?php echo ($order['status'] == 'processing') ? 'selected' : ''; ?>>Đang giao</option>
                            <option value="completed" <?php echo ($order['status'] == 'completed') ? 'selected' : ''; ?>>Hoàn thành</option>
                            <option value="cancelled" <?php echo ($order['status'] == 'cancelled') ? 'selected' : ''; ?>>Đã hủy</option>
                        </select>
                    </td>
                    <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                    <td>
                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">Chi tiết</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('.status').change(function() {
            let orderId = $(this).data('id');
            let status = $(this).val();
            
            // Gửi yêu cầu cập nhật trạng thái
            $.post('update_order_status.php', { order_id: orderId, status: status }, function(response) {
                let res = JSON.parse(response);
                alert(res.message);
            }, 'json');
        });
    });
</script>
</body>
</html>
