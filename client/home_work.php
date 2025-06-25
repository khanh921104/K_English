<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 3) {
    header("Location: ../login.php");
    exit;
}
include '../db.php';

$ma_kh = intval($_SESSION['ma_kh']);
$ma_buoi = isset($_GET['ma_buoi']) ? intval($_GET['ma_buoi']) : 0;

// Xử lý nộp bài
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ma_bai'], $_POST['dap_an'])) {
    $ma_bai = intval($_POST['ma_bai']);
    $dap_an = trim($_POST['dap_an']);
    // Kiểm tra đã nộp chưa
    $check = $mysqli->prepare("SELECT * FROM lam_bai_tap WHERE ma_bai=? AND ma_kh=?");
    $check->bind_param('ii', $ma_bai, $ma_kh);
    $check->execute();
    $check_result = $check->get_result();
    if ($check_result && $check_result->num_rows == 0) {
        $stmt_insert = $mysqli->prepare("INSERT INTO lam_bai_tap (ma_bai, ma_kh, dap_an, trang_thai) VALUES (?, ?, ?, 'Chưa hoàn thành')");
        $stmt_insert->bind_param('iis', $ma_bai, $ma_kh, $dap_an);
        $stmt_insert->execute();
        $thong_bao = "Nộp bài thành công!";
    } else {
        $thong_bao = "Bạn đã nộp bài này rồi.";
    }
}

// Lấy danh sách bài tập của buổi học
$sql = "SELECT * FROM bai_tap WHERE ma_buoi = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $ma_buoi);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Bài tập buổi học</title>
    <link rel="stylesheet" href="home_work.css">
</head>
<body>
    <div class="homework-container">
        <h2>Bài tập của buổi học</h2>
        <?php if (!empty($thong_bao)): ?>
            <div class="alert-message"><?php echo htmlspecialchars($thong_bao); ?></div>
        <?php endif; ?>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="homework-item">
                    <h3><?php echo htmlspecialchars($row['ten_bai']); ?></h3>
                    <div class="content"><?php echo nl2br(htmlspecialchars($row['noi_dung'])); ?></div>
                    <!-- Form nhập đáp án -->
                    <form method="post" style="margin-top:12px;">
                        <input type="hidden" name="ma_bai" value="<?php echo $row['ma_bai']; ?>">
                        <textarea name="dap_an" rows="3" style="width:100%;padding:7px;" placeholder="Nhập đáp án của bạn..." required></textarea>
                        <button type="submit" class="btn-submit">Nộp bài</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Chưa có bài tập cho buổi học này.</p>
        <?php endif; ?>
        <a href="javascript:history.back()" class="btn-back">⫷ Quay lại</a>
    </div>
</body>
</html>