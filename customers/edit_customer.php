<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ma_kh = intval($_POST['ma_kh']);
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);

    $query = "UPDATE khach_hang SET ho_ten = ?, email = ?, so_dien_thoai = ? WHERE ma_kh = ?";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("sssi", $ho_ten, $email, $so_dien_thoai, $ma_kh);
        $stmt->execute();
        $stmt->close();
        header("Location: ../customers.php");
        exit;
    } else {
        echo "Lỗi cập nhật: " . $mysqli->error;
    }
} else {
    echo "Dữ liệu không hợp lệ!";
}
?>