<!-- filepath: c:\xampp\htdocs\K_English\client\login.php -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập tài khoản</title>
    <link rel="stylesheet" href="login.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    
    <div class="login-container">
        <form action="login_process.php" method="post" class="login-form">
            <h2>Đăng nhập</h2>
            <div class="form-group">
                <label for="username">Tên đăng nhập:</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-login">Đăng nhập</button>
        </form>
        <hr style="margin: 24px 0; border: 1px solid #ccc;">
        <div class="google-login-container">
            <button class="btn-google-login">
                <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google" class="google-icon">
                Đăng nhập bằng Google
            </button>
        </div>
    </div>
</body>
</html>
