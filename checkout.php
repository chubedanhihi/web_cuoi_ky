<?php
session_start();
require 'config.php';
require 'model/product_db.php';
require 'model/config_shipping.php';   // GHN_TOKEN + GHN_SHOP_ID

// Lấy toàn bộ sản phẩm trong giỏ từ session
// ƯU TIÊN THANH TOÁN NGAY (POST)
if (!empty($_POST['selected']) && !empty($_POST['qty'])) {
    $selected = $_POST['selected'];        // mảng productID
    $qty_list = $_POST['qty'];             // qty[productID]

// THANH TOÁN TỪ GIỎ HÀNG
} elseif (!empty($_SESSION['cart'])) {
    $selected = array_keys($_SESSION['cart']);
    $qty_list = $_SESSION['cart'];

} else {
    die("<script>alert('Không có sản phẩm để thanh toán!'); window.location='products.php';</script>");
}


$checkout_items = [];
$total_all = 0;
$total_weight = 0;

foreach ($selected as $product_id) {
    $product = get_product($product_id);
    if (!$product) continue;

    $qty = max(1, (int)($qty_list[$product_id] ?? 1));
    $final_price = $product['listPrice'] * (1 - $product['discountPercent']/100);
    $total = $final_price * $qty;

    $total_weight += 300 * $qty; // 300g mặc định

    $checkout_items[] = [
        'id' => $product_id,
        'name' => $product['productName'],
        'image' => $product['image'] ?: 'images/no-image.jpg',
        'price' => $final_price,
        'qty' => $qty,
        'total' => $total,
        'discount' => $product['discountPercent']
    ];

    $total_all += $total;
}

// Lấy thông tin user nếu đăng nhập
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT user_id, username, full_name, role FROM users WHERE user_id=?");
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
    <title>Checkout - SHOPKO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">


</head>

<body>

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
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Giỏ hàng
                            <?php $cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; if($cart_count>0): ?>
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
        <h2 class="text-center fw-bold mb-5 text-danger">XÁC NHẬN ĐƠN HÀNG</h2>
        <div class="row g-5">
            <div class="col-lg-5">
                <?php foreach($checkout_items as $item): ?>
                <div class="d-flex mb-3 p-3 border rounded shadow-sm align-items-center">
                    <img src="<?= htmlspecialchars($item['image']) ?>" class="rounded me-3"
                        style="width:90px;height:90px;object-fit:cover;">
                    <div>
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                        <?php if($item['discount']>0): ?><span
                            class="badge bg-danger">-<?= $item['discount'] ?>%</span><?php endif; ?>
                        <div>x<?= $item['qty'] ?> → <strong
                                class="text-danger"><?= number_format($item['total']) ?>₫</strong></div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="bg-light p-4 rounded mt-4">
                    <div class="d-flex justify-content-between mb-2"><span>Tạm
                            tính</span><strong><?= number_format($total_all) ?>₫</strong></div>
                    <div class="d-flex justify-content-between mb-3"><span>Phí vận chuyển (GHN)</span><strong
                            id="shippingDisplay">Đang tính...</strong></div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <h4>TỔNG THANH TOÁN</h4>
                        <h4 id="grandTotal"><?= number_format($total_all) ?>₫</h4>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <form id="checkoutForm" action="place_order.php" method="POST" class="payment-box">
                    <input type="hidden" name="total_all" value="<?= $total_all ?>">
                    <input type="hidden" name="shipping_fee" id="shipping_fee" value="0">
                    <input type="hidden" name="grand_total" id="grand_total" value="<?= $total_all ?>">
                    <input type="hidden" name="weight" value="<?= $total_weight ?>">
                    <input type="hidden" name="to_district_id" id="to_district_id">
                    <input type="hidden" name="to_ward_code" id="to_ward_code">
                    <?php foreach($checkout_items as $item): ?>
                    <input type="hidden" name="product_id[]" value="<?= $item['id'] ?>">
                    <input type="hidden" name="qty[]" value="<?= $item['qty'] ?>">
                    <?php endforeach; ?>

                    <h4 class="fw-bold mb-4">Thông tin nhận hàng</h4>
                    <div class="row g-3">
                        <div class="col-md-6"><input type="text" name="fullname" class="form-control"
                                placeholder="Họ và tên *" required></div>
                        <div class="col-md-6"><input type="text" name="phone" class="form-control"
                                placeholder="Số điện thoại *" required></div>
                        <div class="col-12"><input type="email" name="email" class="form-control" placeholder="Email">
                        </div>

                        <div class="col-md-4"><select id="province" class="form-select" required>
                                <option value="">Tỉnh/Thành *</option>
                            </select></div>
                        <div class="col-md-4"><select id="district" class="form-select" required disabled>
                                <option value="">Quận/Huyện *</option>
                            </select></div>
                        <div class="col-md-4"><select id="ward" class="form-select" required disabled>
                                <option value="">Phường/Xã *</option>
                            </select></div>
                        <div class="col-12"><input type="text" name="address" class="form-control"
                                placeholder="Số nhà, tên đường *" required></div>
                        <div class="col-12"><textarea name="note" class="form-control" rows="2"
                                placeholder="Ghi chú"></textarea></div>
                    </div>

                    <hr class="my-5">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" name="payment" value="cod" id="cod" checked>
                        <label class="form-check-label fw-bold" for="cod">Thanh toán khi nhận hàng (COD)</label>
                    </div>
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="radio" name="payment" value="bank">
                        <label class="form-check-label">Chuyển khoản / Momo / VNPay</label>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold py-3">HOÀN TẤT ĐẶT HÀNG</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    async function loadProvinces() {
        const res = await fetch('https://online-gateway.ghn.vn/shiip/public-api/master-data/province', {
            headers: {
                'Token': '<?= GHN_TOKEN ?>'
            }
        });
        const json = await res.json();
        let html = '<option value="">Chọn Tỉnh/Thành phố *</option>';
        json.data.forEach(p => html += `<option value="${p.ProvinceID}">${p.ProvinceName}</option>`);
        document.getElementById('province').innerHTML = html;
    }
    async function loadDistricts() {
        const provinceId = document.getElementById('province').value;
        if (!provinceId) return;
        document.getElementById('district').disabled = true;
        document.getElementById('ward').disabled = true;
        const res = await fetch('https://online-gateway.ghn.vn/shiip/public-api/master-data/district', {
            method: 'POST',
            headers: {
                'Token': '<?= GHN_TOKEN ?>',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                province_id: +provinceId
            })
        });
        const json = await res.json();
        let html = '<option value="">Chọn Quận/Huyện *</option>';
        json.data.forEach(d => html += `<option value="${d.DistrictID}">${d.DistrictName}</option>`);
        document.getElementById('district').innerHTML = html;
        document.getElementById('district').disabled = false;
    }
    async function loadWards() {
        const districtId = document.getElementById('district').value;
        if (!districtId) return;
        document.getElementById('ward').disabled = true;
        const res = await fetch(
            `https://online-gateway.ghn.vn/shiip/public-api/master-data/ward?district_id=${districtId}`, {
                headers: {
                    'Token': '<?= GHN_TOKEN ?>'
                }
            });
        const json = await res.json();
        let html = '<option value="">Chọn Phường/Xã *</option>';
        json.data.forEach(w => html += `<option value="${w.WardCode}">${w.WardName}</option>`);
        document.getElementById('ward').innerHTML = html;
        document.getElementById('ward').disabled = false;
    }
    async function calculateFee() {
        const districtId = document.getElementById('district').value;
        const wardCode = document.getElementById('ward').value;
        if (!districtId || !wardCode) {
            document.getElementById('shippingDisplay').innerHTML = 'Vui lòng chọn đầy đủ địa chỉ';
            return;
        }
        document.getElementById('shippingDisplay').innerHTML = 'Đang tính phí GHN...';
        const res = await fetch('https://online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/fee', {
            method: 'POST',
            headers: {
                'Token': '<?= GHN_TOKEN ?>',
                'Content-Type': 'application/json',
                'ShopId': <?= GHN_SHOP_ID ?>
            },
            body: JSON.stringify({
                service_type_id: 2,
                to_district_id: +districtId,
                to_ward_code: wardCode,
                weight: <?= $total_weight ?>,
                insurance_value: <?= $total_all ?>,
                height: 10,
                width: 10,
                length: 20
            })
        });
        const json = await res.json();
        let fee = json.code === 200 ? json.data.total : 35000;
        const grand = <?= $total_all ?> + fee;
        document.getElementById('shippingDisplay').innerHTML =
            `<strong class="text-success">${fee.toLocaleString()}₫</strong>`;
        document.getElementById('shipping_fee').value = fee;
        document.getElementById('grand_total').value = grand;
        document.getElementById('grandTotal').textContent = grand.toLocaleString() + '₫';
        document.getElementById('to_district_id').value = districtId;
        document.getElementById('to_ward_code').value = wardCode;
    }
    document.getElementById('province').onchange = loadDistricts;
    document.getElementById('district').onchange = loadWards;
    document.getElementById('ward').onchange = calculateFee;
    loadProvinces();
    </script>
    <!-- FOOTER CỐ ĐỊNH -->
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>