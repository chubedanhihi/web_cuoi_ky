<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$order_id = (int)$_GET['id'];

/* LẤY ĐƠN HÀNG */
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: orders.php');
    exit;
}

/* LẤY SẢN PHẨM */
$stmt = $conn->prepare("
    SELECT oi.*, p.productName
    FROM order_items oi
    JOIN products p ON oi.productID = p.productID
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();

/* MÀU TRẠNG THÁI */
$statusClass = [
    'pending'    => 'warning',
    'processing' => 'primary',
    'shipped'    => 'info',
    'completed'  => 'success',
    'cancelled'  => 'danger'
][$order['order_status']] ?? 'secondary';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Chi tiết đơn hàng</title>

    <!-- BOOTSTRAP 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container my-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold">
                Chi tiết đơn hàng
                <span class="text-danger">#<?= htmlspecialchars($order['order_code']) ?></span>
            </h3>
            <a href="orders.php" class="btn btn-secondary">← Quay lại</a>
        </div>

        <!-- INFO -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Thông tin khách hàng</h5>

                <div class="row g-3">
                    <div class="col-md-6"><b>Khách hàng:</b> <?= htmlspecialchars($order['customer_name']) ?></div>
                    <div class="col-md-6"><b>SĐT:</b> <?= htmlspecialchars($order['customer_phone']) ?></div>
                    <div class="col-md-6"><b>Địa chỉ:</b> <?= htmlspecialchars($order['customer_address']) ?></div>
                    <div class="col-md-6"><b>Thanh toán:</b> <?= htmlspecialchars($order['payment_method']) ?></div>
                    <div class="col-md-6">
                        <b>Trạng thái:</b>
                        <span class="badge bg-<?= $statusClass ?>">
                            <?= ucfirst($order['order_status']) ?>
                        </span>
                    </div>
                    <div class="col-md-6">
                        <b>Ngày đặt:</b>
                        <?= date('d/m/Y H:i', strtotime($order['order_date'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRODUCTS -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Danh sách sản phẩm</h5>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Sản phẩm</th>
                                <th width="120">Giá</th>
                                <th width="100">Số lượng</th>
                                <th width="150">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $items->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['productName']) ?></td>
                                <td><?= number_format($item['price'], 0, ',', '.') ?>₫</td>
                                <td class="text-center"><?= $item['quantity'] ?></td>
                                <td class="text-danger fw-semibold">
                                    <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>₫
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-end fs-5 fw-bold">
                    Tổng tiền:
                    <span class="text-danger">
                        <?= number_format($order['grand_total'], 0, ',', '.') ?>₫
                    </span>
                </div>

            </div>
        </div>

    </div>

</body>

</html>