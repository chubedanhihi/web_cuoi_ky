<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once '../config.php';

/* TÌM KIẾM */
$keyword = $_GET['keyword'] ?? '';

if ($keyword !== '') {
    $stmt = $conn->prepare("
        SELECT * FROM orders
        WHERE order_code LIKE ?
           OR customer_name LIKE ?
           OR customer_phone LIKE ?
        ORDER BY order_id DESC
    ");
    $search = "%$keyword%";
    $stmt->bind_param("sss", $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM orders ORDER BY order_id DESC";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ICON -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/admin_style.css">
</head>

<body>

    <div class="admin-container">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-user-shield"></i>
                <h2>ADMIN</h2>
            </div>

            <ul class="menu">
                <li class="active"><a href="admin_dashboard.php"><i class="fa fa-chart-line"></i> Dashboard</a></li>
                <li><a href="users.php"><i class="fa fa-users"></i> Người dùng</a></li>
                <li><a href="products.php"><i class="fa fa-box"></i> Sản phẩm</a></li>
                <li class="active"><a href="orders.php"><i class="fa fa-receipt"></i> Đơn hàng</a></li>
            </ul>

            <div class="sidebar-footer">
                <a href="logout.php" onclick="return confirm('Đăng xuất?')">
                    <i class="fa fa-right-from-bracket"></i> Đăng xuất
                </a>
            </div>
        </aside>

        <!-- CONTENT -->
        <main class="content">

            <header class="topbar">
                <h1>Quản lý đơn hàng</h1>
                <span>Xin chào, <b><?= htmlspecialchars($_SESSION['admin_name']) ?></b></span>
            </header>

            <section class="page-content">

                <!-- ACTION BAR -->
                <div class="page-actions">
                    <h2>Danh sách đơn hàng</h2>

                    <form method="get" class="search-box">
                        <input type="text" name="keyword" placeholder="Mã đơn / Tên / SĐT..."
                            value="<?= htmlspecialchars($keyword) ?>">
                        <button type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- TABLE -->
                <div class="table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>SĐT</th>
                                <th>Thanh toán</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày đặt</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $row['order_id'] ?></td>

                                <td>
                                    <b><?= htmlspecialchars($row['order_code']) ?></b>
                                </td>

                                <td><?= htmlspecialchars($row['customer_name']) ?></td>

                                <td><?= htmlspecialchars($row['customer_phone']) ?></td>

                                <td>
                                    <span
                                        class="badge <?= $row['payment_method'] === 'COD' ? 'badge-cod' : 'badge-qr' ?>">
                                        <?= $row['payment_method'] ?>
                                    </span>
                                </td>

                                <td>
                                    <?= number_format($row['grand_total'], 0, ',', '.') ?>₫
                                </td>

                                <td>
                                    <form action="update_order_status.php" method="post">
                                        <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">

                                        <select name="order_status" onchange="this.form.submit()"
                                            class="status-select <?= $row['order_status'] ?>">
                                            <option value="pending"
                                                <?= $row['order_status']=='pending'?'selected':'' ?>>Chờ xử lý</option>
                                            <option value="processing"
                                                <?= $row['order_status']=='processing'?'selected':'' ?>>Đang xử lý
                                            </option>
                                            <option value="shipped"
                                                <?= $row['order_status']=='shipped'?'selected':'' ?>>Đang giao</option>
                                            <option value="completed"
                                                <?= $row['order_status']=='completed'?'selected':'' ?>>Hoàn thành
                                            </option>
                                            <option value="cancelled"
                                                <?= $row['order_status']=='cancelled'?'selected':'' ?>>Hủy</option>
                                        </select>
                                    </form>
                                </td>


                                <td><?= date('d/m/Y H:i', strtotime($row['order_date'])) ?></td>

                                <td class="actions">
                                    <a href="order_detail.php?id=<?= $row['order_id'] ?>" class="btn-icon view">
                                        <i class="fa fa-eye"></i>
                                    </a>

                                    <a href="delete_order.php?id=<?= $row['order_id'] ?>" class="btn-icon delete"
                                        onclick="return confirm('Xóa đơn hàng này?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align:center;">
                                    Chưa có đơn hàng
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>

            </section>

        </main>

    </div>

</body>

</html>