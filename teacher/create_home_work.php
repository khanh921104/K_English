<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 2) { // 2 là giáo viên
    header("Location: ../login.php");
    exit;
}
include '../db.php';

$ma_gv = intval($_SESSION['ma_kh']);
$thong_bao = "";

// Lấy danh sách khóa học mà giáo viên phụ trách
$sql_khoa = "SELECT k.ma_khoa, k.ten_khoa
             FROM khoa_hoc k
             JOIN giao_vien_tao_khoa_hoc gvkh ON k.ma_khoa = gvkh.ma_khoa
             WHERE gvkh.ma_kh = ?";
$stmt_khoa = $mysqli->prepare($sql_khoa);
$stmt_khoa->bind_param('i', $ma_gv);
$stmt_khoa->execute();
$res_khoa = $stmt_khoa->get_result();
$khoa_hoc = $res_khoa->fetch_all(MYSQLI_ASSOC);

// Xử lý khi chọn khóa học để lấy danh sách buổi học
$ma_khoa_selected = isset($_POST['ma_khoa']) ? intval($_POST['ma_khoa']) : 0;
$buoi_hoc = [];
if ($ma_khoa_selected) {
    $sql_buoi = "SELECT ma_buoi, ten_buoi FROM buoi_hoc WHERE ma_khoa = ?";
    $stmt_buoi = $mysqli->prepare($sql_buoi);
    $stmt_buoi->bind_param('i', $ma_khoa_selected);
    $stmt_buoi->execute();
    $res_buoi = $stmt_buoi->get_result();
    $buoi_hoc = $res_buoi->fetch_all(MYSQLI_ASSOC);
}

// Xử lý tạo bài tập
if (isset($_POST['tao_bai_tap'])) {
    $ma_buoi = intval($_POST['ma_buoi']);
    $ten_bai = trim($_POST['ten_bai']);
    $noi_dung = trim($_POST['noi_dung']);
    if ($ma_buoi && $ten_bai && $noi_dung) {
        $stmt = $mysqli->prepare("INSERT INTO bai_tap (ma_buoi, ten_bai, noi_dung) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $ma_buoi, $ten_bai, $noi_dung);
        if ($stmt->execute()) {
            $thong_bao = "Tạo bài tập thành công!";
        } else {
            $thong_bao = "Lỗi: " . $mysqli->error;
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
    <title>Tạo bài tập cho buổi học</title>
    <link rel="stylesheet" href="create_home_work.css">
    
    <script>
        // Tự động submit form khi chọn khóa học để load buổi học
        function submitKhoaHoc() {
            document.getElementById('form-khoa-hoc').submit();
        }
    </script>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="form-container">
        <h2>Tạo bài tập cho buổi học</h2>
        <?php if ($thong_bao): ?>
            <div class="alert-message"><?= htmlspecialchars($thong_bao) ?></div>
        <?php endif; ?>
        <form id="form-khoa-hoc" method="post">
            <div class="form-group">
                <label for="ma_khoa">Chọn khóa học:</label>
                <select name="ma_khoa" id="ma_khoa" onchange="submitKhoaHoc()" required>
                    <option value="">-- Chọn khóa học --</option>
                    <?php foreach ($khoa_hoc as $khoa): ?>
                        <option value="<?= $khoa['ma_khoa'] ?>" <?= $ma_khoa_selected == $khoa['ma_khoa'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($khoa['ten_khoa']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
        <?php if ($ma_khoa_selected): ?>
        <form method="post">
            <input type="hidden" name="ma_khoa" value="<?= $ma_khoa_selected ?>">
            <div class="form-group">
                <label for="ma_buoi">Chọn buổi học:</label>
                <select name="ma_buoi" id="ma_buoi" required>
                    <option value="">-- Chọn buổi học --</option>
                    <?php foreach ($buoi_hoc as $buoi): ?>
                        <option value="<?= $buoi['ma_buoi'] ?>">
                            <?= htmlspecialchars($buoi['ten_buoi']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="ten_bai">Tên bài tập:</label>
                <input type="text" name="ten_bai" id="ten_bai" required>
            </div>
            <div class="form-group">
                <label for="noi_dung">Nội dung bài tập:</label>
                <textarea name="noi_dung" id="noi_dung" rows="4" required></textarea>
            </div>
            <button type="submit" name="tao_bai_tap" class="btn-submit">Tạo bài tập</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>