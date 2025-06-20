<?php

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Lấy thông tin tài khoản từ database
    $query = "SELECT * FROM tai_khoan WHERE ten_dang_nhap = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // So sánh mật khẩu (ở đây là plain text, nên chỉ dùng cho demo, thực tế nên mã hóa)
            if ($password === $user['mat_khau']) {
                // Đăng nhập thành công
                session_start();
                $_SESSION['username'] = $username;
                $_SESSION['ma_kh'] = $user['ma_kh'];
                $_SESSION['ma_quyen'] = $user['ma_quyen']; // Giả sử cột quyền là 'ma_quyen'

                // Điều hướng theo quyền
                if ($user['ma_quyen'] == 1) {
                    header("Location: home.php");
                } elseif ($user['ma_quyen'] == 2) {
                    header("Location: teacher/home.php");
                } elseif ($user['ma_quyen'] == 3) {
                    header("Location: client/home.php");
                } else {
                    $error = "Tài khoản không có quyền truy cập hợp lệ!";
                }
                exit;
            } else {
                $error = "Sai mật khẩu!";
            }
        } else {
            $error = "Tài khoản không tồn tại!";
        }
        $stmt->close();
    } else {
        $error = "Lỗi truy vấn: " . $mysqli->error;
    }
} else {
    $error = "Phương thức gửi dữ liệu không hợp lệ!";
}

// Hiển thị lỗi nếu có
if (isset($error)) {
    echo "<script>alert('$error'); window.location.href='account.php';</script>";
    exit;
}
?>