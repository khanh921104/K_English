<link rel="stylesheet" href="header_style.css">
<!-- filepath: c:\xampp\htdocs\K_English\admin\header.php -->
<header>
        <div class="stars"></div>
        <h1><a href="http://localhost:8080/K_English/home.php">K-English</a></h1>
        <nav>
            <ul>
                
                <li><a href="search.php">Khóa học</a></li>
                <li><a href="client_information.php">Tài khoản</a></li>
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
    