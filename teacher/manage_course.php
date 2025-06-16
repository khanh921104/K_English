<?php
include '../db.php';

// Lấy mã khóa học từ URL
$ma_khoa = isset($_GET['ma_khoa']) ? intval($_GET['ma_khoa']) : 0;

// Lấy thông tin khóa học
$khoa_sql = "SELECT * FROM khoa_hoc WHERE ma_khoa = $ma_khoa";
$khoa_result = $mysqli->query($khoa_sql);
$khoa = $khoa_result ? $khoa_result->fetch_assoc() : null;

// Xử lý thêm buổi học
$show_form = isset($_POST['show_add_form']) || isset($_POST['add_session']);
$thong_bao = '';
if (isset($_POST['add_session'])) {
    $ten_buoi = trim($_POST['ten_buoi']);
    $noi_dung = trim($_POST['noi_dung']);
    $ngay_hoc = $_POST['ngay_hoc'];
    if ($ten_buoi && $ngay_hoc) {
        $stmt = $mysqli->prepare("INSERT INTO buoi_hoc (ten_buoi, noi_dung, ngay_hoc, ma_khoa) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssi', $ten_buoi, $noi_dung, $ngay_hoc, $ma_khoa);
        if ($stmt->execute()) {
            // Sau khi thêm thành công, reload lại trang để hiện danh sách mới
            header("Location: manage_course.php?ma_khoa=$ma_khoa");
            exit;
        } else {
            $thong_bao = "Lỗi khi thêm buổi học: " . $mysqli->error;
            $show_form = true;
        }
    } else {
        $thong_bao = "Vui lòng nhập đầy đủ thông tin!";
        $show_form = true;
    }
}

// Lấy danh sách buổi học
$sql = "SELECT * FROM buoi_hoc WHERE ma_khoa = $ma_khoa ORDER BY ngay_hoc ASC";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>
        Quản lý buổi học
        <?php echo $khoa ? ' - ' . htmlspecialchars($khoa['ten_khoa']) : ''; ?>
    </title>
    <link rel="stylesheet" href="manage_course.css">
    <link rel="stylesheet" href="manage_course1.css">

    
</head>
<body>
    <?php if ($khoa): ?>
        <h2>Quản lý buổi học khóa: <?php echo htmlspecialchars($khoa['ten_khoa']); ?></h2>
        <div class="add-session-container">
            <button type="button" class="btn-add" onclick="showAddSessionForm()">Thêm buổi học</button>
        </div>

        <!-- Overlay form (ẩn mặc định) -->
        <div id="addSessionOverlay" class="overlay" style="display:none;">
            <div class="overlay-content">
                <span class="close-btn" onclick="hideAddSessionForm()">&times;</span>
                <h3>Thêm buổi học mới</h3>
                <form method="post">
                    <label>Tên buổi học:</label>
                    <input type="text" name="ten_buoi" required>
                    <label>Nội dung:</label>
                    <textarea name="noi_dung" rows="3"></textarea>
                    <label>Ngày học:</label>
                    <input type="date" name="ngay_hoc" required>
                    <button type="submit" name="add_session" class="btn-add">Thêm</button>
                </form>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Ngày học</th>
                    <th>Chủ đề</th>
                    <th>Ghi chú</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): $i=1; ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($row['ngay_hoc']); ?></td>
                        <td><?php echo htmlspecialchars($row['ten_buoi'] ?? $row['chu_de'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['noi_dung'] ?? $row['ghi_chu'] ?? ''); ?></td>
                        <td>
                            <a href="edit_session.php?id=<?php echo $row['ma_buoi']; ?>&ma_khoa=<?php echo $ma_khoa; ?>" class="btn-edit">Sửa</a>
                            <a href="delete_session.php?id=<?php echo $row['ma_buoi']; ?>&ma_khoa=<?php echo $ma_khoa; ?>" class="btn-delete" onclick="return confirm('Bạn chắc chắn muốn xóa?')">Xóa</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">Chưa có buổi học nào.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    <?php else: ?>
        <h2>Không tìm thấy thông tin khóa học.</h2>
    <?php endif; ?>

    <script>
        function showAddSessionForm() {
            document.getElementById('addSessionOverlay').style.display = 'block';
        }

        function hideAddSessionForm() {
            document.getElementById('addSessionOverlay').style.display = 'none';
        }
    </script>
</body>
</html>