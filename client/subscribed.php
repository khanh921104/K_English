<?php
// subscribed.php

session_start();
$user_id = $_SESSION['ma_kh'] ?? null;

// Kiểm tra quyền truy cập: chỉ cho phép client (ma_quyen = 3)
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 3) {
    header("Location: ../login.php");
    exit;
}

if (!$user_id) {
    header('Location: /login.php');
    exit;
}

require_once '../db.php';

// Lấy danh sách khóa học đang học
$sql = "SELECT kh.ma_khoa, kh.ten_khoa
        FROM dang_ky dk
        JOIN khoa_hoc kh ON dk.ma_khoa = kh.ma_khoa
        WHERE dk.ma_kh = ? AND dk.trang_thai = 'enrolled'";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách khóa học đã học xong
$sql2 = "SELECT kh.ma_khoa, kh.ten_khoa
         FROM dang_ky dk
         JOIN khoa_hoc kh ON dk.ma_khoa = kh.ma_khoa
         WHERE dk.ma_kh = ? AND dk.trang_thai = 'completed'";
$stmt2 = $mysqli->prepare($sql2);
$stmt2->bind_param('i', $user_id);
$stmt2->execute();
$completed_courses = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Khóa học của tôi</title>
    <link rel="stylesheet" href="subscribed.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <h1>Khóa học của tôi</h1>

    <div class="course-list">
        <h2>Đang học</h2>
        <ul>
            <?php if ($enrolled_courses): ?>
                <?php foreach ($enrolled_courses as $course): ?>
                    <?php
                    // Đếm tổng số buổi của khóa học
                    $sql_total = "SELECT COUNT(*) AS tong_buoi FROM buoi_hoc WHERE ma_khoa = ?";
                    $stmt_total = $mysqli->prepare($sql_total);
                    $stmt_total->bind_param('i', $course['ma_khoa']);
                    $stmt_total->execute();
                    $tong_buoi = $stmt_total->get_result()->fetch_assoc()['tong_buoi'] ?? 0;

                    // Đếm số buổi đã học (giả sử đã học là có dòng trong lich_su_hoc)
                    // Đếm số buổi đã học dựa trên bài tập đã làm và hoàn thành
                    $sql_done = "SELECT COUNT(DISTINCT bt.ma_buoi) AS da_hoc
                                FROM bai_tap bt
                                JOIN lam_bai_tap lbt ON bt.ma_bai = lbt.ma_bai
                                JOIN buoi_hoc bh ON bt.ma_buoi = bh.ma_buoi
                                WHERE lbt.ma_kh = ? AND lbt.trang_thai = 'Hoàn thành' AND bh.ma_khoa = ?";
                    $stmt_done = $mysqli->prepare($sql_done);
                    $stmt_done->bind_param('ii', $user_id, $course['ma_khoa']);
                    $stmt_done->execute();
                    $da_hoc = $stmt_done->get_result()->fetch_assoc()['da_hoc'] ?? 0;

                    ?>
                    <li>
                        <a href="course_detail.php?id=<?= htmlspecialchars($course['ma_khoa']) ?>">
                            <?= htmlspecialchars($course['ten_khoa']) ?>
                        </a>
                        <span style=" font-size:0.95em;">
                            (Đã học <?= $da_hoc ?>/<?= $tong_buoi ?> buổi)
                        </span>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Bạn chưa đăng ký khóa học nào.</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="course-list">
        <h2>Đã học xong</h2>
        <ul>
            <?php if ($completed_courses): ?>
                <?php foreach ($completed_courses as $course): ?>
                    <li>
                        <a href="/client/course.php?id=<?= htmlspecialchars($course['ma_khoa']) ?>">
                            <?= htmlspecialchars($course['ten_khoa']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Bạn chưa hoàn thành khóa học nào.</li>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>