<?php
session_start();
require_once __DIR__ . '/config.php';


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {

        $stmt = $conn->prepare(
            "SELECT user_id, password, full_name, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {

                $_SESSION['user_id']   = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];

                header("Location: index.php");
                exit;
            }
        }

        $error = 'Sai email hoặc mật khẩu';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="css/auth.css">
</head>

<body>

    <form class="auth-box" method="post">
        <h2>Đăng nhập</h2>

        <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Mật khẩu" required>

        <button>Đăng nhập</button>

        <a href="register.php">Chưa có tài khoản? Đăng ký</a>
        <a href="admin/admin_login.php" class="admin-link">
            Đăng nhập với quyền Quản trị
        </a>
        <a href="index.php">← Về trang chủ</a>

    </form>

</body>

</html