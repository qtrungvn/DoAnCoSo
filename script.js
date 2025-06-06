document.addEventListener("DOMContentLoaded", function () {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];

  // Chức năng slider
  const slider = document.querySelector("#list-comment");
  if (!slider) {
    return;
  }
  const commentItems = document.querySelectorAll("#list-comment .item");
  let currentIndex = 0;
  const containerWidth = 600;

  // Chức năng logo
  const logoLink = document.querySelector(".logo a");
  if (logoLink) {
    logoLink.addEventListener("click", function (e) {
      e.preventDefault();
      window.location.href = "index.html";
    });
  }

  // Kiểm tra các trường mật khẩu
  const passwordField = document.getElementById("password");
  const confirmPasswordField = document.getElementById("confirm_password");

  if (passwordField && confirmPasswordField) {
    let password = passwordField.value;
    let confirmPassword = confirmPasswordField.value;

    if (password !== confirmPassword) {
      alert("Mật khẩu nhập lại không khớp!");
      return;
    }
    alert("Đăng ký thành công!");
    form.submit();
  }

  const searchBox = document.getElementById("search-box");
  const searchResults = document.getElementById("search-results");

  searchBox.addEventListener("input", function () {
    let query = searchBox.value.trim();
    if (query.length === 0) {
      searchResults.style.display = "none";
      return;
    }

    fetch(`search.php?query=${encodeURIComponent(query)}`)
      .then((response) => response.json())
      .then((products) => {
        searchResults.innerHTML = "";
        if (products.length === 0) {
          searchResults.innerHTML =
            "<div class='result-item'>Không tìm thấy sản phẩm</div>";
        } else {
          products.forEach((product) => {
            let item = document.createElement("div");
            item.classList.add("result-item");
            item.innerHTML = `
              <img src="admin/${product.image}" alt="${
              product.name
            }" style="width: 50px; height: 50px; margin-right: 10px;">
              <span>${product.name} - ${new Intl.NumberFormat("vi-VN").format(
              product.price
            )} VNĐ</span>
            `;
            item.addEventListener("click", function () {
              window.location.href = `product_detail.php?id=${product.id}`;
            });
            searchResults.appendChild(item);
          });
        }
        searchResults.style.display = "block";
      })
      .catch((error) => console.error("Lỗi tìm kiếm:", error));
  });

  document.addEventListener("click", function (event) {
    if (
      !searchBox.contains(event.target) &&
      !searchResults.contains(event.target)
    ) {
      searchResults.style.display = "none";
    }
  });
});

$(document).ready(function () {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];

  function updateCartCount() {
    let totalQuantity = cart.reduce((sum, item) => sum + item.quantity, 0);
    $("#cart-count").text(totalQuantity);
  }

  function updateCartPopup() {
    let cartList = $("#cart-items");
    cartList.empty();
    if (cart.length === 0) {
      cartList.append("<p>Giỏ hàng trống</p>");
      $(".checkout-btn").hide();
    } else {
      cart.forEach((product, index) => {
        cartList.append(`
          <li>
              <img src="${product.image}" alt="${product.name}" width="50">
              <span>${product.name} - ${product.price} (x${product.quantity})</span>
              <button class="remove-item" data-index="${index}">Xóa</button>
          </li>
        `);
      });
      $(".checkout-btn").show();
    }
  }

  updateCartCount();

  $(".order-btn").click(function () {
    let productId = $(this).data("id");
    let productName = $(this).siblings(".name").text();
    let productPrice = $(this).siblings(".price").text();
    let productImage = $(this).siblings("img").attr("src");

    let existingProduct = cart.find((item) => item.id === productId);
    if (existingProduct) {
      existingProduct.quantity += 1;
    } else {
      cart.push({
        id: productId,
        name: productName,
        price: productPrice,
        image: productImage,
        quantity: 1,
      });
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
    updateCartPopup();
    alert("Đã thêm vào giỏ hàng!");
  });

  $(".cart-link").click(function (e) {
    e.preventDefault();
    updateCartPopup();
    $("#cart-popup").show();
  });

  $(document).on("click", ".remove-item", function () {
    let index = $(this).data("index");
    cart.splice(index, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
    updateCartPopup();
  });

  $(".checkout-btn").click(function () {
    if (cart.length === 0) {
      alert("Giỏ hàng của bạn đang trống!");
      return;
    }
    window.location.href = "checkout.php";
  });

  $(".close-popup").click(function () {
    $("#cart-popup").hide();
  });

  // Đặt hàng (xử lý nút xác nhận)
  $("#confirm-order").click(function () {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    let orderData = {
      name: $("#name").val(),
      phone: $("#phone").val(),
      address: $("#address").val(),
      note: $("#note").val(),
      cart: cart,
    };

    $.ajax({
      url: "process_order.php",
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(orderData),
      success: function (response) {
        let res = JSON.parse(response);
        if (res.status === "success") {
          alert(res.message);
          localStorage.removeItem("cart");
          window.location.href = "index.php";
        } else {
          alert(res.message);
        }
      },
      error: function (xhr, status, error) {
        console.log("Lỗi:", error);
        console.log("Phản hồi từ server:", xhr.responseText);
        alert("Có lỗi xảy ra! Vui lòng thử lại.");
      },
    });
  });

  // Thanh toán VNPAY
  $("#pay-vnpay").click(function (e) {
    e.preventDefault();

    let name = $("#name").val();
    let phone = $("#phone").val();
    let address = $("#address").val();

    if (!name || !phone || !address) {
      alert("Vui lòng nhập đầy đủ thông tin người nhận.");
      return;
    }

    let customerData = {
      name: name,
      phone: phone,
      address: address,
      deliveryMethod: $("#delivery-method").val(),
      deliveryDate: $("#delivery-date").val(),
      deliveryTime: $("#delivery-time").val(),
      orderNote: $("#order-note").val(),
      cart: JSON.parse(localStorage.getItem("cart") || "[]"),
    };

    $.ajax({
      url: "vnpay/create_payment.php",
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(customerData),
      success: function (response) {
        let res = JSON.parse(response);
        window.location.href = res.redirect_url;
      },
      error: function (xhr, status, error) {
        alert("Không thể thực hiện thanh toán VNPAY.");
        console.log("Lỗi:", error);
        console.log("Phản hồi từ server:", xhr.responseText);
      },
    });
  });
});
