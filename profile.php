<?php
session_start();
require_once 'config.php';

/* ====== KIỂM TRA ĐĂNG NHẬP ====== */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* ====== LẤY THÔNG TIN USER ====== */
$stmt = $conn->prepare("
    SELECT user_id, username, email, full_name, role, created_at
    FROM users WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* ====== LẤY ĐƠN HÀNG CỦA USER ====== */
$stmt2 = $conn->prepare("
    SELECT order_id, order_code, order_date, payment_method,
           grand_total, order_status, shipping_order_code
    FROM orders
    WHERE user_id = ?
    ORDER BY order_date DESC
");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$orders = $stmt2->get_result();
$stmt2->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Thông tin cá nhân - SHOPKO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="css/profile.css">
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
                    <li class="nav-item"><a class="nav-link active" href="index.php">Trang Chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">Sản Phẩm</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Liên Hệ</a></li>
                    <?php if ($user): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php">Thông tin cá nhân</a></li>
                            <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Đăng nhập</a></li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Giỏ hàng
                            <?php
        // Nếu bạn lưu giỏ hàng trong session
        $cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
        if($cart_count > 0):
        ?>
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $cart_count ?>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">

        <!-- THÔNG TIN CÁ NHÂN -->
        <div class="card mb-4">
            <div class="card-header fw-bold">👤 Thông tin cá nhân</div>
            <div class="card-body">
                <p><strong>Họ tên:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
                <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Vai trò:</strong> <?= htmlspecialchars($user['role']) ?></p>
                <p><strong>Ngày tạo:</strong>
                    <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                </p>

                <div class="mt-3">
                    <a href="edit_profile.php" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-user-pen me-1"></i>
                        Chỉnh sửa
                    </a>
                    <a href="change_password.php" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-key me-1"></i>
                        Đổi mật khẩu
                    </a>

                </div>
            </div>
        </div>

        <!-- ĐƠN HÀNG -->
        <div class="card">
            <div class="card-header fw-bold">📦 Đơn hàng của tôi</div>
            <div class="card-body p-0">

                <?php if ($orders->num_rows === 0): ?>
                <p class="text-center p-4 text-muted">Bạn chưa có đơn hàng nào</p>
                <?php else: ?>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Ngày</th>
                                <th>Thanh toán</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Vận đơn</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($o = $orders->fetch_assoc()): ?>
                            <tr>
                                <td><?= $o['order_code'] ?></td>
                                <td><?= date('d/m/Y', strtotime($o['order_date'])) ?></td>
                                <td><?= $o['payment_method'] ?></td>
                                <td class="text-danger fw-bold">
                                    <?= number_format($o['grand_total']) ?>₫
                                </td>
                                <td>
                                    <span class="badge bg-<?=
                                    match($o['order_status']) {
                                        'pending'    => 'secondary',
                                        'processing' => 'warning',
                                        'shipped'    => 'info',
                                        'completed'  => 'success',
                                        'cancelled'  => 'danger',
                                        default      => 'dark'
                                    }
                                ?>">
                                        <?= ucfirst($o['order_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $o['shipping_order_code'] ?: '-' ?>
                                </td>
                                <td>
                                    <a href="order_detail.php?order_id=<?= $o['order_id'] ?>"
                                        class="btn btn-sm btn-outline-primary">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- FOOTER -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5>SHOPKO</h5>
                    <p>Chúng tôi mang đến những sản phẩm thời trang tốt nhất cho bạn.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Liên hệ</h5>
                    <p>Email: support@shopko.vn</p>
                    <p>Hotline: 1900 1234</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Theo dõi chúng tôi</h5>
                    <a href="#" class="text-white me-2">Facebook</a>
                    <a href="#" class="text-white me-2">Instagram</a>
                    <a href="#" class="text-white">TikTok</a>
                </div>
            </div>
            <div class="text-center mt-3">&copy; 2025 SHOPKO. All rights reserved.</div>
        </div>
    </footer>

</body>

</html>