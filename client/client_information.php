<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="client_information.css">
</head>
<body>
    <?php 
    include '../db.php';
    include 'header.php';
    $ma_kh = isset($_GET['ma_kh']) ? intval($_GET['ma_kh']) : 1; // Mặc định là 1 nếu không có trên URL
    $sql = "SELECT * FROM khach_hang WHERE ma_kh = $ma_kh";
    $result = $mysqli->query($sql);
    $gv = $result ? $result->fetch_assoc() : null;
    ?>
    <main>
        <?php if ($gv): ?>
            <div class="tab">
                <a>Thông tin</a>
                <a>Thống kê</a>
            </div>
            <div class="client-info">
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
                
            </div>
        <?php else: ?>
            <p style="text-align:center;color:red;">Không tìm thấy thông tin khách hàng.</p>
        <?php endif; ?>
    </main>
    <a href="login.php" class="btn-logout">Đăng xuất</a>
</body>
</html>