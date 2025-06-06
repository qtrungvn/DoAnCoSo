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

// Định nghĩa hằng số cho các trang chuyển hướng (nếu chưa có từ file config chung)
if (!defined('REGISTER_PAGE_RESEND_OTP')) {
    define('REGISTER_PAGE_RESEND_OTP', 'Location: register.php');
}
if (!defined('VERIFY_OTP_PAGE_RESEND_OTP')) {
    define('VERIFY_OTP_PAGE_RESEND_OTP', 'Location: verify_otp.php');
}
if (!defined('EMAIL_QUERY_PARAM_RESEND_OTP')) {
    define('EMAIL_QUERY_PARAM_RESEND_OTP', '?email=');
}


$message = ""; // Không dùng biến này nữa, sẽ gán trực tiếp vào session
$email_to_resend = isset($_GET['email']) ? urldecode($_GET['email']) : '';

if (empty($email_to_resend)) {
    $_SESSION['register_error'] = "Thiếu thông tin email để gửi lại OTP.";
    header(REGISTER_PAGE_RESEND_OTP);
    exit();
}

if (!isset($_SESSION['registration_data']) || $_SESSION['registration_data']['email'] !== $email_to_resend) {
    $_SESSION['register_error'] = "Không tìm thấy thông tin đăng ký để gửi lại OTP. Vui lòng thử đăng ký lại.";
    unset($_SESSION['registration_data']); // Xóa session không hợp lệ
    header(REGISTER_PAGE_RESEND_OTP);
    exit();
}

$time_to_wait = 60; // giây
if (isset($_SESSION['last_otp_resend_time']) && $_SESSION['registration_data']['email'] === $email_to_resend && (time() - $_SESSION['last_otp_resend_time']) < $time_to_wait) {
    $remaining_time = $time_to_wait - (time() - $_SESSION['last_otp_resend_time']);
    $_SESSION['otp_message'] = "Vui lòng đợi " . $remaining_time . " giây trước khi yêu cầu gửi lại OTP.";
    header(VERIFY_OTP_PAGE_RESEND_OTP . EMAIL_QUERY_PARAM_RESEND_OTP . urlencode($email_to_resend));
    exit();
}

$registration_data = $_SESSION['registration_data'];
$new_otp = rand(100000, 999999);
$new_otp_expiry = time() + (10 * 60); // Hết hạn sau 10 phút

$_SESSION['registration_data']['otp'] = $new_otp;
$_SESSION['registration_data']['otp_expiry'] = $new_otp_expiry;
// Chỉ cập nhật last_otp_resend_time nếu email khớp với email trong session
// để tránh trường hợp người dùng cố tình thay đổi email trên URL
if ($_SESSION['registration_data']['email'] === $email_to_resend) {
    $_SESSION['last_otp_resend_time'] = time();
}


$fullname = $registration_data['fullname'];

$mail = new PHPMailer(true);
try {
    // Cấu hình Server SMTP (THAY THẾ BẰNG THÔNG TIN CỦA BẠN)
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // THAY THẾ
    $mail->SMTPAuth   = true;
    $mail->Username   = 'your_email@gmail.com'; // THAY THẾ
    $mail->Password   = 'your_gmail_app_password'; // THAY THẾ (Mật khẩu ứng dụng nếu dùng Gmail)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('no-reply@yourwebsite.com', 'Tên Website Của Bạn'); // THAY THẾ
    $mail->addAddress($email_to_resend, $fullname);

    $mail->isHTML(true);
    $mail->Subject = 'Mã OTP mới cho tài khoản của bạn - Tên Website Của Bạn';
    $mail->Body    = "Chào " . htmlspecialchars($fullname) . ",<br><br>Mã OTP mới của bạn là: <b>" . $new_otp . "</b><br>Mã này sẽ hết hạn sau 10 phút.<br><br>Trân trọng,<br>Đội ngũ [Tên Website Của Bạn]";
    $mail->AltBody = "Mã OTP mới của bạn là: " . $new_otp . ". Mã này sẽ hết hạn sau 10 phút.";

    if ($mail->send()) {
        $_SESSION['otp_message'] = "Một mã OTP mới đã được gửi đến email của bạn.";
    } else {
        $_SESSION['otp_message'] = "Không thể gửi lại email OTP. Vui lòng thử lại sau.";
    }
} catch (Exception $e) {
    $_SESSION['otp_message'] = "Lỗi khi gửi lại OTP. Vui lòng thử lại sau một lát.";
    // Ghi log lỗi chi tiết cho admin: error_log("PHPMailer Resend Error: " . $mail->ErrorInfo);
}

header(VERIFY_OTP_PAGE_RESEND_OTP . EMAIL_QUERY_PARAM_RESEND_OTP . urlencode($email_to_resend));
exit();

