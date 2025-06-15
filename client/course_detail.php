<?php
include '../db.php';

// Lấy id khóa học từ URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Truy vấn thông tin khóa học
$sql = "SELECT * FROM khoa_hoc WHERE ma_khoa = $id";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết khóa học</title>
    <link rel="stylesheet" href="course_detail.css">
    <link rel="stylesheet" href="course_detail1.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="course-layout">
                <div class="course-detail">
                    <h2 class="course-title"><?php echo htmlspecialchars($row['ten_khoa']); ?></h2>
                    <p class="course-desc"><?php echo nl2br(htmlspecialchars($row['mo_ta'])); ?></p>
                    <p class="course-level"><strong>Cấp độ:</strong> <?php echo htmlspecialchars($row['cap_do']); ?></p>
                    <p class="course-price"><strong>Giá:</strong> <?php echo number_format($row['gia'], 0, ',', '.'); ?> VNĐ</p>
                    <!-- Nếu có ảnh khóa học -->
                    <?php if (!empty($row['hinh_anh'])): ?>
                        <img src="<?php echo htmlspecialchars($row['hinh_anh']); ?>" alt="Ảnh khóa học" class="course-image">
                    <?php endif; ?>
                    <a href="home.php" class="btn-back">Quay lại danh sách</a>
                </div>
                <div class="session-list">
                    <h3>Danh sách buổi học</h3>
                    <?php
                    // Lấy danh sách buổi học của khóa này
                    $sql_sessions = "SELECT * FROM buoi_hoc WHERE ma_khoa = " . intval($row['ma_khoa']) . " ORDER BY ngay_hoc ASC";
                    $result_sessions = $mysqli->query($sql_sessions);
                    if ($result_sessions && $result_sessions->num_rows > 0):
                    ?>
                        <div class="sessions-flex">
                            <?php while ($session = $result_sessions->fetch_assoc()): ?>
                                <div class="session-item">
                                    <span class="session-title"><?php echo htmlspecialchars($session['ten_buoi']); ?></span>
                                    <span class="session-content"><?php echo nl2br(htmlspecialchars($session['noi_dung'])); ?></span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>Chưa có buổi học nào cho khóa này.</p>
                    <?php endif; ?>
                </div>
                
                
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Không tìm thấy khóa học phù hợp.</p>
    <?php endif; ?>
</body>
</html>