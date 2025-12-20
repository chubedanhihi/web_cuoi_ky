<?php
session_start();
require 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin user
$stmt = $conn->prepare("SELECT user_id, username, full_name, password FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_pass = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    // Kiểm tra rỗng
    if (!$current_pass || !$new_pass || !$confirm_pass) {
        $error = "Vui lòng điền đầy đủ các trường.";
    } 
    // Kiểm tra mật khẩu hiện tại
    elseif (!password_verify($current_pass, $user['password'])) {
        $error = "Mật khẩu hiện tại không đúng.";
    } 
    // Kiểm tra mật khẩu mới trùng
    elseif ($new_pass !== $confirm_pass) {
        $error = "Mật khẩu mới và xác nhận không trùng khớp.";
    } 
    // Cập nhật mật khẩu
    else {
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $stmt->bind_param("si", $hashed, $user_id);
        if ($stmt->execute()) {
            $success = "Đổi mật khẩu thành công!";
        } else {
            $error = "Có lỗi xảy ra. Thử lại.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - SHOPKO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
    }

    .card {
        border-radius: 15px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .btn-red {
        background-color: #dc3545;
        color: #fff;
    }

    .btn-red:hover {
        background-color: #c82333;
    }
    </style>
</head>

<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">SHOPKO</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">Trang Chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">Sản Phẩm</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Liên Hệ</a></li>
                    <?php if($user): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <?= htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Người dùng') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php">Thông tin cá nhân</a></li>
                            <li><a class="dropdown-item" href="change_password.php">Đổi mật khẩu</a></li>
                            <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            Giỏ hàng
                            <?php $cart_count = $_SESSION['cart'] ?? 0; 
                        if($cart_count): ?>
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= array_sum($_SESSION['cart']) ?>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- FORM ĐỔI MẬT KHẨU -->
    <div class="container my-5">
        <div class="card p-4 mx-auto" style="max-width: 500px;">
            <h3 class="text-center mb-4">Đổi mật khẩu</h3>

            <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Mật khẩu hiện tại</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mật khẩu mới</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Xác nhận mật khẩu mới</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="profile.php" class="btn btn-secondary">Quay lại</a>
                    <button type="submit" class="btn btn-red">Đổi mật khẩu</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>