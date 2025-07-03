<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 3) {
    header("Location: ../login.php");
    exit;
}
include '../db.php';

// Lấy id buổi học từ URL (?id=...)
$ma_buoi = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ma_kh = intval($_SESSION['ma_kh']);

// Lấy đường dẫn video
$duong_dan_video = '';
$mo_ta_video = '';
if ($ma_buoi > 0) {
    $sql = "SELECT duong_dan_video, mo_ta FROM video_bai_giang WHERE ma_buoi = $ma_buoi LIMIT 1";
    $result = $mysqli->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $duong_dan_video = $row['duong_dan_video'];
        $mo_ta_video = $row['mo_ta'];
    }
}

// Xử lý nộp bài
$thong_bao = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dap_an']) && is_array($_POST['dap_an'])) {
    foreach ($_POST['dap_an'] as $ma_bai => $dap_an) {
        $ma_bai = intval($ma_bai);
        $dap_an = trim($dap_an);
        $diem = null;
        $trang_thai = 'Hoàn thành';

        // Kiểm tra đã tồn tại chưa
        $stmt_check = $mysqli->prepare("SELECT 1 FROM lam_bai_tap WHERE ma_bai = ? AND ma_kh = ?");
        $stmt_check->bind_param('ii', $ma_bai, $ma_kh);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Nếu đã tồn tại, cập nhật đáp án mới
            $stmt_update = $mysqli->prepare("UPDATE lam_bai_tap SET dap_an = ?, diem = ?, trang_thai = ?, thoi_gian_nop = NOW() WHERE ma_bai = ? AND ma_kh = ?");
            $stmt_update->bind_param('sissi', $dap_an, $diem, $trang_thai, $ma_bai, $ma_kh);
            $stmt_update->execute();
        } else {
            // Nếu chưa có, thêm mới
            $stmt_insert = $mysqli->prepare("INSERT INTO lam_bai_tap (ma_bai, ma_kh, dap_an, diem, trang_thai) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param('iisis', $ma_bai, $ma_kh, $dap_an, $diem, $trang_thai);
            $stmt_insert->execute();
        }
        $stmt_check->close();   
    }
    $thong_bao = "Nộp bài thành công!";
}

// Lấy danh sách bài tập của buổi học
$sql = "SELECT * FROM bai_tap WHERE ma_buoi = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $ma_buoi);
$stmt->execute();
$result = $stmt->get_result();

// Kiểm tra hoàn thành bài tập buổi học
$stmt_total = $mysqli->prepare("SELECT COUNT(*) FROM bai_tap WHERE ma_buoi = ?");
$stmt_total->bind_param('i', $ma_buoi);
$stmt_total->execute();
$stmt_total->bind_result($total_bai_tap);
$stmt_total->fetch();
$stmt_total->close();

$stmt_nop = $mysqli->prepare("SELECT COUNT(*) FROM lam_bai_tap WHERE ma_kh = ? AND ma_bai IN (SELECT ma_bai FROM bai_tap WHERE ma_buoi = ?)");
$stmt_nop->bind_param('ii', $ma_kh, $ma_buoi);
$stmt_nop->execute();
$stmt_nop->bind_result($total_nop);
$stmt_nop->fetch();
$stmt_nop->close();

$is_hoan_thanh = ($total_bai_tap > 0 && $total_bai_tap == $total_nop);

// Hàm lấy id youtube
function getYoutubeId($url) {
    if (preg_match('/youtu\.be\/([^\?&]+)/', $url, $matches)) return $matches[1];
    if (preg_match('/youtube\.com.*v=([^&]+)/', $url, $matches)) return $matches[1];
    return '';
}
$video_id = getYoutubeId($duong_dan_video);
$is_youtube = !empty($video_id);
$is_file = preg_match('/\.(mp4|webm|ogg)$/i', $duong_dan_video);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Video & Bài tập buổi học</title>
    <link rel="stylesheet" href="video.css">
    <link rel="stylesheet" href="home_work.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="main-flex">
        <div class="video-side">
            <h2>Video bài giảng</h2>
            <?php if ($duong_dan_video): ?>
                <?php if ($is_youtube): ?>
                    <iframe width="100%" height="420"
                        src="https://www.youtube.com/embed/<?php echo htmlspecialchars($video_id); ?>"
                        frameborder="0" allowfullscreen>
                    </iframe>
                <?php elseif ($is_file && file_exists($duong_dan_video)): ?>
                    <video width="100%" height="420" controls>
                        <source src="<?php echo htmlspecialchars($duong_dan_video); ?>">
                        Trình duyệt của bạn không hỗ trợ video.
                    </video>
                <?php else: ?>
                    <p>Không tìm thấy video hợp lệ.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>Không có video cho buổi học này.</p>
            <?php endif; ?>
            <?php if (!empty($mo_ta_video)): ?>
                <?php
                    $max_length = 180; // số ký tự tối đa hiển thị ban đầu
                    $is_long = mb_strlen($mo_ta_video, 'UTF-8') > $max_length;
                    $mo_ta_short = $is_long ? mb_substr($mo_ta_video, 0, $max_length, 'UTF-8') . '...' : $mo_ta_video;
                ?>
                <div class="video-description" style="margin-top:18px; color:#444;">
                    <strong>Mô tả video:</strong><br>
                    <span id="moTaShort" style="<?= $is_long ? '' : 'display:inline;' ?>">
                        <?php echo nl2br(htmlspecialchars($mo_ta_short)); ?>
                        <?php if ($is_long): ?>
                            <a href="javascript:void(0)" id="showMoreBtn" style="color:#1976d2;text-decoration:underline;font-weight:500;">Hiển thị thêm</a>
                        <?php endif; ?>
                    </span>
                    <?php if ($is_long): ?>
                        <span id="moTaFull" style="display:none;">
                            <?php echo nl2br(htmlspecialchars($mo_ta_video)); ?>
                            <a href="javascript:void(0)" id="showLessBtn" style="color:#1976d2;text-decoration:underline;font-weight:500;">Ẩn bớt</a>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if ($is_long): ?>
                <script>
                    const showMoreBtn = document.getElementById('showMoreBtn');
                    const showLessBtn = document.getElementById('showLessBtn');
                    const moTaShort = document.getElementById('moTaShort');
                    const moTaFull = document.getElementById('moTaFull');
                    showMoreBtn.onclick = function() {
                        moTaShort.style.display = 'none';
                        moTaFull.style.display = 'inline';
                    };
                    showLessBtn.onclick = function() {
                        moTaShort.style.display = 'inline';
                        moTaFull.style.display = 'none';
                    };
                </script>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="homework-side">
            <h2>Bài tập của buổi học</h2>
            <?php if (!empty($thong_bao)): ?>
                <div class="alert-message"><?php echo htmlspecialchars($thong_bao); ?></div>
            <?php endif; ?>
            <?php if ($result && $result->num_rows > 0): ?>
                <form method="post">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="homework-item">
                            <h3><?php echo htmlspecialchars($row['ten_bai']); ?></h3>
                            <div class="content"><?php echo nl2br(htmlspecialchars($row['noi_dung'])); ?></div>
                            <textarea name="dap_an[<?php echo $row['ma_bai']; ?>]" rows="3" style="width:100%;padding:7px;" placeholder="Nhập đáp án của bạn..." required></textarea>
                        </div>
                    <?php endwhile; ?>
                    <button type="submit" class="btn-submit">Nộp bài</button>
                </form>
            <?php else: ?>
                <p>Chưa có bài tập cho buổi học này.</p>
            <?php endif; ?>
            <?php if ($is_hoan_thanh): ?>
                <div class="alert-message" style="color:green;">Bạn đã hoàn thành tất cả bài tập của buổi học này!</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>