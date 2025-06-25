<?php
// Kết nối CSDL
$mysqli = new mysqli('localhost:3307', 'root', '', 'web_hoc_tieng_anh');

// Lấy id buổi học từ URL (?id=...)
$ma_buoi = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Truy vấn lấy link video từ bảng video_bai_giang
$duong_dan_video = '';
if ($ma_buoi > 0) {
    $sql = "SELECT duong_dan_video FROM video_bai_giang WHERE ma_buoi = $ma_buoi LIMIT 1";
    $result = $mysqli->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $duong_dan_video = $row['duong_dan_video'];
    }
}
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem video bài giảng</title>
    <link rel="stylesheet" href="video.css">
</head>
<body>
    <div class="video-container">
        <?php if ($duong_dan_video): ?>
            <p>Đường dẫn video: <?php echo htmlspecialchars($duong_dan_video); ?></p>
        <?php endif; ?>
    <?php
function getYoutubeId($url) {
    if (preg_match('/youtu\.be\/([^\?&]+)/', $url, $matches)) return $matches[1];
    if (preg_match('/youtube\.com.*v=([^&]+)/', $url, $matches)) return $matches[1];
    return '';
}

$video_id = getYoutubeId($duong_dan_video);
$is_youtube = !empty($video_id);
$is_file = preg_match('/\.(mp4|webm|ogg)$/i', $duong_dan_video);
?>

<?php if ($is_youtube): ?>
    <!-- Hiển thị video YouTube -->
    <iframe width="100%" height="420"
        src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video_id); ?>"
        frameborder="0" allowfullscreen>
    </iframe>
<?php elseif ($is_file && file_exists($duong_dan_video)): ?>
    <!-- Hiển thị video file trên máy chủ -->
    <video width="100%" height="420" controls>
        <source src="<?php echo htmlspecialchars($duong_dan_video); ?>">
        Trình duyệt của bạn không hỗ trợ video.
    </video>
<?php else: ?>
    <p>Không tìm thấy video hợp lệ.</p>
<?php endif; ?>
    <a href="javascript:history.back()" class="btn-back">&lt; Quay lại</a>
    <!-- Nút làm bài tập cho buổi học hiện tại -->
    <a href="home_work.php?ma_buoi=<?php echo urlencode($ma_buoi); ?>" class="btn-homework">Làm bài tập</a>
    </div>
</body>
</html>