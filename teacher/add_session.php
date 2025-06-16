<?php
include '../db.php';

$ma_khoa = isset($_GET['ma_khoa']) ? intval($_GET['ma_khoa']) : 0;
$thong_bao = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_buoi = trim($_POST['ten_buoi']);
    $noi_dung = trim($_POST['noi_dung']);
    $ngay_hoc = $_POST['ngay_hoc'];

    if ($ten_buoi && $ngay_hoc) {
        $stmt = $mysqli->prepare("INSERT INTO buoi_hoc (ten_buoi, noi_dung, ngay_hoc, ma_khoa) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssi', $ten_buoi, $noi_dung, $ngay_hoc, $ma_khoa);
        if ($stmt->execute()) {
            header("Location: manage_course.php?ma_khoa=$ma_khoa");
            exit;
        } else {
            $thong_bao = "Lỗi khi thêm buổi học: " . $mysqli->error;
        }
    } else {
        $thong_bao = "Vui lòng nhập đầy đủ thông tin!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm buổi học</title>
    <link rel="stylesheet" href="manage_course.css">
</head>
<body>
    <h2>Thêm buổi học mới</h2>
    <?php if ($thong_bao): ?>
        <p style="color:red;"><?php echo $thong_bao; ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Tên buổi học:</label><br>
        <input type="text" name="ten_buoi" required><br><br>
        <label>Nội dung:</label><br>
        <textarea name="noi_dung" rows="4"></textarea><br><br>
        <label>Ngày học:</label><br>
        <input type="date" name="ngay_hoc" required><br><br>
        <button type="submit" class="btn-add">Thêm</button>
        <a href="manage_course.php?ma_khoa=<?php echo $ma_khoa; ?>" class="btn-back">Quay lại</a>
    </form>
</body>
</html>