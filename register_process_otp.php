<?php
session_start();

// PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Đường dẫn tới file PHPMailer (sử dụng require_once)
require_once 'vendor/PHPMailer-master/src/Exception.php';
require_once 'vendor/PHPMailer-master/src/PHPMailer.php';
require_once 'vendor/PHPMailer-master/src/SMTP.php';

// Định nghĩa hằng số cho trang đăng ký
define('REGISTER_PAGE_LOCATION', 'Location: register.php');

// Thông tin kết nối Database
$servername = "localhost";
$username_db = "root";
$password_db = "";
$database = "food_order_db";

// Kết nối MySQL
$conn = new mysqli($servername, $username_db, $password_db, $database);

if ($conn->connect_error) {
    $_SESSION['register_error'] = "Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.";
    header(REGISTER_PAGE_LOCATION); // Sử dụng hằng số
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["fullname"], $_POST["email"], $_POST["username"], $_POST["password"], $_POST["confirm_password"])) {
        $fullname = trim($_POST["fullname"]);
        $email = trim($_POST["email"]);
        $username_form = trim($_POST["username"]);
        $password_form = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];

        $_SESSION['form_data'] = $_POST;

        if ($password_form !== $confirm_password) {
            $message = "Mật khẩu xác nhận không khớp!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Định dạng email không hợp lệ!";
        } elseif (strlen($password_form) < 6) {
            $message = "Mật khẩu phải có ít nhất 6 ký tự!";
        } else {
            $check_sql = "SELECT * FROM users WHERE (email = ? OR username = ?) AND is_verified = 1";
            $stmt_check = $conn->prepare($check_sql);
            if (!$stmt_check) {
                 $message = "Lỗi chuẩn bị truy vấn: " . $conn->error;
            } else {
                $stmt_check->bind_param("ss", $email, $username_form);
                $stmt_check->execute();
                $result = $stmt_check->get_result();

                if ($result->num_rows > 0) {
                    $message = "Email hoặc tên đăng nhập đã tồn tại và đã được xác thực!";
                } else {
                    $otp = rand(100000, 999999);
                    $otp_expiry = time() + (10 * 60);

                    $_SESSION['registration_data'] = [
                        'fullname' => $fullname,
                        'email' => $email,
                        'username' => $username_form,
                        'password' => password_hash($password_form, PASSWORD_DEFAULT),
                        'otp' => $otp,
                        'otp_expiry' => $otp_expiry
                    ];

                    $mail = new PHPMailer(true);
                    try {
                        $mail->SMTPDebug = SMTP::DEBUG_OFF;
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com'; // THAY THẾ
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'trungkg915@gmail.com'; // THAY THẾ
                        $mail->Password   = 'sxap hyig crqz jfdr'; // THAY THẾ
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        $mail->Port       = 465;
                        $mail->CharSet    = 'UTF-8';

                        $mail->setFrom('trungkg915@gmail.com', 'FastFoot'); // THAY THẾ
                        $mail->addAddress($email, $fullname);

                        $mail->isHTML(true);
                        $mail->Subject = 'Mã OTP xác thực tài khoản - FastFood';
                        $mail->Body = "Chào " . htmlspecialchars($fullname) . ",<br><br>
                                Cảm ơn bạn đã đăng ký tài khoản tại [FastFood].<br>
                                Mã OTP của bạn là: <b>" . $otp . "</b><br>
                                Mã này sẽ hết hạn sau 10 phút.<br><br>
                                Trân trọng,<br>
                                Đội ngũ [FastFood]";
                        $mail->AltBody = "Mã OTP của bạn là: " . $otp . ". Mã này sẽ hết hạn sau 10 phút.";

                        if ($mail->send()) {
                            unset($_SESSION['form_data']);
                            header("Location: verify_otp.php?email=" . urlencode($email));
                            exit();
                        } else {
                             $message = "Không thể gửi email OTP. Vui lòng thử lại sau.";
                        }
                    } catch (Exception $e) {
                        $message = "Không thể gửi email. Lỗi PHPMailer: {$mail->ErrorInfo}. Vui lòng thử lại sau.";
                    }
                }
                $stmt_check->close();
            }
        }
    } else {
        $message = "Vui lòng điền đầy đủ thông tin!";
    }
} else {
    header(REGISTER_PAGE_LOCATION); // Sử dụng hằng số
    exit();
}

if (!empty($message)) {
    $_SESSION['register_error'] = $message;
    header(REGISTER_PAGE_LOCATION); // Sử dụng hằng số
    exit();
}

$conn->close();
