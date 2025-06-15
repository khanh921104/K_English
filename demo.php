<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Banner Slider</title>
    <style>
        .banner {
            width: 100%;
            max-width: 900px;
            height: 320px;
            overflow: hidden;
            position: relative;
            margin: 50px auto;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .banner-slider {
            position: relative;
            width: 100%;
            height: 100%;
        }
        .banner-img {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            z-index: 1;
            transform: translateX(100%);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        .banner-img.active {
            opacity: 1;
            z-index: 2;
            transform: translateX(0);
        }
        .banner-img.prev {
            opacity: 0;
            z-index: 1;
            transform: translateX(-100%);
        }
    </style>
</head>
<body>
    <div class="banner">
        <div class="banner-slider">
            <img class="banner-img active" src="https://picsum.photos/id/1015/900/320" alt="Banner 1">
            <img class="banner-img" src="https://picsum.photos/id/1016/900/320" alt="Banner 2">
            <img class="banner-img" src="https://picsum.photos/id/1018/900/320" alt="Banner 3">
        </div>
    </div>

    <script>
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
    </script>
</body>
</html>
