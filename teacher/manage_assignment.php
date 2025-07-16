<?php
include '../db.php';

session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 2) {
    header("Location: ../login.php");
    exit;
}

// Kiểm tra kết nối cơ sở dữ liệu
if (!isset($mysqli) || $mysqli->connect_error) {
    die("Lỗi kết nối cơ sở dữ liệu: " . ($mysqli ? $mysqli->connect_error : "Không thể tải db.php"));
}

// Lấy mã buổi học từ URL
$ma_buoi = isset($_GET['ma_buoi']) ? intval($_GET['ma_buoi']) : 0;

// Lấy thông tin buổi học
$buoi_sql = "SELECT * FROM buoi_hoc WHERE ma_buoi = ?";
$stmt = $mysqli->prepare($buoi_sql);
$stmt->bind_param('i', $ma_buoi);
$stmt->execute();
$buoi_result = $stmt->get_result();
$buoi = $buoi_result ? $buoi_result->fetch_assoc() : null;

// Xử lý thêm bài tập
$show_form = isset($_POST['show_add_form']) || isset($_POST['add_bai_tap']);
$thong_bao = '';
if (isset($_POST['add_bai_tap'])) {
    $type = $_POST['type'];
    $ten_bai = trim($_POST['ten_bai']);
    $noi_dung = trim($_POST['noi_dung']);
    
    if ($ten_bai && $noi_dung && $ma_buoi && in_array($type, ['tu_luan', 'trac_nghiem'])) {
        // Thêm vào bảng bai_tap
        $stmt = $mysqli->prepare("INSERT INTO bai_tap (ma_buoi, loai_bai) VALUES (?, ?)");
        $stmt->bind_param('is', $ma_buoi, $type);
        if ($stmt->execute()) {
            $ma_bai = $mysqli->insert_id;
            
            if ($type == 'tu_luan') {
                $huong_dan_cham = trim($_POST['huong_dan_cham'] ?? '');
                $stmt2 = $mysqli->prepare("INSERT INTO tu_luan (ma_bai, ten_bai, noi_dung, huong_dan_cham) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param('isss', $ma_bai, $ten_bai, $noi_dung, $huong_dan_cham);
                $stmt2->execute();
                $stmt2->close();
            } elseif ($type == 'trac_nghiem') {
                $noi_dung_a = trim($_POST['noi_dung_a'] ?? '');
                $noi_dung_b = trim($_POST['noi_dung_b'] ?? '');
                $noi_dung_c = trim($_POST['noi_dung_c'] ?? '');
                $noi_dung_d = trim($_POST['noi_dung_d'] ?? '');
                $noi_dung_e = trim($_POST['noi_dung_e'] ?? '');
                $noi_dung_f = trim($_POST['noi_dung_f'] ?? '');
                $noi_dung_g = trim($_POST['noi_dung_g'] ?? '');
                $noi_dung_h = trim($_POST['noi_dung_h'] ?? '');
                $noi_dung_i = trim($_POST['noi_dung_i'] ?? '');
                $noi_dung_j = trim($_POST['noi_dung_j'] ?? '');
                $dap_an_dung = !empty($_POST['dap_an_dung']) ? $_POST['dap_an_dung'] : [];

                // Kiểm tra ít nhất một phương án được nhập
                $options = [$noi_dung_a, $noi_dung_b, $noi_dung_c, $noi_dung_d, $noi_dung_e, $noi_dung_f, $noi_dung_g, $noi_dung_h, $noi_dung_i, $noi_dung_j];
                $has_option = array_filter($options, function($opt) { return !empty($opt); });
                $valid_options = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                $option_map = array_combine($valid_options, $options);

                // Kiểm tra đáp án đúng hợp lệ
                $valid_dap_an = array_filter($dap_an_dung, function($opt) use ($option_map) {
                    return in_array($opt, array_keys(array_filter($option_map, function($val) { return !empty($val); })));
                });

                if (!empty($has_option) && !empty($valid_dap_an)) {
                    $dap_an_dung_str = implode(',', $valid_dap_an);
                    $stmt2 = $mysqli->prepare("INSERT INTO trac_nghiem (ma_bai, ten_bai, noi_dung, noi_dung_a, noi_dung_b, noi_dung_c, noi_dung_d, noi_dung_e, noi_dung_f, noi_dung_g, noi_dung_h, noi_dung_i, noi_dung_j, dap_an_dung) 
                                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt2->bind_param('isssssssssssss', $ma_bai, $ten_bai, $noi_dung, $noi_dung_a, $noi_dung_b, $noi_dung_c, $noi_dung_d, $noi_dung_e, $noi_dung_f, $noi_dung_g, $noi_dung_h, $noi_dung_i, $noi_dung_j, $dap_an_dung_str);
                    $stmt2->execute();
                    $stmt2->close();
                } else {
                    $thong_bao = "Vui lòng nhập ít nhất một phương án và chọn đáp án đúng hợp lệ!";
                    $show_form = true;
                }
            }
            if (!$thong_bao) {
                header("Location: manage_assignment.php?ma_buoi=$ma_buoi");
                exit;
            }
        } else {
            $thong_bao = "Lỗi khi thêm bài tập: " . $mysqli->error;
            $show_form = true;
        }
        $stmt->close();
    } else {
        $thong_bao = "Vui lòng nhập đầy đủ thông tin!";
        $show_form = true;
    }
}

// Xử lý sửa bài tập
if (isset($_POST['edit_bai_tap'])) {
    $ma_bai = intval($_POST['edit_ma_bai']);
    $type = $_POST['edit_type'];
    $ten_bai = trim($_POST['edit_ten_bai']);
    $noi_dung = trim($_POST['edit_noi_dung']);
    
    if ($ten_bai && $noi_dung && $ma_bai && in_array($type, ['tu_luan', 'trac_nghiem'])) {
        // Cập nhật bảng bai_tap
        $stmt = $mysqli->prepare("UPDATE bai_tap SET loai_bai = ? WHERE ma_bai = ?");
        $stmt->bind_param('si', $type, $ma_bai);
        if ($stmt->execute()) {
            // Xóa bản ghi cũ trong tu_luan hoặc trac_nghiem để đảm bảo đồng bộ
            $mysqli->query("DELETE FROM tu_luan WHERE ma_bai = $ma_bai");
            $mysqli->query("DELETE FROM trac_nghiem WHERE ma_bai = $ma_bai");
            
            if ($type == 'tu_luan') {
                $huong_dan_cham = trim($_POST['edit_huong_dan_cham'] ?? '');
                $stmt2 = $mysqli->prepare("INSERT INTO tu_luan (ma_bai, ten_bai, noi_dung, huong_dan_cham) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param('isss', $ma_bai, $ten_bai, $noi_dung, $huong_dan_cham);
                $stmt2->execute();
                $stmt2->close();
            } elseif ($type == 'trac_nghiem') {
                $noi_dung_a = trim($_POST['edit_noi_dung_a'] ?? '');
                $noi_dung_b = trim($_POST['edit_noi_dung_b'] ?? '');
                $noi_dung_c = trim($_POST['edit_noi_dung_c'] ?? '');
                $noi_dung_d = trim($_POST['edit_noi_dung_d'] ?? '');
                $noi_dung_e = trim($_POST['edit_noi_dung_e'] ?? '');
                $noi_dung_f = trim($_POST['edit_noi_dung_f'] ?? '');
                $noi_dung_g = trim($_POST['edit_noi_dung_g'] ?? '');
                $noi_dung_h = trim($_POST['edit_noi_dung_h'] ?? '');
                $noi_dung_i = trim($_POST['edit_noi_dung_i'] ?? '');
                $noi_dung_j = trim($_POST['edit_noi_dung_j'] ?? '');
                $dap_an_dung = !empty($_POST['edit_dap_an_dung']) ? $_POST['edit_dap_an_dung'] : [];

                // Kiểm tra ít nhất một phương án được nhập
                $options = [$noi_dung_a, $noi_dung_b, $noi_dung_c, $noi_dung_d, $noi_dung_e, $noi_dung_f, $noi_dung_g, $noi_dung_h, $noi_dung_i, $noi_dung_j];
                $has_option = array_filter($options, function($opt) { return !empty($opt); });
                $valid_options = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                $option_map = array_combine($valid_options, $options);

                // Kiểm tra đáp án đúng hợp lệ
                $valid_dap_an = array_filter($dap_an_dung, function($opt) use ($option_map) {
                    return in_array($opt, array_keys(array_filter($option_map, function($val) { return !empty($val); })));
                });

                if (!empty($has_option) && !empty($valid_dap_an)) {
                    $dap_an_dung_str = implode(',', $valid_dap_an);
                    $stmt2 = $mysqli->prepare("INSERT INTO trac_nghiem (ma_bai, ten_bai, noi_dung, noi_dung_a, noi_dung_b, noi_dung_c, noi_dung_d, noi_dung_e, noi_dung_f, noi_dung_g, noi_dung_h, noi_dung_i, noi_dung_j, dap_an_dung) 
                                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt2->bind_param('isssssssssssss', $ma_bai, $ten_bai, $noi_dung, $noi_dung_a, $noi_dung_b, $noi_dung_c, $noi_dung_d, $noi_dung_e, $noi_dung_f, $noi_dung_g, $noi_dung_h, $noi_dung_i, $noi_dung_j, $dap_an_dung_str);
                    $stmt2->execute();
                    $stmt2->close();
                } else {
                    $thong_bao = "Vui lòng nhập ít nhất một phương án và chọn đáp án đúng hợp lệ!";
                }
            }
            if (!$thong_bao) {
                header("Location: manage_assignment.php?ma_buoi=$ma_buoi");
                exit;
            }
        } else {
            $thong_bao = "Lỗi khi cập nhật bài tập: " . $mysqli->error;
        }
        $stmt->close();
    } else {
        $thong_bao = "Vui lòng nhập đầy đủ thông tin!";
    }
}

// Xử lý xóa bài tập
if (isset($_GET['delete_bai'])) {
    $ma_bai = intval($_GET['delete_bai']);
    $stmt = $mysqli->prepare("DELETE FROM bai_tap WHERE ma_bai = ?");
    $stmt->bind_param('i', $ma_bai);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_assignment.php?ma_buoi=$ma_buoi");
    exit;
}

// Lấy danh sách bài tập
$sql = "SELECT bt.ma_bai, bt.loai_bai, 
               tl.ten_bai, tl.noi_dung, tl.huong_dan_cham, 
               tn.ten_bai AS tn_ten_bai, tn.noi_dung AS tn_noi_dung, tn.noi_dung_a, tn.noi_dung_b, tn.noi_dung_c, tn.noi_dung_d, 
               tn.noi_dung_e, tn.noi_dung_f, tn.noi_dung_g, tn.noi_dung_h, tn.noi_dung_i, tn.noi_dung_j, tn.dap_an_dung 
        FROM bai_tap bt 
        LEFT JOIN tu_luan tl ON bt.ma_bai = tl.ma_bai 
        LEFT JOIN trac_nghiem tn ON bt.ma_bai = tn.ma_bai 
        WHERE bt.ma_buoi = ? 
        ORDER BY bt.ma_bai ASC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $ma_buoi);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Bài Tập<?php echo $buoi ? ' - ' . htmlspecialchars($buoi['ten_buoi']) : ''; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="manage_assignment.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php if ($buoi): ?>
    <div class="manage_course_container">
        <div class="manage_header">
            <a href="manage_course.php?ma_khoa=<?php echo $buoi['ma_khoa']; ?>" class="btn-back-home"><</a>
            <h2><?php echo htmlspecialchars($buoi['ten_buoi']); ?></h2>
            <div class="add-session-container">
                <button type="button" class="btn-add" onclick="showAddForm()">Thêm bài tập</button>
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
                        <th>Tên bài</th>
                        <th>Nội dung</th>
                        <th>Loại</th>
                        <th>Chi tiết</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): $i = 1; ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($row['ten_bai'] ?? $row['tn_ten_bai'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['noi_dung'] ?? $row['tn_noi_dung'] ?? ''); ?></td>
                            <td><?php echo $row['loai_bai'] == 'tu_luan' ? 'Tự luận' : 'Trắc nghiệm'; ?></td>
                            <td>
                                <?php if ($row['loai_bai'] == 'tu_luan'): ?>
                                    Hướng dẫn chấm: <?php echo htmlspecialchars($row['huong_dan_cham'] ?? ''); ?>
                                <?php else: ?>
                                    <?php foreach (['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'] as $opt): ?>
                                        <?php if (!empty($row["noi_dung_$opt"])): ?>
                                            <?php echo strtoupper($opt); ?>: <?php echo htmlspecialchars($row["noi_dung_$opt"]); ?><br>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    <strong><span style="color:green;">Đáp án: <?php echo htmlspecialchars($row['dap_an_dung'] ?? ''); ?></span></strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="#" class="btn-edit" 
                                   data-ma_bai="<?php echo $row['ma_bai']; ?>"
                                   data-ten_bai="<?php echo htmlspecialchars($row['ten_bai'] ?? $row['tn_ten_bai'] ?? ''); ?>"
                                   data-noi_dung="<?php echo htmlspecialchars($row['noi_dung'] ?? $row['tn_noi_dung'] ?? ''); ?>"
                                   data-type="<?php echo $row['loai_bai']; ?>"
                                   data-huong_dan_cham="<?php echo htmlspecialchars($row['huong_dan_cham'] ?? ''); ?>"
                                   data-noi_dung_a="<?php echo htmlspecialchars($row['noi_dung_a'] ?? ''); ?>"
                                   data-noi_dung_b="<?php echo htmlspecialchars($row['noi_dung_b'] ?? ''); ?>"
                                   data-noi_dung_c="<?php echo htmlspecialchars($row['noi_dung_c'] ?? ''); ?>"
                                   data-noi_dung_d="<?php echo htmlspecialchars($row['noi_dung_d'] ?? ''); ?>"
                                   data-noi_dung_e="<?php echo htmlspecialchars($row['noi_dung_e'] ?? ''); ?>"
                                   data-noi_dung_f="<?php echo htmlspecialchars($row['noi_dung_f'] ?? ''); ?>"
                                   data-noi_dung_g="<?php echo htmlspecialchars($row['noi_dung_g'] ?? ''); ?>"
                                   data-noi_dung_h="<?php echo htmlspecialchars($row['noi_dung_h'] ?? ''); ?>"
                                   data-noi_dung_i="<?php echo htmlspecialchars($row['noi_dung_i'] ?? ''); ?>"
                                   data-noi_dung_j="<?php echo htmlspecialchars($row['noi_dung_j'] ?? ''); ?>"
                                   data-dap_an_dung="<?php echo htmlspecialchars($row['dap_an_dung'] ?? ''); ?>"
                                   onclick="showEditForm(this); return false;">Sửa</a>
                                <a href="?ma_buoi=<?php echo $ma_buoi; ?>&delete_bai=<?php echo $row['ma_bai']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Bạn chắc chắn muốn xóa bài tập này?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">Chưa có bài tập nào.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Overlay form thêm bài tập -->
    <div id="addFormOverlay" class="overlay" style="display:<?php echo $show_form ? 'flex' : 'none'; ?>;">
        <div class="overlay-content">
            <span class="close-btn" onclick="hideAddForm()">×</span>
            <h3>Thêm bài tập mới</h3>
            <form method="post" id="addForm" onsubmit="return validateAddForm()">
                <label>Loại bài tập:</label>
                <select name="type" id="add_type" onchange="toggleAddFormFields()" required>
                    <option value="tu_luan">Tự luận</option>
                    <option value="trac_nghiem">Trắc nghiệm</option>
                </select>
                <label>Tên bài tập:</label>
                <input type="text" name="ten_bai" required>
                <label>Nội dung bài tập:</label>
                <textarea name="noi_dung" rows="3" required></textarea>
                <div id="add_tu_luan_fields">
                    <label>Hướng dẫn chấm:</label>
                    <textarea name="huong_dan_cham" rows="3"></textarea>
                </div>
                <div id="add_trac_nghiem_fields" style="display:none;">
                    <label>Nội dung A:</label>
                    <input type="text" name="noi_dung_a">
                    <label>Nội dung B:</label>
                    <input type="text" name="noi_dung_b">
                    <label>Nội dung C:</label>
                    <input type="text" name="noi_dung_c">
                    <label>Nội dung D:</label>
                    <input type="text" name="noi_dung_d">
                    <label>Nội dung E:</label>
                    <input type="text" name="noi_dung_e">
                    <label>Nội dung F:</label>
                    <input type="text" name="noi_dung_f">
                    <label>Nội dung G:</label>
                    <input type="text" name="noi_dung_g">
                    <label>Nội dung H:</label>
                    <input type="text" name="noi_dung_h">
                    <label>Nội dung I:</label>
                    <input type="text" name="noi_dung_i">
                    <label>Nội dung J:</label>
                    <input type="text" name="noi_dung_j">
                    <label>Đáp án đúng:</label>
                    <select name="dap_an_dung[]" id="add_dap_an_dung" multiple>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                        <option value="F">F</option>
                        <option value="G">G</option>
                        <option value="H">H</option>
                        <option value="I">I</option>
                        <option value="J">J</option>
                    </select>
                    <div id="add_dap_an_dung_error" class="error">Vui lòng chọn ít nhất một đáp án đúng hợp lệ!</div>
                </div>
                <button type="submit" name="add_bai_tap" class="btn-add">Thêm</button>
            </form>
        </div>
    </div>

    <!-- Overlay form sửa bài tập -->
    <div id="editFormOverlay" class="overlay" style="display:none;">
        <div class="overlay-content">
            <span class="close-btn" onclick="hideEditForm()">×</span>
            <h3>Sửa bài tập</h3>
            <form method="post" id="editForm" onsubmit="return validateEditForm()">
                <input type="hidden" name="edit_ma_bai" id="edit_ma_bai">
                <label>Loại bài tập:</label>
                <select name="edit_type" id="edit_type" onchange="toggleEditFormFields()" required>
                    <option value="tu_luan">Tự luận</option>
                    <option value="trac_nghiem">Trắc nghiệm</option>
                </select>
                <label>Tên bài tập:</label>
                <input type="text" name="edit_ten_bai" id="edit_ten_bai" required>
                <label>Nội dung bài tập:</label>
                <textarea name="edit_noi_dung" id="edit_noi_dung" rows="3" required></textarea>
                <div id="edit_tu_luan_fields">
                    <label>Hướng dẫn chấm:</label>
                    <textarea name="edit_huong_dan_cham" id="edit_huong_dan_cham" rows="3"></textarea>
                </div>
                <div id="edit_trac_nghiem_fields" style="display:none;">
                    <label>Nội dung A:</label>
                    <input type="text" name="edit_noi_dung_a" id="edit_noi_dung_a">
                    <label>Nội dung B:</label>
                    <input type="text" name="edit_noi_dung_b" id="edit_noi_dung_b">
                    <label>Nội dung C:</label>
                    <input type="text" name="edit_noi_dung_c" id="edit_noi_dung_c">
                    <label>Nội dung D:</label>
                    <input type="text" name="edit_noi_dung_d" id="edit_noi_dung_d">
                    <label>Nội dung E:</label>
                    <input type="text" name="edit_noi_dung_e" id="edit_noi_dung_e">
                    <label>Nội dung F:</label>
                    <input type="text" name="edit_noi_dung_f" id="edit_noi_dung_f">
                    <label>Nội dung G:</label>
                    <input type="text" name="edit_noi_dung_g" id="edit_noi_dung_g">
                    <label>Nội dung H:</label>
                    <input type="text" name="edit_noi_dung_h" id="edit_noi_dung_h">
                    <label>Nội dung I:</label>
                    <input type="text" name="edit_noi_dung_i" id="edit_noi_dung_i">
                    <label>Nội dung J:</label>
                    <input type="text" name="edit_noi_dung_j" id="edit_noi_dung_j">
                    <label>Đáp án đúng:</label>
                    <select name="edit_dap_an_dung[]" id="edit_dap_an_dung" multiple>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                        <option value="F">F</option>
                        <option value="G">G</option>
                        <option value="H">H</option>
                        <option value="I">I</option>
                        <option value="J">J</option>
                    </select>
                    <div id="edit_dap_an_dung_error" class="error">Vui lòng chọn ít nhất một đáp án đúng hợp lệ!</div>
                </div>
                <button type="submit" name="edit_bai_tap" class="btn-add">Cập nhật</button>
            </form>
        </div>
    </div>
    <?php else: ?>
        <h2>Không tìm thấy thông tin buổi học.</h2>
    <?php endif; ?>

    <script>
        function showAddForm() {
            document.getElementById('addFormOverlay').style.display = 'flex';
            toggleAddFormFields();
        }

        function hideAddForm() {
            document.getElementById('addFormOverlay').style.display = 'none';
            document.getElementById('add_dap_an_dung_error').style.display = 'none';
        }

        function toggleAddFormFields() {
            const type = document.getElementById('add_type').value;
            document.getElementById('add_tu_luan_fields').style.display = type === 'tu_luan' ? 'block' : 'none';
            document.getElementById('add_trac_nghiem_fields').style.display = type === 'trac_nghiem' ? 'block' : 'none';
            const dapAnSelect = document.getElementById('add_dap_an_dung');
            dapAnSelect.required = type === 'trac_nghiem';
        }

        function validateAddForm() {
            const type = document.getElementById('add_type').value;
            if (type === 'trac_nghiem') {
                const dapAn = document.getElementById('add_dap_an_dung');
                const error = document.getElementById('add_dap_an_dung_error');
                const inputs = ['noi_dung_a', 'noi_dung_b', 'noi_dung_c', 'noi_dung_d', 'noi_dung_e', 'noi_dung_f', 'noi_dung_g', 'noi_dung_h', 'noi_dung_i', 'noi_dung_j'];
                const validOptions = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                let hasOption = false;
                let validDapAn = false;

                // Kiểm tra ít nhất một phương án được nhập
                for (let input of inputs) {
                    if (document.getElementsByName(input)[0].value.trim()) {
                        hasOption = true;
                        break;
                    }
                }

                // Kiểm tra đáp án đúng hợp lệ
                const selectedDapAn = Array.from(dapAn.selectedOptions).map(opt => opt.value);
                for (let opt of selectedDapAn) {
                    const index = validOptions.indexOf(opt);
                    if (document.getElementsByName(inputs[index])[0].value.trim()) {
                        validDapAn = true;
                    } else {
                        validDapAn = false;
                        break;
                    }
                }

                if (!hasOption || !validDapAn || selectedDapAn.length === 0) {
                    error.style.display = 'block';
                    return false;
                } else {
                    error.style.display = 'none';
                }
            }
            return true;
        }

        function showEditForm(el) {
            document.getElementById('edit_ma_bai').value = el.getAttribute('data-ma_bai');
            document.getElementById('edit_type').value = el.getAttribute('data-type');
            document.getElementById('edit_ten_bai').value = el.getAttribute('data-ten_bai');
            document.getElementById('edit_noi_dung').value = el.getAttribute('data-noi_dung');
            const type = el.getAttribute('data-type');
            if (type === 'tu_luan') {
                document.getElementById('edit_tu_luan_fields').style.display = 'block';
                document.getElementById('edit_trac_nghiem_fields').style.display = 'none';
                document.getElementById('edit_huong_dan_cham').value = el.getAttribute('data-huong_dan_cham');
            } else {
                document.getElementById('edit_tu_luan_fields').style.display = 'none';
                document.getElementById('edit_trac_nghiem_fields').style.display = 'block';
                document.getElementById('edit_noi_dung_a').value = el.getAttribute('data-noi_dung_a');
                document.getElementById('edit_noi_dung_b').value = el.getAttribute('data-noi_dung_b');
                document.getElementById('edit_noi_dung_c').value = el.getAttribute('data-noi_dung_c');
                document.getElementById('edit_noi_dung_d').value = el.getAttribute('data-noi_dung_d');
                document.getElementById('edit_noi_dung_e').value = el.getAttribute('data-noi_dung_e');
                document.getElementById('edit_noi_dung_f').value = el.getAttribute('data-noi_dung_f');
                document.getElementById('edit_noi_dung_g').value = el.getAttribute('data-noi_dung_g');
                document.getElementById('edit_noi_dung_h').value = el.getAttribute('data-noi_dung_h');
                document.getElementById('edit_noi_dung_i').value = el.getAttribute('data-noi_dung_i');
                document.getElementById('edit_noi_dung_j').value = el.getAttribute('data-noi_dung_j');
                const dapAn = el.getAttribute('data-dap_an_dung').split(',');
                const select = document.getElementById('edit_dap_an_dung');
                for (let option of select.options) {
                    option.selected = dapAn.includes(option.value);
                }
            }
            document.getElementById('editFormOverlay').style.display = 'flex';
            toggleEditFormFields();
        }

        function hideEditForm() {
            document.getElementById('editFormOverlay').style.display = 'none';
            document.getElementById('edit_dap_an_dung_error').style.display = 'none';
        }

        function toggleEditFormFields() {
            const type = document.getElementById('edit_type').value;
            document.getElementById('edit_tu_luan_fields').style.display = type === 'tu_luan' ? 'block' : 'none';
            document.getElementById('edit_trac_nghiem_fields').style.display = type === 'trac_nghiem' ? 'block' : 'none';
            const dapAnSelect = document.getElementById('edit_dap_an_dung');
            dapAnSelect.required = type === 'trac_nghiem';
        }

        function validateEditForm() {
            const type = document.getElementById('edit_type').value;
            if (type === 'trac_nghiem') {
                const dapAn = document.getElementById('edit_dap_an_dung');
                const error = document.getElementById('edit_dap_an_dung_error');
                const inputs = ['edit_noi_dung_a', 'edit_noi_dung_b', 'edit_noi_dung_c', 'edit_noi_dung_d', 'edit_noi_dung_e', 'edit_noi_dung_f', 'edit_noi_dung_g', 'edit_noi_dung_h', 'edit_noi_dung_i', 'edit_noi_dung_j'];
                const validOptions = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
                let hasOption = false;
                let validDapAn = false;

                // Kiểm tra ít nhất một phương án được nhập
                for (let input of inputs) {
                    if (document.getElementsByName(input)[0].value.trim()) {
                        hasOption = true;
                        break;
                    }
                }

                // Kiểm tra đáp án đúng hợp lệ
                const selectedDapAn = Array.from(dapAn.selectedOptions).map(opt => opt.value);
                for (let opt of selectedDapAn) {
                    const index = validOptions.indexOf(opt);
                    if (document.getElementsByName(inputs[index])[0].value.trim()) {
                        validDapAn = true;
                    } else {
                        validDapAn = false;
                        break;
                    }
                }

                if (!hasOption || !validDapAn || selectedDapAn.length === 0) {
                    error.style.display = 'block';
                    return false;
                } else {
                    error.style.display = 'none';
                }
            }
            return true;
        }
    </script>
</body>
</html>