<?php
// db.php
$servername = "localhost";
$port = 3307; // Cổng bạn đã cấu hình trong CSDL
$username = "root";
$password = "";
$dbname = "web_hoc_tieng_anh";

$mysqli = new mysqli($servername, $username, $password, $dbname, $port);

// Kiểm tra kết nối
if ($mysqli->connect_error) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $mysqli->connect_error);
}

// Đặt mã hóa UTF-8
$mysqli->set_charset("utf8mb4");

// // Chỉ xử lý id nếu được truyền
// if (isset($_GET['id'])) {
//     $customer_id = intval($_GET['id']); // Chuyển sang số nguyên để bảo mật
//     $query = "SELECT * FROM khach_hang WHERE ma_kh = ?";
//     $stmt = $mysqli->prepare($query);
//     if ($stmt) {
//         $stmt->bind_param("i", $customer_id);
//         $stmt->execute();
//         $result = $stmt->get_result();
//         if ($result->num_rows === 0) {
//             echo "Khách hàng không tồn tại!";
//             // Không thoát ngay, để các file khác có thể tiếp tục sử dụng kết nối
//         }
//         $stmt->close();
//     } else {
//         echo "Lỗi chuẩn bị truy vấn: " . $mysqli->error;
//     }
// }

// KHÔNG đóng kết nối ở đây để các file khác có thể sử dụng
// $mysqli->close(); // Loại bỏ hoặc di chuyển vào file phù hợp
?>