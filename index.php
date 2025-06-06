<?php
session_start();
require_once "config.php";

$isLoggedIn = isset($_SESSION['fullname']);
$userName = $isLoggedIn ? $_SESSION['fullname'] : '';
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Lấy danh sách categories
$categories = [];
$sqlCategories = "SELECT * FROM categories";
$resultCategories = $conn->query($sqlCategories);
if ($resultCategories->num_rows > 0) {
    while ($rowCat = $resultCategories->fetch_assoc()) {
        $categories[] = $rowCat;
    }
}


$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;


$search = isset($_GET['search']) ? trim($_GET['search']) : '';


$products = [];
$sql = "SELECT * FROM products WHERE 1=1";

if ($category_id > 0) {
    $sql .= " AND category_id = $category_id";
}

if (!empty($search)) {
    $sql .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Trang chủ - Đặt món ăn & nước uống</title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div id="wrapper">
    <div class="header-middle">
        <div class="container">
            <div class="header-middle-left">
                <div class="header-logo">
                    <a href="index.php">
                        <img src="assets/logo.png" alt="Logo" />
                    </a>
                </div>
            </div>
            <div class="header-middle-center">
                <form action="index.php" method="GET" class="form-search">
                    <img src="assets/search.png" alt="Tìm kiếm" />
                    <input
                        type="text"
                        name="search"
                        class="form-search-input"
                        placeholder="Tìm kiếm món ăn..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    />
                    <?php if ($category_id > 0): ?>
                        <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                    <?php endif; ?>
                    <button type="submit" class="filter-btn">Lọc</button>
                </form>
            </div>

            <div class="header-middle-right">
                <a href="checkout.html" class="cart-link">
                    <img
                        src="assets/cart.png"
                        alt="Giỏ hàng"
                        style="height: 24px; margin-right: 5px"
                    />
                    Giỏ hàng<span id="cart-count">0</span>
                </a>
                <a href="order_history.php">🧾 Đơn hàng</a>
            </div>

            <div>
                <ul class="header-middle-account">
                    <?php if (isset($_SESSION['fullname'])): ?>
                        <li>
                            <a href="user.php" style="display: flex; align-items: center">
                                <img
                                    src="assets/user.png"
                                    alt="User"
                                    style="height: 24px; margin-right: 5px"
                                />
                                <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="logout.php"><i class="fa fa-sign-out"></i> Đăng xuất</a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a href="login.php"><i class="fa fa-user"></i> Đăng nhập</a>
                        </li>
                        <li>
                            <a href="register.php"><i class="fa fa-user-plus"></i> Đăng ký</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <div id="banner">
        <div class="box-left">
            <h2>
                <span>THỨC ĂN</span><br /><span>THƯỢNG HẠNG</span>
            </h2>
            <button>Mua ngay</button>
        </div>
        <div class="box-right">
            <img src="assets/miy.png" alt="" />
            <img src="assets/hamberger.png" alt="" />
            <img src="assets/pizza.png" alt="" />
        </div>
    </div>

    <div id="wp-products">
        <h2>THỰC ĐƠN CỦA CHÚNG TÔI</h2>

        <!-- Danh mục sản phẩm -->
        <div id="product-categories" style="text-align: center; margin-bottom: 30px;">
            <a
                href="index.php"
                style="margin: 0 15px; font-weight: <?php echo $category_id == 0 ? 'bold' : 'normal'; ?>"
                >Tất cả</a
            >
            <?php foreach ($categories as $cat): ?>
                <a
                    href="index.php?category_id=<?php echo $cat['id']; ?>"
                    style="margin: 0 15px; font-weight: <?php echo $category_id == $cat['id'] ? 'bold' : 'normal'; ?>"
                    ><?php echo htmlspecialchars($cat['name']); ?></a
                >
            <?php endforeach; ?>
        </div>

        <ul id="list-products">
            <?php if (empty($products)): ?>
                <p>Không tìm thấy sản phẩm nào phù hợp.</p>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <li class="item">
                        <img
                            src="admin/<?php echo $product['image']; ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                        />
                        <div class="name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="desc"><?php echo htmlspecialchars($product['description']); ?></div>
                        <div class="price">
                            <?php echo number_format($product['price'], 0, ',', '.'); ?> VNĐ
                        </div>
                        <button class="order-btn" data-id="<?php echo $product['id']; ?>">
                            Đặt ngay
                        </button>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <footer id="footer">
        <div class="footer-container">
            <!-- ... phần footer như bạn đã có ... -->
            <div class="footer-box company-info">
                <div>
                    <img src="assets/logo.png" alt="Logo " />
                </div>
                <div class="company-text">
                    <p><b>CÔNG TY TNHH FASTFOOD VIỆT NAM</b></p>
                    <p>
                        Địa chỉ: Tầng 8, Tòa nhà Thu Duc Campus, Khu Công Nghệ Cao,
                        xa lộ Hà Nội,Hiệp Phú,Thủ Đức, TP.HCM, Việt Nam
                    </p>
                    <p>Điện thoại: (028) 0941810480</p>
                    <p>Tổng đài: <b>1900-9999</b></p>
                    <p>Mã số thuế: 0309883266</p>
                    <p>Ngày cấp: 15/07/2008 – Nơi cấp: Cục Thuế Hồ Chí Minh</p>
                    <p>
                        Hộp thư góp ý:
                        <a href="mailto:trungkg915@gmail.com"> trungkg915@gmail.com.vn</a>
                    </p>
                </div>
            </div>

            <div class="footer-box">
                <h3>Thông Tin Liên Hệ</h3>
                <div>
                    <p>Chính sách & Quy định chung</p>
                    <p>Chính sách thanh toán</p>
                    <p>Chính sách hoạt động</p>
                    <p>Chính sách bảo mật</p>
                    <p>Thông tin vận chuyển</p>
                    <img src="assets/bocongthuong.png" alt="Đã thông báo Bộ Công Thương" />
                </div>
            </div>

            <div class="footer-box social-apps">
                <div class="social">
                    <h3>Kết nối với chúng tôi</h3>
                    <div class="social">
                        <a href="#"><img src="assets/facebook-icon.png" alt="Facebook"> Facebook</a>
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.418521067304!2d106.78303187467064!3d10.855738189297877!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3175276e7ea103df%3A0xb6cf10bb7d719327!2zSFVURUNIIC0gxJDhuqFpIGjhu41jIEPDtG5nIG5naOG7hyBUUC5IQ00gKFRodSBEdWMgQ2FtcHVzKQ!5e0!3m2!1svi!2s!4v1743751938497!5m2!1svi!2s%22%20width=%22600%22%20height=%22450%22%20style=%22border:0;%22%20allowfullscreen=%22%22%20loading=%22lazy%22%20referrerpolicy=%22no-referrer-when-downgrade"
                            width="280" height="150"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            title="Bản đồ cửa hàng của chúng tôi">
                        </iframe>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        © 2024 FastFood Việt Nam
    </div>
</footer>


   <!-- Popup giỏ hàng -->
<div id="cart-popup" class="popup" style="display: none;">
    <div class="popup-content">
        <div class="popup-header">
            <h3><img src="assets/cart.png" alt="Giỏ hàng" style="height: 24px; margin-right: 5px;"> Giỏ hàng</h3>
            <span class="close-popup">×</span>
        </div>
        <div class="popup-body">
            <ul id="cart-items"></ul>
        </div>
        <div class="popup-footer">
            <button class="checkout-btn">Thanh toán</button>
        </div>
    </div>
</div>
    </div>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script src="script.js"></script>
    <script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
<df-messenger
  intent="WELCOME"
  chat-title="chatbox"
  agent-id="97ab553e-5ea2-4efe-832b-36cc0335560c"
  language-code="vi"
></df-messenger>
</body>
</html>
