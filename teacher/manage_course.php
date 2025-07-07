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

// Xử lý cập nhật cấp độ và giá khóa học
if (isset($_POST['update_course_info'])) {
    $cap_do = trim($_POST['cap_do']);
    $gia = floatval($_POST['gia']);
    if ($cap_do && $gia >= 0) {
        $stmt = $mysqli->prepare("UPDATE khoa_hoc SET cap_do=?, gia=? WHERE ma_khoa=?");
        $stmt->bind_param('sdi', $cap_do, $gia, $ma_khoa);
        if ($stmt->execute()) {
            // Cập nhật lại thông tin khóa học sau khi sửa
            $khoa_sql = "SELECT * FROM khoa_hoc WHERE ma_khoa = $ma_khoa";
            $khoa_result = $mysqli->query($khoa_sql);
            $khoa = $khoa_result ? $khoa_result->fetch_assoc() : null;
            $thong_bao = "Cập nhật thành công!";
        } else {
            $thong_bao = "Lỗi khi cập nhật: " . $mysqli->error;
        }
    } else {
        $thong_bao = "Vui lòng nhập đầy đủ thông tin hợp lệ!";
    }
}

// Xử lý thêm buổi học và video bài giảng đầu tiên
$show_form = isset($_POST['show_add_form']) || isset($_POST['add_session']);
$thong_bao = '';
if (isset($_POST['add_session'])) {
    $ten_buoi = trim($_POST['ten_buoi']);
    $noi_dung = trim($_POST['noi_dung']);
    $ten_video = trim($_POST['ten_video']);
    $duong_dan_video = trim($_POST['duong_dan_video']);
    $mo_ta = trim($_POST['mo_ta']);
    $thoi_luong_video = intval($_POST['thoi_luong_video']);

    if ($ten_buoi && $ten_video && $duong_dan_video && $thoi_luong_video) {
        // Thêm buổi học
        $stmt = $mysqli->prepare("INSERT INTO buoi_hoc (ten_buoi, noi_dung, ma_khoa) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $ten_buoi, $noi_dung, $ma_khoa);
        if ($stmt->execute()) {
            $ma_buoi_moi = $stmt->insert_id;
            // Thêm video bài giảng đầu tiên cho buổi học này
            $stmt2 = $mysqli->prepare("INSERT INTO video_bai_giang (ten_video, duong_dan_video, mo_ta, thoi_luong, ma_buoi) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param('sssii', $ten_video, $duong_dan_video, $mo_ta, $thoi_luong_video, $ma_buoi_moi);
            $stmt2->execute();
            // Reload lại trang để hiện danh sách mới
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
// Lấy danh sách buổi học kèm video đầu tiên
$sql = "SELECT bh.*, vbg.ten_video, vbg.thoi_luong 
        FROM buoi_hoc bh
        LEFT JOIN video_bai_giang vbg ON bh.ma_buoi = vbg.ma_buoi
        WHERE bh.ma_khoa = $ma_khoa
        ORDER BY bh.ma_buoi ASC";
$result = $mysqli->query($sql);

// Xử lý tạo bài tập cho buổi học
if (isset($_POST['create_homework'])) {
    $ma_buoi_bai_tap = intval($_POST['ma_buoi_bai_tap']);
    $ten_bai = trim($_POST['ten_bai']);
    $noi_dung_bai_tap = trim($_POST['noi_dung_bai_tap']);
    if ($ma_buoi_bai_tap && $ten_bai && $noi_dung_bai_tap) {
        $stmt = $mysqli->prepare("INSERT INTO bai_tap (ten_bai, noi_dung, ma_buoi) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $ten_bai, $noi_dung_bai_tap, $ma_buoi_bai_tap);
        if ($stmt->execute()) {
            $thong_bao = "Tạo bài tập thành công!";
        } else {
            $thong_bao = "Lỗi khi tạo bài tập: " . $mysqli->error;
        }
    } else {
        $thong_bao = "Vui lòng nhập đầy đủ thông tin bài tập!";
    }
}
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
    <!-- <link rel="stylesheet" href="manage_course1.css">   -->
</head>
<body>
    <?php include 'header.php'; ?>
    <?php if ($khoa): ?>
    <div class="manage_course_container">
        <div class="manage_header">
            <a href="home.php" class="btn-back-home">
            &lt; 
            </a>
            <!-- <h2>Quản lý buổi học</h2> -->
            <h2><?php echo htmlspecialchars($khoa['ten_khoa']); ?></h2>
            <div class="add-session-container">
                <button type="button" class="btn-add" onclick="showAddSessionForm()">Thêm buổi học</button>
            </div>
            
            <div class="edit-course-info" style="max-width:420px;margin:18px 0 28px 0;padding:18px 24px;background:#f5f7fa;border-radius:10px;">
            <form method="post" style="display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
                <label style="font-weight:500;">Cấp độ:
                    <input type="text" name="cap_do" value="<?php echo htmlspecialchars($khoa['cap_do'] ?? ''); ?>" required style="margin-left:8px;padding:6px 10px;border-radius:6px;border:1px solid #90caf9;">
                </label>
                <label style="font-weight:500;">Giá:
                    <input type="number" name="gia" value="<?php echo htmlspecialchars($khoa['gia'] ?? ''); ?>" min="0" step="1000" required style="margin-left:8px;padding:6px 10px;border-radius:6px;border:1px solid #90caf9;">
                </label>
                <button type="submit" name="update_course_info" style="padding:8px 18px;border-radius:6px;background:#1976d2;color:#fff;border:none;font-weight:500;cursor:pointer;">Lưu</button>
            </form>
            </div>
        </div>

        <?php if (!empty($thong_bao)): ?>
            <div class="alert-message"><?php echo htmlspecialchars($thong_bao); ?></div>
            <?php endif; ?>
        <div class="manage_table_wrapper">
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
                            <td><?php echo htmlspecialchars($row['thoi_luong'] ?? ''); ?></td>
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
                                <a href="#" 
                                class="btn-create-homework"
                                data-ma_buoi="<?php echo $row['ma_buoi']; ?>"
                                onclick="showCreateHomeworkForm(this); return false;">Tạo bài tập</a>
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
        </div>

    


    

    <!-- hiden form  -->
<!-- Overlay form (ẩn mặc định) -->
        <div id="addSessionOverlay" class="overlay" style="display:none;">
            <div class="overlay-content">
                <span class="close-btn" onclick="hideAddSessionForm()">&times;</span>
                <h3>Thêm buổi học mới</h3>
                <form method="post">
                    <label>Tên buổi học:</label>
                    <input type="text" name="ten_buoi" required>
                    <label>Nội dung buổi học:</label>
                    <textarea name="noi_dung" rows="3"></textarea>
                    <hr>
                    <h4>Video bài giảng đầu tiên</h4>
                    <label>Tên video:</label>
                    <input type="text" name="ten_video" required>
                    <label>Đường dẫn video:</label>
                    <input type="text" name="duong_dan_video" required>
                    <label>Mô tả video:</label>
                    <textarea name="mo_ta" rows="2"></textarea>
                    <label>Thời lượng video (phút):</label>
                    <input type="number" name="thoi_luong_video" min="1" required>
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

        <!-- Overlay form tạo bài tập (ẩn mặc định) -->
        <div id="createHomeworkOverlay" class="overlay" style="display:none;">
            <div class="overlay-content">
                <span class="close-btn" onclick="hideCreateHomeworkForm()">&times;</span>
                <h3>Tạo bài tập cho buổi học</h3>
                <form method="post">
                    <input type="hidden" name="ma_buoi_bai_tap" id="ma_buoi_bai_tap">
                    <label>Tên bài tập:</label>
                    <input type="text" name="ten_bai" required>
                    <label>Nội dung bài tập:</label>
                    <textarea name="noi_dung_bai_tap" rows="3" required></textarea>
                    <button type="submit" name="create_homework" class="btn-add">Tạo bài tập</button>
                </form>
            </div>
        </div>
    </div>

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

        function showCreateHomeworkForm(el) {
            document.getElementById('ma_buoi_bai_tap').value = el.getAttribute('data-ma_buoi');
            document.getElementById('createHomeworkOverlay').style.display = 'block';
        }

        function hideCreateHomeworkForm() {
            document.getElementById('createHomeworkOverlay').style.display = 'none';
        }

        function showCreateHomeworkForm(el) {
            document.getElementById('ma_buoi_bai_tap').value = el.getAttribute('data-ma_buoi');
            document.getElementById('createHomeworkOverlay').style.display = 'block';
        }
        function hideCreateHomeworkForm() {
            document.getElementById('createHomeworkOverlay').style.display = 'none';
        }

        
    </script>
</body>
</html>