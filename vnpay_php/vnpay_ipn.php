<?php
/* Payment Notify
 * IPN URL: Ghi nhận kết quả thanh toán từ VNPAY
 * Các bước thực hiện:
 * Kiểm tra checksum
 * Tìm giao dịch trong database
 * Kiểm tra số tiền giữa hai hệ thống
 * Kiểm tra tình trạng của giao dịch trước khi cập nhật
 * Cập nhật kết quả vào Database
 * Trả kết quả ghi nhận lại cho VNPAY
 */

require_once "./config_vnpay.php";

$inputData = array();
$returnData = array();

foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

$vnp_SecureHash = $inputData['vnp_SecureHash'];
unset($inputData['vnp_SecureHash']);
ksort($inputData);

$i = 0;
$hashData = "";

foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
$vnpTranId = $inputData['vnp_TransactionNo']; // Mã giao dịch tại VNPAY
$vnp_BankCode = $inputData['vnp_BankCode']; // Ngân hàng thanh toán
$vnp_Amount = $inputData['vnp_Amount'] / 100; // Số tiền thanh toán VNPAY phản hồi

$Status = 0; // Trạng thái thanh toán của giao dịch chưa có IPN lưu tại hệ thống
$orderId = $inputData['vnp_TxnRef'];

try {
    if ($secureHash == $vnp_SecureHash) {
        // Lấy đơn hàng từ DB bằng $orderId
        $order = null;

        if ($order != null) {
            if ($order["Amount"] == $vnp_Amount) {
                if ($order["Status"] != null && $order["Status"] == 0) {
                    if ($inputData['vnp_ResponseCode'] == '00' && $inputData['vnp_TransactionStatus'] == '00') {
                        $Status = 1; // Thanh toán thành công
                    } else {
                        $Status = 2; // Thanh toán lỗi
                    }

                   
                $sql = "UPDATE orders SET status = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $Status, $orderId);

                if ($stmt->execute()) {
                    // Nếu cập nhật thành công, bạn có thể thực hiện thêm các thao tác cần thiết (nếu có)
                    $returnData['RspCode'] = '00';
                    $returnData['Message'] = 'Confirm Success';
                } else {
                    $returnData['RspCode'] = '03';
                    $returnData['Message'] = 'Database update failed';
                }
                $stmt->close();
                
                    $returnData['RspCode'] = '00';
                    $returnData['Message'] = 'Confirm Success';
                } else {
                    $returnData['RspCode'] = '02';
                    $returnData['Message'] = 'Order already confirmed';
                }
            } else {
                $returnData['RspCode'] = '04';
                $returnData['Message'] = 'invalid amount';
            }
        } else {
            $returnData['RspCode'] = '01';
            $returnData['Message'] = 'Order not found';
        }
    } else {
        $returnData['RspCode'] = '97';
        $returnData['Message'] = 'Invalid signature';
    }
} catch (Exception $e) {
    $returnData['RspCode'] = '99';
    $returnData['Message'] = 'Unknown error';
}

echo json_encode($returnData);

