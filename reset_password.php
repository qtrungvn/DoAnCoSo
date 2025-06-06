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

$token = $_GET["token"] ?? '';

if (empty($token)) {
    die("Token không hợp lệ!");
}

// Kiểm tra token trong database
$stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Liên kết đặt lại mật khẩu đã hết hạn hoặc không hợp lệ!");
}

$row = $result->fetch_assoc();
$email = $row["email"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST["password"] ?? '';
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Cập nhật mật khẩu mới vào users
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);
    $stmt->execute();

    // Xóa token sau khi đổi mật khẩu thành công
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    echo "<script>alert('Mật khẩu đã được đặt lại thành công!'); window.location.href='login.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lại Mật Khẩu</title>
</head>
<body>
    <h2>Đặt Lại Mật Khẩu</h2>
    <form method="POST">
        <input type="password" name="password" placeholder="Nhập mật khẩu mới" required>
        <button type="submit">Đặt lại mật khẩu</button>
    </form>
</body>
</html>
