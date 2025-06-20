<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập tài khoản - K_English</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <div class="login-container">
        <form action="login_process.php" method="post" class="login-form">
            <h2>Đăng nhập</h2>
            <div class="social-icons">
                <div class="icon"><i class="fab fa-google"></i></div>
                <div class="icon"><i class="fab fa-facebook-f"></i></div>
                <div class="icon"><i class="fab fa-github"></i></div>
            </div>
            <p class="divider"><span>hoặc</span></p>
            
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i>
                </label>
                <input type="text" id="username" name="username" placeholder="Tên đăng nhập" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                </label>
                <input type="password" id="password" name="password" placeholder="Mật khẩu" required autocomplete="current-password">
                <span class="show-password"><i class="far fa-eye"></i></span>
            </div>
            
            <div class="options">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
                </label>
                <a href="#" class="forgot-password">Quên mật khẩu?</a>
            </div>
            
            <button type="submit" class="btn-login">Đăng nhập</button>
            
            <p class="register-link">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
        </form>
    </div>
    
    <script>
        // Toggle password visibility
        document.querySelector('.show-password').addEventListener('click', function() {
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