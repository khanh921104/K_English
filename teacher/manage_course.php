<?php
include '../db.php';

session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 2) {
    header("Location: ../login.php");
    exit;
}

// Lấy mã khách hàng (giáo viên) từ session
$ma_kh = isset($_SESSION['ma_kh']) ? intval($_SESSION['ma_kh']) : 0;
if ($ma_kh === 0) {
    die("Lỗi: Không tìm thấy mã giáo viên trong session!");
}

// Lấy mã khóa học từ URL
$ma_khoa = isset($_GET['ma_khoa']) ? intval($_GET['ma_khoa']) : 0;

// Kiểm tra kết nối cơ sở dữ liệu
if (!isset($mysqli) || $mysqli->connect_error) {
    die("Lỗi kết nối cơ sở dữ liệu: " . ($mysqli ? $mysqli->connect_error : "Không thể tải db.php"));
}

// Lấy thông tin khóa học và kiểm tra quyền sở hữu
$khoa = null;
$thong_bao = '';
if ($ma_khoa > 0) {
    $khoa_sql = "SELECT kh.* FROM khoa_hoc kh
                 JOIN giao_vien_tao_khoa_hoc gtkh ON kh.ma_khoa = gtkh.ma_khoa
                 WHERE kh.ma_khoa = ? AND gtkh.ma_kh = ?";
    $stmt = $mysqli->prepare($khoa_sql);
    if (!$stmt) {
        die("Lỗi chuẩn bị truy vấn: " . $mysqli->error);
    }
    $stmt->bind_param('ii', $ma_khoa, $ma_kh);
    $stmt->execute();
    $khoa_result = $stmt->get_result();
    $khoa = $khoa_result->fetch_assoc();
    $stmt->close();
    
    if (!$khoa) {
        $thong_bao = "Không tìm thấy khóa học hoặc bạn không có quyền truy cập!";
    }
}

// Xử lý cập nhật cấp độ và giá khóa học
if (isset($_POST['update_course_info']) && $khoa) {
    $cap_do = trim($_POST['cap_do']);
    $gia = floatval($_POST['gia']);
    if ($cap_do && in_array($cap_do, ['Sơ cấp', 'Trung cấp', 'Cao cấp']) && $gia >= 0) {
        $stmt = $mysqli->prepare("UPDATE khoa_hoc SET cap_do = ?, gia = ? WHERE ma_khoa = ?");
        $stmt->bind_param('sdi', $cap_do, $gia, $ma_khoa);
        if ($stmt->execute()) {
            $stmt2 = $mysqli->prepare("SELECT kh.* FROM khoa_hoc kh
                                      JOIN giao_vien_tao_khoa_hoc gtkh ON kh.ma_khoa = gtkh.ma_khoa
                                      WHERE kh.ma_khoa = ? AND gtkh.ma_kh = ?");
            $stmt2->bind_param('ii', $ma_khoa, $ma_kh);
            $stmt2->execute();
            $khoa_result = $stmt2->get_result();
            $khoa = $khoa_result->fetch_assoc();
            $stmt2->close();
            $thong_bao = "Cập nhật thông tin khóa học thành công!";
        } else {
            $thong_bao = "Lỗi khi cập nhật: " . $mysqli->error;
        }
        $stmt->close();
    } else {
        $thong_bao = "Vui lòng nhập cấp độ hợp lệ (Sơ cấp, Trung cấp, Cao cấp) và giá không âm!";
    }
}

// Xử lý xóa khóa học
if (isset($_GET['delete_course']) && $khoa) {
    if ($khoa['so_luong_dang_ky'] > 0) {
        $thong_bao = "Không thể xóa khóa học vì đã có học viên đăng ký!";
    } else {
        $stmt = $mysqli->prepare("DELETE FROM khoa_hoc WHERE ma_khoa = ?");
        $stmt->bind_param('i', $ma_khoa);
        if ($stmt->execute()) {
            header("Location: home.php");
            exit;
        } else {
            $thong_bao = "Lỗi khi xóa khóa học: " . $mysqli->error;
        }
        $stmt->close();
    }
}

// Xử lý thêm buổi học và video bài giảng đầu tiên
$show_form = isset($_POST['show_add_form']) || isset($_POST['add_session']);
if (isset($_POST['add_session']) && $khoa) {
    $ten_buoi = trim($_POST['ten_buoi']);
    $noi_dung = trim($_POST['noi_dung']);
    $ten_video = trim($_POST['ten_video']);
    $duong_dan_video = trim($_POST['duong_dan_video']);
    $mo_ta = trim($_POST['mo_ta']);

    if ($ten_buoi && $ten_video && $duong_dan_video) {
        $stmt = $mysqli->prepare("INSERT INTO buoi_hoc (ten_buoi, noi_dung, ma_khoa) VALUES (?, ?, ?)");
        $stmt->bind_param('ssi', $ten_buoi, $noi_dung, $ma_khoa);
        if ($stmt->execute()) {
            $ma_buoi_moi = $stmt->insert_id;
            $stmt2 = $mysqli->prepare("INSERT INTO video_bai_giang (ten_video, duong_dan_video, mo_ta, ma_buoi) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param('sssi', $ten_video, $duong_dan_video, $mo_ta, $ma_buoi_moi);
            $stmt2->execute();
            $stmt2->close();
            header("Location: manage_course.php?ma_khoa=$ma_khoa");
            exit;
        } else {
            $thong_bao = "Lỗi khi thêm buổi học: " . $mysqli->error;
            $show_form = true;
        }
        $stmt->close();
    } else {
        $thong_bao = "Vui lòng nhập đầy đủ thông tin (tên buổi, tên video, đường dẫn video)!";
        $show_form = true;
    }
}

// Xử lý sửa buổi học và link video
if (isset($_POST['edit_session']) && $khoa) {
    $ma_buoi = intval($_POST['edit_ma_buoi']);
    $ten_buoi = trim($_POST['edit_ten_buoi']);
    $noi_dung = trim($_POST['edit_noi_dung']);
    $duong_dan_video = trim($_POST['edit_duong_dan_video']);
    if ($ten_buoi && $ma_buoi) {
        $stmt = $mysqli->prepare("UPDATE buoi_hoc SET ten_buoi = ?, noi_dung = ? WHERE ma_buoi = ? AND ma_khoa = ?");
        $stmt->bind_param('ssii', $ten_buoi, $noi_dung, $ma_buoi, $ma_khoa);
        if ($stmt->execute()) {
            if ($duong_dan_video) {
                $stmt_video = $mysqli->prepare("SELECT ma_video FROM video_bai_giang WHERE ma_buoi = ? LIMIT 1");
                $stmt_video->bind_param('i', $ma_buoi);
                $stmt_video->execute();
                $result_video = $stmt_video->get_result();
                if ($row_video = $result_video->fetch_assoc()) {
                    $ma_video = $row_video['ma_video'];
                    $stmt_update_video = $mysqli->prepare("UPDATE video_bai_giang SET duong_dan_video = ? WHERE ma_video = ?");
                    $stmt_update_video->bind_param('si', $duong_dan_video, $ma_video);
                    $stmt_update_video->execute();
                    $stmt_update_video->close();
                }
                $stmt_video->close();
            }
            header("Location: manage_course.php?ma_khoa=$ma_khoa");
            exit;
        } else {
            $thong_bao = "Lỗi khi cập nhật buổi học: " . $mysqli->error;
        }
        $stmt->close();
    } else {
        $thong_bao = "Vui lòng nhập đầy đủ thông tin (tên buổi)!";
    }
}

// Xử lý xóa buổi học
if (isset($_GET['delete_buoi']) && $khoa) {
    $ma_buoi = intval($_GET['delete_buoi']);
    $stmt_delete_bai_tap = $mysqli->prepare("DELETE FROM bai_tap WHERE ma_buoi = ?");
    $stmt_delete_buoi = $mysqli->prepare("DELETE FROM buoi_hoc WHERE ma_buoi = ? AND ma_khoa = ?");
    
    $stmt_delete_bai_tap->bind_param('i', $ma_buoi);
    $stmt_delete_bai_tap->execute();
    $stmt_delete_bai_tap->close();

    $stmt_delete_buoi->bind_param('ii', $ma_buoi, $ma_khoa);
    if ($stmt_delete_buoi->execute()) {
        header("Location: manage_course.php?ma_khoa=$ma_khoa");
        exit;
    } else {
        $thong_bao = "Lỗi khi xóa buổi học: " . $mysqli->error;
    }
    $stmt_delete_buoi->close();
}

// Lấy danh sách buổi học kèm video đầu tiên
$sql = "SELECT bh.ma_buoi, bh.ten_buoi, bh.noi_dung, vbg.duong_dan_video 
        FROM buoi_hoc bh
        LEFT JOIN video_bai_giang vbg ON bh.ma_buoi = vbg.ma_buoi
        WHERE bh.ma_khoa = ?
        ORDER BY bh.ma_buoi ASC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $ma_khoa);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý khóa học<?php echo $khoa ? ' - ' . htmlspecialchars($khoa['ten_khoa']) : ''; ?></title>
    <link rel="stylesheet" href="manage_course.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php if ($khoa): ?>
    <div class="manage_course_container">
        <div class="manage_header">
            <a href="home.php" class="btn-back-home"><</a>
            <h2><?php echo htmlspecialchars($khoa['ten_khoa']); ?></h2>
            <div class="add-session-container">
                <button type="button" class="btn-add" onclick="showAddSessionForm()">Thêm buổi học</button>
            </div>
            <div class="edit-course-info" style="max-width:420px;margin:18px 0 28px 0;padding:18px 24px;background:#f5f7fa;border-radius:10px;">
                <form method="post" style="display:flex;gap:18px;align-items:center;flex-wrap:wrap;">
                    <label style="font-weight:500;">Cấp độ:
                        <select name="cap_do" required style="margin-left:8px;padding:6px 10px;border-radius:6px;border:1px solid #90caf9;">
                            <option value="Sơ cấp" <?php echo $khoa['cap_do'] == 'Sơ cấp' ? 'selected' : ''; ?>>Sơ cấp</option>
                            <option value="Trung cấp" <?php echo $khoa['cap_do'] == 'Trung cấp' ? 'selected' : ''; ?>>Trung cấp</option>
                            <option value="Cao cấp" <?php echo $khoa['cap_do'] == 'Cao cấp' ? 'selected' : ''; ?>>Cao cấp</option>
                        </select>
                    </label>
                    <label style="font-weight:500;">Giá:
                        <input type="number" name="gia" value="<?php echo htmlspecialchars($khoa['gia'] ?? ''); ?>" min="0" step="1000" required style="margin-left:8px;padding:6px 10px;border-radius:6px;border:1px solid #90caf9;">
                    </label>
                    <button type="submit" name="update_course_info" style="padding:8px 18px;border-radius:6px;background:#1976d2;color:#fff;border:none;font-weight:500;cursor:pointer;">Lưu</button>
                </form>
            </div>
            <div class="delete-course-container">
                <a href="?ma_khoa=<?php echo $ma_khoa; ?>&delete_course=1" class="btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa khóa học này và tất cả dữ liệu liên quan?');">Xóa khóa học</a>
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
                        <th>Link Video</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): $i = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['ten_buoi'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['noi_dung'] ?? ''); ?></td>
                            <td>
                                <?php if ($row['duong_dan_video']): ?>
                                    <a href="<?php echo htmlspecialchars($row['duong_dan_video']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($row['duong_dan_video']); ?>
                                    </a>
                                <?php else: ?>
                                    Chưa có link
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="#" class="btn-edit"
                                   data-ma_buoi="<?php echo $row['ma_buoi']; ?>"
                                   data-ten_buoi="<?php echo htmlspecialchars($row['ten_buoi']); ?>"
                                   data-noi_dung="<?php echo htmlspecialchars($row['noi_dung']); ?>"
                                   data-duong_dan_video="<?php echo htmlspecialchars($row['duong_dan_video'] ?? ''); ?>"
                                   onclick="showEditSessionForm(this); return false;">Sửa</a>
                                <a href="?ma_khoa=<?php echo $ma_khoa; ?>&delete_buoi=<?php echo $row['ma_buoi']; ?>"
                                   class="btn-delete"
                                   onclick="return confirm('Bạn chắc chắn muốn xóa buổi học này?');">Xóa</a>
                                <a href="manage_assignment.php?ma_buoi=<?php echo $row['ma_buoi']; ?>" 
                                   class="btn-create-homework"
                                   data-ma_buoi="<?php echo $row['ma_buoi']; ?>">Bài tập</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">Chưa có buổi học nào.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Overlay form thêm buổi học -->
        <div id="addSessionOverlay" class="overlay" style="display:<?php echo $show_form ? 'block' : 'none'; ?>;">
            <div class="overlay-content">
                <span class="close-btn" onclick="hideAddSessionForm()">×</span>
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
                    <button type="submit" name="add_session" class="btn-add">Thêm</button>
                </form>
            </div>
        </div>

        <!-- Overlay form sửa buổi học -->
        <div id="editSessionOverlay" class="overlay" style="display:none;">
            <div class="overlay-content">
                <span class="close-btn" onclick="hideEditSessionForm()">×</span>
                <h3>Sửa buổi học</h3>
                <form method="post" id="editSessionForm">
                    <input type="hidden" name="edit_ma_buoi" id="edit_ma_buoi">
                    <label>Tên buổi học:</label>
                    <input type="text" name="edit_ten_buoi" id="edit_ten_buoi" required>
                    <label>Nội dung:</label>
                    <textarea name="edit_noi_dung" id="edit_noi_dung" rows="3"></textarea>
                    <label>Đường dẫn video:</label>
                    <input type="text" name="edit_duong_dan_video" id="edit_duong_dan_video" required>
                    <button type="submit" name="edit_session" class="btn-add">Cập nhật</button>
                </form>
            </div>
        </div>
    </div>
    <?php else: ?>
        <h2><?php echo htmlspecialchars($thong_bao); ?></h2>
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
            document.getElementById('edit_duong_dan_video').value = el.getAttribute('data-duong_dan_video');
            document.getElementById('editSessionOverlay').style.display = 'block';
        }

        function hideEditSessionForm() {
            document.getElementById('editSessionOverlay').style.display = 'none';
        }
    </script>
</body>
</html>