<?php
session_start();
require_once "../config.php";

// Kiểm tra xem có ID được truyền vào không
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Xóa người dùng theo ID
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Xóa người dùng thành công!";
    } else {
        $_SESSION['message'] = "Lỗi khi xóa người dùng!";
    }

    $stmt->close();
    $conn->close();
}

// Quay lại trang danh sách người dùng
header("Location: admin_users.php");
exit();
