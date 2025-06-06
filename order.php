<?php
session_start();
require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        echo json_encode(['status' => 'error', 'message' => 'Giỏ hàng trống!']);
        exit;
    }
    
    $customer_name = $_POST['name'] ?? '';
    $customer_phone = $_POST['phone'] ?? '';
    $customer_address = $_POST['address'] ?? '';
    $delivery_method = $_POST['delivery'] ?? 'Giao tận nơi';
    $note = $_POST['note'] ?? '';
    $total_price = 0;

    foreach ($_SESSION['cart'] as $item) {
        $total_price += $item['price'] * $item['quantity'];
    }

    $total_price += ($delivery_method === 'Giao tận nơi') ? 30000 : 0;

    $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_phone, customer_address, delivery_method, note, total_price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssd", $customer_name, $customer_phone, $customer_address, $delivery_method, $note, $total_price);

    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
        foreach ($_SESSION['cart'] as $id => $item) {
            $stmt->bind_param("isdi", $order_id, $item['name'], $item['price'], $item['quantity']);
            $stmt->execute();
        }
        $stmt->close();
        $_SESSION['cart'] = [];
        echo json_encode(['status' => 'success', 'message' => 'Đặt hàng thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Đặt hàng thất bại!']);
    }
    $conn->close();
    exit;
}
