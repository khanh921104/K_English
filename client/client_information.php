<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 3) {
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
        // Làm mới dữ liệu sau khi cập nhật
        $sql = "SELECT * FROM khach_hang WHERE ma_kh = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $ma_kh);
        $stmt->execute();
        $result = $stmt->get_result();
        $hv = $result->fetch_assoc();
    }
}

// Lấy thông tin học viên
$sql = "SELECT * FROM khach_hang WHERE ma_kh = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $ma_kh);
$stmt->execute();
$result = $stmt->get_result();
$hv = $result->fetch_assoc();

// Thống kê tỷ lệ hoàn thành bài tập
$labels = [];
$data = [];
$sqlChart = "SELECT kh.ten_khoa, 
                    COUNT(lbt.ma_bai) AS so_bai_hoan_thanh,
                    COALESCE((SELECT COUNT(DISTINCT bt.ma_bai) FROM bai_tap bt 
                              INNER JOIN buoi_hoc bh ON bt.ma_buoi = bh.ma_buoi 
                              WHERE bh.ma_khoa = kh.ma_khoa), 0) AS tong_so_bai
             FROM dang_ky dk
             INNER JOIN khoa_hoc kh ON dk.ma_khoa = kh.ma_khoa
             LEFT JOIN bai_tap bt ON bt.ma_buoi IN (SELECT bh.ma_buoi FROM buoi_hoc bh WHERE bh.ma_khoa = kh.ma_khoa)
             LEFT JOIN lam_bai_tap lbt ON lbt.ma_bai = bt.ma_bai AND lbt.ma_kh = dk.ma_kh AND lbt.trang_thai = 'Hoàn thành'
             WHERE dk.ma_kh = ?
             GROUP BY kh.ten_khoa, kh.ma_khoa";
$stmtChart = $mysqli->prepare($sqlChart);
$stmtChart->bind_param('i', $ma_kh);
$stmtChart->execute();
$resultChart = $stmtChart->get_result();
if ($resultChart) {
    while ($row = $resultChart->fetch_assoc()) {
        $tong_so_bai = $row['tong_so_bai'] ?? 0; // Gán 0 nếu cột không tồn tại
        $ti_le = $tong_so_bai > 0 ? ($row['so_bai_hoan_thanh'] / $tong_so_bai * 100) : 0;
        $labels[] = $row['ten_khoa'];
        $data[] = number_format($ti_le, 2) . '%';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin học viên</title>
    <link rel="stylesheet" href="client_information.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<main class="container">
<?php if ($hv): ?>
    <div class="tab">
        <button class="tab-link active" data-tab="info">Thông tin</button>
        <button class="tab-link" data-tab="report">Thống kê</button>
    </div>

    <div class="tab-content" id="info-tab">
        <div class="card">
            <div class="info-row"><span class="label">Tên học viên:</span> <span class="value"><?php echo htmlspecialchars($hv['ho_ten']); ?></span></div>
            <div class="info-row"><span class="label">Email:</span> <span class="value"><?php echo htmlspecialchars($hv['email']); ?></span></div>
            <div class="info-row"><span class="label">Số điện thoại:</span> <span class="value"><?php echo htmlspecialchars($hv['so_dien_thoai']); ?></span></div>
            <div class="button-group">
                <button onclick="document.getElementById('edit-form').style.display='block';">Chỉnh sửa</button>
            </div>
            <form id="edit-form" method="post" style="display:none" class="edit-form">
                <div class="form-group">
                    <label>Tên học viên:</label>
                    <input type="text" name="ho_ten" value="<?php echo htmlspecialchars($hv['ho_ten']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($hv['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại:</label>
                    <input type="text" name="so_dien_thoai" value="<?php echo htmlspecialchars($hv['so_dien_thoai']); ?>" required>
                </div>
                <div class="button-group">
                    <button type="submit" name="update_info">Lưu</button>
                    <button type="button" onclick="document.getElementById('edit-form').style.display='none';">Hủy</button>
                </div>
            </form>
        </div>
    </div>

    <div class="tab-content" id="report-tab" style="display: none;">
        <div class="card">
            <h2 style="text-align: center;">Thống kê tỷ lệ bài tập đã hoàn thành</h2>
            <canvas id="courseChart" height="120"></canvas>
        </div>
    </div>
<?php else: ?>
    <p class="error-text">Không tìm thấy thông tin học viên.</p>
<?php endif; ?>
</main>
<a href="../login.php" class="btn btn-logout">Đăng xuất</a>

<script>
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');

    tabLinks.forEach(link => {
        link.addEventListener('click', () => {
            const targetTab = link.getAttribute('data-tab');
            tabLinks.forEach(btn => btn.classList.remove('active'));
            link.classList.add('active');
            tabContents.forEach(tab => {
                tab.style.display = tab.id === `${targetTab}-tab` ? 'block' : 'none';
            });
            if (targetTab === 'report' && !window.chartRendered) {
                renderChart();
                window.chartRendered = true;
            }
        });
    });

    function renderChart() {
        const ctx = document.getElementById('courseChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Tỷ lệ hoàn thành',
                    data: <?= json_encode($data) ?>,
                    backgroundColor: [
                        '#42a5f5', '#66bb6a', '#ffa726', '#ab47bc',
                        '#26a69a', '#ef5350', '#5c6bc0', '#ffca28'
                    ],
                    borderRadius: 8,
                    borderWidth: 1,
                    barThickness: 40
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: 'Tỷ lệ bài tập hoàn thành theo khóa học',
                        font: { size: 18 }
                    },
                    tooltip: {
                        callbacks: {
                            label: context => ` ${context.dataset.label}: ${context.parsed.y}`
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: { size: 13 },
                            maxRotation: 30,
                            minRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 10,
                            callback: value => value + '%',
                            font: { size: 13 }
                        },
                        title: {
                            display: true,
                            text: 'Tỷ lệ (%)',
                            font: { size: 14 }
                        }
                    }
                }
            }
        });
    }
</script>
</body>
</html>