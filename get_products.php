<?php
require_once "config.php";

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if ($category_id > 0) {  // chỉ lấy sản phẩm nếu category_id hợp lệ
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ?");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $products = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
} else {
    $products = []; // nếu category_id = 0 hoặc không hợp lệ thì không hiện sản phẩm
}
?>

<!DOCTYPE html>
<html lang="vi">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đăng ký tài khoản</title>
    <link rel="stylesheet" href="style.css" />
  </head>
<body>
<ul id="list-products">
<?php foreach ($products as $product): ?>
    <li class="item">
        <img src="admin/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" />
        <div class="name"><?php echo htmlspecialchars($product['name']); ?></div>
        <div class="desc"><?php echo htmlspecialchars($product['description']); ?></div>
        <div class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ</div>
        <button class="order-btn" data-id="<?php echo $product['id']; ?>">Đặt ngay</button>
    </li>
<?php endforeach; ?>
</ul>
</body>
</html>
