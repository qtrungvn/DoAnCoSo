<?php
session_start();
$email_to_verify = isset($_GET['email']) ? htmlspecialchars(urldecode($_GET['email'])) : '';

// Nếu không có email hoặc không có session đăng ký, chuyển về trang đăng ký
if (empty($email_to_verify) || !isset($_SESSION['registration_data']) || $_SESSION['registration_data']['email'] !== $email_to_verify) {
    $_SESSION['register_error'] = "Phiên làm việc không hợp lệ hoặc đã hết hạn. Vui lòng thử đăng ký lại.";
    // Xóa session registration_data không hợp lệ nếu có
    unset($_SESSION['registration_data']);
    header("Location: register.php");
    exit();
}

$message = $_SESSION['otp_message'] ?? '';
unset($_SESSION['otp_message']); // Xóa thông báo sau khi hiển thị
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực OTP</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    padding: 20px;
    box-sizing: border-box;
}

.register-container {
    background-color: #fff;
    padding: 25px 30px;
    border-radius: 8px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}

.register-container h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #555;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 16px;
}

.form-group input:focus {
    border-color: #007bff;
    outline: none;
}

.btn-register {
    width: 100%;
    padding: 12px;
    background-color: #007bff;
    border: none;
    border-radius: 4px;
    color: white;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-register:hover {
    background-color: #0056b3;
}

.register-container p {
    text-align: center;
    margin-top: 15px;
    color: #555;
}

.register-container p a {
    color: #007bff;
    text-decoration: none;
}

.register-container p a:hover {
    text-decoration: underline;
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .register-container {
        padding: 20px;
    }
    .register-container h2 {
        font-size: 1.5em;
    }
}
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Xác thực tài khoản</h2>
        <p>Một mã OTP đã được gửi đến địa chỉ email: <strong><?php echo $email_to_verify; ?></strong></p>
        <p>Vui lòng kiểm tra hộp thư (kể cả mục Spam/Junk) và nhập mã OTP vào ô bên dưới. Mã có hiệu lực trong 10 phút.</p>

        <?php if (!empty($message)): ?>
            <p style="color: <?php echo (strpos(strtolower($message), 'thành công') !== false || strpos(strtolower($message), 'đã gửi') !== false) ? 'green' : 'red'; ?>; text-align: center;">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form action="process_verification.php" method="POST">
            <input type="hidden" name="email" value="<?php echo $email_to_verify; ?>">
            <div class="form-group">
                <label for="otp">Mã OTP</label>
                <input type="text" id="otp" name="otp" placeholder="Nhập mã OTP (6 chữ số)" required maxlength="6" pattern="\d{6}" inputmode="numeric">
            </div>
            <button type="submit" class="btn-register">Xác thực</button>
        </form>
        <p style="margin-top: 15px;">
            <a href="resend_otp.php?email=<?php echo urlencode($email_to_verify); ?>">Gửi lại OTP</a> (Nếu chưa nhận được hoặc OTP hết hạn)
        </p>
        <p><a href="register.php">Quay lại trang đăng ký</a></p>
    </div>
</body>
</html>
