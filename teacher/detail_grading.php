<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 2) {
    header("Location: ../login.php");
    exit;
}
include '../db.php';

$ma_buoi = isset($_GET['ma_buoi']) ? intval($_GET['ma_buoi']) : 0;
if ($ma_buoi <= 0) {
    echo "Thiếu mã buổi học!";
    exit;
}

// Xử lý chấm điểm
if (isset($_POST['cham_bai'])) {
    $ma_bai = intval($_POST['ma_bai']);
    $ma_hv = intval($_POST['ma_hv']);
    $diem = floatval($_POST['diem']);
    $nhan_xet = trim($_POST['nhan_xet']);
    $stmt = $mysqli->prepare("UPDATE lam_bai_tap SET diem = ?, trang_thai = 'Hoàn thành', nhan_xet = ? WHERE ma_bai = ? AND ma_kh = ?");
    $stmt->bind_param('dsii', $diem, $nhan_xet, $ma_bai, $ma_hv);
    $stmt->execute();
    $stmt->close();
    $thong_bao = "Đã chấm điểm!";
}

// Lấy danh sách bài tự luận của buổi học
$sql = "SELECT lbt.ma_bai, lbt.ma_kh, kh.ho_ten, tl.ten_bai, tl.noi_dung, lbt.dap_an, lbt.diem, lbt.trang_thai, lbt.nhan_xet
        FROM lam_bai_tap lbt
        JOIN bai_tap bt ON lbt.ma_bai = bt.ma_bai
        JOIN tu_luan tl ON bt.ma_bai = tl.ma_bai
        JOIN khach_hang kh ON lbt.ma_kh = kh.ma_kh
        WHERE bt.ma_buoi = ? AND bt.loai_bai = 'tu_luan'
        ORDER BY lbt.trang_thai ASC, kh.ho_ten ASC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $ma_buoi);
$stmt->execute();
$result = $stmt->get_result();
$tu_luan_list = [];
while ($row = $result->fetch_assoc()) {
    $tu_luan_list[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chấm bài tự luận buổi học</title>
    <link rel="stylesheet" href="detail_grading.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="grade-container">
    <h2>Chấm bài tự luận - Buổi học ID: <?php echo htmlspecialchars($ma_buoi); ?></h2>

    <?php if (isset($thong_bao)): ?>
        <div class="alert-message"><?php echo htmlspecialchars($thong_bao); ?></div>
    <?php endif; ?>

    <?php if (empty($tu_luan_list)): ?>
        <p>Chưa có bài tự luận nào được nộp cho buổi học này.</p>
    <?php else: ?>
        <div class="grade-table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Họ tên học viên</th>
                        <th>Tên bài</th>
                        <th>Nội dung đề bài</th>
                        <th>Đáp án nộp</th>
                        <th>Điểm</th>
                        <th>Nhận xét</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $stt = 1; foreach ($tu_luan_list as $bai): ?>
                        <tr>
                            <td><?php echo $stt++; ?></td>
                            <td><?php echo htmlspecialchars($bai['ho_ten']); ?></td>
                            <td><?php echo htmlspecialchars($bai['ten_bai']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($bai['noi_dung'])); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($bai['dap_an'])); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="ma_bai" value="<?php echo $bai['ma_bai']; ?>">
                                    <input type="hidden" name="ma_hv" value="<?php echo $bai['ma_kh']; ?>">
                                    <input type="number" name="diem" value="<?php echo $bai['diem'] ?? 0; ?>" min="0" max="10" step="0.1" required style="width:60px;padding:4px;">
                            </td>
                            <td>
                                <textarea name="nhan_xet" rows="2" style="width:150px;padding:4px;"><?php echo htmlspecialchars($bai['nhan_xet'] ?? ''); ?></textarea>
                            </td>
                            <td><?php echo htmlspecialchars($bai['trang_thai']); ?></td>
                            <td>
                                <button type="submit" name="cham_bai" class="btn-grade">Lưu</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</body>
</html>