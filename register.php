<?php
session_start(); // Để hiển thị lại dữ liệu form và lỗi nếu có

$form_data = $_SESSION['form_data'] ?? [];
$error_message = $_SESSION['register_error'] ?? '';

unset($_SESSION['form_data']);
unset($_SESSION['register_error']);
?>
<!DOCTYPE html>
<html lang="vi">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đăng ký tài khoản</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <div class="register-container">
      <h2>Đăng ký tài khoản</h2>

      <?php if (!empty($error_message)): ?>
            <p style="color: red; text-align: center;"><?php echo htmlspecialchars($error_message); ?></p>
      <?php endif; ?>

      <form action="register_process_otp.php" method="POST" id="registerForm">
        <div class="form-group">
          <label for="fullname">Họ và tên</label>
          <input
            type="text"
            id="fullname"
            name="fullname"
            placeholder="Nhập họ và tên"
            value="<?php echo htmlspecialchars($form_data['fullname'] ?? ''); ?>"
            required
          />
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="Nhập email"
            value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
            required
          />
        </div>
        <div class="form-group">
          <label for="username">Tên đăng nhập</label>
          <input
            type="text"
            id="username"
            name="username"
            placeholder="Nhập tên đăng nhập"
            value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>"
            required
          />
        </div>
        <div class="form-group">
          <label for="password">Mật khẩu</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Nhập mật khẩu (ít nhất 6 ký tự)"
            required
          />
        </div>
        <div class="form-group">
          <label for="confirm_password">Xác nhận mật khẩu</label>
          <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            placeholder="Nhập lại mật khẩu"
            required
          />
        </div>
        <button type="submit" class="btn-register">Đăng ký</button>
        <p>Bạn đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
      </form>
    </div>
    <script>
      const password = document.getElementById("password");
      const confirm_password = document.getElementById("confirm_password");
      const registerForm = document.getElementById("registerForm");

      function validatePassword() {
        if (password.value !== confirm_password.value) {
          confirm_password.setCustomValidity("Mật khẩu không khớp!");
        } else {
          confirm_password.setCustomValidity('');
        }
      }

      if (password && confirm_password) {
        password.onchange = validatePassword;
        confirm_password.onkeyup = validatePassword;
      }

      // Client-side validation for password length
      if (registerForm && password) {
        registerForm.addEventListener('submit', function(event) {
          if (password.value.length < 6) {
            alert("Mật khẩu phải có ít nhất 6 ký tự!");
            password.focus();
            event.preventDefault(); // Prevent form submission
            return false;
          }
          if (password.value !== confirm_password.value) {
            alert("Mật khẩu xác nhận không khớp!");
            confirm_password.focus();
            event.preventDefault(); // Prevent form submission
            return false;
          }
        });
      }
    </script>
  </body>
</html>
