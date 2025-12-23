<?php
session_start();

// Chỉ admin mới được xóa
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once '../config.php';

// Kiểm tra id hợp lệ
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = (int) $_GET['id'];

// Lấy thông tin sản phẩm (để xóa ảnh)
$stmt = $conn->prepare("SELECT image FROM products WHERE productID = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header('Location: products.php');
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Xóa ảnh nếu tồn tại
if (!empty($product['image'])) {
    $image_path = "../" . $product['image'];
    if (file_exists($image_path)) {
        unlink($image_path);
    }
}

// Xóa sản phẩm trong DB
$stmt = $conn->prepare("DELETE FROM products WHERE productID = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$stmt->close();

// Quay về danh sách
header('Location: products.php');
exit;