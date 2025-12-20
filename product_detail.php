<?php
session_start();
require 'config.php';
require 'model/product_db.php';
require 'model/category_db.php';

// Lấy ID sản phẩm từ URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = get_product($id);

// Nếu sản phẩm không tồn tại
if (!$product) {
    echo "<!DOCTYPE html><html lang='vi'><head><meta charset='UTF-8'><title>Sản phẩm không tồn tại</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='css/style.css'></head><body>
    <div class='container my-5'><h3>Sản phẩm không tồn tại!</h3>
    <a href='products.php' class='btn btn-primary mt-3'>Quay lại trang sản phẩm</a></div>
    </body></html>";
    exit;
}

// Tính giá sau giảm
$final_price = $product['listPrice'] * (1 - $product['discountPercent']/100);

// Lấy thông tin user nếu đã đăng nhập
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT user_id, username, full_name, role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['productName']) ?> - SHOPKO</title>
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
                    <?php if($user): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#">Thông tin cá nhân</a></li>
                            <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Đăng nhập</a></li>
                    <?php endif; ?><li class="nav-item">
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

    <!-- CHI TIẾT SẢN PHẨM -->
    <div class="container my-5">
        <div class="row">
            <!-- Ảnh -->
            <div class="col-md-5">
                <img src="<?= !empty($product['image']) ? htmlspecialchars($product['image']) : 'images/no-image.jpg' ?>"
                    class="img-fluid rounded shadow" alt="<?= htmlspecialchars($product['productName']) ?>">

            </div>
            <!-- Thông tin -->
            <div class="col-md-7">
                <h2><?= htmlspecialchars($product['productName']) ?></h2>
                <p class="text-muted"><?= htmlspecialchars($product['categoryName']) ?></p>
                <h3 class="text-danger fw-bold"><?= number_format($final_price) ?>₫</h3>
                <?php if($product['discountPercent']>0): ?>
                <del class="text-muted"><?= number_format($product['listPrice']) ?>₫</del>
                <p class="text-danger">Tiết kiệm <?= $product['discountPercent'] ?>%</p>
                <?php endif; ?>
                <p class="mt-3"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

                <!-- Số lượng -->
                <div class="mt-4 mb-3">
                    <label class="fw-bold mb-2">Số lượng:</label>
                    <input type="number" id="quantity" class="form-control qty-box d-inline-block" value="1" min="1">
                </div>

                <!-- Nút thêm giỏ & thanh toán -->
                <div class="d-flex gap-3 mt-4">
                    <form action="add_to_cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $product['productID'] ?>">
                        <input type="hidden" name="quantity" id="qty_cart" value="1">
                        <button type="submit" class="btn btn-add-cart px-4">
                            <i class="fas fa-shopping-cart me-2"></i> Thêm vào giỏ hàng
                        </button>
                    </form>

                    <form action="checkout.php" method="POST">
                        <input type="hidden" name="selected[]" value="<?= $product['productID'] ?>">
                        <input type="hidden" name="qty[<?= $product['productID'] ?>]" id="qty_checkout" value="1">
                        <button type="submit" class="btn btn-buy-now px-4">
                            <i class="fas fa-bolt me-2"></i> Thanh toán ngay
                        </button>
                    </form>
                </div>

                <a href="products.php" class="btn btn-secondary mt-4">← Quay lại</a>
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
    <script>
    // Đồng bộ số lượng cho 2 form
    document.getElementById("quantity").addEventListener("input", function() {
        document.getElementById("qty_cart").value = this.value;
        document.getElementById("qty_checkout").value = this.value;
    });
    </script>

</body>

</html>