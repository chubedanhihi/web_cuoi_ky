<?php
session_start();
require 'config.php';
require 'model/product_db.php';
require 'model/category_db.php';

// Nếu chưa có giỏ hàng thì tạo
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Xóa sản phẩm khỏi giỏ
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    unset($_SESSION['cart'][$remove_id]);
    header("Location: cart.php");
    exit;
}

// Cập nhật số lượng
if (isset($_POST['update'])) {
    foreach ($_POST['qty'] as $product_id => $qty) {
        if ($qty > 0) {
            $_SESSION['cart'][$product_id] = $qty;
        }
    }
    header("Location: cart.php");
    exit;
}

// Lấy thông tin người dùng nếu đăng nhập
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT user_id, username, full_name, role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Chuẩn bị danh sách sản phẩm trong giỏ
$cart_items = [];
$total_all = 0;

foreach ($_SESSION['cart'] as $product_id => $qty) {
    $product = get_product($product_id);
    if ($product) {
        $price = $product['listPrice'] * (1 - $product['discountPercent']/100);
        $total = $price * $qty;
        $total_all += $total;
        $cart_items[] = [
            'id' => $product_id,
            'name' => $product['productName'],
            'image' => !empty($product['image']) ? htmlspecialchars($product['image']) : 'images/no-image.jpg',
            'price' => $price,
            'qty' => $qty,
            'total' => $total
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - SHOPKO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
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

    <!-- NỘI DUNG GIỎ HÀNG -->
    <div class="container my-5">
        <h2 class="text-center fw-bold mb-4">Giỏ hàng của bạn</h2>

        <?php if (empty($cart_items)): ?>
        <div class="alert alert-info text-center">Giỏ hàng đang trống!</div>
        <div class="text-center"><a href="products.php" class="btn btn-primary">Tiếp tục mua sắm</a></div>
        <?php else: ?>
        <form method="POST" action="cart.php">
            <table class="table table-bordered align-middle text-center">
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tổng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cart_items as $item): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="selected[]" value="<?= $item['id'] ?>" checked>
                        </td>
                        <td><img src="<?= $item['image'] ?>" class="img-fluid rounded" width="80"></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td class="text-danger fw-bold"><?= number_format($item['price']) ?>₫</td>
                        <td>
                            <input type="number" class="form-control text-center" name="qty[<?= $item['id'] ?>]"
                                value="<?= $item['qty'] ?>" min="1">
                        </td>
                        <td class="text-danger fw-bold"><?= number_format($item['total']) ?>₫</td>
                        <td>
                            <a href="cart.php?remove=<?= $item['id'] ?>" class="btn btn-sm btn-danger">Xóa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="d-flex justify-content-between mt-4 flex-wrap gap-3">
                <h4>Tổng cộng: <span class="text-danger"><?= number_format($total_all) ?>₫</span></h4>
                <div class="d-flex gap-2">
                    <button type="submit" name="update" class="btn btn-secondary">Cập nhật giỏ</button>
                    <button type="submit" formaction="checkout.php" class="btn btn-success">Thanh toán</button>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <!-- FOOTER -->
    <footer class="bg-dark text-white pt-5 pb-3 mt-auto">
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
    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
    var Tawk_API = Tawk_API || {},
        Tawk_LoadStart = new Date();
    (function() {
        var s1 = document.createElement("script"),
            s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/693e6bb66034a019831932e4/1jcdt9h32';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();
    </script>
    <!--End of Tawk.to Script-->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>