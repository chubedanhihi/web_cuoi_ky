<?php
// logout.php
session_start();

// Xóa tất cả dữ liệu session
session_unset();
session_destroy();

// Chuyển hướng về trang chủ sau khi đăng xuất
header("Location: index.php");
exit;
?>