<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once "./config_vnpay.php";
require_once "../config.php"; // kết nối DB chính xác

// 1. Lấy order_id từ URL
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
if ($order_id <= 0) {
    die("Thiếu order_id hoặc không hợp lệ.");
}

// 2. Truy vấn đơn hàng từ DB
$sql = "SELECT * FROM orders WHERE id = $order_id";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("Không tìm thấy đơn hàng.");
}

// 3. Gán thông tin đơn hàng
$vnp_TxnRef = $order_id; // Mã đơn hàng = order_id để khớp DB
$vnp_Amount = $order['total_price'] * 100; // Nhân 100 vì đơn vị VNĐ
$vnp_Locale = 'vn';
$vnp_BankCode = $_POST['bank_code'] ?? '';
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
$expire = date('YmdHis', strtotime('+10 minutes'));

// 4. Tạo dữ liệu gửi sang VNPAY
$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => "Thanh toán đơn hàng #$vnp_TxnRef",
    "vnp_OrderType" => "other",
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef,
    "vnp_ExpireDate" => $expire
);

if (!empty($vnp_BankCode)) {
    $inputData['vnp_BankCode'] = $vnp_BankCode;
}

// 5. Tạo chuỗi dữ liệu và mã hóa bảo mật
ksort($inputData);
$hashdata = "";
$query = "";
$i = 0;
foreach ($inputData as $key => $value) {
    $hashdata .= ($i ? '&' : '') . urlencode($key) . "=" . urlencode($value);
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
    $i++;
}
$vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
$vnp_Url = $vnp_Url . "?" . $query . "vnp_SecureHash=" . $vnp_SecureHash;

// 6. Chuyển hướng sang VNPAY
header("Location: " . $vnp_Url);
echo $vnp_Url;
exit;
