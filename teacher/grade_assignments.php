<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 2) {
    header("Location: ../login.php");
    exit;
}
include '../db.php';

$ma_kh = intval($_SESSION['ma_kh']);

// Lấy danh sách các khóa học mà giáo viên đã tạo
$sql = "SELECT k.ma_khoa, k.ten_khoa, k.cap_do, k.gia, gvk.ngay_tao
        FROM giao_vien_tao_khoa_hoc gvk
        JOIN khoa_hoc k ON gvk.ma_khoa = k.ma_khoa
        WHERE gvk.ma_kh = $ma_kh";

$result = $mysqli->query($sql);
$courses = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo quá trình học</title>
    <link rel="stylesheet" href="grade.css">
    
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <div class="container">
            <h2>Các khóa học bạn đã tạo</h2>
            <ul>
                <?php if (count($courses) > 0): ?>
                    <?php foreach ($courses as $course): ?>
                        <li><?= htmlspecialchars($course['ten_khoa']) ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>Bạn chưa tạo khóa học nào.</li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="detail">

        </div>
    </main>
    
</body>

</html>
