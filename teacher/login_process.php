<?php
require_once '../db.php';
session_start(); // Đảm bảo khởi động session sớm

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Kiểm tra dữ liệu nhập
    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } else {
        // Truy vấn người dùng
        $query = "SELECT * FROM tai_khoan_giao_vien WHERE ten_dang_nhap = ?";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                // Kiểm tra mật khẩu (plain text - chỉ dùng cho demo)
                if ($password === $user['mat_khau']) {
                    $_SESSION['username'] = $username;
                    $_SESSION['ma_kh'] = $user['ma_kh'];
                    header("Location: home.php");
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
    }
} else {
    $error = "Phương thức gửi dữ liệu không hợp lệ!";
}

// Hiển thị lỗi nếu có
if (isset($error)) {
    echo "<script>alert('$error'); window.location.href='login.php';</script>";
    exit;
}
?>
