<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 2) {
    // Nếu chưa đăng nhập hoặc không phải giáo viên, chuyển hướng về trang đăng nhập
    header("Location: ../login.php");
    exit;
}
$ma_kh = isset($_SESSION['ma_kh']) ? intval($_SESSION['ma_kh']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php
    include '../db.php';
    $sql = "SELECT ma_khoa, ten_khoa, mo_ta, cap_do, gia FROM khoa_hoc";
    $result = $mysqli->query($sql); // đổi từ $conn → $mysqli
    ?>


    <div class="banner">
        <div class="banner-slider">
            <img class="banner-img active" src="https://cf.shopee.vn/file/sg-11134258-7rasy-mav18v0lur1ybd_xhdpi" alt="Banner 1">
            <img class="banner-img" src="https://i.ytimg.com/vi/knW7-x7Y7RE/maxresdefault.jpg" alt="Banner 2">
            <img class="banner-img" src="https://i.ytimg.com/vi/FN7ALfpGxiI/maxresdefault.jpg" alt="Banner 3">
        </div>
    </div>

    <main>
        <h2>Chào mừng đến với trang chủ!</h2>
        <p>Đây là nơi bạn có thể quản lý khách hàng, sản phẩm và đơn hàng.</p>
     

        <div class="courses">
    <h3>Danh sách khóa học của bạn</h3>
    <div class="course-list">
        <?php
        // Lấy danh sách khóa học của giáo viên
        $sql = "SELECT kh.ma_khoa, kh.ten_khoa, kh.mo_ta, kh.cap_do, kh.gia
                FROM khoa_hoc kh
                INNER JOIN giao_vien_tao_khoa_hoc gvkh ON kh.ma_khoa = gvkh.ma_khoa
                WHERE gvkh.ma_kh = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $ma_kh);
        $stmt->execute();
        $result = $stmt->get_result();
        ?>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="course-card">
                    <a class="course-content" href="manage_course.php?ma_khoa=<?php echo $row['ma_khoa']; ?>" style="color:inherit;text-decoration:none;font-weight:inherit;font-size:inherit;">
                        <h4 class="course-title"><?php echo htmlspecialchars($row['ten_khoa']); ?></h4>
                        <p class="course-desc"><?php echo htmlspecialchars($row['mo_ta']); ?></p>
                        <p class="course-level"><strong>Cấp độ:</strong> <?php echo htmlspecialchars($row['cap_do']); ?></p>
                        <p class="course-price"><strong>Giá:</strong> <?php echo number_format($row['gia'], 0, ',', '.'); ?> VNĐ</p>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Bạn chưa có khóa học nào.</p>
        <?php endif; ?>
    </div>
</div>

        
    </main>

    <footer>
        <p>&copy; 2025 Lê Công Khánh. Tất cả quyền được bảo lưu.</p>
    </footer>

    <!-- Slider JavaScript -->
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const images = document.querySelectorAll('.banner-img');
    let current = 0;

    function showSlide(next) {
        images.forEach((img, idx) => {
            img.classList.remove('active', 'prev');
            if (idx === current) img.classList.add('prev');
        });
        images[next].classList.add('active');
        current = next;
    }

    setInterval(() => {
        let next = (current + 1) % images.length;
        showSlide(next);
    }, 3500);
});
</script>
</body>
</html>
