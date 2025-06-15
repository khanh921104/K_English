<?php

require_once '../db.php';

if (isset($_GET['id'])) {
    $ma_kh = intval($_GET['id']);
    $query = "DELETE FROM khach_hang WHERE ma_kh = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("i", $ma_kh);
        $stmt->execute();
        $stmt->close();
        // Quay lại trang danh sách khách hàng sau khi xóa
        header("Location: ../customers.php");
        exit;
    } else {
        echo "Lỗi xóa: " . $mysqli->error;
    }
} else {
    echo "Không tìm thấy khách hàng cần xóa!";
}
?>