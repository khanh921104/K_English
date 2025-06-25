<?php

session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 3) {
    header("Location: ../login.php");
    exit;
}
include '../db.php';

$ma_kh = intval($_SESSION['ma_kh']);

// Thống kê tổng số video đã học, số video hoàn thành, tổng số bài tập, số bài tập đã làm, điểm trung bình
// 1. Tổng số video đã học và số video hoàn thành
$sql_video = "SELECT COUNT(*) AS tong_video, SUM(da_hoan_thanh_video) AS hoan_thanh
              FROM lich_su_hoc
              WHERE ma_kh = $ma_kh";
$res_video = $mysqli->query($sql_video);
$row_video = $res_video ? $res_video->fetch_assoc() : ['tong_video'=>0, 'hoan_thanh'=>0];

// 2. Tổng số bài tập đã giao và đã làm
$sql_baitap = "SELECT COUNT(*) AS tong_bai
               FROM bai_tap
               WHERE ma_bai IN (SELECT ma_bai FROM lam_bai_tap WHERE ma_kh = $ma_kh)";
$res_baitap = $mysqli->query($sql_baitap);
$row_baitap = $res_baitap ? $res_baitap->fetch_assoc() : ['tong_bai'=>0];

$sql_lambai = "SELECT COUNT(*) AS da_lam, AVG(diem) AS diem_tb
               FROM lam_bai_tap
               WHERE ma_kh = $ma_kh";
$res_lambai = $mysqli->query($sql_lambai);
$row_lambai = $res_lambai ? $res_lambai->fetch_assoc() : ['da_lam'=>0, 'diem_tb'=>0];

// 3. Lịch sử học chi tiết
$sql_lichsu = "SELECT v.ten_video, lsh.thoi_gian_xem, lsh.so_phut_da_xem, lsh.da_hoan_thanh_video, lsh.lam_xong_bai_tap
               FROM lich_su_hoc lsh
               JOIN video_bai_giang v ON lsh.ma_video = v.ma_video
               WHERE lsh.ma_kh = $ma_kh
               ORDER BY lsh.thoi_gian_xem DESC";
$res_lichsu = $mysqli->query($sql_lichsu);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo quá trình học</title>
    <link rel="stylesheet" href="report.css">
</head>
<body>
    <div class="report-container">
        <h2>Báo cáo quá trình học</h2>
        <div class="stat">
            <strong>Tổng số video đã học:</strong> <?php echo $row_video['tong_video']; ?><br>
            <strong>Số video hoàn thành:</strong> <?php echo $row_video['hoan_thanh']; ?><br>
            <strong>Tổng số bài tập đã làm:</strong> <?php echo $row_lambai['da_lam']; ?><br>
            <strong>Điểm trung bình bài tập:</strong> <?php echo number_format($row_lambai['diem_tb'], 2); ?>
        </div>
        <h3>Lịch sử học chi tiết</h3>
        <table>
            <tr>
                <th>Video</th>
                <th>Thời gian xem</th>
                <th>Số phút đã xem</th>
                <th>Hoàn thành video</th>
                <th>Làm xong bài tập</th>
            </tr>
            <?php if ($res_lichsu && $res_lichsu->num_rows > 0): ?>
                <?php while ($row = $res_lichsu->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ten_video']); ?></td>
                        <td><?php echo htmlspecialchars($row['thoi_gian_xem']); ?></td>
                        <td><?php echo htmlspecialchars($row['so_phut_da_xem']); ?></td>
                        <td><?php echo $row['da_hoan_thanh_video'] ? '✔️' : '❌'; ?></td>
                        <td><?php echo $row['lam_xong_bai_tap'] ? '✔️' : '❌'; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">Chưa có dữ liệu lịch sử học.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>