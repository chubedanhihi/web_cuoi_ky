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
        SELECT * FROM users
        WHERE username LIKE ? 
           OR email LIKE ? 
           OR full_name LIKE ?
        ORDER BY user_id DESC
    ");
    $search = "%$keyword%";
    $stmt->bind_param("sss", $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM users ORDER BY user_id DESC";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Quản lý người dùng</title>
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
                <li class="active">
                    <a href="users.php">
                        <i class="fa fa-users"></i> Người dùng
                    </a>
                </li>
                <li>
                    <a href="products.php">
                        <i class="fa fa-box"></i> Sản phẩm
                    </a>
                </li>
                <li>
                    <a href="orders.php">
                        <i class="fa fa-receipt"></i> Đơn hàng
                    </a>
                </li>
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
                <h1>Quản lý người dùng</h1>
                <span>Xin chào, <b><?= htmlspecialchars($_SESSION['admin_name']) ?></b></span>
            </header>

            <section class="page-content">

                <!-- ACTION BAR -->
                <div class="page-actions">
                    <h2>Danh sách người dùng</h2>

                    <form method="get" class="search-box">
                        <input type="text" name="keyword" placeholder="Tìm theo tên, email, username..."
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
                                <th>Username</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Quyền</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $row['user_id'] ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>

                                <td>
                                    <span class="badge <?= $row['role'] === 'USER' ? 'badge-user' : 'badge-guest' ?>">
                                        <?= $row['role'] ?>
                                    </span>
                                </td>

                                <td><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>

                                <td class="actions">
                                    <a href="edit_user.php?id=<?= $row['user_id'] ?>" class="btn-icon edit">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                    <a href="delete_user.php?id=<?= $row['user_id'] ?>" class="btn-icon delete"
                                        onclick="return confirm('Xóa người dùng này?')">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center;">
                                    Không có người dùng
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