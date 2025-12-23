<?php
session_start();
require_once 'config.php';

$order_id = (int)($_POST['order_id'] ?? 0);
if ($order_id <= 0) {
    die("Thiếu đơn hàng");
}

$stmt = $conn->prepare("
    UPDATE orders 
    SET payment_status = 'paid'
    WHERE order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

header("Location: order_success.php?order_id=$order_id");
exit;