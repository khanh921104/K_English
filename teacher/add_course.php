<?php
// filepath: c:\xampp\htdocs\K_English\teacher\add_course.php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 2) {
    header("Location: ../login.php");
    exit;
}
include '../db.php';

$thong_bao = '';
$ma_kh = isset($_SESSION['ma_kh']) ? intval($_SESSION['ma_kh']) : 0; // mã giáo viên (theo thiết kế của bạn)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_khoa = trim($_POST['ten_khoa']);
    $mo_ta = trim($_POST['mo_ta']);
    $cap_do = trim($_POST['cap_do']);
    $gia = floatval($_POST['gia']);

    if ($ten_khoa && $cap_do && $gia >= 0 && $ma_kh > 0) {
        // 1. Thêm vào bảng khoa_hoc
        $stmt = $mysqli->prepare("INSERT INTO khoa_hoc (ten_khoa, mo_ta, cap_do, gia) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssd', $ten_khoa, $mo_ta, $cap_do, $gia);
        if ($stmt->execute()) {
            $ma_khoa = $stmt->insert_id; // Lấy mã khóa học vừa tạo

            // 2. Thêm vào bảng giao_vien_tao_khoa_hoc
            $stmt2 = $mysqli->prepare("INSERT INTO giao_vien_tao_khoa_hoc (ma_kh, ma_khoa) VALUES (?, ?)");
            $stmt2->bind_param('ii', $ma_kh, $ma_khoa);
            $stmt2->execute();

            header("Location: home.php");
            exit;
        } else {
            $thong_bao = "Lỗi khi thêm khóa học: " . $mysqli->error;
        }
    } else {
        $thong_bao = "Vui lòng nhập đầy đủ thông tin hợp lệ!";
    }
}


?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm khóa học mới</title>
    <link rel="stylesheet" href="add_course.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="add-course-container">
        <h2>Thêm khóa học mới</h2>
        <?php if ($thong_bao): ?>
            <div class="alert-message"><?php echo htmlspecialchars($thong_bao); ?></div>
        <?php endif; ?>
        <form method="post">
            
            <div class="form-group">
                <label for="ten_khoa">Tên khóa học:</label>
                <input type="text" id="ten_khoa" name="ten_khoa" required>
            </div>
            <div class="form-group">
                <label for="mo_ta">Mô tả:</label>
                <textarea id="mo_ta" name="mo_ta" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="cap_do">Cấp độ:</label>
                <input type="text" id="cap_do" name="cap_do" required>
            </div>
            <div class="form-group">
                <label for="gia">Giá (VNĐ):</label>
                <input type="number" id="gia" name="gia" min="0" step="1000" required>
            </div>
            <button type="submit" class="btn-add">Thêm khóa học</button>
        </form>
    </div>
</body>
</html>