<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - Shop KO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                    <?php if(isset($_SESSION['user_name'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown">
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#">Thông tin cá nhân</a></li>
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

    <!-- MAIN CONTENT -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-body text-center p-5">
                        <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                        <h1 class="mt-4 text-success fw-bold">ĐẶT HÀNG THÀNH CÔNG!</h1>
                        <p class="lead mt-3">Cảm ơn quý khách đã tin tưởng Shop KO</p>

                        <div class="row text-start mt-4">
                            <div class="col-md-6">
                                <strong>Mã đơn hàng:</strong><br>
                                <h4 class="text-danger"><?= htmlspecialchars($_SESSION['last_order_code'] ?? 'DH...') ?>
                                </h4>
                            </div>
                            <div class="col-md-6">
                                <strong>Mã vận đơn GHN:</strong><br>
                                <?php if (!empty($_SESSION['tracking_code'])): ?>
                                <h4 class="text-primary">
                                    <?= htmlspecialchars($_SESSION['tracking_code']) ?>
                                    <a href="https://ghn.vn/pages/tra-cuu-don-hang?order_code=<?= urlencode($_SESSION['tracking_code']) ?>"
                                        target="_blank" class="ms-2">
                                        <i class="fas fa-external-link-alt"></i> Tra cứu
                                    </a>
                                </h4>
                                <?php else: ?>
                                <span class="text-muted">Đang chờ lấy hàng...</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <hr class="my-4">

                        <p>Chúng tôi sẽ sớm liên hệ và giao hàng nhanh nhất có thể (1–3 ngày).</p>
                        <p>Nếu có thắc mắc vui lòng liên hệ Zalo: 090xxx hoặc Fanpage Shop KO</p>

                        <div class="mt-4 d-flex justify-content-center gap-3">
                            <a href="index.php" class="btn btn-outline-primary">Tiếp tục mua sắm</a>
                            <?php if (!empty($_SESSION['tracking_code'])): ?>
                            <a href="print_label.php?code=<?= urlencode($_SESSION['tracking_code']) ?>"
                                class="btn btn-success" target="_blank">In vận đơn</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php
// Dọn session để lần sau đặt lại không bị lẫn
unset($_SESSION['last_order_code'], $_SESSION['tracking_code']);
?>
</body>

</html>