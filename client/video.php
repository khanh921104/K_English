<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 3) {
    header("Location: ../login.php");
    exit;
}
include '../db.php';

$ma_buoi = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ma_kh = intval($_SESSION['ma_kh']);

$duong_dan_video = '';
$mo_ta_video = '';
if ($ma_buoi > 0) {
    $sql = "SELECT duong_dan_video, mo_ta FROM video_bai_giang WHERE ma_buoi = ? LIMIT 1";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $ma_buoi);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $duong_dan_video = $row['duong_dan_video'];
        $mo_ta_video = $row['mo_ta'];
    }
    $stmt->close();
}

$thong_bao = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dap_an']) && is_array($_POST['dap_an'])) {
    $valid_options = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
    foreach ($_POST['dap_an'] as $ma_bai => $dap_an) {
        $ma_bai = intval($ma_bai);
        if (is_array($dap_an)) {
            $dap_an = array_filter($dap_an, function($opt) use ($valid_options) {
                return in_array($opt, $valid_options);
            });
            sort($dap_an);
            $dap_an = implode(',', $dap_an);
        }
        $dap_an = trim($dap_an);

        $diem = null;
        $trang_thai = 'Hoàn thành';

        $stmt_loai = $mysqli->prepare("SELECT loai_bai FROM bai_tap WHERE ma_bai = ?");
        $stmt_loai->bind_param("i", $ma_bai);
        $stmt_loai->execute();
        $stmt_loai->bind_result($loai_bai);
        $stmt_loai->fetch();
        $stmt_loai->close();

        if ($loai_bai === 'trac_nghiem') {
            $stmt_dapan = $mysqli->prepare("SELECT dap_an_dung FROM trac_nghiem WHERE ma_bai = ?");
            $stmt_dapan->bind_param("i", $ma_bai);
            $stmt_dapan->execute();
            $stmt_dapan->bind_result($dap_an_dung);
            $stmt_dapan->fetch();
            $stmt_dapan->close();

            $dap_an_user = explode(',', $dap_an);
            sort($dap_an_user);
            $dap_an_user_str = implode(',', $dap_an_user);

            $dap_an_dung_arr = explode(',', $dap_an_dung);
            sort($dap_an_dung_arr);
            $dap_an_dung_str = implode(',', $dap_an_dung_arr);

            $diem = ($dap_an_user_str === $dap_an_dung_str) ? 10 : 0;
        }

        $stmt_check = $mysqli->prepare("SELECT 1 FROM lam_bai_tap WHERE ma_bai = ? AND ma_kh = ?");
        $stmt_check->bind_param('ii', $ma_bai, $ma_kh);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $stmt_update = $mysqli->prepare("UPDATE lam_bai_tap SET dap_an = ?, diem = ?, trang_thai = ?, thoi_gian_nop = NOW() WHERE ma_bai = ? AND ma_kh = ?");
            $stmt_update->bind_param('sissi', $dap_an, $diem, $trang_thai, $ma_bai, $ma_kh);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            $stmt_insert = $mysqli->prepare("INSERT INTO lam_bai_tap (ma_bai, ma_kh, dap_an, diem, trang_thai) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param('iisis', $ma_bai, $ma_kh, $dap_an, $diem, $trang_thai);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
    $thong_bao = "Nộp bài thành công!";
}

$bai_tap_list = [];
$sql = "SELECT * FROM bai_tap WHERE ma_buoi = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $ma_buoi);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $ma_bai = $row['ma_bai'];
    $loai_bai = $row['loai_bai'];
    $bai = null;

    if ($loai_bai === 'tu_luan') {
        $stmt_tl = $mysqli->prepare("SELECT ma_cau_tu_luan, ten_bai, noi_dung FROM tu_luan WHERE ma_bai = ?");
        $stmt_tl->bind_param("i", $ma_bai);
        $stmt_tl->execute();
        $result_tl = $stmt_tl->get_result();

        while ($bai = $result_tl->fetch_assoc()) {
            $bai_tap_list[] = [
                'ma_cau' => $bai['ma_cau_tu_luan'],
                'ma_bai' => $ma_bai,
                'loai_bai' => $loai_bai,
                'ten_bai' => $bai['ten_bai'],
                'noi_dung' => $bai['noi_dung']
            ];
        }
        $stmt_tl->close();
    } elseif ($loai_bai === 'trac_nghiem') {
        $stmt_tn = $mysqli->prepare("SELECT ma_cau_trac_nghiem, ten_bai, noi_dung, noi_dung_a, noi_dung_b, noi_dung_c, noi_dung_d, noi_dung_e, noi_dung_f, noi_dung_g, noi_dung_h, noi_dung_i, noi_dung_j, dap_an_dung FROM trac_nghiem WHERE ma_bai = ?");
        $stmt_tn->bind_param("i", $ma_bai);
        $stmt_tn->execute();
        $result_tn = $stmt_tn->get_result();

        while ($bai = $result_tn->fetch_assoc()) {
            $bai_tap_list[] = [
                'ma_cau' => $bai['ma_cau_trac_nghiem'],
                'ma_bai' => $ma_bai,
                'loai_bai' => $loai_bai,
                'ten_bai' => $bai['ten_bai'],
                'noi_dung' => $bai['noi_dung'],
                'dap_an' => $bai
            ];
        }
        $stmt_tn->close();
    }
}
$stmt->close();

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

function getYoutubeId($url) {
    if (preg_match('/youtu\.be\/([^\?&]+)/', $url, $matches)) return $matches[1];
    if (preg_match('/youtube\.com.*v=([^&]+)/', $url, $matches)) return $matches[1];
    return '';
}

$video_id = getYoutubeId($duong_dan_video);
$is_youtube = !empty($video_id);
$is_file = preg_match('/\.(mp4|webm|ogg)$/i', $duong_dan_video);

//thong bao cham bai 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dap_an']) && is_array($_POST['dap_an'])) {
    // Lấy ma_khoa từ ma_buoi
    $stmt_khoa = $mysqli->prepare("SELECT ma_khoa FROM buoi_hoc WHERE ma_buoi = ?");
    $stmt_khoa->bind_param("i", $ma_buoi);
    $stmt_khoa->execute();
    $stmt_khoa->bind_result($ma_khoa);
    $stmt_khoa->fetch();
    $stmt_khoa->close();

    if (!$ma_khoa) {
        echo "❌ Không tìm thấy khóa học của buổi học.";
        exit;
    }

    // Tìm giáo viên chủ nhiệm của khóa học
    $stmt_gv = $mysqli->prepare("SELECT ma_kh FROM giao_vien_tao_khoa_hoc WHERE ma_khoa = ?");
    $stmt_gv->bind_param('i', $ma_khoa);
    $stmt_gv->execute();
    $stmt_gv->bind_result($ma_gv);
    $stmt_gv->fetch();
    $stmt_gv->close();

    if (!$ma_gv) {
        echo "❌ Không tìm thấy giáo viên quản lý khóa học.";
        exit;
    }


    if (empty($ma_gv)) {
        exit('Không tìm thấy giáo viên quản lý khóa học.');
    }

    foreach ($_POST['dap_an'] as $ma_bai => $dap_an) {
        $ma_bai = intval($ma_bai);
        $dap_an = trim($dap_an);

        // Kiểm tra bài tập là tự luận
        $stmt = $mysqli->prepare("SELECT loai_bai FROM bai_tap WHERE ma_bai = ?");
        $stmt->bind_param("i", $ma_bai);
        $stmt->execute();
        $stmt->bind_result($loai_bai);
        $stmt->fetch();
        $stmt->close();

        if ($loai_bai === 'tu_luan' && $dap_an !== '') {
            // Gửi thông báo
            $noi_dung = "Học viên ID $ma_kh vừa nộp bài tự luận mã bài $ma_bai thuộc buổi học $ma_buoi.";
            $loai_tb = 'cham_bai';
            $trang_thai = 'chưa đọc';

            $stmt_tb = $mysqli->prepare("INSERT INTO thong_bao (ma_nguoi_gui, ma_nguoi_nhan, ma_khoa, ma_buoi, loai, noi_dung, trang_thai) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_tb->bind_param("iiiisss", $ma_kh, $ma_gv, $ma_khoa, $ma_buoi, $loai_tb, $noi_dung, $trang_thai);
            $stmt_tb->execute();
            $stmt_tb->close();
        }
    }

    echo "✅ Đã nộp bài và gửi thông báo chấm bài tự luận.";
}
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
                            frameborder="0" allowfullscreen></iframe>
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
                    $max_length = 180;
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
                        <script>
                            const showMoreBtn = document.getElementById('showMoreBtn');
                            const showLessBtn = document.getElementById('showLessBtn');
                            const moTaShort = document.getElementById('moTaShort');
                            const moTaFull = document.getElementById('moTaFull');
                            showMoreBtn.onclick = function () {
                                moTaShort.style.display = 'none';
                                moTaFull.style.display = 'inline';
                            };
                            showLessBtn.onclick = function () {
                                moTaShort.style.display = 'inline';
                                moTaFull.style.display = 'none';
                            };
                        </script>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="homework-side">
            <h2>Bài tập của buổi học</h2>

            <?php if (!empty($thong_bao)): ?>
                <div class="alert-message"><?php echo htmlspecialchars($thong_bao); ?></div>
            <?php endif; ?>

            <?php if (!empty($bai_tap_list)): ?>
                <form method="post" onsubmit="return validateForm()">
                    <?php foreach ($bai_tap_list as $bai): ?>
                        <div class="homework-item">
                            <h3><?php echo htmlspecialchars($bai['ten_bai']); ?></h3>
                            <div class="content"><?php echo nl2br(htmlspecialchars($bai['noi_dung'])); ?></div>

                            <?php if ($bai['loai_bai'] === 'tu_luan'): ?>
                                <textarea name="dap_an[<?php echo $bai['ma_bai']; ?>]" rows="4" style="width:100%;padding:7px;" placeholder="Nhập đáp án của bạn..."></textarea>

                            <?php elseif ($bai['loai_bai'] === 'trac_nghiem'): ?>
                                <?php foreach (range('A', 'J') as $opt):
                                    $field = 'noi_dung_' . strtolower($opt);
                                    if (!empty($bai['dap_an'][$field])): ?>
                                        <label>
                                            <input type="checkbox" name="dap_an[<?php echo $bai['ma_bai']; ?>][]" value="<?php echo $opt; ?>">
                                            <?php echo "$opt. " . htmlspecialchars($bai['dap_an'][$field]); ?>
                                        </label><br>
                                    <?php endif;
                                endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
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

    <script>
        function validateForm() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]:checked');
            const textareas = document.querySelectorAll('textarea');
            let valid = false;
            textareas.forEach(textarea => {
                if (textarea.value.trim()) valid = true;
            });
            if (checkboxes.length > 0) valid = true;
            if (!valid) {
                alert('Vui lòng chọn ít nhất một đáp án hoặc nhập đáp án tự luận!');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>