<?php
if (!isset($mysqli)) {
    include '../db.php'; // hoặc chỉnh lại đường dẫn đúng nếu folder khác
}

$ma_gv = $_SESSION['ma_kh'];

$count_tb = 0;
$stmt_tb = $mysqli->prepare("SELECT COUNT(*) FROM thong_bao WHERE ma_nguoi_nhan = ? AND trang_thai = 'chưa đọc' AND loai = 'cham_bai'");
$stmt_tb->bind_param("i", $ma_gv);
$stmt_tb->execute();
$stmt_tb->bind_result($count_tb);
$stmt_tb->fetch();
$stmt_tb->close();
?>


<link rel="stylesheet" href="header_style.css">
<!-- filepath: c:\xampp\htdocs\K_English\admin\header.php -->
<header>
        <div class="stars"></div>
        <h1><a href="home.php">K-English</a></h1>
        <nav>
            <ul>
                <li style="position: relative; display: inline-block;">
                    <a href="grade_assignments.php" style="position: relative; display: inline-block;">
                        Chấm điểm
                        <?php if ($count_tb > 0): ?>
                            <span style="
                                position: absolute;
                                top: -5px;
                                right: -10px;
                                width: 10px;
                                height: 10px;
                                background-color: red;
                                border-radius: 50%;
                                display: inline-block;">
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="add_course.php">Thêm khóa học</a></li>
                <li><a href="teacher_information.php">Tài khoản</a></li>
            </ul>
        </nav>
        
        
    </header>
    <!-- <script>
const starsContainer = document.querySelector('.stars');
const numStars = 60;
const stars = [];

for (let i = 0; i < numStars; i++) {
    const star = document.createElement('div');
    star.className = 'star';
    star.style.top = Math.random() * 100 + '%';
    star.style.left = Math.random() * 100 + '%';
    star.style.width = star.style.height = (Math.random() * 2 + 1) + 'px';
    star.style.opacity = Math.random() * 0.7 + 0.3;
    starsContainer.appendChild(star);
    stars.push({
        el: star,
        speed: Math.random() * 0.2 + 0.05
    });
}

function animateStars() {
    for (let s of stars) {
        let top = parseFloat(s.el.style.top);
        top += s.speed;
        if (top > 100) top = 0;
        s.el.style.top = top + '%';
    }
    requestAnimationFrame(animateStars);
}
animateStars();
</script> -->
    