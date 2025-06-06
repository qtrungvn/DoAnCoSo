<?php
session_start();
require_once "../config.php";

// Định nghĩa hằng số để tránh lặp lại URL
define("REDIRECT_URL", "Location: admin_users.php");


// Kiểm tra nếu có ID được truyền vào
if (!isset($_GET['id'])) {
    header(REDIRECT_URL);
    exit();
}

$id = intval($_GET['id']);

// Lấy thông tin người dùng
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['message'] = "Không tìm thấy người dùng!";
    header(REDIRECT_URL);
    exit();
}

// Xử lý cập nhật thông tin người dùng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);

    if (empty($fullname) || empty($email) || empty($username)) {
        $_SESSION['message'] = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        $sql = "UPDATE users SET fullname = ?, email = ?, username = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $fullname, $email, $username, $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Cập nhật thành công!";
        } else {
            $_SESSION['message'] = "Lỗi khi cập nhật!";
        }

        $stmt->close();
        header(REDIRECT_URL);
        exit();
    }
}

