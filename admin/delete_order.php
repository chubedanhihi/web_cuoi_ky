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

/* XÓA ĐƠN HÀNG */
$stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

/* QUAY LẠI TRANG DANH SÁCH */
header('Location: orders.php?deleted=1');
exit;