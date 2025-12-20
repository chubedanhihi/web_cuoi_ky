<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request');
}

$order_id = (int)($_POST['order_id'] ?? 0);
$status   = $_POST['order_status'] ?? '';

$allow = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

if ($order_id <= 0 || !in_array($status, $allow)) {
    die('Dữ liệu không hợp lệ');
}

$stmt = $conn->prepare("
    UPDATE orders
    SET order_status = ?
    WHERE order_id = ?
");
$stmt->bind_param("si", $status, $order_id);
$stmt->execute();
$stmt->close();

/* QUAY LẠI TRANG ĐƠN */
header("Location: orders.php");
exit;