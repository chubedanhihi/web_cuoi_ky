<?php
session_start();

// Chỉ admin mới được xóa
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once '../config.php';

// Kiểm tra id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$user_id = (int) $_GET['id'];

// ❗ Không cho xóa chính admin đang đăng nhập
if ($user_id == $_SESSION['admin_id']) {
    $_SESSION['error'] = "Không thể xóa chính bạn!";
    header('Location: users.php');
    exit;
}

// Lấy thông tin user để kiểm tra quyền
$stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    header('Location: users.php');
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// ❗ Không cho xóa ADMIN khác (an toàn hơn)
if ($user['role'] === 'ADMIN') {
    $_SESSION['error'] = "Không thể xóa tài khoản ADMIN!";
    header('Location: users.php');
    exit;
}

// Tiến hành xóa
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Quay về danh sách
header('Location: users.php');
exit;