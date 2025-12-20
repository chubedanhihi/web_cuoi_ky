<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = (int)($_POST['order_id'] ?? 0);
$user_id  = $_SESSION['user_id'];

if ($order_id <= 0) {
    die('Đơn hàng không hợp lệ');
}

/* CHỈ CHO PHÉP XÁC NHẬN KHI ĐANG GIAO */
$stmt = $conn->prepare("
    UPDATE orders
    SET order_status = 'completed'
    WHERE order_id = ?
      AND user_id = ?
      AND order_status = 'shipped'
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    $stmt->close();
    die('Không thể xác nhận đơn hàng này');
}

$stmt->close();

/* QUAY LẠI CHI TIẾT ĐƠN */
header("Location: order_detail.php?order_id=$order_id");
exit;