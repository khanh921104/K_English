<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 2) {
    header("Location: ../login.php");
    exit;
}
include '../db.php';
include 'header.php';

$ma_kh = isset($_SESSION['ma_kh']) ? intval($_SESSION['ma_kh']) : 0;
$sql = "SELECT * FROM khach_hang WHERE ma_kh = $ma_kh";
$result = $mysqli->query($sql);
$gv = $result ? $result->fetch_assoc() : null;

if (isset($_POST['update_info'])) {
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);
    if ($ho_ten && $email && $so_dien_thoai) {
        $stmt = $mysqli->prepare("UPDATE khach_hang SET ho_ten=?, email=?, so_dien_thoai=? WHERE ma_kh=?");
        $stmt->bind_param('sssi', $ho_ten, $email, $so_dien_thoai, $ma_kh);
        $stmt->execute();
        $sql = "SELECT * FROM khach_hang WHERE ma_kh = $ma_kh";
        $result = $mysqli->query($sql);
        $gv = $result ? $result->fetch_assoc() : null;
    }
}

$labels = [];
$data = [];
$sqlChart = "SELECT kh.ten_khoa, COUNT(dk.ma_kh) AS so_hs
             FROM khoa_hoc kh
             INNER JOIN giao_vien_tao_khoa_hoc gvtkh ON kh.ma_khoa = gvtkh.ma_khoa
             LEFT JOIN dang_ky dk ON kh.ma_khoa = dk.ma_khoa
             WHERE gvtkh.ma_kh = $ma_kh
             GROUP BY kh.ten_khoa";
$resultChart = $mysqli->query($sqlChart);
if ($resultChart) {
    while ($row = $resultChart->fetch_assoc()) {
        $labels[] = $row['ten_khoa'];
        $data[] = (int)$row['so_hs'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin giáo viên</title>
    <link rel="stylesheet" href="teacher_information.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<main class="container">
<?php if ($gv): ?>
    <div class="tab">
        <button class="tab-link active" data-tab="info">Thông tin</button>
        <button class="tab-link" data-tab="report">Thống kê</button>
    </div>

    <div class="tab-content" id="info-tab">
        <div class="card">
            <div class="info-row"><span class="label">Tên giáo viên:</span> <span class="value"><?php echo htmlspecialchars($gv['ho_ten']); ?></span></div>
            <div class="info-row"><span class="label">Email:</span> <span class="value"><?php echo htmlspecialchars($gv['email']); ?></span></div>
            <div class="info-row"><span class="label">Số điện thoại:</span> <span class="value"><?php echo htmlspecialchars($gv['so_dien_thoai']); ?></span></div>
            <div class="button-group">
                <button onclick="document.getElementById('edit-form').style.display='block';">Chỉnh sửa</button>
            </div>
            <form id="edit-form" method="post" style="display:none" class="edit-form">
                <div class="form-group">
                    <label>Tên giáo viên:</label>
                    <input type="text" name="ho_ten" value="<?php echo htmlspecialchars($gv['ho_ten']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($gv['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại:</label>
                    <input type="text" name="so_dien_thoai" value="<?php echo htmlspecialchars($gv['so_dien_thoai']); ?>" required>
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
            <h2 style="text-align: center;">Thống kê số học sinh theo từng khóa học</h2>
            <canvas id="courseChart" height="120"></canvas>
        </div>
    </div>
<?php else: ?>
    <p class="error-text">Không tìm thấy thông tin giáo viên.</p>
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
                    label: 'Số học sinh',
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
                        text: 'Biểu đồ học sinh theo khóa học',
                        font: { size: 18 }
                    },
                    tooltip: {
                        callbacks: {
                            label: context => ` ${context.dataset.label}: ${context.parsed.y} học sinh`
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
                            stepSize: 1,
                            font: { size: 13 }
                        },
                        title: {
                            display: true,
                            text: 'Số học sinh',
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
