<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 2) {
    header("Location: ../login.php");
    exit;
}
include '../db.php';

$ma_kh = intval($_SESSION['ma_kh']);

// Lấy danh sách các khóa học mà giáo viên đã tạo
$sql = "SELECT k.ma_khoa, k.ten_khoa FROM giao_vien_tao_khoa_hoc gvk
        JOIN khoa_hoc k ON gvk.ma_khoa = k.ma_khoa
        WHERE gvk.ma_kh = $ma_kh";
$result = $mysqli->query($sql);
$courses = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Xử lý chấm điểm bài tự luận
if (isset($_POST['cham_bai'])) {
    $ma_bai = intval($_POST['ma_bai']);
    $ma_hv = intval($_POST['ma_hv']);
    $diem = floatval($_POST['diem']);
    $stmt = $mysqli->prepare("UPDATE lam_bai_tap SET diem = ?, trang_thai = 'Hoàn thành' WHERE ma_bai = ? AND ma_kh = ?");
    $stmt->bind_param('dii', $diem, $ma_bai, $ma_hv);
    $stmt->execute();
    $stmt->close();
    $thong_bao = "Đã chấm điểm!";
}

// Lấy danh sách các buổi học có bài tự luận chưa chấm
$sql = "
    SELECT DISTINCT bh.ma_buoi, bh.ten_buoi, kh.ten_khoa, COUNT(lbt.ma_bai) AS so_bai_chua_cham
    FROM giao_vien_tao_khoa_hoc gvk
    JOIN khoa_hoc kh ON gvk.ma_khoa = kh.ma_khoa
    JOIN buoi_hoc bh ON kh.ma_khoa = bh.ma_khoa
    JOIN bai_tap bt ON bh.ma_buoi = bt.ma_buoi AND bt.loai_bai = 'tu_luan'
    JOIN lam_bai_tap lbt ON bt.ma_bai = lbt.ma_bai
    WHERE gvk.ma_kh = ? AND (lbt.trang_thai = 'Chưa hoàn thành' OR lbt.diem IS NULL)
    GROUP BY bh.ma_buoi, bh.ten_buoi, kh.ten_khoa
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $ma_gv);
$stmt->execute();
$result = $stmt->get_result();
$buoi_chua_cham = [];
while ($row = $result->fetch_assoc()) {
    $buoi_chua_cham[] = $row;
}
$stmt->close();

// Xử lý chấm điểm bài tự luận
if (isset($_POST['cham_bai'])) {
    $ma_bai = intval($_POST['ma_bai']);
    $ma_hv = intval($_POST['ma_hv']);
    $diem = floatval($_POST['diem']);
    $stmt = $mysqli->prepare("UPDATE lam_bai_tap SET diem = ?, trang_thai = 'Hoàn thành' WHERE ma_bai = ? AND ma_kh = ?");
    $stmt->bind_param('dii', $diem, $ma_bai, $ma_hv);
    $stmt->execute();
    $stmt->close();

    // ✅ Sau khi chấm xong thì cập nhật thông báo thành đã đọc
    $update_tb = $mysqli->prepare("UPDATE thong_bao SET trang_thai = 'đã đọc' WHERE ma_nguoi_nhan = ? AND loai = 'cham_bai'");
    $update_tb->bind_param("i", $ma_kh);
    $update_tb->execute();
    $update_tb->close();

    $thong_bao = "Đã chấm điểm!";
}

// Lấy danh sách thông báo chưa đọc theo từng khóa học
$thong_bao_cham_bai = [];
$stmt_tb = $mysqli->prepare("
    SELECT ma_khoa, COUNT(*) as so_tb
    FROM thong_bao
    WHERE ma_nguoi_nhan = ? 
    AND loai = 'cham_bai' 
    AND trang_thai = 'chưa đọc'
    GROUP BY ma_khoa
");
$stmt_tb->bind_param("i", $ma_kh);
$stmt_tb->execute();
$result_tb = $stmt_tb->get_result();
while ($row = $result_tb->fetch_assoc()) {
    $thong_bao_cham_bai[$row['ma_khoa']] = $row['so_tb'];
}
$stmt_tb->close();

// Lấy danh sách các buổi học cần chấm cho từng khóa học đã chọn
$buoi_chua_cham = [];
if (isset($_GET['ma_khoa'])) {
    $ma_khoa_chon = intval($_GET['ma_khoa']);
    $sql = "
    SELECT bh.ma_buoi, bh.ten_buoi, kh.ten_khoa, COUNT(lbt.ma_bai) AS so_bai_chua_cham
    FROM buoi_hoc bh
    JOIN khoa_hoc kh ON bh.ma_khoa = kh.ma_khoa
    JOIN bai_tap bt ON bh.ma_buoi = bt.ma_buoi AND bt.loai_bai = 'tu_luan'
    JOIN lam_bai_tap lbt ON bt.ma_bai = lbt.ma_bai
    WHERE bh.ma_khoa = ? AND (lbt.trang_thai = 'Chưa hoàn thành' OR lbt.diem IS NULL)
    GROUP BY bh.ma_buoi, bh.ten_buoi, kh.ten_khoa
    ";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $ma_khoa_chon);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $buoi_chua_cham[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chấm bài tự luận</title>
    <link rel="stylesheet" href="grade.css">
</head>
<body>
<?php include 'header.php'; ?>
<main>
    <div class="container">
        <h2>Khóa học</h2>
        <ul>
        <?php foreach ($courses as $course): ?>
            <li style="position: relative;">
                <a href="?ma_khoa=<?= $course['ma_khoa'] ?>" style="position: relative;">
                    <?= htmlspecialchars($course['ten_khoa']) ?>
                    <?php if (!empty($thong_bao_cham_bai[$course['ma_khoa']])): ?>
                        <span style="
                            position: absolute;
                            top: -5px;
                            left: -10px;
                            width: 10px;
                            height: 10px;
                            background-color: red;
                            border-radius: 50%;
                            display: inline-block;">
                        </span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
    <div class="detail">
        <!-- <?php if (isset($thong_bao)): ?>
            <script>alert("<?= $thong_bao ?>");</script>
        <?php endif; ?> -->

        <?php if (isset($ma_khoa_chon)): ?>
            <h2>Danh sách buổi học cần chấm bài</h2>
            <?php if (count($buoi_chua_cham) > 0): ?>
                <table border="1" cellpadding="10">
                    <tr>
                        <th>Tên khóa học</th>
                        <th>Tên buổi học</th>
                        <th>Số bài chưa chấm</th>
                        <th>Thao tác</th>
                    </tr>
                    <?php foreach ($buoi_chua_cham as $buoi): ?>
                    <tr>
                        <td><?= htmlspecialchars($buoi['ten_khoa']) ?></td>
                        <td><?= htmlspecialchars($buoi['ten_buoi']) ?></td>
                        <td><?= $buoi['so_bai_chua_cham'] ?></td>
                        <td>
                            <a href="detail_grading.php?ma_buoi=<?= $buoi['ma_buoi'] ?>">Chấm bài</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p>Không có buổi học nào cần chấm bài.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
