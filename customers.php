<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 1) {
    // Nếu chưa đăng nhập hoặc không phải admin, chuyển hướng về trang đăng nhập
    header("Location: login.php");
    conslole.log("Bạn không có quyền truy cập trang này.");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách khách hàng</title>
    <link rel="stylesheet" href="css/customers.css">
</head>
<body>
    <h1>Danh sách khách hàng</h1>
    <div style="text-align:center; margin-bottom: 24px;">
        <button class="btn-edit" onclick="showAddForm()" type="button">+ Thêm khách hàng</button>
    </div>
    <?php
    require_once 'db.php';

    $query = "SELECT * FROM khach_hang";
    $result = $mysqli->query($query);

    if ($result && $result->num_rows > 0): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Mã KH</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Ngày đăng ký</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ma_kh']); ?></td>
                    <td><?php echo htmlspecialchars($row['ho_ten']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['so_dien_thoai']); ?></td>
                    <td><?php echo htmlspecialchars($row['ngay_dang_ky']); ?></td>
                    <td>
                        <button 
                            class="btn-edit" 
                            onclick="showEditForm(
                                '<?php echo $row['ma_kh']; ?>',
                                '<?php echo htmlspecialchars(addslashes($row['ho_ten'])); ?>',
                                '<?php echo htmlspecialchars(addslashes($row['email'])); ?>',
                                '<?php echo htmlspecialchars(addslashes($row['so_dien_thoai'])); ?>'
                            )"
                            type="button"
                        >Chỉnh sửa</button>
                        <a href="customers/delete_customer.php?id=<?php echo $row['ma_kh']; ?>" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa khách hàng này?');">Xóa</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        <!-- Form chỉnh sửa khách hàng (ẩn mặc định) -->
        <div id="editFormContainer" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:1000;">
            <form id="editForm" method="post" action="customers/edit_customer.php" style="background:#fff; max-width:400px; margin:60px auto; padding:24px 32px; border-radius:8px; box-shadow:0 4px 24px rgba(21,101,192,0.15); position:relative;">
                <h2 style="color:#1565c0; text-align:center;">Chỉnh sửa khách hàng</h2>
                <input type="hidden" name="ma_kh" id="edit_ma_kh">
                <div style="margin-bottom:14px;">
                    <label>Họ tên:</label>
                    <input type="text" name="ho_ten" id="edit_ho_ten" required style="width:100%;padding:8px;">
                </div>
                <div style="margin-bottom:14px;">
                    <label>Email:</label>
                    <input type="email" name="email" id="edit_email" required style="width:100%;padding:8px;">
                </div>
                <div style="margin-bottom:18px;">
                    <label>Số điện thoại:</label>
                    <input type="text" name="so_dien_thoai" id="edit_so_dien_thoai" required style="width:100%;padding:8px;">
                </div>
                <div style="text-align:center;">
                    <button type="submit" class="btn-edit">Lưu</button>
                    <button type="button" onclick="hideEditForm()" style="margin-left:8px;" class="btn-delete">Hủy</button>
                </div>
            </form>
        </div>
        <!-- Form thêm khách hàng (ẩn mặc định) -->
        <div id="addFormContainer" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:1000;">
            <form id="addForm" method="post" action="customers/add_customer.php" style="background:#fff; max-width:400px; margin:60px auto; padding:24px 32px; border-radius:8px; box-shadow:0 4px 24px rgba(21,101,192,0.15); position:relative;">
                <h2 style="color:#1565c0; text-align:center;">Thêm khách hàng</h2>
                <div style="margin-bottom:14px;">
                    <label>Họ tên:</label>
                    <input type="text" name="ho_ten" required style="width:100%;padding:8px;">
                </div>
                <div style="margin-bottom:14px;">
                    <label>Email:</label>
                    <input type="email" name="email" required style="width:100%;padding:8px;">
                </div>
                <div style="margin-bottom:18px;">
                    <label>Số điện thoại:</label>
                    <input type="text" name="so_dien_thoai" required style="width:100%;padding:8px;">
                </div>
                <div style="text-align:center;">
                    <button type="submit" class="btn-edit">Lưu</button>
                    <button type="button" onclick="hideAddForm()" style="margin-left:8px;" class="btn-delete">Hủy</button>
                </div>
            </form>
        </div>
        <script>
        function showAddForm() {
            document.getElementById('addFormContainer').style.display = 'block';
        }
        function hideAddForm() {
            document.getElementById('addFormContainer').style.display = 'none';
        }
        function showEditForm(ma_kh, ho_ten, email, so_dien_thoai) {
            document.getElementById('edit_ma_kh').value = ma_kh;
            document.getElementById('edit_ho_ten').value = ho_ten;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_so_dien_thoai').value = so_dien_thoai;
            document.getElementById('editFormContainer').style.display = 'block';
        }
        function hideEditForm() {
            document.getElementById('editFormContainer').style.display = 'none';
        }
        </script>
    <?php else: ?>
        <p>Không có khách hàng nào.</p>
    <?php endif; ?>
</body>
</html>
