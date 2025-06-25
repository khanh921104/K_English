<?php

session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 3) {
    header("Location: ../login.php");
    exit;
}
include '../db.php';

$ma_kh = intval($_SESSION['ma_kh']);

// Lấy danh sách các khóa học của người dùng
$sql_courses = "SELECT k.ma_khoa, k.ten_khoa
                FROM khoa_hoc k
                JOIN dang_ky dk ON k.ma_khoa = dk.ma_khoa
                WHERE dk.ma_kh = $ma_kh";
$res_courses = $mysqli->query($sql_courses);

$courses = [];
$selected_course_id = null;
if ($res_courses && $res_courses->num_rows > 0) {
    while ($row = $res_courses->fetch_assoc()) {
        $courses[] = $row;
    }
    // Lấy id khóa học được chọn hoặc mặc định là khóa đầu tiên
    $selected_course_id = isset($_GET['khoa']) ? intval($_GET['khoa']) : $courses[0]['ma_khoa'];
} else {
    $selected_course_id = 0;
}

// Nếu có khóa học, lấy thống kê cho khóa học được chọn
if ($selected_course_id) {
    // Tổng số video đã học và số video hoàn thành trong khóa này
    $sql_video = "SELECT COUNT(*) AS tong_video, SUM(lsh.da_hoan_thanh_video) AS hoan_thanh
                  FROM buoi_hoc bh
                  JOIN video_bai_giang v ON bh.ma_buoi = v.ma_buoi
                  LEFT JOIN lich_su_hoc lsh ON v.ma_video = lsh.ma_video AND lsh.ma_kh = $ma_kh
                  WHERE bh.ma_khoa = $selected_course_id";
    $res_video = $mysqli->query($sql_video);
    $row_video = $res_video ? $res_video->fetch_assoc() : ['tong_video'=>0, 'hoan_thanh'=>0];

    // Tổng số bài tập đã giao và đã làm trong khóa này
    $sql_baitap = "SELECT COUNT(*) AS tong_bai
                   FROM bai_tap bt
                   JOIN buoi_hoc bh ON bt.ma_buoi = bh.ma_buoi
                   WHERE bh.ma_khoa = $selected_course_id";
    $res_baitap = $mysqli->query($sql_baitap);
    $row_baitap = $res_baitap ? $res_baitap->fetch_assoc() : ['tong_bai'=>0];

    $sql_lambai = "SELECT COUNT(*) AS da_lam, AVG(diem) AS diem_tb
                   FROM lam_bai_tap lbt
                   JOIN bai_tap bt ON lbt.ma_bai = bt.ma_bai
                   JOIN buoi_hoc bh ON bt.ma_buoi = bh.ma_buoi
                   WHERE lbt.ma_kh = $ma_kh AND bh.ma_khoa = $selected_course_id";
    $res_lambai = $mysqli->query($sql_lambai);
    $row_lambai = $res_lambai ? $res_lambai->fetch_assoc() : ['da_lam'=>0, 'diem_tb'=>0];

    // Lịch sử học chi tiết
    $sql_lichsu = "SELECT v.ten_video, lsh.thoi_gian_xem, lsh.so_phut_da_xem, lsh.da_hoan_thanh_video, lsh.lam_xong_bai_tap
                   FROM buoi_hoc bh
                   JOIN video_bai_giang v ON bh.ma_buoi = v.ma_buoi
                   LEFT JOIN lich_su_hoc lsh ON v.ma_video = lsh.ma_video AND lsh.ma_kh = $ma_kh
                   WHERE bh.ma_khoa = $selected_course_id
                   ORDER BY lsh.thoi_gian_xem DESC";
    $res_lichsu = $mysqli->query($sql_lichsu);
}

// Lấy danh sách các khóa học đã học xong
$completed_courses = [];
$sql_completed = "SELECT ma_khoa FROM dang_ky WHERE ma_kh = $ma_kh AND trang_thai = 'completed'";
$res_completed = $mysqli->query($sql_completed);
if ($res_completed) {
    while ($row = $res_completed->fetch_assoc()) {
        $completed_courses[] = $row['ma_khoa'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo quá trình học</title>
    <link rel="stylesheet" href="report.css">
    <style>
        .main-flex {
            display: flex;
            gap: 32px;
            margin: 32px auto;
            max-width: 1200px;
        }
        .course-list {
            flex: 3;
            background:rgb(255, 255, 255);
            border-radius: 12px;
            padding: 24px 18px;
            min-width: 220px;
            max-width: 320px;
            box-shadow: 0 2px 12px rgba(21,101,192,0.07);
            height: fit-content;
        }
        .course-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .course-list li {
            margin-bottom: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            background-color: #e3f2fd;
            transition: background 0.3s, color 0.2s;
            cursor: pointer;
        }
        .course-list li.selected,
        .course-list li:hover {
            background: #1976d2;
            color: #fff;
        }
        .course-list li.selected a,
        .course-list li.selected span,
        .course-list li:hover a,
        .course-list li:hover span {
            color: #fff;
        }
        .course-list a {
            color: #1976d2;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        .report-container {
            flex: 7;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(21,101,192,0.10);
            padding: 32px 28px;
        }
        .stat { margin-bottom: 18px; font-size: 1.1rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th, td { border: 1px solid #90caf9; padding: 8px 12px; text-align: center; }
        th { background: #e3f2fd; color: #1565c0; }
        tr:nth-child(even) { background: #f8fafc; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="main-flex">
        <div class="course-list">
            <h3>Các khóa học của bạn</h3>
            <ul>
                <?php if ($courses): ?>
                    <?php foreach ($courses as $course): ?>
                        <?php
                            $is_selected = ($course['ma_khoa'] == $selected_course_id);
                            $is_completed = in_array($course['ma_khoa'], $completed_courses);
                            $li_class = '';
                            if ($is_selected) $li_class .= ' selected';
                            if ($is_completed) $li_class .= ' completed';
                        ?>
                        <li class="<?= trim($li_class) ?>">
                            <a href="?khoa=<?= $course['ma_khoa'] ?>">
                                <?= htmlspecialchars($course['ten_khoa']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>Bạn chưa đăng ký khóa học nào.</li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="report-container">
            <?php if ($selected_course_id): ?>
                <h2>Báo cáo quá trình học</h2>
                <div class="stat">
                    <strong>Tổng số video:</strong> <?= $row_video['tong_video'] ?><br>
                    <strong>Số video hoàn thành:</strong> <?= $row_video['hoan_thanh'] ?><br>
                    <strong>Tổng số bài tập:</strong> <?= $row_baitap['tong_bai'] ?><br>
                    <strong>Số bài tập đã làm:</strong> <?= $row_lambai['da_lam'] ?><br>
                    <strong>Điểm trung bình bài tập:</strong> <?= number_format($row_lambai['diem_tb'], 2) ?>
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
                                <td><?= htmlspecialchars($row['ten_video']) ?></td>
                                <td><?= htmlspecialchars($row['thoi_gian_xem']) ?></td>
                                <td><?= htmlspecialchars($row['so_phut_da_xem']) ?></td>
                                <td><?= $row['da_hoan_thanh_video'] ? '✔️' : '❌' ?></td>
                                <td><?= $row['lam_xong_bai_tap'] ? '✔️' : '❌' ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5">Chưa có dữ liệu lịch sử học.</td></tr>
                    <?php endif; ?>
                </table>
            <?php else: ?>
                <p>Bạn chưa đăng ký khóa học nào.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>