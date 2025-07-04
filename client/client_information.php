<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 3) {
    // Nếu chưa đăng nhập hoặc không phải giáo viên, chuyển hướng về trang đăng nhập
    header("Location: ../login.php");
    exit;
}
include '../db.php';
include 'header.php';

$ma_kh = isset($_SESSION['ma_kh']) ? intval($_SESSION['ma_kh']) : 0;

// Xử lý cập nhật thông tin
if (isset($_POST['update_info'])) {
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);
    if ($ho_ten && $email && $so_dien_thoai) {
        $stmt = $mysqli->prepare("UPDATE khach_hang SET ho_ten=?, email=?, so_dien_thoai=? WHERE ma_kh=?");
        $stmt->bind_param('sssi', $ho_ten, $email, $so_dien_thoai, $ma_kh);
        $stmt->execute();
    }
}

// Lấy lại thông tin mới nhất
$sql = "SELECT * FROM khach_hang WHERE ma_kh = $ma_kh";
$result = $mysqli->query($sql);
$gv = $result ? $result->fetch_assoc() : null;

$show_edit = isset($_GET['edit']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin khách hàng</title>
    <link rel="stylesheet" href="client_information.css">
</head>
<body>
    <main>
        <?php if ($gv): ?>
            <div class="tab">
                <a>Thông tin</a>
                <a href="report.php">Thống kê</a>
            </div>
            <div class="client-info">
                <?php if (empty($show_edit)): ?>
                    <div class="details">
                        <span class="label">Tên khách hàng:</span>
                        <h2><?php echo htmlspecialchars($gv['ho_ten']); ?></h2>
                    </div>
                    <div class="details">
                        <span class="label">Email:</span>
                        <span class="value"><?php echo htmlspecialchars($gv['email']); ?></span>
                    </div>
                    <div class="details">
                        <span class="label">Số điện thoại:</span>
                        <span class="value"><?php echo htmlspecialchars($gv['so_dien_thoai']); ?></span>
                    </div>
                    <a href="?edit=1" class="btn-update">Chỉnh sửa</a>
                <?php else: ?>
                    <form method="post">
                        <div class="details">
                            <span class="label">Tên khách hàng:</span>
                            <input type="text" name="ho_ten" value="<?php echo htmlspecialchars($gv['ho_ten']); ?>" required>
                        </div>
                        <div class="details">
                            <span class="label">Email:</span>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($gv['email']); ?>" required>
                        </div>
                        <div class="details">
                            <span class="label">Số điện thoại:</span>
                            <input type="text" name="so_dien_thoai" value="<?php echo htmlspecialchars($gv['so_dien_thoai']); ?>" required>
                        </div>
                        <button type="submit" name="update_info" class="btn-update">Lưu</button>
                        <a href="client_information.php" class="btn-cancel">Hủy</a>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p style="text-align:center;color:red;">Không tìm thấy thông tin khách hàng.</p>
        <?php endif; ?>
    </main>
    <a href="../login.php" class="btn-logout">Đăng xuất</a>
</body>
</html>