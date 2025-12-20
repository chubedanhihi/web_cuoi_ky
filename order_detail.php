<?php
session_start();
require_once 'config.php';

/* ===== LẤY USER (GIỐNG INDEX.PHP) ===== */
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmtU = $conn->prepare("
        SELECT user_id, username, full_name, role 
        FROM users 
        WHERE user_id = ?
    ");
    $stmtU->bind_param("i", $_SESSION['user_id']);
    $stmtU->execute();
    $user = $stmtU->get_result()->fetch_assoc();
    $stmtU->close();
}

if (!$user) {
    header("Location: login.php");
    exit;
}

$user_id  = $user['user_id'];
$order_id = (int)($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    die("Đơn hàng không hợp lệ");
}

/* ===== LẤY ĐƠN ===== */
$stmt = $conn->prepare("
    SELECT *
    FROM orders
    WHERE order_id = ? AND user_id = ?
");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Không tìm thấy đơn hàng");
}

/* ===== LẤY SẢN PHẨM ===== */
$stmt2 = $conn->prepare("
    SELECT oi.*, p.productName
    FROM order_items oi
    JOIN products p ON oi.productID = p.productID
    WHERE oi.order_id = ?
");
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$items = $stmt2->get_result();
$stmt2->close();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- CSS CHUNG -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- NAVBAR (GIỐNG INDEX) -->
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

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Thông tin cá nhân</a></li>
                            <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <?php
                        $cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
                        if ($cart_count > 0):
                        ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge bg-danger">
                                <?= $cart_count ?>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </nav>

    <!-- CONTENT -->
    <div class="container py-5">
        <h3 class="mb-4">🧾 Chi tiết đơn hàng</h3>

        <div class="card mb-4 shadow-sm">
            <div class="card-header fw-bold">Thông tin đơn</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <p><b>Mã đơn:</b> <?= $order['order_code'] ?></p>
                    <p><b>Trạng thái:</b>
                        <span class="badge bg-info text-dark"><?= $order['order_status'] ?></span>
                    </p>
                    <p><b>Thanh toán:</b> <?= $order['payment_method'] ?></p>
                    <p><b>Ngày đặt:</b> <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></p>
                </div>
                <div class="col-md-6">
                    <p><b>Khách hàng:</b> <?= $order['customer_name'] ?></p>
                    <p><b>SĐT:</b> <?= $order['customer_phone'] ?></p>
                    <p><b>Email:</b> <?= $order['customer_email'] ?></p>
                    <p><b>Địa chỉ:</b> <?= $order['customer_address'] ?></p>
                </div>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header fw-bold">Sản phẩm</div>
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $items->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['productName'] ?></td>
                        <td><?= number_format($row['price']) ?>₫</td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= number_format($row['price'] * $row['quantity']) ?>₫</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="card shadow-sm">
            <div class="card-body text-end">
                <p>Tạm tính: <b><?= number_format($order['total_amount']) ?>₫</b></p>
                <p>Phí ship: <b><?= number_format($order['shipping_fee']) ?>₫</b></p>
                <h5 class="text-danger">Tổng cộng: <?= number_format($order['grand_total']) ?>₫</h5>

                <?php if ($order['order_status'] === 'shipped'): ?>
                <form method="post" action="order_received.php" onsubmit="return confirm('Xác nhận đã nhận hàng?');">
                    <input type="hidden" name="order_id" value="<?= $order_id ?>">
                    <button type="submit" class="btn btn-confirm-received mt-3">
                        <i class="fa-solid fa-circle-check me-2"></i>
                        Xác nhận đã nhận hàng
                    </button>

                </form>
                <?php endif; ?>
            </div>
        </div>

        <a href="profile.php" class="btn btn-secondary mt-4">← Quay lại</a>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>