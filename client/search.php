<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 3) {
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
    <link rel="stylesheet" href="search.css">
</head>
<body>
    <?php
    include '../db.php';

    $search = isset($_GET['q']) ? trim($_GET['q']) : '';

    if ($search !== '') {
        // Nếu có tìm kiếm, hiển thị tất cả kết quả phù hợp
        $sql = "SELECT * FROM khoa_hoc WHERE ten_khoa LIKE '%" . $mysqli->real_escape_string($search) . "%'";
    } else {
        // Nếu không tìm kiếm, chỉ lấy 3 khóa học đầu tiên
        $sql = "SELECT * FROM khoa_hoc LIMIT 3";
    }

    $result = $mysqli->query($sql);
    ?>

    <!-- Thanh tìm kiếm -->
    <form class="search-bar" method="get" action="">
        <a href="home.php" class="btn-back">⫷</a>
        
        <input type="text" name="q" placeholder="Tìm kiếm khóa học..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Tìm</button>
    </form>

    <!-- Danh sách thẻ khóa học -->
    <div class="course-list">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="course-card">
                    <a class="course-content" href="course_detail.php?id=<?php echo $row['ma_khoa']; ?>" style="color:inherit;text-decoration:none;font-weight:inherit;font-size:inherit;">
                        <h4 class="course-title"><?php echo htmlspecialchars($row['ten_khoa']); ?></h4>
                        <p class="course-desc"><?php echo htmlspecialchars($row['mo_ta']); ?></p>
                        <p class="course-level"><strong>Cấp độ:</strong> <?php echo htmlspecialchars($row['cap_do']); ?></p>
                        <p class="course-price"><strong>Giá:</strong> <?php echo number_format($row['gia'], 0, ',', '.'); ?> VNĐ</p>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Không tìm thấy khóa học phù hợp.</p>
        <?php endif; ?>
    </div>
</body>
</html>