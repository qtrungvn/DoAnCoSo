<?php
session_start();
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data || empty($data['cart']) || empty($data['name']) || empty($data['phone']) || empty($data['address'])) {
        echo json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ!"]);
        exit;
    }

    // Lấy thông tin khách hàng
    $name = $conn->real_escape_string($data['name']);
    $phone = $conn->real_escape_string($data['phone']);
    $address = $conn->real_escape_string($data['address']);
    $note = isset($data['note']) ? $conn->real_escape_string($data['note']) : '';

    // Tính tổng tiền đơn hàng (chuyển giá sang float chính xác)
    $total_price = array_reduce($data['cart'], function ($sum, $item) {
        $clean_price = (float) str_replace([".", ",", " VNĐ"], "", $item['price']);
        return $sum + ($clean_price * intval($item['quantity']));
    }, 0.00);
    
    // Thêm đơn hàng vào bảng `orders`
    $sql = "INSERT INTO orders (user_id, name, phone, address, note, total_price, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $user_id = $_SESSION['user_id'] ?? null;
    $stmt->bind_param("issssd", $user_id, $name, $phone, $address, $note, $total_price);

    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Thêm chi tiết đơn hàng vào bảng `order_details`
        $sql_detail = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt_detail = $conn->prepare($sql_detail);

        foreach ($data['cart'] as $item) {
            $product_id = intval($item['id']);
            $quantity = intval($item['quantity']);
            $price = (float) str_replace([".", ",", " VNĐ"], "", $item['price']);


            $stmt_detail->bind_param("iiid", $order_id, $product_id, $quantity, $price);
            $stmt_detail->execute();
        }
        $stmt_detail->close();

        // Gửi phản hồi thành công về frontend
        echo json_encode([
            "status" => "success",
            "message" => "Đặt hàng thành công!",
            "redirect_url" => "vnpay_php/vnpay_create_payment.php?order_id=" . $order_id

        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Lỗi khi đặt hàng. Vui lòng thử lại!"]);
    }

    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Phương thức không hợp lệ!"]);
}
