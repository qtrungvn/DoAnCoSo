<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT fullname, email, phone, address, password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Không tìm thấy thông tin tài khoản.";
    exit();
}

// Gán mặc định rỗng nếu dữ liệu bị null
$user['phone'] = $user['phone'] ?? '';
$user['address'] = $user['address'] ?? '';
$user['fullname'] = $user['fullname'] ?? '';
$user['email'] = $user['email'] ?? '';
$user['password'] = $user['password'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql = "UPDATE users SET fullname=?, email=?, phone=?, address=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $fullname, $email, $phone, $address, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Cập nhật thông tin thành công!'); window.location.href='user.php';</script>";
    } else {
        echo "<script>alert('Cập nhật thông tin thất bại!');</script>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($user['password']) && password_verify($old_password, $user['password'])) {
        if ($new_password !== $confirm_password) {
            echo "<script>alert('Mật khẩu mới và xác nhận không khớp!');</script>";
        } else {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_hashed_password, $user_id);
            if ($stmt->execute()) {
                echo "<script>alert('Đổi mật khẩu thành công!'); window.location.href='user.php';</script>";
            } else {
                echo "<script>alert('Đổi mật khẩu thất bại!');</script>";
            }
        }
    } else {
        echo "<script>alert('Mật khẩu cũ không đúng hoặc chưa được thiết lập!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Tài Khoản</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364);
            color: #fff;
            margin: 0;
            padding: 40px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h2 {
            color: #fff;
            margin-bottom: 20px;
            font-size: 28px;
        }

        form {
            background-color: #1e1e2f;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            margin-bottom: 6px;
            display: block;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: none;
            border-radius: 8px;
            background-color: #2a2a3d;
            color: #fff;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            box-shadow: 0 0 6px #00bfff;
        }

        button {
            background-color: #00bfff;
            border: none;
            color: #fff;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #008fcc;
        }

        a {
            color: #00bfff;
            text-decoration: none;
            margin-top: 20px;
            font-size: 16px;
            display: inline-block;
        }

        a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <h2>Thông Tin Tài Khoản</h2>
    <form method="POST">
        <label for="fullname">Họ và Tên:</label>
        <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>

        <label for="phone">Số Điện Thoại:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>

        <label for="address">Địa Chỉ:</label>
        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required>

        <button type="submit" name="update_info">Lưu Thay Đổi</button>
    </form>

    <h2>Đổi Mật Khẩu</h2>
    <form method="POST">
        <label for="old_password">Mật Khẩu Cũ:</label>
        <input type="password" id="old_password" name="old_password" required>

        <label for="new_password">Mật Khẩu Mới:</label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_password">Xác Nhận Mật Khẩu Mới:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit" name="change_password">Đổi Mật Khẩu</button>
    </form>

    <a href="index.php">← Quay lại Trang Chủ</a>

</body>
</html>
