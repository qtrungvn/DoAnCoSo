<?php
require_once "../config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];

    // Kiểm tra xem có tải lên hình ảnh mới không
    if (!empty($_FILES['new_image']['name'])) {
        $image = "uploads/" . basename($_FILES["new_image"]["name"]);
        move_uploaded_file($_FILES["new_image"]["tmp_name"], $image);
    } else {
        // Nếu không có hình mới, giữ nguyên hình cũ
        $image = $_POST['old_image'];
    }

    // Cập nhật database
    $sql = "UPDATE products SET category_id=?, name=?, description=?, price=?, image=?, stock=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isseis", $category_id, $name, $description, $price, $image, $stock, $id);
    
    if ($stmt->execute()) {
        echo "Cập nhật sản phẩm thành công!";
        header("Location:admin_products.php"); // Chuyển về danh sách sản phẩm
        exit();
    } else {
        echo "Lỗi khi cập nhật sản phẩm.";
    }
}

