<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$database = "food_order_db";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');

    if (empty($email)) {
        echo "<script>alert('Vui lòng nhập email!'); window.location.href='forgot_password.php';</script>";
        exit();
    }

    // Kiểm tra email có tồn tại không
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50)); // Tạo token ngẫu nhiên
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour")); // Hết hạn sau 1 giờ

        // Lưu token vào database
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires);
        $stmt->execute();

        // Gửi email chứa link đặt lại mật khẩu
        $reset_link = "http://localhost/DO_AN_MON_HOC/reset_password.php?token=$token";
        $subject = "Đặt lại mật khẩu của bạn";
        $message = "Nhấp vào liên kết sau để đặt lại mật khẩu của bạn: $reset_link";
        $headers = "From: no-reply@foodorder.com";

        mail($email, $subject, $message, $headers);

        echo "<script>alert('Một liên kết đặt lại mật khẩu đã được gửi đến email của bạn!'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Email không tồn tại!'); window.location.href='forgot_password.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu</title>
</head>
<body>
    <h2>Quên Mật Khẩu</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Nhập email của bạn" required>
        <button type="submit">Gửi yêu cầu</button>
    </form>
</body>
</html>
