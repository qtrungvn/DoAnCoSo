<?php
session_start();
require_once "../config.php";

// Kiểm tra nếu có tham số ID từ URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Lấy thông tin người dùng từ database
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        die("Không tìm thấy người dùng!");
    }
}

// Xử lý khi nhấn "Cập nhật"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $role = $_POST['role']; // Lấy quyền hạn từ form

    // Cập nhật thông tin
    $update_sql = "UPDATE users SET fullname = ?, email = ?, username = ?, role = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $fullname, $email, $username, $role, $id);

    if ($stmt->execute()) {
        header("Location: admin_users.php");
        exit();
    } else {
        echo "Lỗi cập nhật!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa Người Dùng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Chỉnh sửa Người Dùng</h2>
    <form method="post">
        <div class="mb-3">
            <label for="fullname">Họ và tên</label>
            <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="username">Tài khoản</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="role">Quyền hạn</label>
            <select class="form-control" id="role" name="role" required>
                <option value="user" <?php echo ($user['role'] == 'user') ? 'selected' : ''; ?>>Người dùng</option>
                <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Quản trị viên</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="admin_users.php" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
</body>
</html>
