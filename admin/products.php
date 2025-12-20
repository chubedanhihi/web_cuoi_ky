<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once '../config.php';

/* LẤY DANH SÁCH SẢN PHẨM */
$keyword = $_GET['keyword'] ?? '';

if ($keyword !== '') {
    $stmt = $conn->prepare(
        "SELECT * FROM products 
         WHERE productName LIKE ? 
         ORDER BY productID DESC"
    );
    $search = "%$keyword%";
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM products ORDER BY productID DESC";
    $result = $conn->query($sql);
}

?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm</title>
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
                <li class="active"><a href="products.php"><i class="fa fa-box"></i> Sản phẩm</a></li>
                <li><a href="orders.php"><i class="fa fa-receipt"></i> Đơn hàng</a></li>
            </ul>

            <div class="sidebar-footer">
                <a href="logout.php"><i class="fa fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </aside>

        <!-- CONTENT -->
        <main class="content">

            <header class="topbar">
                <h1>Quản lý sản phẩm</h1>
                <span>Xin chào, <b><?= htmlspecialchars($_SESSION['admin_name']) ?></b></span>
            </header>

            <section class="page-content">

                <div class="page-actions">

                    <form method="get" class="search-box">
                        <i class="fa fa-search"></i>
                        <input type="text" name="keyword" placeholder="Tìm sản phẩm..."
                            value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                    </form>

                    <a href="add_product.php" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Thêm sản phẩm
                    </a>

                </div>

                <div class="table-wrapper">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ảnh</th>
                                <th>Tên</th>
                                <th>Giá</th>
                                <th>Giảm (%)</th>
                                <th>Tồn kho</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): 
                                $img = $row['image'] && file_exists("../".$row['image'])
                                    ? "../".$row['image']
                                    : "https://via.placeholder.com/60x60?text=No+Img";
                            ?>
                            <tr>
                                <td>#<?= $row['productID'] ?></td>
                                <td><img src="<?= $img ?>" class="thumb"></td>
                                <td><?= htmlspecialchars($row['productName']) ?></td>
                                <td><?= number_format($row['listPrice'],0,',','.') ?>₫</td>
                                <td><?= $row['discountPercent'] ?>%</td>
                                <td><?= $row['stock_quantity'] ?></td>
                                <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                                <td class="actions">
                                    <a href="edit_product.php?id=<?= $row['productID'] ?>" class="btn-icon edit">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                    <a href="delete_product.php?id=<?= $row['productID'] ?>" class="btn-icon delete"
                                        onclick="return confirm('Xóa sản phẩm này?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align:center;">Chưa có sản phẩm</td>
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