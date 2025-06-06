<?php
require_once '../config.php';
require_once './config_vnpay.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Lấy dữ liệu từ VNPAY trả về
$vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

unset($inputData['vnp_SecureHash']);
ksort($inputData);
$hashData = urldecode(http_build_query($inputData));
$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

// Xử lý kết quả thanh toán
$giaoDichThanhCong = false;
$trangThai = 'Chữ ký không hợp lệ';

if ($secureHash == $vnp_SecureHash) {
    if ($_GET['vnp_ResponseCode'] == '00') {
        $giaoDichThanhCong = true;
        $trangThai = 'Thành công';

        // === LƯU ĐƠN HÀNG VÀO BẢNG orders ===
        $name = '';         // Lấy tên người nhận từ session hoặc trước đó
        $phone = '';        // Tương tự
        $address = '';      // Địa chỉ giao hàng
        $note = '';         // Ghi chú nếu có
        $total_price = $_GET['vnp_Amount'] / 100; // Đơn vị VNĐ

        // Giả sử bạn có lưu info khách hàng vào session trước khi chuyển qua VNPAY
        if (isset($_SESSION['checkout'])) {
            $checkout = $_SESSION['checkout'];
            $name = $checkout['name'];
            $phone = $checkout['phone'];
            $address = $checkout['address'];
            $note = $checkout['note'];
        }

        // Thêm vào bảng orders
        $stmtOrder = $conn->prepare("INSERT INTO orders (user_id, name, phone, address, note, total_price, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmtOrder->bind_param("issssds", $_SESSION['user_id'], $name, $phone, $address, $note, $total_price);
        $stmtOrder->execute();
        $order_id = $stmtOrder->insert_id;
        $stmtOrder->close();

        // === LƯU VÀO vnpay_transactions DỰA TRÊN order_id MỚI ===
        $stmt = $conn->prepare("INSERT INTO vnpay_transactions (order_id, vnp_Amount, vnp_OrderInfo, vnp_ResponseCode, vnp_TransactionNo, vnp_BankCode, vnp_PayDate, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $amount = $_GET['vnp_Amount'] / 100; // Đơn vị VNĐ
        $stmt->bind_param(
            "idssssss",
            $order_id,
            $amount,
            $_GET['vnp_OrderInfo'],
            $_GET['vnp_ResponseCode'],
            $_GET['vnp_TransactionNo'],
            $_GET['vnp_BankCode'],
            $_GET['vnp_PayDate'],
            $trangThai
        );
        $stmt->execute();
        $stmt->close();

        // Cập nhật trạng thái đơn hàng sau khi thanh toán thành công
        $stmtOrderUpdate = $conn->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        $stmtOrderUpdate->bind_param("i", $order_id);
        $stmtOrderUpdate->execute();
        $stmtOrderUpdate->close();

        // Xoá giỏ hàng nếu cần
        unset($_SESSION['cart']);
        unset($_SESSION['checkout']);
    } else {
        $trangThai = 'Không thành công';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>VNPAY RESPONSE</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="vnpay_php/assets/bootstrap.min.css" rel="stylesheet"/>
    <link href="vnpay_php/assets/jumbotron-narrow.css" rel="stylesheet">
    <script src="vnpay_php/assets/jquery-1.11.3.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group strong {
            display: block;
            font-weight: bold;
        }
        .form-group span {
            font-size: 1.1em;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #666;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            background-color: #007bff;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1em;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .result-success {
            color: green;
            font-weight: bold;
        }
        .result-fail {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php
require_once './config_vnpay.php';
$vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

unset($inputData['vnp_SecureHash']);
ksort($inputData);
$hashData = '';
$i = 0;
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}
$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
?>
<div class="container">
    <div class="header clearfix">
        <h3 class="text-muted">VNPAY RESPONSE</h3>
    </div>
    <div class="table-responsive">
        <div class="form-group">
            <strong>Mã đơn hàng:</strong>
            <span id="vnp_TxnRef"><?php echo $_GET['vnp_TxnRef'] ?></span>
        </div>
        <div class="form-group">
            <strong>Số tiền:</strong>
            <span id="vnp_Amount"><?php echo $_GET['vnp_Amount'] ?></span>
        </div>
        <div class="form-group">
            <strong>Nội dung thanh toán:</strong>
            <span id="vnp_OrderInfo"><?php echo $_GET['vnp_OrderInfo'] ?></span>
        </div>
        <div class="form-group">
            <strong>Mã phản hồi (vnp_ResponseCode):</strong>
            <span id="vnp_ResponseCode"><?php echo $_GET['vnp_ResponseCode'] ?></span>
        </div>
        <div class="form-group">
            <strong>Mã GD Tại VNPAY:</strong>
            <span id="vnp_TransactionNo"><?php echo $_GET['vnp_TransactionNo'] ?></span>
        </div>
        <div class="form-group">
            <strong>Mã Ngân hàng:</strong>
            <span id="vnp_BankCode"><?php echo $_GET['vnp_BankCode'] ?></span>
        </div>
        <div class="form-group">
            <strong>Thời gian thanh toán:</strong>
            <span id="vnp_PayDate"><?php echo $_GET['vnp_PayDate'] ?></span>
        </div>
        <div class="form-group">
            <strong>Kết quả:</strong>
            <span id="vnp_Result">
                <?php
                if ($secureHash == $vnp_SecureHash) {
                    if ($_GET['vnp_ResponseCode'] == '00') {
                        echo "<span class='result-success'>GD Thành công</span>";
                    } else {
                        echo "<span class='result-fail'>GD Không thành công</span>";
                    }
                } else {
                    echo "<span class='result-fail'>Chữ ký không hợp lệ</span>";
                }
                ?>
            </span>
        </div>
    </div>
    <div class="form-group">
        <a href="../index.php" class="btn">Tiếp tục mua sắm</a>
    </div>
    <footer class="footer">
        <p>&copy; VNPAY <?php echo date('Y') ?></p>
    </footer>
</div>
</body>
</html>

