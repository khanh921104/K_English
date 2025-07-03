<?php
include '../db.php';


session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 3) {
    // Nếu chưa đăng nhập hoặc không phải giáo viên, chuyển hướng về trang đăng nhập
    header("Location: ../login.php");
    exit;
}


// Lấy id khóa học từ URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ma_kh = isset($_SESSION['ma_kh']) ? intval($_SESSION['ma_kh']) : 0;

// Truy vấn thông tin khóa học
$sql = "SELECT * FROM khoa_hoc WHERE ma_khoa = $id";
$result = $mysqli->query($sql);

if (isset($_POST['dang_ky']) && $ma_kh && $id) {
    // Kiểm tra đã đăng ký chưa
    $check = $mysqli->query("SELECT * FROM dang_ky WHERE ma_khoa = $id AND ma_kh = $ma_kh");
    if ($check && $check->num_rows == 0) {
        $stmt = $mysqli->prepare("INSERT INTO dang_ky (ma_khoa, ma_kh, ngay_dang_ky) VALUES (?, ?, NOW())");
        $stmt->bind_param('ii', $id, $ma_kh);
        if ($stmt->execute()) {
            $thong_bao = "Đăng ký thành công!";
        } else {
            $thong_bao = "Lỗi đăng ký: " . $mysqli->error;
        }
    } else {
        $thong_bao = "Bạn đã đăng ký khóa học này!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết khóa học</title>
    <link rel="stylesheet" href="course_detail.css">
    
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
                    <p class="course-registered"><strong>Số lượng đã đăng ký:</strong> <?php echo htmlspecialchars($row['so_luong_dang_ky']); ?></p>
                    <!-- Nếu có ảnh khóa học -->
                    <?php if (!empty($row['hinh_anh'])): ?>
                        <img src="<?php echo htmlspecialchars($row['hinh_anh']); ?>" alt="Ảnh khóa học" class="course-image">
                    <?php endif; ?>
                    <?php
                // Kiểm tra đã đăng ký chưa
                $da_dang_ky = false;
                if ($ma_kh && $id) {
                    $check = $mysqli->query("SELECT * FROM dang_ky WHERE ma_khoa = $id AND ma_kh = $ma_kh");
                    if ($check && $check->num_rows > 0) {
                        $da_dang_ky = true;
                    }
                }
                // Kiểm tra đã hoàn thành khóa học chưa
                $completed =  false;
                if($ma_kh && $id) {
                    $check = $mysqli->query("SELECT trang_thai FROM dang_ky WHERE ma_khoa = $id AND ma_kh = $ma_kh AND trang_thai = 'completed'");
                    if ($check && $check->num_rows > 0) {
                        $completed = true;
                    }
                }

                ?>
                <div class="course-actions">
                    <a href="home.php" class="btn-back">⫷</a>

                    <?php if (!$da_dang_ky): ?>
                    <!-- Nếu chưa đăng ký -->
                    <form method="post">
                        <button type="submit" name="dang_ky" class="btn-enroll">Đăng ký khóa học</button>
                    </form>
                
                    <?php elseif ($completed): ?>
                        <!-- Nếu đã hoàn thành khóa học -->
                        <span class="btn_completed">✅ Đã hoàn thành khóa</span>
                    
                    <?php else: ?>
                        <!-- Nếu đã đăng ký nhưng chưa hoàn thành -->
                        <a href="thi_ket_thuc.php?ma_khoa=<?= $id ?>" class="btn-test-end">Thi kết thúc khóa</a>
                    <?php endif; ?>
                </div>

                </div>
                <div class="session-list">
                    <h3>Danh sách buổi học</h3>
                    <?php
                    // Lấy danh sách buổi học của khóa này
                    $sql_sessions = "SELECT * FROM buoi_hoc WHERE ma_khoa = " . intval($row['ma_khoa']) . " ORDER BY thoi_luong ASC";
                    $result_sessions = $mysqli->query($sql_sessions);
                    if ($result_sessions && $result_sessions->num_rows > 0):
                    ?>
                        <div class="sessions-flex">
                            <?php while ($session = $result_sessions->fetch_assoc()): ?>
                                <div class="session-item">
                                    <a href="video.php?id=<?php echo urlencode($session['ma_buoi']); ?>" class="session-title">
                                        <?php echo htmlspecialchars($session['ten_buoi']); ?>
                                    </a>
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