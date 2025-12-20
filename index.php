<?php
session_start();
require 'config.php';
require 'model/product_db.php';
require 'model/category_db.php';

// Lấy tất cả sản phẩm
$products = get_all_products();

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
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHOPKO - Trang Chủ</title>
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

    <!-- SLIDE ẢNH -->
    <div class="container-fluid px-0 mb-5">
        <div id="heroCarousel" class="carousel slide shadow-lg" data-bs-ride="carousel"
            style="max-height:520px; overflow:hidden;">
            <div class="carousel-inner rounded-3">
                <div class="carousel-item active">
                    <img src="images/sl11.webp" class="d-block w-100" alt="Sale 2025"
                        style="height:520px; object-fit:cover;"
                        onerror="this.src='https://via.placeholder.com/1600x520/ff6b6b/ffffff?text=SHOPKO+BIG+SALE+2025'">
                    <div class="carousel-caption d-none d-md-block text-start pb-5">
                        <h1 class="fw-bold display-5 text-white text-shadow">Bộ sưu tập mới 2025</h1>
                        <p class="fs-4 text-white text-shadow">Giảm tới <strong class="text-warning">50%</strong> tất cả
                            sản phẩm</p>
                        <a href="products.php" class="btn btn-danger btn-lg px-4">Mua ngay</a>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="images/sl22.jpg" class="d-block w-100" alt="Thời trang mới"
                        style="height:520px; object-fit:cover;"
                        onerror="this.src='https://via.placeholder.com/1600x520/4ecdc4/ffffff?text=THOI+TRANG+MOI+2025'">
                    <div class="carousel-caption d-none d-md-block text-center">
                        <h1 class="fw-bold display-5 text-white text-shadow">Phong cách & Đẳng cấp</h1>
                        <p class="fs-4 text-white text-shadow">Sản phẩm hot nhất mùa này</p>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="images/sl33.png" class="d-block w-100" alt="Ưu đãi cuối tuần"
                        style="height:520px; object-fit:cover;"
                        onerror="this.src='https://via.placeholder.com/1600x520/f7b731/ffffff?text=UU+DAI+CUOI+TUAN+50%25'">
                    <div class="carousel-caption d-none d-md-block text-end pb-5">
                        <h1 class="fw-bold display-5 text-white text-shadow">Ưu đãi cuối tuần</h1>
                        <p class="fs-4 text-white text-shadow">Sale sốc <strong class="text-warning">lên đến
                                50%</strong></p>
                        <a href="products.php" class="btn btn-warning btn-lg text-dark px-4">Xem ngay</a>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
            </div>
        </div>
    </div>

    <!-- DANH SÁCH SẢN PHẨM -->
    <div class="container my-5">
        <h2 class="text-center fw-bold mb-5 display-5 gradient-text">
            SẢN PHẨM GIẢM GIÁ SÂU
        </h2>
        <?php if (!empty($products)): ?>
        <div class="row g-4">
            <?php foreach ($products as $row): 
            $final_price = $row['listPrice'] * (1 - $row['discountPercent']/100);
            $image_src = !empty($row['image']) ? htmlspecialchars($row['image']) : 'images/no-image.jpg';

        ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card product-card h-100 shadow-sm border-0 position-relative overflow-hidden">
                    <div class="ratio ratio-1x1">
                        <img src="<?= $image_src ?>" class="card-img-top object-fit-cover"
                            alt="<?= htmlspecialchars($row['productName']) ?>" onerror="this.src='images/no-image.jpg'"
                            loading="lazy">
                    </div>
                    <?php if ($row['discountPercent'] > 0): ?>
                    <span class="badge bg-danger position-absolute top-0 end-0 m-2 fw-bold">
                        -<?= round($row['discountPercent']) ?>%
                    </span>
                    <?php endif; ?>
                    <div class="card-body text-center d-flex flex-column">
                        <h6 class="card-title fw-bold mb-2"><?= htmlspecialchars($row['productName']) ?></h6>
                        <?php if (!empty($row['categoryName'])): ?>
                        <p class="text-muted small mb-2"><?= htmlspecialchars($row['categoryName']) ?></p>
                        <?php endif; ?>
                        <div class="mt-auto">
                            <?php if ($row['discountPercent'] > 0): ?>
                            <del class="text-muted small"><?= number_format($row['listPrice']) ?>₫</del><br>
                            <strong class="text-danger fs-5"><?= number_format($final_price) ?>₫</strong>
                            <?php else: ?>
                            <strong class="text-danger fs-5"><?= number_format($row['listPrice']) ?>₫</strong>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <p class="text-muted fs-4">Chưa có sản phẩm nào!</p>
            <a href="admin/add_product.php" class="btn btn-primary">Thêm sản phẩm đầu tiên</a>
        </div>
        <?php endif; ?>
    </div>
    </div>

    <!-- Tawk.to script -->
    <div id="tawkchat-container" style="position: fixed; bottom: 0; right: 0; z-index: 99999;"></div>
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