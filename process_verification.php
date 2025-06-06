<?php
session_start();

// Định nghĩa hằng số
define('VERIFY_OTP_PAGE_LOCATION', 'Location: verify_otp.php');
define('EMAIL_QUERY_PARAM', '?email=');
define('REGISTER_PAGE_LOCATION_FROM_PROCESS', 'Location: register.php'); // Thêm nếu cần chuyển về trang đăng ký từ file này
define('LOGIN_PAGE_LOCATION', 'Location: login.php');


$servername = "localhost";
$username_db = "root";
$password_db = "";
$database = "food_order_db";

$conn = new mysqli($servername, $username_db, $password_db, $database);

if ($conn->connect_error) {
    $_SESSION['otp_message'] = "Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.";
    $email_param_on_error = isset($_POST['email']) ? EMAIL_QUERY_PARAM . urlencode($_POST['email']) : '';
    header(VERIFY_OTP_PAGE_LOCATION . $email_param_on_error);
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["otp"], $_POST["email"])) {
        $entered_otp = trim($_POST["otp"]);
        $email = trim($_POST["email"]);

        if (isset($_SESSION['registration_data']) && $_SESSION['registration_data']['email'] === $email) {
            $registration_data = $_SESSION['registration_data'];

            if (time() > $registration_data['otp_expiry']) {
                $message = "Mã OTP đã hết hạn. Vui lòng yêu cầu gửi lại OTP.";
            } elseif ($registration_data['otp'] == $entered_otp) {
                $fullname = $registration_data['fullname'];
                $db_email = $registration_data['email'];
                $username = $registration_data['username'];
                $password_hashed = $registration_data['password'];
                $is_verified = 1;

                $check_sql = "SELECT * FROM users WHERE email = ? AND is_verified = 1";
                $stmt_check = $conn->prepare($check_sql);
                if (!$stmt_check) {
                    $message = "Lỗi chuẩn bị truy vấn kiểm tra: " . $conn->error;
                } else {
                    $stmt_check->bind_param("s", $db_email);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();

                    if ($result_check->num_rows > 0) {
                        $message = "Email này đã được đăng ký và xác thực bởi một tài khoản khác. Vui lòng sử dụng email khác.";
                        unset($_SESSION['registration_data']);
                    } else {
                        $delete_unverified_sql = "DELETE FROM users WHERE email = ? AND is_verified = 0";
                        $stmt_delete = $conn->prepare($delete_unverified_sql);
                        if ($stmt_delete) {
                            $stmt_delete->bind_param("s", $db_email);
                            $stmt_delete->execute();
                            $stmt_delete->close();
                        }

                        $sql = "INSERT INTO users (fullname, email, username, password, is_verified) VALUES (?, ?, ?, ?, ?)";
                        $stmt_insert = $conn->prepare($sql);
                        if (!$stmt_insert) {
                            $message = "Lỗi chuẩn bị truy vấn chèn dữ liệu: " . $conn->error;
                        } else {
                            $stmt_insert->bind_param("ssssi", $fullname, $db_email, $username, $password_hashed, $is_verified);

                            if ($stmt_insert->execute()) {
                                unset($_SESSION['registration_data']);
                                $_SESSION['login_message'] = "Đăng ký tài khoản thành công! Bạn có thể đăng nhập ngay bây giờ.";
                                header(LOGIN_PAGE_LOCATION);
                                exit();
                            } else {
                                if ($conn->errno == 1062) {
                                     $message = "Tên đăng nhập đã tồn tại. Vui lòng chọn tên đăng nhập khác.";
                                     $_SESSION['register_error'] = $message;
                                     $_SESSION['form_data'] = [
                                         'fullname' => $registration_data['fullname'],
                                         'email' => $registration_data['email'],
                                         'password' => ""
                                     ];
                                     unset($_SESSION['registration_data']);
                                     header(REGISTER_PAGE_LOCATION_FROM_PROCESS);
                                     exit();
                                } else {
                                    $message = "Lỗi khi đăng ký tài khoản: " . $stmt_insert->error;
                                }
                            }
                            $stmt_insert->close();
                        }
                    }
                    $stmt_check->close();
                }
            } else {
                $message = "Mã OTP không chính xác. Vui lòng thử lại.";
            }
        } else {
            $message = "Phiên đăng ký không hợp lệ hoặc đã hết hạn. Vui lòng thử đăng ký lại từ đầu.";
            unset($_SESSION['registration_data']);
            $_SESSION['register_error'] = $message;
            header(REGISTER_PAGE_LOCATION_FROM_PROCESS);
            exit();
        }
    } else {
        $message = "Dữ liệu không hợp lệ.";
    }
} else {
    // Xử lý toán tử ba ngôi lồng nhau (dòng 120 cũ)
    $redirect_email_param = '';
    if (isset($_SESSION['registration_data']['email'])) {
        $redirect_email_param = EMAIL_QUERY_PARAM . urlencode($_SESSION['registration_data']['email']);
    }
    // Trong trường hợp GET request mà không có session, có thể không cần email param hoặc chuyển về trang đăng ký
    // header(VERIFY_OTP_PAGE_LOCATION . $redirect_email_param);
    header(REGISTER_PAGE_LOCATION_FROM_PROCESS); // An toàn hơn là về trang đăng ký nếu không có POST
    exit();
}

// Nếu có lỗi, lưu lỗi vào session và quay lại trang verify_otp
if (!empty($message)) {
    $_SESSION['otp_message'] = $message;
    // Xử lý toán tử ba ngôi lồng nhau (dòng 120 cũ, giờ là phần này)
    $error_redirect_email_param = '';
    if (isset($_POST['email'])) {
        $error_redirect_email_param = EMAIL_QUERY_PARAM . urlencode($_POST['email']);
    } elseif (isset($_SESSION['registration_data']['email'])) {
        $error_redirect_email_param = EMAIL_QUERY_PARAM . urlencode($_SESSION['registration_data']['email']);
    }
    // Nếu không có email nào, vẫn chuyển hướng nhưng không có param email
    header(VERIFY_OTP_PAGE_LOCATION . $error_redirect_email_param);
    exit();
}

$conn->close();

