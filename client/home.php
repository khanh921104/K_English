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
    <title>Trang chủ</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php
    include '../db.php';
    $sql = "SELECT ma_khoa, ten_khoa, mo_ta, cap_do, so_luong_dang_ky, gia FROM khoa_hoc";
    $result = $mysqli->query($sql);
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
            <h3>Danh sách khóa học</h3>
            <div class="course-list" id="course-list">
                <?php
                $maxVisible = 8;
                $index = 0;
                $courseHtml = '';
                if ($result && $result->num_rows > 0):
                    while($row = $result->fetch_assoc()):
                        $hiddenClass = ($index >= $maxVisible) ? 'hidden-course' : '';
                        $courseHtml .= '<div class="course-card ' . $hiddenClass . '">
                            <a class="course-content" href="course_detail.php?id=' . $row['ma_khoa'] . '" style="color:inherit;text-decoration:none;font-weight:inherit;font-size:inherit;">
                                <h4 class="course-title">' . htmlspecialchars($row['ten_khoa']) . '</h4>
                                <p class="course-desc">' . htmlspecialchars($row['mo_ta']) . '</p>
                                <p class="course-level"><strong>Cấp độ:</strong> ' . htmlspecialchars($row['cap_do']) . '</p>
                                <p class="course-registered"><strong>Số lượng đã đăng ký:</strong> ' . htmlspecialchars($row['so_luong_dang_ky']) . '</p>
                                <p class="course-price"><strong>Giá:</strong> ' . number_format($row['gia'], 0, ',', '.') . ' VNĐ</p>
                            </a>
                        </div>';
                        $index++;
                    endwhile;
                    echo $courseHtml;
                else:
                ?>
                    <p>Chưa có khóa học nào.</p>
                <?php endif; ?>
            </div>
            <?php if ($index > $maxVisible): ?>
                <button id="toggle-courses-btn" style="margin-top:15px;">v</button>
            <?php endif; ?>
        </div>

        <style>
        .hidden-course {
            display: none;
        }
        </style>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('toggle-courses-btn');
            if (!btn) return;
            let expanded = false;
            btn.addEventListener('click', function () {
                const hiddenCourses = document.querySelectorAll('.hidden-course');
                if (!expanded) {
                    hiddenCourses.forEach(el => el.style.display = 'block');
                    btn.textContent = '^';
                    expanded = true;
                } else {
                    hiddenCourses.forEach(el => el.style.display = 'none');
                    btn.textContent = 'v';
                    expanded = false;
                }
            });
        });
        </script>
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
