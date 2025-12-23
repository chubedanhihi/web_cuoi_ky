<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = (int)$_GET['id'];

/* (TÙY CHỌN) KHÔNG CHO XÓA ĐƠN ĐÃ HOÀN THÀNH */
$stmt = $conn->prepare("SELECT order_status FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$status = $stmt->get_result()->fetch_assoc()['order_status'] ?? '';
$stmt->close();

if ($status === 'completed') {
    header('Location: orders.php?error=completed');
    exit;
}

/* XÓA SẢN PHẨM TRONG ĐƠN */
$stmt = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

/* XÓA ĐƠN HÀNG */
$stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

/* QUAY LẠI */
header('Location: orders.php?deleted=1');
exit;