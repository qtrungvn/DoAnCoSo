<?php
session_start();

// Định nghĩa hằng số cho URL đăng nhập
define('LOGIN_PAGE', 'Location: login.php');

$servername = "localhost";
$username = "root";
$password = "";
$database = "food_order_db";

$conn = new mysqli($servername, $username, $password, $database);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = trim($_POST["username"] ?? '');
    $input_password = $_POST["password"] ?? '';

    if (empty($input_username) || empty($input_password)) {
        $_SESSION['login_error'] = "Vui lòng điền đầy đủ thông tin!";
        header(LOGIN_PAGE); // Sử dụng hằng số
        exit();
    }

    $sql = "SELECT id, fullname, password, role FROM users WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ss", $input_username, $input_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($input_password, $row["password"])) {
                $_SESSION["user_id"] = $row["id"];
                $_SESSION["fullname"] = $row["fullname"];
                $_SESSION["role"] = $row["role"] ?? 'user';
                $_SESSION["login_success"] = "Đăng nhập thành công!";
                $stmt->close();
                $conn->close();

                if ($row["role"] === "admin") {
                    header("Location: admin/admin_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $_SESSION['login_error'] = "Mật khẩu không đúng!";
            }
        } else {
            $_SESSION['login_error'] = "Tài khoản không tồn tại!";
        }
        $stmt->close();
    } else {
        $_SESSION['login_error'] = "Lỗi hệ thống, vui lòng thử lại sau!";
    }
    $conn->close();

    // Quay lại trang login nếu có lỗi
    header(LOGIN_PAGE); // Sử dụng hằng số
    exit();
} else {
    header(LOGIN_PAGE); // Sử dụng hằng số
    exit();
}
