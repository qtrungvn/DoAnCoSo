<?php
session_start();

// Định nghĩa hằng số cho URL đăng nhập
define('LOGIN_PAGE', 'Location: login.php');

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <h2>Đăng nhập tài khoản</h2>
            
            <!-- Hiển thị thông báo lỗi nếu có -->
            <?php if (isset($_SESSION['login_error'])): ?>
                <p style="color: red; text-align: center;"><?php echo htmlspecialchars($_SESSION['login_error']); ?></p>
                <?php unset($_SESSION['login_error']); ?>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label for="username">Email hoặc Tên đăng nhập</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Nhập email hoặc tên đăng nhập"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                        required
                    />
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Nhập mật khẩu"
                        required
                    />
                </div>
                <button type="submit" class="btn-submit">Đăng nhập</button>
                <div class="form-footer">
                    <a href="forgot_password.php" class="forgot-pass">Quên mật khẩu?</a>
                    <p>
                        Bạn chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
