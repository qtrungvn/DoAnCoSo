<?php session_start(); ?>
<?php
require_once "config.php"; // Kết nối DB

$user = [
    'fullname' => '',
    'phone' => '',
    'address' => ''
];

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT fullname, phone, address FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            text-align: center;
        }

        .container {
            max-width: 700px;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            margin: auto;
            text-align: left;
        }

        h3 {
            color: #d9534f;
            text-align: center;
        }

        .order-summary, .customer-info {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
            margin-bottom: 15px;
        }

        .order-summary p, .customer-info p {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            font-size: 14px;
        }

        .order-summary .item {
            font-weight: bold;
        }

        .total-price {
            font-size: 18px;
            font-weight: bold;
            color: #d9534f;
            text-align: center;
            margin-top: 10px;
        }

        .checkout-btn, .prev-btn,.btn-success {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
        }

        .checkout-btn,.btn-success {
            background: #d9534f;
            color: white;
        }

        .checkout-btn:hover {
            background: #c9302c;
        }
        .btn-success:hover {
            background: #c9302c;
        }

        .prev-btn {
            background: #ddd;
            color: black;
        }

        .prev-btn:hover {
            background: #bbb;
        }

        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        textarea {
            resize: vertical;
        }

    </style>
</head>
<body>

    <div class="container">
        <h3>Thanh toán</h3>

        <div class="customer-info">
            <h4> Thông tin người nhận</h4>
            <input type="text" id="name" placeholder="Tên người nhận" required>
            <input type="text" id="phone" placeholder="Số điện thoại nhận hàng" required>
            <select id="province" required>
            <option value="">Chọn Tỉnh/Thành phố</option>
            </select>
            <select id="district" required>
                <option value="">Chọn Quận/Huyện</option>
            </select>
            <select id="ward" required>
                <option value="">Chọn Phường/Xã</option>
            </select>
            <input type="text" id="address-detail" placeholder="Số nhà, tên đường..." required>
        </div>

        <div class="customer-info">
            <h4>🚚 Thông tin đơn hàng</h4>
            <p>Hình thức giao hàng</p>
            <select id="delivery-method">
                <option value="giao_tan_noi">Giao tận nơi</option>
                <option value="tu_den_lay">Tự đến lấy</option>
            </select>
            <p>Ghi chú đơn hàng</p>
            <textarea id="order-note" placeholder="Nhập ghi chú"></textarea>
        </div>

        <div class="order-summary">
            <h4>🛒 Đơn hàng</h4>
            <div id="order-summary-content"></div>
            <p class="total-price" id="total-price">Tổng tiền: 0 VNĐ</p>
        </div>
    
        
        <button class="checkout-btn" id="confirm-order">Đặt hàng</button>
        <button class="prev-btn" onclick="window.location.href='index.php'">⬅ Quay về trang chủ</button>
    </div>

    <script>
    const userInfo = {
        name: "<?php echo htmlspecialchars($user['fullname'], ENT_QUOTES); ?>",
        phone: "<?php echo htmlspecialchars($user['phone'], ENT_QUOTES); ?>",
        address: "<?php echo htmlspecialchars($user['address'], ENT_QUOTES); ?>"
    };

        $("#vnpay-form").submit(function (e) {
            let totalPrice = 0;
            let shippingFee = 30000;
            let cart = JSON.parse(localStorage.getItem("cart")) || [];

            cart.forEach((product) => {
                let price = parseFloat(product.price.toString().replace(/\./g, "").replace(" VNĐ", "").trim());
                if (!isNaN(price)) {
                    totalPrice += price * product.quantity;
                }
            });

            totalPrice += shippingFee;
            $("#vnpay-amount").val(totalPrice);
        });

        $(document).ready(function () {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let orderSummary = $("#order-summary-content");
    let totalPrice = 0;
    let shippingFee = 30000;
    $("#name").val(userInfo.name);
    $("#phone").val(userInfo.phone);
    $("#address-detail").val(userInfo.address);

    if (cart.length === 0) {
        orderSummary.append("<p>Giỏ hàng trống</p>");
    } else {
        cart.forEach((product) => {
            let price = parseFloat(product.price.toString().replace(/\./g, "").replace(" VNĐ", "").trim());

            orderSummary.append(`
                <p class="item">${product.quantity}x ${product.name}<span>${(price * product.quantity).toLocaleString()} đ</span></p>
                
            `);
            if (!isNaN(price)) {
                totalPrice += price * product.quantity;
            }
        });

        // ✅ Chỉ thêm phí vận chuyển một lần sau vòng lặp
        orderSummary.append(`<p> Phí vận chuyển <span>${shippingFee.toLocaleString()} đ</span></p>`);
    }

    // ✅ Cập nhật tổng tiền chỉ một lần
    $("#total-price").text(`Tổng tiền: ${(totalPrice + shippingFee).toLocaleString()} đ`);

            $("#confirm-order").click(function (e) {
                e.preventDefault();

                let name = $("#name").val();
                let phone = $("#phone").val();
                let province = $("#province option:selected").text();
                let district = $("#district option:selected").text();
                let ward = $("#ward option:selected").text();
                let detailAddress = $("#address-detail").val()
                let address = `${detailAddress}, ${ward}, ${district}, ${province}`;
                let deliveryMethod = $("#delivery-method").val();
                let deliveryDate = $("#delivery-date").val();
                let deliveryTime = $("#delivery-time").val();
                let orderNote = $("#order-note").val();

                if (!name || !phone || !address) {
                    alert("Vui lòng nhập đầy đủ thông tin người nhận.");
                    return;
                }

                let customerData = {
                    name,
                    phone,
                    address,
                    deliveryMethod,
                    deliveryDate,
                    deliveryTime,
                    orderNote,
                    cart: cart
                };

                $.ajax({
                    url: "process_order.php",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(customerData),
                    success: function (response) {
                        let res = JSON.parse(response);
                        alert(res.message);
                        localStorage.removeItem("cart");
                        if (res.status === "success" && res.redirect_url) {
                            window.location.href = res.redirect_url;
                        } else {
                            alert("Có lỗi xảy ra, vui lòng thử lại!");
                        }

                    }
                });
            });
        });
    </script>

</body>
</html>
<script>
    const provinceSelect = $("#province");
    const districtSelect = $("#district");
    const wardSelect = $("#ward");

    let dataTinhThanh = {};

    async function loadProvinces() {
        const res = await fetch("https://raw.githubusercontent.com/madnh/hanhchinhvn/master/dist/tinh_tp.json");
        dataTinhThanh = await res.json();
        for (const [code, tinh] of Object.entries(dataTinhThanh)) {
            provinceSelect.append(`<option value="${code}">${tinh.name_with_type}</option>`);
        }
    }

    async function loadDistricts(provinceCode) {
        districtSelect.empty().append('<option value="">Chọn Quận/Huyện</option>');
        wardSelect.empty().append('<option value="">Chọn Phường/Xã</option>');

        const res = await fetch("https://raw.githubusercontent.com/madnh/hanhchinhvn/master/dist/quan_huyen.json");
        const districts = await res.json();

        for (const [code, quan] of Object.entries(districts)) {
            if (quan.parent_code === provinceCode) {
                districtSelect.append(`<option value="${code}">${quan.name_with_type}</option>`);
            }
        }
    }

    async function loadWards(districtCode) {
        wardSelect.empty().append('<option value="">Chọn Phường/Xã</option>');

        const res = await fetch("https://raw.githubusercontent.com/madnh/hanhchinhvn/master/dist/xa_phuong.json");
        const wards = await res.json();

        for (const [code, xa] of Object.entries(wards)) {
            if (xa.parent_code === districtCode) {
                wardSelect.append(`<option value="${code}">${xa.name_with_type}</option>`);
            }
        }
    }

    // Gọi khi trang tải
    loadProvinces();

    // Khi chọn tỉnh
    provinceSelect.change(function () {
        const provinceCode = $(this).val();
        if (provinceCode) {
            loadDistricts(provinceCode);
        }
    });

    // Khi chọn quận
    districtSelect.change(function () {
        const districtCode = $(this).val();
        if (districtCode) {
            loadWards(districtCode);
        }
    });
</script>
