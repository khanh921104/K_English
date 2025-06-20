
<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);
    $ten_dang_nhap = trim($_POST['ten_dang_nhap']);
    $mat_khau = trim($_POST['mat_khau']);
    $confirm_password = trim($_POST['confirm_password']);

    // Kiểm tra dữ liệu nhập vào
    if ($ho_ten && $email && $so_dien_thoai && $ten_dang_nhap && $mat_khau) {
        if ($mat_khau !== $confirm_password) {
            $error = "Mật khẩu nhập lại không khớp!";
        } else {
            // Kiểm tra tên đăng nhập đã tồn tại chưa
            $check = $mysqli->prepare("SELECT * FROM tai_khoan WHERE ten_dang_nhap = ?");
            $check->bind_param("s", $ten_dang_nhap);
            $check->execute();
            $result = $check->get_result();
            if ($result && $result->num_rows > 0) {
                $error = "Tên đăng nhập đã tồn tại!";
            } else {
                // Thêm vào bảng khach_hang
                $stmt1 = $mysqli->prepare("INSERT INTO khach_hang (ho_ten, email, so_dien_thoai) VALUES (?, ?, ?)");
                $stmt1->bind_param("sss", $ho_ten, $email, $so_dien_thoai);
                if ($stmt1->execute()) {
                    $ma_kh = $stmt1->insert_id; // Lấy mã khách hàng vừa thêm

                    // Thêm vào bảng tai_khoan (giả sử quyền client là 3)
                    $ma_quyen = 3;
                    $stmt2 = $mysqli->prepare("INSERT INTO tai_khoan (ten_dang_nhap, mat_khau, ma_quyen, ma_kh) VALUES (?, ?, ?, ?)");
                    $stmt2->bind_param("ssii", $ten_dang_nhap, $mat_khau, $ma_quyen, $ma_kh);
                    if ($stmt2->execute()) {
                        // Đăng ký thành công
                        header("Location: login.php?success=1");
                        exit;
                    } else {
                        $error = "Lỗi khi tạo tài khoản: " . $mysqli->error;
                    }
                } else {
                    $error = "Lỗi khi thêm khách hàng: " . $mysqli->error;
                }
            }
        }
    } else {
        $error = "Vui lòng nhập đầy đủ thông tin!";
    }
}

// Hiển thị lỗi nếu có
if (isset($error)) {
    echo "<div style='color:red;text-align:center;margin:18px 0;'>$error</div>";
}
?>
