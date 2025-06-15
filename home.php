<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <header>
        <div class="stars"></div>
        <h1>K-English</h1>
        <nav>
            <ul>
                <li><a href="customers.php">Khách hàng</a></li>
                <li><a href="products.php">Khóa học</a></li>
                <li><a href="login.php">Tài khoản</a></li>
            </ul>
        </nav>
        
        
    </header>
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
        <p>Hãy chọn một mục từ menu để bắt đầu.</p>
    </main>
    <footer>
        <p>&copy; 2025 Lê Công Khánh. Tất cả quyền được bảo lưu.</p>
    </footer>
    <script>
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
</script>
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