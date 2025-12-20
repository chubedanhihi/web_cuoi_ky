<?php
session_start();
require 'config.php';
require 'model/product_db.php';

// Nếu chưa có giỏ thì tạo giỏ
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Lấy dữ liệu từ form gửi lên
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$qty        = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Kiểm tra số lượng hợp lệ
if ($qty < 1) {
    $qty = 1;
}

// Lấy thông tin sản phẩm từ DB
$product = get_product($product_id);
if (!$product) {
    die("Sản phẩm không tồn tại!");
}

// Thêm sản phẩm vào giỏ
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $qty;
} else {
    $_SESSION['cart'][$product_id] = $qty;
}

// Tạo thông báo đơn giản để hiển thị khi quay về giỏ
$_SESSION['cart_message'] = $product['productName'] . " đã được thêm vào giỏ hàng.";

// Quay về giỏ hàng
header("Location: cart.php");
exit;