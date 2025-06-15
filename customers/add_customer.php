<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten = trim($_POST['ho_ten']);
    $email = trim($_POST['email']);
    $so_dien_thoai = trim($_POST['so_dien_thoai']);

    $query = "INSERT INTO khach_hang (ho_ten, email, so_dien_thoai) VALUES (?, ?, ?)";
    $stmt = $mysqli->prepare($query);
    if ($stmt) {
        $stmt->bind_param("sss", $ho_ten, $email, $so_dien_thoai);
        $stmt->execute();
        $stmt->close();
        header("Location: ../customers.php");
        exit;
    } else {
        echo "Lỗi thêm khách hàng: " . $mysqli->error;
    }
} else {
    echo "Dữ liệu không hợp lệ!";
}
?>