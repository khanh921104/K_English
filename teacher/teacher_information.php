<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 2) {
    // Nếu chưa đăng nhập hoặc không phải giáo viên, chuyển hướng về trang đăng nhập
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="teacher_information.css">
</head>
<body>
    <?php 
    include '../db.php';
    include 'header.php';
    $ma_kh = isset($_SESSION['ma_kh']) ? intval($_SESSION['ma_kh']) : 0;
    $sql = "SELECT * FROM giao_vien WHERE ma_gv = (SELECT ma_gv FROM tai_khoan WHERE ma_kh = $ma_kh LIMIT 1)";
    $result = $mysqli->query($sql);
    $gv = $result ? $result->fetch_assoc() : null;
    ?>
    <main>
        <?php if ($gv): ?>
            <div class="tab">
                <a>Thông tin</a>
                <a>Thống kê</a>
            </div>
            <div class="teacher-info">
                <div class="details">
                    <span class="label">Tên giáo viên:</span>
                    <h2><?php echo htmlspecialchars($gv['ho_ten']); ?></h2>
                </div>
                <div class="details">
                    <span class="label">Email:</span>
                    <span class="value"><?php echo htmlspecialchars($gv['email']); ?></span>
                </div>
                <div class="details">
                    <span class="label">Số điện thoại:</span>
                    <span class="value"><?php echo htmlspecialchars($gv['so_dien_thoai']); ?></span>
                </div>
                <div class="details">
                    <span class="label">Chuyên môn:</span>
                    <span class="value"><?php echo htmlspecialchars($gv['chuyen_mon']); ?></span>
                </div>
            </div>
        <?php else: ?>
            <p style="text-align:center;color:red;">Không tìm thấy thông tin giáo viên.</p>
        <?php endif; ?>
    </main>
    <a href="../login.php" class="btn-logout">Đăng xuất</a>
</body>
</html>