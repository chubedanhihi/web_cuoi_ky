<?php
// admin_login.php
session_start();
require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {

        $stmt = $conn->prepare(
            "SELECT admin_id, username, password, full_name
             FROM admin
             WHERE username = ?
             LIMIT 1"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($admin = $result->fetch_assoc()) {
          if ($password === $admin['password']) {
    $_SESSION['admin_id']   = $admin['admin_id'];
    $_SESSION['admin_name'] = $admin['full_name'] ?? $admin['username'];
    header("Location: admin_dashboard.php");
    exit();
}
        }

        $error = 'Sai tài khoản hoặc mật khẩu';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>

<body>

    <form class="login-box" method="post">
        <h2>Admin Login</h2>

        <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-group">
            <label>Tài khoản</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
            <label>Mật khẩu</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit" class="btn-login">Đăng nhập</button>

        <a href="../index.php" class="back-home">← Quay lại trang chủ</a>
        <div class="footer-text">© 2025 Admin Panel</div>
    </form>

</body>

</html>