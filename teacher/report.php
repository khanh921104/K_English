<?php
session_start();
if (!isset($_SESSION['ma_quyen']) || $_SESSION['ma_quyen'] != 2) { // 2 là giáo viên
    header("Location: ../login.php");
    exit;
}
include '../db.php';

$ma_gv = intval($_SESSION['ma_kh']); // giáo viên là khách hàng có quyền 2

$sql = "SELECT k.ma_khoa, k.ten_khoa, COUNT(dk.ma_kh) AS so_hoc_sinh
        FROM khoa_hoc k
        JOIN giao_vien_tao_khoa_hoc gvkh ON k.ma_khoa = gvkh.ma_khoa
        LEFT JOIN dang_ky dk ON k.ma_khoa = dk.ma_khoa
        WHERE gvkh.ma_kh = ?
        GROUP BY k.ma_khoa, k.ten_khoa
        ORDER BY k.ma_khoa";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $ma_gv);
$stmt->execute();
$result = $stmt->get_result();

$labels = [];
$data = [];
while ($row = $result->fetch_assoc()) {
    $labels[] = $row['ten_khoa'];
    $data[] = intval($row['so_hoc_sinh']);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê khóa học của giáo viên</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="report.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2>Thống kê số học sinh theo từng khóa học</h2>
        <canvas id="courseChart" height="120"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('courseChart').getContext('2d');
        const courseChart = new Chart(ctx, {
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
    </script>
</body>
</html>
