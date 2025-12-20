<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

require_once '../config.php';

/* ====== TỔNG SỐ ====== */

// Tổng người dùng
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")
    ->fetch_assoc()['total'];

// Tổng sản phẩm
$totalProducts = $conn->query("SELECT COUNT(*) AS total FROM products")
    ->fetch_assoc()['total'];

// Tổng đơn hàng
$totalOrders = $conn->query("SELECT COUNT(*) AS total FROM orders")
    ->fetch_assoc()['total'];

// Tổng doanh thu (đơn hoàn thành)
$totalRevenue = $conn->query("
    SELECT SUM(grand_total) AS total
    FROM orders
    WHERE order_status = 'completed'
")->fetch_assoc()['total'] ?? 0;

/* ====== DOANH THU THEO THÁNG (BIỂU ĐỒ) ====== */
$monthlyRevenue = [];
$orderCountByMonth = [];

$sql = "
    SELECT 
        MONTH(order_date) AS month,
        SUM(grand_total) AS revenue,
        COUNT(*) AS orders
    FROM orders
    WHERE order_status = 'completed'
    GROUP BY MONTH(order_date)
    ORDER BY month
";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $monthlyRevenue[$row['month']] = (int)$row['revenue'];
    $orderCountByMonth[$row['month']] = (int)$row['orders'];
}

// Chuẩn hóa đủ 12 tháng
$revenueData = [];
$orderData = [];
for ($i = 1; $i <= 12; $i++) {
    $revenueData[] = $monthlyRevenue[$i] ?? 0;
    $orderData[] = $orderCountByMonth[$i] ?? 0;
}

/* ====== DOANH THU THÁNG NÀY / THÁNG TRƯỚC ====== */
$currentMonth = date('m');
$currentYear  = date('Y');

// Tháng này
$currentRevenue = $conn->query("
    SELECT SUM(grand_total) AS total
    FROM orders
    WHERE order_status='completed'
    AND MONTH(order_date)=$currentMonth
    AND YEAR(order_date)=$currentYear
")->fetch_assoc()['total'] ?? 0;

// Tháng trước
$prevMonth = $currentMonth - 1;
$prevYear  = $currentYear;
if ($prevMonth == 0) {
    $prevMonth = 12;
    $prevYear--;
}

$prevRevenue = $conn->query("
    SELECT SUM(grand_total) AS total
    FROM orders
    WHERE order_status='completed'
    AND MONTH(order_date)=$prevMonth
    AND YEAR(order_date)=$prevYear
")->fetch_assoc()['total'] ?? 0;

// % thay đổi
$percentChange = 0;
if ($prevRevenue > 0) {
    $percentChange = (($currentRevenue - $prevRevenue) / $prevRevenue) * 100;
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                <li class="active">
                    <a href="admin_dashboard.php">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Tổng quan</span>
                    </a>
                </li>
                <li>
                    <a href="users.php">
                        <i class="fa-solid fa-users"></i>
                        <span>Người dùng</span>
                    </a>
                </li>
                <li>
                    <a href="products.php">
                        <i class="fa-solid fa-box-open"></i>
                        <span>Sản phẩm</span>
                    </a>
                </li>
                <li>
                    <a href="orders.php">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Đơn hàng</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <a href="logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Đăng xuất</span>
                </a>
            </div>
        </aside>

        <!-- CONTENT -->
        <main class="content">

            <header class="topbar">
                <h1>Dashboard</h1>
                <span>Xin chào, <b><?= htmlspecialchars($_SESSION['admin_name']) ?></b></span>
            </header>

            <div class="page-content">

                <h2 class="dashboard-title">Tổng quan hệ thống</h2>

                <!-- STAT CARDS -->
                <div class="dashboard-stats">

                    <div class="stat-card">
                        <i class="fa-solid fa-users"></i>
                        <div>
                            <h3><?= number_format($totalUsers) ?></h3>
                            <p>Người dùng</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <i class="fa-solid fa-box"></i>
                        <div>
                            <h3><?= number_format($totalProducts) ?></h3>
                            <p>Sản phẩm</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <i class="fa-solid fa-receipt"></i>
                        <div>
                            <h3><?= number_format($totalOrders) ?></h3>
                            <p>Đơn hàng</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <i class="fa-solid fa-sack-dollar"></i>
                        <div>
                            <h3><?= number_format($totalRevenue, 0, ',', '.') ?>₫</h3>
                            <p>Doanh thu</p>
                        </div>
                    </div>

                </div>

                <!-- CHARTS -->
                <div class="charts">

                    <!-- ĐƠN HÀNG -->
                    <div class="chart-box">
                        <h3>📊 Đơn hàng theo tháng</h3>
                        <canvas id="orderChart" height="200"></canvas>
                    </div>

                    <!-- DOANH THU -->
                    <div class="chart-box">
                        <h3>💰 Xu hướng doanh thu</h3>

                        <div class="revenue-trend">
                            <div>
                                <p>Tháng này</p>
                                <div class="revenue-amount">
                                    <?= number_format($currentRevenue, 0, ',', '.') ?>₫
                                </div>
                            </div>

                            <div class="revenue-up <?= $percentChange >= 0 ? 'up' : 'down' ?>">
                                <i
                                    class="fa-solid <?= $percentChange >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' ?>"></i>
                                <span>
                                    <?= ($percentChange >= 0 ? '+' : '') . number_format($percentChange, 1) ?>%
                                </span>
                            </div>
                        </div>

                        <canvas id="revenueChart" height="180"></canvas>
                    </div>

                </div>

            </div>
        </main>

    </div>

    <script>
    new Chart(document.getElementById('orderChart'), {
        type: 'bar',
        data: {
            labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
            datasets: [{
                data: <?= json_encode($orderData) ?>,
                backgroundColor: '#3b82f6',
                borderRadius: 8
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
            datasets: [{
                data: <?= json_encode($revenueData) ?>,
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22,163,74,0.15)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    </script>

</body>

</html>