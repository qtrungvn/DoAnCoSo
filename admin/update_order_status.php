<?php
session_start();
require_once "../config.php";

// Kiểm tra nếu có dữ liệu gửi lên
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    // Kiểm tra dữ liệu hợp lệ
    if ($orderId > 0 && in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
        // Cập nhật trạng thái đơn hàng trong database
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $orderId);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Trạng thái đơn hàng đã được cập nhật!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Cập nhật trạng thái thất bại, vui lòng thử lại.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ.']);
    }

    $conn->close();
}

