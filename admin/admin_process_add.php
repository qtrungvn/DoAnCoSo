<?php
require_once "../config.php"; // Đảm bảo file này có kết nối `$conn`

// Kiểm tra form có được gửi không
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Nhận dữ liệu từ form
    $name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $price = isset($_POST['price']) ? (float)$_POST['price'] : null;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : null;
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;

    // Kiểm tra nếu tên sản phẩm rỗng
    if (empty($name)) {
        die("Lỗi: Tên sản phẩm không được để trống.");
    }

    // Kiểm tra và tạo thư mục uploads nếu chưa tồn tại
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Xử lý upload ảnh
    if (!empty($_FILES["image"]["name"])) {
        $imagePath = $uploadDir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
            // Định dạng đường dẫn lưu vào database
            $imagePathDB = "uploads/" . basename($_FILES["image"]["name"]);
        } else {
            die("Lỗi khi tải lên ảnh.");
        }
    } else {
        $imagePathDB = null; // Nếu không có ảnh, đặt NULL
    }

    // Thêm vào database
    $sql = "INSERT INTO products (category_id, name, description, price, image, stock) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdsi", $category_id, $name, $description, $price, $imagePathDB, $stock);

    if ($stmt->execute()) {
        echo "<script>alert('Thêm sản phẩm thành công!'); window.location.href='admin_dashboard.php';</script>";
    } else {
        echo "Lỗi khi thêm sản phẩm: " . $stmt->error;
    }

    // Đóng kết nối
    $stmt->close();
    $conn->close();
}

