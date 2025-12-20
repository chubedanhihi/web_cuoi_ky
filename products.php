<?php
session_start();
require 'config.php';
require 'model/product_db.php';
require 'model/category_db.php';

$categories = get_categories();

// Lấy category nếu có
$categoryID = $_GET['categoryID'] ?? null;
$products = $categoryID ? get_products_by_category((int)$categoryID) : get_all_products();

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
    <title>SHOPKO - Sản phẩm</title>
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
                    <li class="nav-item"><a class="nav-link active" href="products.php">Sản Phẩm</a></li>
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

    <!-- DANH SÁCH SẢN PHẨM -->
    <div class="container my-5">
        <h2 class="text-center fw-bold mb-5 display-5 gradient-text">
            SẢN PHẨM CỦA CỬA HÀNG
        </h2>

        <!-- Lọc danh mục -->
        <div class="d-flex justify-content-center flex-wrap gap-2 mb-5">
            <a href="products.php" class="btn <?= !$categoryID ? 'btn-primary' : 'btn-outline-primary' ?>">Tất cả</a>
            <?php foreach ($categories as $cat): ?>
            <a href="products.php?categoryID=<?= $cat['categoryID'] ?>"
                class="btn <?= ($categoryID == $cat['categoryID']) ? 'btn-primary' : 'btn-outline-primary' ?>">
                <?= htmlspecialchars($cat['categoryName']) ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Danh sách sản phẩm -->
        <?php if (!empty($products)): ?>
        <div class="row g-4">
            <?php foreach ($products as $row):
            $final_price = $row['listPrice'] * (1 - $row['discountPercent']/100);
           $image_src = !empty($row['image']) ? htmlspecialchars($row['image']) : 'images/no-image.jpg';

        ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card product-card h-100 shadow-sm border-0 position-relative overflow-hidden rounded-3">
                    <!-- Ảnh sản phẩm -->
                    <a href="product_detail.php?id=<?= $row['productID'] ?>" class="ratio ratio-1x1 d-block bg-light">
                        <img src="<?= $image_src ?>" class="card-img-top object-fit-cover"
                            alt="<?= htmlspecialchars($row['productName']) ?>" onerror="this.src='images/no-image.jpg'"
                            loading="lazy">
                    </a>
                    <?php if ($row['discountPercent'] > 0): ?>
                    <span class="badge bg-danger position-absolute top-0 end-0 m-2 fw-bold fs-6">
                        -<?= round($row['discountPercent']) ?>%
                    </span>
                    <?php endif; ?>
                    <div class="card-body text-center d-flex flex-column">
                        <h6 class="card-title fw-bold mb-2">
                            <a href="product_detail.php?id=<?= $row['productID'] ?>"
                                class="text-decoration-none text-dark">
                                <?= htmlspecialchars($row['productName']) ?>
                            </a>
                        </h6>
                        <?php if (!empty($row['categoryName'])): ?>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($row['categoryName']) ?></p>
                        <?php endif; ?>
                        <div class="mt-auto">
                            <?php if ($row['discountPercent'] > 0): ?>
                            <del class="text-muted small"><?= number_format($row['listPrice']) ?>₫</del>
                            <div class="text-danger fw-bold fs-5 mt-1"><?= number_format($final_price) ?>₫</div>
                            <?php else: ?>
                            <div class="text-danger fw-bold fs-5"><?= number_format($row['listPrice']) ?>₫</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <img src="images/no-image.jpg" width="120" class="opacity-50 mb-4">
            <p class="text-muted fs-3">Không có sản phẩm nào!</p>
        </div>
        <?php endif; ?>
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
</body>

</html>