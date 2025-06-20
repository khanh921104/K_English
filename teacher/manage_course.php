<?php
include '../db.php';

session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 2) {
    // Nếu chưa đăng nhập hoặc không phải giáo viên, chuyển hướng về trang đăng nhập
    header("Location: ../login.php");
    exit;
}

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
    $thoi_luong = intval($_POST['thoi_luong']);
    if ($ten_buoi && $thoi_luong) {
        $stmt = $mysqli->prepare("INSERT INTO buoi_hoc (ten_buoi, noi_dung, thoi_luong, ma_khoa) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssii', $ten_buoi, $noi_dung, $thoi_luong, $ma_khoa);
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

// Xử lý sửa buổi học
if (isset($_POST['edit_session'])) {
    $ma_buoi = intval($_POST['edit_ma_buoi']);
    $ten_buoi = trim($_POST['edit_ten_buoi']);
    $noi_dung = trim($_POST['edit_noi_dung']);
    $thoi_luong = intval($_POST['edit_thoi_luong']);
    if ($ten_buoi && $thoi_luong && $ma_buoi) {
        $stmt = $mysqli->prepare("UPDATE buoi_hoc SET ten_buoi=?, noi_dung=?, thoi_luong=? WHERE ma_buoi=?");
        $stmt->bind_param('ssii', $ten_buoi, $noi_dung, $thoi_luong, $ma_buoi);
        if ($stmt->execute()) {
            header("Location: manage_course.php?ma_khoa=$ma_khoa");
            exit;
        } else {
            $thong_bao = "Lỗi khi cập nhật buổi học: " . $mysqli->error;
        }
    } else {
        $thong_bao = "Vui lòng nhập đầy đủ thông tin!";
    }
}

// Xử lý xóa buổi học

if (isset($_GET['delete_buoi'])) {
    $ma_buoi = intval($_GET['delete_buoi']);
    $stmt = $mysqli->prepare("DELETE FROM buoi_hoc WHERE ma_buoi = ?");
    $stmt->bind_param('i', $ma_buoi);
    $stmt->execute();
    // Sau khi xóa, reload lại trang để cập nhật danh sách
    header("Location: manage_course.php?ma_khoa=$ma_khoa");
    exit;
}
// Lấy danh sách buổi học
$sql = "SELECT * FROM buoi_hoc WHERE ma_khoa = $ma_khoa ORDER BY ma_buoi ASC";
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
        <div class="manage_header">
            <a href="home.php" class="btn-back-home">
            &lt; 
            </a>
            <h1>Quản lý buổi học</h1>
            <h1>K_English</h1>
            
        </div>
            <h2><?php echo htmlspecialchars($khoa['ten_khoa']); ?></h2>
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
                    <label>Thời lượng (phút):</label>
                    <input type="number" name="thoi_luong" min="1" required>
                    <button type="submit" name="add_session" class="btn-add">Thêm</button>
                </form>
            </div>
        </div>

        <!-- Overlay form sửa buổi học (ẩn mặc định) -->
        <div id="editSessionOverlay" class="overlay" style="display:none;">
            <div class="overlay-content">
                <span class="close-btn" onclick="hideEditSessionForm()">&times;</span>
                <h3>Sửa buổi học</h3>
                <form method="post" id="editSessionForm">
                    <input type="hidden" name="edit_ma_buoi" id="edit_ma_buoi">
                    <label>Tên buổi học:</label>
                    <input type="text" name="edit_ten_buoi" id="edit_ten_buoi" required>
                    <label>Nội dung:</label>
                    <textarea name="edit_noi_dung" id="edit_noi_dung" rows="3"></textarea>
                    <label>Thời lượng (phút):</label>
                    <input type="number" name="edit_thoi_luong" id="edit_thoi_luong" min="1" required>
                    <button type="submit" name="edit_session" class="btn-add">Cập nhật</button>
                </form>
            </div>
        </div>

        
        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Chủ đề</th>
                    <th>Ghi chú</th>
                    <th>Thời lượng</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): $i=1; ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo htmlspecialchars($row['ten_buoi'] ?? $row['chu_de'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['noi_dung'] ?? $row['ghi_chu'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['thoi_luong']); ?></td>
                        <td>
                            <a href="#" 
                               class="btn-edit"
                               data-ma_buoi="<?php echo $row['ma_buoi']; ?>"
                               data-ten_buoi="<?php echo htmlspecialchars($row['ten_buoi']); ?>"
                               data-noi_dung="<?php echo htmlspecialchars($row['noi_dung']); ?>"
                               data-thoi_luong="<?php echo htmlspecialchars($row['thoi_luong']); ?>"
                               onclick="showEditSessionForm(this); return false;">Sửa</a>
                            <a href="?ma_khoa=<?php echo $ma_khoa; ?>&delete_buoi=<?php echo $row['ma_buoi']; ?>"
                               class="btn-delete"
                               onclick="return confirm('Bạn chắc chắn muốn xóa buổi học này?');">
                               Xóa
                            </a>
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

        function showEditSessionForm(el) {
            document.getElementById('edit_ma_buoi').value = el.getAttribute('data-ma_buoi');
            document.getElementById('edit_ten_buoi').value = el.getAttribute('data-ten_buoi');
            document.getElementById('edit_noi_dung').value = el.getAttribute('data-noi_dung');
            document.getElementById('edit_thoi_luong').value = el.getAttribute('data-thoi_luong');
            document.getElementById('editSessionOverlay').style.display = 'block';
        }

        function hideEditSessionForm() {
            document.getElementById('editSessionOverlay').style.display = 'none';
        }
    </script>
</body>
</html>