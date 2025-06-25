<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $query = "SELECT * FROM tai_khoan WHERE ten_dang_nhap = ?";
    $stmt = $mysqli->prepare($query);

    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // So sánh mật khẩu (plain text – chỉ dùng demo)
            if ($password === $user['mat_khau']) {
                session_start();
                $_SESSION['username'] = $username;
                $_SESSION['ma_quyen'] = $user['ma_quyen'];

                if ($user['ma_quyen'] == 1) { // Admin
                    header("Location: admin/home.php");
                    exit;
                } elseif ($user['ma_quyen'] == 2) { // Giáo viên
                    $_SESSION['ma_kh'] = $user['ma_kh'];
                    header("Location: teacher/home.php");
                    exit;
                } elseif ($user['ma_quyen'] == 3) { // Học viên
                    $_SESSION['ma_kh'] = $user['ma_kh'];
                    header("Location: client/home.php");
                    exit;
                } else {
                    $error = "Tài khoản không có quyền truy cập hợp lệ!";
                }
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

if (isset($error)) {
    echo "<script>alert('$error'); window.location.href='account.php';</script>";
    exit;
}
?>
