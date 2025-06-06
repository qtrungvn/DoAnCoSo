<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    echo "Vui lòng đăng nhập để xem lịch sử đơn hàng.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Kiểm tra nếu có yêu cầu hủy đơn
if (isset($_GET['cancel_order_id'])) {
    $cancel_order_id = (int)$_GET['cancel_order_id'];
    
    // Kiểm tra trạng thái đơn hàng
    $sql_check_status = "SELECT status FROM orders WHERE id = ?";
    $stmt_check_status = $conn->prepare($sql_check_status);
    $stmt_check_status->bind_param("i", $cancel_order_id);
    $stmt_check_status->execute();
    $result = $stmt_check_status->get_result();
    $order = $result->fetch_assoc();
    
    if ($order && $order['status'] == 'pending') {
        // Cập nhật trạng thái đơn hàng thành "canceled"
        $sql_update = "UPDATE orders SET status = 'canceled' WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $cancel_order_id);
        
        if ($stmt_update->execute()) {
            echo "<script>alert('Đơn hàng đã được hủy thành công.'); window.location.href='order_history.php';</script>";
        } else {
            echo "Lỗi khi hủy đơn hàng: " . $stmt_update->error;
        }
    } else {
        echo "Không thể hủy đơn hàng này vì đơn hàng không có trạng thái 'Đang chờ'.";
    }
    exit;
}

// Xử lý lọc theo ngày
$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Lấy các đơn hàng trong ngày
$sql = "SELECT * FROM orders WHERE user_id = ? AND DATE(created_at) = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $filter_date);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// Hàm tạo mã đơn hàng ngẫu nhiên (không lặp)
function generateRandomOrderCode($id) {
    $prefix = 'HD';
    $date = date('Ymd');
    $random = substr(md5($id . uniqid()), 0, 6); // tạo chuỗi 6 ký tự ngẫu nhiên từ id
    return $prefix . $date . strtoupper($random);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử đơn hàng</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 12px;
        }
        h2 {
            color: #007bff;
            text-align: center;
            margin-bottom: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background-color: #007bff;
            color: white;
        }
        th, td {
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
            text-align: center;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .empty {
            text-align: center;
            color: #777;
            font-size: 18px;
            margin-top: 20px;
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            text-decoration: none;
        }
        .btn-cancel:hover {
            background-color: #c82333;
        }
        .date-picker {
            display: block;
            margin: 20px auto;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .btn-back {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: block;
            margin: 20px auto;
            text-align: center;
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Lịch sử đơn hàng</h2>

    <!-- Form chọn ngày -->
    <form method="get">
        <label for="date">Chọn ngày:</label>
        <input type="date" id="date" name="date" class="date-picker" value="<?= htmlspecialchars($filter_date) ?>" max="<?= date('Y-m-d') ?>" />
        <button type="submit">Lọc đơn hàng</button>
    </form>

    <?php if (empty($orders)): ?>
        <p class="empty">Bạn chưa có đơn hàng nào trong ngày <?= date('d/m/Y', strtotime($filter_date)) ?>.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày đặt</th>
                    <th>Trạng thái</th>
                    <th>Tổng tiền</th>
                    <th>Hủy đơn</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= generateRandomOrderCode($order['id']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                        <td><?= ucfirst($order['status']) ?></td>
                        <td><?= number_format($order['total_price']) ?> đ</td>
                        <td>
                            <?php if ($order['status'] == 'pending'): ?>
                                <a href="?cancel_order_id=<?= $order['id'] ?>" class="btn-cancel" onclick="return confirm('Bạn chắc chắn muốn hủy đơn hàng này?')">Hủy đơn</a>
                            <?php else: ?>
                                <span>Không thể hủy</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>

    <!-- Nút trở về trang index.php -->
    <a href="/DO_AN_MON_HOC/index.php" class="btn-back">Trở về trang chủ</a>
</div>
</body>
</html>

