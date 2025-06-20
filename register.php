<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản - K_English</title>
    <link rel="stylesheet" href="login.css"> <!-- Dùng chung CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <div class="login-container">
        <form action="register_process.php" method="post" class="login-form">
            <h2>Đăng ký</h2>
            <div class="social-icons">
                <div class="icon"><i class="fab fa-google"></i></div>
                <div class="icon"><i class="fab fa-facebook-f"></i></div>
                <div class="icon"><i class="fab fa-github"></i></div>
            </div>
            <p class="divider"><span>hoặc</span></p>

            <div class="form-group">
                <label for="fullname">
                    <i class="fas fa-user-circle"></i>
                </label>
                <input type="text" id="fullname" name="ho_ten" placeholder="Họ và tên" required>
            </div>
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i>
                </label>
                <input type="text" id="username" name="ten_dang_nhap" placeholder="Tên đăng nhập" required>
            </div>
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i>
                </label>
                <input type="email" id="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <label for="phone">
                    <i class="fas fa-phone"></i>
                </label>
                <input type="text" id="phone" name="so_dien_thoai" placeholder="Số điện thoại" required>
            </div>
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                </label>
                <input type="password" id="password" name="mat_khau" placeholder="Mật khẩu" required>
                <span class="show-password"><i class="far fa-eye"></i></span>
            </div>
            <div class="form-group">
                <label for="confirm-password">
                    <i class="fas fa-lock"></i>
                </label>
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
            </div>

            <button type="submit" class="btn-login">Đăng ký</button>

            <p class="register-link">Đã có tài khoản? <a href="login.html">Đăng nhập</a></p>
        </form>
    </div>

    <script>
        // Toggle password visibility
        document.querySelector('.show-password').addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>
</body>
</html>
